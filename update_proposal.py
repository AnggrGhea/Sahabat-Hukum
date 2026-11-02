import docx
from docx.shared import Pt, Inches, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
import shutil

def set_cell_margins_and_border(cell, top=60, bottom=60, left=100, right=100, fill=None):
    tcPr = cell._element.get_or_add_tcPr()
    
    # borders
    tcBorders = OxmlElement('w:tcBorders')
    for b_name in ['top', 'left', 'bottom', 'right']:
        b = OxmlElement(f'w:{b_name}')
        b.set(qn('w:val'), 'single')
        b.set(qn('w:sz'), '4')
        b.set(qn('w:space'), '0')
        b.set(qn('w:color'), 'auto')
        tcBorders.append(b)
    tcPr.append(tcBorders)
    
    # shading / fill
    if fill:
        shd = OxmlElement('w:shd')
        shd.set(qn('w:val'), 'clear')
        shd.set(qn('w:color'), 'auto')
        shd.set(qn('w:fill'), fill)
        tcPr.append(shd)
        
    # margins
    tcMar = OxmlElement('w:tcMar')
    for m_name, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        m = OxmlElement(f'w:{m_name}')
        m.set(qn('w:w'), str(val))
        m.set(qn('w:type'), 'dxa')
        tcMar.append(m)
    tcPr.append(tcMar)
    
    # vertical alignment center
    vAlign = OxmlElement('w:vAlign')
    vAlign.set(qn('w:val'), 'center')
    tcPr.append(vAlign)

