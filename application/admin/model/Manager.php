<?php
namespace app\admin\model;
use app\common\model\Base;
use think\Model;

class Manager extends Model
{
    protected $name = 'manager';
    protected $table = 'epay_manager';
    
    // 自动完成
    protected $auto = [];
    protected $insert = ['last_login_time', 'online_time'];
    
    // 自动时间戳设置
    protected $autoWriteTimestamp = true;
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';
    
    // 自动时间格式转换
    protected $dateFormat = 'Y-m-d H:i:s';

    // 指定时间字段类型
    protected $type = [
        'last_login_time'  =>  'datetime',
        'online_time'  =>  'datetime',
        'create_time'      =>  'datetime',
        'update_time'      =>  'datetime',
    ];
    
    // 登录时间
    protected function setLoginTimeAttr()
    {
        return date('Y-m-d H:i:s');
    }
    
    // 在线时间
    protected function setOnlineTimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 管理员登录
     */
    public function login($username, $password)
    {
        $admin = $this->where([
            'username' => $username,
            'password' => md5($password),
            'state' => 1
        ])->find();

        if (!$admin) {
            return false;
        }

        // 更新登录信息
        $this->where('id', $admin['id'])->update([
            'login_time' => date('Y-m-d H:i:s'),
            'online_time' => date('Y-m-d H:i:s')
        ]);

        // 隐藏敏感信息
        unset($admin['password']);
        return $admin;
    }

    /**
     * 获取管理员列表
     */
    public function getList($where = [], $page = 1, $limit = 10)
    {
        $total = $this->where($where)->count();
        $list = $this->where($where)
            ->field('id, username, truename, status, last_login_time, last_login_ip, create_time, update_time')
            ->page($page, $limit)
            ->order('id desc')
            ->select();

        return ['total' => $total, 'list' => $list];
    }

    /**
     * 获取管理员详情
     */
    public function getInfo($id)
    {
        return $this->where('id', $id)
            ->field('id, email, username, truename, status, last_login_time, last_login_ip, create_time, update_time, avatar')
            ->find();
    }

    /**
     * 更新状态
     */
    public function updateStatus($id, $status)
    {
        return $this->updateData(['status' => $status], ['id' => $id]);
    }

    /**
     * 添加管理员
     */
    public function add($data)
    {
        // 检查用户名是否存在
        if ($this->where('username', $data['username'])->find()) {
            return ['code' => 0, 'msg' => '用户名已存在'];
        }

        // 密码加密
        $data['password'] = md5($data['password']);
        
        if ($this->allowField(true)->save($data)) {
            return ['code' => 1, 'msg' => '添加成功'];
        }
        return ['code' => 0, 'msg' => '添加失败'];
    }

    /**
     * 编辑管理员
     */
    public function edit($data)
    {
        // 如果修改了密码
        if (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->updateData($data, ['id' => $data['id']]);
    }

    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = array(0 => '禁用', 1 => '正常');
        return $status[$data['status']];
    }
} 