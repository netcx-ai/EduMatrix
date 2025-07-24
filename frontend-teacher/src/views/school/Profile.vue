<template>
  <div class="page-school-profile" v-loading="loading">
    <el-card>
      <h2>学校信息</h2>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="学校名称">{{ data.name }}</el-descriptions-item>
        <el-descriptions-item label="学校编码">{{ data.code }}</el-descriptions-item>
        <el-descriptions-item label="简称">{{ data.short_name || '-' }}</el-descriptions-item>
        <el-descriptions-item label="省市">{{ data.province }} {{ data.city }}</el-descriptions-item>
        <el-descriptions-item label="联系人">{{ data.contact_person || '-' }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ data.contact_phone || '-' }}</el-descriptions-item>
        <el-descriptions-item label="邮箱">{{ data.email || '-' }}</el-descriptions-item>
        <el-descriptions-item label="网站">{{ data.website || '-' }}</el-descriptions-item>
        <el-descriptions-item label="地址" :span="2">{{ data.address || '-' }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ data.create_time }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ data.update_time }}</el-descriptions-item>
      </el-descriptions>
      <el-divider />
      <el-button type="primary" @click="$router.back()">返回</el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/utils/axios';
import { ElMessage } from 'element-plus';

const data = ref({});
const loading = ref(false);

const fetchProfile = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/school/profile');
    if (res.code === 0) {
      data.value = res.data;
    } else {
      ElMessage.error(res.msg || '获取失败');
    }
  } catch (e) {
    ElMessage.error('网络错误');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProfile);
</script>

<style scoped>
.page-school-profile {
  max-width: 900px;
  margin: 30px auto;
}
</style> 