import api from '@/utils/api'

export const userApi = {
  // 用户登录（统一接口 /user/login）
  login: (data = {}) => {
    // 统一走 /user/login，后端根据 user_type 字段区分逻辑
    return api.post('/user/login', data)
  },
  // 可扩展注册、获取用户信息等方法
} 