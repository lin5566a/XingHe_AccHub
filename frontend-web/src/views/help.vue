<template>
    <div class="container-custom">
        <div class="max-w-4xl mx-auto py-2 sm:py-4">
            <h1 class="text-2xl font-bold mb-3 sm:mb-4">常见问题</h1>
       
        <el-tabs v-model="activeCategory" class="help-tabs" @tab-click="handleTabClick">
            <el-tab-pane 
                v-for="category in categories" 
                :key="category.id"
                :label="category.name" 
                :name="category.id.toString()"
            >
                <helpTab :category-id="category.id"></helpTab>
            </el-tab-pane>
        </el-tabs>
    </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import helpTab from '@/components/helpTab.vue'
import { protocolApi } from '@/api'

const categories = ref([])
const activeCategory = ref('')

const handleTabClick = (tab) => {
    activeCategory.value = tab.props.name
}

onMounted(async () => {
    try {
        const res = await protocolApi.getHelpCategories()
        if (res.code === 1) {
            categories.value = res.data
            if (res.data.length > 0) {
                activeCategory.value = res.data[0].id.toString()
            }
        }
    } catch (error) {
        console.error('获取帮助分类失败:', error)
    }
})
</script>
<style lang="scss" scoped>
.help-tabs{
    :deep(.el-tabs__header){
        margin-bottom: 1.5rem;
    }
}

</style>

