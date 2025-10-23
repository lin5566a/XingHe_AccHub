<template>
    <div>
        <el-dialog
            title="编辑商品排序"
            :visible.sync="dialogVisible"
            width="400px"
            custom-class="add-dialog-class"
            :before-close="closeDialog"
            :close-on-click-modal="false"
            >
            <span class="add-dialog-con">
                <el-form ref="form" :model="newForm" :rules="rules" label-width="80px" size="small">
                    <el-form-item label="商品名称" prop="name">
                        <el-input :disabled="true" v-model="newForm.name" placeholder="请输入商品名称"></el-input>
                    </el-form-item>
                    <el-form-item label="当前排序" prop="sort">
                        <el-input :disabled="true" v-model="newForm.sort" placeholder="当前排序"></el-input>
                    </el-form-item>                    
                    <el-form-item label="新排序值" prop="newSort">
                        <el-input-number class="form-input" placeholder="请输入排序值（1-9999）" v-model="newForm.newSort" :min="1" :max="9999" size="small"></el-input-number>
                        <span class="form-tip">数值越小排序越靠前</span>
                    </el-form-item>                    
                </el-form>
            </span>
            <span slot="footer" class="dialog-footer">
                <el-button class="f14" @click="closeDialog" size="small" :disabled="loading">取 消</el-button>
                <el-button class="f14" @click="addSubmit" type="primary" size="small" :disabled="loading">确定</el-button>
            </span>
        </el-dialog>
    </div>
</template>
<script>
export default{
    name:'productListAdd',
    props:{
        dialogVisible:{
            type:Boolean,
            default:false
        },
        formData:{
            type:Object,
            required:true,
        },
        loading:{
            type:Boolean,
            default:false,
        }
    },
    data(){
        return{
            rules:{
                newSort: [
                    { required: true, message: '请输入排序值', trigger: 'blur' }
                ],
                name:[
                    { required: false, message: '请输入排序值', trigger: 'blur' }
                ],
                sort:[
                    { required: false, message: '请输入排序值', trigger: 'blur' }
                ]
            },
            newForm:0,
        }
    },
    mounted(){ 
        this.newForm = this.formData
    },
    methods:{        
        closeDialog(){
            this.$emit('closeDialog')
        },
        async addSubmit(){        
            this.$refs.form.validate((valid) => {
                if(valid){  
                    let data = {
                        id:this.newForm.id,
                        sort:this.newForm.newSort
                    }
                    this.$emit("submitData",data)
                }else{
                    return false;
                }
            });
        },
    }
}
</script>
<style scoped lang="scss">
    .form-input{
        width: 100%;
    }
    .form-tip{
        margin-top: 5px;
        font-size: 12px;
        color: #909399;
        text-align: center;
    }
</style>

