<?php

namespace Tests\Unit\Services\Stats;

use App\Models\Business;
use App\Services\Stats\BusinessStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessStatsTest extends TestCase
{
    use RefreshDatabase;
    public function testDefault()
    {
        $business = Business::factory(5)->create(['created_at'=>"2024-12-05"]);

        $expectedResult = [
            '2024'=>[
                1=>0,
                2=>0,
                3=>0,
                4=>0,
                5=>0,
                6=>0,
                7=>0,
                8=>0,
                9=>0,
                10=>0,
                11=>0,
                12=>5,
            ]
        ];

        $businessStats = new BusinessStats();
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);
    }
}
