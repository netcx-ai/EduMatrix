<template>
  <div class="home">
    <!-- 顶部导航栏 -->
    <el-header class="header">
      <div class="header-content">
        <div class="logo">
          <h2>🏠 EduMatrix 教育管理系统</h2>
        </div>
        <div class="nav-menu">
          <el-menu mode="horizontal" :router="true" class="nav-menu">
            <el-menu-item index="/">首页</el-menu-item>
            <el-menu-item index="/help">帮助中心</el-menu-item>
            <el-menu-item index="/pricing">定价方案</el-menu-item>
            <el-menu-item index="/login">登录</el-menu-item>
            <el-menu-item index="/register">注册</el-menu-item>
          </el-menu>
        </div>
      </div>
    </el-header>

    <!-- 主要内容区域 -->
    <el-main class="main-content">
      <!-- 欢迎横幅 -->
      <div class="welcome-banner">
        <div class="banner-content">
          <h1>欢迎使用 EduMatrix 教育管理系统</h1>
          <p>为教育机构提供全方位的数字化管理解决方案</p>
          <div class="banner-actions">
            <el-button type="primary" size="large" @click="$router.push('/register')">
              立即注册
            </el-button>
            <el-button size="large" @click="$router.push('/login')">
              立即登录
            </el-button>
          </div>
        </div>
      </div>

      <!-- 功能特色 -->
      <div class="features">
        <h2>系统特色功能</h2>
        <el-row :gutter="30">
          <el-col :span="8">
            <el-card class="feature-card">
              <div class="feature-icon">🏫</div>
              <h3>学校管理</h3>
              <p>完整的学校管理功能，包括学院管理、教师管理、课程管理等</p>
            </el-card>
          </el-col>
          <el-col :span="8">
            <el-card class="feature-card">
              <div class="feature-icon">🤖</div>
              <h3>AI 工具</h3>
              <p>智能AI工具，支持讲稿生成、作业生成、题库生成等教学辅助功能</p>
            </el-card>
          </el-col>
          <el-col :span="8">
            <el-card class="feature-card">
              <div class="feature-icon">📊</div>
              <h3>数据分析</h3>
              <p>全面的数据统计和分析功能，帮助教育机构做出更好的决策</p>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <!-- 用户类型选择 -->
      <div class="user-types">
        <h2>选择您的用户类型</h2>
        <el-row :gutter="30">
          <el-col :span="8">
            <el-card class="user-type-card" @click="selectUserType('teacher')">
              <div class="user-type-icon">👩‍🏫</div>
              <h3>教师登录</h3>
              <p>教师专用登录入口，访问课程管理、AI工具等功能</p>
            </el-card>
          </el-col>
          <el-col :span="8">
            <el-card class="user-type-card" @click="selectUserType('school')">
              <div class="user-type-icon">🏫</div>
              <h3>学校管理员登录</h3>
              <p>学校管理员专用登录入口，管理学校整体事务</p>
            </el-card>
          </el-col>
        </el-row>
      </div>
    </el-main>

    <!-- 页脚 -->
    <el-footer class="footer">
      <p>&copy; 2024 EduMatrix 教育管理系统. 保留所有权利.</p>
    </el-footer>
  </div>
</template>

<script>
export default {
  name: 'Home',
  methods: {
    selectUserType(type) {
      // 根据用户类型跳转到相应的登录页面
      this.$router.push({
        path: '/login',
        query: { type }
      })
    }
  }
}
</script>

<style scoped>
.home {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  backdrop-filter: blur(12px);
  background: rgba(255,255,255,0.8);
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.logo h2 {
  margin: 0;
  color: var(--color-primary);
}

.nav-menu {
  border-bottom: none;
}

.main-content {
  margin-top: 60px;
  padding: 0;
}

.welcome-banner {
  background: linear-gradient(135deg, var(--color-primary) 0%, #764ba2 100%);
  color: white;
  padding: 80px 20px;
  text-align: center;
}

.banner-content h1 {
  font-size: 2.5em;
  margin-bottom: 20px;
}

.banner-content p {
  font-size: 1.2em;
  margin-bottom: 30px;
  opacity: 0.9;
}

.banner-actions {
  display: flex;
  justify-content: center;
  gap: 20px;
}

.features {
  padding: 60px 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.features h2 {
  text-align: center;
  margin-bottom: 40px;
  font-size: 2em;
  color: #333;
}

.feature-card {
  text-align: center;
  padding: 30px 20px;
  height: 200px;
  transition: transform 0.3s;
}

.feature-card:hover {
  transform: translateY(-5px);
}

.feature-icon {
  font-size: 3em;
  margin-bottom: 20px;
}

.feature-card h3 {
  margin-bottom: 15px;
  color: #333;
}

.feature-card p {
  color: #666;
  line-height: 1.6;
}

.user-types {
  padding: 60px 20px;
  background: var(--color-bg-surface);
}

.user-types h2 {
  text-align: center;
  margin-bottom: 40px;
  font-size: 2em;
  color: #333;
}

.user-type-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  text-align: center;
  padding: 40px 20px;
  cursor: pointer;
  transition: all 0.3s;
  min-height: 230px; /* 宽度缩小时依旧自适应 */
}

.user-type-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.user-type-icon {
  font-size: 4em;
  margin-bottom: 20px;
}

.user-type-card h3 {
  margin-bottom: 15px;
  color: #333;
}

.user-type-card p {
  color: #666;
  line-height: 1.6;
}

.footer {
  background: var(--color-primary);
  color: #fff;
  text-align: center;
  padding: 20px;
  margin-top: auto;
}

@media (max-width: 768px) {
  .banner-content h1 {
    font-size: 2em;
  }
  
  .banner-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .feature-card,
  .user-type-card {
    margin-bottom: 20px;
  }
}
</style> 