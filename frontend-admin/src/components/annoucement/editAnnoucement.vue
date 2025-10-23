<template>
    <div class="form-box">
      <el-form :model="announcementForm" label-width="100px" :rules="rules" ref="announcementFormRef" size="small">
        <el-form-item label="公告标题" prop="title">
          <el-input v-model="announcementForm.title" placeholder="请输入公告标题" size="small" maxlength="30"></el-input>
        </el-form-item>
        <el-form-item label="公告内容" prop="content">
            <el-input type="textarea" :rows="4" v-model="announcementForm.content" placeholder="请输入公告内容" maxlength="400" size="small"></el-input>
        </el-form-item>
        <el-form-item label="状态" prop="status">
            <el-radio-group v-model="announcementForm.status">
                <el-radio label="1">启用</el-radio>
                <el-radio label="0">禁用</el-radio>
            </el-radio-group>
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
        name:"userDialog",
        props:{
            announcementForm:{
                type:Object,
                required:true,
                default(){return{}}      
            },     
            
            loading:{
              type:Boolean,
              default:false
            },       
          
        }, 
        data(){
          
            return{
              rules:{
                title:[
                    { required: true, message: '请输入公告标题', trigger: 'blur' }
                ],
                content:[
                    { required: true, message: '请输入公告内容', trigger: 'blur' }
                ],
                status:[
                    { required: false, message: '', trigger: 'blur' }
                ],
              },
            }
        },
        methods:{
          closeDialog(){
            this.$refs.announcementFormRef.resetFields();
            this.$emit("closeDialog")
          },
          suerSubmit(){
            this.$refs.announcementFormRef.validate((valid) => {
                if(valid){
                    this.$emit("suerSubmit", this.announcementForm)
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
        .form-number{
            width: 180px;

        }
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