<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UpkController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\Kabupaten\AuthController as KabupatenAuthController;
use App\Http\Controllers\Kabupaten\KabupatenController;
use App\Http\Controllers\Rekap\AuthController as RekapAuthController;
use App\Http\Controllers\Rekap\RekapController;
use App\Http\Controllers\KelompokController;
use App\Http\Controllers\PelaporanController;
use App\Http\Controllers\PinjamanAnggotaController;
use App\Http\Controllers\PinjamanIndividuController;
use App\Http\Controllers\PinjamanKelompokController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\SahamController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Models\Kecamatan;
use App\Models\PinjamanKelompok;
use App\Models\User;
use Illuminate\Support\Facades\Route;


Route::get('/symlink', function() {
    try {
        $target = storage_path('app/public');
        $link = public_path('storage');

        // Check and clear existing blocker
        if (file_exists($link) || is_link($link)) {
            if (is_link($link)) {
                unlink($link);
            } elseif (is_dir($link)) {
                rename($link, $link . '_backup_' . time());
            }
        }

        // 1. Try artisan first
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
            return "✅ [Metode 1] Storage link via Artisan SUKSES!";
        } catch (\Exception $e1) {
            // 2. Try explicit symlink
            if (symlink($target, $link)) {
                return "✅ [Metode 2] Storage link via Native symlink SUKSES!";
            }
            throw $e1;
        }
    } catch (\Exception $e) {
        // 3. Fallback Shell Command for Linux Sharing Hosts
        try {
            $target = storage_path('app/public');
            $link = public_path('storage');
            
            $output = shell_exec("ln -s \"$target\" \"$link\" 2>&1");
            
            if (file_exists($link) || is_link($link)) {
                 return "✅ [Metode 3] Storage link via Shell Command SUKSES! Hasil: $output";
            }
            return "❌ SEMUA GAGAL. Terakhir: " . $e->getMessage() . " | Shell: $output";
        } catch (\Exception $eFinal) {
             return "❌ FATAL: " . $eFinal->getMessage();
        }
    }
});

// PAMUNGKAS: Serve images via PHP if symlink fails on server
Route::get('/storage/logo/{filename}', function ($filename) {
    $path = storage_path('app/public/logo/' . $filename);
    if (!Illuminate\Support\Facades\File::exists($path)) {
        // check if it exists in generic app/logo too just in case
        $path = storage_path('app/logo/' . $filename);
    }
    
    if (Illuminate\Support\Facades\File::exists($path)) {
        return response()->file($path);
    }
    
    // Last resort default fallback
    $default = public_path('assets/img/logo.jpeg');
    if (Illuminate\Support\Facades\File::exists($default)) {
        return response()->file($default);
    }
    
    abort(404);
})->where('filename', '.*');

