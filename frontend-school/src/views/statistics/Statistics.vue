<template>
  <div class="statistics">
    <div class="page-header">
      <h2>使用统计</h2>
      <el-button type="primary" @click="handleExport">
        <el-icon><Download /></el-icon>
        导出报告
      </el-button>
    </div>
    <!-- 筛选条件 -->
    <el-card class="filter-card">
      <el-form :model="filterForm" inline>
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="filterForm.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item label="统计类型">
          <el-select v-model="filterForm.type" placeholder="请选择类型" style="width: 150px">
            <el-option label="全部" value="all" />
            <el-option label="学院" value="college" />
            <el-option label="教师" value="teacher" />
            <el-option label="课程" value="course" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleFilter">
            <el-icon><Search /></el-icon>
            查询
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <!-- 统计卡片 -->
    <div class="stats-overview">
      <el-card class="stat-card" v-for="item in statsCards" :key="item.label">
        <div class="stat-content">
          <div class="stat-icon" :class="item.iconClass">
            <el-icon><component :is="item.icon" /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ item.value }}</div>
            <div class="stat-label">{{ item.label }}</div>
            <div class="stat-trend" :class="item.trend > 0 ? 'up' : 'down'">
              {{ item.trend > 0 ? '+' : '' }}{{ item.trend }}%
            </div>
          </div>
        </div>
      </el-card>
    </div>
    <!-- 详细统计表格 -->
    <el-card class="table-card">
      <div class="table-header">
        <h3>详细统计</h3>
      </div>
      <el-table v-loading="loading" :data="statisticsList" stripe style="width: 100%">
        <el-table-column prop="name" label="名称" min-width="150" />
        <el-table-column prop="type" label="类型" width="100" />
        <el-table-column prop="usageCount" label="使用次数" width="120" align="center" />
        <el-table-column prop="activeUsers" label="活跃用户" width="120" align="center" />
        <el-table-column prop="lastUsed" label="最后使用" width="180" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'info'">
              {{ row.status === 'active' ? '活跃' : '不活跃' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
      <div class="pagination-wrapper">
        <el-pagination
          :current-page="pagination.currentPage"
          :page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Download, Search, Refresh, TrendCharts, User, School, Reading } from '@element-plus/icons-vue'
import { getStatistics, getStatisticsList, exportStatistics } from '@/api/statistics'

export default {
  name: 'Statistics',
  setup() {
    const loading = ref(false)
    const statisticsList = ref([])
    const filterForm = reactive({ dateRange: [], type: 'all' })
    const pagination = reactive({ currentPage: 1, pageSize: 10, total: 0 })
    const statsData = ref({
      totalVisits: 0,
      activeUsers: 0,
      activeColleges: 0,
      activeCourses: 0
    })
    
    // 统计卡片数据
    const statsCards = computed(() => [
      { label: '总访问量', value: statsData.value.totalVisits, trend: 15.2, icon: TrendCharts, iconClass: 'visits' },
      { label: '活跃用户', value: statsData.value.activeUsers, trend: 8.6, icon: User, iconClass: 'users' },
      { label: '活跃学院', value: statsData.value.activeColleges, trend: 2.1, icon: School, iconClass: 'colleges' },
      { label: '活跃课程', value: statsData.value.activeCourses, trend: -3.2, icon: Reading, iconClass: 'courses' }
    ])
    
    // 获取统计概览数据
    const fetchStatsOverview = async () => {
      try {
        const params = { ...filterForm }
        const res = await getStatistics(params)
        statsData.value = res.data || res
      } catch (error) {
        console.error('获取统计概览失败:', error)
        ElMessage.error('获取统计概览失败')
      }
    }

    // 获取统计列表数据
    const fetchStatistics = async () => {
      loading.value = true
      try {
        const params = { 
          ...filterForm, 
          page: pagination.currentPage, 
          pageSize: pagination.pageSize 
        }
        const res = await getStatisticsList(params)
        const data = res.data || res
        statisticsList.value = data.list || data
        pagination.total = data.total || 0
      } catch (error) {
        console.error('获取统计列表失败:', error)
        ElMessage.error('获取统计列表失败')
      } finally {
        loading.value = false
      }
    }
    
    onMounted(() => {
      fetchStatsOverview()
      fetchStatistics()
    })
    
    const handleFilter = () => { 
      pagination.currentPage = 1
      fetchStatsOverview()
      fetchStatistics() 
    }
    const handleReset = () => { 
      filterForm.dateRange = []
      filterForm.type = 'all'
      pagination.currentPage = 1
      fetchStatsOverview()
      fetchStatistics() 
    }
    const handleSizeChange = (size) => { 
      pagination.pageSize = size
      fetchStatistics() 
    }
    const handleCurrentChange = (page) => { 
      pagination.currentPage = page
      fetchStatistics() 
    }
    const handleExport = async () => {
      try {
        await exportStatistics(filterForm)
        ElMessage.success('导出成功')
      } catch (error) {
        ElMessage.error('导出失败')
      }
    }
    
    return { 
      loading, 
      statisticsList, 
      filterForm, 
      pagination, 
      statsCards,
      statsData,
      handleFilter, 
      handleReset, 
      handleSizeChange, 
      handleCurrentChange, 
      handleExport 
    }
  }
}
</script>

<style scoped>
.statistics {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.filter-card { margin-bottom: 20px; }
.stats-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}
.stat-card { border-radius: 16px; box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08); }
.stat-content { display: flex; align-items: center; padding: 20px; }
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
.stat-icon.visits { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.users { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.colleges { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.courses { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.stat-info { flex: 1; }
.stat-number { font-size: 28px; font-weight: bold; color: #333; margin-bottom: 5px; }
.stat-label { color: #666; font-size: 14px; margin-bottom: 5px; }
.stat-trend { font-size: 12px; font-weight: 500; }
.stat-trend.up { color: #67c23a; }
.stat-trend.down { color: #f56c6c; }
.table-card { border-radius: 16px; box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08); background: #fff; }
.table-header { padding: 20px 20px 0; }
.table-header h3 { margin: 0; color: #333; }
.el-table { border-radius: 12px; }
.pagination-wrapper { display: flex; justify-content: center; margin-top: 20px; }
</style> 