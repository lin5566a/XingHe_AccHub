<template>
    <div class="page-dialog-box">
       <mainBoxHeader titleName="首页弹窗内容设置" :noDescription="true" >
           <template slot="oprBtn">
            <el-switch v-model="pageDialogData.form.status" :active-value = "1" :inactive-value = "0" active-text="启用" inactive-text="停用" :disabled="pageDialogData.loading" @change="handleStatusChange"></el-switch>
           </template>
           <template slot="pageCon">
                <el-form ref="form" :model="pageDialogData.form" label-width="90px" :rules="pageDialogData.rules">
                    <el-form-item label="弹窗标题" prop="title">
                        <el-input v-model="pageDialogData.form.title" placeholder="请输入弹窗标题" maxlength="50" show-word-limit></el-input>
                    </el-form-item>
                    <el-form-item label="弹窗内容" prop="content">
                        <div>
                            <!-- 工具栏 -->
                            <Toolbar class="editor-tool" :editor="pageDialogData.editor" :defaultConfig="pageDialogData.toolbarConfig" />
                            <!-- 编辑器 -->
                            <Editor class="editor-container" v-model="pageDialogData.form.content" :defaultConfig="pageDialogData.editorConfig" :key="pageDialogData.editorKey" @onCreated="onEditorCreated" />
                        </div>
                    </el-form-item>
                    <el-form-item>
                        <el-button size="small" @click="handleReset">重 置</el-button>
                        <el-button size="small" type="primary" @click="handleSave" :loading="pageDialogData.loading">保存设置</el-button>
                        <el-button size="small" @click="handlePreview">预览弹窗</el-button>
                    </el-form-item>
                </el-form>
            </template>
       </mainBoxHeader>
       
       <!-- 预览弹窗 -->
       <el-dialog
         :title="pageDialogData.form.title"   
         :visible.sync="previewDialog.visible"
         width="600px"
         :close-on-click-modal="false"
       >
         <div class="preview-content">
           <!-- <div class="preview-title">{{ }}</div> -->
           <div class="preview-body" v-html="pageDialogData.form.content "></div>
         </div>
         <span slot="footer" class="dialog-footer">
           <el-button size="small" @click="previewDialog.visible = false">关 闭</el-button>
         </span>
       </el-dialog>
   </div>
</template>

<script>

