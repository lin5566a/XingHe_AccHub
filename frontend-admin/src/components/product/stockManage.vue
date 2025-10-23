<template>
    <div class="stock-manage-box">
        <el-drawer
        title="Gmail邮箱-稳定可用 (手工) - 库存管理"
        :visible.sync="drawerVisible"
        direction="rtl"
        :with-header="true"
        size="90%"
        :before-close="closeDrawer">
            <div class="stock-manage">
                <div class="stock-tip">
                    管理改商品的库存账号，包括添加新库存、导入库存、编辑和删除等操作。
                </div>
                <div class="operation-buttons">
                    <el-button class="f14" type="primary" @click="batchImport" size="small">批量导入</el-button>
                    <el-button class="f14" type="success" @click="addStock" size="small">新增库存</el-button>
                    <el-button class="f14" type="warning" @click="batchEditCost" size="small">批量修改成本</el-button>
                </div>
                <div class="query-box">
                    <el-form :inline="true" :model="formData" class="demo-form-inline" size="small">  
                        <el-form-item label="卡密ID" >
                            <el-input class='mr32' style="width:190px" type="text" v-model="formData.stock_id" placeholder="请输入卡密ID"></el-input>
                        </el-form-item>
                        <el-form-item label="批次ID" >
                            <el-input class='mr32' style="width:190px" type="text" v-model="formData.batch_id" placeholder="请输入批次ID"></el-input>
                        </el-form-item>
                        
                        <el-form-item label="销售状态">
                            <el-select style="width: 158px;" v-model="formData.status" placeholder="请选择" clearable size="small">
                                <el-option label="已售出" :value="2"></el-option>
                                <el-option label="已锁定" :value="1"></el-option>
                                <el-option label="未售出" :value="0"></el-option>
                                <el-option label="已作废" :value="3"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-button class="f14" type="primary" @click="getData" size="small">查询</el-button>
                            <el-button class="f14" @click="resetForm" size="small">重置</el-button>
                        </el-form-item>
                    </el-form>
                </div>
                <div class="table-box">
                    <el-table ref="multipleTable" :data="table.tableData" style="width: 100%" v-loading="table.loading" border stripe @selection-change="handleSelectionChange" size="small">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column prop="id" label="卡密ID" width="120"></el-table-column>
                        <el-table-column prop="batch_id" label="批次ID" width="120"></el-table-column>                    
                        <el-table-column prop="status" label="状态" width="100">
                        <template scope="scope">
                                <!-- 已作废  红色 -->
                            <el-tag :type="scope.row.status == 0 ? 'success' : scope.row.status == 1 ?'warning':scope.row.status == 3?'danger' : 'info'">
                            {{ scope.row.status == 0 ? '未售出' : scope.row.status == 1?'已锁定': scope.row.status == 3 ?'已作废':'已售出' }}
                            </el-tag>
                        </template>
                        </el-table-column>
                        <el-table-column prop="cardInfo" label="卡密信息" min-width="200">
                        <template scope="scope">
                            <el-button class="f14" link type="primary" @click="showCardInfo(scope.row)" size="small">查看</el-button>
                        </template>
                        </el-table-column>
                        <el-table-column prop="cost_price" label="成本价">
                            <template scope="scope">
                                <span v-if="scope.row.cost_price" >¥{{scope.row.cost_price}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="创建时间" width="180"></el-table-column>
                        <el-table-column prop="remark" label="备注" min-width="120"></el-table-column>
                        <el-table-column label="操作" width="150" fixed="right">
                        <template scope="scope">
                            <el-button class="f14" size="small" type="primary" @click="handleEditInventoryItem(scope.row)">编辑</el-button>
                            <el-button class="f14" size="small" type="danger" @click="handleDeleteInventoryItem(scope.row)" :disabled="table.loading">删除</el-button>
                        </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="stock-footer">
                    <div class="stock-batch-operate">
                        <el-button class="f14" type="success" @click="exportData" size="small" :disabled="table.loading || drawer.ids.length <= 0">导出数据</el-button>
                        <el-button class="f14" type="danger" @click="batchDeleteData" size="small" :disabled="table.loading || drawer.ids.length <= 0">批量删除</el-button>
                    </div>
                    <div class="pagination-box">
                        <el-pagination background :total="table.total"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page="table.page"
                            :page-sizes="table.pageSizes"
                            :page-size="table.pageSize"
                            :disabled="table.loading"
                            layout="total, sizes, prev, pager, next, jumper">
                        </el-pagination>
                    </div>
                </div>
            </div>
        </el-drawer>
        <div class="dialog-box">
            <el-dialog
            title="批量导入库存"
            :visible.sync="importData.importVisible"
            width="500px"
            custom-class="import-dialog-class"
            :before-close="closeImportDialog"
            :close-on-click-modal="false"            
            >
                <div class="dialog-box-con">
                    <el-form :model="importData.importForm" ref="importForm" :rules="importRules" label-width="100px" size="small">
                        <el-form-item label="批次ID" prop="batch_id">
                            <el-input type="text" v-model="importData.importForm.batch_id" :disabled="true" placeholder="请输入批次ID"></el-input>
                            <div class="form-tip">批次ID格式：P+日期+批次号，例如：P20250213001</div>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input type="textarea" v-model="importData.importForm.remark" :rows="2" placeholder="请输入备注信息"></el-input>
                        </el-form-item>
                        <el-form-item label="成本价" prop="cost_price">
                            <el-input-number class="form-input" v-model="importData.importForm.cost_price" :precision="2" :step="0.01" :min="0" size="small"></el-input-number>
                            <div class="form-tip">为所有导入的卡密设置成本价</div>
                        </el-form-item>
                    </el-form>
                    <div class="import-description">
                        <h4>导入说明：</h4>
                        <ol>
                            <li>请按照以下格式准备txt文件：每行一个卡密</li>
                            <li>每行一个卡密信息（例如：username----password----email----emailpass）</li>
                            <li>成本价设置（必填）：将为所有导入的卡密设置统一成本价</li>
                            <li>支持txt文件导入，文件大小不超过2MB</li>
                            <li>导入时将自动过滤重复的卡密信息</li>
                        </ol>
                    </div>
                    <el-upload
                        class="import-uploader"
                        drag
                        action="#"
                        :auto-upload="false"
                        :on-change="handleImportFileChange"
                        :limit="1"
                        accept=".txt,.csv,.xlsx,.xls"
                        :file-list="importData.fileList"
                        @clearFiles="clearFiles"
                    >
                        <div class="upload-content">
                            <el-icon class="f48 mb10 gray-icon-color el-icon-upload2"><Upload /></el-icon>
                            <div class="upload-text">将文件拖到此处，或<em>点击上传</em></div>
                        </div>
                        <div class="upload-tip">支持 .txt,.csv,.xlsx,.xls 格式文件，且不超过 2MB</div>
                    </el-upload>
                </div>
                <template slot=footer>
                    <span class="dialog-footer">
                    <el-button class="f14" @click="closeImportDialog" size="small" :loading="importData.importing">取消</el-button>
                    <el-button class="f14" type="primary" @click="submitImportInventory" :loading="importData.importing" size="small">
                        {{ importData.importing ? '导入中...' : '开始导入' }}
                    </el-button>
                    </span>
                </template>
            </el-dialog>
           <stockEdit ref="addStockRules" :dialogVisible = addStockData.visible :formData="addStockData.formData" :product_id = "product_id" @submitStock = "submitAddStock" @stockDialogClose="addStockDialogClose" title="新增库存"></stockEdit>
           <stockEdit ref="editStockRules" :dialogVisible = editStockData.visible :formData="editStockData.formData" :product_id = "editStockData.id" @submitStock = "submitEditStock" @stockDialogClose="editStockDialogClose" title="编辑库存"></stockEdit>
           <batchEditCost
                :visible.sync="batchData.batchEditCostVisible"
                :loading="batchData.batchEditCostLoading"
                :form="batchData.batchEditCostForm"
                :batchList="batchData.batchList"
                @close="batchEditCostclose"
                @sure="handleBatchEditCost"
            />
        </div>
    </div>
</template>
<script>
import stockEdit from './stockEdit.vue'
import batchEditCost from './batchEditCost.vue'
export default {
    data(){
        return{
            drawer:{
                ids:[],
            },
            formData:{
                status:'',
                stock_id:'',
                batch_id:'',
            },
            table:{
                tableData:[],
                loading:false,
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
            },
            //批量导入
            importData:{
                importing:false,
                importVisible:false,
                importForm:{
                    remark:'',
                    batch_id:'',
                    cost_price:0
                },
                importFile:null,//导入文件file
                fileList:[]
            },
            //新增库存
            addStockData:{
                visible:false,
                formData:{
                    card_info:'',
                    status:0,
                    remark:'',
                    batch_id : '',
                    cost_price : 0,
                }
            },
            
            //编辑库存
            editStockData:{
                visible:false,
                id:'',
                formData:{
                    card_info:'',
                    status:0,
                    remark:'',                    
                    cost_price :0,
                    batch_id : ''
                }
            },
            importRules:{
                batch_id:[
                    { required: false, message: '', trigger: 'blur' }
                ],
                remark:[
                    { required: false, message: '', trigger: 'blur' }
                ],

                cost_price:[
                    { required: true, message: '请输入成本价', trigger: 'blur' }
                ],
            },
            //批量修改成本价
            batchData:{
                batchEditCostVisible: false,
                batchEditCostLoading: false,
                batchEditCostForm: {
                    batch_id: '',
                    cost_price: 0,
                    remark: ''
                },
                batchList: [],
            },
            
        }
    },
    components:{
        stockEdit,
        batchEditCost
    },
    props:{
        drawerVisible:{
            type:Boolean,
            default:false
        },
        product_id:{
            type:String,
            default:''
        }
    },
    created(){
        // console.log('productStockList')
        this.productStockList()
    },
    methods:{
        closeDrawer(){
            this.$emit('closeDrawer')
        },
        //模糊查询批量id
        searchBatchIds(keyword){
            this.$api.searchBatchIds({keyword:keyword,product_id:this.product_id}).then(res=>{
                if(res.code == 1) {

                    this.batchData.batchList = res.data.list.map(item=>{
                        item.label = item.batch_id+`(${item.count}张卡密)`
                        return item
                    })
                    
                }else{

                }
            })
        },
        //批量导入 按钮点击事件
        async batchImport(){
            this.importData.importVisible = true;
            const batch_id = await this.getBatchId();
            this.importData.importForm.batch_id = batch_id;
            this.importData.importFile = null;
        },
        //获取批次id
        async getBatchId(){
            let res = await this.$api.getBatchId()
            if(res.code == 1){
                let batch_id = res.data.batch_id
                console.log(batch_id)
                return batch_id
            }else{
                this.$message.error(res.msg)
                return ''
            }
        },
        //新增库存按钮点击事件
        async addStock(){
            this.addStockData.visible = true;
            const batch_id = await this.getBatchId();
            this.addStockData.formData.batch_id = batch_id;
        },
        //库存查询接口
        productStockList(){
            let data ={
                product_id:this.product_id,
                status:this.formData.status,
                stock_id:this.formData.stock_id,
                batch_id:this.formData.batch_id,
                page:this.table.page,
                limit:this.table.pageSize
            }
            this.table.loading = true;
            this.$api.productStockList(data).then(res=>{
                this.table.loading = false;
                if(res.code === 1) {
                    this.table.tableData = res.data.list;
                    this.table.total = res.data.total;
                }
            }).catch(e=>{
                this.table.loading = false; 
                this.table.tableData = [];
            })
        },
        //查询数据列表
        getData(){
            this.table.page = 1;
            this.productStockList()
        },
        //重置
        resetForm(){
            this.formData.status=''
            this.formData.stock_id = ''
            this.formData.batch_id = ''
            this.table.page = 1;
            this.productStockList()
        },

     
        //表格内编辑事件
        handleEditInventoryItem(item){
            this.editStockData.formData.card_info = item.card_info
            this.editStockData.formData.status = item.status
            this.editStockData.formData.remark = item.remark
            this.editStockData.formData.cost_price = item.cost_price
            this.editStockData.formData.batch_id = item.batch_id
            this.editStockData.id = item.id
            this.editStockData.visible = true;
        },
        //表格内删除事件
        handleDeleteInventoryItem(item){
            this.$confirm('确定要删除这条库存记录吗？', '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.table.loading = true;
                this.$api.productStockDelete({id:item.id}).then(res=>{
                    this.table.loading = false;
                    if(res.code == 1){
                        this.$message.success(res.msg)
                        this.table.page = 1;
                        this.productStockList()
                        this.$emit("updateStock")
                    }
                }).catch(err=>{
                    this.table.loading = false;
                })
               
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
        //导出数据按钮事件
        exportData(){
            this.table.loading = true;
            this.$api.productStockBatchExport({ids:this.drawer.ids}).then(res=>{
                this.table.loading = false;
                if(res.code == 1){                    
                    let url = res.data.url;
                    window.open(this.$baseURL + url,'_blank');
                    this.$message({
                        type: 'success',
                        message: res.msg
                    });                    
                    this.drawer.ids = []                    
                    this.$refs.multipleTable.clearSelection();
                }else{
                    this.$message({
                        type: 'error',
                        message: res.msg
                    });
                }
            }).catch(err=>{
                this.table.loading = false;
            })
        },
        //关闭批量导入弹窗
        closeImportDialog(){
            this.importData.importVisible = false
            this.importData.importFile = null
            this.importData.importForm.batch_id = ''
            this.importData.importForm.remark = ''
            this.importData.importForm.cost_price = 0
            this.importData.fileList = []; 
            this.$refs['importForm'].resetFields()
        },
        //批量删除数据按钮事件
        batchDeleteData(){
            this.$confirm('确定要删除这条库存记录吗？', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                let data={
                    ids:this.drawer.ids
                }
                this.table.loading = true;
                this.$api.productStockBatchDelete(data).then(res=>{
                    this.table.loading = false;
                    if(res.code == 1){
                        this.$message({
                            type: 'success',
                            message: res.msg
                        });
                        this.table.page = 1;
                        this.productStockList()
                        this.$emit("updateStock")
                        this.drawer.ids = []
                        this.$refs.multipleTable.clearSelection();
                    }else{
                        this.$message({
                            type: 'error',
                            message: res.msg
                        });
                    }
                }).catch((e)=>{
                    this.table.loading = false;
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
            this.table.page = val
            this.productStockList()
        },
        handleSizeChange(val){
            this.table.pageSize = val
            this.table.page = 1
            this.productStockList()
        },
        //批量导入弹窗内导入文件事件
        handleImportFileChange(file,fileList){
            // console.log("====")
            if (file.size > 2 * 1024 * 1024) {
                this.$message({
                    type: 'error',
                    message:'文件大小不能超过2MB'
                })
                return false
            }
            this.importData.fileList = fileList
            this.importData.importFile =this.importData.fileList[0].raw
            // console.log(file,'file',this.importData.importFile)
        },
        
        //批量导入弹窗导入按钮事件
        async  submitImportInventory(){
            const valid = await new Promise(resolve => {
                this.$refs['importForm'].validate(valid => resolve(valid));
            });
            if (!valid) return;
                        if(!this.importData.importFile){
                            this.$message.warning('请选择导入文件')
                            return
                        }
                        const formData = new FormData();
                        formData.append("file", this.importData.importFile)
                        formData.append("product_id", this.product_id)
                        formData.append("remark",this.importData.importForm.remark)
                        formData.append("batch_id",this.importData.importForm.batch_id)
                        formData.append("cost_price",this.importData.importForm.cost_price)
                        // console.log(formData,'formData')
                        this.importData.importing = true;
                        try {
                            const response = await this.$axios.post(
                                `${this.$baseURL}/api/admin/product_stock/import`, // 替换为实际的 API 地址
                                formData,
                                {
                                    headers: {
                                        "Content-Type": "multipart/form-data", // 设置请求头
                                        'Authorization': 'Bearer ' + this.$local.get('token')
                                    },
                                }
                            );
                            this.importData.importing = false;
                            let res = response.data;
                            if (res.code == 1) {
                                this.$message({
                                    message: res.msg,
                                    type: "success",
                                    duration: 3000,
                                });
                                this.importData.fileList = [];
                                this.importData.importFile=null;
                                this.getData();
                                this.$emit("updateStock")
                                this.closeImportDialog()
                                
                            }else{
                                this.$message({
                                    message: res.msg,
                                    type: 'error',
                                    duration: 3000,
                                });
                            }
                            // console.log("上传成功：", response.data);
                        } catch (error) {
                            this.importData.importing = false;
                            console.error("上传失败：", error);
                            // this.$message.error("文件上传失败！");
                        }
        },
        clearFiles(file){
            // console.log(file,'fileclear')
        },
        //关闭新增库存弹窗
        addStockDialogClose(){
            this.addStockData.formData.card_info = ''
            this.addStockData.formData.status = 0
            this.addStockData.formData.remark = ''
            this.addStockData.formData.batch_id = ''
            this.addStockData.formData.cost_price = 0
            this.addStockData.visible = false;
        },
        //新增库存弹窗确认按钮点击事件
        submitAddStock(data,product_id){          
            data.product_id = product_id
            this.$refs.addStockRules.loading = true;
            this.$api.productStockAdd(data).then(res=>{
                this.$refs.addStockRules.loading = false;
                if(res.code == 1){
                    this.$message({
                        message: res.msg,
                        type: 'success'
                    });                 
                    this.productStockList()   
                    this.addStockDialogClose();
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error'
                    });
                }
            }).catch(e => {                        
                this.$refs.addStockRules.loading = false;
            })
        },
        //关闭编辑库存弹窗
        editStockDialogClose(){
            this.editStockData.formData.card_info = ''
            this.editStockData.formData.status = '0'
            this.editStockData.formData.remark = ''            
            this.editStockData.formData.cost_price = 0
            this.editStockData.formData.batch_id = ''
            this.editStockData.id = ''
            this.editStockData.visible = false;
        },
        //新增库存弹窗确认按钮点击事件
        submitEditStock(data,id){
            data.id = id
            this.$refs.editStockRules.loading = true;
            this.$api.productStockEdit(data).then(res=>{
                this.$refs.editStockRules.loading = false;
                if(res.code == 1){
                    this.$message({
                        message: res.msg,
                        type: 'success'
                    });                    
                    this.editStockDialogClose();
                    this.productStockList()
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error'
                    });
                }
            }).catch(e => {                        
                this.$refs.editStockRules.loading = false;
            })

        },
        //列表选中事件
        handleSelectionChange(val) {
            // console.log(val,'val')
            let list = val
            let ids=[]
            list.forEach(item => {
                ids.push(item.id)
            });
            this.drawer.ids = ids
        }, 
        batchEditCost() {
            this.searchBatchIds('P')
            this.batchData.batchEditCostVisible = true;
            this.batchData.batchEditCostForm = {
                batch_id: '',
                cost_price: 0,
                remark: ''
            };
        },
        //批量修改成本价弹窗关闭
        batchEditCostclose(){
            this.batchData.batchEditCostVisible = false;
            this.batchData.batchEditCostForm = {
                batch_id: '',
                cost_price: 0,
                remark: ''
            };
        },
        //批量修改成本价接口
        batchUpdateCostPrice(form){
            this.batchData.batchEditCostLoading = true
            this.$api.batchUpdateCostPrice(form).then(res=>{
                this.batchData.batchEditCostLoading = false
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.batchEditCostclose()
                    this.productStockList()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(e=>{
                this.batchData.batchEditCostLoading = false
                console.log(e)
            })
        },
        //批量修改成本价弹出确认按钮事件
        async handleBatchEditCost(form) {
            let batchName = '';
            let totalCount = 0;
            let unsoldCount = 0;
            this.batchData.batchList.forEach(item=>{
                if(item.batch_id == form.batch_id){
                    batchName = item.batch_id;
                    totalCount = item.count;
                    unsoldCount = item.unused_count;
                }
            })           
            let price = Number(form.cost_price).toFixed(2);
            const confirmMsg = `
                <div>
                    您将修改批次 ${batchName} 的成本价：
                </div>
                <ul style="padding: 0 10px; margin: 0;">
                    <li> 影响卡密总数：${totalCount} 张</li>
                    <li> 其中未售出卡密：${unsoldCount} 张</li>
                    <li> 新成本价：¥${price}</li>
                </ul>                
                <div>确定要继续操作吗？</div>`;
                this.$confirm(confirmMsg, '批量修改成本价确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(() => {
                    this.batchUpdateCostPrice(form)
                
                }).catch(() => {
                    console.log('取消');
                });
           
            
        },
           //表格内查看事件
        showCardInfo(row){
            this.$alert(
                `<div class="card-info-container">
                    <div class="card-info-item">
                        <span class="label">卡密ID：</span>
                        <span class="value">${row.product_id}</span>
                    </div>
                     <div class="card-info-item">
                        <span class="label">批次ID：</span>
                        <span class="value">${row.batch_id?row.batch_id:'--'}</span>
                    </div>
                    <div class="card-info-item">
                        <span class="label">卡密信息：</span>
                        <span class="value">${row.card_info}</span>
                    </div>
                    <div class="card-info-item">
                        <span class="label">创建时间：</span>
                        <span class="value">${row.created_at}</span>
                    </div>
                    <div class="card-info-item">
                        <span class="label">状态：</span>
                        <span class="value ${row.status == '0' ? 'text-success' :row.status == '1' ? 'text-warning':row.status == '3' ?'text-danger':'text-info'}">
                        ${row.status == '0' ? '未售出' : row.status == '1' ? '已锁定' : row.status == '3'?'已作废':'已售出'}
                        </span>
                    </div>
                    <div class="card-info-item">
                        <span class="label">成本价：</span>
                        <span class="value">¥${row.cost_price.toFixed(2)}</span>
                    </div>
                    <div class="card-info-item">
                        <span class="label">备注：</span>
                        <span class="value">${row.remark || '无'}</span>
                    </div>
                </div>`, 
                '卡密详细信息', {
                    dangerouslyUseHTMLString: true,
                    confirmButtonText: '关闭',
                    customClass: 'card-info-dialog',
                    beforeClose: (action, instance, done) => {
                        if (action === 'close') {
                            // console.log('点击了关闭图标');
                        }
                        done(); // 必须调用 done，否则对话框不会关闭
                    },
                }
            ) .catch(err => {
                // 捕获取消错误，这里可以选择忽略
                if (err === 'cancel') {
                    console.log('对话框被用户关闭');
                }
            });
        },
    }
}
</script>
<style lang="scss">
    .stock-manage-box{
        .el-drawer__body{
            padding:20px;
        }
    }
    .import-dialog-class{  
        .el-upload-dragger{
            width: 100%;
            border: none;
            background: none;
            box-shadow: none;
            display: flex;
            padding: 20px;
            border-radius: 6px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            height: 200px;
            align-items: center;
            justify-content: center;
        }      
        .el-upload{
            width: 100%;
        }
        .el-upload-dragger:hover{
            border: none;
            background: none;
        }
        .el-upload-list__item-name{
            display: flex;
        }
        
        // .import-uploader {
        //     .el-upload {
        //         width: 100%;
        //     }
        //     .el-upload-dragger {
        //         width: 100%;
        //         height: 180px;
        //         display: flex;
        //         flex-direction: column;
        //         align-items: center;
        //         justify-content: center;
        //         padding: 20px;
        //         border: 2px dashed #dcdfe6;
        //         border-radius: 6px;
        //         cursor: pointer;
        //         position: relative;
        //         overflow: hidden;
        //         transition: border-color .3s;
        //         &:hover {
        //             border-color: #409eff;
        //         }
        //         .upload-content {
        //             text-align: center;

        //             .upload-icon {
        //             font-size: 48px;
        //             color: #909399;
        //             margin-bottom: 10px;
        //             }

        //             .upload-text {
        //             color: #606266;
        //             font-size: 14px;
        //             line-height: 1.6;

        //             em {
        //                 color: #409eff;
        //                 font-style: normal;
        //             }
        //             }
        //         }
        //     }
        //    .upload-tip {
        //         margin-top: 10px;
        //         color: #909399;
        //         font-size: 12px;
        //         line-height: 1.5;
        //         text-align: center;
        //     }
        // }
    }
    
</style>
<style lang="scss" scoped>
    .stock-manage-box{
        .stock-manage{
            padding: 20px;
            .stock-tip{         
            background: #f5f7fa;  
            color: #606266;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #67c23a;
        }
        .operation-buttons{
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .query-box{
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.102);
        }
        .stock-footer{
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            .stock-batch-operate{
                display: flex;
                gap: 10px;
            }
        }
        }
        .form-tip{
            margin-top: 5px;
            font-size: 12px;
            color: #909399;
        }
        .form-input{
            width: 100%;
        }
        
    }
    //批量导入弹窗内样式
    .import-dialog-class{
        .import-description {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 4px;
            h4 {
                font-size: 14px;
                color: #333;
                margin: 0 0 10px 0;
            }

            ol {
                margin: 0;
                padding-left: 20px;
                color: #666;
                font-size: 14px;
                line-height: 1.8;
            }
        }

        .import-uploader {
            text-align: center;
            padding: 20px;
            background: #fafafa;
            border: 1px dashed #d9d9d9;
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: border-color .3s;         
            &:hover {
                border-color: #409eff;
            }
        
        }
        .select-file-btn {
            display: inline-block;
            margin-bottom: 10px;
        }

        
        .upload-content {
            text-align: center;
        }

        .upload-icon {
            font-size: 48px;
            color: #909399;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #606266;
            font-size: 14px;
        }

        .upload-text em {
            color: #409EFF;
            font-style: normal;
        }
        
        .upload-tip {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
    }
</style>