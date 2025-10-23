<template>
    <div class="operation-log-box">
       <mainBoxHeader titleName="操作日志" :description="operationData.description" >
           <template slot="oprBtn">
               <el-button class="f14" type="primary" @click="downLoad" size="small" :disabled="query.loading ||operationData.loading">导出日志</el-button>
           </template>
           <template slot="pageCon">
                <div class="query-box">
                  
                    <div class="query-item">
                        <span class="query-label mr12">用户名</span>
                        <el-input class="query-input" placeholder="请输入用户名" v-model="query.operator" clearable size="small"></el-input>
                    </div>
                    
                    <div class="query-item">
                        <span class="query-label mr12">操作时间</span>
                        <el-date-picker v-model="query.date" type="datetimerange" range-separator="至" start-placeholder="开始时间" end-placeholder="结束时间" :default-time="['00:00:00', '23:59:59']" size="small" :picker-options="pickerOptions"></el-date-picker>
                    </div> 
                    <div class="query-item">
                        <el-button @click="getData" type="primary" size="small" class="f14" :disabled="query.loading">查询</el-button>
                        <el-button @click="reset" size="small" class="f14" :disabled="query.loading">重置</el-button>
                    </div>
                </div>
               <div class="table-box">
                    <el-table 
                    :data="table.tableData" 
                    style="width: 100%"
                     v-loading="query.loading" 
                     border stripe size="small">
                        <el-table-column prop="log_id" label="日志ID" width="80"></el-table-column>
                        <el-table-column prop="operator" label="用户名" width="120"></el-table-column>
                        <el-table-column prop="operation_description" label="操作描述" min-width="250"></el-table-column>
                        <el-table-column prop="operation_time" label="操作时间" width="180"></el-table-column>
                    </el-table>
               </div>
               <div class="pagination-box">
                    <el-pagination background :total="query.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="query.page"
                        :page-sizes="query.pageSizes"
                        :page-size="query.pageSize"
                        :disabled="query.loading"
                        layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
           </template>
       </mainBoxHeader>


       
   </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
export default {
   name:'templateSetting',
   components:{
       mainBoxHeader,
   },
   data(){
       return{
            operationData:{
                loading:false,
               description:'记录系统中的所有操作行为，包括登录、新增、修改、删除等操作，方便追踪用户行为和系统安全审计。',
           },
           table:{
                tableData:[]
           },
           query:{
                date:[],
                operator:'',
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
                loading:false
           },           
            pickerOptions: {
                shortcuts: [
                {
                    text: '今天',
                    onClick(picker) {
                    const start = new Date();
                    start.setHours(0, 0, 0, 0);
                    const end = new Date();
                    // end.setHours(23, 59, 59, 999);
                    picker.$emit('pick', [start, end]);
                    },
                },
                {
                    text: '昨天',
                    onClick(picker) {
                    const start = new Date();
                    start.setDate(start.getDate() - 1);
                    start.setHours(0, 0, 0, 0);
                    const end = new Date();
                    end.setDate(end.getDate() - 1);
                    end.setHours(23, 59, 59, 999);
                    picker.$emit('pick', [start, end]);
                    },
                },
                {
                    text: '最近三天',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 3);
                    start.setHours(0, 0, 0, 0);
                    picker.$emit('pick', [start, end]);
                    }
                },{
                    text: '最近一周',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                    start.setHours(0, 0, 0, 0);
                    picker.$emit('pick', [start, end]);
                    }
                }, {
                    text: '最近一个月',
                    onClick(picker) {
                    const end = new Date();
                    const start = new Date();
                    start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                    start.setHours(0, 0, 0, 0);
                    picker.$emit('pick', [start, end]);
                    }
                }]
            },
          

       }
   },
   created(){
        this.getData()
   },
   methods:{
        //导出日志
        downLoad(){
            let data = {
                "operator": this.query.operator,
                "start_time": this.query.date?(this.query.date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[0])), false) :''):'',
                "end_time": this.query.date?(this.query.date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[1])), false) :''):'',            
            }
            this.operationData.loading = true
            this.$api.logsExport(data).then(res=>{
                this.operationData.loading = false
                if(res.code == 1){
                    window.open(this.$baseURL + res.data.url)
                    this.$message.success(res.msg)
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{
                this.operationData.loading = false
            })
        },
        //查询按钮点击事件
        getData(){
            this.query.page = 1
            this.logsList()
        },
        //操作日志列表查询接口
        logsList(){
            let data ={
                "page": this.query.page,
                "limit": this.query.pageSize,
                "operator": this.query.operator,
                "start_time": this.query.date?(this.query.date[0] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[0])), false) :''):'',
                "end_time": this.query.date?(this.query.date[1] ? this.$util.timestampToTime(Date.parse(new Date(this.query.date[1])), false) :''):'',
            }
            this.query.loading = true
            this.$api.logsList(data).then(res=>{
                this.query.loading = false
                if(res.code == 1){
                    this.table.tableData = res.data.list
                    this.query.total = res.data.total
                }
            }).catch(err=>{
                this.query.loading = false
                // this.$message.error('查询失败')
            })

        },
         //重置按钮点击事件
         reset(){
            this.query.page = 1;
            this.query.operator = '';
            this.query.date = [];
            this.logsList()
        },
        //分页
        handleCurrentChange(val){
            this.query.page = val
            this.logsList()
        },
        handleSizeChange(val){
            this.query.pageSize = val
            this.query.page = 1
            this.logsList()
        },
   }
}
</script>
<style lang="scss">

</style>
<style lang="scss" scoped>
    .operation-log-box{
        .query-box {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
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
    }
    
</style>