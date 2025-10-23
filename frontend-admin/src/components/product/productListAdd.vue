<template>
    <div class="dialog-box">
        <el-dialog
            :title="dialogType=='add'?'新增商品' :'编辑商品'"
            :visible.sync="dialogVisible"
            width="750px"
            custom-class="add-dialog-class"
            :before-close="closeDialog"
            :close-on-click-modal="false"
            :top="dialog.top"
            >
            <span class="add-dialog-con">
                <el-form ref="form" :model="formData" :rules="rules" label-width="100px" size="small">
                    <el-form-item label="商品名称" prop="name">
                        <el-input v-model="formData.name" placeholder="请输入商品名称"></el-input>
                    </el-form-item>
                    <el-form-item label="商品分类" prop="category_id">
                        <el-select v-model="formData.category_id" placeholder="请选择商品分类">
                            <el-option v-for="item in categoryOptions" :key = "item.id" :label ="item.name" :value="item.id"></el-option>
                            <!-- <el-option label="google" value="谷歌邮箱"></el-option> -->
                        </el-select>
                    </el-form-item>
                    <el-form-item label="商品图片" prop="image">
                        <div class="upload-image">
                            <el-upload
                                class="avatar-uploader"
                                action="#"
                                :show-file-list="false"
                                :auto-upload="false"
                                :on-change="handleChange"
                                >
                                <el-image fit='cover' v-if="formData.image" :src="formData.image" class="avatar">
                                </el-image>
                                <div v-else class="avatar">
                                    <div>
                                        <i class="f22 el-icon-plus avatar-uploader-icon"></i>
                                        <div>点击上传图片</div>
                                    </div>
                                    
                                </div>
                            </el-upload>
                            <div class="form-tip">请上传商品缩略图，建议尺寸400×400像素，最大不超过2MB</div>
                        </div>
                    </el-form-item >
                    <el-form-item label="商品价格" prop= "price">
                        <el-input-number class="form-input" v-model="formData.price" :precision="2" :step="0.01" :min="0" ></el-input-number>
                    </el-form-item>
                    <!-- <el-form-item label="成本价" prop= "cost_price">
                        <el-input-number class="form-input" v-model="formData.cost_price" :precision="2" :step="0.01" :min="0" ></el-input-number>
                        <div class="form-tip">商品的成本价格，用于计算利润，不可高于当前价格</div>
                    </el-form-item>                         -->
                    <el-form-item label="原价" prop= "original_price">
                        <el-input-number class="form-input" v-model="formData.original_price" :precision="2" :step="0.01" :min="0"></el-input-number>
                        <div class="form-tip">商品的成本价格，用于计算利润，不可高于当前价格</div>
                    </el-form-item>
                                        
                    <el-form-item label="发货方式" prop= "delivery_method">
                           <el-radio-group v-model="formData.delivery_method">
                                <el-radio v-for="item in queryOptions.delivery_methods" :key="item.value" :label="item.value">{{item.label}}</el-radio>
                                
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="状态" prop= "status">
                        <el-switch
                            v-model="formData.status"
                            :active-value="'1'"
                            :inactive-value="'0'"
                            active-text="上架"
                            inactive-text="下架">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="商品详情" prop= "use_template">
                        <el-tabs type="border-card" v-model="form.use_template">
                            <el-tab-pane label="使用模板" name="userModel">
                                <div class="model-head">
                                    <div class="model-head-top">
                                        <el-select class="model-select" v-model="formData.description_template_id" @change="changeModel" clearable placeholder="请选择模板" size="small">                                            
                                            <el-option v-for="item in templateData.templateOption" :key="item.value" :label="item.label" :value="item.value">
                                                <span style="float: left">{{item.label}}</span>
                                                <span style="float: right;"><el-tag type="info" size="small">{{item.category}}</el-tag></span>
                                            </el-option>
                                        </el-select> 
                                        <el-button type="primary" plain icon="el-icon-setting" circle  class="f14"></el-button>  
                                    </div>
                                    <div class="model-tip">
                                        <el-tag :type="formData.description_template_id?'success':'warning'" effect="dark" size="small">
                                            <i :class="formData.description_template_id? 'el-icon-check' : 'el-icon-warning-outline'"></i> {{(formData.description_template_id && formData.description_template_id !=0) ? 
                                            `已绑定模板：${templateData.hasTemplate ? templateData.templateMap[formData.description_template_id].template_name :''}`:'未选择模板'}}
                                        </el-tag>                                 
                                        <span class="f13">{{formData.description_template_id?'模板内容将随模板更新而自动更新':'请从下拉菜单中选择一个模板'}}</span>
                                    </div>
                                </div>
                                <div class="model-con">
                                    <QuillEditor ref="modelEditor" :content="modelContent" :quillEnable="false"></QuillEditor>
                                    <div class="disable-quill-box">
                                        <i class="f32 gray-icon-color el-icon-lock"></i>
                                        <div class="form-tip">模板内容由系统管理，如需自定义请切换到"自定义内容"模式</div>
                                    </div>
                                </div>
                                
                            </el-tab-pane>
                            <el-tab-pane label="自定义内容" name="customer">
                                <div class="customer-head">
                                    <div class="customer-head-top">
                                        <el-button-group>
                                            <el-button class="f14" type="primary" icon="el-icon-document" @click='setModel(1)'>插入默认模板</el-button>
                                            <el-dropdown  trigger="click" @command="chooseModel">
                                                <el-button type="primary"  class="f14">
                                                    <i class="el-icon-s-order"></i>选择模板<i class="el-icon-arrow-down el-icon--right"></i>
                                                </el-button>
                                                <el-dropdown-menu slot="dropdown">
                                                    <el-dropdown-item  v-for="item in templateData.templateOption" :key="item.value" :command="item.value" > 
                                                        <div class="product-ist-custom-dropdown-item"> 
                                                            <span class="" style="float: left">{{item.label}}</span>
                                                            <span class="ml15" style="float: right;"><el-tag type="info" size="small">{{item.category}}</el-tag></span>
                                                        </div>                                                          
                                                    </el-dropdown-item>                                                    
                                                </el-dropdown-menu>
                                            </el-dropdown>                                                                                                      
                                        </el-button-group>
                                        <div class="preview">                                     
                                            <el-button class="f14" type="success" icon='el-icon-view' plain  @click = "saveContent">预览效果</el-button>
                                            <el-button type="info" plain icon="el-icon-setting" circle class="f14"></el-button>  
                                        </div> 
                                        
                                    </div>
                                    <div class="model-tip">
                                        <el-tag type="info" effect="plain" size="small">
                                            <i class="el-icon-info"></i> 自定以模板
                                        </el-tag>                                 
                                        <span class="f13">您可以自由编辑内容，但不会随模板更新而自动更新</span>
                                    </div>
                                </div>
                                <QuillEditor ref="customerEditor" :quillEnable="true"  :content="customerContent"></QuillEditor>
                                
                            </el-tab-pane>
                        </el-tabs>
                        
                    </el-form-item>
                    
                    <el-form-item v-if="formData.delivery_method == 'manual'" label="库存" prop= "stock">
                        <el-input-number class="form-input" v-model="formData.stock" :precision="0" :step="1" :min="0" ></el-input-number>                        
                    </el-form-item>
                    <el-form-item label="库存预警值" prop= "stock_warning">
                        <el-input-number class="form-input" v-model="formData.stock_warning" :precision="0" :step="1" :min="0" ></el-input-number>
                        <div class="form-tip">当库存低于此值时将发出预警提醒</div>
                    </el-form-item>
                    
                    <el-form-item label="单笔购买限制" prop="enable_purchase_limit">
                        <div class="flex flex-wrap align-center">
                            <div class="limit-box ">
                                <el-switch
                                    v-model="formData.enable_purchase_limit"
                                    :active-value="'1'"
                                    :inactive-value="'0'"
                                    active-text="启用"
                                    inactive-text="不限制">
                                </el-switch>
                                <div class="limit-con" v-show="formData.enable_purchase_limit == 1">
                                    <el-radio-group v-model="formData.purchase_limit_type" class="mr20">
                                        <el-radio label="2">库存百分比</el-radio>
                                        <el-radio label="1">固定数量</el-radio>
                                    </el-radio-group>
                                    <el-input-number class="form-input limit-input" v-model="formData.purchase_limit_value" :precision="0" :step="1" :min="1" size="small"></el-input-number>
                                    <span>{{ formData.purchase_limit_type == 1 ?'件':'%' }}</span>
                                </div>
                            </div>
                            
                            <span class="form-tip">设置用户单笔订单可购买的最大数量，保障库存合理分配</span>
                        </div>   

                    </el-form-item>
                    <!-- <el-form-item label="排序" prop="sort">
                        <el-input-number class="form-input" v-model="formData.sort" :precision="0" :step="1" :min="0" size="small"></el-input-number>
                    </el-form-item> -->
                    <el-form-item label="备注" prop= "note">
                        <el-input type="textarea" class="form-input" v-model="formData.remark" placeholder="请输入备注" :rows="3" ></el-input>
                    </el-form-item>
                    
                </el-form>
            </span>
            <span slot="footer" class="dialog-footer">
                <el-button class="f14" @click="closeDialog" size="small" :disabled="loading">取 消</el-button>
                <el-button class="f14" @click="addSubmit" type="primary" size="small" :disabled="loading">确定</el-button>
            </span>
        </el-dialog>
        <el-dialog title="内容预览"
            :visible.sync="dialog.contentPreviewVisible"
            width="70%"
            custom-class="content-preview-visible"
            @close="closePreview"
            :close-on-click-modal="true"
            :top="dialog.top">
            <previewContainer :htmlStr="dialog.customerEditorContent"></previewContainer>
            <!-- <div class="preview-container" v-html="dialog.customerEditorContent"></div> -->
        </el-dialog>
    </div>
