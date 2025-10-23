<template>
    <div class="document-box">
       <mainBoxHeader titleName="系统文档设置" :description="documentData.description" descriptionBgColor='#ecf8ff' descriptionBorderColor='#50bfff' descriptionBorderWidth='5px' >
           <template slot="oprBtn">
               <el-button class="f14" type="primary" @click="saveAll" size="small" :disabled="loading">保存所有更改</el-button>
           </template>
           <template slot="pageCon">
                <template>
                    <el-tabs v-model="documentData.activeName" @tab-click="tabClick">
                        <el-tab-pane label="使用协议" name="use">
                            <documentQuill editorType="use" ref="useDocument" :updateDate="documentData.useUpdateDate" @saveDocument = "previewDocument" :status="documentData.useStatus" :editorContent = "documentData.useContent"  :loading="loading"></documentQuill>
                        </el-tab-pane>
                        <el-tab-pane label="关于我们" name="about">                            
                            <documentQuill editorType="about" ref="aboutDocument" :updateDate="documentData.aboutUpdateDate" @saveDocument = "previewDocument" :status="documentData.aboutStatus" :editorContent = "documentData.aboutContent" :loading="loading"></documentQuill>
                        </el-tab-pane>
                    </el-tabs>
                </template>
                <div class="preview-box">
                    <div class="preview-title">
                        <span>预览效果</span>
                        <el-switch  v-model="documentData.showPreview" active-text="显示预览" inactive-text="隐藏预览" size="small"></el-switch>
                    </div>
                    <div class="preview-content" v-show="documentData.showPreview" v-html="documentData.previewContent"></div>
                </div>
           </template>
       </mainBoxHeader>
   </div>
</template>
<script>
import mainBoxHeader from '@/components/mainBoxHeader.vue'
import documentQuill from '@/components/system/documentQuill.vue'
export default {
   name:'templateSetting',
   components:{
       mainBoxHeader,
       documentQuill
   },
   data(){
        return{
            loading: false,
            documentData:{
                description:'管理网站前台显示的基础文档内容，包括使用协议和关于我们等重要信息。这些内容将在前台网站的相应页面中显示。',
                activeName:'use',//use  使用协议 about 关于我们，
                useUpdateDate:"",
                aboutUpdateDate:"",
                showPreview:true,//是否显示预览
                previewContent:'',//预览内容
                useContent:'',//使用协议内容
                aboutContent:'',//关于我们内容
                useStatus: '', // 使用协议状态
                aboutStatus: '', // 关于我们状态
            },
        }
   },
   created(){
        this.getContentInfo('use')
        this.getContentInfo('about')
   },
   methods:{
        //获取文档内容
        getContentInfo(type){
            this.loading = true
            const params = {
                type: type === 'use' ? '使用协议' : '关于我们'
            }
            this.$api.contentInfo(params).then(res => {
                this.loading = false
                if(res.code === 1){
                    if(type === 'use'){
                        this.documentData.useContent = res.data.content
                        this.documentData.useUpdateDate = res.data.created_at
                        this.documentData.useStatus = res.data.status
                        if(this.documentData.activeName === 'use'){
                            this.documentData.previewContent = res.data.content
                        }
                    }else{
                        this.documentData.aboutContent = res.data.content
                        this.documentData.aboutUpdateDate = res.data.created_at
                        this.documentData.aboutStatus = res.data.status
                        if(this.documentData.activeName === 'about'){
                            this.documentData.previewContent = res.data.content
                        }
                    }
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('获取文档内容失败')
            })
        },
        //保存所有更改按钮点击事件
        saveAll(){
                this.$refs.useDocument.saveDocument()   
                this.$refs.aboutDocument.saveDocument() 
        },
        //标签页切换点击事件
        tabClick(){
            if(this.documentData.activeName == 'use'){
                this.documentData.previewContent = this.documentData.useContent
            }else if(this.documentData.activeName == 'about'){ 
                this.documentData.previewContent = this.documentData.aboutContent
            }
        },
        previewDocument(content,status,type){
            this.loading = true
            const data = {
                type: type === 'use' ? '使用协议' : '关于我们',
                title:  type === 'use' ? '用户使用协议' : '关于我们',
                content: content,
                status: status
            }
            this.$api.contentUpdate(data).then(res => {
                this.loading = false
                if(res.code === 1) {
                    this.$message.success(res.msg)
                    if(type == 'use'){
                        this.documentData.useContent = content
                        this.documentData.useUpdateDate = new Date().toLocaleString()
                        this.getContentInfo('use')
                    }else if(type == 'about'){
                        this.documentData.aboutContent = content
                        this.documentData.aboutUpdateDate = new Date().toLocaleString()
                        this.getContentInfo('about')
                    }
                    if(this.documentData.activeName === 'use'){                         
                        this.documentData.previewContent =  this.documentData.useContent 
                    }else if(this.documentData.activeName === 'about'){
                        this.documentData.previewContent =  this.documentData.aboutContent 
                    }
                } else {
                    this.$message.error(res.msg)
                }
            }).catch(err => {
                this.loading = false
                this.$message.error('保存失败')
            })
        },
   }
}
</script>
<style lang="scss">

</style>
<style lang="scss" scoped>
   .preview-box{
        margin-top: 20px;
        border: 1px solid #e4e7ed;
        border-radius: 4px;
        padding: 20px;
        .preview-title{
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e4e7ed;
        }
        .preview-content{
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 4px;
        }
   }
    
</style>