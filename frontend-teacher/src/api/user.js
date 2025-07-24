import axios from 'axios'

// 创建axios实例
const api = axios.create({
  // 统一走 Vite 代理，避免端口及跨域问题
  baseURL: '/api',
  timeout: 120000, // 增加到120秒，适应AI生成时间
  headers: {
    'Content-Type': 'application/json'
  }
})

// 请求拦截器
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
api.interceptors.response.use(
  response => {
    return response.data
  },
  error => {
    if (error.response) {
      // 服务器返回错误状态码
      const { status, data } = error.response
      switch (status) {
        case 401:
          // 未授权，清除token并跳转到登录页
          localStorage.removeItem('token')
          window.location.href = '/login'
          break
        case 403:
          // 权限不足
          console.error('权限不足')
          break
        case 404:
          // 资源不存在
          console.error('请求的资源不存在')
          break
        case 500:
          // 服务器内部错误
          console.error('服务器内部错误')
          break
        default:
          console.error('请求失败')
      }
      return Promise.reject(new Error(data.message || '请求失败'))
    } else if (error.request) {
      // 请求已发出但没有收到响应
      return Promise.reject(new Error('网络连接失败'))
    } else {
      // 请求配置出错
      return Promise.reject(new Error('请求配置错误'))
    }
  }
)

// 用户相关API
export const userApi = {
  // 用户登录（统一接口 /user/login）
  login: (data = {}) => {
    // 统一走 /user/login，后端根据 user_type 字段区分逻辑
    return api.post('/user/login', data)
  },

  // 用户注册
  register: (data) => {
    return api.post('/user/register', data)
  },

  // 重置密码
  resetPassword: (data) => {
    return api.post('/user/resetPassword', data)
  },

  // 获取用户信息
  getUserInfo: () => {
    return api.get('/user/info')
  },

  // 更新用户信息
  updateUserInfo: (data) => {
    return api.put('/user/info', data)
  },

  // 修改密码
  changePassword: (data) => {
    return api.put('/user/password', data)
  },

  // 退出登录
  logout: () => {
    return api.post('/auth/logout')
  }
}

// 教师相关API
export const teacherApi = {
  // 获取课程列表
  getCourses: (params) => {
    return api.get('/teacher/courses', { params })
  },

  // 获取课程详情
  getCourseDetail: (id) => {
    return api.get(`/teacher/courses/${id}`)
  },

  // 获取文件列表
  getFiles: (params) => {
    return api.get('/teacher/files', { params })
  },

  // 上传文件
  uploadFile: (data) => {
    return api.post('/teacher/files/upload', data)
  },

  // 删除文件
  deleteFile: (id) => {
    return api.delete(`/teacher/files/${id}`)
  },

  // 获取文件详情
  getFileDetail: (id) => {
    return api.get(`/teacher/files/${id}`)
  },

  // 获取AI工具列表
  getAiTools: (params = {}) => {
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}')
    const query = {
      school_id: params.school_id ?? userInfo.primary_school_id,
      user_id: params.user_id ?? userInfo.id,
      ...params
    }
    return api.get('/teacher/ai/tools', { params: query })
  },

  // 使用AI工具
  useAiTool: (toolCodeOrData, data = {}) => {
    // 兼容原 signature: (toolId, data)
    // 如果第一个参数是对象，则直接视为完整 payload
    if (typeof toolCodeOrData === 'object') {
      return api.post('/teacher/ai/generate', toolCodeOrData)
    }
    // 否则认为是 tool_code / toolId，合并到 payload
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}')
    const payload = {
      tool_code: toolCodeOrData,
      school_id: data.school_id ?? userInfo.primary_school_id,
      user_id: data.user_id ?? userInfo.id,
      ...data
    }
    return api.post('/teacher/ai/generate', payload)
  },

  // 获取AI使用统计
  getAiUsageStats: () => {
    return api.get('/teacher/ai/statistics')
  },

  // 获取AI使用历史
  getAiUsageHistory: (params = {}) => {
    return api.get('/teacher/ai/history', { params })
  },

  // 获取AI使用记录（别名）
  getAiUsageRecords: (params = {}) => {
    return api.get('/teacher/ai/history', { params })
  },

  // 创建内容
  createContent: (data) => {
    return api.post('/teacher/content/create', data)
  },

  // 获取内容列表
  getContentList: (params) => {
    return api.get('/teacher/content/list', { params })
  },

  // 导出文档
  exportDocument: (data) => {
    return api.post('/teacher/content/export', data)
  },

  // 获取AI工具使用历史
  getAiUsageHistory: (params = {}) => {
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}')
    const query = {
      school_id: params.school_id ?? userInfo.primary_school_id,
      user_id: params.user_id ?? userInfo.id,
      ...params
    }
    return api.get('/ai/usage/history', { params: query })
  },

  // 获取AI工具使用统计
  getAiUsageStats: (params = {}) => {
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}')
    const query = {
      school_id: params.school_id ?? userInfo.primary_school_id,
      user_id: params.user_id ?? userInfo.id,
      ...params
    }
    return api.get('/ai/usage/statistics', { params: query })
  },

  // 获取教师个人信息
  getProfile: () => {
    return api.get('/teacher/profile')
  },

  // 更新教师个人信息
  updateProfile: (data) => {
    return api.put('/teacher/profile', data)
  },

  // 修改密码
  changePassword: (data) => {
    return api.put('/teacher/password', data)
  },

  // 获取教师统计信息
  getStatistics: () => {
    return api.get('/teacher/statistics')
  },

  // 获取教师统计信息（别名）
  getStats: () => {
    return api.get('/teacher/statistics')
  },

  // 删除内容
  deleteContent: (id) => {
    return api.delete(`/teacher/content/delete/${id}`)
  },

  // 获取内容统计
  getContentStatistics: () => {
    return api.get('/teacher/content/statistics')
  },

  // 提交审核
  submitAudit: (data) => {
    return api.post('/teacher/content/submit-audit', data)
  }
}

