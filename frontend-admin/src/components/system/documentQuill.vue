<template>
    <div class="document-editor-box">
        <div class="box-status">
            <span class="fb mr10">状态：</span>
            <el-switch v-model="isEnabled" :active-value="'1'" :inactive-value="'2'" @change="statusChange"></el-switch>
            <span class="status-text ml10">{{ isEnabled=='1' ? '已启用' : '已禁用' }}</span>
        </div>           
        <div class="editor-box">
            <div class="editor-title">文档内容:</div>
            <div>
                <!-- 工具栏 -->
                <Toolbar class="editor-tool" :editor="editor" :defaultConfig="toolbarConfig" />
                <!-- 编辑器 -->
                <Editor class="editor-container" v-model="content" :defaultConfig="editorConfig" @onCreated="onEditorCreated" />
            </div>
        </div>
        <div class="editor-footer">
            <div class="updateDate">最后更新时间：{{updateDate}}</div>
            <div class="document-btn">
                <el-button @click="resetDocument" size="small" :disabled="loading">重置</el-button>
                <el-button type="primary" @click="saveDocument" size="small" :disabled="loading">保存</el-button>
            </div>
        </div>
    </div>
</template>
<script>
import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
import '@wangeditor/editor/dist/css/style.css';
export default {
    name:"documentQuill",    
    components: { 
        Editor, 
        Toolbar 
    },
    props:{
        updateDate:{
            type:String,
            default:'2024-03-15 10:00:00'
        },
        editorType:{
            type:String,
            default:'use'
        },
        editorContent:{
            type:String,
            default:''
        },
        status:{
            type:[String, Number],
            default:''
        },
        loading:{
            type:Boolean,
            default:false
        }
    
    },
    data(){
        return{
            isEnabled:'',
            editor: null, // 编辑器实例
            content: '', // 编辑器内容
            toolbarConfig: {}, // 工具栏配置
            editorConfig: {
                placeholder: '请输入内容...', // 占位符
            },
        }
    },
    created(){
        this.isEnabled = this.status
    },
    methods:{
        statusChange(val){
            // console.log('val',val)
        },
        //重置
        resetDocument(){
            this.content = this.editorContent;
            
        },
        //保存
        saveDocument(){
            this.$emit('saveDocument',this.content,this.isEnabled,this.editorType)
        },
        onEditorCreated(editor) {
            this.editor = editor; // 保存编辑器实例
        },

    },
    watch:{
        editorContent:{
            handler(val,old){
                // console.log('val',val,'old',old)
                this.content = val
            },
            immediate: true,
        },
        status:{
            handler(val,old){
                this.isEnabled = val
            },
            immediate: true,
        }
    },
    beforeDestroy() {
        if (this.editor) {
            this.editor.destroy(); // 销毁编辑器实例
            this.editor = null;
        }
    },
}
</script>
<style lang="scss">
    .editor-tool{
        border:solid 1px #ccc;
        border-bottom: none;
    }
    .editor-container{
        height: 400px;
        border:solid 1px #ccc;
    }
</style>
<style lang="scss" scoped>

.document-editor-box{
    margin-top: 20px;
    border: 1px solid #e4e7ed;
    border-radius: 4px;
    padding: 20px;
    .box-status{
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 20px;
        height: 32px;
    }
    .editor-box{
        .editor-title{
            margin-bottom: 10px;
            font-weight: 700;
        }
    }
    .editor-footer{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        .updateDate{            
            color: #909399;
            font-size: 12px;
        }
        .document-btn{
            display: flex;
            gap: 10px;
        }
    }
}
</style>