<template>
  <Layout>
    <div class="files-container">
      <!-- 页面标题和操作 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">文件管理</h1>
          <p class="page-subtitle">管理您的教学文件和资源</p>
        </div>
        <div class="header-right">
          <el-button type="primary" @click="uploadFile">
            <el-icon><Upload /></el-icon>
            上传文件
          </el-button>
          <el-button @click="refreshData">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </div>

      <!-- 文件类型统计 -->
      <div class="file-type-stats">
        <div class="type-stat-item" @click="filterByType('')">
          <div class="type-icon all">
            <el-icon><Folder /></el-icon>
          </div>
          <div class="type-info">
            <div class="type-count">{{ stats.totalFiles || 0 }}</div>
            <div class="type-label">全部文件</div>
          </div>
        </div>
        <div class="type-stat-item" @click="filterByType('document')">
          <div class="type-icon document">
            <el-icon><Document /></el-icon>
          </div>
          <div class="type-info">
            <div class="type-count">{{ stats.documentCount || 0 }}</div>
            <div class="type-label">文档</div>
          </div>
        </div>
        <div class="type-stat-item" @click="filterByType('image')">
          <div class="type-icon image">
            <el-icon><Picture /></el-icon>
          </div>
          <div class="type-info">
            <div class="type-count">{{ stats.imageCount || 0 }}</div>
            <div class="type-label">图片</div>
          </div>
        </div>
        <div class="type-stat-item" @click="filterByType('video')">
          <div class="type-icon video">
            <el-icon><VideoPlay /></el-icon>
          </div>
          <div class="type-info">
            <div class="type-count">{{ stats.videoCount || 0 }}</div>
            <div class="type-label">视频</div>
          </div>
        </div>
        <div class="type-stat-item" @click="filterByType('audio')">
          <div class="type-icon audio">
            <el-icon><Headset /></el-icon>
          </div>
          <div class="type-info">
            <div class="type-count">{{ stats.audioCount || 0 }}</div>
            <div class="type-label">音频</div>
          </div>
        </div>
        <div class="type-stat-item" @click="filterByType('other')">
          <div class="type-icon other">
            <el-icon><Files /></el-icon>
          </div>
          <div class="type-info">
            <div class="type-count">{{ stats.otherCount || 0 }}</div>
            <div class="type-label">其他</div>
          </div>
        </div>
      </div>

      <!-- 搜索和筛选 -->
      <div class="search-section">
        <div class="search-left">
          <el-input
            v-model="searchForm.keyword"
            placeholder="搜索文件名"
            class="search-input"
            clearable
            @keyup.enter="handleSearch"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          <el-select v-model="searchForm.type" placeholder="文件类型" clearable class="type-select">
            <el-option label="全部类型" value="" />
            <el-option label="文档" value="document" />
            <el-option label="图片" value="image" />
            <el-option label="视频" value="video" />
            <el-option label="音频" value="audio" />
            <el-option label="其他" value="other" />
          </el-select>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="resetSearch">重置</el-button>
        </div>
        <div class="search-right">
          <el-button @click="toggleViewMode">
            <el-icon><component :is="viewMode === 'grid' ? List : Grid" /></el-icon>
            {{ viewMode === 'grid' ? '列表视图' : '网格视图' }}
          </el-button>
        </div>
      </div>

      <!-- 文件列表 -->
      <div class="files-content">
        <!-- 网格视图 -->
        <div v-if="viewMode === 'grid'" class="files-grid">
          <div 
            v-for="file in filesList" 
            :key="file.id" 
            class="file-card"
            :class="`file-type-${file.file_category || 'other'}`"
            @click="viewFile(file)"
          >
            <div class="file-icon">
              <el-icon><component :is="getFileIcon(file.file_category || file.type)" /></el-icon>
            </div>
            <div class="file-info">
              <h4 class="file-name" :title="file.original_name || file.file_name">{{ file.original_name || file.file_name }}</h4>
              <p class="file-meta">
                <el-tag :type="getFileTypeTag(file.file_category || file.type)" size="small">
                  {{ getFileTypeText(file.file_category || file.type) }}
                </el-tag>
                <span class="file-size">{{ formatFileSize(file.file_size) }}</span>
              </p>
              <p class="file-date">{{ formatDate(file.create_time) }}</p>
            </div>
            <div class="file-actions">
              <el-button link @click.stop="previewFile(file)" v-if="canPreview(file)">
                <el-icon><View /></el-icon>
              </el-button>
              <el-button link @click.stop="downloadFile(file)">
                <el-icon><Download /></el-icon>
              </el-button>
              <el-button link @click.stop="deleteFile(file.id)">
                <el-icon><Delete /></el-icon>
              </el-button>
            </div>
          </div>
        </div>

        <!-- 列表视图 -->
        <div v-else class="files-table-wrapper">
          <el-table
            v-loading="loading"
            :data="filesList"
            stripe
            class="files-table"
          >
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="original_name" label="文件名" min-width="250">
              <template #default="{ row }">
                <div class="file-name-cell">
                  <el-icon class="file-type-icon" :class="`file-type-${row.file_category || row.type}`">
                    <component :is="getFileIcon(row.file_category || row.type)" />
                  </el-icon>
                  <span :title="row.original_name || row.file_name">{{ row.original_name || row.file_name }}</span>
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="file_category" label="类型" width="120">
              <template #default="{ row }">
                <el-tag :type="getFileTypeTag(row.file_category || row.type)" size="small">
                  {{ getFileTypeText(row.file_category || row.type) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="file_size" label="大小" width="120">
              <template #default="{ row }">
                {{ formatFileSize(row.file_size) }}
              </template>
            </el-table-column>
            <el-table-column prop="storage_type" label="存储" width="100">
              <template #default="{ row }">
                <el-tag :type="getStorageTypeTag(row.storage_type)" size="small">
                  {{ getStorageTypeText(row.storage_type) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="上传时间" width="180">
              <template #default="{ row }">
                {{ formatDate(row.create_time) }}
              </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="200">
              <template #default="scope">
                <el-button link @click="previewFile(scope.row)" v-if="canPreview(scope.row)">
                  <el-icon><View /></el-icon>
                  预览
                </el-button>
                <el-button link @click="downloadFile(scope.row)">
                  <el-icon><Download /></el-icon>
                  下载
                </el-button>
                <el-button link @click="deleteFile(scope.row.id)">
                  <el-icon><Delete /></el-icon>
                  删除
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <!-- 分页 -->
        <div class="pagination-wrapper">
          <el-pagination
            :current-page="pagination.page"
            :page-size="pagination.pageSize"
            :page-sizes="[12, 24, 48, 96]"
            :total="pagination.total"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>

      <!-- 空状态 -->
      <div v-if="!loading && filesList.length === 0" class="empty-state">
        <el-icon><Folder /></el-icon>
        <h3>暂无文件</h3>
        <p>开始上传您的第一个文件吧！</p>
        <el-button type="primary" @click="uploadFile">上传文件</el-button>
      </div>

      <!-- 统计信息 -->
      <div class="stats-section">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-number">{{ stats.totalFiles || 0 }}</div>
            <div class="stat-label">总文件数</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ formatFileSize(stats.totalSize || 0) }}</div>
            <div class="stat-label">总存储空间</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">{{ stats.todayUploads || 0 }}</div>
            <div class="stat-label">今日上传</div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  Upload, 
  Refresh, 
  Search, 
  List, 
  Grid, 
  Download, 
  Delete, 
  View, 
  Folder,
  Document,
  Picture,
  VideoPlay,
  Headset,
  Files
} from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { teacherApi } from '@/api/user'
import { fileBase } from '@/utils/env'
import { downloadById } from '@/utils/file'

const router = useRouter()

// 响应式数据
const loading = ref(false)
const filesList = ref([])
const stats = ref({})
const viewMode = ref('grid')
const searchForm = reactive({
  keyword: '',
  type: ''
})
const pagination = reactive({
  page: 1,
  pageSize: 12,
  total: 0
})

// 获取文件列表
const loadFiles = async () => {
  try {
    loading.value = true
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize,
      keyword: searchForm.keyword,
      category: searchForm.type
    }
    
    const response = await teacherApi.getFiles(params)
    if (response.code === 200) {
      filesList.value = response.data.list || []
      pagination.total = response.data.total || 0
    }
  } catch (error) {
    console.error('获取文件列表失败：', error)
    ElMessage.error('获取文件列表失败')
  } finally {
    loading.value = false
  }
}

