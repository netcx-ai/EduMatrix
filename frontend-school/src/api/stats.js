import api from '@/utils/api'

export const statsApi = {
  getStats: () => api.get('/school/stats')
} 