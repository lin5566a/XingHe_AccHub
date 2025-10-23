<template>
    <div class="customer-setting-box">
        <el-form :model="serviceForm" label-width="120px" :rules="serviceRules" ref="serviceFormRef" size="small">
            <el-form-item label="TG客服链接" prop="tgServiceLink">
                <el-input v-model="serviceForm.tgServiceLink" placeholder="请输入Telegram客服链接" size="small">
                    <template slot="prepend">https://t.me/</template>
                    <template slot="append">
                    <el-button @click="debugTgLink">调试</el-button>
                    </template>
                </el-input>
                <div class="form-tip">输入Telegram用户名或群组链接，将显示在网站底部和客服页面</div>
            </el-form-item>

            <el-form-item label="前台显示" prop="tg_show">
                <el-switch  v-model="serviceForm.tg_show" active-text="显示" inactive-text="隐藏"></el-switch>
            </el-form-item>

            <el-form-item label="群链接" prop="group_link">
                <el-input v-model="serviceForm.group_link" placeholder="请输入Telegram客服链接" size="small">                    
                    <template slot="append">
                        <el-button @click="debugGroup_link">调试</el-button>
                    </template>
                </el-input>
                <div class="form-tip">输入群链接，如 Telegram 群或其他社区群链接</div>
            </el-form-item>
            <el-form-item label="前台显示" prop="group_show">
                <el-switch  v-model="serviceForm.group_show" active-text="显示" inactive-text="隐藏"></el-switch>
            </el-form-item>
            <el-form-item label="在线客服链接" prop="onlineServiceLink">
                <el-input v-model="serviceForm.onlineServiceLink" placeholder="请输入在线客服系统链接" size="small">
                    <template slot="append">
                        <el-button @click="debugServiceLink">调试</el-button>
                    </template>
                </el-input>
                <div class="form-tip">输入完整的在线客服系统链接，将用于网站的在线咨询功能</div>
            </el-form-item>
            <el-form-item label="前台显示" prop="online_show">
                <el-switch  v-model="serviceForm.online_show" active-text="显示" inactive-text="隐藏"></el-switch>
            </el-form-item>
            <el-form-item>
                <el-button class="f14" type="primary" @click="saveServiceSettings" size="small">保存设置</el-button>
                <el-button class="f14" @click="resetServiceForm" size="small">重置</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>
<script>
export default {
    name:"customerSetting",
    data(){
        return{
            loading: false,
            serviceForm:{
                tgServiceLink: '',
                onlineServiceLink: '',
                group_link:'',
                tg_show:false,
                group_show:false,
                online_show:false,
            },
            oldServiceForm:{
                tgServiceLink: '',
                onlineServiceLink: '',
                group_link:'',
                tg_show:false,
                group_show:false,
                online_show:false,
            },
            serviceRules:{
                tgServiceLink: [
                    { required: true, message: '请输入Telegram链接', trigger: 'blur' },
                    { pattern: /^[a-zA-Z0-9_]+$/, message: 'Telegram链接格式不正确', trigger: 'blur' }
                ],
                onlineServiceLink: [
                    { required: true, message: '请输入在线客服系统链接', trigger: 'blur' },
                    { pattern: /^https?:\/\/.+/, message: '请输入有效的URL地址', trigger: 'blur' }
                ],
                group_link:[
                    { required: true, message: '请输入群链接', trigger: 'blur' },
                    { pattern: /^https?:\/\/.+/, message: '请输入有效的URL地址', trigger: 'blur' }
                ]
            }
        }
    },
    created(){
        this.getCustomerInfo()
    },
    methods:{
        //获取客服设置信息
        getCustomerInfo(){
            this.loading = true
            this.$api.customerInfo().then(res => {
                this.loading = false
                if(res.code === 1){
                    this.oldServiceForm = {
                        tgServiceLink: res.data.tg_service_link,
                        onlineServiceLink: res.data.online_service_link,
                        group_link:res.data.group_link,                        
                        tg_show:res.data.tg_show == 1 ? true : false,
                        group_show:res.data.group_show == 1 ? true : false,
                        online_show:res.data.online_show == 1 ? true : false,
                    }
                    this.serviceForm = {...this.oldServiceForm}
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('获取客服设置失败')
            })
        },
        //调试 TG客服链接
        debugTgLink(){
            if (this.serviceForm.tgServiceLink) {
                const url = `https://t.me/${this.serviceForm.tgServiceLink}`
                window.open(url, '_blank')
            } else {
                this.$message({
                    type:"error",
                    message:"请先输入Telegram客服链接"
                })
            }
        },
        //调试 在线客服
        debugServiceLink(){
            if (this.serviceForm.onlineServiceLink) {
                if (this.serviceForm.onlineServiceLink.startsWith('http://') || this.serviceForm.onlineServiceLink.startsWith('https://')) {
                    window.open(this.serviceForm.onlineServiceLink, '_blank')
                } else {
                    this.$message({
                        type:"error",
                        message:"请输入有效的URL地址，需要以http://或https://开头"
                    })
                }
            } else {
                this.$message({
                    type:"error",
                    message:"请先输入在线客服系统链接"
                })
            }
        },
        //调试 群链接
        debugGroup_link(){
            if (this.serviceForm.group_link) {
                if (this.serviceForm.group_link.startsWith('http://') || this.serviceForm.group_link.startsWith('https://')) {
                    window.open(this.serviceForm.group_link, '_blank')
                } else {
                    this.$message({
                        type:"error",
                        message:"请输入有效的URL地址，需要以http://或https://开头"
                    })
                }
            } else {
                this.$message({
                    type:"error",
                    message:"请先输入群链接"
                })
            }
        },
        //保存
        saveServiceSettings(){
            this.$refs.serviceFormRef.validate((valid) => {
                if (valid) {
                    this.loading = true
                    const data = {
                        tg_service_link: this.serviceForm.tgServiceLink,
                        online_service_link: this.serviceForm.onlineServiceLink,
                        group_link:this.serviceForm.group_link,
                        tg_show: this.serviceForm.tg_show,
                        group_show: this.serviceForm.group_show,
                        online_show: this.serviceForm.online_show,
                    }
                    this.$api.customerUpdate(data).then(res => {
                        this.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.oldServiceForm = {...this.serviceForm}
                        } else {
                            this.$message.error(res.msg)
                        }
                    }).catch(err => {
                        this.loading = false
                        this.$message.error('保存失败')
                    })
                }
            });          
        },
        resetServiceForm(){
            this.serviceForm = {...this.oldServiceForm}
            this.$refs.serviceFormRef.resetFields();
        }
    }
}
</script>
<style lang="scss" scoped>
    .customer-setting-box{
        .form-tip{
            font-size: 12px;
            color: #909399;
            margin-top: 5px;
            line-height: 1.5;
        }
    }
</style>