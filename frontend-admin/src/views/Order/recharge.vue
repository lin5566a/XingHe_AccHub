<template>
    <div class="order-view">
        <mainBoxHeader titleName="充值订单列表" :description="orderData.description">
            <div slot="oprBtn">
                <span></span>
                <!-- <el-button class="f14" @click="download" type="primary" icon="el-icon-download" size="small" :disabled="exportLoading ">导出所有订单</el-button>              -->
            </div>
            <template slot="pageCon">
                <div class="order-query-box">
                    <div class="query-item">
                        <span class="query-label mr12">订单号</span>
                        <el-input class="query-input" placeholder="请输入订单号" v-model="query.order_number" clearable size="small"></el-input>
                    </div>   
                    <!-- <div class="query-item">
                        <span class="query-label mr12">用户昵称</span>
                        <el-input class="query-input" placeholder="请输入用户昵称" v-model="query.nickname" clearable size="small"></el-input>
                    </div>   -->
                 
                    <div class="query-item">
                        <span class="query-label mr12">通道名称</span>
                        <el-input class="query-input" placeholder="请输入通道名称" v-model="query.channel_id" clearable size="small"></el-input>
                       
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">支付方式</span>
                        <el-select class="query-select" v-model="query.payment_method" clearable placeholder="请选择" size="small">
                            <el-option :label="item.name" :value="item.id" v-for="item in queryOption.payment_methods" :key="item.id"></el-option>                            
                        </el-select>   
                    </div>     
                    <div class="query-item">
                        <span class="query-label mr12">订单状态</span>
                        <el-select class="query-select" v-model="query.status" clearable placeholder="请选择" size="small">                            
                            <el-option :label="item.name" :value="item.id" v-for="item in queryOption.status" :key="item.id"></el-option> 
                        </el-select>   
                    </div> 
                    <div class="query-item">
                        <span class="query-label mr12">下单时间</span>
                        <el-date-picker v-model="orderData.date" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="small" :picker-options="pickerOptions"></el-date-picker>
                    </div> 
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="loading">重置</el-button>
                    </div>
                                     
                </div>
                <!-- 添加总金额和导出按钮 -->
                <div class="search-summary">
                    <div class="total-amount">
                        <div class="total-item">
                            <span class="mr10">总订单数：</span>
                            <span class="amount-value blue-color">{{ table.all_total }}</span>
                        </div>
                        <div class="total-item">
                            <span class="ml20 mr10">成功订单数：</span>
                            <span class="amount-value green-color">{{table.success_total}}</span>
                        </div>
                        <div class="total-item">
                            <span class="ml20 mr10">订单总金额：</span>
                            <span class="amount-value red-color">¥{{table.total_amount.toFixed(2) }}</span>
                        </div>
                        <div class="total-item">
                            <span class="ml20 mr10">总手续费：</span>
                            <span class="amount-value yellow-color">¥{{table.total_fee}}</span>
                        </div>
                        <div class="total-item">
                            <span class="ml20 mr10">总入账金额：</span>
                            <span class="amount-value black-color">¥{{table.total_arrive}}</span>
                        </div>
                        <div class="total-item">
                            <span v-if="table.multipleSelection.length > 0" class="ml20 mr10">已选择：{{ table.multipleSelection.length }}笔订单</span>
                            <span v-if="table.multipleSelection.length > 0" class="amount-value red-color">¥{{ selectedOrdersTotal }}</span>
                        </div>
                    </div>
                    <div class="action-btns">
                        <el-button class="f14" type="success" icon="el-icon-download" size="small"  @click="exportSelected"> 
                        导出选中订单
                        </el-button>
                    </div>
                </div>
                <!-- 表格 -->
                <div class="data-table">
                    <el-table :data="table.dataTable" style="width: 100%" v-loading="loading" border stripe @selection-change="handleSelectionChange" size="small">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <!-- <el-table-column prop="id" label="订单ID" width="100"></el-table-column> -->
                        <el-table-column prop="order_no" label="订单号" width="180"></el-table-column>                        
                        <!-- <el-table-column prop="nickname" label="用户昵称" min-width="180"></el-table-column>              -->
                        <!-- <el-table-column prop="product_name" label="充值前余额" min-width="180"></el-table-column>             -->
                        <el-table-column prop="product_name" label="充值金额" min-width="180">
                            <template scope="scope">
                                <div class="price f14 red-color fb">¥{{ Number(scope.row.recharge_amount).toFixed(2) }}</div>
                                <div class="price f12 green-color fb">{{ Number(scope.row.usdt_amount).toFixed(2) }} USDT</div>
                            </template>
                        </el-table-column>      
                        <!-- <el-table-column prop="product_name" label="充值后余额" min-width="180"></el-table-column>      -->
                        <el-table-column prop="fee" label="手续费" width="100">
                            <template scope="scope">
                                <div class="fee f14 yellow-color fb">¥{{ Number(scope.row.fee || 0).toFixed(2) }}</div>
                                <div class="price f12 green-color fb">{{ Number(scope.row.usdt_fee || 0).toFixed(2) }} USDT</div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="arrive_amount" label="入账金额" min-width="180"></el-table-column>                         
                        <el-table-column prop="channel_name" label="通道名称" width="180"></el-table-column> 
                        <el-table-column prop="payment_method" label="支付方式" width="100">
                            <template scope="scope">
                                <el-tag  effect="plain" v-if="scope.row.payment_method" :type="getPaymentMethodType(scope.row.payment_method)" size="small">
                                    {{ getPaymentMethodLabel(scope.row.payment_method) }}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="订单状态" width="100">
                            <template scope="scope">
                                <el-tag :type="getStatusType(scope.row.status)" size="small">
                                    {{ scope.row.status_text }}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="创建时间" width="180"></el-table-column>
                        <el-table-column prop="finished_at" label="完成时间" width="180"></el-table-column>                       
                        <el-table-column label="操作" width="180" fixed="right">
                            <template scope="scope">
                                <div class="action-buttons">
                                    <el-dropdown @command="handleCommand($event, scope.row)">
                                        <el-button size="mini" type="primary">
                                            操作<i class="el-icon-arrow-down el-icon--right"></i>
                                        </el-button>
                                        <el-dropdown-menu slot="dropdown">
                                            <el-dropdown-item command="surePay" v-if="scope.row.status == '0'" >确认支付</el-dropdown-item>
                                            <el-dropdown-item command="returnBack" v-if="scope.row.status == '1'">退款</el-dropdown-item>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="pagination-box">
                    <el-pagination background :total="table.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="query.page"
                        :page-sizes="table.pageSizes"
                        :page-size="query.limit"
                        :disabled="loading"
                        layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
            </template>
        </mainBoxHeader>
       <div class="dialog-box">
        <el-dialog
                title="订单退款"
                :visible.sync="returnDialog.dialogVisible"
                width="550px"
                custom-class="return-class"
                @close="returnClose"
                :close-on-click-modal="false"
                :top="returnDialog.top"
            >
                <div class="return-box">
                    <el-form ref="returnForm" :model="dataItem" :rules="returnRules" label-width="100px" size="small">
                        <el-form-item label="订单号" prop="order_number">
                            <el-input type="text" :disabled="true" placeholder="" v-model="dataItem.order_no"></el-input>
                        </el-form-item>
                        <!-- <el-form-item label="用户昵称" prop="nickname">
                            <el-input type="text" :disabled="true" placeholder="" v-model="dataItem.nickname"></el-input>
                        </el-form-item> -->
                        <el-form-item label="通道名称" prop="channel_name">
                            <el-input type="text" :disabled="true" placeholder="" v-model="dataItem.channel_name"></el-input>
                        </el-form-item>
                        <el-form-item label="充值金额" prop="purchase_price">
                            <el-input type="text" :disabled="true" placeholder="" v-model="dataItem.recharge_amount"></el-input>
                        </el-form-item>
                        <el-form-item label="退款金额" prop="price">
                            <el-input-number style="width:100%" v-model="dataItem.price" :min="0" :max="Number(dataItem.recharge_amount)"  :step="1" :precision="2"></el-input-number>
                           
                        </el-form-item>
                        <el-form-item label="退款说明" prop="remark">
                            <el-input
                                type="textarea"
                                :rows="3"
                                placeholder="请输入退款说明"
                                v-model="dataItem.remark">
                            </el-input>
                        </el-form-item>
                    </el-form>
                    
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button class="f14" @click="returnClose" size="small">取 消</el-button>
                    <el-button class="f14" type="primary" @click="returnSuer('returnForm')" size="small" :disabled="loading">确认退款</el-button>
                </span>
            </el-dialog>
       </div>
    </div>
