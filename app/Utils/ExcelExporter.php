<?php

namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExcelExporter
{
    private $spreadsheet;
    private $sheet;
    private $currentRow = 1;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    /**
     * Parse HTML dan konversi ke native Excel
     */
    public function fromHtml(string $html): self
    {
        // Ambil hanya konten dalam <main>...</main>
        if (preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $matches)) {
            $html = $matches[1];
        }

        // Parse HTML dengan DOMDocument
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        // Proses semua elemen secara berurutan (tabel, div, teks)
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $this->processNodes($body);
        }

        return $this;
    }

    /**
     * Proses semua node secara berurutan
     */
    private function processNodes(\DOMElement $parent): void
    {
        foreach ($parent->childNodes as $node) {
            if (!($node instanceof \DOMElement)) continue;
            
            $tagName = strtolower($node->tagName);
            
            if ($tagName === 'table') {
                // Skip nested table (parent adalah td/th)
                $parentTag = strtolower($node->parentNode->tagName);
                if (in_array($parentTag, ['td', 'th'])) continue;
                if ($node->parentNode->parentNode instanceof \DOMElement) {
                    $grandparentTag = strtolower($node->parentNode->parentNode->tagName);
                    if (in_array($grandparentTag, ['td', 'th'])) continue;
                }
                
                // Parse tabel utama
                $table = $this->parseTableElement($node);
                if (!empty($table['rows'])) {
                    $this->writeTable($table);
                    $this->currentRow++;
                }
            } elseif (in_array($tagName, ['div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                // Tulis div/p/heading sebagai baris teks
                $text = trim($this->getInnerHTML($node));
                if (!empty($text)) {
                    // Hitung maxCols dari tabel sebelumnya
                    $maxCols = $this->sheet->getHighestColumn() 
                        ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($this->sheet->getHighestColumn()) 
                        : 8;
                    
                    // Cek apakah right-aligned (seperti "Kode Akun")
                    $isRightAlign = false;
                    $styleAttr = $node->getAttribute('style');
                    $alignAttr = $node->getAttribute('align');
                    
                    if ($alignAttr === 'right' 
                        || stripos($styleAttr, 'text-align:right') !== false 
                        || stripos($styleAttr, 'text-align: right') !== false
                        || stripos($styleAttr, 'text-align : right') !== false) {
                        $isRightAlign = true;
                    }
                    
                    // Cek juga parent node untuk style
                    if (!$isRightAlign && $node->parentNode instanceof \DOMElement) {
                        $parentStyle = $node->parentNode->getAttribute('style');
                        if (stripos($parentStyle, 'text-align:right') !== false 
                            || stripos($parentStyle, 'text-align: right') !== false) {
                            $isRightAlign = true;
                        }
                    }
                    
                    if ($isRightAlign) {
                        // Right-aligned: tulis di kolom terakhir, merge dari tengah
                        $startCol = max(1, $maxCols - 3); // Mulai dari kolom ke-5 (dari 8)
                        $this->sheet->setCellValueByColumnAndRow($startCol, $this->currentRow, $text);
                        if ($maxCols > $startCol) {
                            $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                            $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCols);
                            $this->sheet->mergeCells($startColLetter . $this->currentRow . ':' . $endColLetter . $this->currentRow);
                        }
                        $this->sheet->getCellByColumnAndRow($startCol, $this->currentRow)->getStyle()->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        // Default: tulis di kolom A, merge full width
                        $this->sheet->setCellValueByColumnAndRow(1, $this->currentRow, $text);
                        if ($maxCols > 1) {
                            $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCols);
                            $this->sheet->mergeCells('A' . $this->currentRow . ':' . $endCol . $this->currentRow);
                        }
                    }
                    
                    // Style font
                    $font = $this->sheet->getCellByColumnAndRow($isRightAlign ? $startCol : 1, $this->currentRow)->getStyle()->getFont();
                    $font->setName('Arial');
                    
                    if ($tagName === 'h1') {
                        $font->setSize(18)->setBold(true);
                    } elseif ($tagName === 'h2') {
                        $font->setSize(16)->setBold(true);
                    } elseif ($tagName === 'h3') {
                        $font->setSize(14)->setBold(true);
                    } elseif (strpos($this->getInnerHTML($node), '<b') !== false || strpos($this->getInnerHTML($node), '<strong') !== false) {
                        $font->setSize(11)->setBold(true);
                    } else {
                        $font->setSize(11);
                    }
                    
                    // Alignment center untuk heading
                    if (in_array($tagName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                        $this->sheet->getCellByColumnAndRow(1, $this->currentRow)->getStyle()->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }
                    
                    $this->currentRow++;
                }
            }
            // Tag lain (span, header, main, body, dll) diabaikan
        }
    }

    /**
     * Parse HTML tables ke array - hanya tabel utama (bukan nested)
     */
    private function parseTables(string $html): array
    {
        $tables = [];
        
        // Gunakan DOMDocument untuk parse HTML
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        // Cari hanya tabel yang parent-nya BUKAN td/th/td
        $tableElements = $dom->getElementsByTagName('table');
        
        foreach ($tableElements as $tableEl) {
            $parentTag = strtolower($tableEl->parentNode->tagName);
            // Skip nested table (parent adalah td, th, atau tabel lain)
            if (in_array($parentTag, ['td', 'th'])) {
                continue;
            }
            // Cek juga apakah parent-nya adalah elemen dalam tabel
            if ($tableEl->parentNode->parentNode instanceof \DOMElement) {
                $grandparentTag = strtolower($tableEl->parentNode->parentNode->tagName);
                if (in_array($grandparentTag, ['td', 'th'])) {
                    continue;
                }
            }
            
            $table = $this->parseTableElement($tableEl);
            if (!empty($table['rows'])) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * Parse satu tabel - flatten nested tables
     */
    private function parseTableElement(\DOMElement $tableEl): array
    {
        $rows = [];
        
        foreach ($tableEl->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $tagName = strtolower($node->tagName);
                
                if ($tagName === 'thead' || $tagName === 'tbody' || $tagName === 'tfoot') {
                    foreach ($node->childNodes as $child) {
                        if ($child instanceof \DOMElement && strtolower($child->tagName) === 'tr') {
                            $rows = array_merge($rows, $this->parseRowWithNested($child));
                        }
                    }
                } elseif ($tagName === 'tr') {
                    $rows = array_merge($rows, $this->parseRowWithNested($node));
                }
            }
        }

        return ['rows' => $rows];
    }

    /**
     * Parse satu baris, handle nested table di dalam cell
     */
    private function parseRowWithNested(\DOMElement $tr): array
    {
        $rows = [];
        $normalCells = [];
        $currentCol = 0;
        
        foreach ($tr->childNodes as $node) {
            if ($node instanceof \DOMElement && in_array(strtolower($node->tagName), ['td', 'th'])) {
                $colspan = (int) ($node->getAttribute('colspan') ?: 1);
                
                // Cek apakah cell ini punya nested table
                $nestedTables = $this->findAllNestedTables($node);
                
                if (!empty($nestedTables)) {
                    // Parse semua nested table dengan column offset
                    $nestedStartCol = $currentCol;
                    foreach ($nestedTables as $nestedTable) {
                        $nestedRows = $this->parseNestedTable($nestedTable, $nestedStartCol, $colspan);
                        $rows = array_merge($rows, $nestedRows);
                    }
                    
                    // Cek apakah ada konten teks sebelum/between/after tabel
                    $textContent = $this->getTextOutsideTables($node);
                    if (!empty(trim($textContent))) {
                        // Tambah sebagai baris teks dengan colspan penuh
                        $rows[] = [
                            'cells' => [[
                                'value' => $textContent,
                                'colspan' => $colspan,
                                'rowspan' => 1,
                                'align' => '',
                                'style' => '',
                                'tag' => 'td',
                            ]],
                            'style' => '',
                        ];
                    }
                } else {
                    $normalCells[] = [
                        'value' => $this->getInnerHTML($node),
                        'raw_html' => $node->ownerDocument->saveHTML($node),
                        'colspan' => $colspan,
                        'rowspan' => (int) ($node->getAttribute('rowspan') ?: 1),
                        'height' => (int) ($node->getAttribute('height') ?: 0),
                        'align' => $node->getAttribute('align') ?: '',
                        'style' => $node->getAttribute('style') ?: '',
                        'tag' => strtolower($node->tagName),
                    ];
                }
                
                $currentCol += $colspan;
            }
        }
        
        // Jika ada cell normal, tambahkan sebagai baris
        if (!empty($normalCells)) {
            $rows[] = [
                'cells' => $normalCells,
                'style' => $tr->getAttribute('style') ?: '',
            ];
        }
        
        return $rows;
    }

    /**
     * Cari semua nested table di dalam cell
     */
    private function findAllNestedTables(\DOMElement $node): array
    {
        $tables = [];
        $this->findTablesRecursive($node, $tables);
        return $tables;
    }

    /**
     * Rekursif cari tabel
     */
    private function findTablesRecursive(\DOMElement $node, array &$tables): void
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                if (strtolower($child->tagName) === 'table') {
                    $tables[] = $child;
                } else {
                    $this->findTablesRecursive($child, $tables);
                }
            }
        }
    }

    /**
     * Ambil teks yang ada di luar tabel
     */
    private function getTextOutsideTables(\DOMElement $node): string
    {
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                if (strtolower($child->tagName) === 'table') {
                    continue; // Skip tabel
                }
                if (strtolower($child->tagName) === 'div') {
                    // Ambil teks dari div
                    $divText = trim($this->getInnerHTML($child));
                    if (!empty($divText)) {
                        $text .= $divText . "\n";
                    }
                }
            } elseif ($child instanceof \DOMText) {
                $text .= $child->textContent;
            }
        }
        return trim($text);
    }

    /**
     * Parse nested table dengan column offset
     */
    private function parseNestedTable(\DOMElement $tableEl, int $colOffset, int $parentColspan): array
    {
        $rows = [];
        $nestedRows = [];
        
        // Hitung jumlah kolom di nested table
        $nestedMaxCols = 0;
        foreach ($tableEl->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $tagName = strtolower($node->tagName);
                if ($tagName === 'thead' || $tagName === 'tbody' || $tagName === 'tfoot') {
                    foreach ($node->childNodes as $child) {
                        if ($child instanceof \DOMElement && strtolower($child->tagName) === 'tr') {
                            $colCount = 0;
                            foreach ($child->childNodes as $c) {
                                if ($c instanceof \DOMElement && in_array(strtolower($c->tagName), ['td', 'th'])) {
                                    $colCount += (int) ($c->getAttribute('colspan') ?: 1);
                                }
                            }
                            $nestedMaxCols = max($nestedMaxCols, $colCount);
                        }
                    }
                } elseif ($tagName === 'tr') {
                    $colCount = 0;
                    foreach ($node->childNodes as $c) {
                        if ($c instanceof \DOMElement && in_array(strtolower($c->tagName), ['td', 'th'])) {
                            $colCount += (int) ($c->getAttribute('colspan') ?: 1);
                        }
                    }
                    $nestedMaxCols = max($nestedMaxCols, $colCount);
                }
            }
        }
        
        // Hitung colspan untuk cell pertama agar text full 1 baris
        // Contoh: parent=8 kolom, offset=0, nested punya 4 cell
        // → cell pertama colspan = parentColspan - offset - nestedMaxCols = 8 - 0 - 4 = 4
        // → cell 2,3,4 masing-masing colspan=1 → total = 4+1+1+1 = 7 kolom (kolom 8 kosong)
        $firstCellColspan = max(1, $parentColspan - $colOffset - $nestedMaxCols);
        
        foreach ($tableEl->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $tagName = strtolower($node->tagName);
                
                if ($tagName === 'thead' || $tagName === 'tbody' || $tagName === 'tfoot') {
                    foreach ($node->childNodes as $child) {
                        if ($child instanceof \DOMElement && strtolower($child->tagName) === 'tr') {
                            $rows[] = $this->parseNestedRow($child, $colOffset, $firstCellColspan);
                        }
                    }
                } elseif ($tagName === 'tr') {
                    $rows[] = $this->parseNestedRow($node, $colOffset, $firstCellColspan);
                }
            }
        }
        
        return $rows;
    }

    /**
     * Parse baris nested table dengan column offset
     */
    private function parseNestedRow(\DOMElement $tr, int $colOffset, int $firstCellColspan): array
    {
        $cells = [];
        
        // Tambah empty cells untuk offset
        for ($i = 0; $i < $colOffset; $i++) {
            $cells[] = [
                'value' => '',
                'raw_html' => '',
                'colspan' => 1,
                'rowspan' => 1,
                'align' => '',
                'style' => '',
                'tag' => 'td',
            ];
        }
        
        $isFirstCell = true;
        
        foreach ($tr->childNodes as $node) {
            if ($node instanceof \DOMElement && in_array(strtolower($node->tagName), ['td', 'th'])) {
                $cellColspan = (int) ($node->getAttribute('colspan') ?: 1);
                
                // Cell pertama: gunakan calculated colspan agar sesuai parent
                if ($isFirstCell && $cellColspan === 1) {
                    $cellColspan = $firstCellColspan;
                    $isFirstCell = false;
                }
                
                $cells[] = [
                    'value' => $this->getInnerHTML($node),
                    'raw_html' => $node->ownerDocument->saveHTML($node),
                    'colspan' => $cellColspan,
                    'rowspan' => (int) ($node->getAttribute('rowspan') ?: 1),
                    'align' => $node->getAttribute('align') ?: '',
                    'style' => $node->getAttribute('style') ?: '',
                    'tag' => strtolower($node->tagName),
                ];
            }
        }
        
        return [
            'cells' => $cells,
            'style' => $tr->getAttribute('style') ?: '',
        ];
    }

    /**
     * Ambil innerHTML dari element
     */
    private function getInnerHTML(\DOMElement $node): string
    {
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                    // Ambil teks dari div/p/heading, tambah newline
                    $childText = trim($this->getInnerHTML($child));
                    if (!empty($childText)) {
                        if (!empty($text)) {
                            $text .= "\n";
                        }
                        $text .= $childText;
                    }
                } elseif ($tag === 'br') {
                    $text .= "\n";
                } elseif ($tag === 'table') {
                    continue; // Skip tabel nested
                } else {
                    // Tag lain (b, strong, span, dll) - ambil teks
                    $childText = trim($this->getInnerHTML($child));
                    if (!empty($childText)) {
                        $text .= $childText;
                    }
                }
            } elseif ($child instanceof \DOMText) {
                $textContent = trim($child->textContent);
                if (!empty($textContent)) {
                    $text .= $textContent;
                }
            }
        }
        
        return trim($text);
    }

    /**
     * Tulis tabel ke worksheet
     */
    private function writeTable(array $table): void
    {
        // Hitung jumlah kolom maksimal
        $maxCols = 0;
        foreach ($table['rows'] as $row) {
            $colCount = 0;
            foreach ($row['cells'] as $cell) {
                $colCount += $cell['colspan'];
            }
            $maxCols = max($maxCols, $colCount);
        }

        // Track merged cells
        $mergedCells = [];
        
        foreach ($table['rows'] as $rowIndex => $row) {
            $colIndex = 1;
            
            // Parse row style
            $rowBg = $this->extractBackground($row['style']);
            
            foreach ($row['cells'] as $cell) {
                // Skip jika cell sudah di-merge
                $cellKey = $this->currentRow . '_' . $colIndex;
                
                // Cari cell berikutnya yang belum di-merge
                while (isset($mergedCells[$this->currentRow][$colIndex])) {
                    $colIndex++;
                }
                
                if ($colIndex > $maxCols) break;
                
                $value = $cell['value'];
                
                // Deteksi tipe data
                $dataType = $this->detectDataType($value, $cell);
                
                // Tulis ke cell
                if ($dataType === DataType::TYPE_NUMERIC) {
                    $numericValue = (float) str_replace(',', '', $value);
                    $this->sheet->setCellValueByColumnAndRow($colIndex, $this->currentRow, $numericValue);
                    // Format: angka bulat tanpa desimal, angka desimal dengan 2 desimal
                    if ($numericValue == (int) $numericValue && strpos($value, '.') === false) {
                        $this->sheet->getCellByColumnAndRow($colIndex, $this->currentRow)->getStyle()->getNumberFormat()->setFormatCode('#,##0');
                    } else {
                        $this->sheet->getCellByColumnAndRow($colIndex, $this->currentRow)->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                } elseif ($dataType === DataType::TYPE_STRING) {
                    $this->sheet->setCellValueExplicitByColumnAndRow($colIndex, $this->currentRow, $value, DataType::TYPE_STRING);
                } else {
                    $this->sheet->setCellValueByColumnAndRow($colIndex, $this->currentRow, $value);
                }
                
                // Merge cells
                if ($cell['colspan'] > 1 || $cell['rowspan'] > 1) {
                    $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $endColIndex = $colIndex + $cell['colspan'] - 1;
                    $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColIndex);
                    $endRow = $this->currentRow + $cell['rowspan'] - 1;
                    
                    $mergeRange = $startCol . $this->currentRow . ':' . $endCol . $endRow;
                    $this->sheet->mergeCells($mergeRange);
                    
                    // Tandai cell yang di-merge
                    for ($r = $this->currentRow; $r <= $endRow; $r++) {
                        for ($c = $colIndex; $c <= $endColIndex; $c++) {
                            if ($r === $this->currentRow && $c === $colIndex) continue;
                            $mergedCells[$r][$c] = true;
                        }
                    }
                }
                
                // Styling
                $this->applyCellStyle($colIndex, $this->currentRow, $cell, $rowBg);
                
                $colIndex += $cell['colspan'];
            }
            
            // Set row height dari style tr atau height attribute td
            if (preg_match('/height:\s*(\d+)/', $row['style'], $heightMatch)) {
                $this->sheet->getRowDimension($this->currentRow)->setRowHeight((int)$heightMatch[1]);
            } else {
                // Cek height attribute dari td
                foreach ($row['cells'] as $c) {
                    if (isset($c['height']) && $c['height'] > 0) {
                        $this->sheet->getRowDimension($this->currentRow)->setRowHeight($c['height']);
                        break;
                    }
                }
                
                // Jika cell punya newline, set row height otomatis
                foreach ($row['cells'] as $c) {
                    if (!empty($c['value']) && substr_count($c['value'], "\n") > 0) {
                        $lineCount = substr_count($c['value'], "\n") + 1;
                        $currentHeight = $this->sheet->getRowDimension($this->currentRow)->getRowHeight();
                        $minHeight = $lineCount * 15; // 15px per baris
                        if ($currentHeight < $minHeight) {
                            $this->sheet->getRowDimension($this->currentRow)->setRowHeight($minHeight);
                        }
                        break;
                    }
                }
            }
            
            $this->currentRow++;
        }
        
        // Auto-size columns dengan min/max width
        for ($i = 1; $i <= $maxCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $this->sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        
        // Set width yang lebih tepat setelah auto-size
        $this->sheet->calculateColumnWidths();
        
        // Apply min/max width dan sesuaikan berdasarkan header
        for ($i = 1; $i <= $maxCols; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $dimension = $this->sheet->getColumnDimension($colLetter);
            $currentWidth = $dimension->getWidth();
            
            // Deteksi header kolom untuk set width yang tepat
            $headerText = '';
            for ($row = 1; $row <= min(5, $this->currentRow); $row++) {
                $cellValue = $this->sheet->getCell($colLetter . $row)->getValue();
                if ($cellValue && !empty(trim($cellValue))) {
                    $headerText = strtolower(trim($cellValue));
                    break;
                }
            }
            
            // Set width berdasarkan tipe kolom
            if ($i === 1 && in_array($headerText, ['no', 'no.', 'no '])) {
                // Kolom No
                $dimension->setWidth(5);
                $dimension->setAutoSize(false);
            } elseif (in_array($headerText, ['tanggal', 'tgl', 'date'])) {
                // Kolom tanggal
                $dimension->setWidth(14);
                $dimension->setAutoSize(false);
            } elseif (in_array($headerText, ['ref id.', 'ref id', 'ref', 'kode', 'kode akun', 'kd. rek', 'kd.rek'])) {
                // Kolom kode/referensi
                $dimension->setWidth(14);
                $dimension->setAutoSize(false);
            } elseif (in_array($headerText, ['debit', 'kredit', 'saldo', 'jumlah'])) {
                // Kolom nominal
                $dimension->setWidth(18);
                $dimension->setAutoSize(false);
            } elseif (in_array($headerText, ['ins'])) {
                // Kolom ins (pendek)
                $dimension->setWidth(5);
                $dimension->setAutoSize(false);
            } elseif ($currentWidth > 50) {
                // Batasi max width untuk kolom lain (keterangan dll)
                $dimension->setWidth(50);
                $dimension->setAutoSize(false);
            } elseif ($currentWidth < 8) {
                // Minimum width
                $dimension->setWidth(8);
                $dimension->setAutoSize(false);
            }
        }
    }

    /**
     * Deteksi tipe data cell
     */
    private function detectDataType(string $value, array $cell): string
    {
        $value = trim($value);
        
        if (empty($value)) {
            return DataType::TYPE_STRING;
        }
        
        // Cek apakah header (th tag)
        if ($cell['tag'] === 'th') {
            return DataType::TYPE_STRING;
        }
        
        // Cek apakah angka (format: 1,234,567.00 atau (1,234,567.00) atau 0)
        if (preg_match('/^\(?\d[\d,]*\.?\d*\)?$/', $value)) {
            return DataType::TYPE_NUMERIC;
        }
        
        return DataType::TYPE_STRING;
    }

    /**
     * Extract background color dari style
     */
    private function extractBackground(string $style): string
    {
        if (preg_match('/background(?:-color)?:\s*([^;]+)/', $style, $match)) {
            return trim($match[1]);
        }
        return '';
    }

    /**
     * Apply style ke cell
     */
    private function applyCellStyle(int $col, int $row, array $cell, string $rowBg): void
    {
        $cellObj = $this->sheet->getCellByColumnAndRow($col, $row);
        $style = $cellObj->getStyle();
        
        // Alignment
        $alignment = $style->getAlignment();
        
        switch ($cell['align']) {
            case 'center':
                $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER);
                break;
            case 'right':
                $alignment->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                break;
            default:
                $alignment->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
        
        $alignment->setVertical(Alignment::VERTICAL_BOTTOM);
        $alignment->setWrapText(true);
        
        // Font - deteksi dari style attribute atau raw_html
        $font = new Font();
        $font->setName('Arial');
        
        // Deteksi font-size dari style atau raw_html
        $fontSize = 11; // default
        $styleToCheck = $cell['style'];
        if (isset($cell['raw_html'])) {
            $styleToCheck .= ' ' . $cell['raw_html'];
        }
        
        // Cari font-size terbesar (untuk judul ambil yang terbesar)
        if (preg_match_all('/font-size:\s*(\d+)\s*px/i', $styleToCheck, $sizeMatches)) {
            $fontSize = max(array_map('intval', $sizeMatches[1]));
        }
        $font->setSize($fontSize);
        
        // Deteksi bold dari tag th, style font-weight, atau konten <b>
        $isBold = false;
        if ($cell['tag'] === 'th') {
            $isBold = true;
        }
        if (strpos($cell['style'], 'font-weight') !== false) {
            $isBold = true;
        }
        // Cek raw_html untuk <b> atau <strong>
        if (isset($cell['raw_html']) && (stripos($cell['raw_html'], '<b') !== false || stripos($cell['raw_html'], '<strong') !== false)) {
            $isBold = true;
        }
        
        $font->setBold($isBold);
        $style->setFont($font);
        
        // Background
        if (!empty($rowBg)) {
            $fill = $style->getFill();
            $fill->setFillType(Fill::FILL_SOLID);
            
            // Parse background color
            if (preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $rowBg, $colorMatch)) {
                $r = dechex((int)$colorMatch[1]);
                $g = dechex((int)$colorMatch[2]);
                $b = dechex((int)$colorMatch[3]);
                $fill->getStartColor()->setARGB('FF' . strtoupper(str_pad($r, 2, '0', STR_PAD_LEFT) . str_pad($g, 2, '0', STR_PAD_LEFT) . str_pad($b, 2, '0', STR_PAD_LEFT)));
            } elseif (preg_match('/#([0-9A-Fa-f]{6})/', $rowBg, $colorMatch)) {
                $fill->getStartColor()->setARGB('FF' . strtoupper($colorMatch[1]));
            }
        }
        
        // Border - abu-abu tipis seperti default Excel
        $borders = $style->getBorders();
        $borders->getTop()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB4B4B4'));
        $borders->getBottom()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB4B4B4'));
        $borders->getLeft()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB4B4B4'));
        $borders->getRight()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB4B4B4'));
    }

    /**
     * Set show gridlines
     */
    public function setShowGridlines(bool $show): self
    {
        $this->sheet->setShowGridlines($show);
        return $this;
    }

    /**
     * Simpan ke file
     */
    public function save(string $path): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($path);
    }

    /**
     * Output ke php://output
     */
    public function output(): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
    }

    /**
     * Get spreadsheet instance
     */
    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }
}
