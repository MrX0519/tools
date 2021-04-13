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
