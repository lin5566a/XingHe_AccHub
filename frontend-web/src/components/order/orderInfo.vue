<template>
    
    <div v-if="orderInfo" class="order-info bg-white p-3-5 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center font-medium" >
                <i class="text-base sm:text-xl iconfont icon-info-circle primary-color mr-2" style="font-weight: 300;"></i>
                <!-- <i class="iconfont icon-info-circle mr-2 text-blue-500" style="font-size:16px"></i> -->
                <!-- <el-icon color="var(--primary-color)" size="20px" class="mr-2" ><Handbag /></el-icon> -->
                订单详情
            </h2>
            <div 
                class="px-2 py-1 text-xs rounded-md"
               
                 :class="getStatusType(orderInfo?.status)"
            >
                {{ orderInfo?.status_text || '--' }}
            </div>
        </div>
        <div class="border-t border-gray-200 py-3 sm:py-4 mb-2 sm:mb-4">
            <div class="grid grid-cols-2 gap-y-2 sm:gap-y-3">
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">订单号：</span>
                    <span class="font-medium primary-color ml-1 text-sm">{{ orderInfo?.order_number || '--' }}</span>
                    <span class="primary-color cursor-pointer text-sm ml-2" @click="copyOrderNumber(orderInfo.order_number)">
                        <i class="iconfont icon-clipboard text-primary-color" style="font-size: 14px;"></i>
                    </span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">商品名称：</span>
                    <span class="font-medium primary-color ml-1 text-sm">{{ orderInfo?.product_name || '--' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">商品单价：</span>
                    <span class="font-medium primary-color ml-1 text-sm">¥{{ orderInfo?.unit_price || '0.00' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">数量：</span>
                    <span class="font-medium primary-color ml-1 text-sm">{{ orderInfo?.quantity || '0' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">订单总额：</span>
                    <span class="font-medium primary-color ml-1 text-sm">¥{{ orderInfo?.total_price || '0.00' }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="text-gray-500 text-sm">下单时间：</span>
                    <span class="font-medium ml-1 text-sm">{{ orderInfo?.created_at || '--' }}</span>
                </div>
                
                <div class="col-span-2 sm:col-span-1" v-if="orderInfo?.email">
                    <span class="text-gray-500 text-sm">收货邮箱：</span>
                    <span class="font-medium ml-1 text-sm">{{ orderInfo?.email || '--' }}</span>
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
        case '3': // 已发货
            return 'bg-green-100 text-green-800';  // 绿色
        case '2': // 待发货
            return 'bg-primary-100 primary-color ';  // 绿色
        case '1': // 待付款
            return 'bg-blue-50 text-blue-600';  // 蓝色
        default:
            return 'bg-gray-100 text-gray-800';
    }
    }
const copyOrderNumber = (text) => {
    if (text) {
        navigator.clipboard.writeText(text);
        ElMessage.success('订单号已复制到剪贴板');
    }
}
</script>


