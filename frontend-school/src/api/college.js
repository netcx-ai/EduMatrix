import api from '@/utils/api'

// 获取学院列表
export const getCollegeList = (params) => api.get('/school/college', { params })

// 获取学院下拉列表（用于选择框）
export const getCollegeOptions = () => api.get('/school/college/list')

// 获取学院详情
export const getCollegeDetail = (id) => api.get(`/school/college/${id}`)

// 新增学院
export const addCollege = (data) => api.post('/school/college', data)

// 编辑学院
export const updateCollege = (id, data) => api.put(`/school/college/${id}`, data)

// 删除学院
export const deleteCollege = (id) => api.delete(`/school/college/${id}`) 