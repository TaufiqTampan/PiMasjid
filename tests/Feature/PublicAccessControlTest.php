<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest can view Beranda (home page) without login.
     */
    public function test_guest_can_access_beranda(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test guest can access TV display screen.
     */
    public function test_guest_can_access_display(): void
    {
        $response = $this->get('/display');

        $response->assertStatus(200);
    }

    /**
     * Test guest is redirected to login when trying to access public feature pages.
     */
    public function test_guest_is_redirected_to_login_for_public_features(): void
    {
        $protectedRoutes = [
            '/profil/struktur',
            '/profil/tentang',
            '/transparansi/keuangan',
            '/transparansi/aset',
            '/ibadah/jumat',
            '/ibadah/jadwal',
            '/ibadah/agenda',
            '/ibadah/kiblat',
            '/galeri',
            '/berita',
            '/tarbiyah',
            '/quran',
            '/ramadhan',
            '/info/zakat',
            '/info/qurban',
            '/keuangan',
            '/lumbung-pangan',
            '/fasilitas',
            '/fasilitas/cek-status',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login', "Route {$route} should redirect guest to /login");
        }
    }

    /**
     * Test logged-in User/Jamaah can access public feature pages.
     */
    public function test_logged_in_jamaah_can_access_public_features(): void
    {
        $jamaah = User::factory()->create(['role' => 'user']);

        $protectedRoutes = [
            '/',
            '/profil/struktur',
            '/profil/tentang',
            '/transparansi/keuangan',
            '/transparansi/aset',
            '/fasilitas',
            '/lumbung-pangan',
            '/info/zakat',
            '/info/qurban',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->actingAs($jamaah)->get($route);
            $response->assertStatus(200, "Jamaah should be able to access {$route}");
        }
    }

    /**
     * Test Jamaah is redirected away from /dashboard to home.
     */
    public function test_jamaah_cannot_access_admin_dashboard(): void
    {
        $jamaah = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($jamaah)->get('/dashboard');

        $response->assertRedirect('/');
    }

    /**
     * Test Admin role can access both public features and /dashboard.
     */
    public function test_admin_can_access_dashboard_and_public_features(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);

        $responsePublic = $this->actingAs($admin)->get('/fasilitas');
        $responsePublic->assertStatus(200);
    }
}
