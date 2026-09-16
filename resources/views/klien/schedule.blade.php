@extends('layouts.klien')

@section('title', 'Jadwal')

@section('header-title')
<div style="display:flex;flex-direction:column;gap:2px;">
    <div style="font-size:0.75rem;color:var(--gray-400);font-weight:400;display:flex;align-items:center;gap:4px;">
        <span>Beranda</span>
        <span style="font-size:0.7rem;">&gt;</span>
        <span style="color:var(--gray-600);">Jadwal</span>
    </div>
    <div style="font-size:1.05rem;font-weight:700;color:var(--gray-800);line-height:1.2;">Jadwal</div>
</div>
@endsection

@section('content')

@php
    use Carbon\Carbon;

    // Nama-nama bulan Indonesia
    $monthNamesIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $monthAbbrIndo = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    $dayNamesIndo = [
        0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
    ];

    $activeYear = (int) $targetDate->format('Y');
    $activeMonthNum = (int) $targetDate->format('n');
    $activeMonthTitle = ($monthNamesIndo[$activeMonthNum] ?? $targetDate->format('F')) . ' ' . $activeYear;

    $prevMonthStr = $targetDate->copy()->subMonth()->format('Y-m');
    $nextMonthStr = $targetDate->copy()->addMonth()->format('Y-m');

    // Persiapkan data jadwal dalam format array JS untuk interaktivitas kalender
    $schedulesFormatted = $schedules->map(function($s) use ($monthAbbrIndo, $dayNamesIndo, $monthNamesIndo) {
        $start = $s->start_at;
        $end   = $s->end_at;
        $isSidang = str_contains(strtolower($s->title), 'sidang') || $s->case_id !== null;
        $typeLabel = $isSidang ? 'Sidang Perkara' : 'Konsultasi Hukum';
        $typeKey   = $isSidang ? 'sidang' : 'konsultasi';

        $timeStr = $start ? $start->format('H.i') : '';
        if ($end) {
            $timeStr .= ' – ' . $end->format('H.i');
        } elseif ($start) {
            $timeStr .= ' – ' . $start->copy()->addHour()->format('H.i');
        }
        $timeStr .= ' WIB';

        $dayOfWeek = (int) $start->format('w');
        $mNum = (int) $start->format('n');
        $dateFormattedIndo = ($dayNamesIndo[$dayOfWeek] ?? '') . ', ' . $start->format('j') . ' ' . ($monthNamesIndo[$mNum] ?? '') . ' ' . $start->format('Y');

        return [
            'id'             => $s->id,
            'title'          => $s->title,
            'description'    => $s->description,
            'type'           => $typeKey,
            'type_label'     => $typeLabel,
            'date'           => $start ? $start->format('Y-m-d') : '',
            'day'            => $start ? (int)$start->format('j') : 0,
            'month_abbr'     => $start ? ($monthAbbrIndo[$mNum] ?? $start->format('M')) : '',
            'time_range'     => $timeStr,
            'location'       => $s->location ?: 'Konsultasi di Kantor',
            'status'         => $s->status ?: 'Aktif',
            'case_number'    => $s->case ? $s->case->case_number : null,
            'case_title'     => $s->case ? $s->case->title : null,
            'lawyer_name'    => $s->lawyer ? $s->lawyer->name : null,
            'date_formatted' => $dateFormattedIndo,
        ];
    });

    // Kelompokkan jadwal berdasarkan tanggal 'YYYY-MM-DD'
    $schedulesByDate = [];
    foreach ($schedulesFormatted as $item) {
        $schedulesByDate[$item['date']][] = $item;
    }

    // Hari dalam kalender bulanan
    $firstDayOfMonth = $targetDate->copy()->startOfMonth();
    $daysInMonth = (int) $targetDate->format('t');
    $startDayOfWeek = (int) $firstDayOfMonth->format('w'); // 0 (Minggu) - 6 (Sabtu)

    // Hari ini
    $todayDateStr = Carbon::now('Asia/Jakarta')->format('Y-m-d');
@endphp

