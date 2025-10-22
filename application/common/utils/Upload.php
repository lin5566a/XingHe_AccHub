<?php
namespace app\common\utils;

use think\File;
use think\facade\Log;

class Upload
{
    /**
     * 上传图片
     * @param File $file 文件对象
     * @param string $type 类型（avatar,logo,product等）
     * @param array $config 配置
     * @return array
     */
    public static function image($file, $type, $config = [])
    {
        try {
            // 默认配置
            $defaultConfig = [
                'size' => 2097152, // 2M
                'ext' => 'jpg,jpeg,png,gif',
                'path' => 'uploads/' . $type . '/' . date('Ymd')
            ];
            
            // 合并配置
            $config = array_merge($defaultConfig, $config);
            
            // 验证文件
            $info = $file->validate(['size' => $config['size'], 'ext' => $config['ext']]);
            
            // 移动到框架应用根目录
            $info = $info->move(ROOT_PATH . $config['path']);
            
            if ($info) {
                // 返回相对路径
                $filePath = '/' . $config['path'] . '/' . $info->getSaveName();
                return [
                    'code' => 1,
                    'msg' => '上传成功',
                    'data' => [
                        'file_path' => $filePath
                    ]
                ];
            } else {
                return [
                    'code' => 0,
                    'msg' => $file->getError()
                ];
            }
        } catch (\Exception $e) {
            Log::error('图片上传失败：' . $e->getMessage());
            return [
                'code' => 0,
                'msg' => '上传失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 上传需要权限控制的文件
     * @param File $file 文件对象
     * @param string $type 类型（export等）
     * @param array $config 配置
     * @return array
     */
    public static function secure($file, $type, $config = [])
    {
        try {
            // 默认配置
            $defaultConfig = [
                'size' => 10485760, // 10M
                'ext' => 'csv,xlsx',
                'path' => 'uploads/' . $type
            ];
            
            // 合并配置
            $config = array_merge($defaultConfig, $config);
            
            // 验证文件
            $info = $file->validate(['size' => $config['size'], 'ext' => $config['ext']]);
            
            // 移动到框架应用根目录
            $info = $info->move(ROOT_PATH . $config['path']);
            
            if ($info) {
                // 返回相对路径
                $filePath = '/' . $config['path'] . '/' . $info->getSaveName();
                return [
                    'code' => 1,
                    'msg' => '上传成功',
                    'data' => [
                        'file_path' => $filePath
                    ]
                ];
            } else {
                return [
                    'code' => 0,
                    'msg' => $file->getError()
                ];
            }
        } catch (\Exception $e) {
            Log::error('文件上传失败：' . $e->getMessage());
            return [
                'code' => 0,
                'msg' => '上传失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 删除文件
     * @param string $filePath 文件路径
     * @return bool
     */
    public static function delete($filePath)
    {
        $realPath = ROOT_PATH . 'public' . str_replace('/', DS, $filePath);
        if (file_exists($realPath)) {
            return unlink($realPath);
        }
        return false;
    }
} 