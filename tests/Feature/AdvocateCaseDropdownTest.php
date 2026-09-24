<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdvocateCaseDropdownTest extends TestCase
{
    use DatabaseTransactions;

    protected User $lawyerSupri;
    protected User $lawyerHana;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lawyerSupri = User::firstOrCreate(
            ['email' => 'advokat@sahabathukum.test'],
            ['name' => 'Supri', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->lawyerHana = User::firstOrCreate(
            ['email' => 'hana@sahabathukum.test'],
            ['name' => 'Hana Fauziah Balqis', 'password' => bcrypt('password'), 'role' => 'advokat', 'status' => 'aktif']
        );

        $this->client = User::firstOrCreate(
            ['email' => 'klien@sahabathukum.test'],
            ['name' => 'Riko Pratama', 'password' => bcrypt('password'), 'role' => 'klien', 'status' => 'aktif']
        );
    }

    public function test_dropdown_shows_eligible_consultations_and_excludes_cases_and_other_lawyers(): void
    {
        // 1. Consultation assigned to Supri without case (Dijadwalkan) -> ID 6 exists or create fresh
        $c1 = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Uji Kasus Dijadwalkan Supri',
            'description'  => 'Deskripsi uji',
            'status'       => 'Dijadwalkan',
        ]);

        // 2. Consultation assigned to Supri without case (Menunggu) -> ID 8 exists or create fresh
        $c2 = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Uji Kasus Menunggu Supri',
            'description'  => 'Deskripsi uji',
            'status'       => 'Menunggu',
        ]);

        // 3. Consultation assigned to Supri with existing case -> Must NOT appear
        $cWithCase = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Uji Kasus Sudah Punya Perkara',
            'description'  => 'Deskripsi uji',
            'status'       => 'Selesai',
        ]);
        LegalCase::create([
            'client_id'       => $this->client->id,
            'lawyer_id'       => $this->lawyerSupri->id,
            'consultation_id' => $cWithCase->id,
            'case_number'     => 'TEST/' . uniqid(),
            'case_type'       => 'Perdata — Gugatan',
            'title'           => 'Perkara Milik CWithCase',
            'status'          => 'Persiapan',
            'started_at'      => now(),
        ]);

        // 4. Consultation assigned to Hana -> Must NOT appear for Supri
        $cHana = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerHana->id,
            'problem_type' => 'Pidana',
            'title'        => 'Uji Kasus Milik Hana',
            'description'  => 'Deskripsi uji',
            'status'       => 'Dijadwalkan',
        ]);

        // 5. Unassigned consultation (lawyer_id null) -> Must NOT appear
        $cUnassigned = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => null,
            'problem_type' => 'Perdata',
            'title'        => 'Uji Kasus Belum Ditetapkan',
            'description'  => 'Deskripsi uji',
            'status'       => 'Menunggu',
        ]);

        // 6. Cancelled consultation -> Must NOT appear
        $cCancelled = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Uji Kasus Dibatalkan',
            'description'  => 'Deskripsi uji',
            'status'       => 'Dibatalkan',
        ]);

        // Act as lawyer Supri and visit cases page
        $response = $this->actingAs($this->lawyerSupri)->get('/advokat/perkara');
        $response->assertOk();
        $response->assertViewHas('availableConsultations');

        // Check dropdown options
        $response->assertSee('value="' . $c1->id . '"', false);
        $response->assertSee('Uji Kasus Dijadwalkan Supri');
        $response->assertSee('value="' . $c2->id . '"', false);
        $response->assertSee('Uji Kasus Menunggu Supri');

        // Assert excluded
        $response->assertDontSee('value="' . $cWithCase->id . '"', false);
        $response->assertDontSee('Uji Kasus Sudah Punya Perkara');
        $response->assertDontSee('value="' . $cHana->id . '"', false);
        $response->assertDontSee('Uji Kasus Milik Hana');
        $response->assertDontSee('value="' . $cUnassigned->id . '"', false);
        $response->assertDontSee('Uji Kasus Belum Ditetapkan');
        $response->assertDontSee('value="' . $cCancelled->id . '"', false);
        $response->assertDontSee('Uji Kasus Dibatalkan');
    }

    public function test_existing_seed_data_consultations_visibility_for_supri(): void
    {
        $cPerdata = Consultation::where('title', 'Dokumen Gugatan Perdata')->first();
        $cKontrak = Consultation::where('title', 'Kontrak Bisnis Bermasalah')->first();
        $cWithCase = Consultation::where('title', 'Sengketa Hak Waris')->first();

        $response = $this->actingAs($this->lawyerSupri)->get('/advokat/perkara');
        $response->assertOk();

        if ($cPerdata) {
            $response->assertSee('value="' . $cPerdata->id . '"', false);
            $response->assertSee('Dokumen Gugatan Perdata');
        }

        if ($cKontrak) {
            $response->assertSee('value="' . $cKontrak->id . '"', false);
            $response->assertSee('Kontrak Bisnis Bermasalah');
        }

        if ($cWithCase) {
            $response->assertDontSee('value="' . $cWithCase->id . '"', false);
        }
    }

    public function test_from_consultation_query_parameter_auto_selects_option(): void
    {
        $cPerdata = Consultation::where('lawyer_id', $this->lawyerSupri->id)
            ->whereDoesntHave('case')
            ->first();

        $response = $this->actingAs($this->lawyerSupri)->get('/advokat/perkara?from_consultation=' . $cPerdata->id);
        $response->assertOk();
        $response->assertSee('value="' . $cPerdata->id . '" selected', false);
    }

    public function test_cannot_create_second_case_from_same_consultation(): void
    {
        $c = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerSupri->id,
            'problem_type' => 'Perdata',
            'title'        => 'Uji Unik Dua Perkara',
            'description'  => 'Deskripsi uji',
            'status'       => 'Dijadwalkan',
        ]);

        // First case creation -> Succeeds
        $caseNum1 = 'TEST/1/' . uniqid();
        $res1 = $this->actingAs($this->lawyerSupri)->post('/advokat/perkara', [
            'consultation_id' => $c->id,
            'case_number'     => $caseNum1,
            'case_type'       => 'Perdata — Gugatan',
            'title'           => 'Perkara Pertama',
            'started_at'      => now()->toDateString(),
        ]);
        $res1->assertSessionHas('success');

        // Second case creation with same consultation_id -> Rejected (404 via findOrFail)
        $caseNum2 = 'TEST/2/' . uniqid();
        $res2 = $this->actingAs($this->lawyerSupri)->post('/advokat/perkara', [
            'consultation_id' => $c->id,
            'case_number'     => $caseNum2,
            'case_type'       => 'Perdata — Gugatan',
            'title'           => 'Perkara Kedua Gagal',
            'started_at'      => now()->toDateString(),
        ]);
        $res2->assertStatus(404);
    }

    public function test_cannot_create_case_from_other_lawyers_consultation(): void
    {
        $cHana = Consultation::create([
            'client_id'    => $this->client->id,
            'lawyer_id'    => $this->lawyerHana->id,
            'problem_type' => 'Pidana',
            'title'        => 'Konsultasi Hana',
            'description'  => 'Deskripsi uji',
            'status'       => 'Dijadwalkan',
        ]);

        // Supri attempts to create case from Hana's consultation -> 404
        $res = $this->actingAs($this->lawyerSupri)->post('/advokat/perkara', [
            'consultation_id' => $cHana->id,
            'case_number'     => 'TEST/THEFT/' . uniqid(),
            'case_type'       => 'Pidana Biasa',
            'title'           => 'Perkara Bajakan',
            'started_at'      => now()->toDateString(),
        ]);
        $res->assertStatus(404);
    }
}
