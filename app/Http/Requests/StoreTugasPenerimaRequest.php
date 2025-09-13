<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTugasPenerimaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tugas_id'         => ['required','integer','exists:tugas_header,id'],
            'pengguna_id'      => ['nullable','integer','exists:pengguna,id','prohibited_with:nama_penerima'],
            'nama_penerima'    => ['nullable','string','max:255','required_without:pengguna_id'],
            'jabatan_penerima' => ['nullable','string','max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'pengguna_id.prohibited_with'   => 'Jika memilih pengguna sistem, kolom Nama (manual) harus dikosongkan.',
            'nama_penerima.required_without'=> 'Isi nama penerima jika tidak memilih pengguna sistem.',
        ];
    }
}
