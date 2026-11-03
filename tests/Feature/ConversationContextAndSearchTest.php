<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConversationContextAndSearchTest extends TestCase
{
    use DatabaseTransactions;

    protected User $clientRiko;
    protected User $clientOther;
    protected User $lawyerSupri;
    protected User $lawyerOther;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRiko = User::firstOrCreate(
            ['email' => 'klien@sahabathukum.test'],
            ['name' => 'Riko Pratama', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );

        $this->clientOther = User::firstOrCreate(
            ['email' => 'otherclient@sahabathukum.test'],
            ['name' => 'Klien Lain', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );

        $this->lawyerSupri = User::firstOrCreate(
            ['email' => 'advokat@sahabathukum.test'],
            ['name' => 'Supri', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->lawyerOther = User::firstOrCreate(
            ['email' => 'otherlawyer@sahabathukum.test'],
            ['name' => 'Advokat Lain, S.H.', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );
    }

    /**
     * Test empty state for user without any handled consultation or case
     */
    public function test_user_without_consultation_or_case_sees_clean_empty_state(): void
    {
        $freshUser = User::create([
            'name'     => 'User Tanpa Perkara',
            'email'    => 'freshuser_' . uniqid() . '@sahabathukum.test',
            'password' => bcrypt('password'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);

        $response = $this->actingAs($freshUser)->get('/klien/percakapan');
        $response->assertOk();
        $response->assertSee('Belum ada percakapan');
        $response->assertSee('Percakapan tersedia setelah konsultasi ditangani atau perkara ditetapkan.');
    }

    /**
     * Test consultation assignment links to conversation and case links properly
     */
    public function test_consultation_and_case_relation_context(): void
    {
        // 1. Handled consultation between Riko and Supri
        $consultation = Consultation::create([
            'client_id'    => $this->clientRiko->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Konsultasi Sertifikat Hak Milik ' . uniqid(),
            'description'  => 'Uraian konsultasi sertifikat tanah warisan.',
            'status'       => 'Dijadwalkan',
        ]);

        // Accessing /klien/percakapan safely syncs handled consultation
        $clientResponse = $this->actingAs($this->clientRiko)->get('/klien/percakapan');
        $clientResponse->assertOk();
        $clientResponse->assertSee($this->lawyerSupri->name);
        $clientResponse->assertSee('Konsultasi: ' . $consultation->title);

        $conversation = Conversation::where('consultation_id', $consultation->id)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($this->clientRiko->id, $conversation->client_id);
        $this->assertEquals($this->lawyerSupri->id, $conversation->lawyer_id);

        // 2. Consultation becomes a Case
        $caseNum = '099/Pdt.G/2026/PN.Test_' . uniqid();
        $legalCase = LegalCase::create([
            'client_id'       => $this->clientRiko->id,
            'lawyer_id'       => $this->lawyerSupri->id,
            'consultation_id' => $consultation->id,
            'case_number'     => $caseNum,
            'case_type'       => 'Perdata — Gugatan',
            'title'           => 'Gugatan Sertifikat Tanah ' . uniqid(),
            'status'          => 'Persidangan',
            'started_at'      => now(),
        ]);

        // Accessing percakapan connects/updates case link
        $clientCaseResponse = $this->actingAs($this->clientRiko)->get('/klien/percakapan?conversation_id=' . $conversation->id);
        $clientCaseResponse->assertOk();
        $clientCaseResponse->assertSee('Lihat Perkara');
        $clientCaseResponse->assertSee($caseNum);

        // Verify "Lihat Perkara" button link points to case detail
        $clientCaseResponse->assertSee('/klien/perkara/' . $legalCase->id);
    }

    /**
     * Test multi-criteria search (client name, lawyer name, case number, message body)
     */
    public function test_multi_criteria_search_including_message_body(): void
    {
        $uniqueKeyword = 'rahasianotaris_' . uniqid();

        $consultation = Consultation::create([
            'client_id'    => $this->clientRiko->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Sengketa Warisan Unik ' . uniqid(),
            'description'  => 'Konsultasi warisan.',
            'status'       => 'Dijadwalkan',
        ]);

        $conversation = Conversation::create([
            'consultation_id' => $consultation->id,
            'client_id'       => $this->clientRiko->id,
            'lawyer_id'       => $this->lawyerSupri->id,
            'title'           => 'Konsultasi: ' . $consultation->title,
            'status'          => 'active',
            'last_message_at' => now(),
        ]);

        // Message containing the unique keyword inside the body
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $this->lawyerSupri->id,
            'body'            => 'Bapak harap membawa berkas ' . $uniqueKeyword . ' ke pengadilan besok.',
            'is_read'         => false,
        ]);

        // 1. Search by message body
        $searchBodyResponse = $this->actingAs($this->clientRiko)->get('/klien/percakapan?q=' . $uniqueKeyword);
        $searchBodyResponse->assertOk();
        $searchBodyResponse->assertSee($this->lawyerSupri->name);

        // 2. Search by lawyer name
        $searchLawyerResponse = $this->actingAs($this->clientRiko)->get('/klien/percakapan?q=Supri');
        $searchLawyerResponse->assertOk();
        $searchLawyerResponse->assertSee($this->lawyerSupri->name);

        // 3. Search via JSON API endpoint
        $jsonResponse = $this->actingAs($this->clientRiko)->getJson('/klien/percakapan?q=' . $uniqueKeyword);
        $jsonResponse->assertOk();
        $jsonResponse->assertJson(['success' => true]);
        $this->assertNotEmpty($jsonResponse->json('conversations'));
        $this->assertEquals($conversation->id, $jsonResponse->json('conversations.0.id'));

        // 4. Search for non-existent keyword returns 0 conversations
        $emptySearchResponse = $this->actingAs($this->clientRiko)->get('/klien/percakapan?q=katakuncitidakadabersama' . uniqid());
        $emptySearchResponse->assertOk();
        $emptySearchResponse->assertSee('Tidak ada hasil pencarian');
    }

    /**
     * Test sending message, unread count, and chronological order
     */
    public function test_send_message_and_unread_flow(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->clientRiko->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Konsultasi Alur Chat ' . uniqid(),
            'description'  => 'Konsultasi.',
            'status'       => 'Dijadwalkan',
        ]);

        $conversation = Conversation::create([
            'consultation_id' => $consultation->id,
            'client_id'       => $this->clientRiko->id,
            'lawyer_id'       => $this->lawyerSupri->id,
            'title'           => 'Konsultasi: ' . $consultation->title,
            'status'          => 'active',
            'last_message_at' => null,
        ]);

        // 1. Client sends message
        $sendResponse = $this->actingAs($this->clientRiko)->postJson("/klien/percakapan/{$conversation->id}/messages", [
            'message' => 'Halo Advokat Supri, mohon info kelanjutan dokumen.',
        ]);
        $sendResponse->assertOk();
        $sendResponse->assertJson(['success' => true]);

        // Unread for lawyer should be 1
        $this->assertEquals(1, $conversation->unreadCountFor($this->lawyerSupri->id));
        // Unread for client (sender) should be 0
        $this->assertEquals(0, $conversation->unreadCountFor($this->clientRiko->id));

        // 2. Lawyer views conversation -> marks unread as read
        $lawyerView = $this->actingAs($this->lawyerSupri)->get("/advokat/percakapan?conversation_id={$conversation->id}");
        $lawyerView->assertOk();
        $this->assertEquals(0, $conversation->fresh()->unreadCountFor($this->lawyerSupri->id));

        // 3. Lawyer replies
        $replyResponse = $this->actingAs($this->lawyerSupri)->postJson("/advokat/percakapan/{$conversation->id}/messages", [
            'message' => 'Siap Pak Riko, dokumen sudah selesai kami periksa.',
        ]);
        $replyResponse->assertOk();

        // Unread for client is now 1
        $this->assertEquals(1, $conversation->fresh()->unreadCountFor($this->clientRiko->id));

        // Client views conversation -> marks as read
        $this->actingAs($this->clientRiko)->get("/klien/percakapan?conversation_id={$conversation->id}");
        $this->assertEquals(0, $conversation->fresh()->unreadCountFor($this->clientRiko->id));
    }

    /**
     * Test IDOR Authorization Protection
     */
    public function test_idor_authorization_protection(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->clientRiko->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Konsultasi Terkunci ' . uniqid(),
            'description'  => 'Konsultasi rahasia.',
            'status'       => 'Dijadwalkan',
        ]);

        $conversation = Conversation::create([
            'consultation_id' => $consultation->id,
            'client_id'       => $this->clientRiko->id,
            'lawyer_id'       => $this->lawyerSupri->id,
            'title'           => 'Konsultasi: ' . $consultation->title,
            'status'          => 'active',
            'last_message_at' => null,
        ]);

        // Another client tries to view Riko's conversation -> 403 Forbidden
        $unauthorizedClientResponse = $this->actingAs($this->clientOther)->get("/klien/percakapan?conversation_id={$conversation->id}");
        $unauthorizedClientResponse->assertStatus(403);

        // Another lawyer tries to view Supri's conversation -> 403 Forbidden
        $unauthorizedLawyerResponse = $this->actingAs($this->lawyerOther)->get("/advokat/percakapan?conversation_id={$conversation->id}");
        $unauthorizedLawyerResponse->assertStatus(403);

        // Another client tries to send message into Riko's conversation -> 403 Forbidden
        $unauthorizedSendResponse = $this->actingAs($this->clientOther)->postJson("/klien/percakapan/{$conversation->id}/messages", [
            'message' => 'Mencoba injeksi pesan tidak sah',
        ]);
        $unauthorizedSendResponse->assertStatus(403);
    }
}
