<template>
  <div class="container-custom">
    <div class="max-w-6xl mx-auto py-2 sm:py-4 ">
        <h1 class="text-2xl font-bold mb-3 sm:mb-4">订单查询</h1>
        <div class="lg:bg-white lg:rounded-lg lg:shadow-sm lg:p-6 lg:mt-4">
            <div class="order-search-box flex flex-col lg:flex-row gap-4 lg:gap-6">
                <div class="search-form-box flex-shrink-0">
                    <searchForm 
                        ref="searchFormRef"
                        @searchSuccess="handleSearchSuccess"
                        @searchError="handleSearchError"
                        @orderSelected="handleOrderSelected"
                        :code_token="code_token"
                    ></searchForm>
                </div>
                <div class="search-result-box flex-1" >
                    <SearchResult 
                        :orderList="orderList"
                        @orderSelected="handleOrderSelected"
                        @reSearch="handleReSearch"
                        :firstOrder="currentOrder"
                        :email="email"
                    ></SearchResult>
                    <OrderInfo :orderInfo="currentOrder"  v-if="false"></OrderInfo>
                    <div class="account-info-box mt-4 w-full" v-if="packageList.length>0 && currentOrder && (  currentOrder.status == '2' || currentOrder.status == '3')">
                        <PackageDownload :packageList="packageList"></PackageDownload>
                    </div>
                    <div class="account-info-box mt-4 w-full" v-if="currentOrder?.card_info?.length > 0">
                        <AccountInfo :orderInfo="currentOrder" titleName="卡密详情"></AccountInfo>
                    </div>
                </div>
            </div>
            <div class="account-info-box mt-4" v-if="false">
               <AccountInfo :orderInfo="currentOrder"></AccountInfo>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref ,onMounted} from 'vue'
import SearchForm from '@/components/order/searchForm.vue'
import SearchResult from '@/components/order/searchResult.vue'
import OrderInfo from '@/components/order/orderInfo.vue'
import AccountInfo from '@/components/order/accountInfo.vue'
import PackageDownload from '@/components/order/packageDownload.vue'
import { BASE_URL } from '@/utils/request'
import{productApi,userApi ,orderApi} from '@/api'
import axios from 'axios';
const orderList = ref([])
const currentOrder = ref(null)
const email = ref('')
const searchFormRef = ref(null)
const code_token = ref('')
const packageList = ref([])
onMounted(() => {
})
const handleSearchSuccess = (data,e) => {
    orderList.value = data
    email.value = e
    // console.log(email.value,'====email',orderList)
}


const handleSearchError = () => {
    orderList.value = []
    currentOrder.value = null
}

const handleOrderSelected = (order) => {
    currentOrder.value = order
    // console.log(currentOrder.value,'dddd====currentOrder')
    //查安装包
    // console.log(order,order.product_id,'order.product_id===')
    getPackage(order.product_id)
}

const handleReSearch = () => {
    orderList.value = []
    currentOrder.value = null
    searchFormRef.value?.resetForm()
}
const getPackage = async (product_id) => { 
 // 获取安装包列表
    // console.log(product_id,'产品id')
    const packageRes = await productApi.getPackages(product_id)
    if(packageRes.code === 1) {    
        packageList.value = packageRes.data.list
    }else{
        packageList.value = []
    }
}
</script>
<style lang="scss">
.container-custom{
    .el-form-item__label{
        font-size: 14px;
}
}

</style>
<style scoped lang="scss">
// .container-custom {
//     width: 100%;
//     max-width: 1280px;
//     margin-left: auto;
//     margin-right: auto;
//     padding-left: 1rem;
//     padding-right: 1rem;
   
// }
@media (min-width: 640px) {
    // .container-custom {
    //     max-width: 640px;        
    //     padding-left: 1.5rem;
    //     padding-right: 1.5rem;
    // }
}
@media (min-width: 768px) {
    // .container-custom {
    //     max-width: 768px;
    // }
}
@media (min-width: 1024px) {
    // .container-custom {
    //     max-width: 1024px;
    //     padding-left: 2rem;
    //     padding-right: 2rem;
    // }
    .search-form-box{
        width: 480px;
    }
}
@media (min-width: 1280px) {
    // .container-custom {
    //     max-width: 1280px;
    // }
}
// .container-custom {
//     max-width: 1280px;
   
// }
</style>
