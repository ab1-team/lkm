<?php

namespace Tests\Unit;

use App\Models\SistemAngsuran;
use App\Utils\HitungSistemAngsuran;
use Tests\TestCase;

class SistemAngsuranRefactorTest extends TestCase
{
    public function test_id_list_by_jenis_harian(): void
    {
        $ids = SistemAngsuran::idListByJenis('harian');

        $this->assertContains(12, $ids);
        $this->assertContains(25, $ids);
        $this->assertNotContains(1, $ids);
        $this->assertNotContains(20, $ids);
    }

    public function test_tempo_bulanan(): void
    {
        $sa = SistemAngsuran::find(1); // Bulanan
        $this->assertEquals(12, HitungSistemAngsuran::hitung($sa, 12)['tempo']);
    }

    public function test_tempo_bulanan_ditunda_id_20(): void
    {
        $sa = SistemAngsuran::find(20); // M12
        $this->assertEquals(12, HitungSistemAngsuran::hitung($sa, 24)['tempo']);
    }

    public function test_tempo_bulanan_ditunda_id_11_with_long_jangka(): void
    {
        $sa = SistemAngsuran::find(11); // M24
        $this->assertEquals(0, HitungSistemAngsuran::hitung($sa, 24)['tempo']);
        $this->assertEquals(12, HitungSistemAngsuran::hitung($sa, 36)['tempo']);
    }

    public function test_is_harian(): void
    {
        $sa12 = SistemAngsuran::find(12);
        $sa25 = SistemAngsuran::find(25);
        $sa1  = SistemAngsuran::find(1);
        $sa20 = SistemAngsuran::find(20);

        $this->assertTrue($sa12->isHarian());
        $this->assertTrue($sa25->isHarian());
        $this->assertFalse($sa1->isHarian());
        $this->assertFalse($sa20->isHarian());
    }

    public function test_interval_hari(): void
    {
        $sa12 = SistemAngsuran::find(12);
        $sa25 = SistemAngsuran::find(25);

        $this->assertEquals(7, $sa12->interval_hari);
        $this->assertEquals(14, $sa25->interval_hari);
    }

    public function test_label_satuan(): void
    {
        $sa12 = SistemAngsuran::find(12);
        $sa1  = SistemAngsuran::find(1);
        $sa20 = SistemAngsuran::find(20);

        $this->assertEquals('Hari', $sa12->labelSatuan());
        $this->assertEquals('Bulan', $sa1->labelSatuan());
        $this->assertEquals('Bulan', $sa20->labelSatuan());
    }

    public function test_label_satuan_singkat(): void
    {
        $sa12 = SistemAngsuran::find(12);
        $sa1  = SistemAngsuran::find(1);

        $this->assertEquals('mgg', $sa12->labelSatuanSingkat());
        $this->assertEquals('bln', $sa1->labelSatuanSingkat());
    }

    public function test_id_26_1_harian(): void
    {
        $sa = SistemAngsuran::find(26);

        $this->assertNotNull($sa, 'id=26 harus tersedia sebagai "1 Harian"');
        $this->assertTrue($sa->isHarian());
        $this->assertEquals('harian', $sa->jenis);
        $this->assertEquals(1, $sa->interval_hari);
        $this->assertEquals('Hari', $sa->labelSatuan());
    }

    public function test_id_list_harian_includes_26(): void
    {
        $ids = SistemAngsuran::idListByJenis('harian');
        $this->assertContains(26, $ids, 'id=26 harus masuk list harian');
        $this->assertContains(12, $ids);
        $this->assertContains(25, $ids);
    }

    public function test_urutan_populated_for_used_ids(): void
    {
        // id=1 pasti paling sering dipakai (17k+ baris)
        $sa1 = SistemAngsuran::find(1);
        $this->assertNotNull($sa1->urutan, 'id=1 harus punya urutan');
        $this->assertEquals(1, (int) $sa1->urutan, 'id=1 harus urutan 1');

        // id=2 (3 Bulan) urutan 2
        $sa2 = SistemAngsuran::find(2);
        $this->assertEquals(2, (int) $sa2->urutan);

        // id=25 (2 Mingguan) urutan 3
        $sa25 = SistemAngsuran::find(25);
        $this->assertEquals(3, (int) $sa25->urutan);

        // id=12 urutan 4
        $sa12 = SistemAngsuran::find(12);
        $this->assertEquals(4, (int) $sa12->urutan);
    }

    public function test_urutan_unused_id_is_null(): void
    {
        // id=8 belum pernah dipakai → urutan NULL
        $sa8 = SistemAngsuran::find(8);
        $this->assertNull($sa8->urutan);

        // id=13 juga 0 → NULL
        $sa13 = SistemAngsuran::find(13);
        $this->assertNull($sa13->urutan);
    }
}