Route::get('/master', [AdminAuthController::class, 'index'])->middleware('guest');
Route::post('/master/login', [AdminAuthController::class, 'login'])->middleware('guest');
Route::group(['prefix' => 'master', 'as' => 'master.', 'middleware' => 'master'], function () {
    Route::get('/dashboard', [AdminController::class, 'index']);
    Route::get('/simpan_saldo', [DashboardController::class, 'simpanSaldo']);

    Route::get('/kecamatan/{kd_prov}/{kd_kab}/{kd_kec}', [KecamatanController::class, 'index']);

    Route::get('/kabupaten/{kd_prov}/{kd_kab}/', [AdminKabupatenController::class, 'index']);
    Route::get('/kabupaten/laporan/sub_laporan/{laporan}/', [AdminKabupatenController::class, 'subLaporan']);
    Route::get('/kabupaten/laporan/data/{lokasi}/', [AdminKabupatenController::class, 'data']);
    Route::post('/kabupaten/laporan/preview/{kd_kab}', [AdminKabupatenController::class, 'preview']);

    Route::post('/users/akses_tombol/{user}', [AdminUserController::class, 'AksesTombol']);

    Route::post('/users/hak_akses/{user}', [AdminUserController::class, 'HakAkses']);
    Route::resource('/users', AdminUserController::class);

    Route::get('/laporan', [AdminController::class, 'laporan']);

    Route::get('/buat_invoice', [InvoiceController::class, 'index']);
    Route::get('/nomor_invoice', [InvoiceController::class, 'InvoiceNo']);
    Route::get('/jumlah_tagihan', [InvoiceController::class, 'Tagihan']);

    Route::get('/unpaid', [InvoiceController::class, 'Unpaid']);
    Route::get('/{invoice}/unpaid', [InvoiceController::class, 'DetailUnpaid']);

    Route::get('/paid', [InvoiceController::class, 'Paid']);
    Route::get('/{invoice}/paid', [InvoiceController::class, 'DetailPaid']);

    Route::post('/buat_invoice', [InvoiceController::class, 'store']);
    Route::put('/{invoice}/simpan', [InvoiceController::class, 'simpan']);

    Route::resource('/menu', MenuController::class);

    Route::get('/migrasi_upk/server/{server}', [UpkController::class, 'Server']);
    Route::get('/migrasi_upk/{id}/rekening', [UpkController::class, 'Rekening']);
    Route::get('/migrasi_upk/{id}/rekening/insert', [UpkController::class, 'InsertRekening']);
    Route::get('/migrasi_upk/{id}/transaksi', [UpkController::class, 'Transaksi']);
    Route::get('/migrasi_upk/{id}/desa', [UpkController::class, 'Desa']);

    Route::resource('/migrasi_upk', UpkController::class);

    Route::post('/logout', [AdminAuthController::class, 'logout']);
});


Route::get('/kab', [KabupatenAuthController::class, 'index'])->middleware('guest');
Route::post('/kab/login', [KabupatenAuthController::class, 'login'])->middleware('guest');

Route::group(['prefix' => 'kab', 'as' => 'kab.', 'middleware' => 'kab'], function () {
    Route::get('/dashboard', [KabupatenController::class, 'index']);
    Route::get('/tanda_tangan', [KabupatenController::class, 'tandaTangan']);
    Route::post('/tanda_tangan/simpan', [KabupatenController::class, 'simpanTandaTangan']);

    Route::get('/simpan_saldo', [DashboardController::class, 'simpanSaldo']);
    Route::get('/kecamatan/{kd_kec}', [KabupatenController::class, 'kecamatan']);

    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::get('/laporan/sub_laporan/{laporan}/', [LaporanController::class, 'subLaporan']);
    Route::get('/laporan/data/{lokasi}/', [LaporanController::class, 'data']);
    Route::post('/laporan/preview/{kd_kab}', [LaporanController::class, 'preview']);

    Route::post('/logout', [KabupatenAuthController::class, 'logout']);
});

Route::get('/rekap', [RekapAuthController::class, 'index'])->middleware('guest');
Route::post('/rekap/login', [RekapAuthController::class, 'login'])->middleware('guest');

Route::group(['prefix' => 'rekap', 'as' => 'rekap.', 'middleware' => 'rekap'], function () {
    Route::get('/kecamatan/{kd_kec}', [RekapController::class, 'kecamatan']);
    Route::get('/dashboard', [RekapController::class, 'index']);
    Route::get('/laporan', [RekapController::class, 'laporan']);
    Route::get('/laporan/sub_laporan/{laporan}', [RekapController::class, 'subLaporan']);
    Route::get('/laporan/data/{lokasi}', [RekapController::class, 'data']);
    Route::post('/laporan/preview/{kd_kab}', [RekapController::class, 'preview']);

    Route::post('/logout', [RekapAuthController::class, 'logout']);
});

Route::get('/', [AuthController::class, 'index'])->middleware('guest')->name('/');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/app', [AuthController::class, 'app']);

Route::get('/excel', [ExcelController::class, 'index']);

Route::get('/pelaporan', [PelaporanController::class, 'index']);
Route::get('/pelaporan/sub_laporan/{file}', [PelaporanController::class, 'subLaporan']);
Route::post('/pelaporan/preview', [PelaporanController::class, 'preview']);
Route::post('/pelaporan/preview/{lokasi?}', [PelaporanController::class, 'preview']);

