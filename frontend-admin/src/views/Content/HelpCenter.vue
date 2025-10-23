<template>
    <div class="help-center-box">
       <mainBoxHeader titleName="文档设置" :description="helpData.description" descriptionBgColor='#ecf8ff' descriptionBorderColor='#50bfff' descriptionBorderWidth='5px' >
           <template slot="oprBtn">
               <el-button class="f14" type="primary" @click="addDocument" size="small">新增文档</el-button>
           </template>
           <template slot="pageCon">
                <div class="query-box">
                    <div class="query-item">
                        <span class="query-label mr12">文档标题</span>
                        <el-input class="query-input" placeholder="请输入文档标题" v-model="query.title" clearable size="small"></el-input>
                    </div>

                    <div class="query-item">
                        <span class="query-label mr12">文档分类</span>
                        <el-select class="query-select" v-model="query.category" clearable placeholder="请选择文档分类" size="small" @change="changeCategory">
                            <el-option label="全部" value="all"></el-option>
                            <el-option :label="item.name" :value="item.name" v-for="item in helpData.tabPane" :key="item.id"></el-option>
                        </el-select>   
                    </div>
                    
                    <div class="query-item">
                        <span class="query-label mr12">状态</span>
                        <el-select class="query-select" v-model="query.status" clearable placeholder="请选择" size="small">
                            <el-option label="全部" value="0"></el-option>
                            <el-option label="已发布" value="1"></el-option>
                            <el-option label="未发布" value="2"></el-option>
                        </el-select>   
                    </div>
                   
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="query.loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="query.loading">重置</el-button>
                    </div>
                </div>
                <template>
                    <el-tabs class="help-doc-tabs" v-model="helpData.activeName" @tab-click="tabClick">
                        <el-tab-pane label="全部文档" name="all">
                        </el-tab-pane>
                        <el-tab-pane :label="item.name" :name="item.name"  v-for="item in helpData.tabPane" :key="item.id">                            
                        </el-tab-pane>
                      
                    </el-tabs>
                    <div class="help-table-box">
                        <helpTable ref="allHelpTabs" :loading='query.loading' :dataTable="table.dataTable" @statusChange= "statusChange" @editData="editData" @deleteData ="deleteData" @preview="previewTable"></helpTable>
                    </div>
                    <div class="pagination-box">
                        <el-pagination background :total="query.total"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page="query.page"
                            :page-sizes="query.pageSizes"
                            :page-size="query.pageSize"
                            :disabled="query.loading"
                            layout="total, sizes, prev, pager, next, jumper">
                        </el-pagination>
                    </div>
                    
                </template>
               
           </template>
       </mainBoxHeader>
        <div class="dialog-box">
            <el-dialog title="模板预览"  :visible.sync="dialog.previewVisible"
                width="800px"
                custom-class="preview-class"
                :before-close="closePreviewDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <previewContainer :htmlStr="previewTemplateForm.content"></previewContainer>
            </el-dialog>

            <el-dialog title="新增文档"  :visible.sync="dialog.addVisible"
                width="800px"
                custom-class="preview-class"
                :before-close="closeAddDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <helpDialog v-if="dialog.showAdd" :templateForm="addTemplateForm" @preview="previewTemplate" @submitData="addSubmitData" :categoryOptions="helpData.tabPane" ref="addTemplate"></helpDialog>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" size="small" @click="closeAddDialog" :disabled="query.loading">取消</el-button>
                        <el-button class="f14" size="small" type="primary" @click="addSuer" :disabled="query.loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
            <el-dialog title="编辑文档"  :visible.sync="dialog.editVisible"
                width="800px"
                custom-class="preview-class"
                :before-close="closeEditDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <helpDialog v-if="dialog.showEdit" :templateForm="editTemplateForm" @preview="previewTemplate" @submitData="editSubmitData"  :categoryOptions="helpData.tabPane" ref="editTemplate"></helpDialog>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" size="small" @click="closeEditDialog" :disabled="query.loading">取消</el-button>
                        <el-button class="f14" size="small" type="primary" @click="editSuer" :disabled="query.loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
        </div>
   </div>
