<?php

namespace App\Services\Stats;

use App\Services\Stats\BusinessStatService;
use Illuminate\Support\Facades\DB;

class BusinessStats extends BusinessStatService
{

    /**
     * @inheritDoc
     */
    protected function queryResult()
    {
        return DB::table('business')
            ->selectRaw("count(*) as count,MONTH(created_at) as `month`,YEAR(created_at) as `year`")
            ->groupBy(DB::raw("`year`,`month`"))
            ->where(function($query){
                foreach ($this->getYears() as $y) {
                    $query->orWhereRaw('YEAR(created_at) = ?', [$y]);
                }
            })->get();
    }
}