</template>
<script>
import QuillEditor from '@/components/quillEditor.vue'
import previewContainer from '@/components/previewContainer.vue'
    export default{
        name:'productListAdd',
        props:{
            dialogVisible:{
                type:Boolean,
                default:false
            },
            dialogType:{
                type:String,
                default:'add',//edit 编辑  add 新增
            },
            formData:{
                type:Object,
                required:true,
            },
            categoryOptions:{
                type:Array,
                required:true,
            },
            queryOptions:{
                type:Object,
                required:true,
            }
        },
         components:{
            QuillEditor,
            previewContainer
        },

        data(){
            var checkPrice = (rule, value, callback) => {
                if (value <0) {
                    return callback(new Error('商品价格不能小于0'));
                }else {
                    callback();
                }
            };
            var checkStock = (rule, value, callback) => {
                if(this.formData.delivery_method == 'manual'){
                    if(!value){
                        return callback(new Error('请输入商品库存'));
                    }else if (value <0) {
                        return callback(new Error('商品库存不能小于0'));
                    }else {
                        callback();
                    }
                }else{
                    callback();
                }
                
            };
            
            return{
                loading:false,
                dialog:{
                    top:'15vh',
                    customerEditorContent:'' ,
                    contentPreviewVisible:false
                },
                form:{
                    use_template:'customer',  
                },
                rules:{                
                    name:[
                        { required: true, message: '请输入商品名称', trigger: 'blur' }
                        ,{ min: 2, max: 100, message: '长度在 2 到 100 个字符', trigger: 'blur' }
                    ],
                    category_id:[{ required: true, message: '请选择商品分类', trigger: 'change' }],
                    price:[
                        { required: true, message: '请设置商品价格', trigger: 'blur' },
                        { validator: checkPrice, trigger: 'blur' }
                    ],                    
                    stock_warning:[{ required: true, message: '请设置库存预警', trigger: 'blur' }],
                    delivery_method:[{ required: true, message: '请选择发货方式', trigger: 'change' }],
                    use_template:[{ required: true, message: '请填写商品详情', trigger: 'change' }],
                    image:[{ required: true, message: '请选择商品图片', trigger: 'change' }],
                    stock:[
                        { required: true, message: '请输入库存', trigger: 'blur' },
                        {validator:checkStock, trigger: 'blur' }
                    ],
                    enable_purchase_limit:[{required:false}]
                },
                templateData:{
                    templateOption:[],
                    list:[],
                    templateMap:{},
                    hasTemplate:false,
                },
                modelContent:'',
                customerContent:''

            }
        },
        mounted(){
            // console.log("jiazai组件",this.formData)
            this.templateList()
            if(this.formData.use_template == "0"){
                //未使用模板
                this.form.use_template ='customer'
                this.customerContent = this.formData.description
            }else{
                //使用模板
                this.form.use_template ='userModel'
                if(this.dialogType == 'add'){
                    this.modelContent = ''
                }else{
                    this.modelContent = this.formData.description_template.content
                }
                
            }
        },
        methods:{
            //获取模板列表
            templateList(){
                let params = {
                    page: 1,
                    limit: 10000
                }
                this.templateData.hasTemplate = false
                this.$api.templateList(params).then(res=>{
                    this.templateData.templateOption=[]
                    if(res.code == 1){
                        let list = [...res.data.list]
                        this.templateData.list = list;
                        list.forEach(item=>{
                            this.templateData.templateOption.push({
                                label:item.template_name,
                                value:item.id,
                                category:item.id == '1'? '通用商品模板':(item.category ? item.category.name :'')
                            })
                            this.templateData.templateMap[item.id] = item
                        })
                        if(this.formData.use_template == '1'){
                            if(this.dialogType == 'add'){
                                this.modelContent = ''
                            }else{
                                this.modelContent = this.templateData.templateMap[this.formData.description_template_id].content
                            }
                            
                        }else{
                            this.modelContent = ''
                        }
                       
                        this.templateData.hasTemplate = true
                    }else{
                        this.templateData.hasTemplate = false
                    }
                }).catch((e)=>{
                    this.templateData.hasTemplate = false
                })
            },
            closeDialog(){
                this.$emit('closeDialog')
              },
            //确认提交新增商品
            async addSubmit(){                        
                let data={};         
                this.$refs.form.validate((valid) => {
                    if(valid){        
                        // 使用 URL 对象解析 URL
                            const url = new URL(this.formData.image);
                            // 获取域名
                            const domain = url.hostname; // 输出: example.com
                            // 获取路由路径
                            const route = url.pathname; // 输出: /path/to/resource
                            // console.log("域名:", domain);
                            // console.log("路由:", route);
                        //let urlArr = this.formData.image.split('.com')
                        // 自定义类容及 使用模板通用参数
                        data.name = this.formData.name;                
                        data.category_id = this.formData.category_id;
                        data.price = this.formData.price;
                        // data.cost_price = this.formData.cost_price;
                        data.original_price = this.formData.original_price;
                        data.stock_warning = this.formData.stock_warning;
                        data.delivery_method =  this.formData.delivery_method;
                        data.status = this.formData.status;
                        data.remark = this.formData.remark;
                        // data.sort = this.formData.sort;
                        data.image = route;
                        data.enable_purchase_limit = this.formData.enable_purchase_limit;
                        data.purchase_limit_type = this.formData.purchase_limit_type;
                        data.purchase_limit_value = this.formData.purchase_limit_value;
                        if(this.formData.delivery_method == 'manual'){
                            data.stock = this.formData.stock;
                        }
                        //编辑所需参数
                        if(this.dialogType == "edit"){
                            data.id = this.formData.id;         
                        }
                        //自定义类容 参数
                        if(this.form.use_template =='customer'){
                            this.customerContent = this.$refs.customerEditor.getContent()
                            data.use_template = '0'                    
                            data.description = this.customerContent;                     
                        }else if(this.form.use_template =='userModel'){
                            //使用模板 参数
                            
                            if(!this.formData.description_template_id || this.formData.description_template_id == ""){
                                this.$message.error('请选择商品详情模板')
                                return
                            }
                            data.use_template = '1'
                            data.description_template_id = this.formData.description_template_id;
                        }
                        this.$emit("submitData",data)
                    }else{
                        return false;
                    }
                });
            },
            //选中图片
            async handleChange(file,fileList){
                const isJPG = file.raw.type.startsWith('image/');
                const isLt2M = file.raw.size / 1024 / 1024 <= 2;
                if (!isJPG) {
                    this.$message.error('只能上传图片');
                    return
                }
                if (!isLt2M) {
                    this.$message.error('上传图片大小不能超过 2MB!');
                    return
                }
                const formData = new FormData()
                formData.append('image', file.raw)
                this.loading = true
                this.$axios.post(this.$baseURL + '/admin/product/uploadImage', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Authorization': 'Bearer ' + this.$local.get('token')
                    }
                }).then(res => {
                    this.loading = false
                    // console.log(res,'res')
                    if(res.data.code === 1) {
                        this.formData.image = this.$baseURL + res.data.data.url
                        // console.log(this.formData.image,'this.formData.image')
                        this.$message.success(res.data.msg)

                        this.$refs.form.validateField('image', (errorMessage) => {});



                    } else {
                        this.$message.error(res.data.msg)
                    }
                }).catch(err => {
                    this.loading = false
                    this.$message.error('上传失败')
                })

               
            },          
            //关闭内容预览
            closePreview(){
                this.dialog.contentPreviewVisible = false
                // this.$refs.contentContainer.innerHTML = '';
            },
            //内容预览
            saveContent() {
                this.dialog.contentPreviewVisible = true
                this.$nextTick(()=>{
                    this.dialog.customerEditorContent = this.$refs.customerEditor.getContent();
                    // this.$refs.contentContainer.innerHTML = this.dialog.customerEditorContent;
                })
            
            },
            changeModel(val){
                this.modelContent = this.templateData.templateMap[val].content;
                
            },
            //自定义类容 插入模板
            setModel(id){
                this.customerContent = this.templateData.templateMap[id].content
            },
            //自定义类容  选择模板
            chooseModel(val){
                this.setModel(val)
            },
          
        }
    }
