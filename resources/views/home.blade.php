@extends('layouts.app')

@section('title', 'Dashboard')
@php
use Illuminate\Support\Facades\DB;
$onlineUsers = DB::table('pengguna')
    ->whereNotNull('last_activity')
    ->where('last_activity', '>=', now()->subMinutes(5))
    ->orderBy('nama_lengkap')
    ->get();
$jumlahOnline = $onlineUsers->count();
@endphp

@section('page-header')
    <h1 class="m-0 text-dark"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Selamat Datang!</h5>
                </div>
                <div class="card-body">
                    <p>Selamat datang di panel admin yang indah ini.</p>
                    {{-- Tambahkan info, statistik, atau link lainnya di sini --}}
                </div>
            </div>
        </div>
        <div class="col-lg-6">
    <div class="card">
        <div class="card-header bg-success text-white d-flex align-items-center">
            <i class="fas fa-users fa-lg mr-2"></i>
            <strong>Online Users</strong>
            <span class="badge bg-light text-success ml-auto">{{ $jumlahOnline }}</span>
        </div>
        <div class="card-body p-3">
            <small class="text-muted d-block mb-2">
                {{ $jumlahOnline }} user online (last 5 minutes)
            </small>
            @if ($jumlahOnline > 0)
                <ul class="list-unstyled mb-0">
                    @foreach ($onlineUsers as $u)
                        <li class="d-flex align-items-center mb-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2"
                                style="width:32px;height:32px;font-weight:bold;font-size:1rem;">
                                {{ strtoupper(substr($u->nama_lengkap,0,1)) }}
                            </div>
                            <span>{{ $u->nama_lengkap }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center text-muted small">Tidak ada user online.</div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card {
        border-radius: 14px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    console.log("Dashboard loaded!");
</script>
@endpush
