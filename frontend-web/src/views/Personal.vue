<template>
  <div class="account-center container-custom">
    <NoLogin v-if="!token"></NoLogin>
    <div v-else class="max-w-6xl mx-auto px-4 py-4 ">
      <div class="pc flex flex-col md:flex-row gap-4">
        <div class="pc-nav-box hidden md:block w-60 flex-shrink-0">
          <div
            class="nav-box rounded-lg shadow-sm overflow-hidden sticky top-20"
          >
            <nav class="overflow-y-auto h-full">
              <ul class="flex flex-col pt-2">
                <li>
                  <span
                    :class="
                      currentPath === 'info'
                        ? 'text-blue-700 bg-white active'
                        : 'text-white/90'
                    "
                    class="accoount-item flex items-center px-4 h-12 text-sm w-full text-left cursor-pointer"
                    @click="goToPage('info')"
                  >
                    <i
                      class="mr-2 iconfont icon-info-circle"
                      font-size="14px"
                    ></i>
                    我的信息
                  </span>
                </li>
                <li>
                  <span
                    :class="
                      currentPath === 'profile'
                        ? 'text-blue-700 bg-white active'
                        : 'text-white/90'
                    "
                    class="accoount-item flex items-center px-4 h-12 text-sm w-full text-left cursor-pointer"
                    @click="goToPage('profile')"
                  >
                    <i class="mr-2 iconfont icon-user" font-size="14px"></i>
                    个人资料
                  </span>
                </li>
                <li>
                  <span
                    :class="
                      currentPath === 'order'
                        ? 'text-blue-700 bg-white active'
                        : 'text-white/90'
                    "
                    class="accoount-item flex items-center px-4 h-12 text-sm w-full text-left cursor-pointer"
                    @click="goToPage('order')"
                  >
                    <i
                      class="mr-2 iconfont icon-shopping-bag"
                      font-size="14px"
                    ></i>
                    订单记录
                  </span>
                </li>
                
                <li>
                  <span
                    :class="
                      currentPath === 'rechargeOrder'
                        ? 'text-blue-700 bg-white active'
                        : 'text-white/90'
                    "
                    class="accoount-item flex items-center px-4 h-12 text-sm w-full text-left cursor-pointer"
                    @click="goToPage('rechargeOrder')"
                  >
                    <i
                      class="mr-2 iconfont icon-wallet"
                      font-size="14px"
                    ></i>
                    充值记录
                  </span>
                </li>
                <li>
                  <span
                    :class="
                      currentPath === 'billRecords'
                        ? 'text-blue-700 bg-white active'
                        : 'text-white/90'
                    "
                    class="accoount-item flex items-center px-4 h-12 text-sm w-full text-left cursor-pointer"
                    @click="goToPage('billRecords')"
                  >
                    <i
                      class="mr-2 iconfont icon-file-invoice-dollar"
                      font-size="14px"
                    ></i>
                    账单记录
                  </span>
                </li>
              </ul>
              <div class="mt-4 pt-3 border-t border-white/20 px-4 pb-4">
                <button
                  class="logout-btn cursor-pointer bg-transparent flex items-center h-12 px-0 text-sm w-full text-left text-white/90"
                  @click="handleLogout"
                >
                  <i class="mr-2 iconfont icon-lock" font-size="14px"></i>
                  退出登录
                </button>
              </div>
            </nav>
          </div>
        </div>
        <div
          class="account-content md:w-full w-full md:rounded-lg md:shadow-sm h-auto md:h-[calc(100vh-200px)] overflow-visible md:overflow-auto"
        >
          <router-view />
        </div>
      </div>
      <div class="mobile md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-9999" >
        <div class="flex justify-between items-center">
          <div class="flex flex-col items-center justify-center py-3 flex-1 text-xs" :class="currentPath === 'info' ? 'text-blue-600' : ' text-gray-600'" @click="goToPage('info')" >
            <div class="text-lg mb-1">
              <i class="iconfont icon-info-circle" style="font-size: 18px;"></i>
            </div>
            
            <span>我的信息</span>
          </div>
          <div class="flex flex-col items-center justify-center py-3 flex-1 text-xs" :class="currentPath === 'profile' ? 'text-blue-600' : ' text-gray-600'" @click="goToPage('profile')">  
            <div class="text-lg mb-1">
              <i class="iconfont icon-user" style="font-size: 18px;"></i>
            </div>          
            <span>个人资料</span>
          </div>
          <div class="flex flex-col items-center justify-center py -3 flex-1 text-xs" :class="currentPath === 'order' ? 'text-blue-600' : ' text-gray-600'" @click="goToPage('order')">   
            <div class="text-lg mb-1">
              <i class="iconfont icon-shopping-bag" style="font-size: 18px;"></i>
            </div>         
            <span>订单记录</span>
          </div>

          <div class="flex flex-col items-center justify-center py -3 flex-1 text-xs" :class="currentPath === 'rechargeOrder' ? 'text-blue-600' : ' text-gray-600'" @click="goToPage('rechargeOrder')">   
            <div class="text-lg mb-1">
              <i class="iconfont icon-wallet" style="font-size: 18px;"></i>
            </div>         
            <span>充值记录</span>
          </div>

          <div class="flex flex-col items-center justify-center py -3 flex-1 text-xs" :class="currentPath === 'billRecords' ? 'text-blue-600' : ' text-gray-600'" @click="goToPage('billRecords')">   
            <div class="text-lg mb-1">
              <i class="iconfont icon-file-invoice-dollar" style="font-size: 18px;"></i>
            </div>         
            <span>账单记录</span>
          </div>

          <div class="flex flex-col items-center justify-center py -3 flex-1 text-xs text-gray-600"  @click="handleLogout">   
            <div class="text-lg mb-1">
              <i class="iconfont icon-lock " style="font-size: 18px;"></i>
            </div>         
            <span>退出</span>
          </div>

          <!-- <el-button type="primary" class="flex flex-col items-center justify-center py-3 flex-1 text-xs text-gray-600" @click="handleLogout">
            退出
          </el-button> -->
        </div>
      </div>
    </div>
  
  </div>
