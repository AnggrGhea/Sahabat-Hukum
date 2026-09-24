{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- COMPONENT: Document Scanner Modal Profesional ala CamScanner             --}}
{{-- Digunakan bersama oleh: KLIEN & ADVOKAT                                    --}}
{{-- Fitur: Live Document Detection, Anti-Mirror, No Auto-Capture,              --}}
{{--        Manual Shutter, Perspective Warp, Magic Color, Satu/Multi-Halaman   --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div id="camScannerModal" class="cam-scanner-overlay" style="display:none;" aria-hidden="true">
    <div class="cam-scanner-container">

        {{-- Toast Notification Banner --}}
        <div id="camAutoNotice" class="cam-auto-notice" style="display:none;"></div>

        {{-- ── TOP NAVIGATION BAR ── --}}
        <div class="cam-top-bar">
            <button type="button" class="cam-nav-btn" id="camBackBtn" title="Kembali">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </button>
            <div class="cam-step-title" id="camStepTitle">Pindai Dokumen</div>
            <button type="button" class="cam-nav-btn" id="camCloseBtn" title="Tutup Scanner">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- ── SCREEN 1: CAMERA CAPTURE (LIVE DETECTION) ── --}}
        <div class="cam-screen" id="camScreenCapture" style="display:flex;">
            <div class="cam-video-viewport" id="camVideoViewport">
                {{-- Live Camera Video: Anti-Mirror (Normal Text Orientation) --}}
                <video id="camVideo" playsinline autoplay muted></video>

                {{-- Real-Time Dynamic Polygon Overlay (Tracking 4 Sudut Dokumen) --}}
                <canvas id="camLiveCanvas" class="cam-live-canvas"></canvas>

                {{-- Shutter Visual Flash Effect --}}
                <div id="camFlashOverlay" class="cam-flash-overlay"></div>

                {{-- Live Detection Status Pill --}}
                <div class="cam-status-pill-wrap">
                    <div id="camLiveStatus" class="cam-status-pill">
                        <span class="cam-status-dot"></span>
                        <span id="camStatusText">Posisikan dokumen di dalam kamera</span>
                    </div>
                </div>

                {{-- Floating Multi-Page Status Bar (Hanya tampil saat mode Beberapa Halaman & sudah ada halaman) --}}
                <div id="camMultiFloatingBar" class="cam-multi-floating-bar" style="display:none;">
                    <div class="cam-multi-badge">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                            <line x1="8" y1="7" x2="16" y2="7"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                        <span id="camMultiCount">0 Halaman Siap</span>
                    </div>
                    <button type="button" class="cam-btn-finish" id="camMultiFinishBtn">
                        Selesai Scan &rarr;
                    </button>
                </div>
            </div>

            {{-- Fallback Warning / Button if Camera Denied --}}
            <div class="cam-fallback-notice" id="camFallbackNotice" style="display:none;">
                <p>Kamera tidak dapat diakses atau izin belum diberikan.</p>
                <button type="button" class="cam-btn-outline" onclick="document.getElementById('camFallbackInput').click()">
                    Pilih Berkas / Foto dari Perangkat
                </button>
            </div>
            <input type="file" id="camFallbackInput" accept="image/*" style="display:none;">

            {{-- Mode Switch (Satu Halaman vs Beberapa Halaman) --}}
            <div class="cam-mode-container">
                <div class="cam-mode-switch">
                    <button type="button" class="cam-mode-tab active" id="camModeSingleBtn" data-mode="single">Satu Halaman</button>
                    <button type="button" class="cam-mode-tab" id="camModeMultiBtn" data-mode="multi">Beberapa Halaman</button>
                </div>
            </div>

            {{-- Bottom Shutter Bar --}}
            <div class="cam-bottom-bar">
                <button type="button" class="cam-tool-btn" id="camGalleryBtn" title="Pilih Foto dari Galeri">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Impor Foto</span>
                </button>

                <div class="cam-shutter-wrapper">
                    <button type="button" class="cam-shutter-btn" id="camShutterBtn" title="Tekan untuk Scan">
                        <span class="cam-shutter-inner"></span>
                    </button>
                    <span class="cam-shutter-tag">Scan</span>
                </div>

                <button type="button" class="cam-tool-btn" id="camSwitchBtn" title="Ganti Kamera">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 4v6h-6"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    <span>Kamera</span>
                </button>
            </div>
        </div>

        {{-- ── SCREEN 2: CROP 4 SUDUT & MANUAL ADJUSTMENT ── --}}
        <div class="cam-screen" id="camScreenCrop" style="display:none;">
            <div class="cam-crop-viewport" id="camCropViewport">
                <div class="cam-crop-stage" id="camCropStage">
                    <img id="camCropImage" alt="Preview Dokumen">
                    <svg id="camCropSvg" class="cam-crop-svg">
                        <polygon id="camCropPolygon" class="cam-crop-polygon" points=""></polygon>
                    </svg>
                    {{-- 4 Draggable Corner Handles --}}
                    <div class="cam-handle" id="camHandleTL" data-corner="0" title="Sudut Kiri Atas"></div>
                    <div class="cam-handle" id="camHandleTR" data-corner="1" title="Sudut Kanan Atas"></div>
                    <div class="cam-handle" id="camHandleBR" data-corner="2" title="Sudut Kanan Bawah"></div>
                    <div class="cam-handle" id="camHandleBL" data-corner="3" title="Sudut Kiri Bawah"></div>

                    {{-- Magnifying Loupe Zoom --}}
                    <div class="cam-loupe" id="camLoupe" style="display:none;">
                        <canvas id="camLoupeCanvas" width="120" height="120"></canvas>
                        <div class="cam-loupe-crosshair"></div>
                    </div>
                </div>
            </div>

            {{-- Crop Controls Bar --}}
            <div class="cam-action-bar">
                <button type="button" class="cam-btn-outline" id="camCropRotateBtn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                    Putar 90°
                </button>
                <button type="button" class="cam-btn-outline" id="camCropResetBtn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3z"/>
                    </svg>
                    Pilih Seluruhnya
                </button>
                <button type="button" class="cam-btn-primary" id="camCropApplyBtn">
                    Ratakan Dokumen
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── SCREEN 3: FILTER ENHANCEMENT & MULTI-PAGE REVIEW ── --}}
        <div class="cam-screen" id="camScreenReview" style="display:none;">
            {{-- Filter Enhancement Chips Bar --}}
            <div class="cam-filter-bar">
                <button type="button" class="cam-filter-chip active" data-filter="magic">
                    <span class="chip-sparkle">✦</span> Dokumen Jernih
                </button>
                <button type="button" class="cam-filter-chip" data-filter="bw">
                    Hitam & Putih
                </button>
                <button type="button" class="cam-filter-chip" data-filter="grayscale">
                    Grayscale
                </button>
                <button type="button" class="cam-filter-chip" data-filter="original">
                    Foto Asli
                </button>
            </div>

            {{-- Processed Document Preview Canvas --}}
            <div class="cam-review-viewport">
                <canvas id="camProcessedCanvas"></canvas>
            </div>

            {{-- Multi-Page Thumbnails Strip --}}
            <div class="cam-pages-strip">
                <div class="cam-pages-list" id="camPagesList">
                    {{-- Dynamically populated pages --}}
                </div>
                <button type="button" class="cam-add-page-btn" id="camAddPageBtn" title="Tambah Halaman Baru">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>+ Halaman</span>
                </button>
            </div>

            {{-- Review Action Bar --}}
            <div class="cam-action-bar">
                <button type="button" class="cam-btn-outline" id="camRetakePageBtn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                    </svg>
                    Scan Ulang
                </button>
                <button type="button" class="cam-btn-outline" id="camManualCropBtn" title="Koreksi posisi 4 sudut dokumen secara manual">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>
                    </svg>
                    Sesuaikan Sudut
                </button>
                <button type="button" class="cam-btn-primary" id="camProceedSaveBtn">
                    Lanjut Simpan PDF
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── SCREEN 4: METADATA & FINAL SIMPAN PDF ── --}}
        <div class="cam-screen" id="camScreenMetadata" style="display:none;">
            <div class="cam-meta-wrapper">
                <div class="cam-meta-card">
                    <h3 class="cam-meta-title">Informasi Dokumen Perkara</h3>
                    <p class="cam-meta-desc">Dokumen telah dikonversi ke format PDF standar A4. Silakan lengkapi nama dan jenis dokumen sebelum disimpan.</p>

                    <form id="camMetaForm" onsubmit="camScanner.submitFinalDocument(event)">
                        {{-- Select Case (if not preselected) --}}
                        <div class="cam-form-group" id="camCaseSelectGroup" style="display:none;">
                            <label class="cam-form-label">Pilih Perkara <span class="req">*</span></label>
                            <select id="camCaseSelect" class="cam-form-input">
                                {{-- Populated dynamically --}}
                            </select>
                        </div>

                        <div class="cam-form-group">
                            <label class="cam-form-label">Nama / Judul Dokumen <span class="req">*</span></label>
                            <input type="text" id="camDocName" class="cam-form-input" required placeholder="Contoh: KTP Klien, Surat Kuasa Khusus, Bukti Surat..." maxlength="255">
                        </div>

                        <div class="cam-form-group">
                            <label class="cam-form-label">Jenis Dokumen <span class="req">*</span></label>
                            <select id="camDocType" class="cam-form-input" required>
                                <option value="Identitas Diri (KTP/SIM/Paspor)">Identitas Diri (KTP / SIM / Paspor)</option>
                                <option value="Surat Kuasa">Surat Kuasa</option>
                                <option value="Alat Bukti Surat/Dokumen">Alat Bukti Surat / Dokumen</option>
                                <option value="Bukti Pembayaran/Transfer">Bukti Pembayaran / Transfer</option>
                                <option value="Perjanjian/Kontrak">Perjanjian / Kontrak</option>
                                <option value="Dokumen Lainnya" selected>Dokumen Lainnya</option>
                            </select>
                        </div>

                        <div class="cam-form-group">
                            <label class="cam-form-label">Keterangan / Catatan Tambahan</label>
                            <textarea id="camDocDesc" class="cam-form-input" rows="2" placeholder="Catatan singkat mengenai isi berkas ini (opsional)..."></textarea>
                        </div>

                        <div class="cam-doc-summary-badge">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            <div>
                                <strong id="camSummaryPages">1 Halaman</strong> telah siap digabungkan menjadi satu berkas PDF.
                            </div>
                        </div>

                        <div class="cam-action-bar" style="margin-top:20px;padding:0;background:transparent;border:none;">
                            <button type="button" class="cam-btn-outline" id="camBackToReviewBtn">
                                Kembali ke Editor
                            </button>
                            <button type="submit" class="cam-btn-primary" id="camSubmitBtn">
                                <span id="camSubmitText">Simpan Dokumen (PDF)</span>
                                <span id="camSubmitSpinner" style="display:none;">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- STYLES: CamScanner Clean Dark Theme + Professional Overlays              --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<style>
