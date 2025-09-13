<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar semua pengguna beserta peran.
     * Secara default, ini hanya akan menampilkan pengguna yang tidak di-soft-delete.
     */
    public function index()
    {
        // 1. Mengurutkan berdasarkan data terbaru dan menggunakan paginate untuk performa
        $users = User::with('peran')->latest()->paginate(15); 
        $roles = Peran::all();
        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        $peran = Peran::all();
        return view('users.create', compact('peran'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        // 2. Menambahkan validasi untuk 'jabatan' dan 'status'
        $validated = $request->validate([
            'email'         => 'required|email|unique:pengguna,email',
            'nama_lengkap'  => 'required|string|max:100',
            'jabatan'       => 'nullable|string|max:100', // Jabatan boleh kosong
            'peran_id'      => 'required|exists:peran,id',
            'status'        => ['required', Rule::in(['aktif', 'tidak_aktif'])], // Status harus 'aktif' atau 'tidak_aktif'
            'password'      => 'required|string|min:6|confirmed',
        ]);

        try {
            // 3. Menyesuaikan proses pembuatan data dengan kolom baru
            User::create([
                'email'         => strtolower($validated['email']),
                'password'      => Hash::make($validated['password']), // Menggunakan 'password' sesuai standar Laravel
                'nama_lengkap'  => $validated['nama_lengkap'],
                'jabatan'       => $validated['jabatan'],
                'peran_id'      => $validated['peran_id'],
                'status'        => $validated['status'],
                // 'created_at' dan 'updated_at' akan diisi otomatis oleh Laravel
            ]);

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            // Sebaiknya log error ini untuk debugging
            // Log::error('Gagal tambah user: ' . $e->getMessage()); 
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan user.');
        }
    }

    /**
     * Form edit user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $peran = Peran::all();
        return view('users.edit', compact('user', 'peran'));
    }

    /**
     * Simpan perubahan user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 4. Menambahkan validasi untuk field baru di proses update
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('pengguna')->ignore($user->id),
            ],
            'nama_lengkap'  => 'required|string|max:100',
            'jabatan'       => 'nullable|string|max:100',
            'peran_id'      => 'required|exists:peran,id',
            'status'        => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'password'      => 'nullable|string|min:6|confirmed',
        ]);

        try {
            // 5. Menyesuaikan proses update data
            $user->email = strtolower($validated['email']);
            $user->nama_lengkap = $validated['nama_lengkap'];
            $user->jabatan = $validated['jabatan'];
            $user->peran_id = $validated['peran_id'];
            $user->status = $validated['status'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']); // Menggunakan 'password'
            }

            $user->save();

            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            // Log::error('Gagal update user: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui user.');
        }
    }

    /**
     * Hapus user (Soft Delete).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->check() && auth()->id() == $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        try {
            // 6. Proses delete sekarang akan melakukan Soft Delete secara otomatis
            $user->delete(); 
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            // Log::error('Gagal hapus user: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus user.');
        }
    }
}
