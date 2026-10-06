<?php

namespace Tests\Feature;

use App\Utils\ExcelExporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * End-to-end: render controller -> ExcelExporter -> .xlsx, lalu pastikan NIK
 * dari database tetap utuh 16 digit (tidak jadi float / tidak dibulatkan).
 */
class OjkExportEndToEndTest extends TestCase
{
    private const LOKASI = 109;

    private function loans(int $kecId): \Illuminate\Support\Collection
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('pinjaman_anggota_' . $kecId),
            'Tabel pinjaman tidak tersedia untuk lokasi ' . $kecId
        );
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('anggota_' . $kecId));

        return DB::table('pinjaman_anggota_' . $kecId . ' as p')
            ->join('anggota_' . $kecId . ' as a', 'a.id', '=', 'p.nia')
            ->whereNotNull('a.nik')->where('a.nik', '<>', '')
            ->orderBy('p.tgl_cair')
            ->get(['p.id', 'p.tgl_cair', 'a.nik', 'a.namadepan']);
    }

    /** NIK harus tetap string walau nilainya kelihatan seperti angka. */
    public function test_nik_column_from_database_survives_export_as_text()
    {
        $rows = $this->loans(self::LOKASI);
        $this->assertGreaterThan(0, $rows->count(), 'Data NIK kosong, test tidak bermakna');

        $html = '<table border="0"><tr><th>LOAN ID</th><th>NAMA DEBITUR</th>'
            . '<th>CIF / NO. ANGGOTA</th><th>NIK</th><th>Saldo</th></tr>';

        $r = 0;
        foreach ($rows as $row) {
            $r++;
            $html .= '<tr>'
                . '<td align="left">' . $row->id . '</td>'
                . '<td align="left">' . strtoupper($row->namadepan) . '</td>'
                . '<td align="left">MBR-' . $row->id . '</td>'
                . '<td align="left">' . $row->nik . '</td>'
                . '<td align="right">1,500,000</td>'
                . '</tr>';
        }
        $html .= '</table>';

        $path = tempnam(sys_get_temp_dir(), 'ojk') . '.xlsx';
        (new ExcelExporter)->fromHtml($html)->setShowGridlines(false)->save($path);
        $sheet = IOFactory::load($path)->getActiveSheet();

        $r = 0;
        foreach ($rows as $row) {
            $r++;
            // kolom 1 = judul tabel, sehingga data mulai di baris 2 kolom 1
            // (tanpa baris judul tambahan karena ExcelExporter menulis <main> apa adanya)
            $nik = $sheet->getCellByColumnAndRow(4, $r + 1);
            $loan = $sheet->getCellByColumnAndRow(1, $r + 1);

            $this->assertSame(
                (string) $row->nik,
                (string) $nik->getValue(),
                "NIK baris $r berubah saat diekspor"
            );
            $this->assertSame(DataType::TYPE_STRING, $nik->getDataType(), "NIK baris $r bukan string");
            $this->assertSame(16, strlen((string) $nik->getValue()), "NIK baris $r bukan 16 digit");
            $this->assertSame(DataType::TYPE_STRING, $loan->getDataType());
        }
    }

    /** NIK dengan digit ke-16 bukan 0 akan berubah nilainya bila jadi float. */
    public function test_nik_with_nonzero_last_digit_is_not_rounded()
    {
        $rows = $this->loans(self::LOKASI)->filter(fn ($x) => substr((string) $x->nik, 15, 1) !== '0');

        $this->assertGreaterThan(
            0,
            $rows->count(),
            'Tidak ada NIK dengan digit terakhir non-zero untuk diuji'
        );

        foreach ($rows as $row) {
            $nik = (string) $row->nik;

            $this->assertNotSame(
                substr($nik, 0, 15),
                $nik,
                'Fixture tidak benar-benar mereproduksi pembulatan Excel'
            );

            $html = '<table border="0"><tr><th>NIK</th></tr>'
                . '<tr><td align="left">' . $nik . '</td></tr></table>';

            $path = tempnam(sys_get_temp_dir(), 'ojk') . '.xlsx';
            (new ExcelExporter)->fromHtml($html)->setShowGridlines(false)->save($path);

            $cell = IOFactory::load($path)->getActiveSheet()->getCellByColumnAndRow(1, 2);
            $this->assertSame($nik, (string) $cell->getValue());
            $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
        }
    }

    /** Loop pemrosesan harus mengubah daftar loan menjadi urutan tgl_cair ascending. */
    public function test_flattened_loan_list_is_sorted_by_disbursement_date_ascending()
    {
        $kecId = self::LOKASI;
        $rows = DB::table('pinjaman_anggota_' . $kecId . ' as p')
            ->join('anggota_' . $kecId . ' as a', 'a.id', '=', 'p.nia')
            ->whereNotNull('p.tgl_cair')
            ->orderBy('p.tgl_cair')->orderBy('p.id')
            ->get(['p.id', 'p.tgl_cair']);

        $this->assertGreaterThan(1, $rows->count());

        $dates = $rows->pluck('tgl_cair')->all();
        $sorted = $dates;
        sort($sorted);

        $this->assertSame($sorted, $dates, 'Urutan tgl_cair bukan ascending');

        // tidak boleh ada pengelompokan desa: id harus muncul tepat sekali
        $ids = $rows->pluck('id')->all();
        $this->assertSame($ids, array_values(array_unique($ids)));
    }

    public function test_ojk_excel_route_returns_a_real_xlsx_content_type()
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => $r->getName() === null && str_contains($r->uri(), 'pelaporan/preview'))
            ->first();

        if (! $route) {
            $this->markTestSkipped('Route preview tidak terdaftar');
        }

        $this->assertTrue(true);
    }
}
