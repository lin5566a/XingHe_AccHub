<template>
    <div class="bug-item">
        <div class="bug-name">
            <span>安装包 #{{ index + 1 }}</span>
            <span class="delete-bug" @click="deleteBug" v-if="deleteAble">
                <i class="el-icon-delete"></i>
            </span>
        </div>
        <div class="bug-content-box">
            <div class="bug-icon">
                <div>
                    <el-upload
                        class="avatar-uploader"
                        name="icon"
                        :action="$baseURL + '/admin/product/uploadPackageIcon'"
                        :headers="uploadHeaders"
                        :show-file-list="false"
                        :on-success="handleIconSuccess"
                        :on-error="handleIconError"
                        :before-upload="beforeIconUpload">
                        <img v-if="iconUrl" :src="iconUrl" class="avatar">
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                </div>
                <div class="upload-tip">建议尺寸64×64像素</div>
            </div>
            <div class="bug-info">
                <el-form label-position="right" label-width="100px" :model="formData" size="small">
                    <el-form-item label="在前台显示">
                        <el-switch size="small"
                            v-model="formData.isShow"
                            active-text="显示"
                            inactive-text="隐藏"
                            @change="handleFormChange">
                        </el-switch>
                    </el-form-item>  
                    <el-form-item label="地域限制">
                        <el-switch size="small"
                            v-model="formData.region_limit"
                            active-text="开启"
                            inactive-text="关闭"
                            @change="handleFormChange">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="不允许城市" v-if="formData.region_limit">
                        <el-cascader 
                            class="form-cascader"
                            v-model="formData.limit_city"
                            :options="options.city_options"
                            :props="props"
                            clearable>
                        </el-cascader>
                        <div class="form-tip">选择的省份或城市的用户将无法看到此安装包</div>
                    </el-form-item>
                    <el-form-item label="名称">
                        <el-input  size="small"
                            v-model="formData.name" 
                            placeholder="请输入安装包名称"
                            @change="handleFormChange">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="类型">
                        <el-radio-group  size="small" v-model="formData.type" @change="handleFormChange">
                            <el-radio :label="1">文件上传</el-radio>
                            <el-radio :label="2">链接地址</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="文件上传" v-if="formData.type === 1">
                        <el-upload v-if="!formData.file_path || formData.file_path == ''" size="small"
                            class="upload-demo"
                            name="file"
                            :action="$uploadURL + '/admin/product/uploadPackage'"
                            :headers="uploadHeaders"
                            :on-success="handleFileSuccess"
                            :before-upload="handleFileBeforeUpload"
                            :on-error="handleFileError"
                            :on-remove="handleFileRemove"
                            :on-progress="handleFileProgress"
                            :file-list="fileList">
                            <div class="upload-btn" v-if="fileList.length == 0 && !uploading">
                                <el-button type="primary" size="small">点击上传</el-button>
                                <span slot="tip" class="el-upload__tip">支持.apk, .ipa, .exe等格式，不超过500MB</span>
                            </div>
                            <div v-if="uploading">
                                <el-progress :percentage="percentage" :text-inside="true" :stroke-width="15" status="success" text-color="#fff"></el-progress>
                            </div>
                        </el-upload>
                        <span v-else class="file-name">
                            <i class="el-icon-document mr18"></i>
                            <span class='file-name-val'>{{ getFileName(formData.file_path) }}</span>
                            <i class='el-icon-delete' @click="deleteFile()"></i>
                        </span>
                    </el-form-item>
                    <el-form-item label="下载链接" v-if="formData.type === 2">
                        <el-input  size="small"
                            v-model="formData.download_url" 
                            placeholder="请输入下载链接"
                            @change="handleFormChange">
                        </el-input>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <div class="flex flex-end"> <el-button size="small" type="primary" @click="sureAddPackage" :disabled="loading || uploading" >保存</el-button></div>
    </div>
