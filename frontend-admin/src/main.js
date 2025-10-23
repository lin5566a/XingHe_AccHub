import Vue from 'vue'
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import App from './App.vue'
import router from './router'
import './style/index.scss'
const { baseURL, proName, payNameImg, uploadURL} = require("@/utils/request.js");
Vue.prototype.$baseURL = baseURL;
Vue.prototype.$uploadURL = uploadURL;
Vue.prototype.$proName = proName;

// 动态设置页面标题
document.title = proName;

import local from './utils/local'
Vue.prototype.$local = local

import util from './utils/util'
Vue.prototype.$util = util

import md5 from 'md5';
Vue.prototype.$md5 = md5;

import api from '@/api/api'
Vue.prototype.$api = api

import axios from 'axios'
axios.defaults.withCredentials = true;
Vue.prototype.$axios = axios

// 添加事件总线
Vue.prototype.$eventBus = new Vue()

Vue.use(ElementUI)
Vue.config.productionTip = false
new Vue({
  router,
  render: h => h(App)
}).$mount('#app')
