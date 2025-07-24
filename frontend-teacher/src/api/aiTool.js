// 教师端AI工具API封装
import api from '@/api/user'

// AI工具相关接口
export const aiToolApi = {
  // 获取AI工具列表
  getList: (params) => {
    return api.get('/teacher/ai-tool/list', { params })
  },

  // 获取AI工具详情
  getDetail: (params) => {
    return api.get('/teacher/ai-tool/detail', { params })
  },

  // 生成AI内容
  generate: (data) => {
    return api.post('/teacher/ai-tool/generate', data, {
      timeout: 180000 // 3分钟超时，适应AI生成时间
    })
  },

  // 保存内容到内容库
  saveContent: (data) => {
    return api.post('/teacher/ai-tool/save-content', data)
  },

  // 获取内容分类列表
  getCategories: () => {
    return api.get('/teacher/ai-tool/categories')
  },

  // 导出Word文档
  exportWord: (data) => {
    return api.post('/teacher/ai-tool/export-word', data)
  },

  // 保存到文件中心
  saveToFileCenter: (data) => {
    return api.post('/teacher/ai-tool/save-to-file-center', data)
  },

  // 获取AI工具使用历史
  getHistory: (params) => {
    return api.get('/teacher/ai/history', { params })
  },

  // 获取AI工具使用统计
  getStatistics: (params) => {
    return api.get('/teacher/ai/statistics', { params })
  },

  // 新增：获取AI工具表单配置
  getToolFormConfig: (tool_code) => {
    return api.get('/teacher/ai-tool/getToolFormConfig', { params: { tool_code } })
  }
}

export default aiToolApi; 