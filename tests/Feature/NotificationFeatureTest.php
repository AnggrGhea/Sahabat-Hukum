<?php

namespace Tests\Feature;

use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\User;
use App\Notifications\ConsultationSubmittedClientNotification;
use App\Notifications\NewConsultationAdminNotification;
use App\Notifications\NewClientRegisteredNotification;
use App\Notifications\NewCaseAdminNotification;
use App\Notifications\CaseCreatedClientNotification;
use App\Notifications\ScheduleUpdatedNotification;
use App\Notifications\CaseProgressNotification;
use App\Notifications\CaseProgressAdminNotification;
use App\Notifications\DocumentActionRequiredAdminNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $lawyer;
    private User $client1;
    private User $client2;

    protected function setUp(): void
    {
        parent::setUp();

        $prefix = Str::random(8);

        $this->admin = User::create([
            'name'     => 'Admin Sistem ' . $prefix,
            'email'    => 'admin_' . $prefix . '@sahabathukum.id',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'status'   => 'aktif',
        ]);

        $this->lawyer = User::create([
            'name'     => 'Advokat Wijaya ' . $prefix,
            'email'    => 'wijaya_' . $prefix . '@sahabathukum.id',
            'password' => Hash::make('password123'),
            'role'     => 'advokat',
            'status'   => 'aktif',
        ]);

        $this->client1 = User::create([
            'name'     => 'Klien Budi ' . $prefix,
            'email'    => 'budi_' . $prefix . '@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);

        $this->client2 = User::create([
            'name'     => 'Klien Siti ' . $prefix,
            'email'    => 'siti_' . $prefix . '@gmail.com',
            'password' => Hash::make('password123'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);
    }

    public function test_client_can_view_notifications_page_with_empty_state(): void
    {
        $response = $this->actingAs($this->client1)->get(route('klien.notifications'));

        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan');
        $response->assertSee('Belum Ada Pemberitahuan');
    }

    public function test_client_sees_own_notifications_and_cannot_see_other_clients(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'title'        => 'Konsultasi Waris Tanah',
            'problem_type' => 'Perdata',
            'description'  => 'Sengketa tanah warisan',
            'status'       => 'Menunggu',
        ]);

        $this->client1->notify(new ConsultationSubmittedClientNotification($consultation));

        // Client 1 sees it
        $res1 = $this->actingAs($this->client1)->get(route('klien.notifications'));
        $res1->assertStatus(200);
        $res1->assertSee('Konsultasi Berhasil Diajukan');
        $res1->assertSee('Konsultasi Waris Tanah');

        // Client 2 does NOT see it
        $res2 = $this->actingAs($this->client2)->get(route('klien.notifications'));
        $res2->assertStatus(200);
        $res2->assertDontSee('Konsultasi Waris Tanah');
        $res2->assertSee('Belum Ada Pemberitahuan');
    }

    public function test_client_open_notification_marks_as_read_and_redirects(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'title'        => 'Konsultasi Bisnis',
            'problem_type' => 'Bisnis',
            'description'  => 'Sengketa kontrak',
            'status'       => 'Menunggu',
        ]);

        $this->client1->notify(new ConsultationSubmittedClientNotification($consultation));
        $notification = $this->client1->notifications()->first();

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($this->client1)
            ->get(route('klien.notifications.open', $notification->id));

        $response->assertRedirect(route('klien.consultations.show', $consultation->id));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_client_cannot_access_or_open_another_users_notification(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'title'        => 'Rahasia Klien 1',
            'problem_type' => 'Pidana',
            'description'  => 'Deskripsi',
            'status'       => 'Menunggu',
        ]);

        $this->client1->notify(new ConsultationSubmittedClientNotification($consultation));
        $notification = $this->client1->notifications()->first();

        // Client 2 tries to open Client 1's notification -> 404 IDOR protected
        $response = $this->actingAs($this->client2)
            ->get(route('klien.notifications.open', $notification->id));

        $response->assertStatus(404);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_client_mark_all_notifications_as_read(): void
    {
        $c1 = Consultation::create([
            'client_id'    => $this->client1->id,
            'title'        => 'Konsultasi 1',
            'problem_type' => 'Perdata',
            'description'  => 'Deskripsi 1',
            'status'       => 'Menunggu',
        ]);
        $c2 = Consultation::create([
            'client_id'    => $this->client1->id,
            'title'        => 'Konsultasi 2',
            'problem_type' => 'Perdata',
            'description'  => 'Deskripsi 2',
            'status'       => 'Menunggu',
        ]);

        $this->client1->notify(new ConsultationSubmittedClientNotification($c1));
        $this->client1->notify(new ConsultationSubmittedClientNotification($c2));

        $this->assertEquals(2, $this->client1->unreadNotifications()->count());

        $response = $this->actingAs($this->client1)
            ->post(route('klien.notifications.mark-all-read'));

        $response->assertSessionHas('success');
        $this->assertEquals(0, $this->client1->fresh()->unreadNotifications()->count());
    }

    public function test_admin_can_view_notifications_page_and_mark_all_read(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'title'        => 'Konsultasi Baru untuk Admin',
            'problem_type' => 'Perdata',
            'description'  => 'Deskripsi',
            'status'       => 'Menunggu',
        ]);

        $this->admin->notify(new NewConsultationAdminNotification($consultation));

        $response = $this->actingAs($this->admin)->get(route('admin.notifications'));
        $response->assertStatus(200);
        $response->assertSee('Pemberitahuan Aktivitas Sistem');
        $response->assertSee('Konsultasi Baru Masuk');
        $response->assertSee('Konsultasi Baru untuk Admin');

        $this->assertEquals(1, $this->admin->unreadNotifications()->count());

        // Mark all as read
        $markResponse = $this->actingAs($this->admin)->post(route('admin.notifications.mark-all-read'));
        $markResponse->assertSessionHas('success');
        $this->assertEquals(0, $this->admin->fresh()->unreadNotifications()->count());
    }

    public function test_consultation_submission_triggers_notifications_for_client_and_admin(): void
    {
        $response = $this->actingAs($this->client1)->post(route('klien.consultations.store'), [
            'problem_type' => 'Hukum Keluarga',
            'title'        => 'Gugatan Hak Asuh',
            'description'  => 'Permohonan pendampingan gugatan hak asuh anak.',
        ]);

        $response->assertRedirect(route('klien.consultations'));

        // Check client notification
        $this->assertEquals(1, $this->client1->notifications()->count());
        $clientNotif = $this->client1->notifications()->first();
        $this->assertEquals('consultation_submitted', $clientNotif->data['type']);
        $this->assertStringContainsString('Gugatan Hak Asuh', $clientNotif->data['message']);

        // Check admin notification
        $this->assertEquals(1, $this->admin->notifications()->count());
        $adminNotif = $this->admin->notifications()->first();
        $this->assertEquals('new_consultation_admin', $adminNotif->data['type']);
        $this->assertStringContainsString('Gugatan Hak Asuh', $adminNotif->data['message']);
    }

    public function test_new_client_registration_triggers_notification_for_admin(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Rina Melati',
            'email'                 => 'rina@example.com',
            'phone'                 => '081234567890',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));

        // Admin received registration notification
        $adminNotif = $this->admin->notifications()->where('type', NewClientRegisteredNotification::class)->first();
        $this->assertNotNull($adminNotif);
        $this->assertEquals('new_client_registered', $adminNotif->data['type']);
        $this->assertStringContainsString('Rina Melati', $adminNotif->data['message']);
    }

    public function test_case_creation_and_progress_triggers_notifications(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'lawyer_id'    => $this->lawyer->id,
            'title'        => 'Konsultasi Perdata Sengketa Ruko',
            'problem_type' => 'Perdata',
            'description'  => 'Sengketa kepemilikan ruko',
            'status'       => 'Selesai',
        ]);

        // Advocate creates a case
        $caseResponse = $this->actingAs($this->lawyer)->post(route('advokat.cases.store'), [
            'consultation_id' => $consultation->id,
            'case_number'     => 'PERK-2026-001',
            'case_type'       => 'Perdata',
            'title'           => 'Perkara Sengketa Ruko Komersial',
            'summary'         => 'Ringkasan sengketa ruko komersial',
            'started_at'      => now()->toDateString(),
        ]);

        $case = LegalCase::where('case_number', 'PERK-2026-001')->first();
        $this->assertNotNull($case);

        // Check Client got CaseCreatedClientNotification
        $clientCaseNotif = $this->client1->notifications()->where('type', CaseCreatedClientNotification::class)->first();
        $this->assertNotNull($clientCaseNotif);
        $this->assertEquals('case_created_client', $clientCaseNotif->data['type']);
        $this->assertStringContainsString('PERK-2026-001', $clientCaseNotif->data['message']);

        // Check Admin got NewCaseAdminNotification
        $adminCaseNotif = $this->admin->notifications()->where('type', NewCaseAdminNotification::class)->first();
        $this->assertNotNull($adminCaseNotif);
        $this->assertEquals('new_case_admin', $adminCaseNotif->data['type']);
        $this->assertStringContainsString('PERK-2026-001', $adminCaseNotif->data['message']);

        // Advocate adds progress to the case
        $progressResponse = $this->actingAs($this->lawyer)->post(route('advokat.cases.progress', $case->id), [
            'title'         => 'Sidang Pertama Pembacaan Gugatan',
            'description'   => 'Sidang pertama telah berlangsung lancar di PN Jakarta.',
            'progress_date' => now()->toDateString(),
        ]);

        $progressResponse->assertRedirect(route('advokat.cases.show', $case->id));

        // Client receives CaseProgressNotification
        $clientProgNotif = $this->client1->notifications()->where('type', CaseProgressNotification::class)->first();
        $this->assertNotNull($clientProgNotif);
        $this->assertStringContainsString('Sidang Pertama Pembacaan Gugatan', $clientProgNotif->data['message']);

        // Admin receives CaseProgressAdminNotification
        $adminProgNotif = $this->admin->notifications()->where('type', CaseProgressAdminNotification::class)->first();
        $this->assertNotNull($adminProgNotif);
        $this->assertStringContainsString('Sidang Pertama Pembacaan Gugatan', $adminProgNotif->data['message']);
    }

    public function test_scheduling_consultation_triggers_notification_for_client(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'lawyer_id'    => $this->lawyer->id,
            'title'        => 'Konsultasi Hukum Ketenagakerjaan',
            'problem_type' => 'Ketenagakerjaan',
            'description'  => 'PHK sepihak perusahaan',
            'status'       => 'Menunggu',
        ]);

        $response = $this->actingAs($this->lawyer)->post(route('advokat.consultations.schedule', $consultation->id), [
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'location'     => 'Ruang Mediasi Lt. 2',
        ]);

        $response->assertRedirect(route('advokat.consultations.show', $consultation->id));

        $clientNotif = $this->client1->notifications()->where('type', ScheduleUpdatedNotification::class)->first();
        $this->assertNotNull($clientNotif);
        $this->assertEquals('schedule_updated', $clientNotif->data['type']);
        $this->assertStringContainsString('Ruang Mediasi Lt. 2', $clientNotif->data['message']);
    }

    public function test_document_upload_by_client_triggers_admin_notification(): void
    {
        Storage::fake('private');

        $case = LegalCase::create([
            'client_id'   => $this->client1->id,
            'lawyer_id'   => $this->lawyer->id,
            'case_number' => 'DOC-TEST-001',
            'case_type'   => 'Perdata',
            'title'       => 'Uji Kasus Dokumen',
            'status'      => 'Persiapan',
            'started_at'  => now()->toDateString(),
        ]);

        $dummyFile = UploadedFile::fake()->create('bukti_kontrak.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->client1)->post(route('klien.cases.document.upload', $case->id), [
            'name'          => 'Salinan Kontrak Kerja Sama',
            'file'          => $dummyFile,
            'document_type' => 'Alat Bukti',
            'description'   => 'Salinan kontrak kerja sama',
        ]);

        $response->assertRedirect(route('klien.cases.show', $case->id));

        // Admin received document notification
        $adminDocNotif = $this->admin->notifications()->where('type', DocumentActionRequiredAdminNotification::class)->first();
        $this->assertNotNull($adminDocNotif);
        $this->assertEquals('document_action_admin', $adminDocNotif->data['type']);
        $this->assertStringContainsString('Salinan Kontrak Kerja Sama', $adminDocNotif->data['message']);
    }
}
