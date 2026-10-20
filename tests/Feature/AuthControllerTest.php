<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('dashboardRoutes')]
    public function test_successful_login_redirects_to_role_dashboard_with_toast(string $role, string $destination): void
    {
        $user = User::factory()->create([
            'role' => $role,
            'is_active' => true,
            'password' => 'password123',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $message = "Login berhasil. Selamat datang kembali, {$user->name}!";
        $response->assertRedirect(route($destination))
            ->assertSessionHas('success', $message);

        $this->get(route($destination))
            ->assertOk()
            ->assertSee($message);
    }

    public static function dashboardRoutes(): array
    {
        return [
            'customer home' => ['customer', 'home'],
            'cashier dashboard' => ['cashier', 'cashier.index'],
            'owner dashboard' => ['owner', 'admin.dashboard'],
        ];
    }

    public function test_registration_redirects_customer_to_home_with_success_toast(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan@example.test',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $message = 'Selamat datang di Mobeli! Pendaftaran akun Anda berhasil.';
        $response->assertRedirect(route('home'))
            ->assertSessionHas('success', $message);

        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan@example.test',
            'role' => 'customer',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($message);
    }

    public function test_customer_login_redirects_to_home_even_if_catalog_was_the_intended_url(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
            'password' => 'password123',
        ]);

        $response = $this->withSession(['url.intended' => route('catalog')])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('home'));
    }
}
