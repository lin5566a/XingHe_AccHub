<template>
    <div class="log-box">
        <div class="log-title">用户邮箱：{{ accountForm.email }}</div>
        <div class="log-data-box">
            <div class="query-box mb15">
                <el-select class="query-select" v-model="query.type" clearable placeholder="变动类型" size="small" @change="getData">
                    <el-option :label="item.label" :value="item.value" v-for="item in logTypeArr" :key= "item.value"></el-option>
                </el-select>   
            </div>
            <div class="table-box">
                <el-table :data="tableData" size="small" border stripe  v-loading="query.loading">
                    <el-table-column label="变动类型" prop="type"></el-table-column>
                    <el-table-column label="变动金额" prop="amount"></el-table-column>
                    <el-table-column label="变动说明" prop="remark" min-width="200px"></el-table-column>
                    <el-table-column label="操作人" prop="operator"></el-table-column>
                    <el-table-column label="变动时间" prop="created_at" width="180px"></el-table-column>
                </el-table>
            </div>    
            <div class="pagination-box">
                    <el-pagination background :total="query.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="query.page"
                        :page-sizes="query.pageSizes"
                        :page-size="query.limit"
                        :disabled="query.loading"
                        layout="total, sizes, prev, pager, next">
                    </el-pagination>
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
        }, 
        data(){
            return{   
                logTypeArr:[],
                query:{                  
                    type:'',  
                    total:0,
                    page:1,
                    pageSizes:[10,20,50],
                    limit:10,
                    loading:false,
                },
                tableData:[]
            }
        },
        methods:{
            balanceLogTypes(){
                this.$api.balanceLogTypes().then(res=>{ 
                    if(res.code == 1){
                        this.logTypeArr = res.data
                    }
                })
            },
            getData(){
                this.query.page = 1;
                this.getLogs();
            },
            getLogs(){
                this.query.loading = true;
                let data = {...this.query,user_id:this.accountForm.id}
                this.$api.balanceLog(data).then(res=>{
                    this.query.loading = false;
                    if(res.code == 1){
                        this.query.total = res.data.total;
                        this.tableData = res.data.list;
                    }else{
                        this.$message.error(res.msg)  
                    }
                    
                }).catch(e=>{
                    this.query.loading = false;
                })
            },
              //分页
            handleCurrentChange(val) {
                this.query.page = val;
                this.getLogs();
            },
            handleSizeChange(val) {
                this.query.limit = val;
                this.query.page = 1; // 切换每页条数时，重置为第一页
                this.getLogs();
            },
        },
        mounted(){
            console.log('加载余额变动明细组件')
            this.getData()
            this.balanceLogTypes()
            
        },
    }
</script>
<style lang="scss" scoped>
    .log-box{
        .log-title{
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 500;
            color: #303133;
            border-bottom: 1px solid #EBEEF5;
            padding-bottom: 15px;
        }
        .log-data-box{

        }
        
    }
</style>