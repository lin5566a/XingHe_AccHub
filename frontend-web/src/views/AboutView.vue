<template>
  <div class="about-container">
    <div class="max-w-4xl mx-auto py-2 sm:py-4 px-3.5 sm:px-4">
      <div v-html="DOMPurify.sanitize(articleContent)"></div>
    </div>
  </div>
</template>
<script setup>
import { ref,onMounted } from 'vue';
import DOMPurify from 'dompurify'
import {protocolApi} from '@/api'
const articleContent = ref('');
articleContent.value=``

const getProtocol = async () => {
  const res = await protocolApi.getProtocol('关于我们')
  if (res.code === 1) {
    articleContent.value = res.data.content
  }
}
onMounted(() => {
  getProtocol()
})
</script>
<style lang="scss">

.about-container{
  img{
    max-width: 100%;
  }
  li, menu, ol, ul {
    padding: revert;
    margin: revert;
}
}
</style>
<style scoped lang="scss">

.about-container{
  img{
    max-width: 100%;
  }
  li, menu, ol, ul {
    padding: revert;
    margin: revert;
}
}
</style>