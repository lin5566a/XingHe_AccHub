<template>
    <div class="form-box">
        <el-form ref="form" :model="classifyData" :rules="formRules" label-width="100px" size="small">
            <el-form-item  class="form-input" label="分类名称" prop="name">
                <el-input v-model="classifyData.name" placeholder="请输入分类名称" size="small"></el-input>
            </el-form-item>   
            <el-form-item class="form-input" label="分类描述" prop="description">
                <el-input type="textarea" :rows="3" v-model="classifyData.description" placeholder="请输入分类名称" size="small"></el-input>
            </el-form-item>
            <el-form-item label="排序" prop="sort_order">
                <el-input-number class="form-input" v-model="classifyData.sort_order" :precision="0" :step="1" :min="1" size="small"></el-input-number>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-switch
                    v-model="classifyData.status"
                    :active-value="'1'"
                    :inactive-value="'0'"
                    @change="(val) => statusChange(val)"
                >
                </el-switch>
                <span class="status-text ml8">{{ classifyData.status == '1'? '启用' : '禁用' }}</span>
            </el-form-item>
        </el-form>
        <div class="form-btn">
            <el-button class="f14" @click="closeDialog" size="small" :disabled="loading">取 消</el-button>
            <el-button class="f14" @click="suerSubmit" type="primary" size="small" :disabled="loading">确定</el-button>
        </div>
    </div>
</template>
<script>
    export default {
        name:'productListAdd',
        props:{
            classifyData:{
                type:Object,
                required:true
            },
            dialogType:{
                type:String,
                default:'add',//edit 编辑  add 新增
            }
        },
        data(){
            return {
                loading:false,
                form:{
                    name:'',
                    stock:0,
                    status:true,
                    sort:0,
                },
                formRules:{
                    name:[
                        { required: true, message: '请输入商品名称', trigger: 'blur' }
                        ,{ min: 2, max: 20, message: '长度在 2 到 100 个字符', trigger: 'blur' }
                    ],
                    description:[
                        { required: true, message: '请输入商品名称', trigger: 'blur' }
                        ,{ min: 2, max: 200, message: '长度在 2 到 100 个字符', trigger: 'blur' }
                    ],
                    sort_order:[{ required: true, message: '请输入排序', trigger: 'blur' }]
                }
            }
        },
        created(){
            // this.form = {...this.classifyData}
            // this.$forceUpdate()
            
            // console.log('this.form',this.form)
            // console.log('this.classifyData',this.classifyData)
        },
        methods:{
            statusChange(){

            },
            closeDialog(){
                this.$refs.form.resetFields();
                this.$emit("closeDialog")
            },
            //确认提交表单内容 新增  或   修改
            suerSubmit(){
                this.$refs.form.validate((valid) => {
                    if(valid){
                        this.$emit("suerSubmit",this.classifyData)
                    }else{
                        return false
                    }
                    
                });
               
            },
        },
        
        
    }
</script>
<style lang="scss" scoped>
    .form-box{
        .form-btn{
            display: flex;
            justify-content: flex-end;
            align-items:center;
        }
        .form-input{
            width: 100%;
        }
    }
</style>