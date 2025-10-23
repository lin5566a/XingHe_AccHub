<template>
    <div class="container-custom py-6">
        <div class="login-box flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-md w-full">
                <div class="bg-white rounded-lg shadow-md p-3.5">
                    <div class="login-title text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">账户登录</h2>
                            <p class="mt-2 text-sm text-gray-600">还没有账号？
                            <span class="text-primary ml-1 register hover:text-indigo-600" @click="register">立即注册</span>
                        </p>
                    </div>
                    <div class="login-con space-y-6">
                        <div class="login-item">
                            <div for="email" class="block text-sm font-medium text-gray-700">邮箱</div>
                            <div class="mt-1 relative">                                
                                <el-input class="login-input w-full" 
                                    v-model="loginData.email" 
                                    type="email" 
                                    placeholder="请输入邮箱" 
                                    size="large"
                                    autocomplete="off">
                                    <template #suffix>
                                        <i class="iconfont icon-envelope text-gray-400" style="font-size: 16px;"></i>
                                        <!-- <el-icon :size="20" color="#374151"><Message /></el-icon> -->
                                    </template>
                                </el-input>
                            </div>
                        </div>
                        <div class="login-item mt-6 ">
                            <div for="email" class="block text-sm font-medium text-gray-700">密码</div>
                            <div class="mt-1 relative">                                
                                <el-input class="login-input w-full" 
                                    v-model="loginData.password" 
                                    type="password" 
                                    placeholder="请输入密码" 
                                    size="large"
                                    autocomplete="off">
                                    <template #suffix>
                                        <i class="iconfont icon-lock text-gray-400" style="font-size: 16px;"></i>
                                        <!-- <el-icon :size="20" color="#374151"><Lock /></el-icon> -->
                                    </template>
                                </el-input>
                            </div>
                        </div>
                        <div class="login-item mt-6 ">
                            <div for="email" class="block text-sm font-medium text-gray-700">验证码</div>
                            <div class="mt-1 flex space-x-2">                                
                                <el-input class="login-input w-full" 
                                    v-model="loginData.captcha" 
                                    type="text" 
                                    placeholder="请输入验证码" 
                                    size="large">
                                    <template #suffix>
                                        <i class="iconfont icon-shield-alt text-gray-400" style="font-size: 16px;"></i>
                                        <!-- <el-icon :size="20" color="#374151"><Lock /></el-icon> -->
                                    </template>
                                </el-input>
                                <div @click="getCode" class="captcha-box bg-gray-100 rounded-md overflow-hidden ml-2">
                                    <el-image class="code-img" :src="codeImg" fit="fill"></el-image>
                                </div>
                            </div>
                        </div>
                        <div class="login-item mt-6">
                            <el-checkbox class="remember-me" v-model="loginData.rememberMe" label="" size="large" >
                                <span class="text-gray-700">记住我</span>
                            </el-checkbox>
                        </div>
                        <div class="login-item mt-6">
                            <div class="w-full button-primary cursor-pointer bg-from-blue-600" :class="{'opacity-50 cursor-not-allowed':loginData.loading}" @click="login">
                                登录
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { Message, Lock } from '@element-plus/icons-vue';
import { ref,onMounted } from 'vue';
import { BASE_URL } from '@/utils/request'
import Cookies from 'js-cookie';
import { userApi } from '@/api'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'
import md5 from 'md5';
import emitter from '@/utils/eventBus';
import axios from 'axios';
const router = useRouter();
const loginData = ref({
    email: '',
    password: '',
    captcha:'',
    rememberMe:false,
    loading:false,
});
const codeImg = ref('')
const code_token = ref('')

const getCode = async () => {
    userApi.getCaptcha().then(res=>{
          if(res.code == 1){
            codeImg.value  = res.data.image
            code_token.value = res.data.code_token
          }
       })



    // try {
    //     const response = await axios.get(`${BASE_URL}/v1/user/captcha`, {	responseType: "blob",	withCredentials: true });// 允许携带 cookie					
    //     codeImg.value = URL.createObjectURL(response.data);
    // } catch (error) {
    //     console.error("验证码获取失败", error);
    // }
    // codeImg.value = `${BASE_URL}/v1/user/captcha?_t=${Date.now()}`
}
const login =()=>{
    
    if(loginData.value.loading){
        return;
    }
    let data ={
        email:loginData.value.email,
        password: md5(loginData.value.password),
        captcha:loginData.value.captcha,
        code_token:code_token.value,
    }
    loginData.value.loading = true;
    userApi.login(data).then(async res => {
        if(res.code == 1){
            localStorage.setItem('token',res.data.token);
            localStorage.setItem('userInfo',JSON.stringify(res.data.user));
            if(loginData.value.rememberMe){
                Cookies.set('font-username',loginData.value.email);
                Cookies.set('font-password',loginData.value.password);
            }else{
                Cookies.remove('font-username');
                Cookies.remove('font-password');
            }
            await getUserInfo();
            emitter.emit('loginStatusChange');
            ElMessage.success('登录成功');
            router.push('/');
        }else{
            getCode();
            ElMessage.error(res.msg);
        }
        loginData.value.loading = false;
    }).catch(err => {
        // console.log(err);
        loginData.value.loading = false;
    }).finally(() => {
        loginData.value.loading = false;
    });
}
const register =()=>{
    router.push('/register');
}
//获取用户信息并保存
const getUserInfo = () => {
    return userApi.getUserInfo().then(res => {
        localStorage.setItem('allUserInfo', JSON.stringify(res.data));
    });
}
onMounted(() => {
    getCode()
    const savedUsername = Cookies.get('font-username');
    const savedPassword = Cookies.get('font-password');
    if (savedUsername && savedPassword) {
        loginData.value.email = savedUsername;
        loginData.value.password = savedPassword;
        loginData.value.rememberMe = true;
    }
})
</script>

<style scoped lang="scss">
  
    .login-box{
        min-height: 600px;
        .register{
            cursor: pointer;
            // &:hover{
            //     color: var(--primary-color-dark);
            // }
        }
        .login-con{
            .login-input{
                height: 42px;
                font-size: 1rem;
                :deep(.el-input__wrapper){
                    padding-left:  1rem;
                    padding-right: 1rem;
                    border-radius: 0.375rem;
                }
            }
            .captcha-box{
                width:8rem;
                height: 42px;
                cursor: pointer;
                .code-img{
                    height:100%;
                    width:100%;
                }
            }
            .remember-me{
                :deep(.el-checkbox__input) {
                    &.is-checked {
                        :deep(.el-checkbox__inner) {
                            background-color: var(--primary-color);
                            border-color: var(--primary-color);
                        }
                    }
                    :deep(.el-checkbox__inner) {
                        &:hover {
                            border-color: var(--primary-color);
                        }
                    }
                }
            }
        }
    }


</style>