</template>

<script setup>
import { useRoute, useRouter } from "vue-router";
import { ref, watch, onMounted } from "vue";
import { ElMessage } from "element-plus";
import { userApi } from "@/api";
import emitter from "@/utils/eventBus";
import NoLogin from "@/components/noLogin.vue";

const route = useRoute();
const router = useRouter();
const currentPath = ref("");
const token = ref(localStorage.getItem('token'));
// 更新当前路径
const updateCurrentPath = () => {
  const path = route.path.split("/").pop();
  currentPath.value = path || "info";
};

// 跳转到指定页面
const goToPage = (page) => {
  router.push(`/personal/${page}`);
};

// 处理退出登录
const handleLogout = () => {
  userApi.logout().then((res) => {
    // if (res.code === 1) {
    localStorage.removeItem("token");
    localStorage.removeItem("userInfo");
    emitter.emit("loginStatusChange");
    ElMessage.success("退出成功");
    router.push("/login");
    // }
  });
};

// 监听路由变化
watch(
  () => route.path,
  () => {
    updateCurrentPath();
  }
);

// 组件挂载时初始化
onMounted(() => {
  updateCurrentPath();
});
</script>

<style scoped lang="scss">
.container-custom {
  // max-width: 1280px;
  // margin-left: auto;
  // margin-right: auto;
  // padding-left: 1rem;
  // padding-right: 1rem;
  // width: 100%;
  .product-attr-box {
    background-color: #fff2e8;
    .restock {
      color: var(--primary-color);
      text-decoration: underline;
      &:hover {
        color: var(--primary-dark);
      }
    }
  }
  .product-input {
    padding: 10px 1rem;

    &:focus {
      //   border: solid 2px var(--primary-color);
      outline: solid 2px var(--primary-color);
    }
  }
  .buy-btn {
    background-color: var(--primary-color);
    color: #fff;
    padding: 10px 1rem;
    &:hover {
      background-color: var(--primary-dark);
    }
  }
}
.account-center {
  padding:0;
  .pc-nav-box {
    .nav-box {
      background-image: linear-gradient(to bottom, #2563eb,#1d4ed8,#4338ca);
      height: calc(100vh - 200px);
      .accoount-item {
        &:not(.active):hover {
          background-color: rgba(255, 255, 255, 0.2);
        }
      }
      .logout-btn {
        &:hover {
          background-color: rgba(255, 255, 255, 0.2);
        }
      }
    }
  }
  .account-content{
    height: calc(100vh - 200px);
    box-sizing: border-box;
    overflow-y: auto;
  }
}
.mobile{
  position: fixed;
  width: 100%;
  bottom: 0;
  left: 0;
  right: 0;
}
@media (min-width: 640px) {
  .container-custom {
    // padding-left: 1.5rem;
    // padding-right: 1.5rem;
    // max-width: 640px;
    .product-input {
      width: 66.666667%;
    }
  }
}
@media (min-width: 768px) {
  .container-custom {
    // max-width: 768px;
  }
  .md\:w-full {
    // width: 100%;
    max-width: calc(100% - 15rem);
  }
}
@media (min-width: 1024px) {
  .container-custom {
    // max-width: 1024px;
    // padding-left: 2rem;
    // padding-right: 2rem;
  }
}
@media (min-width: 1280px) {
  .container-custom {
    // max-width: 1280px;
  }
}
</style>
