<template>
    <div class="account-box">
       <mainBoxHeader titleName="账户管理" :description="operationData.description" >
           <template slot="oprBtn">
               <el-button class="f14" type="primary" @click="addAccount" size="small">新增账户</el-button>
           </template>
           <template slot="pageCon">
                <div class="query-box">
                    <div class="query-item">
                        <span class="query-label mr12">用户名</span>
                        <el-input class="query-input" placeholder="请输入用户名" v-model="query.username" clearable size="small"></el-input>
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">邮箱</span>
                        <el-input class="query-input" placeholder="请输入邮箱" v-model="query.email" clearable size="small"></el-input>
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">状态</span>
                        <el-select v-model="query.status" placeholder="请选择" clearable style="width: 168px;" size="small">
                            <el-option label="全部" value=""></el-option>
                            <el-option label="正常" value="1"></el-option>
                            <el-option label="禁用" value="0"></el-option>
                        </el-select>
                    </div> 
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="query.loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="query.loading">重置</el-button>
                    </div>
                </div>
               <div class="table-box">
                <el-table :data="table.tableData" style="width: 100%" v-loading="query.loading" border stripe>
                    
                    <el-table-column prop="username" label="用户名" width="120"></el-table-column>
                    <el-table-column prop="email" label="邮箱" min-width="180"></el-table-column>
                    <el-table-column prop="last_login_time" label="最后登录时间" width="180"></el-table-column>
                    <el-table-column prop="remark" label="备注" min-width="150"></el-table-column>
                    <el-table-column prop="status" label="状态" width="100">
                        <template slot-scope="scope">
                            <el-switch
                                v-model="scope.row.status"
                                :active-value="'1'"
                                :inactive-value="'0'"
                                @change="(val) => statusChange(val, scope.row)"
                                :disabled="query.loading"
                            ></el-switch>
                            <span class="status-text ml8">{{ scope.row.status == '1' ? '正常' : '禁用' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="160" fixed="right">
                        <template slot-scope="scope">
                            <div class="action-buttons">
                                <el-button size="small f14" type="primary" @click="editData(scope.row)" :disabled="query.loading">编辑</el-button>
                                <el-button size="small f14" type="danger" @click="deleteData(scope.row)" :disabled="query.loading">删除</el-button>
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
            <el-dialog title="新增账户"  :visible.sync="dialog.addVisible"
                width="650px"
                custom-class="add-user-class"
                :before-close="closeAddDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <template>
                    <accountDialog dialogType="add" :accountForm="dialog.addObj" ref="addDialog"></accountDialog>
                </template>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" size="small" @click="closeAddDialog" :disabled="dialog.loading">取消</el-button>
                        <el-button class="f14" size="small" type="primary" @click="addSuer" :disabled="dialog.loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
            <el-dialog title="编辑账户"  :visible.sync="dialog.editVisible"
                width="650px"
                custom-class="add-user-class"
                :before-close="closeEditDialog"
                :close-on-click-modal="false"
                :top="dialog.top"
            >
                <template>
                    <accountDialog dialogType="edit" :accountForm="dialog.editObj" ref="editDialog"></accountDialog>
                </template>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" size="small" @click="closeEditDialog" :disabled="dialog.loading">取消</el-button>
                        <el-button class="f14" size="small" type="primary" @click="editSuer" :disabled="dialog.loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
        </div>
   </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
import accountDialog from '@/components/accountDialog.vue'
export default {
   name:'accountManage',
   components:{
       mainBoxHeader,
       accountDialog
   },
   data(){
       return{
            operationData:{
               description:'管理系统的后台账户，包括管理员和操作员。您可以为不同角色的管理人员创建账户，并分配相应的权限。',
           },
           table:{
                tableData:[]
           },
           query:{
                loading:false,
                username:'',
                email:'',
                status:"",
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
           },
           dialog:{
                addVisible:false,
                top:'15vh',
                editVisible:false,
                loading: false,
                editObj:{
                    id: '',
                    username: '',
                    email: '',
                    status: '1',
                    remark: ''
                },
                addObj:{
                    username: '',
                    password: '',
                    confirm_password: '',
                    email: '',
                    status: '1',
                    remark: ''
                }
            }
       }
   },
   created(){
        this.getData()
   },
   methods:{
        //新增账户按钮点击事件
        addAccount(){
            this.dialog.addObj = {
                username: '',
                password: '',
                confirm_password: '',
                email: '',
                status: '1',
                remark: ''
            }
            this.dialog.addVisible = true
        },
        //查询按钮点击事件
        getData(){
            this.query.page = 1
            this.managerList()
        },
        managerList(){
            const params = {
                page: this.query.page,
                limit: this.query.pageSize,
                username: this.query.username,
                email: this.query.email,
                status: this.query.status
            }
            this.query.loading = true
            this.$api.managerList(params).then(res => {
                this.query.loading = false
                if(res.code === 1){
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
        //重置按钮点击事件
        reset(){
            this.query = {
                ...this.query,
                username: '',
                email: '',
                status: '',
                page: 1
            }
            this.managerList()
        },
        //分页
        handleCurrentChange(val){
            this.query.page = val
            this.managerList()
        },
        handleSizeChange(val){
            this.query.pageSize = val
            this.query.page = 1
            this.managerList()
        },
        //列表内 状态改变事件
        statusChange(val, row){
            let status = val;
            const oldStatus = val=='1'?'2':'1'
            row.status = oldStatus // 先恢复原状态

            const data = {
                id: row.id,
                status: Number(val)
            }
            this.query.loading = true
            this.$api.managerUpdate(data).then(res => {
                this.query.loading = false
                if(res.code === 1){
                    row.status = status
                    this.$message.success(res.msg)
                    this.managerList()
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.query.loading = false
                this.$message.error('状态更新失败')
            })
        },
        //列表内编辑按钮点击事件
        editData(item){
            this.dialog.editObj = {
                id: item.id,
                username: item.username,
                email: item.email,
                status: item.status,
                remark: item.remark
            }
            this.dialog.editVisible = true
        },
        //列表内删除按钮点击事件
        deleteData(item){
            this.$confirm(`确定要删除账户"${item.username}"吗？`, '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.query.loading = true
                this.$api.managerDelete({ id: item.id }).then(res => {
                    this.query.loading = false
                    if(res.code === 1){
                        this.$message.success(res.msg)
                        this.query.page = 1
                        this.managerList()
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
        //关闭新增账户弹窗
        closeAddDialog(){
            this.dialog.addVisible = false
            this.$refs.addDialog.$refs.accountFormRef.resetFields()
        },
        //新增账户提交
        addSuer(){
            this.$refs.addDialog.$refs.accountFormRef.validate(valid => {
                if(valid){
                    const data ={...this.dialog.addObj}
                    data.status = data.status == '1' ? 1 : 0
                    this.dialog.loading = true
                    this.$api.managerAdd(data).then(res => {
                        this.dialog.loading = false
                        if(res.code === 1){
                            this.$message.success(res.msg)
                            this.closeAddDialog()
                            this.managerList()
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.dialog.loading = false
                        this.$message.error('新增失败')
                    })
                }
            })
        },
        //关闭编辑账户弹窗
        closeEditDialog(){
            this.dialog.editVisible = false
            this.$refs.editDialog.$refs.accountFormRef.resetFields()
        },
        //编辑账户提交
        editSuer(){
            this.$refs.editDialog.$refs.accountFormRef.validate(valid => {
                if(valid){
                    
                    const data = {...this.dialog.editObj}
                    data.status = data.status == '1' ? 1 : 0
                    this.dialog.loading = true
                    this.$api.managerUpdate(data).then(res => {
                        this.dialog.loading = false
                        if(res.code === 1){
                            this.$message.success(res.msg)
                            this.closeEditDialog()
                            this.managerList()
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.dialog.loading = false
                        this.$message.error('更新失败')
                    })
                }
            })
        }
   }
}
</script>
<style lang="scss">

</style>
<style lang="scss" scoped>
    .account-box{
        .query-box {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
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
        .table-box{
            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .action-buttons .el-button {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
        }
    }
    
</style>