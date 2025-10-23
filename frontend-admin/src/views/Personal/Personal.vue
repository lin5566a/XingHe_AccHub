<template>
    <div class="personal-container">
        <el-card class="box-card" size="small">
            <template slot="header">
                <div class="card-header">
                    <span>个人信息</span>
                    <el-button type="primary" size="small" @click="handleEdit" v-if="!personalData.isEditing" :disabled="personalData.loading">编辑信息</el-button>
                    <div v-else>
                        <el-button type="success" size="small" @click="handleSave" :disabled="personalData.loading ">保存</el-button>
                        <el-button size="small" @click="handleCancel" :disabled="personalData.loading">取消</el-button>
                    </div>
                </div>
            </template>
            
            <div class="profile-content">
                <div class="avatar-container">
                    <el-avatar :size="120" :src="$baseURL + personalData.userForm.avatar" @error="handleAvatarError" v-loading="personalData.uploadLoading">
                        {{ personalData.userForm.username ? personalData.userForm.username.charAt(0).toUpperCase() :''}}
                    </el-avatar>
                    <el-upload
                        v-if="personalData.isEditing"
                        class="avatar-uploader"
                        :action="$baseURL + '/api/admin/manager/avatar'"
                        :auto-upload="true"
                        :show-file-list="false"
                        :on-change="handleAvatarChange"
                        :before-upload="beforeAvatarUpload"
                        :on-success="handleAvatarSuccess"
                        :headers="{'Authorization': 'Bearer ' + $local.get('token')}"                        
                        name="avatar"
                    >
                        <el-button type="primary" size="small" class="change-avatar-btn" :disabled="personalData.uploadLoading || personalData.loading">更换头像</el-button>
                    </el-upload>
                </div>
                
                <div class="info-container">
                    <el-form 
                        :model="personalData.userForm" 
                        :rules="rules" 
                        ref="userFormRef" 
                        label-width="100px" 
                        class="user-form"
                        :disabled="!personalData.isEditing"
                        size="small"
                    >
                        <el-form-item label="用户名" prop="username">
                            <el-input v-model="personalData.userForm.username" placeholder="请输入用户名" size="small"></el-input>
                        </el-form-item>
                        
                        <el-form-item label="邮箱" prop="email">
                            <el-input v-model="personalData.userForm.email" placeholder="请输入邮箱" size="small"></el-input>
                        </el-form-item>
                        
                        <el-form-item label="角色" v-if="false">
                            <el-tag type="danger" size="small">超级管理员</el-tag>
                        </el-form-item>
                        
                        <el-form-item label="注册时间">
                            <span>{{ personalData.userForm.create_time }}</span>
                        </el-form-item>
                        
                        <el-form-item label="最后登录">
                            <span>{{ personalData.userForm.last_login_time }}</span>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </el-card>
        <el-card class="box-card security-card" size="small">
            <template slot="header">
                <div class="card-header"><span>安全设置</span></div>
            </template>
            
            <div class="security-list">
                <div class="security-item">
                    <div class="security-info">
                        <h4>账户密码</h4>
                        <p>定期更改密码可以保护您的账户安全</p>
                    </div>
                    <el-button type="primary" plain @click="goToResetPassword" size="small">修改密码</el-button>
                </div>

                <el-divider></el-divider>
                
                <div class="security-item">
                    <div class="security-info">
                        <h4>绑定邮箱</h4>
                        <p>已绑定邮箱：{{ maskEmail(personalData.userForm.email) }}</p>
                    </div>
                    <el-button plain @click="handleBindEmail" :disabled="!isEmailBindable" size="small">{{ isEmailBindable ? '更换邮箱' : '已绑定' }}</el-button>
                </div>
                
                <el-divider></el-divider>
                
                <div class="security-item">
                    <div class="security-info">
                        <h4>登录日志</h4>
                        <p>查看您的账号登录记录</p>
                    </div>
                    <el-button plain @click="handleViewLogs" size="small">查看记录</el-button>
                </div>
            </div>
        </el-card>
            <!-- 登录日志对话框 -->
        <el-dialog :visible.sync="logsData.visible" title="登录日志" width="800px" @close="closePreview">
            <el-table :data="logsData.tableData" style="width: 100%" border stripe size="small">
                <el-table-column prop="operation_time" label="登录时间" width="180"></el-table-column>
                <el-table-column prop="ip_address" label="IP地址" width="150"></el-table-column>
                <el-table-column prop="location" label="登录地点"></el-table-column>
                <el-table-column prop="user_agent" label="设备信息" width="200"></el-table-column>
                <el-table-column prop="status" label="状态" width="100">
                    <template scope="scope">
                        <el-tag :type="scope.row.status === '成功' ? 'success' : 'danger'" size="small">
                        {{ scope.row.status }}
                        </el-tag>
                    </template>
                </el-table-column>
            </el-table>
            <div class="pagination-box">
                    <el-pagination background 
                        :total="logsData.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="logsData.page"
                        :page-sizes="logsData.pageSizes"
                        :page-size="logsData.pageSize"
                        :disabled="logsData.loading"
                        layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
          
        </el-dialog>
    </div>
