<template>
  <div class="dashboard">
    <!-- 欢迎信息 -->
    <div class="welcome-section">
      <h1>欢迎回来，{{ userStore.userInfo.name }}</h1>
      <p>今天是 {{ currentDate }}，祝您工作愉快！</p>
    </div>
    <!-- 统计卡片 -->
    <el-card class="stats-card" shadow="hover">
      <div class="stats-grid">
        <el-card class="stat-card" v-for="item in statItems" :key="item.label" :body-style="{padding: '24px'}">
          <div class="stat-content">
            <div class="stat-icon" :class="item.iconClass">
              <el-icon><component :is="item.icon" /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-number">{{ item.value }}</div>
              <div class="stat-label">{{ item.label }}</div>
            </div>
          </div>
        </el-card>
      </div>
    </el-card>
    <!-- 快捷操作 -->
    <div class="quick-actions">
      <h2>快捷操作</h2>
      <div class="action-grid">
        <el-card class="action-card" v-for="action in actions" :key="action.text" @click="action.onClick">
          <div class="action-content">
            <el-icon class="action-icon"><component :is="action.icon" /></el-icon>
            <div class="action-text">{{ action.text }}</div>
          </div>
        </el-card>
      </div>
    </div>
    <!-- 最近活动 -->
    <div class="recent-activities">
      <h2>最近活动</h2>
      <el-card>
        <el-skeleton v-if="loadingActivities" rows="4" animated />
        <template v-else>
          <el-empty v-if="!recentActivities.length" description="暂无活动" />
          <el-timeline v-else>
            <el-timeline-item
              v-for="activity in recentActivities"
              :key="activity.id"
              :timestamp="activity.time"
              :type="activity.type"
            >
              {{ activity.content }}
            </el-timeline-item>
          </el-timeline>
        </template>
      </el-card>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { statsApi } from '@/api/stats'
import { getRecentActivities } from '@/api/activity'
import { ElEmpty, ElSkeleton } from 'element-plus'
import { School, User, Reading, Clock, Check, DataAnalysis, Setting } from '@element-plus/icons-vue'

export default {
  name: 'Dashboard',
  setup() {
    const router = useRouter()
    const userStore = useUserStore()

    // 统计数据
    const stats = ref({
      colleges: 0,
      teachers: 0,
      courses: 0,
      pendingAudits: 0
    })

    // 最近活动
    const recentActivities = ref([])

    // 当前日期
    const currentDate = computed(() => {
      return new Date().toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long'
      })
    })

    // 获取统计数据
    const fetchStats = async () => {
      try {
        const res = await statsApi.getStats()
        // 兼容后端返回结构
        if (res.data) {
          stats.value = res.data
        } else {
          stats.value = res
        }
      } catch (error) {
        console.error('获取统计数据失败:', error)
      }
    }

    const statItems = computed(() => [
      { label: '学院总数', value: stats.value.colleges, icon: School, iconClass: 'college' },
      { label: '教师总数', value: stats.value.teachers, icon: User, iconClass: 'teacher' },
      { label: '课程总数', value: stats.value.courses, icon: Reading, iconClass: 'course' },
      { label: '待审核', value: stats.value.pendingAudits, icon: Clock, iconClass: 'pending' }
    ])

    // 页面跳转方法
    const goToColleges = () => router.push('/colleges')
    const goToTeachers = () => router.push('/teachers')
    const goToTeacherAudit = () => router.push('/teacher-audit')
    const goToCourses = () => router.push('/courses')
    const goToStatistics = () => router.push('/statistics')
    const goToSettings = () => router.push('/settings')

    // 再定义 actions
    const actions = [
      { text: '学院管理', icon: School, onClick: goToColleges },
      { text: '教师管理', icon: User, onClick: goToTeachers },
      { text: '教师审核', icon: Check, onClick: goToTeacherAudit },
      { text: '课程管理', icon: Reading, onClick: goToCourses },
      { text: '使用统计', icon: DataAnalysis, onClick: goToStatistics },
      { text: '学校设置', icon: Setting, onClick: goToSettings }
    ]

    const loadingActivities = ref(false)

    const fetchRecentActivities = async () => {
      loadingActivities.value = true
      try {
        const res = await getRecentActivities()
        recentActivities.value = res.data || res
      } catch (e) {
        recentActivities.value = []
      } finally {
        loadingActivities.value = false
      }
    }

    onMounted(() => {
      fetchStats()
      fetchRecentActivities()
    })

    return {
      userStore,
      stats,
      recentActivities,
      currentDate,
      goToColleges,
      goToTeachers,
      goToTeacherAudit,
      goToCourses,
      goToStatistics,
      goToSettings,
      statItems,
      actions,
      loadingActivities
    }
  }
}
</script>

<style scoped>
.dashboard {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
}
.stats-card {
  margin-bottom: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08);
  background: #fff;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
}
.stat-card {
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(102, 126, 234, 0.08);
  background: linear-gradient(135deg, #f8fafc 0%, #eef2fb 100%);
  transition: box-shadow 0.3s;
}
.stat-card:hover {
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
}
.stat-content {
  display: flex;
  align-items: center;
  padding: 10px 0;
}
.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #fff;
  margin-right: 20px;
}
.stat-icon.college { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.teacher { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.course { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.pending { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.stat-info { flex: 1; }
.stat-number { font-size: 32px; font-weight: bold; color: #333; margin-bottom: 5px; }
.stat-label { color: #666; font-size: 14px; }
.quick-actions { margin-bottom: 30px; }
.quick-actions h2 { color: #333; margin: 0 0 20px 0; font-size: 20px; }
.action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.action-card { border-radius: 14px; box-shadow: 0 2px 12px rgba(102, 126, 234, 0.08); background: #fff; cursor: pointer; transition: box-shadow 0.3s; }
.action-card:hover { box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15); }
.action-content { display: flex; flex-direction: column; align-items: center; padding: 30px 20px; text-align: center; }
.action-icon { font-size: 40px; color: #409EFF; margin-bottom: 15px; }
.action-text { font-size: 16px; color: #333; font-weight: 500; }
.recent-activities h2 { color: #333; margin: 0 0 20px 0; font-size: 20px; }

/* 移动端适配 */
@media (max-width: 768px) {
  .dashboard {
    padding: 0;
  }
  
  .welcome-section h1 {
    font-size: 24px;
  }
  
  .welcome-section p {
    font-size: 14px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .stat-number {
    font-size: 24px;
  }
  
  .stat-label {
    font-size: 12px;
  }
  
  .action-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  
  .action-content {
    padding: 20px 16px;
  }
  
  .action-icon {
    font-size: 32px;
    margin-bottom: 12px;
  }
  
  .action-text {
    font-size: 14px;
  }
  
  .quick-actions h2,
  .recent-activities h2 {
    font-size: 18px;
    margin-bottom: 16px;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .action-grid {
    grid-template-columns: 1fr;
  }
  
  .welcome-section h1 {
    font-size: 20px;
  }
  
  .stat-content {
    padding: 8px;
  }
  
  .stat-icon {
    width: 50px;
    height: 50px;
    font-size: 20px;
    margin-right: 16px;
  }
}

/* 平板适配 */
@media (min-width: 769px) and (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .action-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style> 