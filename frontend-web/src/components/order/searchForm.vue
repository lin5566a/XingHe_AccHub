<template>
  <div
    class="search-form bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-200 lg:h-full"
  >
    <div class="search-tips bg-blue-50 flex items-center rounded-md py-2 px-3 mb-4">
      <i class="iconfont icon-info-circle primary-color" style="font-size: 20px;"></i>
      <span class="ml-3 text-xs sm:text-sm text-gray-700"
        >欢迎使用订单查询系统，您可以通过邮箱进行查询。</span
      >
    </div>
    <div v-if="errorMsg" class="error-tip-box text-red-700 bg-red-100 flex items-center rounded-md p-4 mb-4">   
      <span class="text-xs sm:text-sm text-red-500">{{ errorMsg }}</span>
    </div>
    <el-form class="search-form-class" label-position="top" label-width="auto" :model="searchForm" ref="formRef">     
      <el-form-item  label="邮箱地址" prop="email">
        <div class="flex items-center space-x-2 w-full">
          <el-input
            class="search-input text-lgx flex1"
            v-model="searchForm.email"
            type="email"
            placeholder="请输入您下单时使用的邮箱"
            :readonly="emailCodeLoading"
          >
            <template #prefix>
              <i class="iconfont icon-at text-gray-400" style="font-size: 16px;"></i>
            </template>
          </el-input>
          <div class="code-box px-6 px-6 bg-from-primary rounded-lg flex items-center justify-center cursor-pointer">
              <span class="text-sm text-white " @click="getCode" >
                {{ getCodeText }}
              </span>
          </div>
        </div>
      </el-form-item>

    
      <el-form-item label="邮箱验证码" prop="code">
          <el-input
            class="search-input text-lgx flex1"
            v-model="searchForm.code"
            placeholder="请输入6位邮箱验证码"
          >
            <template #prefix>
              <i class="iconfont icon-key text-gray-400" style="font-size: 16px;"></i>
            </template>
          </el-input>
         
      </el-form-item>
    </el-form>
    
    <div class="search-btn-box">
      <el-button 
        type="primary" 
        class="search-btn rounded-lg bg-from-primary"
        :disabled="loading"
        @click="handleSearch"
      >
        <i class="iconfont icon-search text-white mr-2" style="font-size: 18px;"></i>
        查询订单
      </el-button>
    </div>
    <div class="mt-5 text-xs text-gray-500"><p>*如无法查询，请联系客服处理</p></div>
  </div>
</template>

<script setup>
import { ref,defineEmits, defineExpose} from "vue";
import { orderApi } from '@/api'
import { ElMessage } from 'element-plus'
const emit = defineEmits(['searchSuccess', 'searchError', 'orderSelected'])
const formRef = ref(null)
const loading = ref(false)
const emailCodeLoading = ref(false)
const errorMsg = ref('')
const getCodeInterval = ref(null)
const getCodeTime = ref(60)
const getCodeText = ref('获取验证码')

const searchForm = ref({
  email: "",
  code: "",
});

// 添加重置表单方法
const resetForm = () => {
  searchForm.value = {
    email: "",
    code: "",
  };
  errorMsg.value = '';
  if (formRef.value) {
    formRef.value.resetFields();
  }
};
const getCode = async () => {
    let data = {
        email: searchForm.value.email,
    }
    if(!searchForm.value.email){
      errorMsg.value = '请输入邮箱地址'
      return
    }
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(searchForm.value.email)) {
      errorMsg.value = '请输入正确的邮箱地址'
      return
    }
    errorMsg.value = ''
    if(emailCodeLoading.value ){
      return
    }
    emailCodeLoading.value = true
    getCodeText.value='发送中···'
    orderApi.sendQueryCaptcha(data).then(res=>{      
      if (res.code == 1) {
          ElMessage.success(res.msg)
      } else {
          ElMessage.error(res.msg)
      }
      clearInterval(getCodeInterval.value)
      getCodeInterval.value = setInterval(() => { 
        getCodeTime.value--
        if(getCodeTime.value <= 0){          
          emailCodeLoading.value = false
          getCodeText.value = '获取验证码'
          clearInterval(getCodeInterval.value)
          getCodeTime.value = 60
        }else{
          getCodeText.value = `${getCodeTime.value}s后重试`
        }
      }, 1000);
    }).catch(e=>{
        clearInterval(getCodeInterval.value)
        getCodeInterval.value = setInterval(() => { 
          getCodeTime.value--
          if(getCodeTime.value <= 0){          
            emailCodeLoading.value = false
            getCodeText.value = '获取验证码'
            clearInterval(getCodeInterval.value)
            getCodeTime.value = 60
          }else{
            getCodeText.value = `${getCodeTime.value}s后重试`
          }
        }, 1000);
    })
}

// 暴露方法给父组件
defineExpose({
  resetForm
});

const handleSearch = async () => { 
  if(!searchForm.value.email  ){
    errorMsg.value = '请输入邮箱'
    return
  }
  if(!searchForm.value.code){
    errorMsg.value = '验证码不能为空'
    return
  }
  if (!formRef.value) return
  try {
    await formRef.value.validate()
    errorMsg.value = ''    
    let data = {}   
    data.email = searchForm.value.email
    data.email_captcha =searchForm.value.code
    loading.value = true
    const res = await orderApi.getOrderQuery(data)      
    loading.value = false
    if (res.code == 1) {    
      if(res.data.length > 0){
        emit('searchSuccess', res.data,searchForm.value.email)
        emit('orderSelected', res.data[0])
      }else{
        errorMsg.value = '暂无订单数据'
      }
      // 自动查询第一条订单详情
    } else {
      errorMsg.value = res.msg || '暂无订单数据'
      emit('searchError')
    }
  } catch (error) {
    loading.value = false
    console.error('查询失败:', error)
    errorMsg.value = '查询失败，请重试'
    emit('searchError')
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss">
.search-form {
  .order-search-tabs{
    .el-tabs__header{
      margin-bottom: 1.25rem;
    }
  }
  .search-form-class{
    .el-form-item__label{
      color:#374151;
       font-size: 14px;
       font-weight: 500;
    }
  }
}
.el-button.is-disabled, .el-button.is-disabled:hover {
    opacity: 0.5;
    background-color:var(--primary-color);
    border-color: var(--primary-color);
}
</style>
<style scoped lang="scss">
.search-form {
  .search-tips {
    border-left: solid 4px var(--primary-color);
    // background-color: rgb(0, 150, 136, 0.1);
  }
  .error-tip-box {
    // border-left: solid 4px var(--danger-color);
    // background-color: #fef2f2;
  }
  .search-input {
    height: 48px;
    width: 100%;
    box-sizing: border-box;
  }
  .code-box {
    width: 10.7rem;
    &:hover{
      background:var(--primary-dark);
    }
    height: 48px;
  }
  .space-x-2 > :not([hidden]) ~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(0.5rem * var(--tw-space-x-reverse));
    margin-left: calc(0.5rem * calc(1 - var(--tw-space-x-reverse)));
  }
  .search-btn {
    width: 100%;
    height: 48px;
    font-size: 16px;
    font-weight: 500;
   
  }
}
</style>
