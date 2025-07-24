import api from '@/utils/api'

// 获取个人信息
export const getProfile = () => api.get('/school/profile')

// 更新个人信息
export const updateProfile = (data) => api.put('/school/profile', data)

// 修改密码
export const changePassword = (data) => api.post('/school/profile/password', data)

// 上传头像
export const uploadAvatar = (file) => {
  const formData = new FormData()
  formData.append('avatar', file)
  return api.post('/school/profile/avatar', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
} 