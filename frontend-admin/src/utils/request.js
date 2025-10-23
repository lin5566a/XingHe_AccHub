/**
 *  🛠定制开发 - 专业团队提供个性化开发服务
 * 	💳支付通道对接 - 快速对接第三方支付平台
 * 	🤝业务合作 - 多种合作模式，共创双赢
 * 	QQ: 3909001743 | Telegram: @sy9088 
 */
import axios from 'axios'
import local from './local'
import { Message } from 'element-ui'
import router from '@/router'
const urlArr = [];

const baseURL = 'https://XXX.XXX.com'; //  接口域名
const uploadURL = 'https://XXX.XXX.com';//  上传图片域名

const proName = '管理系统';
const payNameImg = 'default';
let url = ''

const service = axios.create({
	baseURL: baseURL, // url = base url + request url
	timeout: 30000, // request timeout
	withCredentials: true,
})
// request interceptor
service.interceptors.request.use(
	config => {
		// console.log(config.Accept,'Accept', config.url)
		url = config.url
		const token = local.get('token')
		config.headers = {
			'Content-Type': 'application/json',//  注意：设置很关键 
			'Authorization': 'Bearer ' + token,			
		}
		if(config.Accept){
			config.headers.Accept = config.Accept
		}
		
		let fullUrl = config.baseURL + config.url
		if (config.allowRepetition) { //允许重复请求数据的接口
			return config
		} else if (urlArr.indexOf(fullUrl) === -1) { //不允许重复请求数据的接口
			urlArr.push(fullUrl);
			setTimeout(() => {
				urlArr.splice(urlArr.indexOf(fullUrl), 1)
			}, 1000)
			return config
		} else {
			return Promise.reject({
				message: '重复请求'
			})
		}
	},
	error => {
		return Promise.reject(error)
	}
)

// response interceptor
service.interceptors.response.use(
	/**
	 * If you want to get http information such as headers or status
	 * Please return  response => response
	 */

	/**
	 * Determine the request status by custom code
	 * Here is just an example
	 * You can also judge the status by HTTP Status Code
	 */
	response => {
		
		// console.log(url,'==url===')
		const res = response.data
		// return res
		// 错误码配置
		
		let fullUrl = response.request.responseURL
		urlArr.splice(urlArr.indexOf(fullUrl), 1)
		if (res.code != 1 && res.code != 0) {
			//验证码错误  重新获取验证码
			Message({
				message: res.msg || 'Error',
				type: 'error',
				duration: 5 * 1000
			})
			if (res.code === 401) {	
				if(url !== '/api/login'){
					local.remove('token')
					local.remove('userInfo')
					router.push('/login')
				}
				// return
			}
			return Promise.reject(new Error(res.msg || 'Error'))
		} else {
			return res
		}
	},
	error => {
		Message({
			message: error.message,
			type: 'error',
			duration: 5 * 1000
		})
		return Promise.reject(error)
	}
)
export {
	baseURL,
	uploadURL,
	proName,
	payNameImg
}
export default service
