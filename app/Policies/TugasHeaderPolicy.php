<?php

namespace App\Policies;

use App\Models\TugasHeader;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TugasHeaderPolicy
{
    use HandlesAuthorization;

    /**
     * Izinkan Admin Super (jika ada) untuk melakukan segalanya.
     * Catatan: Untuk sistem ini, kita definisikan Role 1 (admin_tu) BUKAN super admin.
     * Dia hanya admin untuk modul surat, jadi kita tidak pakai fungsi before() agar aturan spesifik berlaku.
     */
    // public function before(User $user, $ability)
    // {
    //     if ($user->isAdminSuper()) { // Jika Anda punya role super admin sejati
    //         return true;
    //     }
    // }

    /**
     * Aturan: Siapa yang boleh MELIHAT SEMUA surat di halaman 'index' (surat_tugas.all).
     * Hanya admin_tu (Role 1).
     */
    public function viewAny(User $user)
    {
        return $user->peran_id === 1; // Hanya admin_tu
    }

    /**
     * Aturan: Siapa yang boleh MELIHAT DETAIL satu surat.
     * 1. Admin (Role 1).
     * 2. Pembuat suratnya.
     * 3. Penandatangan suratnya.
     * 4. Penerima suratnya.
     */
    public function view(User $user, TugasHeader $tugas)
    {
        if ($user->peran_id === 1) {
            return true; // Admin boleh lihat semua detail
        }

        // Cek jika user adalah penerima
        $isRecipient = $tugas->penerima()->where('pengguna_id', $user->id)->exists();

        return $user->id === $tugas->dibuat_oleh || // Dia pembuat
               $user->id === $tugas->penandatangan || // Dia penandatangan
               $isRecipient; // Dia penerima
    }

    /**
     * Aturan: Siapa yang boleh MEMBUAT surat.
     * Hanya admin_tu (Role 1).
     */
    public function create(User $user)
    {
        return $user->peran_id === 1;
    }

    /**
     * Aturan: Siapa yang boleh UPDATE (Edit / Koreksi) surat.
     * KASUS 1: Admin (Role 1) boleh edit, TAPI HANYA jika surat itu miliknya DAN statusnya masih 'draft'.
     * KASUS 2: Dekan/Wakil Dekan (Roles 2, 3) boleh 'koreksi' (edit), TAPI HANYA jika surat itu ditujukan padanya DAN statusnya 'pending'.
     */
    public function update(User $user, TugasHeader $tugas)
    {
        // KASUS 1: Admin edit draft miliknya
        $adminEditDraft = ($user->peran_id === 1 &&
                           $user->id === $tugas->dibuat_oleh &&
                           $tugas->status_surat === 'draft');

        // KASUS 2: Approver (Dekan/WD) melakukan koreksi pada surat 'pending'
        $approverCorrectsPending = (in_array($user->peran_id, [2, 3]) &&
                                    $user->id === $tugas->penandatangan &&
                                    $tugas->status_surat === 'pending');

        return $adminEditDraft || $approverCorrectsPending;
    }

    /**
     * Aturan: Siapa yang boleh MENGHAPUS surat.
     * Hanya Admin (Role 1), HANYA surat miliknya, dan HANYA saat status 'draft'.
     * Selain itu, TIDAK BOLEH ada yang menghapus surat.
     */
    public function delete(User $user, TugasHeader $tugas)
    {
        return $user->peran_id === 1 &&
               $user->id === $tugas->dibuat_oleh &&
               $tugas->status_surat === 'draft';
    }

    /**
     * Aturan: Siapa yang boleh MENYETUJUI (APPROVE) surat.
     * HANYA Dekan/Wakil Dekan (Roles 2, 3), HANYA surat yang ditujukan padanya, dan HANYA saat status 'pending'.
     * Ini adalah aturan yang akan menyembunyikan tombol approve dari Role 1.
     */
    public function approve(User $user, TugasHeader $tugas)
    {
        return in_array($user->peran_id, [2, 3]) &&       // Peran adalah Dekan atau WD
               $user->id === $tugas->penandatangan &&      // Dia adalah penandatangan yang dituju
               $tugas->status_surat === 'pending';       // Surat sedang menunggu persetujuan
    }

    /**
     * Aturan: Siapa yang boleh menambah penerima (fitur tambahan).
     * Hanya pembuat surat dan hanya saat draft.
     */
    public function addRecipient(User $user, TugasHeader $tugas)
    {
        return $user->id === $tugas->dibuat_oleh && $tugas->status_surat === 'draft';
    }


    // --- (Abaikan fungsi restore/forceDelete jika tidak Anda gunakan) ---

    public function restore(User $user, TugasHeader $tugas)
    {
        return false; // Kita tidak pakai soft delete di sini
    }

    public function forceDelete(User $user, TugasHeader $tugas)
    {
        return false; // Kita tidak pakai soft delete di sini
    }
}