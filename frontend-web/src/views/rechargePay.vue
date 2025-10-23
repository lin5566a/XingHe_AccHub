<template>
    <div class="payment-container container-custom">
        <div class="max-w-4xl mx-auto py-3-5">
            <div class="order-header flex items-center mb-3-5 hidden md:flex" v-if="orderDetail.status == '0'">
                <div class="order-icon-box bg-primary p-2 rounded-full mr-3">
                    <i class="iconfont icon-wallet text-white" style='font-size:14px' ></i>
                </div>
                <h1 class="text-2xl font-bold">充值支付</h1>
            </div>   
            <div v-if="orderDetail.status == '1'">
                <TipsBlock title="充值成功" backgroundColor="#e6eefa" :content="`您的账户已成功充值 ${orderDetail.recharge_amount} 元`"/>      
            </div>             
            <div class="mb-3-5">
                <RechargeOrderDetail :orderInfo="orderDetail" />
            </div>  
            <div class="mb-3-5 justify-end flex"  v-if="orderDetail.status == '1'">
                <el-button class="back-recharge-btn bg-from-blue-600 rounded-md" type="primary" @click="goBack">返回账户页面</el-button>
            </div>         
            <PayMethods v-if="orderDetail.status == '0'" :orderDetail="orderDetail" orderType = 'recharge' @toPay ="toPay" @payTimeout="payTimeout" :paymentMethod="paymentMethod" :loading="loading"></PayMethods>                      
            <div class="payment-notice bg-yellow-50 border border-yellow-200 rounded-lg p-4" v-if="orderDetail.status == '0'">
                <h3 class="text-sm font-medium text-yellow-800 flex items-center mb-2">
                    <i class="iconfont icon-info-circle mr-2 text-yellow-800" style="font-size: 14px; font-weight: 300;"></i>
                     充值须知
                </h3>
                <ul class="text-sm text-yellow-700 space-y-1 pl-5 list-disc">
                    <li>充值成功后，金额将立即添加到您的账户余额中</li>
                    <li>账户余额可用于购买网站上的所有商品</li>
                    <li>如遇支付问题，请联系在线客服获取帮助</li>
                </ul>
                
            </div>
            <div v-if="(order_timeout || orderDetail.order_timeout == 0) && orderDetail.status == '0'">
                <timeOut @goBack="goBack"></timeOut>
            </div>
        </div>
    </div>
</template>
<script setup>
import { useRoute,useRouter } from "vue-router";
import { ref,onMounted,onUnmounted} from 'vue';
import{orderApi,paymentApi,userApi,productApi } from '@/api'
import { ElMessage } from 'element-plus'
import RechargeOrderDetail from "@/components/recharge/rechargeOrderDetail.vue";
import PayMethods from "@/components/payMethods.vue";
import timeOut from "@/components/recharge/timeOut.vue";
import TipsBlock from "@/components/tipsBlock.vue";
import emitter from '@/utils/eventBus';
const route = useRoute();
const router = useRouter();
const paymentMethod = ref('');
const orderParam = ref(route.query)
const orderDetail = ref({})
const loading = ref(false)
const lunXun = ref(null)
const payMethodTime = ref(null)
const isTimeout = ref(false)
const gotoRechargeTime =ref(null) //支付成功 自动跳转充值页面 倒计时
const order_timeout = ref(false)
const getOrderDetail = async () => {
    clearTimeout(lunXun.value)
   try{
        const res = await orderApi.rechargeDetail(orderParam.value)
        if(res.code === 1){
            orderDetail.value = res.data
            if(orderDetail.value.payment_info.payment_method !=''){
                paymentMethod.value = orderDetail.value.payment_info.payment_method
            }
            if(orderDetail.value.status == '0' ){
                // 只在第一次获取订单详情时启动倒计时
                if(orderDetail.value.payment_info.payment_method != '' && orderDetail.value.order_timeout != 0){
                    lunXun.value = setTimeout(() => {
                        getOrderDetail()
                    }, 3000)
                }
            }else{
                if(orderDetail.value.status == '1'){
                    await getUserInfo();
                    emitter.emit('loginStatusChange');
                    gotoRechargeTime.value = setTimeout(()=>{
                        goBack()
                        clearTimeout(gotoRechargeTime.value)
                    },3000)
                }
                clearTimeout(lunXun.value)
                lunXun.value = null
              
            }
        }else{
            lunXun.value = setTimeout(() => {
                getOrderDetail()
            }, 3000)
        }
    }catch(error){
        console.error('获取订单详情失败:', error);
        // 请求失败时继续轮询
        lunXun.value = setTimeout(() => {
            getOrderDetail()
        }, 3000)
    }
}
const goBack=()=>{
    router.push('/recharge')
}
//获取用户信息并保存
const getUserInfo = () => {
    return userApi.getUserInfo().then(res => {
        localStorage.setItem('allUserInfo', JSON.stringify(res.data));
    });
}
onMounted(() => {
    getOrderDetail()
})
onUnmounted(()=>{
    clearTimeout(lunXun.value)
    lunXun.value = null
    clearTimeout(payMethodTime.value)
    payMethodTime.value = null    
    clearTimeout(gotoRechargeTime.value)
    gotoRechargeTime.value = null
  
})
const toPay = async (orderDet,method) => {
    if(loading.value || isTimeout.value){
        return
    }
    if(!method){
        ElMessage.error('请选择支付方式')
        return
    }
    try{
        let data = {
            "order_no": orderDet.order_no,
            "payment_method": method
        }
        loading.value = true
        const res = await paymentApi.rechargePay(data)
        loading.value = false
        if(res.code === 1){
            getOrderDetail()
            ElMessage.success(res.msg)
           
            window.open(`${res.data.payment_url}`, '_blank');
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
const payTimeout = ()=>{ 
    order_timeout.value = true
}
</script>
<style scoped lang="scss">
.container-custom {
  .product-attr-box {
    background-color: #fff2e8;    
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
    .back-recharge-btn{
        height: 40px;
        font-size: 1rem;

    }
}


@media (min-width: 640px) {
  .container-custom {
    .product-input {
      width: 66.666667%;
    }
  }
}
</style>
