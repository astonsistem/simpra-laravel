<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BillingKasirController;
use App\Http\Controllers\BillingSwaController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\BuktiSetorController;
use App\Http\Controllers\CaraBayarController;
use App\Http\Controllers\DataClosingController;
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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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

Route::get('pendapatan_pelayanan', [PendapatanPelayananController::class, 'index']);
Route::get('pendapatan_pelayanan/statistik', [PendapatanPelayananController::class, 'statistik']);
Route::get('pendapatan_pselayanan/{id}', [PendapatanPelayananController::class, 'show']);

Route::get('billing_kasir', [BillingKasirController::class, 'index']);
Route::get('billing_kasir/statistik', [BillingKasirController::class, 'statistik']);
Route::get('billing_kasir/validasi/{id}', [BillingKasirController::class, 'index']);
Route::get('billing_kasir/validasi/filter/{id}', [BillingKasirController::class, 'index']);
Route::get('billing_kasir/validasi/filteruraian/{id}', [BillingKasirController::class, 'index']);
Route::get('billing_kasir/validasi/filterjumlah/{id}', [BillingKasirController::class, 'index']);
Route::get('billing_kasir/{id}', [BillingKasirController::class, 'show']);
Route::put('billing_kasir/{id}', [BillingKasirController::class, 'index']);
Route::put('billing_kasir/validasi/penerimaan_layanan', [BillingKasirController::class, 'index']);
Route::put('billing_kasir/cancel_validasi/penerimaan_layanan', [BillingKasirController::class, 'index']);
Route::delete('billing_kasir/{id}', [BillingKasirController::class, 'destroy']);

Route::get('billing_swa', [BillingSwaController::class, 'index']);
Route::get('billing_swa/statistik', [BillingSwaController::class, 'statistik']);
Route::get('billing_swa/validasi/{id}', [BillingSwaController::class, 'statistik']);
Route::get('billing_swa/validasi/filter/{id}', [BillingSwaController::class, 'statistik']);
Route::get('billing_swa/validasi/filteruraian/{id}', [BillingSwaController::class, 'statistik']);
Route::get('billing_swa/validasi/filterjumlah/{id}', [BillingSwaController::class, 'statistik']);
Route::get('billing_swa/{id}', [BillingSwaController::class, 'show']);
Route::put('billing_swa/{id}', [BillingSwaController::class, 'show']);
Route::put('billing_swa/validasi/penerimaan_lain', [BillingSwaController::class, 'show']);
Route::put('billing_swa/cancel_validasi/penerimaan_lain', [BillingSwaController::class, 'show']);
Route::delete('billing_swa/{id}', [BillingSwaController::class, 'destroy']);

Route::get('potensi_pelayanan', [PotensiPelayananController::class, 'index']);

Route::get('penerimaan_lain', [PenerimaanLainController::class, 'index']);

Route::get('potensi_lain', [PotensiLainController::class, 'index']);

Route::get('data_closing', [DataClosingController::class, 'index']);
Route::post('data_closing/list_closing', [DataClosingController::class, 'list']);
Route::post('data_closing', [DataClosingController::class, 'store']);
Route::put('data_closing/{id}', [DataClosingController::class, 'update']);
Route::delete('data_closing/{id}', [DataClosingController::class, 'destroy']);

Route::get('temp_penerimaan_swa', [TempPenerimaanSwaController::class, 'index']);
Route::get('temp_penerimaan_swa/{id}', [TempPenerimaanSwaController::class, 'show']);

Route::get('rekening_koran', [RekeningKoranController::class, 'index']);

Route::get('bukti_setor', [BuktiSetorController::class, 'index']);
Route::get('bukti_setor/statistik', [BuktiSetorController::class, 'statistik']);

Route::get('bku', [BkuController::class, 'index']);
Route::get('bku/listbku', [BkuController::class, 'index']);
Route::get('bku/bku', [BkuController::class, 'index']);
Route::get('bku/statistik', [BkuController::class, 'index']);
Route::get('bku/bku_post', [BkuController::class, 'index']);
Route::get('bku/bku_put', [BkuController::class, 'index']);
Route::get('bku/all_bku', [BkuController::class, 'index']);
Route::delete('bku/{id}', [BkuController::class, 'destroy']);
Route::delete('bku/rincian/{id}', [BkuController::class, 'destroyRincian']);

Route::get('statistik/dashboard', [StatistikController::class, 'index']);

Route::get('rekap/pasien_rawat_jalan_bpjs', [RekapController::class, 'pasienRajalBpjs']);
Route::get('rekap/pasien_rawat_inap_bpjs', [RekapController::class, 'pasienRanapBpjs']);

Route::get('pasienbpjs', [PasienBpjsController::class, 'index']);
Route::get('pasienbpjs/tarik', [PasienBpjsController::class, 'tarik']);
Route::get('pasienbpjs/tarik/tarik', [PasienBpjsController::class, 'tarik']);
