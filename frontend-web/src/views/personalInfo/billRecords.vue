<template>
    <div class="profile-box space-y-6">
      <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">账单记录</h2>
        <p class="text-sm text-gray-500">查看您的所有账户资金变动记录</p>
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
          <el-table-column prop="order_number" label="类型" width="100">
            <template #default="scope">
                <span class=" font-medium text-gray-900">{{scope.row.type}}</span>
            </template>
          </el-table-column>
          <el-table-column prop="product_name" label="变动金额" >
            <template #default="scope">
                <span class="text-primary font-medium" v-if="scope.row.direction == 'in'">+{{ scope.row.amount }}</span>
                <span class="text-red-600 font-medium" v-if="scope.row.direction == 'out'">-{{ scope.row.amount }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="after_balance" label="账户余额">
            <template #default="scope">
                <span class="text-gray-900">¥{{scope.row.after_balance}}</span>
            </template>
          </el-table-column>
          <el-table-column prop="order_no" label="关联订单" >
            <template #default="scope">
                <span class="text-gray-900">{{scope.row.order_no}}</span>
            </template>
            </el-table-column>
         
          <el-table-column prop="created_at" label="时间" width="160" />
          
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
      const res = await orderApi.balanceLog(data);
      if (res.code === 1) {
        tableData.value = res.data.list;
        query.value.total = res.data.total;
      } else {
        ElMessage.error(res.msg || "获取订单列表失败");
      }
    } catch (error) {
      console.error("获取订单列表失败:", error);
      ElMessage.error("获取订单列表失败，请重试");
    } finally {
      loading.value = false;
    }
  };
  
  const handlePageChange = (page) => {
    query.value.page = page;
    getOrderList();
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
  
  </style>
  