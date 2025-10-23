<template>
    <div class="error-tip-box bg-red-50 text-red-600 p-3 rounded-lg text-sm flex items-start mt-4 border border-red-200">
        <i class="icon-zhuyi iconfont text-sm mr-2 mt-0.5"></i>
        <div>
            <p class="font-medium">支付超时</p>
            <p class="mt-1">订单支付已超时，请返回重新发起充值</p>
            <el-button link class="text-btn mt-2 font-medium text-sm" @click="goBack">返回充值页面</el-button>
        </div>
    </div>
</template>
<script setup>
import { defineEmits } from 'vue';
const emits = defineEmits(['goBack'])
const goBack = () => {
    emits('goBack')
}
</script>
<style lang="scss">
.error-tip-box{
    .text-btn{
        color:#2563eb;
        &:hover{
            color:#1d4ed8 !important;
        }
    }

}
</style>