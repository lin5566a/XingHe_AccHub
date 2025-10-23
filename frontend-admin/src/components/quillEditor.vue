<template>
    <div>
      <div ref="editorContainer" style="height: 400px;"></div>
    </div>
  </template>
  
  <script>
  import Quill from 'quill';
  import 'quill/dist/quill.snow.css';
  
  export default {
    name: 'RichTextEditor',
    props: {
      quillEnable: {
        type: Boolean,
        default: true
      },
      content: {
        type: String,
        required: true,
        default: '',
      },
    },
    data(){
      return {
         quill: null,//quill 实例
      };
    },
    emits: ['update:content'],
    mounted() {
      const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], // 加粗、斜体、下划线、删除线        
        ['blockquote', 'code-block'], // 引用、代码块
        [{ 'header': 1 }, { 'header': 2 }], // 标题
        [{ 'list': 'ordered' }, { 'list': 'bullet' }], // 有序/无序列表
        [{ 'script': 'sub' }, { 'script': 'super' }], // 上标/下标
        [{ 'indent': '-1' }, { 'indent': '+1' }], // 缩进
        [{ 'direction': 'rtl' }], // 文本方向（从右到左）
        [{ 'align': [] }], // 对齐方式
        [{ 'size': ['small', false, 'large', 'huge'] }], // 字体大小
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }], // 多级标题
        [{ 'color': [] }, { 'background': [] }], // 文本颜色和背景色
        [{ 'font': [] }], // 字体
        ['link', 'image', 'video'], // 插入链接、图片、视频
        // 第四行工具（清除格式）
        ['clean'] // 清除格式
      ];
      this.quill = new Quill(this.$refs.editorContainer, {
        theme: 'snow', // 选择主题，`snow` 是 Quill 的默认主题
        placeholder: '在此输入内容...',
        modules: {
          toolbar: toolbarOptions,
        },
      });
      this.setContent(this.content)
      
      this.quill.on('text-change', () => {
        const content = this.quill.root.innerHTML;
        this.$emit('update:content', content); // 同步内容到父组件
      });


      
      // 动态切换模式
      const toggleEditor = (isEnabled) => {
        this.quill.enable(isEnabled);
      };
      toggleEditor(this.quillEnable);
    },
    methods: {
      getContent() {
        // 获取编辑器内容
        return this.quill.root.innerHTML;
      },
      setContent(content) {
        // 设置编辑器内容
        this.quill.root.innerHTML = content;
      },
    },
    watch: {
      content:{
        handler(newVal){
          this.setContent(newVal)
        },
      }
    },
  };
  </script>
  
  <style>
  /* 你可以在这里覆盖 Quill 的默认样式 */
  </style>