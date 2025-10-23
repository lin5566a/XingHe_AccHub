<template>
    <div class="user-manage-box">
        <mainBoxHeader titleName="用户管理" :description="UserData.description">
            <template slot="oprBtn">
                <el-button class="f14" type="primary" @click="addUser" size="small">新增用户</el-button>
            </template>
            <template slot="pageCon">
                <div class="user-query-box">       
                    <!-- <div class="query-item">
                        <span class="query-label mr12">用户昵称</span>
                        <el-input class="query-input" placeholder="请输入用户昵称" v-model="query.nickname" clearable size="small"></el-input>
                    </div>               -->
                    <div class="query-item">
                        <span class="query-label mr12">邮箱</span>
                        <el-input class="query-input" placeholder="请输入邮箱" v-model="query.email" clearable size="small"></el-input>
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">用户状态</span>
                        <el-select class="query-select" v-model="query.status" clearable placeholder="请选择" size="small">
                            <el-option label="正常" value="1"></el-option>
                            <el-option label="禁用" value="2"></el-option>
                        </el-select>   
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">VIP等级</span>
                        <el-select class="query-select" v-model="query.membership_level" clearable placeholder="请选择" size="small">                            
                            <el-option :label="item.name" :value="item.id" v-for="item in queryOption.vip_options" :key="item.id"></el-option> 
                        </el-select>   
                    </div> 
                    <div class="query-item">
                        <span class="query-label mr12">注册时间</span>
                        <el-date-picker v-model="query.date" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="small" :picker-options="pickerOptions"></el-date-picker>
                    </div> 
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="query.loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="query.loading">重置</el-button>
                    </div>                                     
                </div>
                <div class="table-box">
                    <el-table :data="table.tableData" style="width: 100%" v-loading="query.loading" border stripe  size="small">
                        <!-- <el-table-column prop="nickname" label="用户昵称"></el-table-column> -->
                        <el-table-column prop="email" label="邮箱" ></el-table-column>
                        <el-table-column prop="" label="密码" >
                            <template scope="scope">
                                <span>{{scope.row.password}}</span><el-button class="f12" type="text" @click="resetPassword(scope.row)">重置</el-button>
                            </template>
                        </el-table-column>
                        <el-table-column prop="balance" label="用户余额">
                            <template scope="scope">
                                <span class="money f14 fb red-color">¥{{ scope.row.balance }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="membership_level" label="VIP等级" width="100">
                            <template scope="scope">                                
                                <el-tag size="small" :type="(scope.row.membership_level == 1 || scope.row.membership_level == 0) ? 'primary': (scope.row.membership_level == 5?'danger':'warning')">{{scope.row.memberLevel?scope.row.memberLevel.name:'普通'}}</el-tag>
                            </template>
                        </el-table-column> 
                        <el-table-column prop="discount" label="会员折扣" width="100">
                            <template scope="scope">  
                                <span>{{ scope.row.discount }}%</span>   
                            </template>
                        </el-table-column> 
                        <el-table-column prop="total_recharge" label="累计充值">
                            <template scope="scope">
                                <span class="money f14 fb red-color">¥{{ scope.row.total_recharge }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="total_spent" label="累计消费">
                            <template scope="scope">
                                <span class="money f14 fb red-color">¥{{ scope.row.total_spent }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="状态" width="110">
                        <template scope="scope">
                            <el-switch
                            v-model="scope.row.status"
                            :active-value="1"
                            :inactive-value="2"
                            size="small"
                            @change="(val) => statusChange(val, scope.row)"
                            ></el-switch>
                            <span class="status-text ml8">{{ scope.row.status == '1' ? '正常' : '禁用' }}</span>
                        </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="注册时间" width="180"></el-table-column>
                        <el-table-column label="操作" width="160" fixed="right">
                        <template scope="scope">
                            <div class="action-buttons">
                                <el-dropdown @command="handleCommand($event, scope.row)">
                                    <el-button size="mini" type="primary">
                                        操作<i class="el-icon-arrow-down el-icon--right"></i>
                                    </el-button>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item command="editData">编辑</el-dropdown-item>
                                        <el-dropdown-item command="amountChange" >余额操作</el-dropdown-item>
                                        <el-dropdown-item command="resetPassword">重置密码</el-dropdown-item>
                                        <el-dropdown-item command="showLogo">查看日志</el-dropdown-item>
                                        <el-dropdown-item command="deleteData" :disabled="query.loading">删除</el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
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
                <div class="dialog-box">
                    <el-dialog title="新增用户"  :visible.sync="dialog.addVisible"
                        width="650px"
                        custom-class="add-user-class"
                        :before-close="closeAddDialog"
                        :close-on-click-modal="false"
                        :top="dialog.top"
                    >
                        <template>
                            <userDialog dialogType="add" :userForm="dialog.addObj" @suerSubmit="addSuerSubmit" @closeDialog = "closeAddDialog" :vip_options ="queryOption.vip_options" :vip="dialog.addObj.membership_level"></userDialog>
                        </template>
                        <!-- <template slot=footer>
                           
                        </template> -->
                    </el-dialog>
                    <el-dialog title="编辑用户"  :visible.sync="dialog.editVisible"
                        width="650px"
                        custom-class="add-user-class"
                        :before-close="closeEditDialog"
                        :close-on-click-modal="false"
                        :top="dialog.top"
                    >
                        <template>
                            <userDialog dialogType="edit" :userForm="dialog.editObj" @suerSubmit="editSuerSubmit" @closeDialog = "closeEditDialog" :vip_options ="queryOption.vip_options" :vip="dialog.editObj.vip"></userDialog>
                        </template>
                        <!-- <template slot=footer>
                            <span class="dialog-footer">
                                <el-button class="f14" size="small" @click="closeEditDialog">取消</el-button>
                                <el-button class="f14" size="small" type="primary" @click="editSuer">确定</el-button>
                            </span>
                        </template> -->
                    </el-dialog>
                    <el-dialog title="重置用户密码"
                     :visible.sync="dialog.resetVisible"
                        width="450px"
                        custom-class="reset-password-dialog"
                        :before-close="closeResetPassword"
                        :close-on-click-modal="false"
                        :top="dialog.top"
                        >
                        <template>
                            <userDialogResetPassword :loading="query.loading" :userForm="dialog.item" @suerSubmit="sureResetPassword" @closeDialog = "closeResetPassword"></userDialogResetPassword>
                        </template>
                    </el-dialog>
                    <el-dialog title="余额操作" :visible.sync="dialog.amountVisible" width="500px" custom-class="amount-dialog" :close-on-click-modal="false" :top="dialog.top">
                      <amountChange ref="amountChangeRef" :accountForm = "dialog.item" @closeDialog="closeAmount" @suerSubmit="suerAmount" :loading="dialog.loading"></amountChange>
                    </el-dialog>
                    <el-dialog title="余额变动明细" :visible.sync="dialog.logVisible" width="750px" custom-class="log-dialog" :close-on-click-modal="false" :top="dialog.top">
                      <amountLog v-if="dialog.logVisible" :accountForm = "dialog.item"></amountLog>
                    </el-dialog>
                </div>
            </template>
        </mainBoxHeader>
    </div>
</template>
<script>
import mainBoxHeader from "@/components/mainBoxHeader.vue"
import userDialog from "@/components/userManage/userDialog.vue"
import userDialogResetPassword from "@/components/userManage/userDialogResetPassword.vue"
import amountChange from "@/components/userManage/amountChange.vue"
import amountLog from "@/components/userManage/amountLog.vue"
export default {
    name:"UserManage",
    components:{
        mainBoxHeader,
        userDialog,
        userDialogResetPassword,
        amountChange,
        amountLog
    },
    data(){
        return{
            UserData:{
                description:'管理系统的所有用户账号，您可以查看和管理用户的基本信息。',
               
            },
            query:{
                date:[],
                status:"",
                // nickname:'',
                membership_level:'',
                custom_discount:0,
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
            table:{
                tableData:[],
             
            },
            dialog:{
                loading:false,
                addVisible:false,
                top:'15vh',
                editVisible:false,
                resetVisible:false,
                editObj:{},
                addObj:{
                    // nickname:"",
                    password:"",
                    confirmPassword:"",
                    email:"",
                    membership_level:'1',
                    custom_discount:0,
                    status:1,
                },
                item:{},
                amountVisible:false,
                logVisible:false,

            },
            queryOption:{                
                vip_options:[],
                editVIPOptions:[]
            }

        }
    },
    created(){
        this.getMemberList()
        this.userList()
    },
    methods:{
        //获取会员等级
        getMemberList(type){
           const params = {
            type:type,
           }
           this.$api.memberList(params).then(res=>{
                if(res.code == 1){                   
                    this.queryOption.vip_options = res.data.list.map(item=>{
                        return {id:item.id,name:item.name,can_assign:item.can_assign}
                    })
                    // this.queryOption.vipMap=this.queryOption.vip_options.reduce((acc,cur)=>{
                    //     acc[cur.id] = cur.name
                    //     return acc
                    // },{})
                    
                   
                }else{
                }
           }).catch(e=>{
           })         
        },
        //新增用户按钮点击事件
        addUser(){
            this.dialog.addVisible = true
        },
        //查询按钮点击事件
        getData(){
            this.query.page = 1
            this.userList()

        },
        //获取用户列表 接口
        userList(){
            let data = {
                email:this.query.email,
                status:this.query.status,
                start_time:this.query.date?(this.query.date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[0])), true) :''):'',
                end_time:this.query.date?(this.query.date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[1])), true) :''):'',
                page:this.query.page,
                limit:this.query.pageSize,
                membership_level:this.query.membership_level,      
                // nickname:this.query.nickname
            }
            this.query.loading = true
            this.$api.userList(data).then(res=>{
                this.query.loading = false;
                if(res.code === 1){
                    this.table.tableData = res.data.list;
                    this.query.total = res.data.total;
                }
            }).catch(e=>{
                this.query.loading = false;
            })
        },
        //重置按钮点击事件
        reset(){
            this.query.date=[]
            this.query.email = "";
            this.query.status = "";
            this.query.page = "";
            this.query.membership_level="";
            this.query.custom_discount = 0
            // this.query.nickname="";
            this.userList();
        },
        //表格内状态改变事件
        statusChange(val, row){
            let status = val;
            const oldStatus = val=='1'?'2':'1'
            row.status = oldStatus // 先恢复原状态
            this.query.loading = true
            const data = {
                id: row.id,
                status: status,
                email: row.email,
                // nickname: row.nickname,
            }
            this.$api.userEdit(data).then(res => {
                this.query.loading = false
                if(res.code === 1) {
                    this.$message.success(res.msg)
                    row.status = status // 请求成功后再更新状态
                    this.userList() // 刷新列表
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.query.loading = false
                this.$message.error('状态修改失败')
            })
        },
        //表格内编辑事件
        editData(item){
            this.dialog.editObj = {...item};
            this.dialog.editObj.vip = this.dialog.editObj.membership_level.toString();
            this.dialog.editObj.membership_level = this.dialog.editObj.membership_level.toString()
            this.dialog.editObj.custom_discount = Number(this.dialog.editObj.custom_discount)
            this.dialog.editVisible = true
        },
      
        //表格内删除事件
        deleteData(item){
            this.$confirm(`确定要删除用户"${item.email}"吗？`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.query.loading = true
                this.$api.userDelete({id: item.id}).then(res => {
                    this.query.loading = false
                    if(res.code === 1) {
                        this.$message.success(res.msg)
                        this.userList()
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch(err => {
                    this.query.loading = false
                    this.$message.error('删除用户失败')
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
         //分页
         handleCurrentChange(val) {
            this.query.page = val;
            this.userList();
        },
        handleSizeChange(val) {
            this.query.pageSize = val;
            this.query.page = 1; // 切换每页条数时，重置为第一页
            this.userList();
        },
        //关闭新增用户弹窗
        closeAddDialog(){
            this.dialog.addVisible = false;
            // this.dialog.addObj.nickname="";
            this.dialog.addObj.password="";
            this.dialog.addObj.confirmPassword="";
            this.dialog.addObj.email="";
            this.dialog.addObj.membership_level='1';
            this.dialog.addObj.custom_discount = 0;
            this.dialog.addObj.status=1;
        },
        //新增用户提交
        addSuerSubmit(formData){
            this.query.loading = true
            const data = {
                email: formData.email,
                // nickname: formData.nickname,
                password: this.$md5(formData.password),
                confirm_password: this.$md5(formData.confirm_password),
                membership_level:  formData.membership_level,
                custom_discount:formData.custom_discount,
                status: formData.status
            }
            this.$api.userAdd(data).then(res => {
                this.query.loading = false
                if(res.code === 1) {
                    this.$message.success(res.msg)
                    this.closeAddDialog()
                    this.userList()
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.query.loading = false
                this.$message.error('新增用户失败')
            })
        },
        //关闭编辑用户弹窗
        closeEditDialog(){
            this.dialog.editObj = {};
            this.dialog.editVisible = false
        },
       
        //编辑用户提交
        editSuerSubmit(formData){
            this.query.loading = true
            const data = {
                id: formData.id,
                email: formData.email,
                // nickname: formData.nickname,
                status: formData.status,
                membership_level:formData.membership_level,
                custom_discount:formData.custom_discount
            }
            this.$api.userEdit(data).then(res => {
                this.query.loading = false
                if(res.code === 1) {
                    this.$message.success(res.msg)
                    this.closeEditDialog()
                    this.userList()
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.query.loading = false
                this.$message.error('编辑用户失败')
            })
        },
        //重置密码
        //重置密码按钮点击事件   打开重置密码弹窗
        resetPassword(item){
            this.dialog.resetVisible = true
            this.dialog.item = {
                ...item,
                password:'',
                confirm_password:'',
            }
            console.log( this.dialog.resetVisible)
        },
        //关闭重置密码弹窗
        closeResetPassword(){
            this.dialog.item = {};
            this.query.loading = false;
            this.dialog.resetVisible = false
        },
        //弹窗内确认重置密码
        sureResetPassword(formData){
            const data = {
                password: this.$md5(formData.password),
                confirm_password:  this.$md5(formData.confirm_password),
                id:formData.id
            }
            this.query.loading = true
            this.$api.resetUserPassword(data).then(res => { 
                this.query.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)                    
                    this.closeResetPassword()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err => {                 
                this.query.loading = true
            })
        },
        //操作按钮事件
        handleCommand(command, row){
            this[command](row)
        },
        //余额操作 打开余额操作弹窗
        amountChange(row){
            this.dialog.item = {...row}
            this.dialog.amountVisible = true
        },
        //关闭余额操作弹窗
        closeAmount(){
            this.dialog.amountVisible = false
            this.dialog.loading = false
            this.dialog.item = {}            
            this.$refs.amountChangeRef.clearObj()
        },
        //确认余额操作
        suerAmount(item,formData){
            let data = {
                user_id:item.id,
                ...formData,
            }
            this.dialog.loading = true
            this.$api.balanceOperate(data).then(res=>{
                this.dialog.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)      
                    this.userList()         
                    this.closeAmount()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(e=>{
                this.dialog.loading = false
            })
        },
        //余额变动日志弹窗打开按钮
        showLogo(item){
            this.dialog.item = {...item}
            this.dialog.logVisible = true
        }
      
    }
}
</script>
<style lang="scss" scoped>
    .user-manage-box{
        .user-query-box{
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
    }
</style>