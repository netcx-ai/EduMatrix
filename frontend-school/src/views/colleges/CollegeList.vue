<template>
  <div class="college-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>学院管理</h2>
      <el-button type="primary" @click="showAddDialog">
        <el-icon><Plus /></el-icon>
        新增学院
      </el-button>
    </div>

    <!-- 搜索栏 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="学院名称">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入学院名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 150px">
            <el-option label="启用" value="active" />
            <el-option label="禁用" value="inactive" />
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

    <!-- 数据表格 -->
    <el-card class="table-card">
      <el-table
        v-loading="loading"
        :data="collegeList"
        stripe
        style="width: 100%"
      >
        <el-empty v-if="!loading && !collegeList.length" description="暂无数据" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="学院名称" min-width="150" />
        <el-table-column prop="code" label="学院代码" width="120" />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="teacherCount" label="教师数量" width="100" align="center" />
        <el-table-column prop="courseCount" label="课程数量" width="100" align="center" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
              {{ row.status === 'active' ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createdAt" label="创建时间" width="180" />
        <el-table-column label="操作" width="260" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="info" size="small" @click="handleDetail(row)">
              详情
            </el-button>
            <el-button
              :type="row.status === 'active' ? 'warning' : 'success'"
              size="small"
              @click="handleToggleStatus(row)"
            >
              {{ row.status === 'active' ? '禁用' : '启用' }}
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
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

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增学院' : '编辑学院'"
      width="600px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="学院名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入学院名称" />
        </el-form-item>
        <el-form-item label="学院代码" prop="code">
          <el-input v-model="form.code" placeholder="请输入学院代码" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入学院描述"
          />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio label="active">启用</el-radio>
            <el-radio label="inactive">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmit" :loading="submitLoading">
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getCollegeList, addCollege, updateCollege, deleteCollege, getCollegeDetail } from '@/api/college'

export default {
  name: 'CollegeList',
  setup() {
    const router = useRouter()
    
    // 数据状态
    const loading = ref(false)
    const collegeList = ref([])
    
    // 搜索表单
    const searchForm = reactive({
      name: '',
      status: ''
    })
    
    // 分页
    const pagination = reactive({
      currentPage: 1,
      pageSize: 10,
      total: 0
    })
    
    // 对话框
    const dialogVisible = ref(false)
    const dialogType = ref('add')
    const submitLoading = ref(false)
    const formRef = ref()
    
    // 表单数据
    const form = reactive({
      id: null,
      name: '',
      code: '',
      description: '',
      status: 'active'
    })
    
    // 表单验证规则
    const rules = {
      name: [
        { required: true, message: '请输入学院名称', trigger: 'blur' },
        { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
      ],
      code: [
        { required: true, message: '请输入学院代码', trigger: 'blur' },
        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
      ],
      status: [
        { required: true, message: '请选择状态', trigger: 'change' }
      ]
    }
    
    // 2. 数据获取与操作
    // onMounted 时获取学院列表
    const fetchCollegeList = async () => {
      loading.value = true
      try {
        const params = {
          name: searchForm.name,
          status: searchForm.status,
          page: pagination.currentPage,
          pageSize: pagination.pageSize
        }
        const res = await getCollegeList(params)
        // 兼容后端返回结构
        if (res.data) {
          collegeList.value = res.data.list || res.data
          pagination.total = res.data.total || 0
        } else {
          collegeList.value = res.list || res
          pagination.total = res.total || 0
        }
      } catch (e) {
        ElMessage.error('获取学院列表失败')
      } finally {
        loading.value = false
      }
    }
    onMounted(fetchCollegeList)

    const handleSearch = () => {
      pagination.currentPage = 1
      fetchCollegeList()
    }
    const handleReset = () => {
      searchForm.name = ''
      searchForm.status = ''
      pagination.currentPage = 1
      fetchCollegeList()
    }
    const handleSizeChange = (size) => {
      pagination.pageSize = size
      fetchCollegeList()
    }
    const handleCurrentChange = (page) => {
      pagination.currentPage = page
      fetchCollegeList()
    }
    const showAddDialog = () => {
      dialogType.value = 'add'
      Object.assign(form, { id: null, name: '', code: '', description: '', status: 'active' })
      dialogVisible.value = true
    }
    const handleEdit = (row) => {
      dialogType.value = 'edit'
      getCollegeDetail(row.id).then(res => {
        Object.assign(form, res.data || res)
        dialogVisible.value = true
      })
    }
    const handleDetail = (row) => {
      router.push(`/colleges/${row.id}`)
    }
    const handleSubmit = async () => {
      await formRef.value.validate()
      submitLoading.value = true
      try {
        if (dialogType.value === 'add') {
          await addCollege(form)
          ElMessage.success('新增成功')
        } else {
          await updateCollege(form.id, form)
          ElMessage.success('编辑成功')
        }
        dialogVisible.value = false
        fetchCollegeList()
      } catch (e) {
        ElMessage.error('操作失败')
      } finally {
        submitLoading.value = false
      }
    }
    const handleDelete = (row) => {
      ElMessageBox.confirm('确定要删除该学院吗？', '提示', { type: 'warning' })
        .then(async () => {
          await deleteCollege(row.id)
          ElMessage.success('删除成功')
          fetchCollegeList()
        })
        .catch(() => {})
    }
    const handleToggleStatus = async (row) => {
      await updateCollege(row.id, { ...row, status: row.status === 'active' ? 'inactive' : 'active' })
      ElMessage.success('状态已更新')
      fetchCollegeList()
    }
    
    return {
      loading,
      collegeList,
      searchForm,
      pagination,
      dialogVisible,
      dialogType,
      submitLoading,
      formRef,
      form,
      rules,
      handleSearch,
      handleReset,
      handleSizeChange,
      handleCurrentChange,
      showAddDialog,
      handleEdit,
      handleDetail,
      handleToggleStatus,
      handleDelete,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.college-list {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
  margin: 0;
  overflow-x: hidden;
  overflow-y: auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}

.search-card {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08);
  background: #fff;
}

.el-table {
  border-radius: 12px;
}

.el-dialog {
  border-radius: 16px;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style> 