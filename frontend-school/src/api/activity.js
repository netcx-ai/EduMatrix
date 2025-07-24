import api from '@/utils/api'

export const getRecentActivities = (params) => api.get('/school/activity', { params }) 