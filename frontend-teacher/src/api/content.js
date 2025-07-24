// 新建内容中心API封装
import api from '@/api/user'

// 教师内容中心相关接口
export const contentApi = {
  // 获取个人空间内容列表
  getPersonalContent: (params) => {
    return api.get('/teacher/content/personal', { params })
  },

  // 获取课程空间内容列表
  getCourseContent: (params) => {
    return api.get('/teacher/content/course', { params })
  },

  // 上传内容文件（草稿）
  uploadContent: (data) => {
    return api.post('/teacher/content/upload', data)
  },

  // 通过 AI 生成内容（直接保存草稿）
  generateByAi: (data) => {
    return api.post('/teacher/ai/generate', data)
  },

  // 提交内容审核
  submitAudit: (data) => {
    return api.post('/teacher/content/submit', data)
  },

  // 切换内容可见性
  toggleVisibility: (data) => {
    return api.put('/teacher/content/visibility', data)
  },

  // 获取内容详情 (用于预览)
  getContentDetail: (id) => {
    return api.get(`/teacher/preview/content/${id}`)
  },

  // 删除内容
  deleteContent: (id) => {
    return api.delete(`/teacher/content/delete/${id}`)
  },

  // 创建内容
  createContent: (data) => {
    return api.post('/teacher/content/create', data)
  },

  // 更新内容
  updateContent: (data) => {
    return api.put(`/teacher/content/update/${data.id}`, data)
  },

  // 获取内容详情（用于编辑）
  getContentDetailForEdit: (id) => {
    return api.get(`/teacher/content/detail/${id}`)
  },

  // 重新生成AI内容
  regenerateContent: (data) => {
    return api.post('/teacher/content/regenerate', data)
  },

  // 导出Word文档
  exportWord: (data) => {
    return api.post('/teacher/content/export-word', data)
  },

  // 保存到文件中心
  saveToFileCenter: (data) => {
    return api.post('/teacher/content/save-to-file-center', data)
  },

  // 获取可用文件列表
  getAvailableFiles: (params) => {
    return api.get('/teacher/content/available-files', { params })
  },

  // 关联文件
  associateFiles: (data) => {
    return api.post('/teacher/content/associate-files', data)
  }
}

export default contentApi; 