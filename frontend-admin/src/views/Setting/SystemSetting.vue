<template>
    <div class="operation-log-box">
       <mainBoxHeader titleName="系统设置" :noDescription="true">
           <template slot="oprBtn">
               <span></span>
           </template>
           <template slot="pageCon">
            <el-tabs v-model="systemData.activeName" @tab-click="handleClick">
                <el-tab-pane label="基本设置" name="base">
                    <baseSetting v-if="systemData.activeName == 'base'"></baseSetting>
                </el-tab-pane>
                <el-tab-pane label="客户设置" name="customer">
                    <customerSetting v-if="systemData.activeName == 'customer'"></customerSetting>
                </el-tab-pane>
                <el-tab-pane label="邮件设置" name="email">
                    <emailSetting v-if="systemData.activeName == 'email'"></emailSetting>
                </el-tab-pane>
                <el-tab-pane label="手续费设置" name="fee">
                    <feeSetting v-if="systemData.activeName == 'fee'"></feeSetting>
                </el-tab-pane>
                <el-tab-pane label="USDT比率设置" name="usdt">
                    <usdtSetting v-if="systemData.activeName == 'usdt'"></usdtSetting>
                </el-tab-pane>
                <el-tab-pane label="渠道设置" name="channel">
                    <channelSetting v-if="systemData.activeName == 'channel'"></channelSetting>
                </el-tab-pane>
            </el-tabs>
           </template>
       </mainBoxHeader>


       
   </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
import baseSetting from '@/components/system/baseSetting.vue'
import customerSetting from '@/components/system/customerSetting.vue'
import emailSetting from '@/components/system/emailSetting.vue'
import feeSetting from '@/components/system/feeSetting.vue'
import usdtSetting from '@/components/system/usdtSetting.vue'
import channelSetting from '@/components/system/channelSetting.vue'
export default {
   name:'systemSetting',
   components:{
       mainBoxHeader,
       baseSetting,
       customerSetting,
       emailSetting,
       feeSetting,
       usdtSetting,
       channelSetting
   },
   data(){
       return{  
            systemData:{
                activeName:'base',//base基本设置     customer客户设置     fee手续费设置    email邮件设置
            },
       }
   },
   methods:{
    handleClick(){

    }
   }
}
</script>
<style lang="scss">

</style>
<style lang="scss" scoped>
    
    
</style>