<template>
    <div class="account-info-box">
        <div class="account-info">
            <div class="account-info-item">
                <span class="info-label f14 gray-text-color">用户邮箱：</span>
                <span class="info-value f14 gray-text-color">{{ accountForm.email || '--'}}</span>
            </div>
            <div class="account-info-item">
                <span class="info-label f14 gray-text-color">当前余额：</span>
                <span class="info-value red-color f16 fb"> ¥{{ accountForm.balance || '0'}}</span>
            </div>
            
        </div>
        <div class="form-box">
            <el-form :model="amountObj" label-width="100px" :rules="rules" ref="formRef" size="small">      
                <el-form-item label="操作类型" prop="type" >
                    <el-radio-group v-model="amountObj.type" size="small">
                        <el-radio label="add">人工增加</el-radio>
                        <el-radio label="reduce">人工扣减</el-radio>
                    </el-radio-group>
                </el-form-item>      
                <el-form-item label="操作金额" prop="amount" >
                    <el-input-number v-model="amountObj.amount" :precision="2" :step="1" :min="0" size="small"></el-input-number>
                </el-form-item>
                <el-form-item label="操作说明" prop="remark" >
                    <el-input type="textarea" :rows="3" placeholder="请输入操作说明"  v-model="amountObj.remark"></el-input>
                </el-form-item>      
            </el-form>
            <div class="form-btn">
                <el-button class="f14" size="small" @click="closeDialog">取消</el-button>
                <el-button class="f14" size="small" type="primary" @click="suerSubmit" :disabled="loading">确定</el-button>     
            </div>
        </div>
    </div>
      
</template>
<script>
    export default {
        name:"amountChangeDialog",
        props:{
            accountForm:{
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
            const validateCheckDescLen =(rule, value, callback)=>{ 
                if (!value) {
                    return callback(new Error('请输入操作说明'));
                }
                if(value.length > 200){
                    callback(new Error('操作说明不能超过200个字符'));
                }else{
                    callback();
                }
            }
            return{                
                amountObj:{
                    type:'add',
                    amount:0,
                    remark:'',
                },
                rules:{                
                    type:[
                        {required: true, message: '请选择操作类型', trigger: 'change'},
                    ],
                    amount:[
                        {required: true, message: '请输入操作金额', trigger: 'blur'},
                    
                    ],
                    remark:[
                        {required: true, message: '请输入操作说明', trigger: 'blur'},
                        {validator: validateCheckDescLen, trigger: 'blur'}
                    ]
                },
            }
        },
        methods:{
          closeDialog(){
            
            this.$emit("closeDialog")
          },
          clearObj(){
            this.$refs.formRef.resetFields();
            this.amountObj = {
                type:'add',
                amount:0,
                remark:'',
            }
          },
          suerSubmit(){
            this.$refs.formRef.validate((valid) => {
                if(valid){
                    this.$emit("suerSubmit",this.accountForm, this.amountObj)
                }else{
                    return false
                }
            });
          }
        }
    }
</script>
<style lang="scss" scoped>
    .account-info{
        background-color: #f5f7fa;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        .account-info-item{
            margin: 5px 0;
        }
    }

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