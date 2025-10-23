<template>
    <div class="insite-messages-box">
       <mainBoxHeader titleName="补货提醒" :description="insiteData.description" descriptionBgColor='#ffffff' descriptionBorderColor='#ffffff'>
           <template slot="oprBtn">
               <el-button class="f14" type="primary" @click="batchMarking" size="small" :disabled='insiteData.selectMessage.length == 0 || query.loading' >批量标记已解决</el-button>
           </template>
           <template slot="pageCon">
                <div class="query-box">
                    <div class="query-item">
                        <span class="query-label mr12">状态</span>
                        <el-select class="query-select" v-model="query.status" clearable placeholder="全部状态" size="small">
                            <el-option label="未解决" value="0"></el-option>
                            <el-option label="已解决" value="1"></el-option>
                        </el-select>   
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">用户邮箱</span>
                        <el-input class="query-input" placeholder="用户邮箱" v-model="query.email" clearable size="small"></el-input>
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">商品名称</span>
                        <el-input class="query-input" placeholder="商品名称" v-model="query.product" clearable size="small"></el-input>
                    </div>
                    
                    <div class="query-item">
                        <span class="query-label mr12">时间范围</span>
                        <el-date-picker v-model="query.date" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="small"  :picker-options="pickerOptions"></el-date-picker>
                    </div> 
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled='query.loading'>查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled='query.loading'>重置</el-button>
                    </div>
                </div>
               <div class="table-box">
                    <el-table
                        :data="table.tableData"
                        style="width: 100%"
                        @selection-change="handleSelectionChange"
                        :row-class-name="getRowClassName"
                         ref="multipleTable"
                        >
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column label="状态" width="100">
                            <template scope="scope">
                            <el-tag v-if="scope.row.status === '0'" type="danger" size="small">未解决</el-tag>
                            <el-tag v-else-if="scope.row.status === '1'" type="success" size="small">已解决</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="user_email" label="用户邮箱" width="200"></el-table-column>
                        <el-table-column prop="product_name" label="商品名称" min-width="150"></el-table-column>
                        <el-table-column prop="quantity" label="需求数量" width="100"></el-table-column>
                        <el-table-column prop="remarks" label="备注说明" min-width="200"></el-table-column>
                        <el-table-column prop="sent_at" label="发送时间" width="180"></el-table-column>
                        <el-table-column label="操作" width="180" fixed="right">
                            <template scope="scope">
                            <div class="action-buttons">
                                <el-button size="small" type="primary" @click="viewMessage(scope.row)">查看</el-button>
                                <el-button 
                                size="small" 
                                type="primary" 
                                @click="resolveMessage(scope.row)"
                                :disabled="scope.row.status === '1' || query.loading"
                                >解决</el-button>
                                <el-button 
                                size="small" 
                                type="danger" 
                                @click="deleteMessage(scope.row)"
                                :disabled="query.loading"
                                >删除</el-button>
                            </div>
                            </template>
                        </el-table-column>
                    </el-table>
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
       </mainBoxHeader>


       <div class="dialog-box">
           <el-dialog title="补货提醒详情"  :visible.sync="dialog.dialogVisible"
               width="600px"
               custom-class="restock-class"
               :before-close="closeDialog"
               :close-on-click-modal="false"
               :top="dialog.top"
           >
                <div class="message-detail">
                    <div class="message-header">
                        <div class="message-meta">
                            <span>时间: {{ dialog.restockItem.createTime }}</span>
                            <el-tag v-if="dialog.restockItem.status === 'pending'" type="danger" size="small">未解决</el-tag>
                            <el-tag v-else-if="dialog.restockItem.status === 'resolved'" type="success" size="small">已解决</el-tag>
                        </div>
                    </div>
                    <div class="restock-info">
                        <el-descriptions :column="1" border size="medium">
                            <el-descriptions-item label="用户邮箱" label-class-name="restock-label" content-class-name="restock-content">{{ dialog.restockItem.user_email }}</el-descriptions-item>
                            <el-descriptions-item label="商品名称" label-class-name="restock-label" content-class-name="restock-content">{{ dialog.restockItem.product_name }}</el-descriptions-item>
                            <el-descriptions-item label="需求数量" label-class-name="restock-label" content-class-name="restock-content">{{ dialog.restockItem.quantity }}</el-descriptions-item>
                            <el-descriptions-item label="备注说明" label-class-name="restock-label" content-class-name="restock-content">{{ dialog.restockItem.remarks }}</el-descriptions-item>
                        </el-descriptions>
                    </div>
                    <div v-if="dialog.restockItem.resolvedTime" class="resolved-info">
                        <h4>解决信息</h4>
                        <p>已于 {{ dialog.restockItem.resolvedTime }} 标记为已解决</p>
                        <p v-if="dialog.restockItem.resolvedBy">处理人: {{ dialog.restockItem.resolvedBy }}</p>
                    </div>
                </div>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" @click="closeDialog" size="small">关闭</el-button>
                        <el-button class="f14" type="success" @click="resolveMessage(dialog.restockItem)" :disabled="dialog.restockItem.status == '1' || query.loading" size="mini">标记为已解决</el-button>
                    </span>
                </template>
           </el-dialog>
           
       </div>
   </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