</template>
<script>
import  mainBoxHeader from '@/components/mainBoxHeader.vue'

export default {
    name:'Order',
    components:{
        mainBoxHeader
    },
    data() {
        return{
            loading:false,
            exportLoading:false,
            orderData:{
                orderNumber:'',
                description:'管理所有用户充值余额的订单信息，包括充值金额、支付方式、订单状态等。您可以查看订单详情和确认支付操作。',
                date: [],
                totalAmount:0,
            },
            // 商品分类列表
            category:[],
            table:{
                success_total:0,
                total_arrive:0,
                total_fee:0,
                total_amount:0,   
                all_total:0,             
                multipleSelection:[],
                selectedTotalAmount:0,
                dataTable:[
                ],
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
            },
           
            query:{
                order_number:'',
                // nickname:'',
                channel_id:'',
                payment_method:'',
                status:'',
                start_time:'',
                end_time:'',
                page:1,
                limit:10,
            },
            
            pickerOptions: {
                shortcuts: [{
                    text: '今天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 0);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '昨天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                    end.setTime(end.getTime() - 3600 * 1000 * 24 * 1);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '最近三天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 3);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '最近一周',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                    picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                    picker.$emit('pick', [start, end]);
                    }
                }]
            },
            queryOption:{
                payment_methods:[],
                status_options:[],
            },
            returnDialog:{
                dialogVisible:false,
                top:'15vh',
            },
            returnRules:{
                order_number:[{ required: false, message: '请输入订单号', trigger: 'blur' }],
                // nickname:[{ required: false, message: '请输入用户昵称', trigger: 'blur' }],
                channel_name:[{ required: false, message: '请输入通道名称', trigger: 'blur' }],
                purchase_price:[{ required: false, message: '请输入充值金额', trigger: 'blur' }],
                price:[{ required: true, message: '请输入退款金额', trigger: 'blur' }],
                remark:[{ required: false, message: '请输入退款备注信息', trigger: 'blur' }],
            },
            dataItem:{
                price:0,
                remark:'',
            },
            num:0,
           
        }
    },
    computed: {
        // 计算选中订单的总金额
        selectedOrdersTotal() {
            let t = this.table.multipleSelection.reduce((sum, order) => {
                return sum + Number(order.recharge_amount || 0);
            }, 0);
            let total = t.toFixed(2);
            console.log(t,'==选中订单总金额==',total);
            return total ; // 保证返回数值
        }
    },
    created() {
        this.categoryList();
        this.queryOptions();
        this.orderList();
       
    },
    methods:{
        //计算窗口位置
        getDialogTop(className){
            this.$nextTick(() => {			
                let documentHeight = document.documentElement.clientHeight
                let dialogdiv =  document.getElementsByClassName(className)[0].offsetHeight
                this.dialog.top = (documentHeight - dialogdiv > 20)?(( documentHeight - dialogdiv) / 2 +'px'):'10px'
                // console.log(documentHeight,'===',dialogdiv,'===',this.dialog.top)
            })
        },
        download(){
            let data={
            }
            this.exportLoading = true;
            this.$api.rechargeOrderExport(data).then(res=>{
                this.exportLoading = false;
                if(res.code == 1){
                    let url = res.data.url;
                    window.open(this.$baseURL + url,'_blank');
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                    });
                }
            }).catch(e=>{
                this.exportLoading = false;
            })
        },
        //重置
        reset(){
            //时间重置
            this.query.order_number = '';
            this.query.payment_method = '';
            this.query.status = '';
            this.query.start_time = '';
            this.query.end_time = '';
            this.query.page=1;
            this.query.limit=10;
            this.query.channel_id = ''
            this.orderData.date = [];
            this.table.total = 0;
            this.orderList()
        },
        //发货方式  支付方式   订单状态 筛选项查询
        queryOptions(){
            this.$api.rechargeOptions().then(res=>{ 
                if(res.code == 1){
                    this.queryOption = res.data;
                   
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                    });
                }
                
            })
        },
        //查询商品分类
        categoryList(){
            this.$api.categoryList().then(res=>{
                if(res.code === 1){
                    this.category = res.data.list;
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                    });
                }
            })
        },
        //查询按钮点击事件
        getData(){
            this.query.page = 1;
            this.query.start_time = this.orderData.date?(this.orderData.date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.orderData.date[0])), true) :''):''
            this.query.end_time  =  this.orderData.date?(this.orderData.date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.orderData.date[1])), true) :''):''            
            this.orderList()
        },
        //订单列表查询接口
        orderList(){
            this.loading=true;
            this.$api.rechargeOrderList(this.query).then(res=>{
                this.loading=false;
                if(res.code == 1){
                    this.table.dataTable = res.data.list;
                    this.table.all_total = res.data.all_total;
                    this.table.total_amount= res.data.total_amount;
                    this.table.total_arrive = res.data.total_arrive;
                    this.table.success_total = res.data.success_total;
                    this.table.total_fee = res.data.total_fee;
                    this.table.total = res.data.total;
                    this.$forceUpdate()
                }else{
                    this.table.dataTable=[]
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                    });
                }
            }).catch(e=>{
                this.loading=false;
                this.table.dataTable=[]
            })
        },
        //分页
        handleCurrentChange(val) {
            this.query.page = val;
            this.orderList();
        },
        handleSizeChange(val) {
            this.query.limit = val;
            this.query.page = 1; // 切换每页条数时，重置为第一页
            this.orderList();
        },
        // 多选相关
        handleSelectionChange(val) {
            this.table.multipleSelection = val;
        },   
        // 获取支付方式标签
        getPaymentMethodLabel(value) {
            const method = this.queryOption.payment_methods.find(item => item.id === value);
            return method ? method.name : value;
        },
        // 获取支付方式标签类型
        getPaymentMethodType(value) {
            switch(value) {
                case 'wechat':
                    return 'success';  // 绿色
                case 'alipay':
                    return 'primary';  // 蓝色
                case 'usdt':
                    return 'danger';   // 红色
                default:
                    return 'info';
            }
        },
        // 获取状态标签
        // getStatusLabel(value) {
        //     // 确保value是字符串类型
        //     const statusValue = String(value);
        //     const status = this.queryOption.status.find(item => String(item.value) === statusValue);
        //     return status ? status.label : value;
        // },
        // 获取状态标签类型
        getStatusType(value) {
            // 确保value是字符串类型
            const statusValue = String(value);
            switch(statusValue) {
                case '1': // 已完成
                    return 'success';  // 绿色
                case '2': // 已退款
                    return 'danger';  // 
                case '0': // 待付款
                    return 'warning';  // 黄色
                default:
                    return 'info';
            }
        },
       
        // 导出选中订单
        exportSelected(){
            let data = {
                type: "selected",
                ids: this.table.multipleSelection.map(item => item.id),
                ...this.query
            }
            this.exportLoading = true;
            this.$api.rechargeOrderExport(data).then(res=>{
                this.exportLoading = false;
                if(res.code == 1){
                    let url = res.data.url;
                    window.open(this.$baseURL + url,'_blank');
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                    });
                }
            }).catch(e=>{
                this.exportLoading = false;
            })
        },
        //退款
        returnBack(row){
            this.dataItem = {...this.dataItem,...row}
            this.returnDialog.dialogVisible = true
            this.dataItem.price = this.dataItem.recharge_amount

            // console.log('退款 ',row)
        },
       //确认支付
       surePay(row){

        this.$confirm(`确认将订单 ${row.order_no} 标记为已完成吗？`, '确认操作', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.rechargeOrderConfirm({id:row.id}).then(res=>{
                    if(res.code == 1){
                        this.orderList();
                        this.$message.success(res.msg)
                    }else{
                        this.$message.error(res.msg)
                    }
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消操作'
                });          
            });

       },
        handleCommand(command, row){
            switch(command) {
                case 'surePay':
                    this.surePay(row);
                    break;              
                case 'returnBack':
                    this.returnBack(row);
                    break;
              
            }
        },
         //关闭退款弹窗
         returnClose(){
            this.dataItem = {}
            this.returnDialog.dialogVisible = false
        },
        //确认退款
        returnSuer(formName){
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    this.$confirm(`确定要退款 ¥${this.dataItem.price.toFixed(2)} 吗？`, '确认退款', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let data ={
                            "id": this.dataItem.id,
                            "refund_amount": this.dataItem.price,
                            "refund_remark": this.dataItem.remark,
                        }
                        this.loading = true
                        this.$api.rechargeOrderRefund(data).then(res=>{
                            this.loading = false
                            if(res.code == 1){
                                this.$message.success(res.msg)
                                this.returnClose()
                                this.orderList()
                            }else{
                                this.$message.error(res.msg)
                            }
                        }).catch(e=>{
                            this.loading = false
                        })
                       
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消操作'
                        });          
                    });



                  
                } else {
                   
                }
            });
        },
        handleChange(value) {
        console.log(value);
      }

    },
}
</script>
<style>
    
</style>
<style lang="scss" scoped>
.order-view{

    .order-query-box{
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        .query-item{
            display: flex;
            align-items: center;        
            margin-right: 32px;
            font-size: 14px;
            margin-bottom: 18px;
            color:#606266;
            .query-input{
                width: 192px;
            }
            .query-select{
                width: 168px;
            }
        }
    }
    .search-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding: 10px;
        background-color: #f5f7fa;
        border-radius: 4px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        .total-amount {
            font-size: 14px;
            color: #606266;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            .total-item{
                display: flex;
                align-items: center;
                justify-content: flex-start;
            }
            .amount-value {
                font-size: 14px;
                font-weight: bold;
                // color: #f56c6c;
                margin-left: 5px;
            }
        }
        .action-btns {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    }
}











</style>
