<template>
  <Layout>
    <div class="content-edit-container">
      <!-- 页面标题 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">{{ isEdit ? '编辑内容' : '新建内容' }}</h1>
          <p class="page-subtitle">{{ isEdit ? '修改内容信息和关联文件' : '创建新的教学内容' }}</p>
        </div>
        <div class="header-right">
          <el-button @click="goBack">
            <el-icon><ArrowLeft /></el-icon>
            返回
          </el-button>
          <el-button type="primary" @click="saveContent" :loading="saving">
            <el-icon><Check /></el-icon>
            保存
          </el-button>
        </div>
      </div>

      <!-- 编辑表单 -->
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="120px"
        class="content-form"
      >
        <!-- 基本信息 -->
        <el-card class="form-card">
          <template #header>
            <div class="card-header">
              <span>基本信息</span>
            </div>
          </template>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="内容标题" prop="name">
                <el-input
                  v-model="formData.name"
                  placeholder="请输入内容标题"
                  maxlength="200"
                  show-word-limit
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="文件类型" prop="file_type">
                <el-select v-model="formData.file_type" placeholder="请选择文件类型">
                  <el-option
                    v-for="type in fileTypes"
                    :key="type.key"
                    :label="type.name"
                    :value="type.key"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="内容描述" prop="description">
            <el-input
              v-model="formData.description"
              type="textarea"
              :rows="3"
              placeholder="请输入内容描述"
              maxlength="500"
              show-word-limit
            />
          </el-form-item>

          <el-form-item label="可见性" prop="visibility">
            <el-radio-group v-model="formData.visibility">
              <el-radio label="private">仅自己</el-radio>
              <el-radio label="public">公开</el-radio>
              <el-radio label="leader">负责人</el-radio>
            </el-radio-group>
          </el-form-item>
        </el-card>

        <!-- 内容编辑 -->
        <el-card class="form-card">
          <template #header>
            <div class="card-header">
              <span>内容编辑</span>
              <div class="header-actions">
                <el-button 
                  v-if="formData.source_type === 'ai_generate'"
                  link 
                  size="small" 
                  @click="regenerateContent"
                >
                  <el-icon><Refresh /></el-icon>
                  重新生成
                </el-button>
                <el-button 
                  v-if="formData.content"
                  link 
                  size="small" 
                  @click="generateFile"
                >
                  <el-icon><Document /></el-icon>
                  生成文件
                </el-button>
              </div>
            </div>
          </template>
          
          <el-form-item label="内容" prop="content">
            <el-input
              v-model="formData.content"
              type="textarea"
              :rows="15"
              placeholder="请输入或编辑内容"
              maxlength="10000"
              show-word-limit
            />
          </el-form-item>
        </el-card>

        <!-- 关联文件 -->
        <el-card class="form-card">
          <template #header>
            <div class="card-header">
              <span>关联文件</span>
              <el-button link size="small" @click="addFile">
                <el-icon><Plus /></el-icon>
                添加文件
              </el-button>
            </div>
          </template>
          
          <div v-if="formData.related_files?.length" class="file-list">
            <div 
              v-for="(file, index) in formData.related_files" 
              :key="file.id || index"
              class="file-item"
            >
              <div class="file-info">
                <el-icon><Document /></el-icon>
                <span class="file-name">{{ file.original_name || file.file_name }}</span>
                <span class="file-size">{{ formatFileSize(file.file_size) }}</span>
                <el-tag 
                  v-if="file.source_type === 'ai_generate'" 
                  size="small" 
                  type="success"
                >
                  AI生成
                </el-tag>
              </div>
              <div class="file-actions">
                <el-button link size="small" @click="previewFile(file)">
                  <el-icon><View /></el-icon>
                  预览
                </el-button>
                <el-button link size="small" @click="downloadFile(file)">
                  <el-icon><Download /></el-icon>
                  下载
                </el-button>
                <el-button link size="small" @click="removeFile(index)">
                  <el-icon><Delete /></el-icon>
                  移除
                </el-button>
              </div>
            </div>
          </div>
          <div v-else class="no-files">
            <p>暂无关联文件，点击"添加文件"上传</p>
          </div>
        </el-card>
      </el-form>

      <!-- 文件上传对话框 -->
      <el-dialog
        v-model="uploadDialog.visible"
        title="上传文件"
        width="600px"
        :close-on-click-modal="false"
      >
        <el-upload
          ref="uploadRef"
          :action="uploadUrl"
          :headers="uploadHeaders"
          :data="{ content_id: formData.id }"
          :on-success="handleFileUploadSuccess"
          :on-error="handleFileUploadError"
          :before-upload="beforeFileUpload"
          :file-list="uploadDialog.fileList"
          multiple
          drag
        >
          <el-icon class="el-icon--upload"><upload-filled /></el-icon>
          <div class="el-upload__text">
            将文件拖到此处，或<em>点击上传</em>
          </div>
          <template #tip>
            <div class="el-upload__tip">
              支持 Word、PDF、PPT、图片等格式，单个文件不超过 50MB
            </div>
          </template>
        </el-upload>
        
        <template #footer>
          <el-button @click="uploadDialog.visible = false">取消</el-button>
          <el-button type="primary" @click="confirmUpload">确定</el-button>
        </template>
      </el-dialog>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  ArrowLeft,
  Check,
  Document,
  View,
  Download,
  Delete,
  Plus,
  Refresh,
  UploadFilled
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { contentApi } from '@/api/content'

