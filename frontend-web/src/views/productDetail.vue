<template>
  <div class="product-detail-box">
    <div class="bg-white border-b md:block hidden">
      <div class="container-custom py-4">
        <h1 class="text-xl font-bold text-gray-800">
          {{ productDetail.name }}
        </h1>
      </div>
    </div>
    <div class="">
      <div class="product-box max-w-6xl mx-auto py-4 px-[8px] sm:px-4">
        <div class="detail-title-box mb-4">
          <div class="flex items-center text-sm mb-2">
            <Tags :bgColor="productDetail.delivery_method === 'auto' ? '#2563eb':'linear-gradient(to right, #f97316,#ef4444)'"             
              :tag="productDetail.delivery_method === 'auto' ? '自动发货' : '手动发货'"/>
            <router-link
              class="text-gray-600 hover:text-gray-800"
              :to="`/mall#section-${query.index}`"
            >
              {{ productDetail.category_name }}
            </router-link>
            <el-icon class="text-gray-400 mx-2" size="14"><ArrowRight /></el-icon>
            <span class="text-gray-700">{{ productDetail.name }}</span>
          </div>
        </div>
        <div class="detail-content-box flex flex-row gap-2 sm:gap-3.5 md:mb-4 mb-3-5">
          <div class="md:w-1/4 w-1/3 flex-shrink-0">
            <div
              class="product-img-box bg-white p-6px rounded-lg border border-gray-200 shadow-sm h-full flex items-center justify-center"
            >
              <div class="w-full aspect-square flex items-center justify-center">
                <div class="w-full h-full p-6px">
                  <el-image
                    class="w-full h-full rounded-lg"
                    :src="productDetail.image"
                    fit="cover"
                  ></el-image>
                </div>
              </div>
            </div>
          </div>
          <div class="md:w-3/4 w-2/3">
            <!-- <h1 class="text-2xl font-bold text-gray-800 mb-4  md:block hidden">
              {{ productDetail.name }}
            </h1> -->
            <div class="product-attr-box">
              <div class="md:p-4 flex md:items-center items-start md:justify-between md:flex-row flex-col">
                <div class="flex items-baseline product-attr-price md:p-0 p-3">
                  <div>
                    <span class="text-gray-700 sm:mr-2 mr-1 text-md-lg md:text-md">价格</span>
                    <span class="text-red-500 text-2xl font-bold">¥{{ productDetail.price }}</span>
                    <span class="text-gray-500 text-sm line-through ml-1 sm:ml-2 text-sm-x md:text-sm" v-if="productDetail.original_price > 0">¥{{ productDetail.original_price }}</span>
                  </div>
                </div>
                <div class="flex items-center product-attr-stock md:p-0 p-3">                  
                  <span class="text-sm md:text-md">
                    <span class="text-gray-700 ">库存：</span>
                    <span :class="productDetail.stock>0?'text-blue-600':'text-red-500'">{{ productDetail.stock >0 ? productDetail.stock : '售罄'}}</span>
                  </span>                  
                  <span class="restock text-blue-600 text-sm ml-3 cursor-pointer" @click="restockClick">通知补货</span>
                </div>
              </div>
            </div>
            <div class="md:block hidden">
              <ProductDetailBuy :productDetail="productDetail" @buyProduct="buyProduct" @customer = 'customer' :loading="loading" :token="token"></ProductDetailBuy>
            </div>          
            
           
          </div>
        </div>
        <div class="md:hidden block mb-3-5">
              <ProductDetailBuy :productDetail="productDetail" @buyProduct="buyProduct" @customer = 'customer' :loading="loading" :token="token"></ProductDetailBuy>
            </div>
            
        <div class="detail-desc-box bg-white p-6 rounded-lg border border-gray-200 shadow-sm w-full">
          <div class="border-b border-gray-200 pb-3 mb-5">
            <h2 class="text-xl font-bold text-gray-800">商品描述</h2>
          </div>
          <div v-html="DOMPurify.sanitize(productDetail.description)"></div>
        </div>
      </div>
    </div>
    <div class="dialog-box">
      <el-dialog
        v-model="protocolDialogData.visible"
        title="服务协议" 
        class="protocol-dialog" 
        :show-close="false" 
        :close-on-click-modal="false"
        modal-class="protocol-modal" 
        header-class="protocol-header" 
        body-class="protocol-body" 
        footer-class="protocol-footer"
      >
        <div class="protocol-content">
          <div v-html="DOMPurify.sanitize(protocolDialogData.content)"></div>
        </div>
        <template #footer>
          <div
            class="dialog-footer flex items-center justify-center gap-3 bg-gray-50"
          >
            <div
              :class="
                protocolDialogData.disabled
                  ? 'button-info'
                  : 'button-primary cursor-pointer'
              "
              class="text-lgx protocol-btn"
              @click="agreeProtocol()"
            >
              {{
                protocolDialogData.disabled
                  ? `阅读剩余${protocolDialogData.time}秒`
                  : "我同意"
              }}
            </div>
            <div
              class="text-lgx button-info ml-1 cursor-pointer protocol-btn"
              @click="disagreeProtocol()"
            >
              我不同意
            </div>
          </div>
        </template>
      </el-dialog>
      <el-dialog
        v-model="restockDialogData.visible"
        title="通知补货" 
        class="restock-dialog" 
        :show-close="false" 
        :close-on-click-modal="false"
        modal-class="restock-modal" 
        header-class="restock-header" 
        body-class="restock-body" 
        footer-class="restock-footer"
      >
        <el-form
          :model="restockDialogData.formData"
          label-position="top"
          :hide-required-asterisk="true"
          :rules="rules"
          ref="restockDialogRef"
        >
          <el-form-item label="您的邮箱" label-width="" prop="email">
            <el-input
             :readonly="token"
              class="dialog-input"
              v-model="restockDialogData.formData.email"
            />
          </el-form-item>
          <el-form-item label="需要数量" label-width="" prop="count">
            <el-input
              type="number"
              class="dialog-input"
              v-model="restockDialogData.formData.count"
              min="1"
              :max="maxCount"
            />
          </el-form-item>
          <el-form-item label="补充说明（选填）" label-width="" prop="remark">
            <el-input
              class="dialog-input"
              type="textarea"
              v-model="restockDialogData.formData.remark"
              :rows="4"
            />
          </el-form-item>
        </el-form>
        <template #footer>
          <div
            class="dialog-footer flex items-center justify-end gap-3 bg-gray-50"
          >
            <div
              class="text-lgx button-info cursor-pointer"
              @click="closeRestockDialog(restockDialogRef)"
            >
              取消
            </div>
            <div
              class="text-lgx button-primary ml-1 cursor-pointer"
              @click="sureRestockDialog(restockDialogRef)"
            >
              提交通知
            </div>
          </div>
        </template>
      </el-dialog>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute,useRouter } from "vue-router";
