<template>
  <div class="colleges-page">
    <Layout>
      <template #content>
        <div class="page-header">
          <h2>🏢 学院管理</h2>
          <el-button type="primary" @click="showCreateDialog">
            <el-icon><Plus /></el-icon>
            添加学院
          </el-button>
        </div>

        <!-- 搜索和筛选 -->
        <el-card class="search-card">
          <el-form :model="searchForm" inline>
            <el-form-item label="学院名称">
              <el-input v-model="searchForm.name" placeholder="请输入学院名称" clearable />
            </el-form-item>
            <el-form-item label="学院代码">
              <el-input v-model="searchForm.code" placeholder="请输入学院代码" clearable />
            </el-form-item>
            <el-form-item label="状态">
              <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
                <el-option label="正常" value="active" />
                <el-option label="停用" value="inactive" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleSearch">搜索</el-button>
              <el-button @click="resetSearch">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <!-- 学院列表 -->
        <el-card class="list-card">
          <el-table 
            :data="colleges" 
            v-loading="loading"
            style="width: 100%"
          >
            <el-table-column prop="name" label="学院名称" min-width="150" />
            <el-table-column prop="code" label="学院代码" width="120" />
            <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
            <el-table-column prop="teacherCount" label="教师数量" width="100" align="center" />
            <el-table-column prop="courseCount" label="课程数量" width="100" align="center" />
            <el-table-column prop="status" label="状态" width="100" align="center">
              <template #default="scope">
                <el-tag :type="scope.row.status === 'active' || scope.row.status === 1 ? 'success' : 'info'">
                  {{ getStatusText(scope.row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="createTime" label="创建时间" width="180" />
            <el-table-column label="操作" width="250" fixed="right">
              <template #default="scope">
                <el-button size="small" @click="viewCollege(scope.row)">查看</el-button>
                <el-button size="small" type="primary" @click="editCollege(scope.row)">编辑</el-button>
                <el-button size="small" type="warning" @click="manageTeachers(scope.row)">教师管理</el-button>
                <el-button 
                  size="small" 
                  type="danger" 
                  @click="deleteCollege(scope.row)"
                  :disabled="scope.row.teacherCount > 0"
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

        <!-- 添加/编辑学院对话框 -->
        <el-dialog
          v-model="dialogVisible"
          :title="dialogTitle"
          width="600px"
          @close="resetForm"
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
                <el-radio label="active">正常</el-radio>
                <el-radio label="inactive">停用</el-radio>
              </el-radio-group>
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

        <!-- 学院详情对话框 -->
        <el-dialog
          v-model="detailVisible"
          title="学院详情"
          width="800px"
        >
          <div v-if="currentCollege" class="college-detail">
            <el-descriptions :column="2" border>
              <el-descriptions-item label="学院名称">{{ currentCollege.name }}</el-descriptions-item>
              <el-descriptions-item label="学院代码">{{ currentCollege.code }}</el-descriptions-item>
              <el-descriptions-item label="状态">
                <el-tag :type="currentCollege.status === 'active' || currentCollege.status === 1 ? 'success' : 'info'">
                  {{ getStatusText(currentCollege.status) }}
                </el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="教师数量">{{ currentCollege.teacherCount }}</el-descriptions-item>
              <el-descriptions-item label="课程数量">{{ currentCollege.courseCount }}</el-descriptions-item>
              <el-descriptions-item label="创建时间">{{ currentCollege.createTime }}</el-descriptions-item>
              <el-descriptions-item label="描述" :span="2">{{ currentCollege.description }}</el-descriptions-item>
            </el-descriptions>
          </div>
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
  name: 'Colleges',
  components: {
    Layout,
    Plus
  },
  setup() {
    const loading = ref(false)
    const submitting = ref(false)
    const dialogVisible = ref(false)
    const detailVisible = ref(false)
    const dialogTitle = ref('添加学院')
    const isEdit = ref(false)
    const currentCollege = ref(null)
    const formRef = ref()

    // 搜索表单
    const searchForm = reactive({
      name: '',
      code: '',
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

    // 学院列表
    const colleges = ref([])

    // 获取状态显示文本
    const getStatusText = (status) => {
      const texts = {
        active: '正常',
        inactive: '停用',
        1: '正常',
        0: '停用'
      }
      return texts[status] || '未知'
    }

    // 获取学院列表
    const getColleges = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.current,
          limit: pagination.size,
          keyword: searchForm.name || searchForm.code || ''
        }
        const res = await schoolApi.getColleges(params)
        colleges.value = res.data.list || []
        pagination.total = res.data.total || 0
      } catch (error) {
        ElMessage.error('获取学院列表失败')
        console.error(error)
      } finally {
        loading.value = false
      }
    }

    // 搜索
    const handleSearch = () => {
      pagination.current = 1
      getColleges()
    }

    // 重置搜索
    const resetSearch = () => {
      Object.assign(searchForm, {
        name: '',
        code: '',
        status: ''
      })
      handleSearch()
    }

    // 分页处理
    const handleSizeChange = (size) => {
      pagination.size = size
      pagination.current = 1
      getColleges()
    }

    const handleCurrentChange = (current) => {
      pagination.current = current
      getColleges()
    }

    // 显示创建对话框
    const showCreateDialog = () => {
      dialogTitle.value = '添加学院'
      isEdit.value = false
      dialogVisible.value = true
    }

    // 编辑学院
    const editCollege = async (row) => {
      try {
        loading.value = true
        const res = await schoolApi.getCollege(row.id)
        if (res.code === 200) {
          dialogTitle.value = '编辑学院'
          isEdit.value = true
          Object.assign(form, res.data.college)
          dialogVisible.value = true
        } else {
          ElMessage.error('获取学院详情失败')
        }
      } catch (error) {
        ElMessage.error('获取学院详情失败')
        console.error(error)
      } finally {
        loading.value = false
      }
    }

    // 查看学院详情
    const viewCollege = (row) => {
      currentCollege.value = row
      detailVisible.value = true
    }

    // 管理教师
    const manageTeachers = (row) => {
      ElMessage.info(`管理学院教师：${row.name}`)
      // TODO: 跳转到教师管理页面，并筛选该学院
    }

    // 删除学院
    const deleteCollege = async (row) => {
      if (row.teacherCount > 0) {
        ElMessage.warning('该学院下还有教师，无法删除')
        return
      }

      try {
        await ElMessageBox.confirm(
          `确定要删除学院"${row.name}"吗？此操作不可恢复。`,
          '确认删除',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )

        await schoolApi.deleteCollege(row.id)
        ElMessage.success('删除成功')
        getColleges()
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
          await schoolApi.updateCollege(form.id, form)
          ElMessage.success('更新成功')
        } else {
          await schoolApi.createCollege(form)
          ElMessage.success('创建成功')
        }

        dialogVisible.value = false
        getColleges()
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
        name: '',
        code: '',
        description: '',
        status: 'active'
      })
    }

    onMounted(() => {
      getColleges()
    })

    return {
      loading,
      submitting,
      dialogVisible,
      detailVisible,
      dialogTitle,
      searchForm,
      pagination,
      form,
      rules,
      colleges,
      currentCollege,
      formRef,
      getStatusText,
      handleSearch,
      resetSearch,
      handleSizeChange,
      handleCurrentChange,
      showCreateDialog,
      editCollege,
      viewCollege,
      manageTeachers,
      deleteCollege,
      submitForm,
      resetForm
    }
  }
}
</script>

<style scoped>
.colleges-page {
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

.college-detail {
  padding: 20px 0;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style> 