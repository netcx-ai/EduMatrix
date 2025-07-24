<template>
  <div class="statistics-page">
    <Layout>
      <template #content>
        <div class="page-header">
          <h2>📊 使用统计</h2>
          <div class="header-actions">
            <el-select v-model="timeRange" @change="handleTimeRangeChange">
              <el-option label="最近7天" value="7" />
              <el-option label="最近30天" value="30" />
              <el-option label="最近90天" value="90" />
              <el-option label="最近一年" value="365" />
            </el-select>
            <el-button type="primary" @click="refreshData">
              <el-icon><Refresh /></el-icon>
              刷新数据
            </el-button>
          </div>
        </div>

        <!-- 统计卡片 -->
        <el-row :gutter="20" class="stats-cards">
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                  <div class="stat-number">{{ stats.collegeCount }}</div>
                  <div class="stat-label">学院数量</div>
                  <div class="stat-trend" :class="stats.collegeTrend > 0 ? 'up' : 'down'">
                    {{ stats.collegeTrend > 0 ? '+' : '' }}{{ stats.collegeTrend }}%
                  </div>
                </div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-info">
                  <div class="stat-number">{{ stats.teacherCount }}</div>
                  <div class="stat-label">教师数量</div>
                  <div class="stat-trend" :class="stats.teacherTrend > 0 ? 'up' : 'down'">
                    {{ stats.teacherTrend > 0 ? '+' : '' }}{{ stats.teacherTrend }}%
                  </div>
                </div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                  <div class="stat-number">{{ stats.courseCount }}</div>
                  <div class="stat-label">课程数量</div>
                  <div class="stat-trend" :class="stats.courseTrend > 0 ? 'up' : 'down'">
                    {{ stats.courseTrend > 0 ? '+' : '' }}{{ stats.courseTrend }}%
                  </div>
                </div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card class="stat-card">
              <div class="stat-content">
                <div class="stat-icon">🤖</div>
                <div class="stat-info">
                  <div class="stat-number">{{ stats.aiUsage }}</div>
                  <div class="stat-label">AI使用次数</div>
                  <div class="stat-trend" :class="stats.aiTrend > 0 ? 'up' : 'down'">
                    {{ stats.aiTrend > 0 ? '+' : '' }}{{ stats.aiTrend }}%
                  </div>
                </div>
              </div>
            </el-card>
          </el-col>
        </el-row>

        <!-- 图表区域 -->
        <el-row :gutter="20" class="charts-section">
          <el-col :span="12">
            <el-card class="chart-card">
              <template #header>
                <div class="card-header">
                  <span>👨‍🏫 教师活跃度趋势</span>
                </div>
              </template>
              <div class="chart-container">
                <v-chart class="chart" :option="teacherActivityOption" autoresize />
              </div>
            </el-card>
          </el-col>
          <el-col :span="12">
            <el-card class="chart-card">
              <template #header>
                <div class="card-header">
                  <span>🏢 学院使用情况</span>
                </div>
              </template>
              <div class="chart-container">
                <v-chart class="chart" :option="collegeUsageOption" autoresize />
              </div>
            </el-card>
          </el-col>
          <el-col :span="12">
            <el-card class="chart-card">
              <template #header>
                <div class="card-header">
                  <span>🤖 AI工具使用统计</span>
                </div>
              </template>
              <div class="chart-container">
                <v-chart class="chart" :option="aiToolUsageOption" autoresize />
              </div>
            </el-card>
          </el-col>
          <el-col :span="12">
            <el-card class="chart-card">
              <template #header>
                <div class="card-header">
                  <span>📊 课程发布趋势</span>
                </div>
              </template>
              <div class="chart-container">
                <v-chart class="chart" :option="coursePublishOption" autoresize />
              </div>
            </el-card>
          </el-col>
        </el-row>

        <!-- 详细统计表格 -->
        <el-row :gutter="20" class="tables-section">
          <el-col :span="12">
            <el-card class="table-card">
              <template #header>
                <div class="card-header">
                  <span>🏆 学院排行榜</span>
                </div>
              </template>
              <el-table :data="collegeRanking" style="width: 100%">
                <el-table-column prop="rank" label="排名" width="80" align="center" />
                <el-table-column prop="name" label="学院名称" />
                <el-table-column prop="teacherCount" label="教师数" width="100" align="center" />
                <el-table-column prop="courseCount" label="课程数" width="100" align="center" />
                <el-table-column prop="aiUsage" label="AI使用" width="100" align="center" />
              </el-table>
            </el-card>
          </el-col>
          <el-col :span="12">
            <el-card class="table-card">
              <template #header>
                <div class="card-header">
                  <span>👨‍🏫 活跃教师排行</span>
                </div>
              </template>
              <el-table :data="teacherRanking" style="width: 100%">
                <el-table-column prop="rank" label="排名" width="80" align="center" />
                <el-table-column prop="name" label="教师姓名" />
                <el-table-column prop="college" label="所属学院" />
                <el-table-column prop="courseCount" label="课程数" width="100" align="center" />
                <el-table-column prop="aiUsage" label="AI使用" width="100" align="center" />
              </el-table>
            </el-card>
          </el-col>
        </el-row>

        <!-- 实时数据 -->
        <el-row :gutter="20" class="realtime-section">
          <el-col :span="24">
            <el-card class="realtime-card">
              <template #header>
                <div class="card-header">
                  <span>⚡ 实时数据</span>
                  <el-tag type="success" size="small">实时更新</el-tag>
                </div>
              </template>
              <el-row :gutter="20">
                <el-col :span="6">
                  <div class="realtime-item">
                    <div class="realtime-label">今日活跃教师</div>
                    <div class="realtime-value">{{ realtimeData.activeTeachers }}</div>
                  </div>
                </el-col>
                <el-col :span="6">
                  <div class="realtime-item">
                    <div class="realtime-label">今日AI使用</div>
                    <div class="realtime-value">{{ realtimeData.todayAiUsage }}</div>
                  </div>
                </el-col>
                <el-col :span="6">
                  <div class="realtime-item">
                    <div class="realtime-label">今日文件上传</div>
                    <div class="realtime-value">{{ realtimeData.todayUploads }}</div>
                  </div>
                </el-col>
                <el-col :span="6">
                  <div class="realtime-item">
                    <div class="realtime-label">在线用户</div>
                    <div class="realtime-value">{{ realtimeData.onlineUsers }}</div>
                  </div>
                </el-col>
              </el-row>
            </el-card>
          </el-col>
        </el-row>
      </template>
    </Layout>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Refresh } from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { schoolApi } from '@/api/school'

