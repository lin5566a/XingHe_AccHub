<template>
    <div class="payment-type">
        <el-divider content-position="left">支付类型配置</el-divider>
        <div class="payment-list">
            <div class="payment-item" v-for="(item , index) in local_payment_methods" :key="index">
                <div class="payment-name">
                    <span class="mr10 name-box"> {{ item.payment_method_text }}</span>
                    <el-switch :width="48" v-model="item.statusBoolean" active-text="启用" inactive-text="禁用" @change="emitUpdate"></el-switch>
                </div>
                <div class="payment-text" :class="item.statusBoolean?'':'hidden-text'">
                    <div class="payment-code">
                        <el-input style="width:300px" :placeholder="`请输入${item.payment_method_text}产品编码`" v-model="item.product_code" size="small" @input="emitUpdate">
                            <template slot="prepend">产品编码</template>
                        </el-input>
                    </div>
                    <div class="payment-fee">
                        <div class="relative" style="width:200px">
                            <el-input-number class="prepend-input-number" style="width:200px" v-model="item.fee_rate" controls-position="right" :min="0" :max="100" :step="0.1" :precision = '2' size="small" @change="emitUpdate">
                            </el-input-number>
                            <span class="prepend absolute">费率</span>
                            <span class="unit absolute">%</span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script> 
    export default({
        name:'PaymentType',
        props:{
            payment_methods:{
                type: Array,
                default: function () {
                    return []
                }
            }
        },
        data() {
            return {
                local_payment_methods: []
            }
        },
        watch:{
            payment_methods:{
                handler(newVal){
                    // 浅拷贝每一项，避免直接修改 props
                    this.local_payment_methods = Array.isArray(newVal) ? newVal.map(it => ({...it})) : []
                },
                deep: true,
                immediate: true
            }
        },
        methods:{
            emitUpdate(){
                this.$emit('update:payment_methods', this.local_payment_methods)
            }
        }
    })
</script>
<style lang="scss">
.el-switch__label{
    position: absolute;
    display: none;
    z-index: 1;
    margin-right: 4px !important;
    margin-left: 4px !important;
    &.el-switch__label--left{
        right: 0;
    }
    &.el-switch__label--right {
        left: 0;
    }
    & *{
        font-size: 12px;
    }
    &.is-active{
        display: block;
        color:#fff;
    }
}
.prepend-input-number{
    .el-input-number.is-controls-right .el-input__inner{
        padding-left: 52px !important;
    }
}
    
</style>
<style lang="scss" scoped>

.payment-type{ 
    .payment-list{
        .payment-item{
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-start;
                align-items: center;
                height: 33px;
            .payment-name{
                display: flex;
                justify-content: flex-start;
                align-items: center;
                margin-right: 75px;
                .name-box{
                    width: 120px;
                    text-align: right;
                }
            }
            .payment-text{
                display: flex;
                justify-content: flex-start;
                align-items: center;
                flex: 1;
                // flex-wrap: wrap;
                font-size: var(--font-size);
                line-height: 32px;
                min-width: 0;
                position: relative;
                &.hidden-text{
                    display: none;
                }
                .payment-code{
                    margin-right: 10px;

                }
                .payment-fee{
                    .prepend{
                        left: 15px;
                        top: 2px;
                        background: #fff;
                        padding-right: 2px;
                        height: calc(100% - 4px);
                        display: flex;
                        align-items: center;
                        color: #a8abb2;
                    }
                    .unit{
                        right: 50px;
                        background: #fff;
                        height: calc(100% - 4px);
                        top: 2px;
                        display: flex;
                        align-items: center;
                        color: #a8abb2;
                        padding-left: 2px;
                    }
                }
            }
            
        }
    }
}
</style>