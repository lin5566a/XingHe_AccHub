<template>
    <div class="channel-setting-box"> 
        <div class="setting-header">
            <el-button type="primary" icon="el-icon-plus" size="small" @click="addChannelBtn">新增渠道</el-button>
        </div>
        <div class="table-box">
            <el-table 
                :data="channelData.tableData"
                style="width: 100%" border size="small">
                <el-table-column prop="name" label="渠道名称" width="180"></el-table-column>
                <el-table-column prop="promotion_link" label="推广链接">
                    <template slot-scope="scope">
                        <div class="flexBetweenCenter">
                            <div class="blue-color">{{ scope.row.promotion_link }}</div>
                            <i class="el-icon-copy-document copy-btn" @click="copyText(scope.row.promotion_link)"></i>
                        </div>
                    </template>
                </el-table-column>
                <!-- <el-table-column prop="status" label="状态" width="120">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.status" @change="statusChange(scope.row)" :disabled="dialogData.loading"></el-switch>
                    </template>
                </el-table-column> -->
                <el-table-column prop="updated_at" label="创建时间" width="180"></el-table-column>
                <el-table-column prop="status" label="操作" width="120">
                    <template slot-scope="scope">
                        <div class="">
                            <el-button type="text" @click="editChannelBtn(scope.row)">编辑</el-button>
                            <el-button :disabled = "scope.row.is_default" class="text-btn-danger" type="text" @click="deleteChannelBtn(scope.row)">{{scope.row.is_default ? '默认' :'删除'}}</el-button>
                           
                        </div>
                        
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="dialog-box">
            <EditChannel type = "add" title="新增渠道" 
                v-if="dialogData.addChannel.visible"
                :visible = "dialogData.addChannel.visible" 
                :channelData = "dialogData.addChannel.item"
                :loading = "dialogData.loading"
                @closeDialog = "closeDialog"
                @submitChannel="addChannel"
                >
            </EditChannel>
            <EditChannel type = "edit" title="编辑渠道" 
                v-if="dialogData.editChannel.visible"
                :visible = "dialogData.editChannel.visible" 
                :channelData = "dialogData.editChannel.item"
                :loading = "dialogData.loading"
                @closeDialog = "closeDialog"
                @submitChannel="editChannel"
                >
            </EditChannel>
     
        </div>
    </div>
</template>
<script>
import EditChannel from "@/components/system/editChannel.vue"
export default {
    name: "channelSetting",
    components:{
        EditChannel
    },
    data() {
        return {
            dialogData:{
                loading:false,
                addChannel:{
                    visible:false,
                    item:{}
                },
                editChannel:{
                    visible:false,
                    item:{}
                }               
            },
            channelData:{
                tableData:[]
            },
        }
    },
    mounted(){
        this.channelConfig()
    },
    methods: {
        channelConfig(){
            this.$api.channelConfig({}).then(res=>{
                if(res.code == 1){
                    this.channelData.tableData = res.data.list
                }else{
                    this.message.error(res.msg)
                }
            })
        },
        closeDialog(){
            this.dialogData.addChannel.visible = false;
            this.dialogData.editChannel.visible = false;
            this.dialogData.addChannel.item = {};
            this.dialogData.editChannel.item = {};
        },
        addChannelBtn() {   
            this.dialogData.addChannel.visible = true;
            this.dialogData.addChannel.item = {
                "name": "",
                "status": true
            };
        },
        addChannel(form){
            console.log(form,'=====')
            this.dialogData.loading = true
            this.$api.addChannelConfig(form).then(res=>{
                this.dialogData.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.channelConfig()
                    this.closeDialog()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(e=>{
                this.dialogData.loading = false
            })
        },
        copyText(text){     
            navigator.clipboard.writeText(text).then(() => {
                this.$message({
                    message: '复制成功',
                    type: 'success'
                })
            }).catch(() => {
                this.$message({
                    message: '复制失败',
                    type: 'error'
                })
            })
        },
        statusChange(row){
            let status = row.status
            row.status = !status
            let data = {
                "id": row.id,
                "name": row.name,
                "status": !row.status
            }
            this.editChannel(data)
        },
        //编辑按钮
        editChannelBtn(item){  
            this.dialogData.editChannel.visible = true;
            this.dialogData.editChannel.item = item;
        },
        editChannel(form){ 
            this.dialogData.loading = true
            let data = {
                "id": form.id,
                "name": form.name,
                "status": form.status
            }
            this.$api.editChannelConfig(data).then(res=>{
                this.dialogData.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.channelConfig()
                    this.closeDialog()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(e=>{
                this.dialogData.loading = false
            })
          
        },
        //删除按钮
        deleteChannelBtn(row){
            this.$confirm(`确定要删除渠道"${row.name}"吗？删除后将无法恢复。`, '确认删除', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.deleteChannelConfig(row)
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
        //删除配置接口
        deleteChannelConfig(row){
            this.$api.deleteChannelConfig({id:row.id}).then(res=>{
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.channelConfig()
                }else{
                    this.$message.error(res.msg)
                }
            })
        }
    },
};
</script>
<style lang="scss" scoped>
.channel-setting-box{
    padding:20px 0;
    .setting-header{
        
    }
    .table-box{
        margin-top:20px;
        .copy-btn{
            cursor: pointer;
            color: #909399;
            &:hover{
                color: #409eff;
            }
        }
        .default-channel{
            color: #909399;

        }
    }
    
}
    
</style>