import api from '@/utils/api'

export const getSettings = () => api.get('/school/settings')
export const saveSettings = (data) => api.post('/school/settings', data) 