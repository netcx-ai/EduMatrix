import request from '@/utils/request'

// 学校端API接口
export const schoolApi = {
  // 获取学校统计信息
  getStats() {
    return request({
      url: '/school/stats',
      method: 'get'
    })
  },

  // 学院管理
  getColleges(params) {
    return request({
      url: '/school/colleges',
      method: 'get',
      params
    })
  },

  getCollege(id) {
    return request({
      url: `/school/colleges/${id}`,
      method: 'get'
    })
  },

  createCollege(data) {
    return request({
      url: '/school/colleges',
      method: 'post',
      data
    })
  },

  updateCollege(id, data) {
    return request({
      url: `/school/colleges/${id}`,
      method: 'put',
      data
    })
  },

  deleteCollege(id) {
    return request({
      url: `/school/colleges/${id}`,
      method: 'delete'
    })
  },

  // 教师管理
  getTeachers(params) {
    return request({
      url: '/school/teachers',
      method: 'get',
      params
    })
  },

  getTeacher(id) {
    return request({
      url: `/school/teachers/${id}`,
      method: 'get'
    })
  },

  createTeacher(data) {
    return request({
      url: '/school/teachers',
      method: 'post',
      data
    })
  },

  updateTeacher(id, data) {
    return request({
      url: `/school/teachers/${id}`,
      method: 'put',
      data
    })
  },

  deleteTeacher(id) {
    return request({
      url: `/school/teachers/${id}`,
      method: 'delete'
    })
  },

  // 教师审核
  getPendingTeachers() {
    return request({
      url: '/school/teachers/pending',
      method: 'get'
    })
  },

  approveTeacher(id, data) {
    return request({
      url: `/school/teachers/${id}/verify`,
      method: 'post',
      data: { action: 'approve', ...data }
    })
  },

  rejectTeacher(id, data) {
    return request({
      url: `/school/teachers/${id}/verify`,
      method: 'post',
      data: { action: 'reject', ...data }
    })
  },

  // 课程管理
  getCourses(params) {
    return request({
      url: '/school/courses',
      method: 'get',
      params
    })
  },

  updateCourseStatus(id, status) {
    return request({
      url: `/school/courses/${id}/status`,
      method: 'put',
      data: { status }
    })
  },

  // 学校设置
  getSchoolInfo() {
    return request({
      url: '/school/info',
      method: 'get'
    })
  },

  updateSchoolInfo(data) {
    return request({
      url: '/school/info',
      method: 'put',
      data
    })
  },

  // 公告管理
  getAnnouncements() {
    return request({
      url: '/school/announcements',
      method: 'get'
    })
  },

  createAnnouncement(data) {
    return request({
      url: '/school/announcements',
      method: 'post',
      data
    })
  },

  updateAnnouncement(id, data) {
    return request({
      url: `/school/announcements/${id}`,
      method: 'put',
      data
    })
  },

  deleteAnnouncement(id) {
    return request({
      url: `/school/announcements/${id}`,
      method: 'delete'
    })
  },

  // 使用统计
  getStatistics(params) {
    return request({
      url: '/school/statistics',
      method: 'get',
      params
    })
  }
} 