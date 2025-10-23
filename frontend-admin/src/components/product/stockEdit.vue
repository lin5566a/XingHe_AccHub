<template>
    <div class="stock-edit-box">
        <el-dialog :title="title"
             :visible.sync="dialogVisible"
             custom-class="add-stock-dialog-class"
             width="600px"
             :destroy-on-close="false"
             :close-on-click-modal="false"
             :before-close="stockDialogClose"
            >
                <div class="add-stock-dialog-box">
                    <el-form :model="formData" label-width="100px" :rules="stockRules" ref="stockRef"  size="small">
                        <el-form-item label="批次ID" prop="batch_id">
                            <el-input type="text" v-model="formData.batch_id" :disabled="true" placeholder="请输入批次ID"></el-input>
                            <div class="form-tip">批次ID格式：P+日期+批次号，例如：P20250213001</div>
                        </el-form-item>
                        <el-form-item label="卡密信息" prop="card_info" >
                            <el-input
                                type="textarea"
                                v-model="formData.card_info"
                                :rows="4"
                                placeholder="请输入卡密信息，格式：用户名----密码----注册邮箱----邮箱密码"
                            ></el-input>
                        </el-form-item>
                        <el-form-item label="成本价" prop="cost_price">
                            <el-input-number class="form-input" v-model="formData.cost_price" :precision="2" :step="0.01" :min="0" size="small"></el-input-number>
                            <div class="form-tip">为所有导入的卡密设置成本价</div>
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-select v-model="formData.status" placeholder="请选择状态" style="width: 100%;" size="small">
                                <el-option label="已售出" :value="2"></el-option>
                                <el-option label="已锁定" :value="1"></el-option>
                                <el-option label="未售出" :value="0"></el-option>
                                <el-option label="已作废" :value="3"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="备注">
                            <el-input
                                type="textarea"
                                v-model="formData.remark"
                                :rows="2"
                                placeholder="请输入备注信息"
                                size="small"
                            ></el-input>
                        </el-form-item>
                    </el-form>
                </div>
                <template slot=footer>
                    <span class="dialog-footer">
                        <el-button class="f14" @click="stockDialogClose" size="small">取消</el-button>
                        <el-button class="f14" type="primary" @click="submitStock('stockRef')" size="small" :disabled="loading">确定</el-button>
                    </span>
                </template>
            </el-dialog>
    </div>
</template>
<script>
export default {
    name:'editStock',
    data(){
        return{               
            loading:false,
            stockRules:{
                card_info: [
                    { required: true, message: '请输入卡密信息', trigger: 'blur' }
                ],                  
                status: [
                    { required: true, message: '请选择状态', trigger: 'change' }
                ],
                batch_id:[
                    { required: false, message: '', trigger: 'blur' }
                ],
                cost_price:[
                    { required: true, message: '请输入成本价', trigger: 'blur' }
                ],
            }
        }
    },
    props:{
        dialogVisible:{
            type:Boolean,
            default:false
        },
        product_id:{
            type:String,
            default:''
        },
        title:{
            type:String,
            default:''
        },
        formData:{
            type:Object,
            required:true,
        }
    },
    mounted(){
        // console.log('stockDialog',this.formData)
    },
    methods:{
        stockDialogClose(){
            this.$refs.stockRef.resetFields();        
            this.$emit('stockDialogClose')
        },
        submitStock(formName){
            this.$refs[formName].validate((valid) => {
                if (valid) {         
                    let form = {...this.formData}                    
                    this.$emit("submitStock",form,this.product_id) 
                } else {
                    console.log('error submit!!');
                    return false;
                }
            })
        }
    }
}
</script>
<style lang="scss" scoped>
    .stock-edit-box{
        .form-tip{
            margin-top: 5px;
            font-size: 12px;
            color: #909399;
        }
        .form-input{
            width: 100%;
        }
    }
</style>