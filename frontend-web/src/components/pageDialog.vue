<template>
    
    <el-dialog
        v-model="dialogVisible"
        :title="dialogContent.title" 
        class="page-dialog" 
        :close-on-click-modal="false"
        width="500px"
        modal-class="page-dialog-modal" 
        header-class="page-dialog-header" 
        body-class="page-dialog-body" 
        footer-class="page-dialog-footer"
        :before-close="closeDialog"
    >
        <div class="page-dialog-content">
            <div v-html="DOMPurify.sanitize(dialogContent.content)"></div>
        </div>
        <template #footer>
            <div class="p-6 border-t border-gray-200">
                <div type="primary" class="to-buy w-full text-white py-3 px-4 rounded-lg flex items-center justify-center cursor-pointer" disabled="false" @click="closeDialog"> 开始购物 </div>
            </div>
        </template>
    </el-dialog>
</template>
<script setup>

import { ref, defineEmits,defineProps } from 'vue';
import DOMPurify from 'dompurify'
const props = defineProps({
    visible:{
            type:Boolean,
            default: false
        },

    dialogContent:{
            type:Object,
            default: () => ({})
        }
    })
const dialogVisible = ref(props.visible)
const emits = defineEmits(['closeDialog'])
const closeDialog = ()=>{
    emits('closeDialog')
}
</script>
<style lang="scss">
.page-dialog{
    padding-bottom: 0;
    .to-buy{
        background-color:  var(--primary-dark);
    }
}
    
</style>
