<template>
    <div class="message-notice-center"> 
        <div class="notice-head">
            <div>通知中心</div>
            <el-button type="text" size="mini" @click="messageReadAll" :disabled="!messageData.stats || messageData.stats.total == 0">全部已读</el-button>
        </div>
        <div class="notice-con">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="手动发货" name="first">
                    <span slot="label">
                        <span class="flex align-top gap6">
                            <span>手动发货</span>                       
                            <el-badge v-if="messageData.stats && messageData.stats.manual_shipment >0" class="tabs-badge" size="mini" :value="messageData.stats.manual_shipment" :max="99"></el-badge>
                        </span>
                        
                    </span>
                    <div class="message-list">
                        <div class="no-message" v-if="!messageData.manual_shipment || messageData.manual_shipment.length == 0">暂无新消息</div>
                        <MessageItem v-for="(item,index) in messageData.manual_shipment" :key="index" :message = "item" type="manual" icon="el-icon-truck" @readMessage="manualRead"></MessageItem>
                    </div>
                    <div class="all-message text-center" >
                        <el-button type="text" @click="manualReadAll" :disabled="!messageData.manual_shipment || messageData.manual_shipment.length == 0">查看全部</el-button>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="补货提醒" name="second">
                    <span slot="label">
                        <span class="flex align-top gap6">
                            <span>补货提醒</span>                       
                            <el-badge v-if="messageData.stats && messageData.stats.replenishment >0" class="tabs-badge" size="mini" :value="messageData.stats.replenishment" :max="99"></el-badge>
                        </span>                       
                    </span>
                    <div class="message-list">
                        <div class="no-message" v-if="!messageData.replenishment || messageData.replenishment.length == 0">暂无新消息</div>
                        <MessageItem v-for="(item,index) in messageData.replenishment" :key="index" :message = "item" type="restock" icon="el-icon-message" @readMessage="repleniRead"></MessageItem>
                    </div>
                    <div class="all-message text-center" >
                        <el-button type="text" @click="repleniReadAll" :disabled="!messageData.replenishment || messageData.replenishment.length == 0">查看全部补货提醒</el-button>
                    </div>
                </el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>
<script>

    import MessageItem from '@/components/messageCenter/messageItem.vue'
    export default {
        name: "messageNoticeCenter",
        components:{
            MessageItem
        },
        props:{
            messageData:{   
                type:Object,
                default(){
                    return {}
                }
            }  
        },
        data() {
            return {
                activeName: 'first',                
            }
        },
        mounted() { 
           
        },
        methods: {
            handleClick(tab, event) {
                // console.log(tab, event);
            },
            gotoPage(path,message){
                if(message){
                    this.$local.set("order_no",message.order_no)
                }else{                    
                    this.$local.set("order_status",2)
                }
                this.$router.push(
                    {
                        path:'/'+path,
                        query:{
                            _ts: Date.now()
                        }
                    }
                )  
                this.$message.success('搜索条件已应用，跳转到商品订单页面')   
            },
            //补货申请页面跳转
            gotoRepleniRea(path){
                this.$local.set("insite_status",'0')
                this.$router.push(
                    {
                        path:'/'+path,
                        query:{
                            _ts: Date.now()
                        }
                    }
                )                   
                this.$message.success('搜索条件已应用，跳转到补货提醒页面')     
            },
            //手动发货消息阅读
            manualRead(message){
                this.gotoPage("order",message)
                if(!message.is_read){
                    this.$emit("readMessage",message)
                }
            },
            //补货提醒消息阅读
            repleniRead(message){
                this.gotoRepleniRea("insiteMessages")
                if(!message.is_read){
                    this.$emit("readMessage",message)
                }
               
            },
            //手动发货消息全部阅读
            manualReadAll(){
                this.gotoPage("order")
                if(this.messageData.stats.manual_shipment >0){
                    this.$emit("readAllMessage",'manual_shipment')
                }
                
            },
            //补货提醒消息全部阅读
            repleniReadAll(){
                this.gotoRepleniRea("insiteMessages")
                if(this.messageData.stats.replenishment > 0){
                    this.$emit("readAllMessage",'replenishment')
                }
            },
            //消息全部已读
            messageReadAll(){
                this.$emit("messageReadAll")
            }
           
        }
    }
</script>
<style lang="scss" scoped>
    .message-notice-center{
        .notice-head{
            display: flex;
            justify-content: space-between ;
            align-items: center;
        }
        .notice-con{
            .tabs-badge{
                ::v-deep .el-badge__content{
                    font-size: 10px;
                    padding: 0 4px;
                    height: 16px;
                    line-height: 16px;
                }
            }
            .message-list{
                max-height: 320px;
                overflow-y: auto;
                .no-message{
                    text-align: center;
                    padding: 20px;
                    color: #606266;
                }
            }
            .all-message{
                text-align: center;
                padding: 10px;
                padding-bottom: 0;
                border-top: 1px solid #f0f0f0;
            }
        }
    }
</style>