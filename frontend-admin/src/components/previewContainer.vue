<template>
    <div class="preview-container" v-html="htmlStr">
    </div>
</template>
<script>
/**
 * htmlStr: 预览 html字符串
 */
export default {
    name:"previewContainer",
    props:{
        htmlStr:{
            type:String,
            default:""
        }
    },
   
}
</script>
<style lang="scss" scoped>

//预览 编辑器
.preview-container {
    padding: 20px;
    max-height: 600px;
    overflow-y: auto;
    background-color: #fff;
    border: 1px solid #ebeef5;
    border-radius: 4px;
    box-sizing: border-box;
}
.preview-container h2,
.ql-editor h2 {
  margin-top: 20px;
  margin-bottom: 10px;
  padding-bottom: 5px;
  border-bottom: 1px solid #ebeef5;
  color: #303133;
}

.preview-container ul,
.preview-container ol,
.ql-editor ul,
.ql-editor ol {
  padding-left: 20px;
  margin-bottom: 15px;
}

.preview-container li,
.ql-editor li {
  margin-bottom: 5px;
}

.preview-container p,
.ql-editor p {
  margin-bottom: 10px;
  line-height: 1.6;
}

</style>