// 学校管理员相关API
export const schoolApi = {
  // 获取学院列表
  getColleges: (params) => {
    return api.get('/school/colleges', { params })
  },

  // 创建学院
  createCollege: (data) => {
    return api.post('/school/colleges', data)
  },

  // 更新学院
  updateCollege: (id, data) => {
    return api.put(`/school/colleges/${id}`, data)
  },

  // 删除学院
  deleteCollege: (id) => {
    return api.delete(`/school/colleges/${id}`)
  },

  // 获取学院详情
  getCollegeDetail: (id) => {
    return api.get(`/school/colleges/${id}`)
  },

  // 获取学院下拉列表
  getCollegeList: () => {
    return api.get('/school/colleges/list')
  },

  // 获取教师列表
  getTeachers: (params) => {
    return api.get('/school/teachers', { params })
  },

  // 添加教师
  addTeacher: (data) => {
    return api.post('/school/teachers', data)
  },

  // 更新教师信息
  updateTeacher: (id, data) => {
    return api.put(`/school/teachers/${id}`, data)
  },

  // 删除教师
  deleteTeacher: (id) => {
    return api.delete(`/school/teachers/${id}`)
  },

  // 获取教师详情
  getTeacherDetail: (id) => {
    return api.get(`/school/teachers/${id}`)
  },

  // 获取待审核教师
  getPendingTeachers: (params) => {
    return api.get('/school/teachers/pending', { params })
  },

  // 审核教师
  reviewTeacher: (id, data) => {
    return api.post(`/school/teachers/${id}/review`, data)
  },

  // 批量审核教师
  batchReviewTeachers: (data) => {
    return api.post('/school/teachers/batch-review', data)
  },

  // 更新教师状态
  updateTeacherStatus: (id, data) => {
    return api.put(`/school/teachers/${id}/status`, data)
  },

  // 获取教师统计信息
  getTeacherStats: () => {
    return api.get('/school/teachers/stats')
  },

  // 获取课程列表
  getCourses: (params) => {
    return api.get('/school/courses', { params })
  },

  // 获取课程详情
  getCourseDetail: (id) => {
    return api.get(`/school/courses/${id}`)
  },

  // 更新课程状态
  updateCourseStatus: (id, data) => {
    return api.put(`/school/courses/${id}/status`, data)
  },

  // 获取统计信息
  getStatistics: () => {
    return api.get('/school/statistics')
  },

  // 获取学校管理员信息
  getProfile: () => {
    return api.get('/school/profile')
  },

  // 更新学校管理员信息
  updateProfile: (data) => {
    return api.put('/school/profile', data)
  },

  // 修改密码
  changePassword: (data) => {
    return api.put('/school/password', data)
  }
}

// 内容库相关API
export const contentApi = {
  // 获取内容列表
  getContentList: (params) => {
    return api.get('/teacher/content/list', { params })
  },

  // 获取内容详情
  getContentDetail: (id) => {
    return api.get(`/teacher/content/detail/${id}`)
  },

  // 导出Word文档
  exportDocument: (data) => {
    return api.post('/teacher/content/export', data)
  },

  // 下载文档
  downloadDocument: (filename) => {
    return api.get(`/teacher/content/download/${filename}`, {
      responseType: 'blob'
    })
  },

  // 提交审核
  submitAudit: (data) => {
    return api.post('/teacher/content/submit-audit', data)
  },

  // 删除内容
  deleteContent: (id) => {
    return api.delete(`/teacher/content/delete/${id}`)
  },

  // 获取内容统计
  getContentStatistics: () => {
    return api.get('/teacher/content/statistics')
  }
}

// AI工具相关API
export const aiApi = {
  // 获取AI工具列表
  getAiTools: (params = {}) => {
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}')
    const query = {
      school_id: params.school_id ?? userInfo.primary_school_id,
      user_id: params.user_id ?? userInfo.id,
      ...params
    }
    return api.get('/teacher/ai/tools', { params: query })
  },

  // 使用AI工具
  useAiTool: (toolCodeOrData, data = {}) => {
    // 兼容原 signature: (toolId, data)
    // 如果第一个参数是对象，则直接视为完整 payload
    if (typeof toolCodeOrData === 'object') {
      return api.post('/teacher/ai/generate', toolCodeOrData)
    }
    // 否则认为是 tool_code / toolId，合并到 payload
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}')
    const payload = {
      tool_code: toolCodeOrData,
      school_id: data.school_id ?? userInfo.primary_school_id,
      user_id: data.user_id ?? userInfo.id,
      ...data
    }
    return api.post('/teacher/ai/generate', payload)
  },

  // 批量生成
  batchGenerate: (data) => {
    return api.post('/teacher/ai/batch-generate', data)
  },

  // 重新生成
  regenerate: (data) => {
    return api.post('/teacher/ai/regenerate', data)
  },

  // 获取使用历史
  getAiHistory: (params = {}) => {
    return api.get('/teacher/ai/history', { params })
  },

  // 获取使用统计
  getAiStatistics: () => {
    return api.get('/teacher/ai/statistics')
  }
}

// 导出默认API实例
export default api 

// -----------------------
// 额外导出便捷方法，供组件直接解构使用
// -----------------------

// 注册（语法糖 => userApi.register）
export const register = (data) => userApi.register(data)

// 发送短信验证码
export const sendVerificationCode = (phone) => api.post('/sms/sendCode', { phone }) 