def main():
    src_file = r'PROPOSAL_TA_Sahabat_Hukum_Anggre_Ghea_Novika_Revisi.docx'
    doc = docx.Document(src_file)

    # 1. Update Abstrak (P68)
    p68 = doc.paragraphs[68]
    p68.text = (
        "Masyarakat yang berhadapan dengan persoalan hukum kerap mengalami hambatan pemahaman awal "
        "(information barrier) mengenai prosedur layanan, jenis perkara, dan kelengkapan berkas bukti yang "
        "harus dipersiapkan sebelum berkonsultasi langsung dengan advokat. Di sisi lain, pemanfaatan model "
        "bahasa generatif tanpa kontrol sumber rentan menimbulkan halusinasi hukum, fabrikasi dasar peraturan, "
        "serta tafsir yang keliru. Penelitian ini bertujuan merancang dan membangun Sahabat Hukum, yaitu sistem "
        "informasi manajemen perkara dan layanan konsultasi hukum berbasis web yang mengintegrasikan Asisten Hukum "
        "menggunakan Retrieval-Augmented Generation (RAG). Asisten Hukum dirancang sebagai sarana pra-konsultasi "
        "yang memberikan informasi awal terarah berdasarkan potongan dokumen dari basis pengetahuan internal yang "
        "dikelola dan diverifikasi oleh Admin, tanpa mengakses data privat perkara Klien. Sistem dibangun untuk tiga "
        "peran pengguna (Klien, Advokat, dan Admin) menggunakan framework Laravel dan basis data MySQL dengan metode "
        "Waterfall, mencakup pengelolaan konsultasi, perkara, permintaan dan verifikasi dokumen, jadwal, percakapan, "
        "pemberitahuan, serta audit basis pengetahuan. Asisten Hukum dilengkapi klausul pembatasan (disclaimer) bahwa "
        "informasi yang dihasilkan bersifat umum dan bukan nasihat hukum profesional, serta mekanisme pengarahan otomatis "
        "ke formulir konsultasi advokat apabila pertanyaan berada di luar cakupan atau membutuhkan analisis yuridis formal. "
        "Pengujian fungsional direncanakan menggunakan Black-box Testing, evaluasi penerimaan pengguna dengan User "
        "Acceptance Testing (UAT) berbasis System Usability Scale (SUS), serta evaluasi kualitas Asisten Hukum yang "
        "mencakup relevansi konteks retrieval, kesesuaian jawaban dengan sumber (faithfulness), relevansi jawaban, "
        "kelengkapan, penanganan pertanyaan di luar cakupan, dan waktu respons. Luaran yang diharapkan adalah purwarupa "
        "sistem terintegrasi yang berfungsi sesuai spesifikasi kebutuhan beserta bukti pengujian empirisnya. Pada tahap "
        "proposal ini, seluruh kontribusi berstatus rancangan dan belum ada klaim keberhasilan fungsional maupun "
        "keandalan jawaban sistem."
    )

    # 2. Update Bab I Subbab 1.1 Latar Belakang (P73 to P77)
    # P73: Kebutuhan informasi hukum awal & hambatan klien
    p73 = doc.paragraphs[73]
    p73.text = (
        "Akses terhadap keadilan dan kepastian hukum sering kali terkendala oleh tingginya kesenjangan pemahaman hukum "
        "pada masyarakat awam. Individu yang menghadapi permasalahan hukum kerap mengalami kesulitan mendasar dalam "
        "mengidentifikasi klasifikasi masalahnya (apakah tergolong perdata, pidana, atau tata usaha negara), tidak "
        "mengetahui prosedur administratif yang harus dilalui, serta belum memahami dokumen bukti pendukung apa saja yang "
        "harus dipersiapkan sebelum mendatangi kantor hukum. Kondisi ini tidak hanya menimbulkan keraguan bagi masyarakat "
        "untuk mengakses bantuan hukum formal, tetapi juga berdampak langsung pada inefisiensi layanan di kantor hukum: "
        "advokat kerap menghabiskan porsi waktu yang besar pada sesi konsultasi tatap muka awal hanya untuk menjelaskan "
        "informasi umum, alur formal, dan persyaratan berkas dasar yang bersifat repetitif, alih-alih berfokus pada bedah "
        "materi dan perumusan strategi hukum perkara."
    )

    # P74: Risiko AI generatif murni & Relevansi RAG
    p74 = doc.paragraphs[74]
    p74.text = (
        "Perkembangan teknologi kecerdasan buatan, khususnya Large Language Models (LLM), membuka peluang penyediaan sarana "
        "tanya-jawab otomatis dalam bahasa sehari-hari. Kendati demikian, penggunaan LLM generatif murni tanpa pembatasan "
        "sumber membawa risiko yuridis yang sangat berbahaya. LLM generatif beroperasi secara probabilistik sehingga rentan "
        "mengalami halusinasi (hallucination) [5], seperti memfabrikasi nomor pasal, mengutip peraturan perundang-undangan "
        "yang fiktif atau sudah dicabut, serta menghasilkan kesimpulan hukum keliru dengan nada bahasa yang sangat meyakinkan "
        "(authoritative tone). Evaluasi empiris oleh Magesh dkk. [6] terhadap perangkat riset hukum komersial berbasis AI "
        "bahkan menemukan tingkat halusinasi antara 17% hingga 33%, yang menegaskan bahwa luaran model generatif tidak dapat "
        "dijadikan sandaran informasi hukum tanpa penelusuran dokumen otoritatif. Untuk mengatasi kelemahan mendasar tersebut, "
        "pendekatan Retrieval-Augmented Generation (RAG) menjadi sangat relevan [9]. Melalui RAG, model bahasa tidak menyusun "
        "jawaban secara bebas dari parameter memorinya, melainkan dibatasi dan diarahkan (grounded) pada potongan informasi "
        "(chunks) yang diambil secara dinamis dari basis data dokumen yang diverifikasi, disertai sitasi sumber yang jelas "
        "serta kemampuan mendeteksi batas pengetahuan ketika rujukan yang dicari tidak tersedia [3]."
    )

    # P75: Urgensi integrasi ke Sahabat Hukum (bukan chatbot mandiri) & Batasan Peran
    p75 = doc.paragraphs[75]
    p75.text = (
        "Kendati RAG telah diterapkan pada sejumlah penelitian domain hukum di Indonesia—seperti chatbot informasi publik "
        "Ombudsman DIY [1], konsultasi regulasi daerah [2], dan temu balik dokumen undang-undang [3]—penelitian-penelitian "
        "tersebut berfokus pada pembangunan chatbot yang berdiri sendiri (standalone). Chatbot mandiri memiliki kelemahan "
        "sistemik berupa terputusnya mata rantai layanan (service discontinuity): setelah menerima informasi awal, pengguna "
        "harus berpindah media secara manual untuk mendaftar, memilih advokat, menyerahkan dokumen, atau menjadwalkan "
        "konsultasi formal. Oleh karena itu, Asisten Hukum berbasis RAG mendesak diintegrasikan ke dalam sistem informasi "
        "manajemen perkara dan layanan konsultasi hukum Sahabat Hukum. Dalam arsitektur terintegrasi ini, Asisten Hukum "
        "berfungsi sebagai sarana pra-konsultasi cerdas yang membantu Klien memahami peta persoalannya dan mempersiapkan "
        "berkas bukti yang relevan secara mandiri. Asisten Hukum secara tegas dirancang tidak untuk memberikan nasihat hukum "
        "mengikat (legal advice), tidak memprediksi amar putusan peradilan, dan tidak menggantikan diskresi profesional advokat, "
        "melainkan mengarahkan pengguna secara mulus ke formulir pengajuan konsultasi resmi apabila substansi persoalan "
        "membutuhkan analisis yuridis mendalam."
    )

    # P76: Urgensi operasional mitra & tata kelola data
    p76 = doc.paragraphs[76]
    p76.text = (
        "Dari sisi tata kelola kantor hukum mitra, efektivitas penanganan perkara memerlukan dukungan administrasi yang "
        "terpusat. Praktik pencatatan data klien, riwayat konsultasi, status penanganan perkara, batas waktu sidang, dan "
        "penyimpanan berkas bukti yang tersebar pada media fisik, spreadsheet, atau percakapan instan pribadi berisiko "
        "menyulitkan penelusuran kembali data, memicu keterlambatan koordinasi jadwal, serta memperlambat penyusunan "
        "laporan perkara [7], [8]. [Bagian ini memuat ringkasan hasil observasi atau wawancara awal pada kantor hukum mitra "
        "mengenai alur penanganan berkas dan konsultasi saat ini; catatan: data observasi, jumlah narasumber, dan kutipan "
        "lapangan belum diisi pada draft ini karena membutuhkan pelaksanaan observasi langsung dan persetujuan formal mitra.] "
        "Untuk menjamin kerahasiaan hubungan advokat-klien sebagaimana diamanatkan Undang-Undang Nomor 18 Tahun 2003 tentang "
        "Advokat [10] serta kepatuhan terhadap Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi [11], "
        "arsitektur Sahabat Hukum menerapkan isolasi data yang ketat: basis data perkara dan dokumen bukti privat Klien "
        "sepenuhnya dipisahkan dari pipeline Asisten Hukum RAG. Basis pengetahuan RAG murni dibatasi pada informasi layanan "
        "kantor, standar prosedur operasional, persyaratan dokumen perkara, dan ringkasan regulasi umum yang diunggah dan "
        "diverifikasi oleh Admin."
    )

    # P77: Metodologi dan rencana evaluasi
    p77 = doc.paragraphs[77]
    p77.text = (
        "Berdasarkan kesenjangan integrasi dan urgensi tata kelola tersebut, penelitian ini merancang dan membangun sistem "
        "informasi berbasis web Sahabat Hukum yang melayani tiga peran pengguna (Klien, Advokat, dan Admin). Sistem dikembangkan "
        "menggunakan metode Waterfall [13] dengan framework Laravel dan basis data relasional MySQL [14]. Pipeline Asisten Hukum "
        "menerapkan teknik pemotongan teks berbasis struktur dokumen hukum (structural chunking) untuk menjaga keutuhan konteks [3], "
        "sedangkan pemilihan model bahasa dan model embedding dievaluasi pada tahap implementasi dengan mengutamakan model "
        "yang efisien dan aman bagi privasi [12]. Untuk menguji sistem secara komprehensif, dilakukan pengujian fungsional "
        "dengan Black-box Testing [15], pengukuran penerimaan dan kebergunaan pengguna dengan User Acceptance Testing (UAT) "
        "berbasis System Usability Scale (SUS) [21], serta evaluasi kualitas Asisten Hukum berdasarkan metrik relevansi retrieval, "
        "kesesuaian jawaban dengan sumber (faithfulness), relevansi jawaban, kelengkapan, penanganan pertanyaan di luar cakupan, "
        "dan waktu respons."
    )

    # 3. Update Bab I Subbab 1.2 Rumusan Masalah (P80 - P83)
    doc.paragraphs[80].text = (
        "1. Bagaimana merancang dan membangun sistem informasi manajemen perkara dan layanan konsultasi hukum berbasis "
        "web \"Sahabat Hukum\" yang mengintegrasikan peran Klien, Advokat, dan Admin dalam pengelolaan konsultasi, perkara, "
        "permintaan dan verifikasi dokumen, jadwal, percakapan, dan pemberitahuan secara terpusat?"
    )
    doc.paragraphs[81].text = (
        "2. Bagaimana merancang dan mengintegrasikan Asisten Hukum berbasis Retrieval-Augmented Generation (RAG) menggunakan "
        "basis pengetahuan internal yang dikurasi dan diverifikasi oleh Admin, disertai mekanisme pembatasan ruang lingkup "
        "(guardrails), sitasi rujukan, serta pemisahan tegas dari data privat perkara Klien?"
    )
    doc.paragraphs[82].text = (
        "3. Bagaimana merancang alur interaksi terpadu yang menghubungkan informasi awal dari Asisten Hukum dengan tahapan "
        "pengajuan konsultasi resmi, penjadwalan temu, dan penanganan perkara oleh Advokat?"
    )
    doc.paragraphs[83].text = (
        "4. Bagaimana menguji kesesuaian fungsional sistem menggunakan Black-box Testing, mengukur penerimaan dan kebergunaan "
        "pengguna melalui User Acceptance Testing (UAT) berbasis System Usability Scale (SUS), serta mengevaluasi kualitas "
        "retrieval dan jawaban Asisten Hukum secara terukur?"
    )

    # 4. Update Bab I Subbab 1.3 Batasan Masalah (P85 - P95)
    doc.paragraphs[85].text = (
        "Sistem dikembangkan berbasis web responsif menggunakan framework PHP Laravel dan sistem manajemen basis data "
        "relasional MySQL, serta dapat diakses melalui peramban web modern (Google Chrome, Mozilla Firefox, Microsoft Edge)."
    )
    doc.paragraphs[86].text = (
        "Pengguna sistem terdiri atas tiga peran dengan hak akses berbasis peran (role-based access control): Klien, "
        "Advokat, dan Admin."
    )
    doc.paragraphs[87].text = (
        "Fitur utama sistem mencakup manajemen pengguna, pengajuan dan penjadwalan konsultasi, pencatatan hasil konsultasi, "
        "pembentukan dan pemantauan perkara, permintaan dan verifikasi berkas dokumen bukti, percakapan internal, pemberitahuan "
        "sistem, pengelolaan basis pengetahuan, dan Asisten Hukum."
    )
    doc.paragraphs[88].text = (
        "Konsultasi hukum diajukan, dikonfirmasi, dijadwalkan, dan dicatat hasil kesimpulannya melalui sistem. Pelaksanaan "
        "interaksi konsultasi langsung dilakukan secara tatap muka di kantor atau melalui media komunikasi eksternal (seperti "
        "WhatsApp, Zoom, atau Google Meet); sistem tidak menyediakan fitur video conference bawaan dan tidak merekam percakapan "
        "eksternal tersebut."
    )
    doc.paragraphs[89].text = (
        "Pengelolaan dokumen bukti perkara mengikuti alur kerja terstruktur: Advokat menerbitkan permintaan dokumen resmi dengan "
        "keterangan batas waktu dan tingkat urgensi, Klien mengunggah berkas digital pendukung, dan Advokat memverifikasi "
        "keabsahan berkas dengan status Diterima atau Perlu Diperbaiki disertai catatan evaluasi."
    )
    doc.paragraphs[90].text = (
        "Asisten Hukum berbasis RAG murni memanfaatkan dokumen basis pengetahuan internal yang diunggah dan diverifikasi oleh "
        "Admin (mencakup standar prosedur operasional kantor hukum, alur konsultasi, panduan persyaratan dokumen perkara, dan "
        "ringkasan informasi regulasi umum). Sistem tidak melakukan perayapan atau pencarian web eksternal secara dinamis "
        "(no dynamic external web search)."
    )
    doc.paragraphs[91].text = (
        "Asisten Hukum berfungsi murni sebagai sarana penyedia informasi awal (pra-konsultasi). Asisten Hukum tidak memberikan "
        "nasihat hukum mengikat (legal advice), tidak melakukan analisis yuridis kasus spesifik, tidak menyusun dokumen hukum "
        "otomatis, dan tidak memprediksi putusan peradilan. Setiap jawaban wajib memuat klausul sanggahan (disclaimer), dan "
        "pertanyaan di luar cakupan atau yang membutuhkan pertimbangan hukum diarahkan langsung untuk berkonsultasi dengan Advokat."
    )
    doc.paragraphs[92].text = (
        "Pipeline RAG diisolasi secara mutlak dari basis data perkara, dokumen bukti, dan percakapan pribadi Klien guna "
        "menjaga kerahasiaan profesi advokat dan mematuhi regulasi pelindungan data pribadi."
    )
    doc.paragraphs[93].text = (
        "Sistem tidak mencakup persidangan elektronik pengadilan (e-court), pembayaran daring otomatis (payment gateway), "
        "integrasi API dengan basis data Mahkamah Agung/kementerian eksternal, aplikasi seluler native, maupun pemeliharaan "
        "sistem setelah periode pengujian selesai."
    )
    doc.paragraphs[94].text = (
        "Data yang digunakan dalam pengembangan dan pengujian berasal dari pihak mitra dengan persetujuan formal, data simulasi, "
        "atau data yang telah dianonimkan tanpa menyertakan identitas atau data pribadi sensitif Klien riil."
    )
    doc.paragraphs[95].text = (
        "Objek penelitian: [nama kantor hukum mitra atau objek penelitian]."
    )

    # 5. Update Bab I Subbab 1.4 Tujuan (P98 - P101)
    doc.paragraphs[98].text = (
        "1. Merancang dan membangun sistem informasi manajemen perkara dan layanan konsultasi hukum berbasis web \"Sahabat Hukum\" "
        "yang memfasilitasi integrasi alur kerja Klien, Advokat, dan Admin dalam pengelolaan konsultasi, perkara, dokumen bukti, "
        "jadwal, percakapan, dan pemberitahuan secara terpusat."
    )
    doc.paragraphs[99].text = (
        "2. Merancang dan menerapkan komponen Asisten Hukum berbasis Retrieval-Augmented Generation (RAG) yang memanfaatkan basis "
        "pengetahuan internal terverifikasi, dilengkapi mekanisme pembatasan (guardrails), sitasi sumber rujukan, dan isolasi ketat "
        "terhadap data perkara privat Klien."
    )
    doc.paragraphs[100].text = (
        "3. Membangun mekanisme transisi fungsional yang menghubungkan hasil telaah informasi awal Asisten Hukum dengan pengajuan "
        "konsultasi formal kepada Advokat apabila informasi tidak memadai atau persoalan menuntut analisis profesional."
    )
    doc.paragraphs[101].text = (
        "4. Menguji kesesuaian fungsional sistem menggunakan Black-box Testing, mengukur penerimaan dan kebergunaan pengguna "
        "melalui User Acceptance Testing (UAT) berbasis System Usability Scale (SUS), serta mengevaluasi kualitas retrieval dan "
        "jawaban Asisten Hukum secara terukur pada aspek relevansi konteks, kesesuaian sumber (faithfulness), relevansi jawaban, "
        "kelengkapan, penanganan pertanyaan di luar cakupan, dan waktu respons."
    )

    # 6. Update Bab I Subbab 1.5 Manfaat (P103 - P107)
    doc.paragraphs[103].text = (
        "Manfaat bagi pengembangan ilmu: memberikan kontribusi empiris dan artefak arsitektur mengenai integrasi komponen "
        "Retrieval-Augmented Generation (RAG) ke dalam sistem informasi manajemen perkara hukum berbasis web, khususnya pada "
        "desain isolasi data sensitif, tata kelola basis pengetahuan terkurasi, mitigasi risiko halusinasi, dan skema evaluasi multi-dimensi."
    )
    doc.paragraphs[104].text = (
        "Manfaat bagi Klien: membantu masyarakat memperoleh pemahaman hukum awal dan panduan persiapan dokumen secara mandiri, "
        "mengajukan konsultasi, memantau riwayat dan perkembangan perkaranya secara transparan, memenuhi permintaan dokumen bukti, "
        "serta melihat jadwal dan pemberitahuan dalam satu portal terpadu tanpa hambatan komunikasi."
    )
    doc.paragraphs[105].text = (
        "Manfaat bagi Advokat dan kantor hukum: memusatkan tata kelola administrasi konsultasi, perkara, dokumen bukti, dan jadwal "
        "dalam satu basis data terintegrasi; mengurangi beban kerja penyampaian informasi dasar yang repetitif kepada klien melalui "
        "dukungan Asisten Hukum; serta meningkatkan kesiapan klien saat memasuki sesi konsultasi formal."
    )
    doc.paragraphs[106].text = (
        "Manfaat bagi institusi: memperkaya repositori karya ilmiah terapan Program Studi D4 Teknologi Rekayasa Perangkat Lunak "
        "Politeknik Negeri Subang dalam bidang rekayasa perangkat lunak terintegrasi kecerdasan buatan pada ranah layanan hukum profesional."
    )
    doc.paragraphs[107].text = (
        "Manfaat bagi peneliti: mengasah kompetensi praktis dalam merancang, mengimplementasikan, dan menguji sistem informasi "
        "skala enterprise berbasis Laravel yang diintegrasikan dengan arsitektur kecerdasan buatan RAG serta tata kelola privasi data."
    )

    # 7. Update Bab II Subbab 2.1 Kajian Terdahulu narasi (P110 - P113)
    doc.paragraphs[110].text = (
        "Kajian terdahulu ditelaah untuk memetakan perkembangan teknologi, posisi penelitian, dan kesenjangan yang ada pada domain "
        "sistem informasi hukum dan kecerdasan buatan. Publikasi yang dikaji dikelompokkan ke dalam tiga klaster utama: (1) sistem "
        "informasi manajemen berkas perkara dan konsultasi hukum berbasis web; (2) penerapan chatbot dan model bahasa berbasis RAG "
        "pada dokumen hukum; serta (3) studi evaluasi empiris risiko halusinasi dan survei model bahasa besar di ranah hukum. "
        "Seluruh rujukan berasal dari literatur ilmiah bereputasi terbitan tahun 2022 hingga 2026. Ringkasan perbandingan komprehensif "
        "disajikan pada Tabel 2.1, sedangkan status akreditasi dan indeksasi sumber disajikan pada Tabel 2.2."
    )
    doc.paragraphs[111].text = (
        "Pada klaster pertama (sistem informasi hukum konvensional), Setiawan dan Effiyaldi [7] merancang prototipe sistem informasi "
        "manajemen berkas perkara pada Kejaksaan Negeri Merangin menggunakan pemodelan UML untuk mengatasi pencatatan manual dan "
        "penumpukan berkas fisik, namun penelitian tersebut berhenti pada tahap perancangan tanpa implementasi maupun evaluasi empiris. "
        "Sementara itu, Nurwicaksono dkk. [8] menguji keandalan sistem informasi konsultasi hukum \"Advogo\" melalui kombinasi white-box "
        "dan black-box testing, dengan hasil validitas tinggi pada alur otentikasi dan pemesanan sesi konsultasi. Kedua penelitian ini "
        "membuktikan urgensi digitalisasi tata kelola perkara dan konsultasi hukum, namun sistem yang dibangun murni bersifat administratif "
        "konvensional tanpa dukungan pemrosesan informasi awal berbasis AI."
    )
    doc.paragraphs[112].text = (
        "Pada klaster kedua (chatbot dan RAG dokumen hukum), Yasmin dan Fudholi [1] membangun chatbot informasi hukum layanan publik "
        "Ombudsman DIY menggunakan RAG dengan LangChain, OpenAI, dan ChromaDB, dengan evaluasi kualitatif oleh lima orang ahli. "
        "Rahmadi dan Setyanto [2] mengembangkan LexIndoLLM yang mengombinasikan fine-tuning Llama 3.2-1B dengan RAG FAISS pada 393 "
        "dokumen regulasi daerah di Indonesia, menghasilkan nilai faithfulness 0,77 dan answer correctness 0,66. Fadillah dkk. [3] "
        "mengembangkan LawRAG yang menunjukkan bahwa pemotongan data berbasis struktur dokumen hukum (bab, pasal, ayat) serta model "
        "reranker meningkatkan akurasi retrieval dokumen undang-undang Indonesia dibandingkan pemotongan sekuensial. Rivas-Echeverría dkk. [4] "
        "membangun LegalBot-EC menggunakan ChromaDB dan LLaMA 3.1 pada dua regulasi hukum Ekuador. Meskipun berhasil menyediakan temu "
        "balik informasi hukum, seluruh penelitian pada klaster ini mengembangkan chatbot mandiri (standalone) yang terisolasi dari "
        "proses bisnis penanganan perkara, penjadwalan temu advokat, maupun manajemen berkas bukti klien."
    )
    doc.paragraphs[113].text = (
        "Pada klaster ketiga (evaluasi risiko dan model bahasa hukum), Lai dkk. [5] dalam surveinya memetakan potensi dan tantangan "
        "etika model bahasa besar di bidang hukum, menekankan risiko bias dan perlunya pembatasan peran AI. Magesh dkk. [6] mempublikasikan "
        "studi empiris pra-registrasi yang membuktikan bahwa alat riset hukum berbasis RAG komersial terkemuka tetap mengalami halusinasi "
        "pada 17% hingga 33% pengujian, yang membuktikan bahwa RAG tidak menghilangkan kesalahan secara otomatis. Lijaya dkk. [12] "
        "membandingkan tiga model LLM terbuka berbahasa Indonesia berbasis RAG dan menekankan pentingnya privasi data on-premise, namun "
        "mencatat keterbatasan model pada penalaran terstruktur. Al-Qaesm dkk. [16] melakukan fine-tuning model terkuantisasi pada data "
        "hukum Palestina, namun mencatat performa yang belum optimal pada instruksi prosedural dan daftar berurutan. Temuan-temuan ini "
        "menjadi landasan bahwa asisten hukum cerdas harus dirancang dengan pagar pembatasan (guardrails), audit basis pengetahuan terverifikasi, "
        "serta isolasi mutlak terhadap data privat klien."
    )

    # 8. Reconstruct Table 1 (Tabel 2.1 Kajian Terdahulu) with 7 analytical columns
    old_table = doc.tables[1]
    
    table_data = [
        # Row 0: Headers
        [
            "No",
            "Peneliti & Rujukan",
            "Fokus & Objek Penelitian",
            "Metode & Teknologi",
            "Cakupan Fungsi & Integrasi Layanan",
            "Penanganan Sumber & Mitigasi Risiko Informasi",
            "Evaluasi, Keterbatasan, & Perbedaan Konkret dengan Sahabat Hukum"
        ],
        # Row 1: Setiawan
        [
            "1",
            "Setiawan dan Effiyaldi (2022) [7]",
            "Sistem informasi manajemen berkas perkara pidana/perdata di Kejaksaan Negeri Merangin.",
            "Metode Prototyping; pemodelan sistem UML (Use Case, Activity, Class Diagram); web PHP & MySQL.",
            "Administrasi berkas perkara dan tahapan proses hukum internal; tanpa portal publik/klien; tanpa jadwal temu; tanpa komponen AI.",
            "Mengandalkan entri manual staf; validasi berbasis integritas relasional basis data; tidak ada rujukan regulasi otomatis.",
            "Keterbatasan: Berhenti pada perancangan prototipe konseptual; belum diimplementasikan dan belum diuji secara empiris.\nPerbedaan: Sahabat Hukum dibangun penuh hingga uji Black-box dan UAT, melayani 3 aktor (Klien, Advokat, Admin), serta mengintegrasikan Asisten Hukum RAG."
        ],
        # Row 2: Nurwicaksono
        [
            "2",
            "Nurwicaksono dkk. (2024) [8]",
            "Optimasi dan pengujian sistem informasi konsultasi hukum daring \"Advogo\".",
            "Pengujian kombinasi White-box (Basis Path, Branch Testing) dan Black-box (Cause-Effect Graphing); arsitektur web.",
            "Fokus pada otentikasi pengguna, pemesanan sesi konsultasi, dan komunikasi konsultasi; tanpa manajemen berkas perkara lanjutan.",
            "Komunikasi langsung klien-advokat; tidak memiliki basis pengetahuan internal maupun sarana informasi awal pra-konsultasi.",
            "Keterbatasan: 10 kasus uji white-box valid 100%, 14 kasus uji black-box valid 92% (1 cacat); cakupan fungsional terbatas pada penjadwalan konsultasi.\nPerbedaan: Sahabat Hukum mengelola perkara end-to-end (konsultasi, perkara, kronologi, verifikasi dokumen) terintegrasi dengan Asisten Hukum RAG."
        ],
        # Row 3: Yasmin
        [
            "3",
            "Yasmin dan Fudholi (2025) [1]",
            "Chatbot informasi hukum layanan publik pengaduan di Kantor Ombudsman RI DIY.",
            "RAG menggunakan LangChain, model LLM OpenAI, dan basis data vektor Chroma.",
            "Chatbot tanya-jawab mandiri (standalone); tidak terintegrasi dengan registrasi perkara/pengaduan, jadwal, atau pelacakan berkas.",
            "Retrieval berbasis teks regulasi ombudsman; mengurangi halusinasi dibanding LLM generatif murni, tetapi tanpa mekanisme pengalihan otomatis ke petugas jika informasi tidak ditemukan.",
            "Keterbatasan: Evaluasi kualitatif terbatas pada 5 orang ahli; tidak menggunakan metrik kuantitatif terstandar; sistem terpisah dari operasional kantor.\nPerbedaan: Sahabat Hukum mengintegrasikan RAG ke portal web 3 peran, mengisolasi data perkara, dan menyediakan fallback rujukan langsung ke advokat."
        ],
        # Row 4: Rahmadi
        [
            "4",
            "Rahmadi dan Setyanto (2026) [2]",
            "Sistem \"LexIndoLLM\" untuk konsultasi dan temu balik regulasi daerah (Perda/Perbup) Kabupaten Sleman.",
            "Kombinasi Fine-Tuning Llama 3.2-1B-Instruct (393 dokumen regulasi) dengan RAG berbasis FAISS.",
            "Tanya-jawab dokumen regulasi daerah publik; tanpa alur manajemen perkara advokat, tanpa unggah/verifikasi berkas bukti klien, tanpa kalender jadwal.",
            "Fine-tuning mengadaptasi terminologi hukum lokal; RAG menyediakan konteks dokumen regulasi; namun risiko ketidaksesuaian konteks masih ada.",
            "Keterbatasan: Evaluasi RAGAs menunjukkan Faithfulness 0,77 dan Answer Correctness 0,66; terbatas pada satu ranah regulasi pemda; bukan sistem layanan kantor hukum.\nPerbedaan: Sahabat Hukum memadukan RAG berpengetahuan terverifikasi dengan alur manajemen konsultasi, perkara, dokumen bukti, dan jadwal multi-peran."
        ],
        # Row 5: Fadillah
        [
            "5",
            "Fadillah dkk. (2026) [3]",
            "Optimasi retrieval dokumen hukum Indonesia (LawRAG) pada korpus undang-undang nasional.",
            "RAG dengan structural chunking berbasis hierarki regulasi (Judul, Bab, Pasal, Ayat) dan model reranker.",
            "Fokus pada algoritma pemotongan teks dan penelusuran dokumen hukum; bukan aplikasi sistem informasi manajemen layanan hukum.",
            "Mitigasi risiko dengan pemotongan teks struktural agar konteks pasal utuh dan relevan saat dikirim ke LLM; meminimalkan salah kutip regulasi.",
            "Keterbatasan: Pengujian terbatas pada metrik retrieval komputasi (Hit Rate, MRR); tidak menguji interaksi pengguna nyata dan proses bisnis hukum.\nPerbedaan: Sahabat Hukum mengadopsi teknik structural chunking untuk basis pengetahuan internal, namun mengintegrasikannya ke platform web operasional advokat-klien."
        ],
        # Row 6: Rivas
        [
            "6",
            "Rivas-Echeverría dkk. (2025) [4]",
            "Chatbot \"LegalBot-EC\" untuk bantuan pemahaman hukum pidana dan konstitusi di Ekuador.",
            "RAG menggunakan framework Python, ChromaDB, dan model LLaMA 3.1.",
            "Chatbot tanya-jawab mandiri; terpisah dari manajemen kantor advokat; tidak ada alur pembentukan perkara, permintaan berkas, dan notifikasi.",
            "Korpus terbatas pada teks KUHP dan Konstitusi Ekuador 2008; menyajikan rujukan pasal, namun rentan pada sintesis pertanyaan multi-dokumen.",
            "Keterbatasan: Evaluasi berbasis 25 pertanyaan uji (23 benar, 2 sebagian benar); korpus sempit; tidak ada mekanisme manajemen konsultasi formal.\nPerbedaan: Sahabat Hukum mengelola basis pengetahuan terverifikasi Admin yang dinamis, dilengkapi guardrails disclaimer, serta tombol alih instan ke konsultasi advokat."
        ],
        # Row 7: Lijaya
        [
            "7",
            "Lijaya dkk. (2025) [12]",
            "Analisis komparatif performa tiga model LLM open-source berbasis RAG berbahasa Indonesia (domain perbankan).",
            "RAG membandingkan Llama-3-8B, Mistral-7B, dan Gemma2-9B; evaluasi Semantic Similarity, LLM-as-a-Judge, dan validasi manusia.",
            "Sistem tanya-jawab layanan pelanggan berbasis data simulasi; domain perbankan; bukan sistem manajemen perkara hukum.",
            "Menekankan privasi data on-premise agar data pengguna tidak dikirim ke API pihak ketiga; sumber informasi berupa teks FAQ perbankan.",
            "Keterbatasan: Dataset simulasi sempit; LLM terbuka masih mengalami kesulitan pada inferensi logika terstruktur dan aritmetika.\nPerbedaan: Menjadi rujukan pertimbangan privasi data lokal bagi Sahabat Hukum; Sahabat Hukum menerapkan isolasi data perkara klien dari pipeline RAG."
        ],
        # Row 8: Magesh
        [
            "8",
            "Magesh dkk. (2025) [6]",
            "Evaluasi empiris pra-registrasi terhadap keandalan dan halusinasi alat riset hukum AI komersial (Lexis+ AI, Westlaw AI).",
            "Evaluasi empiris skala besar pada ribuan kueri hukum faktual dan doktrinal terhadap platform komersial berbasis RAG.",
            "Evaluasi perangkat lunak eksternal pihak ketiga untuk riset hukum profesional; bukan rancang bangun sistem baru.",
            "Menemukan bahwa RAG menurunkan halusinasi dibanding LLM generatif murni, namun tingkat halusinasi tetap bertahan pada kisaran 17%–33% (salah sitasi, distorsi putusan).",
            "Keterbatasan: Mengkaji sistem komersial hukum Amerika Serikat; bukan penelitian rekayasa perangkat lunak sistem informasi.\nPerbedaan: Memberikan landasan empiris fundamental bagi Sahabat Hukum bahwa RAG tidak boleh diklaim bebas kesalahan; mendasari perlunya guardrails, disclaimer, dan supervisi advokat."
        ],
        # Row 9: Lai
        [
            "9",
            "Lai dkk. (2024) [5]",
            "Survei komprehensif literatur global mengenai Large Language Models (LLM) di ranah hukum.",
            "Survei literatur sistematis dan taksonomi penerapan LLM hukum (prediksi putusan, temu balik informasi, konsultasi hukum).",
            "Kajian pustaka tingkat tinggi mengenai tren teknologi AI hukum global; tanpa implementasi artefak perangkat lunak.",
            "Menganalisis bahaya halusinasi, bias yurisdiksi, pelanggaran etika profesi advokat, dan kerentanan kebocoran kerahasiaan data klien.",
            "Keterbatasan: Bersifat teoritis dan konseptual; tidak menghasilkan sistem aplikasi operasional.\nPerbedaan: Landasan konseptual bagi Sahabat Hukum dalam menetapkan batasan etis: Asisten Hukum murni sarana informasi awal, bukan pengganti advokat berlisensi."
        ],
        # Row 10: Al-Qaesm
        [
            "10",
            "Al-Qaesm dkk. (2025) [16]",
            "Pengembangan \"ALKAFI-LLAMA3\" melalui adaptasi terkuantisasi LLM untuk pemahaman doktrin hukum Palestina.",
            "Supervised Fine-Tuning (SFT) model Llama-3.2-1B-Instruct dengan kuantisasi 4-bit (QLoRA) pada dataset tanya-jawab hukum.",
            "Model bahasa hukum untuk lingkungan komputasi terbatas; tidak memiliki platform manajemen kantor hukum, tanpa jadwal temu, tanpa berkas perkara.",
            "Fine-tuning statis tanpa retrieval dinamis; model memahami pola bahasa hukum tetapi tidak dapat memperbarui regulasi secara instan tanpa pelatihan ulang.",
            "Keterbatasan: Performa menurun pada pertanyaan perhitungan, daftar terstruktur, dan pembaruan aturan baru pasca-pelatihan.\nPerbedaan: Sahabat Hukum menggunakan pendekatan RAG dinamis dengan basis pengetahuan yang dapat diperbarui kapan saja oleh Admin tanpa perlu melatih ulang model."
        ]
    ]

    # Create new table matching dimensions
    new_table = doc.add_table(rows=len(table_data), cols=7)
    new_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    
    col_widths_dxa = [450, 1100, 1250, 1100, 1250, 1300, 1450] # Total ~ 7900 dxa
    
    for r_idx, row_content in enumerate(table_data):
        row = new_table.rows[r_idx]
        is_header = (r_idx == 0)
        fill_color = "D9E2F3" if is_header else None
        
        for c_idx, text_val in enumerate(row_content):
            cell = row.cells[c_idx]
            cell.width = col_widths_dxa[c_idx]
            set_cell_margins_and_border(cell, top=60, bottom=60, left=80, right=80, fill=fill_color)
            
            p = cell.paragraphs[0]
            p.text = text_val
            p.paragraph_format.line_spacing = 1.0
            p.paragraph_format.space_before = Pt(0)
            p.paragraph_format.space_after = Pt(0)
            
            if is_header or c_idx == 0:
                p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            else:
                p.alignment = WD_ALIGN_PARAGRAPH.LEFT
                
            for run in p.runs:
                run.font.size = Pt(8.5)
                if is_header:
                    run.bold = True
                elif c_idx == 0:
                    run.bold = True

    # Swap old table with new table
    old_table._element.addprevious(new_table._element)
    old_table._element.getparent().remove(old_table._element)

    # 9. Update Research Gap & Kontribusi (P116 & P117)
    p116 = doc.paragraphs[116]
    p116.text = (
        "Berdasarkan pemetaan analitis pada Tabel 2.1, posisi penelitian ini dirumuskan melalui kesenjangan nyata pada literatur "
        "yang dikaji. Kesenjangan pertama terletak pada pemisahan arsitektur antara sistem informasi manajemen hukum dan sistem "
        "asisten berbasis AI. Penelitian sistem informasi hukum ([7], [8]) berfokus pada komputerisasi administrasi berkas dan "
        "jadwal secara konvensional tanpa menyediakan sarana temu balik informasi cerdas, sehingga beban penjelasan prosedur dasar "
        "tetap menyita waktu praktisi. Sebaliknya, penelitian kecerdasan buatan hukum ([1]–[4], [16]) berfokus pada pengembangan "
        "chatbot tanya-jawab dokumen secara mandiri (standalone QA) yang terlepas dari proses penanganan perkara, jadwal advokat, "
        "dan pengelolaan dokumen bukti klien. Kesenjangan kedua terletak pada mitigasi risiko dan privasi: temuan empiris Magesh dkk. [6] "
        "bahwa halusinasi tetap bertahan pada kisaran 17%–33% pada sistem RAG menunjukkan bahwa implementasi AI hukum tidak boleh "
        "berdiri sendiri tanpa mekanisme pembatasan (guardrails), penolakan pertanyaan di luar cakupan, dan kewajiban mengarahkan "
        "klien ke advokat profesional. Selain itu, tuntutan kerahasiaan hubungan advokat-klien [10] dan pelindungan data pribadi [11] "
        "mengharuskan adanya isolasi ketat agar data perkara privat tidak bercampur dengan basis pengetahuan AI publik. "
        "Kajian ini bukan telaah sistematis menyeluruh, sehingga pernyataan kesenjangan ini berlaku pada kumpulan sumber yang dikaji."
    )

    p117 = doc.paragraphs[117]
    p117.text = (
        "Untuk menjawab kesenjangan tersebut, Sahabat Hukum mengajukan kebaruan rekayasa perangkat lunak terapan yang "
        "terangkum dalam empat pilar kontribusi: (1) Integrasi Fungsional Layanan Terpadu: mengintegrasikan Asisten Hukum berbasis "
        "RAG sebagai sarana pra-konsultasi cerdas yang terhubung langsung dengan alur manajemen konsultasi, jadwal kalender, perkara, "
        "permintaan dan verifikasi berkas bukti digital, percakapan internal, dan pemberitahuan multi-peran (Klien, Advokat, Admin) "
        "dalam satu ekosistem web berbasis Laravel dan MySQL; (2) Tata Kelola Basis Pengetahuan Terverifikasi & Isolasi Data Privat: "
        "menerapkan mekanisme kurasi basis pengetahuan internal yang dikelola dan diaudit oleh Admin (disertai metadata sumber dan "
        "structural chunking [3]), dengan isolasi mutlak terhadap data perkara dan dokumen bukti Klien guna menjamin privasi dan "
        "kepatuhan kode etik advokat; (3) Mekanisme Pembatasan Respons Berlapis (Guardrails & Referral): merancang instruksi sistem "
        "dan ambang batas kemiripan vektor yang secara otomatis menolak memberikan pertimbangan hukum mengikat atau memprediksi hasil "
        "sidang, menampilkan klausul sanggahan (disclaimer) baku, serta menyediakan tombol aksi instan untuk mengarahkan Klien mengajukan "
        "konsultasi resmi dengan Advokat; dan (4) Skema Evaluasi Multi-Dimensi: merencanakan pengujian berlapis yang menggabungkan validasi "
        "fungsional Black-box Testing [15], evaluasi kebergunaan dan penerimaan pengguna melalui UAT berbasis System Usability Scale (SUS) [21], "
        "serta evaluasi kualitas retrieval dan jawaban Asisten Hukum menggunakan metrik relevansi konteks, kesesuaian sumber (faithfulness) [19], "
        "relevansi jawaban, kelengkapan, penanganan di luar cakupan, dan waktu respons. Seluruh kontribusi tersebut berstatus rancangan dan "
        "baru dapat dinyatakan berhasil setelah tahap implementasi dan pengujian sistem selesai dilaksanakan."
    )

    # 10. Update Landasan Teori: P124, P128, P130, P132 for RAG consistency
    doc.paragraphs[124].text = (
        "Konsultasi hukum adalah proses interaksi profesional antara Klien dan Advokat untuk menganalisis fakta, mengkaji norma hukum, "
        "dan menentukan strategi penanganan perkara. Undang-Undang Nomor 18 Tahun 2003 tentang Advokat mewajibkan Advokat merahasiakan "
        "segala sesuatu yang diketahui atau diperoleh dari Kliennya karena hubungan profesi [10], yang diperkuat oleh Undang-Undang Nomor 27 "
        "Tahun 2022 tentang Pelindungan Data Pribadi [11]. Karena analisis hukum dan pemberian nasihat mengikat merupakan kewenangan "
        "eksklusif Advokat berlisensi, Sahabat Hukum memposisikan sistem web untuk mengelola dan mencatat alur konsultasi resmi, sementara "
        "Asisten Hukum berbasis RAG dibatasi secara tegas hanya untuk informasi awal umum dan operasional kantor. Data perkara, berkas bukti, "
        "dan percakapan privat Klien diisolasi secara mutlak dari pipeline RAG."
    )

    doc.paragraphs[128].text = (
        "Large Language Model (LLM) adalah model kecerdasan buatan berbasis deep learning yang dilatih pada korpus teks berskala masif "
        "untuk menghasilkan teks secara generatif [5]. Namun, ketergantungan pada memori parametrik internal membuat LLM murni rentan "
        "mengalami halusinasi dan memproduksi rujukan hukum fiktif [6]. RAG memitigasi keterbatasan ini dengan mengombinasikan komponen "
        "retrieval (pengambilan dokumen rujukan yang relevan dari korpus terverifikasi) dan komponen generation (penyusunan respons "
        "berdasarkan konteks yang ditemukan) [9]. RAG tidak menjamin 100% jawaban selalu benar secara otomatis [6], sehingga rancangan "
        "Sahabat Hukum memperkuatnya dengan batasan basis pengetahuan internal terverifikasi oleh Admin, sitasi sumber rujukan transparan, "
        "klausul sanggahan (disclaimer) otomatis, serta penolakan menjawab yang disertai arahan untuk berkonsultasi langsung dengan Advokat."
    )

    doc.paragraphs[130].text = (
        "Basis pengetahuan adalah kumpulan dokumen otoritatif yang menjadi rujukan tunggal bagi proses temu balik informasi pada Asisten "
        "Hukum Sahabat Hukum. Sumber pengetahuan tidak diambil dari perayapan web eksternal yang tidak terkontrol, melainkan dari dokumen "
        "internal yang diverifikasi Admin (SOP layanan, persyaratan berkas perkara, dan ringkasan hukum umum). Dokumen dipecah menjadi "
        "potongan teks (chunk) menggunakan strategi structural chunking berbasis hierarki regulasi (judul, bab, pasal, ayat) yang terbukti "
        "lebih akurat daripada pemotongan sekuensial [3]. Setiap potongan dikonversi menjadi representasi vektor numerik menggunakan model "
        "embedding dan disimpan dalam basis data vektor beserta metadata sumber (judul dokumen, bab, nomor pasal) untuk memungkinkan sitasi "
        "yang dapat diaudit."
    )

    doc.paragraphs[132].text = (
        "Evaluasi kualitas sistem RAG memerlukan penilaian terukur pada komponen temu balik (retrieval) dan komponen pembangkitan jawaban "
        "(generation). Kerangka kerja otomatis seperti RAGAs [19] memperkenalkan metrik tanpa acuan manusia, antara lain context relevancy "
        "(ketepatan konteks yang diambil), faithfulness (tingkat kesesuaian jawaban dengan konteks rujukan tanpa halusinasi), dan answer "
        "relevancy (relevansi jawaban terhadap kueri pengguna). Pada domain hukum berbahasa Indonesia, metrik ini telah digunakan oleh "
        "Rahmadi dan Setyanto [2]. Mengingat sensitivitas informasi hukum, penelitian ini merencanakan kombinasi metrik otomatis RAGAs dengan "
        "penilaian manual (expert judgment) oleh Advokat mitra terhadap sekumpulan pertanyaan uji, ditambah pengukuran ketepatan penolakan "
        "pertanyaan di luar cakupan (out-of-scope handling) dan pencatatan waktu respons (latency)."
    )

    # 11. Update Table 9 (Tabel 3.7 Rencana Evaluasi Asisten Hukum)
    t9 = doc.tables[9]
    t9_data = [
        ["No", "Aspek Evaluasi", "Metode / Cara Pengukuran", "Instrumen & Metrik"],
        ["1", "Ketepatan Retrieval (Context Precision / Relevancy)", "Memeriksa apakah potongan teks (chunks) yang diambil dari basis data vektor benar-benar relevan dengan kueri pertanyaan Klien.", "RAGAs Context Relevancy / perbandingan manual terhadap dokumen acuan ground-truth."],
        ["2", "Kesesuaian dengan Sumber (Faithfulness / Groundedness)", "Mengukur apakah seluruh klaim faktual dalam jawaban didukung oleh potongan konteks yang diambil (tidak mengandung halusinasi atau fabrikasi).", "RAGAs Faithfulness metric dan verifikasi manual kesesuaian rujukan oleh Advokat/ahli."],
        ["3", "Relevansi Jawaban (Answer Relevancy)", "Menilai apakah respons yang dihasilkan menjawab maksud dan kebutuhan informasi yang diajukan dalam pertanyaan pengguna secara logis.", "RAGAs Answer Relevancy dan penilaian skala Likert (1–5) oleh Advokat/ahli."],
        ["4", "Kelengkapan Informasi", "Menilai apakah jawaban mencakup butir-butir informasi penting dan persyaratan yang termaktub dalam dokumen rujukan.", "Rubrik penilaian kelengkapan jawaban terhadap jawaban acuan (ground truth benchmark)."],
        ["5", "Penanganan Pertanyaan di Luar Cakupan (Out-of-Scope Fallback)", "Menguji respons sistem terhadap pertanyaan yang menuntut nasihat hukum mengikat, prediksi putusan perkara, atau di luar korpus basis pengetahuan.", "Dataset pertanyaan uji di luar cakupan; diukur dari proporsi penolakan tepat disertai disclaimer dan tombol arahan ke konsultasi advokat."],
        ["6", "Waktu Respons (Latency)", "Mengukur durasi waktu komputasi dari saat pengguna mengirimkan pertanyaan hingga respons lengkap tertampil pada antarmuka web.", "Pencatatan timestamp otomatis pada tabel assistant_logs di sistem."]
    ]
    for r_idx, row_vals in enumerate(t9_data):
        row = t9.rows[r_idx]
        for c_idx, val in enumerate(row_vals):
            row.cells[c_idx].paragraphs[0].text = val
            row.cells[c_idx].paragraphs[0].paragraph_format.line_spacing = 1.0
            row.cells[c_idx].paragraphs[0].paragraph_format.space_after = Pt(0)
            if r_idx == 0:
                row.cells[c_idx].paragraphs[0].runs[0].bold = True

    # 12. Update Table 11 (Tabel 3.9 Indikator Keberhasilan)
    t11 = doc.tables[11]
    t11_data = [
        ["No", "Aspek Pengujian", "Indikator Kinerja", "Target Keberhasilan (Rencana)"],
        ["1", "Kesesuaian Fungsional", "Proporsi skenario pengujian Black-box Testing yang berstatus lulus (pass) pada seluruh modul sistem tiga peran.", "100% skenario fungsi kritis lulus; tidak ada cacat berkategori kritis (critical bug)."],
        ["2", "Penerimaan & Kebergunaan", "Rata-rata skor System Usability Scale (SUS) dari responden Klien, Advokat, dan Admin.", "Mencapai kategori minimal \"Acceptable\" dengan skor rata-rata SUS > 70."],
        ["3", "Kualitas Asisten Hukum", "Kesesuaian jawaban dengan konteks (faithfulness), relevansi retrieval, dan relevansi jawaban pada pertanyaan uji dalam cakupan.", "Faithfulness > 0,80; Answer Relevancy > 0,80; serta validitas substansi rujukan oleh Advokat/ahli."],
        ["4", "Penanganan di Luar Cakupan", "Proporsi keberhasilan sistem menolak memberikan keputusan hukum dan mengarahkan Klien ke konsultasi Advokat pada pertanyaan di luar cakupan.", "Target 100% pertanyaan di luar cakupan ditolak dengan disclaimer dan diarahkan ke Advokat."],
        ["5", "Efisiensi Kinerja (Waktu)", "Waktu respons rata-rata Asisten Hukum dari input pengguna hingga output jawaban tertampil.", "Waktu respons rata-rata < 10 detik per respons (sesuai target awal)."]
    ]
    for r_idx, row_vals in enumerate(t11_data):
        row = t11.rows[r_idx]
        for c_idx, val in enumerate(row_vals):
            row.cells[c_idx].paragraphs[0].text = val
            row.cells[c_idx].paragraphs[0].paragraph_format.line_spacing = 1.0
            row.cells[c_idx].paragraphs[0].paragraph_format.space_after = Pt(0)
            if r_idx == 0:
                row.cells[c_idx].paragraphs[0].runs[0].bold = True

    # Save revised docx
    output_path = r'c:\laragon\www\SahabatHukum\PROPOSAL_TA_Sahabat_Hukum_Anggre_Ghea_Novika_Revisi.docx'
    doc.save(output_path)
    print('Updated docx saved successfully to:', output_path)
    
    # Also copy to Documents folder
    user_docs_dest = r'C:\Users\Admin\Documents\dokumen TA_Anggre bismillah\PROPOSAL_TA_Sahabat_Hukum_Anggre_Ghea_Novika_Revisi.docx'
    shutil.copyfile(output_path, user_docs_dest)
    print('Copied successfully to user documents:', user_docs_dest)

if __name__ == '__main__':
    main()
