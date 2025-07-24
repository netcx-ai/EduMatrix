<template>
  <div class="login-container">
    <!-- 背景装饰 -->
    <div class="background-decoration">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
      <div class="circle circle-3"></div>
    </div>
    
    <div class="login-box">
      <div class="login-header">
        <div class="logo">
          <el-icon class="logo-icon"><School /></el-icon>
        </div>
        <h2>学校管理系统</h2>
        <p>请使用您的账号登录</p>
      </div>
      
      <el-form
        ref="loginFormRef"
        :model="loginForm"
        :rules="loginRules"
        class="login-form"
        @submit.prevent="handleLogin"
      >
        <el-form-item prop="phone">
          <el-input
            v-model="loginForm.phone"
            placeholder="请输入手机号"
            size="large"
            :prefix-icon="User"
            clearable
          />
        </el-form-item>
        
        <el-form-item prop="password">
          <el-input
            v-model="loginForm.password"
            type="password"
            placeholder="请输入密码"
            size="large"
            :prefix-icon="Lock"
            show-password
            clearable
            @keyup.enter="handleLogin"
          />
        </el-form-item>
        
        <el-form-item>
          <el-button
            type="primary"
            size="large"
            class="login-button"
            :loading="loading"
            @click="handleLogin"
          >
            <el-icon v-if="!loading"><Right /></el-icon>
            {{ loading ? '登录中...' : '登录' }}
          </el-button>
        </el-form-item>
      </el-form>
      
      <div class="login-footer">
        <p>© 2024 学校管理系统. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessage } from 'element-plus'
import { User, Lock, Right, School } from '@element-plus/icons-vue'
import { userApi } from '@/api/user'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    const loginFormRef = ref()
    const loading = ref(false)

    // 1. 表单数据
    const loginForm = reactive({
      phone: '',
      password: ''
    })

    // 2. 表单校验规则
    const loginRules = {
      phone: [
        { required: true, message: '请输入手机号', trigger: 'blur' },
        { pattern: /^1[3-9]\d{9}$/, message: '手机号格式不正确', trigger: 'blur' }
      ],
      password: [
        { required: true, message: '请输入密码', trigger: 'blur' },
        { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
      ]
    }

    // 3. 表单输入项
    // 替换 username 为 phone
    // 4. 登录请求参数
    const handleLogin = async () => {
      try {
        // 表单验证
        await loginFormRef.value.validate()
        
        loading.value = true
        
        // 调用统一登录接口
        const loginData = {
          phone: loginForm.phone,
          password: loginForm.password,
          user_type: 'school'
        }
        const response = await userApi.login(loginData)
        console.log('登录返回', response)
        // 兼容不同后端返回结构
        let token, user
        if (response.data) {
          token = response.data.token
          user = response.data.user
        } else {
          token = response.token
          user = response.user
        }
        localStorage.setItem('token', token || '')
        localStorage.setItem('userInfo', JSON.stringify(user || {}))
        // 同步 userStore 状态
        userStore.token = token || ''
        userStore.userInfo = user || {}
        userStore.isLoggedIn = true

        ElMessage.success('登录成功')
        router.push('/dashboard')
      } catch (error) {
        console.error('登录失败:', error)
        ElMessage.error(error.response?.data?.message || '登录失败，请检查用户名和密码')
      } finally {
        loading.value = false
      }
    }

    return {
      loginFormRef,
      loginForm,
      loginRules,
      loading,
      handleLogin,
      User,
      Lock,
      Right,
      School
    }
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  width: 100vw;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
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
  z-index: 0;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  animation: float 6s ease-in-out infinite;
}

.circle-1 {
  width: 100px;
  height: 100px;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.circle-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 10%;
  animation-delay: 2s;
}

.circle-3 {
  width: 80px;
  height: 80px;
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-20px);
  }
}

.login-box {
  width: 100%;
  max-width: 420px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  padding: 40px 30px;
  position: relative;
  z-index: 1;
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.login-header {
  width: 100%;
  text-align: center;
  margin-bottom: 40px;
  margin-top: 0;
}

.logo {
  margin-bottom: 20px;
}

.logo-icon {
  font-size: 48px;
  color: #667eea;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-header h2 {
  color: #333;
  margin: 0 0 12px 0;
  font-size: 28px;
  font-weight: 600;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-header p {
  color: #666;
  margin: 0;
  font-size: 16px;
  font-weight: 400;
}

.login-form {
  width: 100%;
  max-width: 400px;
  margin: 0 auto 30px auto;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.login-form :deep(.el-form-item) {
  margin-bottom: 24px;
}

.login-form :deep(.el-input__wrapper) {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e1e5e9;
  transition: all 0.3s ease;
}

.login-form :deep(.el-input__wrapper:hover) {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.login-form :deep(.el-input__wrapper.is-focus) {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.login-form :deep(.el-input__inner) {
  height: 48px;
  font-size: 16px;
}

.login-button {
  width: 100%;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.login-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.login-button:active {
  transform: translateY(0);
}

.login-footer {
  width: 100%;
  text-align: center;
  color: #999;
  font-size: 14px;
  margin-top: 20px;
  position: absolute;
  bottom: 24px;
  left: 0;
}

.login-footer p {
  margin: 0;
}

/* 移动端适配 */
@media (max-width: 768px) {
  .login-container {
    padding: 16px;
  }
  
  .login-box {
    padding: 30px 24px;
    border-radius: 12px;
  }
  
  .login-header h2 {
    font-size: 24px;
  }
  
  .login-header p {
    font-size: 14px;
  }
  
  .logo-icon {
    font-size: 40px;
  }
  
  .login-form :deep(.el-input__inner) {
    height: 44px;
    font-size: 16px;
  }
  
  .login-button {
    height: 44px;
    font-size: 16px;
  }
  
  .circle-1, .circle-2, .circle-3 {
    display: none;
  }
}

@media (max-width: 480px) {
  .login-container {
    padding: 12px;
  }
  
  .login-box {
    padding: 24px 20px;
  }
  
  .login-header h2 {
    font-size: 22px;
  }
  
  .login-form {
    max-width: 100%;
    padding: 0 12px;
  }
  .login-footer {
    font-size: 12px;
    bottom: 12px;
  }
  
  .login-form :deep(.el-form-item) {
    margin-bottom: 20px;
  }
}

/* 平板适配 */
@media (min-width: 769px) and (max-width: 1024px) {
  .login-box {
    max-width: 480px;
    padding: 50px 40px;
  }
}

/* 大屏幕适配 */
@media (min-width: 1025px) {
  .login-box {
    max-width: 460px;
    padding: 50px 40px;
  }
  
  .login-header h2 {
    font-size: 32px;
  }
  
  .login-header p {
    font-size: 18px;
  }
}

/* 深色模式支持 */
@media (prefers-color-scheme: dark) {
  .login-box {
    background: rgba(30, 30, 30, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .login-header h2 {
    color: #fff;
  }
  
  .login-header p {
    color: #ccc;
  }
  
  .login-form :deep(.el-input__wrapper) {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
  }
  
  .login-form :deep(.el-input__inner) {
    color: #fff;
  }
  
  .login-form :deep(.el-input__inner::placeholder) {
    color: #999;
  }
}
</style> 