<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StatistikService
{
    /**
     * Get saldo kas per hari ini
     */
    public function getSaldoKas()
    {
        return DB::table('data_rekening_koran')
            ->whereDate('tgl_rc', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_rc) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->selectRaw('SUM(kredit) - SUM(debit) as saldo')
            ->value('saldo');
    }

    /**
     * Get penerimaan hari ini
     */
    public function getPenerimaan()
    {
        try {
            return DB::scalar('SELECT get_penerimaan()');
        }catch (\Throwable $e) {
            \Log::error('Error getPenerimaan: '. $e->getMessage());
            return 0;
        }
    }

    /**
     * Get potensi penerimaan hari ini
     */
    public function getPotensiPenerimaan()
    {
        try {
            return DB::scalar('SELECT get_potensi_penerimaan()');
        }catch (\Throwable $e) {
            \Log::error('Error getPotensiPenerimaan: '. $e->getMessage());
            return 0;
        }
    }

    /**
     * Get realisasi pendapatan jumlah netto
     */
    public function getRealisasiPendapatanNetto()
    {
        try {
            return DB::scalar('SELECT get_realisasi_pendapatan_netto()');
        }catch (\Throwable $e) {
            \Log::error('Error getRealisasiPendapatanNetto: '. $e->getMessage());
            return 0;
        }
    }
    /**
     * Get realisasi pendapatan jumlah
     */
    public function getRealisasiPendapatanJumlah()
    {
        return DB::table('data_anggaran_pendapatan')
            ->where('tahun', DB::raw('EXTRACT(YEAR FROM CURRENT_DATE)'))
            ->sum('jumlah');
    }

    /**
     * Get monev penerimaan lainnya
     */
    public function getMonevPenerimaanLainnya()
    {
        return DB::table('data_penerimaan_lain')
            ->whereDate('tgl_bayar', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_bayar) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->whereNotNull('rc_id')
            ->count();
    }
    /**
     * Get monev penerimaan lainnya all
     */
    public function getMonevPenerimaanLainnyaAll()
    {
        return DB::table('data_penerimaan_lain')
            ->whereDate('tgl_bayar', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_bayar) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->count();
    }
    /**
     * Get monev penerimaan layanan
     */
    public function getMonevPenerimaanLayanan()
    {
        return DB::table('data_penerimaan_layanan')
            ->whereDate('tgl_buktibayar', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_buktibayar) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->whereNotNull('rc_id')
            ->count();
    }
    /**
     * Get monev penerimaan layanan all
     */
    public function getMonevPenerimaanLayananAll()
    {
        return DB::table('data_penerimaan_layanan')
            ->whereDate('tgl_buktibayar', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_buktibayar) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->count();
    }
    /**
     * Get monev rekening koran
     */
    public function getMonevRekeningKoran()
    {
        return DB::table('data_rekening_koran')
            ->whereDate('tgl_rc', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_rc) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->where(function ($q) {
                $q->whereNotNull('akun_id')
                ->orWhereNotNull('akunls_id');
            })
            ->where('kredit', '>', 0)
            ->count();
    }
    /**
     * Get monev rekening koran all
     */
    public function getMonevRekeningKoranAll()
    {
        return DB::table('data_rekening_koran')
            ->whereDate('tgl_rc', '<=', DB::raw('CURRENT_DATE'))
            ->whereRaw('EXTRACT(YEAR FROM tgl_rc) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->where('kredit', '>', 0)
            ->count();
    }

    /**
     * Get komposisi target pendapatan layanan
     */
    public function getKomposisiTargetPendapatanLayanan()
    {
        return DB::table('data_anggaran_pendapatan')
            ->where('rek_id', '410201010005')
            ->where('tahun', DB::raw('EXTRACT(YEAR FROM CURRENT_DATE)'))
            ->sum('jumlah');
    }
    /**
     * Get komposisi target pendapatan selain layanan
     */
    public function getKomposisiTargetPendapatanNotLayanan()
    {
        return DB::table('data_anggaran_pendapatan')
            ->where('rek_id', '<>', '410201010005')
            ->where('tahun', DB::raw('EXTRACT(YEAR FROM CURRENT_DATE)'))
            ->sum('jumlah');
    }

    /**
     * Get jumlah personil penerimaan
     */
    public function getJumlahPersonil()
    {
        return DB::table('users')
            ->count();
    }
    /**
     * Get jumlah penjamin pasien
     */
    public function getJumlahPenjamin()
    {
        return DB::table('master_penjamin')
            ->count();
    }
    /**
     * Get jumlah loket kasir
     */
    public function getJumlahLoket()
    {
        return DB::table('master_loket')
            ->count();
    }
    /**
     * Get jumlah instalasi
     */
    public function getJumlahInstalasi()
    {
        return DB::table('master_instalasi')
            ->count();
    }

    /**
     * Get pendapatan selain retribusi layanan
     */
    public function getPendapatanSelainRetribusi()
    {
        $result = DB::table('dashboard_pendapatannonlayanan_v')
            ->selectRaw("parkir, sewa, airlistrik, kerjasama, diklat, litbang, lainlain")
            ->first();
            
        return array_values((array) $result);
    }

    /**
     * Get pendapatan, dokumen klaim dan penerimaan lainnya
     */
    public function getpendapatanDokumenPenerimaan()
    {
        $result = DB::select("
            SELECT status, januari, februari, maret, april, mei, juni, juli, agustus, september, oktober, november, desember
            FROM dashboard_trendpendapatanbulanan_v
        ");

        // ubah ke bentuk array seperti [10000,20000,...]
        $data = collect($result)->mapWithKeys(function ($row) {
            return [
                $row->status => collect((array) $row)
                    ->except('status')
                    ->map(fn($val) => (int) $val)
                    ->values()
                    ->toArray()
            ];
        });

        return $data;
    }
}
