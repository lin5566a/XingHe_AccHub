<template>
    <NoLogin v-if="!token"></NoLogin>
    <div v-else class="recharge-box">
        <div class="max-w-4xl mx-auto py-2 sm:py-4 px-3.5 sm:px-4">
            <div class="recharge-title flex items-center mb-4">
                <div class="title-icon-box bg-from-primary-20 w-12 h-12 rounded-full flex items-center justify-center">
                    <i class="iconfont icon-wallet text-primary"></i>
                </div>
                <div class="title-text ml-4">
                    <div class="text-xl font-bold text-gray-900">账户充值</div>
                    <div class="text-sm text-gray-500">为账户余额充值</div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="iconfont icon-wallet text-primary mr-3 text-xl"></i>
                    <div>
                        <div class="text-sm text-gray-500">当前余额</div>
                        <div class="font-medium text-xl text-primary">{{userInfo.balance}} 元</div>
                    </div>
                </div>
                <div class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-sm" v-if="userInfo.member_level && userInfo.member_level.id != 5  &&  userInfo.member_level.id != 4">还差{{(upgradeNum.upgrade_num).toFixed(2)}}元升级{{upgradeNum.level_name}}</div>
            </div>
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="flex items-center space-x-2 mb-3">
                    <i class="iconfont icon-money-bill-wave text-gray-400 text-sm"></i>
                    <span class="ml-2 text-lg font-semibold text-gray-900">充值金额</span>
                </div>
                <div class = "grid grid-cols-3 gap-3 mb-4">
                    <div @click="chooseAmount(item)" class="amount-item cursor-pointer rounded-lg  bg-gray-50 text-gray-700" :class="{active: amount.choose == item}" v-for="(item,index) in amountArr" :key="index">{{item}}元</div>

                </div>
                <div class="mb-4">
                    <div class="flex items-center">
                        <div class="relative flex-1 flex items-center">
                            <el-input placeholder="自定义充值金额" class="custom text-lgx w-full rounded-lg " type="number" v-model="amount.custom" @focus="customFocus()" @input="handleCustomInput"></el-input>
                            <span class="ml-2 text-gray-700">元</span>
                        </div>
                    </div>
                </div>
                <div>
                    <el-button type="primary" class="md-button-height bg-from-blue-600 rounded-md w-full text-base" @click="createRechargeOrder"><i class="mr-1 iconfont icon-wallet text-base text-white"></i>立即充值</el-button>
                </div>
            </div>
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="mb-4 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <i class="iconfont icon-user text-gray-400 text-base"></i>
                        <span class="text-lg font-semibold text-gray-900 ml-1">账号信息</span>
                    </div>
                    <div class="px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-sm font-medium">
                        {{userInfo.member_level?userInfo.member_level.name:'--'}}
                    </div>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg space-y-2 mb-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">账户昵称</span>
                        <span class="font-medium text-gray-900">{{userInfo.nickname}}</span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-500">账户邮箱</span>
                        <span class="font-medium text-gray-900 break-all">{{userInfo.email}}</span>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-500">累计充值</span>
                        <span class="font-medium text-gray-900">{{ userInfo.total_recharge }} 元</span>
                    </div>
                </div>
                <div v-if="userInfo.member_level && userInfo.member_level.id != 5  &&  userInfo.member_level.id != 4">
                    <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                        <span>{{userInfo.member_level?(userInfo.member_level.id == 1?'普通用户':userInfo.member_level.name):''}}</span>
                        <span>{{upgradeNum.level_name}}</span>
                    </div>
                    <div> 
                        <el-progress :text-inside="true" :stroke-width="10" :percentage="upgradeNum.progress" status="warning"><span></span> </el-progress>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="bg-gray-80 rounded-md p-3">
                        <div class="text-sm text-gray-700">
                            <span class="text-primary font-medium">
                                {{userInfo.member_level ? 
                                (userInfo.member_level.discount>0?`当前任意购买商品${Number(userInfo.member_level.discount)/10}折`:'无折扣')
                                :'无折扣'}}
                            </span>
                            <span class="mx-2 text-gray-400">|</span>
                            <span class="">
                                {{userInfo.member_level?
                                (userInfo.member_level.id == 5 ? userInfo.member_level.upgrade_amount: userInfo.member_level.upgrade_amount>0?`累计充值${userInfo.member_level.upgrade_amount}元享受${Number(userInfo.member_level.discount)/10}折优惠，优先客服支持`:'默认')
                                :'默认' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100">
                <div class="mb-4 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <i class="iconfont icon-crown text-gray-400 text-base"></i>
                        <span class="text-lg font-semibold text-gray-900 ml-1">会员等级说明</span>
                    </div>
                </div>
                <div class="member-table-box">
                    <el-table :data="tableData" style="width: 100%"
                        v-loading="loading"
                        element-loading-text="加载中..."
                        element-loading-background="rgba(255, 255, 255, 0.8)"
                        size="large"
                        header-cell-class-name="member-table-header"
                        row-class-name = "member-table-row"
                        cell-class-name="member-table-cell"
                        empty-text="暂无数据"
                    >

                        <el-table-column prop="name" label="会员等级">
                            <template #default="scope">
                                <span>{{scope.row.id == 1 ? '普通用户':scope.row.name}}</span>
                            </template>
                        </el-table-column>

                        <el-table-column prop="discount" label="充值折扣">   
                            <template #default="scope">
                                <span>{{scope.row.id == 5 ? scope.row.discount :( scope.row.discount == 0 ? '无折扣':Number(scope.row.discount)/10 + '折')}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="upgrade_amount" label="升级条件" width="210px">   
                            <template #default="scope">
                                <span>{{scope.row.id == 5 ? scope.row.upgrade_amount :( scope.row.upgrade_amount == 0 ? '默认': '累计充值 '+scope.row.upgrade_amount+' 元')}}</span>
                            </template>
                        </el-table-column>                     
                        <el-table-column prop="description" label="会员介绍" width="370px" />   

                    </el-table>
                </div>
                
            </div>
        </div>        
    </div>
</template>
<script setup>
import { ref, onMounted,computed } from 'vue';
import {userApi,orderApi} from '@/api'
import { useRoute,useRouter } from 'vue-router';
import { ElMessage } from 'element-plus'
import emitter from '@/utils/eventBus';
import NoLogin from "@/components/noLogin.vue";
const router = useRouter()
const token = ref(localStorage.getItem('token'));
const amountArr = ref([50,100,200,300,500,1000])
const amount = ref({
    choose:null,
    custom:null
})
const tableData = ref([])
const loading = false
const userInfo = ref({})
const chooseAmount = (item) => {
    amount.value.choose = item
    amount.value.custom = null
}
const customFocus = ()=>{
    amount.value.choose = null
}
const handleCustomInput = (value) => {
    // 只允许输入整数，移除所有非数字字符
    amount.value.custom = value.replace(/[^0-9]/g, '');
}
//获取会员等级说明
const getMemberLevel = ()=>{
    userApi.getMemberLevel().then(res=>{
        if(res.code == 1){            
            tableData.value = res.data
        }else{
            ElMessage.error(res.msg)
        }
    }).catch(e=>{
        // console.log(e)
    })
}
const getUserInfo = ()=>{
    userApi.getUserInfo().then(res=>{
        if(res.code == 1){
            userInfo.value = res.data
            localStorage.setItem('allUserInfo', JSON.stringify(res.data));
            emitter.emit('loginStatusChange');
        }else{
            ElMessage.error(res.msg)
        }
        
    }).catch(err=>{
        // console.log(err)
    })
}
const createRechargeOrder =()=>{
    // console.log(amount.value,'====amount.value.choose ' )
    let data = {
        amount:amount.value.choose ? amount.value.choose : Number(amount.value.custom)
    }
    orderApi.createRechargeOrder(data).then(res=>{
        if(res.code == 1){            
            // router.push('/rechargePay');
            router.push({
                path: '/rechargePay',
                query: {
                    order_no:res.data.order_no,
                },
            });

            ElMessage.success(res.msg)
        }else{
            ElMessage.error(res.msg)
        }
    }).catch(err=>{
        // console.log(err)
    })
}
const upgradeNum = computed(()=>{
    let obj={upgrade_num:'',level_name:'',progress:1}
    if(userInfo.value.member_level &&  tableData.value.length > 0){
        tableData.value.forEach(item => {
            if(Number(item.id) - Number(userInfo.value.member_level.id) == 1){
                obj = {
                    // upgrade_num: Number(item.upgrade_amount) - Number(userInfo.value.member_level.upgrade_amount),
                    upgrade_num: Number(item.upgrade_amount) - Number(userInfo.value.total_recharge) >0 ? Number(item.upgrade_amount) - Number(userInfo.value.total_recharge):0,                    
                    level_name: item.name,
                    // progress: (Number(userInfo.value.member_level.upgrade_amount)/Number(item.upgrade_amount)*100)
                    progress: (Number(userInfo.value.total_recharge)/Number(item.upgrade_amount)*100)
                }
            }
        });
    }
    return obj;
})


onMounted(() => {
    getUserInfo();
    getMemberLevel();
})
</script>
<style lang="scss" scoped>
.recharge-box{
    .amount-item{
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        &:hover{
            background-color: #f3f4f6;
        }
        &.active{
            background-color: var(--primary-color);
            color: white;
            box-shadow:  0 4px 6px -1px rgba(0, 0, 0, .1);

        }
    }
    .custom{
        height: 42px;
    }
   
}
.title-icon-box{
    // background-image: linear-gradient(to bottom right,rgba(0, 150, 136, .2),rgba(0, 150, 136, .1));
}
</style>
<style lang="scss"> 
    .member-table-row{
        &:last-child{
            .member-table-cell{
                border-bottom: none !important;
            }
        }
    }
    .member-table-box{
        .el-table__inner-wrapper:before{
            height:0;
        }
    }
    .member-table-header{
        background: #f9fafb !important;
        color:#4b5563 !important;
        font-weight: 500 !important;
        border-bottom: none !important;
    }
</style>