<template>
  <div class="not-found">
    <div class="not-found-content">
      <h1>404</h1>
      <h2>页面不存在</h2>
      <p>抱歉，您访问的页面不存在或已被删除。</p>
      <el-button type="primary" @click="goHome">返回首页</el-button>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router'

export default {
  name: 'NotFound',
  setup() {
    const router = useRouter()
    
    const goHome = () => {
      router.push('/dashboard')
    }
    
    return {
      goHome
    }
  }
}
</script>

<style scoped>
.not-found {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f2f5;
}

.not-found-content {
  text-align: center;
  padding: 40px;
}

.not-found-content h1 {
  font-size: 120px;
  color: #409EFF;
  margin: 0;
  font-weight: bold;
}

.not-found-content h2 {
  font-size: 24px;
  color: #333;
  margin: 20px 0;
}

.not-found-content p {
  color: #666;
  margin-bottom: 30px;
  font-size: 16px;
}
</style> 