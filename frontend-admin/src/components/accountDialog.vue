<template>
    <div>
        <el-form :model="accountForm" label-width="100px" :rules="rules" ref="accountFormRef" size="small">
            <el-form-item label="用户名" prop="username">
                <el-input v-model="accountForm.username" placeholder="请输入用户名" size="small"></el-input>
            </el-form-item>
           
            <el-form-item label="密码" prop="password" v-if="dialogType === 'add'">
                <el-input v-model="accountForm.password" type="password" placeholder="请输入密码" size="small"></el-input>
            </el-form-item>
            <el-form-item label="确认密码" prop="confirm_password" v-if="dialogType === 'add'">
                <el-input v-model="accountForm.confirm_password" type="password" placeholder="请再次输入密码" size="small"></el-input>
            </el-form-item>
            <el-form-item label="邮箱" prop="email">
                <el-input v-model="accountForm.email" placeholder="请输入邮箱" size="small"></el-input>
            </el-form-item>
            <el-form-item label="状态">
                <el-switch v-model="accountForm.status" :active-value="'1'" :inactive-value="'0'" size="small"></el-switch>
                <span class="status-text ml8">{{ accountForm.status === '1' ? '正常' : '禁用' }}</span>
            </el-form-item>
            <el-form-item label="备注" prop="remark">
                <el-input v-model="accountForm.remark" type="textarea" :rows="3" placeholder="请输入备注信息" size="small"></el-input>
            </el-form-item>
        </el-form>
    </div>
</template>
<script>
    export default {
        name:"accountDialog",
        props:{
            accountForm:{
                type:Object,
                default(){return{}}      
            },
            dialogType:{
                type:String,
                default:'add'
            }
        }, 
        data(){
            return{
                rules:{
                    username: [
                        { required: true, message: '请输入用户名', trigger: 'blur' },
                        { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '请输入密码', trigger: 'blur' },
                        { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' }
                    ],
                    confirm_password: [
                        { required: true, message: '请再次输入密码', trigger: 'blur' },
                        { validator: this.validateConfirmPassword, trigger: 'blur' }
                    ],
                    email: [
                        { required: true, message: '请输入邮箱', trigger: 'blur' },
                        { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' }
                    ]
                }
            }
        },
        methods: {
            validateConfirmPassword(rule, value, callback) {
                if (value !== this.accountForm.password) {
                    callback(new Error('两次输入密码不一致'))
                } else {
                    callback()
                }
            }
        }
    }
</script>