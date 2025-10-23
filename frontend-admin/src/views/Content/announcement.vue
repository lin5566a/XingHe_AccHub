<template>
    <div class="payment-set-box">
        <mainBoxHeader :hasHeader="false" titleName="" :noDescription="true">
            <template slot="pageCon">
                <div class="query">
                    <div class="query-item">
                        <span class="query-label">公告标题</span>
                        <el-input class="query-input" v-model="query.title" placeholder="请输入公告标题" size="small"></el-input>
                    </div>                    
                    <div class="query-item">
                        <span class="query-label">状态</span>
                        <el-select class="query-select" v-model="query.status" clearable placeholder="请选择" size="small">                            
                            <el-option :label="item.label" :value="item.value" v-for="item in queryOption.status" :key="item.value"></el-option> 
                        </el-select>   
                    </div>
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="loading">重置</el-button>
                    </div>
                </div>
            </template>
        </mainBoxHeader>
        <mainBoxHeader :noDescription="true" :hasHeader = "true" class="custom-main-box-header">
            <template slot ="title">
                <div>
                    <el-button type="primary" @click="addData" size="small">新增</el-button>
                    <el-button type="danger" @click="bathDeleteData" size="small" :disabled="table.selectArr.length == 0">批量删除</el-button>
                </div>
                
            </template>
            <template slot="oprBtn">
                <div class="refresh-btn">
                    <el-button type="plan" @click="refreshData" size="small">刷新</el-button>
                </div>
            </template>
            <template slot="pageCon">
                <div class="content">
                    <div class="content-tip ">
                        <i class="el-icon-info tip-icon"></i>
                        <div class="tip-con">
                            <div class="tip-title f16">公告设置说明</div>
                            <div class="tip-text f12">系统只允许同时启用一个公告，启用新公告后将自动禁用其他公告。</div>
                        </div>
                    </div>
                    <div class="table-box">
                        <el-table :data="table.dataTable" style="width: 100%" v-loading="loading" border stripe @selection-change="handleSelectionChange" size="small">
                            <el-table-column type="selection" width="55"></el-table-column>
                            <el-table-column prop="title" label="公告标题"></el-table-column>
                            <el-table-column prop="content" label="公告内容" show-overflow-tooltip width="284px"></el-table-column>
                            <el-table-column prop="status" label="状态">
                                <template scope="scope">
                                    <el-tag :type="scope.row.status == '1'?'success':scope.row.status == '0'?'danger':''" size="small">
                                        {{ scope.row.status == '1'?'启用':scope.row.status == '0'?'禁用':'' }}
                                    </el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column prop="create_time" label="发布时间" width="180"></el-table-column>
                            <el-table-column prop="" label="操作">
                                <template scope="scope">
                                    <el-button @click="editData(scope.row)" type="text" size="small">编辑</el-button>
                                    <el-popconfirm  title="确认删除?" @confirm="deleteData(scope.row)">
                                        <el-button class="error-btn-text" slot="reference" type="text" size="small">删除</el-button>
                                    </el-popconfirm>
                                                                                
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                    <div class="pagination-box">
                        <el-pagination background :total="table.total"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page="query.page"
                            :page-sizes="table.pageSizes"
                            :page-size="query.limit"
                            :disabled="loading"
                            layout="total, sizes, prev, pager, next, jumper">
                        </el-pagination>
                    </div>
                </div>
            </template>
        </mainBoxHeader>
        <div class="dialog-box">
            <el-dialog title="新增公告" :visible.sync="dialog.addVisible" width="500px" custom-class="amount-dialog" :close-on-click-modal="false" :top="dialog.top">
                <EditAnnoucement v-if="dialog.addVisible" :announcementForm="dialog.announcementAdd" @closeDialog="closeDialog" @suerSubmit = "addSuer" :loading="dialog.loading"></EditAnnoucement>
            </el-dialog>
            <el-dialog title="编辑公告" :visible.sync="dialog.editVisible" width="500px" custom-class="amount-dialog" :close-on-click-modal="false" :top="dialog.top">
                <EditAnnoucement v-if="dialog.editVisible" :announcementForm="dialog.announcementEdit"  @closeDialog="closeDialog"  @suerSubmit = "editSuer" :loading="dialog.loading"></EditAnnoucement>
            </el-dialog>
        </div>
    </div>
