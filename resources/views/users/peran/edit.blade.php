@extends('layouts.app')

@section('title', 'Manajemen Peran')

@section('content')
<div class="container-fluid">
    <h1 class="mb-3">Manajemen Peran</h1>

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modal-tambah-peran">
        <i class="fa fa-plus"></i> Tambah Peran
    </button>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-sm" id="table-roles">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Peran</th>
                        <th>Deskripsi</th>
                        <th>Dibuat Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="roles-body">
                    @foreach($roles as $r)
                    <tr data-id="{{ $r->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td class="nama">{{ $r->nama }}</td>
                        <td class="deskripsi">{{ $r->deskripsi }}</td>
                        <td>{{ $r->dibuat_pada ? \Carbon\Carbon::parse($r->dibuat_pada)->format('d-m-Y H:i') : '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning btn-edit" data-id="{{ $r->id }}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-hapus" data-id="{{ $r->id }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modal-tambah-peran" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="form-tambah-peran">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahLabel">Tambah Peran Baru</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="msg-tambah"></div>
          <div class="form-group">
            <label>Nama Peran</label>
            <input type="text" class="form-control" name="nama" required>
          </div>
          <div class="form-group">
            <label>Deskripsi (opsional)</label>
            <input type="text" class="form-control" name="deskripsi">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modal-edit-peran" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="form-edit-peran">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditLabel">Edit Peran</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="msg-edit"></div>
          <input type="hidden" name="id" id="edit-id">
          <div class="form-group">
            <label>Nama Peran</label>
            <input type="text" class="form-control" name="nama" id="edit-nama" required>
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <input type="text" class="form-control" name="deskripsi" id="edit-deskripsi">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
    // Tambah peran
    $('#form-tambah-peran').submit(function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            url: "{{ route('roles.store') }}",
            type: 'POST',
            data: data,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(res){
                $('#msg-tambah').html('<div class="alert alert-success">'+res.message+'</div>');
                // Tambah ke tabel tanpa reload
                let idx = $('#table-roles tbody tr').length + 1;
                let row = `<tr data-id="${res.role.id}">
                    <td>${idx}</td>
                    <td class="nama">${res.role.nama}</td>
                    <td class="deskripsi">${res.role.deskripsi ?? ''}</td>
                    <td>${res.role.dibuat_pada ?? '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning btn-edit" data-id="${res.role.id}"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger btn-hapus" data-id="${res.role.id}"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>`;
                $('#table-roles tbody').append(row);
                setTimeout(function(){
                    $('#modal-tambah-peran').modal('hide');
                    $('#msg-tambah').html('');
                    $('#form-tambah-peran')[0].reset();
                }, 800);
            },
            error: function(xhr){
                let msg = xhr.responseJSON?.errors?.nama
                    ? xhr.responseJSON.errors.nama[0]
                    : 'Gagal menambah peran.';
                $('#msg-tambah').html('<div class="alert alert-danger">'+msg+'</div>');
            }
        });
    });

    // Modal edit show
    $(document).on('click', '.btn-edit', function(){
        let tr = $(this).closest('tr');
        let id = tr.data('id');
        let nama = tr.find('.nama').text().trim();
        let deskripsi = tr.find('.deskripsi').text().trim();
        $('#edit-id').val(id);
        $('#edit-nama').val(nama);
        $('#edit-deskripsi').val(deskripsi);
        $('#modal-edit-peran').modal('show');
    });

    // Submit update peran
    $('#form-edit-peran').submit(function(e){
        e.preventDefault();
        let id = $('#edit-id').val();
        let data = $(this).serialize();
        $.ajax({
            url: '/roles/' + id,
            type: 'PUT',
            data: data,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(res){
                $('#msg-edit').html('<div class="alert alert-success">'+res.message+'</div>');
                // Update row pada tabel
                let tr = $('#table-roles tbody tr[data-id="'+id+'"]');
                tr.find('.nama').text(res.role.nama);
                tr.find('.deskripsi').text(res.role.deskripsi ?? '');
                setTimeout(function(){
                    $('#modal-edit-peran').modal('hide');
                    $('#msg-edit').html('');
                }, 800);
            },
            error: function(xhr){
                let msg = xhr.responseJSON?.errors?.nama
                    ? xhr.responseJSON.errors.nama[0]
                    : 'Gagal update peran.';
                $('#msg-edit').html('<div class="alert alert-danger">'+msg+'</div>');
            }
        });
    });

    // Hapus peran
    $(document).on('click', '.btn-hapus', function(){
        if(!confirm('Yakin ingin menghapus peran ini?')) return;
        let tr = $(this).closest('tr');
        let id = tr.data('id');
        $.ajax({
            url: '/roles/' + id,
            type: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            success: function(res){
                tr.remove();
            },
            error: function(){
                alert('Gagal menghapus peran.');
            }
        });
    });
});
</script>
@endpush
