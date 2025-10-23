<template>
    <div class="fee-setting-box">
        <el-form
            ref="feeFormRef"
            :model="feeForm"
            :rules="feeRules"
            label-width="120px"
            class="settings-form"
            size="small"
          >
            <!-- USDT手续费设置 -->
            <el-form-item label="USDT手续费" prop="usdt_fee">
              <el-input-number
                v-model="feeForm.usdt_fee"
                :min="0"
                :max="100"
                :precision="2"
                :step="0.1"
                placeholder="请输入USDT手续费率"
                size="small"
                class="form-input"
              />
              <span class="form-tip">%</span>
              <div class="form-tip">设置USDT支付的手续费率</div>
            </el-form-item>

            <!-- 微信手续费设置 -->
            <el-form-item label="微信手续费" prop="wechat_fee">
              <el-input-number
                v-model="feeForm.wechat_fee"
                :min="0"
                :max="100"
                :precision="2"
                :step="0.1"
                placeholder="请输入微信手续费率"
                size="small"
                class="form-input"
              />
              <span class="form-tip">%</span>
              <div class="form-tip">设置微信支付的手续费率</div>
            </el-form-item>

            <!-- 支付宝手续费设置 -->
            <el-form-item label="支付宝手续费" prop="alipay_fee">
              <el-input-number
                v-model="feeForm.alipay_fee"
                :min="0"
                :max="100"
                :precision="2"
                :step="0.1"
                placeholder="请输入支付宝手续费率"
                size="small"
                class="form-input"
              />
              <span class="form-tip">%</span>
              <div class="form-tip">设置支付宝支付的手续费率</div>
            </el-form-item>

            <el-form-item>
              <el-button class="f14" type="primary" @click="saveFeeSettings" size="small">保存设置</el-button>
              <el-button class="f14" @click="resetFeeForm" size="small">重置</el-button>
            </el-form-item>
          </el-form>
    </div>
</template>
<script>
export default {
    name:"feeSetting",
    data(){
        return{
            loading: false,
            feeForm:{
                usdt_fee: 0.60,
                wechat_fee: 0.60,
                alipay_fee: 0.60
            },
            oldFeeForm:{
                usdt_fee: 0.60,
                wechat_fee: 0.60,
                alipay_fee: 0.60
            },
            feeRules:{
                usdt_fee: [
                    { required: true, message: '请输入USDT手续费率', trigger: 'blur' }
                ],
                wechat_fee: [
                    { required: true, message: '请输入微信手续费率', trigger: 'blur' }
                ],
                alipay_fee: [
                    { required: true, message: '请输入支付宝手续费率', trigger: 'blur' }
                ]
            }
        }
    },
    created(){
        this.getPaymentInfo()
    },
    methods:{
        //获取支付设置信息
        getPaymentInfo(){
            this.loading = true
            this.$api.paymentInfo().then(res => {
                this.loading = false
                if(res.code === 1){
                    this.oldFeeForm = {
                        usdt_fee: parseFloat(res.data.usdt_fee),
                        wechat_fee: parseFloat(res.data.wechat_fee),
                        alipay_fee: parseFloat(res.data.alipay_fee),
                    }
                    this.feeForm = {...this.oldFeeForm}
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('获取支付设置失败')
            })
        },
        saveFeeSettings(){
            this.$refs.feeFormRef.validate((valid) => {
                if (valid) {
                    this.loading = true
                    const data = {
                        usdt_fee: this.feeForm.usdt_fee.toString(),
                        wechat_fee: this.feeForm.wechat_fee.toString(),
                        alipay_fee: this.feeForm.alipay_fee.toString()
                    }
                    this.$api.paymentUpdate(data).then(res => {
                        this.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.oldFeeForm = {...this.feeForm}
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.loading = false
                        this.$message.error('保存失败')
                    })
                }
            });   
        },
        resetFeeForm(){
            this.feeForm = {...this.oldFeeForm}
            this.$refs.feeFormRef.resetFields();
        }
    }
}
</script>
<style lang="scss">
.settings-form {
    .el-form-item__content{
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }
}
    
</style>
<style lang="scss" scoped>
    .fee-setting-box{
        .settings-form{
            .form-input{
                width:150px;
            }
            .form-tip {
                font-size: 12px;
                color: #909399;
                line-height: 1.5;
                margin-top: 5px;
            }
        }
    }
</style>
