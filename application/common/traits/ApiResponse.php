<?php
namespace app\common\traits;
use think\Response;  // 添加这行，引入Response类
trait ApiResponse
{
    /**
     * 成功返回
     */
    protected function ajaxSuccess($msg = '', $data = null)
    {
        $returnData = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data
        ];
        echo Response::create(json_encode($returnData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->send();
        die();
    }

    /**
     * 错误返回
     */
    protected function ajaxError($msg = '', $data = null, $code = 0)
    {   
        $returnData = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        echo Response::create(json_encode($returnData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->send();
        die();
    }
} 