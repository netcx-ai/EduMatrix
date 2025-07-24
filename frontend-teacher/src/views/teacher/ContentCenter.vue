<template>
  <Layout>
    <div class="content-center-container">
      <!-- 页面标题和操作按钮 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">内容中心</h1>
          <p class="page-subtitle">管理并分享您的教学内容</p>
        </div>
        <div class="header-right">
          <el-button type="primary" @click="createContent">
            <el-icon><Plus /></el-icon>
            新建内容
          </el-button>
          <el-button @click="openUpload">
            <el-icon><Upload /></el-icon>
            上传文件
          </el-button>
          <el-button @click="openAiGenerate">
            <el-icon><MagicStick /></el-icon>
            AI 生成
          </el-button>
          <el-button @click="refreshData">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </div>

      <!-- 空间 TAB 切换 -->
      <el-tabs v-model="spaceType">
        <el-tab-pane label="个人空间" name="personal" />
        <el-tab-pane label="课程空间" name="course" />
      </el-tabs>

      <!-- 内容表格 -->
      <el-table
        v-loading="loading"
        :data="contentList"
        stripe
        class="content-table"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="标题" min-width="220" />
        <el-table-column prop="source_type" label="来源" width="100">
          <template #default="{ row }">
            <el-tag :type="row.source_type === 'ai_generate' ? 'success' : 'info'">
              {{ row.source_type === 'ai_generate' ? 'AI生成' : '上传' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="file_info" label="关联文件" width="150">
          <template #default="{ row }">
            <div v-if="row.file_path || row.related_files?.length">
              <el-button 
                v-if="row.file_path" 
                link 
                size="small" 
                @click="downloadFile(row)"
              >
                <el-icon><Document /></el-icon>
                下载文件
              </el-button>
              <div v-if="row.related_files?.length" class="related-files">
                <el-tag 
                  v-for="file in row.related_files.slice(0, 2)" 
                  :key="file.id" 
                  size="small" 
                  type="info"
                  @click="downloadFile(file)"
                  style="cursor: pointer; margin-right: 4px;"
                >
                  {{ file.original_name }}
                </el-tag>
                <el-tag 
                  v-if="row.related_files.length > 2" 
                  size="small" 
                  type="info"
                >
                  +{{ row.related_files.length - 2 }}
                </el-tag>
              </div>
            </div>
            <div v-else class="no-files">
              <span class="text-muted">暂无关联文件</span>
              <el-button 
                link 
                size="small" 
                @click="associateFiles(row)"
                style="margin-left: 8px;"
              >
                <el-icon><Link /></el-icon>
                关联
              </el-button>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="visibility" label="可见性" width="120">
          <template #default="{ row }">
            <el-tag :type="visibilityTag(row.visibility)">
              {{ visibilityText(row.visibility) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="statusTag(row.status)">
              {{ statusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180">
          <template #default="{ row }">
            {{ formatDate(row.create_time) }}
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="320">
          <template #default="scope">
            <el-button link size="small" @click="previewContent(scope.row)">
              <el-icon><View /></el-icon>
              预览
            </el-button>
            <el-button 
              link
              size="small"
              @click="editContent(scope.row.id)"
            >
              <el-icon><Edit /></el-icon>
              编辑
            </el-button>
            <el-button 
              link
              size="small"
              @click="manageFiles(scope.row)"
            >
              <el-icon><Document /></el-icon>
              管理文件
            </el-button>
            <el-button 
              link
              size="small"
              @click="associateFiles(scope.row)"
            >
              <el-icon><Link /></el-icon>
              关联文件
            </el-button>
            <el-button 
              link
              size="small"
              @click="submitAudit(scope.row)"
            >
              <el-icon><Upload /></el-icon>
              提交审核
            </el-button>
            <el-button link size="small" @click="deleteContent(scope.row.id)">
              <el-icon><Delete /></el-icon>
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          :current-page="pagination.page"
          :page-size="pagination.pageSize"
          :page-sizes="[10, 20, 30, 50]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>

      <!-- 空状态 -->
      <div v-if="!loading && contentList.length === 0" class="empty-state">
        <el-icon><Folder /></el-icon>
        <h3>暂无内容</h3>
        <p>赶快上传或使用 AI 生成吧！</p>
        <el-button type="primary" @click="openUpload">上传文件</el-button>
      </div>

      <!-- 文件管理对话框 -->
      <el-dialog
        v-model="fileDialog.visible"
        title="管理关联文件"
        width="800px"
        :close-on-click-modal="false"
      >
        <div class="file-management">
          <div class="file-info">
            <h4>内容信息</h4>
            <p><strong>标题：</strong>{{ fileDialog.content?.name }}</p>
            <p><strong>来源：</strong>{{ fileDialog.content?.source_type === 'ai_generate' ? 'AI生成' : '上传' }}</p>
          </div>
          
          <div class="file-list">
            <h4>关联文件</h4>
            <div v-if="fileDialog.files?.length" class="file-items">
              <div 
                v-for="file in fileDialog.files" 
                :key="file.id" 
                class="file-item"
              >
                <div class="file-info">
                  <el-icon><Document /></el-icon>
                  <span class="file-name">{{ file.original_name }}</span>
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
                  <el-button link size="small" @click="downloadFile(file)">
                    <el-icon><Download /></el-icon>
                    下载
                  </el-button>
                  <el-button link size="small" @click="removeFile(file.id)">
                    <el-icon><Delete /></el-icon>
                    移除
                  </el-button>
                </div>
              </div>
            </div>
            <div v-else class="no-files">
              <p>暂无关联文件</p>
            </div>
          </div>
          
          <div class="add-file">
            <h4>添加文件</h4>
            <el-upload
              ref="uploadRef"
              :action="uploadUrl"
              :headers="uploadHeaders"
              :data="{ content_id: fileDialog.content?.id }"
              :on-success="handleFileUploadSuccess"
              :on-error="handleFileUploadError"
              :before-upload="beforeFileUpload"
              :show-file-list="false"
            >
              <el-button type="primary">
                <el-icon><Upload /></el-icon>
                上传文件
              </el-button>
            </el-upload>
          </div>
        </div>
        
        <template #footer>
          <el-button @click="fileDialog.visible = false">关闭</el-button>
        </template>
      </el-dialog>

      <!-- 关联文件对话框 -->
      <el-dialog
        v-model="associateDialog.visible"
        title="关联文件"
        width="800px"
        :close-on-click-modal="false"
      >
        <div class="associate-dialog">
          <div class="file-info">
            <h4>内容信息</h4>
            <p><strong>标题：</strong>{{ associateDialog.content?.name }}</p>
            <p><strong>来源：</strong>{{ associateDialog.content?.source_type === 'ai_generate' ? 'AI生成' : '上传' }}</p>
          </div>
          
          <div class="file-list">
            <h4>可用文件</h4>
            <div v-if="associateDialog.availableFiles?.length" class="file-items">
              <div 
                v-for="file in associateDialog.availableFiles" 
                :key="file.id" 
                class="file-item"
              >
                <div class="file-info">
                  <el-icon><Document /></el-icon>
                  <span class="file-name">{{ file.original_name }}</span>
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
                  <el-button link size="small" @click="selectFile(file)">
                    <el-icon><Check /></el-icon>
                    选择
                  </el-button>
                </div>
              </div>
            </div>
            <div v-else class="no-files">
              <p>暂无可用文件</p>
            </div>
          </div>
          
          <div class="selected-files">
            <h4>已选文件</h4>
            <div v-if="associateDialog.selectedFiles?.length" class="file-items">
              <div 
                v-for="file in associateDialog.selectedFiles" 
                :key="file.id" 
                class="file-item"
              >
                <div class="file-info">
                  <el-icon><Document /></el-icon>
                  <span class="file-name">{{ file.original_name }}</span>
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
                  <el-button link size="small" @click="removeFile(file.id)">
                    <el-icon><Delete /></el-icon>
                    移除
                  </el-button>
                </div>
              </div>
            </div>
            <div v-else class="no-files">
              <p>暂无已选文件</p>
            </div>
          </div>
        </div>
        
        <template #footer>
          <el-button @click="closeAssociateDialog">取消</el-button>
          <el-button type="primary" @click="confirmAssociate">确认</el-button>
        </template>
      </el-dialog>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Upload,
  MagicStick,
  Refresh,
  View,
  Delete,
  Folder,
  Switch,
  UploadFilled,
  Edit,
  Document,
  Download,
  Plus,
  Link,
  Check
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { contentApi } from '@/api/content'

const router = useRouter()

// 响应式数据
const loading = ref(false)
const spaceType = ref('personal')
const contentList = ref([])
const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})
const fileDialog = reactive({
  visible: false,
  content: null,
  files: []
})
const uploadRef = ref(null)
const uploadUrl = ref('')
const uploadHeaders = ref({})
const associateDialog = reactive({
  visible: false,
  content: null,
  availableFiles: [],
  selectedFiles: []
})

// 加载内容列表
const loadContent = async () => {
  try {
    loading.value = true
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize
    }
    const apiFunc = spaceType.value === 'personal' ? contentApi.getPersonalContent : contentApi.getCourseContent
    const res = await apiFunc(params)
    if (res.code === 200) {
      contentList.value = res.data.list || []
      pagination.total = res.data.total || 0
    }
  } catch (error) {
    console.error('获取内容失败', error)
    ElMessage.error('获取内容失败')
  } finally {
    loading.value = false
  }
}

// 刷新数据
const refreshData = () => {
  loadContent()
}

// 监听 spaceType 变化自动刷新
watch(spaceType, () => {
  pagination.page = 1
  loadContent()
})

// 分页处理
const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  loadContent()
}
const handleCurrentChange = (page) => {
  pagination.page = page
  loadContent()
}

// 预览内容
const previewContent = (row) => {
  window.open(`/preview/content/${row.id}`, '_blank')
}

// 提交审核
const submitAudit = async (row) => {
  try {
    await ElMessageBox.confirm('提交审核后内容将不可编辑，是否继续？', '确认提交', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const res = await contentApi.submitAudit({ content_id: row.id })
    if (res.code === 200) {
      ElMessage.success('提交成功')
      loadContent()
    } else {
      ElMessage.error(res.message || '提交失败')
    }
  } catch (err) {
    if (err !== 'cancel') {
      console.error(err)
    }
  }
}

// 切换可见性
const toggleContentVisibility = async (row) => {
  try {
    const newVisibility = row.visibility === 'public' ? 'leader' : 'public'
    const res = await contentApi.toggleVisibility({ content_id: row.id, visibility: newVisibility })
    if (res.code === 200) {
      ElMessage.success('已切换可见性')
      loadContent()
    }
  } catch (error) {
    console.error(error)
    ElMessage.error('操作失败')
  }
}

// 删除内容
const deleteContent = async (id) => {
  try {
    await ElMessageBox.confirm('删除后无法恢复，确定删除？', '确认删除', {
      confirmButtonText: '删除',
      cancelButtonText: '取消',
      type: 'warning'
    })
    const res = await contentApi.deleteContent(id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadContent()
    }
  } catch (err) {
    if (err !== 'cancel') {
      console.error(err)
    }
  }
}

// 上传文件
const openUpload = () => {
  router.push('/teacher/files/upload')
}

// AI 生成
const openAiGenerate = () => {
  router.push('/teacher/ai-tools')
}

// 新建内容
const createContent = () => {
  router.push('/teacher/content/create')
}

// 下载文件
const downloadFile = (row) => {
  try {
    let downloadUrl = ''
    
    if (row.file_path) {
      // 直接下载内容关联的文件
      downloadUrl = row.file_path
    } else if (row.related_files?.length) {
      // 下载关联的文件
      const file = row.related_files[0] // 下载第一个文件
      downloadUrl = file.file_path
    }
    
    if (downloadUrl) {
      const link = document.createElement('a')
      link.href = downloadUrl
      link.download = row.original_name || row.name
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

// 管理文件
const manageFiles = (row) => {
  fileDialog.content = row
  fileDialog.files = row.related_files || []
  fileDialog.visible = true
}

// 编辑内容
const editContent = (id) => {
  router.push(`/teacher/content/edit/${id}`)
}

// 关联文件
const associateFiles = (row) => {
  associateDialog.content = row
  associateDialog.visible = true
  associateDialog.selectedFiles = [] // 清空之前的选择
  loadAvailableFiles()
}

// 加载可关联的文件
const loadAvailableFiles = async () => {
  try {
    const res = await contentApi.getAvailableFiles()
    if (res.code === 200) {
      associateDialog.availableFiles = res.data || []
    }
  } catch (error) {
    console.error('获取可用文件失败：', error)
    ElMessage.error('获取可用文件失败')
  }
}

// 确认关联文件
const confirmAssociate = async () => {
  if (!associateDialog.selectedFiles.length) {
    ElMessage.warning('请选择要关联的文件')
    return
  }

  try {
    const res = await contentApi.associateFiles({
      content_id: associateDialog.content.id,
      file_ids: associateDialog.selectedFiles.map(file => file.id)
    })
    
    if (res.code === 200) {
      ElMessage.success('文件关联成功')
      closeAssociateDialog()
      loadContent() // 刷新列表
    } else {
      ElMessage.error(res.message || '关联失败')
    }
  } catch (error) {
    console.error('关联文件失败：', error)
    ElMessage.error('关联文件失败')
  }
}

// 关闭关联文件对话框
const closeAssociateDialog = () => {
  associateDialog.visible = false
  associateDialog.content = null
  associateDialog.selectedFiles = []
  associateDialog.availableFiles = []
}

// 工具函数
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleString('zh-CN')
}

const visibilityTag = (v) => {
  const map = { private: 'info', public: 'success', leader: 'warning' }
  return map[v] || 'info'
}
const visibilityText = (v) => {
  const map = { private: '仅自己', public: '公开', leader: '负责人' }
  if (!v) return spaceType.value === 'personal' ? '仅自己' : '-'
  return map[v] || '-'
}
const statusTag = (s) => {
  const map = { draft: 'info', pending: 'warning', approved: 'success' }
  return map[s] || 'info'
}
const statusText = (s) => {
  const map = { draft: '草稿', pending: '待审核', approved: '已通过' }
  return map[s] || s
}

const formatFileSize = (size) => {
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

const handleFileUploadSuccess = (response, file) => {
  // 处理上传成功后的逻辑
  console.log('上传成功', response, file)
}

const handleFileUploadError = (error) => {
  // 处理上传失败后的逻辑
  console.error('上传失败', error)
  ElMessage.error('上传失败')
}

const beforeFileUpload = (file) => {
  // 处理上传前的逻辑
  console.log('上传前', file)
  return true // 返回 true 表示允许上传
}

const removeFile = (fileId) => {
  // 从已选文件列表中移除
  const index = associateDialog.selectedFiles.findIndex(f => f.id === fileId)
  if (index > -1) {
    associateDialog.selectedFiles.splice(index, 1)
    ElMessage.success('文件已移除')
  }
}

const selectFile = (file) => {
  // 检查是否已经选择
  const exists = associateDialog.selectedFiles.find(f => f.id === file.id)
  if (exists) {
    ElMessage.warning('该文件已选择')
    return
  }
  
  // 添加到已选文件列表
  associateDialog.selectedFiles.push(file)
  ElMessage.success('文件已选择')
}

onMounted(() => {
  loadContent()
})
</script>

<style scoped>
.content-center-container {
  max-width: 1200px;
  margin: 0 auto;
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

.content-table {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-top: 10px;
}

.pagination-wrapper {
  margin: 20px 0;
  text-align: right;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
  color: #909399;
}
.empty-state h3 {
  margin: 8px 0 4px;
}

.file-management {
  padding: 20px;
}

.file-management h4 {
  margin-bottom: 12px;
  color: #303133;
  font-weight: 600;
}

.file-management .file-info {
  background: #f5f7fa;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.file-management .file-info p {
  margin: 4px 0;
  color: #606266;
}

.file-items {
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  padding: 12px;
  margin-bottom: 16px;
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.file-item:last-child {
  border-bottom: none;
}

.file-item .file-info {
  display: flex;
  align-items: center;
  background: none;
  padding: 0;
  margin: 0;
}

.file-item .file-name {
  margin-left: 8px;
  font-weight: 500;
  color: #303133;
}

.file-item .file-size {
  margin-left: 8px;
  color: #909399;
  font-size: 12px;
}

.file-item .file-actions {
  display: flex;
  gap: 8px;
}

.no-files {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.no-files .text-muted {
  color: #909399;
  font-size: 12px;
}

.related-files {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  align-items: center;
}

.related-files .el-tag {
  cursor: pointer;
  transition: all 0.2s;
}

.related-files .el-tag:hover {
  background-color: #409eff;
  color: white;
}

.add-file {
  border-top: 1px solid #e4e7ed;
  padding-top: 16px;
  margin-top: 20px;
}

.associate-dialog {
  padding: 20px;
}

.associate-dialog h4 {
  margin-bottom: 12px;
  color: #303133;
  font-weight: 600;
}

.associate-dialog .file-info {
  background: #f5f7fa;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.associate-dialog .file-info p {
  margin: 4px 0;
  color: #606266;
}

.selected-files {
  margin-top: 20px;
}

.file-list .file-items,
.selected-files .file-items {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  padding: 8px;
}

.file-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s;
}

.file-item:last-child {
  border-bottom: none;
}

.file-item:hover {
  background-color: #f5f7fa;
}

.file-item .file-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  background: none;
  padding: 0;
  margin: 0;
}

.file-item .file-name {
  font-weight: 500;
  color: #303133;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-item .file-size {
  color: #909399;
  font-size: 12px;
}

.file-item .file-actions {
  display: flex;
  gap: 4px;
}

.no-files {
  text-align: center;
  padding: 40px 20px;
  color: #909399;
}

.no-files p {
  margin: 0;
}
</style> 