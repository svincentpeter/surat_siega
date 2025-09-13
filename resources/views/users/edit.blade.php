{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container-fluid">
    <h1 class="mb-3">Edit Pengguna</h1>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" 
                           class="form-control @error('nama_lengkap') is-invalid @enderror" 
                           value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                    @error('nama_lengkap')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Tambahan field jabatan --}}
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan" 
                           class="form-control @error('jabatan') is-invalid @enderror" 
                           value="{{ old('jabatan', $user->jabatan) }}">
                    @error('jabatan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="peran_id">Peran</label>
                    <select name="peran_id" id="peran_id" 
                            class="form-control @error('peran_id') is-invalid @enderror" required>
                        <option value="">Pilih Peran</option>
                        @foreach($peran as $p)
                            <option value="{{ $p->id }}" {{ old('peran_id', $user->peran_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('peran_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                {{-- Tambahan field status --}}
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" 
                            class="form-control @error('status') is-invalid @enderror" required>
                        <option value="aktif" {{ old('status', $user->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ old('status', $user->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="password">Password Baru (opsional)</label>
                    <input type="password" name="password" id="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Kosongkan jika tidak ingin mengubah">
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
