<template>
    <div class="table-box">
        <el-table :data="tableData" style="width: 100%" border stripe  size="small">
            <el-table-column prop="channel_name" label="通道名称" min-width="200px"></el-table-column>
            <el-table-column prop="weight" label="送单权重" width="140px" align="center"></el-table-column>
            <el-table-column prop="fee_rate" label="手续费(%)" width="140px" align="center"></el-table-column>  
            <el-table-column label="单笔限额" align="center">
                <template scope="scope">
                   <span>{{ Number(scope.row.min_amount) }} - {{ Number(scope.row.max_amount) > 0 ? Number(scope.row.max_amount) :'不限'}}</span> 
                </template>
            </el-table-column>                      
            <el-table-column prop="status_text" label="状态" width="140px" align="center">
                <template scope="scope">
                    <el-tag :type="scope.row.status == 1? 'success':'info'" size="small">{{scope.row.status_text}}</el-tag>
                </template>
            </el-table-column>                       
            <el-table-column label="操作"  fixed="right" width="140px" align="center">
                <template scope="scope">
                    <div style="padding-bottom: 5px;">
                        <!-- <el-button type="text"> <i class="el-icon-edit-outline"></i> 编辑</el-button> -->
                        <el-button type="text" @click="editData(scope.row)"><i class="el-icon-edit-outline"></i>编辑</el-button>
                    </div>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>
<script>
export default {
    name:'PaymentTable',
    data(){
        return{
        }
    },
    props:{
        tableData:{
            type: Array,
            default: () => []
        },
        payment_method:{
            type:String,
            default: '',
        },
        payment_method_text:{
            type:String,
            default: '',
        }
    },
    methods:{
        editData(row){
            this.$emit('editData', row,this.payment_method,this.payment_method_text)
            console.log('编辑数据:', row)
        },
    }
}
</script>
