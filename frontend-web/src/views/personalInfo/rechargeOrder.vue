<template>
    <div class="profile-box space-y-6">
      <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">充值记录</h2>
        <p class="text-sm text-gray-500">查看您的所有充值记录</p>
      </div>
      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <el-table
          :data="tableData"
          style="width: 100%"
          max-height="calc(100vh - 200px - 8rem)"
          v-loading="loading"
          element-loading-text="加载中..."
          element-loading-background="rgba(255, 255, 255, 0.8)"
          size="large"
        >
          <el-table-column prop="order_number" label="订单号" width="220px">
            <template #default="scope">
                <span class="mr-1 text-gray-900">{{ scope.row.order_no }}</span>
                <i class="iconfont icon-copy text-gray-400 copy-btn cursor-pointer" @click="copyData(scope.row.order_no)"></i>
            </template>
          </el-table-column>
          <el-table-column prop="product_name" label="金额" >
            <template #default="scope">
                <span class=" font-medium text-gray-900">¥{{ scope.row.recharge_amount }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="status" label="状态" width="120px">
            <template #default="scope">
                <tags v-if="scope.row.status == '0'"  class="status-tag" bgColor="#fef9c3" textColor="#854d0e" rounded="rounded-full">待支付</tags>
                <tags v-else-if="scope.row.status == '1'" class="status-tag" bgColor="#dbeafe" textColor="#3b82f6" rounded="rounded-full">已完成</tags>
                <tags v-else-if="scope.row.status == '2'"  class="status-tag" bgColor="#fee2e2" textColor="#991b1b" rounded="rounded-full">已退款</tags>
                <tags v-else class="status-tag" bgColor="#f4f4f5" textColor="#909399" rounded="rounded-full">已取消</tags>             
            </template>
          </el-table-column>
          <el-table-column prop="payment_method_text" label="支付方式">
            <template v-slot="scope"> 
              <span class="text-gray-900">{{ scope.row.payment_method_text }}</span>
            </template>
          </el-table-column>
         
          <el-table-column prop="created_at" label="时间" width="180" />
          
        </el-table>
      </div>
      <div class="pagination-container">
        <el-pagination
          background
          layout=" prev, pager, next, total"
          :total="query.total"
          :page-size="query.limit"
          :current-page="query.page"
          :page-sizes="query.pageSizes"
          @current-change="handlePageChange"
        />
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from "vue";
  import { orderApi } from "@/api";
  import { ElMessage } from "element-plus";
  import tags from "@/components/tags.vue"
  
  const loading = ref(false);
  const tableData = ref([]);
  const query=ref({
    page: 1,
    limit: 10,
    total:0
  })
 
  const getOrderList = async () => {
    try {
      loading.value = true;
      let data = {
        page: query.value.page,
        limit: query.value.limit
      }
      const res = await orderApi.rechargeList(data);
      loading.value = false;
      if (res.code === 1) {
        tableData.value = res.data.list;
        query.value.total = res.data.total;
      } else {
        ElMessage.error(res.msg || "获取订单列表失败");
      }
    } catch (error) {
      console.error("获取订单列表失败:", error);
      ElMessage.error("获取订单列表失败，请重试");
      loading.value = false;
    } finally {
      loading.value = false;
    }
  };
  
const handlePageChange = (page) => {
  query.value.page = page;
  getOrderList();
};
  const copyData = (text) => { 
    if (text) {
        navigator.clipboard.writeText(text).then(() => {
            ElMessage.success('复制成功')
        }).catch(() => {
            ElMessage.error('复制失败')
        })
    }
  };
  
  
  onMounted(() => {
    getOrderList();
  });
  </script>
  <style lang="scss"> 
  
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
  <style scoped lang="scss">
  .profile-box {
    .el-table {
      :deep(.el-table__header) {
        th {
          background-color: #f9fafb;
          color: #6b7280;
          font-weight: 400;
          font-size: .75rem;
          line-height: 1rem;
        }
      }
      :deep(.el-table__row) {
        td {
          color: #374151;
        }
      }
    }
  }
  .copy-btn{
    
    &:hover{
        color:#4b5563;
    }
    
  }
  
  </style>
  