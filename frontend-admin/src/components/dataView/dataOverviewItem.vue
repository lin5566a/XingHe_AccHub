<template>
    <div class="data-overview-item" @click="changeData" :class="active?'active':''">
        <div class="item-title">{{title}}</div>
        <div class="item-value">{{value}}</div>
        <div class="compare">
            <span class="compare-label">{{compareDate}}</span>
            <span class="conpare-value" :class="compareClass">{{compareValue}}</span>
        </div>
    </div>
</template>
<script>
export default {
    name: 'dataOverviewItem',
    props: {
        title: {
            type: String,
            default:""
        },
        value: {
            type: String,
            default:"0.00"
        },
        compareDate: {
            type: String,
            default:""
        },
        compareValue: {
            type: String,
            default:""
        },
        compareClass: {
            type: String,
            default:""
        },
        active:{
            type: Boolean,
            default:false,
        }
    },
    methods: {
        changeData(){
            this.$emit('changeData',this.title)
        }
    }
}
</script>
<style scoped lang="scss">
    .data-overview-item{
        width: 100%;
        box-sizing: border-box;
        // background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        text-align: left;
        transition: all .3s;
        box-shadow: 0 2px 8px #0000001a;
        // border: 1px solid #e9ecef;
        &:hover {
            background: #fff;
            // box-shadow: 0 2px 8px #0000001a;
            transform: translateY(-2px);
            cursor: pointer;
        }
        &.active{
            background: #fff ;
            border: 2px solid #409EFF ;
            box-shadow: 0 4px 12px #409eff33 ;
        }
        .item-title{
            font-size: 14px;
            color: #909399;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .item-value{
            font-size: 28px;
            font-weight: 700;
            color: #303133;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .compare{
            font-size: 12px;
            color: #909399;
            display: flex;
            align-items: center;
            gap: 8px;
            .up {
                color: #67c23a;
                font-weight: 500;
                margin-left: 5px;
            }
            .down{
                color: #f56c6c;
                font-weight: 500;
                margin-left: 5px;
            }
        }
    }
</style>