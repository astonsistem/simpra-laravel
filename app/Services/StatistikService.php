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
        $result = DB::table('data_penerimaan_lain')
            ->selectRaw("
                SUM(CASE WHEN akun_id = 1020101 THEN total ELSE 0 END) AS parkir,
                SUM(CASE WHEN akun_id = 1020201 THEN total ELSE 0 END) AS sewa,
                SUM(CASE WHEN akun_id = 1020202 THEN total ELSE 0 END) AS airlistrik,
                SUM(CASE WHEN akun_id = 1020102 THEN total ELSE 0 END) AS kerjasama,
                SUM(CASE WHEN akun_id = 1020501 THEN total ELSE 0 END) AS diklat,
                SUM(CASE WHEN akun_id = 1020503 THEN total ELSE 0 END) AS litbang,
                SUM(CASE WHEN akun_id NOT IN (1020101,1020201,1020202,1020102,1020501,1020503) THEN total ELSE 0 END) AS lainlain
            ")
            ->whereRaw("EXTRACT(YEAR FROM tgl_bayar) = EXTRACT(YEAR FROM CURRENT_DATE)")
            ->where('akun_id', 'like', '102%')
            ->first();
            
        return array_values((array) $result);
    }

    /**
     * Get pendapatan, dokumen klaim dan penerimaan lainnya
     */
    public function getpendapatanDokumenPenerimaan()
    {
        $result = DB::select("
            SELECT 
                status,
                SUM(januari)   AS januari,
                SUM(februari)  AS februari,
                SUM(maret)     AS maret,
                SUM(april)     AS april,
                SUM(mei)       AS mei,
                SUM(juni)      AS juni,
                SUM(juli)      AS juli,
                SUM(agustus)   AS agustus,
                SUM(september) AS september,
                SUM(oktober)   AS oktober,
                SUM(november)  AS november,
                SUM(desember)  AS desember
            FROM (
                -- Pendapatan Pelayanan
                SELECT 
                    'Pendapatan Pelayanan' AS status,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 1  THEN SUM(total) ELSE 0 END) AS januari,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 2  THEN SUM(total) ELSE 0 END) AS februari,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 3  THEN SUM(total) ELSE 0 END) AS maret,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 4  THEN SUM(total) ELSE 0 END) AS april,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 5  THEN SUM(total) ELSE 0 END) AS mei,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 6  THEN SUM(total) ELSE 0 END) AS juni,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 7  THEN SUM(total) ELSE 0 END) AS juli,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 8  THEN SUM(total) ELSE 0 END) AS agustus,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 9  THEN SUM(total) ELSE 0 END) AS september,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 10 THEN SUM(total) ELSE 0 END) AS oktober,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 11 THEN SUM(total) ELSE 0 END) AS november,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_pelayanan) = 12 THEN SUM(total) ELSE 0 END) AS desember
                FROM data_pendapatan_pelayanan
                WHERE EXTRACT(YEAR FROM tgl_pelayanan) = EXTRACT(YEAR FROM CURRENT_DATE)
                GROUP BY EXTRACT(MONTH FROM tgl_pelayanan)

                UNION ALL

                -- Dokumen Klaim
                SELECT 
                    'Dokumen Klaim' AS status,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 1  THEN SUM(total) ELSE 0 END) AS januari,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 2  THEN SUM(total) ELSE 0 END) AS februari,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 3  THEN SUM(total) ELSE 0 END) AS maret,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 4  THEN SUM(total) ELSE 0 END) AS april,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 5  THEN SUM(total) ELSE 0 END) AS mei,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 6  THEN SUM(total) ELSE 0 END) AS juni,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 7  THEN SUM(total) ELSE 0 END) AS juli,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 8  THEN SUM(total) ELSE 0 END) AS agustus,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 9  THEN SUM(total) ELSE 0 END) AS september,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 10 THEN SUM(total) ELSE 0 END) AS oktober,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 11 THEN SUM(total) ELSE 0 END) AS november,
                    (CASE WHEN EXTRACT(MONTH FROM tgl_dokumen) = 12 THEN SUM(total) ELSE 0 END) AS desember
                FROM data_potensi_pelayanan
                WHERE EXTRACT(YEAR FROM tgl_dokumen) = EXTRACT(YEAR FROM CURRENT_DATE)
                GROUP BY EXTRACT(MONTH FROM tgl_dokumen)

                UNION ALL

                -- Penerimaan Layanan (gabungan)
                SELECT 
                    'Penerimaan Layanan' AS status,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 1  THEN total ELSE 0 END) AS januari,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 2  THEN total ELSE 0 END) AS februari,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 3  THEN total ELSE 0 END) AS maret,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 4  THEN total ELSE 0 END) AS april,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 5  THEN total ELSE 0 END) AS mei,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 6  THEN total ELSE 0 END) AS juni,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 7  THEN total ELSE 0 END) AS juli,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 8  THEN total ELSE 0 END) AS agustus,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 9  THEN total ELSE 0 END) AS september,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 10 THEN total ELSE 0 END) AS oktober,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 11 THEN total ELSE 0 END) AS november,
                    SUM(CASE WHEN EXTRACT(MONTH FROM tgl) = 12 THEN total ELSE 0 END) AS desember
                FROM (
                    SELECT tgl_buktibayar AS tgl, total
                    FROM data_penerimaan_layanan
                    WHERE EXTRACT(YEAR FROM tgl_buktibayar) = EXTRACT(YEAR FROM CURRENT_DATE)

                    UNION ALL

                    SELECT tgl_bayar AS tgl, total
                    FROM data_penerimaan_lain
                    WHERE EXTRACT(YEAR FROM tgl_bayar) = EXTRACT(YEAR FROM CURRENT_DATE)
                    AND akun_id::text LIKE '101%'
                ) pl
            ) gab
            GROUP BY status
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
