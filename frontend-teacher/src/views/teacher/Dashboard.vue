<template>
  <Layout>
    <div class="dashboard-container">
      <!-- 欢迎区域 -->
      <div class="welcome-section">
        <div class="welcome-text">
          <h1>欢迎回来，{{ userInfo.name }}</h1>
          <p>今天是 {{ currentDate }}，祝您工作愉快！</p>
        </div>
      </div>

      <!-- 快捷操作区 -->
      <div class="quick-actions">
        <div class="action-item" @click="goToFiles">
          <el-icon><Folder /></el-icon>
          <div class="action-text">文件管理</div>
        </div>
        <div class="action-item" @click="goToContent">
          <el-icon><Document /></el-icon>
          <div class="action-text">内容库</div>
        </div>
        <div class="action-item" @click="goToAiTools">
          <el-icon><Monitor /></el-icon>
          <div class="action-text">AI 工具</div>
        </div>
      </div>

      <!-- 统计数据 -->
      <el-row :gutter="20" class="stats-section">
        <el-col :span="8">
          <el-card shadow="hover">
            <template #header>
              <div class="card-header">
                <span>课程数量</span>
                <el-button link @click="goToCourses">查看全部</el-button>
              </div>
            </template>
            <div class="stats-number">{{ stats.courseCount }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card shadow="hover">
            <template #header>
              <div class="card-header">
                <span>文件数量</span>
                <el-button link @click="goToFiles">查看全部</el-button>
              </div>
            </template>
            <div class="stats-number">{{ stats.fileCount }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card shadow="hover">
            <template #header>
              <div class="card-header">
                <span>AI 使用次数</span>
                <el-button link @click="goToAiTools">查看全部</el-button>
              </div>
            </template>
            <div class="stats-number">{{ stats.aiUsageCount }}</div>
          </el-card>
        </el-col>
      </el-row>

      <!-- 最近课程 -->
      <el-card class="recent-courses" v-if="recentCourses.length > 0">
        <template #header>
          <div class="card-header">
            <span>最近课程</span>
            <el-button link @click="goToCourses">查看全部</el-button>
          </div>
        </template>
        <el-table :data="recentCourses" style="width: 100%">
          <el-table-column prop="name" label="课程名称" />
          <el-table-column prop="students_count" label="学生数量" width="120" />
          <el-table-column prop="last_active" label="最近活动" width="180" />
          <el-table-column fixed="right" label="操作" width="120">
            <template #default="scope">
              <el-button link type="primary" @click="openCourseDetail(scope.row.id)">查看</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-card>

      <!-- AI 使用记录 -->
      <el-card class="ai-usage" v-if="aiUsageRecords.length > 0">
        <template #header>
          <div class="card-header">
            <span>AI 使用记录</span>
            <el-button link @click="goToAiTools">查看全部</el-button>
          </div>
        </template>
        <el-table :data="aiUsageRecords" style="width: 100%">
          <el-table-column prop="tool_name" label="工具名称" />
          <el-table-column prop="usage_time" label="使用时间" width="180" />
          <el-table-column prop="status" label="状态" width="120">
            <template #default="scope">
              <el-tag :type="scope.row.status === 'success' ? 'success' : 'danger'">
                {{ scope.row.status === 'success' ? '成功' : '失败' }}
              </el-tag>
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>

    <!-- 课程详情弹窗 -->
    <el-dialog v-model="courseDetailDialog.visible" title="课程详情" width="600px" :close-on-click-modal="false">
      <CourseDetail v-if="courseDetailDialog.visible" :id="courseDetailDialog.id" />
    </el-dialog>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { 
  Plus, 
  Upload, 
  Document, 
  Folder, 
  MagicStick, 
  View, 
  ArrowRight, 
  User,
  Monitor
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { teacherApi } from '@/api/user'
import CourseDetail from './CourseDetail.vue'

const router = useRouter()

// 响应式数据
const userInfo = ref({})
const stats = ref({})
const recentCourses = ref([])
const aiUsageRecords = ref([])
const currentDate = ref('')
const courseDetailDialog = ref({ visible: false, id: null })

// 获取当前日期
const getCurrentDate = () => {
  const now = new Date()
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric', 
    weekday: 'long' 
  }
  currentDate.value = now.toLocaleDateString('zh-CN', options)
}

// 获取用户信息和统计数据
const loadDashboardData = async () => {
  try {
    // 获取用户信息
    const userInfoStr = localStorage.getItem('userInfo')
    if (userInfoStr) {
      userInfo.value = JSON.parse(userInfoStr)
    }

    // 获取统计信息
    const statsResponse = await teacherApi.getStats()
    if (statsResponse.code === 200) {
      stats.value = statsResponse.data
    }

    // 获取最近课程
    const coursesResponse = await teacherApi.getCourses({ limit: 5 })
    if (coursesResponse.code === 200) {
      recentCourses.value = coursesResponse.data.list || []
    }

    // 获取AI使用记录
    const aiStatsResponse = await teacherApi.getAiUsageRecords({ limit: 5 })
    if (aiStatsResponse.code === 200) {
      aiUsageRecords.value = aiStatsResponse.data || []
    }
  } catch (error) {
    console.error('加载仪表板数据失败：', error)
    ElMessage.error('加载数据失败')
  }
}

// 页面跳转方法
const goToCourses = () => router.push('/teacher/courses')
const goToFiles = () => router.push('/teacher/files')
const goToContent = () => router.push('/teacher/content')
const goToAiTools = () => router.push('/teacher/ai-tools')
const viewCourse = (id) => router.push(`/teacher/course/${id}`)

const openCourseDetail = (id) => {
  courseDetailDialog.value.id = id
  courseDetailDialog.value.visible = true
}

// 生命周期
onMounted(() => {
  getCurrentDate()
  loadDashboardData()
})
</script>

<style scoped>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* 欢迎区域 */
.welcome-section {
  background: linear-gradient(135deg, #1890ff 0%, #36cfc9 100%);
  border-radius: 16px;
  padding: 32px;
  color: white;
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.welcome-text {
  flex: 1;
}

.welcome-text h1 {
  font-size: 28px;
  font-weight: 600;
  margin: 0 0 8px;
}

.welcome-text p {
  font-size: 16px;
  margin: 0;
  opacity: 0.9;
}

/* 快捷操作区 */
.quick-actions {
  display: flex;
  gap: 20px;
  margin-bottom: 24px;
}

.action-item {
  flex: 1;
  background: #f5f7fa;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.action-item:hover {
  background: #e6f7ff;
  transform: translateY(-2px);
}

.action-item .el-icon {
  font-size: 24px;
  color: #1890ff;
  margin-bottom: 8px;
}

.action-text {
  color: #333;
  font-size: 16px;
}

/* 统计数据 */
.stats-section {
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stats-number {
  font-size: 36px;
  font-weight: bold;
  color: #1890ff;
  text-align: center;
}

/* 最近课程 */
.recent-courses {
  margin-bottom: 24px;
}

.el-card {
  margin-bottom: 20px;
}

:deep(.el-card__header) {
  padding: 15px 20px;
  border-bottom: 1px solid #ebeef5;
  box-sizing: border-box;
}

/* AI 使用记录 */
.ai-usage {
  margin-bottom: 24px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .welcome-section {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .quick-actions {
    flex-direction: column;
  }
  
  .stats-section {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style> 