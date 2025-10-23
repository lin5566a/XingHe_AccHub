<template>
  <div class="profile-box space-y-6">
    <div class="mb-6">
      <h2 class="text-lg font-medium text-gray-900">订单记录</h2>
      <p class="text-sm text-gray-500">查看您的所有购买订单</p>
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
        <el-table-column prop="order_number" label="订单号" min-width="170" />
        <el-table-column prop="product_name" label="商品名" min-width="145" />
        <el-table-column prop="price" label="单价">
          <template #default="scope"> ¥{{ scope.row.price }} </template>
        </el-table-column>
        <el-table-column prop="quantity" label="数量" />
        <el-table-column prop="total_price" label="金额">
          <template #default="scope"> <span class="text-teal-600 font-medium">¥{{ scope.row.total_price }} </span> </template>
        </el-table-column>
        <el-table-column prop="created_at" label="下单时间" width="160" />
        <el-table-column prop="status" label="状态" min-width="130">
          <template #default="scope">
            <Tags
              :tag="scope.row.status_text"
              rounded="rounded-full"
              :bgColor="
                scope.row.status == 3
                  ? '#dbeafe'
                  : scope.row.status == 5
                  ? '#fee2e2'
                  : '#fef9c3'
              "
              :textColor="
                scope.row.status == 3
                  ? '#3b82f6'
                  : scope.row.status == 5
                  ? '#991b1b'
                  : '#854d0e'
              "
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100" fixed="right">
          <template #default="scope">
            <Tags
              class="look-tag cursor-pointer"
              tag="查看"
              bgColor="rgba(0,150,136,0.1)"
              textColor="#009688"
              v-if="scope.row.status == 3"
              @click="handleDetail(scope.row)"
            />
          </template>
        </el-table-column>
      </el-table>
    </div>
    <el-dialog
      class="order-detail-dialog"
      v-model="dialogVisible"
      title="订单详情"
      :close-on-click-modal="false"
      modal-class="protocol-modal"
      header-class="protocol-header"
      body-class="protocol-body"
    >
      <div class="dialog-content">
        <div class="title flex justify-end mb-2">
          <div @click="copyAllCardInfo" class="primary-color hover:text-primary-dark cursor-pointer copy-btn text-sm">
            <i class="iconfont icon-copy "></i> <span>复制</span>
          </div>

          <Tags
            v-if="showCopySuccess"
            tag="复制成功！"
            bgColor="#dbeafe"
            textColor="#2563eb"
          />
        </div>
        <div class="card-list">
          <el-table
            :data="currentPageData"
            style="width: 100%"
            :row-class-name="getRowClass"
            :cell-class-name="getCellClass"
            :header-row-class-name="getHeaderClass"
          >
            <el-table-column prop="id" label="序号" width="90px" >
              <template #default="scope">
                <span class="text-sm text-left font-medium">{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="卡密信息">
              <template #default="scope">
                <div class="card-item">
                  <span class="card-text text-left font-medium text-sm default-color">{{ scope.row.card_info }}</span>
                </div>
              </template>
            </el-table-column>
          </el-table>
          <div class="pagination-container">
            <el-pagination
              background
              layout="prev, pager, next"
              :total="total"
              :page-size="pageSize"
              :current-page="currentPage"
              @current-change="handlePageChange"
            />
            <div class="ml-2 text-gray-500 text-xs">共{{ total }}条，{{ Math.ceil(total / pageSize) }}页</div>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { orderApi } from "@/api";
import { ElMessage } from "element-plus";
import Tags from "@/components/tags.vue";

const loading = ref(false);
const tableData = ref([]);
const dialogVisible = ref(false);
const orderDetail = ref({});
const showCopySuccess = ref(false);
let copyTimer = null;

// 分页相关
const currentPage = ref(1);
const pageSize = ref(8);
const total = computed(() => orderDetail.value.card_info?.length || 0);

const currentPageData = computed(() => {
  if (!orderDetail.value.card_info) return [];
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return orderDetail.value.card_info.slice(start, end);
});

const handlePageChange = (page) => {
  currentPage.value = page;
};

const handleDetail = async (row) => {
  dialogVisible.value = true;
  orderDetail.value = row
  currentPage.value = 1; // 重置页码
};

const copyAllCardInfo = () => {
  if (!orderDetail.value.card_info || !orderDetail.value.card_info.length) {
    ElMessage.warning("没有可复制的卡密信息");
    return;
  }
  const allCardInfo = orderDetail.value.card_info.map((item) => item.card_info).join("\n");
  navigator.clipboard.writeText(allCardInfo).then(() => {
    showCopySuccess.value = true;
    if (copyTimer) clearTimeout(copyTimer);
    copyTimer = setTimeout(() => {
      showCopySuccess.value = false;
    }, 3000);
  }).catch((err) => {
    console.error("复制失败:", err);
    ElMessage.error("复制失败，请重试");
  });
};

const getOrderList = async () => {
  try {
    loading.value = true;
    const res = await orderApi.getOrderList();    
    loading.value = false;
    if (res.code === 1) {
      tableData.value = res.data.list;
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

const getRowClass = (row) => {
  if (row.rowIndex % 2 === 0) {
    return "single-row";
  } else {
    return "double-row";
  }
};

const getCellClass = () => {
  return "content-col";
};

const getHeaderClass = (row) => {
  if (row.rowIndex === 0) {
    return "header-class";
  }
};

onMounted(() => {
  getOrderList();
});
</script>
<style lang="scss">
.protocol-modal {  
  height: 100%;
  .el-overlay-dialog{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }
  .order-detail-dialog{
    padding: 0;
    margin: 0;
    max-width: 42rem;
    width: 90%;
  }
  .protocol-header {
    display: flex;
    justify-content: space-between;
    font-size: 1.125rem;
    line-height: 1.75rem;
    font-weight: 500;
    color: #374151;
    padding: 1.5rem;
    border-bottom-width: 1px;
  }
  .protocol-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    .copy-btn{
        &:hover{
            color: var(--primary-dark);
        }   
    }
  }
  .double-row {
    background-color: #ffffff !important;
    &:hover {
      background-color: #f3f4f6 !important;
    }
  }
  .single-row {
    background-color: #f9fafb !important;
    &:hover {
      background-color: #f3f4f6 !important;
    }
  }
  .header-class {
    color: #6b7280 !important;
    th {
      background-color: #f3f4f6 !important;
    }
  }
  .first-col {
    width: 4rem !important;
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

.look-tag {
  padding: 0.25rem 0.75rem;
  &:hover {
    background-color: rgba(0, 150, 136, 0.2) !important;
  }
}

.dialog-content {
  .title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: #6b7280;
    cursor: pointer;
    &:hover {
      color: #009688;
    }
  }

  .card-list {
    .card-item {
      .card-text {
        word-break: break-all;
      }
    }
  }
}

:deep(.el-table) {
  .even-row {
    background-color: #f9fafb;
  }
  .odd-row {
    background-color: #ffffff;
  }
  .card-info-cell {
    padding: 8px 0;
  }
  .custom-header {
    th {
      background-color: #f9fafb;
      color: #374151;
      font-weight: 500;
    }
  }
}
</style>
