<!-- 
  🛠定制开发 - 专业团队提供个性化开发服务
  💳支付通道对接 - 快速对接第三方支付平台
  🤝业务合作 - 多种合作模式，共创双赢
  QQ: 3909001743 | Telegram: @sy9088  
-->

<template>
  <div class="app-container flex flex-col min-h-screen" :class="{ 'is-mobile': isMobile }">
    <!-- 电脑端导航 -->
    <pcNav :isMobile="isMobile" :systemInfo="systemInfo" :announcement="announcement" @closeAnnouncement = 'closeAnnouncement' />

    <!-- 移动端导航 -->
    <mobileNav :isMobile="isMobile" :systemInfo="systemInfo" :announcement="announcement" @closeAnnouncement = 'closeAnnouncement'/>

    <!-- 主内容区域 --> 
    <main class="main-content flex-grow">
      <router-view />
    </main>
    <Footer :systemInfo="systemInfo"></Footer>
    <div class="fixed-btn fixed right-6 bottom-6 flex flex-col gap-3 z-50">
      <div class="pulse-effect w-10 h-10 rounded-full" v-if="customerLinks.group_link">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center cursor-pointer" @click="handleChat('group')">
          <i class="iconfont icon-qun text-white" style="font-size:18px"></i>
        </div>
      </div>
      <div class="pulse-effect w-10 h-10 rounded-full" v-if="customerLinks.online_service_link">
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center cursor-pointer" @click="handleChat('online')">
          <i class="iconfont icon-headset text-white" style="font-size:18px"></i>
        </div>
      </div>
      <div class="w-10 h-10 rounded-full" v-if="customerLinks.tg_service_link">
        <div class="w-10 h-10 bg-blue-600-2 rounded-full flex items-center justify-center cursor-pointer" @click="handleChat('tg')">
          <i class="iconfont icon-xiaofeiji text-white" style="font-size:16px"></i>
        </div>
      </div>
      <div class="w-10 h-10 rounded-full">
        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center cursor-pointer" 
            :class="{ 'hidden': !showBackToTop }"
            @click="scrollToTop">
          <i class="iconfont icon-arrow-up text-white" style="font-size:18px"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import pcNav from "@/components/pcNav.vue";
import mobileNav from "@/components/mobileNav.vue";
import Footer from "@/components/footer.vue"
import { ref, onMounted, onUnmounted, watch, onBeforeMount } from "vue";
import { userApi,protocolApi } from '@/api'
import emitter from '@/utils/eventBus';
import util from '@/utils/util'
import { useRoute } from 'vue-router'
// 管理移动端状态
const isMobile = ref(false);
const systemInfo = ref({});
//公告相关
//是否显示
const announcement = ref({});
const announcementSetOut = ref(null);
const route = useRoute()

const closeAnnouncement = ()=>{
  sessionStorage.setItem('announcementId',announcement.value.id);
  announcement.value.show = false
  sessionStorage.setItem('announcementShow',announcement.value.show);
  localStorage.setItem('announcementShow',announcement.value.show);  
  emitter.emit('announcementShowChange');
}

// 检查设备屏幕宽度
const checkIsMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

const showBackToTop = ref(false);

const handleScroll = () => {
  showBackToTop.value = window.scrollY > 300;
};
const handleChat = (type) => {
  if(type === 'online'){
    window.open(customerLinks.value.online_service_link);
  }else if(type === 'tg'){
    window.open(customerLinks.value.tg_service_link);
  }else if(type === 'group'){
    window.open(customerLinks.value.group_link);
  }
};
const customerLinks = ref({});
const getCustomerLinks = async () => {
  const res = await userApi.getCustomerLinks();
  if(res.code === 1){
    customerLinks.value = res.data.customer_links;
    localStorage.setItem('customerLinks', JSON.stringify(res.data.customer_links));
  }else{
    console.log(res.msg);
  }
  
};
const getAnnouncement = async () => { 
  const res = await protocolApi.getAnnouncement();  
  if(res.code == 1){
    if(!res.data || JSON.stringify(res.data) == '{}'){
      announcement.value.show = false
    }else{
      announcement.value.title=res.data.title
      announcement.value.content = res.data.content
      announcement.value.token = res.data.token
      announcement.value.publish_time = res.data.publish_time
      announcement.value.id = res.data.id
      let id = sessionStorage.getItem('announcementId');
      // let id = localStorage.getItem('announcementId');
      if(id == announcement.value.id){
        announcement.value.show = false
      }else{
        announcement.value.show = true
      }      
    }    
    sessionStorage.setItem('announcementShow',announcement.value.show);
    localStorage.setItem('announcementShow',announcement.value.show);    
    emitter.emit('announcementShowChange');
  }
};
//获取用户信息并保存 用于刷新个人信息
const getUserInfo = () => {
    userApi.getUserInfo().then(res => {
        localStorage.setItem('allUserInfo', JSON.stringify(res.data));
        emitter.emit('loginStatusChange');
    });
}
onMounted(async() => {
    if(localStorage.getItem("token")){
      getUserInfo();
    }
    //公告相关
    
    localStorage.setItem('announcementShow',announcement.value.show);  
    emitter.emit('announcementShowChange');
    getAnnouncement()
    announcementSetOut.value = setInterval(()=>{
      getAnnouncement()
    },30000)

    getCustomerLinks()
    checkIsMobile();
    window.addEventListener("resize", checkIsMobile);
    window.addEventListener("scroll", handleScroll);
    try {
      // 调用后端接口获取网页标题和图标
      const res = await protocolApi.getSystemInfo();
      systemInfo.value= res.data;
      // 动态设置网页标题
      document.title = systemInfo.value.system_name;
      // 动态设置网页图标
    } catch (error) {
      console.error('获取网页标题和图标失败:', error);
    }
});

onUnmounted(() => {
  window.removeEventListener("resize", checkIsMobile);
  window.removeEventListener("scroll", handleScroll);
  clearInterval(announcementSetOut.value)
});
watch(
  () => route.query,
  (newQuery) => {
    const channel = newQuery.channel
    if (channel) {
      try { 
        util.setCookie('channel', channel); 
        localStorage.setItem('channel', channel); 
      } catch(e){}
    }
  },
  { immediate: true } // 页面首次加载也触发一次
)

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};


//设置网页图标
// const setFavicon = (iconUrl) => {
//   console.log(iconUrl,'iconUrl')
//   let link = document.querySelector("link[rel~='icon']");
//   if (!link) {
//     link = document.createElement('link');
//     link.rel = 'icon';
//     document.head.appendChild(link);
//   }
//   link.href = iconUrl;
// };
    
</script>
<style lang="scss">
.navbar-content {
  max-width: 72rem;
  padding: 0 1.5rem;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 56px;
}
.navbar-brand {
  .logo-img {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background-color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 0.5rem;
  }
  .logo-text {
    color: #fff;
    font-size: 1.125rem;
    font-weight: 600;
  }
}
.fixed-btn{
 
}
.pulse-effect{
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(0, 150, 136, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(0, 150, 136, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(0, 150, 136, 0);
  }
}
</style>