import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
import '@wangeditor/editor/dist/css/style.css';
import mainBoxHeader from '@/components/mainBoxHeader.vue';

    export default {
        name: 'DialogManage',
        components:{
            Editor, 
            Toolbar,
            mainBoxHeader
        },
        data(){
            return{
                pageDialogData:{
                    form:{
                        title:"",
                        content: '', // 编辑器内容
                        status:1,
                    },                  
                    loading: false,
                    editorKey: 0, // 编辑器重新创建标识
                    rules: {
                        title: [
                            { required: false, message: '请输入弹窗标题', trigger: 'blur' },
                            { min: 1, max: 10, message: '标题长度在 1 到 10 个字符', trigger: 'blur' }
                        ],
                        content: [
                            { required: false, message: '请输入弹窗内容', trigger: 'blur' }
                        ]
                    },
                    editor: null, // 编辑器实例  
                    toolbarConfig: {
                        excludeKeys: [
                            'fullScreen', // 排除全屏
                            'group-video', // 排除视频
                            'group-image', // 排除图片
                        ]
                    }, // 工具栏配置
                    editorConfig: {
                        placeholder: '请输入弹窗内容 (支持富文本、图片、链接)', // 占位符
                        MENU_CONF: {
                            // 配置字体
                            fontFamily: {
                                fontFamilyList: [
                                    '黑体',
                                    '楷体',
                                    '仿宋',
                                    '微软雅黑',
                                    'Arial',
                                    'Tahoma',
                                    'Verdana'
                                ]
                            },
                            // 配置字号
                            fontSize: {
                                fontSizeList: ['12px', '13px', '14px', '16px', '18px', '20px', '24px', '28px', '32px']
                            }
                        }
                    },
                },
                previewDialog: {
                    visible: false
                },
                originalData: {
                    title: "",
                    content: '',
                    status: 1,
                }
            }
        },
        created(){
            this.getDialogData();
        },
        mounted(){
            // 如果没有数据，提供示例内容
            this.$nextTick(() => {
                if (!this.pageDialogData.form.title && !this.pageDialogData.form.content) {
                    // this.initExampleData();
                }
            });
        },
        beforeDestroy() {
            // 组件销毁时，及时销毁编辑器
            if (this.pageDialogData.editor) {
                this.pageDialogData.editor.destroy();
            }
        },
        methods:{
            onEditorCreated(editor) {
                this.pageDialogData.editor = editor; // 保存编辑器实例
            },
            
            // 获取弹窗数据
            getDialogData() {
                this.pageDialogData.loading = true;
                this.$api.getDialogData().then(res => {
                    this.pageDialogData.loading = false;
                    // console.log('获取弹窗数据响应:', res);
                    if(res.code == 1) {
                        this.pageDialogData.form = {
                            title: res.data.title ,
                            content: res.data.content,
                            status: res.data.status
                        };
                        // 保存原始数据用于重置
                        this.originalData = { ...this.pageDialogData.form };
                        // console.log('弹窗数据加载完成:', this.pageDialogData.form);
                    } else {
                        this.$message.error(res.msg );
                    }
                }).catch(err => {
                    this.pageDialogData.loading = false;
                    // console.error('获取弹窗数据失败:', err);
                    this.$message.error('err');
                });
            },
            
            // 保存弹窗数据
            handleSave() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.pageDialogData.loading = true;
                        const data = {
                            title: this.pageDialogData.form.title,
                            content: this.pageDialogData.form.content,
                            status: this.pageDialogData.form.status
                        };
                        
                        // console.log('保存弹窗数据:', data);
                        
                        this.$api.saveDialogData(data).then(res => {
                            this.pageDialogData.loading = false;
                            // console.log('保存响应:', res);
                            if(res.code == 1) {
                                this.$message.success(res.msg || '保存成功');
                                // 更新原始数据
                                this.originalData = { ...this.pageDialogData.form };
                            } else {
                                this.$message.error(res.msg || '保存失败');
                            }
                        }).catch(err => {
                            this.pageDialogData.loading = false;
                            // console.error('保存弹窗数据失败:', err);
                            this.$message.error('保存失败，请检查网络连接');
                        });
                    } else {
                        // this.$message.error('请检查表单内容');
                        return false;
                    }
                });
            },
            
            // 预览弹窗
            handlePreview() {
                if (!this.pageDialogData.form.title && !this.pageDialogData.form.content) {
                    this.$message.warning('请先输入标题或内容');
                    return;
                }
                this.previewDialog.visible = true;
            },
            
            // 重置表单
            handleReset() {
                this.$confirm('确定要重置所有内容吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    // 先重置表单数据
                    this.pageDialogData.form = { ...this.originalData };
                    
                    // 完全重新创建编辑器实例
                    this.recreateEditor();
                    
                    this.$message.success('重置成功');
                }).catch(() => {
                    this.$message.info('已取消重置');
                });
            },
            
            // 重新创建编辑器
            recreateEditor() {
                // 销毁现有编辑器
                if (this.pageDialogData.editor) {
                    try {
                        this.pageDialogData.editor.destroy();
                    } catch (error) {
                        console.warn('销毁编辑器时出现错误:', error);
                    }
                    this.pageDialogData.editor = null;
                }
                
                // 更新编辑器key，强制重新创建组件
                this.pageDialogData.editorKey += 1;
            },
            
            // 状态改变处理
            handleStatusChange(value) {                
                // 自动保存状态改变
                if (this.pageDialogData.form.title || this.pageDialogData.form.content) {
                    let oldStatus = value == 1 ? 0: 1;
                    this.pageDialogData.form.status = oldStatus
                    let status = value
                    this.updateDialogStatus(status)
                } else {
                }
            },
            // 状态改变接口
            updateDialogStatus(status){
                this.$api.updateDialogStatus({status:status}).then(res => {
                    if(res.code == 1) {
                        this.getDialogData()
                        this.$message.success(res.msg);
                    } else {
                        this.$message.error(res.msg);
                    }
                }).catch(err => {
                    console.error('状态更新失败:', err);
                })
            },
          
        }
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
    
    // 预览弹窗样式
    .preview-content {
        .preview-title {
            font-size: 18px;
            font-weight: bold;
            color: #303133;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #409eff;
        }
        
        .preview-body {
            line-height: 1.6;
            color: #606266;
            
            // 富文本内容样式
            p {
                margin-bottom: 10px;
            }
            
            h1, h2, h3, h4, h5, h6 {
                margin: 15px 0 10px 0;
                color: #303133;
            }
            
            ul, ol {
                margin: 10px 0;
                padding-left: 20px;
            }
            
            li {
                margin-bottom: 5px;
            }
            
            blockquote {
                margin: 15px 0;
                padding: 10px 15px;
                background-color: #f5f7fa;
                border-left: 4px solid #409eff;
                color: #606266;
            }
            
            code {
                background-color: #f5f7fa;
                padding: 2px 4px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
            
            pre {
                background-color: #f5f7fa;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
                margin: 15px 0;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                
                th, td {
                    border: 1px solid #dcdfe6;
                    padding: 8px 12px;
                    text-align: left;
                }
                
                th {
                    background-color: #f5f7fa;
                    font-weight: bold;
                }
            }
        }
    }
</style>
<style lang="scss" scoped>
    .page-dialog-box{
        .el-form-item {
            margin-bottom: 20px;
        }
       
        .el-button {
            margin-right: 10px;
        }
    }
</style>