import { ElMessage } from 'element-plus'
import Tags from "@/components/tags.vue";
import { productApi,orderApi,protocolApi} from '@/api'
import { BASE_URL } from '@/utils/request'
import ProductDetailBuy from "@/components/productDetailBuy.vue";
import DOMPurify from 'dompurify'

const route = useRoute();
const router = useRouter();
const query = route.query;
const token = ref('')
const userInfo = ref({})
const customerLinks = ref({})
//补货通知表单ref
const restockDialogRef = ref(null);
const loading = ref(false);
const productDetail = ref({
  id: query.id,
  category_id: '',
  name: '',
  category_name: '',
  delivery_method: '',
  price: '',
  original_price: '',
  stock: 0,
  image: '',
  description: '',
  buyProduct: {
    quantity: 1,
    mail: "",
    password: "",
    protocol: false,
  },
});

// 获取商品详情
const getProductDetail = async () => {
  try {
    const res = await productApi.getProductDetail(query.id)
    if (res.code === 1) {
      const data = res.data
      data.image = `${BASE_URL}${data.image}`;
      productDetail.value = {...productDetail.value,...data}
      // productDetail.value = {
      //   ...productDetail.value,
      //   category_id: data.category_id,
      //   name: data.name,
      //   category_name: data.category_name,
      //   delivery_method: data.delivery_method,
      //   price: data.price,
      //   original_price: data.original_price,
      //   stock: data.stock,
      //   image: `${BASE_URL}${data.image}`,
      //   description: data.description
      // }
    }
  } catch (error) {
    console.error('获取商品详情失败:', error)
    ElMessage.error('获取商品详情失败')
  }
}
//获取使用协议
const getProtocol = async () => {
  const res = await protocolApi.getProtocol('使用协议')
  if (res.code === 1) {
    protocolDialogData.value.content = res.data.content
  }
}

