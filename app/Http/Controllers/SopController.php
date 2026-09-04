<?php

namespace App\Http\Controllers;

use App\Models\AdminInvoice;
use App\Models\AkunLevel1;
use App\Models\Kecamatan;
use App\Models\TandaTanganDokumen;
use App\Models\DokumenPinjaman;
use App\Models\User;
use App\Models\Whatsapp;
use App\Utils\Pinjaman;
use App\Utils\QrTtdHelper;
use App\Utils\Tanggal;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Session;
use Yajra\DataTables\DataTables;

class SopController extends Controller
{
    public function index()
    {
        $api = env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com');
        $api_key = env('WA_GATEWAY_API_KEY');

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('ttd', 'wa_session')->first();
        $token = "LKM-" . str_replace('.', '', $kec->kd_kec) . '-' . str_pad($kec->id, 4, '0', STR_PAD_LEFT);
        $keywordSPK = Pinjaman::spk();
        $fungsiSPK = Pinjaman::fungsi();

        $instance_name = $kec->wa_session->instance_name ?? null;

        $title = "Personalisasi SOP";
        return view('sop.index')->with(compact('title', 'kec', 'api', 'token', 'keywordSPK', 'fungsiSPK', 'api_key', 'instance_name'));
    }

    public function users()
    {

        $kec = Kecamatan::where('id', Session::get('lokasi'))->with('ttd')->first();

        $dir = User::where([
            ['jabatan', '1'],
            ['level', '1']
        ])->first();

        $seke = User::where([
            ['jabatan', '1'],
            ['level', '2']
        ])->first();

        $bend = User::where([
            ['jabatan', '1'],
            ['level', '3']
        ])->first();

        $manaj = User::where([
            ['jabatan', '1'],
            ['level', '7']
        ])->first();

        $title = "Users aplikasi";
        return view('sop.users')->with(compact('title', 'kec', 'dir', 'seke', 'bend', 'manaj'));
    }