</template>

<script>
    import mainBoxHeader from '@/components/mainBoxHeader.vue'
    import helpTable from '@/components/help/helpTable.vue'
    import helpDialog from '@/components/help/helpDialog.vue'
    import previewContainer from '@/components/previewContainer.vue'
    export default {
        name:'helpCenter',
        components:{
            mainBoxHeader,
            helpTable,
            helpDialog,
            previewContainer
        },
        data(){
            return{
                helpData:{
                    description:'管理网站前台显示的文档内容，您可以添加、编辑、删除文档，并设置文档的排序和显示状态。',
                    activeName:'all',//all 全部  account账号相关  serve服务相关   education教育指南
                    tabPane:[],//分类列表  账号相关  服务相关  教育指南
                },
                query:{
                    id:'',
                    title:'', // 文档标题
                    category:'', // 文档分类
                    status:'', // 状态
                    pageSizes:[10,20,50,100],
                    pageSize:10,
                    total:0,
                    page:1,
                    loading:false
                },
                table:{
                    dataTable:[]
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
                    title:'',
                    subtitle:'',
                    category:'',
                    content:'',
                    sort_order:1,
                    status:'1'
                },
                //编辑模板参数            
                editTemplateForm:{},
            }
        },
        created(){
            this.helpCategories()
            this.helpDocumentList()
        },
        methods:{
            //获取分类列表
            helpCategories(){
                this.$api.helpCategories().then(res=>{
                    if(res.code == 1){
                        this.helpData.tabPane = res.data
                    }
                })
            },
            //获取文档列表
            helpDocumentList(){
                let data = {
                    page: this.query.page,
                    limit: this.query.pageSize,
                    title: this.query.title,
                    category: this.query.category == 'all' ? '' : this.query.category,
                    status: this.query.status
                }
                this.query.loading = true
                this.$api.helpDocumentList(data).then(res => {
                    this.query.loading = false
                    if(res.code == 1){
                        this.table.dataTable = res.data.list.map(item => ({
                            ...item,
                            statusBool: item.status === '1' // 转换状态为布尔值
                        }))
                        this.query.total = res.data.total
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch(err => {
                    this.query.loading = false
                    this.$message.error('获取列表失败')
                })
            },
            //查询按钮点击事件
            getData(){
                this.query.page = 1
                this.helpData.activeName = this.query.category === '' ? 'all' : this.query.category
                this.helpDocumentList()
            },
            //重置按钮点击事件
            reset(){
                this.query = {
                    ...this.query,
                    title: '',
                    category: '',
                    status: '',
                    page: 1
                }
                this.helpData.activeName = 'all'
                this.helpDocumentList()
            },
            //表格内切换状态事件
            statusChange(val,item){
                let status = val;
                const oldStatus = val=='1'?'0':'1'
                item.status = oldStatus // 先恢复原状态
                // console.log(val,'val','oldStatus',oldStatus)
                this.query.loading = true
                const data = {
                    id: item.id,
                    status: status,
                    title: item.title,
                    subtitle: item.subtitle,
                    category: item.category,
                    content: item.content,
                    sort_order: item.sort_order,
                }
                this.$api.helpDocumentEdit(data).then(res => {
                    this.query.loading = false
                    if(res.code === 1) {
                        this.$message.success(res.msg)
                        item.status = status // 请求成功后再更新状态
                        this.helpDocumentList() // 刷新列表
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch(err => {
                    this.query.loading = false
                    this.$message.error('状态修改失败')
                })
            },
            //表格编辑事件
            editData(item){                
                this.dialog.showEdit = true
                this.editTemplateForm = {...item}
                this.dialog.editVisible = true
            },
            //表格删除事件
            deleteData(item){
                this.$confirm(`确定要删除文档"${item.title}"吗？`, '警告', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.query.loading = true
                    this.$api.helpDocumentDelete({id: item.id}).then(res => {
                        this.query.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.query.page = 1
                            this.helpDocumentList()
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.query.loading = false
                        this.$message.error('删除失败')
                    })
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });          
                });
            },
            //分页
            handleCurrentChange(val){
                this.query.page = val
                this.helpDocumentList()
            },
            handleSizeChange(val){
                this.query.pageSize = val
                this.query.page = 1
                this.helpDocumentList()
            },
            //addDocument 新增文档
            addDocument(){
                this.dialog.showAdd = true
                this.addTemplateForm = { 
                    title:'',
                    subtitle:'',
                    category:'',
                    content:'',
                    sort_order:1,
                    status:'1'
                }
                this.dialog.addVisible = true
            },
            //切换分类事件
            changeCategory(tab){
                // console.log(tab,'====')
                // this.query.category = tab === 'all' ? '' : tab
            },
            //切换tabs标签事件   查询数据
            tabClick(tab){
                this.query.category = tab.name === 'all' ? '' : tab.name
                this.helpDocumentList()
            },            
            //关闭新增弹窗
            closeAddDialog(){
                this.addTemplateForm = {
                    title:'',
                    subtitle:'',
                    category:'',
                    content:'',
                    sort_order:1,
                    status:'1'
                }
                this.dialog.addVisible = false
                this.dialog.showAdd = false
            },
            //新增弹窗确定按钮点击事件
            addSuer(){      
                this.$refs.addTemplate.getQuillContent()
            },
            addSubmitData(){
                const data = {
                    title: this.addTemplateForm.title,
                    subtitle: this.addTemplateForm.subtitle,
                    category: this.addTemplateForm.category,
                    content: this.addTemplateForm.content,
                    sort_order: this.addTemplateForm.sort_order,
                    status: this.addTemplateForm.status
                }
                this.query.loading = true
                this.$api.helpDocumentAdd(data).then(res => {
                    this.query.loading = false
                    if(res.code === 1) {
                        this.$message.success(res.msg)
                        this.closeAddDialog()
                        this.helpDocumentList()
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch(err => {
                    this.query.loading = false
                    this.$message.error('新增失败')
                })
            },
            //预览编辑模板        
            previewTemplate(content){
                this.dialog.previewVisible = true
                this.previewTemplateForm.content = content
                this.$forceUpdate()
            },
            //关闭预览弹窗
            closePreviewDialog(){
                this.previewTemplateForm={}
                this.dialog.previewVisible = false
            },
            //关闭编辑模板弹窗
            closeEditDialog(){
                this.editTemplateForm = {}
                this.dialog.editVisible = false            
                this.dialog.showEdit = false
            },
            //编辑弹窗确定按钮点击事件
            editSuer(){
                this.$refs.editTemplate.getQuillContent();               
            },
            //编辑提交数据
            editSubmitData(){
                // console.log(this.editTemplateForm.status,'this.editTemplateForm.status')
                    const data = {
                        id: this.editTemplateForm.id,
                        title: this.editTemplateForm.title,
                        subtitle: this.editTemplateForm.subtitle,
                        category: this.editTemplateForm.category,
                        content: this.editTemplateForm.content,
                        sort_order: this.editTemplateForm.sort_order,
                        status: this.editTemplateForm.status
                    }
                    this.query.loading = true
                    this.$api.helpDocumentEdit(data).then(res => {
                        this.query.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.closeEditDialog()
                            this.helpDocumentList()
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.query.loading = false
                        this.$message.error('编辑失败')
                    })
            },
            //表格内预览按钮点击事件    
            previewTable(item){
                this.previewTemplate(item.content)
            }
        }
    }
</script>
<style lang="scss" scoped>
    .help-center-box{
        .query-box{
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            .query-item{
                display: flex;
                align-items: center;        
                margin-right: 32px;
                font-size: 14px;
                margin-bottom: 18px;
                color:#606266;
                .query-input{
                    width: 192px;
                }
                .query-select{
                    width: 168px;
                }
            }
        }
        .help-doc-tabs{
            margin-bottom: 20px;
        }
    }
</style>