<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    /**
     * Test public landing page is accessible at GET / and returns status 200.
     */
    public function test_landing_page_can_be_accessed_publicly(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Sahabat Hukum');
    }

    /**
     * Test Navbar elements and absence of public Asisten Hukum menu.
     */
    public function test_navbar_structure_and_no_public_assistant_menu(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Menu items
        $response->assertSee('Beranda');
        $response->assertSee('Tentang Kami');
        $response->assertSee('Layanan');
        $response->assertSee('Tim Advokat');
        $response->assertSee('Struktur Organisasi');
        $response->assertSee('FAQ');
        $response->assertSee('Kontak');
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');

        // Navbar must NOT have Asisten Hukum in the navbar menu
        // We ensure Asisten Hukum is not in navbar menu items
        $content = $response->getContent();
        $this->assertStringNotContainsString('class="nav-link">Asisten Hukum<', $content);
        $this->assertStringNotContainsString('class="mobile-nav-link">Asisten Hukum<', $content);
    }

    /**
     * Test Hero section content, headline, subheadline, and buttons.
     */
    public function test_hero_section_content_and_cta(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('Pendampingan Hukum yang Profesional, Terpercaya, dan Terstruktur');
        $response->assertSeeText('Sahabat Hukum membantu klien memperoleh layanan konsultasi, pendampingan perkara, pengelolaan dokumen, serta komunikasi dengan advokat melalui satu platform yang terintegrasi.');
        $response->assertSee('Ajukan Konsultasi');
        $response->assertSee('Pelajari Layanan');
    }

    /**
     * Test Tentang Kami and Core Values sections.
     */
    public function test_about_and_values_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Tentang Kami points
        $response->assertSee('Layanan Profesional');
        $response->assertSee('Pendampingan Terstruktur');
        $response->assertSee('Pengelolaan Dokumen');
        $response->assertSee('Komunikasi Terintegrasi');

        // Nilai / Keunggulan
        $response->assertSee('Profesional');
        $response->assertSee('Terpercaya');
        $response->assertSee('Transparan');
        $response->assertSee('Terstruktur');
        $response->assertSee('Aman');
    }

    /**
     * Test all 6 legal service cards.
     */
    public function test_all_six_legal_services(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Konsultasi Hukum');
        $response->assertSee('Pendampingan Perkara');
        $response->assertSee('Pengelolaan Dokumen');
        $response->assertSee('Monitoring Perkara');
        $response->assertSee('Penjadwalan Konsultasi');
        $response->assertSee('Komunikasi dengan Advokat');
    }

    /**
     * Test Tim Advokat renders database lawyers.
     */
    public function test_advocates_section_displays_data(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Tim Advokat');
        // Check for at least one known advocate in DB if available
        $advocate = User::where('role', 'advokat')->where('status', 'aktif')->first();
        if ($advocate) {
            $response->assertSee($advocate->name);
        }
    }

    /**
     * Test Struktur Organisasi hierarchy.
     */
    public function test_organizational_structure_section(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Struktur Organisasi');
        $response->assertSee('Pimpinan');
        $response->assertSee('Advokat');
        $response->assertSee('Tim Pendukung / Administrasi');
    }

    /**
     * Test Workflow / Cara Kerja steps.
     */
    public function test_workflow_steps(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bagaimana Sahabat Hukum Bekerja?');
        $response->assertSee('Daftar / Masuk');
        $response->assertSee('Ajukan Konsultasi');
        $response->assertSee('Konsultasi dengan Advokat');
        $response->assertSee('Penanganan Perkara');
        $response->assertSee('Pantau Perkembangan');
        $response->assertSee('Kelola Dokumen &amp; Komunikasi', false);
    }

    /**
     * Test Platform Sahabat Hukum section and login CTA.
     */
    public function test_platform_sahabat_hukum_section(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Platform Sahabat Hukum');
        $response->assertSee('Masuk ke Platform');
        $response->assertSee(route('login'));
    }

    /**
     * Test Asisten Hukum section and notice, and ensure no public chatbot.
     */
    public function test_assistant_section_is_internal_notice_only(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Asisten Hukum');
        $response->assertSee('Bantuan informasi hukum berbasis AI untuk membantu pengguna memahami informasi dan sumber hukum yang relevan.', false);
        $response->assertSee('Mencari Informasi Hukum');
        $response->assertSee('Membantu Memahami Konteks');
        $response->assertSee('Menjelaskan Sumber Tersedia');
        $response->assertSee('Menampilkan Referensi');
        $response->assertSee('Asisten Hukum tersedia setelah pengguna masuk ke platform Sahabat Hukum.', false);
        $response->assertSee('Masuk untuk Menggunakan Asisten Hukum');

        // Ensure internal assistant routes require authentication
        $guestKlienAssistant = $this->get('/klien/asisten-hukum');
        $guestKlienAssistant->assertRedirect('/login');

        $guestAdvokatAssistant = $this->get('/advokat/asisten-hukum');
        $guestAdvokatAssistant->assertRedirect('/login');
    }

    /**
     * Test FAQ section and specific answers.
     */
    public function test_faq_section_content(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Apa itu Sahabat Hukum?');
        $response->assertSee('Bagaimana cara mengajukan konsultasi?');
        $response->assertSee('Apakah saya harus memiliki akun?');
        $response->assertSee('Bagaimana proses penanganan perkara?');
        $response->assertSee('Apakah dokumen saya aman?');
        $response->assertSee('Bagaimana saya dapat berkomunikasi dengan advokat?');
        $response->assertSee('Apakah Asisten Hukum dapat digunakan tanpa login?');
        $response->assertSee('Asisten Hukum merupakan fitur internal platform dan hanya dapat digunakan setelah pengguna masuk ke akun Sahabat Hukum.', false);
    }

    /**
     * Test Call to Action and Footer.
     */
    public function test_cta_and_footer(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Siap Mendapatkan Pendampingan Hukum?');
        $response->assertSee('Mulai konsultasi dan kelola proses hukum Anda melalui Sahabat Hukum.', false);
        $response->assertSee('Daftar Sekarang');
        $response->assertSee('kontak@sahabathukum.com');
        $response->assertSee('Kebijakan Privasi');
        $response->assertSee('Syarat &amp; Ketentuan', false);
    }

    /**
     * Test authenticated user accessing landing page shows their role dashboard.
     */
    public function test_authenticated_user_sees_dashboard_link(): void
    {
        $user = User::where('role', 'klien')->first();
        if ($user) {
            $response = $this->actingAs($user)->get('/');
            $response->assertStatus(200);
            $response->assertSee('Dashboard Klien');
            $response->assertSee(route('klien.dashboard'));
        }
    }

    /**
     * Test that login and register pages continue to function normally.
     */
    public function test_auth_routes_still_work(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);

        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
    }
}
