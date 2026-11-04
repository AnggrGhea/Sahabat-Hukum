<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LegalCase;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentComment;
use App\Models\CaseLawyer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DocumentCollaborationNotification;

class DocumentCollaborationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Skenario 1 & 2 & 3: Hak Akses Advokat Utama, Anggota Tim, dan Blokir Advokat Luar
     */
    public function test_advocate_access_authorization_and_isolation()
    {
        // Lead lawyer
        $leadLawyer = User::where('role', 'advokat')->first();
        // Client
        $client = User::where('role', 'klien')->first();

        // Create case
        $case = LegalCase::create([
            'client_id'       => $client->id,
            'lawyer_id'       => $leadLawyer->id,
            'case_number'     => 'TEST/COL/001/' . time(),
            'case_type'       => 'Perdata — Gugatan',
            'title'           => 'Kasus Uji Kolaborasi Dokumen',
            'summary'         => 'Ringkasan uji coba.',
            'status'          => 'Proses',
            'started_at'      => now(),
        ]);

        // Second lawyer (Co-counsel)
        $secondLawyer = User::where('role', 'advokat')->where('id', '!=', $leadLawyer->id)->first();
        if (!$secondLawyer) {
            $secondLawyer = User::create([
                'name'     => 'Advokat Rekan Uji',
                'email'    => 'advokat_rekan_' . time() . '@sahabathukum.test',
                'password' => bcrypt('password'),
                'role'     => 'advokat',
                'status'   => 'aktif',
            ]);
        }

        // Third lawyer (Unauthorized)
        $unauthorizedLawyer = User::create([
            'name'     => 'Advokat Luar Tim',
            'email'    => 'advokat_luar_' . time() . '@sahabathukum.test',
            'password' => bcrypt('password'),
            'role'     => 'advokat',
            'status'   => 'aktif',
        ]);

        // 1. Lead lawyer can access collaboration room
        $response = $this->actingAs($leadLawyer)->get(route('advokat.cases.collaboration', $case->id));
        $response->assertStatus(200);

        // 3. Unauthorized advocate is blocked with 403
        $responseUnauth = $this->actingAs($unauthorizedLawyer)->get(route('advokat.cases.collaboration', $case->id));
        $responseUnauth->assertStatus(403);

        // Assign second lawyer to case team
        $assignResponse = $this->actingAs($leadLawyer)->post(route('advokat.cases.team.add', $case->id), [
            'user_id'      => $secondLawyer->id,
            'role_in_case' => 'Advokat Pendamping',
        ]);
        $assignResponse->assertRedirect();
        $this->assertDatabaseHas('case_lawyers', [
            'case_id' => $case->id,
            'user_id' => $secondLawyer->id,
        ]);

        // 2. Second lawyer now has full access to collaboration room
        $responseSecond = $this->actingAs($secondLawyer)->get(route('advokat.cases.collaboration', $case->id));
        $responseSecond->assertStatus(200);

        // Unauthorized lawyer still cannot add members (only lead lawyer can)
        $attemptAdd = $this->actingAs($secondLawyer)->post(route('advokat.cases.team.add', $case->id), [
            'user_id' => $unauthorizedLawyer->id,
        ]);
        $attemptAdd->assertStatus(403);
    }

    /**
     * Skenario 4 & 5: Upload Dokumen Baru, Upload Revisi (v1 -> v2), dan Unduh Versi Riwayat
     */
    public function test_document_versioning_and_revision_upload()
    {
        $leadLawyer = User::where('role', 'advokat')->first();
        $client     = User::where('role', 'klien')->first();

        $case = LegalCase::create([
            'client_id'   => $client->id,
            'lawyer_id'   => $leadLawyer->id,
            'case_number' => 'TEST/VER/002/' . time(),
            'case_type'   => 'Perdata — Gugatan',
            'title'       => 'Perkara Uji Versioning Dokumen',
            'status'      => 'Proses',
            'started_at'  => now(),
        ]);

        // 4a. Upload Document v1 (Internal)
        $fileV1 = UploadedFile::fake()->create('gugatan_draf_v1.pdf', 300, 'application/pdf');

        $responseUpload = $this->actingAs($leadLawyer)->post(route('advokat.cases.collaboration.document', $case->id), [
            'name'           => 'Surat Gugatan Wanprestasi',
            'document_type'  => 'Surat Gugatan',
            'is_internal'    => 1,
            'description'    => 'Draf gugatan awal.',
            'revision_notes' => 'Penyusunan kerangka awal posita dan petitum.',
            'file'           => $fileV1,
        ]);

        $responseUpload->assertRedirect();

        $document = Document::where('case_id', $case->id)->where('name', 'Surat Gugatan Wanprestasi')->first();
        $this->assertNotNull($document);
        $this->assertEquals(1, $document->version);
        $this->assertTrue((bool)$document->is_internal);
        $this->assertEquals('Draf', $document->collaboration_status);

        // Check Version 1 record in document_versions table
        $v1Record = DocumentVersion::where('document_id', $document->id)->where('version_number', 1)->first();
        $this->assertNotNull($v1Record);
        $this->assertEquals('gugatan_draf_v1.pdf', $v1Record->original_filename);
        $this->assertFalse((bool)$v1Record->is_final);

        // 4b. Upload Revision v2 (without overwriting v1)
        $fileV2 = UploadedFile::fake()->create('gugatan_revisi_v2.docx', 450, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $responseV2 = $this->actingAs($leadLawyer)->post(route('advokat.collaboration.version', $document->id), [
            'status'         => 'Dalam Revisi',
            'revision_notes' => 'Perbaikan klausul ganti rugi materiil pada posita poin 4.',
            'file'           => $fileV2,
        ]);

        $responseV2->assertRedirect();

        $document->refresh();
        $this->assertEquals(2, $document->version);
        $this->assertEquals('Dalam Revisi', $document->collaboration_status);

        $v2Record = DocumentVersion::where('document_id', $document->id)->where('version_number', 2)->first();
        $this->assertNotNull($v2Record);
        $this->assertEquals('gugatan_revisi_v2.docx', $v2Record->original_filename);

        // Make sure v1 is still intact in database
        $this->assertEquals(2, $document->documentVersions()->count());

        // 5. Download specific version (v1)
        $dlV1Response = $this->actingAs($leadLawyer)->get(route('documents.version.download', [
            'id'        => $document->id,
            'versionId' => $v1Record->id,
        ]));
        $dlV1Response->assertStatus(200);

        // Download specific version (v2)
        $dlV2Response = $this->actingAs($leadLawyer)->get(route('documents.version.download', [
            'id'        => $document->id,
            'versionId' => $v2Record->id,
        ]));
        $dlV2Response->assertStatus(200);
    }

    /**
     * Skenario 6: Komentar & Catatan Revisi Tim Terhubung dengan Versi
     */
    public function test_document_comment_system()
    {
        $leadLawyer = User::where('role', 'advokat')->first();
        $client     = User::where('role', 'klien')->first();

        $case = LegalCase::create([
            'client_id'   => $client->id,
            'lawyer_id'   => $leadLawyer->id,
            'case_number' => 'TEST/COM/003/' . time(),
            'case_type'   => 'Perdata — Gugatan',
            'title'       => 'Perkara Uji Komentar',
            'status'      => 'Proses',
            'started_at'  => now(),
        ]);

        $file = UploadedFile::fake()->create('replik_v1.pdf', 200, 'application/pdf');
        $this->actingAs($leadLawyer)->post(route('advokat.cases.collaboration.document', $case->id), [
            'name'          => 'Replik Penggugat',
            'document_type' => 'Replik',
            'is_internal'   => 1,
            'file'          => $file,
        ]);

        $document = Document::where('case_id', $case->id)->where('name', 'Replik Penggugat')->first();
        $v1 = $document->documentVersions()->first();

        // Add comment tied to v1
        $comResponse = $this->actingAs($leadLawyer)->post(route('advokat.collaboration.comment', $document->id), [
            'comment'             => 'Mohon periksa sanggahan dalil eksepsi tergugat pada paragraf 3.',
            'document_version_id' => $v1->id,
        ]);
        $comResponse->assertRedirect();

        $this->assertDatabaseHas('document_comments', [
            'document_id'         => $document->id,
            'document_version_id' => $v1->id,
            'user_id'             => $leadLawyer->id,
            'comment'             => 'Mohon periksa sanggahan dalil eksepsi tergugat pada paragraf 3.',
        ]);
    }

    /**
     * Skenario 7: Penetapan Versi Final dan Revisi Setelah Final
     */
    public function test_finalize_document_and_subsequent_revision()
    {
        $leadLawyer = User::where('role', 'advokat')->first();
        $client     = User::where('role', 'klien')->first();

        $case = LegalCase::create([
            'client_id'   => $client->id,
            'lawyer_id'   => $leadLawyer->id,
            'case_number' => 'TEST/FIN/004/' . time(),
            'case_type'   => 'Perdata — Gugatan',
            'title'       => 'Perkara Uji Finalisasi',
            'status'      => 'Proses',
            'started_at'  => now(),
        ]);

        $file = UploadedFile::fake()->create('surat_kuasa_v1.pdf', 150, 'application/pdf');
        $this->actingAs($leadLawyer)->post(route('advokat.cases.collaboration.document', $case->id), [
            'name'          => 'Surat Kuasa Khusus',
            'document_type' => 'Surat Kuasa',
            'is_internal'   => 1,
            'file'          => $file,
        ]);

        $document = Document::where('case_id', $case->id)->where('name', 'Surat Kuasa Khusus')->first();

        // Finalize document
        $finalResponse = $this->actingAs($leadLawyer)->post(route('advokat.collaboration.finalize', $document->id));
        $finalResponse->assertRedirect();

        $document->refresh();
        $this->assertEquals('Final', $document->collaboration_status);
        $this->assertEquals($leadLawyer->id, $document->finalized_by);
        $this->assertNotNull($document->finalized_at);

        $v1 = $document->documentVersions()->where('version_number', 1)->first();
        $this->assertTrue((bool)$v1->is_final);

        // Upload revision AFTER final (e.g., v2 revision required by court)
        $fileV2 = UploadedFile::fake()->create('surat_kuasa_v2_addendum.pdf', 200, 'application/pdf');
        $revAfterFinal = $this->actingAs($leadLawyer)->post(route('advokat.collaboration.version', $document->id), [
            'status'         => 'Dalam Revisi',
            'revision_notes' => 'Perubahan klausul mediasi sesuai petunjuk majelis hakim.',
            'file'           => $fileV2,
        ]);
        $revAfterFinal->assertRedirect();

        $document->refresh();
        $this->assertEquals(2, $document->version);
        $this->assertEquals('Dalam Revisi', $document->collaboration_status);

        // Past version 1 MUST remain marked as is_final = true
        $v1->refresh();
        $this->assertTrue((bool)$v1->is_final);

        // Version 2 is currently not final
        $v2 = $document->documentVersions()->where('version_number', 2)->first();
        $this->assertFalse((bool)$v2->is_final);
    }

    /**
     * Skenario 8: Pemisahan Akses Klien - Klien DILARANG Mengakses Dokumen Internal
     */
    public function test_client_cannot_access_internal_advocate_documents()
    {
        $leadLawyer = User::where('role', 'advokat')->first();
        $client     = User::where('role', 'klien')->first();

        $case = LegalCase::create([
            'client_id'   => $client->id,
            'lawyer_id'   => $leadLawyer->id,
            'case_number' => 'TEST/SEC/005/' . time(),
            'case_type'   => 'Perdata — Gugatan',
            'title'       => 'Perkara Uji Isolasi Klien',
            'status'      => 'Proses',
            'started_at'  => now(),
        ]);

        // Upload INTERNAL document (e.g. Strategi & Draf Gugatan Internal)
        $internalFile = UploadedFile::fake()->create('kajian_strategi_internal.pdf', 250, 'application/pdf');
        $this->actingAs($leadLawyer)->post(route('advokat.cases.collaboration.document', $case->id), [
            'name'          => 'Kajian Strategi Hukum Internal',
            'document_type' => 'Kajian Hukum',
            'is_internal'   => 1, // INTERNAL ONLY
            'file'          => $internalFile,
        ]);

        $internalDoc = Document::where('case_id', $case->id)->where('name', 'Kajian Strategi Hukum Internal')->first();
        $this->assertTrue((bool)$internalDoc->is_internal);

        // 8a. Client attempts direct view of internal document -> MUST RETURN 403 FORBIDDEN
        $viewResponse = $this->actingAs($client)->get(route('documents.view', $internalDoc->id));
        $viewResponse->assertStatus(403);

        // 8b. Client attempts direct download of internal document -> MUST RETURN 403 FORBIDDEN
        $downloadResponse = $this->actingAs($client)->get(route('documents.download', $internalDoc->id));
        $downloadResponse->assertStatus(403);

        // 8c. Client attempts download of internal version -> MUST RETURN 403 FORBIDDEN
        $vRecord = $internalDoc->documentVersions()->first();
        $vDownloadResponse = $this->actingAs($client)->get(route('documents.version.download', [
            'id'        => $internalDoc->id,
            'versionId' => $vRecord->id,
        ]));
        $vDownloadResponse->assertStatus(403);

        // 8d. Client case detail page MUST NOT display the internal document
        $caseDetailResponse = $this->actingAs($client)->get(route('klien.cases.show', $case->id));
        $caseDetailResponse->assertStatus(200);
        $caseDetailResponse->assertDontSee('Kajian Strategi Hukum Internal');

        // 8e. Client documents page MUST NOT list the internal document
        $docsPageResponse = $this->actingAs($client)->get(route('klien.documents'));
        $docsPageResponse->assertStatus(200);
        $docsPageResponse->assertDontSee('Kajian Strategi Hukum Internal');
    }

    /**
     * Skenario 11: Validasi Menolak Ekstensi Berkas Berbahaya
     */
    public function test_file_validation_rejects_unsafe_extensions()
    {
        $leadLawyer = User::where('role', 'advokat')->first();
        $client     = User::where('role', 'klien')->first();

        $case = LegalCase::create([
            'client_id'   => $client->id,
            'lawyer_id'   => $leadLawyer->id,
            'case_number' => 'TEST/VAL/006/' . time(),
            'case_type'   => 'Perdata — Gugatan',
            'title'       => 'Perkara Uji Validasi Berkas',
            'status'      => 'Proses',
            'started_at'  => now(),
        ]);

        $dangerousFile = UploadedFile::fake()->create('malicious_script.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($leadLawyer)->post(route('advokat.cases.collaboration.document', $case->id), [
            'name'          => 'Berkas Berbahaya',
            'document_type' => 'Dokumen Lainnya',
            'is_internal'   => 1,
            'file'          => $dangerousFile,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('documents', [
            'name' => 'Berkas Berbahaya',
        ]);
    }
}
