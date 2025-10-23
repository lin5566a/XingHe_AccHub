<template>
    <div>
        <div class="section-item">
            <div class="section-title">商品销售统计</div>
            <div class="data-view-box">
                <div class="data-view-item">
                    <DataBlock title='商品销售额' 
                        :radioValue="radioValue.radioValueSales" 
                        :hasMonth="true"
                        @ChangeradioValue="salesRadioChange($event,'radioValueSales')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>¥{{dataView.product_sales?dataView.product_sales[radioValue.radioValueSales]:'0.00'}} </span>
                            </div> 
                            <div class="data-trend">
                                {{radioValue.radioValueSales == 'total' ? '商品销售总额' : 
                                radioValue.radioValueSales == 'today' ? '今日销售额' : 
                                radioValue.radioValueSales == 'yesterday' ? '昨日销售额' : 
                                radioValue.radioValueSales == 'month' ? '本月销售额' :
                                radioValue.radioValueSales == 'last_month'?'上月销售额':''}}
                            </div>                      
                        </div>
                    </DataBlock>
                </div>
                <div class="data-view-item">
                    <DataBlock title='支付单数' 
                        :radioValue="radioValue.count" 
                        @ChangeradioValue="salesRadioChange($event,'count')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>{{dataView.pay_count?dataView.pay_count[radioValue.count]:'0'}}单 </span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.count == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.pay_count">
                                        <span class="up" v-if="dataView.pay_count['yesterday'] == 0">
                                            ↑ {{dataView.pay_count['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.pay_count['today'] > dataView.pay_count['yesterday'] || dataView.pay_count['today'] == dataView.pay_count['yesterday']">
                                            ↑ {{((dataView.pay_count['today'] - dataView.pay_count['yesterday']) / dataView.pay_count['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.pay_count['yesterday'] - dataView.pay_count['today']) / dataView.pay_count['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>
                <div class="data-view-item">
                    <DataBlock title='商品成本' 
                        :radioValue="radioValue.cost" 
                        @ChangeradioValue="salesRadioChange($event,'cost')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>¥{{dataView.product_cost?dataView.product_cost[radioValue.cost]:'0.00'}}</span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.cost == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.product_cost">
                                        <span class="up" v-if="dataView.product_cost['yesterday'] == 0">
                                            ↑ {{dataView.product_cost['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.product_cost['today'] > dataView.product_cost['yesterday'] || dataView.product_cost['today'] == dataView.product_cost['yesterday']">
                                            ↑ {{((dataView.product_cost['today'] - dataView.product_cost['yesterday']) / dataView.product_cost['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.product_cost['yesterday'] - dataView.product_cost['today']) / dataView.product_cost['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>            
                <div class="data-view-item">
                    <DataBlock title='结算金额' 
                        :radioValue="radioValue.settlement" 
                        @ChangeradioValue="salesRadioChange($event,'settlement')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>¥{{dataView.settle_amount?dataView.settle_amount[radioValue.settlement]:'0.00'}} </span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.settlement == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.settle_amount">
                                        <span class="up" v-if="dataView.settle_amount['yesterday'] == 0">
                                            ↑ {{dataView.settle_amount['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.settle_amount['today'] > dataView.settle_amount['yesterday'] || dataView.settle_amount['today'] == dataView.settle_amount['yesterday']">
                                            ↑ {{((dataView.settle_amount['today'] - dataView.settle_amount['yesterday']) / dataView.settle_amount['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.settle_amount['yesterday'] - dataView.settle_amount['today']) / dataView.settle_amount['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div> 
                <div class="data-view-item">
                    <DataBlock title='退款金额' 
                        :radioValue="radioValue.refund" 
                        @ChangeradioValue="salesRadioChange($event,'refund')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>¥{{dataView.refund_amount?dataView.refund_amount[radioValue.refund]:'0.00'}} </span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.refund == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.refund_amount">
                                        <span class="up" v-if="dataView.refund_amount['yesterday'] == 0">
                                            ↑ {{dataView.refund_amount['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.refund_amount['today'] > dataView.refund_amount['yesterday'] || dataView.refund_amount['today'] == dataView.refund_amount['yesterday']">
                                            ↑ {{((dataView.refund_amount['today'] - dataView.refund_amount['yesterday']) / dataView.refund_amount['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.refund_amount['yesterday'] - dataView.refund_amount['today']) / dataView.refund_amount['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>            
                <div class="data-view-item">
                    <DataBlock title='每单平均消费' 
                        :radioValue="radioValue.average" 
                        @ChangeradioValue="salesRadioChange($event,'average')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>¥{{dataView.avg_amount?dataView.avg_amount[radioValue.average]:'0.00'}} </span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.average == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.avg_amount"> 
                                        <span class="up" v-if="dataView.avg_amount['yesterday'] == 0">
                                            ↑ {{dataView.avg_amount['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.avg_amount['today'] > dataView.avg_amount['yesterday'] || dataView.avg_amount['today'] == dataView.avg_amount['yesterday']">
                                            ↑ {{((dataView.avg_amount['today'] - dataView.avg_amount['yesterday']) / dataView.avg_amount['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.avg_amount['yesterday'] - dataView.avg_amount['today']) / dataView.avg_amount['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>     
            </div>
        </div>
        <div class="section-item">
            <div class="section-title mt10">充值统计</div>
            <div class="data-view-box">
                <div class="data-view-item">
                    <DataBlock title='充值单数' 
                        :radioValue="radioValue.rechargeCount" 
                        @ChangeradioValue="salesRadioChange($event,'rechargeCount')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>{{dataView.recharge_count?dataView.recharge_count[radioValue.rechargeCount]:'0'}}单 </span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.rechargeCount == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.recharge_count">
                                        <span class="up" v-if="dataView.recharge_count['yesterday'] == 0">
                                            ↑ {{dataView.recharge_count['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.recharge_count['today'] > dataView.recharge_count['yesterday'] || dataView.recharge_count['today'] == dataView.recharge_count['yesterday']">
                                            ↑ {{((dataView.recharge_count['today'] - dataView.recharge_count['yesterday']) / dataView.recharge_count['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.recharge_count['yesterday'] - dataView.recharge_count['today']) / dataView.recharge_count['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>
                <div class="data-view-item">
                    <DataBlock title='充值金额' 
                        :radioValue="radioValue.rechargeAmount" 
                        @ChangeradioValue="salesRadioChange($event,'rechargeAmount')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>¥{{ dataView.recharge_amount?dataView.recharge_amount[radioValue.rechargeAmount] : '0.00'}}</span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.rechargeAmount == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.recharge_amount">
                                        <span class="up" v-if="dataView.recharge_amount['yesterday'] == 0">
                                            ↑ {{dataView.recharge_amount['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.recharge_amount['today'] > dataView.recharge_amount['yesterday'] || dataView.recharge_amount['today'] == dataView.recharge_amount['yesterday']">
                                            ↑ {{((dataView.recharge_amount['today'] - dataView.recharge_amount['yesterday']) / dataView.recharge_amount['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.recharge_amount['yesterday'] - dataView.recharge_amount['today']) / dataView.recharge_amount['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>
                <div class="data-view-item">
                    <DataBlock title='注册用户数量' 
                        :radioValue="radioValue.rechargeAccount" 
                        @ChangeradioValue="salesRadioChange($event,'rechargeAccount')"
                        cardIcon="" 
                        bgColor="#409eff">
                        <div class="item-value-block">
                            <div class="item-number-value">
                                <span>{{dataView.user_count?dataView.user_count[radioValue.rechargeAccount]:'0'}} 人 </span>
                            </div> 
                            <div class="data-trend">
                                <div v-if="radioValue.rechargeAccount == 'today'">
                                    <span>较昨日</span>
                                    <span v-if="dataView.user_count">
                                        <span class="up" v-if="dataView.user_count['yesterday'] == 0">
                                            ↑ {{dataView.user_count['today']>0?'新增长（∞%）':'0%'}}
                                        </span>
                                        <span class="up" v-else-if="dataView.user_count['today'] > dataView.user_count['yesterday'] || dataView.user_count['today'] == dataView.user_count['yesterday']">
                                            ↑ {{((dataView.user_count['today'] - dataView.user_count['yesterday']) / dataView.user_count['yesterday'] * 100).toFixed(2) }}%
                                        </span>
                                        <span class="down" v-else>
                                            ↓{{((dataView.user_count['yesterday'] - dataView.user_count['today']) / dataView.user_count['yesterday'] * 100).toFixed(2)}}%
                                        </span>
                                    </span>
                                </div> 
                            </div>       
                        </div>
                    </DataBlock>
                </div>
            </div>
        </div>
        <div class="section-item">
            <div class="section-title mt10">数据概览 </div>
            <div class="data-overview-box">
                <DataOverview></DataOverview> 
            </div>
            
        </div>
        <div class="section-item">
            <div class="section-title mt10">商品销售明细 </div>
            <div class="sale-list-box">
                <SaleList></SaleList>
            </div>
        </div>
    </div>
</template>
<script>
import DataBlock from '@/components/dataView/dataBlock.vue';
import SaleList from '@/components/dataView/saleList.vue'
import DataOverview from '@/components/dataView/dataOverview.vue'
export default {
  name: "DataView",
  components: {
    DataBlock,
    SaleList,
    DataOverview
  },
  data() {
    return {
        radioValue:{ 
            radioValueSales: 'total',
            count:'today',
            cost:'today',
            settlement:'today',
            refund:'today',
            average:'today',
            rechargeCount:'today',
            rechargeAmount:'today',
            rechargeAccount:'today'
        },
       
        dataView: {
        },
        
    };
  },
  mounted(){
    this.getData();
  },
  methods: {
    getData(){
        this.$api.overview({}).then(res=>{
            if(res.code == 1) {
                this.dataView = res.data;
            }
        }).catch(e=>{
        })
    },
    salesRadioChange(value,name){
        this.radioValue[name] = value;
    },
  }
}
</script>
<style lang="scss">
.data-card:hover .card-icon-box {
    transform: scale(1.1);
}
</style>
<style lang="scss" scoped>
.section-item{
    padding: 20px 20px 0px;
    .section-title{
        font-size: 18px;
        font-weight: 700;
        color: #303133;
        margin-bottom: 20px;
        position: relative;   
        padding-left: 12px;
        &:before{
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 18px;
            background-color: #409eff;
            border-radius: 2px;
        }
    }
    .data-view-box{
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        justify-content: space-between;
        .data-view-item{
            // flex: 1;
            .item-value-block{
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: flex-start;
                padding:10px 20px 20px 20px;
                .data-trend{
                    font-size: 12px;
                    color: #909399;
                    margin-top: auto;
                    .up{
                        color: #f56c6c;
                        margin-left: 5px;
                    }
                    .down{
                        color: #67c23a;
                        margin-left: 5px;
                    }
                }
                .usdt-text{
                    color: #67c23a;
                    font-size: .85em;
                    margin-left: 5px;
                    font-weight: 400;
                }
                .item-number-value{
                    font-size: 24px;
                    font-weight: 700;
                    color: #303133;
                    margin-bottom: 5px;
                    display: flex;
                    align-items: center;
                    justify-content: flex-start;

                    
                }
                .item-number-extra-info-item{
                    border-top: 1px dashed #ebeef5;
                    padding-top: 8px;
                    width:100%;
                    .number-extra-info-text{
                        color: #606266;
                    }
                    .number-extra-info-val{
                        font-weight: 500;
                    }
                }

                .item-label-item{
                    font-size: 14px;
                    color: #909399;
                    margin-bottom: 10px;
                }
            }
        }
    }
}
</style>