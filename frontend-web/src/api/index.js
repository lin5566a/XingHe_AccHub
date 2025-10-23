import request from '@/utils/request';

// 用户相关接口
export const userApi = {
  //获取验证码
  getCaptcha: () => request.get('/v1/user/captcha'),
  // 登录
  login: (data) => request.post('/v1/user/login', data),
  // 注册
  register: (data) => request.post('/v1/user/register', data),
  // 退出登录
  logout: () => request.post('/v1/user/logout'),
  // 获取用户信息
  getUserInfo: () => request.get('/v1/user/info'),
  //修改昵称
  updateNickname: (data) => request.post('/v1/user/updateNickname',data),
  //修改密码
  updatePassword: (data) => request.post('/v1/user/updatePassword',data),
  //客服链接获取
  getCustomerLinks: () => request.get('/v1/product/customerLinks'),
  //二期
  //获取会员等级说明表  
  getMemberLevel: () => request.get('/v1/member_level/list'),
  //余额查询
  getBalance: () => request.get('/v1/user/balance'),
  //独立版
  //发送注册验证嘛
  sendRegisterCaptcha:(data)=>request.post('/v1/user/sendRegisterCaptcha',data),
  
  
};

// 商品相关接口
export const productApi = {
  // 获取商品列表
  getProductList: (keyword) => request.get('/v1/product/index', { params: { keyword } }),
  // 获取商品详情
  getProductDetail: (id) => request.get(`/v1/product/detail`,{params:{id}}),
  // 商品补货通知
  notifyReplenish: (data) => request.post('/v1/product/notifyReplenish', data),
  //包列表查询
  getPackages: (id) => request.get(`/index/product_detail/getPackages`,{params:{id}}),
};

// 订单相关接口
export const orderApi = {
  // 获取订单列表
  getOrderList: (params) => request.get('/v1/user/orders', { params }),
  // 订单查询页面  获取订单详情
  getOrderQuery: (data) => request.post(`/v1/order/query`,data),
  // 创建订单
  createOrder: (data) => request.post('/v1/order/create', data),
  //订单详情
  getOrderDetail: (data) => request.post(`/v1/order/detail`,data),
  //二期
  //充值订单
  createRechargeOrder: (data) => request.post(`/v1/recharge/create`,data),
  rechargeDetail: (params) => request.get('/v1/recharge/detail', { params }),
  //充值订单列表查询
  rechargeList: (params) => request.get('/v1/recharge/query', { params }),
  //账单记录查询
  balanceLog: (params) => request.get('/v1/user/balanceLog', { params }),
  //发送订单查询验证码
  sendQueryCaptcha: (data) => request.post(`/v1/order/sendQueryCaptcha`,data),
  
};

// 支付相关接口
export const paymentApi = {
  // 创建支付订单
  createPayment: (data) => request.post('/v1/payment/createPayment', data),
  //获取支付方式
  getPaymentMethods: () => request.get('/index/payment/getPaymentMethods'),
  //二期
  //充值支付
  rechargePay:(data) => request.post('/v1/recharge/pay', data),  
  //商品订单 余额支付 
  
  payWithBalance:(data) => request.post('/v1/order/payWithBalance', data),  

  //获取教程视频
  getProductVideo: (params) => request.get('/v1/payment/productVideo',{params}),
  
}; 
//协议相关接口
export const protocolApi = {
  // 获取使用协议
  getProtocol: (type) => request.get(`/v1/system/content?type=${type}`),
  // 获取帮助分类
  getHelpCategories: () => request.get('/v1/help/categories'),
  //帮助分类下文章列表
  getHelpDocuments: (data) => request.post(`/v1/help/documents`,data),
  // 文章详情
  getHelpDetail: (data) => request.post(`/v1/help/detail`,data),
  //获取系统基本配置
  getSystemInfo: () => request.get('/v1/system/info'),
  //二期  
  //获取公告
  getAnnouncement: () => request.get('/v1/announcement/get'),
  // getAnnouncement: () => request.get('/v1/announcement/get'),
  //首页弹窗/v1/system/popup
  getPopup: () => request.get('/v1/system/popup'),
  
};
