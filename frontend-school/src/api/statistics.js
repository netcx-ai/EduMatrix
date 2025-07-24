import api from '@/utils/api'

// 获取统计数据
export const getStatistics = (params) => api.get('/school/statistics', { params })

// 导出统计报告
export const exportStatistics = (params) => api.post('/school/statistics/export', params)

// 获取详细统计列表
export const getStatisticsList = (params) => api.get('/school/statistics/list', { params }) 