</template>
<script>
import mainBoxHeader from "@/components/mainBoxHeader.vue"
import EditAnnoucement from "@/components/annoucement/editAnnoucement.vue"
export default {
    components:{
        mainBoxHeader,
        EditAnnoucement
    },
    data(){
        return{
            loading:false,
            query:{
                title:'',
                status:'',
                page:1,
                limit:10,
            },
            queryOption:{
                status:[
                    {label:'全部',value:''},
                    {label:'启用',value:'1'},
                    {label:'禁用',value:'0'},
                ]
            },
            table:{
                dataTable:[],
                total:0,
                selectArr:[],
                pageSizes:[10,20,50,100],
            },
            dialog:{
                addVisible:false,
                announcementAdd:{},
                editVisible:false,
                announcementEdit:{},
                loading:false,
            },
            
        }
    },
    computed:{
    },
    created(){
        this.getData()
    },
    methods:{
        //获取公告列表
        getData(){
            this.query.page = 1;
            this.getAnnouncement()
        },
        //重置查询条件
        reset(){
            this.query.page = 1;
            this.query.status = '',
            this.query.title = ''
            this.getAnnouncement()
        },
        //获取公告列表 接口查询
        getAnnouncement(){
            this.loading = true;
            this.$api.announcementList(this.query).then(res=>{
                this.loading = false;
                this.table.dataTable = res.data.list
                this.table.total = res.data.total;
            }).catch(res=>{
                this.loading = false
            })
            
        },
        //分页
        handleCurrentChange(val) {
            this.query.page = val;
            this.getAnnouncement();
        },
        handleSizeChange(val) {
            this.query.limit = val;
            this.query.page = 1; // 切换每页条数时，重置为第一页
            this.getAnnouncement();
        },
        //  新增
        addData(){
            this.dialog.addVisible=true;
            this.dialog.announcementAdd={
                title:'',
                content:'',
                status:'1'
            }
        },
        //批量删除
        bathDeleteData(){
            let ids = this.table.selectArr.map(item=>{
                return item.id
            })
            this.$api.announcementBatchDelete({ids}).then(res=>{
                if(res.code == 1){  
                    this.$message.success(res.msg)
                    this.getAnnouncement()
                }else{
                    this.$message.error(res.msg)
                }
              
            }).catch(res=>{
            })
        },
        //刷新
        refreshData(){
            this.getAnnouncement()
        },
        // 选择
        handleSelectionChange(val){
            this.table.selectArr = val;
            console.log(this.table.selectArr)
        },
        //编辑点击按钮事件
        editData(item){
            this.dialog.editVisible=true;
            this.dialog.announcementEdit={...item}
        },
        //删除点击按钮事件
        deleteData(item){
            this.$api.announcementDelete({id:item.id}).then(res=>{
                if(res.code == 1){  
                    this.$message.success(res.msg)
                    this.getAnnouncement()
                }else{
                    this.$message.error(res.msg)
                }
              
            }).catch(res=>{
                
            })
            console.log(item,'删除')
        },

        //关闭弹窗
        closeDialog(){
            this.dialog.editVisible=false;
            this.dialog.announcementEdit = {}
            this.dialog.addVisible=false;
            this.dialog.announcementAdd = {}
        },
        //新增确认
        addSuer(formData){
            this.dialog.loading = true
            this.$api.announcementAdd(formData).then(res=>{
                if(res.code == 1){  
                    this.dialog.loading = false
                    this.$message.success(res.msg)
                    this.getAnnouncement()
                    this.closeDialog()

                }else{
                    this.$message.error(res.msg)
                }
              
            }).catch(res=>{
                this.dialog.loading = false
            })
            console.log(formData,'新增确认')
        },
        //修改确认
        editSuer(formData){
            console.log(formData,'修改确认')
            this.dialog.loading = true
            this.$api.announcementEdit(formData).then(res=>{
                this.dialog.loading = false
                if(res.code == 1){  
                    this.$message.success(res.msg)
                    this.getAnnouncement()
                    this.closeDialog()

                }else{
                    this.$message.error(res.msg)
                }
              
            }).catch(res=>{
                this.dialog.loading = false
            })
        }
    }
}
</script>
<style scoped lang="scss">
.payment-set-box{
    .query{
        display: flex;
        justify-content: flex-start;
        align-items: center;
        .query-item{
            display: flex;
            align-items: center;
            margin-right: 32px;
            .query-label{
                margin-right: 10px;
            }
            .query-input{
                flex:1;
            }
            .query-select{
                width: 200px;
            }
        }
    }
    .custom-main-box-header{
        :deep(.el-card__header){
            border-bottom: none;
        }
        :deep(.el-card__body){
            padding-top:0;
        }
    }
    .content-tip{
        padding: 8px 16px;
        background: #f4f4f5;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        color:#909399;
        margin-bottom: 16px;
        
        .tip-icon{
            font-size:28px;
            margin-right: 12px;

        }
        .tip-con{
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-content: center;
            gap: 4px;
            line-height: 1.5;
        }
    }
    .table-box{
        .error-btn-text{
            color: #f56c6c;
        }
    }

}

</style>