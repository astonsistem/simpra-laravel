<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DataRekeningKoran extends Model
{
    protected $table = "data_rekening_koran";
    protected $primaryKey = "rc_id";

    protected $fillable = [
        'rc_id',
        'tgl',
        'ket',
        'no_rc',
        'tgl_rc',
        'rek_dari',
        'nama_dari',
        'akun_id',
        'akunls_id',
        'uraian',
        'bku_id',
        'no_bku',
        'ket_bku',
        'klarif_lain',
        'klarif_layanan',
        'debit',
        'kredit',
        'klarif_admin',
        'kunci',
        'pb',
        'mutasi',
        'bank',
        'pb_dari',
        'file_upload',
        'sync_at',
        'status',
        'is_web_change',
    ];

    public static function getTanggalRc(?int $rcId = null, ?string $tglRc = null, ?string $bankTujuan = null, int $skip = 0, int $limit = 1000)
    {
        $query = self::query();

        if (!is_null($rcId)) {
            return $query->where('rc_id', $rcId)->get();
        }

        if (!is_null($tglRc)) {
            $startDate = Carbon::parse($tglRc);
            $endDate = $startDate->copy()->addDays(5);
            $query->whereBetween('tgl_rc', [$startDate, $endDate]);
        }

        if (!is_null($bankTujuan)) {
            $bankTujuan = strtolower($bankTujuan);

            if ($bankTujuan === 'tunai') {
                $query->where(function ($q) {
                    $q->where('bank', 'ilike', 'jatim')->orWhereNull('bank');
                });
            } else {
                $query->whereNotNull('bank')
                    ->where('bank', 'not ilike', 'tunai')
                    ->where('bank', 'ilike', $bankTujuan);
            }
        }

        $query->where('kredit', '>', 0)
            ->orderBy('tgl_rc')
            ->orderBy('no_rc');

        return $query->skip($skip)->take($limit)->get();
    }

    public static function getTanggalRcFilter(?string $tglRc = null, ?string $bankTujuan = null, int $skip = 0, int $limit = 1000)
    {
        $query = self::query();

        if (!is_null($tglRc)) {
            $startDate = Carbon::parse($tglRc);
            $endDate = $startDate->copy()->addDays(5);
            $query->whereBetween('tgl_rc', [$startDate, $endDate]);
        }

        if (!is_null($bankTujuan)) {
            $bankTujuan = strtolower($bankTujuan);

            if ($bankTujuan === 'tunai') {
                $query->where(function ($q) {
                    $q->where('bank', 'ILIKE', 'jatim')->orWhereNull('bank');
                });
            } else {
                $query->whereNotNull('bank')
                    ->where('bank', 'NOT ILIKE', 'tunai')
                    ->where('bank', 'ILIKE', $bankTujuan);
            }
        }

        $query->where('kredit', '>', 0)
            ->whereRaw('kredit > COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0)')
            ->orderBy('tgl_rc')
            ->orderBy('no_rc');

        return $query->skip($skip)->take($limit)->get();
    }

    public static function getTanggalRcFilterUraian(?string $tglRc = null, ?string $bankTujuan = null, ?string $uraian = null, int $skip = 0, int $limit = 1000)
    {
        $query = self::query();

        if (!is_null($tglRc)) {
            $startDate = Carbon::parse($tglRc);
            $endDate = $startDate->copy()->addDays(5);
            $query->whereBetween('tgl_rc', [$startDate, $endDate]);
        }

        if (!is_null($bankTujuan)) {
            $bankTujuan = strtolower($bankTujuan);

            if ($bankTujuan === 'tunai') {
                $query->where(function ($q) {
                    $q->where('bank', 'ilike', 'jatim')->orWhereNull('bank');
                });
            } else {
                $query->whereNotNull('bank')
                    ->where('bank', 'not ilike', 'tunai')
                    ->where('bank', 'ilike', $bankTujuan);
            }
        }

        if (!is_null($uraian)) {
            $query->where('uraian', 'ILIKE', $uraian);
        }

        $query->where('kredit', '>', 0)
            ->orderBy('tgl_rc')
            ->orderBy('no_rc');

        return $query->skip($skip)->take($limit)->get();
    }

    public static function getTanggalRcFilterJumlah(?string $tglRc = null, ?string $bankTujuan = null, ?float $jumlah = null, int $skip = 0, int $limit = 1000)
    {
        $query = self::query();

        if (!is_null($tglRc)) {
            $startDate = Carbon::parse($tglRc);
            $endDate = $startDate->copy()->addDays(5);
            $query->whereBetween('tgl_rc', [$startDate, $endDate]);
        }

        if (!is_null($bankTujuan)) {
            $bankTujuan = strtolower($bankTujuan);

            if ($bankTujuan === 'tunai') {
                $query->where(function ($q) {
                    $q->where('bank', 'ilike', 'jatim')->orWhereNull('bank');
                });
            } else {
                $query->whereNotNull('bank')
                    ->where('bank', 'not ilike', 'tunai')
                    ->where('bank', 'ilike', $bankTujuan);
            }
        }

        if (!is_null($jumlah)) {
            $query->where('kredit', $jumlah);
        }

        $query->where('kredit', '>', 0)
            ->orderBy('tgl_rc')
            ->orderBy('no_rc');

        return $query->skip($skip)->take($limit)->get();
    }
}
