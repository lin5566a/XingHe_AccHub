<template>
    <div>
        <el-dialog
                title="新增成本"
                :visible.sync="dialogVisible"
                width="500px"
                custom-class="dialog-class"
                @close="close"
                :close-on-click-modal="false"
                :top="top"
                >
                <span class="send-dialog-con">
                    <el-form ref="form" :model="form" :rules="rules" label-width="100px" size="small">
                        <el-form-item label="金额类型" prop="amount_type">
                            <el-radio-group v-model="form.amount_type">
                                <el-radio :label="1">增加</el-radio>
                                <el-radio :label="2">减少</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="成本金额" prop="amount">
                            <el-input-number class="form-input" v-model="form.amount" :precision="2" :step="1" :min="0" size="small"></el-input-number>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input
                                type="textarea"
                                :rows="3"
                                placeholder="请输入备注"
                                v-model="form.remark">
                            </el-input>
                        </el-form-item>
                    </el-form>
                </span>
                <span slot="footer" class="dialog-footer">
                    <el-button class="f14" @click="close" size="small">取 消</el-button>
                    <el-button class="f14" type="primary" @click="sure('form')" size="small" :disabled="loading">确认</el-button>
                </span>
            </el-dialog>
    </div>
</template>
<script>
    export default{
        props: {
            visible: {
                type: Boolean,
                default: false
            },
            loading: {
                type: Boolean,
                default: false
            }
        },
        data(){
            return{
                form:{
                    amount_type:2,
                    amount:0,
                    remark:'',
                },
                rules:{
                    amount_type:[
                        { required: true, message: '请选择金额类型', trigger: 'change' }
                    ],
                    amount:[
                        { required: true, message: '请输入成本金额', trigger: 'blur' }
                    ],
                    remark:[
                        { required: false, message: '请输入备注信息', trigger: 'blur' }                        
                    ],
                },
            }
        },
        computed: {
            dialogVisible: {
                get() {
                    return this.visible;
                },
                set(val) {
                    this.$emit('update:visible', val);
                }
            },
            top() {
                return '15vh';
            }
        },
        methods:{
            close(){
                this.$emit('close')
            },
            sure(formName){
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.$emit('suer',this.form)
                    } else {
                        return false
                    }
                });
            }
        }
    }
</script>
<style scoped lang="scss">
    .form-input{
        width: 100%;
    }
</style>