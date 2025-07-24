import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'
import router from '@/router'

// 创建axios实例
const api = axios.create({
  baseURL: '/api', // 使用相对路径，让 Vite 代理处理
  timeout: 30000, // 请求超时时间30秒
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  // 允许跨域携带cookie
  withCredentials: true
})

// 请求重试配置
const retryConfig = {
  retries: 3,
  retryDelay: 1000,
  retryCondition: (error) => {
    // 只对网络错误和5xx错误进行重试
    return !error.response || (error.response.status >= 500 && error.response.status < 600)
  }
}

// 请求取消控制器映射
const cancelTokenMap = new Map()

// 请求拦截器
api.interceptors.request.use(
  (config) => {
    // 生成请求唯一标识
    const requestId = `${config.method}_${config.url}_${Date.now()}`
    config.requestId = requestId
    
    // 创建取消控制器
    const cancelToken = axios.CancelToken.source()
    config.cancelToken = cancelToken.token
    cancelTokenMap.set(requestId, cancelToken)
    
    // 从localStorage获取token
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // 添加请求时间戳，防止缓存
    if (config.method === 'get') {
      config.params = {
        ...config.params,
        _t: Date.now()
      }
    }
    
    // 记录请求日志（开发环境）
    if (process.env.NODE_ENV === 'development') {
      console.log(`[API Request] ${config.method?.toUpperCase()} ${config.url}`, {
        params: config.params,
        data: config.data,
        headers: config.headers
      })
    }
    
    return config
  },
  (error) => {
    console.error('[API Request Error]', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
api.interceptors.response.use(
  (response) => {
    const { config, data } = response
    
    // 清理取消控制器
    if (config.requestId) {
      cancelTokenMap.delete(config.requestId)
    }
    
    // 记录响应日志（开发环境）
    if (process.env.NODE_ENV === 'development') {
      console.log(`[API Response] ${config.method?.toUpperCase()} ${config.url}`, data)
    }
    
    // 统一处理业务错误
    if (data.code !== 200 && data.code !== 0) {
      // 特殊错误码处理
      switch (data.code) {
        case 401:
          handleUnauthorized()
          break
        case 403:
          ElMessage.error('权限不足，无法访问该资源')
          break
        case 404:
          ElMessage.error('请求的资源不存在')
          break
        case 422:
          // 表单验证错误
          if (data.errors && typeof data.errors === 'object') {
            const errorMessages = Object.values(data.errors).flat()
            ElMessage.error(errorMessages.join('; '))
          } else {
            ElMessage.error(data.message || '数据验证失败')
          }
          break
        case 429:
          ElMessage.error('请求过于频繁，请稍后再试')
          break
        case 500:
          ElMessage.error('服务器内部错误，请联系管理员')
          break
        case 503:
          ElMessage.error('服务暂时不可用，请稍后再试')
          break
        default:
          ElMessage.error(data.message || '请求失败')
      }
      
      return Promise.reject(new Error(data.message || '请求失败'))
    }
    
    return data
  },
  async (error) => {
    const { config, response, code, message } = error
    
    // 清理取消控制器
    if (config?.requestId) {
      cancelTokenMap.delete(config.requestId)
    }
    
    // 请求被取消
    if (axios.isCancel(error)) {
      console.log('[API Request Cancelled]', error.message)
      return Promise.reject(error)
    }
    
    // 网络错误
    if (!response) {
      ElMessage.error('网络连接失败，请检查网络设置')
      return Promise.reject(error)
    }
    
    const { status, data } = response
    
    // HTTP状态码处理
    switch (status) {
      case 401:
        handleUnauthorized()
        break
      case 403:
        ElMessage.error('权限不足，无法访问该资源')
        break
      case 404:
        ElMessage.error('请求的资源不存在')
        break
      case 405:
        ElMessage.error('请求方法不允许')
        break
      case 408:
        ElMessage.error('请求超时，请稍后重试')
        break
      case 422:
        // 表单验证错误
        if (data?.errors && typeof data.errors === 'object') {
          const errorMessages = Object.values(data.errors).flat()
          ElMessage.error(errorMessages.join('; '))
        } else {
          ElMessage.error(data?.message || '数据验证失败')
        }
        break
      case 429:
        ElMessage.error('请求过于频繁，请稍后再试')
        break
      case 500:
        ElMessage.error('服务器内部错误，请联系管理员')
        break
      case 502:
        ElMessage.error('网关错误，请稍后重试')
        break
      case 503:
        ElMessage.error('服务暂时不可用，请稍后再试')
        break
      case 504:
        ElMessage.error('网关超时，请稍后重试')
        break
      default:
        ElMessage.error(data?.message || `请求失败 (${status})`)
    }
    
    // 记录错误日志
    console.error('[API Response Error]', {
      url: config?.url,
      method: config?.method,
      status,
      data,
      error: error.message
    })
    
    return Promise.reject(error)
  }
)

/**
 * 处理未授权错误
 */
function handleUnauthorized() {
  // 清除本地存储的用户信息
  localStorage.removeItem('token')
  localStorage.removeItem('userInfo')
  sessionStorage.clear()
  
  // 显示提示
  ElMessage.error('登录已过期，请重新登录')
  
  // 跳转到登录页
  if (router.currentRoute.value.path !== '/login') {
    router.push('/login')
  }
}

/**
 * 取消指定请求
 * @param {string} requestId 请求ID
 */
export function cancelRequest(requestId) {
  const cancelToken = cancelTokenMap.get(requestId)
  if (cancelToken) {
    cancelToken.cancel('请求被取消')
    cancelTokenMap.delete(requestId)
  }
}

/**
 * 取消所有请求
 */
export function cancelAllRequests() {
  cancelTokenMap.forEach((cancelToken) => {
    cancelToken.cancel('取消所有请求')
  })
  cancelTokenMap.clear()
}

/**
 * 带重试的请求方法
 * @param {Function} requestFn 请求函数
 * @param {number} retries 重试次数
 * @param {number} delay 重试延迟
 * @returns {Promise}
 */
export async function requestWithRetry(requestFn, retries = retryConfig.retries, delay = retryConfig.retryDelay) {
  try {
    return await requestFn()
  } catch (error) {
    if (retries > 0 && retryConfig.retryCondition(error)) {
      await new Promise(resolve => setTimeout(resolve, delay))
      return requestWithRetry(requestFn, retries - 1, delay * 2)
    }
    throw error
  }
}

/**
 * 带确认的请求方法
 * @param {Function} requestFn 请求函数
 * @param {string} message 确认消息
 * @param {string} title 确认标题
 * @returns {Promise}
 */
export async function requestWithConfirm(requestFn, message = '确定要执行此操作吗？', title = '确认操作') {
  try {
    await ElMessageBox.confirm(message, title, {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    return await requestFn()
  } catch (error) {
    if (error === 'cancel') {
      return Promise.reject(new Error('用户取消操作'))
    }
    throw error
  }
}

/**
 * 带加载状态的请求方法
 * @param {Function} requestFn 请求函数
 * @param {string} loadingText 加载文本
 * @returns {Promise}
 */
export async function requestWithLoading(requestFn, loadingText = '加载中...') {
  const loading = ElMessage({
    message: loadingText,
    type: 'info',
    duration: 0,
    showClose: false
  })
  
  try {
    const result = await requestFn()
    loading.close()
    return result
  } catch (error) {
    loading.close()
    throw error
  }
}

// 导出默认实例
export default api 