<template>
    <div class="helpTab bg-white rounded-lg shadow-sm p-6">
        <div 
            class="help-item block border-b border-gray-100 py-4 cursor-pointer" 
            v-for="item in helpData" 
            :key="item.id" 
            @click="showArticle(item)"
        >
            <h3 class="title font-medium mb-2 default-colo">{{item.title}}</h3>
            <p class="introduction text-sm text-gray-600 line-clamp-2">{{item.subtitle}}</p>
        </div>
    </div>
</template>
<script setup>
import {ref, defineProps, watch} from 'vue'
import { useRouter } from 'vue-router'
import { protocolApi } from '@/api'

const props = defineProps({
    categoryId: {
        type: [String, Number],
        required: true
    }
})

const router = useRouter()
const helpData = ref([])

const getHelpDocuments = async () => {
    try {
        const res = await protocolApi.getHelpDocuments({
            category_id: props.categoryId
        })
        if (res.code === 1) {
            helpData.value = res.data
        }
    } catch (error) {
        console.error('获取帮助文档失败:', error)
    }
}

const showArticle = (item) => {
    router.push({
        name: 'article',
        query: { id: item.id }
    })
}

// 监听分类ID变化
watch(() => props.categoryId, () => {
    getHelpDocuments()
}, { immediate: true })
</script>
<style scoped lang="scss">
.helpTab{
    .help-item{
        &:hover{
            color: var(--primary-color);
        }
        &:last-child{
            border-bottom: none;
        }
        .title{
            
        }
    }
}
</style>