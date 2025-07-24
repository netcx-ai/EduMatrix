<template>
  <div class="profile">
    <div class="page-header">
      <h2>个人设置</h2>
    </div>
    
    <div class="profile-container">
      <!-- 个人信息卡片 -->
      <el-card class="profile-card">
        <template #header>
          <div class="card-header">
            <span>个人信息</span>
          </div>
        </template>
        
        <el-form
          ref="profileFormRef"
          :model="profileForm"
          :rules="profileRules"
          label-width="100px"
          class="profile-form"
        >
          <div class="avatar-section">
            <div class="avatar-container">
              <el-avatar
                :size="80"
                :src="profileForm.avatar"
                :icon="UserFilled"
                class="user-avatar"
              />
              <el-upload
                class="avatar-uploader"
                :show-file-list="false"
                :before-upload="beforeAvatarUpload"
                :http-request="handleAvatarUpload"
              >
                <el-button size="small" type="primary">更换头像</el-button>
              </el-upload>
            </div>
          </div>
          
          <el-form-item label="用户名" prop="username">
            <el-input v-model="profileForm.username" disabled />
          </el-form-item>
          
          <el-form-item label="真实姓名" prop="real_name">
            <el-input v-model="profileForm.real_name" placeholder="请输入真实姓名" />
          </el-form-item>
          
          <el-form-item label="邮箱" prop="email">
            <el-input v-model="profileForm.email" placeholder="请输入邮箱" />
          </el-form-item>
          
          <el-form-item label="手机号" prop="phone">
            <el-input v-model="profileForm.phone" placeholder="请输入手机号" />
          </el-form-item>
          
          <el-form-item label="用户类型">
            <el-tag :type="getUserTypeTag(profileForm.user_type)">
              {{ getUserTypeText(profileForm.user_type) }}
            </el-tag>
          </el-form-item>
          
          <el-form-item label="最后登录">
            <span class="text-muted">{{ formatTime(profileForm.last_login_time) }}</span>
          </el-form-item>
          
          <el-form-item label="注册时间">
            <span class="text-muted">{{ formatTime(profileForm.create_time) }}</span>
          </el-form-item>
          
          <el-form-item>
            <el-button type="primary" @click="handleUpdateProfile" :loading="updating">
              保存信息
            </el-button>
          </el-form-item>
        </el-form>
      </el-card>
      
      <!-- 密码修改卡片 -->
      <el-card class="password-card">
        <template #header>
          <div class="card-header">
            <span>修改密码</span>
          </div>
        </template>
        
        <el-form
          ref="passwordFormRef"
          :model="passwordForm"
          :rules="passwordRules"
          label-width="100px"
          class="password-form"
        >
          <el-form-item label="原密码" prop="old_password">
            <el-input
              v-model="passwordForm.old_password"
              type="password"
              placeholder="请输入原密码"
              show-password
            />
          </el-form-item>
          
          <el-form-item label="新密码" prop="new_password">
            <el-input
              v-model="passwordForm.new_password"
              type="password"
              placeholder="请输入新密码"
              show-password
            />
          </el-form-item>
          
          <el-form-item label="确认密码" prop="confirm_password">
            <el-input
              v-model="passwordForm.confirm_password"
              type="password"
              placeholder="请确认新密码"
              show-password
            />
          </el-form-item>
          
          <el-form-item>
            <el-button type="primary" @click="handleChangePassword" :loading="changingPassword">
              修改密码
            </el-button>
            <el-button @click="resetPasswordForm">重置</el-button>
          </el-form-item>
        </el-form>
      </el-card>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { UserFilled } from '@element-plus/icons-vue'
import { getProfile, updateProfile, changePassword, uploadAvatar } from '@/api/profile'

