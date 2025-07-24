<template>
  <div class="content-library-container">
    <el-card class="library-header">
      <div class="header-content">
        <h2>📚 内容库管理</h2>
        <p>管理您的教学内容和AI生成的材料</p>
      </div>
    </el-card>

    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-row">
      <el-col :span="4" v-for="stat in statistics" :key="stat.key">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon">{{ stat.icon }}</div>
            <div class="stat-info">
              <div class="stat-number">{{ stat.value }}</div>
              <div class="stat-label">{{ stat.label }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 筛选和搜索 -->
    <el-card class="filter-section">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-input
            v-model="filters.keyword"
            placeholder="搜索内容名称"
            clearable
            @input="handleSearch"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </el-col>
        <el-col :span="4">
          <el-select v-model="filters.file_type" placeholder="文件类型" clearable @change="handleSearch">
            <el-option
              v-for="(label, value) in fileTypes"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-col>
        <el-col :span="4">
          <el-select v-model="filters.source_type" placeholder="来源类型" clearable @change="handleSearch">
            <el-option
              v-for="(label, value) in sourceTypes"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-col>
        <el-col :span="4">
          <el-select v-model="filters.status" placeholder="状态" clearable @change="handleSearch">
            <el-option
              v-for="(label, value) in statuses"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-col>
        <el-col :span="6">
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-col>
      </el-row>
    </el-card>

    <!-- 内容列表 -->
    <el-card class="content-list">
      <template #header>
        <div class="list-header">
          <span>内容列表</span>
          <div class="header-actions">
            <el-button type="primary" @click="showCreateDialog">新建内容</el-button>
            <el-button @click="batchExport">批量导出</el-button>
          </div>
        </div>
      </template>

      <el-table
        :data="contentList"
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="name" label="内容名称" min-width="200">
          <template #default="scope">
            <div class="content-name">
              <span class="name-text">{{ scope.row.name }}</span>
              <el-tag v-if="scope.row.source_type === 'ai_generate'" size="small" type="success">
                AI生成
              </el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="file_type_text" label="类型" width="100" />
        <el-table-column prop="source_type_text" label="来源" width="100" />
        <el-table-column prop="status_text" label="状态" width="100">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ scope.row.status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column prop="file_size_text" label="大小" width="100" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="scope">
            <el-button size="small" @click="viewContent(scope.row)">查看</el-button>
            <el-button size="small" @click="editContent(scope.row)">编辑</el-button>
            <el-dropdown @command="handleCommand">
              <el-button size="small">
                更多<el-icon class="el-icon--right"><arrow-down /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item :command="{ action: 'export', row: scope.row }">
                    导出Word
                  </el-dropdown-item>
                  <el-dropdown-item :command="{ action: 'submit', row: scope.row }">
                    提交审核
                  </el-dropdown-item>
                  <el-dropdown-item :command="{ action: 'delete', row: scope.row }">
                    删除
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          :current-page="pagination.page"
          :page-size="pagination.limit"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 内容详情对话框 -->
    <el-dialog
      v-model="contentDialog.visible"
      :title="contentDialog.title"
      width="80%"
      :before-close="closeContentDialog"
    >
      <div v-if="contentDialog.content" class="content-detail">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="内容名称">
            {{ contentDialog.content.name }}
          </el-descriptions-item>
          <el-descriptions-item label="文件类型">
            {{ contentDialog.content.file_type_text }}
          </el-descriptions-item>
          <el-descriptions-item label="来源类型">
            {{ contentDialog.content.source_type_text }}
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="getStatusType(contentDialog.content.status)">
              {{ contentDialog.content.status_text }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="创建时间">
            {{ contentDialog.content.create_time }}
          </el-descriptions-item>
          <el-descriptions-item label="文件大小">
            {{ contentDialog.content.file_size_text }}
          </el-descriptions-item>
        </el-descriptions>

        <div class="content-body">
          <h4>内容预览</h4>
          <el-input
            v-model="contentDialog.content.content"
            type="textarea"
            :rows="15"
            readonly
          />
        </div>
      </div>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="closeContentDialog">关闭</el-button>
          <el-button type="primary" @click="exportContent(contentDialog.content)">
            导出Word
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 编辑内容对话框 -->
    <el-dialog
      v-model="editDialog.visible"
      title="编辑内容"
      width="60%"
    >
      <el-form :model="editDialog.form" label-width="100px">
        <el-form-item label="内容名称">
          <el-input v-model="editDialog.form.name" />
        </el-form-item>
        <el-form-item label="内容">
          <el-input
            v-model="editDialog.form.content"
            type="textarea"
            :rows="15"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="editDialog.visible = false">取消</el-button>
          <el-button type="primary" @click="saveEdit">保存</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, ArrowDown } from '@element-plus/icons-vue'
import { teacherApi } from '@/api/user'

export default {
  name: 'ContentLibrary',
  components: {
    Search,
    ArrowDown
  },
  setup() {
    const contentList = ref([])
    const selectedContent = ref([])
    const statistics = ref({})
    const fileTypes = ref({})
    const sourceTypes = ref({})
    const statuses = ref({})

    const filters = reactive({
      keyword: '',
      file_type: '',
      source_type: '',
      status: ''
    })

    const pagination = reactive({
      page: 1,
      limit: 20,
      total: 0
    })

    const contentDialog = reactive({
      visible: false,
      title: '',
      content: null
    })

    const editDialog = reactive({
      visible: false,
      form: {
        id: null,
        name: '',
        content: ''
      }
    })

    // 统计卡片数据
    const statsCards = computed(() => [
      {
        key: 'total',
        icon: '📊',
        label: '总内容数',
        value: statistics.value.total_count || 0
      },
      {
        key: 'draft',
        icon: '📝',
        label: '草稿',
        value: statistics.value.draft_count || 0
      },
      {
        key: 'pending',
        icon: '⏳',
        label: '待审核',
        value: statistics.value.pending_count || 0
      },
      {
        key: 'approved',
        icon: '✅',
        label: '已通过',
        value: statistics.value.approved_count || 0
      },
      {
        key: 'ai_generated',
        icon: '🤖',
        label: 'AI生成',
        value: statistics.value.ai_generated_count || 0
      }
    ])

    // 加载内容列表
    const loadContentList = async () => {
      try {
        const params = {
          ...filters,
          page: pagination.page,
          limit: pagination.limit
        }
        
        const response = await teacherApi.getContentList(params)
        contentList.value = response.data.list || []
        pagination.total = response.data.total || 0
        fileTypes.value = response.data.file_types || {}
        sourceTypes.value = response.data.source_types || {}
        statuses.value = response.data.statuses || {}
      } catch (error) {
        ElMessage.error('加载内容列表失败')
      }
    }

    // 加载统计信息
    const loadStatistics = async () => {
      try {
        const response = await teacherApi.getContentStatistics()
        statistics.value = response.data || {}
      } catch (error) {
        ElMessage.error('加载统计信息失败')
      }
    }

    // 搜索
    const handleSearch = () => {
      pagination.page = 1
      loadContentList()
    }

    // 重置筛选
    const resetFilters = () => {
      Object.keys(filters).forEach(key => {
        filters[key] = ''
      })
      handleSearch()
    }

    // 分页处理
    const handleSizeChange = (size) => {
      pagination.limit = size
      pagination.page = 1
      loadContentList()
    }

    const handleCurrentChange = (page) => {
      pagination.page = page
      loadContentList()
    }

    // 选择变化
    const handleSelectionChange = (selection) => {
      selectedContent.value = selection
    }

    // 查看内容
    const viewContent = (row) => {
      contentDialog.content = row
      contentDialog.title = `查看内容 - ${row.name}`
      contentDialog.visible = true
    }

    // 编辑内容
    const editContent = (row) => {
      editDialog.form = {
        id: row.id,
        name: row.name,
        content: row.content
      }
      editDialog.visible = true
    }

    // 保存编辑
    const saveEdit = async () => {
      try {
        await teacherApi.updateContent(editDialog.form.id, editDialog.form)
        ElMessage.success('保存成功')
        editDialog.visible = false
        loadContentList()
      } catch (error) {
        ElMessage.error('保存失败：' + error.message)
      }
    }

    // 导出内容
    const exportContent = async (content) => {
      try {
        const response = await teacherApi.exportDocument({
          content_id: content.id,
          format: 'docx'
        })
        
        // 创建下载链接
        const link = document.createElement('a')
        link.href = response.data.download_url
        link.download = response.data.file_name
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        ElMessage.success('导出成功')
      } catch (error) {
        ElMessage.error('导出失败：' + error.message)
      }
    }

    // 提交审核
    const submitAudit = async (content) => {
      try {
        await ElMessageBox.confirm('确定要提交审核吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        
        await teacherApi.submitAudit({ content_id: content.id })
        ElMessage.success('提交审核成功')
        loadContentList()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('提交失败：' + error.message)
        }
      }
    }

    // 删除内容
    const deleteContent = async (content) => {
      try {
        await ElMessageBox.confirm('确定要删除这个内容吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        
        await teacherApi.deleteContent(content.id)
        ElMessage.success('删除成功')
        loadContentList()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('删除失败：' + error.message)
        }
      }
    }

    // 批量导出
    const batchExport = async () => {
      if (selectedContent.value.length === 0) {
        ElMessage.warning('请选择要导出的内容')
        return
      }

      try {
        for (const content of selectedContent.value) {
          await exportContent(content)
        }
        ElMessage.success('批量导出完成')
      } catch (error) {
        ElMessage.error('批量导出失败')
      }
    }

    // 处理下拉菜单命令
    const handleCommand = ({ action, row }) => {
      switch (action) {
        case 'export':
          exportContent(row)
          break
        case 'submit':
          submitAudit(row)
          break
        case 'delete':
          deleteContent(row)
          break
      }
    }

    // 关闭内容对话框
    const closeContentDialog = () => {
      contentDialog.visible = false
      contentDialog.content = null
    }

    // 显示创建对话框
    const showCreateDialog = () => {
      // 跳转到AI工具页面
      // 这里可以实现跳转逻辑
      ElMessage.info('请前往AI工具页面创建内容')
    }

    // 工具函数
    const getStatusType = (status) => {
      const types = {
        draft: 'info',
        pending: 'warning',
        approved: 'success',
        rejected: 'danger'
      }
      return types[status] || 'info'
    }

    onMounted(() => {
      loadContentList()
      loadStatistics()
    })

    return {
      contentList,
      selectedContent,
      statistics,
      fileTypes,
      sourceTypes,
      statuses,
      filters,
      pagination,
      contentDialog,
      editDialog,
      statsCards,
      handleSearch,
      resetFilters,
      handleSizeChange,
      handleCurrentChange,
      handleSelectionChange,
      viewContent,
      editContent,
      saveEdit,
      exportContent,
      submitAudit,
      deleteContent,
      batchExport,
      handleCommand,
      closeContentDialog,
      showCreateDialog,
      getStatusType
    }
  }
}
</script>

<style scoped>
.content-library-container {
  padding: 20px;
}

.library-header {
  margin-bottom: 20px;
}

.header-content h2 {
  margin: 0 0 10px 0;
  color: #303133;
}

.header-content p {
  margin: 0;
  color: #606266;
}

.stats-row {
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
  font-size: 24px;
  margin-right: 10px;
}

.stat-number {
  font-size: 18px;
  font-weight: bold;
  color: #409eff;
}

.stat-label {
  font-size: 12px;
  color: #606266;
}

.filter-section {
  margin-bottom: 20px;
}

.content-list {
  margin-bottom: 20px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.content-name {
  display: flex;
  align-items: center;
  gap: 8px;
}

.name-text {
  flex: 1;
}

.pagination-wrapper {
  margin-top: 20px;
  text-align: right;
}

.content-detail {
  max-height: 600px;
  overflow-y: auto;
}

.content-body {
  margin-top: 20px;
}

.content-body h4 {
  margin-bottom: 15px;
  color: #303133;
}
</style> 