<?php
namespace app\common\model;
use think\Model;

class Base extends Model
{
    /**
     * 自动完成
     */
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    /**
     * 初始化
     */
    protected static function init()
    {
        // 添加字符编码处理
        self::beforeInsert(function($model){
            foreach ($model->getData() as $key => $value) {
                if (is_string($value)) {
                    $model->setAttr($key, mb_convert_encoding($value, 'UTF-8', 'auto'));
                }
            }
        });
    }

    /**
     * 获取分页列表
     */
    public function getList($where = [], $order = '', $page = 1, $limit = 10)
    {
        // 构建基础查询
        $query = $this;
        
        if (!empty($where)) {
            $query = $query->where($where);
        }
        
        if (!empty($order)) {
            $query = $query->order($order);
            // 调试输出
            echo "Order condition: " . $order . "\n";
        }
        
        // 调试输出最终的SQL
        echo "Final SQL: " . $query->fetchSql(true)->select() . "\n";
        
        // 获取总数
        $count = $query->count();
        
        // 获取列表数据
        $list = $query->page($page, $limit)->select();
        
        return [
            'total' => $count,
            'list' => $list ?: [],
            'page' => (int)$page,
            'limit' => (int)$limit
        ];
    }

    /**
     * 获取单条记录
     */
    public function getOne($where = [])
    {
        return $this->where($where)->find();
    }

    /**
     * 新增记录
     */
    public function add($data)
    {
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新数据
     * @param array $data 更新数据
     * @param array $where 更新条件
     * @return array
     */
    public function updateData($data, $where)
    {
        try {
            $result = $this->allowField(true)->save($data, $where);
            
            // 只要不是 false 就认为是成功
            if ($result !== false) {
                return ['code' => 1, 'msg' => '更新成功'];
            }
            return ['code' => 0, 'msg' => '更新失败'];
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => '更新失败：' . $e->getMessage()];
        }
    }

    /**
     * 删除记录
     */
    public function remove($where)
    {
        return $this->where($where)->delete();
    }
} 