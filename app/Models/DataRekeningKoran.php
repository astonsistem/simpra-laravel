<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataRekeningKoran extends Model
{
    protected $table = "data_rekening_koran";
    protected $primaryKey = "rc_id";
    public $timestamps = false;

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
        'pad_id',
        'pad_tgl',
    ];

    protected $appends = [
        'terklarifikasi',
        'belum_terklarifikasi',
    ];

    // event booted
    protected static function booted()
    {
        static::created(function ($model) {
            if (is_null($model->sync_at)) {
                $model->sync_at = Carbon::now();
                $model->save();
            }
        });
    }

    public function terklarifikasi(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->klarif_layanan + (int)$this->klarif_lain,
        );
    }

    public function belumTerklarifikasi(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->kredit - ((int) $this->klarif_layanan + (int)$this->klarif_lain),
        );
    }

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

    public static function sumBuktiSetor($currentMonth)
    {
        $result = self::query()
            ->selectRaw('SUM(COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0)) as total')
            ->whereRaw('EXTRACT(MONTH FROM tgl_rc) <= ?', $currentMonth)
            ->first();

        return $result?->total ?? 0;
    }

    public static function sumBuktiSetorCurrent($currentMonth)
    {
        $result = self::query()
            ->selectRaw('SUM(COALESCE(klarif_layanan, 0) + COALESCE(klarif_lain, 0)) as total')
            ->whereRaw('EXTRACT(MONTH FROM tgl_rc) = ?', $currentMonth)
            ->first();

        return $result?->total ?? 0;
    }

    public static function countBuktiSetor($currentMonth)
    {
        $result = self::query()
            ->selectRaw('COUNT(*) as total')
            ->whereRaw('EXTRACT(MONTH FROM tgl_rc) <= ?', $currentMonth)
            ->where(function ($query) {
                $query->where('klarif_layanan', '<>', 0)
                    ->orWhere('klarif_lain', '<>', 0);
            })
            ->first();

        return $result?->total ?? 0;
    }

    public static function countBuktiSetorCurrent($currentMonth)
    {
        $result = self::query()
            ->selectRaw('COUNT(*) as total')
            ->whereRaw('EXTRACT(MONTH FROM tgl_rc) = ?', $currentMonth)
            ->where(function ($query) {
                $query->where('klarif_layanan', '<>', 0)
                    ->orWhere('klarif_lain', '<>', 0);
            })
            ->first();

        return $result?->total ?? 0;
    }

    public static function sumDebit(?string $currentMonth = null, ?string $bank = null)
    {
        $query = self::query();
        $query->selectRaw('SUM(debit) as total');

        if (!is_null($currentMonth)) {
            $query->whereRaw('EXTRACT(MONTH FROM tgl_rc) = ?', $currentMonth);
        }

        if (!is_null($bank)) {
            $bank = strtolower($bank);
            $query->where('bank', $bank);
        }

        $result = $query->first();

        return $result?->total ?? 0;
    }

    public static function sumKredit(?string $currentMonth = null, ?string $bank = null)
    {
        $query = self::query();
        $query->selectRaw('SUM(kredit) as total');

        if (!is_null($currentMonth)) {
            $query->whereRaw('EXTRACT(MONTH FROM tgl_rc) = ?', $currentMonth);
        }

        if (!is_null($bank)) {
            $bank = strtolower($bank);
            $query->where('bank', $bank);
        }

        $result = $query->first();

        return $result?->total ?? 0;
    }

    public function scopeWithKwitansiSummary($query)
    {
        $sub = DB::table('data_penerimaan_layanan')
            ->select(
                'rc_id',
                DB::raw('COUNT(*) as vol'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(admin_kredit) as admin_kredit'),
                DB::raw('SUM(admin_debit) as admin_debit'),
                DB::raw('SUM(selisih) as selisih')
            )
            ->groupBy('rc_id')
            ->unionAll(
                DB::table('data_penerimaan_lain')
                    ->select(
                        'rc_id',
                        DB::raw('COUNT(*) as vol'),
                        DB::raw('SUM(total) as total'),
                        DB::raw('SUM(admin_kredit) as admin_kredit'),
                        DB::raw('SUM(admin_debit) as admin_debit'),
                        DB::raw('SUM(selisih) as selisih')
                    )
                    ->groupBy('rc_id')
            );

        return $query->select(
                'data_rekening_koran.rc_id',
                'data_rekening_koran.tgl_rc',
                'data_rekening_koran.bank',
                'data_rekening_koran.uraian',
                'data_rekening_koran.kredit',
                DB::raw('SUM(kw.vol) as volume'),
                DB::raw('SUM(kw.total) as total_kwitansi'),
                DB::raw('SUM(kw.admin_kredit) as admin_kredit'),
                DB::raw('SUM(kw.admin_debit) as admin_debit'),
                DB::raw('SUM(kw.selisih) as selisih')
            )
            ->joinSub($sub, 'kw', fn($join) => $join->on('data_rekening_koran.rc_id', '=', 'kw.rc_id'))
            ->groupBy(
                'data_rekening_koran.rc_id',
                'data_rekening_koran.tgl_rc',
                'data_rekening_koran.bank',
                'data_rekening_koran.uraian',
                'data_rekening_koran.kredit'
            );
    }
    // Accessor for total_setor
    public function getTotalSetorAttribute()
    {
        return $this->total_kwitansi - $this->admin_kredit + $this->selisih;
    }

    // Relationships
    public function akunData()
    {
        return $this->belongsTo(MasterAkun::class, 'akun_id', 'akun_id');
    }

    public function akunlsData()
    {
        return $this->belongsTo(MasterAkun::class, 'akunls_id', 'akun_id');
    }

    public function rekeningDpa()
    {
        return $this->belongsTo(MasterRekeningView::class, 'rek_id', 'rek_id');
    }
}
