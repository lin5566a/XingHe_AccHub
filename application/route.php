<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
use think\facade\Route as FacadeRoute;

// 处理 OPTIONS 请求
Route::rule('api/:any', function() {
    header("Access-Control-Allow-Origin: " . request()->header("Origin"));
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With");
    header("HTTP/1.1 200 OK");
    exit;
}, 'OPTIONS');

Route::rule('v1/:any', function() {
    header("Access-Control-Allow-Origin: " . request()->header("Origin"));
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, X-Visitor-UUID, X-Channel-Code");
    header("HTTP/1.1 200 OK");
    exit;
}, 'OPTIONS');

// 静态资源路由
Route::rule('uploads/export/:file', 'admin/Index/download', 'GET');

// API路由组（后台）
Route::group('api', function () {
    // 登录接口
    Route::rule('login', 'admin/login/login', 'POST');
    Route::rule('logout', 'admin/login/logout', 'POST');
    Route::rule('login/logs', 'admin/login/getLoginLogs', 'GET');
    Route::rule('captcha', 'admin/login/captcha', 'GET');
    Route::rule('verify_password', 'admin/login/verifyPassword', 'POST');
    Route::rule('change_password', 'admin/login/changePassword', 'POST');
    
    // 日志管理
    Route::rule('admin/logs/export$', 'admin/log/export', 'GET|POST');
    Route::rule('admin/logs/delete$', 'admin/log/delete', 'POST');
    Route::rule('admin/logs/clear$', 'admin/log/clear', 'POST');
    Route::rule('admin/logs$', 'admin/log/getList', 'GET');
    
    // 系统设置相关路由
    Route::rule('admin/system/info', 'admin/system/getSystemInfo', 'GET');
    Route::rule('admin/system/info/update', 'admin/system/updateSystemInfo', 'POST');
    Route::rule('admin/system/logo/upload', 'admin/system/uploadLogo', 'POST');
    Route::rule('admin/system/usdt/rate', 'admin/system/updateUsdtRate', 'POST');
    
    Route::rule('admin/system/customer', 'admin/system/getCustomerService', 'GET');
    Route::rule('admin/system/customer/update', 'admin/system/updateCustomerService', 'POST');
    
    Route::rule('admin/system/email', 'admin/system/getEmailConfig', 'GET');
    Route::rule('admin/system/email/update', 'admin/system/updateEmailConfig', 'POST');
    
    Route::rule('admin/system/payment', 'admin/system/getPaymentConfig', 'GET');
    Route::rule('admin/system/payment/update', 'admin/system/updatePaymentConfig', 'POST');
    
    // 邮件测试接口
    Route::rule('admin/system/email/test', 'admin/system/testEmail', 'POST');
    
    // 系统文档相关路由
    Route::rule('admin/system/content', 'admin/system/getContent', 'GET');
    Route::rule('admin/system/content/update', 'admin/system/updateContent', 'POST');
    
    // 弹窗配置相关路由
    Route::rule('admin/popup/config', 'admin/PopupConfig/getConfig', 'GET');
    Route::rule('admin/popup/config/update', 'admin/PopupConfig/updateConfig', 'POST');
    Route::rule('admin/popup/config/status', 'admin/PopupConfig/updateStatus', 'POST');
    Route::rule('admin/popup/config/reset', 'admin/PopupConfig/resetConfig', 'POST');
    
    // 管理员相关路由
    Route::rule('admin/manager/list', 'admin/Manager/index', 'GET');
    Route::rule('admin/manager/info', 'admin/Manager/info', 'GET');
    Route::rule('admin/manager/add', 'admin/Manager/add', 'POST');
    Route::rule('admin/manager/update', 'admin/Manager/update', 'POST');
    Route::rule('admin/manager/delete', 'admin/Manager/delete', 'POST');
    Route::rule('admin/manager/avatar', 'admin/Manager/updateAvatar', 'POST');
    
    // 帮助中心相关路由
    Route::rule('admin/help/categories', 'admin/Help/categories', 'GET');
    
    // 文档管理相关路由
    Route::rule('admin/document/list', 'admin/Document/index', 'GET');
    Route::rule('admin/document/add', 'admin/Document/add', 'POST');
    Route::rule('admin/document/update', 'admin/Document/update', 'POST');
    Route::rule('admin/document/delete', 'admin/Document/delete', 'POST');
    
    // 站内信相关路由
    Route::rule('admin/message/list', 'admin/Message/index', 'GET');
    Route::rule('admin/message/solve', 'admin/Message/solve', 'POST');
    Route::rule('admin/message/batchSolve', 'admin/Message/batchSolve', 'POST');
    Route::rule('admin/message/delete', 'admin/Message/delete', 'POST');
    
    // 商品分类管理
    Route::rule('admin/category/index', 'admin/Category/index', 'GET');
    Route::rule('admin/category/add', 'admin/Category/add', 'POST');
    Route::rule('admin/category/edit', 'admin/Category/edit', 'POST');
    Route::rule('admin/category/delete', 'admin/Category/delete', 'POST');
    
    // 商品描述模板相关路由
    Route::rule('admin/template/list', 'admin/Template/index', 'GET');
    Route::rule('admin/template/categories', 'admin/Template/categories', 'GET');
    Route::rule('admin/template/add', 'admin/Template/add', 'POST');
    Route::rule('admin/template/edit', 'admin/Template/edit', 'POST');
    Route::rule('admin/template/delete', 'admin/Template/delete', 'POST');
    
    // 用户管理相关路由
    Route::rule('admin/user/list', 'admin/User/index', 'GET');
    Route::rule('admin/user/add', 'admin/User/add', 'POST');
    Route::rule('admin/user/edit', 'admin/User/edit', 'POST');
    Route::rule('admin/user/delete', 'admin/User/delete', 'POST');
    Route::rule('admin/user/balanceOperate', 'admin/User/balanceOperate', 'POST');
    Route::rule('admin/user/balanceLog', 'admin/User/balanceLog', 'GET');
    Route::rule('admin/user/resetPassword', 'admin/User/resetPassword', 'POST');
    Route::rule('admin/user/balanceLogTypes', 'admin/User/balanceLogTypes', 'POST');
    
    // 商品管理相关路由
    Route::rule('admin/product/list', 'admin/Product/index', 'GET');
    Route::rule('admin/product/add', 'admin/Product/add', 'POST');
    Route::rule('admin/product/edit', 'admin/Product/edit', 'POST');
    Route::rule('admin/product/delete', 'admin/Product/delete', 'POST');
    Route::rule('admin/product/status', 'admin/Product/updateStatus', 'POST');
    Route::rule('admin/product/sort', 'admin/Product/updateSort', 'POST');
    Route::rule('admin/product/options', 'admin/Product/getOptions', 'GET');
    
    // 商品视频管理相关路由
    Route::rule('admin/product/video/info', 'admin/Product/getVideoInfo', 'GET');
    Route::rule('admin/product/video/upload', 'admin/Product/uploadVideo', 'POST');
    Route::rule('admin/product/video/delete', 'admin/Product/deleteVideo', 'POST');
    Route::rule('admin/product/video/updateStatus', 'admin/Product/updateVideoStatus', 'POST');
    
    // 商品折扣管理路由
    Route::rule('admin/product/discount/set', 'admin/Product/setDiscount', 'POST');
    Route::rule('admin/product/discount/batchSet', 'admin/Product/batchSetDiscount', 'POST');
    Route::rule('admin/product/discount/info', 'admin/Product/getDiscountInfo', 'GET');
    Route::rule('admin/product/search', 'admin/Product/searchProducts', 'GET');
    
    // 商品库存管理
    Route::rule('admin/product_stock/list', 'admin/ProductStock/index', 'GET');
    Route::rule('admin/product_stock/add', 'admin/ProductStock/add', 'POST');
    Route::rule('admin/product_stock/edit', 'admin/ProductStock/edit', 'POST');
    Route::rule('admin/product_stock/delete', 'admin/ProductStock/delete', 'POST');
    Route::rule('admin/product_stock/batchDelete', 'admin/ProductStock/batchDelete', 'POST');
    Route::rule('admin/product_stock/import', 'admin/ProductStock/import', 'POST');
    Route::rule('admin/product_stock/export', 'admin/ProductStock/export', 'POST');
    Route::rule('admin/product_stock/generateBatchId', 'admin/ProductStock/generateBatchId', 'POST');
    Route::rule('admin/product_stock/searchBatchIds', 'admin/ProductStock/searchBatchIds', 'GET');
    Route::rule('admin/product_stock/batchUpdateCostPrice', 'admin/ProductStock/batchUpdateCostPrice', 'POST');
    Route::rule('admin/product_stock/getStockStats', 'admin/ProductStock/getStockStats', 'GET');
    
    // 订单管理相关路由
    Route::rule('admin/order/list', 'admin/ProductOrder/index', 'GET');
    Route::rule('admin/order/export', 'admin/ProductOrder/export', 'POST');
    Route::rule('admin/order/delete', 'admin/ProductOrder/delete', 'POST');
    Route::rule('admin/order/deliver', 'admin/ProductOrder/delivery', 'POST');
    Route::rule('admin/order/deliveryManual', 'admin/ProductOrder/deliveryManual', 'POST');
    Route::rule('admin/order/refund', 'admin/ProductOrder/refund', 'POST');
    Route::rule('admin/order/resendEmail', 'admin/ProductOrder/resendEmail', 'POST');
    Route::rule('admin/order/overview', 'admin/ProductOrder/overview', 'GET');
    Route::rule('admin/order/options', 'admin/ProductOrder/getOptions', 'GET');
    Route::rule('admin/order/productSalesDetail', 'admin/ProductOrder/productSalesDetail', 'GET');
    Route::rule('admin/order/productSalesDetailExport', 'admin/ProductOrder/productSalesDetailExport', 'POST');
    Route::rule('admin/product_order/updateCardInfo', 'admin/ProductOrder/updateCardInfo', 'POST');
    
    // 公告管理
    Route::rule('admin/announcement/list', 'admin/Announcement/index', 'GET');
    Route::rule('admin/announcement/add', 'admin/Announcement/add', 'POST');
    Route::rule('admin/announcement/edit', 'admin/Announcement/edit', 'POST');
    Route::rule('admin/announcement/delete', 'admin/Announcement/delete', 'POST');
    Route::rule('admin/announcement/batchDelete', 'admin/Announcement/batchDelete', 'POST');
    
    // 会员等级管理
    Route::rule('admin/member_level/list', 'admin/MemberLevel/getList', 'GET');
    Route::rule('admin/member_level/edit', 'admin/MemberLevel/edit', 'POST');
    
    // 充值订单管理
    Route::rule('admin/recharge_order/list', 'admin/RechargeOrder/index', 'GET');
    Route::rule('admin/recharge_order/export', 'admin/RechargeOrder/export', 'POST');
    Route::rule('admin/recharge_order/refund', 'admin/RechargeOrder/refund', 'POST');
    Route::rule('admin/recharge_order/confirm', 'admin/RechargeOrder/confirm', 'POST');
    Route::rule('admin/recharge_order/getOptions', 'admin/RechargeOrder/getOptions', 'GET');
    
    // 成本中心
    Route::rule('admin/cost_center/list', 'admin/CostCenter/index', 'GET');
    Route::rule('admin/cost_center/add_manual_cost', 'admin/CostCenter/addManualCost', 'POST');
    Route::rule('admin/cost_center/export', 'admin/CostCenter/export', 'GET');
    Route::rule('admin/cost_center/get_type_options', 'admin/CostCenter/getCostTypeOptions', 'POST');
    
    // 支付通道管理
    Route::rule('admin/payment_channel/index', 'admin/PaymentChannel/index', 'GET');
    Route::rule('admin/payment_channel/add', 'admin/PaymentChannel/add', 'GET|POST');
    Route::rule('admin/payment_channel/edit', 'admin/PaymentChannel/edit', 'GET|POST');
    Route::rule('admin/payment_channel/delete', 'admin/PaymentChannel/delete', 'POST');
    Route::rule('admin/payment_channel/editChannelWithRates', 'admin/PaymentChannel/editChannelWithRates', 'GET|POST');
    Route::rule('admin/payment_channel/getChannelInfo', 'admin/PaymentChannel/getChannelInfo', 'GET');
    
    // 全站流量统计
    Route::rule('admin/traffic_stats', 'admin/TrafficStats/getStats', 'GET');
    Route::rule('admin/traffic_trend', 'admin/TrafficStats/getTrendData', 'GET');
    Route::rule('admin/traffic_channels', 'admin/TrafficStats/getChannelList', 'GET');
    Route::rule('admin/traffic_channel_stats', 'admin/TrafficStats/getChannelStats', 'GET');
    
    // 通知中心
    Route::rule('admin/notifications/getNotifications', 'admin/NotificationCenter/getNotifications', 'GET');
    Route::rule('admin/notifications/mark_read', 'admin/NotificationCenter/markAsRead', 'POST');
    Route::rule('admin/notifications/mark_all_read_by_type', 'admin/NotificationCenter/markAllAsReadByType', 'POST');
    Route::rule('admin/notifications/mark_all_read', 'admin/NotificationCenter/markAllAsRead', 'POST');
    Route::rule('admin/notifications/stats', 'admin/NotificationCenter/getStats', 'GET');
    
    // 渠道配置管理
    Route::rule('admin/channel_config/index', 'admin/ChannelConfig/index', 'GET');
    Route::rule('admin/channel_config/add', 'admin/ChannelConfig/add', 'GET|POST');
    Route::rule('admin/channel_config/edit', 'admin/ChannelConfig/edit', 'GET|POST');
    Route::rule('admin/channel_config/delete', 'admin/ChannelConfig/delete', 'POST');
    Route::rule('admin/channel_config/set_default', 'admin/ChannelConfig/setDefault', 'POST');
    Route::rule('admin/channel_config/update_status', 'admin/ChannelConfig/updateStatus', 'POST');
    Route::rule('admin/channel_config/copy_link', 'admin/ChannelConfig/copyLink', 'POST');
});

