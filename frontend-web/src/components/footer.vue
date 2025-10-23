<template>
  <footer class="footer-content bg-gray-800 text-gray-300 py-8">
    <div class="container mx-auto px-4 mb-8">
      <div class="bg-gray-700 rounded-lg p-4">
        <div
          class="flex items-start mb-2 last:mb-0"
          v-for="(item, index) in footerList"
          :key="index"
        >
          <span class="mr-2">{{ index + 1 }}.</span>
          <p class="text-sm">
            {{item}}
          </p>
        </div>
      </div>
    </div>
    <div class="container mx-auto px-4">
      <div class="mall-anchor flex flex-wrap justify-center gap-4 mb-8">
        <a
          v-for="section in productSections"
          :key="section.title"
          class="text-gray-400 hover:text-white transition-colors"
          :href="`/mall#section-${section.index}`"
        >
          {{ section.title }}
        </a>
      </div>
      <div class="text-center text-gray-500 text-sm mt-8">
        {{systemInfo.copyright_info?''+systemInfo.copyright_info:'© 2022 演示站账户'}} | Powered by
        <!-- -->
        <a
          :href="'https://' + halfUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="text-primary hover:underline"
          >{{halfUrl}}</a
        >
      </div>
    </div>
  </footer>
</template>
<script setup>
import { ref, onMounted,defineProps } from 'vue';
import { productApi } from '@/api'
import { BASE_URL } from '@/utils/request'
const halfUrl = ref('')
const props = defineProps({
  systemInfo: {
    type: Object,
    default: () => ({}),
  },
})
const footerList = ref([
    '本站只是代注册各种账号，提供账号和密码，账号所有权归账号官网所有。我们只保证账号密码正确，特殊功能需少量购买后自行测试。',
    '客户付款提供后，为保证安全，项目可更改密码及密码保护资料等一切可以修改的信息。账号问题售后时间内只处理有问题的账号，不对因使用账户产生的问题做任何处理。',
    '请合法使用购买的账号，对非法使用造成的后果由购买人承担一切后果以及法律责任。'
])

const productSections = ref([])

const getProductList = async () => {
    try {
        const res = await productApi.getProductList('')
        if (res.code === 1) {
            productSections.value = res.data.list.map((item, index) => ({
                title: item.category.name,
                index: index
            }))
        }
    } catch (error) {
        console.error('获取商品列表失败:', error)
    }
}

onMounted(() => {
    getProductList();
    halfUrl.value = 'www' + BASE_URL.split('//')[1].split('api')[1]
})
</script>
<style scoped lang="scss">
.footer-content {
  .container {
    max-width: 100%;
  }
}
@media (min-width: 640px) {
  .footer-content {
    .container {
      max-width: 640px;
    }
  }
}
@media (min-width: 768px) {
  .footer-content {
    .container {
      max-width: 768px;
    }
  }
}
@media (min-width: 1024px) {
  .footer-content {
    .container {
      max-width: 1024px;
    }
  }
}
@media (min-width: 1280px) {
  .footer-content {
    .container {
      max-width: 1280px;
    }
  }
}
</style>
