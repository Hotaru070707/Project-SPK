<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;
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

    public function test_authenticated_user_can_logout_and_receives_success_toast(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Anda berhasil keluar dari akun.')
            ->assertSessionHas('status', 'Anda berhasil keluar dari akun.');

        $this->assertGuest();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Anda berhasil keluar dari akun.');
    }

    public function test_user_can_redirect_to_google_oauth(): void
    {
        $response = $this->get(route('auth.google'));

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', (string) $response->headers->get('Location'));
    }

    public function test_new_user_can_authenticate_via_google_oauth(): void
    {
        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $abstractUser->shouldReceive('getName')->andReturn('Google User');
        $abstractUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = \Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'google_id' => 'google-unique-id-12345',
            'role' => 'customer',
        ]);
    }

    public function test_google_oauth_falls_back_to_stateless_on_invalid_state(): void
    {
        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-67890');
        $abstractUser->shouldReceive('getName')->andReturn('Stateless User');
        $abstractUser->shouldReceive('getEmail')->andReturn('stateless@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = \Mockery::mock(GoogleProvider::class);
        $attempts = 0;
        $provider->shouldReceive('user')->andReturnUsing(function () use (&$attempts, $abstractUser) {
            $attempts++;
            if ($attempts === 1) {
                throw new InvalidStateException;
            }

            return $abstractUser;
        });
        $provider->shouldReceive('stateless')->once()->andReturnSelf();

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'stateless@example.com',
            'google_id' => 'google-unique-id-67890',
        ]);
    }

    public function test_google_oauth_handles_failure_gracefully(): void
    {
        $provider = \Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andThrow(new \Exception('Connection refused by Google'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
