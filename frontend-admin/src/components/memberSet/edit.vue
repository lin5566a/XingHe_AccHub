<template>
    <div class="form-box">
      <el-form :model="memberForm" label-width="130px" :rules="rules" ref="memberFormRef" size="small">
        <el-form-item label="等级名称" prop="name">
          <el-input v-model="memberForm.name" placeholder="请输入等级名称" size="small"></el-input>
        </el-form-item>
        <el-form-item label="累充升级条件" prop="upgrade_amount" v-if="memberForm.id !='5'">
            <el-input-number class="form-number" v-model="memberForm.upgrade_amount" :step="100" :min="0"></el-input-number>
            <span class="f12 gray-icon-color ml10">元（用户累计充值达到此金额时自动升级）</span>
        </el-form-item>
        <el-form-item label="会员折扣" prop="discount" v-if="memberForm.id !='5'">
            <el-input-number class="form-number"  v-model="memberForm.discount" :step="0.1" :min="0"></el-input-number>
            <span class="f12 gray-icon-color ml10">%（百分比，例如：50表示5折，即0.5折）</span>
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input type="textarea" :rows="2" v-model="memberForm.remark" placeholder="" size="small"></el-input>
        </el-form-item>        
        <el-form-item label="会员介绍" prop="description">
          <el-input type="textarea" :rows="3" v-model="memberForm.description" placeholder="请输入会员介绍" size="small"></el-input>
        </el-form-item>
        <!-- <el-form-item label="启用状态" prop="status" v-if="memberForm.id =='5'">
          <el-switch v-model="memberForm.status"
            :active-value="1"
            :inactive-value="2"
            @change="(val) => statusChange(val)" 
            size="small">
          </el-switch>
          <span class="status-tip">关闭后，超级VIP功能将被禁用，后台可选择用户为VIP1/2/3</span>
        </el-form-item>          -->
      </el-form>
      <div class="form-btn">
          <el-button class="f14" size="small" @click="closeDialog">取消</el-button>
          <el-button class="f14" size="small" type="primary" @click="suerSubmit" :disabled = "loading">确定</el-button>     
      </div>
    </div>
</template>
<script>
    export default {
        name:"userDialog",
        props:{
            memberForm:{
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
                name:[
                    { required: true, message: '请输入等级名称', trigger: 'blur' }
                ],
                upgrade_amount:[
                    { required: true, message: '请输入累计充值升级条件', trigger: 'blur' }
                ],
                discount:[
                    { required: true, message: '请输入会员折扣', trigger: 'blur' }
                ],
                remark:[ 
                    { required: false, message: '', trigger: 'blur' }
                ],
                description:[
                    { required: true, message: '请输入会员介绍', trigger: 'blur' }
                ]
              },
            }
        },
        methods:{
          closeDialog(){
            this.$refs.memberFormRef.resetFields();
            this.$emit("closeDialog")
          },
          suerSubmit(){
            this.$refs.memberFormRef.validate((valid) => {
                if(valid){
                    this.$emit("suerSubmit", this.memberForm)
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
        .status-tip{
          margin-left: 10px;
          color: #909399;
          font-size: 12px;
        }
    }
</style>