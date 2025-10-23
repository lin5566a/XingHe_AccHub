<template>
    <div class="payment-set-box">
        <mainBoxHeader :titleName="'支付等待时间：' + paymentData.timeout + ' 分钟'" titleClass="payment-title" :noDescription="true" :hasHeader = "true">
            <template slot="oprBtn">
                <div class="edit-time">
                    <el-button type="text" @click="editTimeout"> <i class="el-icon-edit-outline"></i> 编辑</el-button>
                </div>
            </template>
            <template slot="pageCon">
                <div class="content">
                    <el-tabs class="payment-tabs" v-model="paymentData.activeName" :disabled="paymentData.loading" @tab-click="changePayment">
                        <el-tab-pane v-for="item in paymentData.paymentList" :key="item.payment_method" :label="item.payment_method_text" :name="item.payment_method">
                            <div class="payment-table-box">
                                <paymentTable :tableData = item.channels @editData="editData" :payment_method="item.payment_method" :payment_method_text ="item.payment_method_text"></paymentTable>
                            </div>
                        </el-tab-pane>
                    </el-tabs>
                </div>
            </template>
        </mainBoxHeader>
        <div class="dialog-box">
            <el-dialog
                title="编辑支付通道"
                :visible.sync="dialog.visible"
                width="500px"
                :before-close="handleClose"
                :close-on-click-modal="false">
                <div class="form-box">
                    <el-form :model="dialog.form" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm" size="small">
                        <el-form-item label="通道名称" prop="channel_name">
                            <el-input v-model="dialog.form.channel_name" size="small"></el-input>
                        </el-form-item>
                        <el-form-item label="支付方式" prop="payment_method">
                            <el-select v-model="dialog.form.payment_method" placeholder="请选择活动区域" :disabled="true" size="small">
                                <el-option :label="dialog.form.payment_method_text" :value="dialog.form.payment_method"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="送单权重" prop="weight">
                            <el-input-number controls-position="right" v-model="dialog.form.weight" :min="0"></el-input-number>
                            <div class="form-tip yellow" v-if="weightTip">提示：权重为0的启用通道实际送单比例为0%。</div>
                        </el-form-item>
                        <el-form-item label="手续费" prop="fee_rate">
                            <el-input-number controls-position="right" v-model="dialog.form.fee_rate" :precision="2" :min="0" :step="0.1"></el-input-number>%
                        </el-form-item>
                        <el-form-item label="单笔限额" prop="min_amount">
                            <el-input-number controls-position="right" placeholder="最低限额" v-model="dialog.form.min_amount" :precision="0" :min="0" :max="Number(dialog.form.max_amount)" :step="10"></el-input-number>
                            至
                            <el-input-number controls-position="right" placeholder="最高限额（0表示不限）" v-model="dialog.form.max_amount" :precision="0" :min="Number(dialog.form.min_amount)" :step="100"></el-input-number>
                            <div class="form-tip">单笔交易限额范围，0表示不限</div>
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-switch v-model="dialog.form.status" 
                            active-value="1"
                            inactive-value="0"
                            ></el-switch>
                        </el-form-item>
                        
                    </el-form>
                </div>
                
                <span slot="footer" class="dialog-footer">
                    <el-button @click="handleClose">取 消</el-button>
                    <el-button type="primary" :disabled="paymentData.loading" @click="paymentEdit">确 定</el-button>
                </span>
            </el-dialog>
        </div>
        <div class="dialog-box">
            <el-dialog
                title="编辑支付等待时间"
                :visible.sync="timeoutDialog.visible"
                width="400px"
                :before-close="handleTimeoutClose"
                :close-on-click-modal="false">
                <div class="form-box">
                    <el-form :model="timeoutDialog.form" :rules="timeoutRules" ref="timeoutForm" label-width="100px" class="demo-ruleForm" size="small">
                        <el-form-item label="等待时间" prop="order_timeout">
                            <el-input-number :style="{width:'168px'}" controls-position="right" v-model="timeoutDialog.form.order_timeout" :min="1" :max="60" label="分钟"></el-input-number>
                            <span class="unit ml8">分钟</span>
                        </el-form-item>
                    </el-form>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button size="small" @click="handleTimeoutClose">取 消</el-button>
                    <el-button size="small" type="primary" :disabled="paymentData.loading" @click="saveTimeout">确 定</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
