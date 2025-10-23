<template>
    <div class="payment-info-box">
        <div>
            <div class="info-title">
                配置商户信息，用于对接支付系统。请确保商户号和密钥的准确性。
            </div>
            <div class="info-form">
                <el-form ref="paymentInfoForm" :model="paymentInfo" label-width="120px" :rules="rules" size="small">
                    <el-form-item label="商户号" prop="merchant_id">
                        <el-input class="form-input" v-model="paymentInfo.merchant_id" placeholder="请输入商户号" size="small" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="商户密钥" prop="merchant_key">
                        <div class="flex align-center flex-wrap">
                            <el-input class="form-input" type="password" show-password v-model="paymentInfo.merchant_key" placeholder="请输入商户密钥" size="small" autocomplete="new-password"></el-input>
                            <span class="key-hint">商户密钥用于签名验证，请妥善保管</span>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <paymentType ref="paymentTypeRef" :payment_methods.sync="payment_methods"></paymentType>
    </div>
</template>
<script>

import paymentType from "@/components/payment/paymentType.vue"
export default {
    name: 'PaymentInfo',
    components:{
        paymentType,
    },
    props:{
        loading:{
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            paymentInfo: {
                merchant_id: '',
                merchant_key: '',
            },
            payment_methods:[],
            rules: {
                merchant_id: [
                    { required: true, message: '请输入商户号', trigger: 'blur'},
                    { min: 4, max: 30, message: '商户号长度应在4-30个字符之间', trigger: 'blur' }
                ],
                
                merchant_key: [
                    { required: true, message: '请输入商户密钥', trigger: 'blur' },
                    { min: 8, max: 64, message: '商户密钥长度应在8-64个字符之间', trigger: 'blur' }
                ]
            }
        }
    },
    created(){
        this.getChannelInfo()
    },
    methods:{
        saveConfig() {             
            this.$refs['paymentInfoForm'].validate((valid) => {
                if (!valid) {
                    this.$message.error('请检查表单填写是否正确')
                    return false;
                }

                const toNumberOrNull = function(v){
                    if (v === '' || v === null || v === undefined) return null
                    const n = Number(v)
                    return isNaN(n) ? null : n
                }
                const rates = (this.payment_methods || []).map(item => {
                    const weightNum = toNumberOrNull(item.weight)
                    const feeRateNum = toNumberOrNull(item.fee_rate)
                    const minAmountNum = toNumberOrNull(item.min_amount)
                    const maxAmountNum = toNumberOrNull(item.max_amount)
                    return {
                        payment_method: item.payment_method,
                        weight: weightNum === null ? 0 : weightNum,
                        fee_rate: feeRateNum === null ? 0 : feeRateNum,
                        min_amount: minAmountNum === null ? 0 : minAmountNum,
                        max_amount: maxAmountNum === null ? 0 : maxAmountNum,
                        status: item.statusBoolean ? 1 : 0,
                        product_code: item.product_code || ''
                    }
                })
                // const rates = ratesList.filter(item => item.status == 1)
                const data = {
                    channel_id: this.paymentInfo.id,
                    name: this.paymentInfo.name,
                    abbr: this.paymentInfo.abbr,
                    merchant_id: this.paymentInfo.merchant_id,
                    merchant_key: this.paymentInfo.merchant_key,
                    create_order_url: this.paymentInfo.create_order_url,
                    query_order_url: this.paymentInfo.query_order_url,
                    balance_query_url: this.paymentInfo. balance_query_url,
                    notify_url: this.paymentInfo.notify_url,
                    return_url: this.paymentInfo.return_url,
                    rates: rates
                }
                this.$emit('setLoading',true)
                // this.loading = true
                this.$api.editChannelWithRates(data).then(res => {                      
                    this.$emit('setLoading',false)                  
                    // this.loading = false
                    if (res.code == 1) {
                        this.$message.success(res.msg)
                        this.getChannelInfo()
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch((e) => {     
                    this.$emit('setLoading',false)                
                    // this.loading = false
                    this.$message.error('e')
                })
            });
        },
        resetForm() {
            this.$refs['paymentInfoForm'].resetFields();
            // 重新拉取服务器配置恢复页面
            this.getChannelInfo()
        },
        getChannelInfo(){
            let data={id:1}
            this.$api.getChannelInfo(data).then(res=>{
                if(res.code==1){
                    this.paymentInfo = res.data.channel
                    let payment_methods = res.data.payment_methods
                    this.payment_methods = []
                    for (let key in payment_methods) {
                        this.payment_methods.push({ payment_method: key, payment_method_text: payment_methods[key] })
                    }
                    if(this.payment_methods.length>0){
                        for(let i = 0; i < this.payment_methods.length; i++){
                            let item = this.payment_methods[i];
                            let state = false;
                            let rate={};
                            for(let j = 0; j < res.data.rates.length; j++){
                                let item2 = res.data.rates[j]
                                if(item.payment_method == item2.payment_method){
                                    state = true;
                                    rate = item2;
                                    break;
                                }
                            }
                            if(state){
                                let statusBoolean = rate.status == 1?true:false;
                                this.payment_methods[i] = {...item,...rate,statusBoolean:statusBoolean}
                            }else{
                                this.payment_methods[i]={
                                    ...item,
                                    create_time: null,
                                    fee_rate: "",
                                    max_amount: "",
                                    min_amount: "",
                                    product_code: null,
                                    status: "0",
                                    statusBoolean:false,
                                    status_text: "禁用",
                                    update_time: null,
                                    weight: "0",
                                }
                            }
                        }
                       
                    }
                }else{
                    this.$message.error(res.msg)
                }
            })
        }
    }
}
</script>
<style lang="scss" scoped>
.info-title {
    margin-bottom: 24px;
    color: #606266;
    font-size: 14px;
    line-height: 1.5;
}
.info-form{
    .form-input{
        width:350px;
    }
    .key-hint {
        margin-left: 10px;
        font-size: 12px;
        color: #909399;
    }
}
</style>