<?php
namespace app\admin\controller;

use think\Cache;
use app\common\traits\DeviceInfo;
use think\facade\Db;

class Log extends Base
{
    use DeviceInfo;

    protected $adminLogModel;

    protected function _initialize()
    {
        parent::_initialize();
        $this->adminLogModel = model('AdminLog');
    }

    /**
     * 获取日志列表
     */
    public function getList()
    {
        $params = input('param.');
        $page = isset($params['page']) ? intval($params['page']) : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 10;
        
        // 构建查询条件
        $where = [];
        
        // 操作人筛选
        if (!empty($params['operator'])) {
            $where['operator'] = ['like', '%' . $params['operator'] . '%'];
        }
        
        // 操作类型筛选
        if (!empty($params['operation_type'])) {
            $where['operation_type'] = ['like', '%' . $params['operation_type'] . '%'];
        }
        
        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $where['status'] = $params['status'];
        }
        
        // 时间范围筛选
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $where['operation_time'] = ['between', [$params['start_time'], $params['end_time']]];
        }

        // 排序方式
        $order = !empty($params['order']) ? $params['order'] : 'log_id desc';
        // 获取数据
        $result = $this->adminLogModel->getLogList($where, $order, $page, $limit);
        
        return $this->ajaxSuccess('获取成功', $result);
    }

    /**
     * 删除日志
     */
    public function delete()
    {
        if (!request()->isPost()) {
            $this->ajaxError('请求方式错误');
        }

        $logIds = input('post.log_ids/a');
        if (empty($logIds)) {
            $this->ajaxError('请选择要删除的日志');
        }

        // 执行删除
        $result = $this->adminLogModel->where('log_id', 'in', $logIds)->delete();
        if ($result) {
            $this->addLog('删除日志', '批量删除操作日志，ID：' . implode(',', $logIds), '成功');
            $this->ajaxSuccess('删除成功');
        }

        $this->ajaxError('删除失败');
    }

    /**
     * 清空日志
     */
    public function clear()
    {
        if (!request()->isPost()) {
            return $this->error('请求方式错误');
        }

        $days = input('post.days/d', 30);
        if ($days < 1) {
            return $this->ajaxError('天数不能小于1');
        }
        
        $time = strtotime("-{$days} days");
        if (model('AdminLog')->where('operation_time', '<', date('Y-m-d H:i:s', $time))->delete()) {
            $this->addLog('清空日志', "清空{$days}天前的操作日志", '成功');
            return $this->ajaxSuccess('清空成功');
        }
        return $this->ajaxError('清空失败');
    }

    /**
     * 记录操作日志
     */
    private function addLog($type, $description, $status)
    {
        $adminInfo = Cache::get('token_' . request()->header('Authorization'));
        $logData = [
            'admin_id' => $adminInfo ? $adminInfo['id'] : 0,
            'operator' => $adminInfo ? $adminInfo['username'] : 'unknown',
            'operation_type' => $type,
            'module' => 'admin',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('user-agent', ''),
            'device_info' => json_encode([
                'browser' => $this->getBrowser(),
                'os' => $this->getOS(),
                'device' => $this->getDevice(),
                'ip' => request()->ip(),
                'location' => $this->getIpLocation(request()->ip())
            ]),
            'operation_description' => $description,
            'status' => $status,
            'operation_time' => date('Y-m-d H:i:s')
        ];

        $this->adminLogModel->add($logData);
    }

    /**
     * 导出日志
     */
    public function export()
    {
        // 构建查询条件
        $where = [];
        $params = input('param.');
        
        // 操作人筛选
        if (!empty($params['operator'])) {
            $where['operator'] = ['like', '%' . $params['operator'] . '%'];
        }
        
        // 操作类型筛选
        if (!empty($params['operation_type'])) {
            $where['operation_type'] = ['like', '%' . $params['operation_type'] . '%'];
        }
        
        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $where['status'] = $params['status'];
        }
        
        // 时间范围筛选
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $where['operation_time'] = ['between', [$params['start_time'], $params['end_time']]];
        }

        try {
            // 生成文件名
            $filename = 'admin_log_' . date('YmdHis') . '.csv';
            $filepath = ROOT_PATH . 'runtime/export/' . $filename;
            
            // 确保目录存在
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0777, true);
            }

            // 获取日志数据
            $list = $this->adminLogModel
                ->where($where)
                ->field('log_id,operator,operation_description,operation_time')
                ->order('log_id desc')
                ->select();

            // 打开文件
            $fp = fopen($filepath, 'w');
            
            // 写入表头
            fputcsv($fp, ['日志ID', '用户名', '操作描述', '操作时间']);
            
            // 写入数据
            foreach ($list as $item) {
                fputcsv($fp, [
                    $item['log_id'],
                    $item['operator'],
                    $item['operation_description'],
                    $item['operation_time']
                ]);
            }
            
            // 关闭文件
            fclose($fp);

            // 记录操作日志
            $this->add_log('日志管理', '导出日志', '成功');

            return $this->ajaxSuccess('导出成功', ['url' => '/admin/log/download?file=' . $filename]);
        } catch (\Exception $e) {
            $this->add_log('日志管理', '导出日志：' . $e->getMessage(), '失败');
            return $this->ajaxError('导出失败：' . $e->getMessage());
        }
    }

    /**
     * 下载导出的日志文件
     */
    public function download()
    {
        $filename = input('file');
        if (empty($filename)) {
            return $this->ajaxError('文件不存在');
        }

        $filepath = ROOT_PATH . 'runtime/export/' . $filename;
        if (!file_exists($filepath)) {
            return $this->ajaxError('文件不存在');
        }

        // 设置响应头
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        // 输出文件内容
        readfile($filepath);
        
        // 删除文件
        unlink($filepath);
        exit;
    }
} 