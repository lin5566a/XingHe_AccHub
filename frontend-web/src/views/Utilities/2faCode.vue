<template>
    <div class="tow-fa-code-container container-custom py-6">
        <div class="max-w-6xl mx-auto">
            <div class="mb-3 sm:mb-4">
                <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-1">2FA验证码</h2>
                <p class="text-xs sm:text-sm text-gray-600">生成两步验证码，用于账户安全验证，支持标准的TOTP协议</p>
            </div>
            <div class="secret-key-container">
                <el-form class="secret-key-form" :model="form" label-width="80px" label-position="left" size="large">
                    <el-form-item label="2FA密钥">
                        <el-input class="form-input" :class="message ? 'error-input' : ''" v-model="form.secret" placeholder="输入卡密中的2FA代码"></el-input>
                        <div class="flex-1">
                            <div class="mt-1 text-xs text-gray-500">输入您的2FA密钥，通常是一串字母和数字的组合</div>
                        <div class="secret-key-error mt-2 p-2 bg-red-50 border border-red-200 rounded-md" v-if="message">
                            <p class="text-sm text-red-600 font-medium">密钥格式错误，请输入正确的2FA密钥</p>
                            
                        </div>
                        </div>                       
                    </el-form-item>
                    <el-form-item label="2FA验证码">
                        <el-input :readonly="true" class="form-input code-input" v-model="currentCode" placeholder="有效期30秒，过期请点击重新获取">
                            <template #suffix>
                                <span class="bg-primary-color text-white text-xs rounded-full px-2 py-1" v-show="currentCode">{{ timeLeft }}s</span>
                            </template>
                            <template #append>
                                <div class="copy-code-btn text-sm text-blue-500 px-4 rounded-r-md transition-all" 
                                :class="currentCode ?'cursor-pointer' : 'cursor-not-allowed'"
                                @click="copyCode">{{isCopy?'已复制':'点击复制'}}</div>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button size="large" class="get-code-btn hover:bg-blue-700" type="primary" @click="generateCode">{{currentCode?'重新获取':'立即获取'}}</el-button>
                    </el-form-item>
                </el-form>
             <div class="mt-4">
                <div class="use-tip-box bg-blue-50 border border-blue-200 rounded-md p-4"><h3 class="text-blue-700 text-base font-medium mb-2">使用提示</h3>
                    <ol class="pl-4 text-sm text-blue-600 list-decimal list-inside space-y-2">
                        <li>2FA验证码每30秒自动更新一次，请在有效期内使用</li>
                        <li class="mt-2">如果验证失败，请检查密钥是否正确，或尝试重新获取验证码</li>
                        <li class="mt-2">验证码使用标准TOTP协议生成，与Google Authenticator等应用兼容</li>
                        <li v-if="noSecret" class="mt-2 text-red-500" >请输入2FA密钥</li>
                    </ol>
                </div>
             </div>
            </div>
           
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { TOTP } from 'otpauth';

const form = ref({
    secret: '',
});

const isCopy = ref(false);
const currentCode = ref('');
const timeLeft = ref(0);
const message = ref('');
const messageType = ref('');
const noSecret = ref(false);
let timer = null;

const generateCode = () => {
    if (!form.value.secret) {
        noSecret.value = true;
        message.value = '';
        messageType.value = 'error';
        return;
    }    
    noSecret.value = false;
    try {
        const totp = new TOTP({
            algorithm: 'SHA1',
            digits: 6,
            period: 30,
            secret: form.value.secret
        });
        let code = totp.generate();
        currentCode.value =code;
        message.value = '';
        messageType.value = 'success';            
        // 重置倒计时
        let currentTime = Math.floor(Date.now() / 1000); // 当前时间（秒）
        let timeStep = 30; // TOTP时间间隔
        timeLeft.value = timeStep - (currentTime % timeStep);
        startTimer();    
       
    } catch (error) {
        currentCode.value = '';
        message.value = '生成2FA验证码失败: ' + error.message;
        messageType.value = 'error';
        startTimer();    
    }
};

const startTimer = () => {
    stopTimer(); // 确保之前的定时器被清除
    timer = setInterval(() => {
        timeLeft.value--;
        if (timeLeft.value <= 0) {
            generateCode();
        }
    }, 1000);
};

const stopTimer = () => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
};

const copyCode = () => {
    if(!currentCode.value){
        return;
    }
    navigator.clipboard.writeText(currentCode.value).then(() => {
        isCopy.value = true;
        setTimeout(() => {
            isCopy.value = false;
        }, 3000);
    }).catch(() => {
        isCopy.value = false;
    })
}
// 监听secret变化
// watch(() => form.value.secret, (newVal) => {
//     if (newVal) {
//         generateCode();
//     } else {
//         currentCode.value = '';
//         stopTimer();
//     }
// });

onMounted(() => {
    // if (form.value.secret) {
    //     generateCode();
    // }
});

onUnmounted(() => {
    stopTimer();
});
</script>

<style lang="scss">
.tow-fa-code-container{
    padding:  0.875rem ;
    
    .secret-key-form{
        .el-form-item__label{
            font-size: .875rem;
            color: #374151;
            font-weight: 500;
        }
        .form-input{
            height:42px;
            font-size: 1rem;
            border-radius: 0.375rem;
            .el-input__inner{
                color:#1f2937
            }
            &.code-input{
                .el-input__inner{
                    // text-align: center;
                } 
                .el-input__wrapper{
                    z-index: 1;
                }
            }
            .el-input-group__append{
                padding: 0;
                background-color: transparent;
                border-top-right-radius: 0.375rem;
                border-bottom-right-radius: 0.375rem;
            }
            &.error-input{
                .el-input__wrapper{
                    // outline: 1px solid #ef4444;
                    box-shadow: 0 0 0 1px #ef4444;
                }
                
            }
        
        }
        .get-code-btn{
            height: 42px;
            border-radius: 0.375rem;
            font-size: 1rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
    }

}
</style>
<style scoped lang="scss">
.use-tip-box{
    // background-color: rgba(0, 150, 136, 0.05);
    // border-color: rgba(0, 150, 136, .2); 
}
.copy-code-btn{
    // height: 42px;
}
</style>

