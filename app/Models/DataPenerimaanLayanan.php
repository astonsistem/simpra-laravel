<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataPenerimaanLayanan extends Model
{
    protected $table = "data_penerimaan_layanan";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id ',
        'pendaftaran_id',
        'no_pendaftaran',
        'tgl_pendaftaran',
        'pasien_id',
        'no_rekam_medik',
        'pasien_nama',
        'pasien_alamat',
        'jenis_tagihan',
        'tgl_krs',
        'tgl_pelayanan',
        'carabayar_id',
        'carabayar_nama',
        'penjamin_id',
        'penjamin_nama',
        'instalasi_id',
        'instalasi_nama',
        'metode_bayar',
        'tandabuktibayar_id',
        'no_buktibayar',
        'tgl_buktibayar',
        'sep_id',
        'no_sep',
        'tgl_sep',
        'cara_pembayaran',
        'bank_tujuan',
        'admin_kredit',
        'admin_debit',
        'kartubank_pasien',
        'no_kartubank_pasien',
        'closingkasir_id',
        'tgl_closingkasir',
        'no_closingkasir',
        'kasir_id',
        'kasir_nama',
        'loket_id',
        'loket_nama',
        'guna_bayar',
        'total',
        'selisih',
        'jumlah_netto',
        'klasifikasi',
        'status_id',
        'bulan_mrs',
        'bulan_krs',
        'bulan_pelayanan',
        'akun_id',
        'rc_id',
        'is_web_change',
        'rek_id'
    ];

    protected $appends = [
        'tervalidasi',
        'total_jumlah_netto',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function rekeningKoran(): BelongsTo
    {
        return $this->belongsTo(DataRekeningKoran::class, 'rc_id', 'rc_id');
    }

    public function rekeningDpa(): BelongsTo
    {
        return $this->belongsTo(MasterRekeningView::class, 'rek_id', 'rek_id');
    }

    protected function tervalidasi(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->rc_id !== null || $this->rc_id !== ''
        );
    }

    protected function totalJumlahNetto(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->total ?? 0) - ($this->admin_kredit ?? 0) - ($this->admin_debit ?? 0) + ($this->selisih ?? 0)
        );
    }


    public static function sumTotal(): float
    {
        $result = self::query()
            ->selectRaw('SUM(COALESCE(total,0)) as total')
            ->first();

        return $result?->total ?? 0;
    }

    public static function sumTotalSetor(?string $noClosing = null, ?string $caraPembayaran = null): float
    {
        $query = self::query()
            ->selectRaw('SUM(COALESCE(total, 0) - COALESCE(admin_kredit, 0) + COALESCE(selisih, 0)) as total');

        if (!empty($noClosing)) {
            $query->where('no_closingkasir', $noClosing);
        }

        if (!empty($caraPembayaran)) {
            $query->where('cara_pembayaran', $caraPembayaran);
        }

        $result = $query->first();

        return $result?->total ?? 0;
    }

    public static function sumTotalByPaymentMethod(?string $metodeBayar = null): float
    {
        $currentYear = Carbon::now()->format('Y');
        $query = self::query()
            ->selectRaw('SUM(COALESCE(total, 0)) as total');

        if (!empty($metodeBayar)) {
            $query->where('metode_bayar', $metodeBayar);
        }

        $query->whereRaw('EXTRACT(YEAR FROM tgl_pelayanan) = ?', $currentYear);

        $result = $query->first();

        return $result?->total ?? 0;
    }

    public static function sumTotalByNotPaymentMethod(?string $metodeBayar = null): float
    {
        $query = self::query()
            ->selectRaw('SUM(COALESCE(total, 0)) as total');

        if (!empty($metodeBayar)) {
            $query->where('metode_bayar', '!=', $metodeBayar);
        }

        $result = $query->first();

        return $result?->total ?? 0;
    }

    public static function sumTotalByStatus(array $status)
    {
        if (empty($status)) {
            return (object)['total' => 0, 'count_total' => 0];
        }

        $result = self::query()
            ->selectRaw('SUM(COALESCE(total, 0)) as total, COUNT(total) as count_total')
            ->whereIn('status_id', $status)
            ->first();

        return (object)[
            'total'       => $result->total ?? 0,
            'count_total' => $result->count_total ?? 0,
        ];
    }

    public static function sumTotalByNotStatus(array $status, string $caraBayar)
    {
        if (empty($status) || empty($caraBayar)) {
            return (object)['total' => 0, 'count_total' => 0];
        }

        $result = self::query()
            ->selectRaw('SUM(COALESCE(total, 0)) as total, COUNT(total) as count_total')
            ->whereNotIn('status_id', $status)
            ->where('cara_pembayaran', $caraBayar)
            ->first();

        return (object)[
            'total'       => $result->total ?? 0,
            'count_total' => $result->count_total ?? 0,
        ];
    }

    public static function countTotal(): float
    {
        $result = self::query()
            ->count();

        return $result ?? 0;
    }

    public static function countTotalByPaymentMethod(?string $metodeBayar = null): float
    {
        $currentYear = Carbon::now()->format('Y');
        $query = self::query()
            ->selectRaw('COUNT(id) as total');

        if (!empty($metodeBayar)) {
            $query->where('metode_bayar', $metodeBayar);
        }

        $query->whereRaw('EXTRACT(YEAR FROM tgl_pelayanan) = ?', $currentYear);

        $result = $query->first();

        return $result?->total ?? 0;
    }

    public static function countTotalByNotPaymentMethod(?string $metodeBayar = null): float
    {
        $query = self::query()
            ->selectRaw('count(id) as total');

        if (!empty($metodeBayar)) {
            $query->where('metode_bayar', '!=', $metodeBayar);
        }

        $result = $query->first();

        return $result?->total ?? 0;
    }
}
