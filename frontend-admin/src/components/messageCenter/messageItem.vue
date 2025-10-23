<template>
    <div class="message-item " :class="message.is_read?'':'not-read'" @click="readMessage">
        <div class="message-icon">
            <i class="f16" :class="[icon, type=='manual'?'yellow-color':'blue-color']" ></i>            
        </div>
        <div class="message-content">
            <div class="message-title">{{ message.title }}</div>
            <div class="message-desc">{{ message.content }}</div>
            <div class="message-time">{{ message.time_ago }}</div>
        </div>
        <div class="unread-dot" :class="message.is_read?'hidden':''"></div>
    </div>
</template>
<script>
    export default{
        name:'messageItem',
        props:{
            message:{
                type:Object,
                default(){
                    return {}
                }
            },
            icon:{
                type:String,
                default:'el-icon-truck'
            },
            type:{
                type:String,
                default:'manual'

            }
        },
        data(){
            return{

            }
        },
        methods:{
            readMessage(){
                this.$emit('readMessage',this.message)
            }
        }
    }
</script>
<style lang="scss" scoped>
.message-item{
    display:flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 12px;
    cursor: pointer;
    transition: background-color .3s;
    position: relative;
    &.not-read{        
        background-color: #f0f9ff;
        &:hover {
            background-color: #e6f4ff;
        }
    }
    &:hover {
        background-color: #f8f9fa;
    }

    .message-icon{
        margin-right: 12px;
        margin-top: 2px;
    }
    .message-content{
        flex: 1;
        .message-title{
            font-size: 14px;
            font-weight: 500;
            color: #303133;
            margin-bottom: 4px;
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .message-desc{
            font-size: 12px;
            color: #606266;
            margin-bottom: 4px;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .message-time{
            font-size: 12px;
            color: #606266;
            margin-bottom: 4px;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
    .unread-dot {
        width: 6px;
        height: 6px;
        background-color: #f56c6c;
        border-radius: 50%;
        margin-left: 8px;
        margin-top: 6px;
        flex-shrink: 0;
        &.hidden{
            visibility: hidden;
        }
    }
}

</style>