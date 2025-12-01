<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\DataPenerimaanLain;
use App\Models\DataRekapRegister;
use App\Models\DataPendapatanPelayanan;
use App\Models\DataPenerimaanLayanan;
use App\Models\DataPotensiPelayanan;
use App\Models\DokumenNonlayanan;
use App\Models\DataRekapHarianCaraBayar;
use App\Models\RincianPotensiPelayanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            $yesterday = Carbon::yesterday()->toDateString();
            $two_days_ago = Carbon::now()->subDays(2)->toDateString();
            $three_days_ago = Carbon::now()->subDays(3)->toDateString();
            $four_days_ago = Carbon::now()->subDays(4)->toDateString();
            $twenty_eight_days_ago = Carbon::now()->subDays(28)->toDateString();
            $thirty_days_ago = Carbon::now()->subDays(30)->toDateString();
            $tahun = Carbon::now()->year;
            $tenDaysAgo = Carbon::now()->subDays(11);
            // HELPER TO RUN EACH STEP SAFELY 
            $errors = [];
            $run = function (string $step, callable $fn) use (&$errors) {
                try {
                    $fn();
                } catch (\Throwable $e) {
                    $errors[] = [
                        'step' => $step,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                    \Log::error("❌ Step {$step} failed", [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            };

            // 1. INSERT DATA REKAPREGISTER (Rekapitulasi Register Pasien Harian (H+1))
            $run('1. Insert Data Rekap Register', function () use ($yesterday) {
                // Get data rekap register yesterday
                $rekapRegisterSiesta = (new DataRekapRegister)->setTable('simpra_rekapregister_ft')->whereDate('tgl', $yesterday)->get();
                $inserted = 0;
                foreach ($rekapRegisterSiesta as $rr) {
                    // Check if data already exist in table data_rekap_register (based on tgl)
                    $exist = DataRekapRegister::where('tgl', $rr->tgl)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataRekapRegister::create($rr->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 1 berhasil insert {$inserted} data ke table data_rekap_register.");
            });

            // 2. INSERT DATA PENDAPATAN JALAN TO PENDAPATAN PELAYANAN (Memindahkan Rincian Pasien Rawat Jalan (H+3) berdasarkan pasien MRS)
            //menambah tanggal penarikan 20251911
            $run('2. Insert Data Pendapatan Jalan To Pendapatan Pelayanan', function () use ($three_days_ago) {
                // Get data pendapatan jalan 3 days ago
                $pendapatanJalanSiesta = (new DataPendapatanPelayanan)->setTable('simpra_pendapatanjalan_ft')->whereDate('tgl_pendaftaran', $three_days_ago)->get();
                $inserted = 0;
                foreach ($pendapatanJalanSiesta as $pj) {
                    // Check if data already exist in table data_pendapatan_pelayanan (based on pendaftaran_id)
                    $exist = DataPendapatanPelayanan::where('pendaftaran_id', $pj->pendaftaran_id)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPendapatanPelayanan::create(array_merge(
                            $pj->toArray(),
                            [
                                'sync_at' => Carbon::now(),                                
                            ]
                        ));
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 2 berhasil insert {$inserted} data ke table data_pendapatan_pelayanan.");
            });

            // 3. INSERT DATA PENDAPATAN INAP TO PENDAPATAN PELAYANAN (Memindahkan Rincian Pasien Rawat Inap (H+1) berdasarkan pasien KRS)
            //menambah tanggal penarikan 20251911
            $run('3. Insert Data Pendapatan Inap To Pendapatan Pelayanan', function () use ($yesterday) {
                // Get data pendapatan inap yesterday
                $pendapatanInapSiesta = (new DataPendapatanPelayanan)->setTable('simpra_pendapataninap_ft')->whereDate('tgl_krs', $yesterday)->get();
                $inserted = 0;
                foreach ($pendapatanInapSiesta as $pi) {
                    // Check if data already exist in table data_pendapatan_pelayanan (based on pendaftaran_id)
                    $exist = DataPendapatanPelayanan::where('pendaftaran_id', $pi->pendaftaran_id)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPendapatanPelayanan::create(array_merge(
                            $pi->toArray(),
                            [
                                'sync_at' => Carbon::now(),                                
                            ]
                        ));
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 3 berhasil insert {$inserted} data ke table data_pendapatan_pelayanan.");
            });

            // 4. INSERT DATA PENERIMAAN LAYANAN (Memindahkan Rincian Transaksi Penerimaan Kasir (H+1) berdasarkan kwitansi (bukti bayar pasien))
            $run('4. Insert Data Penerimaan Layanan', function () use ($yesterday) {
                // Get data penerimaan layanan yesterday
                $penerimaanlayananSiesta = (new DataPenerimaanLayanan)->setTable('ft_penerimaanlayanan_v')->whereDate('tgl_buktibayar', $yesterday)->get();
                $inserted = 0;
                foreach ($penerimaanlayananSiesta as $pl) {
                    // Check if data already exist in table data_penerimaan_layanan (based on pendaftaran_id)
                    $exist = DataPenerimaanLayanan::where('pendaftaran_id', $pl->pendaftaran_id)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPenerimaanLayanan::create($pl->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 4 berhasil insert {$inserted} data ke table data_penerimaan_layanan.");
            });

            // 5. INSERT DATA PENERIMAAN UMUM TO DATA PENERIMAAN LAIN (Memindahkan Rincian Transaksi Penerimaan Non Billing Kasir (H+1) berdasarkan kwitansi (bukti bayar pihak pembayar))
            $run('5. Insert Data Penerimaan Umum To Data Penerimaan Lain', function () use ($thirty_days_ago, $yesterday) {
                // Get data penerimaan umum yesterday
                $penerimaanUmumSiesta = (new DataPenerimaanLain)->setTable('penerimaanumum_v')->whereBetween('tgl_bayar', [$thirty_days_ago, $yesterday])->get();
                $inserted = 0;
                foreach ($penerimaanUmumSiesta as $pu) {
                    // Check if data already exist in table data_penerimaan_lain (based on no_bayar)
                    $exist = DataPenerimaanLain::where('no_bayar', $pu->no_bayar)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPenerimaanLain::create($pu->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 5 berhasil insert {$inserted} data ke table data_penerimaan_lain.");
            });

            // 6. INSERT DATA POTENSI PELAYANAN PASIEN UMUM (Berdasarkan Tanggal Dokumen (tgl_dokumen = yesterday))
            $run('6. Insert Data Potensi Pelayanan Pasien Umum', function () use ($yesterday) {
                // Get data potensi pelayanan (pasien umum) yesterday
                $potensiPelayananSiesta = (new DataPotensiPelayanan)->setTable('simpra_potensipelayanan_ft')->whereDate('tgl_dokumen', $yesterday)->where('akun_id', 1010201)->get();
                $inserted = 0;
                foreach ($potensiPelayananSiesta as $pp) {
                    // Check if data already exist in table data_potensi_pelayanan (based on pendaftaran_id)
                    $exist = DataPotensiPelayanan::where('pendaftaran_id', $pp->pendaftaran_id)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPotensiPelayanan::create($pp->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 6 berhasil insert {$inserted} data piutang pasien umum ke table data_potensi_pelayanan.");
            });

            // 7. INSERT DATA POTENSI PELAYANAN PASIEN GL (Berdasarkan Tanggal Dokumen (tgl_dokumen = this year))
            $run('7. Insert Data Potensi Pelayanan Pasien GL', function () use ($tahun) {
                // Get data potensi pelayanan (pasien gl) this year
                $potensiPelayananGLSiesta = (new DataPotensiPelayanan)->setTable('simpra_potensipelayanan_ft')->whereYear('tgl_dokumen', $tahun)->where('akun_id', 1010208)->get();
                $inserted = 0;
                foreach ($potensiPelayananGLSiesta as $ppgl) {
                    // Check if data already exist in table data_potensi_pelayanan (based on pendaftaran_id)
                    $exist = DataPotensiPelayanan::where('pendaftaran_id', $ppgl->pendaftaran_id)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPotensiPelayanan::create($ppgl->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 7 berhasil insert {$inserted} data pasien GL ke table data_potensi_pelayanan.");
            });

            // 8. INSERT DATA POTENSI PELAYANAN SELAIN PASIEN UMUM DAN GL (Berdasarkan tahun Tanggal Dokumen (tgl_dokumen = this year))
             $run('8. Insert Data Potensi Pelayanan selain Pasien UMUM & GL', function () use ($tahun) {
                // Get data potensi pelayanan (except pasien umum and gl) this year
                // perubahan diambil dari 10 hari yang lalu = 20251119
                $potensiPelayananNonUmumGLSiesta = (new DataPotensiPelayanan)->setTable('ft_potensipelayanan_nonumumgl_v')->whereYear('tgl_dokumen', $tahun)->get();
                $inserted = 0;
                foreach ($potensiPelayananNonUmumGLSiesta as $ppnugl) {
                    // Check if data already exist in table data_potensi_pelayanan (based on no_dokumen)
                    $exist = DataPotensiPelayanan::where('no_dokumen', $ppnugl->no_dokumen)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataPotensiPelayanan::create($ppnugl->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 8 berhasil insert {$inserted} data selain pasien umum & GL ke table data_potensi_pelayanan.");
            });

            // 9. INSERT DATA RINCIAN POTENSI PELAYANAN SELAIN PASIEN UMUM DAN GL (Berdasarkan Tanggal Dokumen (tgl_dokumen = yesterday))
            $run('9. Insert Data Rincian Potensi Pelayanan selain Pasien UMUM & GL', function () use ($tahun) {
                // Get data rincian potensi pelayanan (except pasien umum and gl) yesterday
                $rincianPotensiPelayananSiesta = (new RincianPotensiPelayanan)->setTable('ft_rincianpotensipelayanan_nonumumgl_v')->whereYear('tgl_dokumen', $tahun)->get();
                $inserted = 0;                
                foreach ($rincianPotensiPelayananSiesta as $rpp) {                   
                    $exist = RincianPotensiPelayanan::where('no_dokumen', $rpp->no_dokumen)->where('pendaftaran_id', $rpp->pendaftaran_id)->exists();
                    if (!$exist) {
                        RincianPotensiPelayanan::create($rpp->toArray());
                        $inserted++;      
                    }              
                }
                // log inserted data
                \Log::info("Cronjob no 9 berhasil insert {$inserted} data rincian sleian pasien umum & GL ke table rincian_potensi_pelayanan.");
            });

            // 10. UPDATE PIUTANG ID PADA RINCIAN POTENSI LAYANAN            
            $run('10. Update piutang_id On Rincian Potensi Pelayanan', function () use ($tahun) {
                $updated = DataPendapatanPelayanan::query()
                    ->from('rincian_potensi_pelayanan as a')
                    ->whereNull('piutang_id')
                    ->update([
                        'piutang_id' => DB::raw("(select max(id) from data_potensi_pelayanan b where b.no_dokumen = a.no_dokumen)"),
                    ]);
                // log updated data
                \Log::info("Cronjob no 10 berhasil update {$updated} data pada table rincian_potensi_pelayanan.");
            });

            // 11. INSERT DATA DOKUMEN NON-LAYANAN (Memindahkan Rincian Transaksi Potensi Lainnya (H+1) berdasarkan tanggal_dokumen (tanggal surat pengesahan invoice))
            //diubah ditarik dala setahun = 20251119
            $run('11. Insert Data Dokumen Non-Layanan', function () use ($tahun) {
                // Get data potensi lain yesterday
                $potensiLainSiesta = (new DokumenNonlayanan)->setTable('simpra_potensilain_ft')->whereYear('tgl_dokumen', $tahun)->get();
                $inserted = 0;
                foreach ($potensiLainSiesta as $pl) {
                    // Check if data already exist in table dokumen_nonlayanan (based on no_dokumen)
                    $exist = DokumenNonlayanan::where('no_dokumen', $pl->no_dokumen)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DokumenNonlayanan::create($pl->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 11 berhasil insert {$inserted} data ke table dokumen_nonlayanan.");
            });

            // 12. INSERT DATA REKAP HARIAN CARA BAYAR (Memindahkan Data Rekapitulasi Harian Tagihan Pelayanan per Cara Bayar (H+28) berdasarkan tanggal pelayanan)
            $run('12. Insert Data Rekap Harian Cara Bayar', function () use ($twenty_eight_days_ago) {
                // Get data rekap harian cara bayar 28 days ago
                $rekapHarianCarabayarSiesta = (new DataRekapHarianCaraBayar)->setTable('simpra_rekaphariancarabayar_ft')->whereDate('tgl_pelayanan', $twenty_eight_days_ago)->get();
                $inserted = 0;
                foreach ($rekapHarianCarabayarSiesta as $rhc) {
                    // Check if data already exist in table data_rekap_harian_carabayar (based on nilai_klaim(SEMENTARA SAJA KRN TIDAK ADA KOLOM UNIQUE LAINNYA))
                    $exist = DataRekapHarianCaraBayar::where('nilai_klaim', $rhc->nilai_klaim)->exists();
                    // Insert if data not exist yet
                    if (!$exist) {
                        DataRekapHarianCaraBayar::create($rhc->toArray());
                        $inserted++;
                    }
                }
                // log inserted data
                \Log::info("Cronjob no 12 berhasil insert {$inserted} data ke table data_rekap_harian_carabayar.");
            });

            // 13. SYNC STATUS FASE 1 ON DATA PENDAPATAN PELAYANAN (Sinkronisasi data tagihan (H+4) berdasarkan tanggal pelayanan)
            // merubah criteria tanggal yang akan di update datanya berdasarkan tanggal sync_at 20251119
            $run('13. Sync Status Fase 1 On Data Pendapatan Pelayanan', function () use ($yesterday) {
                $updated = DataPendapatanPelayanan::where('status_fase1', '!=', 'Valid')->orWhereNull('status_fase1')
                    ->update([
                        'status_fase1' => DB::raw("
                            CASE 
                                WHEN COALESCE(total_sharing, 0) + COALESCE(total_dijamin, 0) = COALESCE(pendapatan, 0) + COALESCE(pdd, 0) + COALESCE(piutang, 0) 
                                    THEN 'Valid'
                                WHEN COALESCE(total_dijamin, 0) = COALESCE(piutang, 0) 
                                    THEN 'Koreksi Tagihan'
                                ELSE 'Koreksi Piutang'
                            END
                        "),
                    ]);
                // log updated data
                \Log::info("Cronjob no 13 berhasil update {$updated} data pada table data_pendapatan_pelayanan.");
            });

            // 14. SYNC STATUS FASE 2 ON DATA PENDAPATAN PELAYAAN (PAKAI DARI MODEL DATA PENDAPATAN PELAYANAN)
            //  (1) Sinkronisasi klarifikasi pendapatan pada data tagihan (H+5) berdasarkan tanggal pelayanan
            //  (2) Sinkronisasi klarifikasi pdd pada data tagihan (H+5) berdasarkan tanggal pelayanan
            //  (3) Sinkronisasi status klarifikasi piutang perorangan pada data tagihan (H+6) berdasarkan tanggal pelayanan
            //  (4) Sinkronisasi status klarifikasi piutang penjaminan pada data tagihan (H+31) berdasarkan tanggal pelayanan
            $run('14. Sync Status Fase 2 On Data Pendapatan Pelayanan', function () {
                $syncFase2All = DataPendapatanPelayanan::syncFase2All();
                // log data
                \Log::info('Cronjob no 14 berhasil dijalankan', $syncFase2All);
            });

            // 15. SYNC NON UMUM / GL POTENSI ID, POTENSI NO AND POTENSI NOMINAL ON DATA PENDAPATAN PELAYANAN (Sinkronisasi klarifikasi piutang penjaminan pada data tagihan (H+30) berdasarkan tanggal pelayanan)
            $run('15. Sync Potensi Id, Potensi No and Potensi Nominal on Data Pendapatan Pelayanan Cara Bayar Non UMUM / GL', function () use ($thirty_days_ago) {
                $updated = DataPendapatanPelayanan::query()
                    ->from('data_pendapatan_pelayanan as a')
                    ->whereDate('a.tgl_pelayanan', $thirty_days_ago)
                    ->whereNotIn('a.carabayar_id', [1,15])
                    ->whereRaw('COALESCE(a.total_dijamin, 0) <> 0')
                    ->update([
                        'potensi_id' => DB::raw('(SELECT MAX(b.rincian_id) FROM rincian_potensi_pelayanan b WHERE b.pendaftaran_id = a.pendaftaran_id)'),
                        'potensi_no' => DB::raw('(SELECT MAX(b.no_dokumen) FROM rincian_potensi_pelayanan b WHERE b.pendaftaran_id = a.pendaftaran_id)'),
                        'potensi_nominal' => DB::raw('(SELECT SUM(b.total_klaim) FROM rincian_potensi_pelayanan b WHERE b.pendaftaran_id = a.pendaftaran_id)'),
                    ]);
                // log updated data
                \Log::info("Cronjob no 15 berhasil update {$updated} data pada table data_pendapatan_pelayanan cara bayar selain UMUM / GL.");
            });

            // 16. SYNC UMUM / GL POTENSI ID, POTENSI NO AND POTENSI NOMINAL ON DATA PENDAPATAN PELAYANAN (Sinkronisasi klarifikasi piutang penjaminan pada data tagihan (H+30) berdasarkan tanggal pelayanan)
            $run('16. Sync Potensi Id, Potensi No and Potensi Nominal on Data Pendapatan Pelayanan Cara Bayar UMUM / GL', function () use ($thirty_days_ago) {
                $updated = DataPendapatanPelayanan::query()
                    ->from('data_pendapatan_pelayanan as a')
                    ->whereDate('a.tgl_pelayanan', $thirty_days_ago)
                    ->whereIn('a.carabayar_id', [1,15])
                    ->whereRaw('COALESCE(a.total_dijamin, 0) <> 0')
                    ->update([
                        'potensi_id' => DB::raw('(SELECT MAX(b.id) FROM data_potensi_pelayanan b WHERE b.pendaftaran_id = a.pendaftaran_id)'),
                        'potensi_no' => DB::raw('(SELECT MAX(b.no_dokumen) FROM data_potensi_pelayanan b WHERE b.pendaftaran_id = a.pendaftaran_id)'),
                        'potensi_nominal' => DB::raw('(SELECT MAX(b.total) FROM data_potensi_pelayanan b WHERE b.pendaftaran_id = a.pendaftaran_id)'),
                    ]);
                // log updated data
                \Log::info("Cronjob no 16 berhasil update {$updated} data pada table data_pendapatan_pelayanan cara bayar UMUM / GL.");
            });

            // THROW IF THERS ANY ERRORS
            if (!empty($errors)) {
                // Re-throw a combined error so outer catch can handle
                throw new \Exception(json_encode($errors, JSON_PRETTY_PRINT));
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
