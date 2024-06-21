<?php

namespace App\Services\Stats;

use App\Services\Stats\BusinessStatService;
use Illuminate\Support\Facades\DB;

class OrderStats extends BusinessStatService
{
    private $business_id;

    public function __construct(int $business_id,?array $years=[])
    {
        parent::__construct($years);
        $this->business_id = $business_id;
    }
    protected function queryResult()
    {
        return DB::table('order')->where('business_id',$this->business_id)
            ->selectRaw("count(*) as count,MONTH(created_at) as `month`,YEAR(created_at) as `year`")
            ->groupBy(DB::raw("`year`,`month`"))
            ->where(function($query){
                foreach ($this->getYears() as $y) {
                    $query->orWhereRaw('YEAR(created_at) = ?', [$y]);
                }
            })->get();
    }
}