export default {
   name:'templateSetting',
   components:{
       mainBoxHeader,
   },
   created(){
        let insite_status = this.$local.get('insite_status');
        this.$local.remove("insite_status")
        if(insite_status){            
            this.query.status = insite_status;    
        }
        this.getData()
   },
   data(){
       return{
           insiteData:{
               description:'管理用户发来的补货提醒，您可以查看详情并标记为已解决',
               selectMessage:[],//列表选中的数据
           },
           table:{
                tableData:[],
           },
           dialog:{
               dialogVisible:false,
               top:'15vh',
               //当前查看的补货详情数据
               restockItem:{}
           },
           query:{
                id:'',
                date:[],
                status:"",
                product:'',
                email:'',
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
                loading:false
           },            
            pickerOptions: {
                shortcuts: [{
                    text: '今天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 0);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '昨天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                    end.setTime(end.getTime() - 3600 * 1000 * 24 * 1);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '最近三天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 3);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '最近一周',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                    picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                    picker.$emit('pick', [start, end]);
                    }
                }]
            },
          

       }
   },
   methods:{
        //批量标记 已解决
        batchMarking(){
            if(this.insiteData.selectMessage.length === 0) {
                this.$message.warning('请选择要标记的消息')
                return
            }
            const ids = this.insiteData.selectMessage.map(item => item.id)
            this.query.loading = true
            this.$api.messageBatchSolve({ids: ids}).then(res => {
                this.query.loading = false
                if(res.code === 1) {
                    this.$message.success(res.msg)
                    this.insiteData.selectMessage = [] // 清除选中项
                    this.getData() // 刷新列表
                    this.$refs.multipleTable.clearSelection();
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.query.loading = false
                this.$message.error('标记失败')
            })
        },
        //获取站内信列表
        messageList(){
            const data = {
                page: this.query.page,
                limit: this.query.pageSize,
                status: this.query.status,
                email: this.query.email,
                product: this.query.product,
                start_time: this.query.date?(this.query.date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[0])), true) :''):"",
                end_time:   this.query.date?(this.query.date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[1])), true) : ''):""
                
            }
            this.query.loading = true
            this.$api.messageList(data).then(res => {
                this.query.loading = false
                if(res.code === 1) {
                    this.table.tableData = res.data.list
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
            this.messageList()
        },
         //重置按钮点击事件
         reset(){
            this.query = {
                ...this.query,
                date: [],
                status: "",
                product: "",
                email: "",
                page: 1
            }
            this.getData()
        },
          //分页
          handleCurrentChange(val){
            this.query.page = val
            this.messageList()
        },
        handleSizeChange(val){
            this.query.pageSize = val
            this.query.page = 1
            this.messageList()
        },
        //关闭补货详情弹窗
        closeDialog(){
            this.dialog.dialogVisible = false;
            this.dialog.restockItem = {};
        },
       
        //列表内查看按钮点击事件 打开补货详情弹窗
        viewMessage(item){
            this.dialog.dialogVisible = true;
            this.dialog.restockItem = item;
        },
        //列表内解决按钮点击事件
        resolveMessage(item){
            this.query.loading = true
            this.$api.messageSolve({id: item.id}).then(res => {
                this.query.loading = false
                if(res.code === 1) {
                    this.dialog.restockItem.status = '1'
                    this.$message.success(res.msg)
                    this.messageList() // 刷新列表
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.query.loading = false
                this.$message.error('标记失败')
            })
        },
        //列表内删除按钮点击事件
        deleteMessage(item){
            this.$confirm(`确定要删除用户"${item.user_email.substring(item.user_email.indexOf('@'))}"的补货提醒吗？`, '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.query.loading = true
                this.$api.messageDelete({id: item.id}).then(res => {
                    this.query.loading = false
                    if(res.code === 1) {
                        this.$message.success(res.msg)
                        this.messageList() // 刷新列表
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
        //复选框组改变选项事件
        handleSelectionChange(val){
            this.insiteData.selectMessage = val
        },
        //列表内 获取行的class 名称
        getRowClassName(row){
            if(row.row.status == '0'){
                return 'pending-row'
            }
        },
   },
   watch: {
    '$route'(path) { 
        if(this.$route.query){            
            let insite_status = this.$local.get('insite_status');
            this.$local.remove("insite_status")
            if(insite_status){            
                this.query.status = insite_status;    
            }        
            this.getData()
        }
     }
  },
}
</script>
<style lang="scss">
/* 预览样式 */

.insite-messages-box{
    .main-box-component {
        .page-description{
            padding: 0;
        }
    }
    .pending-row{
        background-color: #fff8f6;
        font-weight: bold;
    }
    .pending-row.hover-row {
        background-color: #ffece8 !important;
        td.el-table__cell{
            background-color: #ffece8 !important;
        }
       
    }
    .action-buttons {
        display: flex;
        gap: 5px;
        white-space: nowrap;
    }

    .action-buttons .el-button {
        padding-left: 8px;
        padding-right: 8px;
    }
    .restock-class{
        .restock-info {
            margin-bottom: 20px;
            .restock-label{
                background: #f5f7fa !important;
                color: #606266 !important;
                font-weight: 700 !important;
                padding: 9px 11px;
                width: 152px;
                box-sizing: border-box;            
                border:1px solid #ebeef5;
            }
            .restock-content{
                color:#303133;
                padding: 9px 11px;
                border:1px solid #ebeef5;
            }
            h4 {
                margin: 0 0 10px 0;
                font-size: 16px;
                color: #303133;
            }
            p{
                margin: 0;
            }
        }
        .resolved-info {        
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f9eb;
            border-radius: 4px;
            h4 {
                margin: 0 0 10px 0;
                font-size: 16px;
                color: #303133;
            }        
            p{
                margin: 0;
            }
        }
        .el-button { 
            height:32px
        }
    }
   
    
} 

</style>
<style lang="scss" scoped>
    .insite-messages-box{
        .query-box {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f7fa;
            border-radius: 4px;
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
    }
    .restock-class{
        .message-detail {
            padding: 10px;
            .message-header {
                margin-bottom: 20px;
                h3 {
                    margin: 0 0 10px 0;
                    font-size: 18px;
                    color: #303133;
                }
                .message-meta {
                    display: flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 15px;
                    font-size: 14px;
                    color: #909399;
                }

            }
            .restock-info {
                margin-bottom: 20px;
            }
        }
    }
    
    
</style>