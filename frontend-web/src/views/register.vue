<template>
    <div class="container-custom py-6">
        <div class="login-box flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-md w-full">
                <div class="bg-white rounded-lg shadow-md p-3.5">
                    <div class="login-title text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">账号注册</h2>
                            <p class="mt-2 text-sm text-gray-600">已有账号？
                            <span class="text-primary ml-1 register hover:text-indigo-600" @click="login">立即登录</span>
                        </p>
                    </div>
                    <div class="login-con space-y-6">
                        <el-form :model="registerData" :rules="rules" ref="registerForm" label-width="" label-position="top" :hide-required-asterisk="true">
                           
                            <el-form-item label="邮箱" prop="email">
                                <div class="mt-1 flex space-x-2 w-full">
                                    <el-input class="login-input w-full flex1" 
                                        v-model="registerData.email" 
                                        type="email" 
                                        placeholder="请输入邮箱" 
                                        size="large"
                                        :readonly="codeData.codeLoading"
                                        autocomplete="off">
                                        <template #suffix>
                                            <i class="iconfont icon-envelope text-gray-400" style="font-size: 16px;"></i>
                                        </template>
                                    </el-input>
                                    <div class="code-box flex px-4 py-2 bg-from-primary rounded-lg ml-2 items-center justify-center cursor-pointer">
                                        <span class="text-sm text-white " @click="getEmailCode">
                                            {{getCodeText}}
                                        </span>
                                    </div>
                                </div>
                            </el-form-item>
                            <el-form-item label="邮箱验证码" prop="emailCode">
                                <el-input class="login-input w-full" 
                                    v-model="registerData.emailCode" 
                                    type="text" 
                                    placeholder="请输入邮箱验证码" 
                                    size="large"
                                    autocomplete="off">
                                    <template #suffix>
                                        <i class="iconfont icon-shield-alt text-gray-400" style="font-size: 16px;"></i>
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="密码" prop="password">
                                <el-input class="login-input w-full" 
                                    v-model="registerData.password" 
                                    type="password" 
                                    placeholder="请设置6-16位密码" 
                                    size="large"
                                    autocomplete="new-password">
                                    <template #suffix>
                                        <i class="iconfont icon-lock text-gray-400" style="font-size: 16px;"></i>
                                        <!-- <el-icon :size="20" color="#374151"><Message /></el-icon> -->
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="确认密码" prop="confirmPassword">
                                <el-input class="login-input w-full" 
                                    v-model="registerData.confirmPassword" 
                                    type="password" 
                                    placeholder="请再次输入密码" 
                                    size="large"
                                    autocomplete="new-password">
                                    <template #suffix>
                                        <i class="iconfont icon-lock text-gray-400" style="font-size: 16px;"></i>
                                        <!-- <el-icon :size="20" color="#374151"><Message /></el-icon> -->
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="验证码" prop="captcha">
                                <div class="mt-1 flex space-x-2 w-full">                                
                                    <el-input class="login-input w-full" 
                                        v-model="registerData.captcha" 
                                        type="text" 
                                        placeholder="请输入验证码" 
                                        size="large">
                                        <template #suffix>
                                            <i class="iconfont icon-shield-alt text-gray-400" style="font-size: 16px;"></i>
                                        </template>
                                    </el-input>
                                    <div @click="getCode" class="captcha-box bg-gray-100 rounded-md overflow-hidden ml-2">
                                        <div class="refresh-btn bg-white rounded-full"><i class="icon-sync iconfont text-blue-600 font-semibold" style="font-size: 12px;"></i></div>
                                        <el-image class="code-img  rounded-md" :src="codeImg" fit="fill"></el-image>
                                    </div>
                                </div>
                            </el-form-item>
                        </el-form>
                        <div class="flex flex-col sm:flex-row sm:items-center mt-5">
                            <el-checkbox
                            class="protocol-checkbox"
                            v-model="registerData.protocol"
                            size="large"
                            @change="protocolChange"
                            >
                            <span class="text-gray-600"> 我已阅读并同意</span>
                            <span class="text-primary">《服务条款》</span>
                            <span class="text-gray-600">和</span>
                            <span class="text-primary">《隐私政策》</span>
                            </el-checkbox>
                        </div>

                        <div class="login-item mt-6">
                            <div class="w-full button-primary bg-from-blue-600 cursor-pointer" :class="{'opacity-50 cursor-not-allowed':loading}" @click="register">
                                注册
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dialog-box">
            <el-dialog
                v-model="protocolDialogData.visible"
                title="服务协议" 
                class="protocol-dialog" 
                :show-close="false" 
                :close-on-click-modal="false"
                modal-class="protocol-modal" 
                header-class="protocol-header" 
                body-class="protocol-body" 
                footer-class="protocol-footer"
            >
                <div class="protocol-content">
                    <div v-html="DOMPurify.sanitize(protocolDialogData.content)"></div>
                </div>
                <template #footer>
                    <div
                        class="dialog-footer flex items-center justify-center gap-3 bg-gray-50"
                    >
                        <div
                        :class="
                            protocolDialogData.disabled
                            ? 'button-info'
                            : 'button-primary cursor-pointer'
                        "
                        class="text-lgx protocol-btn"
                        @click="agreeProtocol()"
                        >
                        {{
                            protocolDialogData.disabled
                            ? `阅读剩余${protocolDialogData.time}秒`
                            : "我同意"
                        }}
                        </div>
                        <div
                        class="text-lgx button-info ml-1 cursor-pointer protocol-btn"
                        @click="disagreeProtocol()"
                        >
                        我不同意
                        </div>
                    </div>
                </template>
            </el-dialog>
        </div>
    </div>
