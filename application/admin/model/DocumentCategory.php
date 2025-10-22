<?php
namespace app\admin\model;

use think\Model;

class DocumentCategory extends Model
{
    protected $name = 'document_category';
    
    protected $autoWriteTimestamp = false;
    // protected $createTime = 'create_time';
    // protected $updateTime = 'update_time';
    protected $type = [
        'create_time' => 'string',
        'update_time' => 'string',
    ];
} 