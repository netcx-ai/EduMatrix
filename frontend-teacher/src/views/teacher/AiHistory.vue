<template>
  <Layout>
    <div class="ai-history-container">
      <!-- 页面标题 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">AI使用历史</h1>
          <p class="page-subtitle">查看所有AI工具的使用记录</p>
        </div>
        <div class="header-right">
          <el-button @click="refreshData">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </div>

      <!-- 筛选条件 -->
      <div class="filter-section">
        <el-form :model="filterForm" inline>
          <el-form-item label="工具类型">
            <el-select v-model="filterForm.tool_code" placeholder="选择工具" clearable>
              <el-option
                v-for="tool in toolOptions"
                :key="tool.value"
                :label="tool.label"
                :value="tool.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="filterForm.status" placeholder="选择状态" clearable>
              <el-option label="成功" value="success" />
              <el-option label="失败" value="failed" />
              <el-option label="处理中" value="processing" />
            </el-select>
          </el-form-item>
          <el-form-item label="时间范围">
            <el-date-picker
              v-model="filterForm.date_range"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">
              <el-icon><Search /></el-icon>
              搜索
            </el-button>
            <el-button @click="resetFilter">
              <el-icon><Refresh /></el-icon>
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 统计信息 -->
      <div class="stats-section">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-number">{{ stats.total_records || 0 }}</div>
            <div class="stat-label">总记录数</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ stats.success_count || 0 }}</div>
            <div class="stat-label">成功次数</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ stats.failed_count || 0 }}</div>
            <div class="stat-label">失败次数</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ stats.today_count || 0 }}</div>
            <div class="stat-label">今日使用</div>
          </div>
        </div>
      </div>

      <!-- 历史记录表格 -->
      <div class="table-section">
        <el-table
          v-loading="loading"
          :data="historyList"
          stripe
          style="width: 100%"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="tool_name" label="工具名称" width="150">
            <template #default="{ row }">
              <div class="tool-info">
                <el-icon class="tool-icon">
                  <component :is="getToolIcon(row.tool_name)" />
                </el-icon>
                <span>{{ row.tool_name }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="status" label="状态" width="100">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)" size="small">
                {{ getStatusText(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="request_content" label="请求内容" min-width="200">
            <template #default="{ row }">
              <div class="content-preview">
                {{ truncateText(row.prompt_preview || row.request_data, 50) }}
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="response_content" label="响应内容" min-width="200">
            <template #default="{ row }">
              <div class="content-preview">
                {{ truncateText(row.content_preview || row.response_data, 50) }}
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="创建时间" width="180">
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>
          <el-table-column label="操作" width="120" fixed="right">
            <template #default="{ row }">
              <el-button 
                link 
                type="primary" 
                @click="viewDetail(row)"
              >
                查看详情
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <!-- 分页 -->
        <div class="pagination-wrapper">
          <el-pagination
            :current-page="pagination.page"
            :page-size="pagination.page_size"
            :page-sizes="[10, 20, 50, 100]"
            :total="pagination.total"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>

      <!-- 详情对话框 -->
      <el-dialog
        v-model="detailDialog.visible"
        title="使用详情"
        width="80%"
        :before-close="() => detailDialog.visible = false"
      >
        <div v-if="detailDialog.data" class="detail-content">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="记录ID">{{ detailDialog.data.id }}</el-descriptions-item>
            <el-descriptions-item label="工具名称">{{ detailDialog.data.tool_name }}</el-descriptions-item>
            <el-descriptions-item label="状态">
              <el-tag :type="getStatusType(detailDialog.data.status)">
                {{ getStatusText(detailDialog.data.status) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="创建时间">{{ formatDate(detailDialog.data.created_at) }}</el-descriptions-item>
          </el-descriptions>

          <div class="detail-section">
            <h4>请求参数</h4>
            <el-input
              :model-value="detailDialog.data.request_data || detailDialog.data.prompt_preview || ''"
              type="textarea"
              :rows="6"
              readonly
            />
          </div>

          <div class="detail-section">
            <h4>响应结果</h4>
            <el-input
              :model-value="detailDialog.data.response_data || detailDialog.data.content_preview || ''"
              type="textarea"
              :rows="10"
              readonly
            />
          </div>

          <div v-if="detailDialog.data.error_message" class="detail-section">
            <h4>错误信息</h4>
            <el-alert
              :title="detailDialog.data.error_message"
              type="error"
              show-icon
            />
          </div>
        </div>
      </el-dialog>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { 
  Refresh, 
  Search, 
  Document, 
  MagicStick,
  Edit,
  VideoPlay,
  Picture,
  Microphone,
  ChatDotRound
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { aiToolApi } from '@/api/aiTool'

const router = useRouter()

// 响应式数据
const loading = ref(false)
const historyList = ref([])
const stats = ref({})
const toolOptions = ref([])

// 筛选表单
const filterForm = reactive({
  tool_code: '',
  status: '',
  date_range: []
})

// 分页
const pagination = reactive({
  page: 1,
  page_size: 20,
  total: 0
})

// 详情对话框
const detailDialog = reactive({
  visible: false,
  data: null
})

// 获取工具图标
const getToolIcon = (toolName) => {
  const iconMap = {
    '文档生成': Document,
    '视频生成': VideoPlay,
    '图片生成': Picture,
    '语音生成': Microphone,
    '对话助手': ChatDotRound,
    '内容编辑': Edit
  }
  return iconMap[toolName] || MagicStick
}

// 获取状态类型
const getStatusType = (status) => {
  const typeMap = {
    'success': 'success',
    'failed': 'danger',
    'processing': 'warning'
  }
  return typeMap[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const textMap = {
    'success': '成功',
    'failed': '失败',
    'processing': '处理中'
  }
  return textMap[status] || status
}

// 截断文本
const truncateText = (text, length = 50) => {
  if (!text) return '-'
  return text.length > length ? text.substring(0, length) + '...' : text
}

// 格式化日期
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      limit: pagination.page_size,
      ...filterForm
    }
    
    // 处理日期范围
    if (filterForm.date_range && filterForm.date_range.length === 2) {
      params.start_date = filterForm.date_range[0]
      params.end_date = filterForm.date_range[1]
    }
    
    // 处理工具筛选
    if (filterForm.tool_code) {
      params.tool_code = filterForm.tool_code
    }
    
    // 处理状态筛选
    if (filterForm.status) {
      params.status = filterForm.status
    }
    
    const response = await aiToolApi.getHistory(params)
    
    if (response.code === 200) {
      historyList.value = response.data.list || []
      pagination.total = response.data.total || 0
      
      // 计算统计信息
      stats.value = {
        total_records: response.data.total || 0,
        success_count: historyList.value.filter(item => item.status === 'success').length,
        failed_count: historyList.value.filter(item => item.status === 'failed').length,
        today_count: historyList.value.filter(item => {
          const today = new Date().toDateString()
          const itemDate = new Date(item.created_at).toDateString()
          return today === itemDate
        }).length
      }
    } else {
      ElMessage.error(response.message || '获取历史记录失败')
    }
  } catch (error) {
    console.error('加载历史记录失败:', error)
    ElMessage.error('加载历史记录失败')
  } finally {
    loading.value = false
  }
}

// 加载工具选项
const loadToolOptions = async () => {
  try {
    const response = await aiToolApi.getList()
    if (response.code === 200) {
      toolOptions.value = response.data.list.map(tool => ({
        label: tool.name,
        value: tool.code
      }))
    }
  } catch (error) {
    console.error('加载工具选项失败:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadData()
}

// 重置筛选
const resetFilter = () => {
  Object.assign(filterForm, {
    tool_code: '',
    status: '',
    date_range: []
  })
  pagination.page = 1
  loadData()
}

// 刷新数据
const refreshData = () => {
  loadData()
}

// 分页大小改变
const handleSizeChange = (size) => {
  pagination.page_size = size
  pagination.page = 1
  loadData()
}

// 当前页改变
const handleCurrentChange = (page) => {
  pagination.page = page
  loadData()
}

// 查看详情
const viewDetail = (row) => {
  detailDialog.data = { ...row }
  detailDialog.visible = true
}

// 初始化
onMounted(() => {
  loadToolOptions()
  loadData()
})
</script>

<style scoped>
.ai-history-container {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.filter-section {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 确保Element Plus组件样式正确显示 */
.filter-section :deep(.el-select) {
  min-width: 150px;
}

.filter-section :deep(.el-select .el-input__inner) {
  color: #606266;
}

.filter-section :deep(.el-select .el-input__inner::placeholder) {
  color: #c0c4cc;
}

.filter-section :deep(.el-date-editor) {
  min-width: 240px;
}

.filter-section :deep(.el-form-item__label) {
  color: #606266;
  font-weight: 500;
}

/* 确保在暗色模式下也能正常显示 */
@media (prefers-color-scheme: dark) {
  .filter-section :deep(.el-select .el-input__inner) {
    color: #e5eaf3;
    background-color: #2a2a2a;
    border-color: #3f3f46;
  }
  
  .filter-section :deep(.el-select .el-input__inner::placeholder) {
    color: #a3a6ad;
  }
  
  .filter-section :deep(.el-form-item__label) {
    color: #e5eaf3;
  }
}

.stats-section {
  margin-bottom: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.stat-card {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-number {
  font-size: 28px;
  font-weight: 600;
  color: #409eff;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #909399;
}

.table-section {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.tool-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tool-icon {
  color: #409eff;
}

.content-preview {
  color: #606266;
  line-height: 1.4;
}

.pagination-wrapper {
  padding: 20px;
  display: flex;
  justify-content: center;
}

.detail-content {
  max-height: 70vh;
  overflow-y: auto;
}

.detail-section {
  margin-top: 20px;
}

.detail-section h4 {
  margin: 0 0 12px 0;
  color: #303133;
  font-size: 16px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #909399;
}

.empty-state .el-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
}
</style> 