<template>
    <div class="mall-view bg-gray-100">
        <!-- 搜索框 -->
        <div class="relative search-box px-3 sm:px-6 pt-8 pb-6 sm:pt-12 sm:pb-8 ">
            <div class="left-position z-1"></div>
            <div class="right-position z-1"></div>
            <div class="max-w-3xl mx-auto z-2 relative">               
                <div class="mb-8 text-center">
                    <h2 class="text-2xl sm:text-4xl font-bold text-white mb-3">寻找优质账号</h2>
                    <p class="text-blue-100 text-sm sm:text-base max-w-2xl mx-auto">精选优质账号，安全可靠，即买即用</p>
                </div>
                <div class="search-input-box">
                    <div class="input-box pr-10">
                        <input class="search-input" v-model="searchData.input1" type="text" placeholder="搜索商品..." @keyup.enter="searchProduct" />
                        <div class="search-icon cursor-pointer" @click="searchProduct">                            
                            <i class="iconfont icon-search text-white" style="font-size: 18px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 内容区域 -->
        <div class="max-w-6xl mx-auto px-3 sm:px-6 py-4 sm:py-8 relative">            
        <!-- 索引列表 -->
            <el-anchor class="mall-anchor" :offset="140" :bound="140">
                <div class="anchor-title text-gray-700 font-bold mb-3">索引</div>
                <el-anchor-link v-for="(item, index) in productSections" :key="index"
                    :href="'#section-' + index"
                    :title="item.title" 
                    :class="{ 'is-active': isFirstSectionActive && index === 0 }"/>
            </el-anchor>
            <el-affix :offset="affixOffset" class="mobile-anchor-box">
                <div class="w-full px-4 py-3">
                    <el-anchor ref="anchorRef" class="mobile-anchor" :offset="140" :bound="140" direction="horizontal" :marker="false">
                        <el-anchor-link class="mobile-anchor-link" v-for="(item, index) in productSections" :key="index"
                         :href="'#section-' + index" :title="item.title"
                         :class="{ 'is-active': isFirstSectionActive && index === 0 }">
                        </el-anchor-link>
                    </el-anchor>
                </div>
            </el-affix>
            <!-- 商品列表 -->
            <div class="space-y-6 mall-product-item" :class="index == 0? 'no-margin-top' : ''" v-for="(item, index) in productSections" :key="index">
                <mall-product-block                     
                    :title="item.title"
                    :description="item.description"
                    :index="index"
                    :products="item.products" />
            </div>
            <div class="about-us mt-8 sm:mt-12">
                <h2 class="about-title sm:mb-6">为什么选择我们</h2>
                <div class="about-content">
                    <div class="about-item" v-for="(item,index) in chooseUs" :key="index">
                        <div class="about-item-title flex items-center mb-2">
                            <i :class="item.icon" class="iconfont w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-2 text-gray-700" style="font-size: 14px;"></i>
                            <!-- <el-icon class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-2" size="16px"><Check /></el-icon> -->
                            <h3 class="font-bold text-gray-800 text-sm sm:text-base">{{item.title}}</h3>
                        </div>
                        <div class="about-item-content text-xs sm:text-sm text-gray-600">
                           {{item.content}}
                        </div>
                    </div>
                </div>
            </div>      
        </div>
        <div class="dialog-box">
            <PageDialog v-if="dialog.pageDialog.visible" :visible="dialog.pageDialog.visible" :dialogContent ="dialog.pageDialog.dialogContent" @closeDialog="closeDialog"></PageDialog>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, onUnmounted, computed } from 'vue'
import { productApi,protocolApi } from '@/api'
import { BASE_URL } from '@/utils/request'
import { useRoute } from 'vue-router'
import emitter from '@/utils/eventBus';
import mallProductBlock from '@/components/mall-product-block.vue'
import PageDialog from '@/components/pageDialog.vue'

const route = useRoute()
const searchData = ref({
    input1: '',
})
const affixOffset = ref(64)

const productSections = ref([])
const chooseUs= ref([{
    icon:'icon-shield-alt',
    title:'安全保障',
    content:'所有账号均经过严格测试，确保质量可靠，交易安全有保障。'
},
{
    icon:'icon-bolt',
    title:'极速发货',
    content:'自动发货系统，下单后秒级到账，让您立即获得所需账号。'
},
{
    icon:'icon-headset',
    title:'贴心服务',
    content:'专业客服团队，7×24小时在线，随时解答您的问题和疑虑。'
},
{
    icon:'icon-thumbs-up',
    title:'品质保证',
    content:'严选优质账号，稳定可靠，使用无忧，让您的体验更加出色。'
}
])

const isFirstSectionActive = ref(true);
const dialog = ref({
    pageDialog: {
        visible: false,
        dialogContent: {}
    }
})
const checkFirstSection = () => {
    if (productSections.value.length === 0) return;
    
    const firstSection = document.getElementById('section-0');
    if (!firstSection) return;
    
    const rect = firstSection.getBoundingClientRect();
    const searchBoxHeight = document.querySelector('.search-box')?.offsetHeight || 0;
    const scrollTop = window.pageYOffset;
    
    // 当页面顶部或第一个section在视口上方时，激活第一个锚点
    isFirstSectionActive.value = scrollTop === 0 || rect.top > searchBoxHeight + 140;
};

