<template>
  <div v-if="props.orderList.length > 0" class="search-result bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 mb-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-800 flex items-center" >
        <i class="iconfont icon-shopping-bag text-blue-600 mr-2" style="font-size: 18px;"></i>
        查询结果
        <!--  ({{ props.orderList.length }}个订单) -->
      </h2>
     
    </div>
    <div class="result-list space-y-3 overflow-y-auto pr-1">
      <div 
        class="result-item pb-1 px-3 pt-3 rounded-lg cursor-pointer transition-all" 
        v-for="(item, index) in props.orderList" 
        :key="index" 
        :class="{ 
          active: currentOrder?.order_number === item.order_number
        }"
        @click="handleOrderClick(item)"
      >
        <div class="flex justify-between items-start mb-2 items-center">   
          <span class="font-medium text-gray-900">{{ item.product_name }}</span>
          <div class="px-2 py-1 text-xs rounded-full" :class="getStatusType(item.status)">
              {{ item.status_text || '--' }}
          </div>
        </div>
        <div class="flex justify-between text-sm flex-wrap">         
          <div class="mb-2 mr-2 order-item">
            <span class="text-gray-500"><i class = "iconfont icon-calendar-alt text-gray-400 mr-1" style="font-size: 12px;"></i>下单时间：</span>
            <span class="text-gray-700">{{ item.created_at }}</span>
          </div>
          <div class="mb-2 mr-2 order-item">
            <span class="text-gray-500"> <i class = "iconfont icon-clock text-gray-400 mr-1" style="font-size: 12px;"></i>  支付时间：</span>
            <span class="text-gray-700">{{ item.created_at }}</span>
          </div>
        </div>
        <div class="flex justify-between text-sm flex-wrap">
         
          <div class="mb-2 mr-2 order-item">
            <span class="text-gray-500">订单号：</span>
            <span class="text-blue-600">{{ item.order_number }}</span>
          </div>
          <div class="flex justify-between items-center mb-2  mr-2 order-item" >
            <div>
              <span class="text-gray-500 mr-1">卡密数量：</span>
              <span class="text-blue-600">{{ item.card_info?.length || 0 }} 个</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref ,defineProps,defineEmits,watch} from 'vue'
import { orderApi } from '@/api'

const props = defineProps({
  orderList: {
    type: Array,
    default: () => []
  },
  firstOrder: {
    type: Object,
    default: () => {}
  },
  email: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['orderSelected', 'reSearch'])
const currentOrder = ref(null)
const loading = ref(false)

const handleOrderClick = async (order) => {
  
  emit('orderSelected', order)
  return
  if (loading.value) return
  if (currentOrder.value?.order_number === order.order_number) return
  
  try {
    loading.value = true
    const res = await orderApi.getOrderDetail({
      order_no: order.order_number,
      email: props.email
    })
    
    if (res.code === 1) {
      currentOrder.value = res.data
      emit('orderSelected', res.data)
    }
  } catch (error) {
    console.error('获取订单详情失败:', error)
  } finally {
    loading.value = false
  }
}
const getStatusType = (value) => {
    // 确保value是字符串类型
    const statusValue = String(value);
    switch(statusValue) {
        case '3': // 已发货
            return 'bg-blue-100 text-primary';  // 
        case '2': // 待发货
            return 'bg-primary-100 primary-color ';  // 
        case '1': // 待付款
            return 'bg-blue-50 text-blue-600';  // 
        default:
            return 'bg-gray-100 text-gray-800';
    }
    }
// 监听路由变化
watch(() => props.firstOrder, () => {
    currentOrder.value = props.firstOrder
})
</script>

<style lang="scss" scoped>
.search-result {
  .space-y-3 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-y-reverse: 0;
    margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
    margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
  }
  .result-list {
    max-height: calc(3 * 7.2rem);
    .result-item {
      border: 1px solid #e5e7eb;
      &:hover {
        border: 1px solid #d1d5db;
        background: #f9fafb;
      }
      &.active {
        border: 1px solid var(--primary-dark);
        background: #eff6ff;
      }
      .order-item{
        min-width: calc(50% - 0.5rem);
      }
    }
  }
}
</style>