// ECharts
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { PieChart, BarChart, LineChart } from 'echarts/charts';
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  ToolboxComponent,
} from 'echarts/components';
import VChart, { THEME_KEY } from 'vue-echarts';

use([
  CanvasRenderer,
  PieChart,
  BarChart,
  LineChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent,
  ToolboxComponent
]);


export default {
  name: 'Statistics',
  components: {
    Layout,
    Refresh,
    VChart
  },
  setup() {
    const timeRange = ref('30')
    const loading = ref(false)

    // ECharts options
    const teacherActivityOption = ref({});
    const collegeUsageOption = ref({});
    const aiToolUsageOption = ref({});
    const coursePublishOption = ref({});

    // 统计数据
    const stats = reactive({
      collegeCount: 0,
      teacherCount: 0,
      courseCount: 0,
      aiUsage: 0,
      collegeTrend: 0,
      teacherTrend: 0,
      courseTrend: 0,
      aiTrend: 0
    })

    // 实时数据
    const realtimeData = reactive({
      activeTeachers: 0,
      todayAiUsage: 0,
      todayUploads: 0,
      onlineUsers: 0
    })

    // 排行榜数据
    const collegeRanking = ref([])
    const teacherRanking = ref([])

    // 更新图表数据
    const updateChartData = (data) => {
      // 教师活跃度趋势 - 折线图
      teacherActivityOption.value = {
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'category', data: data.teacherActivity.dates || [] },
        yAxis: { type: 'value' },
        series: [{ data: data.teacherActivity.counts || [], type: 'line', smooth: true }],
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true }
      };

      // 学院使用情况 - 饼图
      collegeUsageOption.value = {
        tooltip: { trigger: 'item' },
        legend: { top: '5%', left: 'center' },
        series: [
          {
            name: '教师数量',
            type: 'pie',
            radius: ['40%', '70%'],
            avoidLabelOverlap: false,
            itemStyle: {
              borderRadius: 10,
              borderColor: '#fff',
              borderWidth: 2
            },
            label: { show: false, position: 'center' },
            emphasis: {
              label: { show: true, fontSize: '20', fontWeight: 'bold' }
            },
            labelLine: { show: false },
            data: data.collegeUsage || []
          }
        ]
      };

      // AI工具使用统计 - 柱状图
      aiToolUsageOption.value = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        xAxis: { type: 'category', data: (data.aiToolUsage || []).map(item => item.name) },
        yAxis: { type: 'value' },
        series: [{ data: (data.aiToolUsage || []).map(item => item.value), type: 'bar' }],
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true }
      };

      // 课程发布趋势 - 折线图
      coursePublishOption.value = {
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'category', data: data.coursePublish.dates || [] },
        yAxis: { type: 'value' },
        series: [{ data: data.coursePublish.counts || [], type: 'line', areaStyle: {} }],
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true }
      };
    }

    // 获取统计数据
    const getStatistics = async () => {
      loading.value = true
      try {
        const params = { timeRange: timeRange.value }
        const res = await schoolApi.getStatistics(params)
        
        // 更新统计数据
        Object.assign(stats, res.data.stats || {})
        Object.assign(realtimeData, res.data.realtime || {})
        collegeRanking.value = res.data.collegeRanking || []
        teacherRanking.value = res.data.teacherRanking || []

        // 更新图表数据
        updateChartData(res.data.charts || {});
      } catch (error) {
        ElMessage.error('获取统计数据失败，加载模拟数据')
        console.error(error)
        // 使用模拟数据
        loadMockData()
      } finally {
        loading.value = false
      }
    }

    // 加载模拟数据
    const loadMockData = () => {
      Object.assign(stats, {
        collegeCount: 8,
        teacherCount: 156,
        courseCount: 342,
        aiUsage: 1250,
        collegeTrend: 12.5,
        teacherTrend: 8.3,
        courseTrend: 15.7,
        aiTrend: 23.4
      })

      // 模拟图表数据
      const mockChartData = {
        teacherActivity: {
          dates: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
          counts: [12, 15, 8, 22, 18, 14, 10],
        },
        collegeUsage: [
          { value: 25, name: '数学学院' },
          { value: 20, name: '物理学院' },
          { value: 18, name: '化学学院' },
          { value: 22, name: '计算机学院' },
          { value: 15, name: '经济学院' },
        ],
        aiToolUsage: [
          { name: '讲稿生成', value: 350 },
          { name: '作业生成', value: 450 },
          { name: '题库生成', value: 200 },
          { name: '课程分析', value: 150 },
          { name: '教案生成', value: 100 },
        ],
        coursePublish: {
          dates: ['1月', '2月', '3月', '4月', '5月', '6月'],
          counts: [20, 32, 28, 45, 50, 62],
        }
      };
      updateChartData(mockChartData);

      Object.assign(realtimeData, {
        activeTeachers: 45,
        todayAiUsage: 89,
        todayUploads: 156,
        onlineUsers: 23
      })

      collegeRanking.value = [
        { rank: 1, name: '数学学院', teacherCount: 25, courseCount: 45, aiUsage: 320 },
        { rank: 2, name: '物理学院', teacherCount: 20, courseCount: 38, aiUsage: 280 },
        { rank: 3, name: '化学学院', teacherCount: 18, courseCount: 32, aiUsage: 245 },
        { rank: 4, name: '计算机学院', teacherCount: 22, courseCount: 40, aiUsage: 310 },
        { rank: 5, name: '经济学院', teacherCount: 15, courseCount: 28, aiUsage: 180 }
      ]

      teacherRanking.value = [
        { rank: 1, name: '张教授', college: '数学学院', courseCount: 8, aiUsage: 45 },
        { rank: 2, name: '李副教授', college: '物理学院', courseCount: 6, aiUsage: 38 },
        { rank: 3, name: '王讲师', college: '化学学院', courseCount: 5, aiUsage: 32 },
        { rank: 4, name: '赵教授', college: '计算机学院', courseCount: 7, aiUsage: 41 },
        { rank: 5, name: '钱副教授', college: '经济学院', courseCount: 4, aiUsage: 28 }
      ]
    }

    // 时间范围变化
    const handleTimeRangeChange = () => {
      getStatistics()
    }

    // 刷新数据
    const refreshData = () => {
      getStatistics()
      ElMessage.success('数据已刷新')
    }

    onMounted(() => {
      getStatistics()
    })

    return {
      timeRange,
      loading,
      stats,
      realtimeData,
      collegeRanking,
      teacherRanking,
      handleTimeRangeChange,
      refreshData,
      // ECharts options
      teacherActivityOption,
      collegeUsageOption,
      aiToolUsageOption,
      coursePublishOption
    }
  }
}
</script>

