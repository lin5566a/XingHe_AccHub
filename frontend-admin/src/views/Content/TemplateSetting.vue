<template>
     <div class="template-setting-box">
        <mainBoxHeader titleName="模板设置" :description="templateData.description" descriptionBgColor='#ecf8ff' descriptionBorderColor='#50bfff' descriptionBorderWidth='5px'>
            <template slot="oprBtn">
                <el-button class="f14" type="primary" @click="addTemplateData" size="small">新增模板</el-button>
            </template>
            <template slot="pageCon">
                <div class="table-box">
                    <el-table :data="table.dataTable" style="width: 100%" v-loading="templateData.loading" border stripe>
                        <el-table-column type="index" label="序号" width="60"></el-table-column>
                        <el-table-column prop="template_name" label="模板名称" min-width="180"></el-table-column>
                        <el-table-column prop="category" label="适用分类" min-width="150">
                            <template scope="scope">
                                <el-tag v-if="scope.row.category_id && scope.row.id!='1' && scope.row.category" size="small">{{ scope.row.category?scope.row.category.name:'' }}</el-tag>
                                <span v-else class="no-data">{{ scope.row.id=='1' ? '通用': (scope.row.category ? scope.row.category.name:'') }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="updated_at" label="更新时间" width="180"></el-table-column>
                        <el-table-column label="操作" width="250" fixed="right">
                            <template scope="scope">
                                <div class="action-buttons">
                                <el-button size="small" @click="preview(scope.row)">预览</el-button>
                                <el-button size="small" type="primary" @click="editData(scope.row)">编辑</el-button>
                                <el-button size="small" type="danger" @click="deleteData(scope.row)" :disabled="templateData.loading">删除</el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="pagination-box">
                    <el-pagination background :total="table.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="table.page"
                        :page-sizes="table.pageSizes"
                        :page-size="table.pageSize"
                        layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
            </template>
        </mainBoxHeader>
        <div class="dialog-box">
            <el-dialog title="模板预览"  :visible.sync="dialog.previewVisible"
                width="70%"
                custom-class="preview-class"
                :before-close="closePreviewDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
            <previewContainer :htmlStr="previewTemplateForm.content"></previewContainer>
            </el-dialog>
            
            <el-dialog title="新增模板"  :visible.sync="dialog.addVisible"
                width="70%"
                custom-class="preview-class"
                :before-close="closeAddDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <templateDialog v-if="dialog.showAdd" :templateForm="addTemplateForm" @preview="previewTemplate" ref="addTemplate"></templateDialog>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" size="small" @click="closeAddDialog">取消</el-button>
                        <el-button class="f14" size="small" type="primary" @click="addSuer" :disabled="templateData.loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
            <el-dialog title="编辑模板"  :visible.sync="dialog.editVisible"
                width="70%"
                custom-class="preview-class"
                :before-close="closeEditDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <templateDialog v-if="dialog.showEdit" :templateForm="editTemplateForm" @preview="previewTemplate" ref="editTemplate"></templateDialog>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" size="small" @click="closeEditDialog">取消</el-button>
                        <el-button class="f14" size="small" type="primary" @click="editSuer" :disabled="templateData.loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
        </div>
    </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
import previewContainer from '@/components/previewContainer.vue'
import templateDialog from '@/components/templateDialog.vue'
export default {
    name:'templateSetting',
    components:{
        mainBoxHeader,
        previewContainer,
        templateDialog
    },
    data(){
        return{
            templateData:{
                loading:false,
                description:'管理商品描述模板，您可以创建多个模板用于不同类型的商品，在添加或编辑商品时可以快速应用这些模板。'
            },
            table:{
                dataTable:[],
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1
            },
            dialog:{
                previewVisible:false,
                addVisible:false,
                editVisible:false,
                showEdit:false,
                showAdd:false,
                top:'15vh'
            },
            //预览数据
            previewTemplateForm:{},
            //新增模板参数
            addTemplateForm:{
                template_name:'',
                category_id:'',
                content:'',
            },
            //编辑模板参数            
            editTemplateForm:{},

        }
    },
    created(){
        this.templateList()
    },
    methods:{
        //获取模板列表
        templateList(){
            this.templateData.loading = true
            const params = {
                page: this.table.page,
                limit: this.table.pageSize
            }
            this.$api.templateList(params).then(res=>{
                if(res.code == 1){
                    this.table.dataTable = res.data.list
                    this.table.total = res.data.total
                }
            }).finally(() => {
                this.templateData.loading = false
            })
        },
        //分页
        handleCurrentChange(val){
            this.table.page = val
            this.templateList()
        },
        handleSizeChange(val){
            this.table.pageSize = val
            this.table.page = 1
            this.templateList()
        },
        //新增模板
        addTemplateData(){
            
            this.dialog.showAdd = true
            this.addTemplateForm = { 
                template_name:'',
                category_id:'',
                content:'',
            }
            this.dialog.addVisible = true
        },
        //列表内预览按钮点击事件
        preview(item){
            this.dialog.previewVisible = true
            this.previewTemplateForm = {...item}
        },
        //列表内编辑按钮点击事件
        editData(item){
            this.dialog.showEdit = true
            this.editTemplateForm = {...item}
            this.dialog.editVisible = true
        },
        //列表内删除按钮点击事件
        deleteData(item){
            if(item.id == '1'){
                this.$message.warning("通用模板不可删除")
                return
            }
            this.$confirm(`确定要删除模板"${item.template_name}"吗?`, '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                
                this.templateData.loading = true
                this.$api.templateDelete({id:item.id}).then(res=>{                    
                    this.templateData.loading = false
                    if(res.code == 1){
                        this.templateList()
                        this.$message.success(res.msg)
                    }else{
                        this.$message.error(res.msg)
                    }
                }).catch(e=>{
                    this.templateData.loading = false
                })
               
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
        //关闭预览弹窗
        closePreviewDialog(){
            this.previewTemplateForm={}
            this.dialog.previewVisible = false
        },
        
        //关闭新增弹窗
        closeAddDialog(){
            this.addTemplateForm = {
                template_name:'',
                category_id:'',
                content:'',
            }
            this.dialog.addVisible = false
            this.dialog.showAdd = false
        },
        //新增弹窗确定按钮点击事件
        addSuer(){
            this.$refs.addTemplate.getQuillContent()
            this.$refs.addTemplate.validateForm((valid) => {
                if (valid) {
                    this.addSubmitData()
                // 执行提交逻辑
                } else {
                    // this.$message.error('表单验证失败，请检查输入！');
                }
            })         
            
        },
        addSubmitData(){
            this.$refs.addTemplate.getQuillContent()
            let data ={
                "template_name": this.addTemplateForm.template_name,
                "category_id": this.addTemplateForm.category_id,
                "content": this.addTemplateForm.content
            }
            this.templateData.loading = true
            this.$api.templateAdd(data).then(res=>{
                this.templateData.loading = false
                if(res.code == 1){
                    this.templateList()
                    this.closeAddDialog()
                    this.$message.success(res.msg)
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{
                this.templateData.loading = false
                this.$message.error('新增失败')
            })
        },
        //关闭编辑模板弹窗
        closeEditDialog(){
            this.editTemplateForm = {}
            this.dialog.editVisible = false            
            this.dialog.showEdit = false
        },
        //编辑弹窗确定按钮点击事件
        editSuer(){
            this.$refs.editTemplate.getQuillContent()
            this.$refs.editTemplate.validateForm((valid) => {
                if (valid) {
                        // 执行提交逻辑
                    let data ={
                        "id": this.editTemplateForm.id,
                        "template_name": this.editTemplateForm.template_name,
                        "category_id": this.editTemplateForm.category_id,
                        "content": this.editTemplateForm.content,
                    }
                    this.templateData.loading = true
                    this.$api.templateEdit(data ).then(res=>{
                        this.templateData.loading = false
                        if(res.code == 1){
                            this.templateList()
                            this.closeEditDialog()
                            this.$message.success(res.msg)
                        }else{
                            this.$message.error(res.msg)
                        }
                    }).catch(err=>{
                        this.templateData.loading = false
                        this.$message.error('编辑失败')
                    })
                } else {
                    // this.$message.error('表单验证失败，请检查输入！');
                }
            }) 
        },    
        //预览编辑模板        
        previewTemplate(content){
            this.dialog.previewVisible = true
            this.previewTemplateForm.content = content
            this.$forceUpdate()
        }
    }
}
</script>
<style lang="scss">
/* 预览样式 */

</style>
<style lang="scss" scoped>
.template-setting-box{
    .no-data{
        color: #909399;
        font-size: 12px;
        font-style: italic;
    }

}

</style>