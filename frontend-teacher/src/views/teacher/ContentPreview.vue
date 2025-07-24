<template>
  <Layout>
    <div class="preview-container" v-loading="loading">
      <el-page-header :content="content.name || '内容预览'" @back="goBack" />

      <div v-if="content.source_type === 'upload'" class="file-preview">
        <p>文件名称：{{ content.name }}</p>
        <el-button type="primary" @click="downloadFile">下载文件</el-button>
      </div>

      <div v-else class="text-preview">
        <el-card shadow="never">
          <pre>{{ content.content }}</pre>
        </el-card>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import Layout from '@/components/Layout.vue'
import { contentApi } from '@/api/content'
import { fileBase } from '@/utils/env'
import { downloadById } from '@/utils/file'

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const content = ref({})

const loadDetail = async () => {
  try {
    loading.value = true
    const res = await contentApi.getContentDetail(route.params.id)
    if (res.code === 200) {
      content.value = res.data
    } else {
      ElMessage.error(res.message || '加载失败')
    }
  } catch (e) {
    console.error(e)
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const downloadFile = () => {
  if (!content.value.file_path) return
  downloadById(content.value)
}

const goBack = () => router.back()

onMounted(() => {
  loadDetail()
})
</script>

<style scoped>
.preview-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 24px;
}
.text-preview pre {
  white-space: pre-wrap;
  word-break: break-word;
}
</style> 