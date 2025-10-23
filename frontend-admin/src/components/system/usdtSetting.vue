<template>
    <div class="usd-setting-box">
        <el-form
            ref="usdFormRef"
            :model="usdtForm"
            :rules="usdtRules"
            label-width="120px"
            class="settings-form"
            size="small"
          >
            <!-- USDT手续费设置 -->
            <el-form-item label="USDT兑人民币比率" prop="usdt_rate">
              <el-input-number
                v-model="usdtForm.usdt_rate"
                :min="0"
                :max="100"
                :precision="2"
                :step="0.01"
                placeholder="请输入USDT兑人民币比率"
                size="small"
                class="form-input"
              />
              <span class="form-unit ml10 gray-text-color">元</span>
              <div class="form-tip">设置1USDT等于多少人民币，用于系统内货币转换计算</div>
            </el-form-item>

            <el-form-item>
              <el-button class="f14" type="primary" @click="saveUsdSettings" size="small">保存设置</el-button>
              <el-button class="f14" @click="resetUsdForm" size="small">重置</el-button>
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
            usdtForm:{
                usdt_rate: 7.25
            },
            oldUsdtForm:{
                usdt_rate: 7.25
            },
            usdtRules:{
                usdt_rate: [
                    { required: true, message: '请输入USDT兑人民币比率', trigger: 'blur' }
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
                    this.oldUsdtForm = {
                        usdt_rate: parseFloat(res.data.usdt_rate),
                    }
                    this.usdtForm = {...this.oldUsdtForm}
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('获取支付设置失败')
            })
        },
        saveUsdSettings(){
            this.$refs.usdFormRef.validate((valid) => {
                if (valid) {
                    this.loading = true
                    const data = {
                        rate: this.usdtForm.usdt_rate.toString()
                    }
                    this.$api.usdtRateUpdate(data).then(res => {
                        this.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.oldUsdtForm = {...this.usdtForm}
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
        resetUsdForm(){
            this.usdtForm = {...this.oldUsdtForm}
            this.$refs.usdFormRef.resetFields();
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
    .usd-setting-box{
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
