<template>
    <div class="payment-container container-custom">
        <div class="max-w-4xl mx-auto py-3-5">
            <div class="order-header flex items-center mb-3-5 hidden md:flex" v-if="orderDetail.status == '1'">
                <div class="order-icon-box bg-primary p-2 rounded-full mr-3">
                    <i class="iconfont icon-shopping-cart text-white" style='font-size:14px' ></i>
                </div>
                <h1 class="text-2xl font-bold">订单支付</h1>
            </div>
            <div class="mb-3-5" v-if="orderDetail.status == '2' || orderDetail.status == '3'">
                <TipsBlock/>
            </div>
            <div class="mb-3-5">
                <OrderInfo :orderInfo="orderDetail" />
            </div>
            <div class="mb-3-5" v-if="packageList.length >0 &&( orderDetail.status == '2' || orderDetail.status == '3')">
                <PackageDownload :packageList="packageList"></PackageDownload>
            </div>
            <div class="mb-3-5" v-if="orderDetail.status == '2' || orderDetail.status == '3'">
                <AccountInfo :orderInfo = "orderDetail" :hasTip="true"/>
            </div>
            <div class="flex justify-end" v-if="orderDetail.status == '2' || orderDetail.status == '3'">
                <el-button type="primary" @click="toMall" size="large" class="text-base rounded-md bg-from-blue-600">继续购物</el-button>
            </div>
            <div>
                <PayMethods v-if="orderDetail.status == '1'" :orderDetail="orderDetail" @toPay ="toPay" :paymentMethod="paymentMethod" :loading="loading"></PayMethods>
            </div>
            <div>
                <VideoPlayer v-if="orderDetail.status == '1'" :orderDetail="orderDetail" ></VideoPlayer>
            </div>
           
        </div>
    </div>
</template>
<script setup>
import { useRoute,useRouter } from "vue-router";
import { ref,onMounted,onUnmounted} from 'vue';
import{orderApi,paymentApi,userApi,productApi } from '@/api'
import { ElMessage } from 'element-plus'
import OrderInfo from "@/components/order/orderInfo.vue";
import AccountInfo from "@/components/order/accountInfo.vue";
import TipsBlock from "@/components/tipsBlock.vue";
import  PackageDownload  from '@/components/order/packageDownload.vue';
import PayMethods from "@/components/payMethods.vue";
import VideoPlayer from "@/components/videoPlayer.vue";
import emitter from '@/utils/eventBus';
const route = useRoute();
const router = useRouter();
const paymentMethod = ref('');
const paymentMethods = ref([])
const orderParam = ref(route.query)
const orderDetail = ref({})
const loading = ref(false)
const lunXun = ref(null)
const payMethodTime = ref(null)
const packageList = ref([])
const timeLeft = ref('00:00')
const countdownTimer = ref(null)
const isTimeout = ref(false)

// 格式化时间为 mm:ss 格式
const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// 计算剩余时间（秒）
const calculateTimeLeft = () => {
    if (!orderDetail.value.created_at || !orderDetail.value.order_timeout) {
        return 0;
    }
    return orderDetail.value.order_timeout>0?orderDetail.value.order_timeout:0;
}

