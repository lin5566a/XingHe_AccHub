<template>
    <div v-if="videoDataObj.has_video" class="video-player-box bg-white border border-gray-200 rounded-lg shadow-sm p-3.5 mb-6">
        <div class="method-header flex items-center mb-4 pb-3 border-b border-gray-100">
            <h2 class="text-base sm:text-lg font-medium text-gray-800 flex items-center">
                <!-- <el-icon color="#6b7280" class="mr-2"><Ticket /></el-icon> -->
                <i class="iconfont icon-shipin mr-2 text-primary text-sm sm:text-lg"></i>
                使用教程
            </h2>
        </div>
        <div class="border-t border-gray-200 py-3 sm:py-4 mb-2 sm:mb-4">
            <video class="w-full h-48 sm:h-64 " 
            ref="previewVideo"
            :src="BASE_URL + videoDataObj.video_url"
            controls
            preload="metadata"
            @error="handleVideoError"
            ></video>
        </div>
    </div>
</template>
<script setup>
import { BASE_URL } from '@/utils/request'
    import { ref, onMounted, defineProps} from 'vue'
    import { ElMessage } from 'element-plus'
    import {paymentApi} from '@/api'
    const videoDataObj = ref({})
    const props = defineProps({
        orderDetail:{
            type:Object,
            default:()=>{}
        }
    })
    onMounted(() => {
        getProductVideo()
    })
    const getProductVideo = async () => {
        let data = {
            order_no: props.orderDetail.order_number
        }
        const res = await paymentApi.getProductVideo(data)
        if (res.code == 1) {            
            videoDataObj.value = res.data
        }else{
            ElMessage.error(res.msg)
        }
    }
    const handleVideoError = (event) => {
        // console.log('视频加载失败:', event);
    }
</script>