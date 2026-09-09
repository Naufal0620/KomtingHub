<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationPerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_page_selector_appears_and_persists(): void
    {
        $admin = User::factory()->admin()->create();
        ClassRoom::factory()->count(15)->create(['komting_id' => $admin->id]);

        $this->actingAs($admin)
            ->get(route('class-rooms.index'))
            ->assertOk()
            ->assertSee('name="per_page"', false);

        $this->actingAs($admin)
            ->get(route('class-rooms.index', ['per_page' => 5]))
            ->assertOk()
            ->assertSee('value="5" selected', false);

        $this->actingAs($admin)
            ->get(route('class-rooms.index', ['per_page' => 'all']))
            ->assertOk()
            ->assertSee('value="all" selected', false);
    }
}