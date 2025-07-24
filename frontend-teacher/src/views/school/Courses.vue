<template>
  <div class="courses-page">
    <Layout>
      <template #content>
        <div class="page-header">
          <h2>📚 课程管理</h2>
        </div>

        <!-- 搜索和筛选 -->
        <el-card class="search-card">
          <el-form :model="searchForm" inline>
            <el-form-item label="课程名称">
              <el-input v-model="searchForm.name" placeholder="请输入课程名称" clearable />
            </el-form-item>
            <el-form-item label="课程代码">
              <el-input v-model="searchForm.code" placeholder="请输入课程代码" clearable />
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
                <el-option label="进行中" value="active" />
                <el-option label="已结束" value="ended" />
                <el-option label="待审核" value="pending" />
                <el-option label="已拒绝" value="rejected" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleSearch">搜索</el-button>
              <el-button @click="resetSearch">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>

        <!-- 课程列表 -->
        <el-card class="list-card">
          <el-table 
            :data="courses" 
            v-loading="loading"
            style="width: 100%"
          >
            <el-table-column prop="name" label="课程名称" min-width="150" />
            <el-table-column prop="code" label="课程代码" width="120" />
            <el-table-column prop="collegeName" label="所属学院" min-width="150" />
            <el-table-column prop="teacherName" label="负责人" width="120" />
            <el-table-column prop="credits" label="学分" width="80" align="center" />
            <el-table-column prop="hours" label="学时" width="80" align="center" />
            <el-table-column prop="status" label="状态" width="100" align="center">
              <template #default="scope">
                <el-tag :type="getStatusType(scope.row.status)">
                  {{ getStatusText(scope.row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="studentCount" label="学生数" width="100" align="center" />
            <el-table-column prop="createTime" label="创建时间" width="180" />
            <el-table-column label="操作" width="250" fixed="right">
              <template #default="scope">
                <el-button size="small" @click="viewCourse(scope.row)">查看</el-button>
                <el-button 
                  v-if="scope.row.status === 'pending'"
                  size="small" 
                  type="success" 
                  @click="approveCourse(scope.row)"
                >审核</el-button>
                <el-button 
                  v-if="scope.row.status === 'pending'"
                  size="small" 
                  type="danger" 
                  @click="rejectCourse(scope.row)"
                >拒绝</el-button>
                <el-button 
                  size="small" 
                  :type="scope.row.status === 'active' ? 'warning' : 'success'"
                  @click="toggleStatus(scope.row)"
                  v-if="scope.row.status !== 'pending'"
                >
                  {{ scope.row.status === 'active' ? '结束' : '开始' }}
                </el-button>
                <el-button 
                  size="small" 
                  type="danger" 
                  @click="deleteCourse(scope.row)"
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

        <!-- 课程详情对话框 -->
        <el-dialog
          v-model="detailVisible"
          title="课程详情"
          width="900px"
        >
          <div v-if="currentCourse" class="course-detail">
            <el-descriptions :column="2" border>
              <el-descriptions-item label="课程名称">{{ currentCourse.name }}</el-descriptions-item>
              <el-descriptions-item label="课程代码">{{ currentCourse.code }}</el-descriptions-item>
              <el-descriptions-item label="所属学院">{{ currentCourse.collegeName }}</el-descriptions-item>
              <el-descriptions-item label="负责人">{{ currentCourse.teacherName }}</el-descriptions-item>
              <el-descriptions-item label="学分">{{ currentCourse.credits }}</el-descriptions-item>
              <el-descriptions-item label="学时">{{ currentCourse.hours }}</el-descriptions-item>
              <el-descriptions-item label="状态">
                <el-tag :type="getStatusType(currentCourse.status)">
                  {{ getStatusText(currentCourse.status) }}
                </el-tag>
              </el-descriptions-item>
              <el-descriptions-item label="学生数">{{ currentCourse.studentCount }}</el-descriptions-item>
              <el-descriptions-item label="创建时间">{{ currentCourse.createTime }}</el-descriptions-item>
              <el-descriptions-item label="更新时间">{{ currentCourse.updateTime }}</el-descriptions-item>
              <el-descriptions-item label="课程描述" :span="2">{{ currentCourse.description }}</el-descriptions-item>
            </el-descriptions>
          </div>
        </el-dialog>

        <!-- 审核课程对话框 -->
        <el-dialog
          v-model="approvalVisible"
          title="审核课程"
          width="500px"
        >
          <div v-if="currentCourse" class="approval-content">
            <p>确定要审核通过课程 <strong>{{ currentCourse.name }}</strong> 吗？</p>
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

        <!-- 拒绝课程对话框 -->
        <el-dialog
          v-model="rejectVisible"
          title="拒绝课程"
          width="500px"
        >
          <div v-if="currentCourse" class="reject-content">
            <p>确定要拒绝课程 <strong>{{ currentCourse.name }}</strong> 吗？</p>
            <el-form :model="rejectForm" label-width="100px">
              <el-form-item label="拒绝原因" prop="reason">
                <el-input
                  v-model="rejectForm.reason"
                  type="textarea"
                  :rows="3"
                  placeholder="请输入拒绝原因"
                />
              </el-form-item>
            </el-form>
          </div>
          <template #footer>
            <span class="dialog-footer">
              <el-button @click="rejectVisible = false">取消</el-button>
              <el-button type="danger" @click="submitReject" :loading="submitting">
                确认拒绝
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
import Layout from '@/components/Layout.vue'
import { schoolApi } from '@/api/school'

export default {
  name: 'Courses',
  components: {
    Layout
  },
  setup() {
    const loading = ref(false)
    const submitting = ref(false)
    const detailVisible = ref(false)
    const approvalVisible = ref(false)
    const rejectVisible = ref(false)
    const currentCourse = ref(null)

    // 搜索表单
    const searchForm = reactive({
      name: '',
      code: '',
      collegeId: '',
      status: ''
    })

    // 分页
    const pagination = reactive({
      current: 1,
      size: 20,
      total: 0
    })

    // 审核表单
    const approvalForm = reactive({
      remark: ''
    })

    // 拒绝表单
    const rejectForm = reactive({
      reason: ''
    })

    // 数据列表
    const courses = ref([])
    const colleges = ref([])

    // 获取课程列表
    const getCourses = async () => {
      loading.value = true
      try {
        const params = {
          page: pagination.current,
          size: pagination.size,
          ...searchForm
        }
        const res = await schoolApi.getCourses(params)
        courses.value = res.data.list || []
        pagination.total = res.data.total || 0
      } catch (error) {
        ElMessage.error('获取课程列表失败')
        console.error(error)
      } finally {
        loading.value = false
      }
    }

    // 获取学院列表
    const getColleges = async () => {
      try {
        const res = await schoolApi.getColleges({ size: 1000 })
        colleges.value = res.data.list || []
      } catch (error) {
        console.error('获取学院列表失败:', error)
      }
    }

    // 搜索
    const handleSearch = () => {
      pagination.current = 1
      getCourses()
    }

    // 重置搜索
    const resetSearch = () => {
      Object.assign(searchForm, {
        name: '',
        code: '',
        collegeId: '',
        status: ''
      })
      handleSearch()
    }

    // 分页处理
    const handleSizeChange = (size) => {
      pagination.size = size
      pagination.current = 1
      getCourses()
    }

    const handleCurrentChange = (current) => {
      pagination.current = current
      getCourses()
    }

    // 状态处理
    const getStatusType = (status) => {
      const types = {
        active: 'success',
        ended: 'info',
        pending: 'warning',
        rejected: 'danger'
      }
      return types[status] || 'info'
    }

    const getStatusText = (status) => {
      const texts = {
        active: '进行中',
        ended: '已结束',
        pending: '待审核',
        rejected: '已拒绝'
      }
      return texts[status] || '未知'
    }

    // 查看课程详情
    const viewCourse = (row) => {
      currentCourse.value = row
      detailVisible.value = true
    }

    // 审核课程
    const approveCourse = (row) => {
      currentCourse.value = row
      approvalForm.remark = ''
      approvalVisible.value = true
    }

    // 拒绝课程
    const rejectCourse = (row) => {
      currentCourse.value = row
      rejectForm.reason = ''
      rejectVisible.value = true
    }

    // 提交审核
    const submitApproval = async () => {
      try {
        submitting.value = true
        await schoolApi.updateCourseStatus(currentCourse.value.id, 'active')
        ElMessage.success('审核成功')
        approvalVisible.value = false
        getCourses()
      } catch (error) {
        ElMessage.error('审核失败')
        console.error(error)
      } finally {
        submitting.value = false
      }
    }

    // 提交拒绝
    const submitReject = async () => {
      if (!rejectForm.reason.trim()) {
        ElMessage.warning('请输入拒绝原因')
        return
      }

      try {
        submitting.value = true
        await schoolApi.updateCourseStatus(currentCourse.value.id, 'rejected')
        ElMessage.success('拒绝成功')
        rejectVisible.value = false
        getCourses()
      } catch (error) {
        ElMessage.error('拒绝失败')
        console.error(error)
      } finally {
        submitting.value = false
      }
    }

    // 切换状态
    const toggleStatus = async (row) => {
      const newStatus = row.status === 'active' ? 'ended' : 'active'
      const action = newStatus === 'active' ? '开始' : '结束'
      
      try {
        await ElMessageBox.confirm(
          `确定要${action}课程"${row.name}"吗？`,
          '确认操作',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )

        await schoolApi.updateCourseStatus(row.id, newStatus)
        ElMessage.success(`${action}成功`)
        getCourses()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error(`${action}失败`)
          console.error(error)
        }
      }
    }

    // 删除课程
    const deleteCourse = async (row) => {
      try {
        await ElMessageBox.confirm(
          `确定要删除课程"${row.name}"吗？此操作不可恢复。`,
          '确认删除',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )

        await schoolApi.deleteCourse(row.id)
        ElMessage.success('删除成功')
        getCourses()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('删除失败')
          console.error(error)
        }
      }
    }

    onMounted(() => {
      getCourses()
      getColleges()
    })

    return {
      loading,
      submitting,
      detailVisible,
      approvalVisible,
      rejectVisible,
      searchForm,
      pagination,
      approvalForm,
      rejectForm,
      courses,
      colleges,
      currentCourse,
      getStatusType,
      getStatusText,
      handleSearch,
      resetSearch,
      handleSizeChange,
      handleCurrentChange,
      viewCourse,
      approveCourse,
      rejectCourse,
      submitApproval,
      submitReject,
      toggleStatus,
      deleteCourse
    }
  }
}
</script>

<style scoped>
.courses-page {
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

.course-detail {
  padding: 20px 0;
}

.approval-content,
.reject-content {
  padding: 20px 0;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style> 