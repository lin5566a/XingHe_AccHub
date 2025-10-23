<template>
  <div class="pc-nav-bg" :class="announcement.show? 'show-announcement':''" v-show="!props.isMobile"> 
    <nav class=" pc-navbar">
    <Announcement v-if="announcement.show" :announcement="announcement" @closeAnnouncement="closeAnnouncement"></Announcement>
    <div class="navbar">
      <div class="nav-container">
        <div class="navbar-content relative">
          <a class="navbar-brand " href="/"> 
            <div class="logo-img has-system-logo" v-if='systemInfo.system_logo'>
              <img v-if="systemInfo.system_logo" :src="BASE_URL + systemInfo.system_logo" alt="logo" class="logo-img-img">
            </div>
            <span class="logo-text">{{systemInfo.system_name}}</span>
          </a>
          <div class="nav-links absolute w-full flex items-center justify-center">
            <div class="flex items-center justify-center rounded-full px-2 py-1 bg-white/10">
              <router-link
                to="/mall"
                class="nav-links-item rounded-full px-4 py-2 text-sm font-medium rounded-md text-white/90"
                >商品商城</router-link
              >
              <router-link
                to="/orders"
                class="nav-links-item rounded-full px-4 ml-2 py-2 text-sm font-medium rounded-md text-white/90"
                >订单查询</router-link
              >
              <router-link
                to="/utilities"
                class="nav-links-item rounded-full px-4 ml-2 py-2 text-sm font-medium rounded-md text-white/90"
                >实用工具</router-link
              >
              <router-link
                to="/help"
                class="nav-links-item rounded-full px-4 ml-2 py-2 text-sm font-medium rounded-md text-white/90"
                >常见问题</router-link
              >
              <router-link
                to="/about"
                class="nav-links-item rounded-full px-4 ml-2 py-2 text-sm font-medium rounded-md text-white/90"
                >关于我们</router-link
              >
            </div>
          </div>
          <div class="auth-buttons" v-if="!isLoggedIn">  
            <el-button class="login-btn" @click="goToPage('login')">登录</el-button>
            <el-button class="register-btn" @click="goToPage('register')">注册</el-button>
          </div>
          <div class="auth-buttons auth-dropdown" v-else>
            <div class="flex items-center justify-between">

            <div class="recharge-btn rounded-full cursor-pointer flex items-center justify-center px-3 py-1.5 text-sm font-medium text-white bg-gray-700 rounded" @click="goToPage('recharge')"> 
              <i class="mr-2 iconfont icon-wallet" font-size="14px"></i><span>充值</span>
            </div>
            <el-dropdown trigger="click" popper-class="auth-dropdown-menu" >
              <div class="dropdown-box flex items-center justify-between  rounded-full bg-white/10 text-white/90 px-3 py-2 ml-4">               
                <span class="auth-title text-gray-300 cursor-pointer ">
                  <!-- {{ userEmail.substring(0,userEmail.indexOf('@')) }} -->
                  {{allUserInfo.email.length > 4 ? allUserInfo.email.substring(0,4) + '...': allUserInfo.email}}
                  <span class="ml-2 mr-2"><span class="px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-sm font-medium" v-if="allUserInfo.member_level && allUserInfo.member_level.id >1">{{allUserInfo.member_level? allUserInfo.member_level.name:''}}</span></span>
                  <i class="iconfont icon-caret-down text-gray-300" style="font-size: 14px;"></i>
                </span>
              </div>
              
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item class="info-item">
                    <div class="w-56">
                      <div class="flex items-center">
                        <!-- {{ userEmail.substring(0,userEmail.indexOf('@')) }} -->
                        <div class="user-email text-gray-900 font-medium text-md">{{allUserInfo.email}}</div>  
                        <!-- <div class="ml-2 px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-sm font-medium" v-if="allUserInfo.member_level && allUserInfo.member_level.id >1">{{allUserInfo.member_level? allUserInfo.member_level.name:''}}</div> -->
                      </div>
                      <div class="text-blue-600 text-sm font-medium mt-1">
                        余额：¥{{allUserInfo.balance || '0.00'}}
                      </div>
                    </div>                    
                  </el-dropdown-item>
                  <el-dropdown-item @click="goToAccount">
                    <i class="text-blue-600 iconfont icon-user mr-2" style="font-size: 14px;"></i> 我的账户
                  </el-dropdown-item>     
                  <el-dropdown-item @click="goToOrder">
                    <i class="text-blue-600 iconfont icon-shopping-bag mr-2" style="font-size: 14px;"></i>订单记录
                  </el-dropdown-item>     
                  <el-dropdown-item @click="goToRechargeOrder">
                    <i class="primary-color iconfont icon-wallet mr-2" style="font-size: 14px;"></i>充值记录
                  </el-dropdown-item> 
                   
                  <el-dropdown-item @click="goToBillOrder">
                    <i class="text-blue-600 iconfont icon-history mr-2" style="font-size: 14px;"></i>账单记录
                  </el-dropdown-item>        
                  
                  <el-dropdown-item class="log-out-item" @click="handleLogout"><span class="text-red-600">退出登录</span></el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
            
          </div>
          </div>
        </div>
      </div>
    </div>
      
    </nav>
  </div>
