<template>
    <div class=" utilities-container">   
        <div class="utilities-header bg-white border-b md:block hidden text-left">
            <div class="container-custom py-4">
                <h1 class="text-xl font-bold text-gray-800">实用工具</h1>
            </div>
            
        </div>
        <div class="container-custom container py-3.5 sm:py-8">           
            <div class="bg-white lg:rounded-lg lg:shadow-sm">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden md:bg-transparent md:rounded-none md:shadow-none md:overflow-visible">
                    <el-tabs v-model="activeName" class="utils-tabs" @tab-click="handleClick" :stretch="false">
                        <el-tab-pane label="2FA验证码" name="2fa">
                            <TwoFAcode/>
                        </el-tab-pane>
                        <el-tab-pane label="字符换行" name="split">
                            <characterSplit/>
                        </el-tab-pane>
                        <el-tab-pane label="字符替换" name="replace">
                            <characterReplace/>
                        </el-tab-pane>
                    </el-tabs>
                </div>
            </div>
            
        </div>
    </div>
</template>
<script setup>  
    import { ref } from 'vue';
    import TwoFAcode from './2faCode.vue';
    import characterSplit from './characterSplit.vue';
    import characterReplace from './characterReplace.vue'
    const activeName = ref('2fa');
    const handleClick = (tab, event) => {
        // console.log(tab, event);
    };
</script>
<style lang="scss" scoped>
.utils-tabs{
    ::v-deep(.el-tabs__header){
        margin: 0;
        .el-tabs__active-bar{
            display: none;
        }
    }
    ::v-deep(.el-tabs__item){
        padding: 1rem 1.5rem !important;
        height: 54px;
        box-sizing: border-box;
        &.is-active{    
            background:#eff6ff;
            border-bottom: solid 2px;
        }

    }
}

@media (min-width: 640px) {
    .container {
        max-width: 640px;
    }
    .utils-tabs{
        ::v-deep(.el-tabs__item){
            font-size: .875rem;
            line-height: 1.25rem;
           
        }
    }
}
@media (min-width: 768px) {
    .container {
        max-width: 768px;
    }
}
@media (min-width: 1024px) {
    .container {
        max-width: 1024px;
    }
}
@media (min-width: 1280px) {
    .container {
        max-width: 1280px;
    }
}
</style>

