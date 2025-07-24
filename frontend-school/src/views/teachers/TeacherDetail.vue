<template>
  <div class="teacher-detail">
    <!-- 页面标题 -->
    <div class="page-header">
      <el-button @click="$router.go(-1)" type="text">
        <el-icon><ArrowLeft /></el-icon>
        返回
      </el-button>
      <h2>教师详情</h2>
    </div>

    <!-- 加载状态 -->
    <div v-if="loading" class="loading-container">
      <el-skeleton :rows="10" animated />
    </div>

    <!-- 教师详情 -->
    <div v-else-if="teacher" class="detail-content">
      <el-card class="info-card">
        <template #header>
          <div class="card-header">
            <span>基本信息</span>
            <el-button type="primary" @click="handleEdit">编辑</el-button>
          </div>
        </template>
        
        <el-descriptions :column="2" border>
          <el-descriptions-item label="姓名">
            {{ teacher.name }}
          </el-descriptions-item>
          <el-descriptions-item label="工号">
            {{ teacher.teacher_no }}
          </el-descriptions-item>
          <el-descriptions-item label="邮箱">
            {{ teacher.email }}
          </el-descriptions-item>
          <el-descriptions-item label="电话">
            {{ teacher.phone }}
          </el-descriptions-item>
          <el-descriptions-item label="所属学院">
            {{ teacher.collegeName || '未分配学院' }}
          </el-descriptions-item>
          <el-descriptions-item label="职称">
            {{ teacher.titleName || teacher.title }}
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="teacher.status === 'active' ? 'success' : 'danger'">
              {{ teacher.status === 'active' ? '启用' : '禁用' }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="入职时间">
            {{ teacher.hire_date || teacher.joinDate || '未设置' }}
          </el-descriptions-item>
          <el-descriptions-item label="创建时间" :span="2">
            {{ teacher.createTime || teacher.createdAt }}
          </el-descriptions-item>
          <el-descriptions-item label="简介" :span="2">
            {{ teacher.bio || teacher.remark || '暂无简介' }}
          </el-descriptions-item>
        </el-descriptions>
      </el-card>

      <!-- 课程信息 -->
      <el-card class="course-card" style="margin-top: 20px;">
        <template #header>
          <span>课程信息</span>
        </template>
        <el-empty description="暂无课程信息" />
      </el-card>
    </div>

    <!-- 错误状态 -->
    <div v-else class="error-container">
      <el-result
        icon="error"
        title="教师不存在"
        sub-title="该教师可能已被删除或不存在"
      >
        <template #extra>
          <el-button type="primary" @click="$router.go(-1)">返回列表</el-button>
        </template>
      </el-result>
    </div>

    <!-- 编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="编辑教师"
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
import { ElMessage } from 'element-plus'
import { getTeacherDetail, updateTeacher, getTitleOptions } from '@/api/teacher'

export default {
  name: 'TeacherDetail',
  setup() {
    const route = useRoute()
    const router = useRouter()
    
    const loading = ref(false)
    const teacher = ref(null)
    const dialogVisible = ref(false)
    const submitLoading = ref(false)
    const formRef = ref()
    
    // 学院选项
    const collegeOptions = ref([
      { id: 1, name: '计算机学院' },
      { id: 2, name: '数学学院' },
      { id: 3, name: '物理学院' },
      { id: 4, name: '化学学院' },
      { id: 5, name: '生物学院' }
    ])
    
    // 职称选项
    const titleOptions = ref([])
    
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
    
    const fetchTeacherDetail = async () => {
      loading.value = true
      try {
        const res = await getTeacherDetail(route.params.id)
        if (res.data) {
          teacher.value = res.data
        } else {
          teacher.value = res
        }
      } catch (error) {
        ElMessage.error('获取教师详情失败')
        console.error('获取教师详情失败:', error)
      } finally {
        loading.value = false
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
    
    const handleEdit = () => {
      // 填充表单数据
      Object.assign(form, {
        id: teacher.value.id,
        name: teacher.value.name,
        email: teacher.value.email,
        phone: teacher.value.phone,
        collegeId: teacher.value.collegeId,
        title: Number(teacher.value.title), // 确保是数字类型
        joinDate: teacher.value.hire_date || teacher.value.joinDate,
        status: teacher.value.status,
        remark: teacher.value.bio || teacher.value.remark
      })
      dialogVisible.value = true
    }
    
    const handleSubmit = async () => {
      await formRef.value.validate()
      submitLoading.value = true
      try {
        await updateTeacher(form.id, form)
        ElMessage.success('编辑成功')
        dialogVisible.value = false
        // 重新获取教师详情
        await fetchTeacherDetail()
      } catch (e) {
        ElMessage.error('编辑失败')
      } finally {
        submitLoading.value = false
      }
    }
    
    onMounted(() => {
      fetchTitleOptions()
      fetchTeacherDetail()
    })
    
    return {
      loading,
      teacher,
      dialogVisible,
      submitLoading,
      formRef,
      form,
      rules,
      collegeOptions,
      titleOptions,
      handleEdit,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.teacher-detail {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}

.loading-container {
  background: #fff;
  padding: 24px;
  border-radius: 8px;
}

.detail-content {
  max-width: 1200px;
}

.info-card,
.course-card {
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.error-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style> 