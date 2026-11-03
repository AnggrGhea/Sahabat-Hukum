<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminLawyerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_lawyers_page_and_see_lawyers()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lawyer = User::factory()->create([
            'name' => 'Bambang Sutrisno, S.H.',
            'role' => 'advokat',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lawyers'));
        $response->assertStatus(200);
        $response->assertSee('Bambang Sutrisno, S.H.');
    }

    public function test_admin_can_store_new_lawyer()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name'           => 'Dedi Kusnadi, S.H.',
            'email'          => 'dedi.law@test.com',
            'password'       => 'password123',
            'specialization' => 'Hukum Pidana',
            'phone'          => '081298765432',
        ];

        $response = $this->actingAs($admin)->post(route('admin.lawyers.store'), $payload);
        $response->assertRedirect(route('admin.lawyers'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'dedi.law@test.com',
            'role'  => 'advokat',
        ]);

        $this->assertDatabaseHas('lawyer_profiles', [
            'specialization' => 'Hukum Pidana',
            'phone'          => '081298765432',
        ]);
    }
}
