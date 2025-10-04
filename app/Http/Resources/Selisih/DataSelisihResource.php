<?php

namespace App\Http\Resources\Selisih;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataSelisihResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'admin_debit'        => (int) $this->admin_debit ?? 0,          // 8. Admin QRIS
            'admin_kredit'       => (int) $this->admin_kredit ?? 0,         // 7. Admin EDC
            'bank_tujuan'        => (string) strtoupper($this->bank_tujuan),            // 5. Rekening Bank
            'cara_pembayaran'    => (string) $this->cara_pembayaran,        // 4. Cara Pembayaran
            'id'                 => (string) $this->id,                     // 1. ID
            'jenis'              => (string) $this->jenis,                  // 3. Jenis
            'jumlah'             => $this->jumlah ? (int) $this->jumlah : $this->tersetor,                    // 6. Jumlah Setor
            'jumlah_netto'       => (int) $this->jumlah_netto,              // 9. Jumlah Netto
            'kasir_id'           => (int) $this->kasir_id,
            'kasir_nama'         => (string) $this->kasir_nama,             // 17. Nama Kasir
            'klasifikasi'        => (string) $this->klasifikasi,            // 13. Klasifikasi
            'loket_id'           => (int) $this->loket_id,
            'loket_nama'         => (string) $this->loket_nama,             // 18. Loket Kasir
            'nilai'              => (int) $this->nilai,                     // 16. Selisih Kurang
            'no_bukti'           => (string) $this->no_bukti,               // 15. No. Bukti
            'no_buktibayar'      => (string) $this->no_bukti,               // 15. No. Bukti
            'penyetor'           => (string) $this->penyetor,               // 10. Penyetor
            'rc_id'              => (string) $this->rc_id,
            'rekening_dpa'       => $this->rekeningDpa ? [                  // 11. Rekening DPA
                'rek_id'   => (string) $this->rekeningDpa->rek_id,
                'rek_nama' => (string) $this->rekeningDpa->rek_nama,
            ] : null,
            'sumber_id'          => (string) $this->id,
            'sumber_transaksi'   => (string) $this->sumber_transakasi,      // 12. Sumber Transaksi
            'tgl_bukti'          => (string) $this->tgl_bukti,              // 14. Tgl. Bukti
            'tgl_buktibayar'     => (string) $this->tgl_bukti,              // 14. Tgl. Bukti
            'tgl_setor'          => (string) $this->tgl_setor,              // 2. Tgl. Setor
            'tersetor'           => (int) $this->tersetor,                 // 19. Tersetor
            'total_jumlah_netto' => (int) $this->jumlah > 0 ? $this->jumlah : (int) $this->total_jumlah_netto,
            'data_transaksi_exists'     => $this->data_transaksi_exists,
            'data_transaksi_id'         => $this->dataTransaksi?->id
        ];
    }
}
