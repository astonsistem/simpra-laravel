<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\CaraBayar;
use App\Models\SyncPendapatanPelayanan;
use App\Models\Instalasi;
use App\Models\Penjamin;

class StatistikService
{
    /**
     * Get sum of pendapatan
     */
    public function getSumPendapatan()
    {
        return DB::table('pendapatan')->sum('amount');
    }

    /**
     * Get count of pasien
     */
    public function getCountPasien()
    {
        return SyncPendapatanPelayanan::getCountPasienThisYear();
    }

    /**
     * Get top instalasi
     */
    public function getTopInstalasi()
    {
        return DB::table('instalasi')
            ->select('nama', DB::raw('COUNT(*) as total'))
            ->groupBy('nama')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get sum of all potensi
     */
    public function getSumAllPotensi()
    {
        return DB::table('potensi')->sum('amount');
    }

    /**
     * Get sum potensi pelayanan
     */
    public function getSumPotensiPelayanan()
    {
        return DB::table('potensi')
            ->where('jenis', 'pelayanan')
            ->sum('amount');
    }

    /**
     * Get sum potensi lain
     */
    public function getSumPotensiLain()
    {
        return DB::table('potensi')
            ->where('jenis', 'lain')
            ->sum('amount');
    }

    /**
     * Count potensi pelayanans
     */
    public function countPotensiPelayanans()
    {
        return DB::table('potensi')
            ->where('jenis', 'pelayanan')
            ->count();
    }

    /**
     * Get sum pendapatan chart data
     */
    public function getSumPendapatanChart()
    {
        return DB::table('pendapatan')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get total anggaran
     */
    public function getTotalAnggaran()
    {
        return DB::table('anggaran')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get pendapatan pelayanan penjamin
     */
    public function getPendapatanPelayananPenjamin()
    {
        return DB::table('pendapatan')
            ->join('penjamin', 'pendapatan.penjamin_id', '=', 'penjamin.id')
            ->select('penjamin.nama', DB::raw('SUM(pendapatan.amount) as total'))
            ->groupBy('penjamin.nama')
            ->get();
    }

    /**
     * Get sum penerimaan lain tahun ini
     */
    public function getSumPenerimaanLainTahunini()
    {
        return DB::table('penerimaan')
            ->where('jenis', 'lain')
            ->whereYear('created_at', date('Y'))
            ->sum('amount');
    }

    /**
     * Get sum total by payment method
     */
    public function getSumTotalByPaymentMethod(string $method)
    {
        return DB::table('pembayaran')
            ->where('metode', $method)
            ->sum('amount');
    }

    /**
     * Get count total by payment method
     */
    public function getCountTotalByPaymentMethod(string $method)
    {
        return DB::table('pembayaran')
            ->where('metode', $method)
            ->count();
    }

    /**
     * Get count carabayar
     */
    public function getCountCarabayar()
    {
        return CaraBayar::count();
    }

    /**
     * Get count penjamin
     */
    public function getCountPenjamin()
    {
        return Penjamin::count();
    }

    /**
     * Get count instalasi
     */
    public function getCountInstalasi()
    {
        return Instalasi::count();
    }
}
