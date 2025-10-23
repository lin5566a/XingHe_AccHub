<template>
  <div class="product-item" @click="showDetail(props.item)">
    <el-card shadow="hover">
      <div class="product-item-content flex flex-row sm:flex-col">
        <div class="product-item-img flex-1">
          <el-image
            :src="props.item.image"
            fit="cover"
          ></el-image>
        </div>
        <div class="product-item-text flex flex-col justify-between sm:p-4 p-3 flex-1">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="item-text flex-1 line-clamp-2">{{ props.item.name }}</div>
            <span class="span-btn  text-white rounded" :class="props.item.delivery_method === 'auto'?'bg-blue-600':'param-btn'">{{ props.item.delivery_method === 'auto' ? '自动发货' : '手动发货' }}</span>
          </div>
          <div class="flex items-center justify-between mt-auto pt-1 sm:pt-2 sm:border-t sm:border-gray-100">
            <div>
              <span class="text-red-500 text-base sm:text-xl font-bold mr-1">¥{{ props.item.price }}</span>
              <span class="text-xs sm:text-sm text-gray-400 line-through" v-if="props.item.original_price > 0">¥{{ props.item.original_price }}</span>
            </div>
            <span class="text-xs sm:text-sm flex items-center" :class="props.item.stock <= 0?'text-red-500':'text-blue-600'">
              <span class="dio mr-1" :class="props.item.stock <= 0?'bg-red-500':'bg-blue-500'"></span>{{ props.item.stock<=0?'售罄': `库存：${props.item.stock }`}}</span>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true,
    default: () => ({
      id: '',
      name: '',
      image: '',
      price: '',
      original_price: '',
      stock: '',
      delivery_method: ''
    })
  },
})

const emit = defineEmits(['showDetail'])
const showDetail = (item) => {
  emit('showDetail', item)
}
</script>

<style scoped lang="scss">
.product-item {
  cursor: pointer;
  :deep(.el-card){
    height:100%
  }
  :deep(.el-card__body) {
    padding: 0;
    height:100%;
  }
  width: 100%;
  height: 100%;
  .product-item-content{
    height:100%
  }
  .product-item-img {
    flex:none;
    padding: 6px;
    box-sizing: border-box;
    width: 6rem;
    height: 6rem;
    img {
      width: 100%;
      height: 100%;
    }
    :deep(.el-image) {
      position: relative;
      width: 100%;
      height:100%;
      padding-top: 100%;
      img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
      }
    }
  }
  .product-item-text {
    .gap-2 {
      gap: 0.5rem;
    }
    .span-btn{
      font-size: 10px;
      font-size: .75rem;
      line-height: 1rem;
      padding-left: .375rem;
      padding-right: .375rem;
      padding-top: .125rem;
      padding-bottom: .125rem;
    }
    .param-btn{
      background-color: var(--primary-color);
      background-image: linear-gradient(to right, #f97316,#ef4444)
    }
    
    .line-through {
        text-decoration-line: line-through;
    }
    .dio{
      display: inline-block;
      width: .375rem;
      height: .375rem;
      // background-color: var(--success-color);
      border-radius: 50%;
    }
   
  }
}
@media (min-width: 640px) {
  .product-item {
    .product-item-img {
      flex: 1 1 0%;
      width: 100%;
      height: 100%;
      
    }
    .sm\:p-4 {
      padding: 1rem;
    }
    .span-btn{
      font-size: .75rem;
      line-height: 1rem;
    }
    .sm\:text-xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    .sm\:text-sm {
        font-size: .875rem;
        line-height: 1.25rem;
    }
    // .sm\:border-gray-100 {
    //     --tw-border-opacity: 1;
    //     border-color: rgb(243 244 246 / var(--tw-border-opacity, 1));
    // }
  }
}
</style>
