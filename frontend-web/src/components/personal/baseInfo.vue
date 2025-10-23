<template>
  <div class="base-info-box mt-6">
    <el-form
      label-position="top"
      label-width="auto"
      :model="userInfo"
      style="max-width: 28rem"
      ref="formRef"
    >
      <!-- <el-form-item label="昵称" label-position="top" >
        <div class="w-full">
            <el-input class="form-input" v-model="userInfo.nickname" placeholder="请输入您的昵称"/>
            <p class="mt-1 text-xs text-gray-500">昵称长度2-20个字符，支持中英文、数字和下划线</p>
        </div>
        
      </el-form-item> -->
      <el-form-item label="邮箱" label-position="top">
        <div class="w-full">
            <el-input
            class="form-input readonly-input"
            v-model="userInfo.email"
            :readonly="true"
            />
            <p class="mt-1 text-xs text-gray-500">邮箱不可修改</p>
        </div>
      </el-form-item>
    </el-form>
    <!-- <div class="mt-6">
      <el-button
        type="primary"
        class="px-4 py-2 rounded-md content-box text-base bg-from-blue-600 hover:bg-from-blue-700"
        :class="baseInfoData.loading? 'bg-disabled-70' :''"
        @click="saveInfo"
        :loading="baseInfoData.loading"
        :disabled="baseInfoData.loading"
        >保存修改</el-button
      >
    </div> -->
    <!-- <div
      v-if="info.message"
      :class="
        (info.type == 'success' ? ' bg-green-50 text-green-700': ' bg-red-50 text-red-700') "
      class="mt-6 p-3 rounded-md"
    >
      {{ info.message }}
    </div> -->
  </div>
</template>
<script setup>
import { defineProps,ref,defineEmits} from 'vue';
import { userApi } from '@/api';
import { ElMessage } from 'element-plus';
const props = defineProps({
    userInfo: {
        type: Object,
        default: () => ({}),
    },
});
const formRef = ref();

const emit = defineEmits(['getInfo']);
const info = ref({
    type:'',
    message:'',
});
const baseInfoData = ref({
    loading:false,
})
// const validateNickname = (rule, value, callback) => {
//     const reg = /^[\u4e00-\u9fa5a-zA-Z0-9_]{2,20}$/;
//     if (!reg.test(value)) {
//         callback(new Error('昵称仅支持中文、英文、数字、下划线，长度2-20字符'));
//     } else {
//         callback();
//     }
// }
// const rules = ref({
//     nickname: [
//         { required: true, message: '请输入昵称', trigger: 'blur' },
//         { validator: validateNickname, trigger: 'blur' },
//     ],
// });
const saveInfo = async () => {
    if(!props.userInfo.nickname){
        ElMessage.warning('请输入昵称');
        return;
    }
    const reg = /^[\u4e00-\u9fa5a-zA-Z0-9_]{2,20}$/;
    if (!reg.test(props.userInfo.nickname)) {
        info.value.type = 'error';
        info.value.message = '昵称仅支持中文、英文、数字、下划线，长度2-20字符';  
        setTimeout(()=>{
            info.value.type = '';
            info.value.message = '';  
        },3000)
        return;
    }
    info.value.type = '';
    info.value.message = '';  

    let data = {
            nickname:props.userInfo.nickname,
        }
    try{
        baseInfoData.value.loading = true;
        const res = await userApi.updateNickname(data);
        baseInfoData.value.loading = false;
        if(res.code === 1){
            
            // console.log('res success',res.code);
            info.value.type = 'success';
            info.value.message = res.msg;        
            emit('getInfo');          
            let userInfo = localStorage.getItem('userInfo');
            userInfo = JSON.parse(userInfo);
            userInfo.nickname = props.userInfo.nickname;
            localStorage.setItem('userInfo',JSON.stringify(userInfo));
        }else{
            info.value.type = 'error';
            info.value.message = res.msg;
            // console.log('reserror',res.code,info.value.type );
        }
        setTimeout(()=>{
            info.value.type = '';
            info.value.message = '';    
        },3000)
    }catch(err){
        baseInfoData.value.loading = false;
        info.value.type = 'error';
        info.value.message = err.message;
    }

}
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
