import api from '@/utils/api'

export const getTeacherList = (params) => api.get('/school/teachers', { params })
export const getTeacherDetail = (id) => api.get(`/school/teachers/${id}`)
export const addTeacher = (data) => api.post('/school/teachers', data)
export const updateTeacher = (id, data) => api.put(`/school/teachers/${id}`, data)
export const deleteTeacher = (id) => api.delete(`/school/teachers/${id}`)

// 获取职称选项
export const getTitleOptions = () => api.get('/school/teacher_title/options')

// 教师审核相关接口
export const getAuditList = (params) => api.get('/school/teachers/audit', { params })
export const approveTeacher = (id, data = {}) => api.post(`/school/teachers/${id}/approve`, data)
export const rejectTeacher = (id, data) => api.post(`/school/teachers/${id}/reject`, data)
export const batchAudit = (data) => api.post('/school/teachers/batch-audit', data)
export const getPendingCount = () => api.get('/school/teachers/pending-count') 