</template>
<script>
import mainBoxHeader from "@/components/mainBoxHeader.vue"
import paymentTable from "@/components/payment/paymentTable.vue";
export default {
    components:{
        mainBoxHeader,
        paymentTable
    },
    data(){
        var checkAmount = (rule, value, callback) => {
        if ((!value && value != 0) || (!this.dialog.form.max_amount && this.dialog.form.max_amount != 0)) {
          return callback(new Error('单笔限额不能为空'));
        }
        callback()
      };
        return{
            paymentData:{
                loading:false,
                activeName:'wechat',
                paymentList:[
                    {
                        "payment_method": "wechat",
                        "payment_method_text": "微信支付",
                        "channels": []
                    },
                    {
                        "payment_method": "alipay",
                        "payment_method_text": "支付宝",
                        "channels": []
                    },
                    {
                        "payment_method": "usdt",
                        "payment_method_text": "USDT",
                        "channels": []
                    }
                ],
                timeout: '--',
            },
            dialog:{
                visible:false,
                form:{},              
            },
            rules:{
                channel_name: [
                    { required: true, message: '通道名称不能为空', trigger: 'blur' },
                ],
                payment_method:[
                    { required: false, message: '', trigger: 'blur' },
                ],
                weight:[
                    { required: true, message: '送单权重不能为空', trigger: 'blur' },
                ],
                fee_rate:[
                    { required: true, message: '手续费不能为空', trigger: 'blur' },
                ],
                status:[
                    { required: false, message: '', trigger: 'blur' },
                ],
                min_amount:[
                    { required: true, message: '单笔限额不能为空', trigger: 'blur' },
                    { validator: checkAmount, trigger: 'blur' }
                ],
            },
            timeoutDialog: {
                visible: false,
                form: {
                    order_timeout: 1
                }
            },
            timeoutRules: {
                order_timeout: [
                    { required: true, message: '请输入1-60分钟的有效时间', trigger: 'blur' }
                ]
            }
        }
    },
    computed:{
        weightTip(){
            return this.dialog.form.weight == 0
        }
    },
    created(){
        this.getPaymentList()
        this.getSystemInfo()
    },
    methods:{
        setDefault(){
            this.paymentData.paymentList = [
                {
                    "payment_method": "wechat",
                    "payment_method_text": "微信支付",
                    "channels": []
                },
                {
                    "payment_method": "alipay",
                    "payment_method_text": "支付宝",
                    "channels": []
                },
                {
                    "payment_method": "usdt",
                    "payment_method_text": "USDT",
                    "channels": []
                }
            ]
        },
        getPaymentList(type){
            this.paymentData.loading = true;
            this.$api.paymentList().then(res=>{
                this.paymentData.loading = false;
                if(res.code == 1){
                    this.paymentData.paymentList = res.data.list;
                    this.paymentData.activeName = type ? type :this.paymentData.paymentList[0].payment_method;
                    console.log(this.paymentData.activeName,'this.paymentData.activeName')
                }else{
                    // this.setDefault()
                    this.$message.error(res.msg);
                }
            console.log(res);
            }).catch(e=>{
                this.paymentData.loading = false;
                // this.setDefault()
            })
        },
        changePayment(val){
            // console.log(val)
            // this.getPaymentList(val.name)

        },
        //编辑列表
        editData(item,payment_method,payment_method_text){
            this.dialog.visible = true;
            this.dialog.form = {...item,payment_method:payment_method,payment_method_text:payment_method_text}
        },
        //编辑接口
        paymentEdit(){
            console.log(this.dialog.form)
            let data = {
                id:this.dialog.form.channel_id,
                name:this.dialog.form.channel_name,
                rates:[
                    {
                        id:this.dialog.form.id,
                        payment_method:this.dialog.form.payment_method,
                        weight:this.dialog.form.weight,
                        fee_rate:this.dialog.form.fee_rate,
                        status:this.dialog.form.status,
                        max_amount:this.dialog.form.max_amount,
                        min_amount:this.dialog.form.min_amount,
                    }
                ]
            }
            this.paymentData.loading = true;
            this.$api.paymentEdit(data).then(res=>{                
                this.paymentData.loading = false;
                if(res.code == 1){
                    this.getPaymentList(this.paymentData.activeName);
                    this.$message({
                        message: res.msg,
                        type: 'success'
                    });
                    this.handleClose()
                }else{
                    this.$message({message: res.msg,type: 'error'});
                }

            }).catch(e=>{
                this.paymentData.loading = false;
            })
        },
        //关闭弹窗
        handleClose(){
            this.dialog.visible = false;
            this.dialog.form = {}
        },
        // 获取系统信息
        getSystemInfo() {
            this.$api.systemInfo().then(res => {
                if (res.code === 1) {
                    this.paymentData.timeout = res.data.order_timeout
                }
            })
        },
        // 编辑等待时间
        editTimeout() {
            this.timeoutDialog.form.order_timeout = Number(this.paymentData.timeout)
            this.timeoutDialog.visible = true
        },
        // 保存等待时间
        saveTimeout() {
            this.$refs.timeoutForm.validate((valid) => {
                if (valid) {
                    this.paymentData.loading = true
                    this.$api.systemEdit({
                        order_timeout: this.timeoutDialog.form.order_timeout
                    }).then(res => {
                        this.paymentData.loading = false
                        if (res.code === 1) {
                            this.$message.success(res.msg)
                            this.getSystemInfo()
                            this.handleTimeoutClose()
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(() => {
                        this.paymentData.loading = false
                    })
                }
            })
        },
        // 关闭等待时间弹窗
        handleTimeoutClose() {
            this.timeoutDialog.visible = false
            this.$refs.timeoutForm.resetFields()
        }
    }
}
</script>
<style lang="scss">
.payment-set-box{
    .edit-time{
        .el-button--text{
            padding: 0
        }
    }
    .payment-title{
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #606266 !important;
        margin-right: 8px !important;
    }
}
</style>
<style lang="scss" scoped>
    .payment-set-box{    
        .edit-time{
            flex:1
        }  
        .content{
            padding: 10px 0;
            .payment-tabs{
                .payment-table-box{
                    margin-top: 26px;
                }
               
            }
        }
        .dialog-box{
            .form-tip{
                font-size: 12px;
                color: #909399;
                line-height: 1.5;
                margin-top: 4px;
                &.yellow{
                    color: #e6a23c;
                }
            }
        }
    }
</style>