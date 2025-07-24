<template>
  <div class="teacher-audit">
    <div class="page-header">
      <h2>教师审核</h2>
    </div>
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="姓名">
          <el-input v-model="searchForm.name" placeholder="请输入姓名" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 150px">
            <el-option label="待审核" value="pending" />
            <el-option label="已通过" value="approved" />
            <el-option label="已拒绝" value="rejected" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="table-card">
      <div class="table-header">
        <h3>教师审核列表</h3>
        <div class="table-actions">
          <el-button 
            type="primary" 
            :disabled="selectedRows.length === 0" 
            @click="handleBatchApprove"
          >
            批量通过 ({{ selectedRows.length }})
          </el-button>
        </div>
      </div>
      <el-table v-loading="loading" :data="auditList" stripe style="width: 100%" @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="姓名" width="120" />
        <el-table-column prop="email" label="邮箱" min-width="180" />
        <el-table-column prop="phone" label="电话" width="140" />
        <el-table-column prop="college" label="所属学院" width="140" />
        <el-table-column prop="title" label="职称" width="100" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'pending' ? 'warning' : (row.status === 'approved' ? 'success' : 'danger')">
              {{ row.status === 'pending' ? '待审核' : (row.status === 'approved' ? '已通过' : '已拒绝') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button v-if="row.status === 'pending'" type="success" size="small" @click="handleApprove(row)">通过</el-button>
            <el-button v-if="row.status === 'pending'" type="danger" size="small" @click="handleReject(row)">拒绝</el-button>
            <el-button v-if="row.status !== 'pending'" type="info" size="small" disabled>
              {{ row.status === 'approved' ? '已通过' : '已拒绝' }}
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pagination-wrapper">
        <el-pagination
          :current-page="pagination.currentPage"
          :page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getAuditList, approveTeacher, rejectTeacher, batchAudit } from '@/api/teacher'

export default {
  name: 'TeacherAudit',
  setup() {
    const loading = ref(false)
    const auditList = ref([])
    const searchForm = reactive({ name: '', status: 'pending' })
    const pagination = reactive({ currentPage: 1, pageSize: 10, total: 0 })
    const selectedRows = ref([])
    
    // 获取审核列表
    const fetchAuditList = async () => {
      loading.value = true
      try {
        const params = { 
          ...searchForm, 
          page: pagination.currentPage, 
          pageSize: pagination.pageSize 
        }
        const res = await getAuditList(params)
        const data = res.data || res
        auditList.value = data.list || data
        pagination.total = data.total || 0
      } catch (error) {
        console.error('获取审核列表失败:', error)
        ElMessage.error('获取审核列表失败')
        // 接口失败时清空数据，避免显示错误信息
        auditList.value = []
        pagination.total = 0
      } finally {
        loading.value = false
      }
    }
    
    onMounted(fetchAuditList)
    
    const handleSearch = () => { 
      pagination.currentPage = 1
      fetchAuditList() 
    }
    
    const handleReset = () => { 
      searchForm.name = ''
      searchForm.status = 'pending'
      pagination.currentPage = 1
      fetchAuditList() 
    }
    
    const handleSizeChange = (size) => { 
      pagination.pageSize = size
      fetchAuditList() 
    }
    
    const handleCurrentChange = (page) => { 
      pagination.currentPage = page
      fetchAuditList() 
    }
    
    // 通过审核
    const handleApprove = async (row) => {
      try {
        await approveTeacher(row.id)
        ElMessage.success('审核通过')
        fetchAuditList()
      } catch (error) {
        console.error('审核失败:', error)
        ElMessage.error('审核失败')
      }
    }
    
    // 拒绝审核
    const handleReject = async (row) => {
      try {
        const { value: reason } = await ElMessageBox.prompt(
          '请输入拒绝理由',
          '拒绝审核',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            inputPattern: /.+/,
            inputErrorMessage: '拒绝理由不能为空'
          }
        )
        
        await rejectTeacher(row.id, { reason })
        ElMessage.success('已拒绝')
        fetchAuditList()
      } catch (error) {
        if (error !== 'cancel') {
          console.error('拒绝失败:', error)
          ElMessage.error('拒绝失败')
        }
      }
    }
    
    // 批量审核
    const handleBatchApprove = async () => {
      if (selectedRows.value.length === 0) {
        ElMessage.warning('请选择要审核的记录')
        return
      }
      
      try {
        await ElMessageBox.confirm(
          `确定要批量通过选中的 ${selectedRows.value.length} 条记录吗？`,
          '批量审核',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )
        
        const teacher_ids = selectedRows.value.map(row => row.id)
        await batchAudit({ teacher_ids, action: 'approve' })
        ElMessage.success('批量审核成功')
        fetchAuditList()
        selectedRows.value = []
      } catch (error) {
        if (error !== 'cancel') {
          console.error('批量审核失败:', error)
          ElMessage.error('批量审核失败')
        }
      }
    }
    
    // 表格选择处理
    const handleSelectionChange = (selection) => {
      selectedRows.value = selection
    }
    
    return {
      loading,
      auditList,
      searchForm,
      pagination,
      selectedRows,
      handleSearch,
      handleReset,
      handleSizeChange,
      handleCurrentChange,
      handleApprove,
      handleReject,
      handleBatchApprove,
      handleSelectionChange
    }
  }
}
</script>

<style scoped>
.teacher-audit {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
  margin: 0;
  overflow-x: hidden;
  overflow-y: auto;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.table-header h3 {
  margin: 0;
  color: #333;
  font-size: 16px;
}

.table-actions {
  display: flex;
  gap: 8px;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.search-card { margin-bottom: 20px; }
.table-card { margin-bottom: 20px; border-radius: 16px; box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08); background: #fff; }
.el-table { border-radius: 12px; }
.pagination-wrapper { display: flex; justify-content: center; margin-top: 20px; }
.dialog-footer { display: flex; justify-content: flex-end; gap: 10px; }
</style> 