Route::get('/pelaporan/mou', [PelaporanController::class, 'mou'])->middleware('auth', 'is_aktif');
Route::get('/pelaporan/ts', [PelaporanController::class, 'ts'])->middleware('auth', 'is_aktif');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth', 'is_aktif');
Route::get('/kosong', [DashboardController::class, 'keluar']);
Route::get('/piutang_jasa', [DashboardController::class, 'piutang'])->middleware('auth', 'is_aktif');
Route::get('/pelaporan/invoice/{invoice}', [PelaporanController::class, 'invoice']);
Route::get('/simpan_saldo', [DashboardController::class, 'simpanSaldo']);

Route::post('/dashboard/jatuh_tempo', [DashboardController::class, 'jatuhTempo'])->middleware('auth', 'is_aktif');
Route::post('/dashboard/nunggak', [DashboardController::class, 'nunggak'])->middleware('auth', 'is_aktif');
Route::post('/dashboard/tagihan', [DashboardController::class, 'tagihan'])->middleware('auth', 'is_aktif');
Route::get('/dashboard/pinjaman', [DashboardController::class, 'pinjaman'])->middleware('auth', 'is_aktif');
Route::get('/dashboard/pemanfaat', [DashboardController::class, 'pemanfaat'])->middleware('auth', 'is_aktif');

