<template>
  <Layout>
    <div class="courses-container">
      <!-- 页面标题和操作 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">课程管理</h1>
          <p class="page-subtitle">管理您的课程</p>
        </div>
      </div>

      <!-- 搜索和筛选 -->
      <div class="search-section">
        <div class="search-left">
          <el-input
            v-model="searchForm.keyword"
            placeholder="搜索课程名称或描述"
            class="search-input"
            clearable
            @keyup.enter="handleSearch"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <el-select v-model="searchForm.status" placeholder="课程状态" clearable class="status-select">
            <el-option label="全部状态" value="" />
            <el-option label="进行中" value="1" />
            <el-option label="已结束" value="0" />
          </el-select>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="resetSearch">重置</el-button>
        </div>
        <div class="search-right">
          <el-button @click="refreshData">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </div>

      <!-- 课程列表 -->
      <div class="courses-content">
        <el-table
          v-loading="loading"
          :data="coursesList"
          stripe
          class="courses-table"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="name" label="课程名称" min-width="200">
            <template #default="{ row }">
              <div class="course-name-cell">
                <div class="course-name">{{ row.name }}</div>
                <div class="course-desc">{{ row.description }}</div>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="category" label="分类" width="120" />
          <el-table-column prop="status" label="状态" width="100">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)">
                {{ getStatusText(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="创建时间" width="180">
            <template #default="{ row }">
              {{ formatDate(row.created_at) }}
            </template>
          </el-table-column>
          <el-table-column prop="updated_at" label="更新时间" width="180">
            <template #default="{ row }">
              {{ formatDate(row.updated_at) }}
            </template>
          </el-table-column>
          <el-table-column fixed="right" label="操作" width="200">
            <template #default="scope">
              <el-button link @click="viewCourse(scope.row.id)">
                <el-icon><View /></el-icon>
                查看
              </el-button>
              <el-button link @click="editCourse(scope.row.id)">
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button 
                link
                type="danger"
                @click="deleteCourse(scope.row.id)"
              >
                <el-icon><Delete /></el-icon>
                删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <!-- 分页 -->
        <div class="pagination-wrapper">
          <el-pagination
            v-model:current-page="pagination.page"
            v-model:page-size="pagination.pageSize"
            :page-sizes="[10, 20, 50, 100]"
            :total="pagination.total"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>

      <!-- 空状态 -->
      <div v-if="!loading && coursesList.length === 0" class="empty-state">
        <el-icon><Document /></el-icon>
        <h3>暂无课程</h3>
        <p>您还没有任何课程记录</p>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  Plus, 
  Search, 
  Refresh, 
  View, 
  Edit, 
  Delete, 
  Document 
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { teacherApi } from '@/api/user'

const router = useRouter()

// 响应式数据
const loading = ref(false)
const coursesList = ref([])
const searchForm = reactive({
  keyword: '',
  status: ''
})
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 获取课程列表
const loadCourses = async () => {
  try {
    loading.value = true
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize,
      keyword: searchForm.keyword,
      status: searchForm.status
    }
    
    const response = await teacherApi.getCourses(params)
    if (response.code === 200) {
      coursesList.value = response.data.list || []
      pagination.total = response.data.total || 0
    }
  } catch (error) {
    console.error('获取课程列表失败：', error)
    ElMessage.error('获取课程列表失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadCourses()
}

// 重置搜索
const resetSearch = () => {
  searchForm.keyword = ''
  searchForm.status = ''
  pagination.page = 1
  loadCourses()
}

// 刷新数据
const refreshData = () => {
  loadCourses()
}

// 分页处理
const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  loadCourses()
}

const handleCurrentChange = (page) => {
  pagination.page = page
  loadCourses()
}

// 格式化日期
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

// 获取状态类型
const getStatusType = (status) => {
  const typeMap = {
    draft: 'info',
    published: 'success',
    archived: 'warning'
  }
  return typeMap[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const textMap = {
    draft: '草稿',
    published: '已发布',
    archived: '已归档'
  }
  return textMap[status] || status
}

// 页面跳转方法
const viewCourse = (id) => {
  router.push(`/teacher/courses/${id}`)
}

const editCourse = (id) => {
  router.push(`/teacher/courses/${id}/edit`)
}

const deleteCourse = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个课程吗？删除后无法恢复。', '确认删除', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const response = await teacherApi.deleteCourse(id)
    if (response.code === 200) {
      ElMessage.success('删除成功')
      loadCourses()
    } else {
      ElMessage.error(response.message || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除课程失败：', error)
      ElMessage.error('删除失败')
    }
  }
}

// 生命周期
onMounted(() => {
  loadCourses()
})
</script>

<style scoped>
.courses-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* 页面标题 */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin: 0;
}

.page-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

/* 搜索区域 */
.search-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-left {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-input {
  width: 300px;
}

.status-select {
  width: 150px;
}

/* 课程内容 */
.courses-content {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.courses-table {
  width: 100%;
}

.course-name-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.course-name {
  font-weight: 500;
  color: #303133;
}

.course-desc {
  font-size: 12px;
  color: #909399;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* 分页 */
.pagination-wrapper {
  padding: 20px;
  display: flex;
  justify-content: center;
}

/* 空状态 */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.empty-state .el-icon {
  font-size: 64px;
  color: #c0c4cc;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  color: #606266;
  margin: 0 0 8px;
}

.empty-state p {
  font-size: 14px;
  color: #909399;
  margin: 0 0 20px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .search-section {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .search-left {
    flex-direction: column;
    gap: 12px;
  }
  
  .search-input {
    width: 100%;
  }
  
  .status-select {
    width: 100%;
  }
}
</style> 