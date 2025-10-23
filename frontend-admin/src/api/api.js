import request from '@/utils/request'
export default{
	//登录
	dologin(data){
		return request({url: '/api/login',method: 'POST',data})		
	},
	//验证码图片获取
	captcha(data){
		return request({url: '/api/captcha',method: 'get',params: data})
	},
	//数据概览
	overview(data){
		return request({url: '/api/admin/order/overview',method: 'get',params: data})		
	},
	
	//订单列表
	orderList(data){
		return request({url: '/api/admin/order/list',method: 'get',params: data})	
		
	},
	//商品管理分类
	categoryList(data){
		return request({url: '/api/admin/category/index',method: 'get',params: data})	
	},
	//发货方式  支付方式   订单状态 筛选项查询
	queryOptions(data){
		return request({url: '/api/admin/order/options',method: 'get',params: data})	
	},
	//导出全部订单
	exportAll(data){
		return request({url: '/api/admin/order/export',method: 'POST',data})	
	},
	//订单发货
	orderDeliver(data){
		return request({url: '/api/admin/order/deliver',method: 'POST',data})	
	},
	//删除订单
	orderDelete(data){
		return request({url: '/api/admin/order/delete',method: 'POST',data})	
	},
	//重发邮件
	orderResend(data){
		return request({url: '/api/admin/order/resendEmail',method: 'POST',data})	
	},
	//商品列表查询
	productList(data){
		return request({url: '/api/admin/product/list',method: 'get',params: data})	
	},
	//商品管理 新增商品
	
	productAdd(data){
		return request({url: '/api/admin/product/add',method: 'POST',data})	
	},
	//商品管理  编辑商品
	productEdit(data){
		return request({url: '/api/admin/product/edit',method: 'POST',data})	
	},
	//商品管理  商品状态修改
	productStatus(data){
		return request({url: '/api/admin/product/status',method: 'POST',data})	
	},
	//商品管理  删除商品
	productDelete(data){
		return request({url: '/api/admin/product/delete',method: 'POST',data})	
	},
	//商品管理 商品列表 包管理 列表查询
	productBugList(data){
		return request({url: '/admin/product/getPackages',method: 'get',params: data})	
	},
	//商品管理  商品列表 包管理  删除包	
	deletePackage(data){
		return request({url: '/admin/product/deletePackage',method: 'POST',data})	 
	},
	//商品管理  商品列表 包管理  新增包	
	addPackage(data){
		return request({url: '/admin/product/addPackage',method: 'POST',data})	 
	},
	//添加安装包地域限制
	
	// addPackage(data){ 
	// 	return request({url: '/api/admin/product/addPackage',method: 'POST', data})
	// },
	//商品管理  商品列表 包管理  更新包	
	
	editPackage(data){
		return request({url: '/admin/product/editPackage',method: 'POST',data})	 
	},
	
	// editPackage(data){
	// 	return request({url: '/api/admin/product/editPackage',method: 'POST',data})	 
	// },
	
	//商品管理 库存列表查询
	productStockList(data){
		return request({url: '/api/admin/product_stock/list',method: 'get',params: data})	
	},
	//商品管理 新增库存
	productStockAdd(data){
		return request({url: '/api/admin/product_stock/add',method: 'POST',data})	
	},
	//商品管理 编辑库存
	productStockEdit(data){
		return request({url: '/api/admin/product_stock/edit',method: 'POST',data})	
	},
	//商品管理 删除库存
	productStockDelete(data){
		return request({url: '/api/admin/product_stock/delete',method: 'POST',data})	
	},
	//商品管理  批量删除
	productStockBatchDelete(data){
		return request({url: '/api/admin/product_stock/batchDelete',method: 'POST',data})	
	},
	//商品管理 批量导出
	productStockBatchExport(data){
		return request({url: '/api/admin/product_stock/export',method: 'POST',data,allowRepetition:false,Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'})	
	},
	//商品管理 批量导入库存
	productStockBatchImport(data){
		return request({url: '/api/admin/product_stock/import',method: 'POST',data})	
	},
	//商品管理 分类管理编辑
	categoryEdit(data){
		return request({url: '/api/admin/category/edit',method: 'POST',data})	
	},
	//商品管理 分类管理新增
	categoryAdd(data){
		return request({url: '/api/admin/category/add',method: 'POST',data})	
	},
	//商品管理 分类管理删除
	categoryDelete(data){
		return request({url: '/api/admin/category/delete',method: 'POST',data})	
	},

	//发货方式  商品状态   库存预警 筛选项查询
	productQueryOptions(data){
		return request({url: '/api/admin/product/options',method: 'get',params: data})	
	},

	//用户管理 列表查询
	userList(data){
		return request({url: '/api/admin/user/list',method: 'get',params: data})	
	},
	//用户管理 新增	
	userAdd(data){
		return request({url: '/api/admin/user/add',method: 'POST',data})	
	},
	//用户管理 编辑
	userEdit(data){
		return request({url: '/api/admin/user/edit',method: 'POST',data})	
	},
	
	//用户管理 删除
	userDelete(data){
		return request({url: '/api/admin/user/delete',method: 'POST',data})	
	},
	//内容管理  站内信列表查询
	messageList(data){
		return request({url: '/api/admin/message/list',method: 'get',params: data})	
	},
	//内容管理  站内信解决
	messageSolve(data){
		return request({url: '/api/admin/message/solve',method: 'POST',data})	
	},
	//内容管理  站内信批量解决
	messageBatchSolve(data){
		return request({url: '/api/admin/message/batchSolve',method: 'POST',data})	
	},
	//内容管理 站内信删除
	messageDelete(data){
		return request({url: '/api/admin/message/delete',method: 'POST',data})	
	},
	//新增模板
	templateAdd(data){
		return request({url: '/api/admin/template/add',method: 'POST',data})	
	},
	//模板列表查询
	
	templateList(data){
		return request({url: '/api/admin/template/list',method: 'get',params: data})	
	},
	//编辑模板
	templateEdit(data){
		return request({url: '/api/admin/template/edit',method: 'POST',data})	
	},
	//删除模板
	templateDelete(data){
		return request({url: '/api/admin/template/delete',method: 'POST',data})	
	},
	//内容管理  帮助中心 文档分类查询
	helpCategories(data){
		return request({url: '/api/admin/help/categories',method: 'get',params: data})	
	},
	//内容管理  帮助中心 文档列表查询
	helpDocumentList(data){
		return request({url: '/api/admin/document/list',method: 'get',params: data})	
	},
	//内容管理 帮助中心 文档编辑
	helpDocumentEdit(data){
		return request({url: '/api/admin/document/update',method: 'POST',data})	
	},
	//内容管理 帮助中心 文档新增
	helpDocumentAdd(data){
		return request({url: '/api/admin/document/add',method: 'POST',data})	
	},
	//内容管理 帮助中心 文档删除
	helpDocumentDelete(data){
		return request({url: '/api/admin/document/delete',method: 'POST',data})	
	},
	//系统管理 系统设置 基本设置  获取设置
	systemInfo(data){
		return request({url: '/api/admin/system/info',method: 'get',params: data})	
	},
	//系统管理 系统设置 基本设置  修改设置
	systemUpdate(data){
		return request({url: 'api/admin/system/info/update',method: 'POST',data})	
	},
	//系统管理 系统设置 基本设置  上传logo
	systemLogoUpload(data){
		return request({url: '/api/admin/system/logo/upload',method: 'POST',data})	
	},	
	//系统管理  系统设置  客户设置  获取设置
	customerInfo(data){
		return request({url: '/api/admin/system/customer',method: 'get',params: data})	
	},
	//系统管理  系统设置  客户设置  修改设置
	customerUpdate(data){
		return request({url: '/api/admin/system/customer/update',method: 'POST',data})	
	},
	//系统管理  系统设置  邮件设置  获取设置
	emailInfo(data){
		return request({url: '/api/admin/system/email',method: 'get',params: data})	
	},
	//系统管理  系统设置  邮件设置  修改设置
	emailUpdate(data){
		return request({url: '/api/admin/system/email/update',method: 'POST',data})	
	},
	//系统管理  系统设置  邮件设置 测试邮件
	emailTest(data){
		return request({url: '/api/admin/system/email/test',method: 'POST',data})	
	},
	//系统管理 USDT比率设置 手续费 获取设置
	paymentInfo(data){
		return request({url: '/api/admin/system/payment',method: 'get',params: data})	
	},
	//系统管理 手续费  修改设置
	paymentUpdate(data){
		return request({url: '/api/admin/system/payment/update',method: 'POST',data})	
	},
	//系统管理 USDT汇率设置 修改设置
	usdtRateUpdate(data){
		return request({url: '/api/admin/system/usdt/rate',method: 'POST',data})	
	},
	//系统管理 系统文档设置 获取设置
	contentInfo(data){
		return request({url: '/api/admin/system/content',method: 'get',params: data,allowRepetition:true})	
	},
	//系统管理 系统文档设置 修改设置
	contentUpdate(data){
		return request({url: '/api/admin/system/content/update',method: 'POST',data,allowRepetition:true})	
	},
	//系统管理 账户管理 获取列表
	managerList(data){
		return request({url: '/api/admin/manager/list',method: 'get',params: data})	
	},
	//系统管理 账户管理 新增
	managerAdd(data){
		return request({url: '/api/admin/manager/add',method: 'POST',data})	
	},
	//系统管理 账户管理 编辑
	managerUpdate(data){
		return request({url: '/api/admin/manager/update',method: 'POST',data})	
	},
	//系统管理 账户管理 删除
	managerDelete(data){
		return request({url: '/api/admin/manager/delete',method: 'POST',data})	
	},
	//系统管理 操作日志 查询列表
	logsList(data){
		return request({url: '/api/admin/logs',method: 'get',params: data})	
	},
	//系统管理 操作日志 导出
	logsExport(data){
		return request({url: '/api/admin/logs/export',method: 'get',params: data})	
	},
	//个人中心 登录日志
	loginLogs(data){
		return request({url: '/api/login/logs',method: 'get',params: data})	
	},
	//个人中心 修改密码 原密码验证
	verifyPassword(data){
		return request({url: '/api/verify_password',method: 'POST',data})	
	},
	//个人中心 修改密码 修改密码
	resetPassword(data){
		return request({url: '/api/change_password',method: 'POST',data})	
	},
	//个人中心 获取用户信息
	getUserInfo(data){
		return request({url: '/api/admin/manager/info',method: 'get',params: data})	
	},
	//支付配置 获取支付列表
	paymentList(data){
		return request({url: '/admin/payment_channel/index',method: 'get',params: data})	
	},
	//支付配置 编辑支付
	paymentEdit(data){
		return request({url: '/admin/payment_channel/edit',method: 'post',data})	
	},
	//支付配置 设置支付等待时间
	systemEdit(data){
		return request({url: '/admin/system/updateOrderTimeout',method: 'post',data})	
	},
	//获取通道筛选项
	getChannels(data){
		return request({url: '/admin/product_order/getChannels',method: 'get',params: data})
	},
	//二期接口

	//公告管理  获取公告列表
	announcementList(data){
		return request({url: '/api/admin/announcement/list',method: 'get',params: data})
	},
	//公告管理  添加新公告
	announcementAdd(data){
		return request({url: '/api/admin/announcement/add',method: 'post', data})
	},
	//公告管理  编辑公告
	announcementEdit(data){
		return request({url: '/api/admin/announcement/edit',method: 'post', data})
	},
	//公告管理  删除公告
	announcementDelete(data){
		return request({url: '/api/admin/announcement/delete',method: 'post', data})
	},
	//公告管理  批量删除公告
	announcementBatchDelete(data){
		return request({url: '/api/admin/announcement/batchDelete',method: 'post', data})
	},
	//会员设置-列表查询
	memberList(data){
		return request({url: '/api/admin/member_level/list',method: 'get',params: data,allowRepetition:true})
	},
	//会员设置 编辑会员等级
	memberLevelEdit(data){
		return request({url: '/api/admin/member_level/edit',method: 'post', data})
	},
	//用户管理  余额增减
	balanceOperate(data){
		return request({url: '/api/admin/user/balanceOperate',method: 'post', data})
	},	
	//用户管理 余额变动日志
	balanceLog(data){
		return request({url: '/api/admin/user/balanceLog',method: 'get',params: data})
	},
	//用户管理 余额变动日志 变动类型
	
	balanceLogTypes(data){
		return request({url: '/api/admin/user/balanceLogTypes',method: 'post', data})
	},
	//用户管理 重置密码
	resetUserPassword(data){
		return request({url: '/api/admin/user/resetPassword',method: 'post', data})
	},
	//充值订单  列表筛选项查询
	rechargeOptions(data){
		return request({url: '/api/admin/recharge_order/getOptions',method: 'get',params: data})
	},
	
	//充值订单 列表查询
	rechargeOrderList(data){
		return request({url: '/api/admin/recharge_order/list',method: 'get',params: data})
	},
	//充值订单 确认支付
	rechargeOrderConfirm(data){
		return request({url: '/api/admin/recharge_order/confirm',method: 'post', data})
	},
	//充值订单 退款
	rechargeOrderRefund(data){
		return request({url: '/api/admin/recharge_order/refund',method: 'post', data})
	},
	//充值订单 导出全部订单
	rechargeOrderExport(data){
		return request({url: '/api/admin/recharge_order/export',method: 'post', data})
	},
	//商品订单发货
	deliveryManual(data){
		return request({url: '/api/admin/order/deliveryManual',method: 'post', data})
	},
	//商品订单 退货
	orderRefund(data){
		return request({url: '/api/admin/order/refund',method: 'post', data})
	},
	//三期接口
	//数据概览  销售明细列表查询
	productSalesDetail(data){
		return request({url: '/api/admin/order/productSalesDetail',method: 'get',params: data})
	},
	//人工录入成本 （新增成本）
	addManualCost(data){
		return request({url: '/api/admin/cost_center/add_manual_cost',method: 'post', data})
	},
	//成本流水列表及统计查询
	costCenterList(data){
		return request({url: '/api/admin/cost_center/list',method: 'get',params: data})
	},
	//成本中心 获取成本类型和金额类型
	getTypeOptions(data){
		return request({url: '/api/admin/cost_center/get_type_options',method: 'post', data})
	},	
	//导出全部订单
	exportCost(data){
		return request({url: '/api/admin/cost_center/export',method: 'get',params: data})	
	},
	// 获取批次id
	getBatchId(data){
		return request({url: '/api/admin/product_stock/generateBatchId',method: 'post', data})
	},
	//模糊查询批量id
	searchBatchIds(data){
		return request({url: '/api/admin/product_stock/searchBatchIds',method: 'get', params: data})
	},
	//批量修改成本价
	batchUpdateCostPrice(data){
		return request({url: '/api/admin/product_stock/batchUpdateCostPrice',method: 'post', data})
	},
	//商品订单更改卡密信息
	updateCardInfo(data){
		return request({url: '/api/admin/product_order/updateCardInfo',method: 'post', data})
	},
	//销售明细导出
	exportProductSalesDetail(data){
		return request({url: '/api/admin/order/productSalesDetailExport',method: 'post', data})
	},
	//商品列表-排序
	sortProduct(data){
		return request({url: '/api/admin/product/sort',method: 'post', data})
	},
	//独立版本	
	//支付权限配置-查询
	getChannelInfo(data){
		return request({url: '/api/admin/payment_channel/getChannelInfo',method: 'get', params: data})
	},	
	//支付权限配置-更新
	editChannelWithRates(data){ 
		return request({url: '/api/admin/payment_channel/editChannelWithRates',method: 'post', data})
	},
	// 管理后台-通知中心-获取通知中心数据
	notifications(data){ 
		return request({url: '/api/admin/notifications/getNotifications',method: 'get', params:data})
	},
	//通知中心  数字角标	
	notificationsStats(data){ 
		return request({url: '/api/admin/notifications/stats',method: 'get', params:data})
	},
	//管理后台-仪表盘-全站流量数据统计（realtime, yesterday, 7days, 30days）
	trafficStats(data){
		return request({url: '/api/admin/traffic_stats',method: 'get', params:data})
	},
	//管理后台-仪表盘-支付金额趋势（实时）-（payment_amount, net_payment, visitors, paying_buyers, conversion_rate, refund_amount, refund_rate）
	traffic_trend(data){
		return request({url: '/api/admin/traffic_trend',method: 'get', params:data})
	},
	//阅读消息api/admin/notifications/mark_read
	readNotifications(data){
		return request({url: '/api/admin/notifications/mark_read',method: 'post', data})
	},
	//分消息类型 消息全部阅读 api/admin/notifications/mark_all_read_by_type
	readAllNotifications(data){
		return request({url: '/api/admin/notifications/mark_all_read_by_type',method: 'post', data})
	},
	//消息全部已读	// /api/admin/notifications/mark_all_read
	readAllMessage(){
		return request({url: '/api/admin/notifications/mark_all_read',method: 'post'})
	},
	//渠道配置 列表获取admin/channel_config
	channelConfig(data){ 
		return request({url: 'api/admin/channel_config/index',method: 'get', params:data})
	},
	//渠道配置 添加
	addChannelConfig(data){ 
		return request({url: 'api/admin/channel_config/add',method: 'post', data})
	},
	//渠道配置 修改
	editChannelConfig(data){ 
		return request({url: 'api/admin/channel_config/edit',method: 'post', data})
	},
	//渠道配置 删除
	deleteChannelConfig(data){ 
		return request({url: 'api/admin/channel_config/delete',method: 'post', data})
	},
	//商品列表 删除商品视频
	// api/admin/product/video/delete
	deleteProductVideo(data){
		return request({url: 'api/admin/product/video/delete',method: 'post', data})
	},
	//弹窗内容管理 获取弹窗数据
	getDialogData(data){
		return request({url: '/api/admin/popup/config',method: 'get',params: data})	
	},
	//弹窗内容管理 保存弹窗数据
	saveDialogData(data){
		return request({url: '/api/admin/popup/config/update',method: 'post', data})	
	},
	//弹窗状态更新
	// api/admin/popup/config/status
	updateDialogStatus(data){
		return request({url: '/api/admin/popup/config/status',method: 'post', data})	
	},
	//商品列表-设置单个商品折扣
	setProductDiscount(data){
		return request({url: '/api/admin/product/discount/set',method: 'post', data})
	},
	//商品列表-设置商品折扣
	setDiscountBatch(data){
		return request({url: '/api/admin/product/discount/batchSet',method: 'post', data})
	},
	//商品列表 - 设置商品折扣 查询所有商品
	productAllList(data){
		return request({url: '/api/admin/product/search',method: 'get',params: data})	
	},
}
