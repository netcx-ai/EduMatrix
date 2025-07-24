<template>
  <div class="login-container">
    <!-- 背景装饰 -->
    <div class="background-decoration">
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
      <div class="floating-shape shape-3"></div>
      <div class="floating-shape shape-4"></div>
    </div>
    
    <!-- 主要内容区域 -->
    <div class="login-content">
      <!-- 左侧品牌区域 -->
      <div class="brand-section">
        <div class="brand-content">
          <div class="logo-container">
            <div class="logo-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <h1 class="brand-title">EduMatrix</h1>
            <p class="brand-subtitle">智能教育管理平台</p>
          </div>
          <div class="brand-description">
            <p>连接教育，创造未来</p>
            <p>让学习更高效，让管理更智能</p>
          </div>
        </div>
      </div>
      
      <!-- 右侧登录表单 -->
      <div class="login-form-section">
        <div class="form-container">
          <div class="form-header">
            <h2 class="welcome-text">欢迎回来</h2>
            <p class="login-subtitle">请登录您的账户</p>
          </div>
          
          <!-- 用户类型选择 -->
          <div class="user-type-selector" v-if="showUserTypeSelector">
            <h3>选择您的用户类型</h3>
            <div class="user-type-options">
              <div 
                class="user-type-option" 
                :class="{ active: selectedUserType === 'teacher' }"
                @click="selectUserType('teacher')"
              >
                <div class="user-type-icon">👩‍🏫</div>
                <div class="user-type-info">
                  <h4>教师登录</h4>
                  <p>访问课程管理、AI工具等功能</p>
                </div>
              </div>
              <div 
                class="user-type-option" 
                :class="{ active: selectedUserType === 'school' }"
                @click="selectUserType('school')"
              >
                <div class="user-type-icon">🏫</div>
                <div class="user-type-info">
                  <h4>学校管理员登录</h4>
                  <p>管理学校整体事务</p>
                </div>
              </div>
            </div>
            <el-button 
              type="primary" 
              @click="confirmUserType"
              :disabled="!selectedUserType"
              class="confirm-type-button"
              size="large"
            >
              确认选择
            </el-button>
          </div>
          
          <!-- 登录表单 -->
          <el-form
            v-else
            ref="loginFormRef"
            :model="loginForm"
            :rules="rules"
            class="login-form"
            @submit.prevent="handleLogin"
          >
            <div class="user-type-display">
              <div class="current-user-type">
                <span class="user-type-icon">{{ getUserTypeIcon() }}</span>
                <span class="user-type-text">{{ getUserTypeText() }}</span>
                <el-button link @click="backToUserTypeSelection" class="change-type-btn">
                  切换用户类型
                </el-button>
              </div>
            </div>
            
            <div class="form-group">
              <el-input
                v-model="loginForm.phone"
                placeholder="请输入手机号"
                class="modern-input"
                size="large"
              >
                <template #prefix>
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 2H7C5.89543 2 5 2.89543 5 4V20C5 21.1046 5.89543 22 7 22H17C18.1046 22 19 21.1046 19 20V4C19 2.89543 18.1046 2 17 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 18H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </template>
              </el-input>
            </div>
            
            <div class="form-group">
              <el-input
                v-model="loginForm.password"
                type="password"
                placeholder="请输入密码"
                class="modern-input"
                show-password
                size="large"
              >
                <template #prefix>
                  <svg class="input-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 11H5C3.89543 11 3 11.8954 3 13V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V13C21 11.8954 20.1046 11 19 11Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </template>
              </el-input>
            </div>
            
            <div class="form-options">
              <el-checkbox v-model="rememberMe" class="remember-checkbox">
                记住我
              </el-checkbox>
              <router-link to="/reset-password" class="forgot-link">
                忘记密码？
              </router-link>
            </div>
            
            <el-button 
              type="primary" 
              native-type="submit" 
              :loading="loading" 
              class="login-button"
              size="large"
            >
              <span v-if="!loading">登录</span>
              <span v-else>登录中...</span>
            </el-button>
            
            <div class="divider">
              <span class="divider-text">或</span>
            </div>
            
            <div class="social-login">
              <button type="button" class="social-button wechat">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M8.5,13.5A1.5,1.5 0 0,1 7,12A1.5,1.5 0 0,1 8.5,10.5A1.5,1.5 0 0,1 10,12A1.5,1.5 0 0,1 8.5,13.5M15.5,13.5A1.5,1.5 0 0,1 14,12A1.5,1.5 0 0,1 15.5,10.5A1.5,1.5 0 0,1 17,12A1.5,1.5 0 0,1 15.5,13.5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>
                </svg>
                微信登录
              </button>
            </div>
            
            <div class="form-footer">
              <span class="footer-text">还没有账号？</span>
              <router-link to="/register" class="register-link">
                立即注册
              </router-link>
            </div>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { userApi } from '@/api/user'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const rememberMe = ref(false)
