<?php
namespace app\common\service;

class LogService
{
    /**
     * 记录系统日志
     * @param string $name 日志文件名
     * @param string $title 日志标题
     * @param string $note 日志内容
     * @param string $baseDir 日志基础目录，默认为 system_logs
     * @return bool
     */
    public static function writeSystemLog($name, $title, $note, $baseDir = 'system_logs')
    {
        try {
            // 构建日志目录路径
            $dir = ROOT_PATH . $baseDir . '/' . date('Y-m') . '/' . date('d');
            
            // 创建目录
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
                // 设置目录权限
                chmod(ROOT_PATH . $baseDir, 0777);
                chmod(ROOT_PATH . $baseDir . '/' . date('Y-m'), 0777);
                chmod($dir, 0777);
            }
            
            // 构建日志文件路径
            $fileName = $dir . DS . $name . "_log.txt";
            
            // 格式化日志内容
            $logContent = date("Y-m-d H:i:s") . ' :' . $title . ':' . $note . "\n";
            
            // 如果是新文件，先创建并设置权限
            if (!file_exists($fileName)) {
                file_put_contents($fileName, $logContent, FILE_APPEND);
                chmod($fileName, 0777);
            } else {
                file_put_contents($fileName, $logContent, FILE_APPEND);
            }
            
            return true;
        } catch (\Exception $e) {
            // 记录错误日志
            \think\Log::error('记录系统日志失败：' . $e->getMessage());
            return false;
        }
    }
} 