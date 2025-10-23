<template>
    <div class="base-info-box mt-6">
    <el-form
      label-position="top"
      label-width="auto"
      :model="userInfo"
      style="max-width: 28rem"
      ref="formRef"
      :rules="rules"
      :hide-required-asterisk = "true"
    >
      <el-form-item label="当前密码" prop="oldPassword">
        <div class="w-full">
            <el-input 
              class="form-input" 
              type="password" 
              v-model="userInfo.oldPassword" 
              placeholder="请输入当前密码"
            />
        </div>
      </el-form-item>
      <el-form-item label="新密码" prop="newPassword">
        <div class="w-full">
            <el-input 
              class="form-input" 
              type="password" 
              v-model="userInfo.newPassword" 
              placeholder="请输入新密码(至少6个字符)"
            />
        </div>        
      </el-form-item>
      <el-form-item label="确认密码" prop="confirmPassword">
        <div class="w-full">
            <el-input 
              class="form-input" 
              type="password" 
              v-model="userInfo.confirmPassword" 
              placeholder="请再次输入新密码"
            />
        </div>
      </el-form-item>
    </el-form>
    <div class="mt-6">
      <el-button
        type="primary"
        class="px-4 py-2 rounded-md content-box text-base bg-from-blue-600 hover:bg-from-blue-700"
        :class="loading?'bg-disabled-70':''"
        @click="handleSubmit"
        :loading="loading"
        :disabled="loading"
        >{{loading?'更新中':'更新密码'}}</el-button
      >
    </div>
    <div
      v-if="info.message"
      :class="info.type === 'success' ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-700'"
      class="mt-6 p-3 rounded-md"
    >
      {{ info.message }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { userApi } from '@/api';
import emitter from '@/utils/eventBus';
import md5 from 'md5';
import Cookies from 'js-cookie';

const router = useRouter();
const formRef = ref(null);
const loading = ref(false);

const userInfo = ref({
  oldPassword: '',
  newPassword: '',
  confirmPassword: '',
});

// 验证密码格式
const validatePassword = (rule, value, callback) => {
  if (!value) {
    callback(new Error('请输入密码'));
  } else if (value.length < 6) {
    callback(new Error('密码长度不能少于6个字符'));
  } else if (!/^(?=.*[a-zA-Z])(?=.*\d).+$/.test(value)) {
    callback(new Error('密码必须包含英文字母和数字'));
  } else {
    callback();
  }
};

// 验证确认密码
const validateConfirmPassword = (rule, value, callback) => {
  if (!value) {
    callback(new Error('请再次输入密码'));
  } else if (value !== userInfo.value.newPassword) {
    callback(new Error('两次输入密码不一致'));
  } else {
    callback();
  }
};

const rules = {
  oldPassword: [
    { required: true, message: '请输入当前密码', trigger: 'blur' },
    { validator: validatePassword, trigger: 'blur' }
  ],
  newPassword: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { validator: validatePassword, trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, message: '请再次输入新密码', trigger: 'blur' },
    { validator: validateConfirmPassword, trigger: 'blur' }
  ]
};

const info = ref({
  message: '',
  type: '',
});

const handleSubmit = async () => {
  if (!formRef.value) return;  
  await formRef.value.validate();
  try {
    
    loading.value = true;

    const res = await userApi.updatePassword({
      old_password: md5(userInfo.value.oldPassword),
      new_password: md5(userInfo.value.newPassword),
      confirm_password: md5(userInfo.value.confirmPassword)
    });

    if (res.code === 1) {
      info.value = {
        message: res.msg,
        type: 'success'
      };
      // 清除登录信息
      localStorage.removeItem('token');
      localStorage.removeItem('userInfo');
      Cookies.remove('font-password');
      emitter.emit('loginStatusChange');
      // 延迟跳转
      setTimeout(() => {
        router.push('/login');        
      }, 1500);
    } else {
      info.value = {
        message: res.msg,
        type: 'error'
      };
    }
  } catch (error) {
    console.error('密码修改失败:', error);
    info.value = {
      message: '密码修改失败，请重试',
      type: 'error'
    };
  } finally {
    loading.value = false;
    setTimeout(() => {
      info.value = {
        message: '',
        type: ''
      };
    }, 3000);
  }
};
</script>

<style scoped lang="scss">
.base-info-box {
  padding-left: 2px;
  :deep(.el-form-item__label) {
    font-size: 0.875rem;
    color: #374151;
    font-weight: 500;
  }
}

.form-input {
  width: 100%;
  max-width: 28rem;
  height: 42px;
  font-size: 1rem;
  :deep(.el-input__inner) {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
  }
  :deep(.el-input__inner) {
    color: #1f2937;
  }
  &.readonly-input{
    :deep(.el-input__wrapper){
      background-color: #f9fafb;
    }
  }
}
</style>