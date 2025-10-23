<template>
     <div class="form-box">
        <el-form ref="form" :model="templateForm" :rules="formRules" label-width="100px" size="small">
            <el-form-item  class="form-input" label="模板名称" prop="template_name">
                <el-input v-model="templateForm.template_name" placeholder="请输入模板名称" size="small"></el-input>
            </el-form-item>   
            <el-form-item class="form-input" label="适用分类" prop="category_id">
                <el-select v-model="templateForm.category_id" placeholder="请选择适用分类" style="width: 100%;" clearable>
                    <el-option 
                        v-for="item in categoryOptions" 
                        :key="item.id" 
                        :label="item.name" 
                        :value="item.id">
                    </el-option>
                </el-select>
            </el-form-item>
           
            <el-form-item label="模板内容" prop="content">
                <div class="editor-container">
                    <div class="editor-toolbar">
                        <el-button size="small" @click="previewContent">预览效果</el-button>
                    </div>
                    <QuillEditor ref="modelEditor" :quillEnable="true" :content="templateForm.content"></QuillEditor>
                </div>
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
                required: true,
            }
        },
        created(){
            this.getCategoryList()
        },
        data(){
            return{
                formRules:{
                    template_name:[{ required: true, message: '请输入模板名称', trigger: 'blur' }],
                    category_id:[{ required: true, message: '请选择适用分类', trigger: 'change' }],
                    content:[{ required: true, message: '请输入模板内容', trigger: 'blur' }]
                },
                content:'',
                categoryOptions: []
            }
        },
        methods:{
            validateForm(callback){
                this.$refs['form'].validate((valid) => {
                    callback(valid);
                })
            
            },
            
            //获取商品分类列表
            getCategoryList(){
                this.$api.categoryList().then(res => {
                    if(res.code == 1){
                        this.categoryOptions = res.data.list
                    }else{
                        this.$message.error(res.msg || '获取商品分类失败')
                    }
                }).catch(err => {
                    console.error('获取商品分类失败:', err)
                    this.$message.error('获取商品分类失败')
                })
            },
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
}
   
</style>