{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- COMPONENT: Document Scanner Modal ala CamScanner (Klien & Advokat)      --}}
{{-- Upgrade: Auto Document Detection, Auto-Crop 4 Sudut & Perspective Warp   --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div id="camScannerModal" class="cam-scanner-overlay" style="display:none;" aria-hidden="true">
    <div class="cam-scanner-container">

        {{-- Toast / Auto-detection Notification Banner --}}
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

        {{-- ── SCREEN 1: CAMERA CAPTURE ── --}}
        <div class="cam-screen" id="camScreenCapture" style="display:flex;">
            <div class="cam-video-viewport">
                <video id="camVideo" playsinline autoplay muted></video>
                <div class="cam-document-guide">
                    <div class="cam-guide-box">
                        <span class="cam-guide-corner tl"></span>
                        <span class="cam-guide-corner tr"></span>
                        <span class="cam-guide-corner bl"></span>
                        <span class="cam-guide-corner br"></span>
                        <div class="cam-guide-hint">Posisikan dokumen di dalam area kotak</div>
                    </div>
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

            {{-- Bottom Shutter Bar --}}
            <div class="cam-bottom-bar">
                <button type="button" class="cam-tool-btn" id="camGalleryBtn" title="Pilih Foto dari Perangkat">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Galeri</span>
                </button>

                <div class="cam-shutter-wrapper">
                    <button type="button" class="cam-shutter-btn" id="camShutterBtn" title="Ambil Foto Dokumen">
                        <span class="cam-shutter-inner"></span>
                    </button>
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

        {{-- ── SCREEN 2: CROP 4 SUDUT & PERSPECTIVE CORRECTION ── --}}
        <div class="cam-screen" id="camScreenCrop" style="display:none;">
            <div class="cam-crop-viewport" id="camCropViewport">
                <canvas id="camSourceCanvas" style="display:none;"></canvas>
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

        {{-- ── SCREEN 3: FILTER & MULTI-PAGE REVIEW ── --}}
        <div class="cam-screen" id="camScreenReview" style="display:none;">
            {{-- Filter Chips Bar --}}
            <div class="cam-filter-bar">
                <button type="button" class="cam-filter-chip active" data-filter="bw">
                    Hitam & Putih
                </button>
                <button type="button" class="cam-filter-chip" data-filter="grayscale">
                    Grayscale
                </button>
                <button type="button" class="cam-filter-chip" data-filter="original">
                    Asli
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

            {{-- Review Action Bar (Termasuk Sesuaikan Sudut Manual) --}}
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
                    <p class="cam-meta-desc">Dokumen telah dikonversi ke format PDF standar. Silakan lengkapi nama dan jenis dokumen sebelum disimpan.</p>

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

                        <div class="cam-action-bar" style="margin-top:20px;padding:0;">
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
{{-- STYLES: CamScanner Clean Dark Theme                                      --}}
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
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(56, 189, 248, 0.45);
    color: #38bdf8;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 7px 16px;
    border-radius: 20px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
    z-index: 1000;
    pointer-events: none;
    transition: opacity 0.3s ease;
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

/* ── Screen 1: Camera ── */
.cam-video-viewport {
    flex: 1;
    position: relative;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

#camVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cam-document-guide {
    position: absolute;
    inset: 0;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.cam-guide-box {
    width: 100%;
    max-width: 480px;
    height: 80%;
    border: 2px dashed rgba(255, 255, 255, 0.4);
    border-radius: 12px;
    position: relative;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.35);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 16px;
}

.cam-guide-corner {
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: #38bdf8;
    border-style: solid;
}
.cam-guide-corner.tl { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-top-left-radius: 10px; }
.cam-guide-corner.tr { top: -2px; right: -2px; border-width: 4px 4px 0 0; border-top-right-radius: 10px; }
.cam-guide-corner.bl { bottom: -2px; left: -2px; border-width: 0 0 4px 4px; border-bottom-left-radius: 10px; }
.cam-guide-corner.br { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-bottom-right-radius: 10px; }

.cam-guide-hint {
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    color: #cbd5e1;
    font-size: 0.75rem;
    padding: 6px 14px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
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

.cam-bottom-bar {
    height: 90px;
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
}
.cam-tool-btn:hover {
    color: #f1f5f9;
    background: rgba(255, 255, 255, 0.05);
}

.cam-shutter-wrapper {
    position: relative;
}

.cam-shutter-btn {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: transparent;
    border: 4px solid #fff;
    padding: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s;
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
    fill: rgba(56, 189, 248, 0.15);
    stroke: #38bdf8;
    stroke-width: 2;
    stroke-dasharray: 4 2;
}

.cam-handle {
    position: absolute;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #38bdf8;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
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
    border: 3px solid #38bdf8;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.6);
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
    width: 16px;
    height: 2px;
    background: #ef4444;
}
.cam-loupe-crosshair::after {
    content: '';
    position: absolute;
    height: 16px;
    width: 2px;
    background: #ef4444;
}

/* ── Screen 3: Review & Multi-page ── */
.cam-filter-bar {
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #0f172a;
    border-bottom: 1px solid #1e293b;
    padding: 0 12px;
}

.cam-filter-chip {
    background: #1e293b;
    border: 1px solid #334155;
    color: #94a3b8;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.78rem;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.15s;
}
.cam-filter-chip.active {
    background: #0284c7;
    color: #fff;
    border-color: #38bdf8;
}

.cam-review-viewport {
    flex: 1;
    background: #020617;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px;
    overflow: hidden;
}

#camProcessedCanvas {
    max-width: 100%;
    max-height: 100%;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);
    border-radius: 4px;
    object-fit: contain;
}

.cam-pages-strip {
    height: 96px;
    background: #090d16;
    border-top: 1px solid #1e293b;
    display: flex;
    align-items: center;
    padding: 8px 16px;
    gap: 12px;
    overflow-x: auto;
}

.cam-pages-list {
    display: flex;
    gap: 10px;
    align-items: center;
}

.cam-page-thumb-card {
    width: 60px;
    height: 76px;
    border-radius: 6px;
    background: #1e293b;
    border: 2px solid #334155;
    position: relative;
    cursor: pointer;
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
}
.cam-page-thumb-card.active {
    border-color: #38bdf8;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.4);
}

.cam-page-thumb-card img {
    width: 100%;
    height: 54px;
    object-fit: cover;
}

.cam-page-num {
    height: 18px;
    background: rgba(15, 23, 42, 0.9);
    font-size: 0.65rem;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.cam-page-del-btn {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.9);
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
    height: 76px;
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
    border-color: #38bdf8;
    color: #38bdf8;
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
    border-color: #38bdf8;
}

.cam-doc-summary-badge {
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid rgba(56, 189, 248, 0.25);
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
{{-- SCRIPT: CamScanner Controller & Heckbert Perspective Correction Engine     --}}
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
    let facingMode = 'environment';
    let currentStep = 'capture'; // 'capture', 'crop', 'review', 'metadata'

    let pages = []; // [{ id, sourceImgSrc, cleanCanvas, corners, rotation, filter, displayCanvas }]
    let activePageIndex = 0;
    let isReEditingPage = false;

    // Crop UI drag tracking
    let isDragging = false;
    let activeCornerIndex = -1;
    let cropImgRect = { x: 0, y: 0, width: 0, height: 0 };
    let cropDisplayScale = 1;
    let cropRotation = 0; // 0, 90, 180, 270

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

        // Camera controls
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

        // Review controls
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

        // Sesuaikan Sudut Manual: Buka kembali crop screen dengan sudut yang sudah terpasang
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
            titleEl.textContent = pages.length > 0 && !isReEditingPage ? `Ambil Halaman ${pages.length + 1}` : 'Pindai Dokumen';
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
            setTimeout(() => { el.style.display = 'none'; }, 300);
        }, 2500);
    }

    // ── Camera Management ──
    async function startCamera() {
        stopCamera();
        fallbackNotice.style.display = 'none';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraFallback();
            return;
        }

        try {
            const constraints = {
                video: {
                    facingMode: facingMode,
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            };
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            video.play();
        } catch (err) {
            console.warn("Kamera tidak dapat diakses, beralih ke fallback:", err);
            showCameraFallback();
        }
    }

    function stopCamera() {
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

    function handleFileSelected(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            loadCapturedImage(evt.target.result);
        };
        reader.readAsDataURL(file);
        fallbackInput.value = ''; // Reset
    }

    function capturePhoto() {
        if (!video || !video.videoWidth) {
            fallbackInput.click();
            return;
        }

        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = video.videoWidth;
        tempCanvas.height = video.videoHeight;
        const ctx = tempCanvas.getContext('2d');
        ctx.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);

        stopCamera();
        loadCapturedImage(tempCanvas.toDataURL('image/jpeg', 0.95));
    }

    function loadCapturedImage(dataUrl) {
        const img = new Image();
        img.onload = function() {
            cropRotation = 0;
            // Setup crop state & dimensions so display coordinates are established
            setupCropScreenData(img);

            // ── Auto Document Detection ──
            const detectResult = detectDocumentQuad(img);

            if (detectResult.success && detectResult.corners) {
                // High confidence quadrilateral detected: set corners and auto-warp directly!
                window.currentCorners = detectResult.corners;
                renderCornerHandles();
                applyCropAndPerspective();
                showAutoNotice("✓ Dokumen terdeteksi otomatis");
            } else {
                // Fallback: Show manual crop screen with 5% inset handles
                resetCropCorners();
                goToStep('crop');
                showAutoNotice("Silakan sesuaikan 4 sudut dokumen");
            }
        };
        img.src = dataUrl;
    }

    // ── Setup Crop Screen Dimensions ──
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
        // Inset 5% from edges
        const insetX = Math.round(w * 0.05);
        const insetY = Math.round(h * 0.05);

        window.currentCorners = [
            { x: insetX,     y: insetY },     // TL
            { x: w - insetX, y: insetY },     // TR
            { x: w - insetX, y: h - insetY }, // BR
            { x: insetX,     y: h - insetY }  // BL
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

        // Clamp inside stage
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

        // Zoom 2.5x
        const zoom = 2.5;
        const srcW = 120 / zoom;
        const srcH = 120 / zoom;

        // Map display point to natural image coordinates
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

    // ── Auto Document Detection Engine (Pure Canvas 2D) ──
    function detectDocumentQuad(imgElement) {
        try {
            const rawW = imgElement.naturalWidth || imgElement.width;
            const rawH = imgElement.naturalHeight || imgElement.height;
            if (!rawW || !rawH) return { success: false };

            // 1. Proportional processing dimensions (width ~360px)
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

            // 2. Luminance & Edge Gradient Map
            const lum = new Float32Array(pw * ph);
            let totalLum = 0;
            for (let i = 0, j = 0; i < data.length; i += 4, j++) {
                const y = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                lum[j] = y;
                totalLum += y;
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
            const gradThreshold = Math.max(16, avgGrad * 1.5);

            // 3. Radial Raycast from center outward in 36 directions to detect document contour
            const cx = pw / 2;
            const cy = ph / 2;
            const numRays = 36;
            const boundaryPoints = [];

            for (let r = 0; r < numRays; r++) {
                const angle = (r * 2 * Math.PI) / numRays;
                const cosA = Math.cos(angle);
                const sinA = Math.sin(angle);

                // Ray limit to canvas borders
                const maxR = Math.min(
                    cosA > 0 ? (pw - 4 - cx) / cosA : (4 - cx) / cosA,
                    sinA > 0 ? (ph - 4 - cy) / sinA : (4 - cy) / sinA
                );

                let bestPoint = null;
                let maxEdgeVal = 0;

                // Step outward from 12% to 98% radius
                for (let dist = maxR * 0.12; dist < maxR * 0.98; dist += 2) {
                    const px = Math.round(cx + cosA * dist);
                    const py = Math.round(cy + sinA * dist);
                    if (px < 2 || px >= pw - 2 || py < 2 || py >= ph - 2) break;

                    const gVal = grad[py * pw + px];
                    if (gVal > gradThreshold && gVal > maxEdgeVal) {
                        maxEdgeVal = gVal;
                        bestPoint = { x: px, y: py, edge: gVal };
                    }
                }

                if (bestPoint) {
                    boundaryPoints.push(bestPoint);
                }
            }

            if (boundaryPoints.length < 14) {
                return { success: false };
            }

            // 4. Determine 4 candidate corner points most consistent with document contours
            let tl = null, tr = null, br = null, bl = null;
            let minTL = Infinity, maxTR = -Infinity, maxBR = -Infinity, minBL = Infinity;

            for (const pt of boundaryPoints) {
                // Top-Left: min(x + y)
                if (pt.x < cx * 1.15 && pt.y < cy * 1.15) {
                    const v = pt.x + pt.y;
                    if (v < minTL) { minTL = v; tl = pt; }
                }
                // Top-Right: max(x - y)
                if (pt.x > cx * 0.85 && pt.y < cy * 1.15) {
                    const v = pt.x - pt.y;
                    if (v > maxTR) { maxTR = v; tr = pt; }
                }
                // Bottom-Right: max(x + y)
                if (pt.x > cx * 0.85 && pt.y > cy * 0.85) {
                    const v = pt.x + pt.y;
                    if (v > maxBR) { maxBR = v; br = pt; }
                }
                // Bottom-Left: min(x - y)
                if (pt.x < cx * 1.15 && pt.y > cy * 0.85) {
                    const v = pt.x - pt.y;
                    if (v < minBL) { minBL = v; bl = pt; }
                }
            }

            if (!tl || !tr || !br || !bl) {
                return { success: false };
            }

            // 5. Geometrical validation: Convexity check
            const cross = (o, a, b) => (a.x - o.x) * (b.y - o.y) - (a.y - o.y) * (b.x - o.x);
            const c0 = cross(tl, tr, br);
            const c1 = cross(tr, br, bl);
            const c2 = cross(br, bl, tl);
            const c3 = cross(bl, tl, tr);

            const isConvex = (c0 > 0 && c1 > 0 && c2 > 0 && c3 > 0) || (c0 < 0 && c1 < 0 && c2 < 0 && c3 < 0);
            if (!isConvex) {
                return { success: false };
            }

            // Area Ratio Check (Shoelace formula)
            const quadArea = 0.5 * Math.abs(
                (tl.x * tr.y + tr.x * br.y + br.x * bl.y + bl.x * tl.y) -
                (tl.y * tr.x + tr.y * br.x + br.y * bl.x + bl.y * tl.x)
            );
            const totalArea = pw * ph;
            const areaRatio = quadArea / totalArea;
            if (areaRatio < 0.15 || areaRatio > 0.92) {
                return { success: false };
            }

            // Aspect Ratio Check
            const dist = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);
            const avgW = (dist(tl, tr) + dist(bl, br)) / 2;
            const avgH = (dist(tl, bl) + dist(tr, br)) / 2;
            const aspect = avgW / avgH;
            if (aspect < 0.35 || aspect > 2.8) {
                return { success: false };
            }

            // 6. Map to display coordinates (cropImgRect)
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

    // ── Manual Crop Re-adjustment for Active Page ──
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
                    window.currentCorners = [
                        { x: p.corners[0].x, y: p.corners[0].y },
                        { x: p.corners[1].x, y: p.corners[1].y },
                        { x: p.corners[2].x, y: p.corners[2].y },
                        { x: p.corners[3].x, y: p.corners[3].y }
                    ];
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

    // ── Heckbert Projective Homography Perspective Correction ──
    function applyCropAndPerspective() {
        const rawW = cropImg.naturalWidth;
        const rawH = cropImg.naturalHeight;
        const dispW = cropImgRect.width || 320;
        const dispH = cropImgRect.height || 420;

        // Map corners to natural image coordinates
        const scaleX = rawW / dispW;
        const scaleY = rawH / dispH;
        const s = window.currentCorners.map(p => ({
            x: p.x * scaleX,
            y: p.y * scaleY
        }));

        // Calculate destination dimensions
        const dist = (p1, p2) => Math.hypot(p1.x - p2.x, p1.y - p2.y);
        const targetW = Math.round(Math.max(dist(s[0], s[1]), dist(s[3], s[2])));
        const targetH = Math.round(Math.max(dist(s[0], s[3]), dist(s[1], s[2])));

        // Clamp maximum resolution to 1600px on the longest side to keep memory light
        const maxDim = 1600;
        let finalW = targetW;
        let finalH = targetH;
        if (Math.max(finalW, finalH) > maxDim) {
            const downscale = maxDim / Math.max(finalW, finalH);
            finalW = Math.round(finalW * downscale);
            finalH = Math.round(finalH * downscale);
        }

        // Draw rotated raw image to offscreen canvas first if rotated
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

        const srcCtx = offCanvas.getContext('2d');
        const srcData = srcCtx.getImageData(0, 0, offCanvas.width, offCanvas.height);

        // Target canvas
        const dstCanvas = document.createElement('canvas');
        dstCanvas.width = finalW;
        dstCanvas.height = finalH;
        const dstCtx = dstCanvas.getContext('2d');
        const dstData = dstCtx.createImageData(finalW, finalH);

        // Heckbert Projective Homography Mapping: Unit Square [0,1]x[0,1] -> Quad
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
            // Affine
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

        const sWidth = offCanvas.width;
        const sHeight = offCanvas.height;
        const sPixels = srcData.data;
        const dPixels = dstData.data;

        // Pixel inverse sampling
        for (let y = 0; y < finalH; y++) {
            const v = y / finalH;
            for (let x = 0; x < finalW; x++) {
                const u = x / finalW;
                const denom = (g * u + h * v + 1.0) || 1e-6;
                const srcX = Math.round((a * u + b * v + c) / denom);
                const srcY = Math.round((d * u + e * v + f) / denom);

                const dstIdx = (y * finalW + x) * 4;
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

        // Store / update page
        if (isReEditingPage && pages[activePageIndex]) {
            pages[activePageIndex].cleanCanvas = dstCanvas;
            pages[activePageIndex].corners = window.currentCorners.map(p => ({ x: p.x, y: p.y }));
            pages[activePageIndex].rotation = cropRotation;
            applyFilterToPage(pages[activePageIndex]);
            isReEditingPage = false;
        } else {
            const newPage = {
                id: Date.now(),
                sourceImgSrc: cropImg.src,
                cleanCanvas: dstCanvas,
                corners: window.currentCorners.map(p => ({ x: p.x, y: p.y })),
                rotation: cropRotation,
                filter: 'bw',
                displayCanvas: document.createElement('canvas')
            };
            applyFilterToPage(newPage);
            pages.push(newPage);
            activePageIndex = pages.length - 1;
        }

        renderReviewScreen();
    }

    // ── Filters ──
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

        if (page.filter === 'grayscale') {
            for (let i = 0; i < data.length; i += 4) {
                const y = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                data[i] = y;
                data[i + 1] = y;
                data[i + 2] = y;
            }
        } else if (page.filter === 'bw') {
            // Black & White Binarization: contrast stretch and threshold
            for (let i = 0; i < data.length; i += 4) {
                const y = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                const val = y > 135 ? 255 : (y < 85 ? 0 : (y - 85) * 5.1);
                data[i] = val;
                data[i + 1] = val;
                data[i + 2] = val;
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

    // ── Review Screen & Multi-page ──
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

        // Update active filter chip UI
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

            // Delete button (only if > 1 page)
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

    // ── PDF Generation & Submission ──
    async function submitFinalDocument(e) {
        e.preventDefault();

        if (pages.length === 0) {
            alert("Belum ada halaman dokumen yang dipindai.");
            return;
        }

        const docName = document.getElementById('camDocName').value.trim();
        const docType = document.getElementById('camDocType').value;
        const docDesc = document.getElementById('camDocDesc').value.trim();

        // Determine target case_id
        let targetCaseId = activeOptions.caseId;
        if (!targetCaseId) {
            const caseSelect = document.getElementById('camCaseSelect');
            targetCaseId = caseSelect ? caseSelect.value : null;
        }

        if (!targetCaseId) {
            alert("Perkara tujuan wajib dipilih.");
            return;
        }

        // Show loading
        const submitBtn = document.getElementById('camSubmitBtn');
        const submitText = document.getElementById('camSubmitText');
        const submitSpinner = document.getElementById('camSubmitSpinner');
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitSpinner.style.display = 'inline';

        try {
            // Generate PDF using jsPDF
            if (!window.jspdf || !window.jspdf.jsPDF) {
                throw new Error("Library jsPDF tidak ditemukan. Pastikan file jspdf.umd.min.js telah dimuat.");
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

                // Fit to A4
                const pageWidth = isLandscape ? 297 : 210;
                const pageHeight = isLandscape ? 210 : 297;

                // Scale image to fit inside A4 margins (5mm margin)
                const margin = 5;
                const maxW = pageWidth - (margin * 2);
                const maxH = pageHeight - (margin * 2);

                const ratio = Math.min(maxW / pCanvas.width, maxH / pCanvas.height);
                const drawW = pCanvas.width * ratio;
                const drawH = pCanvas.height * ratio;
                const posX = (pageWidth - drawW) / 2;
                const posY = (pageHeight - drawH) / 2;

                const imgDataUrl = pCanvas.toDataURL('image/jpeg', 0.82);
                pdf.addImage(imgDataUrl, 'JPEG', posX, posY, drawW, drawH);
            }

            const pdfBlob = pdf.output('blob');

            // Size validation against 10MB limit
            const maxSize = 10 * 1024 * 1024;
            if (pdfBlob.size > maxSize) {
                alert("Ukuran dokumen melebihi batas 10 MB. Silakan kurangi jumlah halaman atau kualitas scan sebelum menyimpan.");
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                submitSpinner.style.display = 'none';
                return;
            }

            const cleanFileName = docName.replace(/[^a-zA-Z0-9_\-\s]/g, '').trim() || 'Dokumen_Scan';
            const pdfFile = new File([pdfBlob], `${cleanFileName}.pdf`, { type: 'application/pdf' });

            // Build FormData
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

            // Determine route based on role
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

            // Success: Close and reload to show new document with flash message
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
