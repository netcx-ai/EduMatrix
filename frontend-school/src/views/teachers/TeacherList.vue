<template>
  <div class="teacher-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>教师管理</h2>
      <el-button type="primary" @click="showAddDialog">
        <el-icon><Plus /></el-icon>
        新增教师
      </el-button>
    </div>

    <!-- 搜索栏 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="教师姓名">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入教师姓名"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="所属学院">
          <el-select v-model="searchForm.collegeId" placeholder="请选择学院" clearable style="width: 200px">
            <el-option
              v-for="college in collegeOptions"
              :key="college.id"
              :label="college.name"
              :value="college.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="职称">
          <el-select v-model="searchForm.title" placeholder="请选择职称" clearable style="width: 150px">
            <el-option
              v-for="title in titleOptions"
              :key="title.id"
              :label="title.name"
              :value="title.id"
            />
          </el-select>
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
        :data="teacherList"
        stripe
        style="width: 100%"
      >
        <el-empty v-if="!loading && !teacherList.length" description="暂无数据" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="姓名" width="120" />
        <el-table-column prop="email" label="邮箱" min-width="200" />
        <el-table-column prop="phone" label="电话" width="150" />
        <el-table-column prop="collegeName" label="所属学院" width="150" />
        <el-table-column prop="titleName" label="职称" width="100" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
              {{ row.status === 'active' ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="joinDate" label="入职时间" width="120" />
        <el-table-column prop="courseCount" label="课程数" width="100" align="center" />
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
      :title="dialogType === 'add' ? '新增教师' : '编辑教师'"
      width="700px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="姓名" prop="name">
              <el-input v-model="form.name" placeholder="请输入教师姓名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="邮箱" prop="email">
              <el-input v-model="form.email" placeholder="请输入邮箱" />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="电话" prop="phone">
              <el-input v-model="form.phone" placeholder="请输入电话" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="所属学院" prop="collegeId">
              <el-select v-model="form.collegeId" placeholder="请选择学院" style="width: 100%">
                <el-option
                  v-for="college in collegeOptions"
                  :key="college.id"
                  :label="college.name"
                  :value="college.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="职称" prop="title">
              <el-select v-model="form.title" placeholder="请选择职称" style="width: 100%">
                <el-option
                  v-for="title in titleOptions"
                  :key="title.id"
                  :label="title.name"
                  :value="title.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="入职时间">
              <el-date-picker
                v-model="form.joinDate"
                type="date"
                placeholder="选择入职时间"
                style="width: 100%"
                format="YYYY-MM-DD"
                value-format="YYYY-MM-DD"
              />
            </el-form-item>
          </el-col>
        </el-row>
        
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio label="active">启用</el-radio>
            <el-radio label="inactive">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item label="备注" prop="remark">
          <el-input
            v-model="form.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入备注信息"
          />
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
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getTeacherList, addTeacher, updateTeacher, deleteTeacher, getTeacherDetail, getTitleOptions } from '@/api/teacher'
import { getCollegeOptions } from '@/api/college'

export default {
  name: 'TeacherList',
  setup() {
    const route = useRoute()
    const router = useRouter()
    
    // 数据状态
    const loading = ref(false)
    const teacherList = ref([])
    
    // 学院选项
    const collegeOptions = ref([])
    
    // 职称选项
    const titleOptions = ref([])
    
    // 搜索表单
    const searchForm = reactive({
      name: '',
      collegeId: '',
      title: '',
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
      email: '',
      phone: '',
      collegeId: '',
      title: '',
      joinDate: '',
      status: 'active',
      remark: ''
    })
    
    // 表单验证规则
    const rules = {
      name: [
        { required: true, message: '请输入教师姓名', trigger: 'blur' },
        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
      ],
      email: [
        { required: true, message: '请输入邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
      ],
      phone: [
        { required: true, message: '请输入电话', trigger: 'blur' },
        { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
      ],
      collegeId: [
        { required: true, message: '请选择所属学院', trigger: 'change' }
      ],
      title: [
        { required: true, message: '请选择职称', trigger: 'change' }
      ],
      status: [
        { required: true, message: '请选择状态', trigger: 'change' }
      ]
    }
    
    // 2. 数据获取与操作
    const fetchTeacherList = async () => {
      loading.value = true
      try {
        const params = {
          name: searchForm.name,
          collegeId: searchForm.collegeId,
          title: searchForm.title,
          status: searchForm.status,
          page: pagination.currentPage,
          pageSize: pagination.pageSize
        }
        const res = await getTeacherList(params)
        if (res.data) {
          teacherList.value = res.data.list || res.data
          pagination.total = res.data.total || 0
        } else {
          teacherList.value = res.list || res
          pagination.total = res.total || 0
        }
      } catch (e) {
        ElMessage.error('获取教师列表失败')
      } finally {
        loading.value = false
      }
    }
    
    // 获取学院列表
    const fetchCollegeOptions = async () => {
      try {
        const res = await getCollegeOptions()
        if (res.data) {
          collegeOptions.value = res.data
        } else {
          collegeOptions.value = []
          ElMessage.warning('学院列表为空')
        }
      } catch (e) {
        console.error('获取学院列表失败:', e)
        ElMessage.error('学院列表加载失败，请刷新页面重试')
        collegeOptions.value = []
      }
    }
    
    // 获取职称选项
    const fetchTitleOptions = async () => {
      try {
        const res = await getTitleOptions()
        if (res.data && Array.isArray(res.data)) {
          titleOptions.value = res.data
        } else {
          titleOptions.value = []
          ElMessage.warning('职称列表为空')
        }
      } catch (e) {
        console.error('获取职称选项失败:', e)
        ElMessage.error('职称列表加载失败，请刷新页面重试')
        titleOptions.value = []
      }
    }
    
    onMounted(() => {
      fetchCollegeOptions()
      fetchTitleOptions()
      fetchTeacherList()
      
      // 检查是否有编辑参数
      const editId = route.query.edit
      if (editId) {
        // 延迟执行，确保数据加载完成
        setTimeout(() => {
          const teacher = teacherList.value.find(t => t.id == editId)
          if (teacher) {
            handleEdit(teacher)
          }
        }, 500)
      }
    })
    
    const handleSearch = () => {
      pagination.currentPage = 1
      fetchTeacherList()
    }
    
    const handleReset = () => {
      searchForm.name = ''
      searchForm.collegeId = ''
      searchForm.title = ''
      searchForm.status = ''
      pagination.currentPage = 1
      fetchTeacherList()
    }
    
    const handleSizeChange = (size) => {
      pagination.pageSize = size
      fetchTeacherList()
    }
    
    const handleCurrentChange = (page) => {
      pagination.currentPage = page
      fetchTeacherList()
    }
    
    const showAddDialog = () => {
      dialogType.value = 'add'
      Object.assign(form, { id: null, name: '', email: '', phone: '', collegeId: '', title: '', joinDate: '', status: 'active', remark: '' })
      dialogVisible.value = true
    }
    
    const handleEdit = (row) => {
      dialogType.value = 'edit'
      getTeacherDetail(row.id).then(res => {
        const teacherData = res.data || res
        Object.assign(form, teacherData)
        // 确保职称字段是数字类型
        form.title = Number(teacherData.title)
        dialogVisible.value = true
      })
    }
    
    const handleDetail = (row) => {
      router.push(`/teachers/${row.id}`)
    }
    
    const handleSubmit = async () => {
      await formRef.value.validate()
      submitLoading.value = true
      try {
        if (dialogType.value === 'add') {
          // 新增时进行字段映射
          const submitData = {
            ...form,
            real_name: form.name,
            college_id: form.collegeId,
            hire_date: form.joinDate
          }
          await addTeacher(submitData)
          ElMessage.success('新增成功')
        } else {
          // 编辑时进行字段映射
          const submitData = {
            ...form,
            real_name: form.name,
            college_id: form.collegeId,
            hire_date: form.joinDate
          }
          await updateTeacher(form.id, submitData)
          ElMessage.success('编辑成功')
        }
        dialogVisible.value = false
        fetchTeacherList()
      } catch (e) {
        ElMessage.error('操作失败')
      } finally {
        submitLoading.value = false
      }
    }
    
    const handleDelete = (row) => {
      ElMessageBox.confirm('确定要删除该教师吗？', '提示', { type: 'warning' })
        .then(async () => {
          await deleteTeacher(row.id)
          ElMessage.success('删除成功')
          fetchTeacherList()
        })
        .catch(() => {})
    }
    
    const handleToggleStatus = async (row) => {
      const newStatus = row.status === 'active' ? 'inactive' : 'active'
      const statusText = newStatus === 'active' ? '启用' : '禁用'
      
      try {
        await ElMessageBox.confirm(
          `确定要${statusText}该教师吗？`,
          '确认操作',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )
        
        await updateTeacher(row.id, { ...row, status: newStatus })
        ElMessage.success(`${statusText}成功`)
      fetchTeacherList()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('操作失败')
          console.error('状态变更失败:', error)
        }
      }
    }
    
    return {
      loading,
      teacherList,
      collegeOptions,
      titleOptions,
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
.teacher-list {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
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