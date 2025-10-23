<template>
    <div v-if="orderInfo?.card_info?.length > 0" ref="tableRef" class=" w-full account-info bg-white p-3-5 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-3 sm:mb-4">
            <div>

                <h3 class="text-base sm:text-lg font-medium text-gray-800">
                    <i class="icon-key iconfont text-blue-600 mr-2" style="font-size: 18px;"></i>
                    {{ titleName }}
                </h3>
                <!-- <p class="text-xs text-gray-500 mt-1">{{ orderInfo?.product_name || '--' }} - 共 {{ total }} 个账号</p> -->
            </div>
            <el-button v-if="!isCopy"
                type="primary" 
                link 
                class="text-blue-600 hover:text-blue-700 text-xs sm:text-sm flex items-center"
                @click="handleCopyAll"
            >
                <i class="iconfont icon-clipboard mr-1" style="font-size: 14px;"></i>
                <!-- <el-icon color="var(--primary-color)"><List /></el-icon> -->
                复制全部
            </el-button>  
            <el-button v-else
                type="primary"
                link 
                class=" text-blue-600 hover:text-blue-700 text-xs sm:text-sm flex items-center">
                <i class="iconfont icon-check mr-1" style="font-size: 14px;"></i>
                复制成功
            </el-button>          
        </div>
        <div class="table-container">
            <el-table 
                :data="currentPageData" 
                :row-class-name="getRowClass" 
                :cell-class-name="getCellClass" 
                :header-row-class-name="getHeaderClass"
                table-layout="fixed"
            >
                <el-table-column prop="id" label="序号" width="70"/>
                <el-table-column prop="card_info" label="卡密信息">
                    <template #default="scope">
                        <div class='font-medium break-all text-xs sm:text-sm'>
                            <span class="default-color">{{ scope.row.card_info }}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="70">
                    <template #default="scope">
                        <i class="iconfont icon-clipboard text-blue-600" style="font-size: 14px;" @click="copyOrderNumber(scope.row.card_info)"></i>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class=" flex items-center justify-end border-t border-gray-200 px-2 sm:px-3.5 py-3 mt-3">
            <div class="pagination-container ">
                <el-pagination
                background
                layout="prev, pager, next"
                :total="total"
                :page-size="pageSize"
                :current-page="currentPage"
                @current-change="handlePageChange"
                />
            </div>
            <!-- <div class="ml-2 text-gray-500 text-xs">共{{ total }}条，{{ Math.ceil(total / pageSize) }}页</div> -->
        </div>
        <div v-if="hasTip && orderInfo?.email" class="mt-3.5">
            <p class="text-sm text-primary flex items-center justify-center">
                <i class="iconfont icon-check-circle text-secondary mr-2" style="font-size: 14px;"></i>您的账号信息已发送至您的邮箱: {{orderInfo?.email || '--'}}
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, defineProps, watch } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps({
    orderInfo: {
        type: Object,
        default: () => ({})
    },
    hasTip: {
        type: Boolean,
        default: false
    },
    titleName:{
        type: String,
        default: '账号信息'
    }
})

const tableRef = ref(null)

// const cardInfo = computed(() => {
//     return props.orderInfo?.card_info || []
// })
// 分页相关
const currentPage = ref(1);
const pageSize = ref(8);
const total = computed(() => props.orderInfo?.card_info?.length || 0);
const currentPageData = computed(() => {
  if (!props.orderInfo?.card_info) return [];
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return props.orderInfo?.card_info.slice(start, end);
});

// 监听orderInfo变化，重置分页
watch(() => props.orderInfo, () => {
    currentPage.value = 1;
}, { deep: true });

//复制
const isCopy = ref(false);

const handlePageChange = (page) => {
  currentPage.value = page;
};

const getRowClass = (row) => {
    if(row.rowIndex % 2 === 0){
        return 'single-row'
    } else {
        return 'double-row'
    }
}

const getCellClass = () => {
    return 'content-col'
}

const getHeaderClass = (row) => {
    if(row.rowIndex === 0){
        return 'header-class'
    }
}

const handleCopyAll = () => {

    const text = currentPageData.value.map(item => item.card_info).join('\n')
    navigator.clipboard.writeText(text).then(() => {
        isCopy.value = true;
        setTimeout(() => {
            isCopy.value = false;
        }, 3000)
        ElMessage.success('复制成功')
    }).catch(() => {
        isCopy.value = false;
        ElMessage.error('复制失败')
    })
}
const copyOrderNumber = (text) => {
    if (text) {
        navigator.clipboard.writeText(text).then(() => {
            isCopy.value = true;
            setTimeout(() => {
                isCopy.value = false;
            }, 3000)
            ElMessage.success('复制成功')
        }).catch(() => {
            isCopy.value = false;
            ElMessage.error('复制失败')
        })
    }
}
</script>

<style lang="scss">
    .double-row{
        background-color: #ffffff !important;
        &:hover{
            background-color: #f3f4f6 !important;
        }
    }
    .single-row{
        background-color:#f9fafb !important;
        &:hover{
            background-color: #f3f4f6 !important;
        }
    }
    .header-class{
        color:#6b7280 !important;
        th{
            background-color: #f3f4f6 !important;
        }
        
    }
    .first-col{
        width:4rem !important;
    }
    .content-col{
        padding:0.5rem 0 !important;
    }
    @media (min-width: 640px) {
    .content-col {
        padding: .75rem 0rem !important;
    }
}
.el-table {
    width: 100% !important;
    table-layout: fixed !important;
    
    .el-table__inner-wrapper {
        width: 100% !important;
    }
    
    .el-table__body,
    .el-table__header {
        width: 100% !important;
        table {
            width: 100% !important;
        }
    }
    
    .el-table__body-wrapper {
        width: 100% !important;
    }
    
    .el-table__header-wrapper {
        width: 100% !important;
    }
    
    .el-table__cell {
        box-sizing: border-box;
    }
}
.table-container {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    width: 100%;
    
    .el-table {
        width: 100%;
        table-layout: fixed;
        
        .el-table__inner-wrapper {
            width: 100%;
        }
        
        .el-table__body,
        .el-table__header {
            width: 100%;
            table {
                width: 100%;
            }
        }
        
        .el-table__body-wrapper {
            width: 100%;
        }
        
        .el-table__header-wrapper {
            width: 100%;
        }
        
        .el-table__cell {
            box-sizing: border-box;
        }
    }
}
.account-info{
    // width: 100%;
    .el-button.is-link{
        color:#2563eb;
    }
    .el-button.is-link:hover{
        color:#1d4ed8
    }
}
@media (max-width: 1024px) {
    .table-wrapper {
        width: 100%;
        .el-table {
            width: 100% !important;
        }
    }
}
</style>
<style scoped lang="scss">
.account-info{
    // width: 100%;
    .el-button.is-link:hover{
        color:#1d4ed8
    }
}
.pagination-container {
  margin-top: 1rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  :deep(.el-pagination) {
    .el-pager li {
      background-color: #f3f4f6;
      &:hover {
        color: var(--primary-color);
      }
      &.is-active {
        background-color: var(--primary-color);
        color: white;
      }
    }
    .btn-prev, .btn-next {
      background-color: #f3f4f6;
      &:hover {
        color: var(--primary-color);
      }
    }
  }
}

</style>