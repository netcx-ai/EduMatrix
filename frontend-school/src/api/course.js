import api from '@/utils/api'

export const getCourseList = (params) => api.get('/school/courses', { params })
export const getCourseDetail = (id) => api.get(`/school/courses/${id}`)
export const addCourse = (data) => api.post('/school/courses', data)
export const updateCourse = (id, data) => api.put(`/school/courses/${id}`, data)
export const deleteCourse = (id) => api.delete(`/school/courses/${id}`) 