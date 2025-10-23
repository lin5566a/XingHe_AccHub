/**
 *  🛠定制开发 - 专业团队提供个性化开发服务
 * 	💳支付通道对接 - 快速对接第三方支付平台
 * 	🤝业务合作 - 多种合作模式，共创双赢
 * 	QQ: 3909001743 | Telegram: @sy9088 
 */
import axios from 'axios';
import { ElMessage } from 'element-plus';
import emitter from '@/utils/eventBus';
import router from '@/router';
import util from '@/utils/util'

// 导出 baseURL
export const BASE_URL = 'https://XXX.XXX.com'; // 接口域名
export const uploadURL = 'https://XXX.XXX.com';// 上传图片域名

const  ensureVisitorId = () => {
  let id = util.getCookie('visitor_id');
  if (!id) {
    try { id = localStorage.getItem('visitor_id'); } catch(e){ id = null; }
  }
  if (!id) {
    id = util.uuidv4();
    try { util.setCookie('visitor_id', id); localStorage.setItem('visitor_id', id); } catch(e){}
  }
  return id;
}
let visitorId = ensureVisitorId();
const getChannel=()=>{
  let channel = util.getCookie('channel');
  if(!channel){
    try{
      channel = localStorage.getItem('channel')
    }catch(e){}
  }
  return channel;
}
// 创建 axios 实例
const service = axios.create({
  baseURL: BASE_URL,
  timeout: 10000, // 请求超时时间
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json'
  }
});

// 请求拦截器
service.interceptors.request.use(
  config => {
    // 在发送请求之前做些什么
    const token = localStorage.getItem('token');
    const channel = getChannel()
    config.headers['X-Visitor-UUID'] = visitorId
    config.headers['X-Channel-Code'] = channel
    if (token) {

      config.headers['Authorization'] = `Bearer ${token}`;
    }
    
    return config;
  },
  error => {
    // 对请求错误做些什么
    console.error('请求错误:', error);
    return Promise.reject(error);
  }
);

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data;
    // 根据自定义错误码判断请求是否成功
    if (res.code !== 1 && res.code !== 0) {
      ElMessage({
        message: res.message || '请求失败',
        type: 'error',
        duration: 5 * 1000
      });
      // 401: 未登录或token过期
      if (res.code === 401) {
        // 清除token并跳转到登录页
        localStorage.removeItem('token');        
        emitter.emit('loginStatusChange');
        router.push('/login');
      }
      return Promise.reject(new Error(res.message || '请求失败'));
    } else {
      return res;
    }
  },
  error => {
    console.error('响应错误:', error);
    
    ElMessage({
      message: error.message || '请求失败',
      type: 'error',
      duration: 5 * 1000
    });
    return Promise.reject(error);
  }
);

export default service; 