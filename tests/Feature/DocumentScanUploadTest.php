<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LegalCase;
use App\Models\Document;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Test integrasi backend untuk upload dokumen PDF (termasuk hasil Scanner).
 * Catatan: Fitur kamera dan interaksi canvas diuji secara manual di browser.
 */
class DocumentScanUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected User $clientRiko;
    protected User $otherClient;
    protected User $lawyerSupri;
    protected User $otherLawyer;
    protected LegalCase $case;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->clientRiko = User::firstOrCreate(
            ['email' => 'klien_test@sahabathukum.test'],
            ['name' => 'Riko Klien Test', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );

        $this->otherClient = User::firstOrCreate(
            ['email' => 'other_klien@sahabathukum.test'],
            ['name' => 'Klien Lain', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );

        $this->lawyerSupri = User::firstOrCreate(
            ['email' => 'advokat_test@sahabathukum.test'],
            ['name' => 'Supri Advokat Test', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->otherLawyer = User::firstOrCreate(
            ['email' => 'other_lawyer@sahabathukum.test'],
            ['name' => 'Advokat Lain', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->case = LegalCase::firstOrCreate(
            ['case_number' => 'TEST-SCAN-001'],
            [
                'client_id'  => $this->clientRiko->id,
                'lawyer_id'  => $this->lawyerSupri->id,
                'case_type'  => 'Perdata',
                'title'      => 'Sengketa Perjanjian Kerjasama',
                'status'     => 'Berjalan',
                'started_at' => now()->toDateString(),
            ]
        );
    }

    public function test_1_client_uploads_scanned_pdf_successfully(): void
    {
        $fakePdf = UploadedFile::fake()->create('KTP_Hasil_Scan.pdf', 350, 'application/pdf');

        $response = $this->actingAs($this->clientRiko)->post("/klien/perkara/{$this->case->id}/dokumen", [
            'name'          => 'KTP Klien Scan',
            'document_type' => 'Identitas Diri (KTP/SIM/Paspor)',
            'description'   => 'Dokumen KTP dipindai via Scanner Dokumen',
            'file'          => $fakePdf,
        ]);

        $response->assertRedirect("/klien/perkara/{$this->case->id}");
        $response->assertSessionHas('success');

        $doc = Document::where('case_id', $this->case->id)
            ->where('name', 'KTP Klien Scan')
            ->first();

        $this->assertNotNull($doc);
        $this->assertEquals('Menunggu Verifikasi', $doc->status);
        $this->assertFalse($doc->is_from_lawyer);
        $this->assertEquals($this->clientRiko->id, $doc->uploaded_by);

        Storage::disk('local')->assertExists($doc->file_path);
    }

    public function test_2_advocate_uploads_scanned_pdf_successfully(): void
    {
        $fakePdf = UploadedFile::fake()->create('Surat_Kuasa_Scan.pdf', 450, 'application/pdf');

        $response = $this->actingAs($this->lawyerSupri)->post("/advokat/perkara/{$this->case->id}/dokumen/upload", [
            'name'          => 'Surat Kuasa Khusus Scan',
            'document_type' => 'Surat Kuasa',
            'description'   => 'Surat kuasa fisik dipindai oleh Advokat',
            'file'          => $fakePdf,
        ]);

        $response->assertRedirect("/advokat/perkara/{$this->case->id}");
        $response->assertSessionHas('success');

        $doc = Document::where('case_id', $this->case->id)
            ->where('name', 'Surat Kuasa Khusus Scan')
            ->first();

        $this->assertNotNull($doc);
        $this->assertTrue($doc->is_from_lawyer);
        $this->assertEquals($this->lawyerSupri->id, $doc->uploaded_by);

        Storage::disk('local')->assertExists($doc->file_path);
    }

    public function test_3_client_cannot_upload_to_another_client_case(): void
    {
        $fakePdf = UploadedFile::fake()->create('Unauthorized.pdf', 100, 'application/pdf');

        // Other client tries to upload to Riko's case
        $response = $this->actingAs($this->otherClient)->post("/klien/perkara/{$this->case->id}/dokumen", [
            'name'          => 'Dokumen Ilegal',
            'document_type' => 'Dokumen Lainnya',
            'file'          => $fakePdf,
        ]);

        $response->assertStatus(404);
    }

    public function test_4_advocate_cannot_upload_to_unauthorized_case(): void
    {
        $fakePdf = UploadedFile::fake()->create('Unauthorized.pdf', 100, 'application/pdf');

        // Another lawyer tries to upload to Supri's case
        $response = $this->actingAs($this->otherLawyer)->post("/advokat/perkara/{$this->case->id}/dokumen/upload", [
            'name'          => 'Dokumen Ilegal',
            'document_type' => 'Dokumen Lainnya',
            'file'          => $fakePdf,
        ]);

        $response->assertStatus(404);
    }

    public function test_5_upload_exceeding_10mb_is_rejected(): void
    {
        // 11 MB file (exceeds 10240 KB limit)
        $oversizedPdf = UploadedFile::fake()->create('Too_Large.pdf', 11264, 'application/pdf');

        $response = $this->actingAs($this->clientRiko)->post("/klien/perkara/{$this->case->id}/dokumen", [
            'name'          => 'File Raksasa',
            'document_type' => 'Dokumen Lainnya',
            'file'          => $oversizedPdf,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_6_advocate_documents_page_has_no_scanner_button_or_modal_but_retains_minta_and_unggah(): void
    {
        $response = $this->actingAs($this->lawyerSupri)->get('/advokat/dokumen');

        $response->assertOk();
        $response->assertSee('Minta Dokumen Klien');
        $response->assertSee('Unggah Dokumen Advokat');
        $response->assertDontSee('Scan Dokumen Advokat');
        $response->assertDontSee('camScannerModal');
        $response->assertDontSee('jspdf.umd.min.js');
    }

    public function test_7_advocate_case_detail_page_has_no_scan_document_button(): void
    {
        $response = $this->actingAs($this->lawyerSupri)->get("/advokat/perkara/{$this->case->id}");

        $response->assertOk();
        $response->assertDontSee('Scan Dokumen');
        $response->assertDontSee('camScannerModal');
    }

    public function test_8_advocate_can_request_document_from_client(): void
    {
        $response = $this->actingAs($this->lawyerSupri)->post("/advokat/perkara/{$this->case->id}/dokumen", [
            'title'         => 'Akta Kelahiran Pemohon',
            'document_type' => 'Dokumen Pendukung',
            'description'   => 'Mohon unggah akta kelahiran asli.',
            'priority'      => 'Tinggi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('document_requests', [
            'case_id'   => $this->case->id,
            'client_id' => $this->clientRiko->id,
            'title'     => 'Akta Kelahiran Pemohon',
        ]);
    }

    public function test_9_advocate_can_verify_and_reject_document(): void
    {
        $fakePdf = UploadedFile::fake()->create('Bukti_Transfer.pdf', 200, 'application/pdf');
        $filePath = $fakePdf->store('documents/test', 'local');

        $doc = Document::create([
            'case_id'       => $this->case->id,
            'client_id'     => $this->clientRiko->id,
            'lawyer_id'     => $this->lawyerSupri->id,
            'uploaded_by'   => $this->clientRiko->id,
            'name'          => 'Bukti Transfer Bank',
            'file_name'     => 'Bukti_Transfer.pdf',
            'file_path'     => $filePath,
            'file_size'     => 200 * 1024,
            'file_type'     => 'pdf',
            'mime_type'     => 'application/pdf',
            'document_type' => 'Bukti Pembayaran',
            'status'        => 'Menunggu Verifikasi',
        ]);

        // Verifikasi dokumen
        $resVerify = $this->actingAs($this->lawyerSupri)->post("/advokat/dokumen/{$doc->id}/verifikasi");
        $resVerify->assertSessionHas('success');
        $this->assertEquals('Terverifikasi', $doc->fresh()->status);

        // Tolak dokumen
        $resReject = $this->actingAs($this->lawyerSupri)->post("/advokat/dokumen/{$doc->id}/tolak", [
            'rejection_reason' => 'Bukti transfer buram dan nominal tidak terlihat jelas.',
        ]);
        $resReject->assertSessionHas('success');
        $this->assertEquals('Ditolak', $doc->fresh()->status);
        $this->assertEquals('Bukti transfer buram dan nominal tidak terlihat jelas.', $doc->fresh()->rejection_reason);
    }

    public function test_10_client_document_features_and_modal_remain_functional(): void
    {
        $response = $this->actingAs($this->clientRiko)->get('/klien/dokumen');

        $response->assertOk();
        $response->assertSee('Unggah Dokumen Baru');
        $response->assertSee('Scan Dokumen');
        $response->assertSee('camScannerModal');
        $response->assertSee('jspdf.umd.min.js');
    }
}
