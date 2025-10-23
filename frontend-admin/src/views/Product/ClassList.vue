
<template>
    <div class="classify-view">
        <mainBoxHeader titleName="商品分类" :description="classifyData.description">
            <template slot="oprBtn">
                <el-button class="f14" type="primary" @click="addClassify" size="small">新增分类</el-button>
            </template>
            <template slot="pageCon">
                <div class="classify-query-box">
                    <div class="query-item">
                        <span class="query-label mr12">商品分类</span>
                        <el-input class="query-input" placeholder="请输入商品分类" v-model="classifyData.name" clearable size="small"></el-input>
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">状态</span>
                        <el-select class="query-select" v-model="classifyData.status" clearable placeholder="请选择" size="small">
                            <el-option label="禁用" value="0"></el-option>
                            <el-option label="启用" value="1"></el-option>
                        </el-select>   
                    </div>
                    <div class="query-item">
                        <el-button class="f14" @click="query" type="primary" size="small" :disabled="loading">查询</el-button>
                        <el-button class="f14" @click="reset" size="small" :disabled="loading">重置</el-button>
                    </div>                                     
                </div>
                 <!-- 表格 -->
                 <div class="data-table">
                    <el-table :data="table.tableData" style="width: 100%" v-loading="loading" border stripe size="small">
                        <el-table-column prop="id" label="分类ID" width="80"></el-table-column>
                        <el-table-column prop="name" label="分类名称" min-width="180"></el-table-column>
                        <el-table-column prop="description" label="分类描述" min-width="200"></el-table-column>
                        <el-table-column prop="sort_order" label="排序" width="80"></el-table-column>
                        <el-table-column prop="status" label="状态" width="100">
                        <template scope="scope">
                            <el-switch
                            v-model="scope.row.status"
                            :active-value="'1'"
                            :inactive-value="'0'"
                            :disabled="loading"
                            @change="(val) => statusChange(val, scope.row)"
                            ></el-switch>
                            <span class="status-text ml8">{{ scope.row.status == '1' ? '启用' : '禁用' }}</span>
                        </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="创建时间" width="180"></el-table-column>
                        <el-table-column label="操作" width="160" fixed="right">
                        <template scope="scope">
                            <div class="action-buttons">
                            <el-button class="f14" size="small" type="primary" @click="editClick(scope.row)" :disabled="loading">编辑</el-button>
                            <el-button class="f14" size="small" type="danger" @click="deleteClick(scope.row)" :disabled="loading">删除</el-button>
                            </div>
                        </template>
                        </el-table-column>
                    </el-table>
                    
                </div>
                <div class="pagination-box">
                    <el-pagination background :total="table.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="table.page"
                        :page-sizes="table.pageSizes"
                        :page-size="table.pageSize"
                        :disabled="loading"
                        layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
                <div class="dialog-box">
                    <el-dialog title="新增分类"  :visible.sync="dialog.addVisible"
                        width="500px"
                        custom-class="add-classify-class"
                        :before-close="closeAddDialog"
                        :close-on-click-modal="false"
                        :top="dialog.top"
                    >
                        <template>
                           <classifyDialog ref="addClassify" @closeDialog="closeAddDialog" :classifyData="dialog.addObj" @suerSubmit="addSuerSubmit" dialogType='add'></classifyDialog>
                        </template>
                    </el-dialog>
                    <el-dialog title="编辑分类"  :visible.sync="dialog.editVisible"
                        width="500px"
                        custom-class="add-classify-class"
                        :before-close="closeEditDialog"
                        :close-on-click-modal="false"
                        :top="dialog.top"
                    >
                        <template>
                           <classifyDialog ref="editClassify" @closeDialog="closeEditDialog" :classifyData="dialog.editObj" @suerSubmit="editSuerSubmit" dialogType="edit"></classifyDialog>
                        </template>
                    </el-dialog>
                </div>

            </template>
        </mainBoxHeader>
    </div>
</template>

