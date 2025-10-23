<template>
  <div :style="`background-color: ${props.backgroundColor}; border-color:${props.borderColor}`"
    class=" border-l-4 px-3 sm:px-4 py-2 sm:py-[10.5px] mb-3.5 rounded-md animate-fadeIn"
  >
    <div class="flex items-center">
        <div class="flex-shrink-0" :style="`color:${props.borderColor}`">
            <i :class="icon" class="iconfont" style="font-size: 24px;"></i>
        </div>
      <div class="ml-3">
        <h2 class="text-sm sm:text-base font-semibold text-gray-800">
          {{title}}
        </h2>
        <p class="text-xs sm:text-sm text-gray-700">
          {{content}}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
    import {defineProps} from 'vue'
   
    const props = defineProps({
        backgroundColor: {
            type: String,
            default: '#eff6ff'
        },
        borderColor:{
            type:String,
            default:'#3b82f6'
        },
        icon:{
            type:String,
            default:'icon-check-circle'
        },
        title:{
            type:String,
            default:'支付成功'
        },
        content:{
            type:String,
            default:'您的订单已支付成功，卡密信息已生成'
        }
    })
</script>

