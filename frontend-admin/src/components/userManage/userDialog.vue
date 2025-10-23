<template>
    <div class="form-box">
      <el-form :model="userForm" label-width="100px" :rules="rules" ref="userFormRef" size="small">
        <!-- <el-form-item label="昵称" prop="nickname">
          <el-input v-model="userForm.nickname" placeholder="请输入昵称" size="small"></el-input>
        </el-form-item> -->
        <el-form-item label="密码" prop="password" v-if="dialogType === 'add'">
          <el-input v-model="userForm.password" type="password" placeholder="请输入密码" size="small"></el-input>
        </el-form-item>
        <el-form-item label="确认密码" prop="confirm_password" v-if="dialogType === 'add'">
          <el-input v-model="userForm.confirm_password" type="password" placeholder="请再次输入密码" size="small"></el-input>
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="userForm.email" placeholder="请输入邮箱" size="small"></el-input>
        </el-form-item>
        <el-form-item label="VIP等级" prop="membership_level">
          <el-select class="query-select" v-model="userForm.membership_level" clearable placeholder="请选择" size="small">                            
              <el-option :label="item.name" :value="item.id" v-for="item in vip_options" :key="item.id" :disabled="(!item.can_assign && item.id != vip) || (vip!='5' && vip!='1' && item.id == '1')"></el-option> 
          </el-select>   
        </el-form-item>
        
        <el-form-item label="会员折扣" prop="custom_discount" v-if="userForm.membership_level == 5">
          <el-input-number class="form-input-number" v-model="userForm.custom_discount" :min="0" :max="100" :step="10" :precision="1" placeholder="请设置折扣率"></el-input-number>
          <span class="text-info"><span class="ml5">%</span> <span class="f12 ml8">(百分比，例如：50表示5折，即0.5折)</span></span>
        </el-form-item>

        <el-form-item label="状态">
          <el-switch v-model="userForm.status"
            :active-value="1"
            :inactive-value="2"
            @change="(val) => statusChange(val)" 
            size="small">
          </el-switch>
          <span class="status-text ml8">{{ userForm.status == '1' ? '正常' : '禁用' }}</span>
        </el-form-item>
      </el-form>
      <div class="form-btn">
          <el-button class="f14" size="small" @click="closeDialog">取消</el-button>
          <el-button class="f14" size="small" type="primary" @click="suerSubmit">确定</el-button>     
      </div>
  </div>
</template>
<script>
    export default {
        name:"userDialog",
        props:{
            userForm:{
                type:Object,
                required:true,
                default(){return{}}      
            },
            dialogType:{
                type:String,
                default:'add'
            },
            vip_options:{
              type:Array,
              default(){
                  return []
              }
            },
            vip:{
              type:String,
              default:'1'
            },
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
              loading:false,
              rules:{
                // nickname:[
                //     { required: true, message: '请输入昵称', trigger: 'blur' }
                // ],
                password:[
                  {required: true, message: '密码不能为空，请输入有效的密码。', trigger: 'blur'},
                  {validator: validatePassword, trigger: 'blur'}
                ],
                confirm_password:[
                  {required: true, message: '密码不能为空，请输入有效的密码。', trigger: 'blur'},
                  {validator: validateCheckPassword, trigger: 'blur'}
                ],
                email:[
                  { required: true, message: '请输入邮箱地址', trigger: 'blur' },
                  { type: 'email', message: '请输入正确的邮箱地址', trigger: ['blur', 'change'] }
                ],
                membership_level:[{ required: true, message: '请选择VIP等级', trigger: 'change' },],
                custom_discount:[
                  { required: true, message: '请设置会员折扣', trigger: 'blur' },
                ]
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
        .form-input-number{
          width:168px;
        }
    }
</style>