// 前台路由组
Route::group('v1', function() {
    // 用户相关路由
    Route::rule('user/register', 'index/user/register', 'POST');
    Route::rule('user/captcha', 'index/user/captcha', 'GET');
    Route::rule('user/sendRegisterCaptcha', 'index/user/sendRegisterCaptcha', 'POST');
    Route::rule('user/sendLoginCaptcha', 'index/user/sendLoginCaptcha', 'POST');
    Route::rule('user/login', 'index/user/login', 'POST');
    Route::rule('user/logout', 'index/user/logout', 'POST');
    Route::rule('user/logoutAll', 'index/user/logoutAll', 'POST');
    Route::rule('user/onlineDevices', 'index/user/getOnlineDevices', 'GET');
    Route::rule('user/kickDevice', 'index/user/kickDevice', 'POST');
    Route::get('user/balance', 'index/UserCenter/getBalance');
    
    // 商品相关路由
    Route::rule('product/index', 'index/product/index', 'GET');
    Route::rule('product/customerLinks', 'index/product/getCustomerLinks', 'GET');
    Route::rule('product/search', 'index/product/search', 'GET');
    
    // 商品详情
    Route::get('product/detail', 'index/ProductDetail/index');
    // Route::get('product/getPackages', 'index/ProductDetail/getPackages');
    Route::post('product/notifyReplenish', 'index/ProductDetail/notifyReplenish');
    
    // 订单相关路由
    Route::post('order/create', 'index/Order/create');
    Route::post('order/sendQueryCaptcha', 'index/Order/sendQueryCaptcha');
    Route::post('order/query', 'index/Order/query');
    Route::post('order/checkTimeout', 'index/Order/checkTimeout');
    Route::post('order/detail', 'index/Order/detail');
    Route::post('order/payWithBalance', 'index/Order/payWithBalance');
    
    // 充值相关路由
    Route::post('recharge/create', 'index/Recharge/create');
    Route::post('recharge/pay', 'index/Recharge/pay');
    Route::get('recharge/query', 'index/Recharge/query');
    Route::get('recharge/detail', 'index/Recharge/detail');
    
    // 帮助中心相关路由
    Route::get('help/categories', 'index/Help/categories');
    Route::post('help/documents', 'index/Help/documents');
    Route::post('help/detail', 'index/Help/detail');
    
    // 系统内容相关路由
    Route::get('system/content', 'index/System/getContent');
    Route::get('system/info', 'index/System/getSystemInfo');
    Route::get('system/popup', 'index/System/getPopupContent');
    Route::get('system/customerService', 'index/System/getCustomerService');
    
    // 个人中心相关路由
    Route::get('user/info', 'index/UserCenter/getInfo');
    Route::post('user/updatePassword', 'index/UserCenter/updatePassword');
    Route::get('user/orders', 'index/UserCenter/getOrders');
    Route::post('user/logout', 'index/UserCenter/logout');
    Route::get('user/balanceLog', 'index/UserCenter/balanceLog');
    Route::get('user/balanceLogTypes', 'index/UserCenter/balanceLogTypes');
    
    // 支付相关路由
    Route::post('payment/createPayment', 'index/Payment/createPayment');
    Route::get('payment/query', 'index/Payment/query');
    Route::get('payment/productVideo', 'index/Payment/getProductVideo');
    Route::rule('payment/notify', 'index/Payment/notify', 'GET|POST');
    Route::get('payment/return', 'index/Payment/return');
    Route::rule('payment/speedNotify', 'index/Payment/speedNotify', 'GET|POST');
    Route::rule('payment/k4Notify', 'index/Payment/k4Notify', 'GET|POST');
    Route::rule('payment/pmpPayNotify', 'index/Payment/pmpPayNotify', 'GET|POST');
    
    // 公告查询
    Route::get('announcement/get', 'index/Announcement/get');
    
    // 会员等级前台接口
    Route::get('member_level/list', 'index/MemberLevel/getList');
});


