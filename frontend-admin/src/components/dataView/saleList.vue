<template>
    <div class="sale-list-card">
        <el-card shadow="hover" class="data-card">
            <div class="card-header">
                <div class="card-header-date">
                    <el-tabs v-model="dateValue" @tab-click="handleClick">
                        <el-tab-pane label="今日" name="today"></el-tab-pane>
                        <el-tab-pane label="昨日" name="yesterday"></el-tab-pane>
                        <el-tab-pane label="本月" name="month"></el-tab-pane>
                        <el-tab-pane label="上月" name="last_month"></el-tab-pane>
                    </el-tabs>
                </div>
                <div>
                    <el-button type="primary" size="mini" @click="exportData">导出数据</el-button>
                </div>
            </div>
            <div class="card-body">
                <el-table :data="table.dataTable" header-cell-class-name="card-table-header" style="width: 100%" v-loading="table.loading" border stripe  size="small">
                    <el-table-column prop="product_name" label="商品名称" min-width="150px"></el-table-column>
                    <el-table-column sortable prop="sales_quantity" label="销量" ></el-table-column>
                    <el-table-column sortable prop="sales" label="销售额">
                        <template slot-scope="scope">
                            <span>¥{{scope.row.sales.toFixed(2)}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable prop="pay_count" label="支付单数">
                        <template slot-scope="scope">
                            <span>{{scope.row.pay_count }} 单</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable prop="avg_amount" label="每单平均消费">
                        <template slot-scope="scope">
                            <span>¥{{scope.row.avg_amount.toFixed(2) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable prop="cost" label="商品成本">
                        <template slot-scope="scope">
                            <span>¥{{scope.row.cost.toFixed(2) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable prop="refund" label="退款金额">
                        <template slot-scope="scope">
                            <span>¥{{scope.row.refund.toFixed(2) }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column sortable prop="settle" label="结算金额">
                        <template slot-scope="scope">
                            <span>¥{{scope.row.settle.toFixed(2)}}</span>
                        </template>
                    </el-table-column> 
                    <el-table-column sortable prop="buyers" label="商品付款人数"></el-table-column>    
                    <el-table-column sortable prop="viewers" label="商品浏览人数"></el-table-column>     
                    <el-table-column sortable prop="conversion_rate" label="转化率">
                        <template slot-scope="scope">
                            <span>{{scope.row.conversion_rate.toFixed(2)}}%</span>
                        </template>
                    </el-table-column>                       
                </el-table>
            </div>
            <div class="card-footer">
                <div class="total-info">
                    <span class="info-label">总计:</span>                    
                    <span class="info-val">销量: {{totalsalesQuantity}}</span>
                    <span class="info-val">销售额: ¥{{totalSales}}</span>
                    <span class="info-val">支付单数: {{ totalPayCount }} 单</span>
                    <span class="info-val">每单平均消费: ¥{{ totalPayCount > 0 ? (totalSales/totalPayCount).toFixed(2) :'0.00'}}</span>
                    <span class="info-val">商品成本: ¥{{totalCost}}</span>
                    <span class="info-val">退款金额: ¥{{totalRefund}}</span>
                    <span class="info-val">结算金额: ¥{{ totalSettle }}</span>
                    <span class="info-val">商品付款人数: {{ totalPayPeopleCount }} 人 </span>
                    <span class="info-val">商品浏览人数: {{ totalViewPeopleCount }} 人</span>
                    <span class="info-val">平均转化率: {{ totalAverageRate }}%</span>
                </div>
            </div>
        </el-card>
    </div>
</template>
<script>
    export default {
        name: "SaleList",
        data() {
            return {
                dateValue: 'today',
                table:{
                    dataTable:[],
                    loading:false,
                }
            }
        },
        computed: {
            totalsalesQuantity(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.sales_quantity;
                })
                return total.toFixed(0);
            },
            totalSales(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.sales;
                })
                return total.toFixed(2);
            },
            totalPayCount(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.pay_count;
                })
                return total;

            },
            totalAvgAmount(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.avg_amount;
                })
                return total.toFixed(2);
            },
            totalCost(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.cost;
                })
                return total.toFixed(2);

            },
            totalRefund(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.refund;
                })
                return total.toFixed(2);

            },
            totalSettle(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.settle;
                })
                return total.toFixed(2);


            },
            //商品付款人数
            totalPayPeopleCount(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.buyers;
                })
                return total;


            },
            //商品浏览人数
            totalViewPeopleCount(){
                let total = 0;
                this.table.dataTable.forEach(item=>{
                    total += item.viewers;
                })
                return total;
            },
            //平均转化率
            totalAverageRate(){
                let total = 0;
                if(this.totalViewPeopleCount == 0){
                    total = 0;
                }else{
                    total = (this.totalPayPeopleCount / this.totalViewPeopleCount)*100
                }               
                return total.toFixed(2);
            }
        },
        methods: {
            handleClick(tab, event) {
                // console.log(tab,event,'ddddddd')
                this.getData()
            },
            getData() {
                this.table.loading = true;
                this.$api.productSalesDetail({date_type:this.dateValue}).then(res=>{
                    this.table.loading = false;
                    if(res.code == 1){
                        this.table.dataTable = res.data;
                    }
                }).catch(e=>{
                    this.table.loading = false;
                })
            },
            exportData(){
               this.$api.exportProductSalesDetail({date_type:this.dateValue}).then(res=>{
                    if(res.code == 1){
                        this.$message.success(res.msg)
                        let url = res.data.url;
                        window.open(this.$baseURL + url,'_blank');
                    }else{
                        this.$message.error(res.msg)
                    }
               })
                
            },
        },
        mounted() {
            this.getData()
        }
    }
</script>
<style lang="scss">
 .card-body{
    .card-table-header{        
        background-color: #f5f7fa !important;    
        color: #606266 !important;    
        font-size: 14px;
        padding: 4px 0;
    }
}
</style>
<style scoped lang="scss">
    .sale-list-card{ 
        margin-bottom: 10px;
        .card-header{
            display: flex;
            .card-header-date{
                flex: 1;
                ::v-deep .el-tabs__nav-wrap::after{
                    height:0px;
                }
            }
        }
        .card-footer{
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #EBEEF5;
            .total-info{
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                .info-label{
                    font-weight: 700;
                    color: #606266;
                }
                .info-val{
                    color: #303133;
                    font-weight: 500;
                }
            }
        }
        
    }
</style>