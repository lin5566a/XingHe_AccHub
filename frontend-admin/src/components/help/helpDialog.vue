<template>
    <div class="form-box">
       <el-form ref="form" :model="templateForm" :rules="formRules" label-width="100px" size="small">
           <el-form-item  class="form-input" label="文档标题" prop="title">
               <el-input v-model="templateForm.title" placeholder="请输入模板名称" size="small"></el-input>
           </el-form-item> 
           <el-form-item  class="form-input" label="副标题" prop="subtitle">
               <el-input v-model="templateForm.subtitle" placeholder="请输入模板名称" size="small"></el-input>
           </el-form-item>   
           <el-form-item class="form-input" label="文档分类" prop="category">
               <el-select v-model="templateForm.category" placeholder="请选择适用分类" style="width: 100%;" clearable>
                   <el-option :label="item.name" :value="item.name" v-for="item in categoryOptions" :key="item.id"></el-option>
                   
               </el-select>
           </el-form-item>          
           <el-form-item label="文档内容" prop="content">
               <div class="editor-container">
                   <div class="editor-toolbar">
                       <el-button size="small" @click="previewContent">预览效果</el-button>
                   </div>
                   <QuillEditor ref="modelEditor" :content="templateForm.content" :quillEnable="true"></QuillEditor>
               </div>
            </el-form-item>
            <el-form-item label="排序" prop= "sort_order">
                <el-input-number class="form-input" v-model="templateForm.sort_order" :precision="0" :step="1" :min="1" ></el-input-number>
            </el-form-item>
            <el-form-item label="状态" prop= "status">
                <el-switch sizi="small"
                    v-model="templateForm.status"
                    :active-value="'1'"
                    :inactive-value="'0'">
                </el-switch>
                <span class="status-text ml8">{{ templateForm.status=='1' ? '已发布' : '未发布' }}</span>
                    
            </el-form-item>
       </el-form>       
   </div>
</template>
<script>
   import QuillEditor from '@/components/quillEditor.vue'
   export default {
       name: 'templateDialog',
       components:{
           QuillEditor 
       },
       props:{
           templateForm:{
               type:Object,
               required:true,
               default(){
                   return {}
               }
           },
           categoryOptions:{
                type:Array,
                required:true,
                default(){
                   return []
               }
           }
       },
       created(){
           this.$nextTick(()=>{
               this.$refs.modelEditor.setContent(this.templateForm.content);
           })
          
       },
       data(){
           return{
               formRules:{
                   title: [
                       { required: true, message: '请输入文档标题', trigger: 'blur' },
                       { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
                   ],
                   category: [
                       { required: true, message: '请选择文档分类', trigger: 'change' }
                   ],
                   content: [
                       { required: true, message: '请输入文档内容', trigger: 'blur' }
                   ]
               },
               content:''
           }
       },
       methods:{
           //预览内容
           previewContent(){
                let content = this.$refs.modelEditor.getContent();
                this.templateForm.content = content
                this.$emit('preview',content)
           },
           //获取编辑器内容
           getQuillContent(){
            
                let content = this.$refs.modelEditor.getContent();
                this.templateForm.content = content
                this.$refs.form.validate((valid) => {
                   if (valid) {
                       this.$emit("submitData")
                       return true
                   }
                   return false
               })
           }
       }
   }
</script>
<style lang="scss" scoped>
.form-box{
   .editor-container {
       border: 1px solid #dcdfe6;
       border-radius: 4px;
       overflow: hidden;
       .editor-toolbar {
           padding: 8px;
           border-bottom: 1px solid #dcdfe6;
           background-color: #f5f7fa;
           display: flex;
           gap: 10px;
           justify-content: flex-end;
           align-items: center;
       }
   }
   .form-input{
        width: 100%;
   }
}
  
</style>