import request from './request'

export default request
 
// 说明：为兼容旧代码中 `@/utils/axios` 的引用，简单 re-export `request` 实例。
// 后续组件可逐步改为直接 `import request from '@/utils/request'` 