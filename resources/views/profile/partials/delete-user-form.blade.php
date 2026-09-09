<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-red-600">Hapus Akun</h2>
        <p class="mt-1 text-sm text-gray-500">Setelah akun kamu dihapus, seluruh sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun, unduh atau simpan data yang kamu butuhkan.</p>
    </header>

    <x-ui.button type="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        Hapus Akun
    </x-ui.button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900">Kamu yakin ingin menghapus akun ini?</h2>

            <div class="mt-1 space-y-2 text-sm text-gray-500">
                <p>Akun kamu beserta seluruh data berikut akan dihapus secara permanen dan tidak dapat dikembalikan:</p>
                <ul class="list-disc space-y-1 pl-5">
                    <li>Keanggotaan ruang kelas, mata kuliah, dan kelompok</li>
                    <li>Daftar tugas, pengumpulan tugas, dan nilai</li>
                    <li>Riwayat pembagian kelompok dan notifikasi</li>
                </ul>
                <p>Unduh atau simpan data yang kamu butuhkan terlebih dahulu.</p>
            </div>

            <div class="mt-6">
                <label for="password" class="label">Kata Sandi</label>
                <input id="password" name="password" type="password" class="input" placeholder="Kata Sandi" autocomplete="current-password" />
                @error('password', 'userDeletion')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-ui.button type="outline" x-on:click="$dispatch('close')" class="w-full sm:w-auto">Batal</x-ui.button>
                <x-ui.button :submit="true" type="danger" class="w-full sm:w-auto">Hapus Akun</x-ui.button>
            </div>
        </form>
    </x-modal>
</section>
