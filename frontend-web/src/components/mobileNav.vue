<template> 
  <div class="mobile-nav-bg" :class="announcement.show? 'show-announcement':''" v-show="props.isMobile"> 
    <div class="mobile-nav">
      <Announcement v-if="announcement.show" :announcement="announcement" @closeAnnouncement="closeAnnouncement"></Announcement>
      <nav class="navbar"  v-show="props.isMobile">      
      <div class="navbar-content">
        <a class="navbar-brand" href="/">
            <div class="logo-img has-system-logo">            
              <img v-if="systemInfo.system_logo" :src="BASE_URL + systemInfo.system_logo" alt="logo" class="logo-img-img">
          </div>            
          <span class="logo-text">{{systemInfo.system_name}}</span>
        </a>


        <!-- <a class="navbar-brand" href="/" v-else>
            <div class="logo-img">
              <i class="iconfont icon-envelope" style="font-size: 20px; color:#374151"></i>  
          </div>            
          <span class="logo-text">星海</span>
        </a> -->
        <div class="navbar-toggler"  @click="toggleMobileMenu">
          <div class="navbar-toggler-icon"></div>
          <div class="navbar-toggler-icon"></div>
          <div class="navbar-toggler-icon"></div>
        </div>
      </div>
    </nav>
    </div>
    
  </div>
    <!-- 移动端侧边栏菜单 -->
    <div class="offcanvas "  id="mobileMenu" tabindex="-1"  v-show="props.isMobile">
      <el-drawer size="18rem" class="mobile-drawer" v-model="isMobileMenuOpen" direction="ltr" title="" :with-header="false">
        <div class="mobile-menu-content px-4 pt-6 pb-6 space-y-6">
          <div class="flex flex-col pb-4" v-if="!isLoggedIn">
            <div class="nav-link flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md" @click="linkClick('/login')">登录</div>
            <div class="nav-link mt-2 flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-md" @click="linkClick('/register')">注册</div>
          </div>
          <div class="flex flex-col pb-4" v-else>
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="iconfont icon-user text-gray-600" style="font-size: 1rem;"></i>
              </div>
              <div class="ml-3">
                <div>
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">{{allUserInfo.email}}</div>  
                    <!-- <div class="ml-2 px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-sm font-medium" v-if="allUserInfo.member_level && allUserInfo.member_level.id >1">{{allUserInfo.member_level? allUserInfo.member_level.name:''}}</div> -->
                  </div>
                  <div class="text-sky-500 text-sm font-medium mt-1">
                    余额：¥{{allUserInfo.balance || '0.00'}}
                  </div>
                </div>
              </div>
            </div>
            <div class="recharge-btn items-center justify-center px-4 py-2 ml-4 text-sm font-medium text-white bg-sky-600 rounded-md" @click="goToPage('recharge')"><span>充值</span></div>

          </div>          
          <el-divider class="nav-divider mt-4 mb-6" />
          <div>
            <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md" :class="path === '/mall' ?'bg-sky-50 text-sky-700':'text-gray-600'" @click="linkClick('/mall')">商品商城</div>
            <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md" :class="path === '/orders' ?'bg-sky-50 text-sky-700':'text-gray-600'" @click="linkClick('/orders')">订单查询</div>
            <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md" :class="path === '/utilities' ?'bg-sky-50 text-sky-700':'text-gray-600'" @click="linkClick('/utilities')">实用工具</div>
            <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md" :class="path === '/help' ?'bg-sky-50 text-sky-700':'text-gray-600'" @click="linkClick('/help')">帮助中心</div>
            <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md" :class="path === '/about' ?'bg-sky-50 text-sky-700':'text-gray-600'" @click="linkClick('/about')">关于我们</div>
          </div>
          <div v-if="isLoggedIn">
            <el-divider class="nav-divider mt-4 mb-6" />
            <div class="pt-4 space-y-1">
              <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md text-gray-600" @click="linkClick('/personal/info')">
                <i class="iconfont icon-user text-sky-500 mr-3" style="font-size: 14px;"></i>
                我的账户
              </div>
              <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md text-gray-600" @click="linkClick('/personal/order')">                
                <i class="iconfont icon-shopping-bag text-blue-500 mr-3" style="font-size: 14px;"></i>
                订单记录
              </div>
              <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md text-gray-600" @click="linkClick('/personal/rechargeOrder')">                
                <i class="iconfont icon-wallet text-blue-500 mr-3" style="font-size: 14px;"></i>
                充值记录
              </div>
              <div class="nav-link flex items-center px-4 text-sm font-medium rounded-md text-gray-600" @click="linkClick('/personal/billRecords')">                
                <i class="iconfont icon-history text-blue-500 mr-3" style="font-size: 14px;"></i>
                账单记录
              </div>

                 

              <div class="logout-btn flex items-center w-full px-4 text-sm font-medium text-red-600 rounded-md mt-2" @click="handleLogout">退出登录</div>
            </div>
          </div>
        </div>
       
      </el-drawer>
    </div>
