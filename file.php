<?php


/** 移动上传的临时文件
 *
 * public下temporary目录设置为临时文件夹
 * @img_dir  string  存储路径
 * @file  array  上传的临时文件路径
 */
function move_temp_images($img_dir, $file)
{
    //目录绝对路径
    $root_path = Env::get('root_path').'public';
    // 目录是否存在 不存在则创建
    if (!file_exists($root_path . $img_dir)) {
        mkdir($root_path . $img_dir, 0777, true);
    }
    if (is_array($file)) {
        $images = [];
        foreach ($file as &$image) {
            if (stripos($image, 'temporary') !== false && file_exists($root_path . $image)) {
                $img_name = basename($image);
                if ($img_name && @rename($root_path . $image, $root_path . $img_dir . $img_name)) {
                    $images[] = $img_dir . $img_name;
                }
            }
        }
        $images = serialize($images);
    } else {
        if (stripos($file, 'temporary') !== false && file_exists($root_path . $file)) {
            $img_name = basename($file);

            if ($img_name && @rename($root_path . $file, $root_path . $img_dir . $img_name)) {
                $images = $img_dir . $img_name;
            }
        }
    }
    return $images;
}
