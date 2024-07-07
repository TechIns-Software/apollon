<?php

namespace Tests\Unit\Services\Stats;

use App\Models\Business;
use App\Services\Stats\BusinessStats;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessStatsTest extends TestCase
{
    use RefreshDatabase;
    public function testDefault()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');
        Business::factory(5)->create(['created_at'=>$currentYear."-12-05"]);

        $expectedResult = [
            $currentYear=>[
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

    public function testDefaultYear()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');

        Business::factory(5)->create(['created_at'=>"$currentYear-12-05"]);
        Business::factory(15)->create(['created_at'=>"$currentYear-02-05"]);
        Business::factory(153)->create(['created_at'=>"$currentYear-04-05"]);

        Business::factory(5)->create(['created_at'=>"2023-12-05"]);
        Business::factory(5)->create(['created_at'=>"2023-06-05"]);

        $expectedResult = [
            $currentYear=>[
                1=>0,
                2=>15,
                3=>0,
                4=>153,
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

    public function testMultipleYears()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');

        Business::factory(5)->create(['created_at'=>"$currentYear-12-05"]);
        Business::factory(15)->create(['created_at'=>"$currentYear-02-05"]);
        Business::factory(153)->create(['created_at'=>"$currentYear-04-05"]);

        Business::factory(5)->create(['created_at'=>"2023-12-05"]);
        Business::factory(5)->create(['created_at'=>"2023-06-05"]);

        $inputYears = ['2023',"2024","2022"];

        $expectedResult = [
            "2024"=>[
                1=>0,
                2=>15,
                3=>0,
                4=>153,
                5=>0,
                6=>0,
                7=>0,
                8=>0,
                9=>0,
                10=>0,
                11=>0,
                12=>5,
            ],
            "2023"=>[
                1=>0,
                2=>0,
                3=>0,
                4=>0,
                5=>0,
                6=>5,
                7=>0,
                8=>0,
                9=>0,
                10=>0,
                11=>0,
                12=>5,
            ],
            "2022"=>[
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
                12=>0,
            ]
        ];

        $businessStats = new BusinessStats($inputYears);
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);

    }

    public function testMultipleDatesSameMonth()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');

        Business::factory(5)->create(['created_at'=>"$currentYear-12-05"]);
        Business::factory(33)->create(['created_at'=>"$currentYear-12-09"]);

        $expectedResult = [
            $currentYear=>[
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
                12=>38,
            ]
        ];

        $businessStats = new BusinessStats();
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);
    }
}
