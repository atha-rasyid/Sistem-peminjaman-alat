<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    #[DataProvider('roleDashboardRoutes')]
    public function test_dashboard_redirects_users_to_their_role_dashboard(string $role, string $route): void
    {
        $this->actingAs(new User(['role' => $role]))
            ->get(route('dashboard'))
            ->assertRedirect(route($route));
    }

    public function test_successful_login_redirects_to_the_users_role_dashboard(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->timestamps();
        });

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_welcome_page_always_shows_login_button(): void
    {
        $this->actingAs(new User(['role' => 'admin']))
            ->get('/')
            ->assertSee('href="' . route('login') . '"', false)
            ->assertSeeText('Login')
            ->assertDontSeeText('Dashboard');
    }

    public function test_authenticated_user_can_open_login_form(): void
    {
        $this->actingAs(new User(['role' => 'admin']))
            ->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public static function roleDashboardRoutes(): array
    {
        return [
            'admin' => ['admin', 'admin.dashboard'],
            'petugas' => ['petugas', 'petugas.peminjaman.index'],
            'peminjam' => ['peminjam', 'peminjam.dashboard'],
        ];
    }
}
