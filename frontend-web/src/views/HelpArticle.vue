<template>
    <div class="help-article-box">
        <div class="bg-white border-b hidden md:block">
            <div class="container-custom py-4">
                <h1 class="text-xl font-bold text-gray-800">{{ articleData.title }}</h1>
            </div>
        </div>
        <div class="">
            <div class="container-custom py-8">
                <div class="mb-6">
                    <router-link to="/help" class="linkHelp">
                        <div class="flex items-center">
                            <i class="iconfont icon-arrow-left text-gray-600 mr-2" style="font-size: 14px;"></i>
                            <!-- <el-icon class="mr-2" size="18px"><Back /></el-icon> -->
                            <span>返回帮助中心</span>
                        </div>
                    </router-link>
                </div>
                <div class="article-content flex flex-col md:flex-row gap-8">
                    <div class="prose prose-lg max-w-none md:w-3/4">
                        <div v-html="DOMPurify.sanitize(articleData.content)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { protocolApi } from '@/api';
import DOMPurify from 'dompurify'

const route = useRoute()
const articleData = ref({
    title: '',
    content: ''
})

const getArticleDetail = async () => {
    try {
        const res = await protocolApi.getHelpDetail({
            id: route.query.id
        })
        if (res.code === 1) {
            articleData.value = res.data
        }
    } catch (error) {
        console.error('获取文章详情失败:', error)
    }
}

onMounted(() => {
    getArticleDetail()
})
</script>
<style lang="scss" scoped>
img{
    max-width: 100%;
}
</style>
<style lang="scss"> 
.article-content{
    li, menu, ol, ul {
    padding: revert;
    margin: revert;
}
}


</style>