// 获取统计信息
const loadStats = async () => {
  try {
    const response = await teacherApi.getStatistics()
    if (response.code === 200) {
      stats.value = {
        totalFiles: response.data.fileCount || 0,
        totalSize: response.data.totalSize || 0,
        todayUploads: response.data.todayUploads || 0,
        documentCount: response.data.documentCount || 0,
        imageCount: response.data.imageCount || 0,
        videoCount: response.data.videoCount || 0,
        audioCount: response.data.audioCount || 0,
        otherCount: response.data.otherCount || 0
      }
    }
  } catch (error) {
    console.error('获取统计信息失败：', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadFiles()
}

// 重置搜索
const resetSearch = () => {
  searchForm.keyword = ''
  searchForm.type = ''
  pagination.page = 1
  loadFiles()
}

// 刷新数据
const refreshData = () => {
  loadFiles()
  loadStats()
}

// 切换视图模式
const toggleViewMode = () => {
  viewMode.value = viewMode.value === 'grid' ? 'list' : 'grid'
}

// 分页处理
const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  loadFiles()
}

const handleCurrentChange = (page) => {
  pagination.page = page
  loadFiles()
}

// 格式化文件大小
const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// 格式化日期
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

// 获取文件图标
const getFileIcon = (type) => {
  const iconMap = {
    document: 'Document',
    image: 'Picture',
    video: 'VideoPlay',
    audio: 'Headset',
    other: 'Files'
  }
  return iconMap[type] || 'Files'
}

// 获取文件类型标签
const getFileTypeTag = (type) => {
  const tagMap = {
    document: 'primary',
    image: 'success',
    video: 'warning',
    audio: 'info',
    other: 'info'
  }
  return tagMap[type] || 'info'
}

// 获取文件类型文本
const getFileTypeText = (type) => {
  const textMap = {
    document: '文档',
    image: '图片',
    video: '视频',
    audio: '音频',
    other: '其他'
  }
  return textMap[type] || '其他'
}

// 获取存储类型标签
const getStorageTypeTag = (type) => {
  const tagMap = {
    local: 'success',
    oss: 'warning',
    cos: 'info'
  }
  return tagMap[type] || 'info'
}

// 获取存储类型文本
const getStorageTypeText = (type) => {
  const textMap = {
    local: '本地',
    oss: '阿里云',
    cos: '腾讯云'
  }
  return textMap[type] || '未知'
}

// 判断是否可以预览
const canPreview = (file) => {
  const previewableTypes = ['image', 'document']
  return previewableTypes.includes(file.file_category || file.type)
}

// 按类型筛选
const filterByType = (type) => {
  searchForm.type = type
  pagination.page = 1
  loadFiles()
}

// 预览文件
const previewFile = (file) => {
  const url = getFileUrl(file)
  if (!url) {
    ElMessage.error('无法获取文件链接')
    return
  }
  
  if (file.file_category === 'image' || file.type === 'image') {
    // 图片直接在新窗口打开
    window.open(url, '_blank')
  } else if (file.file_category === 'document' || file.type === 'document') {
    // 文档尝试在线预览
    const previewUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(url)}`
    window.open(previewUrl, '_blank')
  } else {
    // 其他类型直接下载
    downloadFile(file)
  }
}

// 页面跳转方法
const uploadFile = () => {
  router.push('/teacher/files/upload')
}

// 构造文件完整 URL
const getFileUrl = (file) => {
  // 优先使用后端返回的完整URL
  if (file.url) return file.url
  if (file.full_url) return file.full_url
  if (file.download_url) return file.download_url
  
  // 兼容旧版本，使用fileBase拼接
  if (file.file_path) return `${fileBase}/${file.file_path.replace(/^([/\\])+/, '')}`
  return ''
}

const viewFile = (file) => {
  previewFile(file)
}

const downloadFile = async (file) => {
  try {
    downloadById(file)
    ElMessage.success('开始下载文件')
  } catch (error) {
    console.error('下载文件失败：', error)
    ElMessage.error('下载文件失败')
  }
}

const deleteFile = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个文件吗？删除后无法恢复。', '确认删除', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const response = await teacherApi.deleteFile(id)
    if (response.code === 200) {
      ElMessage.success('删除成功')
      loadFiles()
      loadStats()
    } else {
      ElMessage.error(response.message || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除文件失败：', error)
      ElMessage.error('删除失败')
    }
  }
}

// 生命周期
onMounted(() => {
  loadFiles()
  loadStats()
})
</script>

<style scoped>
.files-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* 页面标题 */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
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

/* 搜索区域 */
.search-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-left {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-input {
  width: 300px;
}

.type-select {
  width: 150px;
}

/* 文件内容 */
.files-content {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

/* 网格视图 */
.files-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  padding: 24px;
}

.file-card {
  border: 1px solid #f0f0f0;
  border-radius: 8px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.file-card:hover {
  border-color: #409eff;
  box-shadow: 0 2px 8px rgba(64, 158, 255, 0.2);
}

.file-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  margin-bottom: 12px;
}

.file-info {
  margin-bottom: 12px;
}

.file-name {
  font-size: 14px;
  font-weight: 500;
  color: #303133;
  margin: 0 0 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.file-size, .file-date {
  font-size: 12px;
  color: #909399;
  margin: 0 0 2px;
}

.file-actions {
  display: flex;
  gap: 8px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.file-card:hover .file-actions {
  opacity: 1;
}

/* 列表视图 */
.files-table-wrapper {
  padding: 0;
}

.files-table {
  width: 100%;
}

.file-name-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.file-type-icon {
  color: #409eff;
  font-size: 16px;
}

/* 分页 */
.pagination-wrapper {
  padding: 20px;
  display: flex;
  justify-content: center;
}

/* 统计信息 */
.stats-section {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  text-align: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #409eff;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

/* 空状态 */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.empty-state .el-icon {
  font-size: 64px;
  color: #c0c4cc;
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 18px;
  color: #606266;
  margin: 0 0 8px;
}

.empty-state p {
  font-size: 14px;
  color: #909399;
  margin: 0 0 20px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }
  
  .header-right {
    width: 100%;
    justify-content: flex-end;
  }
  
  .search-section {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .search-left {
    flex-direction: column;
    gap: 12px;
  }
  
  .search-input {
    width: 100%;
  }
  
  .type-select {
    width: 100%;
  }
  
  .files-grid {
    grid-template-columns: 1fr;
    padding: 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* 文件类型统计 */
.file-type-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.type-stat-item {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 2px solid transparent;
}

.type-stat-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.type-stat-item.active {
  border-color: var(--el-color-primary);
}

.type-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.type-icon.all {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.type-icon.document {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.type-icon.image {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.type-icon.video {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.type-icon.audio {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.type-icon.other {
  background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
  color: #333;
}

.type-info {
  flex: 1;
}

.type-count {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  line-height: 1;
}

.type-label {
  font-size: 14px;
  color: #909399;
  margin-top: 4px;
}

/* 文件卡片类型样式 */
.file-card.file-type-document {
  border-left: 4px solid #f093fb;
}

.file-card.file-type-image {
  border-left: 4px solid #4facfe;
}

.file-card.file-type-video {
  border-left: 4px solid #43e97b;
}

.file-card.file-type-audio {
  border-left: 4px solid #fa709a;
}

.file-card.file-type-other {
  border-left: 4px solid #a8edea;
}

.file-type-icon.file-type-document {
  color: #f093fb;
}

.file-type-icon.file-type-image {
  color: #4facfe;
}

.file-type-icon.file-type-video {
  color: #43e97b;
}

.file-type-icon.file-type-audio {
  color: #fa709a;
}

.file-type-icon.file-type-other {
  color: #a8edea;
}

/* 文件卡片优化 */
.file-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #f0f0f0;
  position: relative;
  overflow: hidden;
}

.file-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.file-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--el-color-primary), var(--el-color-success));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.file-card:hover::before {
  opacity: 1;
}

.file-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #606266;
  margin-bottom: 16px;
}

.file-info {
  flex: 1;
  min-width: 0;
}

.file-name {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.file-size {
  font-size: 12px;
  color: #909399;
}

.file-date {
  font-size: 12px;
  color: #c0c4cc;
}

.file-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.file-card:hover .file-actions {
  opacity: 1;
}

/* 表格优化 */
.files-table .file-name-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.files-table .file-type-icon {
  font-size: 20px;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f5f7fa;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style> 