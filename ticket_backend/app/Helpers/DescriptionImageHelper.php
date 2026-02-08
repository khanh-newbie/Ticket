<?php

namespace App\Helpers;

class DescriptionImageHelper
{
    /**
     * Lưu tất cả ảnh Base64 trong HTML description ra file thật
     * và thay src thành đường dẫn file
     */
    public static function saveImages($html)
    {
        return preg_replace_callback('/<img\s+[^>]*src="data:image\/(.*?);base64,(.*?)"[^>]*>/', function($matches) {
            $ext = $matches[1]; // png, jpg, ...
            $data = base64_decode($matches[2]);
            $filename = uniqid() . '.' . $ext;

            // tạo thư mục nếu chưa có
            $dir = public_path('description_images');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $path = $dir . '/' . $filename;
            file_put_contents($path, $data);

            return '<img src="http://127.0.0.1:8000/description_images/' . $filename . '" />';
        }, $html);
    }
}
