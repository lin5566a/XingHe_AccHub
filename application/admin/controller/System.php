<?php
namespace app\admin\controller;
use app\common\utils\Upload;
use app\common\utils\Email;
use app\common\traits\ApiResponse;

class System extends Base
{
    use ApiResponse;

    protected $systemInfoModel;
    protected $customerServiceModel;
    protected $emailConfigModel;
    protected $paymentConfigModel;

    protected function _initialize()
    {
        parent::_initialize();
        $this->systemInfoModel = model('SystemInfo');
        $this->customerServiceModel = model('CustomerService');
        $this->emailConfigModel = model('EmailConfig');
        $this->paymentConfigModel = model('PaymentConfig');
    }

    /**
     * 获取系统基本信息
     */
    public function getSystemInfo()
    {
        $info = $this->systemInfoModel->getInfo();
        return $this->ajaxSuccess('获取成功', $info);
    }

    /**
     * 更新系统订单过期时间
     */
    public function updateOrderTimeout()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新系统订单过期时间：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        if (empty($data)) {
            $this->add_log('系统管理', '更新系统订单过期时间：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        // 验证必填字段
        if (empty($data['order_timeout'])) {
            $this->add_log('系统管理', '更新系统订单过期时间：订单过期时间不能为空', '失败');
            return $this->ajaxError('订单过期时间不能为空');
        }
        if (!is_numeric($data['order_timeout']) || $data['order_timeout'] < 0) {
            $this->add_log('系统管理', '更新系统订单过期时间：订单过期时间必须为正整数', '失败');
            return $this->ajaxError('订单过期时间必须为正整数');
        }

        $info = model('SystemInfo')->find();
        if (!$info) {
            // 如果记录不存在，则创建新记录
            if (model('SystemInfo')->save($data)) {
                $this->add_log('系统管理', '新增系统订单过期时间', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增系统订单过期时间', '失败');
            return $this->ajaxError('保存失败');
        }

        // 更新已存在的记录
        $result = model('SystemInfo')->save($data, ['id' => $info['id']]);
        if ($result !== false) { // 修改判断条件，允许数据无变化的情况
            $this->add_log('系统管理', '更新系统订单过期时间', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新系统订单过期时间', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 更新系统基本信息
     */
    public function updateSystemInfo()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新系统基本信息：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        if (empty($data)) {
            $this->add_log('系统管理', '更新系统基本信息：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        // 验证必填字段
        if (empty($data['system_name'])) {
            $this->add_log('系统管理', '更新系统基本信息：系统名称不能为空', '失败');
            return $this->ajaxError('系统名称不能为空');
        }
        if (empty($data['system_logo'])) {
            $this->add_log('系统管理', '更新系统基本信息：系统Logo不能为空', '失败');
            return $this->ajaxError('系统Logo不能为空');
        }
        if (empty($data['copyright_info'])) {
            $this->add_log('系统管理', '更新系统基本信息：版权信息不能为空', '失败');
            return $this->ajaxError('版权信息不能为空');
        }
        
        // 处理提示音设置（转换为整数）
        if (isset($data['manual_shipment_sound'])) {
            $data['manual_shipment_sound'] = $data['manual_shipment_sound'] ? 1 : 0;
        }
        if (isset($data['replenishment_sound'])) {
            $data['replenishment_sound'] = $data['replenishment_sound'] ? 1 : 0;
        }

        $info = model('SystemInfo')->find();
        if (!$info) {
            // 如果记录不存在，则创建新记录
            if (model('SystemInfo')->save($data)) {
                $this->add_log('系统管理', '新增系统基本信息', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增系统基本信息', '失败');
            return $this->ajaxError('保存失败');
        }

        // 更新已存在的记录
        $result = model('SystemInfo')->save($data, ['id' => $info['id']]);
        if ($result !== false) { // 修改判断条件，允许数据无变化的情况
            $this->add_log('系统管理', '更新系统基本信息', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新系统基本信息', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 获取系统配置
     */
    public function getSystemConfig()
    {
        $config = model('SystemInfo')->find();
        return $this->ajaxSuccess('获取成功', $config);
    }

    /**
     * 更新系统配置
     */
    public function updateSystemConfig()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新系统配置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        $config = model('SystemInfo')->find();
        if (!$config) {
            if (model('SystemInfo')->save($data)) {
                $this->add_log('系统管理', '新增系统配置', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增系统配置', '失败');
            return $this->ajaxError('保存失败');
        }

        if (model('SystemInfo')->save($data, ['id' => $config['id']])) {
            $this->add_log('系统管理', '更新系统配置', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新系统配置', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 获取客服配置
     */
    public function getCustomerService()
    {
        $config = model('CustomerService')->find();
        return $this->ajaxSuccess('获取成功', $config);
    }

    /**
     * 更新客服配置
     */
    public function updateCustomerService()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新客服配置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        // 处理开关字段（转换为整数）
        if (isset($data['tg_show'])) {
            $data['tg_show'] = $data['tg_show'] ? 1 : 0;
        }
        if (isset($data['group_show'])) {
            $data['group_show'] = $data['group_show'] ? 1 : 0;
        }
        if (isset($data['online_show'])) {
            $data['online_show'] = $data['online_show'] ? 1 : 0;
        }
        
        $config = model('CustomerService')->find();
        if (!$config) {
            if (model('CustomerService')->save($data)) {
                $this->add_log('系统管理', '新增客服配置', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增客服配置', '失败');
            return $this->ajaxError('保存失败');
        }

        $result = model('CustomerService')->save($data, ['id' => $config['id']]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新客服配置', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新客服配置', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 获取邮件配置
     */
    public function getEmailConfig()
    {
        $config = model('EmailConfig')->find();
        return $this->ajaxSuccess('获取成功', $config);
    }

    /**
     * 更新邮件配置
     */
    public function updateEmailConfig()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新邮件配置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        $config = model('EmailConfig')->find();
        if (!$config) {
            if (model('EmailConfig')->save($data)) {
                $this->add_log('系统管理', '新增邮件配置', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增邮件配置', '失败');
            return $this->ajaxError('保存失败');
        }

        $result = model('EmailConfig')->save($data, ['id' => $config['id']]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新邮件配置', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新邮件配置', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 获取支付配置
     */
    public function getPaymentConfig()
    {
        $config = model('PaymentConfig')->find();
        return $this->ajaxSuccess('获取成功', $config);
    }

    /**
     * 更新支付配置
     */
    public function updatePaymentConfig()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新支付配置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        // 验证数据
        $validate = \think\Loader::validate('PaymentConfig');
        if (!$validate->check($data)) {
            $this->add_log('系统管理', '更新支付配置：验证失败 - ' . $validate->getError(), '失败');
            return $this->ajaxError($validate->getError());
        }
        
        $config = model('PaymentConfig')->find();
        if (!$config) {
            if (model('PaymentConfig')->save($data)) {
                $this->add_log('系统管理', '新增支付配置', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增支付配置', '失败');
            return $this->ajaxError('保存失败');
        }

        $result = model('PaymentConfig')->save($data, ['id' => $config['id']]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新支付配置', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新支付配置', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 上传系统Logo
     */
    public function uploadLogo()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '上传系统Logo：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $file = $this->request->file('logo');
        if (!$file) {
            $this->add_log('系统管理', '上传系统Logo：未选择文件', '失败');
            return $this->ajaxError('请选择要上传的Logo');
        }

        $result = \app\common\utils\Upload::image($file, 'logo', [
            'size' => 2097152, // 2M
            'ext' => 'jpg,jpeg,png,gif'
        ]);
        
        if ($result['code'] === 0) {
            $this->add_log('系统管理', '上传系统Logo：' . $result['msg'], '失败');
            return $this->ajaxError($result['msg']);
        }

        $this->add_log('系统管理', '上传系统Logo', '成功');
        return $this->ajaxSuccess('上传成功', ['path' => $result['data']['file_path']]);
    }

    /**
     * 测试邮件发送
     */
    public function testEmail()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '测试邮件发送：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $email = $this->request->post('email');
        if (empty($email)) {
            $this->add_log('系统管理', '测试邮件发送：未输入邮箱', '失败');
            return $this->ajaxError('请输入测试邮箱');
        }

        $config = model('EmailConfig')->find();
        if (empty($config)) {
            $this->add_log('系统管理', '测试邮件发送：未配置邮件参数', '失败');
            return $this->ajaxError('请先配置邮件参数');
        }

        $result = Email::send($email, '测试邮件', '这是一封测试邮件', $config);
        
        if ($result['code']) {
            $this->add_log('系统管理', '测试邮件发送：' . $email, '成功');
            return $this->ajaxSuccess($result['msg']);
        }
        $this->add_log('系统管理', '测试邮件发送：' . $email . ' - ' . $result['msg'], '失败');
        return $this->ajaxError($result['msg']);
    }

    /**
     * 获取系统设置
     */
    public function getConfig()
    {
        $config = model('SystemInfo')->find();
        return $this->ajaxSuccess('获取成功', $config);
    }

    /**
     * 更新系统设置
     */
    public function updateConfig()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新系统设置：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        
        $config = model('SystemInfo')->find();
        if (!$config) {
            if (model('SystemInfo')->save($data)) {
                $this->add_log('系统管理', '新增系统设置', '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增系统设置', '失败');
            return $this->ajaxError('保存失败');
        }

        $result = model('SystemInfo')->save($data, ['id' => $config['id']]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新系统设置', '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新系统设置', '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 上传图片
     */
    public function uploadImage()
    {
        $file = request()->file('file');
        if (!$file) {
            $this->add_log('系统管理', '上传图片：未选择文件', '失败');
            return $this->ajaxError('请选择上传文件');
        }

        $info = $file->validate(['size'=>1567800, 'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
            $this->add_log('系统管理', '上传图片：' . $info->getSaveName(), '成功');
            return $this->ajaxSuccess('上传成功', ['path' => '/uploads/' . $info->getSaveName()]);
        }
        $this->add_log('系统管理', '上传图片：' . $file->getError(), '失败');
        return $this->ajaxError($file->getError());
    }

    /**
     * 获取系统内容
     */
    public function getSystemContent()
    {
        $type = $this->request->param('type');
        if (empty($type)) {
            return $this->ajaxError('参数错误');
        }

        $content = model('SystemContent')->where('type', $type)->find();
        return $this->ajaxSuccess('获取成功', $content);
    }

    /**
     * 更新系统内容
     */
    public function updateSystemContent()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新系统内容：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        if (empty($data['type']) || empty($data['content'])) {
            $this->add_log('系统管理', '更新系统内容：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        $content = model('SystemContent')->where('type', $data['type'])->find();
        if (!$content) {
            if (model('SystemContent')->save($data)) {
                $this->add_log('系统管理', '新增系统内容：' . $data['type'], '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增系统内容：' . $data['type'], '失败');
            return $this->ajaxError('保存失败');
        }

        $result = model('SystemContent')->save($data, ['id' => $content['id']]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新系统内容：' . $data['type'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新系统内容：' . $data['type'], '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 获取文档列表
     */
    public function getDocuments()
    {
        $where = [];
        $category = $this->request->param('category', '');
        if ($category) {
            $where['category'] = $category;
        }

        $list = model('Documents')->where($where)->order('sort_order asc')->select();
        return $this->ajaxSuccess('获取成功', $list);
    }

    /**
     * 更新文档
     */
    public function updateDocument()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新文档：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        if (empty($data['title']) || empty($data['content'])) {
            $this->add_log('系统管理', '更新文档：请填写完整信息', '失败');
            return $this->ajaxError('请填写完整信息');
        }

        if (isset($data['id']) && $data['id']) {
            $result = model('Documents')->save($data, ['id' => $data['id']]);
            if ($result !== false) {
                $this->add_log('系统管理', '更新文档：' . $data['title'], '成功');
                return $this->ajaxSuccess('更新成功');
            }
            $this->add_log('系统管理', '更新文档：' . $data['title'], '失败');
            return $this->ajaxError('更新失败');
        }

        if (model('Documents')->save($data)) {
            $this->add_log('系统管理', '新增文档：' . $data['title'], '成功');
            return $this->ajaxSuccess('添加成功');
        }
        $this->add_log('系统管理', '新增文档：' . $data['title'], '失败');
        return $this->ajaxError('添加失败');
    }

    /**
     * 删除文档
     */
    public function deleteDocument()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '删除文档：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            $this->add_log('系统管理', '删除文档：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        $document = model('Documents')->find($id);
        if (!$document) {
            $this->add_log('系统管理', '删除文档：文档不存在', '失败');
            return $this->ajaxError('文档不存在');
        }

        if (model('Documents')->where('id', $id)->delete()) {
            $this->add_log('系统管理', '删除文档：' . $document['title'], '成功');
            return $this->ajaxSuccess('删除成功');
        }
        $this->add_log('系统管理', '删除文档：' . $document['title'], '失败');
        return $this->ajaxError('删除失败');
    }

    /**
     * 获取系统文档内容
     * @param string $type 文档类型：使用协议/关于我们
     */
    public function getContent()
    {
        $type = $this->request->param('type');
        if (empty($type)) {
            return $this->ajaxError('参数错误');
        }

        $content = model('SystemContent')->where('type', $type)->find();
        if (!$content) {
            // 如果不存在，创建默认内容
            $defaultContent = [
                'type' => $type,
                'title' => $type,
                'content' => '',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')  // 手动设置创建时间
            ];
            model('SystemContent')->save($defaultContent);
            return $this->ajaxSuccess('获取成功', $defaultContent);
        }

        return $this->ajaxSuccess('获取成功', $content);
    }

    /**
     * 更新系统文档内容
     */
    public function updateContent()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新系统文档：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $data = $this->request->post();
        if (empty($data['type']) || empty($data['title']) || empty($data['content'])) {
            $this->add_log('系统管理', '更新系统文档：请填写完整信息', '失败');
            return $this->ajaxError('请填写完整信息');
        }

        // 验证状态值
        if (isset($data['status'])) {
            $status = intval($data['status']);  // 确保是整数
            if (!in_array($status, [1, 2], true)) {  // 使用严格模式比较
                $this->add_log('系统管理', '更新系统文档：状态值错误', '失败');
                return $this->ajaxError('状态值只能是1或2');
            }
            $data['status'] = $status;  // 使用转换后的整数值
        }

        // 查找是否存在
        $content = model('SystemContent')->where('type', $data['type'])->find();
        if (!$content) {
            // 不存在则新增，确保设置默认状态
            if (!isset($data['status'])) {
                $data['status'] = 1;  // 默认开启
            }
            if (model('SystemContent')->save($data)) {
                $this->add_log('系统管理', '新增系统文档：' . $data['type'], '成功');
                return $this->ajaxSuccess('保存成功');
            }
            $this->add_log('系统管理', '新增系统文档：' . $data['type'], '失败');
            return $this->ajaxError('保存失败');
        }

        // 存在则更新
        $result = model('SystemContent')->save($data, ['id' => $content['id']]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新系统文档：' . $data['type'], '成功');
            return $this->ajaxSuccess('更新成功');
        }
        $this->add_log('系统管理', '更新系统文档：' . $data['type'], '失败');
        return $this->ajaxError('更新失败');
    }

    /**
     * 更新文档状态
     */
    public function updateContentStatus()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新文档状态：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $type = $this->request->post('type');
        $status = $this->request->post('status');

        if (empty($type) || !in_array($status, [1, 2])) {
            $this->add_log('系统管理', '更新文档状态：参数错误', '失败');
            return $this->ajaxError('参数错误');
        }

        $content = model('SystemContent')->where('type', $type)->find();
        if (!$content) {
            $this->add_log('系统管理', '更新文档状态：文档不存在', '失败');
            return $this->ajaxError('文档不存在');
        }

        $result = model('SystemContent')->where('type', $type)->update(['status' => $status]);
        if ($result !== false) {
            $this->add_log('系统管理', '更新文档状态：' . $type . ' - ' . $status, '成功');
            return $this->ajaxSuccess('状态更新成功');
        }
        $this->add_log('系统管理', '更新文档状态：' . $type . ' - ' . $status, '失败');
        return $this->ajaxError('状态更新失败');
    }

    /**
     * 更新USDT汇率
     */
    public function updateUsdtRate()
    {
        if (!$this->request->isPost()) {
            $this->add_log('系统管理', '更新USDT汇率：请求方式错误', '失败');
            return $this->ajaxError('请求方式错误');
        }

        $rate = $this->request->post('rate');
        if (!is_numeric($rate) || $rate <= 0) {
            $this->add_log('系统管理', '更新USDT汇率：汇率必须为大于0的数字', '失败');
            return $this->ajaxError('汇率必须为大于0的数字');
        }

        $config = model('PaymentConfig')->find();
        if (!$config) {
            // 如果配置不存在，创建新配置
            $result = model('PaymentConfig')->save(['usdt_rate' => $rate]);
        } else {
            // 更新现有配置
            $result = model('PaymentConfig')->save(['usdt_rate' => $rate], ['id' => $config['id']]);
        }

        if ($result !== false) {
            $this->add_log('系统管理', '更新USDT汇率：' . $rate, '成功');
            return $this->ajaxSuccess('更新成功');
        }
        
        $this->add_log('系统管理', '更新USDT汇率：更新失败', '失败');
        return $this->ajaxError('更新失败');
    }
} 