// 监听数量变化，确保不超过库存
watch(() => productDetail.value.buyProduct.quantity, (newVal) => {
  if (!/^\d+$/.test(newVal) && newVal!="") {
    // 如果不是正整数，将其设置为上一次有效的值
    productDetail.value.buyProduct.quantity = Math.max(1, parseInt(newVal) || 1);
  }else if (newVal > productDetail.value.stock) {
    productDetail.value.buyProduct.quantity = productDetail.value.stock
  }
  //只能输入正整数
})

onMounted(() => {
  customerLinks.value = JSON.parse(localStorage.getItem('customerLinks'))
  getProductDetail()
  getProtocol()
  token.value = localStorage.getItem('token')
  if(token.value){
    userInfo.value = JSON.parse(localStorage.getItem('userInfo'))
    if(userInfo.value.email){
      productDetail.value.buyProduct.mail = userInfo.value.email
      restockDialogData.value.formData.email = userInfo.value.email
    }
  }
})

const restockDialogData = ref({
  visible: false,
  formData: {
    email: "",
    count: null,
    remark: "",
  },
});
const protocolDialogData = ref({
  time: 3,
  disabled: true,
  visible: false,
  title: "服务协议",
  content:'',
});
// const timOut = ref(null);
//数量校验规则
const maxCount = 9999;
const checkNum = (rule, value, callback) => {  
  if (value < 1 || value > maxCount) {
    callback(new Error(`数量必须在1-${maxCount}之间`));
  } else {
    callback();
  }
};
const rules = {
  email: [
    { required: true, message: "请输入邮箱", trigger: "blur" },
    { type: "email", message: "请输入正确的邮箱", trigger: "blur" },
  ],
  count: [
    { required: true, message: "请输入数量", trigger: "blur" },
    { pattern: /^[1-9]\d*$/, message: "请输入数字", trigger: "blur" },
    { validator: checkNum, trigger: "blur" },
  ],
  remark: [
    { max: 150, message: "补充说明不超过150字", trigger: "blur" },
  ],
};
//补货通知按钮点击
const restockClick = () => {
  restockDialogData.value.visible = true;
  if(token.value){
    if(userInfo.value.email){
      restockDialogData.value.formData.email = userInfo.value.email
    }
  }
};

