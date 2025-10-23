<template>
    
    <div class="payment-method-box bg-white border border-gray-200 rounded-lg shadow-sm p-3.5 mb-6">
        <div class="method-header flex items-center mb-4 pb-3 border-b border-gray-100">
            <h2 class="text-base sm:text-lg font-medium text-gray-800 flex items-center">
                <!-- <el-icon color="#6b7280" class="mr-2"><Ticket /></el-icon> -->
                <i class="iconfont icon-money-bill-wave mr-2 text-primary text-sm sm:text-lg"></i>
                选择支付方式
            </h2>
        </div>
        <div class="method-list grid  gap-1.5 sm:gap-3.5 mb-3.5" :class="(orderType == 'order' && paymentMethods && paymentMethods.length == 4)?'sm:grid-cols-4 grid-cols-2': orderType=='recharge'?'grid-cols-3':'grid-cols-3'">
            <template  v-for="(item,index) in paymentMethods" :key="index">

                <div class="method-item border rounded-lg  p-2 sm:p-3.5 flex flex-col sm:flex-row items-center cursor-pointer border-gray-200"  
                    :class="[paymentMethod == item.code ? `${item.code} active`:'', (item.code == 'balance' && !payForBalance) ? 'disable':'']"
                    @click="changePaymentMethod(item.code)"
                    v-if="orderDetail.payment_info.payment_method == '' || orderDetail.payment_info.payment_method == item.code"
                >                    
                    <div class="w-8 h-8 sm:w-10 sm:h-10 sm:mr-3 mb-1 sm:mb-0 rounded-full flex items-center justify-center" :class="`${item.code}-icon`">
                        <i class="iconfont icon-text text-base sm:text-xl" :class="item.icon"></i>
                    </div>
                    <div>
                        <h3 class="font-medium  text-xs sm:text-sm">{{item.name}}</h3>
                        <p class="text-gray-500 text-xxs sm:text-xs hidden sm:block">{{item.description}}</p>
                    </div>
                </div>

            </template>
        </div>
        <div class="flex justify-end items-center">
            <span class="text-base text-gray-700 mr-1">支付倒计时：</span>
            <span v-if="isTimeout" class="text-lg font-semibold mr-4 text-red-500">支付超时</span>
            <span v-else class="text-lg font-semibold mr-4 primary-color">{{timeLeft}}</span>
            <el-button class="pay-btn bg-from-blue-600" :class="{'bg-disabled-70':loading || isTimeout}" type="primary" :loading ="loading"  @click="toPay" :disabled="loading || isTimeout">立即支付 ¥{{ orderType == 'order'?orderDetail.total_price : orderDetail.recharge_amount}}</el-button>
            <!-- <div class="button-primary pay-btn cursor-pointer" @click="toPay" :class="{'opacity-50 cursor-not-allowed': loading || isTimeout}"><el-icon ><Loading /></el-icon>立即支付</div> -->
        </div>

        <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 flex flex-col sm:flex-row items-center justify-between">
            <div class="flex items-center mb-2 sm:mb-0">
                <span class="text-sm text-blue-700">
                    <i class="iconfont icon-info-circle mr-2 text-blue-500" style="font-size:16px"></i> 
                    付款不成功？请联系我们的客服获取帮助
                </span>
            </div>
            <span class="kefu-btn cursor-pointer flex items-center justify-center px-4 py-1.5 bg-from-blue-600 text-white text-sm rounded-md" @click="handleChat('online')">
                <i class="iconfont icon-kefu mr-2" style="font-size:18px"></i>
                联系客服
            </span>
        </div>

    </div>
</template>
<script setup> 

