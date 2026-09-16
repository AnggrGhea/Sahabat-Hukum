@extends('layouts.advokat')

@section('title', 'Jadwal')

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

    // Format data jadwal
    $schedulesFormatted = $schedules->map(function($s) use ($monthAbbrIndo, $dayNamesIndo, $monthNamesIndo) {
        $start = $s->start_at;
        $end   = $s->end_at;

        // Tentukan kategori berdasarkan relasi existing
        if ($s->case_id !== null || $s->case !== null) {
            $typeKey   = 'sidang';
            $typeLabel = 'Sidang';
        } elseif ($s->consultation_id !== null || $s->consultation !== null) {
            $typeKey   = 'konsultasi';
            $typeLabel = 'Konsultasi';
        } else {
            $lowerTitle = strtolower($s->title);
            if (str_contains($lowerTitle, 'sidang')) {
                $typeKey   = 'sidang';
                $typeLabel = 'Sidang';
            } elseif (str_contains($lowerTitle, 'konsultasi')) {
                $typeKey   = 'konsultasi';
                $typeLabel = 'Konsultasi';
            } else {
                $typeKey   = 'neutral';
                $typeLabel = 'Jadwal';
            }
        }

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
            'location'       => $s->location ?: 'Kantor',
            'status'         => $s->status ?: 'Aktif',
            'client_name'    => $s->client ? $s->client->name : null,
            'case_number'    => $s->case ? $s->case->case_number : null,
            'case_title'     => $s->case ? $s->case->title : null,
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

    $todayDateStr = Carbon::now('Asia/Jakarta')->format('Y-m-d');
@endphp

<div class="schedule-page-container">

    {{-- ═══════════ HEADER & VIEW SWITCHER ═══════════ --}}
    <div class="schedule-header-row">
        <div class="schedule-title-area">
            <h1>Jadwal</h1>
            <p>Jadwal konsultasi dan agenda kegiatan perkara Anda.</p>
        </div>

        <div class="schedule-view-switcher">
            <a href="{{ route('advokat.schedule', ['month' => $currentMonth, 'view' => 'bulanan']) }}"
               class="schedule-tab-btn {{ $view === 'bulanan' ? 'active' : '' }}">
                Bulanan
            </a>
            <a href="{{ route('advokat.schedule', ['month' => $currentMonth, 'view' => 'mingguan']) }}"
               class="schedule-tab-btn {{ $view === 'mingguan' ? 'active' : '' }}">
                Mingguan
            </a>
            <a href="{{ route('advokat.schedule', ['month' => $currentMonth, 'view' => 'agenda']) }}"
               class="schedule-tab-btn {{ $view === 'agenda' ? 'active' : '' }}">
                Agenda
            </a>
        </div>
    </div>

    @if ($view === 'bulanan')
    {{-- ═══════════ MAIN CONTENT: VIEW BULANAN ═══════════ --}}
    <div class="schedule-grid-layout">

        {{-- LEFT: KALENDER BULANAN CARD --}}
        <div class="calendar-card">
            {{-- Navigation Header --}}
            <div class="calendar-header">
                <a href="{{ route('advokat.schedule', ['month' => $prevMonthStr, 'view' => 'bulanan']) }}" class="calendar-nav-btn" title="Bulan Sebelumnya">
                    <i data-lucide="chevron-left"></i>
                </a>

                <div class="calendar-month-title">
                    {{ $activeMonthTitle }}
                </div>

                <a href="{{ route('advokat.schedule', ['month' => $nextMonthStr, 'view' => 'bulanan']) }}" class="calendar-nav-btn" title="Bulan Berikutnya">
                    <i data-lucide="chevron-right"></i>
                </a>
            </div>

            {{-- Day Header --}}
            <div class="calendar-days-header">
                <div class="calendar-day-header-item">Min</div>
                <div class="calendar-day-header-item">Sen</div>
                <div class="calendar-day-header-item">Sel</div>
                <div class="calendar-day-header-item">Rab</div>
                <div class="calendar-day-header-item">Kam</div>
                <div class="calendar-day-header-item">Jum</div>
                <div class="calendar-day-header-item">Sab</div>
            </div>

            {{-- Days Grid --}}
            <div class="calendar-grid">
                {{-- Leading empty cells --}}
                @for ($i = 0; $i < $startDayOfWeek; $i++)
                    <div class="calendar-cell empty-cell"></div>
                @endfor

                {{-- Month Days --}}
                @for ($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dayDateStr = sprintf('%04d-%02d-%02d', $activeYear, $activeMonthNum, $d);
                        $hasEvents = isset($schedulesByDate[$dayDateStr]);
                        $dayEvents = $hasEvents ? $schedulesByDate[$dayDateStr] : [];

                        $hasConsultation = false;
                        $hasHearing = false;
                        $hasNeutral = false;
                        foreach ($dayEvents as $ev) {
                            if ($ev['type'] === 'sidang') {
                                $hasHearing = true;
                            } elseif ($ev['type'] === 'konsultasi') {
                                $hasConsultation = true;
                            } else {
                                $hasNeutral = true;
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
                                    @if ($hasHearing)
                                        <span class="event-dot event-dot-hearing" title="Sidang Perkara"></span>
                                    @endif
                                    @if ($hasConsultation)
                                        <span class="event-dot event-dot-consultation" title="Konsultasi"></span>
                                    @endif
                                    @if ($hasNeutral && !$hasHearing && !$hasConsultation)
                                        <span class="event-dot event-dot-neutral" title="Jadwal"></span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- RIGHT: DETAIL CARD & JADWAL MENDATANG --}}
        <div class="schedule-right-column">

            {{-- Detail Tanggal Terpilih --}}
            <div class="schedule-card" id="detailCard">
                {{-- State 1: Belum Memilih Tanggal --}}
                <div id="stateNoSelection" class="detail-empty-state" style="{{ $selectedDate ? 'display:none;' : '' }}">
                    <i data-lucide="calendar" class="detail-empty-icon"></i>
                    <div class="detail-empty-text">Pilih tanggal dengan jadwal.</div>
                </div>

                {{-- State 2: Tanggal Dipilih Tapi Tidak Ada Jadwal --}}
                <div id="stateNoEvents" class="detail-empty-state" style="display:none;">
                    <div class="detail-card-header" style="width:100%;text-align:left;margin-bottom:16px;">
                        <span class="detail-card-title" id="selectedDateTitleNoEvents">-</span>
                    </div>
                    <i data-lucide="calendar-x" class="detail-empty-icon"></i>
                    <div class="detail-empty-text">Tidak ada jadwal pada tanggal ini.</div>
                </div>

                {{-- State 3: Tanggal Dipilih dan Memiliki Jadwal --}}
                <div id="stateHasEvents" style="display:none;">
                    <div class="detail-card-header">
                        <span class="detail-card-title" id="selectedDateTitle">-</span>
                    </div>
                    <div id="selectedDateEventsList"></div>
                </div>
            </div>

            {{-- Jadwal Mendatang --}}
            <div class="schedule-card">
                <div class="upcoming-card-title">Jadwal Mendatang</div>

                @if ($upcomingSchedules->isEmpty())
                    <div class="detail-empty-state" style="padding:20px 10px;">
                        <i data-lucide="calendar" class="detail-empty-icon" style="width:36px;height:36px;"></i>
                        <div class="detail-empty-text">Belum ada jadwal mendatang.</div>
                    </div>
                @else
                    <div class="upcoming-list">
                        @foreach ($upcomingSchedules as $upcoming)
                            @php
                                $upStart = $upcoming->start_at;
                                $upEnd   = $upcoming->end_at;

                                if ($upcoming->case_id !== null || $upcoming->case !== null) {
                                    $upType = 'Sidang';
                                    $catClass = 'cat-sidang';
                                } elseif ($upcoming->consultation_id !== null || $upcoming->consultation !== null) {
                                    $upType = 'Konsultasi';
                                    $catClass = 'cat-konsultasi';
                                } else {
                                    $lowerTitle = strtolower($upcoming->title);
                                    if (str_contains($lowerTitle, 'sidang')) {
                                        $upType = 'Sidang';
                                        $catClass = 'cat-sidang';
                                    } elseif (str_contains($lowerTitle, 'konsultasi')) {
                                        $upType = 'Konsultasi';
                                        $catClass = 'cat-konsultasi';
                                    } else {
                                        $upType = 'Jadwal';
                                        $catClass = 'cat-neutral';
                                    }
                                }

                                $upTime = $upStart ? $upStart->format('H.i') : '';
                                if ($upEnd) {
                                    $upTime .= ' – ' . $upEnd->format('H.i');
                                } elseif ($upStart) {
                                    $upTime .= ' – ' . $upStart->copy()->addHour()->format('H.i');
                                }

                                $upLoc = $upcoming->location ?: ($upType === 'Sidang' ? 'Pengadilan Negeri Subang' : 'Kantor');
                                $upTimeLoc = $upTime . ' - ' . $upLoc;

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
                                    <div class="upcoming-time-loc">{{ $upTimeLoc }}</div>
                                    <span class="upcoming-cat-badge {{ $catClass }}">{{ $upType }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>{{-- end schedule-right-column --}}
    </div>{{-- end schedule-grid-layout --}}

    @elseif ($view === 'mingguan')
    {{-- ═══════════ VIEW MINGGUAN (WEEKLY VIEW) ═══════════ --}}
    <div class="weekly-calendar-card">
        <div class="calendar-header" style="margin-bottom:16px;">
            <div class="calendar-month-title">
                Kalender Mingguan — {{ $activeMonthTitle }}
            </div>
            <div style="font-size:0.825rem;color:var(--color-gray-text);">
                Jadwal sidang dan konsultasi selama minggu terpilih
            </div>
        </div>

        @php
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
                        <div style="flex:1;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:0.75rem;">
                            —
                        </div>
                    @else
                        @foreach ($colEvents as $ev)
                            @php
                                $cardTypeClass = $ev['type'] === 'sidang' ? 'type-hearing' : ($ev['type'] === 'konsultasi' ? 'type-consultation' : 'type-neutral');
                            @endphp
                            <div class="weekly-event-card {{ $cardTypeClass }}">
                                <div class="weekly-event-time">{{ $ev['time_range'] }}</div>
                                <div class="weekly-event-title">{{ $ev['title'] }}</div>
                                <div style="font-size:0.675rem;color:var(--color-gray-text);margin-top:2px;">{{ $ev['location'] }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endfor
        </div>
    </div>

    @elseif ($view === 'agenda')
    {{-- ═══════════ VIEW AGENDA (CHRONOLOGICAL) ═══════════ --}}
    <div class="agenda-card">
        <div class="calendar-header" style="margin-bottom:16px;">
            <div class="calendar-month-title">
                Daftar Agenda Kegiatan — {{ $activeMonthTitle }}
            </div>
            <div style="font-size:0.825rem;color:var(--color-gray-text);">
                Urutan kronologis agenda perkara dan jadwal konsultasi
            </div>
        </div>

        @if (empty($schedulesByDate))
            <div class="detail-empty-state" style="padding:40px 16px;">
                <i data-lucide="calendar" class="detail-empty-icon" style="width:48px;height:48px;"></i>
                <div class="detail-empty-text" style="font-size:0.925rem;font-weight:600;color:var(--color-navy);">
                    Belum Ada Agenda Terjadwal
                </div>
                <div style="font-size:0.8rem;color:var(--color-gray-text);margin-top:4px;">
                    Tidak ada kegiatan sidang maupun konsultasi pada periode ini.
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
                        @php
                            $badgeClass = $ev['type'] === 'sidang' ? 'badge-type-hearing' : ($ev['type'] === 'konsultasi' ? 'badge-type-consultation' : 'badge-type-neutral');
                            $statusClass = $ev['status'] === 'Aktif' ? 'badge-status-active' : ($ev['status'] === 'Selesai' ? 'badge-status-completed' : 'badge-status-cancelled');
                        @endphp
                        <div class="agenda-item">
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                                    <span class="badge-type {{ $badgeClass }}">
                                        {{ $ev['type_label'] }}
                                    </span>
                                    <span class="badge-status-sm {{ $statusClass }}">
                                        {{ $ev['status'] }}
                                    </span>
                                </div>
                                <h3 style="font-size:1rem;font-weight:700;color:var(--color-gray-dark);margin-bottom:6px;">{{ $ev['title'] }}</h3>

                                @if ($ev['description'])
                                    <p style="font-size:0.825rem;color:var(--color-gray-text);margin-bottom:8px;">{{ $ev['description'] }}</p>
                                @endif

                                <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:0.8125rem;color:var(--color-gray-text);">
                                    <span style="display:flex;align-items:center;gap:6px;">
                                        <i data-lucide="clock" style="width:14px;height:14px;"></i>
                                        {{ $ev['time_range'] }}
                                    </span>
                                    <span style="display:flex;align-items:center;gap:6px;">
                                        <i data-lucide="map-pin" style="width:14px;height:14px;"></i>
                                        {{ $ev['location'] }}
                                    </span>
                                    @if ($ev['client_name'])
                                        <span style="display:flex;align-items:center;gap:6px;">
                                            <i data-lucide="user" style="width:14px;height:14px;"></i>
                                            Klien: {{ $ev['client_name'] }}
                                        </span>
                                    @endif
                                    @if ($ev['case_title'])
                                        <span style="display:flex;align-items:center;gap:6px;">
                                            <i data-lucide="folder" style="width:14px;height:14px;"></i>
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
    @endif

</div>

@endsection

@push('scripts')
<script>
    const schedulesByDate = @json($schedulesByDate);
    const initialSelectedDate = "{{ $selectedDate ?? '' }}";

    function selectCalendarDate(dateStr) {
        document.querySelectorAll('.calendar-cell').forEach(cell => cell.classList.remove('selected-cell'));

        const targetCell = document.getElementById('cell-' + dateStr);
        if (targetCell) {
            targetCell.classList.add('selected-cell');
        }

        const stateNoSelection = document.getElementById('stateNoSelection');
        const stateNoEvents    = document.getElementById('stateNoEvents');
        const stateHasEvents   = document.getElementById('stateHasEvents');

        if (!stateNoSelection || !stateNoEvents || !stateHasEvents) return;

        stateNoSelection.style.display = 'none';

        const events = schedulesByDate[dateStr] || [];

        if (events.length === 0) {
            stateHasEvents.style.display = 'none';
            stateNoEvents.style.display = 'flex';

            const parts = dateStr.split('-');
            const formatted = formatDateDisplay(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
            document.getElementById('selectedDateTitleNoEvents').textContent = formatted;
        } else {
            stateNoEvents.style.display = 'none';
            stateHasEvents.style.display = 'block';

            document.getElementById('selectedDateTitle').textContent = events[0].date_formatted;

            const listContainer = document.getElementById('selectedDateEventsList');
            listContainer.innerHTML = '';

            events.forEach(item => {
                const isSidang = item.type === 'sidang';
                const isKonsultasi = item.type === 'konsultasi';
                const typeClass = isSidang ? 'badge-type-hearing' : (isKonsultasi ? 'badge-type-consultation' : 'badge-type-neutral');
                const statusClass = item.status === 'Aktif' ? 'badge-status-active' : (item.status === 'Selesai' ? 'badge-status-completed' : 'badge-status-cancelled');
                const locationClass = isSidang ? 'highlight-location' : '';

                let clientHtml = '';
                if (item.client_name) {
                    clientHtml = `
                        <div class="detail-meta-line" style="color:#475569;">
                            <i data-lucide="user" style="width:14px;height:14px;"></i>
                            <span>Klien: ${escapeHtml(item.client_name)}</span>
                        </div>
                    `;
                }

                let caseHtml = '';
                if (item.case_title) {
                    caseHtml = `
                        <div class="detail-meta-line" style="color:#475569;">
                            <i data-lucide="folder" style="width:14px;height:14px;"></i>
                            <span>${escapeHtml(item.case_title)}</span>
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
                        <i data-lucide="clock" style="width:14px;height:14px;"></i>
                        <span>${escapeHtml(item.time_range)}</span>
                    </div>
                    <div class="detail-meta-line ${locationClass}">
                        <i data-lucide="map-pin" style="width:14px;height:14px;"></i>
                        <span>${escapeHtml(item.location)}</span>
                    </div>
                    ${clientHtml}
                    ${caseHtml}
                `;
                listContainer.appendChild(itemCard);
            });

            if (window.lucide) {
                lucide.createIcons();
            }
        }
    }

    function focusOnUpcomingDate(dateStr) {
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

    document.addEventListener('DOMContentLoaded', function() {
        if (initialSelectedDate) {
            selectCalendarDate(initialSelectedDate);
        }
    });
</script>
@endpush
