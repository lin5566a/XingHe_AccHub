<?php
namespace app\admin\model;

use think\Model;

class SystemContent extends Model
{
    protected $name = 'system_content';
    
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;

    protected function initialize()
    {
        parent::initialize();
        
        // 注册新增前的事件
        self::beforeInsert(function($model){
            if (!isset($model->created_at)) {
                $model->created_at = date('Y-m-d H:i:s');
            }
        });
    }
} 