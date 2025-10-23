<template>
    <div class="template-setting-box">
       <mainBoxHeader titleName="会员等级设置" :description="memberData.description" descriptionBgColor='#ecf8ff' descriptionBorderColor='#50bfff' descriptionBorderWidth='5px'>
           <template slot="oprBtn">
               <span></span>
           </template>
           <template slot="pageCon">
               <div class="table-box">
                   <el-table :data="table.dataTable" style="width: 100%" v-loading="memberData.loading" border stripe>
                       <el-table-column label="等级名称">
                            <template scope="scope">
                               <el-tag size="small" :type="scope.row.id == 0 ? 'primary' : scope.row.id == 5?'danger':'warning'">{{scope.row.name}}</el-tag>                              
                           </template>
                       </el-table-column>
                       <el-table-column prop="upgrade_amount" label="累充升级条件"></el-table-column>
                       <el-table-column prop="discount" label="会员折扣" width="180">
                            <template scope="scope">
                               <span> {{ scope.row.discount }} {{ scope.row.id =='5'?'':'%'}} </span>
                           </template>
                       </el-table-column>
                       <el-table-column prop="description" label="会员介绍" min-width="200"></el-table-column>
                       <el-table-column label="操作" width="250" fixed="right">
                           <template scope="scope">
                               <div class="action-buttons">
                                    <el-button size="small" type="primary" @click="editData(scope.row)">编辑</el-button>
                               </div>
                           </template>
                       </el-table-column>
                   </el-table>
               </div>
              
           </template>
       </mainBoxHeader>
       <div class="dialog-box">
           
           <el-dialog title="编辑会员等级"  :visible.sync="dialog.editVisible"
               width="600px"
               custom-class="preview-class"
               :before-close="closeDialog"
               :close-on-click-modal="false"
               :top="dialog.top"
           >
           <EditMember @suerSubmit = "editSuer" :memberForm = 'memberForm' @closeDialog="closeDialog" :loading = 'dialog.loading'></EditMember>
           </el-dialog>
       </div>
   </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
import EditMember from '@/components/memberSet/edit.vue'
export default {
   name:'templateSetting',
   components:{
       mainBoxHeader,
       EditMember
   },
   data(){
        return{
            memberData:{
                loading:false,
                description:'管理会员等级和会员折扣设置，会员等级折扣适用于用户购买商品时的优惠。用户注册账户即可享受9折优惠。用户累计充值金额达到设定条件时，系统将自动升级用户的会员等级。'
            },
            table:{
                dataTable:[],
                
            },
            dialog:{
                editVisible:false,
                top:'15vh',
                loading:false,
            },
            //编辑模板参数            
            memberForm:{},

        }
   },
   created(){
       this.getMemberList()
   },
   methods:{
       //获取模板列表
        getMemberList(){
           
           const params = {
           }
           this.memberData.loading = true
           this.$api.memberList(params).then(res=>{
                this.memberData.loading = false
                if(res.code == 1){
                    this.table.dataTable = res.data.list
                    this.$message.success(res.msg)
                }else{
                    this.$message.error(res.msg)
                }
           }).catch(e=>{
                this.memberData.loading = false
           })         
        },
            //列表内编辑按钮点击事件
        editData(item){
           this.memberForm = {...item}
           this.dialog.editVisible = true
        },
        //关闭编辑模板弹窗
        closeDialog(){
            this.memberForm = {}
            this.dialog.editVisible = false    
        },
       //编辑弹窗确定按钮点击事件
        editSuer(formData){
            this.dialog.loading = true
            this.$api.memberLevelEdit(formData).then(res=>{                
                this.dialog.loading = false
                if(res.code == 1){
                    this.getMemberList();
                    this.$message.success(res.msg)
                    this.closeDialog()
                }
            }).catch(e=>{
                this.dialog.loading = false
            })
        },    
    
   }
}
</script>
<style lang="scss">
/* 预览样式 */

</style>
<style lang="scss" scoped>
.template-setting-box{
   .no-data{
       color: #909399;
       font-size: 12px;
       font-style: italic;
   }

}

</style>