<div class="schedule-page-container">

    {{-- ═══════════ HEADER & VIEW SWITCHER ═══════════ --}}
    <div class="schedule-header-row">
        <div class="schedule-title-area">
            <h1>Jadwal</h1>
            <p>Jadwal konsultasi dan kegiatan terkait perkara Anda.</p>
        </div>

        <div class="schedule-view-switcher">
            <button type="button" class="schedule-tab-btn {{ $view === 'bulanan' ? 'active' : '' }}" onclick="switchView('bulanan')">
                Bulanan
            </button>
            <button type="button" class="schedule-tab-btn {{ $view === 'mingguan' ? 'active' : '' }}" onclick="switchView('mingguan')">
                Mingguan
            </button>
            <button type="button" class="schedule-tab-btn {{ $view === 'agenda' ? 'active' : '' }}" onclick="switchView('agenda')">
                Agenda
            </button>
        </div>
    </div>

    {{-- ═══════════ MAIN CONTENT: VIEW BULANAN ═══════════ --}}
    <div id="viewBulananContainer" class="schedule-grid-layout" style="{{ $view === 'bulanan' ? '' : 'display:none;' }}">

        {{-- LEFT: KALENDER BULANAN CARD --}}
        <div class="calendar-card">
            {{-- Calendar Navigation Header --}}
            <div class="calendar-header">
                <a href="{{ route('klien.schedule', ['month' => $prevMonthStr, 'view' => 'bulanan']) }}" class="calendar-nav-btn" title="Bulan Sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>

                <div class="calendar-month-title">
                    {{ $activeMonthTitle }}
                </div>

                <a href="{{ route('klien.schedule', ['month' => $nextMonthStr, 'view' => 'bulanan']) }}" class="calendar-nav-btn" title="Bulan Berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>

            {{-- Day Header (Min, Sen, Sel, Rab, Kam, Jum, Sab) --}}
            <div class="calendar-days-header">
                <div class="calendar-day-header-item">Min</div>
                <div class="calendar-day-header-item">Sen</div>
                <div class="calendar-day-header-item">Sel</div>
                <div class="calendar-day-header-item">Rab</div>
                <div class="calendar-day-header-item">Kam</div>
                <div class="calendar-day-header-item">Jum</div>
                <div class="calendar-day-header-item">Sab</div>
            </div>

            {{-- Calendar Grid Days --}}
            <div class="calendar-grid">
                {{-- Leading empty/previous month cells --}}
                @for ($i = 0; $i < $startDayOfWeek; $i++)
                    <div class="calendar-cell empty-cell"></div>
                @endfor

                {{-- Month Days --}}
                @for ($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dayDateStr = sprintf('%04d-%02d-%02d', $activeYear, $activeMonthNum, $d);
                        $hasEvents = isset($schedulesByDate[$dayDateStr]);
                        $dayEvents = $hasEvents ? $schedulesByDate[$dayDateStr] : [];

                        // Cek jenis event untuk menentukan dot
                        $hasConsultation = false;
                        $hasHearing = false;
                        foreach ($dayEvents as $ev) {
                            if ($ev['type'] === 'sidang') {
                                $hasHearing = true;
                            } else {
                                $hasConsultation = true;
                            }
                        }

                        $isToday = ($dayDateStr === $todayDateStr);
                        $isSelected = ($selectedDate === $dayDateStr);
                    @endphp

                    <div class="calendar-cell {{ $isToday ? 'today' : '' }} {{ $isSelected ? 'selected-cell' : '' }}"
                         id="cell-{{ $dayDateStr }}"
                         data-date="{{ $dayDateStr }}"
                         onclick="selectCalendarDate('{{ $dayDateStr }}')">
                        <div class="day-number-wrapper">
                            <span class="calendar-day-num">{{ $d }}</span>
                            @if ($hasEvents)
                                <div class="event-dots-container">
                                    @if ($hasConsultation)
                                        <span class="event-dot event-dot-consultation" title="Konsultasi"></span>
                                    @endif
                                    @if ($hasHearing)
                                        <span class="event-dot event-dot-hearing" title="Sidang Perkara"></span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAIL CARD & JADWAL MENDATANG --}}
        <div class="schedule-right-column">

            {{-- Card Detail Tanggal Terpilih --}}
            <div class="schedule-card" id="detailCard">
                {{-- State 1: Belum Memilih Tanggal --}}
                <div id="stateNoSelection" class="detail-empty-state" style="{{ $selectedDate ? 'display:none;' : '' }}">
                    <svg class="detail-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <div class="detail-empty-text">Pilih tanggal dengan jadwal untuk melihat detail.</div>
                </div>

                {{-- State 2: Tanggal Dipilih Tapi Tidak Ada Jadwal --}}
                <div id="stateNoEvents" class="detail-empty-state" style="display:none;">
                    <div class="detail-card-header" style="width:100%;text-align:left;margin-bottom:16px;">
                        <span class="detail-card-title" id="selectedDateTitleNoEvents">-</span>
                    </div>
                    <svg class="detail-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div class="detail-empty-text">Pilih tanggal dengan jadwal untuk melihat detail.</div>
                    <div style="font-size:0.75rem;color:var(--gray-400);margin-top:4px;">Tidak ada agenda yang dijadwalkan pada tanggal ini.</div>
                </div>

                {{-- State 3: Tanggal Dipilih dan Memiliki Jadwal --}}
                <div id="stateHasEvents" style="display:none;">
                    <div class="detail-card-header">
                        <span class="detail-card-title" id="selectedDateTitle">-</span>
                    </div>
                    <div id="selectedDateEventsList"></div>
                </div>
            </div>

            {{-- Card Jadwal Mendatang --}}
            <div class="schedule-card">
                <div class="upcoming-card-title">Jadwal Mendatang</div>

                @if ($upcomingSchedules->isEmpty())
                    <div class="detail-empty-state" style="padding:20px 10px;">
                        <svg class="detail-empty-icon" style="width:36px;height:36px;color:var(--gray-300);" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <div class="detail-empty-text" style="font-size:0.8rem;">Tidak ada jadwal mendatang yang aktif saat ini.</div>
                    </div>
                @else
                    <div class="upcoming-list">
                        @foreach ($upcomingSchedules as $upcoming)
                            @php
                                $upStart = $upcoming->start_at;
                                $upEnd   = $upcoming->end_at;
                                $isSidangUp = str_contains(strtolower($upcoming->title), 'sidang') || $upcoming->case_id !== null;
                                $upType = $isSidangUp ? 'Sidang Perkara' : 'Konsultasi Hukum';

                                $upTime = $upStart ? $upStart->format('H.i') : '';
                                if ($upEnd) {
                                    $upTime .= ' – ' . $upEnd->format('H.i');
                                } elseif ($upStart) {
                                    $upTime .= ' – ' . $upStart->copy()->addHour()->format('H.i');
                                }
                                $upTime .= ' WIB';

                                $upMNum = (int)$upStart->format('n');
                                $upMAbbr = $monthAbbrIndo[$upMNum] ?? $upStart->format('M');
                                $upDateStr = $upStart->format('Y-m-d');
                            @endphp

                            <div class="upcoming-item" onclick="focusOnUpcomingDate('{{ $upDateStr }}')">
                                <div class="upcoming-date-box">
                                    <div class="upcoming-date-day">{{ $upStart->format('j') }}</div>
                                    <div class="upcoming-date-month">{{ $upMAbbr }}</div>
                                </div>
                                <div class="upcoming-content">
                                    <div class="upcoming-name">{{ $upcoming->title }}</div>
                                    <div class="upcoming-time">{{ $upTime }}</div>
                                    <div class="upcoming-location {{ $isSidangUp ? 'accent-amber' : '' }}">
                                        {{ $upcoming->location ?: ($isSidangUp ? 'Pengadilan Negeri Subang' : 'Konsultasi di Kantor') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>{{-- end schedule-right-column --}}
    </div>{{-- end viewBulananContainer --}}

    {{-- ═══════════ VIEW MINGGUAN (WEEKLY VIEW) ═══════════ --}}
    <div id="viewMingguanContainer" class="weekly-calendar-card" style="{{ $view === 'mingguan' ? '' : 'display:none;' }}">
        <div class="calendar-header" style="margin-bottom:16px;">
            <div class="calendar-month-title">
                Kalender Mingguan — {{ $activeMonthTitle }}
            </div>
            <div style="font-size:0.825rem;color:var(--gray-500);">
                Menampilkan jadwal kegiatan selama minggu berjalan
            </div>
        </div>

        @php
            // Rentang 7 hari untuk minggu berjalan (Minggu ke Sabtu)
            $weekStart = $targetDate->copy()->startOfWeek(Carbon::SUNDAY);
        @endphp

        <div class="weekly-grid">
            @for ($w = 0; $w < 7; $w++)
                @php
                    $dayColDate = $weekStart->copy()->addDays($w);
                    $dayColStr  = $dayColDate->format('Y-m-d');
                    $isColToday = ($dayColStr === $todayDateStr);
                    $colEvents  = $schedulesByDate[$dayColStr] ?? [];
                    $dayNameIdx = (int) $dayColDate->format('w');
                @endphp
                <div class="weekly-day-col {{ $isColToday ? 'today' : '' }}">
                    <div class="weekly-day-header">
                        <div class="weekly-day-name">{{ $dayNamesIndo[$dayNameIdx] ?? '' }}</div>
                        <div class="weekly-day-num">{{ $dayColDate->format('j') }}</div>
                    </div>

                    @if (empty($colEvents))
                        <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--gray-400);font-size:0.75rem;">
                            —
                        </div>
                    @else
                        @foreach ($colEvents as $ev)
                            <div class="weekly-event-card {{ $ev['type'] === 'sidang' ? 'type-hearing' : 'type-consultation' }}"
                                 onclick="selectCalendarDate('{{ $dayColStr }}'); switchView('bulanan');">
                                <div class="weekly-event-time">{{ $ev['time_range'] }}</div>
                                <div class="weekly-event-title">{{ $ev['title'] }}</div>
                                <div style="font-size:0.675rem;color:var(--gray-500);margin-top:2px;">{{ $ev['location'] }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- ═══════════ VIEW AGENDA (LIST VIEW) ═══════════ --}}
    <div id="viewAgendaContainer" class="agenda-card" style="{{ $view === 'agenda' ? '' : 'display:none;' }}">
        <div class="calendar-header" style="margin-bottom:16px;">
            <div class="calendar-month-title">
                Daftar Agenda Kegiatan — {{ $activeMonthTitle }}
            </div>
            <div style="font-size:0.825rem;color:var(--gray-500);">
                Urutan kronologis seluruh konsultasi dan jadwal persidangan
            </div>
        </div>

        @if (empty($schedulesByDate))
            <div class="detail-empty-state" style="padding:40px 16px;">
                <svg class="detail-empty-icon" style="width:48px;height:48px;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <div class="detail-empty-text" style="font-size:0.925rem;font-weight:600;color:var(--gray-700);">
                    Belum Ada Agenda Terjadwal
                </div>
                <div style="font-size:0.8rem;color:var(--gray-400);margin-top:4px;">
                    Tidak ada kegiatan konsultasi atau perkara yang tercatat pada bulan ini.
                </div>
            </div>
        @else
            @foreach ($schedulesByDate as $dateKey => $eventsOnDate)
                <div class="agenda-date-group">
                    <div class="agenda-date-header">
                        <span class="agenda-date-label">{{ $eventsOnDate[0]['date_formatted'] }}</span>
                        <div class="agenda-date-line"></div>
                    </div>

                    @foreach ($eventsOnDate as $ev)
                        <div class="agenda-item">
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                    <span class="badge-type {{ $ev['type'] === 'sidang' ? 'badge-type-hearing' : 'badge-type-consultation' }}">
                                        {{ $ev['type_label'] }}
                                    </span>
                                    <span class="badge-status-sm {{ $ev['status'] === 'Aktif' ? 'badge-status-active' : ($ev['status'] === 'Selesai' ? 'badge-status-completed' : 'badge-status-cancelled') }}">
                                        {{ $ev['status'] }}
                                    </span>
                                </div>
                                <h3 style="font-size:1rem;font-weight:700;color:var(--gray-800);margin-bottom:6px;">{{ $ev['title'] }}</h3>

                                @if ($ev['description'])
                                    <p style="font-size:0.825rem;color:var(--gray-600);margin-bottom:8px;">{{ $ev['description'] }}</p>
                                @endif

                                <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:0.8125rem;color:var(--gray-600);">
                                    <span style="display:flex;align-items:center;gap:6px;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--gray-400);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        {{ $ev['time_range'] }}
                                    </span>
                                    <span style="display:flex;align-items:center;gap:6px;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--gray-400);"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        {{ $ev['location'] }}
                                    </span>
                                    @if ($ev['lawyer_name'])
                                        <span style="display:flex;align-items:center;gap:6px;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--gray-400);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            Advokat: {{ $ev['lawyer_name'] }}
                                        </span>
                                    @endif
                                    @if ($ev['case_title'])
                                        <span style="display:flex;align-items:center;gap:6px;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;color:var(--gray-400);"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                                            {{ $ev['case_title'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>

</div>{{-- end schedule-page-container --}}

@endsection

@push('scripts')
<script>
    // Data jadwal yang dievaluasi dari server
    const schedulesByDate = @json($schedulesByDate);
    const initialSelectedDate = "{{ $selectedDate ?? '' }}";

    // Ubah tampilan antara Bulanan, Mingguan, Agenda
    function switchView(viewName) {
        document.querySelectorAll('.schedule-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.schedule-view-switcher button').forEach(btn => {
            if (btn.textContent.trim().toLowerCase() === viewName) {
                btn.classList.add('active');
            }
        });

        document.getElementById('viewBulananContainer').style.display = (viewName === 'bulanan') ? 'grid' : 'none';
        document.getElementById('viewMingguanContainer').style.display = (viewName === 'mingguan') ? 'block' : 'none';
        document.getElementById('viewAgendaContainer').style.display = (viewName === 'agenda') ? 'block' : 'none';
    }

    // Pemilihan tanggal kalender
    function selectCalendarDate(dateStr) {
        // Hilangkan highlight sebelumnya
        document.querySelectorAll('.calendar-cell').forEach(cell => cell.classList.remove('selected-cell'));

        // Pasang highlight pada cell terpilih
        const targetCell = document.getElementById('cell-' + dateStr);
        if (targetCell) {
            targetCell.classList.add('selected-cell');
        }

        const stateNoSelection = document.getElementById('stateNoSelection');
        const stateNoEvents    = document.getElementById('stateNoEvents');
        const stateHasEvents   = document.getElementById('stateHasEvents');

        stateNoSelection.style.display = 'none';

        const events = schedulesByDate[dateStr] || [];

        if (events.length === 0) {
            // State 2: Tanggal dipilih tapi TIDAK memiliki jadwal
            stateHasEvents.style.display = 'none';
            stateNoEvents.style.display = 'flex';

            // Format tanggal Indonesia sederhana
            const parts = dateStr.split('-');
            const formatted = formatDateDisplay(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
            document.getElementById('selectedDateTitleNoEvents').textContent = formatted;
        } else {
            // State 3: Tanggal dipilih dan MEMILIKI jadwal
            stateNoEvents.style.display = 'none';
            stateHasEvents.style.display = 'block';

            document.getElementById('selectedDateTitle').textContent = events[0].date_formatted;

            const listContainer = document.getElementById('selectedDateEventsList');
            listContainer.innerHTML = '';

            events.forEach(item => {
                const isSidang = item.type === 'sidang';
                const typeClass = isSidang ? 'badge-type-hearing' : 'badge-type-consultation';
                const statusClass = item.status === 'Aktif' ? 'badge-status-active' : (item.status === 'Selesai' ? 'badge-status-completed' : 'badge-status-cancelled');
                const locationClass = isSidang ? 'highlight-location' : '';

                let caseHtml = '';
                if (item.case_title) {
                    caseHtml = `
                        <div class="detail-meta-line" style="color:var(--gray-700);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            </svg>
                            <span>${escapeHtml(item.case_title)}</span>
                        </div>
                    `;
                }

                let lawyerHtml = '';
                if (item.lawyer_name) {
                    lawyerHtml = `
                        <div class="detail-meta-line" style="color:var(--gray-500);font-size:0.775rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span>Advokat: ${escapeHtml(item.lawyer_name)}</span>
                        </div>
                    `;
                }

                const itemCard = document.createElement('div');
                itemCard.className = 'detail-item-card';
                itemCard.innerHTML = `
                    <div class="detail-badge-row">
                        <span class="badge-type ${typeClass}">${escapeHtml(item.type_label)}</span>
                        <span class="badge-status-sm ${statusClass}">${escapeHtml(item.status)}</span>
                    </div>
                    <div class="detail-event-title">${escapeHtml(item.title)}</div>
                    <div class="detail-meta-line">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span>${escapeHtml(item.time_range)}</span>
                    </div>
                    <div class="detail-meta-line ${locationClass}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>${escapeHtml(item.location)}</span>
                    </div>
                    ${caseHtml}
                    ${lawyerHtml}
                `;
                listContainer.appendChild(itemCard);
            });
        }
    }

    // Klik dari daftar "Jadwal Mendatang"
    function focusOnUpcomingDate(dateStr) {
        switchView('bulanan');
        selectCalendarDate(dateStr);

        const targetCell = document.getElementById('cell-' + dateStr);
        if (targetCell) {
            targetCell.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function formatDateDisplay(year, month, day) {
        const monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return `${day} ${monthNames[month] || month} ${year}`;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Inisialisasi saat load jika tanggal terpilih diberikan
    document.addEventListener('DOMContentLoaded', function() {
        if (initialSelectedDate) {
            selectCalendarDate(initialSelectedDate);
        }
    });
</script>
@endpush
