<template>
    <div class="no-login-box flex items-center justify-center  min-h-screen bg-gray-50">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
            <div class="flex justify-center mb-6">     
                <div class="w-16 h-16 bg-primary-color-1 rounded-full flex items-center justify-center">
                    <i class="iconfont icon-user text-primary" style="font-size: 24px;"></i>
                </div>          
            </div>
            <h2 class="text-xl font-bold text-center text-gray-800 mb-6">需要登录</h2>
            <div class="text-gray-600 mb-6 text-center">您需要登录才能访问账户页面。</div>
            <div class="flex flex-col space-y-3">
                <div><el-button type="primary" class="w-full login-btn bg-blue-600 hover:bg-blue-700" @click="login">立即登录</el-button></div>
                
                <div><el-button class="w-full home-btn mt-2  border-blue-600 text-blue-600 hover:bg-blue-50" plain @click="goHome">返回首页</el-button></div>
                
                <!-- <button class="w-full bg-[#009688] text-white py-2 rounded-md hover:bg-[#00796b] transition-colors disabled:opacity-70">立即登录</button> -->
                <!-- <button class="w-full border border-[#009688] text-[#009688] py-2 rounded-md hover:bg-[#009688]/5 transition-colors">返回首页</button> -->
            </div>
        </div>
    </div>
</template>
<script setup>
import { onMounted } from 'vue';
import emitter from "@/utils/eventBus";
import { useRouter } from 'vue-router';

const router = useRouter();
const goHome = () => {
    router.push('/mall');
}
const login = () => {
    router.push('/login');
}
onMounted(()=>{
    emitter.emit("loginStatusChange");
})




</script>
<style lang="scss">
.no-login-box{
    .login-btn{
        height: 40px;
        font-size: 1rem;
        border-radius: .375rem;
    }
    .home-btn{
        height: 40px;
        // border-color: #009688;
        // color: #009688;
        font-size: 1rem;
        border-radius: .375rem;
        &:hover{
            // background-color: rgba(0, 150, 136,0.1);
            // color: #fff;
        }
    }
}
</style>
