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
        $row = DB::selectOne("
                SELECT 
                    COALESCE((SELECT SUM(total) 
                            FROM data_penerimaan_layanan 
                            WHERE tgl_buktibayar = CURRENT_DATE), 0)
                +
                    COALESCE((SELECT SUM(total) 
                            FROM data_penerimaan_lain 
                            WHERE tgl_bayar = CURRENT_DATE), 0)
                +
                    COALESCE((SELECT SUM(rk.kredit) 
                            FROM data_rekening_koran rk
                            JOIN master_akun ma ON rk.akunls_id = ma.akun_id
                            WHERE ma.rek_id LIKE '4%' 
                                AND rk.tgl_rc = CURRENT_DATE), 0) 
                AS total
            ");

        return $row->total;
    }

    /**
     * Get potensi penerimaan hari ini
     */
    public function getPotensiPenerimaan()
    {
        return DB::table(DB::raw('(
                SELECT pl.id, pl.total - COALESCE(SUM(pln.total),0) as sisa
                FROM data_potensi_pelayanan pl
                LEFT JOIN data_penerimaan_lain pln ON pl.id = pln.piutang_id
                WHERE pl.tgl_dokumen <= CURRENT_DATE
                GROUP BY pl.id, pl.total
            ) as sub'))
            ->selectRaw('SUM(sub.sisa) as total_sisa')
            ->value('total_sisa');
    }

    /**
     * Get realisasi pendapatan jumlah netto
     */
    public function getRealisasiPendapatanNetto()
    {
        $row = DB::selectOne("
                SELECT 
                    COALESCE((SELECT SUM(jumlah_netto) 
                            FROM data_penerimaan_layanan 
                            WHERE rek_id LIKE '4%' 
                                AND EXTRACT(YEAR FROM tgl_buktibayar) = EXTRACT(YEAR FROM CURRENT_DATE)), 0)
                +
                    COALESCE((SELECT SUM(jumlah_netto) 
                            FROM data_penerimaan_lain 
                            WHERE rek_id LIKE '4%' 
                                AND EXTRACT(YEAR FROM tgl_bayar) = EXTRACT(YEAR FROM CURRENT_DATE)), 0)
                +
                    COALESCE((SELECT SUM(rk.kredit) 
                            FROM data_rekening_koran rk
                            JOIN master_akun ma ON rk.akunls_id = ma.akun_id
                            WHERE ma.rek_id LIKE '4%' 
                                AND EXTRACT(YEAR FROM rk.tgl_rc) = EXTRACT(YEAR FROM CURRENT_DATE)), 0)
                AS total
            ");

        return $row->total;
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
}
