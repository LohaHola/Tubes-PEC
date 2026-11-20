<?php

namespace Tests\Unit;

use App\Services\IndonesiaRegionService;
use Tests\TestCase;

class IndonesiaRegionServiceTest extends TestCase
{
    public function test_it_loads_all_38_indonesian_provinces_from_local_data(): void
    {
        $regions = new IndonesiaRegionService();

        $provinces = $regions->provinces();

        $this->assertCount(38, $provinces);
        $this->assertSame('Bengkulu', $regions->findProvinceByName('bengkulu')['name']);
        $this->assertSame('Papua Barat Daya', $regions->findProvinceByName('Papua Barat Daya')['name']);
    }

    public function test_it_loads_only_the_selected_provinces_regencies_and_cities(): void
    {
        $regions = new IndonesiaRegionService();

        $cities = $regions->cities('17');
        $names = $cities->pluck('name')->all();

        $this->assertCount(10, $cities);
        $this->assertContains('Kota Bengkulu', $names);
        $this->assertContains('Kabupaten Rejang Lebong', $names);
        $this->assertSame('Kota Bengkulu', $regions->findCity('17', 'kota bengkulu')['name']);
    }

    public function test_it_returns_an_empty_list_for_an_unknown_province(): void
    {
        $regions = new IndonesiaRegionService();

        $this->assertCount(0, $regions->cities('00'));
    }

    public function test_city_lookup_cannot_match_a_city_from_another_province(): void
    {
        $regions = new IndonesiaRegionService();

        $this->assertNull($regions->findCity('17', 'Kota Sorong'));
    }
}