Route::get('/pengaturan/sop', [SopController::class, 'index'])->middleware('auth', 'is_aktif');
Route::get('/pengaturan/coa', [SopController::class, 'coa'])->middleware('auth', 'is_aktif');
Route::get('/pengaturan/users', [SopController::class, 'users'])->middleware('auth', 'is_aktif');
Route::get('/pengaturan/ttd_pelaporan', [SopController::class, 'ttdPelaporan'])->middleware('auth', 'is_aktif');
Route::get('/pengaturan/ttd_spk', [SopController::class, 'ttdSpk'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/pesan_whatsapp/{kec}', [SopController::class, 'pesanWhatsapp'])->middleware('auth', 'is_aktif');

Route::put('/pengaturan/lembaga/{kec}', [SopController::class, 'lembaga'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/pengelola/{kec}', [SopController::class, 'pengelola'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/pinjaman/{kec}', [SopController::class, 'pinjaman'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/kolek/{kec}', [SopController::class, 'kolek'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/simpanan/{kec}', [SopController::class, 'simpanan'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/asuransi/{kec}', [SopController::class, 'asuransi'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/spk/{kec}', [SopController::class, 'spk'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/logo/{kec}', [SopController::class, 'logo'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/calk/{kec}', [SopController::class, 'calk'])->middleware('auth', 'is_aktif');
Route::put('/pengaturan/kustomisasi_calk/{kec}', [SopController::class, 'kustomisasiCalk'])->middleware('auth', 'is_aktif');

Route::post('/pengaturan/whatsapp/save_device', [SopController::class, 'save_whatsapp_session'])->middleware('auth');
Route::post('/pengaturan/whatsapp/delete_session', [SopController::class, 'delete_whatsapp_session'])->middleware('auth');
Route::post('/pengaturan/whatsapp/{token}', [SopController::class, 'whatsapp'])->middleware('auth', 'is_aktif');

Route::get('/pengaturan/invoice', [SopController::class, 'invoice'])->middleware('auth', 'is_aktif');
Route::get('/pengaturan/{inv}/invoice', [SopController::class, 'detailInvoice'])->middleware('auth', 'is_aktif');

Route::post('/pengaturan/sop/simpanttdpelaporan', [SopController::class, 'simpanTtdPelaporan'])->middleware('auth', 'is_aktif');

Route::get('/database/kelompok/register_kelompok', [KelompokController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/database/kelompok/generatekode', [KelompokController::class, 'generateKode'])->middleware('auth', 'is_aktif');
Route::get('/database/agent/generatekode', [AgentController::class, 'generateKode'])->middleware('auth', 'is_aktif');
Route::get('/database/supplier/generatekode', [AgentController::class, 'generateKode'])->middleware('auth', 'is_aktif');

Route::get('/database/penduduk/register_penduduk', [AnggotaController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/database/penduduk/cari_nik', [AnggotaController::class, 'cariNik'])->middleware('auth', 'is_aktif');

Route::post('/database/penduduk/{nik}/blokir', [AnggotaController::class, 'blokir'])->middleware('auth', 'is_aktif');

Route::get('/database/kelompok/detail_kelompok/{id}', [KelompokController::class, 'detailKelompok'])->middleware('auth', 'is_aktif');
Route::get('/database/anggota/detail_anggota/{id}', [AnggotaController::class, 'detailAnggota'])->middleware('auth', 'is_aktif');

Route::get('/database/supplier/', [SupplierController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/database/agent/', [AgentController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/database/saham/', [SahamController::class, 'register'])->middleware('auth', 'is_aktif');

Route::resource('/database/desa', DesaController::class)->middleware('auth', 'is_aktif');
Route::resource('/database/supplier', SupplierController::class)->middleware('auth', 'is_aktif');
Route::resource('/database/agent', AgentController::class)->middleware('auth', 'is_aktif');
Route::resource('/database/saham', SahamController::class)->middleware('auth', 'is_aktif');
Route::resource('/database/kelompok', KelompokController::class)->middleware('auth', 'is_aktif');
Route::resource('/database/penduduk', AnggotaController::class)->middleware('auth', 'is_aktif');

Route::get('/register_proposal', [PinjamanKelompokController::class, 'create'])->middleware('auth', 'is_aktif');
Route::get('/register_proposal/{id_kel}', [PinjamanKelompokController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/daftar_kelompok', [PinjamanKelompokController::class, 'DaftarKelompok'])->middleware('auth', 'is_aktif');

Route::get('/detail/{perguliran}', [PinjamanKelompokController::class, 'detail'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/proposal', [PinjamanKelompokController::class, 'proposal'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/verified', [PinjamanKelompokController::class, 'verified'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/waiting', [PinjamanKelompokController::class, 'waiting'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/aktif', [PinjamanKelompokController::class, 'aktif'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/lunas', [PinjamanKelompokController::class, 'lunas'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/generate/{id_pinj}', [PinjamanKelompokController::class, 'generate'])->middleware('auth', 'is_aktif');
Route::get('/lunas/{perguliran}', [PinjamanKelompokController::class, 'pelunasan'])->middleware('auth', 'is_aktif');
Route::get('/cetak_keterangan_lunas/{perguliran}', [PinjamanKelompokController::class, 'keterangan'])->middleware('auth', 'is_aktif');

Route::get('/perguliran/cari_kelompok', [PinjamanKelompokController::class, 'cariKelompok'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/cari_anggota', [PinjamanAnggotaController::class, 'cariAnggota'])->middleware('auth', 'is_aktif');

Route::post('/perguliran/simpan_data/{id}', [PinjamanKelompokController::class, 'simpan'])->middleware('auth', 'is_aktif');
Route::post('/perguliran/rescedule', [PinjamanKelompokController::class, 'rescedule'])->middleware('auth', 'is_aktif');
Route::post('/perguliran/hapus', [PinjamanKelompokController::class, 'hapus'])->middleware('auth', 'is_aktif');
Route::resource('/perguliran', PinjamanKelompokController::class)->middleware('auth', 'is_aktif');

Route::get('/anggota/{id}/kartu', [AnggotaController::class, 'kartu'])->name('anggota.kartu')->middleware('auth', 'is_aktif');

Route::get('/perguliran/dokumen/kartu_angsuran/{id}', [PinjamanKelompokController::class, 'kartuAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/dokumen/kartu_angsuran_i/{id}', [PinjamanAnggotaController::class, 'kartuAngsuranIndividu'])->middleware('auth', 'is_aktif');


Route::get('/perguliran/dokumen/kartu_angsuran/{id}/{idtp}', [PinjamanKelompokController::class, 'cetakPadaKartu'])->middleware('auth', 'is_aktif');

Route::get('/perguliran/dokumen/kartu_angsuran_anggota/{id}/{nia?}', [PinjamanKelompokController::class, 'kartuAngsuranAnggota'])->middleware('auth', 'is_aktif');
Route::get('/perguliran/dokumen/kartu_angsuran_anggota_i/{id}/{nia?}', [PinjamanAnggotaController::class, 'kartuAngsuranAnggotaIndividu'])->middleware('auth', 'is_aktif');


Route::get('/perguliran/dokumen/cetak_kartu_angsuran_anggota/{id}/{idtp}/{nia?}', [PinjamanKelompokController::class, 'cetakKartuAngsuranAnggota'])->middleware('auth', 'is_aktif');

Route::post('/perguliran/dokumen', [PinjamanKelompokController::class, 'dokumen'])->middleware('auth', 'is_aktif');

Route::post('/perguliran/kembali_proposal/{id}', [PinjamanKelompokController::class, 'kembaliProposal'])->middleware('auth', 'is_aktif');
Route::post('/perguliran_i/waiting_edit_jaminan/{pinjaman}', [PinjamanIndividuController::class, 'Waiting_Jaminan'])->middleware('auth', 'is_aktif');

Route::get('/register_proposal_i', [PinjamanIndividuController::class, 'create'])->middleware('auth', 'is_aktif');
Route::get('/register_proposal_i/{nia}', [PinjamanIndividuController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/register_proposal_i/jenis_pinjaman/{id}', [PinjamanIndividuController::class, 'JenisProdukPinjam'])->middleware('auth', 'is_aktif');
Route::get('/register_proposal_i/jaminan/{id}', [PinjamanIndividuController::class, 'Jaminan'])->middleware('auth', 'is_aktif');
Route::get('/daftar_individu', [PinjamanIndividuController::class, 'DaftarAnggota'])->middleware('auth', 'is_aktif');

Route::get('/detail_i/{perguliran_i}', [PinjamanIndividuController::class, 'detail'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/proposal', [PinjamanIndividuController::class, 'proposal'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/jaminan/{id}', [PinjamanIndividuController::class, 'EditJaminan'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/waitingjaminan/{id}', [PinjamanIndividuController::class, 'Waiting_Edit_Jaminan'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/verified', [PinjamanIndividuController::class, 'verified'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/waiting', [PinjamanIndividuController::class, 'waiting'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/aktif', [PinjamanIndividuController::class, 'aktif'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/lunas', [PinjamanIndividuController::class, 'lunas'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/generate/{id_pinj}', [PinjamanIndividuController::class, 'generate'])->middleware('auth', 'is_aktif');
Route::get('/lunas_i/{perguliran_i}', [PinjamanIndividuController::class, 'pelunasan'])->middleware('auth', 'is_aktif');
Route::get('/cetak_keterangan_lunas_i/{perguliran_i}', [PinjamanIndividuController::class, 'keterangan'])->middleware('auth', 'is_aktif');

Route::get('/perguliran_i/cari_kelompok', [PinjamanIndividuController::class, 'cariKelompok'])->middleware('auth', 'is_aktif');
Route::post('/perguliran_i/simpan_data/{id}', [PinjamanIndividuController::class, 'simpan'])->middleware('auth', 'is_aktif');
Route::post('/perguliran_i/rescedule', [PinjamanIndividuController::class, 'rescedule'])->middleware('auth', 'is_aktif');
Route::post('/perguliran_i/hapus', [PinjamanIndividuController::class, 'hapus'])->middleware('auth', 'is_aktif');
Route::resource('/perguliran_i', PinjamanIndividuController::class)->middleware('auth', 'is_aktif');

Route::get('/perguliran_i/dokumen/kartu_angsuran/{id}', [PinjamanIndividuController::class, 'kartuAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/perguliran_i/dokumen/kartu_angsuran/{id}/{idtp}', [PinjamanIndividuController::class, 'cetakPadaKartu'])->middleware('auth', 'is_aktif');

Route::get('/perguliran_i/dokumen/kartu_angsuran_anggota/{id}/{nia?}', [PinjamanIndividuController::class, 'kartuAngsuranAnggota'])->middleware('auth', 'is_aktif');

Route::get('/perguliran_i/dokumen/cetak_kartu_angsuran_anggota/{id}/{idtp}/{nia?}', [PinjamanIndividuController::class, 'cetakKartuAngsuranAnggota'])->middleware('auth', 'is_aktif');

Route::post('/perguliran_i/dokumen', [PinjamanIndividuController::class, 'dokumen'])->middleware('auth', 'is_aktif');

Route::post('/perguliran_i/kembali_proposal/{id}', [PinjamanIndividuController::class, 'kembaliProposal'])->middleware('auth', 'is_aktif');
Route::post('/perguliran_i/tdklayak/{id}', [PinjamanIndividuController::class, 'tdklayak'])->middleware('auth', 'is_aktif');
Route::post('/perguliran_i/kembali_verifikasi/{id}', [PinjamanIndividuController::class, 'kembaliverifikasi'])->middleware('auth', 'is_aktif');

Route::get('/pinjaman_anggota/register/{id_pinkel}', [PinjamanAnggotaController::class, 'create'])->middleware('auth', 'is_aktif');
Route::get('/pinjaman_anggota/cari_pemanfaat', [PinjamanAnggotaController::class, 'cariPemanfaat'])->middleware('auth', 'is_aktif');
Route::get('/hapus_pemanfaat/{id}', [PinjamanAnggotaController::class, 'hapus'])->middleware('auth', 'is_aktif');

Route::get('/pinjaman_anggota/form_hapus/{pinj}', [PinjamanAnggotaController::class, 'form_penghapusan'])->middleware('auth', 'is_aktif');

Route::resource('/pinjaman_anggota', PinjamanAnggotaController::class)->middleware('auth', 'is_aktif');

Route::post('/lunaskan_pemanfaat/{pinjaman}', [PinjamanAnggotaController::class, 'lunaskan'])->middleware('auth', 'is_aktif');
Route::post('/hapus_pemanfaat/{pinjaman}', [PinjamanAnggotaController::class, 'penghapusan'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/jurnal_umum', [TransaksiController::class, 'jurnalUmum'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/jurnal_angsuran', [TransaksiController::class, 'jurnalAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/jurnal_angsuran_individu', [TransaksiController::class, 'jurnalAngsuranIndividu'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/tutup_buku', [TransaksiController::class, 'jurnalTutupBuku'])->middleware('auth', 'is_aktif');
Route::get('/trasaksi/saldo/{kode_akun}', [TransaksiController::class, 'saldo'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/ambil_rekening/{id}', [TransaksiController::class, 'rekening'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/form_nominal/', [TransaksiController::class, 'form'])->middleware('auth');
Route::get('/transaksi/form_angsuran/{id_pinkel}', [TransaksiController::class, 'formAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/form_angsuran_individu/{id_pinkel}', [TransaksiController::class, 'formAngsuranIndividu'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/angsuran/target/{id_pinkel}', [TransaksiController::class, 'targetAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/angsuran_individu/target/{id_pinkel}', [TransaksiController::class, 'targetAngsuranIndividu'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/angsuran_individu', [TransaksiController::class, 'angsuranIndividu']);

Route::get('/transaksi/data/{idt}', [TransaksiController::class, 'data'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/tutup_buku/saldo_awal/{tahun}', [TransaksiController::class, 'saldoAwal'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/tutup_buku/saldo', [TransaksiController::class, 'saldoTutupBuku'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/tutup_buku', [TransaksiController::class, 'simpanTutupBuku'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/simpan_laba', [TransaksiController::class, 'simpanAlokasiLaba'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/reversal', [TransaksiController::class, 'reversal'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/hapus', [TransaksiController::class, 'hapus'])->middleware('auth', 'is_aktif');
Route::post('/Saham/hapus', [SahamController::class, 'hapus'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/angsuran/lpp/{id}', [TransaksiController::class, 'lpp'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/angsuran_i/lpp/{id}', [TransaksiController::class, 'lppIndividu'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/angsuran/detail_angsuran/{id}', [TransaksiController::class, 'detailAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/angsuran/detail_angsuran_i/{id}', [TransaksiController::class, 'detailAngsuranIndividu'])->middleware('auth', 'is_aktif');


Route::get('/transaksi/detail_transaksi/', [TransaksiController::class, 'detailTransaksi'])->middleware('auth', 'is_aktif');

Route::post('/transaksi/angsuran', [TransaksiController::class, 'angsuran'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/angsuran/cetak_bkm', [TransaksiController::class, 'cetakBkm'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/generate_real/{id_pinkel}', [TransaksiController::class, 'generateReal'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/regenerate_real/{id_pinkel}', [TransaksiController::class, 'realisasi'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/angsuran/form_anggota/{id_pinkel}', [TransaksiController::class, 'formAnggota'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/angsuran/form_anggota_i/{id_pinkel}', [TransaksiController::class, 'formAnggotaIndividu'])->middleware('auth', 'is_aktif');

Route::get('/angsuran/notifikasi/{idtp}', [TransaksiController::class, 'notifikasi'])->middleware('auth', 'is_aktif');
Route::get('/angsuran/notifikasi_i/{idtp}', [TransaksiController::class, 'notifikasiIndividu'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/dokumen/kuitansi/{id}', [TransaksiController::class, 'kuitansi'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/kuitansi_thermal/{id}', [TransaksiController::class, 'kuitansi_thermal'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/bkk/{id}', [TransaksiController::class, 'bkk'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/bkm/{id}', [TransaksiController::class, 'bkm'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/bm/{id}', [TransaksiController::class, 'bm'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/dokumen/struk/{id}', [TransaksiController::class, 'struk'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/struk_matrix/{id}', [TransaksiController::class, 'strukMatrix'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/dokumen/struk_individu/{id}', [TransaksiController::class, 'struk_individu'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/struk_matrix_individu/{id}', [TransaksiController::class, 'strukMatrixIndividu'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/struk_thermal_individu/{id}', [TransaksiController::class, 'strukThermalIndividu'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/dokumen/cetak', [TransaksiController::class, 'cetak'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/dokumen/struk/{id}', [TransaksiController::class, 'struk'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/struk_matrix/{id}', [TransaksiController::class, 'strukMatrix'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/struk_thermal/{id}', [TransaksiController::class, 'strukThermal'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/bkm_angsuran/{id}', [TransaksiController::class, 'bkmAngsuran'])->middleware('auth', 'is_aktif');
Route::get('/transaksi/dokumen/bkk_angsuran/{id}', [TransaksiController::class, 'bkkAngsuran'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/dokumen/cetak', [TransaksiController::class, 'cetak'])->middleware('auth', 'is_aktif');

Route::get('/transaksi/ebudgeting', [TransaksiController::class, 'ebudgeting'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/anggaran', [TransaksiController::class, 'formAnggaran'])->middleware('auth', 'is_aktif');
Route::post('/transaksi/simpan_anggaran', [TransaksiController::class, 'simpanAnggaran'])->middleware('auth', 'is_aktif');

Route::resource('/transaksi', TransaksiController::class)->middleware('auth', 'is_aktif');

Route::resource('/profil', UserController::class);

Route::get('/sync/{lokasi}', [DashboardController::class, 'sync'])->middleware('auth', 'is_aktif');
Route::get('/link', function () {
    $target = '/home/siupk/public_html/lkm/storage/app/public';
    $shortcut = '/home/siupk/public_html/lkm/public/storage';
    symlink($target, $shortcut);
});

Route::get('/uname', function () {
    $host = request()->getHost();
    $kec = Kecamatan::where('web_kec', request()->getHost())->orwhere('web_alternatif', request()->getHost())->with('kabupaten')->first();
    $users = User::where('lokasi', $kec->id)->with('l', 'j')->orderBy('level', 'ASC')->orderBy('jabatan', 'ASC')->get();

    Session::put('login', true);
    $http = 'http';
    if (request()->secure()) {
        $http .= 's';
    }

    return view('welcome', ['users' => $users, 'kec' => $kec, 'host' => $host, 'http' => $http]);
});

Route::get('/download/{file}', function ($file) {
    return response()->download(storage_path('app/public/docs/' . $file));
})->name('download');

Route::get('/unpaid', [DashboardController::class, 'unpaid'])->middleware('auth', 'is_aktif');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::get('/generate', [GenerateController::class, 'index']);
Route::get('/generate/individu', [GenerateController::class, 'individu']);
Route::get('/generate/kelompok', [GenerateController::class, 'kelompok']);
Route::get('/generate/agent', [GenerateController::class, 'agent']);
Route::get('/generate/supplier', [GenerateController::class, 'agent']);
Route::post('/generate/save/{offset?}', [GenerateController::class, 'generate']);
Route::get('/generate_simpanan', [GenerateController::class, 'simpanan']);

Route::get('/simpanan/cari_nik', [SimpananController::class, 'cariNik'])->middleware('auth', 'is_aktif');

Route::post('/simpanan/{nik}/blokir', [SimpananController::class, 'blokir'])->middleware('auth', 'is_aktif');

Route::get('/simpanan/detail_simpanan/{id}', [SimpananController::class, 'detailAnggota'])->middleware('auth', 'is_aktif');

Route::get('/register_simpanan', [SimpananController::class, 'create'])->middleware('auth', 'is_aktif');
Route::get('/register_simpanan/{nia}', [SimpananController::class, 'register'])->middleware('auth', 'is_aktif');
Route::get('/register_simpanan/jenis_simpanan/{id}', [SimpananController::class, 'jenis_simpanan'])->middleware('auth', 'is_aktif');

Route::get('/simpanan/kuasa/{id}', [SimpananController::class, 'Kuasa'])->middleware('auth', 'is_aktif');

Route::get('/cetak_kop/{simpanan}', [SimpananController::class, 'kop'])->middleware('auth', 'is_aktif');
Route::get('/cetak_formulir/{simpanan?}', [SimpananController::class, 'cetakFormulir'])->middleware('auth', 'is_aktif');

Route::get('/cetak_koran/{simpanan}', [SimpananController::class, 'koran'])->middleware('auth', 'is_aktif');

Route::get('/cetak_sertifikat/{simpanan}', [SimpananController::class, 'cetakSertifikat'])->middleware('auth', 'is_aktif');

Route::get('/cetak_koran/{simpanan}/{bln}/{thn}', [SimpananController::class, 'koran_dtl'])->middleware('auth', 'is_aktif');

Route::get('/cetak_buku/{idt}', [SimpananController::class, 'cetakPadaBuku'])->middleware('auth');
Route::get('/cetak_kuitansi/{idt}', [SimpananController::class, 'cetakKwitansi'])->middleware('auth');

Route::get('/qr/{filename}', function ($filename) {
    $path = storage_path('app/public/qr/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});

Route::get('/simpanan/get-transaksi', [SimpananController::class, 'getTransaksi'])->middleware('auth', 'is_aktif');

Route::post('/simpanan/simpan-transaksi', [SimpananController::class, 'simpanTransaksi']);
Route::post('/simpanan/generate/{cif}', [SimpananController::class, 'generateSimpanan'])->middleware('auth', 'is_aktif');
Route::resource('/simpanan', SimpananController::class)->middleware('auth', 'is_aktif');
Route::get('/bunga', [SimpananController::class, 'bunga'])->middleware('auth', 'is_aktif');
Route::get('/bunga/info', [SimpananController::class, 'infoBunga'])->middleware('auth', 'is_aktif');
Route::get('/simpan_bunga', [SimpananController::class, 'simpanBunga'])->middleware('auth');

Route::get('/{invoice}', [PelaporanController::class, 'invoice']);