    public function coa()
    {
        $title = "Chart Of Account (CoA)";
        $akun1 = AkunLevel1::with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek'
        ])->get();

        return view('sop.coa')->with(compact('title', 'akun1'));
    }

    public function lembaga(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'nama_bumdesma',
            'nomor_badan_hukum',
            'telpon',
            'email',
            'alamat',
            'peraturan_desa',
        ]);

        $validate = Validator::make($data, [
            'nama_bumdesma' => 'required',
            'nomor_badan_hukum' => 'required',
            'telpon' => 'required',
            'email' => 'required',
            'alamat' => 'required',
            'peraturan_desa' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $calk = [
            'peraturan_desa' => $request->peraturan_desa,
            "D" => [
                "1" => [
                    "d" => [
                        "1" => 0,
                        "2" => 0,
                        "3" => 0
                    ]
                ],
                "2" => [
                    "a" => 0,
                    "b" => 0,
                    "c" => 0
                ]
            ]
        ];

        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'nama_lembaga_sort' => ucwords(strtolower($data['nama_bumdesma'])),
            'nama_lembaga_long' => ucwords(strtolower($data['nama_bumdesma'])),
            'nomor_bh' => $data['nomor_badan_hukum'],
            'telpon_kec' => $data['telpon'],
            'email_kec' => $data['email'],
            'alamat_kec' => $data['alamat'],
            'calk' => json_encode($calk),
        ]);

        Session::put('nama_lembaga', ucwords(strtolower($data['nama_bumdesma'])));

        return response()->json([
            'success' => true,
            'msg' => 'Identitas Lembaga Berhasil Diperbarui.',
            'nama_lembaga' => ucwords(strtolower($data['nama_bumdesma']))
        ]);
    }

    public function pengelola(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'sebutan_pengawas',
            'sebutan_verifikator',
            'kepala_lembaga',
            'kabag_administrasi',
            'kabag_keuangan',
            'bkk_bkm'
        ]);

        $validate = Validator::make($data, [
            'sebutan_pengawas' => 'required',
            'sebutan_verifikator' => 'required',
            'kepala_lembaga' => 'required',
            'kabag_administrasi' => 'required',
            'kabag_keuangan' => 'required',
            'bkk_bkm' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'nama_bp_long' => ucwords(strtolower($data['sebutan_pengawas'])),
            'nama_bp_sort' => ucwords(strtolower($data['sebutan_pengawas'])),
            'nama_tv_long' => ucwords(strtolower($data['sebutan_verifikator'])),
            'nama_tv_sort' => ucwords(strtolower($data['sebutan_verifikator'])),
            'sebutan_level_1' => ucwords(strtolower($data['kepala_lembaga'])),
            'sebutan_level_2' => ucwords(strtolower($data['kabag_administrasi'])),
            'sebutan_level_3' => ucwords(strtolower($data['kabag_keuangan'])),
            'disiapkan' => ucwords(strtolower($data['bkk_bkm']))
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Sebutan Pengelola Berhasil Diperbarui.',
        ]);
    }

    public function pinjaman(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'default_jasa',
            'default_jangka',
            'pembulatan',
            'sistem',
            'def_fee_supp',
            'def_fee_agen',
            'hit_fee_agen',
            'jdwl_angsuran',
            'hak_kredit',
            'provisi',
            'def_admin',
            'def_depe'
        ]);

        $validate = Validator::make($data, [
            'default_jasa'      => 'required',
            'default_jangka'    => 'required',
            'pembulatan'        => 'required',
            'def_fee_supp'      => 'required',
            'def_fee_agen'      => 'required',
            'hit_fee_agen'      => 'required',
            'jdwl_angsuran'     => 'required',
            'hak_kredit'        => 'required',
            'provisi'           => 'required',
            'def_admin'         => 'required',
            'def_depe'          => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $data['pembulatan'] = "$data[sistem]$data[pembulatan]";

        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'def_jasa'      => $data['default_jasa'],
            'def_jangka'    => $data['default_jangka'],
            'pembulatan'    => $data['pembulatan'],
            'def_fee_supp'  => $data['def_fee_supp'],
            'def_fee_agen'  => $data['def_fee_agen'],
            'hit_fee_agen' => $data['hit_fee_agen'],
            'jdwl_angsuran' => $data['jdwl_angsuran'],
            'hak_kredit'    => $data['hak_kredit'],
            'provisi'       => $data['provisi'],
            'def_admin'     => $data['def_admin'],
            'def_depe'      => $data['def_depe'],
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Sistem Pinjaman Berhasil Diperbarui.',
        ]);
    }

    public function kolek(Request $request, Kecamatan $kec)
    {
        $rules = [];
        $kolekData = [];

        // looping untuk 5 tingkat
        for ($i = 1; $i <= 5; $i++) {
            $rules["nama_kolek{$i}"] = 'nullable|string';
            $rules["pros_kolek{$i}"] = 'nullable|numeric';
            $rules["durasi{$i}"]     = 'nullable|numeric';
            $rules["satuan{$i}"]     = 'nullable|string';

            $kolekData[] = [
                'nama'       => $request->input("nama_kolek{$i}"),
                'prosentase' => $request->input("pros_kolek{$i}"),
                'durasi'     => $request->input("durasi{$i}"),
                'satuan'     => $request->input("satuan{$i}"),
            ];
        }

        // validasi
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_BAD_REQUEST);
        }
        
        
        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'kolek' => json_encode($kolekData)
        ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Kolek Berhasil Diperbarui.',
        ]);
    }

    public function simpanan(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'hitung_bunga',
            'tgl_bunga',
            'min_bunga',
            'min_pajak',
            'def_bunga',
            'def_pajak',
            'def_admin_buka',
            'def_admin_simp'
        ]);

        $validate = Validator::make($data, [
            'hitung_bunga'   => 'required',
            'tgl_bunga'      => 'required',
            'min_bunga'      => 'required',
            'min_pajak'      => 'required',
            'def_bunga'      => 'required',
            'def_pajak'      => 'required',
            'def_admin_buka' => 'required',
            'def_admin_simp' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'hitung_bunga'         => $data['hitung_bunga'],
            'tgl_bunga'         => $data['tgl_bunga'],
            'min_bunga'         => $data['min_bunga'],
            'min_pajak'         => $data['min_pajak'],
            'def_bunga'         => $data['def_bunga'],
            'def_pajak'         => $data['def_pajak'],
            'def_admin_buka'    => $data['def_admin_buka'],
            'def_admin_simp'    => $data['def_admin_simp']
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Sistem Simpanan Berhasil Diperbarui.',
        ]);
    }

    public function asuransi(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'nama_asuransi',
            'jenis_asuransi',
            'usia_maksimal',
            'presentase_premi',
        ]);

        $validate = Validator::make($data, [
            'nama_asuransi' => 'required',
            'jenis_asuransi' => 'required',
            'usia_maksimal' => 'required',
            'presentase_premi' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'nama_asuransi_p' => $data['nama_asuransi'],
            'pengaturan_asuransi' => $data['jenis_asuransi'],
            'usia_mak' => $data['usia_maksimal'],
            'besar_premi' => $data['presentase_premi'],
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Pengaturan Asuransi Berhasil Diperbarui.',
        ]);
    }

    public function spk(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'spk'
        ]);

        $validate = Validator::make($data, [
            'spk' => 'required'
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $spk = json_encode($data['spk']);
        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'redaksi_spk' => $spk
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Redaksi Dokumen SPK Berhasil Diperbarui.',
        ]);
    }

    public function kustomisasiCalk(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'custom_calk'
        ]);
        $validate = Validator::make($data, [
            'custom_calk' => 'required'
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $custom_calk = json_encode($data['custom_calk']);
        $kecamatan = Kecamatan::where('id', $kec->id)->update([
            'custom_calk' => $custom_calk
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Custom CALK Berhasil Disimpan.',
        ]);
    }

    public function logo(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'logo_kec'
        ]);

        $validate = Validator::make($data, [
            'logo_kec' => 'required|image|mimes:jpg,png,jpeg|max:4096'
        ]);

        if ($request->file('logo_kec')->isValid()) {
            $extension = $request->file('logo_kec')->getClientOriginalExtension();

            $filename = time() . '_' . $kec->id . '_' . date('Ymd') . '.' . $extension;
            $path = $request->file('logo_kec')->storeAs('logo', $filename, 'public');

            if (Storage::exists('logo/' . $kec->logo)) {
                if ($kec->logo != '1.png') {
                    Storage::delete('logo/' . $kec->logo);
                }
            }

            $kecamatan = Kecamatan::where('id', $kec->id)->update([
                'logo' => str_replace('logo/', '', $path)
            ]);

            Session::put('logo', str_replace('logo/', '', $path));
            return response()->json([
                'success' => true,
                'msg' => 'Logo berhasil diperbarui.'
            ]);
        }

        return response()->json([
            'success' => false,
            'msg' => 'Logo gagal diperbarui'
        ]);
    }

    public function hapusTtdQr(Request $request, Kecamatan $kec)
    {
        $dir = QrTtdHelper::DIRECTORY;

        $deleted = 0;
        foreach (QrTtdHelper::EXTENSIONS as $ext) {
            foreach (['', QrTtdHelper::NAME_SUFFIX] as $suffix) {
                $existing = "{$dir}/{$kec->id}{$suffix}.{$ext}";
                if (Storage::disk('public')->exists($existing)) {
                    Storage::disk('public')->delete($existing);
                    $deleted++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'msg'    => $deleted > 0
                ? 'Gambar tanda tangan berhasil dihapus.'
                : 'Tidak ada gambar tanda tangan untuk dihapus.',
        ]);
    }

    public function saveTtdQr(Request $request, Kecamatan $kec)
    {
        $disk   = Storage::disk('public');
        $dir    = QrTtdHelper::DIRECTORY;
        $lokasi = $kec->id;
        $desired = $request->boolean('dengan_nama');

        $hasExisting = false;
        foreach (QrTtdHelper::EXTENSIONS as $ext) {
            foreach (['', QrTtdHelper::NAME_SUFFIX] as $suffix) {
                if ($disk->exists("{$dir}/{$lokasi}{$suffix}.{$ext}")) {
                    $hasExisting = true;
                    break 2;
                }
            }
        }

        $rules = [
            'dengan_nama' => 'nullable|boolean',
        ];
        $messages = [];

        if (!$hasExisting) {
            $rules['gambar_ttd'] = 'required|image|mimes:jpg,jpeg,png|max:4096';
            $messages['gambar_ttd.required'] = 'Pilih gambar tanda tangan terlebih dahulu.';
            $messages['gambar_ttd.image']    = 'File harus berupa gambar.';
            $messages['gambar_ttd.mimes']    = 'Format yang didukung: JPG, JPEG, PNG.';
            $messages['gambar_ttd.max']      = 'Ukuran maksimum 4MB.';
        } else {
            $rules['gambar_ttd'] = 'nullable|image|mimes:jpg,jpeg,png|max:4096';
            $messages['gambar_ttd.image']    = 'File harus berupa gambar.';
            $messages['gambar_ttd.mimes']    = 'Format yang didukung: JPG, JPEG, PNG.';
            $messages['gambar_ttd.max']      = 'Ukuran maksimum 4MB.';
        }

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'msg'    => $validate->errors()->first(),
            ], 422);
        }

        $disk->makeDirectory($dir);

        $uploadedFile = $request->file('gambar_ttd');
        $willUpload   = $uploadedFile && $uploadedFile->isValid();

        if ($willUpload) {
            foreach (QrTtdHelper::EXTENSIONS as $ext) {
                foreach (['', QrTtdHelper::NAME_SUFFIX] as $suffix) {
                    $existing = "{$dir}/{$lokasi}{$suffix}.{$ext}";
                    if ($disk->exists($existing)) {
                        $disk->delete($existing);
                    }
                }
            }

            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            if (!in_array($extension, QrTtdHelper::EXTENSIONS, true)) {
                $extension = 'jpeg';
            }

            $suffix   = $desired ? QrTtdHelper::NAME_SUFFIX : '';
            $filename = "{$lokasi}{$suffix}.{$extension}";
            $path     = $uploadedFile->storeAs($dir, $filename, 'public');

            return response()->json([
                'success'   => true,
                'msg'       => 'Gambar tanda tangan berhasil diunggah.',
                'url'       => '/storage/' . $path,
                'path'      => $path,
                'with_name' => $desired,
                'uploaded'  => true,
            ]);
        }

        $disk = Storage::disk('public');
        $currentPath = null;
        $currentExt  = null;
        foreach (QrTtdHelper::EXTENSIONS as $ext) {
            $candidate = "{$dir}/{$lokasi}-name.{$ext}";
            if ($disk->exists($candidate)) {
                $currentPath = $candidate;
                $currentExt  = $ext;
                break;
            }
            $candidate = "{$dir}/{$lokasi}.{$ext}";
            if ($disk->exists($candidate)) {
                $currentPath = $candidate;
                $currentExt  = $ext;
                break;
            }
        }

        if ($currentPath === null) {
            return response()->json([
                'success' => false,
                'msg'    => 'Tidak ada gambar untuk diubah.',
            ], 422);
        }

        $alreadyHasName = str_contains(basename($currentPath), QrTtdHelper::NAME_SUFFIX);

        if ($alreadyHasName === $desired) {
            return response()->json([
                'success'   => true,
                'msg'       => 'Tidak ada perubahan yang perlu disimpan.',
                'with_name' => $desired,
                'uploaded'  => false,
                'noop'      => true,
            ]);
        }

        $targetName = $desired
            ? "{$lokasi}-name.{$currentExt}"
            : "{$lokasi}.{$currentExt}";

        $targetPath = "{$dir}/{$targetName}";

        if ($disk->exists($targetPath) && $targetPath !== $currentPath) {
            $disk->delete($targetPath);
        }

        $disk->move($currentPath, $targetPath);

        return response()->json([
            'success'   => true,
            'msg'       => $desired
                ? 'Nama penandatangan akan disertakan di bawah gambar.'
                : 'Nama penandatangan dihilangkan dari blok tanda tangan.',
            'with_name' => $desired,
            'path'      => $targetPath,
            'uploaded'  => false,
        ]);
    }
    public function whatsapp($token)
    {
        return response()->json([
            'success' => true,
            'msg' => 'Legacy endpoint disabled. Use Evolution API endpoints.',
        ]);
    }

    public function ttdDokumen(Request $request)
    {
        $title = "Pengaturan Tanda Tangan Dokumen";
        $lokasi = Session::get('lokasi');

        $statis = TandaTanganDokumen::daftarJenis();
        $pinjaman = TandaTanganDokumen::daftarJenisDokumenPinjaman();

        $daftarJenis = array_merge($statis, $pinjaman);

        if (empty($daftarJenis)) {
            $jenis = null;
        } else {
            $jenis = $request->get('jenis');
            if (!$jenis || !array_key_exists($jenis, $daftarJenis)) {
                $jenis = array_key_first($daftarJenis);
            }
        }

        $kec = Kecamatan::where('id', $lokasi)->first();
        $ttd = $jenis
            ? TandaTanganDokumen::where('lokasi', $lokasi)->where('jenis', $jenis)->first()
            : null;

        $tanggal = false;
        if ($ttd) {
            $str = strpos((string) $ttd->tanda_tangan, '{tanggal}');
            if ($str !== false) {
                $tanggal = true;
            }
        }

        $keyword = Pinjaman::keyword();
        $kec->load(['ttd', 'ttdSpk']);

        return view('sop.partials.ttd_dokumen')->with(compact('title', 'kec', 'ttd', 'tanggal', 'jenis', 'daftarJenis', 'keyword', 'statis', 'pinjaman'));
    }

    public function resetTtdDokumen(Request $request)
    {
        $jenis = $request->get('jenis');
        $lokasi = Session::get('lokasi');

        $allowed = array_merge(
            array_keys(TandaTanganDokumen::daftarJenis()),
            array_keys(TandaTanganDokumen::daftarJenisDokumenPinjaman())
        );

        if (!$jenis || !in_array($jenis, $allowed, true)) {
            return response()->json(['success' => false, 'msg' => 'Jenis dokumen tidak valid'], 422);
        }

        $deleted = TandaTanganDokumen::where('lokasi', $lokasi)
                                     ->where('jenis', $jenis)
                                     ->delete();

        return response()->json([
            'success' => true,
            'msg' => $deleted
                ? 'Tanda Tangan ' . ucwords(str_replace('_', ' ', $jenis)) . ' berhasil direset'
                : 'Tidak ada tanda tangan untuk jenis ' . ucwords(str_replace('_', ' ', $jenis)),
        ]);
    }

    public function ttdDokumenData(Request $request)
    {
        $jenis = $request->get('jenis', 'laporan');

        $allowed = array_merge(
            array_keys(TandaTanganDokumen::daftarJenis()),
            array_keys(TandaTanganDokumen::daftarJenisDokumenPinjaman())
        );

        if (!in_array($jenis, $allowed, true)) {
            return response()->json(['success' => false, 'msg' => 'Jenis dokumen tidak valid'], 422);
        }

        $ttd = TandaTanganDokumen::where('lokasi', Session::get('lokasi'))
                                 ->where('jenis', $jenis)
                                 ->first();

        $tanggal = false;
        if ($ttd) {
            $str = strpos((string) $ttd->tanda_tangan, '{tanggal}');
            if ($str !== false) {
                $tanggal = true;
            }
        }

        return response()->json([
            'success'     => true,
            'jenis'       => $jenis,
            'tanda_tangan' => $ttd ? json_decode($ttd->tanda_tangan, true) : '',
            'tanggal'     => $tanggal,
        ]);
    }

    public function simpanTtdPelaporan(Request $request)
    {
        $data = $request->only([
            'field',
            'jenis',
            'tanda_tangan'
        ]);

        $allowed = array_merge(
            array_keys(TandaTanganDokumen::daftarJenis()),
            array_keys(TandaTanganDokumen::daftarJenisDokumenPinjaman())
        );

        $jenis = $data['jenis'] ?? null;
        if (!$jenis || !in_array($jenis, $allowed, true)) {
            if ($data['field'] == 'tanda_tangan_pelaporan') {
                $jenis = 'laporan';
            } else {
                $jenis = 'spk';
            }
        }

        $fontSize = ($jenis === 'laporan') ? '11px' : '12px';
        $data['tanda_tangan'] = preg_replace('/<table[^>]*>/', '<table class="p0" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: ' . $fontSize . ';">', $data['tanda_tangan'], 1);
        $data['tanda_tangan'] = preg_replace('/height:\s*[^;]+;?/', '', $data['tanda_tangan']);

        $data['tanda_tangan'] = str_replace('colgroup', 'tr', $data['tanda_tangan']);
        $data['tanda_tangan'] = preg_replace('/<col([^>]*)>/', '<td$1>&nbsp;</td>', $data['tanda_tangan']);

        $ttd = TandaTanganDokumen::where('lokasi', Session::get('lokasi'))
                                 ->where('jenis', $jenis)
                                 ->first();
        if (!$ttd) {
            $insert = [
                'lokasi' => Session::get('lokasi'),
                'jenis' => $jenis,
                'tanda_tangan' => json_encode($data['tanda_tangan'])
            ];

            $tanda_tangan = TandaTanganDokumen::create($insert);
        } else {
            $tanda_tangan = TandaTanganDokumen::where('lokasi', Session::get('lokasi'))
                                              ->where('jenis', $jenis)
                                              ->update([
                                                  'tanda_tangan' => json_encode($data['tanda_tangan'])
                                              ]);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Tanda Tangan ' . ucwords(str_replace('_', ' ', $jenis)) . ' Berhasil diperbarui'
        ]);
    }

    public function invoice()
    {
        if (request()->ajax()) {
            $invoice = AdminInvoice::where('lokasi', Session::get('lokasi'))->with('jp')->withSum('trx', 'jumlah')->get();

            return DataTables::of($invoice)
                ->editColumn('tgl_invoice', function ($row) {
                    return Tanggal::tglIndo($row->tgl_invoice);
                })
                ->editColumn('tgl_lunas', function ($row) {
                    return Tanggal::tglIndo($row->tgl_lunas);
                })
                ->editColumn('jumlah', function ($row) {
                    return number_format($row->jumlah);
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 'PAID') {
                        return '<span class="badge bg-success">' . $row->status . '</span>';
                    }

                    return '<span class="badge bg-danger">' . $row->status . '</span>';
                })
                ->addColumn('saldo', function ($row) {
                    if ($row->trx_sum_jumlah) {
                        return number_format($row->jumlah - $row->trx_sum_jumlah);
                    }

                    return number_format($row->jumlah);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        $title = 'Daftar Invoice';
        return view('sop.invoice')->with(compact('title'));
    }

    public function calk(Request $request, Kecamatan $kec)
    {
        $data = $request->only([
            'peraturan_desa',
            'bantuan_rumah_tangga',
            'pengembangan_kapasitas',
            'pelatihan_masyarakat',
            'peningkatan_modal',
            'penambahan_investasi',
            'pendirian_unit_usaha',
        ]);

        $validate = Validator::make($data, [
            'peraturan_desa' => 'required',
            'bantuan_rumah_tangga' => 'required',
            'pengembangan_kapasitas' => 'required',
            'pelatihan_masyarakat' => 'required',
            'peningkatan_modal' => 'required',
            'penambahan_investasi' => 'required',
            'pendirian_unit_usaha' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $data = [
            'peraturan_desa' => $request->peraturan_desa,
            "D" => [
                "1" => [
                    "d" => [
                        "1" => $request->bantuan_rumah_tangga,
                        "2" => $request->pengembangan_kapasitas,
                        "3" => $request->pelatihan_masyarakat
                    ]
                ],
                "2" => [
                    "a" => str_replace(',', '', $request->peningkatan_modal),
                    "b" => str_replace(',', '', $request->penambahan_investasi),
                    "c" => str_replace(',', '', $request->pendirian_unit_usaha)
                ]
            ]
        ];

        $kec = Kecamatan::where('id', $kec->id)->update([
            'calk' => json_encode($data)
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Pengaturan CALK Berhasil Diperbarui.',
        ]);
    }

    public function pesanWhatsapp(Request $request, Kecamatan $kec)
    {
        if ($kec->id != Session::get('lokasi')) {
            abort(404);
        }

        $data = $request->only([
            'tagihan',
            'angsuran'
        ]);

        $validate = Validator::make($data, [
            'tagihan' => 'required',
            'angsuran' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $wa = [
            'tagihan' => $data['tagihan'],
            'angsuran' => $data['angsuran']
        ];

        Kecamatan::where('id', $kec->id)->update([
            'whatsapp' => $wa
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Pengaturan Pesan Whatsapp Berhasil Diperbarui.',
        ]);
    }

    public function detailInvoice($inv)
    {
        $inv = AdminInvoice::where('idv', $inv)
            ->where('lokasi', Session::get('lokasi'))
            ->with('jp')
            ->firstOrFail();

        $title = 'Invoice #' . $inv->nomor . ' - ' . $inv->jp->nama_jp;
        return view('sop.detail_invoice')->with(compact('title', 'inv'));
    }

    public function save_whatsapp_session(Request $request)
    {
        $lokasi = Session::get('lokasi');
        if (! $lokasi) {
            return response()->json(['success' => false, 'msg' => 'Lokasi session tidak ditemukan']);
        }

        $kec = Kecamatan::where('id', $lokasi)->first();
        if (! $kec) {
            return response()->json(['success' => false, 'msg' => 'Kecamatan tidak ditemukan']);
        }

        $apiKey = env('WA_GATEWAY_API_KEY');
        $base = rtrim(env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com'), '/');

        if (! $apiKey) {
            return response()->json(['success' => false, 'msg' => 'WA_GATEWAY_API_KEY belum di-set di .env']);
        }

        $instanceName = 'lkm_'.\Illuminate\Support\Str::slug($kec->nama_lembaga_sort ?? $kec->nama_kec ?? 'lkm')
            .'-'.$kec->id
            .'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));

        try {
            // NOTE: enpii Cloudflare WAF blocks User-Agent: GuzzleHttp/7 → use browser-like UA.
            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
            ]);

            $createRes = $client->post($base.'/instance/create', [
                'headers' => [
                    'apikey' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode([
                    'instanceName' => $instanceName,
                    'qrcode' => true,
                    'integration' => 'WHATSAPP-BAILEYS',
                    'token' => (string) \Illuminate\Support\Str::uuid(),
                ]),
            ]);

            $createBodyRaw = (string) $createRes->getBody();
            $createBody = json_decode($createBodyRaw, true);
            \Log::info('Evolution create instance', [
                'instance' => $instanceName,
                'endpoint' => $base.'/instance/create',
                'status' => $createRes->getStatusCode(),
                'has_qr' => ! empty($createBody['qrcode']['base64'] ?? null),
            ]);

            $createStatus = $createRes->getStatusCode();
            if ($createStatus !== 200 && $createStatus !== 201 && $createStatus !== 403 && $createStatus !== 409) {
                $errMsg = $createBody['response']['message'] ?? $createBodyRaw;
                if (is_array($errMsg)) {
                    $errMsg = json_encode($errMsg);
                }

                \Log::warning('Evolution create instance non-success', [
                    'instance' => $instanceName,
                    'status' => $createStatus,
                    'body' => $createBodyRaw,
                ]);

                return response()->json([
                    'success' => false,
                    'msg' => 'Gagal membuat instance (HTTP '.$createStatus.'): '.$errMsg,
                ]);
            }

            // Per Evolution v2 schema: create response already contains qrcode.base64 at root.
            // If 403 (instance exists) or qrcode.base64 missing, poll /connect/{name}.
            $qr = $createBody['qrcode']['base64'] ?? null;
            $pairingCode = $createBody['qrcode']['pairingCode'] ?? ($createBody['qrcode']['code'] ?? null);
            $connectBody = $createBody;

            if (! $qr) {
                try {
                    $client->post($base.'/instance/restart/'.$instanceName, [
                        'headers' => ['apikey' => $apiKey, 'Accept' => 'application/json'],
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Evolution restart error: '.$e->getMessage());
                }

                for ($i = 0; $i < 5; $i++) {
                    sleep(2);
                    $connectRes = $client->get($base.'/instance/connect/'.$instanceName, [
                        'headers' => ['apikey' => $apiKey, 'Accept' => 'application/json'],
                    ]);
                    $connectBodyRaw = (string) $connectRes->getBody();
                    $connectBody = json_decode($connectBodyRaw, true);
                    \Log::info('Evolution connect', [
                        'attempt' => $i + 1,
                        'status' => $connectRes->getStatusCode(),
                        'has_qr' => ! empty($connectBody['qrcode']['base64'] ?? null),
                    ]);

                    $qr = $connectBody['qrcode']['base64']
                        ?? $connectBody['base64']
                        ?? null;
                    $pairingCode = $connectBody['qrcode']['pairingCode']
                        ?? $connectBody['pairingCode']
                        ?? $connectBody['qrcode']['code']
                        ?? $connectBody['code']
                        ?? null;

                    if ($qr) {
                        break;
                    }
                }
            }

            Whatsapp::updateOrCreate(
                ['lokasi' => $lokasi],
                [
                    'nama' => $kec->nama_lembaga_sort ?? 'LKM',
                    'instance_name' => $instanceName,
                    'status' => 'pending',
                ]
            );

            return response()->json([
                'success' => true,
                'instance' => $instanceName,
                'qr' => $qr,
                'pairingCode' => $pairingCode,
                'state' => $connectBody['instance']['state'] ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $sqlCode = $e->errorInfo[1] ?? null;
            $isDuplicate = ($sqlCode == 1062) || (isset($e->errorInfo[0]) && $e->errorInfo[0] === '23000');

            \Log::warning('Evolution save_whatsapp_session db error', [
                'instance' => $instanceName,
                'endpoint' => $base.'/instance/create',
                'class' => get_class($e),
                'sql_state' => $e->errorInfo[0] ?? null,
                'sql_code' => $sqlCode,
                'message' => $e->getMessage(),
            ]);

            if ($isDuplicate) {
                $msg = 'Nama instance WhatsApp bentrok dengan data lain di database. Silakan hubungi admin untuk dibuatkan nama instance baru.';
            } else {
                $msg = 'Kesalahan database saat menyimpan instance: '.$e->getMessage();
            }

            return response()->json(['success' => false, 'msg' => $msg], 500);
        } catch (\GuzzleHttp\Exception\ConnectException | \GuzzleHttp\Exception\RequestException $e) {
            \Log::error('Evolution save_whatsapp_session network error', [
                'instance' => $instanceName,
                'endpoint' => $base.'/instance/create',
                'class' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Gagal terhubung ke gateway Evolution: '.$e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Evolution save_whatsapp_session error', [
                'instance' => $instanceName,
                'endpoint' => $base.'/instance/create',
                'class' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    public function evolution_connection_state(Request $request)
    {
        $lokasi = Session::get('lokasi');
        if (! $lokasi) {
            return response()->json(['success' => false, 'msg' => 'Lokasi session tidak ditemukan']);
        }

        $wa = Whatsapp::where('lokasi', $lokasi)->first();
        if (! $wa || ! $wa->instance_name) {
            return response()->json(['success' => false, 'state' => 'unknown']);
        }

        $apiKey = env('WA_GATEWAY_API_KEY');
        $base = rtrim(env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com'), '/');

        if (! $apiKey) {
            return response()->json(['success' => false, 'msg' => 'WA_GATEWAY_API_KEY belum di-set di .env', 'state' => 'unknown']);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
            ]);
            $res = $client->get($base.'/instance/connectionState/'.$wa->instance_name, [
                'headers' => ['apikey' => $apiKey, 'Accept' => 'application/json'],
            ]);

            $bodyRaw = (string) $res->getBody();
            $body = json_decode($bodyRaw, true) ?? [];
            $state = $body['instance']['state'] ?? ($body['state'] ?? 'unknown');

            if ($state === 'open') {
                Whatsapp::where('lokasi', $lokasi)->update(['status' => 'connected']);
            }

            return response()->json([
                'success' => true,
                'state' => $state,
                'raw' => $body,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    // Proxy QR / pairingCode for current user's instance. Frontend polls until QR appears.
    public function evolution_qr(Request $request)
    {
        $lokasi = Session::get('lokasi');
        if (! $lokasi) {
            return response()->json(['success' => false, 'msg' => 'Lokasi session tidak ditemukan']);
        }

        $wa = Whatsapp::where('lokasi', $lokasi)->first();
        if (! $wa || ! $wa->instance_name) {
            return response()->json(['success' => false, 'qr' => null, 'pairingCode' => null]);
        }

        $apiKey = env('WA_GATEWAY_API_KEY');
        $base = rtrim(env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com'), '/');

        if (! $apiKey) {
            return response()->json(['success' => false, 'msg' => 'WA_GATEWAY_API_KEY belum di-set di .env', 'qr' => null]);
        }

        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
            ]);

            $res = $client->get($base.'/instance/connect/'.$wa->instance_name, [
                'headers' => ['apikey' => $apiKey, 'Accept' => 'application/json'],
            ]);

            $raw = (string) $res->getBody();
            $body = json_decode($raw, true) ?? [];

            $qr = $body['qrcode']['base64']
                ?? $body['base64']
                ?? null;
            $pairingCode = $body['qrcode']['pairingCode']
                ?? $body['pairingCode']
                ?? $body['qrcode']['code']
                ?? $body['code']
                ?? null;

            return response()->json([
                'success' => true,
                'qr' => $qr,
                'pairingCode' => $pairingCode,
                'state' => $body['instance']['state'] ?? ($body['state'] ?? null),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    public function delete_whatsapp_session(Request $request)
    {
        $lokasi = $request->input('lokasi', Session::get('lokasi'));
        if ($lokasi === null || $lokasi === '') {
            return response()->json([
                'success' => false,
                'deleted' => 0,
                'message' => 'Lokasi tidak ditemukan.',
            ], 422);
        }

        $wa = Whatsapp::where('lokasi', $lokasi)->first();
        $apiKey = env('WA_GATEWAY_API_KEY');
        $base = rtrim(env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com'), '/');

        if ($wa && $wa->instance_name && $apiKey) {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ],
            ]);
            try {
                $client->delete($base.'/instance/logout/'.$wa->instance_name, [
                    'headers' => ['apikey' => $apiKey],
                ]);
            } catch (\Exception $e) {
                \Log::warning('Evolution logout error: '.$e->getMessage());
            }

            try {
                $client->delete($base.'/instance/delete/'.$wa->instance_name, [
                    'headers' => ['apikey' => $apiKey],
                ]);
            } catch (\Exception $e) {
                \Log::warning('Evolution delete error: '.$e->getMessage());
            }
        }

        $deleted = Whatsapp::where('lokasi', $lokasi)->delete();

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