<script>
import  mainBoxHeader from '@/components/mainBoxHeader.vue'
import classifyDialog from '@/components/product/classifyDialog.vue'
export default {
    name:'ClassList',
    components:{
        mainBoxHeader,
        classifyDialog
    },
    data() {
        return{
            loading:false,
            dialog:{
                addVisible:false,
                top:'15vh',
                editVisible:false,
                editObj:{},
                addObj:{
                    name: '', 
                    description: '',
                    sort_order: 1,
                    status: '1',
                }
            },
            classifyData:{
                description:'管理所有邮箱账号和社交媒体账号的分类信息，包括Gmail、微软邮箱、Instagram、Twitter、Facebook等账号类型。您可以添加、编辑、删除分类，并设置分类的描述、排序等信息。',
                name:'',
                status:"",
            },
            table:{
                tableData:[],
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
            },
            
        }
    },
    created(){
        this.categoryList();
    },
    methods:{
        //重置
        reset(){
            this.table.page = 1;
            this.classifyData.status = '';
            this.classifyData.name = '';
            this.categoryList();       
        },
        //查询
        query(){
            this.table.page = 1;
            this.categoryList();
        },
        //查询商品分类
        categoryList(){
            let data = {
                name:this.classifyData.name,
                status:this.classifyData.status,
                page:this.table.page,
                limit:this.table.pageSize
            }
            this.loading = true;
            this.$api.categoryList(data).then(res=>{
                this.loading = false;
                if(res.code === 1){
                    this.table.tableData = res.data.list;
                    this.table.total = res.data.total;
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                    });
                }
            }).catch(err=>{
                this.loading = false;
            })

        },
        //新增分类
        addClassify(){
            this.dialog.addVisible = true;
        },
        //表格内状态改变
        statusChange(val,row){
            let status = val;
            let old =val == '1' ? '0' : '1';
            row.status = old;
            let param = {...row }
            param.status = status;
            // console.log(old,'old',status,'val',param)
            this.loading = true
            this.$api.categoryEdit(param).then(res=>{
                this.loading = false;
                if(res.code === 1) {
                    this.$message({
                        type: 'success',
                        message: '操作成功!'
                    });
                    row.status = status;                    
                    this.categoryList();
                }else{
                    this.$message({
                        type: 'error',
                        message: res.msg
                    });
                }
            }).catch(err=>{
                this.loading = false;
            })
        },
        //表格内编辑按钮
        editClick(item){
            this.dialog.editObj ={...item}
            this.$forceUpdate()
            this.dialog.editVisible = true;
        },
        //表格内删除按钮
        deleteClick(item){
            this.$confirm(`确定要删除分类"${item.name}"吗？`, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true;
                this.$api.categoryDelete({id:item.id}).then(res=>{
                    this.loading = false;
                    if(res.code === 1){
                        this.$message({
                            type: 'success',
                            message: res.msg
                        });
                        this.table.page = 1;
                        this.categoryList();
                    }else{
                        this.$message({
                            type: 'error',
                            message: res.msg
                        });
                    }
                }).catch(err=>{
                    this.loading = false;
                })
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
         //分页
         handleCurrentChange(val) {
            this.table.page = val;
            this.categoryList();
        },
        handleSizeChange(val) {
            this.table.pageSize = val;
            this.table.page = 1; // 切换每页条数时，重置为第一页
            this.categoryList();
        },
        //关闭新增分类弹窗
        closeAddDialog(){
            this.dialog.addVisible = false;
            this.dialog.addObj.name= '';
            this.dialog.addObj.description= '';
            this.dialog.addObj.sort_order= 1;
            this.dialog.addObj.status= '1';
        },
        //关闭编辑分类弹窗
        closeEditDialog(){
            this.dialog.editVisible = false;
            this.dialog.editObj = {}
        },
        //编辑确认
        editSuerSubmit(form){
            // categoryEdit
            let param = form;
            this.$refs.editClassify.loading = true;
            this.$api.categoryEdit(param).then(res=>{
                this.$refs.editClassify.loading = false;
                if(res.code == 1){
                    this.$message({
                        message: res.msg,
                        type: 'success',
                        duration:3000,
                    });
                    this.closeEditDialog();
                    this.categoryList();
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                   });
                }
            }).catch(e=>{                
                this.$refs.editClassify.loading = false;
            })
            
        },
        //新增分类确认
        addSuerSubmit(form){
            let param = form;
            this.$refs.addClassify.loading = true;
            this.$api.categoryAdd(param).then(res=>{
                this.$refs.addClassify.loading = false;
                if(res.code == 1){
                    this.$message({
                        message: res.msg,
                        type: 'success',
                        duration:3000,
                    });
                    this.closeAddDialog();
                    this.categoryList();
                }else{
                    this.$message({
                        message: res.msg,
                        type: 'error',
                        duration:3000,
                  });
                }
            }).catch(e=>{
                this.$refs.addClassify.loading = false;
            })
        },
    }

}
</script>
<style lang="scss" scope>
.classify-view{
    .classify-query-box{
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 20px;
        .query-item{
            display: flex;
            align-items: center;        
            margin-right: 32px;
            font-size: 14px;
            margin-bottom: 18px;
            color:#606266;
            .query-input{
                width: 192px;
            }
            .query-select{
                width: 168px;
            }
        }
    }
}

</style>