<template>
  <div class="space-y-6">
    <div class="mb-6">
      <h2 class="text-lg font-medium text-gray-900">我的信息</h2>
      <p class="text-sm text-gray-500">查看您的账户和余额信息</p>
    </div>
    <div class="my-6 bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <i class="text-gray-400 iconfont icon-user" font-size="16px"></i>
            <h2 class="text-lg font-semibold text-gray-900 mx-2">账号信息</h2>
          </div>
          <div class="bg-transparent bg-gray-50 p-2 sm:p-4 rounded-lg space-y-4">
            <!-- <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
              <span class="text-sm text-gray-500 mb-1 sm:mb-0">用户名</span>
              <span class="font-medium text-gray-900">{{ userInfo.nickname || '--' }}</span>
            </div> -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4">
              <span class="text-sm text-gray-500 mb-1 sm:mb-0">账户邮箱</span>
              <span class="font-medium text-gray-900 break-all">{{ userInfo.email || '--' }}</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4">
              <span class="text-sm text-gray-500 mb-1 sm:mb-0">会员等级</span>
              <span class="vip-lab px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-sm font-medium w-fit">{{ userInfo.member_level?userInfo.member_level.name:'--'}}</span>
            </div>
            <div class="flex flex-col mt-4" v-if="userInfo.member_level && userInfo.member_level.id != 5 &&  userInfo.member_level.id != 4">
              <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span class="">{{userInfo.member_level?(userInfo.member_level.id == 1?'普通用户':userInfo.member_level.name):''}}</span>
                <span class="">{{upgradeNum.level_name}}</span>
              </div>
              <div class="prose">
                <el-progress :percentage="upgradeNum.progress" :stroke-width="10" :show-text="false" color="#eab308" status="warning"></el-progress>               
              </div> 
              <div class="text-right  mt-1">
                  <span class="text-xs text-gray-500" v-if="userInfo.member_level && userInfo.member_level.id != 5  &&  userInfo.member_level.id != 4">还差{{(upgradeNum.upgrade_num).toFixed(2)}}元升级</span>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <i class="text-gray-400 iconfont icon-wallet" font-size="16px"></i>
            <h2 class="text-lg font-semibold text-gray-900 mx-2">余额信息</h2>
          </div>
           <div class="bg-transparent bg-gray-50 p-2 sm:p-4 rounded-lg space-y-4">
            <div class="flex flex-row justify-between items-center">
              <span class="text-sm text-gray-500 mb-1 mb-0">当前余额</span>
              <span class="font-medium text-2xl text-primary">{{userInfo.balance || '0.00'}} 元</span>
            </div>
            <div class="flex flex-row justify-between items-center mt-4">
              <span class="text-sm text-gray-500 mb-0">累计充值</span>
              <span class="font-medium text-gray-900">{{userInfo.total_recharge || '0.00'}} 元</span>
            </div>
            <div class = "mt-3">
              <el-button type="primary" class="bg-blue-600 md-button-height text-lgx w-full text-white px-4 py-2 rounded-md hover:bg-blue-700" @click="gotoPage">立即充值</el-button>
            </div>
           </div>
        </div>
      </div>
      
      <div class="mt-4 pt-3 border-t border-gray-100">
        <div class="bg-gray-50 rounded-md p-3">
          <div class="text-sm text-gray-700">
            <span class="text-primary font-medium"> 
              {{userInfo.member_level ? 
              ( userInfo.member_level.discount>0?`当前任意购买商品${Number(userInfo.member_level.discount)/10}折`:'无折扣')
              :'无折扣'}}
            </span>
            <span class="mx-2 text-gray-400">|</span>
            <span> {{userInfo.member_level?
              (userInfo.member_level.id == 5 ? userInfo.member_level.upgrade_amount: userInfo.member_level.upgrade_amount>0?`累计充值${userInfo.member_level.upgrade_amount}元享受${Number(userInfo.member_level.discount)/10}折优惠，优先客服支持`:'默认')
              :'默认' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted,computed } from 'vue';
import { userApi } from '@/api';
import { ElMessage } from 'element-plus';
import { useRouter } from 'vue-router';
import emitter from '@/utils/eventBus';
const tableData = ref([])
const userInfo = ref({
  nickname: '',
  email: '',
  created_at: ''
});
const router = useRouter();
// userInfo.value = JSON.parse(localStorage.getItem('userInfo'));
//计算属性计算距离下个等级还差多少
const upgradeNum = computed(()=>{
    let obj={upgrade_num:'',level_name:'',progress:1}
    if(userInfo.value.member_level &&  tableData.value.length > 0){
        tableData.value.forEach(item => {
            if(Number(item.id) - Number(userInfo.value.member_level.id) == 1){
                obj = {
                    upgrade_num: Number(item.upgrade_amount) - Number(userInfo.value.total_recharge) >0 ? Number(item.upgrade_amount) - Number(userInfo.value.total_recharge):0,                    
                    level_name: item.name,
                    progress: (Number(userInfo.value.total_recharge)/Number(item.upgrade_amount)*100),
                }
            }
        });
    }
    return obj;
})
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
// 获取用户信息
const getUserInfo = async () => {
  try {
    const res = await userApi.getUserInfo();
    if (res.code === 1) {
      userInfo.value = res.data;      
      localStorage.setItem('allUserInfo', JSON.stringify(res.data));      
      emitter.emit('loginStatusChange');
    } else {
      ElMessage.error(res.msg);
    }
  } catch (error) {
    console.error('获取用户信息失败:', error);
    // ElMessage.error('获取用户信息失败，请重试');
  }
};
const gotoPage = ()=>{
  router.push('/recharge');
}


onMounted(() => {
  getUserInfo();
  getMemberLevel();
});
</script>
