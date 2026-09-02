<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ClientProfile;
use App\Models\LawyerProfile;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\CaseProgress;
use App\Models\Document;
use App\Models\Schedule;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // ─── ADMIN ───────────────────────────────────────────────────────────
        User::firstOrCreate(['email' => 'admin@sahabathukum.test'], [
            'name'     => 'Administrator',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'status'   => 'aktif',
        ]);

        // ─── ADVOKAT ─────────────────────────────────────────────────────────
        $advokat = User::firstOrCreate(['email' => 'advokat@sahabathukum.test'], [
            'name'     => 'Adv. Dewi Kusuma, S.H.',
            'password' => Hash::make('password'),
            'role'     => 'advokat',
            'status'   => 'aktif',
        ]);
        LawyerProfile::firstOrCreate(['user_id' => $advokat->id], [
            'specialization' => 'Perdata & Pidana',
            'phone'          => '081200001111',
        ]);

        // ─── KLIEN ───────────────────────────────────────────────────────────
        $riko = User::firstOrCreate(['email' => 'klien@sahabathukum.test'], [
            'name'     => 'Riko Pratama',
            'password' => Hash::make('password'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);
        ClientProfile::firstOrCreate(['user_id' => $riko->id], [
            'phone'   => '0812-3456-7890',
            'address' => 'Jl. Merdeka No. 10, Subang',
        ]);

        $siti = User::firstOrCreate(['email' => 'siti@sahabathukum.test'], [
            'name'     => 'Siti Rahmawati',
            'password' => Hash::make('password'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);
        ClientProfile::firstOrCreate(['user_id' => $siti->id], [
            'phone'   => '0813-1122-3344',
            'address' => 'Jl. Pahlawan No. 5, Subang',
        ]);

        $ahmad = User::firstOrCreate(['email' => 'ahmad@sahabathukum.test'], [
            'name'     => 'Ahmad Fauzi',
            'password' => Hash::make('password'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);
        ClientProfile::firstOrCreate(['user_id' => $ahmad->id], [
            'phone'   => '0814-5566-7788',
            'address' => 'Jl. Diponegoro No. 12, Subang',
        ]);

        $hendra = User::firstOrCreate(['email' => 'hendra@sahabathukum.test'], [
            'name'     => 'Hendra Gunawan',
            'password' => Hash::make('password'),
            'role'     => 'klien',
            'status'   => 'aktif',
        ]);
        ClientProfile::firstOrCreate(['user_id' => $hendra->id], [
            'phone'   => '0815-9900-1122',
            'address' => 'Jl. Sudirman No. 8, Subang',
        ]);

        // ─── KONSULTASI ───────────────────────────────────────────────────────
        // Riko – Dijadwalkan (Dokumen Gugatan Perdata)
        $konsultasi1 = Consultation::firstOrCreate(
            ['client_id' => $riko->id, 'title' => 'Dokumen Gugatan Perdata'],
            [
                'lawyer_id'    => $advokat->id,
                'problem_type' => 'Perdata',
                'description'  => 'Klien memerlukan konsultasi lebih lanjut terkait dokumen gugatan yang telah diajukan. Klien ingin mengetahui perkembangan terbaru dan dokumen tambahan yang diperlukan.',
                'scheduled_at' => Carbon::parse('2025-08-15 10:00'),
                'status'       => 'Dijadwalkan',
                'result_notes' => null,
                'created_at'   => Carbon::parse('2025-08-10'),
                'updated_at'   => Carbon::parse('2025-08-10'),
            ]
        );

        // Siti – Menunggu (Sengketa Hak Waris)
        $konsultasi2 = Consultation::firstOrCreate(
            ['client_id' => $siti->id, 'title' => 'Sengketa Hak Waris'],
            [
                'lawyer_id'    => $advokat->id,
                'problem_type' => 'Perdata',
                'description'  => 'Klien mengalami sengketa hak waris dengan saudara kandung mengenai tanah peninggalan orang tua.',
                'scheduled_at' => null,
                'status'       => 'Menunggu',
                'result_notes' => null,
                'created_at'   => Carbon::parse('2025-08-12'),
                'updated_at'   => Carbon::parse('2025-08-12'),
            ]
        );

        // Hendra – Menunggu (Kontrak Bisnis Bermasalah)
        $konsultasi3 = Consultation::firstOrCreate(
            ['client_id' => $hendra->id, 'title' => 'Kontrak Bisnis Bermasalah'],
            [
                'lawyer_id'    => $advokat->id,
                'problem_type' => 'Perdata',
                'description'  => 'Klien menghadapi permasalahan kontrak bisnis yang tidak dipenuhi oleh pihak mitra.',
                'scheduled_at' => null,
                'status'       => 'Menunggu',
                'result_notes' => null,
                'created_at'   => Carbon::parse('2025-08-11'),
                'updated_at'   => Carbon::parse('2025-08-11'),
            ]
        );

        // Riko – Selesai (Sengketa Tanah Warisan)
        $konsultasi4 = Consultation::firstOrCreate(
            ['client_id' => $riko->id, 'title' => 'Sengketa Tanah Warisan'],
            [
                'lawyer_id'    => $advokat->id,
                'problem_type' => 'Perdata',
                'description'  => 'Konsultasi awal mengenai sengketa tanah warisan. Advokat mempelajari pokok permasalahan.',
                'scheduled_at' => Carbon::parse('2025-07-25 10:00'),
                'status'       => 'Selesai',
                'result_notes' => 'Konsultasi telah selesai. Advokat menyarankan untuk melanjutkan ke proses perkara perdata. Dokumen yang diperlukan: KTP, Kartu Keluarga, Sertifikat Tanah, dan Surat Wasiat.',
                'created_at'   => Carbon::parse('2025-07-22'),
                'updated_at'   => Carbon::parse('2025-07-25'),
            ]
        );

        // Ahmad – Selesai
        $konsultasi5 = Consultation::firstOrCreate(
            ['client_id' => $ahmad->id, 'title' => 'Kasus Penggelapan Dana'],
            [
                'lawyer_id'    => $advokat->id,
                'problem_type' => 'Pidana',
                'description'  => 'Klien dilaporkan atas dugaan penggelapan dana oleh rekan bisnis.',
                'scheduled_at' => Carbon::parse('2025-08-01 09:00'),
                'status'       => 'Selesai',
                'result_notes' => 'Konsultasi selesai. Akan dilanjutkan ke tahap penyidikan. Diperlukan dokumen bukti transaksi dan rekening koran.',
                'created_at'   => Carbon::parse('2025-07-30'),
                'updated_at'   => Carbon::parse('2025-08-01'),
            ]
        );

        // ─── PERKARA ─────────────────────────────────────────────────────────
        // Perkara 1: Riko – Perdata Gugatan (Persidangan)
        $perkara1 = LegalCase::firstOrCreate(
            ['case_number' => '023/Pdt.G/2025/PN.Sbg'],
            [
                'client_id'       => $riko->id,
                'lawyer_id'       => $advokat->id,
                'consultation_id' => $konsultasi4->id,
                'case_type'       => 'Perdata — Gugatan',
                'title'           => 'Sengketa Tanah Warisan',
                'summary'         => 'Perkara sengketa kepemilikan tanah warisan seluas 1.200 m² di Kecamatan Subang antara klien dengan saudara kandung.',
                'status'          => 'Persidangan',
                'started_at'      => Carbon::parse('2025-07-22'),
                'created_at'      => Carbon::parse('2025-07-22'),
                'updated_at'      => Carbon::parse('2025-08-12'),
            ]
        );

        // Perkara 2: Ahmad – Pidana Biasa (Penyidikan)
        $perkara2 = LegalCase::firstOrCreate(
            ['case_number' => '017/Pid.B/2025/PN.Sbg'],
            [
                'client_id'       => $ahmad->id,
                'lawyer_id'       => $advokat->id,
                'consultation_id' => $konsultasi5->id,
                'case_type'       => 'Pidana Biasa',
                'title'           => 'Kasus Penggelapan Dana',
                'summary'         => 'Perkara pidana atas dugaan penggelapan dana oleh klien yang dilaporkan oleh mitra bisnis.',
                'status'          => 'Penyidikan',
                'started_at'      => Carbon::parse('2025-08-01'),
                'created_at'      => Carbon::parse('2025-08-01'),
                'updated_at'      => Carbon::parse('2025-08-10'),
            ]
        );

        // Perkara 3: Siti – Perdata Permohonan (Persiapan)
        $perkara3 = LegalCase::firstOrCreate(
            ['case_number' => '031/Pdt.P/2025/PN.Sbg'],
            [
                'client_id'       => $siti->id,
                'lawyer_id'       => $advokat->id,
                'consultation_id' => $konsultasi2->id,
                'case_type'       => 'Perdata — Permohonan',
                'title'           => 'Permohonan Penetapan Ahli Waris',
                'summary'         => 'Permohonan penetapan ahli waris atas harta peninggalan almarhum ayah klien.',
                'status'          => 'Persiapan',
                'started_at'      => Carbon::parse('2025-08-10'),
                'created_at'      => Carbon::parse('2025-08-10'),
                'updated_at'      => Carbon::parse('2025-08-12'),
            ]
        );

        // ─── PERKEMBANGAN PERKARA ─────────────────────────────────────────────
        // Perkara 1 – Riko
        if ($perkara1->progress()->count() === 0) {
            CaseProgress::insert([
                [
                    'case_id'       => $perkara1->id,
                    'title'         => 'Konsultasi awal dilaksanakan',
                    'description'   => 'Klien melakukan konsultasi pertama di kantor. Advokat mempelajari pokok permasalahan sengketa tanah.',
                    'progress_date' => '2025-07-20',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-07-20'),
                    'updated_at'    => Carbon::parse('2025-07-20'),
                ],
                [
                    'case_id'       => $perkara1->id,
                    'title'         => 'Perkara dibuat berdasarkan hasil konsultasi',
                    'description'   => 'Adv. Dewi Kusuma, S.H. membuat perkara berdasarkan hasil konsultasi mengenai sengketa tanah warisan.',
                    'progress_date' => '2025-07-22',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-07-22'),
                    'updated_at'    => Carbon::parse('2025-07-22'),
                ],
                [
                    'case_id'       => $perkara1->id,
                    'title'         => 'Surat gugatan diajukan ke Pengadilan Negeri Subang',
                    'description'   => 'Berkas gugatan resmi telah didaftarkan di kepaniteraan Pengadilan Negeri Subang.',
                    'progress_date' => '2025-08-05',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-08-05'),
                    'updated_at'    => Carbon::parse('2025-08-05'),
                ],
                [
                    'case_id'       => $perkara1->id,
                    'title'         => 'Perkara memasuki tahap persidangan',
                    'description'   => 'Sidang perdana dijadwalkan pada 19 Agustus 2025 di Pengadilan Negeri Subang. Semua pihak telah menerima surat panggilan.',
                    'progress_date' => '2025-08-12',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-08-12'),
                    'updated_at'    => Carbon::parse('2025-08-12'),
                ],
            ]);
        }

        // Perkara 2 – Ahmad
        if ($perkara2->progress()->count() === 0) {
            CaseProgress::insert([
                [
                    'case_id'       => $perkara2->id,
                    'title'         => 'Perkara dibuat, tahap penyidikan dimulai',
                    'description'   => 'Berkas perkara diterima. Advokat mempersiapkan dokumen untuk tahap penyidikan.',
                    'progress_date' => '2025-08-01',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-08-01'),
                    'updated_at'    => Carbon::parse('2025-08-01'),
                ],
                [
                    'case_id'       => $perkara2->id,
                    'title'         => 'BAP klien selesai dilakukan',
                    'description'   => 'Berita Acara Pemeriksaan klien telah selesai dilakukan di Polres Subang.',
                    'progress_date' => '2025-08-08',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-08-08'),
                    'updated_at'    => Carbon::parse('2025-08-08'),
                ],
            ]);
        }

        // Perkara 3 – Siti
        if ($perkara3->progress()->count() === 0) {
            CaseProgress::insert([
                [
                    'case_id'       => $perkara3->id,
                    'title'         => 'Perkara dibuat, persiapan berkas dimulai',
                    'description'   => 'Advokat mulai mempersiapkan berkas permohonan penetapan ahli waris.',
                    'progress_date' => '2025-08-10',
                    'created_by'    => $advokat->id,
                    'created_at'    => Carbon::parse('2025-08-10'),
                    'updated_at'    => Carbon::parse('2025-08-10'),
                ],
            ]);
        }

        // ─── DOKUMEN ─────────────────────────────────────────────────────────
        // Dokumen untuk Perkara 1 – Riko
        if ($perkara1->documents()->count() === 0) {
            Document::insert([
                [
                    'case_id'     => $perkara1->id,
                    'client_id'   => $riko->id,
                    'lawyer_id'   => $advokat->id,
                    'name'        => 'Kartu Tanda Penduduk',
                    'description' => 'KTP asli dan fotokopi',
                    'file_path'   => null,
                    'due_date'    => '2025-07-30',
                    'priority'    => 'Tinggi',
                    'status'      => 'Sudah Diterima',
                    'created_at'  => Carbon::parse('2025-07-22'),
                    'updated_at'  => Carbon::parse('2025-07-28'),
                ],
                [
                    'case_id'     => $perkara1->id,
                    'client_id'   => $riko->id,
                    'lawyer_id'   => $advokat->id,
                    'name'        => 'Surat Kuasa',
                    'description' => 'Surat kuasa bermaterai untuk advokat',
                    'file_path'   => null,
                    'due_date'    => '2025-07-30',
                    'priority'    => 'Tinggi',
                    'status'      => 'Sudah Diterima',
                    'created_at'  => Carbon::parse('2025-07-22'),
                    'updated_at'  => Carbon::parse('2025-07-28'),
                ],
                [
                    'case_id'     => $perkara1->id,
                    'client_id'   => $riko->id,
                    'lawyer_id'   => $advokat->id,
                    'name'        => 'Kartu Keluarga',
                    'description' => 'Kartu keluarga terbaru',
                    'file_path'   => null,
                    'due_date'    => '2025-08-08',
                    'priority'    => 'Normal',
                    'status'      => 'Belum Diunggah',
                    'created_at'  => Carbon::parse('2025-07-22'),
                    'updated_at'  => Carbon::parse('2025-07-22'),
                ],
                [
                    'case_id'     => $perkara1->id,
                    'client_id'   => $riko->id,
                    'lawyer_id'   => $advokat->id,
                    'name'        => 'Bukti Kepemilikan',
                    'description' => 'Sertifikat atau bukti kepemilikan tanah',
                    'file_path'   => null,
                    'due_date'    => '2025-08-08',
                    'priority'    => 'Tinggi',
                    'status'      => 'Belum Diunggah',
                    'created_at'  => Carbon::parse('2025-07-22'),
                    'updated_at'  => Carbon::parse('2025-07-22'),
                ],
            ]);
        }

        // ─── JADWAL ──────────────────────────────────────────────────────────
        $today = Carbon::today();
        if (Schedule::where('lawyer_id', $advokat->id)->count() === 0) {
            Schedule::insert([
                [
                    'lawyer_id'       => $advokat->id,
                    'client_id'       => $riko->id,
                    'consultation_id' => $konsultasi4->id,
                    'case_id'         => $perkara1->id,
                    'title'           => 'Sidang Perkara 023/Pdt',
                    'description'     => 'Sidang perdana perkara sengketa tanah warisan.',
                    'start_at'        => $today->copy()->setTime(9, 0),
                    'end_at'          => $today->copy()->setTime(12, 0),
                    'location'        => 'Pengadilan Negeri Subang',
                    'status'          => 'Aktif',
                    'created_at'      => Carbon::parse('2025-08-12'),
                    'updated_at'      => Carbon::parse('2025-08-12'),
                ],
                [
                    'lawyer_id'       => $advokat->id,
                    'client_id'       => $riko->id,
                    'consultation_id' => $konsultasi1->id,
                    'case_id'         => null,
                    'title'           => 'Konsultasi — Riko Pratama',
                    'description'     => 'Konsultasi lanjutan mengenai dokumen gugatan perdata.',
                    'start_at'        => $today->copy()->setTime(11, 0),
                    'end_at'          => $today->copy()->setTime(12, 0),
                    'location'        => 'Kantor',
                    'status'          => 'Aktif',
                    'created_at'      => Carbon::parse('2025-08-10'),
                    'updated_at'      => Carbon::parse('2025-08-10'),
                ],
            ]);
        }

        $this->command->info('✅ Demo data seeded!');
        $this->command->line('   Admin   : admin@sahabathukum.test / password');
        $this->command->line('   Advokat : advokat@sahabathukum.test / password');
        $this->command->line('   Klien   : klien@sahabathukum.test / password');
        $this->command->line('   + Siti, Ahmad, Hendra juga di-seed');
    }
}
