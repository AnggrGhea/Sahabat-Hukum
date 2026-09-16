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
                'title'       => 'UU No. 23 Tahun 2004 tentang Penghapusan Kekerasan Dalam Rumah Tangga (KDRT)',
                'description' => 'Ketentuan komprehensif mengenai definisi KDRT, bentuk kekerasan, lingkup rumah tangga, hak perlindungan korban, dan ancaman pidana.',
                'source_type' => 'Undang-Undang',
                'file_path'   => null,
                'content'     => "Berdasarkan Undang-Undang Republik Indonesia Nomor 23 Tahun 2004 tentang Penghapusan Kekerasan Dalam Rumah Tangga (UU PKDRT):\n\n"
                               . "1. Pengertian KDRT (Pasal 1 angka 1):\n"
                               . "Kekerasan dalam Rumah Tangga adalah setiap perbuatan terhadap seseorang terutama perempuan, yang berakibat timbulnya kesengsaraan atau penderitaan secara fisik, seksual, psikologis, dan/atau penelantaran rumah tangga termasuk ancaman untuk melakukan perbuatan, pemaksaan, atau perampasan kemerdekaan secara melawan hukum dalam lingkup rumah tangga.\n\n"
                               . "2. Lingkup Rumah Tangga (Pasal 2 ayat 1):\n"
                               . "Lingkup rumah tangga meliputi:\n"
                               . "a. Suami, isteri, dan anak;\n"
                               . "b. Orang-orang yang mempunyai hubungan kekeluargaan karena hubungan darah, perkawinan, persusuan, pengasuhan, dan perwalian, yang menetap dalam rumah tangga; dan/atau\n"
                               . "c. Orang yang bekerja membantu rumah tangga dan menetap dalam rumah tangga tersebut.\n\n"
                               . "3. Bentuk-Bentuk Kekerasan Dalam Rumah Tangga (Pasal 5):\n"
                               . "Setiap orang dilarang melakukan kekerasan dalam rumah tangga terhadap orang dalam lingkup rumah tangganya, dengan cara:\n"
                               . "- Kekerasan fisik (Pasal 6): perbuatan yang mengakibatkan rasa sakit, jatuh sakit, atau luka berat.\n"
                               . "- Kekerasan psikis (Pasal 7): perbuatan yang mengakibatkan ketakutan, hilangnya rasa percaya diri, hilangnya kemampuan untuk bertindak, rasa tidak berdaya, dan/atau penderitaan psikis berat.\n"
                               . "- Kekerasan seksual (Pasal 8): pemaksaan hubungan seksual terhadap orang yang menetap dalam lingkup rumah tangga atau pemaksaan hubungan seksual untuk tujuan komersial/tujuan tertentu.\n"
                               . "- Penelantaran rumah tangga (Pasal 9): menelantarkan orang dalam lingkup rumah tangganya, padahal menurut hukum atau persetujuan ia wajib memberikan kehidupan, perawatan, atau pemeliharaan.\n\n"
                               . "4. Hak-Hak Korban KDRT (Pasal 10):\n"
                               . "Korban KDRT berhak mendapatkan perlindungan dari pihak keluarga, kepolisian, kejaksaan, pengadilan, advokat, lembaga sosial, atau pihak lainnya; pelayanan kesehatan sesuai kebutuhan medis; penanganan khusus berkaitan dengan kerahasiaan korban; pendampingan oleh pekerja sosial dan bantuan hukum; serta pelayanan bimbingan rohani.\n\n"
                               . "5. Ketentuan Perlindungan Korban:\n"
                               . "- Perlindungan Sementara (Pasal 16-20): Diberikan oleh kepolisian dalam waktu 1x24 jam sejak mengetahui/menerima laporan untuk jangka waktu maksimal 30 hari.\n"
                               . "- Surat Perintah Perlindungan (Pasal 21-25): Dikeluarkan oleh Pengadilan Negeri setelah permohonan diajukan korban atau kuasa hukumnya untuk jangka waktu maksimal 1 tahun dan dapat diperpanjang.\n\n"
                               . "6. Ketentuan Pidana Pokok (Pasal 44 - 49):\n"
                               . "- Kekerasan fisik biasa diancam pidana penjara paling lama 5 tahun atau denda paling banyak Rp15.000.000,00 (Pasal 44 ayat 1).\n"
                               . "- Kekerasan fisik yang menyebabkan luka berat dipidana penjara paling lama 10 tahun (Pasal 44 ayat 2), dan jika menyebabkan kematian dipidana penjara paling lama 15 tahun (Pasal 44 ayat 3).\n"
                               . "- Kekerasan fisik ringan tanpa halangan menjalankan mata pencaharian/kegiatan sehari-hari dipidana penjara paling lama 4 bulan atau denda Rp5.000.000,00 dan merupakan delik aduan (Pasal 44 ayat 4).\n"
                               . "- Kekerasan psikis dipidana penjara paling lama 3 tahun atau denda paling banyak Rp9.000.000,00 (Pasal 45 ayat 1).\n"
                               . "- Penelantaran rumah tangga dipidana penjara paling lama 3 tahun atau denda paling banyak Rp15.000.000,00 (Pasal 49).",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Ketentuan Hukum Perjanjian dan Syarat Sah Perjanjian (KUHPerdata)',
                'description' => 'Ketentuan perikatan, syarat sahnya perjanjian Pasal 1320 KUHPerdata, asas konsensualisme, pacta sunt servanda, dan akibat wanprestasi.',
                'source_type' => 'KUHPerdata',
                'file_path'   => null,
                'content'     => "Berdasarkan Buku III Kitab Undang-Undang Hukum Perdata (KUHPerdata) tentang Perikatan:\n\n"
                               . "1. Pengertian Perjanjian (Pasal 1313 KUHPerdata):\n"
                               . "Suatu perjanjian adalah suatu perbuatan dengan mana satu orang atau lebih mengikatkan dirinya terhadap satu orang lain atau lebih.\n\n"
                               . "2. Syarat Sah Perjanjian (Pasal 1320 KUHPerdata):\n"
                               . "Supaya terjadi persetujuan yang sah, perlu dipenuhi 4 (empat) syarat pokok:\n"
                               . "1. Kesepakatan mereka yang mengikatkan dirinya (syarat subjektif: tanpa paksaan/dwang, penipuan/bedrog, atau kekhilafan/dwaling - Pasal 1321);\n"
                               . "2. Kecakapan untuk membuat suatu perikatan (syarat subjektif: dewasa, tidak di bawah pengampuan - Pasal 1329 jo. Pasal 1330);\n"
                               . "3. Suatu pokok persoalan tertentu (syarat objektif: barang yang menjadi objek perjanjian harus dapat ditentukan jenisnya - Pasal 1332-1334);\n"
                               . "4. Suatu sebab yang halal (syarat objektif: tidak dilarang oleh undang-undang, tidak berlawanan dengan kesusilaan baik atau ketertiban umum - Pasal 1335 jo. Pasal 1337).\n\n"
                               . "Konsekuensi Hukum Pembatalan:\n"
                               . "- Tidak terpenuhinya syarat subjektif (1 & 2) mengakibatkan perjanjian DAPAT DIBATALKAN (voidable / vernietigbaar) melalui permohonan ke pengadilan oleh pihak yang berhak.\n"
                               . "- Tidak terpenuhinya syarat objektif (3 & 4) mengakibatkan perjanjian BATAL DEMI HUKUM (null and void / nietig) sejak semula dianggap tidak pernah ada perjanjian.\n\n"
                               . "3. Asas Kekuatan Mengikat Perjanjian (Pacta Sunt Servanda) (Pasal 1338 ayat 1 KUHPerdata):\n"
                               . "Semua persetujuan yang dibuat secara sah berlaku sebagai undang-undang bagi mereka yang membuatnya. Perjanjian harus dilaksanakan dengan iktikad baik (te goeder trouw - Pasal 1338 ayat 3).\n\n"
                               . "4. Wanprestasi dan Tuntutan Ganti Rugi (Pasal 1238 & 1243 KUHPerdata):\n"
                               . "Debitur dinyatakan lalai (wanprestasi) dengan surat perintah atau akta sejenis (somasi). Bentuk wanprestasi meliputi: tidak melakukan apa yang disanggupi, terlambat berprestasi, melakukan apa yang diperjanjikan tetapi tidak sempurna, atau melakukan hal yang dilarang dalam perjanjian. Kreditur dapat menuntut pemenuhan perikatan, ganti rugi (biaya, rugi, dan bunga), atau pembatalan perjanjian disertai ganti rugi (Pasal 1267).",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Ketentuan dan Pengertian Surat Kuasa Menurut KUHPerdata',
                'description' => 'Definisi pemberian kuasa Pasal 1792, macam-macam kuasa (khusus, umum, substitusi), dan berakhirnya kuasa Pasal 1813 KUHPerdata.',
                'source_type' => 'KUHPerdata',
                'file_path'   => null,
                'content'     => "Menurut Bab XVI Buku III Kitab Undang-Undang Hukum Perdata (KUHPerdata):\n\n"
                               . "1. Pengertian Pemberian Kuasa (Pasal 1792 KUHPerdata):\n"
                               . "Pemberian kuasa adalah suatu persetujuan dengan mana seorang memberikan kekuasaan kepada seorang lain, yang menerimanya, untuk atas namanya menyelenggarakan suatu urusan.\n\n"
                               . "2. Jenis-Jenis Surat Kuasa:\n"
                               . "- Kuasa Umum (Pasal 1796 KUHPerdata): Kuasa yang dirumuskan dalam kata-kata umum hanya meliputi perbuatan-perbuatan pengurusan (beheer). Untuk memindahtangankan barang, menghibahkan, meletakkan hipotik/hak tanggungan, membuat perdamaian, atau beracara di pengadilan, diperlukan kuasa khusus.\n"
                               . "- Kuasa Khusus (Pasal 1795 KUHPerdata & SEMA No. 1 Tahun 1971 / SEMA No. 6 Tahun 1994): Kuasa yang diberikan secara tegas hanya mengenai satu kepentingan tertentu atau lebih. Dalam beracara di pengadilan (litigasi), surat kuasa khusus wajib menyebutkan kompetensi pengadilan, identitas para pihak (Penggugat dan Tergugat), serta materi pokok sengketa secara rinci.\n"
                               . "- Kuasa Substitusi: Hak yang diberikan kepada penerima kuasa untuk menunjuk pihak lain (substitut) guna mewakili atau menggantikan dirinya melaksanakan kuasa tersebut, sepanjang dicantumkan klausula hak substitusi.\n\n"
                               . "3. Berakhirnya Kuasa (Pasal 1813 KUHPerdata):\n"
                               . "Pemberian kuasa berakhir dengan:\n"
                               . "1. Penarikan kembali kuasa oleh pemberi kuasa;\n"
                               . "2. Pemberitahuan penghentian kuasa oleh penerima kuasa;\n"
                               . "3. Meninggalnya, pengampuan, atau pailitnya pemberi kuasa maupun penerima kuasa;\n"
                               . "4. Perkawinan perempuan yang memberi atau menerima kuasa (klausul historis tidak berlaku lagi).",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Prosedur dan Tahapan Pengajuan Gugatan Perdata (HIR/RBg & Perma)',
                'description' => 'Tata cara pendaftaran surat gugatan, e-Court, mediasi pengadilan Perma No. 1/2016, tahapan persidangan perdata, dan pembuktian Pasal 164 HIR.',
                'source_type' => 'Hukum Acara Perdata',
                'file_path'   => null,
                'content'     => "Berdasarkan Hukum Acara Perdata (HIR / RBg) dan regulasi Mahkamah Agung Republik Indonesia:\n\n"
                               . "1. Penyusunan Surat Gugatan:\n"
                               . "Surat gugatan memuat 3 bagian esensial:\n"
                               . "- Identitas Para Pihak: Nama lengkap, NIK/umur, pekerjaan, tempat tinggal Penggugat dan Tergugat;\n"
                               . "- Posita (Fundamentum Petendi): Uraian kronologis fakta kejadian dan dasar hubungan hukum yang mendasari gugatan;\n"
                               . "- Petitum: Hal-hal yang dimohonkan kepada Majelis Hakim untuk dikabulkan dalam amar putusan (termasuk petitum primer dan subsider/ex aequo et bono).\n\n"
                               . "2. Pendaftaran dan Pembayaran Panjar Perkara:\n"
                               . "Gugatan didaftarkan di Kepaniteraan Pengadilan Negeri yang berwenang (sesuai asas Actor Sequitur Forum Rei - domisili Tergugat Pasal 118 HIR) baik secara langsung maupun melalui aplikasi e-Court (Perma No. 1 Tahun 2019) dengan membayar Surat Kuasa Untuk Membayar (SKUM).\n\n"
                               . "3. Pemanggilan Para Pihak (Relaas):\n"
                               . "Jurusita pengadilan memanggil Penggugat dan Tergugat secara resmi dan patut minimal 3 hari kerja sebelum sidang pertama dimulai.\n\n"
                               . "4. Mediasi Wajib (Perma No. 1 Tahun 2016):\n"
                               . "Pada hari sidang pertama yang dihadiri kedua belah pihak, Majelis Hakim wajib memerintahkan mediasi selama maksimal 30 hari kerja yang dipandu oleh Hakim Mediator. Apabila tercapai kesepakatan damai, dituangkan dalam Akta Perdamaian (Dading). Jika mediasi gagal, persidangan dilanjutkan ke pokok perkara.\n\n"
                               . "5. Tahapan Persidangan Pokok Perkara:\n"
                               . "a. Pembacaan Surat Gugatan oleh Penggugat;\n"
                               . "b. Jawaban Tergugat (dapat memuat Eksepsi dan Gugatan Rekonvensi/Gugat Balik);\n"
                               . "c. Replik dari Penggugat;\n"
                               . "d. Duplik dari Tergugat;\n"
                               . "e. Pembuktian Surat dan Saksi/Ahli dari para pihak;\n"
                               . "f. Kesimpulan para pihak;\n"
                               . "g. Musyawarah Majelis Hakim dan Pembacaan Putusan.\n\n"
                               . "6. Alat-Alat Bukti Perdata (Pasal 164 HIR / Pasal 1866 KUHPerdata):\n"
                               . "Alat bukti yang sah dalam hukum acara perdata terdiri dari: bukti tertulis (surat/akta autentik dan akta bawah tangan), bukti saksi, persangkaan (vermoedens), pengakuan (bekentenis), dan sumpah (eed).\n\n"
                               . "7. Upaya Hukum:\n"
                               . "- Upaya Hukum Biasa: Verzet (perlawanan atas putusan verstek), Banding ke Pengadilan Tinggi (tenggang waktu 14 hari kalender sejak putusan diberitahukan), dan Kasasi ke Mahkamah Agung (14 hari sejak putusan banding diberitahukan).\n"
                               . "- Upaya Hukum Luar Biasa: Peninjauan Kembali (PK) berdasarkan novum (bukti baru) atau kekhilafan hakim.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Ketentuan Pokok Tindak Pidana dan Asas Legalitas dalam KUHP',
                'description' => 'Prinsip dasar pertanggungjawaban pidana, asas legalitas Pasal 1 KUHP, konsep tindak pidana, percobaan Pasal 53, dan penyertaan Pasal 55.',
                'source_type' => 'KUHP',
                'file_path'   => null,
                'content'     => "Berdasarkan Kitab Undang-Undang Hukum Pidana (KUHP):\n\n"
                               . "1. Asas Legalitas (Pasal 1 ayat 1 KUHP):\n"
                               . "Tiada suatu perbuatan dapat dipidana, melainkan atas kekuatan aturan perundang-undangan pidana yang telah ada sebelum perbuatan dilakukan (Nullum delictum nulla poena sine praevia lege poenali).\n"
                               . "Ayat 2: Bilamana ada perubahan dalam perundang-undangan sesudah saat pembuatan dilakukan, maka terhadap terdakwa diterapkan ketentuan yang paling menguntungkan baginya.\n\n"
                               . "2. Unsur-Unsur Tindak Pidana (Strafbaar Feit):\n"
                               . "Suatu peristiwa dikualifikasikan sebagai tindak pidana jika memenuhi:\n"
                               . "- Unsur Objektif: perbuatan manusia yang bersifat melawan hukum (wederrechtelijk), mencocoki rumusan undang-undang, serta akibat yang dilarang undang-undang.\n"
                               . "- Unsur Subjektif: kemampuan bertanggung jawab dari pelaku dan kesalahan (schuld) berupa kesengajaan (dolus) atau kealpaan (culpa).\n\n"
                               . "3. Percobaan Tindak Pidana (Poging) (Pasal 53 KUHP):\n"
                               . "Mencoba melakukan kejahatan dipidana jika niat untuk itu telah ternyata dari adanya permulaan pelaksanaan, dan tidak selesainya pelaksanaan itu bukan semata-mata disebabkan karena kehendaknya sendiri. Maksimum pidana pokok dikurangi sepertiga.\n\n"
                               . "4. Penyertaan dalam Tindak Pidana (Deelneming) (Pasal 55 KUHP):\n"
                               . "Dipidana sebagai pelaku tindak pidana:\n"
                               . "1. Mereka yang melakukan, menyuruh melakukan, dan yang turut serta melakukan perbuatan;\n"
                               . "2. Mereka yang dengan memberi atau menjanjikan sesuatu, menyalahgunakan kekuasaan, atau dengan kekerasan sengaja menganjurkan orang lain supaya melakukan perbuatan.\n\n"
                               . "5. Alasan Penghapus Pidana:\n"
                               . "- Alasan Pembenar: Keadaan memaksa/noodtoestand (Pasal 48), Pembelaan terpaksa/noodweer (Pasal 49 ayat 1), Menjalankan perintah undang-undang (Pasal 50), Menjalankan perintah jabatan yang sah (Pasal 51 ayat 1).\n"
                               . "- Alasan Pemaaf: Jiwa cacat dalam pertumbuhan/terganggu karena penyakit (Pasal 44), Pembelaan terpaksa melampaui batas karena guncangan jiwa hebat/noodweer exces (Pasal 49 ayat 2), Perintah jabatan tanpa wewenang dengan iktikad baik (Pasal 51 ayat 2).\n\n"
                               . "6. Hak Bantuan Hukum Tersangka/Terdakwa:\n"
                               . "Berdasarkan KUHAP (Pasal 54 & 56), tersangka atau terdakwa berhak mendapatkan bantuan hukum dari seorang atau lebih advokat/penasihat hukum guna kepentingan pembelaan pada setiap tingkat pemeriksaan.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Hak, Kewajiban, dan Kerahasiaan Hubungan Advokat dengan Klien (UU No. 18 Tahun 2003)',
                'description' => 'Prinsip profesi advokat sebagai penegak hukum bebas mandiri, hak imunitas Pasal 16, kerahasiaan klien Pasal 19, dan kewajiban pro bono Pasal 22.',
                'source_type' => 'Undang-Undang',
                'file_path'   => null,
                'content'     => "Berdasarkan Undang-Undang Republik Indonesia Nomor 18 Tahun 2003 tentang Advokat:\n\n"
                               . "1. Pengertian dan Status Advokat (Pasal 1 angka 1 jo. Pasal 5 ayat 1):\n"
                               . "Advokat adalah orang yang berprofesi memberi jasa hukum, baik di dalam maupun di luar pengadilan yang memenuhi persyaratan berdasarkan ketentuan undang-undang ini. Advokat berstatus sebagai penegak hukum, bebas dan mandiri yang dijamin oleh hukum dan peraturan perundang-undangan.\n\n"
                               . "2. Hak Imunitas Advokat (Pasal 16 UU Advokat jo. Putusan MK No. 26/PUU-XI/2013):\n"
                               . "Advokat tidak dapat dituntut baik secara perdata maupun pidana dalam menjalankan tugas profesinya dengan iktikad baik untuk kepentingan pembelaan klien di dalam maupun di luar sidang pengadilan.\n\n"
                               . "3. Kewajiban Merahasiakan Informasi Klien (Pasal 19 UU Advokat):\n"
                               . "- Advokat wajib merahasiakan segala sesuatu yang diketahui atau diperoleh dari kliennya karena hubungan profesinya, kecuali ditentukan lain oleh undang-undang.\n"
                               . "- Advokat berhak atas kerahasiaan hubungannya dengan klien, termasuk perlindungan atas berkas dan dokumennya terhadap penyitaan atau pemeriksaan dan perlindungan terhadap penyadapan atas komunikasi elektronik advokat.\n\n"
                               . "4. Larangan Diskriminasi dan Penelantaran Klien (Pasal 18):\n"
                               . "Advokat tidak dapat diidentikkan dengan kliennya dalam membela perkara klien oleh masyarakat dan/atau pemerintah. Advokat dilarang membedakan perlakuan terhadap klien berdasarkan jenis kelamin, agama, politik, keturunan, ras, atau latar belakang sosial budaya.\n\n"
                               . "5. Kewajiban Bantuan Hukum Cuma-Cuma / Pro Bono (Pasal 22):\n"
                               . "Advokat wajib memberikan bantuan hukum secara cuma-cuma kepada pencari keadilan yang tidak mampu.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Jangka Waktu Penyelesaian Perkara di Pengadilan (SEMA No. 2 Tahun 2014 & Gugatan Sederhana)',
                'description' => 'Pedoman jangka waktu penyelesaian perkara pengadilan tingkat pertama (5 bulan), banding (3 bulan), dan gugatan sederhana (25 hari).',
                'source_type' => 'Surat Edaran Mahkamah Agung',
                'file_path'   => null,
                'content'     => "Berdasarkan Surat Edaran Mahkamah Agung (SEMA) No. 2 Tahun 2014 tentang Penyelesaian Perkara di Pengadilan Tingkat Pertama dan Tingkat Banding serta regulasi terkait:\n\n"
                               . "1. Asas Peradilan Sederhana, Cepat, dan Biaya Ringan:\n"
                               . "Sesuai Pasal 2 ayat (4) UU No. 48 Tahun 2009 tentang Kekuasaan Kehakiman, peradilan dilakukan dengan sederhana, cepat, dan biaya ringan guna memberikan kepastian hukum bagi para pihak pencari keadilan.\n\n"
                               . "2. Jangka Waktu Penyelesaian di Pengadilan Tingkat Pertama (Pengadilan Negeri / Pengadilan Agama):\n"
                               . "Penyelesaian perkara perdata maupun pidana pada pengadilan tingkat pertama harus diselesaikan dalam waktu paling lama 5 (lima) bulan terhitung sejak perkara didaftarkan.\n\n"
                               . "3. Jangka Waktu Penyelesaian di Pengadilan Tingkat Banding (Pengadilan Tinggi):\n"
                               . "Penyelesaian perkara pada tingkat banding harus diselesaikan dalam waktu paling lama 3 (tiga) bulan terhitung sejak berkas perkara diterima oleh pengadilan tinggi.\n\n"
                               . "4. Gugatan Sederhana / Small Claim Court (Perma No. 2 Tahun 2015 jo. Perma No. 4 Tahun 2019):\n"
                               . "- Gugatan perdata dengan nilai materiil maksimal Rp500.000.000,00 (lima ratus juta rupiah) mengenai wanprestasi atau perbuatan melawan hukum (PMH) non-tanah dan non-pengadilan khusus.\n"
                               . "- Diperiksa oleh Hakim Tunggal dan wajib diputus dalam waktu paling lama 25 (dua puluh lima) hari kerja sejak hari sidang pertama.\n"
                               . "- Tidak dapat diajukan tuntutan provisi, eksepsi, rekonvensi, replik, duplik, atau kesimpulan.\n"
                               . "- Upaya hukum hanya berupa Keberatan kepada Ketua Pengadilan Negeri (tanpa banding atau kasasi).\n\n"
                               . "5. Pengecualian Batas Waktu:\n"
                               . "Apabila pemeriksaan perkara melampaui batas waktu yang ditentukan karena kendala geografis, pemanggilan saksi di luar negeri, atau kerumitan teknis perkara, Majelis Hakim wajib membuat laporan tertulis kepada Ketua Pengadilan untuk ditembuskan kepada Mahkamah Agung.",
                'status'      => 'Aktif',
                'verified_by' => $adminId,
            ],
            [
                'title'       => 'Panduan dan Prosedur Layanan Konsultasi Kantor Sahabat Hukum',
                'description' => 'Persyaratan berkas identitas, alur pengajuan konsultasi, tata tertib sesi konsultasi, dan indikasi kapan klien wajib menunjuk advokat.',
                'source_type' => 'Prosedur Layanan',
                'file_path'   => null,
                'content'     => "Panduan resmi layanan konsultasi hukum pada kantor hukum Sahabat Hukum:\n\n"
                               . "1. Dokumen yang Wajib Disiapkan Klien Sebelum Konsultasi:\n"
                               . "- Dokumen Identitas Resmi: KTP elektronik / Paspor / SIM yang masih aktif, serta Kartu Keluarga (KK) bila menyangkut sengketa waris, perkawinan, atau keluarga.\n"
                               . "- Bukti Surat / Dokumen Tertulis Pokok: Kontrak/perjanjian, akta notaris, kuitansi bukti pembayaran/transfer bank, surat somasi, sertifikat tanah, atau surat resmi yang mendasari sengketa.\n"
                               . "- Kronologi Fakta Tertulis: Catatan urutan peristiwa yang memuat tanggal, tempat kejadian, para pihak yang terlibat, serta kronologis timbulnya masalah secara runtut.\n"
                               . "- Bukti Elektronik & Komunikasi: Salinan percakapan (WhatsApp/Email), rekaman, atau korespondensi penting.\n\n"
                               . "2. Alur Pengajuan Konsultasi di Sistem Sahabat Hukum:\n"
                               . "Langkah 1: Klien masuk ke akun Sahabat Hukum dan mengakses menu 'Konsultasi'.\n"
                               . "Langkah 2: Menekan tombol 'Ajukan Konsultasi' dan mengisi form judul permasalahan, kategori perkara (Perdata, Pidana, Ketenagakerjaan, Keluarga), serta ringkasan masalah.\n"
                               . "Langkah 3: Mengunggah dokumen bukti pendukung dalam format PDF atau gambar.\n"
                               . "Langkah 4: Admin/Advokat memverifikasi pengajuan dan menetapkan jadwal konsultasi serta advokat pendamping.\n"
                               . "Langkah 5: Pelaksanaan sesi konsultasi secara langsung (tatap muka) di kantor atau teleconference online.\n"
                               . "Langkah 6: Klien memperoleh telaah hukum awal (legal opinion) dan opsi langkah hukum terbaik.\n\n"
                               . "3. Kapan Klien Sangat Disarankan Menunjuk Advokat:\n"
                               . "Klien disarankan segera memberikan kuasa kepada advokat apabila:\n"
                               . "- Menerima surat panggilan pemeriksaan dari kepolisian atau kejaksaan sebagai saksi/tersangka;\n"
                               . "- Menghadapi surat somasi resmi dari pihak lawan dengan ancaman gugatan atau pidana;\n"
                               . "- Memerlukan perwakilan formal untuk mengajukan gugatan ke pengadilan atau mediasi resmi;\n"
                               . "- Menghadapi transaksi komersial bernilai tinggi yang membutuhkan legal audit / perancangan kontrak berisiko.",
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
