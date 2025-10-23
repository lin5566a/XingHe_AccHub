<template>
    <div class="data-block">
        <el-card shadow="hover" class="data-card">
            <div class="data-card-con">
                <div class="data-block-header" v-if="hasTitle">
                    <div class="left-header">
                        <span>{{title}}</span>
                    </div>
                    <div class="right-header">
                        <el-radio-group v-model="localRadioValue" size="mini">
                            <el-radio-button label="total" size="mini">总计</el-radio-button>
                            <el-radio-button label="today" size="mini">今日</el-radio-button>
                            <el-radio-button label="yesterday" size="mini">昨日</el-radio-button>
                            <el-radio-button label="month" v-if="hasMonth" size="mini">本月</el-radio-button>
                            <el-radio-button label="last_month" v-if="hasMonth" size="mini">上月</el-radio-button>
                        </el-radio-group>
                    </div>
                </div>                
                <slot name="cardContent"></slot>
                <div class="card-content" v-if="!custom">
                    <div v-if="cardIcon" class="card-icon-box" :style="{backgroundColor:bgColor}">
                        <i class="card-icon" :class="cardIcon"></i>
                    </div>
                    <div class="card-data-box">
                        <slot></slot>
                    </div>
                    
                </div>
            </div>
        </el-card>
        
    </div>
</template>
<script>
    export default{
        data(){
            return{

            }
        },
        props:{
            hasTitle:{
                type:Boolean,
                default:true,
            },
            hasMonth:{
                type:Boolean,
                default:false,
            },
            title:{
                type:String,
                default:'',
            },
            radioValue:{
                type:String,
                default:'',
            },
            cardIcon:{
                type:String,
                default:'',
            },
            bgColor:{
                type:String,
                default:'#409eff',
            },
            custom:{
                type:Boolean,
                default:false,
            }
        },
        computed:{
            localRadioValue:{
                get(){
                    return this.radioValue;
                },
                set(val){
                    this.$emit('ChangeradioValue', val);
                }
            }
        },
        methods:{
            // handleRadioChange(value){
            //     this.$emit('ChangeradioValue', value);
            // }
        }
    }
</script>
<style lang="scss">
    .data-block{
        .right-header{
            .el-radio-button--mini .el-radio-button__inner{
                padding: 5px 11px;
            }
        }
        
    }
</style>
<style lang="scss" scope>
.data-block{
    .data-card{
        height: 166px;
        transition: all .3s;
        .data-block-header{
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0px 10px;
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
        .card-content{
            display: flex;
            align-items: center;
            height: calc(100% - 50px);
            padding: 0px 15px;
            .card-icon-box{
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 15px;
                border-radius: 8px;
                transition: 0.3s;
                .card-icon{
                    font-size: 30px;
                    color: #fff;
                    
                }
            }
            .card-data-box{
                flex:1;
            }
        }
    }
    .data-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px #0000001a;
    }
   
}
</style>