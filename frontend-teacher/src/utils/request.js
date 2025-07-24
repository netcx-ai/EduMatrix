import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '@/router'

// 创建 axios 实例
const service = axios.create({
  baseURL: '/api',  // 使用相对路径，让 Vite 代理处理
  timeout: 10000,
  withCredentials: true  // 允许跨域请求携带凭证
})

// 缓存 token
let cachedToken = localStorage.getItem('token')

// 请求拦截器
service.interceptors.request.use(
  config => {
    // 使用缓存的 token
    if (cachedToken) {
      config.headers['token'] = cachedToken
    }
    // 确保 Content-Type 设置正确
    if (config.method === 'post') {
      config.headers['Content-Type'] = 'application/x-www-form-urlencoded'
    }
    return config
  },
  error => {
    console.error('请求错误：', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data
    
    // 如果返回的状态码不是 200，说明接口请求有误
    if (res.code !== 200) {
      // 401: 未登录或 token 过期
      if (res.code === 401) {
        // 清除缓存的 token
        cachedToken = null
        localStorage.removeItem('token')
        // 跳转到登录页
        router.push('/login')
      }
      
      return Promise.reject(new Error(res.message || '请求失败'))
    }
    
    return res
  },
  error => {
    console.error('响应错误：', error)
    return Promise.reject(error)
  }
)

// 更新 token 的方法
export const updateToken = (token) => {
  cachedToken = token
  localStorage.setItem('token', token)
}

export default service 