{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@push('styles')
<style>
    .page-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem 1.3rem 1.8rem;
        border-radius: 1.1rem;
        margin-bottom: 2.2rem;
        border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);
        width: 54px; height: 54px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px #1498ff30;
        font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold;
        color: #0056b3;
        font-size: 1.85rem;
        margin-bottom: 0.13rem;
        letter-spacing: -1px;
    }
    .page-header-desc {
        color: #636e7b; font-size: 1.03rem;
    }
    @media (max-width: 767.98px) {
        .page-header { flex-direction: column; align-items: flex-start; padding: 1.2rem 1rem; gap: .7rem; }
        .page-header-title { font-size: 1.18rem; }
        .page-header-desc { font-size: .99rem; }
    }
    .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }
    .card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }
    .table thead th {
        background-color: #343a40;
        color: #fff;
        border-bottom: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f3f5;
    }
    .btn-action {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease-in-out;
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
    .badge-status {
        padding: 0.5em 0.75em;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon">
        <i class="fas fa-users-cog text-white"></i>
    </span>
    <span>
        <div class="page-header-title">Manajemen Pengguna</div>
        <div class="page-header-desc">
            Kelola seluruh data <b>pengguna, peran, dan akses</b> dalam sistem. Tambahkan, edit, atau hapus pengguna di sini.
        </div>
    </span>
    <span class="ml-auto d-flex align-items-center gap-2">
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm mr-2">
            <i class="fas fa-user-plus mr-1"></i> Tambah Pengguna
        </a>
        <a href="#" class="btn btn-secondary shadow-sm" data-toggle="modal" data-target="#modal-peran">
            <i class="fas fa-user-tag mr-1"></i> Kelola Peran
        </a>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('users.peran.modal')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="table-users">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Bergabung Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $u->nama_lengkap }}</div>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->jabatan ?? '-' }}</td>
                            <td>
                                {!! badge_peran(optional($u->peran)->nama ?? 'N/A', optional($u->peran)->deskripsi ?? '-') !!}
                            </td>
                            <td>
                                @if($u->status == 'aktif')
                                    <span class="badge badge-success badge-status">Aktif</span>
                                @else
                                    <span class="badge badge-danger badge-status">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $u->created_at ? $u->created_at->isoFormat('D MMMM YYYY') : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-warning btn-action" data-toggle="tooltip" title="Edit Pengguna">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-action btn-hapus-user" data-id="{{ $u->id }}" data-toggle="tooltip" title="Hapus Pengguna">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Belum ada data pengguna.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3">
                {{-- Menampilkan link pagination --}}
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Inisialisasi Tooltip dari Bootstrap
    $('[data-toggle="tooltip"]').tooltip();

    // Notifikasi sukses pakai SweetAlert2
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2000
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
        });
    @endif

    // Hapus User pakai SweetAlert2
    $(document).on('click', '.btn-hapus-user', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Anda Yakin?',
            text: 'Pengguna akan dihapus (soft delete).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#form-hapus-'+id).submit();
            }
        });
    });
});
</script>
@endpush