</template>
<script setup>
import { ref,defineEmits, defineProps, watch, onMounted, onUnmounted } from 'vue';
import { useRouter,useRoute } from 'vue-router';
import emitter from '@/utils/eventBus';
import { userApi } from '@/api';
import { ElMessage } from 'element-plus';
import {BASE_URL} from '@/utils/request'
import Announcement from "@/components/announcement.vue";
const router = useRouter();
const route = useRoute();
const isLoggedIn = ref(false);
const userEmail = ref('');
const allUserInfo = ref({})
const path = ref(route.path);
// console.log(path.value);
const props = defineProps({
    isMobile: {
        type: Boolean,
        default: false
    },
    systemInfo: {
        type: Object,
        default: () => ({}),
    },
    announcement:{
      type: Object,
      default: () => ({})
    }
})
const emit = defineEmits(['closeAnnouncement'])
//关闭公告
const closeAnnouncement = ()=>{ 
  emit('closeAnnouncement')
}
const isMobileMenuOpen = ref(false);

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

// 处理退出登录
const handleLogout = () => {
  userApi.logout().then(res=>{
    // console.log(res);
    ElMessage.success('退出成功');
  })
  setTimeout(()=>{
    localStorage.removeItem('token');
    localStorage.removeItem('userInfo');
    isLoggedIn.value = false;
    userEmail.value = '';
    router.push('/mall');
    isMobileMenuOpen.value = false;
  },200)
};

// 监听登录状态变化事件
const handleLoginStatusChange = () => {
  checkLoginStatus();
};


// 切换移动端菜单
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
const linkClick = (path) => {
  isMobileMenuOpen.value = false;
  router.push(path);
};
// 更新当前路径
const updateCurrentPath = () => {
  path.value = route.path;
  // currentPath.value = path || "info";

};
//去充值页面
const goToPage = (page)=>{
  isMobileMenuOpen.value = false;
  router.push(`/${page}`);
}

// 监听路由变化
watch(
  () => route.path,
  () => {
    updateCurrentPath();
  }
);
onMounted(() => {
  checkLoginStatus();
  window.addEventListener('storage', handleStorageChange);
  emitter.on('loginStatusChange', handleLoginStatusChange);
});

onUnmounted(() => {
  window.removeEventListener('storage', handleStorageChange);
  emitter.off('loginStatusChange', handleLoginStatusChange);
});
// // 关闭移动端菜单
// const closeMobileMenu = () => {
//   isMobileMenuOpen.value = false;
// };
</script>
<style lang="scss">
  
.mobile-drawer{
  .el-drawer__body{
    padding: 0;
  }
}
</style>
<style scoped lang="scss">
.mobile-nav-bg{
  height: 56px;
  &.show-announcement{
    height:calc(56px + 3rem)
  }
}
.mobile-nav{
  position: fixed;
  top: 0px;
  left: 0px;
  width: 100%;
  z-index: 1000;
}
.navbar{
    
}
.navbar-content{
    flex:1;    
    height: 56px;
    .navbar-brand{
      display: flex;
      align-items: center;
      justify-content: flex-start;
      .logo-img{        
        max-width: 50px;
        margin-right: 10px;
        &.has-system-logo{     
          max-width: 200px;
          width: 40px;
          height: 40px;
          .logo-img-img{
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
          }
       }
      }
  }
  .navbar-toggler-icon{
    width: 1.4rem;
    height: 3px;
    background: #fff;
    margin-top: 5px;
    border-radius: 1rem;
  }
}
.offcanvas{

  .mobile-menu-content{
    .nav-link{
      padding-top: .625rem;
      padding-bottom: .625rem;
      &:hover{
        color:#111827;
        background: #f9fafb;
      }
    }
    .logout-btn{
      padding-top: .625rem;
      padding-bottom: .625rem;
      &:hover{
        background: #fef2f2;
      }
    }
    .recharge-btn{
      width:60px;
      &:hover{
        background: #0369a1;
      }
    }
  }
}
@media (max-width: 460px) {
  .logo-text{
    display: none;
  }
  // .navbar-content{
  //   .navbar-brand{
  //     .logo-img{        
  //       max-width: 50px;
  //       margin-right: 10px;
  //       &.has-system-logo{     
  //         max-width: 200px;
  //         width: 40px;
  //         height: 40px;
  //         .logo-img-img{
  //           width: 40px;
  //           height: 40px;
  //           border-radius: 50%;
  //           object-fit: cover;
  //         }
  //      }
  //     }
  //   }
  // }
  
}
  
</style>