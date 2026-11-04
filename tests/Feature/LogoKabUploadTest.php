<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end fitur upload logo kabupaten (/admin/logo-kab).
 *
 * Yang diverifikasi:
 *   1. file gambar tersimpan sebagai {id_kabupaten}.png (bukan hash/random);
 *   2. file non-PNG (mis. JPG) tetap dikonversi menjadi PNG asli;
 *   3. nama file wajib merujuk ID kabupaten yang ada di tabel `kabupaten`;
 *   4. upload dengan nama sama menimpa file lama (bukan membuat duplikat);
 *   5. file hasil upload bisa dipakai dokumen (PNG valid, bukan teks).
 *
 * Login memakai guard yang sama dengan /admin/update-fitur, yaitu guard `web`
 * (user LKM), bukan guard `master`.
 *
 * Catatan: fitur ini tidak menulis ke tabel `kabupaten` (hanya membaca untuk
 * validasi), jadi test ini tidak perlu membuat baris kabupaten. Data dibuat
 * lewat Storage::fake('public') sehingga file asli tidak tersentuh.
 */
class LogoKabUploadTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * ID kabupaten yang dipakai pada test. Dipastikan ada di tabel
     * `kabupaten` (dipakai halaman index sebagai baris pertama).
     */
    private const KAB_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function loginUser(): void
    {
        // User LKM (guard web) — sama seperti yang dipakai /admin/update-fitur.
        $user = new User;
        $user->uname = 'uji-logo-kab';
        $user->pass = 'rahasia';
        $user->namadepan = 'Uji';
        $user->namabelakang = 'Logo';
        $user->lokasi = 351;
        $user->hak_akses = '';
        $user->akses_menu = '';
        $user->akses_tombol = '';

        $this->actingAs($user, 'web');
    }

    private function filePngPalsu(): UploadedFile
    {
        // PNG 1x1 valid (base64).
        $data = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        return UploadedFile::fake()->createWithContent('logo.png', $data);
    }

    private function fileJpgPalsu(): UploadedFile
    {
        // JPEG 1x1 valid.
        $data = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0a'
            .'HBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAA'
            .'AAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q=='
        );

        return UploadedFile::fake()->createWithContent('logo.jpg', $data);
    }

    public function test_halaman_index_tampil_dan_menampilkan_daftar_kabupaten(): void
    {
        $this->loginUser();

        $response = $this->get('/admin/logo-kab');

        $response->assertStatus(200);
        $response->assertSee('Logo Kabupaten');
        // Nama file yang dibentuk dari ID kabupaten.
        $response->assertSee(self::KAB_ID.'.png');
    }

    public function test_halaman_create_tampil(): void
    {
        $this->loginUser();

        $response = $this->get('/admin/logo-kab/create');

        $response->assertStatus(200);
        $response->assertSee('Nama File');
        $response->assertSee('File Gambar');
    }

    public function test_upload_png_disimpan_dengan_nama_id_kabupaten(): void
    {
        $this->loginUser();

        $response = $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertRedirect('/admin/logo-kab');
        $response->assertSessionHas('status');

        // WAJIB: nama file persis {id}.png
        Storage::disk('public')->assertExists('logo_kab/'.self::KAB_ID.'.png');
    }

    public function test_upload_jpg_dikonversi_menjadi_png_asli(): void
    {
        $this->loginUser();

        $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
            'file' => $this->fileJpgPalsu(),
        ])->assertRedirect('/admin/logo-kab');

        Storage::disk('public')->assertExists('logo_kab/'.self::KAB_ID.'.png');

        $path = Storage::disk('public')->path('logo_kab/'.self::KAB_ID.'.png');
        $this->assertSame('image/png', mime_content_type($path), 'File hasil harus benar-benar PNG.');
        $this->assertNotFalse(@getimagesize($path), 'File hasil harus bisa dibaca sebagai gambar.');
    }

    public function test_nama_file_harus_merujuk_id_kabupaten_yang_ada(): void
    {
        $this->loginUser();

        $response = $this->post('/admin/logo-kab', [
            'nama_file' => 999999,
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertSessionHasErrors('nama_file');
        Storage::disk('public')->assertMissing('logo_kab/999999.png');
    }

    public function test_nama_file_harus_angka(): void
    {
        $this->loginUser();

        $response = $this->post('/admin/logo-kab', [
            'nama_file' => 'bukan-angka',
            'file' => $this->filePngPalsu(),
        ]);

        $response->assertSessionHasErrors('nama_file');
    }

    public function test_upload_tanpa_file_ditolak(): void
    {
        $this->loginUser();

        $response = $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
        ]);

        $response->assertSessionHasErrors('file');
        Storage::disk('public')->assertMissing('logo_kab/'.self::KAB_ID.'.png');
    }

    public function test_upload_dengan_nama_sama_menimpa_file_lama(): void
    {
        $this->loginUser();

        $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
            'file' => $this->filePngPalsu(),
        ]);

        $path = Storage::disk('public')->path('logo_kab/'.self::KAB_ID.'.png');
        $isiLama = file_get_contents($path);

        // Upload ulang dengan isi berbeda.
        $dataBaru = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
            'file' => UploadedFile::fake()->createWithContent('logo.png', $dataBaru),
        ])->assertRedirect('/admin/logo-kab');

        Storage::disk('public')->assertExists('logo_kab/'.self::KAB_ID.'.png');

        $file = Storage::disk('public')->files('logo_kab');
        $this->assertCount(1, $file, 'Upload ulang harus menimpa, bukan membuat file tambahan.');
        $this->assertSame(
            'logo_kab/'.self::KAB_ID.'.png',
            $file[0],
            'Nama file harus tetap {id}.png.'
        );

        $this->assertNotSame($isiLama, file_get_contents($path), 'File lama harus tertimpa, bukan digabung.');
    }

    public function test_gambar_besar_diskala_turun(): void
    {
        $this->loginUser();

        // PNG besar (3000x2000) harus diperkecil supaya file tidak membengkak.
        $lebar = 3000;
        $tinggi = 2000;
        $image = imagecreatetruecolor($lebar, $tinggi);
        imagefilledrectangle($image, 0, 0, $lebar - 1, $tinggi - 1, imagecolorallocate($image, 30, 30, 30));
        ob_start();
        imagepng($image);
        $isi = ob_get_clean();
        imagedestroy($image);

        $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
            'file' => UploadedFile::fake()->createWithContent('besar.png', $isi),
        ])->assertRedirect('/admin/logo-kab');

        $path = Storage::disk('public')->path('logo_kab/'.self::KAB_ID.'.png');
        [$w, $h] = getimagesize($path);

        $this->assertLessThanOrEqual(1200, $w, 'Lebar gambar harus dibatasi.');
        $this->assertLessThanOrEqual(1200, $h, 'Tinggi gambar harus dibatasi.');
        $this->assertSame(1200, $w, 'Lebar harus proporsional (3000 -> 1200).');
        $this->assertSame(800, $h, 'Tinggi harus proporsional (2000 -> 800).');
    }

    public function test_hapus_logo_menghapus_file(): void
    {
        $this->loginUser();

        $this->post('/admin/logo-kab', [
            'nama_file' => self::KAB_ID,
            'file' => $this->filePngPalsu(),
        ]);

        Storage::disk('public')->assertExists('logo_kab/'.self::KAB_ID.'.png');

        $this->delete('/admin/logo-kab/'.self::KAB_ID)
            ->assertRedirect('/admin/logo-kab');

        Storage::disk('public')->assertMissing('logo_kab/'.self::KAB_ID.'.png');
    }

    public function test_halaman_terlindungi_dari_tamu(): void
    {
        // Sama seperti /admin/update-fitur: tamu diarahkan ke halaman login.
        $this->get('/admin/logo-kab')->assertRedirect('/');
    }
}