// import { useRoute,useRouter } from "vue-router";
import { ref,onMounted,onUnmounted,defineProps,defineEmits,computed} from 'vue';
import { ElMessage } from 'element-plus'
import{paymentApi,userApi } from '@/api'
const paymentMethod = ref('');
const paymentMethods = ref([])
const payMethodTime = ref(null)
const timeLeft = ref('00:00')
const countdownTimer = ref(null)
const isTimeout = ref(false)
//余额支付
const payForBalance = ref(false)
const props = defineProps({
    loading:{
        type:Boolean,
        default:false
    },
    orderDetail:{
        type:Object,
        default:()=>{}
    },
    orderType:{
        type:String,
        default:'order'
    },
    paymentMethod:{
        type:String,
        default:''
    }
})
const getBalance = ()=>{
    userApi.getBalance().then(res=>{
        if(res.code == 1){
            // console.log(res.data.balance,'=====',Number(props.orderDetail.total_price))
            if(res.data.balance >0 && res.data.balance > Number(props.orderDetail.total_price)){
                payForBalance.value = true
            }else{
                payForBalance.value = false
            }
        }else{
            ElMessage.error(res.msg)
        }
    }).catch(err=>{
        // console.log(err)
    })
}
const emit = defineEmits(['toPay','payTimeout'])
// 格式化时间为 mm:ss 格式
const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}
// 计算剩余时间（秒）
const calculateTimeLeft = () => {
    if (!props.orderDetail.created_at || !props.orderDetail.order_timeout) {
        return 0;
    }
    return props.orderDetail.order_timeout>0?props.orderDetail.order_timeout:0;
}
// 开始倒计时
const startCountdown = () => {
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
        emit('payTimeout');
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
            emit('payTimeout');
            clearInterval(countdownTimer.value);
            return;
        }
        timeLeft.value = formatTime(remainingSeconds);
    }, 1000);
}
//获取支付方式
const getPaymentMethods = async () => {
    
    // console.log('=====')
    clearTimeout(payMethodTime.value)
    try{
        const res = await paymentApi.getPaymentMethods()
        if(res.code === 1){
            if(props.orderType == 'order'){
                paymentMethods.value = res.data.payment_methods
                let hasBalance = false;
                if(paymentMethods.value && paymentMethods.value.length>0){
                    paymentMethods.value.forEach(item=>{
                        if(item.code == 'balance'){
                            hasBalance = true
                        }
                    })
                }
                if(hasBalance){
                    getBalance();
                }
                // console.log(paymentMethods.value,'paymentMethods.value')
            }else if(props.orderType == 'recharge'){
                paymentMethods.value = res.data.payment_methods.filter(item=>item.code != 'balance')
            }

            
        }
        clearTimeout(payMethodTime.value)
        payMethodTime.value = null
    }catch(error){
        // console.log(error,'====error====')
        payMethodTime.value = setTimeout(()=>{
            getPaymentMethods()
        },3000)
    }
   
}
//选择支付方式
const changePaymentMethod = (method) => {
    if(method != 'balance'){
        paymentMethod.value = method;
    }else if(payForBalance.value ){
        paymentMethod.value = method;
    }
}   
//确认支付
const toPay = ()=>{
    if(props.loading || isTimeout.value){
        return
    }
    if(!paymentMethod.value){
        ElMessage.error('请选择支付方式')
        return
    }
    // console.log('props.loading',props.loading)
    emit('toPay',props.orderDetail, paymentMethod.value)
}
onMounted(() => {
    // console.log('orderDetail',props.orderDetail)
    paymentMethod.value = props.paymentMethod;
    startCountdown()
    getPaymentMethods();
   
})

onUnmounted(()=>{
    clearTimeout(payMethodTime.value)
    payMethodTime.value = null
      // 清除倒计时定时器
    if (countdownTimer.value) {
        clearInterval(countdownTimer.value);
        countdownTimer.value = null;
    }
})
</script>
<style lang="scss" scoped>
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
                &.balance{
                    border-color: #4f46e5;
                    background-color: #eef2ff;
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
                .balance-icon{
                    background-color: #e0e7ff;
                    .icon-text{
                        color: #4f46e5;
                    }
                }
                
                &.disable{
                    opacity: 0.5;
                    cursor: no-drop;
                }
            }
        }
        .pay-btn{
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            height:40px;
            border:none;            
            border-radius: 0.375rem;
            font-size: 16px;
            font-weight: 400;
            // background-image: linear-gradient(to right, #2563eb,#4f46e5);
            // &:hover {
            //     background-image: linear-gradient(to right,#1d4ed8,#4338ca );
            // }
        }
        .kefu-btn{
            &:hover{
                background-color: #2563eb;
            }
        }
    }
</style>