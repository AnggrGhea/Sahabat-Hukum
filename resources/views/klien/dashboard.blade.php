@extends('layouts.klien')

@section('title', 'Beranda')
@section('header-title', 'Beranda')

@section('content')

{{-- =========================================================
     GREETING + CTA
========================================================= --}}
<div class="greeting-box">
    <div class="greeting-text">
        <h2>Selamat {{ $greeting }}, {{ $firstName }}.</h2>
        <p>{{ $date }}</p>
    </div>

    <a href="{{ route('klien.consultations') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Ajukan Konsultasi
    </a>
</div>


{{-- =========================================================
     STAT CARDS
========================================================= --}}
<div class="stats-grid">

    {{-- Konsultasi Aktif --}}
    <div class="stat-card">
        <div class="stat-icon-wrap stat-icon-blue">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>

        <div class="stat-number">
            {{ $stats['active_consultations'] ?? 0 }}
        </div>

        <div class="stat-label">
            Konsultasi Aktif
        </div>
    </div>


    {{-- Perkara Ditangani --}}
    <div class="stat-card">
        <div class="stat-icon-wrap stat-icon-amber">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
            </svg>
        </div>

        <div class="stat-number">
            {{ $stats['active_cases'] ?? 0 }}
        </div>

        <div class="stat-label">
            Perkara Ditangani
        </div>
    </div>


    {{-- Dokumen Diperlukan --}}
    <div class="stat-card">
        <div class="stat-icon-wrap stat-icon-orange">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>

        <div class="stat-number">
            {{ $stats['pending_documents'] ?? 0 }}
        </div>

        <div class="stat-label">
            Dokumen Diperlukan
        </div>
    </div>


    {{-- Jadwal Terdekat --}}
    <div class="stat-card">
        <div class="stat-icon-wrap stat-icon-purple">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>

        <div class="stat-number">
            {{ $stats['nearest_schedule'] ?? '-' }}
        </div>

        <div class="stat-label">
            {{ ($stats['nearest_schedule'] ?? '-') !== '-' ? 'Jadwal Terdekat' : 'Tidak Ada Jadwal' }}
        </div>
    </div>

</div>


