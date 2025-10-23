<template>
    <div class="profile-box space-y-6">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">个人资料</h2>
            <p class="text-sm text-gray-500">管理您的个人信息和账户安全</p>
        </div>
        <div class="mb-6">
            <el-tabs v-model="activeName" class="demo-tabs" @tab-click="handleClick">
                <el-tab-pane label="基本信息" name="baseInfo">
                    <BaseInfo :userInfo="userInfo" v-if="activeName === 'baseInfo'" @getInfo="getUserInfo"/>
                </el-tab-pane>
                <el-tab-pane label="修改密码" name="modifyPassword"><Password></Password></el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>
<script setup>
import { ref,onMounted } from 'vue';
import BaseInfo from '@/components/personal/baseInfo.vue';
import Password from '@/components/personal/password.vue';
import { userApi } from '@/api';
import { ElMessage } from 'element-plus';
import emitter from '@/utils/eventBus';
const activeName = ref('baseInfo');
const userInfo = ref({});
const handleClick = (tab, event) => {
    // console.log(tab, event);
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

onMounted(() => {
  getUserInfo();
});
</script>
<style scoped lang="scss">
.demo-tabs{
    :deep(.el-tabs__header){
        margin-bottom: 0;
    }
}
</style>


