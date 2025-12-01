<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RekeningKoranUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'tgl_rc' => normalize_date($this->tgl_rc),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'tgl_rc' => 'required|date',
            'no_rc' => 'required|string|max:255',
            'akunls_id' => 'required|integer|exists:master_akun,akun_id',
            'klarif_layanan' => 'required|numeric|min:0',
            'klarif_lain' => 'required|numeric|min:0',
            'rek_id' => 'nullable|exists:master_rekening_v,rek_id',
            'pb_dari' => 'nullable|string|max:255',
            'mutasi' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tgl_rc.required' => 'Tanggal RC harus diisi',
            'tgl_rc.date' => 'Format tanggal RC tidak valid',
            'no_rc.required' => 'No. RC harus diisi',
            'no_rc.string' => 'No. RC harus berupa teks',
            'no_rc.max' => 'No. RC maksimal 255 karakter',
            'akunls_id.required' => 'Klarifikasi Langsung harus dipilih',
            'akunls_id.integer' => 'Klarifikasi Langsung tidak valid',
            'akunls_id.exists' => 'Akun klarifikasi tidak ditemukan',
            'klarif_layanan.required' => 'Klarifikasi Layanan harus diisi',
            'klarif_layanan.numeric' => 'Klarifikasi Layanan harus berupa angka',
            'klarif_layanan.min' => 'Klarifikasi Layanan minimal 0',
            'klarif_lain.required' => 'Klarifikasi Lain harus diisi',
            'klarif_lain.numeric' => 'Klarifikasi Lain harus berupa angka',
            'klarif_lain.min' => 'Klarifikasi Lain minimal 0',
            'rek_id.exists' => 'Rekening DPA tidak ditemukan',
        ];
    }
}
