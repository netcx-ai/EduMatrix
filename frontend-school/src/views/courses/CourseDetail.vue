<template>
  <div class="course-detail">
    <div class="page-header">
      <el-button @click="goBack">
        <el-icon><ArrowLeft /></el-icon>
        返回
      </el-button>
      <h2>{{ courseInfo.name }} - 课程详情</h2>
    </div>
    <el-card class="info-card">
      <template #header>
        <div class="card-header">
          <span>基本信息</span>
        </div>
      </template>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="课程名称">{{ courseInfo.name }}</el-descriptions-item>
        <el-descriptions-item label="课程代码">{{ courseInfo.code }}</el-descriptions-item>
        <el-descriptions-item label="所属学院">{{ courseInfo.college_name }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="courseInfo.status === 'active' ? 'success' : 'danger'">
            {{ courseInfo.status === 'active' ? '启用' : '禁用' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="学分">{{ courseInfo.credits }}</el-descriptions-item>
        <el-descriptions-item label="学时">{{ courseInfo.hours }}</el-descriptions-item>
        <el-descriptions-item label="学期">{{ courseInfo.semester }}</el-descriptions-item>
        <el-descriptions-item label="学年">{{ courseInfo.academic_year }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ courseInfo.createdAt }}</el-descriptions-item>
        <el-descriptions-item label="描述" :span="2">{{ courseInfo.description }}</el-descriptions-item>
      </el-descriptions>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getCourseDetail } from '@/api/course'
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()
const courseInfo = ref({})

const goBack = () => {
  router.back()
}

const fetchDetail = async () => {
  try {
    const res = await getCourseDetail(route.params.id)
    courseInfo.value = res.data || res
  } catch (e) {
    ElMessage.error('获取课程详情失败')
  }
}

onMounted(() => {
  fetchDetail()
})
</script>

<style scoped>
.course-detail {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
}
.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}
.info-card {
  margin-bottom: 24px;
}
.card-header {
  font-weight: bold;
  font-size: 16px;
}
</style> 