<template>
  <div class="course-list">
    <div class="page-header">
      <h2>课程管理</h2>
      <el-button type="primary" @click="showAddDialog">
        <el-icon><Plus /></el-icon>
        新增课程
      </el-button>
    </div>
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="课程名称">
          <el-input v-model="searchForm.name" placeholder="请输入课程名称" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 150px">
            <el-option label="启用" value="active" />
            <el-option label="禁用" value="inactive" />
          </el-select>
        </el-form-item>
        <el-form-item label="学院">
          <el-select v-model="searchForm.college_id" placeholder="请选择学院" clearable style="width: 180px">
            <el-option v-for="item in collegeOptions" :key="item.id" :label="item.name" :value="item.id" />
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
      <el-table v-loading="loading" :data="courseList" stripe style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="课程名称" min-width="150" />
        <el-table-column prop="code" label="课程代码" width="120" />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="college_name" label="所属学院" min-width="120" />
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
            <el-button type="primary" size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button type="info" size="small" @click="handleDetail(row)">详情</el-button>
            <el-button :type="row.status === 'active' ? 'warning' : 'success'" size="small" @click="handleToggleStatus(row)">
              {{ row.status === 'active' ? '禁用' : '启用' }}
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">删除</el-button>
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
    <el-dialog v-model="dialogVisible" :title="dialogType === 'add' ? '新增课程' : '编辑课程'" width="600px">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="课程名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入课程名称" />
        </el-form-item>
        <el-form-item label="课程代码" prop="code">
          <el-input v-model="form.code" placeholder="请输入课程代码" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="请输入课程描述" />
        </el-form-item>
        <el-form-item label="所属学院" prop="college_id" required>
          <el-select v-model="form.college_id" placeholder="请选择学院">
            <el-option v-for="item in collegeOptions" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
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
          <el-button type="primary" @click="handleSubmit" :loading="submitLoading">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getCourseList, addCourse, updateCourse, deleteCourse, getCourseDetail } from '@/api/course'
import { getCollegeOptions } from '@/api/college'
import CourseDetail from './CourseDetail.vue'
import { useRouter } from 'vue-router'

export default {
  name: 'CourseList',
  components: { CourseDetail },
  setup() {
    const loading = ref(false)
    const courseList = ref([])
    const searchForm = reactive({ name: '', status: '', college_id: '' })
    const pagination = reactive({ currentPage: 1, pageSize: 10, total: 0 })
    const dialogVisible = ref(false)
    const dialogType = ref('add')
    const submitLoading = ref(false)
    const formRef = ref()
    const form = reactive({ id: null, name: '', code: '', description: '', teacherId: '', status: 'active', college_id: '' })
    const rules = {
      name: [ { required: true, message: '请输入课程名称', trigger: 'blur' } ],
      code: [ { required: true, message: '请输入课程代码', trigger: 'blur' } ],
      status: [ { required: true, message: '请选择状态', trigger: 'change' } ]
    }
    const collegeOptions = ref([])
    const router = useRouter()
    const fetchCourseList = async () => {
      loading.value = true
      try {
        const params = { name: searchForm.name, status: searchForm.status, college_id: searchForm.college_id, page: pagination.currentPage, pageSize: pagination.pageSize }
        const res = await getCourseList(params)
        if (res.data) {
          courseList.value = res.data.list || res.data
          pagination.total = res.data.total || 0
        } else {
          courseList.value = res.list || res
          pagination.total = res.total || 0
        }
      } catch (e) {
        ElMessage.error('获取课程列表失败')
      } finally {
        loading.value = false
      }
    }
    const fetchCollegeOptions = async () => {
      try {
        const res = await getCollegeOptions()
        collegeOptions.value = res.data?.list || res.data || []
      } catch (e) {
        collegeOptions.value = []
      }
    }
    onMounted(() => {
      fetchCourseList()
      fetchCollegeOptions()
    })
    const handleSearch = () => { pagination.currentPage = 1; fetchCourseList() }
    const handleReset = () => { searchForm.name = ''; searchForm.status = ''; searchForm.college_id = ''; pagination.currentPage = 1; fetchCourseList() }
    const handleSizeChange = (size) => { pagination.pageSize = size; fetchCourseList() }
    const handleCurrentChange = (page) => { pagination.currentPage = page; fetchCourseList() }
    const showAddDialog = () => {
      dialogType.value = 'add';
      Object.assign(form, { id: null, name: '', code: '', description: '', teacherId: '', status: 'active', college_id: '' });
      dialogVisible.value = true
    }
    const handleEdit = (row) => {
      dialogType.value = 'edit';
      getCourseDetail(row.id).then(res => {
        Object.assign(form, res.data || res)
        // 兼容后端返回collegeId/college_id
        form.college_id = res.data.college_id || res.data.collegeId || ''
        dialogVisible.value = true
      })
    }
    const handleDetail = (row) => {
      router.push(`/courses/detail/${row.id}`)
    }
    const handleSubmit = async () => {
      await formRef.value.validate();
      submitLoading.value = true;
      try {
        const submitData = { ...form };
        // status转换为数字
        if (submitData.status === 'active') submitData.status = 1;
        else if (submitData.status === 'inactive') submitData.status = 0;
        if (dialogType.value === 'add') {
          await addCourse(submitData);
          ElMessage.success('新增成功')
        } else {
          await updateCourse(form.id, submitData);
          ElMessage.success('编辑成功')
        }
        dialogVisible.value = false;
        fetchCourseList();
      } catch (e) {
        ElMessage.error('操作失败')
      } finally {
        submitLoading.value = false
      }
    }
    const handleDelete = (row) => { ElMessageBox.confirm('确定要删除该课程吗？', '提示', { type: 'warning' }).then(async () => { await deleteCourse(row.id); ElMessage.success('删除成功'); fetchCourseList() }).catch(() => {}) }
    const handleToggleStatus = async (row) => {
      const action = row.status === 'active' ? '禁用' : '启用';
      ElMessageBox.confirm(`确定要${action}该课程吗？`, '提示', { type: 'warning' })
        .then(async () => {
          await updateCourse(row.id, { status: row.status === 'active' ? 'inactive' : 'active' });
          ElMessage.success(`课程已${action}`);
          fetchCourseList();
        })
        .catch(() => {});
    }
    return { loading, courseList, searchForm, pagination, dialogVisible, dialogType, submitLoading, formRef, form, rules, handleSearch, handleReset, handleSizeChange, handleCurrentChange, showAddDialog, handleEdit, handleDetail, handleToggleStatus, handleDelete, handleSubmit, collegeOptions }
  }
}
</script>

<style scoped>
.course-list {
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
.search-card { margin-bottom: 20px; }
.table-card { margin-bottom: 20px; }
.pagination-wrapper { display: flex; justify-content: center; margin-top: 20px; }
.dialog-footer { display: flex; justify-content: flex-end; gap: 10px; }
</style> 