export default {
  name: 'Profile',
  setup() {
    const profileFormRef = ref()
    const passwordFormRef = ref()
    const updating = ref(false)
    const changingPassword = ref(false)
    
    // 个人信息表单
    const profileForm = reactive({
      id: '',
      username: '',
      real_name: '',
      email: '',
      phone: '',
      avatar: '',
      user_type: '',
      school_id: '',
      last_login_time: '',
      create_time: ''
    })
    
    // 密码修改表单
    const passwordForm = reactive({
      old_password: '',
      new_password: '',
      confirm_password: ''
    })
    
    // 个人信息验证规则
    const profileRules = {
      real_name: [
        { required: true, message: '请输入真实姓名', trigger: 'blur' },
        { min: 2, max: 50, message: '长度在 2 到 50 个字符', trigger: 'blur' }
      ],
      email: [
        { required: true, message: '请输入邮箱', trigger: 'blur' },
        { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
      ],
      phone: [
        { required: true, message: '请输入手机号', trigger: 'blur' },
        { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号格式', trigger: 'blur' }
      ]
    }
    
    // 密码修改验证规则
    const passwordRules = {
      old_password: [
        { required: true, message: '请输入原密码', trigger: 'blur' }
      ],
      new_password: [
        { required: true, message: '请输入新密码', trigger: 'blur' },
        { min: 6, max: 20, message: '长度在 6 到 20 个字符', trigger: 'blur' }
      ],
      confirm_password: [
        { required: true, message: '请确认新密码', trigger: 'blur' },
        {
          validator: (rule, value, callback) => {
            if (value !== passwordForm.new_password) {
              callback(new Error('两次密码输入不一致'))
            } else {
              callback()
            }
          },
          trigger: 'blur'
        }
      ]
    }
    
    // 获取个人信息
    const fetchProfile = async () => {
      try {
        const res = await getProfile()
        const data = res.data || res
        Object.assign(profileForm, data)
      } catch (error) {
        console.error('获取个人信息失败:', error)
        ElMessage.error('获取个人信息失败')
      }
    }
    
    // 更新个人信息
    const handleUpdateProfile = async () => {
      try {
        await profileFormRef.value.validate()
        updating.value = true
        
        const data = {
          real_name: profileForm.real_name,
          email: profileForm.email,
          phone: profileForm.phone,
          avatar: profileForm.avatar
        }
        
        await updateProfile(data)
        ElMessage.success('个人信息更新成功')
      } catch (error) {
        if (error.message) {
          ElMessage.error(error.message)
        }
      } finally {
        updating.value = false
      }
    }
    
    // 修改密码
    const handleChangePassword = async () => {
      try {
        await passwordFormRef.value.validate()
        changingPassword.value = true
        
        await changePassword({
          old_password: passwordForm.old_password,
          new_password: passwordForm.new_password,
          confirm_password: passwordForm.confirm_password
        })
        
        ElMessage.success('密码修改成功')
        resetPasswordForm()
      } catch (error) {
        if (error.message) {
          ElMessage.error(error.message)
        }
      } finally {
        changingPassword.value = false
      }
    }
    
    // 重置密码表单
    const resetPasswordForm = () => {
      Object.assign(passwordForm, {
        old_password: '',
        new_password: '',
        confirm_password: ''
      })
      passwordFormRef.value?.clearValidate()
    }
    
    // 头像上传前验证
    const beforeAvatarUpload = (file) => {
      const isImage = file.type.startsWith('image/')
      const isLt2M = file.size / 1024 / 1024 < 2
      
      if (!isImage) {
        ElMessage.error('只能上传图片文件!')
        return false
      }
      if (!isLt2M) {
        ElMessage.error('图片大小不能超过 2MB!')
        return false
      }
      return true
    }
    
    // 处理头像上传
    const handleAvatarUpload = async ({ file }) => {
      try {
        const res = await uploadAvatar(file)
        const data = res.data || res
        profileForm.avatar = data.url
        ElMessage.success('头像上传成功')
      } catch (error) {
        ElMessage.error('头像上传失败')
      }
    }
    
    // 获取用户类型标签
    const getUserTypeTag = (type) => {
      const tags = {
        'school_admin': 'success',
        'teacher': 'primary',
        'student': 'info'
      }
      return tags[type] || 'info'
    }
    
    // 获取用户类型文本
    const getUserTypeText = (type) => {
      const texts = {
        'school_admin': '学校管理员',
        'teacher': '教师',
        'student': '学生'
      }
      return texts[type] || '未知'
    }
    
    // 格式化时间
    const formatTime = (time) => {
      if (!time) return '暂无'
      return new Date(time).toLocaleString('zh-CN')
    }
    
    onMounted(() => {
      fetchProfile()
    })
    
    return {
      profileFormRef,
      passwordFormRef,
      profileForm,
      passwordForm,
      profileRules,
      passwordRules,
      updating,
      changingPassword,
      handleUpdateProfile,
      handleChangePassword,
      resetPasswordForm,
      beforeAvatarUpload,
      handleAvatarUpload,
      getUserTypeTag,
      getUserTypeText,
      formatTime,
      UserFilled
    }
  }
}
</script>

<style scoped>
.profile {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  padding: 24px;
  background: #f5f7fa;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h2 {
  margin: 0;
  color: #333;
  font-size: 24px;
  font-weight: 600;
}

.profile-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  max-width: 1200px;
}

.profile-card,
.password-card {
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(102, 126, 234, 0.08);
  background: #fff;
}

.card-header {
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.avatar-section {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
}

.avatar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.user-avatar {
  border: 4px solid #f0f0f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.avatar-uploader {
  text-align: center;
}

.profile-form,
.password-form {
  padding: 20px 0;
}

.text-muted {
  color: #666;
  font-size: 14px;
}

/* 移动端适配 */
@media (max-width: 768px) {
  .profile {
    padding: 16px;
  }
  
  .profile-container {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .page-header h2 {
    font-size: 20px;
  }
}

/* 表单样式优化 */
:deep(.el-form-item__label) {
  font-weight: 500;
  color: #333;
}

:deep(.el-input__wrapper) {
  border-radius: 8px;
}

:deep(.el-button) {
  border-radius: 8px;
}

:deep(.el-card__header) {
  padding: 20px 24px;
  border-bottom: 1px solid #f0f0f0;
}

:deep(.el-card__body) {
  padding: 24px;
}
</style> 