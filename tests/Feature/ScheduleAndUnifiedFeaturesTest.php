<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\Schedule;
use App\Models\LegalCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ScheduleAndUnifiedFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $lawyer;
    protected User $client;
    protected User $otherClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lawyer = User::firstOrCreate(
            ['email' => 'advokat_test@sahabathukum.test'],
            ['name' => 'Advokat Penguji', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->client = User::firstOrCreate(
            ['email' => 'klien_test@sahabathukum.test'],
            ['name' => 'Klien Penguji', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );

        $this->otherClient = User::firstOrCreate(
            ['email' => 'klien_lain@sahabathukum.test'],
            ['name' => 'Klien Lain', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );
    }

    /**
     * 1. Test Advokat Schedule Endpoints (Bulanan, Mingguan, Agenda, JSON AJAX)
     */
    public function test_advokat_schedule_views_and_json(): void
    {
        // Create sample schedules
        $schedule = Schedule::create([
            'lawyer_id'   => $this->lawyer->id,
            'client_id'   => $this->client->id,
            'title'       => 'Sidang Pertama Pengujian',
            'description' => 'Uji coba persidangan',
            'start_at'    => Carbon::now('Asia/Jakarta')->addDays(1)->setHour(9)->setMinute(0),
            'location'    => 'Pengadilan Negeri',
            'status'      => 'Aktif',
        ]);

        // HTML Bulanan
        $res = $this->actingAs($this->lawyer)->get('/advokat/jadwal?view=bulanan');
        $res->assertStatus(200);
        $res->assertSee('Jadwal');

        // HTML Mingguan
        $resWeekly = $this->actingAs($this->lawyer)->get('/advokat/jadwal?view=mingguan');
        $resWeekly->assertStatus(200);

        // HTML Agenda
        $resAgenda = $this->actingAs($this->lawyer)->get('/advokat/jadwal?view=agenda');
        $resAgenda->assertStatus(200);

        // AJAX JSON
        $resJson = $this->actingAs($this->lawyer)->getJson('/advokat/jadwal?view=bulanan');
        $resJson->assertStatus(200);
        $resJson->assertJsonFragment(['success' => true]);
        $resJson->assertJsonFragment(['view' => 'bulanan']);
    }

    /**
     * 2. Test Klien Schedule Endpoints and IDOR Isolation
     */
    public function test_client_schedule_views_and_isolation(): void
    {
        // Schedule for this client
        $mySchedule = Schedule::create([
            'lawyer_id'   => $this->lawyer->id,
            'client_id'   => $this->client->id,
            'title'       => 'Konsultasi Privat Klien Penguji',
            'description' => 'Rahasia klien',
            'start_at'    => Carbon::now('Asia/Jakarta')->addDays(3)->setHour(14)->setMinute(0),
            'location'    => 'Kantor',
            'status'      => 'Aktif',
        ]);

        // Schedule for other client
        $otherSchedule = Schedule::create([
            'lawyer_id'   => $this->lawyer->id,
            'client_id'   => $this->otherClient->id,
            'title'       => 'Agenda Klien Lain Yang Sangat Rahasia',
            'description' => 'Rahasia orang lain',
            'start_at'    => Carbon::now('Asia/Jakarta')->addDays(3)->setHour(16)->setMinute(0),
            'location'    => 'Kantor',
            'status'      => 'Aktif',
        ]);

        // As this client
        $res = $this->actingAs($this->client)->get('/klien/jadwal');
        $res->assertStatus(200);
        $res->assertSee('Konsultasi Privat Klien Penguji');
        $res->assertDontSee('Agenda Klien Lain Yang Sangat Rahasia');

        // AJAX JSON check
        $resJson = $this->actingAs($this->client)->getJson('/klien/jadwal');
        $resJson->assertStatus(200);
        $resJson->assertJsonFragment(['success' => true]);
        $resJson->assertJsonFragment(['view' => 'bulanan']);
    }

    /**
     * 3. Test Consultation Scheduling to Schedule Sync
     */
    public function test_consultation_scheduling_creates_schedule(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyer->id,
            'problem_type' => 'Hukum Bisnis',
            'title'        => 'Konsultasi Perjanjian Usaha',
            'description'  => 'Review kontrak kerjasama',
            'status'       => 'Menunggu',
        ]);

        $scheduledTime = Carbon::now('Asia/Jakarta')->addDays(5)->format('Y-m-d H:i');

        $res = $this->actingAs($this->lawyer)->post("/advokat/konsultasi/{$consultation->id}/jadwalkan", [
            'scheduled_at' => $scheduledTime,
            'location'     => 'Ruang Rapat Sahabat Hukum',
        ]);

        $res->assertRedirect();

        $consultation->refresh();
        $this->assertEquals('Dijadwalkan', $consultation->status);

        $this->assertDatabaseHas('schedules', [
            'consultation_id' => $consultation->id,
            'lawyer_id'       => $this->lawyer->id,
            'client_id'       => $this->client->id,
            'status'          => 'Aktif',
        ]);
    }

    /**
     * 4. Test Legal Assistant Chat Endpoint for Klien and Advokat
     */
    public function test_legal_assistant_endpoints(): void
    {
        // Index page
        $resClient = $this->actingAs($this->client)->get('/klien/asisten-hukum');
        $resClient->assertStatus(200);

        $resAdvokat = $this->actingAs($this->lawyer)->get('/advokat/asisten-hukum');
        $resAdvokat->assertStatus(200);

        // Chat endpoint (Casual query)
        $resChat = $this->actingAs($this->client)->postJson('/klien/asisten-hukum/chat', [
            'message' => 'Halo selamat siang, terima kasih bantuannya',
        ]);

        $resChat->assertStatus(200);
        $resChat->assertJsonStructure([
            'success',
            'answer',
            'sources',
            'time',
        ]);

        // Clear history endpoint
        $resClear = $this->actingAs($this->client)->postJson('/klien/asisten-hukum/clear');
        $resClear->assertStatus(200);
        $resClear->assertJsonFragment(['success' => true]);
    }
}
