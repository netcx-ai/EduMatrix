import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/utils/api'

export const useUserStore = defineStore('user', () => {
  // 状态
  const token = ref(localStorage.getItem('token') || '')
  function safeParseUserInfo() {
    try {
      const raw = localStorage.getItem('userInfo')
      if (!raw || raw === 'undefined') return {}
      return JSON.parse(raw)
    } catch (e) {
      return {}
    }
  }
  const userInfo = ref(safeParseUserInfo())
  const isLoggedIn = ref(!!token.value)

  // 登录
  const login = async (credentials) => {
    try {
      const response = await api.post('/auth/login', credentials)
      const { token: newToken, user } = response.data
      
      // 保存token和用户信息
      token.value = newToken
      userInfo.value = user
      isLoggedIn.value = true
      
      localStorage.setItem('token', newToken)
      localStorage.setItem('userInfo', JSON.stringify(user))
      
      return response
    } catch (error) {
      throw error
    }
  }

  // 登出
  const logout = () => {
    token.value = ''
    userInfo.value = {}
    isLoggedIn.value = false
    
    localStorage.removeItem('token')
    localStorage.removeItem('userInfo')
  }

  // 获取用户信息
  const getUserInfo = async () => {
    try {
      const response = await api.get('/auth/profile')
      userInfo.value = response.data
      localStorage.setItem('userInfo', JSON.stringify(response.data))
      return response.data
    } catch (error) {
      throw error
    }
  }

  // 更新用户信息
  const updateUserInfo = async (userData) => {
    try {
      const response = await api.put('/auth/profile', userData)
      userInfo.value = response.data
      localStorage.setItem('userInfo', JSON.stringify(response.data))
      return response.data
    } catch (error) {
      throw error
    }
  }

  // 修改密码
  const changePassword = async (passwordData) => {
    try {
      const response = await api.put('/auth/change-password', passwordData)
      return response
    } catch (error) {
      throw error
    }
  }

  // 检查认证状态
  const checkAuth = async () => {
    if (token.value) {
      try {
        await getUserInfo()
      } catch (error) {
        // 如果获取用户信息失败，清除本地存储
        logout()
      }
    }
  }

  return {
    token,
    userInfo,
    isLoggedIn,
    login,
    logout,
    getUserInfo,
    updateUserInfo,
    changePassword,
    checkAuth
  }
}) 