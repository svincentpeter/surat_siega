@extends('layouts.app')

@section('title', 'Daftar Notifikasi')

@section('content_header')
    <h1>Daftar Notifikasi</h1>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header bg-purple text-white">
            <i class="fas fa-bell"></i> Semua Notifikasi
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Dibuat Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notifList as $n)
                        <tr @if(!$n->dibaca) class="font-weight-bold" @endif>
                            <td>{{ $n->pesan }}</td>
                            <td>
                                @if($n->dibaca)
                                    <span class="badge badge-success">Dibaca</span>
                                @else
                                    <span class="badge badge-warning">Belum Dibaca</span>
                                @endif
                            </td>
                            <td>{{ $n->dibuat_pada->format('d-m-Y H:i') }}</td>
                            <td>
                                @if(!$n->dibaca)
                                    <form action="{{ route('notifikasi.read', $n->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Tandai Dibaca
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>Dibaca</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($notifList->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">Belum ada notifikasi.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .bg-purple { background: #6f42c1 !important; color: #fff !important; }
</style>
@endpush
