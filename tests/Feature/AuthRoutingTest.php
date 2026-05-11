<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_served_from_login_php(): void
    {
        $response = $this->get('/login.php');

        $response->assertOk();
        $response->assertSee('Login');
    }

    public function test_login_route_redirects_to_login_php(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect('/login.php');
    }

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login.php', [
            'email' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }
}
