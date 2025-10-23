<template>
    <div class="payment-set-box">
        <mainBoxHeader titleName="支付系统配置" titleClass="payment-title" :noDescription="true" :hasHeader = "true">
            <template slot="oprBtn">
                <span></span>
            </template>
            <template slot="pageCon">
                <div class="content">
                    <paymentInfo ref="paymentInfoRef" :loading = loading @setLoading="setLoading"></paymentInfo>
                   
                    <div class="btn-box">
                        <el-button type="primary" size="small" @click="saveConfig" :disabled="loading">保存配置</el-button>
                        <el-button size="small" @click="resetForm" :disabled="loading">重置</el-button>
                    </div>
                    <UserGuid></UserGuid>
                    <TechnicalSupport></TechnicalSupport>
                </div>
            </template>
        </mainBoxHeader>
    </div>
</template>
<script>
import mainBoxHeader from "@/components/mainBoxHeader.vue"
// import paymentTable from "@/components/payment/paymentTable.vue";
import paymentInfo from "@/components/payment/paymentInfo.vue"
import UserGuid from "@/components/payment/userGuid.vue"
import TechnicalSupport from "@/components/payment/technicalSupport.vue"
export default {
    components:{
        mainBoxHeader,
        paymentInfo,
        UserGuid,
        TechnicalSupport
    },
    data(){
        return{
            loading:false
        }
    },
    computed:{
    },
    created(){
    },
    methods:{
        saveConfig(){
            // 确保 ref 存在再调用方法
            if (this.$refs.paymentInfoRef) {
                this.$refs.paymentInfoRef.saveConfig()
            }
        },
        resetForm(){
            // 确保 ref 存在再调用方法
            if (this.$refs.paymentInfoRef) {
                this.$refs.paymentInfoRef.resetForm()
            }
        },
        setLoading(loading){
            this.loading = loading;

        }
    }       
}
</script>
<style lang="scss">
</style>
<style lang="scss" scoped>
.payment-set-box{
   max-width: 840px;
   margin: 0 auto;
   .btn-box{
     margin-left: 130px;
   }
}
</style>