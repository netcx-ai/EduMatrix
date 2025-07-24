<template>
  <div class="teachers-page">
    <Layout>
      <template #content>
        <div class="page-header">
          <h2>👨‍🏫 教师管理</h2>
          <el-button type="primary" @click="showCreateDialog">
            <el-icon><Plus /></el-icon>
            添加教师
          </el-button>
        </div>

        <!-- 搜索和筛选 -->
        <el-card class="search-card">
          <el-form :model="searchForm" inline>
            <el-form-item label="教师姓名">
              <el-input v-model="searchForm.name" placeholder="请输入教师姓名" clearable />
            </el-form-item>
            <el-form-item label="工号">
              <el-input v-model="searchForm.teacher_no" placeholder="请输入工号" clearable />
            </el-form-item>
            <el-form-item label="邮箱">
              <el-input v-model="searchForm.email" placeholder="请输入邮箱" clearable />
            </el-form-item>
            <el-form-item label="所属学院">
              <el-select v-model="searchForm.collegeId" placeholder="请选择学院" clearable>
                <el-option 
                  v-for="college in colleges" 
                  :key="college.id" 
                  :label="college.name" 
                  :value="college.id" 
                />
              </el-select>
            </el-form-item>
            <el-form-item label="状态">
              <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
                <el-option label="正常" value="active" />
                <el-option label="停用" value="inactive" />
                <el-option label="待审核" value="pending" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleSearch">搜索</el-button>
              <el-button @click="resetSearch">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <!-- 教师列表 -->
        <el-card class="list-card">
          <el-table 
            :data="teachers" 
            v-loading="loading"
            style="width: 100%"
          >
            <el-table-column prop="real_name" label="姓名" min-width="120" />
            <el-table-column prop="teacher_no" label="工号" min-width="120" />
            <el-table-column prop="email" label="邮箱" min-width="200" />
            <el-table-column prop="phone" label="电话" width="150" />
            <el-table-column prop="collegeName" label="所属学院" min-width="150" />
            <el-table-column prop="title" label="职称" width="100">
              <template #default="scope">
                {{ getTitleText(scope.row.title) }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态" width="100" align="center">
              <template #default="scope">
                <el-tag :type="getStatusType(scope.row.status)">
                  {{ getStatusText(scope.row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="lastLoginTime" label="最后登录" width="180" />
            <el-table-column prop="createTime" label="创建时间" width="180" />
            <el-table-column label="操作" width="280" fixed="right">
              <template #default="scope">
                <el-button size="small" @click="viewTeacher(scope.row)">查看</el-button>
                <el-button size="small" type="primary" @click="editTeacher(scope.row)">编辑</el-button>
                <el-button 
                  v-if="scope.row.status === 'pending'"
                  size="small" 
                  type="success" 
                  @click="approveTeacher(scope.row)"
                >审核</el-button>
                <el-button 
                  size="small" 
                  :type="scope.row.status === 'active' ? 'warning' : 'success'"
                  @click="toggleStatus(scope.row)"
                >
                  {{ scope.row.status === 'active' ? '停用' : '启用' }}
                </el-button>
                <el-button 
                  size="small" 
                  type="danger" 
                  @click="deleteTeacher(scope.row)"
                >删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <!-- 分页 -->
          <div class="pagination-wrapper">
            <el-pagination
              :current-page="pagination.current"
              :page-size="pagination.size"
              :page-sizes="[10, 20, 50, 100]"
              :total="pagination.total"
              layout="total, sizes, prev, pager, next, jumper"
              @size-change="handleSizeChange"
              @current-change="handleCurrentChange"
            />
          </div>
        </el-card>

        <!-- 添加/编辑教师对话框 -->
        <el-dialog
          v-model="dialogVisible"
          :title="dialogTitle"
          width="700px"
          @close="resetForm"
        >
          <el-form
            ref="formRef"
            :model="form"
            :rules="rules"
            label-width="100px"
          >
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="姓名" prop="real_name">
                  <el-input v-model="form.real_name" placeholder="请输入教师姓名" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="工号" prop="teacher_no">
                  <el-input v-model="form.teacher_no" placeholder="请输入工号" />
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="邮箱" prop="email">
                  <el-input v-model="form.email" placeholder="请输入邮箱" />
                </el-form-item>
              </el-col>
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="电话" prop="phone">
                  <el-input v-model="form.phone" placeholder="请输入电话" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="职称" prop="title">
                  <el-select v-model="form.title" placeholder="请选择职称">
                    <el-option label="教授" value="professor" />
                    <el-option label="副教授" value="associate_professor" />
                    <el-option label="讲师" value="lecturer" />
                    <el-option label="助教" value="assistant" />
                  </el-select>
                </el-form-item>
              </el-col>
            </el-row>
            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="所属学院" prop="collegeId">
                  <el-select v-model="form.collegeId" placeholder="请选择学院">
                    <el-option 
                      v-for="college in colleges" 
                      :key="college.id" 
                      :label="college.name" 
                      :value="college.id" 
                    />
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="状态" prop="status">
                  <el-radio-group v-model="form.status">
                    <el-radio label="active">正常</el-radio>
                    <el-radio label="inactive">停用</el-radio>
                  </el-radio-group>
                </el-form-item>
              </el-col>
            </el-row>
            <el-form-item label="简介" prop="bio">
              <el-input
                v-model="form.bio"
                type="textarea"
                :rows="3"
                placeholder="请输入教师简介"
              />
            </el-form-item>
          </el-form>
          <template #footer>
            <span class="dialog-footer">
              <el-button @click="dialogVisible = false">取消</el-button>
              <el-button type="primary" @click="submitForm" :loading="submitting">
                确定
              </el-button>
            </span>
          </template>
        </el-dialog>

        <!-- 教师详情对话框 -->
        <el-dialog
          v-model="detailVisible"
          title="教师详情"
          width="800px"
        >
          <div v-if="currentTeacher" class="teacher-detail">
            <el-descriptions :column="2" border>
              <el-descriptions-item label="姓名">{{ currentTeacher.real_name }}</el-descriptions-item>
              <el-descriptions-item label="工号">{{ currentTeacher.teacher_no }}</el-descriptions-item>
              <el-descriptions-item label="邮箱">{{ currentTeacher.email }}</el-descriptions-item>
              <el-descriptions-item label="电话">{{ currentTeacher.phone }}</el-descriptions-item>
              <el-descriptions-item label="职称">{{ getTitleText(currentTeacher.title) }}</el-descriptions-item>
              <el-descriptions-item label="所属学院">{{ currentTeacher.collegeName }}</el-descriptions-item>
              <el-descriptions-item label="状态">
                <el-tag :type="getStatusType(currentTeacher.status)">
                  {{ getStatusText(currentTeacher.status) }}
                </el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="最后登录">{{ currentTeacher.lastLoginTime }}</el-descriptions-item>
              <el-descriptions-item label="创建时间">{{ currentTeacher.createTime }}</el-descriptions-item>
              <el-descriptions-item label="简介" :span="2">{{ currentTeacher.bio }}</el-descriptions-item>
            </el-descriptions>
          </div>
        </el-dialog>

        <!-- 审核教师对话框 -->
        <el-dialog
          v-model="approvalVisible"
          title="审核教师"
          width="500px"
        >
          <div v-if="currentTeacher" class="approval-content">
            <p>确定要审核通过教师 <strong>{{ currentTeacher.real_name }}</strong> 吗？</p>
            <el-form :model="approvalForm" label-width="100px">
              <el-form-item label="审核备注">
                <el-input
                  v-model="approvalForm.remark"
                  type="textarea"
                  :rows="3"
                  placeholder="请输入审核备注（可选）"
                />
              </el-form-item>
            </el-form>
          </div>
          <template #footer>
            <span class="dialog-footer">
              <el-button @click="approvalVisible = false">取消</el-button>
              <el-button type="success" @click="submitApproval" :loading="submitting">
                审核通过
              </el-button>
            </span>
          </template>
        </el-dialog>
      </template>
    </Layout>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { schoolApi } from '@/api/school'

export default {
  name: 'Teachers',
  components: {
    Layout,
    Plus
  },
  setup() {
    const loading = ref(false)
    const submitting = ref(false)
    const dialogVisible = ref(false)
    const detailVisible = ref(false)
    const approvalVisible = ref(false)
    const dialogTitle = ref('添加教师')
    const isEdit = ref(false)
    const currentTeacher = ref(null)
    const formRef = ref()

    // 搜索表单
    const searchForm = reactive({
      name: '',
      teacher_no: '',
      email: '',
      collegeId: '',
      status: ''
    })

    // 分页
    const pagination = reactive({
      current: 1,
      size: 20,
      total: 0
    })

    // 表单数据
    const form = reactive({
      id: null,
      real_name: '',
      teacher_no: '',
      email: '',
      phone: '',
      title: '',
      collegeId: '',
      status: 'active',
      bio: ''
    })

    // 审核表单
    const approvalForm = reactive({
      remark: ''
    })

    // 表单验证规则
    const rules = {
      real_name: [
        { required: true, message: '请输入教师姓名', trigger: 'blur' },
        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
      ],
      teacher_no: [
        { required: true, message: '请输入工号', trigger: 'blur' },
        { min: 3, max: 20, message: '长度在 3 到 20 个字符', trigger: 'blur' }
      ],
      email: [
        { required: true, message: '请输入邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
      ],
      phone: [
        { required: true, message: '请输入电话', trigger: 'blur' }
      ],
      title: [
        { required: true, message: '请选择职称', trigger: 'change' }
      ],
      collegeId: [
        { required: true, message: '请选择所属学院', trigger: 'change' }
      ],
      status: [
        { required: true, message: '请选择状态', trigger: 'change' }
      ]
    }

    // 数据列表
    const teachers = ref([])
    const colleges = ref([])

    // 获取教师列表
    const getTeachers = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.current,
          limit: pagination.size,
          keyword: searchForm.name || searchForm.teacher_no || searchForm.email || '',
          college_id: searchForm.collegeId || '',
          status: searchForm.status || ''
        }
        const res = await schoolApi.getTeachers(params)
        teachers.value = res.data.list || []
        pagination.total = res.data.total || 0
      } catch (error) {
        ElMessage.error('获取教师列表失败')
        console.error(error)
      } finally {
        loading.value = false
      }
    }

    // 获取学院列表
    const getColleges = async () => {
      try {
        const res = await schoolApi.getColleges({ limit: 1000 })
        colleges.value = res.data.list || []
      } catch (error) {
        console.error('获取学院列表失败:', error)
      }
    }

    // 搜索
    const handleSearch = () => {
      pagination.current = 1
      getTeachers()
    }

    // 重置搜索
    const resetSearch = () => {
      Object.assign(searchForm, {
        name: '',
        teacher_no: '',
        email: '',
        collegeId: '',
        status: ''
      })
      handleSearch()
    }

    // 分页处理
    const handleSizeChange = (size) => {
      pagination.size = size
      pagination.current = 1
      getTeachers()
    }

    const handleCurrentChange = (current) => {
      pagination.current = current
      getTeachers()
    }

    // 状态处理
    const getStatusType = (status) => {
      const types = {
        active: 'success',
        inactive: 'info',
        pending: 'warning'
      }
      return types[status] || 'info'
    }

    const getStatusText = (status) => {
      const texts = {
        active: '正常',
        inactive: '停用',
        pending: '待审核'
      }
      return texts[status] || '未知'
    }

    // 获取职称显示文本
    const getTitleText = (title) => {
      const texts = {
        professor: '教授',
        associate_professor: '副教授',
        lecturer: '讲师',
        assistant: '助教'
      }
      return texts[title] || title
    }

    // 显示创建对话框
    const showCreateDialog = () => {
      dialogTitle.value = '添加教师'
      isEdit.value = false
      dialogVisible.value = true
    }

    // 编辑教师
    const editTeacher = async (row) => {
      try {
        loading.value = true
        const res = await schoolApi.getTeacher(row.id)
        if (res.code === 200) {
          dialogTitle.value = '编辑教师'
          isEdit.value = true
          // 确保字段名正确映射
          const teacherData = res.data
          Object.assign(form, {
            id: teacherData.id,
            real_name: teacherData.real_name,
            teacher_no: teacherData.teacher_no,
            email: teacherData.email,
            phone: teacherData.phone,
            title: teacherData.title,
            collegeId: teacherData.college_id,
            status: teacherData.status == 1 ? 'active' : (teacherData.status == 2 ? 'pending' : 'inactive'),
            bio: teacherData.bio || ''
          })
          dialogVisible.value = true
        } else {
          ElMessage.error('获取教师详情失败')
        }
      } catch (error) {
        ElMessage.error('获取教师详情失败')
        console.error(error)
      } finally {
        loading.value = false
      }
    }

    // 查看教师详情
    const viewTeacher = (row) => {
      currentTeacher.value = row
      detailVisible.value = true
    }

    // 审核教师
    const approveTeacher = (row) => {
      currentTeacher.value = row
      approvalForm.remark = ''
      approvalVisible.value = true
    }

    // 提交审核
    const submitApproval = async () => {
      try {
        submitting.value = true
        await schoolApi.approveTeacher(currentTeacher.value.id, approvalForm)
        ElMessage.success('审核成功')
        approvalVisible.value = false
        getTeachers()
      } catch (error) {
        ElMessage.error('审核失败')
        console.error(error)
      } finally {
        submitting.value = false
      }
    }

    // 切换状态
    const toggleStatus = async (row) => {
      const newStatus = row.status === 'active' ? 'inactive' : 'active'
      const action = newStatus === 'active' ? '启用' : '停用'
      
      try {
        await ElMessageBox.confirm(
          `确定要${action}教师"${row.real_name}"吗？`,
          '确认操作',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )

        await schoolApi.updateTeacher(row.id, { status: newStatus })
        ElMessage.success(`${action}成功`)
        getTeachers()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error(`${action}失败`)
          console.error(error)
        }
      }
    }

    // 删除教师
    const deleteTeacher = async (row) => {
      try {
        await ElMessageBox.confirm(
          `确定要删除教师"${row.real_name}"吗？此操作不可恢复。`,
          '确认删除',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )

        await schoolApi.deleteTeacher(row.id)
        ElMessage.success('删除成功')
        getTeachers()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('删除失败')
          console.error(error)
        }
      }
    }

    // 提交表单
    const submitForm = async () => {
      if (!formRef.value) return

      try {
        await formRef.value.validate()
        submitting.value = true

        if (isEdit.value) {
          await schoolApi.updateTeacher(form.id, form)
          ElMessage.success('更新成功')
        } else {
          await schoolApi.createTeacher(form)
          ElMessage.success('创建成功')
        }

        dialogVisible.value = false
        getTeachers()
      } catch (error) {
        if (error !== false) {
          ElMessage.error(isEdit.value ? '更新失败' : '创建失败')
          console.error(error)
        }
      } finally {
        submitting.value = false
      }
    }

    // 重置表单
    const resetForm = () => {
      if (formRef.value) {
        formRef.value.resetFields()
      }
      Object.assign(form, {
        id: null,
        real_name: '',
        teacher_no: '',
        email: '',
        phone: '',
        title: '',
        collegeId: '',
        status: 'active',
        bio: ''
      })
    }

    onMounted(() => {
      getTeachers()
      getColleges()
    })

    return {
      loading,
      submitting,
      dialogVisible,
      detailVisible,
      approvalVisible,
      dialogTitle,
      searchForm,
      pagination,
      form,
      approvalForm,
      rules,
      teachers,
      colleges,
      currentTeacher,
      formRef,
      getStatusType,
      getStatusText,
      getTitleText,
      handleSearch,
      resetSearch,
      handleSizeChange,
      handleCurrentChange,
      showCreateDialog,
      editTeacher,
      viewTeacher,
      approveTeacher,
      submitApproval,
      toggleStatus,
      deleteTeacher,
      submitForm,
      resetForm
    }
  }
}
</script>

<style scoped>
.teachers-page {
  padding: 20px;
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

.list-card {
  margin-bottom: 20px;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.teacher-detail {
  padding: 20px 0;
}

.approval-content {
  padding: 20px 0;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style> 