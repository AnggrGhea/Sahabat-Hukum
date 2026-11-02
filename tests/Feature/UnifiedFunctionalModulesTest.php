<?php

namespace Tests\Feature;

use App\Models\CaseProgress;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\KnowledgeSource;
use App\Models\LegalCase;
use App\Models\Message;
use App\Models\User;
use App\Services\LegalRagService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnifiedFunctionalModulesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $lawyer1;
    private User $lawyer2;
    private User $client1;
    private User $client2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $prefix = Str::random(8);

        $this->admin = User::create([
            'name'     => 'Admin ' . $prefix,
            'email'    => 'admin_' . $prefix . '@sahabathukum.test',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
            'status'   => 'aktif',
        ]);

        $this->lawyer1 = User::create([
            'name'     => 'Advokat Satu ' . $prefix,
            'email'    => 'lawyer1_' . $prefix . '@sahabathukum.test',
            'password' => Hash::make('password123'),
            'role'     => 'advokat',
            'status'   => 'aktif',
        ]);

        $this->lawyer2 = User::create([
            'name'     => 'Advokat Dua ' . $prefix,
            'email'    => 'lawyer2_' . $prefix . '@sahabathukum.test',
            'password' => Hash::make('password123'),
            'role'     => 'advokat',
            'status'   => 'aktif',
        ]);

        $this->client1 = User::create([
            'name'     => 'Klien Satu ' . $prefix,
            'email'    => 'client1_' . $prefix . '@sahabathukum.test',
            'password' => Hash::make('password123'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);

        $this->client2 = User::create([
            'name'     => 'Klien Dua ' . $prefix,
            'email'    => 'client2_' . $prefix . '@sahabathukum.test',
            'password' => Hash::make('password123'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. MODUL ASISTEN HUKUM (AI + RAG + KNOWLEDGE BASE)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_asisten_hukum_pages_accessible_for_client_and_lawyer(): void
    {
        // Klien accessing Asisten Hukum
        $resClient = $this->actingAs($this->client1)->get(route('klien.assistant'));
        $resClient->assertStatus(200);
        $resClient->assertSee('Asisten Hukum');

        // Advokat accessing Asisten Hukum
        $resLawyer = $this->actingAs($this->lawyer1)->get(route('advokat.assistant'));
        $resLawyer->assertStatus(200);
        $resLawyer->assertSee('Asisten Hukum');
    }

    public function test_asisten_hukum_chat_and_clear_history(): void
    {
        // Mock LegalRagService to avoid real external network calls
        $mockRag = $this->createMock(LegalRagService::class);
        $mockRag->method('ask')->willReturn([
            'success' => true,
            'answer'  => 'Ketentuan Pasal 362 KUHP mengatur tentang tindak pidana pencurian dengan ancaman pidana maksimal lima tahun.',
            'sources' => [
                [
                    'title'       => 'Kitab Undang-Undang Hukum Pidana (KUHP)',
                    'url'         => 'https://peraturan.bpk.go.id/Details/123/kuhp',
                    'tier'        => 'primary',
                    'status'      => 'Terverifikasi Resmi',
                    'is_official' => true,
                ]
            ],
        ]);
        $this->app->instance(LegalRagService::class, $mockRag);

        // Client asks legal question
        $response = $this->actingAs($this->client1)->postJson(route('klien.assistant.chat'), [
            'message' => 'Apa bunyi pasal 362 KUHP tentang pencurian?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'title' => 'Kitab Undang-Undang Hukum Pidana (KUHP)',
        ]);

        // Verify history exists in session
        $sessionKey = 'legal_assistant_messages_' . $this->client1->id;
        $this->assertNotEmpty(session($sessionKey));

        // Clear history
        $clearResponse = $this->actingAs($this->client1)->postJson(route('klien.assistant.clear'));
        $clearResponse->assertStatus(200);
        $clearResponse->assertJson(['success' => true]);
        $this->assertEmpty(session($sessionKey));
    }

    public function test_admin_knowledge_source_lifecycle(): void
    {
        // 1. Admin creates a new knowledge source
        $resStore = $this->actingAs($this->admin)->post(route('admin.knowledge.store'), [
            'title'       => 'SOP Pelayanan Konsultasi Hukum 2026',
            'content'     => 'Seluruh konsultasi harus ditanggapi dalam waktu 1x24 jam kerja.',
            'source_type' => 'manual',
            'status'      => 'Aktif',
            'description' => 'SOP Internal Pelayanan',
        ]);

        $resStore->assertRedirect(route('admin.knowledge'));
        $source = KnowledgeSource::where('title', 'SOP Pelayanan Konsultasi Hukum 2026')->first();
        $this->assertNotNull($source);
        $this->assertEquals('Aktif', $source->status);

        // 2. Admin toggles status to Nonaktif
        $resToggle = $this->actingAs($this->admin)->patch(route('admin.knowledge.toggle', $source->id));
        $resToggle->assertRedirect(route('admin.knowledge'));
        $this->assertEquals('Nonaktif', $source->fresh()->status);

        // 3. Admin updates content
        $resUpdate = $this->actingAs($this->admin)->put(route('admin.knowledge.update', $source->id), [
            'title'       => 'SOP Pelayanan Konsultasi Hukum Revisi',
            'content'     => 'Pembaruan: Seluruh konsultasi ditanggapi dalam waktu 12 jam.',
            'source_type' => 'manual',
            'status'      => 'Aktif',
            'description' => 'SOP Pelayanan Revisi',
        ]);
        $resUpdate->assertRedirect(route('admin.knowledge'));
        $this->assertEquals('SOP Pelayanan Konsultasi Hukum Revisi', $source->fresh()->title);

        // 4. Admin deletes knowledge source
        $resDestroy = $this->actingAs($this->admin)->delete(route('admin.knowledge.destroy', $source->id));
        $resDestroy->assertRedirect(route('admin.knowledge'));
        $this->assertNull(KnowledgeSource::find($source->id));
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. MODUL MANAJEMEN PERKARA (LEGAL CASE MANAGEMENT)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_advocate_creates_legal_case_from_consultation(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'lawyer_id'    => $this->lawyer1->id,
            'title'        => 'Sengketa Pembagian Waris Tanah',
            'problem_type' => 'Perdata',
            'description'  => 'Tanah warisan dikuasai sepihak.',
            'status'       => 'Berjalan',
        ]);

        $caseNumber = 'PERK-' . strtoupper(Str::random(6));

        $resStore = $this->actingAs($this->lawyer1)->post(route('advokat.cases.store'), [
            'consultation_id' => $consultation->id,
            'case_number'     => $caseNumber,
            'case_type'       => 'Perdata',
            'title'           => 'Gugatan Perdata Sengketa Waris Tanah',
            'summary'         => 'Uraian gugatan perdata di Pengadilan Negeri.',
            'started_at'      => now()->toDateString(),
        ]);

        $case = LegalCase::where('case_number', $caseNumber)->first();
        $this->assertNotNull($case);
        $resStore->assertRedirect(route('advokat.cases.show', $case->id));
        $this->assertEquals($this->client1->id, $case->client_id);
        $this->assertEquals($this->lawyer1->id, $case->lawyer_id);
    }

    public function test_advocate_adds_progress_to_case_and_client_views_detail(): void
    {
        $case = LegalCase::create([
            'client_id'   => $this->client1->id,
            'lawyer_id'   => $this->lawyer1->id,
            'case_number' => 'CASE-PROG-001',
            'case_type'   => 'Perdata',
            'title'       => 'Perkara Uji Coba Progres',
            'status'      => 'Berjalan',
            'started_at'  => now()->toDateString(),
        ]);

        // Advocate adds progress timeline
        $resProg = $this->actingAs($this->lawyer1)->post(route('advokat.cases.progress', $case->id), [
            'title'         => 'Pendaftaran Berkas di Kepaniteraan Pengadilan',
            'description'   => 'Berkas perkara telah resmi terdaftar dan mendapat nomor register.',
            'progress_date' => now()->toDateString(),
        ]);

        $resProg->assertRedirect(route('advokat.cases.show', $case->id));

        $progress = CaseProgress::where('case_id', $case->id)->first();
        $this->assertNotNull($progress);
        $this->assertEquals('Pendaftaran Berkas di Kepaniteraan Pengadilan', $progress->title);

        // Client views the case detail and sees the progress item
        $resClientView = $this->actingAs($this->client1)->get(route('klien.cases.show', $case->id));
        $resClientView->assertStatus(200);
        $resClientView->assertSee('Pendaftaran Berkas di Kepaniteraan Pengadilan');
    }

    public function test_case_access_control_and_idor_protection(): void
    {
        $caseClient1 = LegalCase::create([
            'client_id'   => $this->client1->id,
            'lawyer_id'   => $this->lawyer1->id,
            'case_number' => 'CASE-IDOR-001',
            'case_type'   => 'Perdata',
            'title'       => 'Perkara Rahasia Klien Satu',
            'status'      => 'Berjalan',
            'started_at'  => now()->toDateString(),
        ]);

        // Client 2 attempts to view Client 1's case -> 404 forbidden/not found
        $resClient2 = $this->actingAs($this->client2)->get(route('klien.cases.show', $caseClient1->id));
        $resClient2->assertStatus(404);

        // Lawyer 2 attempts to view Lawyer 1's case -> 404 forbidden/not found
        $resLawyer2 = $this->actingAs($this->lawyer2)->get(route('advokat.cases.show', $caseClient1->id));
        $resLawyer2->assertStatus(404);

        // Admin can view all cases in list
        $resAdmin = $this->actingAs($this->admin)->get(route('admin.cases'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('CASE-IDOR-001');
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 3. MODUL MANAJEMEN DOKUMEN (UPLOAD, VERIFIKASI, REVISI, IDOR)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_document_full_lifecycle_upload_verify_reject_and_reupload(): void
    {
        $case = LegalCase::create([
            'client_id'   => $this->client1->id,
            'lawyer_id'   => $this->lawyer1->id,
            'case_number' => 'DOC-LIFE-001',
            'case_type'   => 'Perdata',
            'title'       => 'Sengketa Perjanjian Kontrak',
            'status'      => 'Berjalan',
            'started_at'  => now()->toDateString(),
        ]);

        // 1. Advocate requests document from client
        $resReq = $this->actingAs($this->lawyer1)->post(route('advokat.cases.document', $case->id), [
            'title'         => 'Salinan Polis Asuransi',
            'document_type' => 'Polis',
            'description'   => 'Lampirkan halaman ketentuan klaim.',
            'due_date'      => now()->addDays(5)->toDateString(),
            'priority'      => 'Tinggi',
        ]);
        $resReq->assertRedirect(route('advokat.cases.show', $case->id));
        $docRequest = DocumentRequest::where('case_id', $case->id)->first();
        $this->assertNotNull($docRequest);

        // 2. Client uploads requested document (simulated PDF)
        $fakeFile = UploadedFile::fake()->create('polis_asuransi.pdf', 300, 'application/pdf');
        $resUpload = $this->actingAs($this->client1)->post(route('klien.cases.document.upload', $case->id), [
            'document_request_id' => $docRequest->id,
            'name'                => 'Polis Asuransi Jiwa',
            'document_type'       => 'Polis',
            'description'         => 'Salinan polis asli',
            'file'                => $fakeFile,
        ]);
        $resUpload->assertRedirect(route('klien.cases.show', $case->id));

        $uploadedDoc = Document::where('case_id', $case->id)->where('name', 'Polis Asuransi Jiwa')->first();
        $this->assertNotNull($uploadedDoc);
        $this->assertEquals('Menunggu Verifikasi', $uploadedDoc->status);

        // 3. Advocate rejects document with revision notes
        $resReject = $this->actingAs($this->lawyer1)->post(route('advokat.documents.reject', $uploadedDoc->id), [
            'rejection_reason' => 'Halaman tanda tangan tidak terlampir pada dokumen.',
        ]);
        $resReject->assertRedirect();
        $this->assertEquals('Ditolak', $uploadedDoc->fresh()->status);
        $this->assertEquals('Halaman tanda tangan tidak terlampir pada dokumen.', $uploadedDoc->fresh()->rejection_reason);

        // 4. Client re-uploads revised version
        $revisedFile = UploadedFile::fake()->create('polis_asuransi_lengkap.pdf', 400, 'application/pdf');
        $resReupload = $this->actingAs($this->client1)->post(route('klien.documents.reupload', $uploadedDoc->id), [
            'file' => $revisedFile,
        ]);
        $resReupload->assertRedirect(route('klien.cases.show', $case->id));

        $newVersionDoc = Document::where('parent_id', $uploadedDoc->id)->first();
        $this->assertNotNull($newVersionDoc);
        $this->assertEquals(2, $newVersionDoc->version);
        $this->assertEquals('Menunggu Verifikasi', $newVersionDoc->status);

        // 5. Advocate verifies the revised document
        $resVerify = $this->actingAs($this->lawyer1)->post(route('advokat.documents.verify', $newVersionDoc->id));
        $resVerify->assertRedirect();
        $this->assertEquals('Terverifikasi', $newVersionDoc->fresh()->status);
        $this->assertNotNull($newVersionDoc->fresh()->verified_at);
    }

    public function test_document_download_and_view_idor_protection(): void
    {
        $case = LegalCase::create([
            'client_id'   => $this->client1->id,
            'lawyer_id'   => $this->lawyer1->id,
            'case_number' => 'DOC-IDOR-001',
            'case_type'   => 'Perdata',
            'title'       => 'Dokumen Sangat Rahasia',
            'status'      => 'Berjalan',
            'started_at'  => now()->toDateString(),
        ]);

        Storage::disk('local')->put("documents/{$case->id}/secret.pdf", "Dummy PDF content");

        $doc = Document::create([
            'case_id'           => $case->id,
            'client_id'         => $this->client1->id,
            'lawyer_id'         => $this->lawyer1->id,
            'uploaded_by'       => $this->client1->id,
            'name'              => 'Akta Rahasia',
            'document_type'     => 'Akta',
            'file_path'         => "documents/{$case->id}/secret.pdf",
            'original_filename' => 'secret.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 1234,
            'status'            => 'Terverifikasi',
        ]);

        // Client 1 (owner) can download
        $resOwner = $this->actingAs($this->client1)->get(route('documents.download', $doc->id));
        $resOwner->assertStatus(200);

        // Lawyer 1 (assigned advocate) can download
        $resLawyer = $this->actingAs($this->lawyer1)->get(route('documents.download', $doc->id));
        $resLawyer->assertStatus(200);

        // Admin can download
        $resAdmin = $this->actingAs($this->admin)->get(route('documents.download', $doc->id));
        $resAdmin->assertStatus(200);

        // Client 2 (unauthorized) cannot download -> 403 Forbidden
        $resStranger = $this->actingAs($this->client2)->get(route('documents.download', $doc->id));
        $resStranger->assertStatus(403);

        // Lawyer 2 (unauthorized) cannot view -> 403 Forbidden
        $resStrangerLawyer = $this->actingAs($this->lawyer2)->get(route('documents.view', $doc->id));
        $resStrangerLawyer->assertStatus(403);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 4. MODUL PERCAKAPAN (CONVERSATIONS / CHAT)
    // ═════════════════════════════════════════════════════════════════════════

    public function test_conversation_messaging_authorization_and_polling(): void
    {
        $consultation = Consultation::create([
            'client_id'    => $this->client1->id,
            'lawyer_id'    => $this->lawyer1->id,
            'title'        => 'Konsultasi Perjanjian Waralaba',
            'problem_type' => 'Bisnis',
            'description'  => 'Review draf waralaba.',
            'status'       => 'Berjalan',
        ]);

        $conversation = Conversation::create([
            'consultation_id' => $consultation->id,
            'client_id'       => $this->client1->id,
            'lawyer_id'       => $this->lawyer1->id,
            'title'           => 'Percakapan Konsultasi Bisnis',
            'status'          => 'active',
            'last_message_at' => null,
        ]);

        // 1. Client 1 sends message
        $resSend = $this->actingAs($this->client1)->postJson(route('klien.chat.send', $conversation->id), [
            'message' => 'Halo Advokat, mohon review lampiran 2.',
        ]);
        $resSend->assertStatus(200);
        $resSend->assertJson(['success' => true]);

        $this->assertEquals(1, Message::where('conversation_id', $conversation->id)->count());
        $this->assertNotNull($conversation->fresh()->last_message_at);

        // 2. Lawyer 1 gets messages and marks unread as read
        $resGet = $this->actingAs($this->lawyer1)->getJson(route('advokat.chat.messages', $conversation->id));
        $resGet->assertStatus(200);
        $resGet->assertJsonStructure(['success', 'messages']);
        $this->assertCount(1, $resGet->json('messages'));

        // 3. Unauthorized Client 2 attempts to send message -> 403 Forbidden
        $resUnauthorized = $this->actingAs($this->client2)->postJson(route('klien.chat.send', $conversation->id), [
            'message' => 'Pesan penyusup.',
        ]);
        $resUnauthorized->assertStatus(403);

        // 4. Unauthorized Lawyer 2 attempts to fetch messages -> 403 Forbidden
        $resUnauthorizedGet = $this->actingAs($this->lawyer2)->getJson(route('advokat.chat.messages', $conversation->id));
        $resUnauthorizedGet->assertStatus(403);
    }
}