<style scoped>
.statistics-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  text-align: center;
}

.stat-content {
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon {
  font-size: 2.5em;
  margin-right: 15px;
}

.stat-info {
  text-align: left;
}

.stat-number {
  font-size: 2em;
  font-weight: bold;
  color: #409EFF;
  line-height: 1;
}

.stat-label {
  color: #666;
  font-size: 0.9em;
  margin-top: 5px;
}

.stat-trend {
  font-size: 0.8em;
  margin-top: 5px;
}

.stat-trend.up {
  color: #67C23A;
}

.stat-trend.down {
  color: #F56C6C;
}

.charts-section {
  margin-bottom: 20px;
}

.chart-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-container {
  height: 300px;
}

.chart {
  height: 100%;
  width: 100%;
}

.chart-placeholder {
  text-align: center;
  color: #999;
}

.chart-placeholder p {
  margin: 5px 0;
}

.chart-desc {
  font-size: 0.9em;
  color: #ccc;
}

.tables-section {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
}

.realtime-section {
  margin-bottom: 20px;
}

.realtime-card {
  margin-bottom: 20px;
}

.realtime-item {
  text-align: center;
  padding: 20px;
}

.realtime-label {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 10px;
}

.realtime-value {
  font-size: 2em;
  font-weight: bold;
  color: #409EFF;
}
</style> 