.cam-scanner-overlay {
    position: fixed;
    inset: 0;
    z-index: 100000;
    background: #090d16;
    color: #f8fafc;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.cam-scanner-container {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    max-width: 900px;
    margin: 0 auto;
    position: relative;
}

.cam-auto-notice {
    position: absolute;
    top: 64px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.94);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(0, 242, 254, 0.45);
    color: #00f2fe;
    font-size: 0.76rem;
    font-weight: 600;
    padding: 7px 18px;
    border-radius: 20px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    z-index: 1000;
    pointer-events: none;
    transition: opacity 0.25s ease;
    white-space: nowrap;
}

.cam-top-bar {
    height: 52px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #0f172a;
    border-bottom: 1px solid #1e293b;
    z-index: 10;
}

.cam-step-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #f1f5f9;
    letter-spacing: 0.2px;
}

.cam-nav-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, color 0.15s;
}
.cam-nav-btn:hover {
    background: #1e293b;
    color: #fff;
}

.cam-screen {
    flex: 1;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

/* ── Screen 1: Camera Viewport (Anti-Mirror) ── */
.cam-video-viewport {
    flex: 1;
    position: relative;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

/* Camera Video: strictly anti-mirror by default for rear camera */
#camVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: none !important;
    -webkit-transform: none !important;
}

/* Live Dynamic Polygon Canvas Overlay */
.cam-live-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 5;
}

/* Shutter Flash Animation */
.cam-flash-overlay {
    position: absolute;
    inset: 0;
    background: #ffffff;
    opacity: 0;
    pointer-events: none;
    z-index: 25;
    transition: opacity 0.18s ease-out;
}
.cam-flash-overlay.active {
    opacity: 0.85;
    transition: none;
}

/* Live Real-time Status Badge */
.cam-status-pill-wrap {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    pointer-events: none;
}
.cam-status-pill {
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.16);
    color: #e2e8f0;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    transition: all 0.22s ease;
    white-space: nowrap;
}
.cam-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #94a3b8;
    transition: background 0.2s, box-shadow 0.2s;
}

/* Status variants */
.cam-status-pill.detected {
    border-color: rgba(0, 242, 254, 0.7);
    color: #00f2fe;
}
.cam-status-pill.detected .cam-status-dot {
    background: #00f2fe;
    box-shadow: 0 0 8px #00f2fe;
}
.cam-status-pill.ready {
    border-color: rgba(16, 185, 129, 0.8);
    color: #34d399;
}
.cam-status-pill.ready .cam-status-dot {
    background: #10b981;
    box-shadow: 0 0 10px #10b981;
}

/* Floating Multi-page Bar */
.cam-multi-floating-bar {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(0, 242, 254, 0.4);
    padding: 6px 14px;
    border-radius: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    white-space: nowrap;
}
.cam-multi-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.76rem;
    color: #bae6fd;
    font-weight: 600;
}
.cam-btn-finish {
    background: #10b981;
    border: none;
    color: #fff;
    padding: 5px 12px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
}
.cam-btn-finish:hover {
    background: #059669;
    transform: scale(1.03);
}

.cam-fallback-notice {
    padding: 20px;
    text-align: center;
    background: #1e293b;
    border-radius: 10px;
    margin: 20px;
    color: #e2e8f0;
    font-size: 0.85rem;
}

/* Mode Switch (Satu Halaman vs Beberapa Halaman) */
.cam-mode-container {
    height: 42px;
    background: #090d16;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    border-top: 1px solid #131d2e;
}
.cam-mode-switch {
    display: inline-flex;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 24px;
    padding: 3px;
    gap: 3px;
}
.cam-mode-tab {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 5px 14px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.18s ease;
}
.cam-mode-tab.active {
    background: #0284c7;
    color: #fff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.4);
}

/* Bottom Shutter Bar */
.cam-bottom-bar {
    height: 94px;
    background: #090d16;
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 0 20px;
    border-top: 1px solid #1e293b;
}

.cam-tool-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    font-size: 0.7rem;
    padding: 8px 12px;
    border-radius: 8px;
    min-width: 60px;
}
.cam-tool-btn:hover {
    color: #f1f5f9;
    background: rgba(255, 255, 255, 0.05);
}

.cam-shutter-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}
.cam-shutter-tag {
    font-size: 0.72rem;
    font-weight: 700;
    color: #00f2fe;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.cam-shutter-btn {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: transparent;
    border: 4px solid #00f2fe;
    box-shadow: 0 0 14px rgba(0, 242, 254, 0.35);
    padding: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s, box-shadow 0.15s;
}
.cam-shutter-btn:hover {
    box-shadow: 0 0 22px rgba(0, 242, 254, 0.65);
}
.cam-shutter-btn:active {
    transform: scale(0.92);
}
.cam-shutter-inner {
    width: 100%;
    height: 100%;
    background: #fff;
    border-radius: 50%;
    display: block;
}

/* ── Screen 2: Crop & 4 Corners ── */
.cam-crop-viewport {
    flex: 1;
    position: relative;
    background: #020617;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    touch-action: none;
}

.cam-crop-stage {
    position: relative;
    user-select: none;
}

#camCropImage {
    display: block;
    max-width: 100%;
    max-height: 100%;
    pointer-events: none;
}

.cam-crop-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.cam-crop-polygon {
    fill: rgba(0, 242, 254, 0.15);
    stroke: #00f2fe;
    stroke-width: 2.5;
    stroke-dasharray: 4 2;
}

.cam-handle {
    position: absolute;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #00f2fe;
    border: 3px solid #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
    transform: translate(-50%, -50%);
    cursor: grab;
    z-index: 50;
    touch-action: none;
}
.cam-handle:active {
    cursor: grabbing;
    background: #0284c7;
    transform: translate(-50%, -50%) scale(1.15);
}

.cam-loupe {
    position: absolute;
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid #00f2fe;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.65);
    overflow: hidden;
    pointer-events: none;
    z-index: 100;
    background: #000;
    transform: translate(-50%, -130%);
}
#camLoupeCanvas {
    width: 100%;
    height: 100%;
}
.cam-loupe-crosshair {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cam-loupe-crosshair::before {
    content: '';
    position: absolute;
    width: 14px;
    height: 2px;
    background: #00f2fe;
}
.cam-loupe-crosshair::after {
    content: '';
    position: absolute;
    width: 2px;
    height: 14px;
    background: #00f2fe;
}

/* ── Screen 3: Filter & Multi-Page Review ── */
.cam-filter-bar {
    height: 48px;
    background: #0f172a;
    border-bottom: 1px solid #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 12px;
    overflow-x: auto;
}

.cam-filter-chip {
    padding: 5px 14px;
    border-radius: 18px;
    border: 1px solid #334155;
    background: transparent;
    color: #94a3b8;
    font-size: 0.74rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.cam-filter-chip:hover {
    border-color: #64748b;
    color: #e2e8f0;
}
.cam-filter-chip.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #fff;
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
}
.cam-filter-chip .chip-sparkle {
    color: #facc15;
    font-size: 0.8rem;
}

.cam-review-viewport {
    flex: 1;
    background: #020617;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overflow: hidden;
}

#camProcessedCanvas {
    max-width: 100%;
    max-height: 100%;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.7);
    border-radius: 4px;
    background: #fff;
}

