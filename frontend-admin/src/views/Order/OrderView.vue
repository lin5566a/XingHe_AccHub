<template>
    <div class="order-view">
        <mainBoxHeader titleName="商品订单列表" :description="orderData.description">
            <div slot="oprBtn">
                <span></span>
                <!-- <el-button class="f14" @click="download" type="primary" icon="el-icon-download" size="small" :disabled="exportLoading ">导出所有订单</el-button>              -->
            </div>
            <template slot="pageCon">
                <div class="order-box-header">
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
                            <span class="query-label mr12">用户邮箱</span>
                            <el-input class="query-input" placeholder="请输入用户邮箱" v-model="query.user_email" clearable size="small"></el-input>
                        </div>  
                         <div class="query-item">
                            <span class="query-label mr12">商品名称</span>
                            <el-input class="query-input" placeholder="请输入商品名称" v-model="query.product_name" clearable size="small"></el-input>
                        </div>  
                        <div class="query-item">
                            <span class="query-label mr12">商品分类</span>
                            <el-select class="query-select" v-model="query.category_id" clearable placeholder="请选择" size="small">
                                <el-option :label="item.name" :value="item.id" v-for="item in queryOption.categories" :key="item.id"></el-option>
                            </el-select>   
                        </div>
                        <div class="query-item">
                            <span class="query-label mr12">通道名称</span>
                            <el-select class="query-select" v-model="query.channel_id" clearable placeholder="请选择" size="small">
                                <el-option :label="item.name" :value="item.id" v-for="(item,index) in queryOption.channels" :key="index"></el-option>                            
                            </el-select>
                        </div>
                        <div class="query-item">
                            <span class="query-label mr12">支付方式</span>
                            <el-select class="query-select" v-model="query.payment_method" clearable placeholder="请选择" size="small">
                                <el-option :label="item.name" :value="item.id" v-for="item in queryOption.payment_methods" :key="item.id"></el-option>                            
                            </el-select>   
                        </div>    
                        <div class="query-item">
                            <span class="query-label mr12">发货方式</span>
                            <el-select class="query-select" v-model="query.delivery_method" clearable placeholder="请选择" size="small">
                                <el-option :label="item.name" :value="item.id" v-for="item in queryOption.delivery_methods" :key="item.id"></el-option>                             
                            </el-select>   
                        </div>  
                        <div class="query-item">
                            <span class="query-label mr12">订单状态</span>
                            <el-select class="query-select" v-model="query.status" clearable placeholder="请选择" size="small">                            
                                <el-option :label="item.name" :value="item.id" v-for="item in queryOption.status" :key="item.id"></el-option> 
                            </el-select>   
                        </div> 
                        <div class="query-item">
                            <span class="query-label mr12">用户身份</span>
                            <el-select class="query-select" v-model="query.user_role" clearable placeholder="请选择" size="small">                            
                                <el-option :label="item.name" :value="item.id" v-for="item in queryOption.user_roles" :key="item.id"></el-option> 
                            </el-select>   
                        </div> 
                        <div class="query-item">
                            <span class="query-label mr12">创建时间</span>
                            <el-date-picker v-model="orderData.date" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="small" :picker-options="pickerOptions"></el-date-picker>
                        </div> 
                        <div class="query-item">
                            <span class="query-label mr12">完成时间</span>
                        <el-date-picker v-model="orderData.finished_date" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="small" :picker-options="pickerOptions"></el-date-picker>
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
                                <span class="amount-value blue-color">{{ orderData.stat.total || 0}}</span>
                            </div>
                            <div class="total-item">
                                <span class="ml20 mr10">成功订单：</span>
                                <span class="amount-value green-color">{{orderData.stat.success || 0}}</span>
                            </div>
                            <div class="total-item">
                                <span class="ml20 mr10">订单总金额：</span>
                                <span class="amount-value red-color">¥{{orderData.stat.total_amount || 0}}</span>
                            </div>
                            <div class="total-item">
                                <span class="ml20 mr10">总手续费：</span>
                                <span class="amount-value yellow-color">¥{{orderData.stat.total_fee || 0}}</span>
                            </div>
                            <div class="total-item">
                                <span class="ml20 mr10">总入账金额：</span>
                                <span class="amount-value black-color">¥{{orderData.stat.total_arrive || 0}}</span>
                            </div>
                            <div class="total-item">
                                <span v-if="table.multipleSelection.length > 0" class="ml20 mr10">已选择：{{ table.multipleSelection.length }}笔订单</span>
                                <span v-if="table.multipleSelection.length > 0" class="amount-value red-color">¥{{ selectedOrdersTotal }}</span>
                            </div>
                        </div>
                        <div class="action-btns">
                            <el-button class="f14" type="success" icon="el-icon-download" size="small"  @click="exportSelected"> 
                            导出订单
                            </el-button>
                        </div>
                    </div>
                </div>
                <!-- 表格 -->
                <div class="table-page-box">
                    <div class="data-table">
                        <el-table :data="table.dataTable" style="width: 100%" v-loading="loading" border stripe @selection-change="handleSelectionChange" size="small" :row-class-name="selectedRowClass">
                            <el-table-column type="selection" width="55"></el-table-column>
                            <!-- <el-table-column prop="id" label="商品订单ID" width="100"></el-table-column> -->
                            <el-table-column prop="order_number" label="订单号" width="130"></el-table-column>
                            <el-table-column prop="status" label="用户信息" width="220">
                                <template scope="scope">
                                    <div>
                                        <!-- <div class=" mb4">
                                           <span  v-if='scope.row.user_role_id != 0' class="black-color fb7"> {{  scope.row.user_nickname }}</span>
                                            <el-tag v-else type="info" size="small">{{scope.row.user_role}}</el-tag>
                                        </div> -->
                                        <div class="text-info f12">{{  scope.row.user_email }}</div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="status" label="商品信息" width="180">
                                <template scope="scope">
                                    <div>
                                        <div class="mb4">{{  scope.row.product_name }}</div>
                                        <el-tag type="primary" v-if='scope.row.category' effect="plain" size="small"> {{ scope.row.category? scope.row.category.name  :'' }}</el-tag>
                                    </div>
                                </template>
                            </el-table-column>
                            
                          
                            <el-table-column prop="purchase_price" label="商品价格" width="100">
                                <template scope="scope">
                                    <div class="price f14 ">¥{{ Number(scope.row.purchase_price).toFixed(2) }}</div>
                                    <div class="price f09 green-color">{{ Number(scope.row.usdt_price).toFixed(2) }} USDT</div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="quantity" label="数量" width="80"></el-table-column>
                            <el-table-column prop="total_price" label="订单金额" width="100">
                                <template scope="scope">
                                    <div class="price f14 ">¥{{ scope.row.total_price }}</div>
                                    <div class="price f09 green-color ">{{ scope.row.usdt_total_price.toFixed(2) }} USDT</div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="fee" label="手续费" width="100">
                                <template scope="scope">
                                    <div class="fee f14 yellow-color ">¥{{ Number(scope.row.fee || 0).toFixed(2) }}</div>
                                    <div class="price f09 green-color ">{{ Number(scope.row.usdt_fee || 0).toFixed(2) }} USDT</div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="arrive_amount " label="入账金额" width="100">
                                <template scope="scope">
                                    <div class="price f14 ">¥{{scope.row.arrive_amount.toFixed(2) }}</div>
                                    <!-- <div class="price f09 green-color ">{{ Number(scope.row.usdt_total_price).toFixed(2) }} USDT</div> -->
                                </template>
                            </el-table-column>
                            <el-table-column prop="total_price" label="成本价" width="100">
                                <template scope="scope">
                                    <div class="price f14 ">¥{{ scope.row.cost_price ? scope.row.cost_price :'0.00'}}</div>
                                    <!-- <div class="price f09 green-color ">{{ Number(scope.row.usdt_total_price).toFixed(2) }} USDT</div> -->
                                </template>
                            </el-table-column>
                            <!-- <el-table-column prop="card_id" label="卡密ID" width="100" v-if="false"></el-table-column> -->
                            <el-table-column prop="card_info" label="卡密信息" min-width="120">
                                <template scope="scope">
                                    <div class="black-color fb7 mb4 f12" v-if= "scope.row.card_id ">ID：{{ scope.row.card_id }}</div>
                                    <el-button class="f14" type="text" @click="viewCardInfo(scope.row)">查看</el-button>
                                </template>
                            </el-table-column>
                            <el-table-column prop="" label="用户身份/折扣" width="140">
                                <template scope="scope">
                                    <el-tag v-if='scope.row.user_role_id == 0 ||scope.row.user_role_id == 1' type="info" size="small">{{scope.row.user_role}} ({{ scope.row.discount }}%)</el-tag>
                                    <el-tag v-if='scope.row.user_role_id == 2' type="primary" size="small">{{scope.row.user_role}} ({{ scope.row.discount }}%)</el-tag>
                                    <el-tag v-if='scope.row.user_role_id == 3' type="success" size="small">{{scope.row.user_role}} ({{ scope.row.discount }}%)</el-tag>
                                    <el-tag v-if='scope.row.user_role_id == 4' type="Warning" size="small">{{scope.row.user_role}} ({{ scope.row.discount }}%)</el-tag>
                                    <el-tag v-if='scope.row.user_role_id == 5' type="danger" size="small">{{scope.row.user_role}} ({{ scope.row.discount }}%)</el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column prop="payment_method" label="支付方式" width="120">
                                <template scope="scope">
                                    <el-tag v-if="scope.row.payment_method" :type="getPaymentMethodType(scope.row.payment_method)" size="small">
                                        {{ getPaymentMethodLabel(scope.row.payment_method) }}
                                    </el-tag>
                                    <div class="text-info f12 mt4">{{scope.row.channel_name}}</div>
                                </template>
                            </el-table-column>
                            <!-- <el-table-column prop="" label="折扣" width="100">
                                <template scope="scope">
                                    <el-tag type="success"  effect="plain" size="small"> {{ scope.row.discount }} %</el-tag>
                                </template>
                            </el-table-column> -->
                            
                            <!-- <el-table-column prop="channel_name" label="通道名称" width="180"></el-table-column> -->
                           
                            <el-table-column prop="delivery_method" label="发货方式" width="100">
                                <template scope="scope">
                                    <el-tag :type="scope.row.delivery_method === 'auto' ? 'success' : 'info'" size="small">
                                    {{ scope.row.delivery_method === 'auto' ? '自动发货' : scope.row.delivery_method ==='manual'?'手动发货' :''}}
                                    </el-tag>
                                </template>
                            </el-table-column>
                        
                            <el-table-column prop="created_at" label="创建时间" width="180"></el-table-column>
                            <el-table-column prop="finished_at" label="完成时间" width="180">
                                <template scope="scope">
                                    <div v-if="scope.row.finished_at">{{ scope.row.finished_at == '1970-01-01 08:00:00' ? '--': scope.row.finished_at}}</div>
                                    <div v-else>--</div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="remark" label="备注" min-width="120"></el-table-column>
                            <el-table-column prop="status" label="订单状态" width="100" fixed="right">
                                <template scope="scope">
                                    <el-tag :type="getStatusType(scope.row.status)" size="small">
                                        {{ scope.row.status_text }}
                                    </el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column label="操作" width="180" fixed="right">
                                <template scope="scope">
                                    <div class="action-buttons">
                                        <el-dropdown @command="handleCommand($event, scope.row)">
                                            <el-button size="mini" type="primary">
                                                操作<i class="el-icon-arrow-down el-icon--right"></i>
                                            </el-button>
                                            <el-dropdown-menu slot="dropdown">
                                                <el-dropdown-item command="orderDeliver" v-if="scope.row.status == '1'|| scope.row.status == '2' || scope.row.status == '4'">发货</el-dropdown-item>                                                
                                                <el-dropdown-item command="suerSend" v-if="scope.row.status == '5'">确认发货</el-dropdown-item>                                                
                                               <el-dropdown-item command="resendEmail" v-if="(scope.row.status == '3'  || scope.row.status == '5' )&& scope.row.delivery_method == 'auto'" >重发邮件</el-dropdown-item>
                                                <el-dropdown-item command="returnBack" v-if="scope.row.status == '3' || scope.row.status == '2'  || scope.row.status == '5'">退款</el-dropdown-item>
                                                <el-dropdown-item command="updateCardPwd" v-if="scope.row.status == '3'  || scope.row.status == '5'">更改卡密信息</el-dropdown-item>
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
                </div>

            </template>
        </mainBoxHeader>
        <div class="dialog-box">
            <el-dialog v-if="false"
                title="订单发货"
                :visible.sync="dialog.dialogVisible"
                width="550px"
                custom-class="dialog-class"
                @close="close"
                :close-on-click-modal="false"
                :top="dialog.top"
                >
                <span class="send-dialog-con">
                    <el-form ref="form" :model="form" :rules="rules" label-width = "100px" size="small">
                        <el-form-item label="订单号" prop="order_number">
                            <el-input v-model="form.order_number" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="商品名称" prop="product_name">
                            <el-input v-model="form.product_name" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="用户邮箱" prop="user_email">
                            <el-input v-model="form.user_email" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="卡密ID" prop="card_id" v-if="false">
                            <el-input v-model="form.card_id" placeholder="请输入卡密ID"></el-input>
                        </el-form-item>
                        <el-form-item label="卡密信息" prop="card_info">
                            <el-input
                                type="textarea"
                                :rows="3"
                                placeholder="请输入卡密信息"
                                v-model="form.card_info">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input
                                type="textarea"
                                :rows="3"
                                placeholder="请输入备注"
                                v-model="form.remark">
                            </el-input>
                        </el-form-item>
                    </el-form>
                </span>
                <span slot="footer" class="dialog-footer">
                    <el-button class="f14" @click="close" size="small">取 消</el-button>
                    <el-button class="f14" type="primary" @click="suer('form')" size="small" :disabled="loading">确认发货</el-button>
                </span>
            </el-dialog>
            <el-dialog
                title="订单退款" :visible.sync="returnDialog.dialogVisible"
                width="550px"
                custom-class="return-class"
                @close="returnClose"
                :close-on-click-modal="false"
                :top="returnDialog.top"
            >
                <div class="return-box">
                    <div class="return-info">
                        <div class="return-info-item">
                            <span class="info-label">订单号：</span>
                            <span class="info-value black-color">{{ dataItem.order_number}}</span>
                        </div>
                        <div class="return-info-item">
                            <span class="info-label">商品名称：</span>
                            <span class="info-value black-color">{{ dataItem.product_name}} - {{ dataItem.delivery_method === 'auto' ? '自动发货' : dataItem.delivery_method ==='manual'?'手动发货' :''}}</span>
                        </div>
                        <div class="return-info-item">
                            <span class="info-label">退款金额：</span>
                            <span class="info-value red-color fb">¥{{ dataItem.total_price}}</span>
                        </div>
                        <!-- <div class="return-info-item">
                            <span class="info-label">用户昵称：</span>
                            <span class="info-value black-color">{{ dataItem.user_nickname}}</span>
                        </div> -->
                    </div>
                    <div class="return-mark">
                        <el-form ref="returnForm" :model="returnForm" :rules="returnRules" label-width="100px" size="small">
                            <el-form-item label="退款备注" prop="remark">
                                <el-input
                                    type="textarea"
                                    :rows="3"
                                    placeholder="请输入备注"
                                    v-model="returnForm.remark">
                                </el-input>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button class="f14" @click="returnClose" size="small">取 消</el-button>
                    <el-button class="f14" type="primary" @click="returnSuer('returnForm')" size="small" :disabled="loading">确认退款</el-button>
                </span>
            </el-dialog>
            <!-- 编辑卡密 -->
            <editCardPwd v-if="editCardDialog.dialogVisible" title="编辑卡密信息" suerText="确认更改"  :dialogVisible="editCardDialog.dialogVisible" :loading="editCardDialog.loading" :orderObj="editCardDialog.item" @close="closeEditCardDialog" @suer="editCardSuer">  </editCardPwd>
            <!-- 手动发货 -->
            <editCardPwd v-if="deliverDialog.dialogVisible" title="订单发货" suerText="确认发货" :dialogVisible="deliverDialog.dialogVisible" :loading="deliverDialog.loading" :orderObj="deliverDialog.item" @close="closeDeliverDialog" @suer="deliverSuer"></editCardPwd>
      
        </div>
    </div>
