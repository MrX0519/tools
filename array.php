<?php

/**
 * @description:根据数据
 * @param {dataArr:需要分组的数据；keyStr:分组依据}
 * @return:
 */
function dataGroup(array $dataArr, string $keyStr):array
{
    $newArr=[];
    foreach ($dataArr as $k => $val) {    //数据根据日期分组
        $newArr[$val[$keyStr]][] = $val;
    }
    return $newArr;
}