</template>
<script>
export default {
  name:'Personal',
  data(){
    return{
        personalData:{
            loading:false,
            isEditing:false,
            defaultAvatar:'https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png',
            userForm:{
                
            },
            userInfo:{},//用户信息
            uploadLoading:false,
        },
        logsData:{
            visible:false,
            tableData:[],
            loading:false,
            total:0,
            page:1,
            pageSize:10
        },
        rules:{
            username:[
                {required:true,message:'请输入用户名',trigger:'blur'},
                {min:3,max:20,message:'长度在3-20位之间',trigger:'blur'}
            ],
            email:[
                {required:true,message:'请输入邮箱',trigger:'blur'},
                {type:'email',message:'请输入正确的邮箱地址',trigger:'blur'}
            ]
        }
    }
  },
  computed:{
    isEmailBindable(){
        return !!this.personalData.userForm.email;
    }
  },
  created(){
    this.getUserInfo()
  },
  methods:{
    //获取用户信息
    getUserInfo(){
        let userInfo = JSON.parse(this.$local.get("userInfo"))
        this.personalData.userForm = userInfo
        if(!this.personalData.userForm.avatar || this.personalData.userForm.avatar ==''){            
            this.personalData.userForm.avatar = this.personalData.defaultAvatar;
        }
        
    },
    //编辑信息
    handleEdit(){
        this.personalData.isEditing = true
    },
    //保存信息
    handleSave(){
        this.$refs['userFormRef'].validate((valid) => {
          if (valid) {
            this.personalData.loading = true
            this.$api.managerUpdate(this.personalData.userForm).then(res=>{
                this.$local.set("userInfo",JSON.stringify(this.personalData.userForm))
                // 触发用户信息更新事件
                this.$eventBus.$emit('updateUserInfo', this.personalData.userForm)
                this.personalData.loading = false
                this.personalData.isEditing = false
                if(res.code == 1){
                    this.$message({
                        type:"success",
                        message:res.msg
                    })
                    
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{
                this.personalData.loading = false
            })
           
          } else {
            // console.log('表单验证失败!!');
            return false;
          }
        });
    },
    //取消编辑
    handleCancel(){
        this.personalData.isEditing = false;
        this.personalData.uploadLoading = false;
        this.$refs['userFormRef'].resetFields();
    },
    //处理头像加载错误
    handleAvatarError(){
        this.personalData.userForm.avatar = this.personalData.defaultAvatar;        
        this.personalData.uploadLoading = false
    },
    //处理头像上传选择成功
    handleAvatarChange(file){
        if(file.status == 'fail'){
            this.$message.error(res.msg)
            this.personalData.uploadLoading = false
        }
    },
    //上传前检查图片格式及大小
    beforeAvatarUpload(file) {
        const isJPG = file.type === 'image/jpeg';
        const isLt2M = file.size / 1024 / 1024 < 2;

        if (!isJPG) {
          this.$message.error('上传头像图片只能是 JPG 格式!');
          return false
        }
        if (!isLt2M) {
          this.$message.error('上传头像图片大小不能超过 2MB!');
          return false
        }

        this.personalData.uploadLoading = true
        return isJPG && isLt2M;
    },
    //上传成功后赋值图片展示
    handleAvatarSuccess(res) {
        this.personalData.userForm.avatar = res.data.avatar;
        this.$forceUpdate()
        let userInfo = JSON.parse(this.$local.get("userInfo"))
        userInfo.avatar = res.data.avatar
        this.$local.set("userInfo",JSON.stringify(userInfo))
        // 触发头像更新事件
        this.$eventBus.$emit('updateUserInfo', userInfo)
        this.personalData.uploadLoading = false
    },
    //修改密码点击事件
    goToResetPassword(){
        this.$router.push('/resetPwd')
    },
    //绑定邮箱
    handleBindEmail(){
        this.$message({
            message:'功能开发中，敬请期待',
            type:'info'
        })
    },
    //查看登录日志
    handleViewLogs(){
        this.logsData.page = 1
        this.loginLogs()
    },
    loginLogs(){
        this.logsData.visible = true
        this.logsData.loading = true
        let data = {
            page:this.logsData.page,
            limit:this.logsData.pageSize
        }
        this.$api.loginLogs(data).then(res=>{
            this.logsData.loading = false
            if(res.code == 1){
                this.logsData.tableData = res.data.list
                this.logsData.total = res.data.total
            }
        }).catch(err=>{
            this.logsData.loading = false
        })
    },
    //分页
    handleCurrentChange(val){
        this.logsData.page = val
        this.loginLogs()
    },
    handleSizeChange(val){
        this.logsData.pageSize = val
        this.logsData.page = 1
        this.loginLogs()
    },
    //关闭登录日志对话框
    closePreview(){
        this.logsData.visible = false
    },
    // 邮箱脱敏
    maskEmail(email){
        if (!email) return ''
        let parts = email.split('@')
        if (parts.length !== 2) return email        
            let name = parts[0]
            let domain = parts[1]        
        if (name.length <= 3) {
            name = name.charAt(0) + '***'
        } else {
            name = name.substring(0, 3) + '***'
        }        
        return name + '@' + domain
    }
  }
}
</script>
<style lang="scss" scoped>
    .personal-container{
        padding: 20px;
        .box-card{
            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .profile-content{
                display: flex;
                margin-top: 20px;
                .avatar-container{
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    margin-right: 40px;
                    .change-avatar-btn {
                        margin-top: 15px;
                    }
                    .info-container {
                        flex: 1;
                        .user-form {
                            max-width: 500px;
                        }
                    }
                }
            }
            
            &.security-card {
                margin-top: 30px;
                .security-list {
                    padding: 10px 0;
                    .security-item {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 15px 0;
                        .security-info {
                            h4 {
                                margin: 0 0 8px 0;
                                font-size: 16px;
                                color: #303133;
                            }
                            p {
                                margin: 0;
                                font-size: 14px;
                                color: #909399;
                            }
                        }
                    }
                }
            }
        }
        .el-divider {
            margin: 0;
        }
    }
</style>