</template>
<script setup>
import { ref,onMounted } from 'vue';
import { useRouter } from 'vue-router'
import { userApi,protocolApi } from '@/api';
import { ElMessage } from 'element-plus';
import DOMPurify from 'dompurify'
import md5 from 'md5';

/* global defineOptions */
defineOptions({ name: 'RegisterView' })
const router = useRouter();
const registerForm = ref(null);
const loading = ref(false);
const getCodeInterval = ref(null)
const getCodeTime = ref(60)
const getCodeText = ref('获取验证码')
const protocolDialogData = ref({
  time: 3,
  disabled: true,
  visible: false,
  title: "服务协议",
  content:'',
});
const registerData = ref({
    emailCode:'',
    email: '',
    password: '',
    confirmPassword:'',
    captcha:'',
    rememberMe:false,
    loading:false,
    protocol:false,
});
const codeData = ref({
    codeLoading:false,
})

// const timOut = ref(null);
// const validateNickname = (rule, value, callback) => {
//     const reg = /^[\u4e00-\u9fa5a-zA-Z0-9_]{2,20}$/;
//     if (!reg.test(value)) {
//         callback(new Error('昵称仅支持中文、英文、数字、下划线，长度2-20字符'));
//     } else {
//         callback();
//     }
// }

const validatePassword = (rule, value, callback) => {
    const reg = /^(?=.*[a-zA-Z])(?=.*\d).+$/;
    if (!reg.test(value)) {
        callback(new Error('密码必须包含英文字母和数字'));
    } else {
        callback();
    }
}

const validateConfirmPassword = (rule, value, callback) => {
    if (value !== registerData.value.password) {
        callback(new Error('两次输入密码不一致'));
    } else {
        callback();
    }
}