const route = useRoute()
const router = useRouter()

// 响应式数据
const formRef = ref(null)
const saving = ref(false)
const isEdit = computed(() => !!route.params.id)

const formData = reactive({
  id: null,
  name: '',
  description: '',
  content: '',
  file_type: 'text',
  visibility: 'private',
  source_type: 'upload',
  ai_tool_code: '',
  ai_prompt_params: '',
  related_files: []
})

const formRules = {
  name: [
    { required: true, message: '请输入内容标题', trigger: 'blur' },
    { max: 200, message: '标题长度不能超过200个字符', trigger: 'blur' }
  ],
  file_type: [
    { required: true, message: '请选择文件类型', trigger: 'change' }
  ],
  content: [
    { required: true, message: '请输入内容', trigger: 'blur' }
  ]
}

const fileTypes = [
  { key: 'text', name: '文本文件' },
  { key: 'document', name: '文档文件' },
  { key: 'image', name: '图片文件' },
  { key: 'video', name: '视频文件' }
]

const uploadDialog = reactive({
  visible: false,
  fileList: []
})

const uploadRef = ref(null)
const uploadUrl = ref('/api/teacher/files/upload')
const uploadHeaders = ref({})

// 加载内容详情
const loadContent = async () => {
  if (!isEdit.value) return
  
  try {
    const res = await contentApi.getContentDetailForEdit(route.params.id)
    if (res.code === 200) {
      console.log('内容详情数据:', res.data)
      Object.assign(formData, res.data)
    } else {
      ElMessage.error(res.message || '获取内容详情失败')
      goBack()
    }
  } catch (error) {
    console.error('获取内容详情失败：', error)
    ElMessage.error('获取内容详情失败')
    goBack()
  }
}

// 保存内容
const saveContent = async () => {
  try {
    await formRef.value.validate()
    saving.value = true
    
    const apiFunc = isEdit.value ? contentApi.updateContent : contentApi.createContent
    const res = await apiFunc(formData)
    
    if (res.code === 200) {
      ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
      goBack()
    } else {
      ElMessage.error(res.message || '保存失败')
    }
  } catch (error) {
    console.error('保存失败：', error)
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
  }
}

