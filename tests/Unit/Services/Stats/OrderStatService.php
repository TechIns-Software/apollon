<?php

namespace Tests\Unit\Services\Stats;

use App\Models\Business;
use App\Models\Order;
use App\Services\Stats\BusinessStats;
use App\Services\Stats\OrderStats;
use Carbon\Carbon;
use Tests\TestCase;

class OrderStatService extends TestCase
{
    public function testDefault()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');
        $business = Business::factory()->create();

        $orders = Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-12-05"]);

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
                12=>10,
            ]
        ];

        $businessStats = new OrderStats($business->id);
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);
    }

    public function testDefaultYear()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');
        $business = Business::factory()->create();

        Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-12-05"]);
        Order::factory(20)->withBusiness($business)->create(['created_at'=>"$currentYear-04-05"]);
        Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-06-05"]);

        Order::factory(10)->withBusiness($business)->create(['created_at'=>"2023-12-05"]);
        Order::factory(10)->withBusiness($business)->create(['created_at'=>"2023-05-04"]);
        Order::factory(10)->withBusiness($business)->create(['created_at'=>"2023-06-05"]);

        $expectedResult = [
            $currentYear=>[
                1=>0,
                2=>0,
                3=>0,
                4=>20,
                5=>0,
                6=>10,
                7=>0,
                8=>0,
                9=>0,
                10=>0,
                11=>0,
                12=>10,
            ]
        ];

        $businessStats = new OrderStats($business->id);
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);
    }

    public function testMultipleYear()
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');
        $business = Business::factory()->create();

        Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-12-05"]);
        Order::factory(20)->withBusiness($business)->create(['created_at'=>"$currentYear-04-05"]);
        Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-06-05"]);

        Order::factory(10)->withBusiness($business)->create(['created_at'=>"2023-12-05"]);
        Order::factory(10)->withBusiness($business)->create(['created_at'=>"2023-09-04"]);
        Order::factory(10)->withBusiness($business)->create(['created_at'=>"2023-06-05"]);

        $expectedResult = [
            $currentYear=>[
                1=>0,
                2=>0,
                3=>0,
                4=>20,
                5=>0,
                6=>10,
                7=>0,
                8=>0,
                9=>0,
                10=>0,
                11=>0,
                12=>10,
            ],
            '2023' => [
                1=>0,
                2=>0,
                3=>0,
                4=>0,
                5=>0,
                6=>10,
                7=>0,
                8=>0,
                9=>10,
                10=>0,
                11=>0,
                12=>10,
            ],
            '2022'=>[
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

        $years = array_keys($expectedResult);

        $businessStats = new OrderStats($business->id,$years);
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);
    }

    public function testMultipleDatesSameMonth()
    {

        $currentDate = Carbon::now();
        $currentYear = $currentDate->format('Y');
        $business = Business::factory()->create();

        Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-12-05"]);

        Order::factory(20)->withBusiness($business)->create(['created_at'=>"$currentYear-04-05"]);
        Order::factory(20)->withBusiness($business)->create(['created_at'=>"$currentYear-04-09"]);

        Order::factory(10)->withBusiness($business)->create(['created_at'=>"$currentYear-06-05"]);

        $expectedResult = [
            $currentYear=>[
                1=>0,
                2=>0,
                3=>0,
                4=>40,
                5=>0,
                6=>10,
                7=>0,
                8=>0,
                9=>0,
                10=>0,
                11=>0,
                12=>10,
            ]
        ];

        $businessStats = new OrderStats($business->id);
        $result = $businessStats->getStats();
        $this->assertEquals($expectedResult,$result);
    }
}
