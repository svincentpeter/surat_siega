<?php

namespace App\Policies;

use App\Models\KeputusanHeader;
use App\Models\User;

class KeputusanHeaderPolicy
{
    /**
     * Determine whether the user can view any KeputusanHeader models.
     * 
     * Hanya Admin TU (peran_id === 1) yang dapat melihat daftar semua SK.
     */
    public function viewAny(User $user): bool
    {
        return $user->peran_id === 1;
    }

    /**
     * Determine whether the user can view the specific KeputusanHeader model.
     *
     * Kita izinkan $keputusanHeader nullable agar Gate::allows('view', $user) 
     * yang hanya mengecek policy tanpa model tidak error.
     */
    public function view(User $user, KeputusanHeader $keputusanHeader = null): bool
    {
        // Jika tidak ada instance yang diberikan, kembalikan true agar Gate::allows('view') tidak error
        if (is_null($keputusanHeader)) {
            return true;
        }

        // Jika ada instance, cuma yang membuat SK (dibuat_oleh) dan Admin TU yang boleh melihat detail
        return $user->id === $keputusanHeader->dibuat_oleh
            || $user->peran_id === 1;
    }

    /**
     * Determine whether the user can create KeputusanHeader models.
     * 
     * Misalnya, hanya Admin TU yang boleh membuat SK. Sesuaikan jika perlu.
     */
    public function create(User $user): bool
    {
        return $user->peran_id === 1;
    }

    /**
     * Determine whether the user can update the KeputusanHeader model.
     * 
     * Hanya Admin TU yang membuat SK (status = draft) yang boleh mengedit SK tersebut.
     * Atau bisa ditambahkan logika lain misalnya jika SK masih draft.
     */
    public function update(User $user, KeputusanHeader $keputusanHeader): bool
    {
        // Cek bahwa yang sedang login adalah pembuat SK, SK masih berstatus 'draft', dan user adalah Admin TU
        return $user->peran_id === 1
            && $user->id === $keputusanHeader->dibuat_oleh
            && $keputusanHeader->status_surat === 'draft';
    }

    /**
     * Determine whether the user can delete the KeputusanHeader model.
     * 
     * Hanya Admin TU yang membuat SK dan statusnya masih 'draft' yang boleh menghapus.
     */
    public function delete(User $user, KeputusanHeader $keputusanHeader): bool
    {
        return $user->peran_id === 1
            && $user->id === $keputusanHeader->dibuat_oleh
            && $keputusanHeader->status_surat === 'draft';
    }

    /**
     * Determine whether the user can restore the KeputusanHeader model.
     */
    public function restore(User $user, KeputusanHeader $keputusanHeader): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the KeputusanHeader model.
     */
    public function forceDelete(User $user, KeputusanHeader $keputusanHeader): bool
    {
        return false;
    }
}
