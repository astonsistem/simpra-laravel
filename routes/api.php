<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BillingKasirController;
use App\Http\Controllers\BillingKasirSetorController;
use App\Http\Controllers\BillingSwaController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\BuktiSetorController;
use App\Http\Controllers\CaraBayarController;
use App\Http\Controllers\CaraPembayaranController;
use App\Http\Controllers\DataClosingController;
use App\Http\Controllers\DataSelisihController;
use App\Http\Controllers\InstalasiController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\PasienBpjsController;
use App\Http\Controllers\PendapatanPelayananController;
use App\Http\Controllers\PenerimaanLainController;
use App\Http\Controllers\PenjaminController;
use App\Http\Controllers\PotensiLainController;
use App\Http\Controllers\PotensiPelayananController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekeningKoranController;
use App\Http\Controllers\SinkronisasiController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\SyncApiController;
use App\Http\Controllers\TempPenerimaanSwaController;
use App\Http\Controllers\PelaporanController;
use App\Http\Controllers\PenerimaanSelisihController;
use App\Http\Controllers\SumberTransaksiController;
use App\Http\Controllers\SelisihKasController;
use App\Http\Controllers\DataPenerimaanSelisihController;
use App\Http\Controllers\PendapatanPenjamin1Controller;

Route::post('auth/login_token', [AuthController::class, 'login']);
Route::post('auth/logintoken', [AuthController::class, 'loginToken']);
Route::middleware([
    'middleware' => 'api',
    'prefix' => 'auth'
])->group(function () {
    Route::get('auth/user/me', [AuthController::class, 'me']);
    Route::get('auth/users', [AuthController::class, 'list']);
    Route::get('auth/adminonly', [AuthController::class, 'adminonly']);
    Route::get('auth/users/{id}', [AuthController::class, 'show']);
    Route::post('auth/user', [AuthController::class, 'register']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::put('auth/user/{id}', [AuthController::class, 'update']);
    Route::delete('auth/user/{id}', [AuthController::class, 'destroy']);
});

Route::get('akun', [AkunController::class, 'index']);
Route::get('akun/list', [AkunController::class, 'list']);
Route::get('akun/list/akunpotensilain', [AkunController::class, 'listAkunPotensiLain']);
Route::get('akun/list/pendapatan', [AkunController::class, 'listPendapatan']);
Route::get('akun/{id}', [AkunController::class, 'show']);
Route::post('akun', [AkunController::class, 'store']);
Route::put('akun/{id}', [AkunController::class, 'update']);
Route::delete('akun/{id}', [AkunController::class, 'destroy']);

Route::get('carabayar', [CaraBayarController::class, 'index']);
Route::get('carabayar/list', [CaraBayarController::class, 'list']);

Route::get('penjamin', [PenjaminController::class, 'index']);
Route::get('penjamin/list', [PenjaminController::class, 'list']);
Route::get('penjamin/sync', [PenjaminController::class, 'sync']);

Route::get('instalasi', [InstalasiController::class, 'index']);
Route::get('instalasi/list', [InstalasiController::class, 'list']);
Route::get('instalasi/sync', [InstalasiController::class, 'sync']);

Route::get('kasir', [KasirController::class, 'index']);
Route::get('kasir/list', [KasirController::class, 'list']);
Route::get('kasir/sync', [KasirController::class, 'sync']);

Route::get('loket', [LoketController::class, 'index']);
Route::get('loket/list', [LoketController::class, 'list']);
Route::get('loket/sync', [LoketController::class, 'sync']);

Route::get('syncapi', [SyncApiController::class, 'index']);
Route::get('syncapi/menu', [SyncApiController::class, 'menu']);
Route::get('syncapi/list/{id}', [SyncApiController::class, 'list']);
Route::get('syncapi/{id}', [SyncApiController::class, 'show']);
Route::post('syncapi', [SyncApiController::class, 'store']);
Route::put('syncapi/{id}', [SyncApiController::class, 'update']);

Route::post('sinkronisasi/request', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/request/kasir', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/request/penerimaanumum', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/request/rincianpendapatan', [SinkronisasiController::class, 'pasien_bpjs']);
Route::post('sinkronisasi/save', [SinkronisasiController::class, 'pasien_bpjs']);
// Tambahkan rute ini untuk menangani request dengan ID kasir
Route::post('sinkronisasi/request/kasir/{kasirId}', [SinkronisasiController::class, 'requestKasir']);

Route::get('pendapatan_pelayanan', [PendapatanPelayananController::class, 'index']);
Route::get('pendapatan_pelayanan/statistik', [PendapatanPelayananController::class, 'statistik']);
Route::get('pendapatan_pelayanan/validasi/{id}', [PendapatanPelayananController::class, 'validasi']);
Route::get('pendapatan_pelayanan/tarik/{id}', [PendapatanPelayananController::class, 'tarik']);
Route::get('pendapatan_pelayanan/sinkron_fase1/{id}', [PendapatanPelayananController::class, 'sinkron_fase1']);
Route::get('pendapatan_pelayanan/sinkron_fase2/{id}', [PendapatanPelayananController::class, 'sinkron_fase2']);
Route::get('pendapatan_pelayanan/{id}', [PendapatanPelayananController::class, 'show']);
Route::put('pendapatan_pelayanan/{id}', [PendapatanPelayananController::class, 'update']);

Route::get('pendapatan_penjamin1', [PendapatanPenjamin1Controller::class, 'index']);
Route::post('pendapatan_penjamin1', [PendapatanPenjamin1Controller::class, 'store']);
Route::get('pendapatan_penjamin1/{id}', [PendapatanPenjamin1Controller::class, 'show']);
Route::put('pendapatan_penjamin1/{id}', [PendapatanPenjamin1Controller::class, 'update']);
Route::delete('pendapatan_penjamin1/{id}', [PendapatanPenjamin1Controller::class, 'destroy']);

Route::middleware([
    'middleware' => 'api',
    'prefix' => 'auth'
])->group(function () {
    Route::get('billing_kasir', [BillingKasirController::class, 'index']);
    Route::get('billing_kasir/statistik', [BillingKasirController::class, 'statistik']);
    Route::get('billing_kasir/validasi/{id}', [BillingKasirController::class, 'validasi']);
    Route::get('billing_kasir/validasi/filter/{id}', [BillingKasirController::class, 'validasiFilter']);
    Route::get('billing_kasir/validasi/filteruraian/{id}', [BillingKasirController::class, 'validasiFilterUraian']);
    Route::get('billing_kasir/validasi/filterjumlah/{id}', [BillingKasirController::class, 'validasiFilterJumlah']);
    Route::get('billing_kasir/{id}', [BillingKasirController::class, 'show']);
    Route::put('billing_kasir/validasi/penerimaan_layanan', [BillingKasirController::class, 'updateValidasi']);
    Route::put('billing_kasir/cancel_validasi/penerimaan_layanan', [BillingKasirController::class, 'cancelValidasi']);
    Route::put('billing_kasir/{id}', [BillingKasirController::class, 'update']);
    Route::delete('billing_kasir/{id}', [BillingKasirController::class, 'destroy']);
    // Aksi setor
    Route::get('billing_kasir/setor/{rc_id}', [BillingKasirSetorController::class, 'show']);
});

Route::middleware([
    'middleware' => 'api',
    'prefix' => 'auth'
])->group(function () {
    Route::get('billing_swa', [BillingSwaController::class, 'index']);
    Route::get('billing_swa/statistik', [BillingSwaController::class, 'statistik']);
    Route::get('billing_swa/validasi/filter/{id}', [BillingSwaController::class, 'validasiFilter']);
    Route::get('billing_swa/validasi/filteruraian/{id}', [BillingSwaController::class, 'validasiFilterUraian']);
    Route::get('billing_swa/validasi/filterjumlah/{id}', [BillingSwaController::class, 'validasiFilterJumlah']);
    Route::get('billing_swa/validasi/{id}', [BillingSwaController::class, 'validasi']);
    Route::get('billing_swa/{id}', [BillingSwaController::class, 'show']);
    Route::put('billing_swa/validasi/penerimaan_lain', [BillingSwaController::class, 'updateValidasi']);
    Route::put('billing_swa/cancel_validasi/penerimaan_lain', [BillingSwaController::class, 'cancelValidasi']);
    Route::put('billing_swa/{id}', [BillingSwaController::class, 'update']);
    Route::delete('billing_swa/{id}', [BillingSwaController::class, 'destroy']);
});

Route::get('potensi_pelayanan', [PotensiPelayananController::class, 'index']);
Route::get('potensi_pelayanan/getdata', [PotensiPelayananController::class, 'getdata']);
Route::get('potensi_pelayanan/statistik', [PotensiPelayananController::class, 'statistik']);
Route::get('potensi_pelayanan/{id}', [PotensiPelayananController::class, 'index']);

Route::get('penerimaan_lain', [PenerimaanLainController::class, 'index']);
Route::get('penerimaan_lain/getdata', [PenerimaanLainController::class, 'getdata']);
Route::get('penerimaan_lain/statistik', [PenerimaanLainController::class, 'statistik']);
Route::get('penerimaan_lain/{id}', [PenerimaanLainController::class, 'show']);
Route::get('penerimaan_lain/validasi/filter/{id}', [PenerimaanLainController::class, 'validasiFilter']);
Route::get('penerimaan_lain/validasi/filteruraian/{id}', [PenerimaanLainController::class, 'validasiFilterUraian']);
Route::get('penerimaan_lain/validasi/filterjumlah/{id}', [PenerimaanLainController::class, 'validasiFilterJumlah']);
Route::get('penerimaan_lain/validasi/{id}', [PenerimaanLainController::class, 'validasi']);
Route::post('penerimaan_lain', [PenerimaanLainController::class, 'store']);
Route::post('penerimaan_lain/list', [PenerimaanLainController::class, 'list']);
Route::post('penerimaan_lain/createdata', [PenerimaanLainController::class, 'createData']);
Route::put('penerimaan_lain/editdata/{id}', [PenerimaanLainController::class, 'updateEditData']);
Route::put('penerimaan_lain/validasi/penerimaan_lain', [PenerimaanLainController::class, 'updateValidasi']);
Route::put('penerimaan_lain/cancel_validasi/penerimaan_lain', [PenerimaanLainController::class, 'cancelValidasi']);
Route::put('penerimaan_lain/{id}', [PenerimaanLainController::class, 'update']);
Route::delete('penerimaan_lain/{id}', [PenerimaanLainController::class, 'destroy']);

Route::get('potensi_lain', [PotensiLainController::class, 'index']);
Route::get('potensi_lain/statistik', [PotensiLainController::class, 'statistik']);
Route::get('potensi_lain/{id}', [PotensiLainController::class, 'show']);
Route::post('potensi_lain', [PotensiLainController::class, 'store']);
Route::post('potensi_lain/terima/{id}', [PotensiLainController::class, 'terima']);
Route::put('potensi_lain/{id}', [PotensiLainController::class, 'update']);
Route::delete('potensi_lain/{id}', [PotensiLainController::class, 'destroy']);

Route::get('data_closing', [DataClosingController::class, 'index']);
Route::post('data_closing/list_closing', [DataClosingController::class, 'list']);
Route::post('data_closing', [DataClosingController::class, 'store']);
Route::put('data_closing/{id}', [DataClosingController::class, 'update']);
Route::delete('data_closing/{id}', [DataClosingController::class, 'destroy']);

Route::get('temp_penerimaan_swa', [TempPenerimaanSwaController::class, 'index']);
Route::get('temp_penerimaan_swa/{id}', [TempPenerimaanSwaController::class, 'show']);

Route::group([
    'middleware' => 'api',
], function() {
    Route::get('rekening_koran', [RekeningKoranController::class, 'index']);
    Route::get('rekening_koran/list', [RekeningKoranController::class, 'list']);
});

Route::get('rekening_koran/statistik', [RekeningKoranController::class, 'statistik']);
Route::get('rekening_koran/sum_rekening_koran', [RekeningKoranController::class, 'sum']);
Route::get('rekening_koran/statistik', [RekeningKoranController::class, 'statistik']);
Route::get('rekening_koran/{id}', [RekeningKoranController::class, 'show']);
Route::get('rekening_koran/pb/uncheck', [RekeningKoranController::class, 'pbUncheck']);
Route::get('rekening_koran/pb/check/{rc_id}', [RekeningKoranController::class, 'pbCheck']);
Route::get('rekening_koran/bukti_setor/{rc_id}', [RekeningKoranController::class, 'buktiSetor']);
Route::post('rekening_koran/{bank}/upload', [RekeningKoranController::class, 'index']);
Route::post('rekening_koran/mutasi/{bank}', [RekeningKoranController::class, 'index']);
Route::post('rekening_koran/sinkronisasi', [RekeningKoranController::class, 'index']);
Route::post('rekening_koran/sinkronisasi-api/{bank}', [RekeningKoranController::class, 'index']);
Route::put('rekening_koran/{id}', [RekeningKoranController::class, 'update']);
Route::put('rekening_koran/pb/{id}', [RekeningKoranController::class, 'updatePb']);
Route::put('rekening_koran/pb_cancel/{id}', [RekeningKoranController::class, 'updatePbCancel']);

Route::get('bukti_setor', [BuktiSetorController::class, 'index']);
Route::get('bukti_setor/statistik', [BuktiSetorController::class, 'statistik']);

Route::get('bku', [BkuController::class, 'index']);
Route::get('bku/list', [BkuController::class, 'list']);
Route::get('bku/bku', [BkuController::class, 'bku']);
Route::get('bku/statistik', [BkuController::class, 'statistik']);
Route::get('bku/rincian/{id}', [BkuController::class, 'rincian']);
Route::get('bku/rincian/detail/{id}', [BkuController::class, 'detailRincian']);
Route::get('bku/{id}', [BkuController::class, 'show']);
Route::post('bku', [BkuController::class, 'store']);
Route::post('bku/listbku', [BkuController::class, 'listbku']);
Route::post('bku/bku_post', [BkuController::class, 'listbku']);
Route::post('bku/all_bku', [BkuController::class, 'listbku']);
Route::post('bkurincian', [BkuController::class, 'bkurincian']);
Route::put('bku/{id}', [BkuController::class, 'index']);
Route::put('bku/bku_put/{id}', [BkuController::class, 'index']);
Route::put('bku/rincian/{id}', [BkuController::class, 'index']);
Route::put('bku/validasi/{id}', [BkuController::class, 'updateValidasi']);
Route::put('bku/cancel-validasi/{id}', [BkuController::class, 'cancelValidasi']);
Route::delete('bku/{id}', [BkuController::class, 'destroy']);
Route::delete('bku/rincian/{id}', [BkuController::class, 'destroyRincian']);

Route::get('statistik/dashboard', [StatistikController::class, 'index']);

Route::get('rekap/pasien_rawat_jalan_bpjs', [RekapController::class, 'pasienRajalBpjs']);
Route::get('rekap/pasien_rawat_inap_bpjs', [RekapController::class, 'pasienRanapBpjs']);

Route::get('pasienbpjs', [PasienBpjsController::class, 'index']);
Route::get('pasienbpjs/tarik', [PasienBpjsController::class, 'tarik']);
Route::get('pasienbpjs/tarik/tarik', [PasienBpjsController::class, 'tarik']);

Route::get('pelaporan-list', [PelaporanController::class, 'list']);
Route::get('pelaporan/{slug}', [PelaporanController::class, 'show']);
Route::post('/pelaporan-generate/{id}', [PelaporanController::class, 'generate']);
Route::delete('/pelaporan-delete/{filename}', [PelaporanController::class, 'deleteTemp']);

Route::get('bank', [BankController::class, 'index']);
Route::get('bank/list', [BankController::class, 'list']);
Route::get('bank/{id}', [BankController::class, 'show']);
Route::post('bank', [BankController::class, 'store']);
Route::put('bank/{id}', [BankController::class, 'update']);
Route::delete('bank/{id}', [BankController::class, 'destroy']);

Route::get('carapembayaran', [CaraPembayaranController::class, 'index']);
Route::get('carapembayaran/list', [CaraPembayaranController::class, 'list']);
Route::get('carapembayaran/{id}', [CaraPembayaranController::class, 'show']);
Route::post('carapembayaran', [CaraPembayaranController::class, 'store']);
Route::put('carapembayaran/{id}', [CaraPembayaranController::class, 'update']);
Route::delete('carapembayaran/{id}', [CaraPembayaranController::class, 'destroy']);

Route::get('sumbertransaksi', [SumberTransaksiController::class, 'index']);
Route::get('sumbertransaksi/list', [SumberTransaksiController::class, 'list']);
Route::get('sumbertransaksi/{id}', [SumberTransaksiController::class, 'show']);
Route::post('sumbertransaksi', [SumberTransaksiController::class, 'store']);
Route::put('sumbertransaksi/{id}', [SumberTransaksiController::class, 'update']);
Route::delete('sumbertransaksi/{id}', [SumberTransaksiController::class, 'destroy']);

Route::get('kurangbayar/penerimaan_selisih', [PenerimaanSelisihController::class, 'index']);
Route::get('kurangbayar/penerimaan_selisih/list', [PenerimaanSelisihController::class, 'list']);
Route::get('kurangbayar/penerimaan_selisih/{id}', [PenerimaanSelisihController::class, 'show']);
Route::post('kurangbayar/penerimaan_selisih', [PenerimaanSelisihController::class, 'store']);
Route::put('kurangbayar/penerimaan_selisih/{id}', [PenerimaanSelisihController::class, 'update']);
Route::delete('kurangbayar/penerimaan_selisih/{id}', [PenerimaanSelisihController::class, 'destroy']);

Route::get('kurangbayar/data_selisih', [DataSelisihController::class, 'index']);
Route::get('kurangbayar/data_selisih/{id}', [DataSelisihController::class, 'show']);

Route::get('kurangbayar/penerimaan_transaksi', [DataPenerimaanSelisihController::class, 'getTransaksi']);
Route::get('selisih-kas', [SelisihKasController::class, 'index']);
Route::get('selisih-kas/{id}', [SelisihKasController::class, 'getBYId']);
Route::post('selisih-kas', [SelisihKasController::class, 'store']);
Route::put('selisih-kas/{id}', [SelisihKasController::class, 'update']);
Route::delete('selisih-kas/{id}', [SelisihKasController::class, 'destroy']);

Route::get('data-penerimaan-selisih', [DataPenerimaanSelisihController::class, 'index']);
Route::post('data-penerimaan-selisih', [DataPenerimaanSelisihController::class, 'store']);
Route::get('data-penerimaan-selisih/{id}', [DataPenerimaanSelisihController::class, 'show']);
Route::delete('data-penerimaan-selisih/{id}', [DataPenerimaanSelisihController::class, 'destroy']);