{{-- =========================================================
     MAIN GRID
========================================================= --}}
<div class="dash-grid">


    {{-- =====================================================
         LEFT : PERKARA TERBARU
    ====================================================== --}}
    <div>

        <div class="card">

            {{-- Card Header --}}
            <div class="card-header">

                <div>

                    @if($latestCase)

                        <div class="card-title">
                            Perkembangan Perkara Terbaru
                        </div>

                        <div
                            style="
                                font-size:.75rem;
                                color:var(--gray-400);
                                margin-top:2px;
                            "
                        >
                            Perkara No.
                            {{ $latestCase->case_number ?? '-' }}

                            —

                            {{ $latestCase->case_type ?? '-' }}
                        </div>

                    @else

                        <div class="card-title">
                            Perkara Saya
                        </div>

                    @endif

                </div>


                <a
                    href="{{ route('klien.cases') }}"
                    class="card-link"
                >
                    Lihat Detail

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>

            </div>


            {{-- Card Body --}}
            <div
                class="card-body"
                style="padding:0 20px;"
            >


                {{-- =================================================
                     JIKA ADA PERKARA
                ================================================== --}}
                @if($latestCase)

                    {{-- Status Perkara --}}
                    <div
                        style="
                            padding:14px 0;
                            border-bottom:1px solid var(--gray-100);
                        "
                    >

                        <div
                            style="
                                display:flex;
                                align-items:center;
                                gap:10px;
                                background:var(--green-light);
                                border:1px solid #bbf7d0;
                                border-radius:8px;
                                padding:10px 14px;
                            "
                        >

                            {{-- Icon Status --}}
                            <div
                                style="
                                    width:28px;
                                    height:28px;
                                    background:#dcfce7;
                                    border-radius:50%;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    flex-shrink:0;
                                "
                            >

                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="#16a34a"
                                    stroke-width="2.5"
                                >
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>

                            </div>


                            {{-- Informasi Status --}}
                            <div>

                                <div
                                    style="
                                        font-size:.8125rem;
                                        font-weight:600;
                                        color:var(--green-text);
                                    "
                                >
                                    Perkara Aktif —
                                    {{ $latestCase->status ?? 'Belum ditentukan' }}
                                </div>


                                <div
                                    style="
                                        font-size:.75rem;
                                        color:var(--gray-500);
                                        margin-top:1px;
                                    "
                                >
                                    {{ $latestCase->lawyer?->name ?? 'Advokat belum ditentukan' }}
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         TIMELINE PERKEMBANGAN
                    ================================================== --}}
                    <div class="timeline">

                        @if($latestCase->progress && $latestCase->progress->count() > 0)

                            @foreach($latestCase->progress->sortByDesc('progress_date')->take(2) as $prog)

                                <div class="timeline-item">

                                    {{-- Timeline Dot --}}
                                    <div class="tl-dot-wrap">

                                        <div class="tl-dot tl-dot-gray">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="4"
                                                />
                                            </svg>

                                        </div>


                                        {{-- Timeline Line --}}
                                        @if(!$loop->last)
                                            <div class="tl-line"></div>
                                        @endif

                                    </div>


                                    {{-- Timeline Content --}}
                                    <div
                                        style="
                                            flex:1;
                                            padding-top:4px;
                                        "
                                    >

                                        {{-- Tanggal --}}
                                        <div class="tl-date">

                                            @if($prog->progress_date)

                                                {{ \Carbon\Carbon::parse($prog->progress_date)
                                                    ->locale('id')
                                                    ->isoFormat('D MMM YYYY') }}

                                            @else

                                                -

                                            @endif

                                        </div>


                                        {{-- Judul --}}
                                        <div class="tl-title">
                                            {{ $prog->title ?? 'Perkembangan perkara' }}
                                        </div>


                                        {{-- Deskripsi --}}
                                        @if(!empty($prog->description))

                                            <div class="tl-desc">
                                                {{ $prog->description }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            @endforeach

                        @else

                            <div
                                style="
                                    padding:1rem 0;
                                    color:var(--gray-400);
                                    font-size:.875rem;
                                "
                            >
                                Belum ada perkembangan perkara.
                            </div>

                        @endif

                    </div>


                {{-- =================================================
                     JIKA TIDAK ADA PERKARA
                ================================================== --}}
                @else

                    <div
                        style="
                            text-align:center;
                            padding:2rem;
                            color:var(--gray-400);
                            font-size:.875rem;
                        "
                    >

                        Belum ada perkara aktif.

                        <br>

                        <a
                            href="{{ route('klien.consultations') }}"
                            style="
                                color:var(--navy);
                                font-weight:600;
                            "
                        >
                            Ajukan Konsultasi
                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>



    {{-- =====================================================
         RIGHT : PEMBERITAHUAN
    ====================================================== --}}
    <div>

        <div class="card">

            {{-- Notification Header --}}
            <div class="card-header">

                <div class="card-title">
                    Pemberitahuan
                </div>

                <span
                    class="badge badge-red"
                    style="
                        background:#fee2e2;
                        color:var(--red);
                    "
                >
                    3
                </span>

            </div>


            {{-- Notification Body --}}
            <div
                class="card-body"
                style="padding:0 20px;"
            >

                <div class="notif-list">


                    {{-- =================================================
                         NOTIFIKASI 1
                    ================================================== --}}
                    <div class="notif-item">

                        <div class="notif-icon notif-icon-blue">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>

                        </div>


                        <div class="notif-text">

                            <div class="notif-msg">
                                Konsultasi dijadwalkan besok pukul 10.00
                            </div>

                            <div class="notif-time">
                                2 jam lalu
                            </div>

                        </div>

                    </div>



                    {{-- =================================================
                         NOTIFIKASI 2
                    ================================================== --}}
                    <div class="notif-item">

                        <div class="notif-icon notif-icon-orange">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line
                                    x1="12"
                                    y1="9"
                                    x2="12"
                                    y2="13"
                                />
                                <line
                                    x1="12"
                                    y1="17"
                                    x2="12.01"
                                    y2="17"
                                />
                            </svg>

                        </div>


                        <div class="notif-text">

                            <div class="notif-msg">
                                Dokumen Kartu Keluarga belum diunggah
                            </div>

                            <div class="notif-time">
                                5 jam lalu
                            </div>

                        </div>

                    </div>



                    {{-- =================================================
                         NOTIFIKASI 3
                    ================================================== --}}
                    <div class="notif-item">

                        <div class="notif-icon notif-icon-green">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>

                        </div>


                        <div class="notif-text">

                            <div class="notif-msg">
                                Dokumen KTP telah diterima Advokat
                            </div>

                            <div class="notif-time">
                                Kemarin
                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>

@endsection