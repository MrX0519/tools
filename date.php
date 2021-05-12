<?php
//时间类方法

/**
 * PHP输出两个置顶日期之间的所有日期
 * @param $start 开始时间
 * @param $end 结束时间
 * @return array
 */
function printDates($start, $end)
{
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    $data = [];
    while ($dt_start <= $dt_end) {
        $data[] = date('Y-m-d', $dt_start);
        $dt_start = strtotime('+1 day', $dt_start);
    }
    return $data;
}



/**
 * @description:根据时间(如：2021-05-12)获取月头月尾
 * @param $time (如：2021-05-12)
 * @return array ['月头','月尾']
 */
function getMonthBetween($time)
{
    $time = explode('-', $time);
    $year = $time[0];
    $month = $time[1];
    $beginDate = date('Y-m-01', strtotime("$year-$month"));
    $endDate = date('Y-m-d', strtotime("$beginDate +1 month -1 day"));
    return [$beginDate,$endDate];
}
