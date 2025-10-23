<template>
    <div class="data-overview-block">
       <el-card shadow="hover" class="data-card">
           <div class="data-card-con">
               <div class="data-block-header">
                   <div class="left-header">
                       <span>全站流量数据统计</span>
                   </div>
                   <div class="right-header">
                       <el-radio-group v-model="date" size="mini" @change="changeDate" :disabled ="loading">
                           <el-radio-button label="realtime" size="mini">实时</el-radio-button>
                           <el-radio-button label="yesterday" size="mini">昨日</el-radio-button>
                           <el-radio-button label="7days" size="mini">近7日</el-radio-button>
                           <el-radio-button label="30days" size="mini">近30日</el-radio-button>
                       </el-radio-group>
                   </div>
               </div>                
               <div class="card-content">
                   <div class="data-overview-list">
                        <div class="flex mb20">
                            <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('payment_amount')">
                                   <DataOverviewItem                                         
                                       :active="activeItem == 'payment_amount' ? true:false " 
                                       title='支付金额' 
                                       :value="`¥${dataObj.stats ? dataObj.stats.payment_amount : '0'}`" 
                                       :compareDate="`较${getDateText(date)}`" 
                                       :compareValue="`${dataObj.comparison ? dataObj.comparison.payment_amount.direction == 'down'?'↓':'↑' :'↑'}${dataObj.comparison ? dataObj.comparison.payment_amount.value : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.payment_amount.direction : ''">
                                   </DataOverviewItem>
                               </div>
                            </div>
                            <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('net_payment_amount')">
                                   <DataOverviewItem                                         
                                       :active="activeItem == 'net_payment_amount' ? true:false " 
                                       title='净支付金额' 
                                       :value="`¥${dataObj.stats ? dataObj.stats.net_payment_amount : '0'}`" 
                                        :compareDate="`较${getDateText(date)}`" 
                                        :compareValue="`${dataObj.comparison ? dataObj.comparison.net_payment_amount.direction == 'down'?'↓':'↑' :'↑'}${dataObj.comparison ? dataObj.comparison.net_payment_amount.value : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.net_payment_amount.direction : ''">
                                   </DataOverviewItem>
                               </div>
                            </div>
                            <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('visitors')">
                                   <DataOverviewItem                                         
                                       :active="activeItem == 'visitors' ? true:false "
                                       title='访客数' 
                                      :value="`${dataObj.stats ? dataObj.stats.visitors: '0'}`" 
                                        :compareDate="`较${getDateText(date)}`" 
                                        :compareValue="`${dataObj.comparison ? dataObj.comparison.visitors.direction == 'down'?'↓':'↑' :'↑'}${dataObj.comparison ? dataObj.comparison.visitors.value : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.visitors.direction : ''">
                                   </DataOverviewItem>
                               </div>
                           </div>
                           <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('paying_buyers')">
                                   <DataOverviewItem                                         
                                       :active="activeItem == 'paying_buyers' ? true:false " 
                                       title='支付买家数' 
                                       :value="`${dataObj.stats ? dataObj.stats.paying_buyers : '0'}`" 
                                        :compareDate="`较${getDateText(date)}`" 
                                        :compareValue="`${dataObj.comparison ? dataObj.comparison.paying_buyers.direction == 'down'?'↓':'↑': '↑'}${dataObj.comparison ? dataObj.comparison.paying_buyers.value  : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.paying_buyers.direction : ''">
                                   </DataOverviewItem>
                               </div>
                           </div>
                           <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('conversion_rate')">
                                   <DataOverviewItem 
                                       :active="activeItem == 'conversion_rate' ? true:false " 
                                       title='支付转化率' 
                                       :value="`${dataObj.stats ? dataObj.stats.conversion_rate : '0'}%`" 
                                        :compareDate="`较${getDateText(date)}`" 
                                        :compareValue="`${dataObj.comparison ? dataObj.comparison.conversion_rate.direction == 'down'?'↓':'↑' : '↑'}${dataObj.comparison ? dataObj.comparison.conversion_rate.value : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.conversion_rate.direction : ''">
                                   </DataOverviewItem>
                               </div>
                           </div>
                           <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('refund_amount')">
                                   <DataOverviewItem                                         
                                       :active="activeItem == 'refund_amount' ? true:false " 
                                       title='退款金额（完结时间)' 
                                       :value="`¥${dataObj.stats ? dataObj.stats.refund_amount : '0'}`" 
                                        :compareDate="`较${getDateText(date)}`" 
                                        :compareValue="`${dataObj.comparison ? dataObj.comparison.refund_amount.direction == 'down'?'↓':'↑' : '↑'}${dataObj.comparison ? dataObj.comparison.refund_amount.value : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.refund_amount.direction : ''">
                                   </DataOverviewItem>
                               </div>
                           </div>
                           <div>
                               <div class="data-overview-item-box" @click="chooseDataOverview('refund_rate')">
                                   <DataOverviewItem                                        
                                       :active="activeItem == 'refund_rate' ? true:false "
                                       title='金额退款率' 
                                        :value="`${dataObj.stats ? dataObj.stats.refund_rate : ''}%`" 
                                        :compareDate="`较${getDateText(date)}`" 
                                        :compareValue="`${dataObj.comparison ? dataObj.comparison.refund_rate.direction == 'down'?'↓':'↑' : '↑'}${dataObj.comparison ? dataObj.comparison.refund_rate.value : '0'}%`" 
                                       :compareClass="dataObj.comparison ? dataObj.comparison.refund_rate.direction : ''">
                                   </DataOverviewItem>
                               </div>
                            </div>                     
                       </div>
                   </div>
                   <div class="data-overview-chart">
                       <div class="left-header">
                           <span>今日金额退款率趋势</span>
                       </div>
                       <div id="main" class="overview-line-area"></div>
                   </div>
               </div>
           </div>
       </el-card>
       
   </div>