// 获取商品列表数据
const getProductList = async (keyword) => {
    try {
        const res = await productApi.getProductList(keyword)
        if (res.code === 1) {
            productSections.value = res.data.list.map(item => ({
                title: item.category.name,
                description: item.category.description,
                products: item.products.map(product => ({
                    id: product.id,
                    name: product.name,
                    image: `${BASE_URL}${product.image}`,
                    price: product.price,
                    original_price: product.original_price,
                    stock: product.stock,
                    delivery_method: product.delivery_method
                }))
            }))
            // 数据加载完成后，检查是否需要滚动
            await nextTick()
            if (route.hash) {
                scrollToSection()
            } else {
                // 确保第一个锚点激活
                isFirstSectionActive.value = true
            }
        }
    } catch (error) {
        console.error('获取商品列表失败:', error)
    }
}

// 滚动到指定分类
const scrollToSection = () => {
    const hash = route.hash
    if (hash) {
        const sectionId = hash.substring(1) // 去掉 # 号
        const element = document.getElementById(sectionId)
        if (element) {
            // 计算滚动位置，考虑固定定位的搜索框高度
            const searchBoxHeight = document.querySelector('.search-box')?.offsetHeight || 0
            const elementPosition = element.getBoundingClientRect().top
            const offsetPosition = elementPosition + window.pageYOffset - searchBoxHeight - 20 // 额外减去20px的间距
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            })
        }
    }
}

//获取弹窗信息
const getPopup = () =>{
  protocolApi.getPopup().then(res => {
    if(res.code == 1){             
      if(res.data.show_popup){
        const date = new Date()
        let year = date.getFullYear()  
        let month = date.getMonth() + 1
        let day = date.getDate()
        let showDate = year + '-' + month + '-' + day
        const oldShowDate = localStorage.getItem('showDate')
        if(oldShowDate == showDate){
          return
        }
        dialog.value.pageDialog.visible = true;
        dialog.value.pageDialog.dialogContent = res.data    
        localStorage.setItem('showDate', showDate); 

      }
    }
  })
}
const closeDialog = ()=>{
    dialog.value.pageDialog.visible = false;
}
// 监听路由变化
watch(() => route.hash, () => {
    if (productSections.value.length > 0) {
        nextTick(() => {
            scrollToSection()
        })
    }
})

// 搜索商品
const searchProduct = () => {
    getProductList(searchData.value.input1)
}

onMounted(() => {
    getPopup();
    getProductList('')
    // 初始时设置第一个锚点激活
    isFirstSectionActive.value = true
    window.addEventListener('scroll', checkFirstSection);

    checkStatus();
    window.addEventListener('storage', handleStorageChange);
    emitter.on('announcementShowChange', handleStatusChange);
   
})

onUnmounted(() => {
    window.removeEventListener('scroll', checkFirstSection);
    
  window.removeEventListener('storage', handleStorageChange);
  emitter.off('announcementShowChange', handleStatusChange);
})

// 监听数据加载完成
watch(() => productSections.value, () => {
    if (productSections.value.length > 0) {
        nextTick(() => {
            // 数据加载完成后，如果没有hash，确保第一个锚点激活
            if (!route.hash) {
                isFirstSectionActive.value = true
            }
        });
    }
}, { immediate: true });

// 检查登录状态
const checkStatus = () => {
  const announcementShow = localStorage.getItem('announcementShow');
  affixOffset.value = (announcementShow== true || announcementShow == 'true') ? 112 : 64;
};

// 监听 localStorage 变化
const handleStorageChange = (e) => {
  if (e.key === 'announcementShow') {
    checkStatus();
  }
};
// 监听登录状态变化事件
const handleStatusChange = () => {
    checkStatus();
};

</script>
<style lang="scss">
    .mall-view{
        .mall-anchor{
            padding:0.5rem !important;
        
            .el-anchor__marker {
                display: none;
            }
            .el-anchor__list{
                padding-left: 0 !important;
            }
            .el-anchor__item{
                position: relative;
                align-items: center;
              
            
                
                .el-anchor__link {
                    &:hover {
                        --tw-bg-opacity: 1;
                        background-color: #f9fafb;
                        border-radius: .25rem;
                    }
                    font-size: 0.875rem;
                    width: 100%;
                    padding-left: 0.8rem;
                    font-size: .875rem;
                    line-height: 1.25rem;
                    color: #4b5563;
                    position: relative;
                    padding-top: 5px;
                    padding-bottom: 5px;
                    &::before{
                        content: '';
                        position: absolute;
                        left: 0;
                        top: 50%;
                        transform: translateY(-50%);
                        width:  .375rem;
                        height:  .375rem;
                        border-radius: 50%;
                        background-color: #374151;
                    }

                    &.is-active {
                        color: #2563eb;
                        font-weight: 500;
                        &::before{
                            background-color: #2563eb;
                        }
                    }
                }
                &.is-active{
                    .el-anchor__link{
                        color: #2563eb;
                        font-weight: 500;
                        &::before{
                            background-color: #2563eb;
                        }
                    }
                }
            }
        }
        .mobile-anchor{
            display: block;
            background:transparent;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom:2px;
            .el-anchor__item{
                overflow: visible;
                .el-anchor__link{
                    display: inline-block;
                    padding: 0.5rem 1rem;
                    border-radius: 9999px;
                    background-color: rgba(243, 244, 246, 0.8);
                    
                    backdrop-filter: blur(4px);
                    color: #374151;
                    font-size: .875rem;
                    line-height: 1.25rem;
                    &.is-active{
                        background-color: #2563eb;
                        color: #ffffff;
                    }
                }                
                &.is-active{
                    .el-anchor__link{
                        background-color: #2563eb;
                        color: #ffffff;
                    }
                }
            }
            
        }
    }