.cam-pages-strip {
    height: 94px;
    background: #0f172a;
    border-top: 1px solid #1e293b;
    display: flex;
    align-items: center;
    padding: 8px 16px;
    gap: 12px;
}

.cam-pages-list {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    flex: 1;
    height: 100%;
    padding: 2px 0;
}

.cam-page-thumb-card {
    width: 54px;
    height: 74px;
    border-radius: 6px;
    border: 2px solid #334155;
    background: #1e293b;
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
    overflow: hidden;
    transition: border-color 0.15s;
}
.cam-page-thumb-card.active {
    border-color: #00f2fe;
    box-shadow: 0 0 10px rgba(0, 242, 254, 0.4);
}
.cam-page-thumb-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cam-page-num {
    position: absolute;
    bottom: 2px;
    left: 2px;
    right: 2px;
    background: rgba(15, 23, 42, 0.85);
    color: #fff;
    font-size: 0.62rem;
    font-weight: 700;
    text-align: center;
    border-radius: 3px;
    padding: 1px 0;
}
.cam-page-del-btn {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ef4444;
    color: #fff;
    border: none;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    line-height: 1;
}

.cam-add-page-btn {
    width: 60px;
    height: 74px;
    border-radius: 6px;
    border: 2px dashed #475569;
    background: rgba(30, 41, 59, 0.4);
    color: #94a3b8;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 0.65rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.15s;
}
.cam-add-page-btn:hover {
    border-color: #00f2fe;
    color: #00f2fe;
}

/* ── Screen 4: Metadata Card ── */
.cam-meta-wrapper {
    flex: 1;
    overflow-y: auto;
    padding: 24px 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #020617;
}

.cam-meta-card {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 24px;
    width: 100%;
    max-width: 520px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.cam-meta-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin: 0 0 6px 0;
}

.cam-meta-desc {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0 0 18px 0;
    line-height: 1.4;
}

.cam-form-group {
    margin-bottom: 14px;
}

.cam-form-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 6px;
}
.cam-form-label .req {
    color: #ef4444;
}

.cam-form-input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #334155;
    border-radius: 8px;
    background: #1e293b;
    color: #f1f5f9;
    font-size: 0.85rem;
    outline: none;
    box-sizing: border-box;
}
.cam-form-input:focus {
    border-color: #00f2fe;
}

.cam-doc-summary-badge {
    background: rgba(0, 242, 254, 0.08);
    border: 1px solid rgba(0, 242, 254, 0.25);
    color: #bae6fd;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
}

/* ── Generic Action Bars & Buttons ── */
.cam-action-bar {
    height: 64px;
    background: #090d16;
    border-top: 1px solid #1e293b;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
    gap: 10px;
}

.cam-btn-outline {
    padding: 9px 16px;
    background: transparent;
    border: 1px solid #334155;
    color: #e2e8f0;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
}
.cam-btn-outline:hover {
    background: #1e293b;
    border-color: #64748b;
}

