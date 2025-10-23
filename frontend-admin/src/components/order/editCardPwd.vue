<template>
    <div class="edit-card-pwd-box">
        <el-dialog
                :title="title"
                :visible.sync="dialogVisible"
                width="500px"
                custom-class="edit-card-dialog-class"
                @close="close"
                :close-on-click-modal="false"
                top="15vh"
            >
            <div class="edit-card-pwd-content">
                <div class="order-info">
                    <div class="order-info-item">
                        <span class="item-label">订单号：</span>
                        <span class="item-value">{{ orderObj.order_number }}</span>
                    </div>
                    <div class="order-info-item">
                        <span class="item-label">商品名称：</span>
                        <span class="item-value">{{ orderObj.product_name }}</span>
                    </div>
                    <div class="order-info-item">
                        <span class="item-label">商品分类：</span>
                        <span class="item-value" v-if="orderObj.category">{{ orderObj.category.name }}</span>
                    </div>                    
                    <div class="order-info-item">
                        <span class="item-label">购买数量：</span>
                        <span class="item-value">{{ orderObj.quantity }}</span>
                    </div>                
                    <div class="order-info-item">
                        <span class="item-label">总成本：</span>
                        <span class="item-value">{{ orderObj.cost_price }}</span>
                    </div>
                </div>
                <div class="form-box">
                    <el-form ref="form" :model="formData" :rules="rules" label-width="80px" size="small">
                        <el-form-item label="变动成本" prop="cost_price">
                            <el-input-number class="form-input" v-model="formData.cost_price" :precision="2" :step="1"  size="small"></el-input-number>
                        </el-form-item>   
                        <el-form-item label="卡密信息" prop="card_info">
                            <el-input
                                type="textarea"
                                :rows="4"
                                placeholder="请输入卡密信息"
                                v-model="formData.card_info">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input
                                type="textarea"
                                :rows="3"
                                placeholder="请输入发货备注信息（选填）"
                                v-model="formData.remark">
                            </el-input>
                        </el-form-item>
                    </el-form>
                </div>
            </div>

            <span slot="footer" class="dialog-footer">
                    <el-button class="f14" @click="close" size="small">取 消</el-button>
                    <el-button class="f14" type="primary" @click="suer()" size="small" :disabled="loading">{{suerText}}</el-button>
                </span>
        </el-dialog>
    </div>
</template>
<script>
    export default {
        name: "editCardPwd",
        props:{
            dialogVisible:{
                type:Boolean,
                default:false,
            },
            loading:{
                type:Boolean,
                default:false,
            },
            orderObj:{
                type:Object,
                required:true,
                default:{},
            },
            title:{
                type:String,
                default:''
            },
            suerText:{
                type:String,
                default:''
            },
            
        },
        data(){
            return {
                formData:{
                    cost_price:"",
                    card_info:'',
                    remark:''
                },
                rules:{
                    cost_price:[
                        { required: true, message: '请输入成本价', trigger: 'blur' },
                    ],
                    card_info:[
                        { required: true, message: '请输入卡密信息', trigger: 'blur' },
                    ],
                    remark:[
                        { required: false, message: '', trigger: 'blur' },
                    ],
                }
            }
        },
        mounted() {
            this.formData.card_info = this.orderObj.card_info
        },
        methods:{
            close(){
                this.$emit('close')
            },
            suer(){
                this.$refs['form'].validate((valid) => {
                    if (valid) {
                        this.$emit('suer',this.formData,this.orderObj)
                    } else {
                        return false
                    }
                });
            }
        }
    }
</script>
<style lang="scss">
.edit-card-dialog-class{
    .el-dialog__header{
        padding:24px 20px;
        font-size: 16px;
        font-weight: 500;
    }
}
</style>
<style lang="scss" scoped>
.edit-card-pwd-content{
    border-top:1px solid #ebeef5;
    padding: 20px 24px;
    .order-info{
        margin-bottom: 20px;
        .order-info-item{
            margin-bottom: 8px;
            font-size: 14px;
            .item-label{
                color: #606266;
                margin-right: 5px;
            }
            .item-value{
                color: #303133;
            }
        }
    }
    
    .form-input{
        width:100%;
    }
}
    
</style>