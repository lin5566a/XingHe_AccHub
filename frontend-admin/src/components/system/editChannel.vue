<template>
    <div class="edit-channel-box">
        <el-dialog :visible.sync="visible" :title="title" width="500px" @close="closeDialog" :before-close="closeDialog" :close-on-click-modal="false"> 
            <el-form :model="channelForm" :rules="rules" ref="channelForm" label-width="100px" class="channel-form" size="small">
                <el-form-item label="渠道名称" prop="name">
                    <el-input v-model="channelForm.name" size="small"></el-input>
                </el-form-item> 
                <!-- <el-form-item label="渠道状态" prop="status">
                    <el-switch v-model="channelForm.status" size="small"></el-switch>
                </el-form-item> -->
                <el-form-item label="推广链接" prop="promotion_link" v-if="type == 'edit'">
                    <el-input  :readonly="true" v-model="channelForm.promotion_link" size="small">
                        <el-button slot="append" @click="copyText(channelForm.promotion_link)"> 复制 </el-button>
                    </el-input>
                </el-form-item> 
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="closeDialog" size="small">取 消</el-button>
                <el-button type="primary" @click="submitChannel" size="small" :disabled = "loading">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
export default {
    name: "editChannel",
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        title:{
            type: String,
            default: ''
        },
        type: {
            type: String,
            default: ''
        },
        loading: {
            type: Boolean,
            default: false
        },
        channelData: {
            type: Object,
            default: () => {
                return {}
            }
        }
    },
    data() {
        return {
            channelForm:{},
            rules: {
                name: [
                    { required: true, message: '请输入渠道名称', trigger: 'blur' },
                    { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
                ],
                status: [
                    { required: false, message: '请选择渠道状态', trigger: 'change' }
                ]
            }
        }
    },
    mounted(){
        this.channelForm = JSON.parse(JSON.stringify(this.channelData))
    },
    methods: {
        closeDialog() {
            this.$emit('closeDialog')
        },
        submitChannel(){
            this.$refs['channelForm'].validate((valid) => {
                if (valid) {
                    let channelForm = JSON.parse(JSON.stringify(this.channelForm))
                    channelForm.status = channelForm.status == true? 1:0
                    this.$emit("submitChannel",channelForm)
                } else {
                    return false;
                }
            });
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
        }
    }
}
</script>