.cam-btn-primary {
    padding: 9px 18px;
    background: #0284c7;
    border: none;
    color: #fff;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
    transition: all 0.15s;
}
.cam-btn-primary:hover {
    background: #0369a1;
}
.cam-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 600px) {
    .cam-action-bar {
        padding: 0 12px;
    }
    .cam-btn-outline, .cam-btn-primary {
        padding: 8px 10px;
        font-size: 0.72rem;
    }
}
</style>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- SCRIPT: CamScanner Controller & Live Computer Vision Engine               --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<script>
window.camScanner = (function() {
    // ── State ──
    let activeOptions = {
        caseId: null,
        casesList: [],
        isAdvocate: false,
        documentRequestId: null,
        presetName: '',
        presetType: 'Dokumen Lainnya',
    };

    let stream = null;
    let facingMode = 'environment'; // Kamera belakang default
    let currentStep = 'capture';     // 'capture', 'crop', 'review', 'metadata'
    let scanMode = 'single';         // 'single' (Satu Halaman) | 'multi' (Beberapa Halaman)

    let pages = []; // [{ id, sourceImgSrc, cleanCanvas, corners, rotation, filter, displayCanvas }]
    let activePageIndex = 0;
    let isReEditingPage = false;

    // Crop UI drag tracking
    let isDragging = false;
    let activeCornerIndex = -1;
    let cropImgRect = { x: 0, y: 0, width: 0, height: 0 };
    let cropDisplayScale = 1;
    let cropRotation = 0;

    // ── Live Document Detection Engine State ──
    let isLiveDetecting = false;
    let liveDetectCanvas = null;
    let liveDetectCtx = null;
    let liveOverlayCanvas = null;
    let liveOverlayCtx = null;
    let smoothedLiveCorners = null; // [{x, y}] in normalized [0..1] coordinates
    let liveStabilityScore = 0;
    let lastDetectTimestamp = 0;
    let lastCornerSnapshot = null;

    // DOM Elements
    let modal, video, shutterBtn, switchBtn, fallbackNotice, fallbackInput;
    let screenCapture, screenCrop, screenReview, screenMetadata;
    let cropStage, cropImg, cropSvg, cropPolygon, loupe, loupeCanvas;
    let handles = [];
    let filterChips = [];
    let processedCanvas, pagesList;

    function init() {
        modal          = document.getElementById('camScannerModal');
        video          = document.getElementById('camVideo');
        shutterBtn     = document.getElementById('camShutterBtn');
        switchBtn      = document.getElementById('camSwitchBtn');
        fallbackNotice = document.getElementById('camFallbackNotice');
        fallbackInput  = document.getElementById('camFallbackInput');

        liveOverlayCanvas = document.getElementById('camLiveCanvas');
        if (liveOverlayCanvas) {
            liveOverlayCtx = liveOverlayCanvas.getContext('2d');
        }

        // Lightweight offscreen canvas for live frame CV processing
        liveDetectCanvas = document.createElement('canvas');
        liveDetectCanvas.width = 320;
        liveDetectCanvas.height = 240;
        liveDetectCtx = liveDetectCanvas.getContext('2d', { willReadFrequently: true });

        screenCapture  = document.getElementById('camScreenCapture');
        screenCrop     = document.getElementById('camScreenCrop');
        screenReview   = document.getElementById('camScreenReview');
        screenMetadata = document.getElementById('camScreenMetadata');

        cropStage      = document.getElementById('camCropStage');
        cropImg        = document.getElementById('camCropImage');
        cropSvg        = document.getElementById('camCropSvg');
        cropPolygon    = document.getElementById('camCropPolygon');
        loupe          = document.getElementById('camLoupe');
        loupeCanvas    = document.getElementById('camLoupeCanvas');

        handles = [
            document.getElementById('camHandleTL'),
            document.getElementById('camHandleTR'),
            document.getElementById('camHandleBR'),
            document.getElementById('camHandleBL')
        ];

        processedCanvas = document.getElementById('camProcessedCanvas');
        pagesList       = document.getElementById('camPagesList');
        filterChips     = document.querySelectorAll('.cam-filter-chip');

        bindEvents();
    }

    function bindEvents() {
        // Navigation buttons
        document.getElementById('camCloseBtn').addEventListener('click', closeScanner);
        document.getElementById('camBackBtn').addEventListener('click', handleBackStep);

        // Mode switch tabs
        const singleBtn = document.getElementById('camModeSingleBtn');
        const multiBtn  = document.getElementById('camModeMultiBtn');
        if (singleBtn && multiBtn) {
            singleBtn.addEventListener('click', () => setScanMode('single'));
            multiBtn.addEventListener('click', () => setScanMode('multi'));
        }

        // Multi-page finish button
        const multiFinishBtn = document.getElementById('camMultiFinishBtn');
        if (multiFinishBtn) {
            multiFinishBtn.addEventListener('click', () => {
                if (pages.length > 0) {
                    stopCamera();
                    renderReviewScreen();
                }
            });
        }

        // Camera shutter and gallery controls
        shutterBtn.addEventListener('click', capturePhoto);
        switchBtn.addEventListener('click', switchCamera);
        document.getElementById('camGalleryBtn').addEventListener('click', () => fallbackInput.click());
        fallbackInput.addEventListener('change', handleFileSelected);

        // Crop controls
        document.getElementById('camCropRotateBtn').addEventListener('click', rotateCurrentCrop);
        document.getElementById('camCropResetBtn').addEventListener('click', resetCropCorners);
        document.getElementById('camCropApplyBtn').addEventListener('click', () => applyCropAndPerspective());

        // Drag corners (Touch + Mouse)
        handles.forEach((h, idx) => {
            h.addEventListener('mousedown', (e) => startDrag(e, idx));
            h.addEventListener('touchstart', (e) => startDrag(e, idx), { passive: false });
        });
        window.addEventListener('mousemove', doDrag);
        window.addEventListener('touchmove', doDrag, { passive: false });
        window.addEventListener('mouseup', endDrag);
        window.addEventListener('touchend', endDrag);

        // Review filter controls
        filterChips.forEach(chip => {
            chip.addEventListener('click', () => {
                filterChips.forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                setPageFilter(chip.dataset.filter);
            });
        });

        document.getElementById('camAddPageBtn').addEventListener('click', () => {
            isReEditingPage = false;
            goToStep('capture');
            startCamera();
        });

        document.getElementById('camRetakePageBtn').addEventListener('click', () => {
            isReEditingPage = true;
            goToStep('capture');
            startCamera();
        });

        // Sesuaikan Sudut Manual: Buka kembali crop screen
        document.getElementById('camManualCropBtn').addEventListener('click', () => {
            openManualCropForActivePage();
        });

        document.getElementById('camProceedSaveBtn').addEventListener('click', () => {
            goToStep('metadata');
        });

        document.getElementById('camBackToReviewBtn').addEventListener('click', () => {
            goToStep('review');
        });
    }

    function setScanMode(mode) {
        scanMode = mode;
        const singleBtn = document.getElementById('camModeSingleBtn');
        const multiBtn  = document.getElementById('camModeMultiBtn');
        if (singleBtn) singleBtn.classList.toggle('active', mode === 'single');
        if (multiBtn)  multiBtn.classList.toggle('active', mode === 'multi');

        updateMultiFloatingBar();
    }

    function updateMultiFloatingBar() {
        const bar = document.getElementById('camMultiFloatingBar');
        const countEl = document.getElementById('camMultiCount');
        if (!bar || !countEl) return;

        if (scanMode === 'multi' && pages.length > 0) {
            countEl.textContent = `${pages.length} Halaman Siap`;
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    }

    // ── Public Entry Point ──
    function openScanner(options = {}) {
        if (!modal) init();

        activeOptions = {
            caseId: options.caseId || null,
            casesList: options.casesList || [],
            isAdvocate: options.isAdvocate || false,
            documentRequestId: options.documentRequestId || null,
            presetName: options.presetName || '',
            presetType: options.presetType || 'Dokumen Lainnya',
        };

        pages = [];
        activePageIndex = 0;
        isReEditingPage = false;
        setScanMode('single');

        // Reset metadata fields
        document.getElementById('camDocName').value = activeOptions.presetName;
        document.getElementById('camDocType').value = activeOptions.presetType;
        document.getElementById('camDocDesc').value = '';

        // Configure case picker if multiple cases are provided
        const caseGroup = document.getElementById('camCaseSelectGroup');
        const caseSelect = document.getElementById('camCaseSelect');
        if (!activeOptions.caseId && activeOptions.casesList && activeOptions.casesList.length > 0) {
            caseSelect.innerHTML = activeOptions.casesList.map(c =>
                `<option value="${c.id}">${c.case_number ? c.case_number + ' — ' : ''}${c.title}</option>`
            ).join('');
            caseGroup.style.display = 'block';
        } else {
            caseGroup.style.display = 'none';
        }

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        goToStep('capture');
        startCamera();
    }

    function closeScanner() {
        stopCamera();
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    }

    function handleBackStep() {
        if (currentStep === 'metadata') {
            goToStep('review');
        } else if (currentStep === 'review') {
            if (pages.length <= 1) {
                goToStep('capture');
                startCamera();
            } else {
                closeScanner();
            }
        } else if (currentStep === 'crop') {
            if (pages.length > 0) {
                goToStep('review');
            } else {
                goToStep('capture');
                startCamera();
            }
        } else {
            closeScanner();
        }
    }

    function goToStep(step) {
        currentStep = step;
        screenCapture.style.display  = (step === 'capture') ? 'flex' : 'none';
        screenCrop.style.display     = (step === 'crop') ? 'flex' : 'none';
        screenReview.style.display   = (step === 'review') ? 'flex' : 'none';
        screenMetadata.style.display = (step === 'metadata') ? 'flex' : 'none';

        const titleEl = document.getElementById('camStepTitle');
        if (step === 'capture') {
            titleEl.textContent = pages.length > 0 && !isReEditingPage
                ? `Pindai Halaman ${pages.length + 1}`
                : 'Pindai Dokumen';
            updateMultiFloatingBar();
        } else if (step === 'crop') {
            titleEl.textContent = 'Atur 4 Sudut Dokumen';
        } else if (step === 'review') {
            titleEl.textContent = `Pratinjau (${pages.length} Halaman)`;
        } else if (step === 'metadata') {
            titleEl.textContent = 'Simpan Berkas PDF';
        }
    }

    function showAutoNotice(msg) {
        const el = document.getElementById('camAutoNotice');
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
        el.style.opacity = '1';
        setTimeout(() => {
            el.style.opacity = '0';
            setTimeout(() => { el.style.display = 'none'; }, 250);
        }, 2200);
    }

    // ── Camera Management & Anti-Mirror Enforcement ──
    async function startCamera() {
        stopCamera();
        fallbackNotice.style.display = 'none';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraFallback();
            return;
        }

        try {
            // Prioritize back camera (environment) with HD ideal resolution
            const constraints = {
                video: {
                    facingMode: facingMode ? { ideal: facingMode } : { ideal: 'environment' },
                    width: { ideal: 1920, min: 1280 },
                    height: { ideal: 1080, min: 720 }
                },
                audio: false
            };

            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;

            // Enforce Anti-Mirror: rear camera must be normal; mirror only if front camera
            if (facingMode === 'user') {
                video.style.transform = 'scaleX(-1)';
            } else {
                video.style.transform = 'none';
            }

            await video.play();

            // Start Live Real-Time Detection Loop
            startLiveDetection();
        } catch (err) {
            console.warn("Kamera belakang tidak dapat diakses, mencoba kamera alternatif:", err);
            // Fallback: try any available camera without facingMode constraint
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                video.srcObject = stream;
                video.style.transform = 'none';
                await video.play();
                startLiveDetection();
            } catch (errFallback) {
                console.warn("Kamera benar-benar tidak dapat diakses:", errFallback);
                showCameraFallback();
            }
        }
    }

    function stopCamera() {
        stopLiveDetection();
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function switchCamera() {
        facingMode = (facingMode === 'environment') ? 'user' : 'environment';
        startCamera();
    }

    function showCameraFallback() {
        fallbackNotice.style.display = 'block';
    }

    // ── Live Document Detection Engine (Throttled ~10-12 FPS) ──
    function startLiveDetection() {
        isLiveDetecting = true;
        smoothedLiveCorners = null;
        liveStabilityScore = 0;
        lastCornerSnapshot = null;
        updateLiveStatusUI('searching');
        requestAnimationFrame(processLiveFrame);
    }

    function stopLiveDetection() {
        isLiveDetecting = false;
        smoothedLiveCorners = null;
        liveStabilityScore = 0;
        clearLiveOverlay();
    }

    function clearLiveOverlay() {
        if (!liveOverlayCanvas || !liveOverlayCtx) return;
        liveOverlayCtx.clearRect(0, 0, liveOverlayCanvas.width, liveOverlayCanvas.height);
    }

    function processLiveFrame(timestamp) {
        if (!isLiveDetecting || currentStep !== 'capture') return;

        // Throttle frame processing: ~85ms between detections (~11.7 FPS)
        if (timestamp - lastDetectTimestamp >= 85) {
            lastDetectTimestamp = timestamp;

            if (video && video.readyState >= 2 && video.videoWidth > 0) {
                runLiveQuadDetection();
            }
        }

        if (isLiveDetecting && currentStep === 'capture') {
            requestAnimationFrame(processLiveFrame);
        }
    }

    function runLiveQuadDetection() {
        const vw = video.videoWidth;
        const vh = video.videoHeight;
        if (!vw || !vh) return;

        // Sync live overlay canvas size with display dimensions
        const vp = document.getElementById('camVideoViewport');
        if (vp && liveOverlayCanvas) {
            const rect = vp.getBoundingClientRect();
            if (liveOverlayCanvas.width !== rect.width || liveOverlayCanvas.height !== rect.height) {
                liveOverlayCanvas.width = rect.width;
                liveOverlayCanvas.height = rect.height;
            }
        }

        // Draw downscaled frame to 320x240 for fast CV processing
        const pw = 320;
        const ph = Math.round(pw * (vh / vw)) || 240;
        if (liveDetectCanvas.width !== pw || liveDetectCanvas.height !== ph) {
            liveDetectCanvas.width = pw;
            liveDetectCanvas.height = ph;
        }

        liveDetectCtx.drawImage(video, 0, 0, pw, ph);
        const imgData = liveDetectCtx.getImageData(0, 0, pw, ph);
        const data = imgData.data;

        // Compute Grayscale & Gradient Magnitude Map
        const lum = new Float32Array(pw * ph);
        for (let i = 0, j = 0; i < data.length; i += 4, j++) {
            lum[j] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        }

        const grad = new Float32Array(pw * ph);
        let totalGrad = 0;
        for (let y = 1; y < ph - 1; y++) {
            const row = y * pw;
            for (let x = 1; x < pw - 1; x++) {
                const idx = row + x;
                const gx = lum[idx + 1] - lum[idx - 1];
                const gy = lum[idx + pw] - lum[idx - pw];
                const g = Math.abs(gx) + Math.abs(gy);
                grad[idx] = g;
                totalGrad += g;
            }
        }

        const avgGrad = totalGrad / (pw * ph);
        const gradThreshold = Math.max(16, avgGrad * 1.55);

        // Radial raycasting from center in 32 directions
        const cx = pw / 2;
        const cy = ph / 2;
        const numRays = 32;
        const boundaryPts = [];

        for (let r = 0; r < numRays; r++) {
            const angle = (r * 2 * Math.PI) / numRays;
            const cosA = Math.cos(angle);
            const sinA = Math.sin(angle);

            const maxR = Math.min(
                cosA > 0 ? (pw - 4 - cx) / cosA : (4 - cx) / cosA,
                sinA > 0 ? (ph - 4 - cy) / sinA : (4 - cy) / sinA
            );

            let bestPt = null;
            let maxVal = 0;

            for (let dist = maxR * 0.15; dist < maxR * 0.96; dist += 3) {
                const px = Math.round(cx + cosA * dist);
                const py = Math.round(cy + sinA * dist);
                if (px < 2 || px >= pw - 2 || py < 2 || py >= ph - 2) break;

                const val = grad[py * pw + px];
                if (val > gradThreshold && val > maxVal) {
                    maxVal = val;
                    bestPt = { x: px, y: py };
                }
            }

            if (bestPt) {
                boundaryPts.push(bestPt);
            }
        }

        // Find candidate 4 corners
        let detected = false;
        if (boundaryPts.length >= 14) {
            let tl = null, tr = null, br = null, bl = null;
            let minTL = Infinity, maxTR = -Infinity, maxBR = -Infinity, minBL = Infinity;

            for (const pt of boundaryPts) {
                if (pt.x < cx * 1.15 && pt.y < cy * 1.15) {
                    const v = pt.x + pt.y;
                    if (v < minTL) { minTL = v; tl = pt; }
                }
                if (pt.x > cx * 0.85 && pt.y < cy * 1.15) {
                    const v = pt.x - pt.y;
                    if (v > maxTR) { maxTR = v; tr = pt; }
                }
                if (pt.x > cx * 0.85 && pt.y > cy * 0.85) {
                    const v = pt.x + pt.y;
                    if (v > maxBR) { maxBR = v; br = pt; }
                }
                if (pt.x < cx * 1.15 && pt.y > cy * 0.85) {
                    const v = pt.x - pt.y;
                    if (v < minBL) { minBL = v; bl = pt; }
                }
            }

            if (tl && tr && br && bl) {
                // Convexity verification
                const cross = (o, a, b) => (a.x - o.x) * (b.y - o.y) - (a.y - o.y) * (b.x - o.x);
                const c0 = cross(tl, tr, br);
                const c1 = cross(tr, br, bl);
                const c2 = cross(br, bl, tl);
                const c3 = cross(bl, tl, tr);
                const isConvex = (c0 > 0 && c1 > 0 && c2 > 0 && c3 > 0) || (c0 < 0 && c1 < 0 && c2 < 0 && c3 < 0);

                if (isConvex) {
                    // Area check (Shoelace formula)
                    const quadArea = 0.5 * Math.abs(
                        (tl.x * tr.y + tr.x * br.y + br.x * bl.y + bl.x * tl.y) -
                        (tl.y * tr.x + tr.y * br.x + br.y * bl.x + bl.y * tl.x)
                    );
                    const areaRatio = quadArea / (pw * ph);

                    // Aspect ratio check
                    const dist = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);
                    const avgW = (dist(tl, tr) + dist(bl, br)) / 2;
                    const avgH = (dist(tl, bl) + dist(tr, br)) / 2;
                    const aspect = avgW / (avgH || 1);

                    if (areaRatio >= 0.12 && areaRatio <= 0.88 && aspect >= 0.35 && aspect <= 2.85) {
                        detected = true;

                        // Normalize to [0..1]
                        const rawNorm = [
                            { x: tl.x / pw, y: tl.y / ph },
                            { x: tr.x / pw, y: tr.y / ph },
                            { x: br.x / pw, y: br.y / ph },
                            { x: bl.x / pw, y: bl.y / ph }
                        ];

                        // Exponential Moving Average Smoothing to eliminate polygon jitter
                        if (!smoothedLiveCorners) {
                            smoothedLiveCorners = rawNorm;
                        } else {
                            const alpha = 0.35;
                            for (let i = 0; i < 4; i++) {
                                smoothedLiveCorners[i].x = smoothedLiveCorners[i].x * (1 - alpha) + rawNorm[i].x * alpha;
                                smoothedLiveCorners[i].y = smoothedLiveCorners[i].y * (1 - alpha) + rawNorm[i].y * alpha;
                            }
                        }

                        // Evaluate stability
                        if (lastCornerSnapshot) {
                            let maxDrift = 0;
                            for (let i = 0; i < 4; i++) {
                                const d = Math.hypot(smoothedLiveCorners[i].x - lastCornerSnapshot[i].x, smoothedLiveCorners[i].y - lastCornerSnapshot[i].y);
                                if (d > maxDrift) maxDrift = d;
                            }
                            if (maxDrift < 0.015) {
                                liveStabilityScore = Math.min(8, liveStabilityScore + 1);
                            } else {
                                liveStabilityScore = Math.max(0, liveStabilityScore - 2);
                            }
                        }
                        lastCornerSnapshot = smoothedLiveCorners.map(p => ({ x: p.x, y: p.y }));
                    }
                }
            }
        }

        if (!detected) {
            liveStabilityScore = Math.max(0, liveStabilityScore - 1);
            if (liveStabilityScore === 0) {
                smoothedLiveCorners = null;
                lastCornerSnapshot = null;
            }
        }

        // Render polygon & status
        renderLiveOverlay();
    }

    function renderLiveOverlay() {
        if (!liveOverlayCanvas || !liveOverlayCtx) return;
        const ctx = liveOverlayCtx;
        const w = liveOverlayCanvas.width;
        const h = liveOverlayCanvas.height;

        ctx.clearRect(0, 0, w, h);

        if (!smoothedLiveCorners) {
            updateLiveStatusUI('searching');
            return;
        }

        const isReady = (liveStabilityScore >= 3);
        updateLiveStatusUI(isReady ? 'ready' : 'detected');

        // Map normalized corners to overlay canvas
        const pts = smoothedLiveCorners.map(p => ({
            x: p.x * w,
            y: p.y * h
        }));

        const strokeColor = isReady ? '#10b981' : '#00f2fe';
        const fillColor   = isReady ? 'rgba(16, 185, 129, 0.16)' : 'rgba(0, 242, 254, 0.14)';

        // 1. Draw quadrilateral contour
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(pts[0].x, pts[0].y);
        ctx.lineTo(pts[1].x, pts[1].y);
        ctx.lineTo(pts[2].x, pts[2].y);
        ctx.lineTo(pts[3].x, pts[3].y);
        ctx.closePath();

        ctx.fillStyle = fillColor;
        ctx.fill();

        ctx.strokeStyle = strokeColor;
        ctx.lineWidth = 2.5;
        ctx.shadowColor = strokeColor;
        ctx.shadowBlur = 10;
        ctx.stroke();

        // 2. Draw 4 corner pins with white border
        pts.forEach(p => {
            ctx.beginPath();
            ctx.arc(p.x, p.y, 6.5, 0, Math.PI * 2);
            ctx.fillStyle = strokeColor;
            ctx.fill();
            ctx.lineWidth = 2.2;
            ctx.strokeStyle = '#ffffff';
            ctx.stroke();
        });

        ctx.restore();
    }

    function updateLiveStatusUI(state) {
        const pill = document.getElementById('camLiveStatus');
        const text = document.getElementById('camStatusText');
        if (!pill || !text) return;

        pill.classList.remove('detected', 'ready');

        if (state === 'ready') {
            pill.classList.add('ready');
            text.textContent = 'Siap dipindai';
        } else if (state === 'detected') {
            pill.classList.add('detected');
            text.textContent = 'Dokumen terdeteksi';
        } else {
            text.textContent = 'Posisikan dokumen di dalam kamera';
        }
    }

    function triggerShutterFlash() {
        const flash = document.getElementById('camFlashOverlay');
        if (!flash) return;
        flash.classList.add('active');
        setTimeout(() => flash.classList.remove('active'), 140);
    }

    // ── Photo Capture (MANUAL SHUTTER - TIDAK ADA AUTO-CAPTURE) ──
    function capturePhoto() {
        if (!video || !video.videoWidth) {
            fallbackInput.click();
            return;
        }

        // 1. Visual flash feedback
        triggerShutterFlash();

        // 2. Capture full-resolution frame from camera
        const fullCanvas = document.createElement('canvas');
        fullCanvas.width = video.videoWidth;
        fullCanvas.height = video.videoHeight;
        const ctx = fullCanvas.getContext('2d');
        ctx.drawImage(video, 0, 0, fullCanvas.width, fullCanvas.height);

        // 3. Evaluate live detection corners
        if (smoothedLiveCorners && smoothedLiveCorners.length === 4) {
            // Document corners were detected: map directly to full canvas resolution
            const rawW = fullCanvas.width;
            const rawH = fullCanvas.height;
            const fullCorners = smoothedLiveCorners.map(p => ({
                x: Math.round(p.x * rawW),
                y: Math.round(p.y * rawH)
            }));

            // Execute auto perspective warp & enhancement
            processDirectScan(fullCanvas, fullCorners);
        } else {
            // Live detection was not conclusive: fall back to manual 4-corner crop screen
            stopCamera();
            loadCapturedImage(fullCanvas.toDataURL('image/jpeg', 0.95));
        }
    }

    function processDirectScan(fullCanvas, corners) {
        const rawW = fullCanvas.width;
        const rawH = fullCanvas.height;

        // Calculate target dimensions
        const dist = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);
        const targetW = Math.round(Math.max(dist(corners[0], corners[1]), dist(corners[3], corners[2])));
        const targetH = Math.round(Math.max(dist(corners[0], corners[3]), dist(corners[1], corners[2])));

        // Clamp maximum resolution to 1600px for speed and low memory
        const maxDim = 1600;
        let finalW = targetW;
        let finalH = targetH;
        if (Math.max(finalW, finalH) > maxDim) {
            const downscale = maxDim / Math.max(finalW, finalH);
            finalW = Math.round(finalW * downscale);
            finalH = Math.round(finalH * downscale);
        }

        // Warp perspective using Heckbert homography mapping
        const warpedCanvas = warpPerspectiveCanvas(fullCanvas, corners, finalW, finalH);

        // Create new page object with 'magic' filter as default
        const newPage = {
            id: Date.now(),
            sourceImgSrc: fullCanvas.toDataURL('image/jpeg', 0.88),
            cleanCanvas: warpedCanvas,
            corners: corners,
            rotation: 0,
            filter: 'magic',
            displayCanvas: document.createElement('canvas')
        };

        applyFilterToPage(newPage);

        if (isReEditingPage && pages[activePageIndex]) {
            pages[activePageIndex] = newPage;
            isReEditingPage = false;
            stopCamera();
            renderReviewScreen();
        } else {
            pages.push(newPage);
            activePageIndex = pages.length - 1;

            if (scanMode === 'single') {
                // Satu Halaman: stop camera and proceed directly to review
                stopCamera();
                renderReviewScreen();
                showAutoNotice("✓ Dokumen berhasil dipindai & diratakan");
            } else {
                // Beberapa Halaman: keep camera running, update counter badge
                updateMultiFloatingBar();
                showAutoNotice(`✓ Halaman ${pages.length} tersimpan. Lanjut scan halaman berikutnya.`);
            }
        }
    }

    function handleFileSelected(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            stopCamera();
            loadCapturedImage(evt.target.result);
        };
        reader.readAsDataURL(file);
        fallbackInput.value = '';
    }

    function loadCapturedImage(dataUrl) {
        const img = new Image();
        img.onload = function() {
            cropRotation = 0;
            setupCropScreenData(img);

            // Run detection on imported image
            const detectResult = detectDocumentQuad(img);
            if (detectResult.success && detectResult.corners) {
                window.currentCorners = detectResult.corners;
                renderCornerHandles();
                applyCropAndPerspective();
                showAutoNotice("✓ Dokumen terdeteksi otomatis");
            } else {
                resetCropCorners();
                goToStep('crop');
                showAutoNotice("Silakan sesuaikan 4 sudut dokumen");
            }
        };
        img.src = dataUrl;
    }

    // ── Crop Screen Management ──
    function setupCropScreenData(imgElement) {
        cropImg.src = imgElement.src;
        cropImg.dataset.rawWidth = imgElement.naturalWidth || imgElement.width;
        cropImg.dataset.rawHeight = imgElement.naturalHeight || imgElement.height;
        updateCropDimensions();
    }

    function updateCropDimensions() {
        const vp = document.getElementById('camCropViewport');
        const img = cropImg;
        const naturalW = parseInt(img.dataset.rawWidth) || 1200;
        const naturalH = parseInt(img.dataset.rawHeight) || 1600;

        const isRotated = (cropRotation === 90 || cropRotation === 270);
        const effectiveW = isRotated ? naturalH : naturalW;
        const effectiveH = isRotated ? naturalW : naturalH;

        const maxW = Math.max(280, (vp ? vp.clientWidth : 600) - 40);
        const maxH = Math.max(380, (vp ? vp.clientHeight : 800) - 40);

        const scale = Math.min(maxW / effectiveW, maxH / effectiveH, 1);
        cropDisplayScale = scale;

        const dispW = Math.round(effectiveW * scale);
        const dispH = Math.round(effectiveH * scale);

        cropStage.style.width = dispW + 'px';
        cropStage.style.height = dispH + 'px';

        cropImg.style.width = dispW + 'px';
        cropImg.style.height = dispH + 'px';
        cropImg.style.transform = `rotate(${cropRotation}deg)`;

        cropSvg.setAttribute('viewBox', `0 0 ${dispW} ${dispH}`);
        cropImgRect = { x: 0, y: 0, width: dispW, height: dispH };
    }

    function resetCropCorners() {
        const w = cropImgRect.width || 320;
        const h = cropImgRect.height || 420;
        const insetX = Math.round(w * 0.05);
        const insetY = Math.round(h * 0.05);

        window.currentCorners = [
            { x: insetX,     y: insetY },
            { x: w - insetX, y: insetY },
            { x: w - insetX, y: h - insetY },
            { x: insetX,     y: h - insetY }
        ];

        renderCornerHandles();
    }

    function renderCornerHandles() {
        if (!window.currentCorners) return;
        const pts = window.currentCorners;

        handles.forEach((h, idx) => {
            h.style.left = pts[idx].x + 'px';
            h.style.top  = pts[idx].y + 'px';
        });

        const pointsAttr = pts.map(p => `${p.x},${p.y}`).join(' ');
        cropPolygon.setAttribute('points', pointsAttr);
    }

    function rotateCurrentCrop() {
        cropRotation = (cropRotation + 90) % 360;
        updateCropDimensions();
        resetCropCorners();
    }

    // ── Drag Corner Interaction & Magnifying Loupe ──
    function startDrag(e, cornerIdx) {
        e.preventDefault();
        isDragging = true;
        activeCornerIndex = cornerIdx;
        loupe.style.display = 'block';
        updateLoupe(window.currentCorners[cornerIdx]);
    }

    function doDrag(e) {
        if (!isDragging || activeCornerIndex === -1) return;
        e.preventDefault();

        const stageRect = cropStage.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        let posX = clientX - stageRect.left;
        let posY = clientY - stageRect.top;

        posX = Math.max(0, Math.min(posX, cropImgRect.width));
        posY = Math.max(0, Math.min(posY, cropImgRect.height));

        window.currentCorners[activeCornerIndex] = { x: posX, y: posY };
        renderCornerHandles();
        updateLoupe({ x: posX, y: posY });
    }

    function endDrag() {
        if (isDragging) {
            isDragging = false;
            activeCornerIndex = -1;
            loupe.style.display = 'none';
        }
    }

    function updateLoupe(point) {
        loupe.style.left = point.x + 'px';
        loupe.style.top  = point.y + 'px';

        const ctx = loupeCanvas.getContext('2d');
        ctx.clearRect(0, 0, 120, 120);

        const zoom = 2.5;
        const srcW = 120 / zoom;
        const srcH = 120 / zoom;

        const scaleX = cropImg.naturalWidth / cropImgRect.width;
        const scaleY = cropImg.naturalHeight / cropImgRect.height;
        const natX = point.x * scaleX;
        const natY = point.y * scaleY;

        ctx.drawImage(
            cropImg,
            natX - (srcW / 2), natY - (srcH / 2), srcW, srcH,
            0, 0, 120, 120
        );
    }

    // ── Static Image Quad Detection Engine ──
    function detectDocumentQuad(imgElement) {
        try {
            const rawW = imgElement.naturalWidth || imgElement.width;
            const rawH = imgElement.naturalHeight || imgElement.height;
            if (!rawW || !rawH) return { success: false };

            const pw = 360;
            const ph = Math.round(pw * (rawH / rawW));
            if (pw < 50 || ph < 50) return { success: false };

            const offCanvas = document.createElement('canvas');
            offCanvas.width = pw;
            offCanvas.height = ph;
            const offCtx = offCanvas.getContext('2d');
            offCtx.drawImage(imgElement, 0, 0, pw, ph);

            const imgData = offCtx.getImageData(0, 0, pw, ph);
            const data = imgData.data;

            const lum = new Float32Array(pw * ph);
            for (let i = 0, j = 0; i < data.length; i += 4, j++) {
                lum[j] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            }

            const grad = new Float32Array(pw * ph);
            let totalGrad = 0;
            for (let y = 1; y < ph - 1; y++) {
                for (let x = 1; x < pw - 1; x++) {
                    const idx = y * pw + x;
                    const gx = lum[idx + 1] - lum[idx - 1];
                    const gy = lum[idx + pw] - lum[idx - pw];
                    const g = Math.abs(gx) + Math.abs(gy);
                    grad[idx] = g;
                    totalGrad += g;
                }
            }
            const avgGrad = totalGrad / (pw * ph);
            const gradThreshold = Math.max(16, avgGrad * 1.55);

            const cx = pw / 2;
            const cy = ph / 2;
            const numRays = 36;
            const boundaryPoints = [];

            for (let r = 0; r < numRays; r++) {
                const angle = (r * 2 * Math.PI) / numRays;
                const cosA = Math.cos(angle);
                const sinA = Math.sin(angle);

                const maxR = Math.min(
                    cosA > 0 ? (pw - 4 - cx) / cosA : (4 - cx) / cosA,
                    sinA > 0 ? (ph - 4 - cy) / sinA : (4 - cy) / sinA
                );

                let bestPoint = null;
                let maxEdgeVal = 0;

                for (let dist = maxR * 0.14; dist < maxR * 0.98; dist += 2) {
                    const px = Math.round(cx + cosA * dist);
                    const py = Math.round(cy + sinA * dist);
                    if (px < 2 || px >= pw - 2 || py < 2 || py >= ph - 2) break;

                    const gVal = grad[py * pw + px];
                    if (gVal > gradThreshold && gVal > maxEdgeVal) {
                        maxEdgeVal = gVal;
                        bestPoint = { x: px, y: py };
                    }
                }

                if (bestPoint) boundaryPoints.push(bestPoint);
            }

            if (boundaryPoints.length < 14) return { success: false };

            let tl = null, tr = null, br = null, bl = null;
            let minTL = Infinity, maxTR = -Infinity, maxBR = -Infinity, minBL = Infinity;

            for (const pt of boundaryPoints) {
                if (pt.x < cx * 1.15 && pt.y < cy * 1.15) {
                    const v = pt.x + pt.y;
                    if (v < minTL) { minTL = v; tl = pt; }
                }
                if (pt.x > cx * 0.85 && pt.y < cy * 1.15) {
                    const v = pt.x - pt.y;
                    if (v > maxTR) { maxTR = v; tr = pt; }
                }
                if (pt.x > cx * 0.85 && pt.y > cy * 0.85) {
                    const v = pt.x + pt.y;
                    if (v > maxBR) { maxBR = v; br = pt; }
                }
                if (pt.x < cx * 1.15 && pt.y > cy * 0.85) {
                    const v = pt.x - pt.y;
                    if (v < minBL) { minBL = v; bl = pt; }
                }
            }

            if (!tl || !tr || !br || !bl) return { success: false };

            const cross = (o, a, b) => (a.x - o.x) * (b.y - o.y) - (a.y - o.y) * (b.x - o.x);
            const isConvex = (cross(tl, tr, br) > 0 && cross(tr, br, bl) > 0 && cross(br, bl, tl) > 0 && cross(bl, tl, tr) > 0) ||
                             (cross(tl, tr, br) < 0 && cross(tr, br, bl) < 0 && cross(br, bl, tl) < 0 && cross(bl, tl, tr) < 0);
            if (!isConvex) return { success: false };

            const scaleToDispX = cropImgRect.width / pw;
            const scaleToDispY = cropImgRect.height / ph;

            const corners = [
                { x: Math.round(tl.x * scaleToDispX), y: Math.round(tl.y * scaleToDispY) },
                { x: Math.round(tr.x * scaleToDispX), y: Math.round(tr.y * scaleToDispY) },
                { x: Math.round(br.x * scaleToDispX), y: Math.round(br.y * scaleToDispY) },
                { x: Math.round(bl.x * scaleToDispX), y: Math.round(bl.y * scaleToDispY) }
            ];

            return { success: true, corners: corners };
        } catch (err) {
            console.warn("Auto-detect exception:", err);
            return { success: false };
        }
    }

    function openManualCropForActivePage() {
        if (!pages[activePageIndex]) return;
        isReEditingPage = true;

        const p = pages[activePageIndex];
        if (p.sourceImgSrc) {
            const img = new Image();
            img.onload = function() {
                cropImg.src = img.src;
                cropRotation = p.rotation || 0;
                updateCropDimensions();

                if (p.corners && p.corners.length === 4) {
                    // Map natural coordinates to crop stage display dimensions
                    const scaleX = cropImgRect.width / img.naturalWidth;
                    const scaleY = cropImgRect.height / img.naturalHeight;
                    window.currentCorners = p.corners.map(c => ({
                        x: Math.round(c.x * scaleX),
                        y: Math.round(c.y * scaleY)
                    }));
                } else {
                    resetCropCorners();
                }
                renderCornerHandles();
                goToStep('crop');
            };
            img.src = p.sourceImgSrc;
        } else {
            goToStep('crop');
        }
    }

    // ── Heckbert Projective Homography Perspective Correction Engine ──
    function applyCropAndPerspective() {
        const rawW = cropImg.naturalWidth;
        const rawH = cropImg.naturalHeight;
        const dispW = cropImgRect.width || 320;
        const dispH = cropImgRect.height || 420;

        const scaleX = rawW / dispW;
        const scaleY = rawH / dispH;
        const s = window.currentCorners.map(p => ({
            x: p.x * scaleX,
            y: p.y * scaleY
        }));

        const dist = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);
        const targetW = Math.round(Math.max(dist(s[0], s[1]), dist(s[3], s[2])));
        const targetH = Math.round(Math.max(dist(s[0], s[3]), dist(s[1], s[2])));

        const maxDim = 1600;
        let finalW = targetW;
        let finalH = targetH;
        if (Math.max(finalW, finalH) > maxDim) {
            const downscale = maxDim / Math.max(finalW, finalH);
            finalW = Math.round(finalW * downscale);
            finalH = Math.round(finalH * downscale);
        }

        const offCanvas = document.createElement('canvas');
        if (cropRotation !== 0) {
            const isRot = (cropRotation === 90 || cropRotation === 270);
            offCanvas.width = isRot ? rawH : rawW;
            offCanvas.height = isRot ? rawW : rawH;
            const offCtx = offCanvas.getContext('2d');
            offCtx.translate(offCanvas.width / 2, offCanvas.height / 2);
            offCtx.rotate((cropRotation * Math.PI) / 180);
            offCtx.drawImage(cropImg, -rawW / 2, -rawH / 2);
        } else {
            offCanvas.width = rawW;
            offCanvas.height = rawH;
            offCanvas.getContext('2d').drawImage(cropImg, 0, 0);
        }

        const dstCanvas = warpPerspectiveCanvas(offCanvas, s, finalW, finalH);

        if (isReEditingPage && pages[activePageIndex]) {
            pages[activePageIndex].cleanCanvas = dstCanvas;
            pages[activePageIndex].corners = s.map(p => ({ x: p.x, y: p.y }));
            pages[activePageIndex].rotation = cropRotation;
            applyFilterToPage(pages[activePageIndex]);
            isReEditingPage = false;
        } else {
            const newPage = {
                id: Date.now(),
                sourceImgSrc: cropImg.src,
                cleanCanvas: dstCanvas,
                corners: s.map(p => ({ x: p.x, y: p.y })),
                rotation: cropRotation,
                filter: 'magic',
                displayCanvas: document.createElement('canvas')
            };
            applyFilterToPage(newPage);
            pages.push(newPage);
            activePageIndex = pages.length - 1;
        }

        renderReviewScreen();
    }

    function warpPerspectiveCanvas(sourceCanvas, s, finalW, finalH) {
        const sWidth = sourceCanvas.width;
        const sHeight = sourceCanvas.height;
        const srcCtx = sourceCanvas.getContext('2d');
        const srcData = srcCtx.getImageData(0, 0, sWidth, sHeight);
        const sPixels = srcData.data;

        const dstCanvas = document.createElement('canvas');
        dstCanvas.width = finalW;
        dstCanvas.height = finalH;
        const dstCtx = dstCanvas.getContext('2d');
        const dstData = dstCtx.createImageData(finalW, finalH);
        const dPixels = dstData.data;

        const x0 = s[0].x, y0 = s[0].y;
        const x1 = s[1].x, y1 = s[1].y;
        const x2 = s[2].x, y2 = s[2].y;
        const x3 = s[3].x, y3 = s[3].y;

        const dx1 = x1 - x2;
        const dx2 = x3 - x2;
        const dx3 = x0 - x1 + x2 - x3;
        const dy1 = y1 - y2;
        const dy2 = y3 - y2;
        const dy3 = y0 - y1 + y2 - y3;

        let a, b, c, d, e, f, g, h;
        if (dx3 === 0 && dy3 === 0) {
            a = x1 - x0; b = x3 - x0; c = x0;
            d = y1 - y0; e = y3 - y0; f = y0;
            g = 0; h = 0;
        } else {
            const det = (dx1 * dy2 - dx2 * dy1) || 1e-6;
            g = (dx3 * dy2 - dx2 * dy3) / det;
            h = (dx1 * dy3 - dx3 * dy1) / det;
            a = x1 - x0 + g * x1;
            b = x3 - x0 + h * x3;
            c = x0;
            d = y1 - y0 + g * y1;
            e = y3 - y0 + h * y3;
            f = y0;
        }

        for (let y = 0; y < finalH; y++) {
            const v = y / finalH;
            const rowOffset = y * finalW;
            for (let x = 0; x < finalW; x++) {
                const u = x / finalW;
                const denom = (g * u + h * v + 1.0) || 1e-6;
                const srcX = Math.round((a * u + b * v + c) / denom);
                const srcY = Math.round((d * u + e * v + f) / denom);

                const dstIdx = (rowOffset + x) * 4;
                if (srcX >= 0 && srcX < sWidth && srcY >= 0 && srcY < sHeight) {
                    const srcIdx = (srcY * sWidth + srcX) * 4;
                    dPixels[dstIdx]     = sPixels[srcIdx];
                    dPixels[dstIdx + 1] = sPixels[srcIdx + 1];
                    dPixels[dstIdx + 2] = sPixels[srcIdx + 2];
                    dPixels[dstIdx + 3] = 255;
                } else {
                    dPixels[dstIdx]     = 255;
                    dPixels[dstIdx + 1] = 255;
                    dPixels[dstIdx + 2] = 255;
                    dPixels[dstIdx + 3] = 255;
                }
            }
        }

        dstCtx.putImageData(dstData, 0, 0);
        return dstCanvas;
    }

    // ── Professional Document Enhancement Filters ──
    function applyFilterToPage(page) {
        const src = page.cleanCanvas;
        const dst = page.displayCanvas;
        dst.width = src.width;
        dst.height = src.height;

        const ctx = dst.getContext('2d');
        ctx.drawImage(src, 0, 0);

        if (page.filter === 'original') {
            return;
        }

        const imgData = ctx.getImageData(0, 0, dst.width, dst.height);
        const data = imgData.data;

        if (page.filter === 'magic') {
            // "Dokumen Jernih / Magic Color":
            // Whitens paper background, removes yellow/gray shadows, sharpens ink contrast while preserving signature/stamp colors
            const shadowLift = 26;
            for (let i = 0; i < data.length; i += 4) {
                const r = data[i], g = data[i + 1], b = data[i + 2];
                const y = 0.299 * r + 0.587 * g + 0.114 * b;

                let newY;
                if (y > 155) {
                    // Background paper: stretch to clean bright white
                    newY = Math.min(255, y + (255 - y) * 0.78 + shadowLift);
                } else if (y < 95) {
                    // Deepen text and lines for legibility
                    newY = Math.max(0, y * 0.88);
                } else {
                    // Smooth contrast curve
                    newY = ((y - 95) / 60) * 160 + 83;
                }

                const factor = (newY + 1) / (y + 1);
                data[i]     = Math.min(255, Math.max(0, r * factor));
                data[i + 1] = Math.min(255, Math.max(0, g * factor));
                data[i + 2] = Math.min(255, Math.max(0, b * factor));
            }
        } else if (page.filter === 'bw') {
            // High-contrast clean black & white binarization
            for (let i = 0; i < data.length; i += 4) {
                const y = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                const val = y > 140 ? 255 : (y < 85 ? 0 : (y - 85) * 4.6);
                data[i]     = val;
                data[i + 1] = val;
                data[i + 2] = val;
            }
        } else if (page.filter === 'grayscale') {
            // Neutral grayscale
            for (let i = 0; i < data.length; i += 4) {
                const y = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                data[i]     = y;
                data[i + 1] = y;
                data[i + 2] = y;
            }
        }

        ctx.putImageData(imgData, 0, 0);
    }

    function setPageFilter(filterType) {
        if (!pages[activePageIndex]) return;
        pages[activePageIndex].filter = filterType;
        applyFilterToPage(pages[activePageIndex]);
        renderProcessedPreview();
        renderThumbnails();
    }

    // ── Review Screen & Multi-Page Manager ──
    function renderReviewScreen() {
        goToStep('review');
        renderProcessedPreview();
        renderThumbnails();
    }

    function renderProcessedPreview() {
        const page = pages[activePageIndex];
        if (!page) return;

        processedCanvas.width  = page.displayCanvas.width;
        processedCanvas.height = page.displayCanvas.height;
        const ctx = processedCanvas.getContext('2d');
        ctx.drawImage(page.displayCanvas, 0, 0);

        filterChips.forEach(c => {
            c.classList.toggle('active', c.dataset.filter === page.filter);
        });
    }

    function renderThumbnails() {
        pagesList.innerHTML = '';
        pages.forEach((p, idx) => {
            const card = document.createElement('div');
            card.className = `cam-page-thumb-card ${idx === activePageIndex ? 'active' : ''}`;
            card.onclick = () => {
                activePageIndex = idx;
                renderProcessedPreview();
                renderThumbnails();
            };

            const thumbImg = document.createElement('img');
            thumbImg.src = p.displayCanvas.toDataURL('image/jpeg', 0.6);

            const numSpan = document.createElement('div');
            numSpan.className = 'cam-page-num';
            numSpan.textContent = `Hal ${idx + 1}`;

            // Delete button (allowed if > 1 page)
            if (pages.length > 1) {
                const delBtn = document.createElement('button');
                delBtn.className = 'cam-page-del-btn';
                delBtn.innerHTML = '&times;';
                delBtn.title = 'Hapus Halaman';
                delBtn.onclick = (e) => {
                    e.stopPropagation();
                    pages.splice(idx, 1);
                    activePageIndex = Math.max(0, activePageIndex - 1);
                    renderReviewScreen();
                };
                card.appendChild(delBtn);
            }

            card.appendChild(thumbImg);
            card.appendChild(numSpan);
            pagesList.appendChild(card);
        });

        document.getElementById('camSummaryPages').textContent = `${pages.length} Halaman`;
    }

    // ── Final PDF Generation & Backend Upload ──
    async function submitFinalDocument(e) {
        e.preventDefault();

        if (pages.length === 0) {
            alert("Belum ada halaman dokumen yang dipindai.");
            return;
        }

        const docName = document.getElementById('camDocName').value.trim();
        const docType = document.getElementById('camDocType').value;
        const docDesc = document.getElementById('camDocDesc').value.trim();

        let targetCaseId = activeOptions.caseId;
        if (!targetCaseId) {
            const caseSelect = document.getElementById('camCaseSelect');
            targetCaseId = caseSelect ? caseSelect.value : null;
        }

        if (!targetCaseId) {
            alert("Perkara tujuan wajib dipilih.");
            return;
        }

        const submitBtn = document.getElementById('camSubmitBtn');
        const submitText = document.getElementById('camSubmitText');
        const submitSpinner = document.getElementById('camSubmitSpinner');
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitSpinner.style.display = 'inline';

        try {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                throw new Error("Library jsPDF tidak ditemukan. Pastikan jspdf.umd.min.js telah dimuat.");
            }

            const { jsPDF } = window.jspdf;
            let pdf = null;

            for (let i = 0; i < pages.length; i++) {
                const pCanvas = pages[i].displayCanvas;
                const isLandscape = pCanvas.width > pCanvas.height;
                const orientation = isLandscape ? 'landscape' : 'portrait';

                if (i === 0) {
                    pdf = new jsPDF({
                        orientation: orientation,
                        unit: 'mm',
                        format: 'a4'
                    });
                } else {
                    pdf.addPage('a4', orientation);
                }

                const pageWidth = isLandscape ? 297 : 210;
                const pageHeight = isLandscape ? 210 : 297;

                const margin = 5;
                const maxW = pageWidth - (margin * 2);
                const maxH = pageHeight - (margin * 2);

                const ratio = Math.min(maxW / pCanvas.width, maxH / pCanvas.height);
                const drawW = pCanvas.width * ratio;
                const drawH = pCanvas.height * ratio;
                const posX = (pageWidth - drawW) / 2;
                const posY = (pageHeight - drawH) / 2;

                const imgDataUrl = pCanvas.toDataURL('image/jpeg', 0.84);
                pdf.addImage(imgDataUrl, 'JPEG', posX, posY, drawW, drawH);
            }

            const pdfBlob = pdf.output('blob');

            const maxSize = 10 * 1024 * 1024;
            if (pdfBlob.size > maxSize) {
                alert("Ukuran dokumen melebihi batas 10 MB. Silakan kurangi jumlah halaman sebelum menyimpan.");
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitSpinner.style.display = 'none';
                return;
            }

            const cleanFileName = docName.replace(/[^a-zA-Z0-9_\-\s]/g, '').trim() || 'Dokumen_Scan';
            const pdfFile = new File([pdfBlob], `${cleanFileName}.pdf`, { type: 'application/pdf' });

            const formData = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                              document.querySelector('input[name="_token"]')?.value;

            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            formData.append('name', docName);
            formData.append('document_type', docType);
            formData.append('description', docDesc);
            formData.append('file', pdfFile);

            if (activeOptions.documentRequestId) {
                formData.append('document_request_id', activeOptions.documentRequestId);
            }

            const endpoint = activeOptions.isAdvocate
                ? `/advokat/perkara/${targetCaseId}/dokumen/upload`
                : `/klien/perkara/${targetCaseId}/dokumen`;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Upload gagal dengan status ${response.status}`);
            }

            closeScanner();
            window.location.reload();

        } catch (err) {
            console.error("Gagal menyimpan dokumen scan:", err);
            alert("Terjadi kesalahan saat menyimpan dokumen: " + (err.message || "Gagal memproses PDF"));
            submitBtn.disabled = false;
            submitText.style.display = 'inline';
            submitSpinner.style.display = 'none';
        }
    }

    return {
        open: openScanner,
        close: closeScanner,
        submitFinalDocument: submitFinalDocument
    };
})();

// Global helper for Blade views
function openDocumentScanner(options) {
    if (window.camScanner) {
        window.camScanner.open(options);
    }
}
</script>
