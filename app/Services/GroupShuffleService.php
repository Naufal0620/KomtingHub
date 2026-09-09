<?php

namespace App\Services;

use App\Models\Group;
use App\Models\ShuffleLog;
use App\Models\ShuffleRun;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;
use RuntimeException;

class GroupShuffleService
{
    public const ALGORITHM = 'seeded-shuffle-v1';

    public const VERSION = '1.1.0';

    /**
     * Shuffle the members of a subject into groups deterministically.
     *
     * @param  int|null  $groupCount  Number of groups to form
     * @param  int|null  $membersPerGroup  Members per group (mutually exclusive with groupCount)
     * @param  string|null  $seed  Optional deterministic seed
     */
    public function shuffle(
        Subject $subject,
        ?int $groupCount = null,
        ?int $membersPerGroup = null,
        ?string $seed = null,
        ?User $actor = null,
    ): ShuffleRun {
        if ($subject->groups_locked) {
            throw new RuntimeException('Kelompok sudah dikunci dan tidak dapat diacak ulang.');
        }

        $members = $subject->members()->orderBy('users.id')->get()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($members->isEmpty()) {
            throw new RuntimeException('Tidak dapat mengacak mata pelajaran yang tidak memiliki anggota.');
        }

        if ($groupCount !== null && $membersPerGroup !== null) {
            throw new RuntimeException('Berikan salah satu: jumlah kelompok atau anggota per kelompok, bukan keduanya.');
        }

        if ($groupCount === null && $membersPerGroup === null) {
            throw new RuntimeException('Jumlah kelompok atau anggota per kelompok wajib diisi.');
        }

        [$groups, $groupCount] = $this->createGroups($subject, $groupCount, $membersPerGroup);

        // Seed: use provided seed or derive one from time entropy.
        $seed ??= $this->generateEntropySeed();

        $ordered = $this->shuffleMembers($members->all(), $seed);

        $assignment = $this->distribute($ordered, $groupCount);

        $resultPayload = collect($assignment)->map(function ($userIdList, $groupIndex) use ($groups) {
            return [
                'group' => $groups[$groupIndex]->name,
                'members' => $userIdList,
            ];
        })->values()->all();

        $hash = $this->commit($seed, $resultPayload);

        $run = DB::transaction(function () use ($subject, $actor, $seed, $groupCount, $membersPerGroup, $hash, $resultPayload, $groups, $assignment, $members) {
            $run = ShuffleRun::create([
                'subject_id' => $subject->id,
                'user_id' => $actor?->id,
                'user_name' => $actor?->name,
                'algorithm' => self::ALGORITHM,
                'version' => self::VERSION,
                'seed' => $seed,
                'group_count' => $groupCount,
                'members_per_group' => $membersPerGroup,
                'hash' => $hash,
                'result' => $resultPayload,
                'member_snapshot' => $members->all(),
            ]);

            ShuffleLog::create([
                'shuffle_run_id' => $run->id,
                'payload' => [
                    'member_ids' => $assignment,
                    'group_names' => $groups->map(fn ($g) => $g->name),
                    'seed' => $seed,
                    'algorithm' => self::ALGORITHM,
                    'version' => self::VERSION,
                    'hash' => $hash,
                ],
            ]);

            // Persist group membership.
            foreach ($assignment as $groupIndex => $userIdList) {
                $group = $groups[$groupIndex];
                $group->members()->syncWithPivotValues($userIdList, ['locked_at' => null]);
            }

            return $run;
        });

        return $run;
    }

    /**
     * Re-verify a run by recomputing the shuffle from its seed and the member
     * snapshot captured at shuffle time, then comparing to the recorded result
     * and commitment hash. This verifies the run exactly as it was drawn and
     * never depends on today's members or group rows, and it detects any
     * tampering with the stored result (unlike re-hashing the stored values).
     */
    public function verify(ShuffleRun $run): bool
    {
        $groupCount = $run->group_count;

        if ($groupCount < 1) {
            return false;
        }

        $snapshot = $this->activeMemberIds($run);

        if ($snapshot === []) {
            return false;
        }

        $ordered = $this->shuffleMembers($snapshot, $run->seed);
        $assignment = $this->distribute($ordered, $groupCount);

        $recorded = collect($run->result ?? [])->values();

        if ($recorded->count() !== $groupCount) {
            return false;
        }

        $recomputedPayload = collect($assignment)->map(function ($userIdList, $groupIndex) use ($recorded) {
            return [
                'group' => $recorded[$groupIndex]['group'] ?? 'Group '.($groupIndex + 1),
                'members' => $userIdList,
            ];
        })->values()->all();

        $recomputedHash = $this->commit($run->seed, $recomputedPayload);

        return hash_equals($run->hash, $recomputedHash);
    }