</template>
<script>
import  mainBoxHeader from '@/components/mainBoxHeader.vue'
import  editCardPwd from '@/components/order/editCardPwd.vue'
export default {
    name:'Order',
    components:{
        mainBoxHeader,
        editCardPwd
    },
    data() {
        return{
            loading:false,
            exportLoading:false,
            orderData:{
                orderNumber:'',
                description:'管理所有邮箱账号和社交媒体账号的订单信息，包括订单状态、商品信息、买家信息等。您可以查看、编辑、删除订单，并进行发货操作。',
                date: [],
                finished_date:[],
                totalAmount:0,
                stat:{},
            },
            // 商品分类列表
            category:[],
            table:{
                total_amount:0,                
                multipleSelection:[],
                selectedTotalAmount:0,
                dataTable:[
                ],
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
            },
            dataItem:{

            },
            dialog:{
                dialogVisible:false,
                top:'15vh',
            },
            deliverDialog:{
                dialogVisible:false,
                loading:false,
                item:{},
            },
            editCardDialog:{
                dialogVisible:false,
                loading:false,
                item:{},
            },
            returnDialog:{
                dialogVisible:false,
                top:'15vh',
            },
            query:{
                order_number:'',
                // nickname:'',
                user_email:'',
                product_name:'',
                category_id:'',
                channel_id:'',
                payment_method:'',
                delivery_method:'',
                status:'',
                start_time:'',
                end_time:'',
                page:1,
                limit:10,
                user_role:'',
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
                delivery_methods:[],
                status_options:[],
                user_roles:[],
                vipMap:{},
            },
            form:{
                order_number:'',
                product_name:'',
                user_email:'',
                card_info:'',
                remark:'',
            },
            rules:{                
                order_number:[{ required: false, message: '请输入订单编号', trigger: 'blur' }],
                product_name:[{ required: false, message: '请输入商品名称', trigger: 'blur' }],
                user_email:[{ required: false, message: '请输入邮箱账号', trigger: 'blur' }],
                card_id:[{ required: true, message: '请输入卡密ID', trigger: 'blur' }],
                card_info:[{ required: true, message: '请输入卡密信息', trigger: 'blur' }],
                remark:[{ required: false, message: '请输入备注', trigger: 'blur' }],
            },
            returnForm:{
                remark:'账号无法正常登录，提示安全验证',
            },
            returnRules:{
                remark:[{ required: true, message: '请输入退款备注信息', trigger: 'blur' }],
            },

        }
    },
    computed: {
        // 计算选中订单的总金额
        selectedOrdersTotal() {
            let t = this.table.multipleSelection.reduce((sum, order) => {
                return sum + Number(order.total_price || 0);
            }, 0);
            let total = t.toFixed(2);
            // console.log(t,'==选中订单总金额==',total);
            return total ; // 保证返回数值
        }
    },
    created() {
        let order_no = this.$local.get('order_no');
        let order_status = this.$local.get('order_status');
        this.$local.remove("order_no")
        this.$local.remove("order_status")
        if(order_no){
            this.query.order_number = order_no;
        }
        if(order_status){
            this.query.status =Number(order_status)
        }  
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
                 "type": "all"
            }
            this.exportLoading = true;
            this.$api.exportAll(data).then(res=>{
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
            // this.query.nickname = ''
            this.query.user_role = ''
            this.query.order_number = '';
            this.query.user_email = '';
            this.query.category_id = '';
            this.query.payment_method = '';
            this.query.delivery_method = '';
            this.query.status = '';
            this.query.start_time = '';
            this.query.end_time = '';
            this.query.page=1;
            this.query.limit=10;
            this.query.channel_id = ''
            this.orderData.date = [];
            this.orderData.finished_date=[]
            this.table.total = 0;
            this.orderList()
        },
        //发货方式  支付方式   订单状态 筛选项查询
        queryOptions(){
            this.$api.queryOptions().then(res=>{ 
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
            this.query.finished_start_time = this.orderData.finished_date?(this.orderData.finished_date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.orderData.finished_date[0])), true) :''):''
            this.query.finished_end_time = this.orderData.finished_date?(this.orderData.finished_date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.orderData.finished_date[1])), true) :''):''           
            this.orderList()
        },
        //订单列表查询接口
        orderList(){
            this.loading=true;
            this.$api.orderList(this.query).then(res=>{
                this.loading=false;
                if(res.code == 1){
                    this.table.dataTable = res.data.list;
                    this.table.total_amount= res.data.total_amount;
                    this.table.total = res.data.total;
                    this.orderData.stat = res.data.stat;
                    
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
        //关闭
        close(){
            this.dialog.dialogVisible = false;
            this.resetForm('form')
        },
               //点击发货按钮事件
        sendProduct(item){
            const { order_number, product_name, user_email, card_info, remark } = item;
            this.form = {
                order_number,
                product_name,
                user_email,
                card_info,
                remark
            };
            this.dialog.dialogVisible = true;
            this.$nextTick(()=>{
                this.getDialogTop('dialog-class')
            })
        },
        //重置表单验证
        resetForm(formName) {
            this.$refs[formName].resetFields();
        },
        //弹窗确认发货按钮点击事件  暂时无用
        suer(formName){
            //验证表单
            this.$refs[formName].validate((valid) => {
                if (valid) {
                    this.loading = true;
                    this.$api.orderDeliver(this.form).then(res => {
                        this.loading = false;
                        if(res.code == 1){
                            this.$message({
                                message: '发货成功',
                                type: 'success',
                                duration: 3000
                            });
                    this.close();
                            this.orderList(); // 刷新列表数据
                        }else{
                            this.$message({
                                message: res.msg,
                                type: 'error',
                                duration: 3000
                            });
                        }
                    }).catch(e => {
                        this.loading = false;
                        this.$message({
                            message: '发货失败，请重试',
                            type: 'error',
                            duration: 3000
                        });
                    });
                } else {
                    console.log('error submit!!');
                    return false;
                }
            });
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
        getStatusLabel(value) {
            // 确保value是字符串类型
            const statusValue = String(value);
            const status = this.queryOption.status_options.find(item => String(item.value) === statusValue);
            return status ? status.label : value;
        },
        // 获取状态标签类型
        getStatusType(value) {
            // 确保value是字符串类型
            const statusValue = String(value);
            switch(statusValue) {
                case '3': // 已发货
                    return 'success';  // 绿色
                case '2': // 待发货
                    return 'primary';  // 蓝色
                case '1': // 待付款
                    return 'warning';  // 黄色
                default:
                    return 'info';
            }
        },
        // 查看卡密信息
        viewCardInfo(row) {
            if (!row.card_info) {
                this.$message({
                    message: '暂无卡密信息',
                    type: 'info',
                    duration: 3000
                });
                return;
            }
            let cardInfoHtml = '';
            let isJson = false;
            if (typeof row.card_info === 'string') {
                try {
                    const parsed = JSON.parse(row.card_info);
                    if (Array.isArray(parsed)) {
                        isJson = true;
                        cardInfoHtml = parsed.map((card, index) => `
                            <div class="card-info-item">
                                <span class="label">卡密${card.id !== undefined ? ' ' + card.id : ''}：</span>
                                <span class="value">${card.card_info !== undefined ? card.card_info : card}</span>
                            </div>
                        `).join('');
                    }
                } catch (e) {
                    // 不是json
                }
            }
            if (!isJson) {
                cardInfoHtml = `
                    <div class="card-info-item">
                        <span class="label">${row.card_info}</span>
                    </div>`;
            }
            this.$alert(
                `<div class="card-info-container">
                    ${cardInfoHtml}
                </div>`, `卡密信息${row.card_id ? '(ID:' + row.card_id + ')' : ''}`, {
                dangerouslyUseHTMLString: true,
                customClass: 'card-info-alert'
            });
        },
        // 导出选中订单
        exportSelected(){
            let data = {
                type: "selected",
                ids: this.table.multipleSelection.map(item => item.id),
                ...this.query
            }
            this.exportLoading = true;
            this.$api.exportAll(data).then(res=>{
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
        //列表退款按钮
        returnBack(row){
            this.dataItem = row
            this.returnDialog.dialogVisible = true
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
                    this.loading = true
                    let data = {
                        "id": this.dataItem.id,
                        "refund_remark": this.returnForm.remark
                    }
                    this.$api.orderRefund(data).then(res=>{
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
                } else {
                   
                }
            });
        },
        // 删除订单
        deleteOrder(row) {
            this.$confirm(`确认删除订单"${row.order_number}"吗？`, '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true;
                this.$api.orderDelete({ id: row.id }).then(res => {
                    this.loading = false;
                    if(res.code == 1){
                        this.$message({
                            message: '删除成功',
                            type: 'success',
                            duration: 3000
                        });
                        this.orderList(); // 刷新列表数据
                    }else{
                        this.$message({
                            message: res.msg,
                            type: 'error',
                            duration: 3000
                        });
                    }
                }).catch(e => {
                    this.loading = false;
                    this.$message({
                        message: '删除失败，请重试',
                        type: 'error',
                        duration: 3000
                    });
                });
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
        //重发邮件
        resendEmail(row){
            this.$confirm(`确定要重发订单 ${row.order_number} 的邮件通知吗？`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'info'
            }).then(() => {
                let data = {id:row.id}
                this.$api.orderResend(data).then(res=>{
                    if(res.code == 1){
                        this.$message({
                            message: res.msg,
                            type: 'success',
                            duration: 3000
                        })
                    }else{
                        this.$message({
                            message: res.msg,
                            type: 'error',
                            duration: 3000
                        });
                    }
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消操作'
                });          
            });



            
        },
        //修改卡密信息
        updateCardPwd(row){
            this.editCardDialog.item = {...row};
            this.editCardDialog.dialogVisible = true;
        },
        //关闭修改卡密信息弹窗
        closeEditCardDialog(){
            this.editCardDialog.item = {};
            this.editCardDialog.dialogVisible = false;
        },
        
        //修改卡密信息 弹窗确认按钮
        editCardSuer(form,item){
            let data = {
                order_id:item.id,
                new_card_info:form.card_info,
                remark:form.remark,
                cost_price_delta:form.cost_price
            }
            this.editCardDialog.loading = true
            this.$api.updateCardInfo(data).then(res=>{
                this.editCardDialog.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)                    
                    this.closeEditCardDialog()
                    this.orderList(); // 刷新列表数据
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(e=>{
                this.editCardDialog.loading = false
                console.log(e)
            })
        },
        // 列表手动发货按钮 第三期
        orderDeliver(item){
            this.deliverDialog.dialogVisible = true
            this.deliverDialog.item = {...item};
        },
        //关闭发货弹窗
        closeDeliverDialog(){
            this.deliverDialog.dialogVisible = false
            this.deliverDialog.item = {};
        },
        //弹窗内确认发货
        deliverSuer(form,item){
            let data = {
                order_number:item.order_number,
                product_name:item.product_name,
                card_info:form.card_info,
                cost_price:form.cost_price,
                remark:form.remark
            }
            this.deliverDialog.loading = true
            this.$api.orderDeliver(data).then(res=>{                
                this.deliverDialog.loading = false
                if(res.code == 1){
                    this.$message({
                        message: '发货成功',
                        type: 'success',
                        duration: 3000
                    });
                    this.closeDeliverDialog();
                    this.orderList(); // 刷新列表数据
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration: 3000
                    });
                }
            }).catch(e=>{
                this.deliverDialog.loading = false
            })
        },
        //发货失败后再次确认发货
        suerSend(item){
            this.$confirm(`确认该用户已付款并已成功发货？订单号:${item.order_number}`, '订单发货确认', {
                confirmButtonText: '确认发货',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.$api.deliveryManual({"id": item.id}).then(res=>{
                    if(res.code == 1){
                        this.$message.success(res.msg)
                        this.orderList()
                    }else{
                        this.$message.error(res.msg)
                    }
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消发货'
                });          
            });
        },
        handleCommand(command, row){
            switch(command) {
                case 'sendProduct':
                    this.sendProduct(row);
                    break;
                case 'resendEmail':
                    this.resendEmail(row);
                    break;
                case 'returnBack':
                    this.returnBack(row);
                    break;
                case 'deleteOrder':
                    this.deleteOrder(row);
                    break;
                case 'orderDeliver':
                    this.orderDeliver(row);
                    break;
                case 'updateCardPwd':
                    this.updateCardPwd(row);
                    break;
                case 'suerSend':
                    this.suerSend(row);
                    break;
            }
        },
        selectedRowClass({ row }) {
          // table.multipleSelection 是已选中的行数组
          return this.table.multipleSelection && this.table.multipleSelection.find(item => item.id === row.id)
            ? 'selected-row-bg'
            : '';
        },
    },
    watch: {
    '$route'(path) { 
        if(this.$route.query){
            let order_no = this.$local.get('order_no');
            let order_status = this.$local.get('order_status');
            this.$local.remove("order_no")
            this.$local.remove("order_status")
            this.query.order_number = '';
            this.query.status = '';
            if(order_no){
                this.query.order_number = order_no;
            }
            if(order_status){
                this.query.status =Number(order_status)
            }       
            this.getData()
        }
     }
  },
}
</script>
<style>
    
</style>
<style lang="scss" scoped>
.order-view{
    .order-box-header{
        border: solid 1px #e4e7ed;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 4px;

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
            margin-top: 7px;
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
    .table-page-box{
        border: solid 1px #e4e7ed;
        padding: 20px;
        border-radius: 4px;
    }
}

.return-class{
    .return-info{
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 4px;
        .return-info-item{
            margin-bottom: 8px;
            font-size: 14px;
            .info-label{
                color: #606266;
                margin-right: 5px;
                // width:100px;
                display: inline-block;
            }
            .info-value{

            }
        }
    }
}

// 表格复选框选中行背景色
:deep(.el-table__row.is-selected) {
    background-color: #e6f1fc !important;
}

:deep(.el-table__row.is-selected > td) {
    background-color: #e6f1fc !important;
}

:deep(.el-table__row.is-selected:hover > td) {
    background-color: #e6f1fc !important;
}

// 更具体的选择器，确保覆盖默认样式
:deep(.el-table .el-table__row.is-selected) {
    background-color: #e6f1fc !important;
}

:deep(.el-table .el-table__row.is-selected > td) {
    background-color: #e6f1fc !important;
}

:deep(.el-table .el-table__row.is-selected:hover > td) {
    background-color: #e6f1fc !important;
}

// 针对 stripe 表格的选中行
:deep(.el-table--striped .el-table__row.is-selected) {
    background-color: #e6f1fc !important;
}

:deep(.el-table--striped .el-table__row.is-selected > td) {
    background-color: #e6f1fc !important;
}

:deep(.selected-row-bg) > td {
  background-color: #e6f1fc !important;
}

</style>