const loginFormRef = ref(null)
const showUserTypeSelector = ref(true)
const selectedUserType = ref('')

const loginForm = reactive({
  phone: '',
  password: ''
})

const rules = {
  phone: [
    { required: true, message: '请输入手机号', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' }
  ]
}

// =================  新增：平台管理员直接跳后台  =================
// 如果选择平台管理员，直接跳转后端 /admin/login
const redirectToPlatformAdmin = () => {
  window.open('/admin/login', '_blank')
}

// 检查URL参数中的用户类型
onMounted(() => {
  const type = route.query.type
  if (type === 'platform') {
    // 直接跳转到后端登录页
    redirectToPlatformAdmin()
    return
  }
  if (type && ['teacher', 'school'].includes(type)) {
    selectedUserType.value = type
    showUserTypeSelector.value = false
  }
})

const selectUserType = (type) => {
  if (type === 'platform') {
    redirectToPlatformAdmin()
    return
  }
  selectedUserType.value = type
}

const confirmUserType = () => {
  if (selectedUserType.value) {
    showUserTypeSelector.value = false
  }
}

const backToUserTypeSelection = () => {
  showUserTypeSelector.value = true
  selectedUserType.value = ''
}

const getUserTypeIcon = () => {
  const icons = {
    teacher: '👩‍🏫',
    school: '🏫',
    platform: '🛠️'
  }
  return icons[selectedUserType.value] || '👤'
}

const getUserTypeText = () => {
  const texts = {
    teacher: '教师登录',
    school: '学校管理员登录',
    platform: '平台管理员登录'
  }
  return texts[selectedUserType.value] || '用户登录'
}

const getDashboardPath = () => {
  const paths = {
    teacher: '/teacher',
    school: '/school',
    platform: '/platform'
  }
  return paths[selectedUserType.value] || '/'
}

const handleLogin = async () => {
  if (!loginFormRef.value) return
  
  try {
    loading.value = true
    
    // 验证表单
    await loginFormRef.value.validate()
    
    // 验证手机号格式
    if (!/^1[3-9]\d{9}$/.test(loginForm.phone)) {
      ElMessage.error('请输入正确的手机号')
      return
    }
    
    // 验证密码长度
    if (loginForm.password.length < 6) {
      ElMessage.error('密码长度不能小于6位')
      return
    }
    
    // 根据用户类型构造参数
    const loginData = {
      password: loginForm.password,
      user_type: selectedUserType.value,
      phone: loginForm.phone  // 统一使用 phone 字段
    }
    
    const response = await userApi.login(loginData)
    
    if (response.code === 200) {
      // 保存token和用户信息
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('userInfo', JSON.stringify(response.data.user))
      localStorage.setItem('userType', response.data.user.user_type || selectedUserType.value)
      
      if (rememberMe.value) {
        localStorage.setItem('rememberMe', 'true')
        localStorage.setItem('rememberedPhone', loginForm.phone)
      }
      
      ElMessage.success('登录成功')
      
      // 根据用户类型跳转到相应的控制台
      const dashboardPath = getDashboardPath()
      router.push(dashboardPath)
    } else {
      ElMessage.error(response.message || '登录失败')
    }
  } catch (error) {
    console.error('登录失败：', error)
    if (error.response && error.response.data) {
      ElMessage.error(error.response.data.message || '登录失败')
    } else if (error.message) {
      ElMessage.error(error.message)
    } else {
      ElMessage.error('登录失败，请稍后重试')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  background: linear-gradient(135deg, var(--color-primary) 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

/* 背景装饰 */
.background-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.floating-shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 120px;
  height: 120px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 60px;
  height: 60px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

.shape-4 {
  width: 100px;
  height: 100px;
  top: 30%;
  right: 30%;
  animation-delay: 1s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

/* 主要内容区域 */
.login-content {
  display: flex;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  min-height: 100vh;
}

/* 左侧品牌区域 */
.brand-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  position: relative;
}

.brand-content {
  text-align: center;
  color: white;
  z-index: 1;
}

.logo-container {
  margin-bottom: 40px;
}

.logo-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.logo-icon svg {
  width: 40px;
  height: 40px;
  color: white;
}

.brand-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 10px;
  background: linear-gradient(45deg, #10b981, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.brand-subtitle {
  font-size: 1.1rem;
  margin: 0 0 30px;
  color: #e2e8f0;
  font-weight: 400;
}

.brand-description {
  font-size: 1rem;
  line-height: 1.6;
  color: #cbd5e1;
}

.brand-description p {
  margin: 8px 0;
  font-weight: 500;
}

/* 右侧登录表单 */
.login-form-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.form-container {
  width: 100%;
  max-width: 480px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  animation: slideIn 0.6s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.form-header {
  text-align: center;
  margin-bottom: 30px;
}

.welcome-text {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 8px;
}

.login-subtitle {
  color: #666;
  margin: 0;
  font-size: 0.95rem;
}

/* 用户类型选择器 */
.user-type-selector {
  text-align: center;
}

.user-type-selector h3 {
  margin-bottom: 20px;
  color: #333;
  font-size: 1.2rem;
}

.user-type-options {
  margin-bottom: 30px;
}

.user-type-option {
  display: flex;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.user-type-option:hover {
  border-color: #409EFF;
  background: #f0f9ff;
}

.user-type-option.active {
  border-color: #409EFF;
  background: #eff6ff;
}

.user-type-icon {
  font-size: 2rem;
  margin-right: 15px;
}

.user-type-info h4 {
  margin: 0 0 5px;
  color: #333;
  font-size: 1rem;
}

.user-type-info p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.confirm-type-button {
  width: 100%;
  margin-top: 20px;
}

/* 用户类型显示 */
.user-type-display {
  margin-bottom: 20px;
  padding: 15px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.current-user-type {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.user-type-icon {
  font-size: 1.5rem;
  margin-right: 10px;
}

.user-type-text {
  font-weight: 500;
  color: #333;
}

.change-type-btn {
  color: #409EFF;
  font-size: 0.9rem;
}

.login-form {
  width: 100%;
}

.form-group {
  margin-bottom: 20px;
}

/* 现代输入框样式 */
.modern-input :deep(.el-input__wrapper) {
  background: rgba(255, 255, 255, 0.9);
  border: 2px solid rgba(0, 0, 0, 0.08);
  border-radius: 16px;
  padding: 16px 20px;
  transition: all 0.3s ease;
  height: 52px;
  min-height: 52px;
}

.modern-input :deep(.el-input__wrapper:hover) {
  border-color: #409EFF;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

.modern-input :deep(.el-input__wrapper.is-focus) {
  border-color: #409EFF;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
}

.input-icon {
  width: 20px;
  height: 20px;
  color: #9ca3af;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.remember-checkbox {
  color: #666;
}

.forgot-link {
  color: #409EFF;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

.forgot-link:hover {
  color: #337ecc;
}

.login-button {
  width: 100%;
  height: 52px;
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 600;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
  border: none;
  transition: all 0.3s ease;
}

.login-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.divider {
  position: relative;
  text-align: center;
  margin: 25px 0;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #e5e7eb;
}

.divider-text {
  background: rgba(255, 255, 255, 0.95);
  padding: 0 15px;
  color: #666;
  font-size: 0.9rem;
}

.social-login {
  margin-bottom: 25px;
}

.social-button {
  width: 100%;
  height: 48px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  color: #333;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.social-button:hover {
  border-color: #409EFF;
  background: #f0f9ff;
}

.social-button svg {
  width: 20px;
  height: 20px;
}

.social-button.wechat {
  color: #07c160;
}

.social-button.wechat:hover {
  border-color: #07c160;
  background: #f0fdf4;
}

.form-footer {
  text-align: center;
  color: #666;
  font-size: 0.9rem;
}

.footer-text {
  margin-right: 5px;
}

.register-link {
  color: #409EFF;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.register-link:hover {
  color: #337ecc;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .login-content {
    flex-direction: column;
  }
  
  .brand-section {
    padding: 20px;
    min-height: 200px;
  }
  
  .login-form-section {
    padding: 20px;
  }
  
  .form-container {
    padding: 30px 20px;
  }
  
  .brand-title {
    font-size: 2rem;
  }
  
  .welcome-text {
    font-size: 1.5rem;
  }
}
</style> 