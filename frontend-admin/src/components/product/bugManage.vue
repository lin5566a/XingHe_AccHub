<template>
    <div>
        <el-dialog
        title="安装包管理"
        :visible.sync="dialogVisible"
        width="780px"
        @close="handleClose"
        :before-close="handleClose"
        :close-on-click-modal = "false">
        <div class="bug-manage-box">
            <div class="goods-name"><span class="name-label">商品名称</span><span class="name-value">{{bugData.name}}</span></div>            
            <div class="bug-list" v-loading="loading">
                <div  v-for="(item,index) in bugList" :key="item.id" >
                    <bugItem                    
                    :bugData="item" 
                    :index="index" 
                    @deleteBug="deleteBug"
                    @updateBug="updateBug"
                    :deleteAble="bugList.length > 1">
                </bugItem>
                </div>
                
            </div>
            <div class="add-bug-box">
                <el-button type="primary" @click="addBug" :disabled="bugList.length >= 3 || loading" >添加安装包</el-button>
                <span class="tip-text">最多支持3个安装包</span>
            </div>
        </div>
        <!-- <span slot="footer" class="dialog-footer">
            <el-button @click="handleClose">取 消</el-button>
            <el-button type="primary" @click="sureAddPackage" :disabled="loading" >确 定</el-button>
        </span> -->
        </el-dialog>
    </div>
</template>
<script>
    import bugItem from './bugItem.vue'
    export default {
        components: {
            bugItem
        },
        data() {
            return {
                loading: false,
                bugList: []
            }
        },
        props: {
            dialogVisible: {
                type: Boolean,
                default: false
            },
            bugData:{
                type: Object,
                default: () => ({})
            }
        },
        watch: {
            dialogVisible(val) {
                if (val) {
                    this.getBugList()
                }else{
                    this.bugList = []
                }
            }
        },
        methods: {
            handleClose() {
                this.$emit('handleClose')
            },
            //设置默认包 空包
            setDefaultBug() {
                this.bugList = [{
                    product_id: this.bugData.id,
                    name: '',
                    type: 1,
                    is_show: 0,
                    isShow: false,
                    sort: this.bugList.length
                }]
            },
            // 获取包列表
            getBugList() {

                this.setDefaultBug()
                this.loading = true
                this.$api.productBugList({ product_id: this.bugData.id }).then(res => {
                    if (res.code === 1) {
                        if(res.data.list.length >0){
                            this.bugList = res.data.list.map(item => ({
                                ...item,
                                isShow: item.is_show === 1
                            }))
                        }
                        
                    } else {
                        this.$message.error(res.msg )
                    }
                }).catch(err => {
                    console.error('获取安装包列表失败:', err)
                    // this.$message.error('获取安装包列表失败')
                }).finally(() => {
                    this.loading = false
                })
            },
            // 添加安装包
            addBug() {
                if (this.bugList.length >= 3) {
                    this.$message.warning('最多支持3个安装包')
                    return
                }
                let newBug = {
                    product_id: this.bugData.id,
                    name: '',
                    type: 1,
                    is_show: 0,
                    isShow: false,
                    sort: this.bugList.length
                }
                this.bugList.push(newBug)
            },
            // 删除安装包
            deleteBug(bug,index) {
                // const bug = this.bugList[index]
                if (bug.id) {
                    this.$confirm('确认删除该安装包吗？', '提示', {
                        type: 'warning'
                    }).then(() => {
                        this.$api.deletePackage({ id: bug.id }).then(res => {
                            if (res.code === 1) {
                                this.$message.success(res.msg)
                                this.getBugList()
                            } else {
                                this.$message.error(res.msg)
                            }
                        })
                    }).catch(() => {})
                } else {
                    this.bugList.splice(index, 1)
                }
            },
            // 更新安装包
            updateBug() {
                this.getBugList()
                // const index = this.bugList.findIndex(item => item.id === data.id)
                // if (index > -1) {
                //     this.bugList[index] = {
                //         ...this.bugList[index],
                //         ...data,
                //         is_show: data.isShow ? 1 : 0
                //     }
                // }
            },
        }
    }
</script>
<style lang="scss" scoped>
.bug-manage-box{
    .goods-name {
        display: flex;
        font-size: 14px;
        margin-bottom: 18px;
        .name-label{
            color: #606266;
            font-size: 14px;
            height: 32px;
            padding: 0 12px 0 0;
            width: 100px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .name-value{
            background-color: #f5f7fa;
            height: 32px;
            cursor: not-allowed;
            // pointer-events: none;
            color: #a8abb2;
            display: flex;
            padding: 1px 11px;
            flex: 1;
            align-items: center;
            justify-content: flex-start;
            box-shadow: 0 0 0 1px #e4e7ed inset;
            // cursor: not-allowed;
            
        }
    }
    // padding: 0 20px;
        .bug-list {
            max-height: 500px;
            overflow-y: auto;
            // margin: 20px 0;
        }
        .add-bug-box {
            // margin-top: 20px;
            .tip-text {
                margin-left: 10px;
                color: #909399;
                font-size: 14px;
            }
        }
}
   
</style>    
