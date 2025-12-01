<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPendapatanPelayanan extends Model
{
    protected $table = "data_pendapatan_pelayanan";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'pendaftaran_id',
        'tgl_pendaftaran',
        'pasien_id',
        'pasien_alamat',
        'jenis_tagihan',
        'tgl_krs',
        'tgl_pelayanan',
        'carabayar_id',
        'carabayar_nama',
        'penjamin_id',
        'penjamin_nama',
        'no_penjamin',
        'tgl_jaminan',
        'instalasi_id',
        'instalasi_nama',
        'kasir_id',
        'kasir_nama',
        'loket_id',
        'loket_nama',
        'layanan',
        'obatalkes',
        'penunjang',
        'total',
        'total_dijamin',
        'pendapatan',
        'piutang',
        'pdd',
        'pembayaranpelayanan_id',
        'status_id',
        'bulan_mrs',
        'bulan_krs',
        'bulan_pelayanan',
        'no_pendaftaran',
        'no_rekam_medik',
        'pasien_nama',
        'sync_at',
        'is_web_change ',
        'koreksi_sharing',
        'is_valid',
        'is_naikkelas',
        'is_penjaminlebih1',
        'biaya_admin',
        'hak_kelasrawat',
        'naik_kelasrawat',
        'status_fase1',
        'status_fase2',
        'status_fase3',
        'total_sharing',
        'obat_dijamin',
        'piutang_perorangan',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function syncFase2()
    {
        $penerimaanLayanan = DataPenerimaanLayanan::selectRaw("
                pendaftaran_id,
                SUM(CASE WHEN LOWER(klasifikasi) = 'pendapatan' THEN total ELSE 0 END) AS pendapatan,
                SUM(CASE WHEN LOWER(klasifikasi) = 'pdd' THEN total ELSE 0 END) AS pdd,
                SUM(CASE WHEN LOWER(klasifikasi) = 'piutang' THEN total ELSE 0 END) AS piutang,
                SUM(admin_kredit) AS bea_admin,
                SUM(total) AS total
            ")
            ->where('metode_bayar', 'ILIKE', '%langsung%')
            ->where('pendaftaran_id', $this->pendaftaran_id)
            ->groupBy('pendaftaran_id')
            ->first();

        $piutangUmumGl = DataPotensiPelayanan::whereIn('carabayar_id', [1, 15])
        ->where('pendaftaran_id', $this->pendaftaran_id)
        ->sum('total')?? 0;

        $piutangJaminan = RincianPotensiPelayanan::whereNotNull('piutang_id')
        ->where('pendaftaran_id', $this->pendaftaran_id)
        ->sum('total_klaim')?? 0;

        $piutangAll = $piutangUmumGl + $piutangJaminan; 
        
        if (!$penerimaanLayanan) {
            return response()->json([
                'message' => 'Data Penerimaan Layanan not found',
            ], 404);
        }

        if ($this->pendapatan != $penerimaanLayanan->pendapatan || $this->pdd != $penerimaanLayanan->pdd) {
            $this->status_fase2 = 'Koreksi Pendapatan';
        } elseif ($this->piutang != $piutangAll) {
            $this->status_fase2 = 'Koreksi Piutang';
        } elseif ($penerimaanLayanan->total != 0) {
            $this->status_fase2 = 'Bayar';
        } else {
            $this->status_fase2 = 'Valid';
        }

        $this->biaya_admin = $penerimaanLayanan->bea_admin;       
        $this->save();

        return true;
    }

    public static function syncFase2All($daysAgo = 5)
    {
        //$date = now()->subDays($daysAgo)->toDateString();
        //$records = self::whereDate('tgl_pelayanan', $date)->get();
        
        $records = self::whereNotIn('status_fase2', ['Valid', 'Bayar'])->orWhereNull('status_fase2')->get();
        $count = $records->count();
        $date = $records->max('tgl_pelayanan');

        $success = 0;
        $failed = 0;

        foreach ($records as $record) {
            try {
                $result = $record->syncFase2();

                if ($result === false) {
                    $failed++;
                } else {
                    $success++;
                }

            } catch (\Throwable $e) {
                $failed++;
                \Log::error('syncFase2All failed for record', [
                    'id' => $record->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => $failed === 0,
            'processed' => $success + $failed,
            'success_count' => $success,
            'failed_count' => $failed,
            'date' => $date,
        ];
    }
}
