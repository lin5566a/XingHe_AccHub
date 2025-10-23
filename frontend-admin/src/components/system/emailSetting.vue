<template>
    <div class="email-setting-box">
        <el-form
            ref="emailFormRef"
            :model="emailForm"
            :rules="emailRules"
            label-width="120px"
            class="settings-form"
            size="small"
          >
            <el-form-item label="SMTP服务器" prop="smtp_server">
              <el-input v-model="emailForm.smtp_server" placeholder="请输入SMTP服务器地址" size="small"/>
            </el-form-item>
            <el-form-item label="端口" prop="port">
              <el-input v-model="emailForm.port" placeholder="请输入端口" size="small" />
            </el-form-item>
            <el-form-item label="安全协议" prop="security_protocol">
              <el-select v-model="emailForm.security_protocol" placeholder="请选择安全协议" size="small">
                <el-option label="SSL" value="ssl" />
                <el-option label="TLS" value="tls" />
                <el-option label="无" value="none" />
              </el-select>
            </el-form-item>
            <el-form-item label="用户名" prop="username">
              <el-input v-model="emailForm.username" placeholder="请输入用户名" size="small" />
            </el-form-item>
            <el-form-item label="发件人邮箱" prop="sender_email">
              <el-input v-model="emailForm.sender_email" placeholder="请输入发件人邮箱" size="small" />
            </el-form-item>
            <el-form-item label="称呼" prop="sender_name">
              <el-input v-model="emailForm.sender_name" placeholder="请输入称呼" size="small"/>
            </el-form-item>
            <el-form-item label="授权码/密码" prop="auth_code">
              <el-input v-model="emailForm.auth_code" type="password" placeholder="请输入授权码或密码" size="small" />
            </el-form-item>
            <el-form-item>
              <el-button class='f14' type="primary" @click="saveEmailSettings" size="small">保存设置</el-button>
              <el-button class='f14' type="success" @click="testEmailSettings" size="small">测试邮件</el-button>
              <el-button class='f14' @click="resetEmailForm" size="small">重置</el-button>
            </el-form-item>
          </el-form>

        <!-- 测试邮件弹窗 -->
        <el-dialog
            title="测试邮件"
            :visible.sync="testDialogVisible"
            width="500px"
            :close-on-click-modal="false"
        >
            <el-form
                ref="testFormRef"
                :model="testForm"
                :rules="testRules"
                label-width="100px"
                size="small"
            >
                <el-form-item label="收件人邮箱" prop="email">
                    <el-input v-model="testForm.email" placeholder="请输入收件人邮箱" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="closeTestDialog">取消</el-button>
                    <el-button type="primary" @click="submitTestEmail" :loading="loading">确定</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script>
export default {
    name:"emailSetting",
    data(){
        return{
            loading: false,
            testDialogVisible: false,
            testForm: {
                email: ''
            },
            testRules: {
                email: [
                    { required: true, message: '请输入收件人邮箱', trigger: 'blur' },
                    { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' }
                ]
            },
            emailForm:{
                smtp_server: '',
                port: '',
                security_protocol: 'ssl',
                username: '',
                sender_email: '',
                sender_name: '',
                auth_code: ''
            },
            oldEmailForm:{
                smtp_server: '',
                port: '',
                security_protocol: 'ssl',
                username: '',
                sender_email: '',
                sender_name: '',
                auth_code: ''
            },
            emailRules:{
                smtp_server: [
                    { required: true, message: '请输入SMTP服务器地址', trigger: 'blur' }
                ],
                port: [
                    { required: true, message: '请输入端口', trigger: 'blur' }
                ],
                username: [
                    { required: true, message: '请输入用户名', trigger: 'blur' }
                ],
                sender_email: [
                    { required: true, message: '请输入发件人邮箱', trigger: 'blur' },
                    { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' }
                ],
                sender_name: [
                    { required: true, message: '请输入称呼', trigger: 'blur' }
                ],
                auth_code: [
                    { required: true, message: '请输入授权码', trigger: 'blur' }
                ]
            }
        }
    },
    created(){
        this.getEmailInfo()
    },
    methods:{
        //获取邮件设置信息
        getEmailInfo(){
            this.loading = true
            this.$api.emailInfo().then(res => {
                this.loading = false
                if(res.code === 1){
                    this.oldEmailForm = {...res.data}
                    this.emailForm = {...this.oldEmailForm}
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('获取邮件设置失败')
            })
        },
        //保存设置
        saveEmailSettings(){
            this.$refs.emailFormRef.validate((valid) => {
                if (valid) {
                    this.loading = true
                    this.$api.emailUpdate(this.emailForm).then(res => {
                        this.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.oldEmailForm = {...this.emailForm}
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.loading = false
                        this.$message.error('保存失败')
                    })
                }
            });   
        },
        //测试邮件设置
        testEmailSettings(){
            // console.log('=====')
            this.testDialogVisible = true
        },
        //提交测试邮件
        submitTestEmail(){
            this.$refs.testFormRef.validate((valid) => {
                if (valid) {
                    this.loading = true
                    this.$api.emailTest(this.testForm).then(res => {
                        this.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.testDialogVisible = false
                            this.testForm.email = ''
                            this.$refs.testFormRef.resetFields()
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.loading = false
                        this.$message.error('测试失败')
                    })
                }
            })
        },
        closeTestDialog(){
            this.testDialogVisible = false
            this.testForm.email = ''
            this.$refs.testFormRef.resetFields()
        },
        //重置
        resetEmailForm(){
            this.emailForm = {...this.oldEmailForm}
            this.$refs.emailFormRef.resetFields();
        }
    }
}
</script>
<style lang="scss" scoped>
    
</style>