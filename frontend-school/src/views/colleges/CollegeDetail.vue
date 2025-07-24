<template>
  <div class="college-detail">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-left">
        <el-button @click="goBack">
          <el-icon><ArrowLeft /></el-icon>
          返回
        </el-button>
        <h2>{{ collegeInfo.name }} - 学院详情</h2>
      </div>
      <div class="header-right">
        <el-button type="primary" @click="handleEdit">
          <el-icon><Edit /></el-icon>
          编辑学院
        </el-button>
      </div>
    </div>

    <!-- 学院基本信息 -->
    <el-card class="info-card">
      <template #header>
        <div class="card-header">
          <span>基本信息</span>
        </div>
      </template>
      
      <el-descriptions :column="2" border>
        <el-descriptions-item label="学院名称">
          {{ collegeInfo.name }}
        </el-descriptions-item>
        <el-descriptions-item label="学院代码">
          {{ collegeInfo.code }}
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="collegeInfo.status === 'active' ? 'success' : 'danger'">
            {{ collegeInfo.status === 'active' ? '启用' : '禁用' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="创建时间">
          {{ collegeInfo.createdAt }}
        </el-descriptions-item>
        <el-descriptions-item label="描述" :span="2">
          {{ collegeInfo.description }}
        </el-descriptions-item>
      </el-descriptions>
    </el-card>

    <!-- 统计信息 -->
    <div class="stats-grid">
      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon teacher">
            <el-icon><User /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ collegeInfo.teacherCount }}</div>
            <div class="stat-label">教师总数</div>
          </div>
        </div>
      </el-card>

      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon course">
            <el-icon><Reading /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ collegeInfo.courseCount }}</div>
            <div class="stat-label">课程总数</div>
          </div>
        </div>
      </el-card>

      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon student">
            <el-icon><UserFilled /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ collegeInfo.studentCount || 0 }}</div>
            <div class="stat-label">学生总数</div>
          </div>
        </div>
      </el-card>

      <el-card class="stat-card">
        <div class="stat-content">
          <div class="stat-icon active">
            <el-icon><DataAnalysis /></el-icon>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ collegeInfo.activeRate || '85%' }}</div>
            <div class="stat-label">活跃度</div>
          </div>
        </div>
      </el-card>
    </div>

    <!-- 教师列表 -->
    <el-card class="teacher-card">
      <template #header>
        <div class="card-header">
          <span>教师列表</span>
          <el-button type="primary" size="small" @click="handleAddTeacher">
            <el-icon><Plus /></el-icon>
            添加教师
          </el-button>
        </div>
      </template>
      
      <el-table :data="teacherList" stripe style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="姓名" width="120" />
        <el-table-column prop="email" label="邮箱" min-width="200" />
        <el-table-column prop="phone" label="电话" width="150" />
        <el-table-column prop="title" label="职称" width="100" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'danger'">
              {{ row.status === 'active' ? '在职' : '离职' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="joinDate" label="入职时间" width="120" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleEditTeacher(row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="handleRemoveTeacher(row)">
              移除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 课程列表 -->
    <el-card class="course-card">
      <template #header>
        <div class="card-header">
          <span>课程列表</span>
          <el-button type="primary" size="small" @click="handleAddCourse">
            <el-icon><Plus /></el-icon>
            添加课程
          </el-button>
        </div>
      </template>
      
      <el-table :data="courseList" stripe style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="课程名称" min-width="150" />
        <el-table-column prop="code" label="课程代码" width="120" />
        <el-table-column prop="teacher" label="授课教师" width="120" />
        <el-table-column prop="credits" label="学分" width="80" align="center" />
        <el-table-column prop="hours" label="学时" width="80" align="center" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 'active' ? 'success' : 'info'">
              {{ row.status === 'active' ? '进行中' : '已结束' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="startDate" label="开始时间" width="120" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleEditCourse(row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="handleRemoveCourse(row)">
              移除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <!-- 编辑学院对话框 -->
    <el-dialog v-model="editDialogVisible" title="编辑学院" width="500px">
      <el-form ref="editFormRef" :model="editForm" :rules="editRules" label-width="100px">
        <el-form-item label="学院名称" prop="name">
          <el-input v-model="editForm.name" placeholder="请输入学院名称" />
        </el-form-item>
        <el-form-item label="学院代码" prop="code">
          <el-input v-model="editForm.code" placeholder="请输入学院代码" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input 
            v-model="editForm.description" 
            type="textarea" 
            :rows="3" 
            placeholder="请输入学院描述" 
          />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="editForm.status">
            <el-radio label="active">启用</el-radio>
            <el-radio label="inactive">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="editDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSaveEdit" :loading="editLoading">
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 添加教师对话框 -->
    <el-dialog v-model="addTeacherDialogVisible" title="添加教师" width="500px">
      <el-form ref="addTeacherFormRef" :model="addTeacherForm" :rules="addTeacherRules" label-width="100px">
        <el-form-item label="教师姓名" prop="name">
          <el-input v-model="addTeacherForm.name" placeholder="请输入教师姓名" />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="addTeacherForm.email" placeholder="请输入邮箱" />
        </el-form-item>
        <el-form-item label="电话" prop="phone">
          <el-input v-model="addTeacherForm.phone" placeholder="请输入电话" />
        </el-form-item>
        <el-form-item label="职称" prop="title">
          <el-select v-model="addTeacherForm.title" placeholder="请选择职称" style="width: 100%">
            <el-option label="教授" value="教授" />
            <el-option label="副教授" value="副教授" />
            <el-option label="讲师" value="讲师" />
            <el-option label="助教" value="助教" />
          </el-select>
        </el-form-item>
        <el-form-item label="入职时间" prop="joinDate">
          <el-date-picker
            v-model="addTeacherForm.joinDate"
            type="date"
            placeholder="请选择入职时间"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="addTeacherDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSaveTeacher" :loading="addTeacherLoading">
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
    
    <!-- 添加课程对话框 -->
    <el-dialog v-model="addCourseDialogVisible" title="添加课程" width="500px">
      <el-form ref="addCourseFormRef" :model="addCourseForm" :rules="addCourseRules" label-width="100px">
        <el-form-item label="课程名称" prop="name">
          <el-input v-model="addCourseForm.name" placeholder="请输入课程名称" />
        </el-form-item>
        <el-form-item label="课程代码" prop="code">
          <el-input v-model="addCourseForm.code" placeholder="请输入课程代码" />
        </el-form-item>
        <el-form-item label="主讲教师" prop="teacher">
          <el-input v-model="addCourseForm.teacher" placeholder="请输入主讲教师" />
        </el-form-item>
        <el-form-item label="学分" prop="credits">
          <el-input-number 
            v-model="addCourseForm.credits" 
            :min="1" 
            :max="10" 
            placeholder="请输入学分"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="课时" prop="hours">
          <el-input-number 
            v-model="addCourseForm.hours" 
            :min="1" 
            :max="200" 
            placeholder="请输入课时"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="开课时间" prop="startDate">
          <el-date-picker
            v-model="addCourseForm.startDate"
            type="date"
            placeholder="请选择开课时间"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="addCourseDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSaveCourse" :loading="addCourseLoading">
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
import { getCollegeDetail, updateCollege } from '@/api/college'

export default {
  name: 'CollegeDetail',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const collegeId = route.params.id
    
    // 学院信息
    const collegeInfo = reactive({
      id: collegeId,
      name: '',
      code: '',
      description: '',
      status: 'active',
      teacherCount: 0,
      courseCount: 0,
      studentCount: 0,
      activeRate: '0%',
      createdAt: ''
    })
    
    // 教师列表
    const teacherList = ref([])
    
    // 课程列表
    const courseList = ref([])
    
    // 获取学院详情
    const fetchCollegeDetail = async () => {
      try {
        console.log('获取学院详情:', collegeId)
        const res = await getCollegeDetail(collegeId)
        console.log('学院详情数据:', res)
        
        if (res && res.data) {
          // 更新学院信息
          Object.assign(collegeInfo, {
            id: res.data.id,
            name: res.data.name || '',
            code: res.data.code || '',
            description: res.data.description || '',
            status: res.data.status || 'active',
            teacherCount: res.data.teacherCount || 0,
            courseCount: res.data.courseCount || 0,
            studentCount: res.data.studentCount || 0,
            activeRate: res.data.activeRate || '0%',
            createdAt: res.data.createdAt || res.data.created_at || res.data.create_time || ''
          })
          
          // 更新教师列表（如果有）
          if (res.data.teachers && Array.isArray(res.data.teachers)) {
            teacherList.value = res.data.teachers
          }
          
          // 更新课程列表（如果有）
          if (res.data.courses && Array.isArray(res.data.courses)) {
            courseList.value = res.data.courses
          }
        }
      } catch (error) {
        ElMessage.error('获取学院详情失败')
        console.error('获取学院详情失败:', error)
      }
    }
    
    // 返回上一页
    const goBack = () => {
      router.back()
    }
    
    // 编辑学院对话框
    const editDialogVisible = ref(false)
    const editForm = reactive({
      id: '',
      name: '',
      code: '',
      description: '',
      status: 'active'
    })
    const editFormRef = ref()
    const editLoading = ref(false)
    
    const editRules = {
      name: [
        { required: true, message: '请输入学院名称', trigger: 'blur' },
        { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
      ],
      code: [
        { required: true, message: '请输入学院代码', trigger: 'blur' },
        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
      ]
    }
    
    // 编辑学院
    const handleEdit = () => {
      Object.assign(editForm, collegeInfo)
      editDialogVisible.value = true
    }
    
    // 保存编辑
    const handleSaveEdit = async () => {
      try {
        await editFormRef.value.validate()
        editLoading.value = true
        
        const data = {
          name: editForm.name,
          code: editForm.code,
          description: editForm.description,
          status: editForm.status
        }
        
        // 调用更新API
        await updateCollege(collegeId, data)
        
        // 更新本地数据
        Object.assign(collegeInfo, editForm)
        
        ElMessage.success('编辑成功')
        editDialogVisible.value = false
      } catch (error) {
        console.error('更新学院失败:', error)
        ElMessage.error('更新失败')
      } finally {
        editLoading.value = false
      }
    }
    
    // 添加教师对话框
    const addTeacherDialogVisible = ref(false)
    const addTeacherForm = reactive({
      name: '',
      email: '',
      phone: '',
      title: '',
      joinDate: ''
    })
    const addTeacherFormRef = ref()
    const addTeacherLoading = ref(false)
    
    const addTeacherRules = {
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
      title: [
        { required: true, message: '请选择职称', trigger: 'change' }
      ],
      joinDate: [
        { required: true, message: '请选择入职时间', trigger: 'change' }
      ]
    }
    
    // 添加教师
    const handleAddTeacher = () => {
      Object.assign(addTeacherForm, {
        name: '',
        email: '',
        phone: '',
        title: '',
        joinDate: ''
      })
      addTeacherDialogVisible.value = true
    }
    
    // 保存教师
    const handleSaveTeacher = async () => {
      try {
        await addTeacherFormRef.value.validate()
        addTeacherLoading.value = true
        
        // 模拟API调用
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        // 添加到本地列表
        const newTeacher = {
          id: Date.now(),
          ...addTeacherForm,
          status: 'active'
        }
        teacherList.value.push(newTeacher)
        collegeInfo.teacherCount++
        
        ElMessage.success('添加成功')
        addTeacherDialogVisible.value = false
      } catch (error) {
        if (error.message) {
          ElMessage.error(error.message)
        }
      } finally {
        addTeacherLoading.value = false
      }
    }
    
    // 编辑教师
    const handleEditTeacher = (teacher) => {
      ElMessage.info(`编辑教师功能：${teacher.name}`)
      // 这里可以扩展为完整的编辑对话框
    }
    
    // 移除教师
    const handleRemoveTeacher = async (teacher) => {
      try {
        await ElMessageBox.confirm(
          `确定要从该学院移除教师 ${teacher.name} 吗？`,
          '提示',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )
        
        const index = teacherList.value.findIndex(item => item.id === teacher.id)
        if (index > -1) {
          teacherList.value.splice(index, 1)
          collegeInfo.teacherCount--
          ElMessage.success('移除成功')
        }
      } catch {
        // 用户取消
      }
    }
    
    // 添加课程对话框
    const addCourseDialogVisible = ref(false)
    const addCourseForm = reactive({
      name: '',
      code: '',
      teacher: '',
      credits: '',
      hours: '',
      startDate: ''
    })
    const addCourseFormRef = ref()
    const addCourseLoading = ref(false)
    
    const addCourseRules = {
      name: [
        { required: true, message: '请输入课程名称', trigger: 'blur' },
        { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
      ],
      code: [
        { required: true, message: '请输入课程代码', trigger: 'blur' },
        { min: 2, max: 20, message: '长度在 2 到 20 个字符', trigger: 'blur' }
      ],
      teacher: [
        { required: true, message: '请输入主讲教师', trigger: 'blur' }
      ],
      credits: [
        { required: true, message: '请输入学分', trigger: 'blur' },
        { type: 'number', message: '学分必须是数字', trigger: 'blur' }
      ],
      hours: [
        { required: true, message: '请输入课时', trigger: 'blur' },
        { type: 'number', message: '课时必须是数字', trigger: 'blur' }
      ],
      startDate: [
        { required: true, message: '请选择开课时间', trigger: 'change' }
      ]
    }
    
    // 添加课程
    const handleAddCourse = () => {
      Object.assign(addCourseForm, {
        name: '',
        code: '',
        teacher: '',
        credits: '',
        hours: '',
        startDate: ''
      })
      addCourseDialogVisible.value = true
    }
    
    // 保存课程
    const handleSaveCourse = async () => {
      try {
        await addCourseFormRef.value.validate()
        addCourseLoading.value = true
        
        // 模拟API调用
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        // 添加到本地列表
        const newCourse = {
          id: Date.now(),
          ...addCourseForm,
          status: 'active'
        }
        courseList.value.push(newCourse)
        collegeInfo.courseCount++
        
        ElMessage.success('添加成功')
        addCourseDialogVisible.value = false
      } catch (error) {
        if (error.message) {
          ElMessage.error(error.message)
        }
      } finally {
        addCourseLoading.value = false
      }
    }
    
    // 编辑课程
    const handleEditCourse = (course) => {
      ElMessage.info(`编辑课程功能：${course.name}`)
      // 这里可以扩展为完整的编辑对话框
    }
    
    // 移除课程
    const handleRemoveCourse = async (course) => {
      try {
        await ElMessageBox.confirm(
          `确定要从该学院移除课程 ${course.name} 吗？`,
          '提示',
          {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }
        )
        
        const index = courseList.value.findIndex(item => item.id === course.id)
        if (index > -1) {
          courseList.value.splice(index, 1)
          collegeInfo.courseCount--
          ElMessage.success('移除成功')
        }
      } catch {
        // 用户取消
      }
    }
    
    // 页面加载时获取数据
    onMounted(() => {
      fetchCollegeDetail()
    })
    
    return {
      collegeInfo,
      teacherList,
      courseList,
      goBack,
      handleEdit,
      handleAddTeacher,
      handleEditTeacher,
      handleRemoveTeacher,
      handleAddCourse,
      handleEditCourse,
      handleRemoveCourse,
      // 编辑学院相关
      editDialogVisible,
      editForm,
      editFormRef,
      editLoading,
      editRules,
      handleSaveEdit,
      // 添加教师相关
      addTeacherDialogVisible,
      addTeacherForm,
      addTeacherFormRef,
      addTeacherLoading,
      addTeacherRules,
      handleSaveTeacher,
      // 添加课程相关
      addCourseDialogVisible,
      addCourseForm,
      addCourseFormRef,
      addCourseLoading,
      addCourseRules,
      handleSaveCourse
    }
  }
}
</script>

<style scoped>
.college-detail {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-left h2 {
  margin: 0;
  color: #333;
}

.info-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.stat-card {
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.stat-content {
  display: flex;
  align-items: center;
  padding: 10px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  font-size: 24px;
  color: #fff;
}

.stat-icon.teacher {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.course {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.student {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.active {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.stat-label {
  color: #666;
  font-size: 14px;
}

.teacher-card,
.course-card {
  margin-bottom: 20px;
}
</style> 