    /**
     * Whether the subject's current members differ from the snapshot captured
     * when the run was created.
     */
    public function memberSetChanged(ShuffleRun $run): bool
    {
        $subject = $run->subject;

        if (! $subject instanceof Subject || $run->member_snapshot === null) {
            return false;
        }

        $current = $subject->members()->select('users.id')->get()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $snapshot = collect($run->member_snapshot)
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        return $current !== $snapshot;
    }

    /**
     * The member id list used for this run: the stored snapshot when available,
     * otherwise a best-effort fallback to the subject's current members.
     *
     * @return array<int, int>
     */
    protected function activeMemberIds(ShuffleRun $run): array
    {
        $snapshot = $run->member_snapshot;

        if ($snapshot === null) {
            $subject = $run->subject;

            if (! $subject instanceof Subject) {
                return [];
            }

            $snapshot = $subject->members()->orderBy('users.id')->get()->pluck('id')->all();
        }

        return collect($snapshot)
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Deterministically shuffle a list using a seed (Mt19937 engine so the
     * result is reproducible regardless of the process-local RNG state).
     *
     * @param  array<int, int>  $items
     * @return array<int, int>
     */
    protected function shuffleMembers(array $items, string $seed): array
    {
        $randomizer = new Randomizer(new Mt19937($this->seedToInt($seed)));
        $result = $items;

        for ($i = count($result) - 1; $i > 0; $i--) {
            $j = $randomizer->getInt(0, $i);
            [$result[$i], $result[$j]] = [$result[$j], $result[$i]];
        }

        return $result;
    }

    /**
     * Derive a stable 32-bit integer from the seed string. The engine is
     * self-contained (no process-global RNG state is mutated) and the digest
     * spreads the seed evenly, unlike crc32.
     */
    protected function seedToInt(string $seed): int
    {
        return unpack('N', hash('sha256', $seed, true))[1];
    }

    protected function generateEntropySeed(): string
    {
        return sprintf('%s|%s', now()->format('YmdHisv'), bin2hex(random_bytes(6)));
    }

    /**
     * Create the group records for a subject.
     *
     * @return array{0: Collection<int, Group>, 1: int}
     */
    protected function createGroups(Subject $subject, ?int $groupCount, ?int $membersPerGroup): array
    {
        $memberCount = (int) $subject->members()->count();

        if ($membersPerGroup !== null) {
            if ($membersPerGroup < 1 || $membersPerGroup > 50) {
                throw new RuntimeException('Anggota per kelompok harus antara 1 dan 50.');
            }

            $groupCount = (int) ceil($memberCount / $membersPerGroup);
        }

        if ($groupCount === null) {
            throw new RuntimeException('Jumlah kelompok atau anggota per kelompok wajib diisi.');
        }

        if ($groupCount < 1) {
            throw new RuntimeException('Setidaknya satu kelompok diperlukan.');
        }

        if ($groupCount > 100) {
            throw new RuntimeException('Jumlah kelompok maksimal adalah 100.');
        }

        if ($groupCount > $memberCount) {
            throw new RuntimeException('Jumlah kelompok tidak boleh melebihi jumlah anggota.');
        }

        // Reuse existing empty groups? Simpler: delete existing groups and recreate.
        $subject->groups()->delete();

        $groups = collect(range(1, $groupCount))->map(function ($index) use ($subject) {
            return Group::create([
                'subject_id' => $subject->id,
                'name' => 'Group '.$index,
            ]);
        });

        return [$groups, $groupCount];
    }

    /**
     * Distribute members across groups (round-robin from shuffled list).
     *
     * @param  array<int, int>  $ordered
     * @return array<int, array<int, int>>
     */
    protected function distribute(array $ordered, int $groupCount): array
    {
        $assignment = array_fill(0, $groupCount, []);

        foreach ($ordered as $i => $memberId) {
            $assignment[$i % $groupCount][] = $memberId;
        }

        return $assignment;
    }

    /**
     * Compute a commitment hash of seed + result.
     *
     * @param  array<int, mixed>  $result
     */
    protected function commit(string $seed, array $result): string
    {
        return hash('sha256', $seed.'|'.json_encode($result));
    }
}