</script>
<style lang="scss">
    .order-view{
        .el-tabs--border-card>.el-tabs__content{
            padding:0 !important;
        }
    }
    .add-dialog-con{
        .el-button-group .el-dropdown {
            display: flex;
            align-items: center;
        }
    }
    
</style>
<style lang="scss" scoped>
    .product-ist-custom-dropdown-item{
        display: flex;
        min-width: 207px;
        justify-content: space-between;
        align-items: center;
        box-sizing: border-box;
    }
    .dialog-box{
        .add-dialog-class{
            .add-dialog-con{
                .model-tip{                        
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 8px 12px;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                    border-left: 3px solid #409EFF; 
                }
                .upload-image{                    
                    text-align: center;
                    width: 100%;
                    .avatar-uploader{             
                        .avatar{
                            width:180px;
                            height:180px;
                            color:#8c939d;                            
                            display: flex;
                            justify-content: center;
                            align-items: center;
                        }
                    }
                }
                .form-input{
                    width: 100%;
                }
                
                .form-tip{
                    text-align: left;
                    font-size: 12px;
                    color: #909399;
                }
                .model-head{
                    padding: 15px;
                    .model-head-top{
                        display: flex;
                        justify-content: flex-start;
                        align-items: center;                        
                        gap: 10px;
                        margin-bottom: 15px;
                        .model-select{  
                            width: 300px;
                        }
                    }
                    
                    
                }
                .model-con{
                    position: relative;
                    .disable-quill-box{
                        width:100%;
                        height: 100%;
                        box-sizing: border-box;
                        position: absolute;
                        top: 0;
                        left: 0;
                        background-color: #f5f7fab3;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        flex-direction: column;
                        padding: 20px;
                        border-radius: 4px;
                        pointer-events: none;
                        
                    }
                }
                .customer-head{
                    padding: 15px;
                    .custom-dropdown-item{
                        &:hover {
                            background-color: #ecf5ff !important; /* 设置选中状态的背景色 */
                        }
                    }
                    .customer-head-top{
                        display: flex;
                        justify-content: space-between;
                        align-items:center;
                        margin-bottom: 15px;
                    }
                }
                .limit-box{
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                    .limit-con{
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 15px;
                        margin-top: 10px;
                        width: 100%;
                        .limit-input{
                            width: 140px;
                        }
                    }

                }
                
            }
        }


    }
</style>