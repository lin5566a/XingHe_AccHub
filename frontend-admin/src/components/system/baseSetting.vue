<template>
    <div class="base-setting">
        <el-form :model="basicForm" label-width="120px" :rules="basicRules" ref="basicFormRef" size="small">
            <el-form-item label="系统名称" prop="system_name">
              <el-input v-model="basicForm.system_name" placeholder="请输入系统名称" size="small"></el-input>
            </el-form-item>
            <el-form-item label="系统Logo">
                <div class="upload-box">
                    <el-upload
                        class="avatar-uploader"
                        action="#"
                        :show-file-list="false"
                        :auto-upload="false"
                        :on-change="handleLogoChange"
                    >
                        <img v-if="basicForm.system_logo" :src="$baseURL + basicForm.system_logo" class="avatar" />
                        <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                    </el-upload>
                    <div class="upload-tip">建议尺寸: 200px * 60px，支持jpg、png格式</div>
                </div>
                
            </el-form-item>
            <el-form-item label="版权信息" prop="copyright_info">
              <el-input v-model="basicForm.copyright_info" placeholder="请输入版权信息" size="small"></el-input>
            </el-form-item>
            <el-form-item label="提示音设置" prop="copyright_info">
                <div class="auto-set-box">
                    <el-switch size="small"
                        v-model="basicForm.manual_shipment_sound"
                        @change="changeManual">
                    </el-switch>
                    <span>手动发货提示音</span>
                    <span>开启后，有新的手动发货订单时会播放提示音</span>
                </div>
               
            </el-form-item> 
            <el-form-item >
                <div class="auto-set-box">
                    <el-switch size="small"
                        v-model="basicForm.replenishment_sound"
                        @change="changeRestock">
                    </el-switch>
                    <span>补货提醒提示音</span>
                    <span>开启后，有新的补货提醒时会播放提示音</span>
                </div>
            </el-form-item>
            
            <el-form-item>
              <el-button class="f14" type="primary" @click="saveBasicSettings" :disabled="loading">保存设置</el-button>
              <el-button class="f14" @click="resetBasicForm" :disabled="loading">重置</el-button>
            </el-form-item>
          </el-form>
    </div>
</template>
<script>
export default {
    name:"BaseSetting",
    data(){
        return{
            loading:false,
            basicForm:{
                system_name:'',
                system_logo:'',
                copyright_info:'',
                manual_shipment_sound:false,//手动发货提示音
                replenishment_sound:false,//补货提醒
            },
            oldBasicForm:{
                system_name:'',
                system_logo:'',
                copyright_info:'',
            },
            basicRules:{
                system_name: [
                    { required: true, message: '请输入系统名称', trigger: 'blur' },
                    { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
                ]
            }
        }
        
    },
    created(){
        this.getSystemInfo()
    },
    methods:{
        getSystemInfo(){
            this.loading = true
            this.$api.systemInfo().then(res => {
                this.loading = false
                if(res.code == 1){
                    res.data.manual_shipment_sound = res.data.manual_shipment_sound == 1 ? true : false
                    res.data.replenishment_sound = res.data.replenishment_sound == 1 ? true : false
                    // res.data.system_logo = this.$baseURL + res.data.system_logo
                    this.oldBasicForm = {...res.data}                    
                    this.basicForm = {...this.oldBasicForm}
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('获取系统信息失败')
            })
        },
        handleLogoChange(file){
            let isImage = file.raw.type === 'image/jpeg' || file.raw.type === 'image/png'
            let isLt2M = file.raw.size / 1024 / 1024 < 2            
            if (!isImage) {
                this.$message({
                    message: '上传Logo只能是JPG或PNG格式!',
                    type: 'error'
                })
                return
            }
            if (!isLt2M) {
                this.$message({
                    message: '上传Logo大小不能超过2MB!',
                    type: 'error'
                })
                return
            }
            this.loading = true
            const formData = new FormData()
            formData.append('logo', file.raw)
            this.$axios.post(this.$baseURL + '/api/admin/system/logo/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Authorization': 'Bearer ' + this.$local.get('token')
                }
            }).then(res => {
                this.loading = false
                if(res.data.code === 1) {
                    this.basicForm.system_logo = res.data.data.path
                    this.$message.success(res.data.data.msg)
                } else {
                    this.$message.error(res.data.data.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('上传失败')
            })
        },
        saveBasicSettings(){
            this.$refs.basicFormRef.validate((valid) => {
                if (valid) {
                    this.loading = true
                    const data = {
                        system_name: this.basicForm.system_name,
                        copyright_info: this.basicForm.copyright_info,
                        system_logo: this.basicForm.system_logo,
                        manual_shipment_sound:this.basicForm.manual_shipment_sound,
                        replenishment_sound: this.basicForm.replenishment_sound
                    }
                    this.$api.systemUpdate(data).then(res => {
                        this.loading = false
                        if(res.code === 1) {
                            this.$message.success(res.msg)
                            this.oldBasicForm = {...this.basicForm}
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
        resetBasicForm(){
            this.basicForm = {...this.oldBasicForm}
            this.$refs.basicFormRef.resetFields();
        },
        changeManual(){
            // 手动发货声音提示打开开关
        },
        changeRestock(){
            // 补货声音提示打开开关
        },
    }
}
</script>
<style lang="scss" scoped>
.base-setting{

    .upload-box{
        display: flex;
        justify-content: flex-start;
        align-items: center;
        .avatar-uploader {
            width: 200px;
            height: 60px;
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s;
            &:hover {
                border-color: #409EFF;
            }
            .avatar-uploader-icon {
                font-size: 28px;
                color: #8c939d;
                width: 200px;
                height: 60px;
                line-height: 60px;
                text-align: center;
                }

            .avatar {
                width: 200px;
                height: 60px;
                display: block;
            }
        }
        
        .upload-tip {
            font-size: 12px;
            color: #909399;
            margin-top: 5px;
        }
    }
    .auto-set-box{
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
}




</style>