<template>
    <div class="main-box-component">
        <el-card >
            <div slot="header" class="main-box-header" v-if="hasHeader">
                
                <slot name="title">
                    <span class="title-name" :class="titleClass">{{titleName}}</span>
                </slot>
                <slot name="oprBtn">
                    <el-button class="f14" type="primary" size="medium">导出所有订单</el-button>
                </slot>
            </div>
            <div v-if="!noDescription" class="page-description" :style="{background:descriptionBgColor,borderColor:descriptionBorderColor,'border-left-width':descriptionBorderWidth}">
                {{description}}
            </div>
            <template>
                <slot name="pageCon">
                    主要内容
                </slot>
            </template>
        </el-card>
    </div>
</template>
<script>
/**
 * 页面头部组件
 * titleClass :标题名称自定义Class
 * titleName:标题名称 default:标题 例：商品订单列表
 * descriptionBgColor: 描述模块背景色 default:#f0f9eb 绿色
 * descriptionBorderColor:描述模块左边框颜色 default:#67c23a 绿色
 * 
 * description：描述模块描述内容 default:描述
 * 
 * descriptionBorderWidth: 描述模块左边框宽度 default:4px
 * 
 * noDescription: 是否显示描述模块 default:false 显示
 * hasHeader:有卡片头部 default:true  有
 * 
 */
    export default {
        name:'MainBoxHeader',
        data() {
            return{

            }
        },
        props:{
            titleClass:{
                type:String,
                default:'',
            },
            titleName:{
                type:String,
                default:'标题'
            },
            descriptionBgColor:{
                type:String,
                default:'#f0f9eb'
            },
            descriptionBorderColor:{
                type:String,
                default:'#67c23a'
            },
            description:{
                type:String,
                default:'描述'
            },
            descriptionBorderWidth:{
                type:String,
                default:'4px'
            },
            noDescription:{
                type:Boolean,
                default:false,
            },
            hasHeader:{
                type:Boolean,
                default:true,
            }
        },
    }
</script>
<style lang="scss" scoped>

    .main-box-component{
        padding: 20px;
        .main-box-header{
            display: flex;
            justify-content: space-between;
            align-items: center;    
            .title-name{
                font-size: 14px;
                // font-weight: 600;
                color: #303133;
            }       
        }
        .page-description{            
            color: #606266;
            margin-bottom: 20px;
            padding: 10px 15px;
            border-radius: 4px;
            border: none;
            border-left: 4px solid;
        }
    }
    
</style>
