<template>
    <div class="order-view">
        <mainBoxHeader titleName="商品管理" :description="orderData.description">
            <div slot="oprBtn">
                <el-button class="f14" @click="bathDiscount" type="success" size="small">折扣价批量设置</el-button>  
                <el-button class="f14" @click="addProduct" type="primary" size="small">新增商品</el-button>             
            </div>
            <template slot="pageCon">
                <div class="order-query-box">
                    <div class="query-item">
                        <span class="query-label mr12">商品名称</span>
                        <el-input class="query-input" placeholder="请输入订单号" v-model="orderData.orderNumber" clearable size="small"></el-input>
                    </div>   
                  
                    <div class="query-item">
                        <span class="query-label mr12">商品分类</span>
                        <el-select class="query-select" v-model="orderData.categoryId" clearable placeholder="请选择" size="small">
                            
                            <el-option 
                                v-for="item in orderData.categoryOptions" 
                                :key="item.id" 
                                :label="item.name" 
                                :value="item.id">
                            </el-option>
                        </el-select>   
                    </div>
                    <div class="query-item">
                        <span class="query-label mr12">发货方式</span>
                        <el-select class="query-select" v-model="orderData.deliveryMethod" clearable placeholder="请选择" size="small">
                        
                            <el-option 
                                v-for="item in orderData.queryOptions.delivery_methods" 
                                :key="item.value" 
                                :label="item.label" 
                                :value="item.value">
                            </el-option>
                        </el-select>   
                    </div>    
                    <div class="query-item">
                        <span class="query-label mr12">状态</span>
                        <el-select class="query-select" v-model="orderData.status" clearable placeholder="请选择" size="small">
                            
                            <el-option 
                                v-for="item in orderData.queryOptions.status_options" 
                                :key="item.value" 
                                :label="item.label" 
                                :value="item.value">
                            </el-option>
                        </el-select>   
                    </div>  
                    <div class="query-item">
                        <span class="query-label mr12">库存预警</span>
                        <el-select class="query-select" v-model="orderData.stockWarning" clearable placeholder="请选择" size="small">
                           
                            <el-option 
                                v-for="item in orderData.queryOptions.stock_warning_options" 
                                :key="item.value" 
                                :label="item.label" 
                                :value="item.value">
                            </el-option>
                        </el-select>   
                    </div> 
                   
                    <div class="query-item">
                        <el-button @click="query" type="primary" size="small" class="f14">查询</el-button>
                        <el-button @click="reset" size="small" class="f14">重置</el-button>
                    </div>
                                     
                </div>
                <!-- 表格 -->
                <div class="data-table">
                    <el-table :data="table.dataTable" style="width: 100%" v-loading="loading" border stripe size="small">
                        <el-table-column prop="id" label="商品ID" width="80"></el-table-column>
                        <el-table-column prop="name" label="商品名称" min-width="180"></el-table-column>
                        <el-table-column prop="image" label="商品图片" width="120">
                            <template scope="scope">
                                <el-image class="product-img"
                                    v-if="scope.row.image"
                                    :src="scope.row.image"
                                    fit="cover"
                                    :preview-src-list="[scope.row.image]"
                                >
                                    <div slot="error" class="image-slot">
                                        <i class="error-img-icon el-icon-picture-outline"></i>
                                    </div>
                                </el-image>
                            </template>
                        </el-table-column>
                        <el-table-column prop="category.name" label="商品分类" width="100">
                            <template scope="scope">
                                <span>{{ scope.row.category?.name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="价格"  min-width="260">
                            <template scope="scope">
                                <div class="price-container">
                                    <div v-if=" scope.row.discount_enabled == '1' && scope.row.discount_status != '已过期'">折扣价：<span class="current-price f14 fb blue-color">¥{{ scope.row.discounted_price.toFixed(2) }} ({{ scope.row.usdt_discounted_price }}USDT)</span></div>
                                   <div>商品价：<span class="current-price f14 fb ">¥{{ scope.row.price }} ({{ scope.row.usdt_price }}USDT)</span></div>
                                   <div class="gray-text-color">原价：<span class=" original-price f12 " v-if="scope.row.original_price">¥{{ scope.row.original_price }}</span></div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="折扣" width="200" align="center">
                            <template scope="scope">
                                <div class="flex flex-column gap6" v-if="scope.row.discount_enabled == 1">
                                    <!-- 未启用，未开始，进行中，已过期   -->
                                    <div class="flex gap6 flex-center">
                                        <el-tag size="small" type="danger">{{scope.row.discount_percentage/10}}折</el-tag>
                                        <el-tag v-if="scope.row.discount_status == '进行中'" size="mini" type="success" >{{scope.row.discount_status}}</el-tag>
                                        <el-tag v-if="scope.row.discount_status == '未开始'" size="mini" type="primary" >{{scope.row.discount_status}}</el-tag>
                                        <el-tag v-if="scope.row.discount_status == '已过期'" size="mini" type="warning" >{{scope.row.discount_status}}</el-tag>
                                    </div>
                                    <div>
                                        <el-tag size="mini" class="f12" type="info">
                                            {{scope.row.discount_start_time.substring(5,7)}}/{{scope.row.discount_start_time.substring(8,10)}} {{scope.row.discount_start_time.substring(11,16)}} - 
                                            {{scope.row.discount_end_time.substring(5,7)}}/{{scope.row.discount_end_time.substring(8,10)}} {{scope.row.discount_end_time.substring(11,16)}}
                                        </el-tag>
                                    </div>
                                </div>
                                <div v-else>
                                    <el-tag size="small" type="info">{{scope.row.discount_status}}</el-tag>
                                </div>
                            </template>
                        </el-table-column>
                        <!-- <el-table-column label="成本价" width="100">
                            <template scope="scope">
                                    <span class="cost-price f14">¥{{ scope.row.cost_price }}</span>
                            </template>
                        </el-table-column> -->
                        <el-table-column prop="stock_count" label="库存" width="80">
                            <template scope="scope">
                                <span :class="{ 'low-stock': scope.row.stock_count < scope.row.stock_warning }">{{ scope.row.stock_count }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="stock_warning" label="库存预警值" width="100"></el-table-column>
                        <el-table-column prop="sold_count" label="销量" width="80"></el-table-column>
                        <el-table-column prop="sort" label="排序" width="80"></el-table-column>
                        <el-table-column prop="purchase_limit_value" label="单笔购买限制" width="130">
                            <template slot-scope="scope">
                                <el-tag size="small" v-if="scope.row.enable_purchase_limit == 1" type="success">{{scope.row.purchase_limit_value}}{{scope.row.purchase_limit_type == 1 ?'件':'%'}}</el-tag>
                                <span class="no-limit" v-else>不限制</span>
                            </template>
                        </el-table-column>
                        

                        <el-table-column prop="delivery_method" label="发货方式" width="100">
                            <template scope="scope">
                                <el-tag size="small" :type="scope.row.delivery_method === 'auto' ? 'success' : 'info'">
                                    {{ scope.row.delivery_method === 'auto' ? '自动发货' : '手动发货' }}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="状态" width="120">
                            <template scope="scope">
                                <el-switch
                                    v-model="scope.row.status"
                                    :active-value="'1'"
                                    :inactive-value="'0'"
                                    :disabled="loading"
                                    @change="(val) => handleStatusChange(val, scope.row)">
                                </el-switch>
                                <span class="status-textn ml8">{{ scope.row.status === '1' ? '上架中' : '已下架' }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="sort" label="排序" width="120">
                            <template scope="scope"> 
                                <div class=" sort-edit-box">
                                    <span class="f14 black-color">{{ scope.row.sort }}</span>
                                    <div class="edit-sort"><i @click="editSort(scope.row)" class="sort-icon el-icon-edit-outline"></i></div>
                                    
                                </div>                               
                                
                            </template>
                        </el-table-column>
                        <el-table-column prop="remark" label="备注" min-width="150"></el-table-column>
                        <el-table-column prop="created_at" label="创建时间" width="180"></el-table-column>
                        <el-table-column label="操作" width="120" fixed="right">
                            <template scope="scope">
                                <div class="action-buttons">
                                    <el-dropdown @command="handleCommand($event, scope.row)">
                                        <el-button size="mini" type="primary">
                                            操作<i class="el-icon-arrow-down el-icon--right"></i>
                                        </el-button>
                                        <el-dropdown-menu slot="dropdown">
                                            <el-dropdown-item command="editProduct">编辑</el-dropdown-item>
                                            <el-dropdown-item command="setDiscount">折扣设置</el-dropdown-item>
                                            <el-dropdown-item command="bugManage">包管理</el-dropdown-item>
                                            <el-dropdown-item command="stockOpen">库存管理</el-dropdown-item>
                                            <el-dropdown-item command="videoOpen">视频管理</el-dropdown-item>
                                            <el-dropdown-item command="deleteData">删除</el-dropdown-item>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>


                <div class="pagination-box">
                    <el-pagination background :total="table.total"
                        @size-change="handleSizeChange"
                        @current-change="handleCurrentChange"
                        :current-page="table.page"
                        :page-sizes="table.pageSizes"
                        :page-size="table.pageSize"
                        :disabled="loading"
                        layout="total, sizes, prev, pager, next, jumper">
                    </el-pagination>
                </div>
            </template>
        </mainBoxHeader>
        <!-- 新增商品 编辑商品组件 -->
        <div class="dialog-box">
            <!-- 编辑商品 -->
            <ProductListAdd  
                v-if="dialog.showProductEdit" 
                ref="productEdit"
                :queryOptions = orderData.queryOptions
                :categoryOptions="orderData.categoryOptions" 
                :formData = "formData" 
                :dialogVisible = "dialog.dialogVisible" 
                @closeDialog = "closeDialog" 
                @submitData = "submitEdit"
                dialogType="edit">
            </ProductListAdd>
           <!-- 新增商品 -->
            <ProductListAdd  
                v-if="dialog.showProductAdd" 
                ref="productAdd"
                :queryOptions = orderData.queryOptions
                :categoryOptions="orderData.categoryOptions" 
                :formData = "formDataAdd" 
                :dialogVisible = "dialog.dialogVisibleAdd"
                @closeDialog = "closeDialogAdd" 
                @submitData = "submitAdd"
                dialogType="add">
            </ProductListAdd>
            <!-- 包管理 -->
            <BugManage :dialogVisible="dialog.dialogVisibleBug" @handleClose="closeBug" :bugData="bugData"></BugManage>
            <!-- 排序 -->
            <SortEdit v-if="dialog.sortEditVisible" @closeDialog="closeSortDialog" @submitData="submitSort" :dialogVisible="dialog.sortEditVisible" :loading="dialog.sortLoading" :formData="dialog.sortForm"></SortEdit>
        
        </div>
        <!-- 库存管理组件 -->
        <div class="stock-box">
            <StockManage v-if="drawer.stockShow" :drawerVisible="drawer.drawerVisible" @closeDrawer = "closeDrawer"  @updateStock="getProductList" :product_id = 'drawer.product_id'></StockManage>
        </div>
        <!-- 视频管理组件 -->
        <Video :dialogVisible="dialog.videoVisible" :videoData="dialog.itemData" @handleClose="closeVideo"  @updateVideo="getProductList" v-if="dialog.videoVisible"></Video>
        <!-- 单个折扣设置组件 -->
        <Discount v-if="dialog.discountVisible" :bath="dialog.discountBath" :dialogVisible="dialog.discountVisible" :title="dialog.discountTitle" :discountForm="dialog.itemData" :optionList ="options.productList" :loading="dialog.discountLoading" @close="closeDiscount" @sure = "sureDiscount" ></Discount>
         <!-- 批量个折扣设置组件 -->
         <!-- <Discount :bath="true" :dialogVisible="dialog.discountVisible" :discountForm="dialog.itemData" @close="closeDiscount" @sure = "sureDiscount" ></Discount> -->

   </div>
</template>
<script>
import  mainBoxHeader from '@/components/mainBoxHeader.vue'
import ProductListAdd from '@/components/product/productListAdd.vue'
import StockManage from '@/components/product/stockManage.vue'
import BugManage from '@/components/product/bugManage.vue'
import SortEdit from '@/components/product/sortEdit.vue'
import Video from '@/components/product/videoDialog.vue'
import Discount from '@/components/product/setDiscount.vue'
export default {
    name:'Order',
    components:{
        mainBoxHeader,
        ProductListAdd,
        StockManage,
        BugManage,
        SortEdit,
        Video,
        Discount
    },
    data() {
        return{
            loading:false,
            orderData:{
                orderNumber:'',
                description:'管理所有邮箱账号和社交媒体账号商品，包括Gmail、微软邮箱、Instagram、Twitter、Facebook等账号。您可以添加、编辑、删除商品，并设置商品的价格、库存、发货方式等信息。',
                categoryId: '',
                categoryOptions: [],
                status: '',
                stockWarning: '',
                deliveryMethod: '',
                queryOptions: {
                    status_options: [],
                    stock_warning_options: [],
                    delivery_methods: []
                }
            },
            table:{
                multipleSelection:[],
                selectedTotalAmount:0,
                dataTable:[],
                pageSizes:[10,20,50,100],
                pageSize:10,
                total:0,
                page:1,
            },
            dialog:{
                showProductEdit:false,
                showProductAdd:false,
                dialogVisible:false,
                dialogVisibleAdd:false,
                dialogVisibleBug:false,
                sortEditVisible:false,
                sortLoading:false,//排序loading
                dialogType:'add',//edit 
                videoVisible:false,//视频管理
                discountVisible:false,//折扣管理
                discountBath:false,//是否批量折扣
                discountTitle:'',//折扣弹窗标题
                discountLoading:false,
                itemData:{},

            },
            drawer:{
                stockShow:false,
                drawerVisible:false,
            },
            formData:{},
            formDataAdd:{
                name:'',
                category_id:'',
                image:'',
                price:0,
                cost_price:0,
                original_price:0,
                delivery_method:'',
                status:'',
                description_template_id:'',
                stock_warning:0,
                remark:'',
                description:'',
                use_template:1,
                sort:0,
                stock:0,
                enable_purchase_limit:'0',
                purchase_limit_type:'0',
                purchase_limit_value:'0',
            },
            bugData:{},
            options:{
                productList:[],
            }
        }
    },
    created() {
        this.getCategoryList()
        this.getQueryOptions()
        this.getProductList()
        this.getAllProductList()
    },
    methods:{
        //获取所有商品
        getAllProductList(){
            const params = {
                limit: 10000
            }
            this.$api.productAllList(params).then(res => {
                if(res.code == 1){
                    this.options.productList = res.data.products
                }else{
                    this.$message.error(res.msg)
                }
            })
        },
        //获取商品分类列表
        getCategoryList(){
            this.$api.categoryList().then(res => {
                if(res.code == 1){
                    this.orderData.categoryOptions = res.data.list
                }else{
                    this.$message.error(res.msg || '获取商品分类失败')
                }
            }).catch(err => {
                console.error('获取商品分类失败:', err)
                this.$message.error('获取商品分类失败')
            })
        },
        //获取查询选项数据
        getQueryOptions(){
            this.$api.productQueryOptions().then(res => {
                if(res.code == 1){
                    this.orderData.queryOptions = res.data
                }else{
                    this.$message.error(res.msg || '获取查询选项失败')
                }
            }).catch(err => {
                console.error('获取查询选项失败:', err)
                this.$message.error('获取查询选项失败')
            })
        },
         //计算窗口位置
         getDialogTop(className){
            // this.$nextTick(() => {			
            //     let documentHeight = document.documentElement.clientHeight
            //     let dialogdiv =  document.getElementsByClassName(className)[0].offsetHeight
            //     this.dialog.top = (documentHeight - dialogdiv > 20)?(( documentHeight - dialogdiv) / 2 +'px'):'10px'
            //     console.log(documentHeight,'===',dialogdiv,'===',this.dialog.top)
            // })
        },
        //重置
        reset(){
            this.orderData.orderNumber = ''
            this.orderData.categoryId = ''
            this.orderData.status = ''
            this.orderData.stockWarning = ''
            this.orderData.deliveryMethod = ''
            this.table.page = 1
            this.getProductList()     
        },
        //查询按钮点击事件
        query(){
            this.table.page = 1
            this.getProductList()
        },
        //获取商品列表
        getProductList(){
            this.loading = true
            const params = {
                name: this.orderData.orderNumber,
                category_id: this.orderData.categoryId,
                delivery_method: this.orderData.deliveryMethod,
                status: this.orderData.status,
                stock_warning: this.orderData.stockWarning,
                page: this.table.page,
                limit: this.table.pageSize
            }
            this.$api.productList(params).then(res => {
                if(res.code == 1){
                    res.data.list.forEach(item => {
                        if(item.image){
                            item.image = this.$baseURL + item.image
                        }
                        return item
                    })
                    this.table.dataTable = res.data.list
                    this.table.total = res.data.total
                }else{
                    this.$message.error(res.msg || '获取商品列表失败')
                }
            }).catch(err => {
                console.error('获取商品列表失败:', err)
                this.$message.error('获取商品列表失败')
            }).finally(() => {
                this.loading = false
            })
        },  
        //分页
        handleCurrentChange(val){
            this.table.page = val
            this.getProductList()
        },
        handleSizeChange(val){
            this.table.pageSize = val
            this.table.page = 1
            this.getProductList()
        },
        //新增商品按钮
        addProduct(){
            this.dialog.dialogVisibleAdd = true
            this.dialog.dialogType = 'add'
            this.dialog.showProductAdd = true
        },  
        // 状态变更
        handleStatusChange (val,row) {
            let status = val;
            let old =val == '1' ? '0' : '1';
            let data = {
                "id": row.id,
                "status": val
            }
            row.status = old;
            this.loading = true
            this.$api.productStatus(data).then(res=>{
                this.loading = false;
                if(res.code === 1) {
                    this.$message({
                        type: 'success',
                        message: '操作成功!'
                    });
                    row.status = status;
                }else{
                    this.$message({
                        type: 'error',
                        message: res.msg
                    });
                }
            }).catch(err=>{
                this.loading = false;
            })
        },
        //关闭编辑弹窗
        closeDialog(){
            this.dialog.dialogVisible = false
            this.dialog.dialogType = 'edit'
            this.dialog.showProductEdit = false
            this.formDataAdd={
                name:'',
                category_id:'',
                image:'',
                price:0,
                cost_price:0,
                original_price:0,
                delivery_method:'',
                status:'',
                description_template_id:'',
                stock_warning:0,
                remark:'',
                description:'',
                use_template:'1',
                sort:0,
                stock:0,
                enable_purchase_limit:'0',
                purchase_limit_type:'0',
                purchase_limit_value:'0',
            };
        },
        //关闭新增弹窗
        closeDialogAdd(){
            this.dialog.dialogVisibleAdd = false
            this.dialog.dialogType = 'add'
            this.dialog.showProductAdd = false
            this.formDataAdd={
                name:'',
                category_id:'',
                image:'',
                price:0,
                cost_price:0,
                original_price:0,
                delivery_method:'',
                status:'',
                description_template_id:'',
                stock_warning:0,
                remark:'',
                description:'',
                use_template:1,
                sort:0,
                stock:0,
                enable_purchase_limit:'0',                
                purchase_limit_type:'0',
                purchase_limit_value:'0',
            };
        },
        //表格编辑按钮事件
        editProduct(item){
            this.dialog.dialogVisible = true
            this.dialog.dialogType = 'edit'
            this.dialog.showProductEdit = true
            this.formData = {...item}
        },
        //管理库存
        stockOpen(item){
            this.drawer.drawerVisible = true
            this.drawer.stockShow = true
            this.drawer.product_id = item.id
        },
        //关闭库存管理抽屉
        closeDrawer(){
            this.drawer.drawerVisible = false
            this.drawer.stockShow = false
        },
        //提交新增商品
        submitAdd(data){
            // console.log(data,'新增商品参数')
            this.$refs.productAdd.loading = true
            this.$api.productAdd(data).then(res=>{
                this.$refs.productAdd.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.closeDialogAdd()
                    this.getProductList()
                    this.getAllProductList()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{
                this.$refs.productAdd.loading = false
            })
        },
        //提交编辑商品
        submitEdit(data){
            // console.log(data,'编辑商品参数')
            this.$refs.productEdit.loading = true
            this.$api.productEdit(data).then(res=>{
                this.$refs.productEdit.loading = false
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.closeDialog()
                    this.getProductList()
                    this.getAllProductList()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{
                this.$refs.productEdit.loading = false
            })
        },
        //列表删除按钮点击事件    删除商品
        deleteData(item){
            this.$confirm(`确定要删除商品"${item.name}"吗？`, '警告', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                this.loading = true;
                this.$api.productDelete({id: item.id}).then(res => {
                    this.loading = false;
                    if(res.code == 1) {
                        this.$message.success(res.msg)
                        this.getProductList()
                        this.getAllProductList()
                    }
                }).catch(err => {
                    this.loading = false;
                })
            }).catch(err => {
                this.$message.info('已取消删除')
            })
        },
        //包管理按钮点击事件
        bugManage(item){
            console.log(item,'包管理')
            this.dialog.dialogVisibleBug = true
            this.bugData = item
        },
        videoOpen(item){
            this.dialog.videoVisible = true
            this.dialog.itemData = item
        },
        closeVideo(){
            this.dialog.videoVisible = false
            this.dialog.itemData = {}
        },
        
        //关闭包管理
        closeBug(){
            this.dialog.dialogVisibleBug = false
        },
         //批量折扣设置
         bathDiscount(){
            this.dialog.discountBath=true;
            this.dialog.discountVisible = true;
            this.dialog.discountTitle = '批量折扣设置';
            this.dialog.itemData={}
        },
          //折扣设置
        setDiscount(item){
            this.dialog.discountBath=false;
            this.dialog.discountVisible = true;
            this.dialog.discountTitle = '折扣设置';
            this.dialog.itemData = {...item}
        },//关闭折扣设置弹窗
        closeDiscount(){
            this.dialog.discountVisible = false;
            this.dialog.itemData = {}
        },
        //确认折扣设置
        sureDiscount(form,bath){
            console.log(form,'折扣设置参数')
            if(bath){
                //批量设置
                this.setDiscountBatch(form)
            }else{
                //单个设置
                this.setProductDiscount(form)
            }
        },
        //单个设置折扣
        setProductDiscount(form){
            let data ={
                "id": form.id,
                "enabled": form.discount_enabled,
                "percentage": form.discount_percentage,
                "start_time": this.$util.timestampToTime(Date.parse(new Date(form.discount_start_time), false)),
                "end_time": this.$util.timestampToTime(Date.parse(new Date(form.discount_end_time), false)),
            }
            this.dialog.discountLoading = true;
            this.$api.setProductDiscount(data).then(res=>{
                this.dialog.discountLoading = false;
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.closeDiscount()
                    this.getProductList()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{
                this.dialog.discountLoading = false;
            })
        },
        //批量设置折扣
        setDiscountBatch(form){
            // console.log(form,'批量折扣设置参数')
            let data = {
                "product_ids": form.names,
                "enabled": form.discount_enabled,
                "percentage": form.discount_percentage,
                "start_time": this.$util.timestampToTime(Date.parse(new Date(form.discount_start_time), false)),
                "end_time":  this.$util.timestampToTime(Date.parse(new Date(form.discount_end_time), false)),
            }
            this.dialog.discountLoading = true;
            this.$api.setDiscountBatch(data).then(res=>{
                this.dialog.discountLoading = false;
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.closeDiscount()
                    this.getProductList()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(err=>{                
                this.dialog.discountLoading = false;
            })
        },
        handleCommand(command, row){
            this[command](row);
        },
        //排序 
        //打开排序编辑弹窗
        editSort(item){
            this.dialog.sortForm={
                name:item.name,
                sort:item.sort,
                newSort:Number(item.sort),
                id:item.id
            }
            this.dialog.sortEditVisible = true;
            this.dialog.sortLoading = false
        },
        // 关闭
        closeSortDialog(){
            this.dialog.sortForm={}
            this.dialog.sortEditVisible = false;
            this.dialog.sortLoading = false
        },

        //提交排序
        submitSort(data){
            this.dialog.sortLoading = true
            this.$api.sortProduct(data).then(res=>{
                this.dialog.sortLoading = false
                if(res.code == 1){
                    this.$message.success(res.msg)
                    this.closeSortDialog()
                    this.getProductList()
                }else{
                    this.$message.error(res.msg)
                }
            }).catch(e=>{
                this.dialog.sortLoading = false
            })
        },
      
    },
}
</script>
<style lang="scss">
    .order-view{
        .el-tabs--border-card>.el-tabs__content{
            padding:0 !important;
        }
    }   
     
</style>
<style lang="scss" scoped>

.order-view{

    .order-query-box{
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 20px;
        .query-item{
            display: flex;
            align-items: center;        
            margin-right: 32px;
            font-size: 14px;
            margin-bottom: 18px;
            color:#606266;
            .query-input{
                width: 192px;
            }
            .query-select{
                width: 168px;
            }
        }
    }
    .data-table{
        .product-img{
            width: 80px; 
            height: 80px; 
            border-radius: 4px;
            color: #f56c6c;
            background-color: #fef0f0;
            border: 1px solid #fde2e2;
            display: flex;
            justify-content: center;
            align-items: center;
            .error-img-icon{
                color: #f56c6c;
                font-size: 14px;
            }
        }
        .price-container {
            .original-price {
                text-decoration: line-through;
                color: #909399;
                font-size: 12px;
                margin-left: 4px;
            }
        }     
        
        .low-stock {
            color: #f56c6c;
            font-weight: bold;
        } 
        .no-limit{
            color: #909399;
            font-size: 12px;
        }     
        :deep(.el-tag--small){
            height:20px;
            padding:0 7px;
        } 
        .sort-edit-box{
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            .edit-sort{
                width: 20px;
                .sort-icon{
                    color: #409eff;
                    font-size: 16px;
                    cursor: pointer;
                    transition: all .3s;
                    font-weight: 400;
                    stroke-width: 2;
                    &:hover{
                        font-size: 18px;
                    }
                }
                
            }
        }

        
    }
   
}











</style>
