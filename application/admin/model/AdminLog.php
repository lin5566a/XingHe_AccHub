<?php
namespace app\admin\model;
use app\common\model\Base;
use think\Db;

class AdminLog extends Base
{
    protected $table = 'epay_admin_log';
    protected $pk = 'log_id';  // 明确指定主键
    
    // 自动完成
    protected $auto = ['operation_time'];
    
    // 允许写入的字段
    protected $allowField = [
        'operator',
        'operation_type',
        'module',
        'ip_address',
        'operation_description',
        'status',
        'operation_time'
    ];
    
    protected function setOperationTimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 获取日志列表
     */
    public function getLogList($where = [], $order = 'log_id desc', $page = 1, $limit = 10)
    {
        // 构建基础查询
        $offset = ($page - 1) * $limit;
        
        // 获取总数
        $count = $this->where($where)->count();
        
        // 获取列表数据
        $list = $this->where($where)
                    ->order($order)
                    ->limit($offset, $limit)
                    ->select();
        
        return [
            'total' => $count,
            'list' => $list ?: [],
            'page' => (int)$page,
            'limit' => (int)$limit
        ];
    }

    /**
     * 执行原生SQL查询
     */
    private function query($sql, $params = [])
    {
        return $this->db()->query($sql, $params);
    }

    /**
     * 重写add方法，特殊处理description字段
     */
    public function add($data)
    {
        if (isset($data['operation_description'])) {
            $data['operation_description'] = mb_convert_encoding($data['operation_description'], 'UTF-8', 'auto');
        }
        return parent::add($data);
    }
} 