<template>
    <div class="help-components-box">
        <div class="table-box">
            <el-table :data="dataTable" style="width: 100%" v-loading="loading" border stripe sizi="small">
                <el-table-column type="index" label="序号" width="60"></el-table-column>
                <el-table-column prop="title" label="文档标题" min-width="150" show-overflow-tooltip></el-table-column>
                <el-table-column prop="subtitle" label="副标题" min-width="150" show-overflow-tooltip></el-table-column>
                <el-table-column prop="category" label="文档分类" width="120">
                    <template scope="scope">
                        <el-tag size="small" :type="getCategoryTagType(scope.row.category)">
                        {{scope.row.category }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="文档内容" width="100" align="center">
                    <template scope="scope">
                        <el-button type="text" size="small" link @click="preview(scope.row)">预览</el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="sort_order" label="排序" width="80"></el-table-column>
                <el-table-column prop="status" label="状态" width="120">
                    <template scope="scope">
                        <el-switch sizi="small"
                        v-model="scope.row.status"
                        :active-value="'1'"
                        :inactive-value="'0'"
                        @change="(val) => statusChange(val, scope.row)"
                        ></el-switch>
                        <span class="status-text ml8">{{ scope.row.status =='1' ? '已发布' : '未发布' }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="createTime" label="创建时间" width="180"></el-table-column>
                <el-table-column label="操作" width="180" fixed="right">
                <template scope="scope">
                    <div class="action-buttons">
                    <el-button size="small" type="primary" @click="editData(scope.row)">编辑</el-button>
                    <el-button size="small" type="danger" @click="deleteData(scope.row)">删除</el-button>
                    </div>
                </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>
<script>
    export default {
        name: "helpComponents",
        props:{
            loading:{
                type:Boolean,
                default:false,
            },
            dataTable:{
                type:Array,
                default(){return[]}
            }
        },
        data(){
            return{}
        },
        methods:{
            // 获取文档分类 标签类型
            getCategoryTagType (categoryCode) {
                switch (categoryCode) {
                    case '账号相关':
                    return 'success'
                    case '服务相关':
                    return 'warning'
                    case '教程指南':
                    return 'info'
                    default:
                    return ''
                }
            },
            //表格内 预览按钮点击事件
            preview(item){
                this.$emit("preview",item)
            },
            //表格内 状态切换事件
            statusChange(val,item){
                this.$emit("statusChange",val,item)
                // console.log(val)
            },
            //表格内 编辑按钮点击事件
            editData(item){
                this.$emit("editData",item)
            },
            //表格内 删除按钮点击事件
            deleteData(item){
                this.$emit("deleteData",item)
            }
        }
        
    }
</script>
<style lang="scss" scoped>
    .help-components-box{
        .table-box{

        }
    }
</style>