</template>
<script setup>
import { defineProps,defineEmits, ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { ElMessage } from "element-plus";
import {UserFilled } from "@element-plus/icons-vue";
import emitter from '@/utils/eventBus';
import { userApi } from '@/api';
import {BASE_URL} from '@/utils/request'

import Announcement from "@/components/announcement.vue";
// import ReactIcon from "./reactIcon.vue";

const props = defineProps({
  isMobile: {
    type: Boolean,
    default: false,
  },
  systemInfo: {
    type: Object,
    default: () => ({}),
  },
  announcement:{
    type: Object,
    default: () => ({})
  }
});
const emit = defineEmits(['closeAnnouncement'])
const router = useRouter();
const isLoggedIn = ref(false);
const userEmail = ref('');
const allUserInfo = ref({})
//关闭公告
const closeAnnouncement = ()=>{ 
  emit('closeAnnouncement')
}
// 检查登录状态
const checkLoginStatus = () => {
  const token = localStorage.getItem('token');
  const user = localStorage.getItem('userInfo');
  const info = localStorage.getItem('allUserInfo')
  const email = user ? JSON.parse(user).email : '';
  isLoggedIn.value = !!token;
  userEmail.value = email || '';

  allUserInfo.value = info ? JSON.parse(info) : {}
};

// 监听 localStorage 变化
const handleStorageChange = (e) => {
  if (e.key === 'token' || e.key === 'email' || e.key === 'allUserInfo') {
    checkLoginStatus();
  }
};

// 跳转到账户页面
const goToAccount = () => {
  router.push('/personal/info');
};

// 跳转到订单记录页面
const goToOrder = () => {
  router.push('/personal/order');
};
const goToRechargeOrder = ()=>{
  router.push('/personal/rechargeOrder')
}
const goToBillOrder = ()=>{
  router.push('/personal/billRecords')
}

// 处理退出登录
const handleLogout = () => {
  userApi.logout().then(res=>{
    // console.log(res);
    ElMessage.success('退出成功');
  })
  setTimeout(()=>{
    localStorage.removeItem('token');
    localStorage.removeItem('userInfo');
    localStorage.removeItem('allUserInfo')
    isLoggedIn.value = false;
    userEmail.value = '';
    router.push('/login');
  },200)
};
//去充值页面
const goToPage = (page)=>{
  router.push(`/${page}`);
}
// 监听登录状态变化事件
const handleLoginStatusChange = () => {
  checkLoginStatus();
};

onMounted(() => {
  checkLoginStatus();
  window.addEventListener('storage', handleStorageChange);
  emitter.on('loginStatusChange', handleLoginStatusChange);
});

onUnmounted(() => {
  window.removeEventListener('storage', handleStorageChange);
  emitter.off('loginStatusChange', handleLoginStatusChange);
});
</script>
<style lang="scss">

.auth-dropdown-menu{
  display: none;
  width:14rem;
  padding-top:0.5rem !important;
  padding-bottom: 0.5rem!important;
  .el-dropdown-menu__item{
    padding: 0.63rem 1rem !important;
    font-size: 0.875rem !important;
    &.info-item:focus,
    &.info-item:hover{
      background-color: #fff;
    }
    &.log-out-item:focus,
    &.log-out-item:hover{
      background-color: #fef2f2;
    }
  }
  .info-item{
    border-bottom: solid 1px #f3f4f6;
  }
  .log-out-item{
    margin-top:4px;
    border-top:solid 1px #f3f4f6;
  }
  
}
@media (min-width: 768px) {
  .auth-dropdown-menu{
    display: block;
  }
}
</style>
<style scoped lang="scss">
.pc-nav-bg {
  height: 56px;
  &.show-announcement{
    height: calc(56px + 3rem)
  }
  .pc-navbar {
    position: fixed;
    top: 0px;
    left: 0px;
    width: 100%;
    z-index: 1000;
    .navbar{      
      // box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1), 0 4px 6px -4px rgba(0, 0, 0, .1);
    // background-image: linear-gradient(to right, #1d4ed8, #4338ca);
      //background-color: #1f2937;
    }
    .nav-container {
      width: 100%;
      .nav-links{
        z-index: 1;
        .nav-links-item{
          &:not(.router-link-active){
            &:hover{
              background: rgba(255, 255, 255, 0.2);
              color:#fff;
            }
          }
          
        }
      }
      .auth-buttons{
        z-index: 2;
        .login-btn{
          background-color: transparent;
          border:none;
          color: rgba(255, 255, 255, 0.9);
          font-weight: 500;
          font-size: 0.875rem;
          line-height: 1.25rem;
          padding: 0.5rem 1rem;
          height: 36px;
          margin-left: 0;
          &:hover{
            background: rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
          }
        }
        .register-btn{
          background-image: linear-gradient(to right,#10b981,#059669);
          box-shadow:0 4px 6px -1px rgba(0, 0, 0, .1), 0 2px 4px -2px rgba(0, 0, 0, .1);
          border-radius: 9999px;
          border:none;
          color: rgba(255, 255, 255, 0.9);
          font-weight: 500;
          font-size: 0.875rem;
          line-height: 1.25rem;
          padding: 0.5rem 1rem;
          height: 36px;
          margin-left: 0.75rem;
          &:hover{
            background-image: linear-gradient(to right,#059669,#047857);
            border-radius: 9999px;
          }
        }
        .dropdown-box{
          backdrop-filter: blur(4px);
        }
        
      }
    }
    .logo-img {      
      max-width: 50px;
      margin-right: 10px;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-right: 10px;
      &.has-system-logo{     
        max-width: 40px;
        width: 40px;
        height: 40px;
        overflow: hidden;
        .logo-img-img{
          width: 40px;
          height: 40px;
          object-fit: cover;
        }
    }
    }
    
    .logo-text {
      color: #fff;
      font-size: 1.125rem;
      font-weight: 600;
    }
    .auth-dropdown{
      .auth-title{
        // color: #fff;
        font-size: 1rem;
      }
      .recharge-btn{
        height:36px;
        background-image: linear-gradient(to right, #10b981,#059669);
        &:hover{
          background-image: linear-gradient(to right, #059669,#047857);
        }
      }
    }
  }
} 
</style>