//关闭补货通知弹窗
const closeRestockDialog = (formRef) => {
  if (!formRef) return;
  formRef.resetFields();
  restockDialogData.value.visible = false;
  restockDialogData.value.formData = {
    email: "",
    count: null,
    remark: "",
  };
};
//提交补货通知
const sureRestockDialog = async (formRef) => {
  if (!formRef) return;
  formRef.validate(async (valid) => {
    if (valid) {
      try {
        loading.value = true;
        const res = await productApi.notifyReplenish({
          product_id: productDetail.value.id,
          product_name: productDetail.value.name,
          email: restockDialogData.value.formData.email,
          quantity: restockDialogData.value.formData.count,
          remarks: restockDialogData.value.formData.remark
        });
        if (res.code === 1) {
          ElMessage.success('补货通知已提交');
          closeRestockDialog(formRef);
        } else {
          ElMessage.error(res.msg || '提交失败');
        }
      } catch (error) {
        console.error('提交补货通知失败:', error);
        ElMessage.error('提交失败');
      } finally {
        loading.value = false;
      }
    }
  });
};
//协议勾选事件
const protocolChange = (val) => {
  // console.log(val, "====");
  if (val) {
    //设置倒计时
    protocolDialogData.value.time = 0;
    protocolDialogData.value.disabled = false;
    //先取消勾选  等带同意协议后才勾选
    productDetail.value.buyProduct.protocol = false;
    //弹窗协议弹窗    
    protocolDialogData.value.visible = true;
    // clearInterval(timOut.value);
    // timOut.value = setInterval(() => {
    //   protocolDialogData.value.time--;
    //   if (protocolDialogData.value.time <= 0) {
    //     protocolDialogData.value.disabled = false;
    //     clearInterval(timOut.value);
    //   }
    // }, 1000);
  } else {
    productDetail.value.buyProduct.protocol = false;
  }
};
//弹窗内同意用户协议事件
const agreeProtocol = () => {
  if (protocolDialogData.value.disabled) {
    return;
  }
  productDetail.value.buyProduct.protocol = true;
  protocolDialogData.value.visible = false;  
  // clearInterval(timOut.value);
};
//弹窗内不同意用户协议事件
const disagreeProtocol = () => {
  productDetail.value.buyProduct.protocol = false;
  protocolDialogData.value.visible = false;  
  // clearInterval(timOut.value);
};
const emailValid = (email) => {
  //邮箱验证正则表达式
  if(!email){
    return true
  }else{
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
  
};
//查询密码验证
const passwordValid=(password)=>{ 
  if(password.length<6 || password.length>16){
    return false
  }
  const reg = /^(?=.*[a-zA-Z])(?=.*\d).+$/;
  if(!reg.test(password)){
    return false
  }
  return true
}
//购买商品
const buyProduct = async () => {
  // if (!productDetail.value.buyProduct.protocol) {
  //   ElMessage.error('请先同意用户协议');
  //   return;
  // }
  if (!productDetail.value.buyProduct.quantity || productDetail.value.buyProduct.quantity<=0) {
    ElMessage.error('请输入数量');
    return;
  }
  if (!productDetail.value.buyProduct.mail) {
    ElMessage.error('请输入邮箱');
    return;
  }
  if (!emailValid(productDetail.value.buyProduct.mail)) {
    ElMessage.error('请输入正确的邮箱');
    return;
  }
  // if(!productDetail.value.buyProduct.password){
  //   ElMessage.error('请输入查询密码');
  // }
  // if (!passwordValid(productDetail.value.buyProduct.password)) {
  //   ElMessage.error('密码为6-16位，必须包含英文字母和数字');
  //   return;
  // }

  try {
    loading.value = true;
    const res = await orderApi.createOrder({
      product_id: productDetail.value.id,
      quantity: productDetail.value.buyProduct.quantity,
      email: productDetail.value.buyProduct.mail,
      // query_password: productDetail.value.buyProduct.password,
    });
    
    if (res.code === 1) {
      router.push({
        path: '/payment',
        query: {
          order_no:res.data.order_no,
          email:productDetail.value.buyProduct.mail,
          id:productDetail.value.id,
        },
      });
    } else {
      ElMessage.error(res.msg);
    }
  } catch (error) {
    console.error('创建订单失败:', error);
    // ElMessage.error('创建订单失败');
  } finally {
    loading.value = false;
  }
}
//联系客服
 const customer=()=>{
  
  window.open(customerLinks.value.online_service_link);
 }
</script>
<style lang="scss">
.restock-modal {
  .el-overlay-dialog{
  display: flex;
  justify-content: center;
  align-items: center;
}
 
}
.restock-dialog {
  padding: 0;
  max-width: 28rem;
  border-radius: 0.5rem;
  background-color: #f9fafb;
  width:100%;
  margin: 1rem;

  .restock-header {
    border-bottom: solid 1px #e5e7eb;
    padding: 1rem;
    .el-dialog__title {
      color: #111827;
      font-size: 1.125rem;
      font-weight: 500;
      line-height: 1.75rem;
    }
  }
  .restock-body {
    background-color: #fff;
    padding: 1rem;
    .el-form-item__label {
      color: #374151;
      font-weight: 500;
      font-size: 0.875rem;
      line-height: 1.25rem;
    }
    .dialog-input {
      .el-input__wrapper {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 1rem;
      }
      .el-textarea__inner {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 1rem;
        &:focus {
          outline: solid 2px var(--primary-color);
          box-shadow: none;
        }
      }
      // input{
      //   padding: 10px 1rem;
      //   border: solid 1px #e5e7eb;
      //   border-radius: 0.5rem;
      //   &:focus{
      //     outline: solid 2px var(--primary-color);
      //   }
      // }
    }
  }
  .restock-footer {
    padding: 1rem;
    border-top: solid 1px #e5e7eb;
  }
}
// .protocol-checkbox {
//   .el-checkbox__input {
//     &.is-checked {
//       .el-checkbox__inner {
//         background-color: var(--primary-color);
//         border-color: var(--primary-color);
//       }
//     }
//   }
//   .el-checkbox__input {
//     .el-checkbox__inner {
//       &:hover {
//         border-color: var(--primary-color);
//       }
//     }
//   }
// }
.protocol-modal {
  .el-overlay-dialog{
  display: flex;
  justify-content: center;
  align-items: center;}
  
  .protocol-dialog {
  padding: 0;
  max-width: 48rem;
  margin: 1rem;
  width:100%;
    .protocol-header {
    padding: 1rem;
    border-bottom: solid 1px #e5e7eb;
    text-align: center;
      .el-dialog__title {
      font-size: 1.25rem;
      color: var(--danger-color);
      font-weight: 500;
      line-height: 1.75rem;
    }
  }
    .protocol-body {
      padding: 1.25rem;
    max-height: 65vh;
    overflow-y: auto;
  }
    .protocol-footer {
    padding: 1rem;
    border-top: solid 1px #e5e7eb;
      .protocol-btn {
      padding-left: 2rem;
      padding-right: 2rem;
    }
  }
}
}
.detail-desc-box{
    img{
      max-width: 100%;
    }
    ol, ul {
      padding-left: 1rem;
      white-space: normal;
      word-break: break-all;
      // margin: revert;
    }
  }

</style>
<style scoped lang="scss">
 
  .product-attr-box {
    // background-color: #eff6ff;
    .product-attr-price{
      order: 1;
      background-color: #eff6ff;
      width: 100%;
      box-shadow:  0 1px 2px 0 rgba(0, 0, 0, .05);
      height:72px;
      border-radius: .5rem;
      align-items: center;
    }
    .product-attr-stock{
      background-color: #fff;
      width: 100%;
      justify-content: space-between;
      box-shadow:  0 1px 2px 0 rgba(0, 0, 0, .05);
      height:60px;
      border: solid 1px #e5e7eb;
      border-radius: .5rem;
      margin-bottom: 0.5rem;
    }
    .restock {
      // color: var(--primary-color);
      text-decoration: underline;
      &:hover {
        color: var(--primary-dark);
      }
    }
  }
  .product-input {
    padding: 10px 1rem;
    &:focus {
    outline: solid 2px var(--primary-color);
    }
  }
  .buy-btn {
    color: #fff;
    padding: 10px 1rem;
    &.can-buy{
      &:hover {
        background-color: var(--primary-dark);
    }
    }
    
  }
  
@media (min-width: 640px) {
  .product-input {
      width: 66.666667%;
  }
}
@media (min-width: 768px) {  
  .product-attr-box {
    background-color: #eff6ff;
    border-radius: 0.5rem;
    box-shadow:  0 1px 2px 0 rgba(0, 0, 0, .05);
    margin-bottom: 1.25rem;
    .product-attr-price{
      order: 0;
      background-color: transparent;
      width: auto;
      box-shadow:  none;
      height:auto;
      align-items: baseline;
    }
    .product-attr-stock{
      background-color: transparent;
      width: auto;
      justify-content: flex-end;
      box-shadow:  none;
      height:auto;
      border:none;
      margin-bottom: 0;
    }
    .restock {
      // color: var(--primary-color);
      text-decoration: underline;
      &:hover {
        color: var(--primary-dark);
      }
    }
  }
 
}
@media (min-width: 1024px) {
 
}
</style>
