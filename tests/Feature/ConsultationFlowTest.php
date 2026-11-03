<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConsultationFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $lawyerSupri;
    protected User $lawyerHana;
    protected User $clientRiko;
    protected User $otherClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@sahabathukum.test'],
            ['name' => 'Administrator', 'password' => bcrypt('password'), 'role' => 'admin', 'status' => 'aktif']
        );

        $this->lawyerSupri = User::firstOrCreate(
            ['email' => 'advokat@sahabathukum.test'],
            ['name' => 'Supri', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->lawyerHana = User::firstOrCreate(
            ['email' => 'hana@sahabathukum.test'],
            ['name' => 'Hana Fauziah Balqis', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->clientRiko = User::firstOrCreate(
            ['email' => 'klien@sahabathukum.test'],
            ['name' => 'Riko Pratama', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );

        $this->otherClient = User::firstOrCreate(
            ['email' => 'siti@sahabathukum.test'],
            ['name' => 'Siti Rahmawati', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );
    }

    public function test_1_client_submits_consultation_has_no_lawyer_and_no_conversation(): Consultation
    {
        $response = $this->actingAs($this->clientRiko)->post('/klien/konsultasi', [
            'problem_type' => 'Hukum Perdata',
            'title'        => 'Uji Sengketa Warisan Unik',
            'description'  => 'Uraian masalah sengketa warisan tanah keluarga.',
        ]);

        $response->assertRedirect('/klien/konsultasi');
        $response->assertSessionHas('success');

        $consultation = Consultation::where('client_id', $this->clientRiko->id)
            ->where('title', 'Uji Sengketa Warisan Unik')
            ->latest('id')
            ->first();

        $this->assertNotNull($consultation);
        $this->assertNull($consultation->lawyer_id, 'Lawyer should be null upon client submission');
        $this->assertEquals('Menunggu', $consultation->status);

        // Assert NO conversation is created before admin assignment
        $conversation = Conversation::where('consultation_id', $consultation->id)->first();
        $this->assertNull($conversation, 'Conversation must not exist before lawyer assignment');

        return $consultation;
    }

    public function test_2_admin_assigns_lawyer_creates_conversation_with_null_last_message_at(): void
    {
        $consultation = $this->test_1_client_submits_consultation_has_no_lawyer_and_no_conversation();

        // Admin assigns lawyer Supri
        $response = $this->actingAs($this->admin)->post("/admin/consultations/{$consultation->id}/assign-lawyer", [
            'lawyer_id' => $this->lawyerSupri->id,
        ]);

        $response->assertRedirect('/admin/consultations');
        $response->assertSessionHas('success');

        $consultation->refresh();
        $this->assertEquals($this->lawyerSupri->id, $consultation->lawyer_id);
        $this->assertEquals('Menunggu', $consultation->status, 'Status must remain existing status Menunggu');

        // Assert Conversation created
        $conversation = Conversation::where('consultation_id', $consultation->id)->first();
        $this->assertNotNull($conversation, 'Conversation must be created after assignment');
        $this->assertEquals($this->clientRiko->id, $conversation->client_id);
        $this->assertEquals($this->lawyerSupri->id, $conversation->lawyer_id);
        $this->assertNull($conversation->last_message_at, 'last_message_at must be NULL upon initialization');
        $this->assertStringContainsString('Uji Sengketa Warisan Unik', $conversation->context_title);
    }

    public function test_3_messaging_between_client_and_lawyer_and_idor_protection(): void
    {
        $consultation = $this->test_1_client_submits_consultation_has_no_lawyer_and_no_conversation();
        $this->actingAs($this->admin)->post("/admin/consultations/{$consultation->id}/assign-lawyer", [
            'lawyer_id' => $this->lawyerSupri->id,
        ]);
        $conversation = Conversation::where('consultation_id', $consultation->id)->first();

        // 1. Client attempts empty message -> rejected
        $emptyResponse = $this->actingAs($this->clientRiko)->postJson("/klien/percakapan/{$conversation->id}/messages", [
            'message' => '   ',
        ]);
        $emptyResponse->assertStatus(422);

        // 2. Client sends valid message
        $sendResponse = $this->actingAs($this->clientRiko)->postJson("/klien/percakapan/{$conversation->id}/messages", [
            'message' => 'Selamat pagi Advokat Supri, mohon info jadwal konsultasi.',
        ]);
        $sendResponse->assertOk();
        $sendResponse->assertJson(['success' => true]);

        $conversation->refresh();
        $this->assertNotNull($conversation->last_message_at, 'last_message_at must be updated when a message is sent');

        // 3. IDOR: Another client tries to view conversation -> 403 Forbidden
        $idorClientResponse = $this->actingAs($this->otherClient)->get("/klien/percakapan?conversation_id={$conversation->id}");
        $idorClientResponse->assertStatus(403);

        // 4. IDOR: Another lawyer tries to view conversation -> 403 Forbidden
        $idorLawyerResponse = $this->actingAs($this->lawyerHana)->get("/advokat/percakapan?conversation_id={$conversation->id}");
        $idorLawyerResponse->assertStatus(403);

        // 5. Assigned lawyer views conversation -> 200 OK
        $assignedLawyerResponse = $this->actingAs($this->lawyerSupri)->get("/advokat/percakapan?conversation_id={$conversation->id}");
        $assignedLawyerResponse->assertOk();

        // 6. Assigned lawyer replies
        $replyResponse = $this->actingAs($this->lawyerSupri)->postJson("/advokat/percakapan/{$conversation->id}/messages", [
            'message' => 'pak dokumennya jangan lupa di bawa yah',
        ]);
        $replyResponse->assertOk();

        // 7. Client visits conversation page and renders message snippet without error
        $clientPage = $this->actingAs($this->clientRiko)->get("/klien/percakapan?conversation_id={$conversation->id}");
        $clientPage->assertOk();
        $clientPage->assertSee('pak dokumennya jangan lupa di bawa');
    }

    public function test_4_admin_reassigns_lawyer_transfers_access(): void
    {
        $consultation = $this->test_1_client_submits_consultation_has_no_lawyer_and_no_conversation();
        $this->actingAs($this->admin)->post("/admin/consultations/{$consultation->id}/assign-lawyer", [
            'lawyer_id' => $this->lawyerSupri->id,
        ]);
        $conversation = Conversation::where('consultation_id', $consultation->id)->first();

        // Supri initially has access
        $this->actingAs($this->lawyerSupri)->get("/advokat/percakapan?conversation_id={$conversation->id}")->assertOk();

        // Admin reassigns to Hana
        $reassignResponse = $this->actingAs($this->admin)->post("/admin/consultations/{$consultation->id}/assign-lawyer", [
            'lawyer_id' => $this->lawyerHana->id,
        ]);
        $reassignResponse->assertRedirect('/admin/consultations');

        $consultation->refresh();
        $conversation->refresh();
        $this->assertEquals($this->lawyerHana->id, $consultation->lawyer_id);
        $this->assertEquals($this->lawyerHana->id, $conversation->lawyer_id);

        // Now Supri is denied (403)
        $this->actingAs($this->lawyerSupri)->get("/advokat/percakapan?conversation_id={$conversation->id}")->assertStatus(403);

        // Hana now has access (200)
        $this->actingAs($this->lawyerHana)->get("/advokat/percakapan?conversation_id={$conversation->id}")->assertOk();
    }

    public function test_5_case_creation_links_conversation_and_updates_context_title(): void
    {
        $consultation = $this->test_1_client_submits_consultation_has_no_lawyer_and_no_conversation();
        $this->actingAs($this->admin)->post("/admin/consultations/{$consultation->id}/assign-lawyer", [
            'lawyer_id' => $this->lawyerSupri->id,
        ]);
        $conversation = Conversation::where('consultation_id', $consultation->id)->first();

        // Complete consultation first so case can be created
        $consultation->update([
            'status'       => 'Selesai',
            'result_notes' => 'Konsultasi selesai dilaksanakan.',
        ]);

        $uniqueCaseNum = 'TEST/' . uniqid() . '/2026';
        $caseResponse = $this->actingAs($this->lawyerSupri)->post('/advokat/perkara', [
            'consultation_id' => $consultation->id,
            'case_number'     => $uniqueCaseNum,
            'case_type'       => 'Perdata — Gugatan',
            'title'           => 'Perkara Uji Integrasi',
            'summary'         => 'Ringkasan perkara uji.',
            'started_at'      => now()->toDateString(),
        ]);
        $caseResponse->assertSessionHas('success');

        $conversation->refresh();
        $this->assertNotNull($conversation->case_id, 'Conversation should be linked to LegalCase');
        $this->assertStringContainsString('Perkara:', $conversation->context_title);
        $this->assertStringContainsString($uniqueCaseNum, $conversation->context_title);
    }
}
