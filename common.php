<?php

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
 * 计算两个日期相隔多少年、月、日
 * @param $date1 string 开始日期时间
 * @param $date2 string 结束日期时间
 * @return array
 */
function diffDate($date1, $date2)
{
    $date1 = $date1 ? $date1 : date('Y-m-d', time());
    $date2 = $date2 ? $date2 : date('Y-m-d', time());
    // 如果开始大于结束则交换
    if (strtotime($date1) > strtotime($date2)) {
        $tmp = $date2;
        $date2 = $date1;
        $date1 = $tmp;
    }
    // 分别取得年月日
    list($Y1, $m1, $d1) = explode('-', $date1);
    list($Y2, $m2, $d2) = explode('-', $date2);
    $Y = $Y2 - $Y1; // 年
    $m = $m2 - $m1; // 月
    $d = $d2 - $d1; // 日
    if ($d < 0) {
        $d += (int)date('t', strtotime("-1 month $date2"));
        $m--;
    }
    if ($m < 0) {
        $m += 12;
        $Y--;
    }
    return array('year' => $Y, 'month' => $m, 'day' => $d);
}
