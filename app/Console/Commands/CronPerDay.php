<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\DataPenerimaanLain;
use App\Models\DataRekapRegister;
use App\Models\DataPendapatanPelayanan;
use App\Models\DataPenerimaanLayanan;
use App\Models\DokumenNonlayanan;
use App\Models\DataRekapHarianCaraBayar;
use Carbon\Carbon;

class CronPerDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:per-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this task every 1 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // DATE VARIABLE
            $yesterday      = Carbon::yesterday()->toDateString();
            $three_days_ago = Carbon::now()->subDays(3)->toDateString();
            $twenty_eight_days_ago = Carbon::now()->subDays(28)->toDateString();

            // 1. INSERT DATA REKAPREGISTER (Rekapitulasi Register Pasien Harian (H+1 jam 01:00 WIB))
            // Get data rekap register yesterday
            $rekapRegisterSiesta = (new DataRekapRegister)->setTable('simpra_rekapregister_ft')->whereDate('tgl', $yesterday)->get();
            foreach ($rekapRegisterSiesta as $rr) {
                // Check if data already exist in table data_rekap_register (based on tgl)
                $exist = DataRekapRegister::where('tgl', $rr->tgl)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataRekapRegister::create($rr->toArray());
                }
            }

            // 2. INSERT DATA PENDAPATAN JALAN TO PENDAPATAN PELAYANAN (Memindahkan Rincian Pasien Rawat Jalan (H+3 jam 01:00 WIB) berdasarkan pasien MRS)
            // Get data pendapatan jalan 3 days ago
            $pendapatanJalanSiesta = (new DataPendapatanPelayanan)->setTable('simpra_pendapatanjalan_ft')->whereDate('tgl_pendaftaran', $three_days_ago)->get();
            foreach ($pendapatanJalanSiesta as $pj) {
                // Check if data already exist in table data_pendapatan_pelayanan (based on pendaftaran_id)
                $exist = DataPendapatanPelayanan::where('pendaftaran_id', $pj->pendaftaran_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataPendapatanPelayanan::create($pj->toArray());
                }
            }

            // 3. INSERT DATA PENDAPATAN INAP TO PENDAPATAN PELAYANAN (Memindahkan Rincian Pasien Rawat Inap (H+1 jam 01:00 WIB) berdasarkan pasien KRS)
            // Get data pendapatan inap yesterday
            $pendapatanInapSiesta = (new DataPendapatanPelayanan)->setTable('simpra_pendapataninap_ft')->whereDate('tgl_krs', $yesterday)->get();
            foreach ($pendapatanInapSiesta as $pi) {
                // Check if data already exist in table data_pendapatan_pelayanan (based on pendaftaran_id)
                $exist = DataPendapatanPelayanan::where('pendaftaran_id', $pi->pendaftaran_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataPendapatanPelayanan::create($pi->toArray());
                }
            }

            // 4. INSERT DATA PENERIMAAN LAYANAN (Memindahkan Rincian Transaksi Penerimaan Kasir (H+1 jam 01:00) berdasarkan kwitansi (bukti bayar pasien))
            // Get data penerimaan layanan yesterday
            $penerimaanlayananSiesta = (new DataPenerimaanLayanan)->setTable('simpra_penerimaanlayanan_ft')->whereDate('tgl_buktibayar', $yesterday)->get();
            foreach ($penerimaanlayananSiesta as $pl) {
                // Check if data already exist in table data_penerimaan_layanan (based on pendaftaran_id)
                $exist = DataPenerimaanLayanan::where('pendaftaran_id', $pl->pendaftaran_id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataPenerimaanLayanan::create($pl->toArray());
                }
            }

            // 5. INSERT DATA PENERIMAAN UMUM TO DATA PENERIMAAN LAIN (Memindahkan Rincian Transaksi Penerimaan Non Billing Kasir (H+1 jam 01:00 WIB) berdasarkan kwitansi (bukti bayar pihak pembayar))
            // Get data penerimaan umum yesterday
            $penerimaanUmumSiesta = (new DataPenerimaanLain)->setTable('penerimaanumum_v')->whereDate('tgl_bayar', $yesterday)->get();
            foreach ($penerimaanUmumSiesta as $pu) {
                // Check if data already exist in table data_penerimaan_lain (based on no_bayar)
                $exist = DataPenerimaanLain::where('no_bayar', $pu->no_bayar)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataPenerimaanLain::create($pu->toArray());
                }
            }

            // 8. INSERT DATA DOKUMEN NON-LAYANAN (Memindahkan Rincian Transaksi Potensi Lainnya (H+1 jam 01:00 WIB) berdasarkan tanggal_dokumen (tanggal surat pengesahan invoice))
            // Get data potensi lain yesterday
            $potensiLainSiesta = (new DokumenNonlayanan)->setTable('simpra_potensilain_ft')->whereDate('tgl_dokumen', $yesterday)->get();
            foreach ($potensiLainSiesta as $pl) {
                // Check if data already exist in table dokumen_nonlayanan (based on id)
                $exist = DokumenNonlayanan::where('id', $pl->id)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DokumenNonlayanan::create($pl->toArray());
                }
            }

            // 9. INSERT DATA REKAP HARIAN CARA BAYAR (Memindahkan Data Rekapitulasi Harian Tagihan Pelayanan per Cara Bayar (H+28 jam 01:00 WIB) berdasarkan tanggal pelayanan)
            // Get data rekap harian cara bayar 28 days ago
            $rekapHarianCarabayarSiesta = (new DataRekapHarianCaraBayar)->setTable('simpra_rekaphariancarabayar_ft')->whereDate('tgl_pelayanan', $twenty_eight_days_ago)->get();
            foreach ($rekapHarianCarabayarSiesta as $rhc) {
                // Check if data already exist in table data_rekap_harian_carabayar (based on nilai_klaim(SEMENTARA SAJA KRN TIDAK ADA KOLOM UNIQUE LAINNYA))
                $exist = DataRekapHarianCaraBayar::where('nilai_klaim', $rhc->nilai_klaim)->exists();
                // Insert if data not exist yet
                if (!$exist) {
                    DataRekapHarianCaraBayar::create($rhc->toArray());
                }
            }

        } catch (\Throwable $e) {
            \Log::error('cron:per-day failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Display error in terminal
            $this->error("cron:per-day failed: " . $e->getMessage());
        }
    }
}
