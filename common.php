<?php

/**
 * PHP输出两个置顶日期之间的所有日期
 * $start 开始时间
 * $end 结束时间
 */
function printDates($start, $end)
{
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    $data = [];
    while ($dt_start <= $dt_end) {
        array_push($data, date('Y-m-d', $dt_start));
        $dt_start = strtotime('+1 day', $dt_start);
    }
    return $data;
}