const rules = ref({
    emailCode: [
        { required: true, message: '请输入邮箱验证码', trigger: 'blur' },
        // { validator: validateNickname, trigger: 'blur' }
    ],
    email: [
        { required: true, message: '请输入邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
    ],
    password: [
        { required: true, message: '请输入密码', trigger: 'blur' },
        { min: 6, max: 16, message: '密码长度在6-16个字符之间', trigger: 'blur' },
        { validator: validatePassword, trigger: 'blur' }
    ],
    confirmPassword: [
        { required: true, message: '请输入确认密码', trigger: 'blur' },
        { validator: validateConfirmPassword, trigger: 'blur' }
    ],
    captcha: [{ required: true, message: '请输入验证码', trigger: 'blur' }],
})

const codeImg = ref('')
const code_token = ref('')
const getCode = async () => {
    userApi.getCaptcha().then(res=>{
        if(res.code == 1){
        codeImg.value  = res.data.image
        code_token.value = res.data.code_token
        }
    })
}
const protocolChange = (val) => {
    // console.log(val, "====");
    if (val) {
    //设置倒计时
    protocolDialogData.value.time = 0;
    protocolDialogData.value.disabled = false;
    //先取消勾选  等带同意协议后才勾选
    registerData.value.protocol = false;
    //弹窗协议弹窗    
    protocolDialogData.value.visible = true;
    // clearInterval(timOut.value);
    // timOut.value = setInterval(() => {
    //   protocolDialogData.value.time--;
    //   if (protocolDialogData.value.time <= 0) {
    //     protocolDialogData.value.disabled = false;
    //     clearInterval(timOut.value);
    //   }
    // }, 1000);
  } else {
    registerData.value.protocol = false;
  }
}
//弹窗内同意用户协议事件
const agreeProtocol = () => {
    // console.log(protocolDialogData.value.disabled,'protocolDialogData.value.disabled')
  if (protocolDialogData.value.disabled) {
    return;
  }
  registerData.value.protocol = true;
  protocolDialogData.value.visible = false;  
//   clearInterval(timOut.value);
};
//弹窗内不同意用户协议事件
const disagreeProtocol = () => {
    registerData.value.protocol = false;
  protocolDialogData.value.visible = false;  
//   clearInterval(timOut.value);
};
const login =()=>{
    router.push('/login');
}
const register = async () => {
    

    if (loading.value) return;

    if (!registerForm.value) return;    
  
    await registerForm.value.validate();
    
    if (!registerData.value.protocol) {
        ElMessage.warning('请阅读并同意服务条款和隐私政策');
        return;
    } 
    try {

        loading.value = true;
        const res = await userApi.register({
            email: registerData.value.email,
            password: md5(registerData.value.password),
            captcha: registerData.value.captcha,
            email_captcha:registerData.value.emailCode,
            code_token:code_token.value
        });

        if (res.code === 1) {
            ElMessage.success(res.msg);
            router.push('/login');
        } else {
            ElMessage.error(res.msg);
            getCode(); // 刷新验证码
        }
    } catch (error) {        
        loading.value = false;
        console.error('注册失败:', error);
        // ElMessage.error('注册失败，请重试');
        getCode(); // 刷新验证码
    } finally {
        loading.value = false;
    }
}
//获取使用协议
const getProtocol = async () => {
  const res = await protocolApi.getProtocol('使用协议')
  if (res.code === 1) {
    protocolDialogData.value.content = res.data.content
  }
}
//获取邮箱验证码
const getEmailCode = () => {
    if (codeData.value.codeLoading) {
        return;
    }
    if (!registerForm.value) {
        return;
    }

    registerForm.value.validateField('email', (arg1) => {
        const isValid = typeof arg1 === 'boolean' ? arg1 : !arg1;
        if (!isValid) {
            return;
        }
        const data = {
            email: registerData.value.email
        };
        codeData.value.codeLoading = true;
        getCodeText.value='发送中···'
        userApi.sendRegisterCaptcha(data).then((res) => {  
                if (res.code == 1) {
                    ElMessage.success(res.msg)
                } else {
                    ElMessage.error(res.msg)
                }            
                clearInterval(getCodeInterval.value)
                getCodeInterval.value = setInterval(() => { 
                    getCodeTime.value--
                    if(getCodeTime.value <= 0){ 
                        codeData.value.codeLoading = false;   
                        getCodeText.value = '获取验证码'
                        clearInterval(getCodeInterval.value)
                        getCodeTime.value = 60
                    }else{
                        getCodeText.value = `${getCodeTime.value}s后重试`
                    }
                }, 1000); 
            })
            .catch((e) => {
                clearInterval(getCodeInterval.value)
                getCodeInterval.value = setInterval(() => { 
                    getCodeTime.value--
                    if(getCodeTime.value <= 0){ 
                        codeData.value.codeLoading = false;   
                        getCodeText.value = '获取验证码'
                        clearInterval(getCodeInterval.value)
                        getCodeTime.value = 60
                    }else{
                        getCodeText.value = `${getCodeTime.value}s后重试`
                    }
                }, 1000);                
                // console.log(e)
            });
    });
}
onMounted(() => {
    getProtocol()
    getCode()
    
})
</script>
<style lang="scss">

.protocol-modal {
  .el-overlay-dialog{
  display: flex;
  justify-content: center;
  align-items: center;}
  
  .protocol-dialog {
    padding: 0;
    max-width:48rem;
    margin: 1rem;
    width:100%;
    .protocol-header {
    padding: 1rem;
    border-bottom: solid 1px #e5e7eb;
    text-align: center;
      .el-dialog__title {
      font-size: 1.25rem;
      color: var(--danger-color);
      font-weight: 500;
      line-height: 1.75rem;
    }
  }
    .protocol-body {
      padding: 1.25rem;
    max-height: 65vh;
    overflow-y: auto;
  }
    .protocol-footer {
    padding: 1rem;
    border-top: solid 1px #e5e7eb;
      .protocol-btn {
      padding-left: 2rem;
      padding-right: 2rem;
    }
  }
}
}
</style>
<style scoped lang="scss">
    .container-custom {
    max-width: 1280px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 1rem;
    padding-right: 1rem;
    width: 100%;
    
    }
    .login-box{
        min-height: 600px;
        .register{
            cursor: pointer;
            // &:hover{
            //     color: var(--primary-color-dark);
            // }
        }
        .login-con{
            :deep(.el-form-item__label){
                font-size: 0.875rem;
                line-height: 1.25rem;
                font-weight: 500;
                color:#374151;
            }
            .login-input{
                height: 42px;
                font-size: 1rem;
                :deep(.el-input__wrapper){
                    padding-left:  1rem;
                    padding-right: 1rem;
                    border-radius: 0.375rem;
                }
            }
            .code-box{
                width: 10.8rem;
            }
            .captcha-box{
                width:8rem;
                height: 42px;
                cursor: pointer;
                position: relative;
                border: solid 1px #e5e7eb;
                .refresh-btn{
                    position: absolute;
                    top:-7px;
                    right:-7px;
                    width:22px;
                    height: 22px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1;                    
                    border: solid 1px #e5e7eb;
                }
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
    @media (min-width: 640px) {
  .container-custom {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    max-width: 640px;
  }
}
@media (min-width: 768px) {
  .container-custom {
    max-width: 768px;
  }
}
@media (min-width: 1024px) {
  .container-custom {
    max-width: 1024px;
    padding-left: 2rem;
    padding-right: 2rem;
  }
}
@media (min-width: 1280px) {
  .container-custom {
    max-width: 1280px;
  }
}

</style>