<?php
namespace app\admin\controller;

class Index extends Base
{
    /**
     * 文件下载
     */
    public function download($file)
    {
        $filepath = ROOT_PATH . 'uploads/export/' . $file;
        
        if (!file_exists($filepath)) {
            $this->add_log('系统管理', '文件下载：文件不存在 - ' . $file, '失败');
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['code' => 0, 'msg' => '文件不存在', 'data' => null]);
            exit;
        }
        
        // 获取文件扩展名
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        // 设置下载头信息
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // 输出文件内容
        readfile($filepath);
        $this->add_log('系统管理', '文件下载：' . $file, '成功');
        exit;
    }
} 