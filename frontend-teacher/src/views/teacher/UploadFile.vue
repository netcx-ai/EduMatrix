<template>
  <div class="page-upload-file">
    <el-card>
      <h2>上传文件</h2>
      <el-upload
        class="upload-area"
        drag
        :action="uploadAction"
        :headers="headers"
        :data="extraData"
        :on-success="handleSuccess"
        :on-error="handleError"
        multiple>
        <i class="el-icon-upload"></i>
        <div class="el-upload__text">将文件拖到此处，或 <em>点击上传</em></div>
        <template #tip>
          <div class="el-upload__tip">支持多文件上传，单文件不超过 100MB</div>
        </template>
      </el-upload>
    </el-card>
  </div>
</template>

<script setup>
import { ElMessage } from 'element-plus';
import { useRouter } from 'vue-router';

const router = useRouter();

const uploadAction = '/api/teacher/files/upload'

const headers = {
  Authorization: localStorage.getItem('token') ? `Bearer ${localStorage.getItem('token')}` : ''
};
// 如需额外字段 (目录/分类) 可在此添加
const extraData = {};

const handleSuccess = (res) => {
  if (res.code === 200) {
    ElMessage.success('上传成功');
    router.push('/teacher/files');
  } else {
    ElMessage.error(res.message || res.msg || '上传失败');
  }
};

const handleError = () => {
  ElMessage.error('网络错误');
};
</script>

<style scoped>
.page-upload-file {
  max-width: 800px;
  margin: 30px auto;
}
.upload-area {
  width: 100%;
}
</style> 