<?php

namespace Tests\Feature;

use App\Utils\ExcelExporter;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Regression test untuk bug presisi NIK 16 digit.
 *
 * Excel hanya menyimpan 15 digit angka signifikan (IEEE 754 double). Bila NIK
 * ditulis sebagai number, digit ke-16 dibulatkan menjadi 0 sehingga
 * 3515014208900001 tersimpan sebagai 3515014208900000.
 *
 * Solusi: bind nilai sebagai DataType::TYPE_STRING + number format "@".
 */
class NikIdentityTest extends TestCase
{
    private function export(string $html): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $path = tempnam(sys_get_temp_dir(), 'nik') . '.xlsx';
        (new ExcelExporter)->fromHtml($html)->setShowGridlines(false)->save($path);

        return IOFactory::load($path)->getActiveSheet();
    }

    private function table(array $headers, array $rows): string
    {
        $head = '';
        foreach ($headers as $h) {
            $head .= '<th class="t l b">' . htmlspecialchars($h) . '</th>';
        }

        $body = '';
        foreach ($rows as $r) {
            $body .= '<tr>';
            foreach ($r as $c) {
                $body .= '<td class="t l b" align="left">' . $c . '</td>';
            }
            $body .= '</tr>';
        }

        return '<table border="0" width="100%"><tr>' . $head . '</tr>' . $body . '</table>';
    }

    public function test_nik_16_digit_is_preserved_exactly()
    {
        $niks = [
            '3273010101010001',
            '3515014208900001',
            '1601010101010000',
            '1209090909090000',
        ];

        $rows = [];
        foreach ($niks as $i => $nik) {
            $rows[] = [(string) ($i + 1), 'LN-2026-000' . $i, 'BUDI SANTOSO', 'MBR-000' . $i, $nik, '1,000,000'];
        }

        $sheet = $this->export($this->table(
            ['No', 'LOAN ID', 'NAMA DEBITUR', 'CIF / NO. ANGGOTA', 'NIK', 'Saldo'],
            $rows
        ));

        foreach ($niks as $i => $nik) {
            $row = $i + 2;
            $cell = $sheet->getCellByColumnAndRow(5, $row);

            $this->assertSame($nik, (string) $cell->getValue(), "NIK baris $row tidak utuh");
            $this->assertSame(16, strlen((string) $cell->getValue()));
            $this->assertSame(DataType::TYPE_STRING, $cell->getDataType(), "NIK baris $row bukan string");
            $this->assertSame('@', $cell->getStyle()->getNumberFormat()->getFormatCode());
        }
    }

    public function test_nik_whose_last_digit_is_nonzero_is_not_rounded()
    {
        // 351501...0001 akan menjadi ...0000 bila dipaksakan menjadi float
        $nik = '3515014208900001';

        $sheet = $this->export($this->table(
            ['No', 'NIK'],
            [['1', $nik]]
        ));

        $cell = $sheet->getCellByColumnAndRow(2, 2);

        $this->assertSame($nik, (string) $cell->getValue());
        $this->assertNotSame((string) (float) $nik, (string) $cell->getValue());
    }

    public function test_empty_nik_falls_back_to_empty_string_without_type_change()
    {
        $sheet = $this->export($this->table(
            ['No', 'LOAN ID', 'NIK', 'Saldo'],
            [['1', 'LN-2026-0001', '', '1,000,000']]
        ));

        $cell = $sheet->getCellByColumnAndRow(3, 2);

        $this->assertNotSame('0', (string) $cell->getValue());
        $this->assertNotSame(DataType::TYPE_NUMERIC, $cell->getDataType());
        $this->assertSame('@', $cell->getStyle()->getNumberFormat()->getFormatCode());
    }

    public function test_loan_id_and_cif_columns_are_also_text()
    {
        $sheet = $this->export($this->table(
            ['No', 'LOAN ID', 'CIF / NO. ANGGOTA', 'Saldo'],
            [['1', 'LN-2026-000123', 'MBR-001234', '1,500,000']]
        ));

        $this->assertSame(DataType::TYPE_STRING, $sheet->getCellByColumnAndRow(2, 2)->getDataType());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCellByColumnAndRow(3, 2)->getDataType());
    }

    public function test_numeric_columns_are_not_forced_to_text()
    {
        $sheet = $this->export($this->table(
            ['No', 'Kode Akun', 'NIK', 'Saldo'],
            [['1', '1.2.3.4', '3201010101010001', '1,234,567,890']]
        ));

        // kolom nominal tetap numeric supaya bisa dijumlahkan di Excel
        $this->assertSame(DataType::TYPE_NUMERIC, $sheet->getCellByColumnAndRow(4, 2)->getDataType());
        $this->assertSame(1234567890.0, (float) $sheet->getCellByColumnAndRow(4, 2)->getValue());

        // kolom kode akun tetap string (bukan angka)
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCellByColumnAndRow(2, 2)->getDataType());

        // NIK terdeteksi meski tidak di kolom pertama
        $nik = $sheet->getCellByColumnAndRow(3, 2);
        $this->assertSame('3201010101010001', (string) $nik->getValue());
        $this->assertSame(DataType::TYPE_STRING, $nik->getDataType());
    }

    public function test_identity_headers_with_extra_words_are_detected()
    {
        $cases = [
            ['NIK PENJAMIN', '3201010101010002', 2],
            ['No. Rekening', '12345678901234567890', 2],
            ['No Identitas', '3301010101010004', 2],
        ];

        foreach ($cases as [$header, $value, $col]) {
            $sheet = $this->export($this->table(
                ['No', $header, 'Saldo'],
                [['1', $value, '1,000']]
            ));

            $cell = $sheet->getCellByColumnAndRow($col, 2);
            $this->assertSame($value, (string) $cell->getValue(), "header '$header' gagal");
            $this->assertSame(DataType::TYPE_STRING, $cell->getDataType(), "header '$header' bukan string");
        }
    }

    public function test_table_without_header_cells_does_not_crash()
    {
        $html = '<table border="0"><tr><td>1</td><td>3201010101010003</td><td>5,000</td></tr></table>';

        $sheet = $this->export($html);

        $this->assertSame(1, (int) $sheet->getCellByColumnAndRow(1, 1)->getValue());
    }

    public function test_empty_html_does_not_crash()
    {
        $sheet = $this->export('');

        $this->assertInstanceOf(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::class, $sheet);
    }
}
