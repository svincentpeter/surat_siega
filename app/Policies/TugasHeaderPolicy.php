<?php

namespace App\Policies;

use App\Models\TugasHeader;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TugasHeaderPolicy
{
    use HandlesAuthorization;

    /**
     * Admin TU boleh semua.
     */
    public function before(User $user, $ability)
    {
        if ($user->peran_id === 1) {
            return true;
        }
    }

    /**
     * Lihat daftar "Semua Surat Tugas" (hanya Admin TU).
     * (Tetap kita tulis eksplisit meski sudah di-handle di before()).
     */
    public function viewAny(User $user): bool
    {
        return $user->peran_id === 1;
    }

    /**
     * Lihat detail sebuah surat:
     * Pembuat, Penandatangan, atau Penerima internal.
     */
    public function view(User $user, TugasHeader $tugasHeader): bool
    {
        $isPenerima = $tugasHeader->penerima()
            ->where('pengguna_id', $user->id)
            ->exists();

        return $user->id === $tugasHeader->dibuat_oleh
            || $user->id === $tugasHeader->penandatangan
            || $isPenerima;
    }

    /**
     * Membuat surat (Admin TU).
     */
    public function create(User $user): bool
    {
        return $user->peran_id === 1;
    }

    /**
     * Mengedit surat:
     * - Pembuat saat status 'draft', ATAU
     * - Penandatangan saat status 'pending' (revisi sebelum tanda tangan).
     * Tidak boleh jika nomor sudah locked.
     */
    public function update(User $user, TugasHeader $tugasHeader): bool
    {
        if ($tugasHeader->nomor_status === 'locked') {
            return false;
        }

        $isPembuatDraft = $user->id === $tugasHeader->dibuat_oleh
            && $tugasHeader->status_surat === 'draft';

        $isPenandatanganPending = $user->id === $tugasHeader->penandatangan
            && $tugasHeader->status_surat === 'pending';

        return $isPembuatDraft || $isPenandatanganPending;
    }

    /**
     * Menghapus surat: hanya pembuat & masih draft & belum locked.
     */
    public function delete(User $user, TugasHeader $tugasHeader): bool
    {
        return $tugasHeader->nomor_status !== 'locked'
            && $user->id === $tugasHeader->dibuat_oleh
            && $tugasHeader->status_surat === 'draft';
    }

    /**
     * Mengajukan (submit) surat:
     * hanya pembuat & status masih draft.
     */
    public function submit(User $user, TugasHeader $tugasHeader): bool
    {
        return $user->id === $tugasHeader->dibuat_oleh
            && $tugasHeader->status_surat === 'draft';
    }

    /**
     * Menyetujui (approve) surat:
     * hanya penandatangan & status pending.
     */
    public function approve(User $user, TugasHeader $tugasHeader): bool
    {
        return $user->id === $tugasHeader->penandatangan
            && $tugasHeader->status_surat === 'pending';
    }

    /**
     * Menambahkan penerima:
     * hanya pembuat saat draft dan belum locked.
     */
    public function addRecipient(User $user, TugasHeader $tugasHeader): bool
    {
        return $tugasHeader->nomor_status !== 'locked'
            && $tugasHeader->status_surat === 'draft'
            && $user->id === $tugasHeader->dibuat_oleh;
    }

    /**
     * Lihat halaman daftar approval:
     * Dekan (2) & Wakil Dekan (3).
     */
    public function approveList(User $user): bool
    {
        return in_array($user->peran_id, [2, 3], true);
    }
}