// 开始倒计时
const startCountdown = () => {
    return
    // 清除之前的定时器
    if (countdownTimer.value) {
        clearInterval(countdownTimer.value);
    }
    
    // 计算初始剩余时间
    let remainingSeconds = calculateTimeLeft();
    
    // 如果已经超时，直接返回
    if (remainingSeconds <= 0) {
        isTimeout.value = true;
        timeLeft.value = '00:00';
        return;
    }
    
    // 立即更新一次显示
    timeLeft.value = formatTime(remainingSeconds);
    
    // 设置每秒更新一次
    countdownTimer.value = setInterval(() => {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            isTimeout.value = true;
            timeLeft.value = '00:00';
            clearInterval(countdownTimer.value);
            return;
        }
        timeLeft.value = formatTime(remainingSeconds);
    }, 1000);
}

  
const getOrderDetail = async () => {
    clearTimeout(lunXun.value)
   try{
        const res = await orderApi.getOrderDetail(orderParam.value)
        if(res.code === 1){
            orderDetail.value = res.data
            if(orderDetail.value.payment_info.payment_method !=''){
                paymentMethod.value = orderDetail.value.payment_info.payment_method
            }
            if(orderDetail.value.status == '1' ){
                // 只在第一次获取订单详情时启动倒计时
                if (!countdownTimer.value) {
                    startCountdown();
                }
                if(orderDetail.value.payment_info.payment_method != '' && orderDetail.value.order_timeout != 0){
                    lunXun.value = setTimeout(() => {
                        getOrderDetail()
                    }, 3000)
                }
            }else{
                //已支付 或者 已完成  2  3 可查询包
                getPackage()
                clearTimeout(lunXun.value)
                lunXun.value = null
                // 清除倒计时
                if (countdownTimer.value) {
                    clearInterval(countdownTimer.value);
                    countdownTimer.value = null;
                }
            }
        }else{
            if(res.code !=0){
                lunXun.value = setTimeout(() => {
                    getOrderDetail()
                }, 3000)
            }else{
                ElMessage.error(res.msg)
            }
            
        }
    }catch(error){
        console.error('获取订单详情失败:', error);
        // 请求失败时继续轮询
        lunXun.value = setTimeout(() => {
            getOrderDetail()
        }, 3000)
    }
}
//获取用户信息并保存
const getUserInfo = () => {
    return userApi.getUserInfo().then(res => {
        localStorage.setItem('allUserInfo', JSON.stringify(res.data));
    });
}
onMounted(() => {
    getOrderDetail()
    getPaymentMethods()
    getCustomerLinks()
})
onUnmounted(()=>{
    orderDetail.value={}
    clearTimeout(lunXun.value)
    lunXun.value = null
    clearTimeout(payMethodTime.value)
    payMethodTime.value = null
    // 清除倒计时定时器
    if (countdownTimer.value) {
        clearInterval(countdownTimer.value);
        countdownTimer.value = null;
    }
})
const toPay = async (orderDet,method) => {
    if(method=='balance'){
        try{
            let data = {"order_no":orderDet.order_number}
            loading.value = true
            const res = await paymentApi.payWithBalance(data)
            loading.value = false
            if(res.code == 1){
                getOrderDetail()
                await getUserInfo()
                emitter.emit('loginStatusChange'); // 新增这一行
                ElMessage.success(res.msg)            
                // window.open(`${res.data.payment_url}?order_no=${res.data.order_no}`, '_blank');
            }else{
                ElMessage.error(res.msg)
            }
        }catch(error){
            loading.value = false
            console.error('创建支付订单失败:', error);
        }finally{
            loading.value = false
        }

        return      
    }
    try{
        let data = {
            "order_no": orderDet.order_number,
            "payment_method": method
        }
        loading.value = true
        // const newWindow = window.open(``, '_blank');
        const res = await paymentApi.createPayment(data)
        loading.value = false
        if(res.code === 1){
            ElMessage.success(res.msg)
            if(res.data.payment_url && res.data.payment_url!=''){
                if (isIOS()) {
                    // iOS 设备使用 location.href，避免 window.open 被拦截
                    window.location.href = res.data.payment_url;  
                    
                } else {
                    // 非 iOS 设备可以安全使用 window.open
                    window.open(`${res.data.payment_url}`, '_blank');
                }
            }
            
            setTimeout(() => {                   
                getOrderDetail()
            }, 500);
            return;
        //    newWindow.location = res.data.payment_url
            // window.open(`${res.data.payment_url}`, '_blank');
        }else{
            ElMessage.error(res.msg)
        }
    }catch(error){
        loading.value = false
        console.error('创建支付订单失败:', error);
    }finally{
        loading.value = false
    }
}
const isIOS =() =>{
  const ua = navigator.userAgent;
  const platform = navigator.platform;
  const isIpadOS13 = platform === 'MacIntel' && navigator.maxTouchPoints > 1;
  return /iPhone|iPad|iPod/i.test(ua) || isIpadOS13;
}
const getPackage = async () => { 
 // 获取安装包列表
    const packageRes = await productApi.getPackages(orderParam.value.id)
    if(packageRes.code === 1) {
    
        packageList.value = packageRes.data.list
    }else{
        packageList.value = []
    }
}
const getPaymentMethods = async () => {
    return
    clearTimeout(payMethodTime.value)
    try{
        const res = await paymentApi.getPaymentMethods()
        if(res.code === 1){
            paymentMethods.value = res.data.payment_methods
        }
        clearTimeout(payMethodTime.value)
        payMethodTime.value = null
    }catch(error){
        payMethodTime.value = setTimeout(()=>{
            getPaymentMethods()
        },3000)
    }
   
}
const toMall = () => {
    router.push('/mall')
}
const handleChat = (type) => {
  if(type === 'online'){
    window.open(customerLinks.value.online_service_link);
  }else if(type === 'tg'){
    window.open(customerLinks.value.tg_service_link);
  }
};
const customerLinks = ref({});
const getCustomerLinks = async () => {
  const res = await userApi.getCustomerLinks();
  if(res.code === 1){
    customerLinks.value = res.data.customer_links;
  }else{
    // console.log(res.msg);
  }
  
};
</script>
<style scoped lang="scss">
.container-custom {
//   max-width: 1280px;
//   margin-left: auto;
//   margin-right: auto;
//   padding-left: 1rem;
//   padding-right: 1rem;
//   width: 100%;
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
.payment-container{
    .order-icon-box{
        height: 2rem;
        width: 2rem;
        // background-color: #009688;
        display: flex;
        justify-content: center;
        align-items: center;
      
    }
    .order-detail-box{
        .detail-title{
            .status-tag{
                font-size: .875rem;
                line-height: 1.25rem;
                padding: .25rem .75rem;
                border-radius: 9999px;
                &.to-paid{
                    color: #2563eb;
                    background-color: #eff6ff;
                }
            }
        }
        .detail-con{
            .detail-item{
                display: flex;
                justify-content: flex-start;
                align-items: center;
                .detail-label{
                    flex: 1;
                }
                .detail-value{
                    flex: 3;
                }
            }
        }
    }
    .payment-method-box{
        .method-list{
            .method-item{
                &:not(.active):hover{
                    background-color: #f9fafb;
                }
                &.alipay{
                    border-color: #3b82f6;
                    background-color: #eff6ff;
                }
                &.wechat{
                    border-color: #22c55e;
                    background-color: #f0fdf4;
                }
                &.usdt{
                    border-color: #14b8a6;
                    background-color: #f0fdfa;
                }
                .alipay-icon{
                    background-color: #dbeafe;
                    .icon-text{
                        color: #3b82f6;
                    }
                }
                .wechat-icon{
                    background-color: #dcfce7;
                    .icon-text{
                        color: #22c55e;
                    }
                }
                .usdt-icon{
                    background-color: #ccfbf1;
                    .icon-text{
                        color: #14b8a6;
                    }
                }
            }
        }
        .pay-btn{
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        .kefu-btn{
            &:hover{
                background-color: #2563eb;
            }
        }
    }
    
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
//   .container-custom {
//     max-width: 768px;
//   }
}
@media (min-width: 1024px) {
//   .container-custom {
//     max-width: 1024px;
//     padding-left: 2rem;
//     padding-right: 2rem;
//   }
}
@media (min-width: 1280px) {
//   .container-custom {
//     max-width: 1280px;
//   }
}
// .container-custom {
//     max-width: 1280px;
// }
</style>
