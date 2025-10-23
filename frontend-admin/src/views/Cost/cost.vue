<template>
    <div class="cost-box">
        <div class="query-box">
            <el-card class="data-card" shadow="never">
                <div class="data-card-content">
                    <div class="query-item">                    
                        <span class="query-label mr12">成本类型</span>
                        <el-select class="select-input" v-model="query.type" size="small" clearable placeholder="请选择成本类型">
                            <el-option v-for="item in options.type" :key="item.value" :label="item.label" :value="item.value"></el-option>
                        </el-select>                    
                    </div>
                    <div class="query-item">                    
                        <span class="query-label mr12">金额类型</span>
                        <el-select class="select-input" v-model="query.amount_type" size="small" clearable placeholder="请选择金额类型">
                            <el-option v-for="item in options.amount_type" :key="item.value" :label="item.label" :value="item.value"></el-option>
                        </el-select>                    
                    </div>
                    <div class="query-item">                    
                        <span class="query-label mr12">关联ID</span>
                        <el-input class="query-input" placeholder="请输入关联ID" v-model="query.relate_id" clearable size="small"></el-input>                   
                    </div>
                    <div class="query-item">        
                        <span class="query-label mr12">商品分类</span>
                        <el-select class="select-input" v-model="query.category_id" size="small" clearable placeholder="请选择商品分类">
                            <el-option v-for="item in options.category" :key="item.value" :label="item.label" :value="item.value"></el-option>
                        </el-select>
                    </div>
                    <div class="query-item">                    
                        <span class="query-label mr12">商品名称</span>
                        <el-input class="query-input" placeholder="请输入商品名称" v-model="query.product_name" clearable size="small"></el-input>                   
                    </div>

                    <div class="query-item">                    
                        <span class="query-label mr12">日期范围</span>
                        <el-date-picker class="query-date" v-model="costData.date" type="daterange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" size="small"></el-date-picker>                
                    </div>
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="table.loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="table.loading">重置</el-button>
                    </div>
                </div>
            </el-card>
        </div>
        <div class="total-box">
            <TotalData label="总成本支出" :value="stat.total_cost"></TotalData>
            <TotalData label="批次成本总额" :value="stat.batch_cost"></TotalData>
            <TotalData label="人工发货成本" :value="stat.manual_delivery_cost"></TotalData>
            <TotalData label="人工录入成本" :value="stat.manual_input_cost"></TotalData>
        </div>
        <div class="table-box">
            <el-card class="table-data-card" shadow="never">
                <div class="table-data-card-content">
                    <div class="operate-box">
                        <div>
                            <el-button type="primary" size="small" @click="showAddDialog">新增成本</el-button>
                            <el-button size="small" @click="exportData">导出数据</el-button>
                        </div>                        
                        <div class="refresh"><el-button size="small" @click="getCostData" icon="el-icon-refresh" circle></el-button></div>
                    </div>
                    <div class="data-table">
                        <el-table :data="table.tableData" header-cell-class-name="card-table-header" @sort-change="tableSort" style="width: 100%" v-loading="table.loading" border stripe size="small">
                            <el-table-column prop="type" label="成本类型" width="150px">
                                <template slot-scope="scope">
                                    <el-tag size="small" :type="
                                        scope.row.type == 4 ? 'success' :
                                        scope.row.type == 3 ? 'info' :
                                        scope.row.type == 5 ||scope.row.type == 2 ? 'warning' :
                                        scope.row.type == 1 ? 'primary' : 
                                        scope.row.type == 6 ? 'success' : ''
                                    "
                                    >
                                        {{ options.type.find(t => t.value == scope.row.type)?.label || scope.row.type }}
                                    </el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column prop="relate_id" label="关联ID" >
                                <template slot-scope="scope">
                                    <span>{{ scope.row.relate_id?scope.row.relate_id:'-' }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="" label="商品分类" width="120px">
                                <template slot-scope="scope">
                                    <el-tag size="small" type="info" v-if="scope.row.category_name">
                                        {{scope.row.category_name}}
                                    </el-tag>
                                    <span v-else >-</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="" label="商品名称" width="150px">
                                <template slot-scope="scope">
                                    <span>{{ scope.row.product_name?scope.row.product_name:'-' }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="" label="数量">
                                <template slot-scope="scope">
                                    <span class="quantity" v-if="scope.row.quantity">{{ scope.row.quantity }}</span>
                                    <span v-else>-</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="amount" sortable = 'custom' label="成本金额">
                                <template slot-scope="scope">
                                    <span :style="{color: scope.row.amount_type == 2 ? '#f56c6c' : '#67c23a'}">
                                        {{ scope.row.amount_type == 2 ? '-' : '+' }} ¥{{ Math.abs(Number(scope.row.amount)).toFixed(2) }}
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="amount_type" label="金额类型">
                                <template slot-scope="scope">
                                    <el-tag size="small" :type="scope.row.amount_type == 1 ? 'success' : scope.row.amount_type == 2 ? 'danger' : ''">
                                        {{ options.amount_type.find(a => a.value == scope.row.amount_type)?.label || scope.row.amount_type }}
                                    </el-tag>
                                </template>
                            </el-table-column>
                            <el-table-column prop="operator" label="操作人" />
                            <el-table-column prop="detail" label="详情" width="220px">
                                <template slot-scope="scope">
                                    <span style="white-space: pre-line;">{{ scope.row.detail ? scope.row.detail :'-' }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="remark" label="备注" width="250px" :show-overflow-tooltip="true"></el-table-column>
                            <el-table-column prop="create_time" label="创建时间" width="180px" />
                        </el-table>
                    </div>
                    <div class="pagination-box">
                        <el-pagination background :total="table.total"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page="query.page"
                            :page-sizes="table.pageSizes"
                            :page-size="query.limit"
                            :disabled="table.loading"
                            layout="total, sizes, prev, pager, next, jumper">
                        </el-pagination>
                    </div>
                </div>
            </el-card>
        </div>
        <div class="dialog-box">
            <AddDialog v-if="dialog.visible" :visible.sync="dialog.visible" :loading="dialog.loading" @close="closeAddDialog" @suer="suer"></AddDialog>
        </div>
    </div>
</template>
<script> 
import TotalData from "../../components/cost/totalData.vue";
import AddDialog from "../../components/cost/addDialog.vue";
import api from '@/api/api';
    export default {
        name: "Cost",
        components: {
            TotalData,
            AddDialog,
        },

        data() {
            return {
                costData:{
                    date:[],
                    totalData:{},
                },
                query:{
                    type:'',
                    amount_type:'',
                    relate_id:'',
                    page:1,
                    limit:10,
                    amountSort:'',
                    category_id:'',
                    product_name:'',
                },
                options:{
                    type:[],
                    amount_type:[],
                    category:[]
                },
                table:{
                    tableData:[],
                    loading:false,
                    total:0,
                    pageSizes:[10,20,50,100],
                },
                stat: {
                    total_cost: 0,
                    batch_cost: 0,
                    manual_delivery_cost: 0,
                    manual_input_cost: 0
                },
                dialog:{
                    visible:false,
                    loading:false,
                }
            }
        },
        methods: {
            //getTypeOptions 获取成本类型和金额类型
            getTypeOptions(){
                api.getTypeOptions().then(res => {
                    if(res.code == 1 ) {
                        this.options = res.data;
                    }
                })
            },
            //查询按钮点击
            getData(){
                this.query.page = 1;
                this.getCostData();
            },
            //接口请求
            getCostData(){
                this.table.loading = true;                
                this.query.start_time = this.costData.date?(this.costData.date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.costData.date[0])), true) :''):''
                this.query.end_time  =  this.costData.date?(this.costData.date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.costData.date[1])), true) :''):''      

                api.costCenterList(this.query).then(res => {
                    this.table.loading = false;
                    if(res.code === 1 && res.data) {
                        // const listData = res.data.list;
                        this.table.total = res.data.total || 0;
                        this.table.tableData = res.data.list;
                        this.stat = res.data.stat || {};
                    } else {
                        this.table.tableData = [];
                        this.table.total = 0;
                        this.stat = {
                            total_cost: 0,
                            batch_cost: 0,
                            manual_delivery_cost: 0,
                            manual_input_cost: 0
                        };
                    }
                }).catch(() => {
                    this.table.loading = false;
                    this.table.tableData = [];
                    this.table.total = 0; 
                    this.stat = {
                        total_cost: 0,
                        batch_cost: 0,
                        manual_delivery_cost: 0,
                        manual_input_cost: 0
                    };
                });
            },
            //分页
            handleCurrentChange(val) {
                this.query.page = val;
                this.getCostData();
            },
            handleSizeChange(val) {
                this.query.limit = val;
                this.query.page = 1; // 切换每页条数时，重置为第一页
                this.getCostData();
            },
             //表格排序
             tableSort(column){
                this.query.amountSort = (column.order?(column.order == "ascending"?"asc" : "desc"):'')
                this.getData()
            },
            //导出数据
            exportData(){
                this.$api.exportCost(this.query).then(res=>{
                    if(res.code == 1){
                        // this.$message.success(res.msg);
                        // this.exportData();
                        let url = res.data.url;
                        window.open(this.$baseURL + url,'_blank');
                    }else{
                        this.$message.error(res.msg);
                    }
                })
            },
            //重置按钮点击
            reset(){
                this.query = {
                    type: '',
                    amount_type: '',
                    relate_id: '',
                    page: 1,
                    limit: 10
                };
                this.costData.date = [];
                this.getCostData();
            },
            //显示新增对话框
            showAddDialog(){
                this.dialog.visible = true;
            },
            //关闭新增对话框
            closeAddDialog(){
                this.dialog.visible = false;
            },
            //新增对话框确认按钮点击
            suer(form){
                this.dialog.loading = true;
                api.addManualCost(form).then(res => {
                    this.dialog.loading = false;
                    if(res.code === 1) {
                        this.$message.success(res.msg);
                        this.closeAddDialog();
                        this.getCostData();
                    } else {
                        this.$message.error(res.msg );
                    }
                }).catch(() => {
                    this.dialog.loading = false;
                    // this.$message.error('录入失败');
                });
            }

        },
        mounted(){
            this.getTypeOptions()
            this.getData()
        },
    }
</script>
<style lang="scss" scoped>
.cost-box{
    padding: 20px;
    .query-box{
        margin-bottom: 16px;
        .data-card{
            .data-card-content{
                display: flex;
                justify-content: flex-start;
                align-items: center;
                flex-wrap: wrap;
                .query-item{
                    margin-right: 32px;
                    white-space: nowrap;
                    margin-bottom: 18px;
                    .select-input{
                        width: 168px;

                    }
                    .query-input{
                        width: 192px;
                        // height: 32px;
                    }
                    .query-date{
                        width: 240px;
                    }
                }
            }
            
        }  
    }
    .total-box{
        display: grid;
        grid-template-columns: repeat(4,1fr);
        grid-gap: 16px;
        margin-bottom: 16px;
    }
    .table-box{
        .table-data-card-content{
            .operate-box{
                margin-bottom: 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .data-table{
                .quantity {
                    color: #409eff;
                    font-weight: 600;
                }
            }
        }
    }
}

  
</style>