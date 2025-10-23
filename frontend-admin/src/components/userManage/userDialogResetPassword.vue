<template>
     <div class="form-box">
      <el-form :model="userForm" label-width="100px" :rules="rules" ref="userFormRef" size="small">
       
        <el-form-item label="新密码" prop="password" >
          <el-input v-model="userForm.password" type="password" placeholder="请输入密码" size="small"></el-input>
        </el-form-item>
        <el-form-item label="确认密码" prop="confirm_password" >
          <el-input v-model="userForm.confirm_password" type="password" placeholder="请再次输入密码" size="small"></el-input>
        </el-form-item>      
      </el-form>
      <div class="form-btn">
          <el-button class="f14" size="small" @click="closeDialog">取消</el-button>
          <el-button class="f14" size="small" type="primary" @click="suerSubmit" :disabled="loading">确定</el-button>     
      </div>
  </div>
</template>
<script>
    export default {
        name:"resetPasswordDialog",
        props:{
            userForm:{
                type:Object,
                required:true,
                default(){return{}}      
            },
            loading:{
                type:Boolean,
                default:false
            }
        }, 
        data(){
            const validatePassword = (rule, value, callback) => {
              if (!value) {
                  return callback(new Error('请输入密码。'));
              }
              if(value.length < 6 || value.length > 16){
                  return callback(new Error('长度在 6 到 16 个字符'));
              }
              callback();
            };
            const validateCheckPassword = (rule, value, callback) => {
              if (!value) {
                  return callback(new Error('请再次输入密码'));
              }
              if (value !== this.userForm.password) {
                  callback(new Error('两次输入密码不一致!'));
              } else {
                  callback();
              }
            };
            return{
              rules:{
              
                password:[
                  {required: true, message: '密码不能为空，请输入有效的密码。', trigger: 'blur'},
                  {validator: validatePassword, trigger: 'blur'}
                ],
                confirm_password:[
                  {required: true, message: '密码不能为空，请输入有效的密码。', trigger: 'blur'},
                  {validator: validateCheckPassword, trigger: 'blur'}
                ],
              },
            }
        },
        methods:{
          statusChange(val){

          },
          closeDialog(){
            this.$refs.userFormRef.resetFields();
            this.$emit("closeDialog")
          },
          suerSubmit(){
            this.$refs.userFormRef.validate((valid) => {
                if(valid){
                    this.$emit("suerSubmit", this.userForm)
                }else{
                    return false
                }
            });
          }
        }
    }
</script>
<style lang="scss" scoped>
    .form-box{
        .form-btn{
            display: flex;
            justify-content: flex-end;
            align-items:center;
        }
        .form-input{
            width: 100%;
        }
    }
</style>