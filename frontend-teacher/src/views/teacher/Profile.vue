<template>
  <Layout>
    <div class="profile-container">
      <!-- 页面标题 -->
      <div class="page-header">
        <h1 class="page-title">个人设置</h1>
        <p class="page-subtitle">管理您的个人信息和账户设置</p>
      </div>

      <div class="profile-content">
        <!-- 基本信息 -->
        <div class="profile-section">
          <h3 class="section-title">基本信息</h3>
          <el-form
            ref="profileFormRef"
            :model="profileForm"
            :rules="profileRules"
            label-width="100px"
            class="profile-form"
          >
            <div class="avatar-section">
              <div class="avatar-wrapper">
                <el-avatar :size="100" :src="profileForm.avatar">
                  {{ profileForm.name ? profileForm.name.charAt(0) : 'U' }}
                </el-avatar>
                <div class="avatar-overlay" @click="uploadAvatar">
                  <el-icon><Camera /></el-icon>
                </div>
              </div>
              <input
                ref="avatarInputRef"
                type="file"
                accept="image/*"
                style="display: none"
                @change="handleAvatarChange"
              />
            </div>

            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="姓名" prop="name">
                  <el-input v-model="profileForm.name" placeholder="请输入姓名" />
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="手机号" prop="phone">
                  <el-input v-model="profileForm.phone" placeholder="请输入手机号" disabled />
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="20">
              <el-col :span="12">
                <el-form-item label="性别" prop="gender">
                  <el-select v-model="profileForm.gender" placeholder="请选择性别">
                    <el-option label="男" value="male" />
                    <el-option label="女" value="female" />
                    <el-option label="保密" value="secret" />
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :span="12">
                <el-form-item label="生日" prop="birthday">
                  <el-date-picker
                    v-model="profileForm.birthday"
                    type="date"
                    placeholder="请选择生日"
                    format="YYYY-MM-DD"
                    value-format="YYYY-MM-DD"
                  />
                </el-form-item>
              </el-col>
            </el-row>

            <el-form-item label="邮箱" prop="email">
              <el-input v-model="profileForm.email" placeholder="请输入邮箱" />
            </el-form-item>

            <el-form-item label="个人简介" prop="bio">
              <el-input
                v-model="profileForm.bio"
                type="textarea"
                :rows="4"
                placeholder="请输入个人简介"
                maxlength="200"
                show-word-limit
              />
            </el-form-item>

            <el-form-item>
              <el-button type="primary" @click="saveProfile" :loading="saving">
                保存修改
              </el-button>
              <el-button @click="resetForm">重置</el-button>
            </el-form-item>
          </el-form>
        </div>

        <!-- 账户信息 -->
        <div class="profile-section">
          <h3 class="section-title">账户信息</h3>
          <div class="account-info">
            <div class="info-item">
              <span class="info-label">用户ID：</span>
              <span class="info-value">{{ userInfo.id }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">注册时间：</span>
              <span class="info-value">{{ formatDate(userInfo.create_time) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">最后登录：</span>
              <span class="info-value">{{ formatDate(userInfo.last_login_time) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">账户状态：</span>
              <el-tag :type="userInfo.status === 'active' ? 'success' : 'danger'">
                {{ userInfo.status === 'active' ? '正常' : '禁用' }}
              </el-tag>
            </div>
          </div>
        </div>

        <!-- 安全设置 -->
        <div class="profile-section">
          <h3 class="section-title">安全设置</h3>
          <div class="security-actions">
            <div class="security-item">
              <div class="security-info">
                <h4>修改密码</h4>
                <p>定期修改密码可以提高账户安全性</p>
              </div>
              <el-button @click="changePassword">修改密码</el-button>
            </div>
            <div class="security-item">
              <div class="security-info">
                <h4>登录记录</h4>
                <p>查看您的登录历史和设备信息</p>
              </div>
              <el-button @click="viewLoginHistory">查看记录</el-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Camera } from '@element-plus/icons-vue'
import Layout from '@/components/Layout.vue'
import { teacherApi } from '@/api/user'

const router = useRouter()

// 响应式数据
const profileFormRef = ref(null)
const avatarInputRef = ref(null)
const saving = ref(false)
const userInfo = ref({})

const profileForm = reactive({
  name: '',
  phone: '',
  gender: '',
  birthday: '',
  email: '',
  bio: '',
  avatar: ''
})

const profileRules = {
  name: [
    { required: true, message: '请输入姓名', trigger: 'blur' },
    { min: 2, max: 20, message: '姓名长度在 2 到 20 个字符', trigger: 'blur' }
  ],
  email: [
    { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' }
  ],
  bio: [
    { max: 200, message: '个人简介不能超过200个字符', trigger: 'blur' }
  ]
}

// 获取用户信息
const loadUserInfo = async () => {
  try {
    const userInfoStr = localStorage.getItem('userInfo')
    if (userInfoStr) {
      userInfo.value = JSON.parse(userInfoStr)
    }

    const response = await teacherApi.getProfile()
    if (response.code === 200) {
      const data = response.data
      const userData = data.user || {}
      const teacherData = data.teacher || {}
      
      Object.assign(profileForm, {
        name: userData.real_name || '',
        phone: userData.phone || '',
        gender: teacherData.gender || '',
        birthday: teacherData.birthday || '',
        email: userData.email || '',
        bio: teacherData.bio || '',
        avatar: teacherData.avatar || ''
      })
      
      // 保存完整数据用于显示
      userInfo.value = {
        ...userData,
        ...teacherData,
        create_time: userData.create_time,
        last_visit_time: userData.last_visit_time,
        last_login_time: teacherData.last_login_time
      }
    }
  } catch (error) {
    console.error('获取用户信息失败：', error)
    ElMessage.error('获取用户信息失败')
  }
}

// 保存个人信息
const saveProfile = async () => {
  if (!profileFormRef.value) return
  
  try {
    await profileFormRef.value.validate()
    saving.value = true
    
    const response = await teacherApi.updateProfile(profileForm)
    if (response.code === 200) {
      ElMessage.success('保存成功')
      
      // 更新本地存储的用户信息
      const updatedUserInfo = { ...userInfo.value, ...profileForm }
      localStorage.setItem('userInfo', JSON.stringify(updatedUserInfo))
      userInfo.value = updatedUserInfo
    } else {
      ElMessage.error(response.message || '保存失败')
    }
  } catch (error) {
    console.error('保存个人信息失败：', error)
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
  }
}

// 重置表单
const resetForm = () => {
  if (profileFormRef.value) {
    profileFormRef.value.resetFields()
  }
  loadUserInfo()
}

// 上传头像
const uploadAvatar = () => {
  avatarInputRef.value?.click()
}

// 处理头像变化
const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // 验证文件类型和大小
  if (!file.type.startsWith('image/')) {
    ElMessage.error('请选择图片文件')
    return
  }
  
  if (file.size > 5 * 1024 * 1024) {
    ElMessage.error('图片大小不能超过5MB')
    return
  }
  
  // 创建预览
  const reader = new FileReader()
  reader.onload = (e) => {
    profileForm.avatar = e.target.result
  }
  reader.readAsDataURL(file)
  
  // 这里可以添加上传到服务器的逻辑
  // uploadAvatarToServer(file)
}

// 修改密码
const changePassword = () => {
  router.push('/teacher/password')
}

// 查看登录历史
const viewLoginHistory = () => {
  ElMessage.info('登录历史功能开发中')
}

// 格式化日期
const formatDate = (dateStr) => {
  if (!dateStr) return '未知'
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN')
}

// 生命周期
onMounted(() => {
  loadUserInfo()
})
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 0 auto;
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
.profile-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.profile-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
}

/* 头像区域 */
.avatar-section {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.avatar-wrapper {
  position: relative;
  cursor: pointer;
}

.avatar-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.avatar-wrapper:hover .avatar-overlay {
  opacity: 1;
}

.avatar-overlay .el-icon {
  color: white;
  font-size: 24px;
}

/* 表单样式 */
.profile-form {
  max-width: 600px;
}

/* 账户信息 */
.account-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.info-label {
  font-weight: 500;
  color: #606266;
  min-width: 100px;
}

.info-value {
  color: #303133;
}

/* 安全设置 */
.security-actions {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.security-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border: 1px solid #f0f0f0;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.security-item:hover {
  background: #f8f9fa;
}

.security-info h4 {
  font-size: 16px;
  font-weight: 500;
  color: #303133;
  margin: 0 0 4px;
}

.security-info p {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .profile-section {
    padding: 16px;
  }
  
  .security-item {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
  
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  
  .info-label {
    min-width: auto;
  }
}
</style> 