</style>
<style scoped lang="scss">
.mall-view{
    width: 100%;
    height: 100%;
    background-image: linear-gradient(to bottom right, #f9fafb, #eff6ff);
    .search-box{
        background-image: linear-gradient(to bottom right, #3b82f6, #2563eb,#4f46e5 );
        overflow: hidden;
        .left-position{
            position: absolute;
            left: -8rem;
            bottom: -8rem;
            width: 16rem;
            height: 16rem;
            border-radius: 50%;
            background-color: rgba(129, 140, 248 , 0.2);
        }
        .right-position{position: absolute;
            right: -12rem;
            top: -12rem;
            width: 24rem;
            height: 24rem;
            border-radius: 50%;
            background-color: rgba(255, 255, 255 , 0.05);
        }
        .search-input-box{
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            .input-box{
                max-width: 36rem;
                width: 100%;
                margin: 0 auto;
                border-radius: .75rem;
                background-color: #fff;                
                box-sizing: border-box;
                position: relative;
                .search-input{
                    width: 100%;
                    border: none;
                    padding-top: 10px;
                    padding-bottom: 10px;
                    border: none;
                    outline: none;
                    border-radius: .75rem;
                    font-size: 1rem;
                    line-height: 1.5rem;
                    height: 3.5rem;
                    padding-left: 1rem;
                    padding-right: 2.5rem;
                }
                .search-icon{
                    position: absolute;
                    right: 1rem;
                    top: 50%;
                    transform: translate(0, -50%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 2.5rem;
                    background: #ff0;
                    width: 2.5rem;
                    padding: 10px 0;
                    border-radius: .75rem;
                    background-image: linear-gradient(to right, #3b82f6, #6366f1);
                }
            }
        }
    }
    .mall-anchor {
        display: none;
        position: fixed;
        right: 20px;
        top: 45%;
        transform: translateY(-50%);
        width: 150px;
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
        z-index: 999;
        backdrop-filter: blur(4px);
        &:hover{
            background: #fff;
            .anchor-title{
                border-bottom: 1px solid #e5e7eb;
            }
        }
        .anchor-title {
            font-size: .875rem;
            line-height: 1.25rem;
            color: #1f2937;
            font-weight: 500;
            margin-bottom: 0.25rem;
            padding:0.25rem;
            text-align: center;
            border-bottom:1px solid #f3f4f6;
        }

        // :deep(.el-anchor) {
           
        // }
    }
    .mall-product-item{
        --tw-space-y-reverse: 0;
        margin-top: calc(2rem * calc(1 - var(--tw-space-y-reverse)));
        margin-bottom: calc(2rem * var(--tw-space-y-reverse));
    }
    .no-margin-top{
        margin-top: 0;
    }
    .about-us{ 
        .about-title{
            font-size: 1.125rem;
            line-height: 1.75rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color:#1f2937;
            text-align: center;
        }
        .about-content{
            gap: 0.75rem;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            display: grid;
            .about-item{
                padding:0.75rem;
                background-color: #fff;
                border:solid 1px #e5e7eb;
                border-radius: 0.5rem;
                &:hover{
                    --tw-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1), 0 2px 4px -2px rgba(0, 0, 0, .1);
                    --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);
                    box-shadow: var(--tw-ring-offset-shadow,0 0 #0000),var(--tw-ring-shadow,0 0 #0000),var(--tw-shadow);
                }                
            }
        }
    }
   
    @media (min-width: 768px) {
        .mall-anchor{
            display: block;
        }
        .mobile-anchor-box{
            display: none;
        }
    }
    @media (min-width: 640px) {
      
        .mall-product-item {
            --tw-space-y-reverse: 0;
            margin-top: calc(2rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(2rem * var(--tw-space-y-reverse));
        }        
        .no-margin-top{
            margin-top: 0;
        }
        .about-us{
            .about-title{
                font-size: 1.25rem;
                line-height: 1.75rem;
                margin-bottom: 1.5rem;
            }
            .about-content{
                gap: 1rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                display: grid;
                .about-item{
                    padding:1rem;
                }
            }
        }
    }
    @media(min-width: 1024px){
        .about-us {
            .about-content{
                gap: 1rem;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                display: grid;
            }
        }
        
        
    }
}
</style>