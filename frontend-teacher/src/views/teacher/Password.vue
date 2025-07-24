<template>
  <Layout>
    <div class="password-container">
      <!-- 页面标题 -->
      <div class="page-header">
        <h1 class="page-title">修改密码</h1>
        <p class="page-subtitle">定期修改密码可以提高账户安全性</p>
      </div>

      <div class="password-content">
        <div class="password-form-wrapper">
          <el-form
            ref="passwordFormRef"
            :model="passwordForm"
            :rules="passwordRules"
            label-width="120px"
            class="password-form"
          >
            <el-form-item label="当前密码" prop="oldPassword">
              <el-input
                v-model="passwordForm.oldPassword"
                type="password"
                placeholder="请输入当前密码"
                show-password
                clearable
              />
            </el-form-item>

            <el-form-item label="新密码" prop="newPassword">
              <el-input
                v-model="passwordForm.newPassword"
                type="password"
                placeholder="请输入新密码"
                show-password
                clearable
              />
              <div class="password-tips">
                <p>密码要求：</p>
                <ul>
                  <li :class="{ valid: hasLength }">长度至少6位</li>
                  <li :class="{ valid: hasLetter }">包含字母</li>
                  <li :class="{ valid: hasNumber }">包含数字</li>
                  <li :class="{ valid: hasSpecial }">包含特殊字符（可选）</li>
                </ul>
              </div>
            </el-form-item>

            <el-form-item label="确认新密码" prop="confirmPassword">
              <el-input
                v-model="passwordForm.confirmPassword"
                type="password"
                placeholder="请再次输入新密码"
                show-password
                clearable
              />
            </el-form-item>

            <el-form-item>
              <el-button type="primary" @click="changePassword" :loading="changing">
                确认修改
              </el-button>
              <el-button @click="resetForm">重置</el-button>
              <el-button @click="goBack">返回</el-button>
            </el-form-item>
          </el-form>
        </div>

        <!-- 安全提示 -->
        <div class="security-tips">
          <h3>安全提示</h3>
          <ul>
            <li>请使用强密码，包含字母、数字和特殊字符</li>
            <li>不要使用与其他网站相同的密码</li>
            <li>定期更换密码，建议每3个月更换一次</li>
            <li>不要在公共场所输入密码</li>
            <li>如果怀疑密码泄露，请立即修改</li>
          </ul>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import Layout from '@/components/Layout.vue'
import { teacherApi } from '@/api/user'

const router = useRouter()

// 响应式数据
const passwordFormRef = ref(null)
const changing = ref(false)

const passwordForm = reactive({
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// 密码验证规则
const validateConfirmPassword = (rule, value, callback) => {
  if (value === '') {
    callback(new Error('请再次输入密码'))
  } else if (value !== passwordForm.newPassword) {
    callback(new Error('两次输入密码不一致'))
  } else {
    callback()
  }
}

const passwordRules = {
  oldPassword: [
    { required: true, message: '请输入当前密码', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
  ],
  newPassword: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' },
    { 
      validator: (rule, value, callback) => {
        if (value === passwordForm.oldPassword) {
          callback(new Error('新密码不能与当前密码相同'))
        } else {
          callback()
        }
      }, 
      trigger: 'blur' 
    }
  ],
  confirmPassword: [
    { required: true, validator: validateConfirmPassword, trigger: 'blur' }
  ]
}

// 密码强度检查
const hasLength = computed(() => passwordForm.newPassword.length >= 6)
const hasLetter = computed(() => /[a-zA-Z]/.test(passwordForm.newPassword))
const hasNumber = computed(() => /\d/.test(passwordForm.newPassword))
const hasSpecial = computed(() => /[!@#$%^&*(),.?":{}|<>]/.test(passwordForm.newPassword))

// 修改密码
const changePassword = async () => {
  if (!passwordFormRef.value) return
  
  try {
    await passwordFormRef.value.validate()
    changing.value = true
    
    const data = {
      old_password: passwordForm.oldPassword,
      new_password: passwordForm.newPassword,
      confirm_password: passwordForm.confirmPassword
    }
    
    const response = await teacherApi.changePassword(data)
    if (response.code === 200) {
      ElMessage.success('密码修改成功，请重新登录')
      
      // 清除本地存储
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
      localStorage.removeItem('userType')
      
      // 跳转到登录页
      router.push('/login')
    } else {
      ElMessage.error(response.message || '密码修改失败')
    }
  } catch (error) {
    console.error('修改密码失败：', error)
    if (error.response && error.response.data) {
      ElMessage.error(error.response.data.message || '修改密码失败')
    } else {
      ElMessage.error('修改密码失败')
    }
  } finally {
    changing.value = false
  }
}

// 重置表单
const resetForm = () => {
  if (passwordFormRef.value) {
    passwordFormRef.value.resetFields()
  }
}

// 返回
const goBack = () => {
  router.push('/teacher/profile')
}
</script>

<style scoped>
.password-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

/* 页面标题 */
.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 8px;
}

.page-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

/* 内容区域 */
.password-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.password-form-wrapper {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-width: 500px;
}

.password-form {
  width: 100%;
}

/* 密码提示 */
.password-tips {
  margin-top: 8px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 6px;
  font-size: 12px;
}

.password-tips p {
  margin: 0 0 8px;
  color: #606266;
  font-weight: 500;
}

.password-tips ul {
  margin: 0;
  padding-left: 16px;
  color: #909399;
}

.password-tips li {
  margin-bottom: 4px;
}

.password-tips li.valid {
  color: #67c23a;
}

.password-tips li.valid::before {
  content: '✓ ';
  color: #67c23a;
}

/* 安全提示 */
.security-tips {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  max-width: 500px;
}

.security-tips h3 {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 16px;
}

.security-tips ul {
  margin: 0;
  padding-left: 16px;
  color: #606266;
  font-size: 14px;
  line-height: 1.6;
}

.security-tips li {
  margin-bottom: 8px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .password-container {
    max-width: 100%;
    padding: 0 16px;
  }
  
  .password-form-wrapper {
    padding: 16px;
  }
  
  .security-tips {
    padding: 16px;
  }
}
</style> 