// 重新生成内容
const regenerateContent = async () => {
  if (!formData.ai_tool_code) {
    ElMessage.warning('无法重新生成，缺少AI工具信息')
    return
  }
  
  try {
    await ElMessageBox.confirm('重新生成将覆盖当前内容，是否继续？', '确认重新生成', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    // 调用AI重新生成接口
    const response = await contentApi.regenerateContent({
      content_id: formData.id,
      tool_code: formData.ai_tool_code,
      prompt_params: formData.ai_prompt_params || {},
      provider: 'deepseek'
    })
    
    if (response.code === 200) {
      formData.content = response.data.content
      ElMessage.success('重新生成成功')
    } else {
      ElMessage.error(response.message || '重新生成失败')
    }
  } catch (err) {
    if (err !== 'cancel') {
      console.error(err)
      ElMessage.error('重新生成失败：' + err.message)
    }
  }
}

// 生成文件到文件中心
const generateFile = async () => {
  if (!formData.content) {
    ElMessage.warning('没有可生成文件的内容')
    return
  }
  
  try {
    await ElMessageBox.confirm('将生成Word文档并保存到文件中心，是否继续？', '确认生成文件', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'info'
    })
    
    // 先导出Word文档
    const exportResponse = await contentApi.exportWord({
      content_id: formData.id,
      format: 'docx',
      ...(formData.source_type === 'ai_generate' && formData.ai_tool_code ? { ai_tool_code: formData.ai_tool_code } : {})
    })
    
    if (exportResponse.code === 200) {
      // 构造保存到文件中心的数据
      const fileData = {
        file_url: exportResponse.data.file_path,
        file_name: exportResponse.data.file_name,
        original_name: exportResponse.data.file_name,
        file_size: exportResponse.data.file_size,
        file_type: 'document',
        mime_type: exportResponse.data.mime_type,
        source_type: formData.source_type || 'upload',
        content_id: formData.id,
        ...(formData.source_type === 'ai_generate' && formData.ai_tool_code ? { ai_tool_code: formData.ai_tool_code } : {})
      }
      
      const fileResponse = await contentApi.saveToFileCenter(fileData)
      if (fileResponse.code === 200) {
        ElMessage.success('文件已生成并保存到文件中心')
        router.push('/teacher/files')
      } else {
        ElMessage.error(fileResponse.message || '保存到文件中心失败')
      }
    } else {
      ElMessage.error(exportResponse.message || '导出失败')
    }
  } catch (err) {
    if (err !== 'cancel') {
      console.error(err)
      ElMessage.error('生成文件失败：' + err.message)
    }
  }
}

// 添加文件
const addFile = () => {
  uploadDialog.visible = true
  uploadDialog.fileList = []
}

// 预览文件
const previewFile = (file) => {
  if (file.file_path) {
    window.open(file.file_path, '_blank')
  } else {
    ElMessage.warning('文件路径不存在')
  }
}

// 下载文件
const downloadFile = (file) => {
  try {
    if (file.file_path) {
      const link = document.createElement('a')
      link.href = file.file_path
      link.download = file.original_name || file.file_name
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      ElMessage.success('开始下载文件')
    } else {
      ElMessage.warning('文件路径不存在')
    }
  } catch (error) {
    console.error('下载文件失败：', error)
    ElMessage.error('下载文件失败')
  }
}

// 移除文件
const removeFile = (index) => {
  formData.related_files.splice(index, 1)
  ElMessage.success('文件已移除')
}

// 文件上传成功
const handleFileUploadSuccess = (response, file) => {
  if (response.code === 200) {
    const uploadedFile = response.data
    formData.related_files.push(uploadedFile)
    ElMessage.success('文件上传成功')
  } else {
    ElMessage.error(response.message || '上传失败')
  }
}

// 文件上传失败
const handleFileUploadError = (error) => {
  console.error('上传失败：', error)
  ElMessage.error('文件上传失败')
}

// 上传前验证
const beforeFileUpload = (file) => {
  const maxSize = 50 * 1024 * 1024 // 50MB
  if (file.size > maxSize) {
    ElMessage.error('文件大小不能超过50MB')
    return false
  }
  return true
}

// 确认上传
const confirmUpload = () => {
  uploadDialog.visible = false
}

// 返回
const goBack = () => {
  router.back()
}

// 格式化文件大小
const formatFileSize = (size) => {
  if (!size) return '0 B'
  if (size < 1024) {
    return size + ' B'
  } else if (size < 1024 * 1024) {
    return (size / 1024).toFixed(2) + ' KB'
  } else if (size < 1024 * 1024 * 1024) {
    return (size / (1024 * 1024)).toFixed(2) + ' MB'
  } else {
    return (size / (1024 * 1024 * 1024)).toFixed(2) + ' GB'
  }
}

onMounted(() => {
  loadContent()
})
</script>

<style scoped>
.content-edit-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
}

.page-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.header-right {
  display: flex;
  gap: 12px;
}

.content-form {
  background: #fff;
  border-radius: 8px;
}

.form-card {
  margin-bottom: 20px;
}

.form-card:last-child {
  margin-bottom: 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.file-list {
  margin-bottom: 16px;
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  margin-bottom: 8px;
}

.file-item:last-child {
  margin-bottom: 0;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.file-name {
  font-weight: 500;
  color: #303133;
}

.file-size {
  color: #909399;
  font-size: 12px;
}

.file-actions {
  display: flex;
  gap: 8px;
}

.no-files {
  text-align: center;
  padding: 40px;
  color: #909399;
}

.no-files p {
  margin: 0;
}
</style> 