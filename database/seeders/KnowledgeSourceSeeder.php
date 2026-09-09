<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KnowledgeSource;
use App\Models\User;

class KnowledgeSourceSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin?->id;

        $sources = [
            [
                'title'       => 'Persyaratan Dokumen untuk Konsultasi Hukum',
                'description' => 'Panduan dokumen identitas, bukti kronologi, dan dokumen pendukung yang wajib disiapkan klien sebelum melakukan konsultasi.',
                'source_type' => 'Prosedur Layanan',
                'file_path'   => null,
                'content'     => "Untuk melakukan konsultasi hukum di kantor Sahabat Hukum, klien diharapkan menyiapkan beberapa dokumen pokok:\n"
                               . "1. Dokumen Identitas Diri: KTP (Kartu Tanda Penduduk) atau Paspor/SIM yang masih berlaku, dan Kartu Keluarga (KK) jika terkait masalah keluarga atau waris.\n"
                               . "2. Dokumen Bukti & Surat Perjanjian: Bukti tertulis yang relevan dengan kasus, seperti perjanjian/kontrak kerja sama, akta jual beli, sertifikat tanah, kuitansi bukti transfer pembayaran, atau surat somasi.\n"
                               . "3. Catatan Kronologi Kejadian: Ringkasan urutan fakta dan peristiwa yang terjadi beserta tanggal dan pihak-pihak yang terlibat.\n"
                               . "4. Dokumen Tambahan: Surat Kuasa jika dikuasakan kepada perwakilan, dokumen korespondensi (email, pesan chat, surat menyurat resmi), dan surat peringatan jika ada.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Prosedur dan Tahapan Pengajuan Gugatan Perdata',
                'description' => 'Tata cara pendaftaran, mediasi pengadilan, hingga tahap persidangan perdata berdasarkan HIR dan Perma No. 1 Tahun 2016.',
                'source_type' => 'Hukum Acara Perdata',
                'file_path'   => null,
                'content'     => "Berdasarkan Hukum Acara Perdata (HIR/RBg) dan regulasi Mahkamah Agung, tahapan pengajuan gugatan perdata terdiri dari:\n"
                               . "1. Penyusunan Surat Gugatan: Berisi identitas para pihak (Penggugat dan Tergugat), Posita (uraian kronologi dan dasar hukum dalil gugatan), serta Petitum (hal-hal yang dimohonkan untuk diputuskan oleh majelis hakim).\n"
                               . "2. Pendaftaran dan Pembayaran Panjar: Gugatan didaftarkan di Kepaniteraan Pengadilan Negeri yang berwenang (secara langsung atau sistem e-Court) dan membayar Surat Kuasa Untuk Membayar (SKUM).\n"
                               . "3. Penetapan Majelis Hakim & Pemanggilan Sidang: Ketua Pengadilan menunjuk majelis hakim dan jurusita melakukan pemanggilan resmi kepada para pihak.\n"
                               . "4. Sidang Mediasi Wajib: Sesuai Perma No. 1 Tahun 2016, para pihak wajib menempuh mediasi selama maksimal 30 hari yang dipandu oleh Hakim Mediator.\n"
                               . "5. Persidangan Pokok Perkara: Jika mediasi gagal, persidangan dilanjutkan dengan pembacaan gugatan, jawaban tergugat (termasuk eksepsi/rekonvensi), replik, duplik, pembuktian surat dan saksi/ahli, kesimpulan, hingga pembacaan Putusan Pengadilan.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Ketentuan dan Pengertian Surat Kuasa Menurut KUHPerdata',
                'description' => 'Definisi, unsur, dan jenis surat kuasa berdasarkan Pasal 1792 hingga Pasal 1796 Kitab Undang-Undang Hukum Perdata.',
                'source_type' => 'KUHPerdata',
                'file_path'   => null,
                'content'     => "Menurut Pasal 1792 Kitab Undang-Undang Hukum Perdata (KUHPerdata), pemberian kuasa adalah suatu persetujuan dengan mana seorang memberikan kekuasaan kepada seorang lain, yang menerimanya, untuk atas namanya menyelenggarakan suatu urusan.\n\n"
                               . "Jenis Surat Kuasa dalam praktik hukum:\n"
                               . "1. Kuasa Umum (Pasal 1796 KUHPerdata): Memberikan wewenang sebatas tindakan pengurusan atau administrasi biasa, tidak dapat digunakan untuk memindahtangankan barang atau beracara di pengadilan.\n"
                               . "2. Kuasa Khusus (Pasal 1795 KUHPerdata & SEMA No. 1 Tahun 1971): Surat kuasa yang secara spesifik menyebutkan tindakan apa saja yang boleh dilakukan penerima kuasa, seperti mewakili klien di muka persidangan pengadilan tertentu, membuat perdamaian, atau mengajukan upaya hukum banding/kasasi.\n"
                               . "3. Kuasa Substitusi: Hak yang diberikan kepada penerima kuasa untuk menunjuk orang lain sebagai pengganti guna menjalankan kuasa tersebut.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Jangka Waktu Penyelesaian Perkara di Pengadilan (SEMA No. 2 Tahun 2014)',
                'description' => 'Pedoman jangka waktu dan asas peradilan cepat, sederhana, dan biaya ringan pada peradilan tingkat pertama dan banding.',
                'source_type' => 'Surat Edaran Mahkamah Agung',
                'file_path'   => null,
                'content'     => "Berdasarkan Surat Edaran Mahkamah Agung (SEMA) No. 2 Tahun 2014 tentang Penyelesaian Perkara di Pengadilan Tingkat Pertama dan Tingkat Banding:\n"
                               . "1. Penyelesaian perkara perdata maupun pidana pada Pengadilan Tingkat Pertama (Pengadilan Negeri) harus diselesaikan dalam waktu paling lama 5 (lima) bulan terhitung sejak perkara didaftarkan.\n"
                               . "2. Penyelesaian perkara pada Pengadilan Tingkat Banding (Pengadilan Tinggi) diselesaikan dalam waktu paling lama 3 (tiga) bulan.\n"
                               . "3. Untuk perkara perdata sederhana (Small Claim Court berdasarkan Perma No. 2 Tahun 2015 jo. Perma No. 4 Tahun 2019), proses peradilan diselesaikan maksimal dalam waktu 25 hari kerja sejak sidang pertama.\n"
                               . "4. Jika perkara memerlukan waktu lebih dari batas tersebut karena hambatan geografis pemanggilan saksi atau kerumitan perkara, majelis hakim wajib membuat laporan tertulis kepada Ketua Pengadilan.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Hak, Kewajiban, dan Kerahasiaan Hubungan Advokat dengan Klien',
                'description' => 'Prinsip kerahasiaan hubungan hukum advokat dan klien serta hak imunitas berdasarkan UU No. 18 Tahun 2003 tentang Advokat.',
                'source_type' => 'Undang-Undang',
                'file_path'   => null,
                'content'     => "Berdasarkan Undang-Undang No. 18 Tahun 2003 tentang Advokat:\n"
                               . "1. Kerahasiaan Klien (Pasal 19): Advokat wajib merahasiakan segala sesuatu yang diketahui atau diperoleh dari kliennya karena hubungan profesinya, kecuali ditentukan lain oleh undang-undang. Dokumen dan informasi yang diserahkan klien kepada advokat dilindungi undang-undang.\n"
                               . "2. Hak Imunitas Advokat (Pasal 16): Advokat tidak dapat dituntut baik secara perdata maupun pidana dalam menjalankan tugas profesinya dengan iktikad baik untuk kepentingan pembelaan klien dalam sidang pengadilan.\n"
                               . "3. Bantuan Hukum Cuma-Cuma (Pro Bono) (Pasal 22): Advokat wajib memberikan bantuan hukum secara cuma-cuma kepada pencari keadilan yang tidak mampu sesuai ketentuan perundang-undangan.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Ketentuan Pokok Tindak Pidana dan Asas Legalitas dalam KUHP',
                'description' => 'Prinsip dasar pertanggungjawaban pidana, asas legalitas Pasal 1 KUHP, dan unsur tindak pidana.',
                'source_type' => 'KUHP',
                'file_path'   => null,
                'content'     => "Berdasarkan Kitab Undang-Undang Hukum Pidana (KUHP):\n"
                               . "1. Asas Legalitas (Pasal 1 ayat 1 KUHP): Tiada suatu perbuatan dapat dipidana, melainkan atas kekuatan aturan perundang-undangan pidana yang telah ada sebelum perbuatan dilakukan (Nullum delictum nulla poena sine praevia lege poenali).\n"
                               . "2. Unsur Tindak Pidana: Suatu peristiwa dapat dikategorikan sebagai tindak pidana jika memenuhi unsur objektif (perbuatan melawan hukum yang dilarang undang-undang) dan unsur subjektif (kesalahan berupa kesengajaan / dolus atau kealpaan / culpa dari pelaku yang mampu bertanggung jawab).\n"
                               . "3. Hak Tersangka/Terdakwa: Tersangka atau terdakwa berhak mendapatkan bantuan hukum dari advokat/penasihat hukum sejak saat ditangkap atau ditahan pada setiap tingkat pemeriksaan menurut KUHAP.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ]
        ];

        foreach ($sources as $src) {
            KnowledgeSource::updateOrCreate(
                ['title' => $src['title']],
                $src
            );
        }
    }
}
