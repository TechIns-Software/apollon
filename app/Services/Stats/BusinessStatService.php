<?php

namespace App\Services\Stats;

use Carbon\Carbon;

abstract class BusinessStatService
{
    private $business_id;

    private $years;

    public function __construct(array $years=[])
    {
        $this->years = empty($years)?$years:[(int)Carbon::now()->format('Y')];
    }

    public function getYears()
    {
        return $this->years;
    }

    /**
     * Queries the database and fetches the results.
     *
     * The returned collection contains items in the following format:
     * [
     *     {
     *         count: int,  // Number of items
     *         month: int,  // Month in which these items occurred
     *         year: int    // Year in which these items occurred
     *     },
     *     ...
     * ]
     *
     * @return \Illuminate\Support\Collection| array |iterable
     */
    protected abstract function queryResult();

    /**
     * Returns the statistics.
     *
     * The returned result is in the following format:
     * [
     *   'year' => [
     *       1 => int,   // Number of items in January
     *       2 => int,   // Number of items in February
     *       ...
     *       12 => int   // Number of items in December
     *   ],
     *   ...
     * ]
     *
     * - 'year' represents the year for which statistics exist.
     * - Each item is a month where 1 is January and 12 is December.
     * - If a year has no results, it is returned as an array with 0 values for each month.
     * - 0 is returned for months that have no statistics. For example, if there are no results for December, the following is returned:
     * [
     *    // Rest of the months
     *    12 => 0
     * ]
     *
     * Example:
     * If the statistics for the year 2023 are:
     * [
     *     2023 => [
     *         1 => 5,   // 5 items in January
     *         2 => 3,   // 3 items in February
     *         ...
     *         12 => 0   // 0 items in December
     *     ]
     * ]
     *
     * @return array
     */

    public function getStats():array
    {
        $dbResult = $this->queryResult();
        $monthStats=[];

        foreach ($this->years as $year){
            $monthStats[$year]=array_fill(0,12,0);
        }

        foreach ($dbResult as $item){
            $monthStats[$item->year][$item->month]=$item->count;
        }

        return $monthStats;
    }
}
