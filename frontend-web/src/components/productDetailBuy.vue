<template>
  <div class="bg-white p-3-5 rounded-lg border border-gray-200 shadow-sm">
        <div class="space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center">
                <span class="text-gray-700 w-24 font-medium mb-2 sm:mb-0">数量</span>
                <div class="flex-1 flex flex-col sm:flex-row sm:items-center">
                    <div class="w-full flex-1">
                        <input
                            type="number"
                            min="1"
                            :max="maxQuantity"
                            class="product-input w-full px-4 border border-gray-300 rounded-lg flex-1"
                            :value="props.productDetail.buyProduct.quantity"
                            @input="handleQuantityInput"
                        />
                        <div class="mt-1 text-xs text-orange-500" v-if="props.productDetail.enable_purchase_limit == '1'">
                            单笔最多购买
                            {{props.productDetail.purchase_limit_type == '1' ? props.productDetail.purchase_limit_value : Math.floor(props.productDetail.stock * props.productDetail.purchase_limit_value / 100)>0?Math.floor(props.productDetail.stock * props.productDetail.purchase_limit_value / 100):1}} 
                            件，有大量需求，
                            <span class="limit-tip text-blue-600 underline cursor-pointer" @click="customer">请联系客服</span>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-0 sm:ml-4 relative">
                        <span class="text-gray-700 flex items-center">
                        总价:
                        <span class="text-red-500 font-medium ml-1">¥{{ (props.productDetail.buyProduct?.quantity * props.productDetail.price).toFixed(2) }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center mt-5">
                <span class="text-gray-700 w-24 font-medium mb-2 sm:mb-0">接收邮箱</span>
                <div class="flex-1 flex flex-col sm:flex-row sm:items-center">
                <input :readonly="token"
                    type="email"
                    class="product-input w-full px-4 border border-gray-300 rounded-lg"
                    placeholder="填写邮箱用以接收商品信息"
                    v-model="props.productDetail.buyProduct.mail"
                />
                </div>
            </div>
        
        
            <div class="flex flex-col sm:flex-row sm:items-center mt-5">
                <div v-if="props.productDetail.stock>0" type="primary"
                class="w-full buy-btn can-buy w-full rounded-lg flex items-center justify-center cursor-pointer"                   
                @click="buyProduct"
                :class="{ 'opacity-50 cursor-not-allowed': loading }"                   
                :disabled="loading"
                >
                    <i class="iconfont icon-tag text-white mr-2" style="font-size: 18px;"></i>
                    立即购买
                </div>
                <div v-else type="primary"
                class="w-full buy-btn w-full rounded-lg flex items-center justify-center opacity-50 cursor-not-allowed"
                >
                    <i class="iconfont icon-tag text-white mr-2" style="font-size: 18px;"></i>
                    立即购买
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { defineProps, defineEmits, computed } from 'vue';
    const emit = defineEmits(['buyProduct','customer', 'update:quantity'])
    const props = defineProps({
        productDetail: {
            type: Object,
            required: true
        },
        loading: {
            type: Boolean,
            required: true
        },
        token: {
            type: String,
            required: true
        },
    })

    // 计算最大可购买数量
    const maxQuantity = computed(() => {
        if (props.productDetail.enable_purchase_limit != '1') {
            return props.productDetail.stock;
        }
        
        if (props.productDetail.purchase_limit_type == '1') {
            const limit = Math.min(Number(props.productDetail.purchase_limit_value), props.productDetail.stock);
            // console.log('最大可购买数量单笔限制：', limit);
            return limit;
        } else {
            // 计算百分比限制的件数
            const limitQuantity = Math.floor(props.productDetail.stock * Number(props.productDetail.purchase_limit_value) / 100);
            // 确保至少可以购买一件
            const finalLimit = Math.max(1, Math.min(limitQuantity, props.productDetail.stock));
            // console.log('最大可购买数量百分比：', finalLimit);
            return finalLimit;
        }
    });

    // 处理数量输入
    const handleQuantityInput = (event) => {
        const value = Number(event.target.value);
        if (value > maxQuantity.value) {
            event.target.value = maxQuantity.value;
            props.productDetail.buyProduct.quantity = maxQuantity.value;
        } else {
            props.productDetail.buyProduct.quantity = value;
        }
    };

    const buyProduct = () => {
        emit('buyProduct')
    }
    const customer = () =>{
        emit('customer')
    }
</script>
<style scoped lang="scss">
.limit-tip{
    &:hover{
        color:#00796b
    }
}
.product-input{
    padding: 10px 1rem;
    &:focus {
        outline: solid 2px var(--primary-color);
    }
}

.buy-btn {
    color: #fff;
    padding: 10px 1rem;
    background-image: linear-gradient(to right, #2563eb,#4f46e5);
    &.can-buy{
        &:hover {
            background-image: linear-gradient(to right,#1d4ed8,#4338ca );
        }
    }
    
  }
</style>