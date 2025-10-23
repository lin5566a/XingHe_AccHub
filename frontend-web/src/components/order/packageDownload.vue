<template>    
    <div class="order-info bg-white p-3-5 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center font-medium" >
                    <!-- <i class="text-base sm:text-xl iconfont icon-info-circle primary-color mr-2" style="font-weight: 300;"></i> -->
                    <i class="text-base sm:text-xl text-primary iconfont icon-download mr-2"></i>
                    安装包下载
                </h2>
                <p class="text-xs text-gray-500 mt-1">下载相关应用提升账号使用体验</p>
            </div>
            
        </div>
        <div class="border-t border-gray-200 py-3 sm:py-3.5">           
            <div class="package-list" v-for="(item,index) in packageList" :key="index">
                <div class="package-item flex items-center justify-between p-2 sm:p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <el-image class="icon-img mr-3" :src="`${BASE_URL}${item.icon}`"></el-image>
                        <span class="text-sm sm:text-base">{{ item.name }}</span>
                    </div>
                    <div>
                        <el-button 
                            class="download-btn bg-from-blue-600 text-xs sm:text-sm px-3 py-1.5 rounded-md" 
                            color="#009688"
                            :disabled="!item.can_download"
                            @click="handleDownload(item)"
                        >
                            <i class="iconfont icon-download text-sm"></i>
                            下载
                        </el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { ElMessage } from 'element-plus'
import { BASE_URL,uploadURL } from '@/utils/request'

const props = defineProps({
  packageList: {
    type: Array,
    required: true,
    default: () => []
  }
})

const handleDownload = (item) => {
//   if (item.type === 2) {
    // 复制链接
    // navigator.clipboard.writeText(item.download_url).then(() => {
    //   ElMessage.success('链接已复制到剪贴板')
    // }).catch(() => {
    //   ElMessage.error('复制失败，请手动复制')
    // })
//   } else {
    // 下载文件
    window.open(`${uploadURL}${item.download_url}`, '_blank')
//   }
}
</script>

<style lang="scss" scoped>
.package-list{
    .package-item{
        .icon-img{
            width: 20px;
            height: 20px;
        }
        .download-btn{
            height: 28px;
            box-sizing: border-box;
            
        }
    }
}
@media (min-width: 640px) {
    .package-list{
    .package-item{
        .icon-img{
            width: 24px;
            height: 24px;
        }
        .download-btn{
            height: 32px;
            box-sizing: border-box;
            
        }
    }
}
}
</style>
