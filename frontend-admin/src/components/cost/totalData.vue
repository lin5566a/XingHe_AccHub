<template>
    <div class="cost-total-box">
        <el-card class="total-card" shadow="hover">           
            <div class="total-card-content">
                <div class="summary-title">{{label}}</div>
                <div class=" summary-value">¥{{value.toFixed(2)}}</div>
            </div>
        </el-card>
    </div>
</template>
<script>
export default {
    name:'CostTotal',
    props:{
        label:{
            type:String,
            default:'',
        },
        value:{
            type:Number,
            default:0,
        },
    },

}
</script>
<style scoped lang="scss">
.total-card-content{
    padding: 10px;
    text-align: center;
    .summary-title {
        font-size: 14px;
        color: #606266;
        margin-bottom: 10px;
    }
    .summary-value {
        font-size: 24px;
        font-weight: 700;
        color: #303133;
    }
}

</style>