</template>
<script>
    import * as echarts from 'echarts';
    import DataOverviewItem from '@/components/dataView/dataOverviewItem.vue';
    export default {
        name: "dataOverview",
        components:{
            DataOverviewItem
        },
        data(){
            return{
                dataObj:{},
                activeItem:'payment_amount',
                date:'realtime',
                loading:false,
                dataStatistics:{
                    loading:false,
                    dataList:[],
                    chartData:{
                        xAxis: [],
                        series:[],                        
                        legendName:'',
                        yAxisName:'',
                        legend:'',
                    },
                },
                chartInstance: null
            }
        },
        mounted(){
            this.trafficStats()
            this.getDataStatistics()
            // this.chartData(this.dataStatistics.chartData);
            window.addEventListener('resize', this.handleResize);
        },
        beforeDestroy(){
            window.removeEventListener('resize', this.handleResize)
            if(this.chartInstance){
                this.chartInstance.dispose()
                this.chartInstance = null
            }
        },
        methods:{
            getDateText(date){
                switch(date){
                    case 'realtime':
                        return '昨日'
                    case 'yesterday':
                        return '前日'
                    case '7days':
                        return '上周'
                    case '30days':
                        return '上月'
                }
            },
            changeDate(val){
                this.date = val
                this.trafficStats()
                this.getDataStatistics();
            },
            chooseDataOverview(val){
                this.activeItem = val
                this.getDataStatistics();
            },
            handleResize(){
                if(this.chartInstance){
                    this.chartInstance.resize()
                }
            },
            trafficStats(){
                let data ={
                    date_type:this.date
                }
                this.loading = true;
                this.$api.trafficStats(data).then(res=>{
                    this.loading = false;
                    console.log(res);
                    if(res.code == 1) {
                        this.dataObj = res.data;
                    }
                }).catch(e=>{
                    this.loading = false;
                })
            },
            //趋势数据查询
            getDataStatistics(){ 
                let data = {
                    date_type:this.date,
                    data_type:this.activeItem
                }
                this.dataStatistics.loading = true;
                this.$api.traffic_trend(data).then(res=>{
                    this.dataStatistics.loading = false;
                    if(res.code == 1){
                        this.dataStatistics.chartData.xAxis=[]
                        this.dataStatistics.chartData.series=[]
                        
                        this.dataStatistics.chartData.legend = []
                        let total_trend_data = res.data.total_trend_data;
                        let channel_trend_data = res.data.channel_trend_data
                        for(let i = 0; i<channel_trend_data.length; i++){
                            let item = channel_trend_data[i]
                            let series = {
                                type: 'line',       // 折线
                                data:[],
                                smooth: true,    // 是否平滑曲线
                                symbol: 'circle',       // 使用圆形作为数据点
                                symbolSize: 8,           // 数据点的大小 
                                itemStyle: {                // 数据点的样式 颜色、大小等
                                    opacity: 1,
                                    borderColor: '#ffffff', // 白色边框
                                    borderWidth: 2, 
                                },
                                emphasis: {                 // 鼠标悬停样式
                                    focus: 'series', 
                                    lineStyle: {
                                        width: 4
                                    },
                                    itemStyle: {
                                        opacity: 1
                                    },
                                    symbolSize: 10 // 悬停时放大
                                },
                            }
                            this.dataStatistics.chartData.legend.push(item.channel_name)
                            series.name = item.channel_name;
                            for(let j = 0; j < item.trend_data.length; j++){
                                let trendItem = item.trend_data[j]
                                series.data.push(trendItem.value)
                            }
                            this.dataStatistics.chartData.series.push(series)    
                        }
                            
                        let totalSeries = {
                            name: '总计',
                            type: 'line',
                            data: [],
                            smooth: true,                            
                            symbol: 'none',  
                            lineStyle: {
                                color:  "#909399",
                                width: 2,
                                type:"dashed",
                            },
                             itemStyle: {
                                color:  "#909399",
                            },
                            emphasis: {
                                focus: 'series',
                                lineStyle: {
                                    width: 3
                                },
                                itemStyle: {
                                    opacity: 1
                                }
                            },
                        }
                        this.dataStatistics.chartData.legend.push('总计')
                        for(let i=0; i<total_trend_data.length;i++){
                            this.dataStatistics.chartData.xAxis.push(total_trend_data[i].time)
                            totalSeries.data.push(total_trend_data[i].value)
                        }
                        this.dataStatistics.chartData.series.push(totalSeries)
                        
                        let legendName = ''
                        let yAxisName = ''
                        let color = ''
                        let lineColor = ''
                        switch(this.activeItem){
                            case 'payment_amount':
                                legendName = '支付金额'
                                yAxisName='支付金额（¥）'
                                color="#ffbb55"
                                lineColor="#ff9e40"
                                break;
                            case 'net_payment_amount':
                                legendName = '净支付金额' 
                                yAxisName='净支付金额（¥）'
                                color="#79bbff"
                                lineColor="#409eff"
                                break;
                            case 'visitors':
                                legendName = '访客数'
                                yAxisName='访客数'
                                color="#07c160"
                                lineColor="#06ab55"
                                break;
                            case 'paying_buyers':
                                legendName = '支付买家数'
                                yAxisName='支付买家数'
                                color="#8d43f3"
                                lineColor="#722ed1"
                                break; 
                            case 'conversion_rate':
                                legendName = '支付转化率'
                                yAxisName='支付转化率（%）'
                                color="#409EFF"
                                lineColor="#3181d3"
                                break;
                            case 'refund_amount':
                                legendName = '退款金额（完结时间)'
                                yAxisName='退款金额（¥）'
                                color="#3ac2af"
                                lineColor="#2ea393"
                                break;
                            case 'refund_rate':
                                legendName = '金额退款率'
                                yAxisName='金额退款率（%）'
                                color="#F56C6C"
                                lineColor="#dd6161"
                                break;
                            
                        }
                        this.dataStatistics.chartData.legendName = legendName
                        this.dataStatistics.chartData.yAxisName = yAxisName
                        this.dataStatistics.chartData.color = color
                        this.dataStatistics.chartData.lineColor = lineColor
                        this.$nextTick(()=>{
                            this.chartData(this.dataStatistics.chartData);
                        })
                        
                    }else{
                        this.$message.error(res.msg)
                    }
                }).catch(e=>{
                    this.dataStatistics.loading = false;
                })
            },
            chartData(chart){
                var chartDom = document.getElementById('main');
                if(!chartDom){
                    return
                }
                if (this.chartInstance) {
                    this.chartInstance.dispose(); // 销毁旧实例
                }
                this.chartInstance = echarts.init(chartDom);
                var option;

                option = {
                    legend: {
                        show: true,
                        data: chart.legend,
                        top: 0,           // 图表顶部位置，可调整
                        left: 'center',         // 右侧对齐
                        itemGap: 12,       // 图例项间距
                        itemWidth: 14,
                        itemHeight: 8,
                        orient: 'horizontal',
                        textStyle: {
                            color: '#666',
                            fontSize: 12
                        },
                        // 鼠标悬停或点击的样式（可选）
                        selectedMode: true,    // 允许点击切换系列显示/隐藏
                        tooltip: {
                            show: true
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                    },
                    grid: {
                        top: 50,
                        left: '10%',
                        right: '10%',
                        bottom: 10,
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,                        
                        data: chart.xAxis
                    },
                    yAxis: {
                        type: 'value',
                        name:chart.yAxisName,
                        nameTextStyle: {
                            color: '#e4e7ed',
                            fontSize: 12
                        },

                    },
                    series:chart.series
                };
                this.chartInstance.setOption(option, true);

            }
        }
    }
</script>
<style lang="scss" scope>
.data-overview-block{
    .data-card{ 
        &:hover {
            box-shadow: 0 4px 12px #0000001a;
        }  
        .data-card-con{
            .data-block-header{
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding: 0px 10px;
                border-bottom: solid 1px #EBEEF5;
                padding-bottom: 15px;            
            }
            .card-content{
                .data-overview-list{
                    .data-overview-item-box{
                        padding: 0 7.5px;
                    }
                }
            }
            .left-header{
                font-size: 16px;
                font-weight: 700;
                color: rgb(48, 49, 51);
                position: relative;
                padding-left: 10px;
                &::before {
                    content: "";
                    position: absolute;
                    left: 0px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 4px;
                    height: 16px;
                    background-color: rgb(64, 158, 255);
                    border-radius: 2px;
                }
            }
        }     
    }
    
    .data-overview-chart{
        padding: 10px;
        .overview-line-area{
            margin-top:25px;
            width: 100%;
            height: 400px;
        }
    }
   
}
</style>