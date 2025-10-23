<template>
    <div class="announcement-box">
        <div class="announcement-content">
            <div class="max-w-6xl mx-auto px-3 sm:px-6 h-full flex items-center justify-between">
                <div class="flex item-center justify-between flex-1 items-center">
                    <div class="flex item-center flex-1 items-center">
                        <i class="iconfont icon-info-circle mr-2" style="font-size:16px;"></i>
                        <span class="">公告：</span>
                        <span class="text-gray-700 announcement-title">{{announcement.title}}</span>
                        <span class="ml-2 font-medium cursor-pointer hover:text-sky-600" @click="showContent">查看详情 > </span>
                    </div>
                    <div @click="closeAnnouncement" class="cursor-pointer hover:text-sky-600"><i class="icon-times iconfont"></i>关闭</div>
                </div>
            </div>
      
        </div>        
        <div class="dialog-box">
            <el-dialog
                :close-on-click-modal="false"
                v-model="dialogVisible"
                title=""
                align-center
                class="announcement-class"
                @close="closeDialog"
            >
                <div class="text-gray-700 text-base py-4">{{announcement.content}}</div>
                <template #footer>
                    <div class="dialog-footer">
                        <el-button class="dialog-footer-btn bg-sky-600 hover:bg-sky-700" type="primary" @click="iKnow">我已了解</el-button>
                    </div>
                </template>
            </el-dialog>
        </div>
    </div>
    
</template>
<script setup>
    import {ref,defineEmits,defineProps} from "vue"
    const dialogVisible = ref(false)
    const emit = defineEmits(['closeAnnouncement']) 
    const props = defineProps({
        announcement:{
            type:Object,
            default: () => ({})
        }
    })
    const closeAnnouncement = () => {
        emit('closeAnnouncement')
    } 
   
    const iKnow = () => {
        closeDialog();
        emit('closeAnnouncement')
    }
    const closeDialog = () => {
        dialogVisible.value = false
    }
    const showContent = () => {
        dialogVisible.value = true
    }
</script>
<style scoped lang="scss">
    .announcement-box{
        background: #fff;
        .announcement-content{
            background: #f0f9ff;
            border:solid 1px #e0f2fe;
            height: 3rem;
            color:#0ea5e9;
            .announcement-title{
                // width: 11rem;
                // overflow: hidden;
                // white-space: nowrap;
                // text-overflow: ellipsis;
            }
        }
      
    }
    :deep(.announcement-class){
        width: calc(100% - 2rem);
        max-width: 28rem;
    }
    .dialog-footer-btn{
        width: 96px;
        height: 40px;
        font-size: 1rem;
        line-height: 1.5;
        font-weight: 400;
    }
    @media (max-width: 768px) {
        .announcement-box{
            .announcement-content{
                .announcement-title{
                    flex:1;
                    width: 11rem;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                }
            }
        }
    }
</style>