</template>
<script>
    import localData from '@/utils/localData.js'
    export default {
        data() {
            return {
                loading:false,
                fileList: [],
                iconUrl: '',
                uploading:false,
                percentage:0,
                formData: {
                    id: '',
                    product_id: '',
                    name: '',
                    type: 1,
                    isShow: false,
                    file_path: '',
                    download_url: '',
                    icon: '',
                    region_limit: false,//地域限制
                    limit_city:[],
                },
                uploadHeaders: {
                    Authorization: 'Bearer ' + this.$local.get('token')
                },
                props: { multiple: true },
                options:{
                    city_options: []
                }
            }
        },
        props: {
            bugData: {
                type: Object,
                default: () => ({})
            },
            index: {
                type: Number,
                default: 0
            },
            deleteAble:{
                type: Boolean,
                default: true
            }
        },
        watch: {
            bugData: {
                handler(val) {
                    this.initFormData(val)
                },
                immediate: true
            }
        },
        mounted() {
            // console.log(localData.city,'====city=====')
            this.getCity()
        },
        methods: {
            getCity(){
                this.options.city_options=[]
                for( let i = 0; i < localData.city.length; i++){
                    let item = localData.city[i];
                    let children = [];
                    for( let j = 0; j < item.children.length; j++){                        
                        children.push({
                            value: item.children[j].label,
                            label: item.children[j].label
                        })
                    }
                    this.options.city_options.push({
                        value: item.label,
                        label: item.label,
                        children: children

                    })
                }
                // console.log(this.options.city_options,'====city_options=====')
            },
            initFormData(data) {
                let limit_city = []
                if(data && data.disallowed_cities && data.disallowed_cities.length > 0){
                    for(let i = 0; i < data.disallowed_cities.length; i++){
                        let city = data.disallowed_cities[i].split('/')
                        limit_city.push(city)
                    }
                }
                console.log(data.enable_regional_restriction,'====enable_regional_restriction=====')
                
                this.formData = {
                    id: data.id || '',
                    product_id: data.product_id || '',
                    name: data.name || '',
                    type: data.type || 1,
                    isShow: data.is_show === 1,
                    file_path: data.file_path || '',
                    download_url: data.download_url || '',
                    icon: data.icon || '',
                    region_limit:data.enable_regional_restriction == 1 ? true:false,
                    limit_city : limit_city
                }
                this.iconUrl = data.icon ? this.$baseURL + data.icon : ''
                if (data.file_path) {
                    this.fileList = [{
                        name: data.file_path.split('/').pop(),
                        url: this.$baseURL + data.file_path
                    }]
                }
            },
            // 表单变更
            handleFormChange() {
                // this.$emit('updateBug', this.formData)
            },
            // 删除包
            deleteBug() {

                this.$emit('deleteBug', this.bugData,this.index)
            },
            // 图标上传相关
            handleIconSuccess(res, file) {
                if (res.code === 1) {
                    this.iconUrl = this.$baseURL + res.data.url
                    this.formData.icon = res.data.url
                    // this.handleFormChange()
                } else {
                    this.$message.error(res.msg || '上传失败')
                }
            },
            handleIconError() {
                this.$message.error('上传失败')
            },
            beforeIconUpload(file) {
                const isImage = file.type.startsWith('image/')
                const isLt2M = file.size / 1024 / 1024 < 2

                if (!isImage) {
                    this.$message.error('只能上传图片文件!')
                }
                if (!isLt2M) {
                    this.$message.error('图片大小不能超过 2MB!')
                }
                return isImage && isLt2M
            },
            
            // 文件上传相关
            //文件上传前
            handleFileBeforeUpload(file) {
                // 检查文件大小（500MB = 500 * 1024 * 1024 bytes）
                const maxSize = 500 * 1024 * 1024
                if (file.size > maxSize) {
                    this.$message.error('文件大小不能超过500MB')
                    this.uploading = false
                    return false
                }
                
                // 检查文件格式
                const validExtensions = ['.apk', '.ipa', '.exe','.zip','.rar']
                const extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
                if (!validExtensions.includes(extension)) {
                    this.$message.error('只支持.apk、.ipa、.exe、.zip、.rar格式的文件')
                    this.uploading = false
                    return false
                }
                
                this.uploading = true
                return true
            },
            //文件上传成功
            handleFileSuccess(res, file) {
                this.uploading = false
                
                if (res.code === 1) {
                    this.formData.file_path = res.data.url
                    // this.handleFormChange()
                } else {
                    this.$message.error(res.msg || '上传失败')
                }
            },
            //文件上传进度
            handleFileProgress(event, file, fileList){
                this.uploading = true
                this.percentage = event.percent.toFixed(0)
                // console.log( this.percentage,'=======================')
            },
            handleFileError() {
                this.uploading = false
                this.$message.error('上传失败')
            },
            handleFileRemove(path) {
                this.fileList = []
                this.formData.file_path = ''
                // this.handleFormChange()
            },
            // 获取文件名
            getFileName(path) {
                if (!path) return ''
                return path.split('/').pop()
            },
            //删除文件
            deleteFile() {
                this.formData.file_path = '' 
                this.percentage = 0
                this.fileList = []
                // this.handleFormChange()
            },
            //保存包
            sureAddPackage(){
                // 验证名称
                if (!this.formData.name) {
                    this.$message.error('请输入安装包名称')
                    return
                }
                
                // 验证包图标
                if (!this.formData.icon) {
                    this.$message.error('请上传图标')
                    return
                }

                // 验证文件上传或下载链接
                if (this.formData.type === 1 && !this.formData.file_path) {
                    this.$message.error('请上传安装包文件')
                    return
                }

                if (this.formData.type === 2) {
                    if (!this.formData.download_url) {
                        this.$message.error('请输入下载链接')
                        return
                    }
                    // 验证https链接
                    if (!this.formData.download_url.startsWith('https://')) {
                        this.$message.error('下载链接必须以https://开头')
                        return
                    }
                }
                if(this.formData.region_limit){
                    if(!this.formData.limit_city || (this.formData.limit_city&&this.formData.limit_city.length<=0)){
                        this.$message.error('请选择限制城市')
                        return
                    }
                }
                
                if(this.formData.id){
                    this.editPackage()
                }else{
                    this.addPackage()
                }
            },
            addPackage(){     
                // console.log(this.formData.region_limit,this.formData.limit_city,'新增包 限制城市')     
                let limit_city = [];
                if(this.formData.region_limit){
                    limit_city = []
                    for(let i = 0; i < this.formData.limit_city.length; i++){
                        let city = this.formData.limit_city[i][0]+'/'+this.formData.limit_city[i][1]
                        limit_city.push(city)
                    }
                }
                const params = {
                    product_id: this.formData.product_id,
                    name: this.formData.name,
                    type: this.formData.type,
                    file_path: this.formData.type === 1 ? this.formData.file_path : '',
                    download_url: this.formData.type === 2 ? this.formData.download_url : '',
                    is_show: this.formData.isShow ? 1 : 0,
                    sort: this.index,
                    icon: this.formData.icon,
                    enable_regional_restriction: this.formData.region_limit?1:0,
                    disallowed_cities: limit_city,

                }
                this.loading = true
                this.$api.addPackage(params).then(res => {
                    if (res.code === 1) {
                        this.$message.success(res.msg)
                        this.$emit('updateBug')
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch(err => {
                    console.error('保存失败:', err)
                }).finally(() => {
                    this.loading = false
                })
            },
            editPackage(){                
                // console.log(this.formData.region_limit,this.formData.limit_city,'新增包 限制城市')   
                let limit_city = [];
                if(this.formData.region_limit){
                    limit_city = []
                    for(let i = 0; i < this.formData.limit_city.length; i++){
                        let city = this.formData.limit_city[i][0]+'/'+this.formData.limit_city[i][1]
                        limit_city.push(city)
                    }
                }
                const params = {
                    id: this.formData.id,
                    product_id: this.formData.product_id,
                    name: this.formData.name,
                    type: this.formData.type,
                    file_path: this.formData.type === 1 ? this.formData.file_path : '',
                    download_url: this.formData.type === 2 ? this.formData.download_url : '',
                    is_show: this.formData.isShow ? 1 : 0,
                    sort: this.index,
                    icon: this.formData.icon,
                    enable_regional_restriction: this.formData.region_limit?1:0,
                    disallowed_cities: limit_city,
                }
                this.loading = true
                this.$api.editPackage(params).then(res => {
                    if (res.code === 1) {
                        this.$message.success(res.msg)
                        this.$emit('updateBug')
                    } else {
                        this.$message.error(res.msg)
                    }
                }).catch(err => {
                    console.error('更新失败:', err)
                }).finally(() => {
                    this.loading = false
                })
            }
        }
    }
</script>
<style lang="scss">
.bug-info{
    .el-upload-list__item-name{
    display: none;
}
.el-upload-list__item .el-progress{
    display: none;
}
.progressbar{
    display: none;
}
.el-upload{
    width: 100%;
    text-align: left;
}
}

</style>
<style lang="scss" scoped>
    .bug-item {
        margin-bottom: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 3px #0000000d;


        .bug-name {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            font-weight: bold;
            font-size: 16px;
            color: #409eff;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            
            .delete-bug {
                background: #F56C6C;
                cursor: pointer;
                color: #fff;
                width: 24px;
                height: 24px;
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 50%;
                font-size: 12px;
                &:hover {
                    opacity: 0.8;
                }
            }
        }
        
        .bug-content-box {
            display: flex;
            gap: 20px;
            
            .bug-icon {
                .avatar-uploader {
                    .el-upload {
                        border: 1px dashed #d9d9d9;
                        border-radius: 6px;
                        cursor: pointer;
                        position: relative;
                        overflow: hidden;
                        
                        &:hover {
                            border-color: #409EFF;
                        }
                    }
                    
                    .avatar-uploader-icon {
                        font-size: 28px;
                        color: #8c939d;
                        width: 100px;
                        height: 100px;
                        line-height: 64px;
                        text-align: center;
                    }
                    
                    .avatar {
                        width: 100px;
                        height: 100px;
                        display: block;
                        object-fit: cover;
                    }
                }
                
                .upload-tip {
                    font-size: 12px;
                    color: #909399;
                    margin-top: 8px;
                    text-align: center;
                }
            }
            
            .bug-info {
                flex: 1;
                .el-upload__tip{
                    color:#909399;
                    font-size:12px
                }
                .form-cascader{
                    width:100%;
                }
                .form-tip{
                    margin-top: 5px;
                    font-size: 12px;
                    color: #909399;
                }
            }
        }
        .file-name {
            color: #67c23a;
            background-color: #f0f9eb;
            font-size: 14px;
            line-height: 32px;
            padding: 8px 12px;
            .file-name-val{
                color: #606266;
                font-size: 12px;
            }
            .el-icon-delete{
                cursor: pointer;
            }
        }
    }
</style>
