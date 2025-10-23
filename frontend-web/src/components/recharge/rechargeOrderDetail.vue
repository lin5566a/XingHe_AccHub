<template>
    
    <div v-if="orderInfo" class="order-info bg-white p-3-5 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center font-medium" >
                <i class="text-base sm:text-xl iconfont icon-info-circle primary-color mr-2" style="font-weight: 300;"></i>
                充值详情
            </h2>
            <div class="px-2 py-1 text-xs rounded-md" :class="getStatusType(orderInfo?.status)">
                {{ orderInfo?.status_text || '--' }}
            </div>
        </div>
        <div class="border-t border-gray-200 py-3 sm:py-4 mb-2 sm:mb-4">
            <div class="grid grid-cols-2 gap-y-2 sm:gap-y-3">
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">充值单号：</span>
                    <span class="font-medium primary-color ml-1 text-sm">{{ orderInfo?.order_no || '--' }}</span>
                  
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">充值金额：</span>
                    <span class="font-medium primary-color ml-1 text-sm">¥{{ orderInfo?.recharge_amount || '0.00' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">充值账户：</span>
                    <span class="font-medium primary-color ml-1 text-sm">{{ orderInfo?.nickname || '--' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">充值时间：</span>
                    <span class="font-medium ml-1 text-sm">{{ orderInfo?.created_at || '--' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">账户邮箱：</span>
                    <span class="font-medium ml-1 text-sm">{{ orderInfo?.user_email || '--' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">支付状态：</span>
                    <span class="font-medium ml-1 text-sm" :class="orderInfo?.status == '0'?'text-orange-500':'primary-color'">{{ orderInfo?.status_text || '--' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { ElMessage } from 'element-plus'


const props = defineProps({
    orderInfo: {
        type: Object,
        default: () => ({})
    }
})
const getStatusType = (value) => {
    // 确保value是字符串类型
    const statusValue = String(value);
    switch(statusValue) {
        case '1': // 已完成
            return 'bg-blue-100 text-primary ';  // 蓝色       
        case '0': // 待支付
            return 'bg-blue-50 text-blue-600';  // 蓝色
        default:
            //2 已退款  3 已取消
            return 'bg-gray-100 text-gray-800';
    }
}
</script>


