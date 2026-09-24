@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card">
    <div class="action-toolbar">
        <div class="search-box">
            <i data-lucide="search" style="color: var(--color-gray-text); width: 16px;"></i>
            <input type="text" placeholder="Cari nama, email, role..." id="searchInput" oninput="filterTable(this.value)">
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.lawyers') }}" class="btn btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <i data-lucide="user-plus"></i> Kelola Advokat
            </a>
        </div>
    </div>

    <div class="table-container">
        <table id="usersTable">
            <thead>
                <tr>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                @php
                    $roleStyle = match($u->role) {
                        'admin'   => 'background-color: var(--color-gray-dark); color: white;',
                        'advokat' => 'background-color: var(--color-navy); color: white;',
                        default   => 'background-color: var(--color-gray-light); color: var(--color-gray-dark);',
                    };
                    $badgeClass = $u->status === 'aktif' ? 'badge-success' : 'badge-danger';
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:#1e3a5f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 500; color: var(--color-gray-dark);">{{ $u->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-text);">Dibuat: {{ $u->created_at ? $u->created_at->format('d M Y') : '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge" style="{{ $roleStyle }}">{{ ucfirst($u->role) }}</span></td>
                    <td><span class="badge {{ $badgeClass }}">{{ ucfirst($u->status) }}</span></td>
                    <td class="text-right">
                        @if($u->isAdmin())
                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem;" disabled title="Admin Utama"><i data-lucide="shield" style="width: 14px;"></i></button>
                        @else
                            <button type="button" class="btn btn-outline" style="padding: 0.25rem 0.5rem;" onclick="alert('Informasi akun: {{ $u->name }} ({{ $u->email }})')">
                                <i data-lucide="user" style="width: 14px;"></i> Detail
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:2rem;color:var(--color-gray-text);">Tidak ada data pengguna.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(val.toLowerCase()) ? '' : 'none';
    });
}
</script>
@endsection
