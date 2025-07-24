<template>
  <div class="course-detail" v-loading="loading">
    <el-card v-if="course">
      <h2>{{ course.name }} <span class="code">({{ course.course_code || course.code }})</span></h2>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="课程名称">{{ course.name }}</el-descriptions-item>
        <el-descriptions-item label="课程代码">{{ course.course_code || course.code }}</el-descriptions-item>
        <el-descriptions-item label="学分">{{ course.credits || course.credit }}</el-descriptions-item>
        <el-descriptions-item label="学时">{{ course.hours }}</el-descriptions-item>
        <el-descriptions-item label="学年">{{ course.academic_year }}</el-descriptions-item>
        <el-descriptions-item label="学期">{{ course.semester }}</el-descriptions-item>
        <el-descriptions-item label="学院">{{ course.college_name || '-' }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="course.status == 1 ? 'success' : 'info'">
            {{ course.status == 1 ? '启用' : '停用' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ course.create_time || course.created_at }}</el-descriptions-item>
      </el-descriptions>
      <el-divider />
      <div>
        <strong>课程简介：</strong>
        <div v-html="course.description || '暂无简介'" class="intro"></div>
      </div>
      <el-divider />
      <el-button type="primary" @click="$emit('close')">关闭</el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { teacherApi } from '@/api/user';
import { ElMessage } from 'element-plus';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const course = ref(null);
const loading = ref(false);

const fetchDetail = async () => {
  if (!props.id) return;
  loading.value = true;
  try {
    const res = await teacherApi.getCourseDetail(props.id);
    if (res.code === 0 || res.code === 200) {
      if (res.data && Array.isArray(res.data.list) && res.data.list.length > 0) {
        course.value = res.data.list[0];
      } else if (res.data && typeof res.data === 'object') {
        course.value = res.data;
      } else {
        course.value = null;
      }
    } else {
      ElMessage.error(res.message || res.msg || '获取详情失败');
    }
  } catch (e) {
    ElMessage.error('网络错误');
  } finally {
    loading.value = false;
  }
};

watch(() => props.id, fetchDetail, { immediate: true });
</script>

<style scoped>
.course-detail {
  max-width: 900px;
  margin: 30px auto;
}
.code {
  font-size: 14px;
  color: #888;
}
.intro {
  white-space: pre-wrap;
  line-height: 1.6;
}
</style> 