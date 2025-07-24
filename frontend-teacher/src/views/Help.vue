<template>
  <div class="help">
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
      <!-- 帮助中心标题 -->
      <div class="help-header">
        <h1>📚 帮助中心</h1>
        <p>在这里找到您需要的所有帮助信息</p>
      </div>

      <!-- 搜索框 -->
      <div class="search-section">
        <el-input
          v-model="searchQuery"
          placeholder="搜索帮助内容..."
          prefix-icon="Search"
          size="large"
          clearable
        />
      </div>

      <!-- 帮助内容 -->
      <div class="help-content">
        <el-row :gutter="30">
          <!-- 左侧导航 -->
          <el-col :span="8">
            <el-card class="help-nav">
              <template #header>
                <div class="card-header">
                  <span>📋 帮助目录</span>
                </div>
              </template>
              
              <el-menu
                :default-active="activeSection"
                @select="handleSectionSelect"
                class="help-menu"
              >
                <el-menu-item index="getting-started">
                  <span>快速开始</span>
                </el-menu-item>
                <el-menu-item index="user-guide">
                  <span>用户指南</span>
                </el-menu-item>
                <el-menu-item index="ai-tools">
                  <span>AI 工具使用</span>
                </el-menu-item>
                <el-menu-item index="file-management">
                  <span>文件管理</span>
                </el-menu-item>
                <el-menu-item index="course-management">
                  <span>课程管理</span>
                </el-menu-item>
                <el-menu-item index="faq">
                  <span>常见问题</span>
                </el-menu-item>
                <el-menu-item index="contact">
                  <span>联系我们</span>
                </el-menu-item>
              </el-menu>
            </el-card>
          </el-col>

          <!-- 右侧内容 -->
          <el-col :span="16">
            <el-card class="help-detail">
              <!-- 快速开始 -->
              <div v-if="activeSection === 'getting-started'" class="section-content">
                <h2>🚀 快速开始</h2>
                <el-steps :active="1" direction="vertical" class="steps">
                  <el-step title="注册账号" description="选择您的用户类型并完成注册" />
                  <el-step title="完善信息" description="填写个人或机构信息" />
                  <el-step title="开始使用" description="登录系统开始使用各项功能" />
                </el-steps>
              </div>

              <!-- 用户指南 -->
              <div v-if="activeSection === 'user-guide'" class="section-content">
                <h2>👤 用户指南</h2>
                <el-collapse v-model="activeNames">
                  <el-collapse-item title="教师用户指南" name="teacher">
                    <div class="guide-content">
                      <h4>教师用户功能：</h4>
                      <ul>
                        <li>课程管理：创建和管理您的课程</li>
                        <li>文件管理：上传和管理教学文件</li>
                        <li>AI工具：使用AI辅助教学功能</li>
                        <li>内容库：管理教学资源</li>
                      </ul>
                    </div>
                  </el-collapse-item>
                  <el-collapse-item title="学校管理员指南" name="school">
                    <div class="guide-content">
                      <h4>学校管理员功能：</h4>
                      <ul>
                        <li>学院管理：管理学校下属学院</li>
                        <li>教师管理：管理教师账号和权限</li>
                        <li>课程管理：管理学校课程</li>
                        <li>数据统计：查看使用统计</li>
                      </ul>
                    </div>
                  </el-collapse-item>
                </el-collapse>
              </div>

              <!-- AI工具使用 -->
              <div v-if="activeSection === 'ai-tools'" class="section-content">
                <h2>🤖 AI 工具使用指南</h2>
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-card class="ai-tool-card">
                      <h4>📝 讲稿生成</h4>
                      <p>输入课程主题和要点，AI将为您生成完整的讲稿内容。</p>
                    </el-card>
                  </el-col>
                  <el-col :span="12">
                    <el-card class="ai-tool-card">
                      <h4>📝 作业生成</h4>
                      <p>基于课程内容，AI将生成相应的作业题目和答案。</p>
                    </el-card>
                  </el-col>
                </el-row>
              </div>

              <!-- 文件管理 -->
              <div v-if="activeSection === 'file-management'" class="section-content">
                <h2>📂 文件管理指南</h2>
                <div class="file-guide">
                  <h4>文件空间说明：</h4>
                  <el-alert
                    title="个人空间"
                    description="个人文件存储空间，只有您自己可以访问"
                    type="info"
                    show-icon
                    class="alert-item"
                  />
                  <el-alert
                    title="课程空间"
                    description="课程相关文件存储空间，课程成员可以访问"
                    type="success"
                    show-icon
                    class="alert-item"
                  />
                  <el-alert
                    title="内容库"
                    description="经过审批的教学资源库，全校教师可以访问"
                    type="warning"
                    show-icon
                    class="alert-item"
                  />
                </div>
              </div>

              <!-- 课程管理 -->
              <div v-if="activeSection === 'course-management'" class="section-content">
                <h2>📖 课程管理指南</h2>
                <el-timeline>
                  <el-timeline-item timestamp="第一步" placement="top">
                    <el-card>
                      <h4>创建课程</h4>
                      <p>在课程中心创建新的课程，设置课程基本信息</p>
                    </el-card>
                  </el-timeline-item>
                  <el-timeline-item timestamp="第二步" placement="top">
                    <el-card>
                      <h4>添加成员</h4>
                      <p>邀请其他教师加入课程，设置成员权限</p>
                    </el-card>
                  </el-timeline-item>
                  <el-timeline-item timestamp="第三步" placement="top">
                    <el-card>
                      <h4>上传文件</h4>
                      <p>上传课程相关文件到课程空间</p>
                    </el-card>
                  </el-timeline-item>
                  <el-timeline-item timestamp="第四步" placement="top">
                    <el-card>
                      <h4>使用AI工具</h4>
                      <p>使用AI工具生成教学内容，提高教学效率</p>
                    </el-card>
                  </el-timeline-item>
                </el-timeline>
              </div>

              <!-- 常见问题 -->
              <div v-if="activeSection === 'faq'" class="section-content">
                <h2>❓ 常见问题</h2>
                <el-collapse v-model="activeFAQ">
                  <el-collapse-item title="如何重置密码？" name="reset-password">
                    <p>点击登录页面的"忘记密码"链接，按照提示操作即可重置密码。</p>
                  </el-collapse-item>
                  <el-collapse-item title="如何申请学校账号？" name="school-account">
                    <p>联系平台管理员，提供学校相关证明材料，等待审核通过。</p>
                  </el-collapse-item>
                </el-collapse>
              </div>

              <!-- 联系我们 -->
              <div v-if="activeSection === 'contact'" class="section-content">
                <h2>📞 联系我们</h2>
                <el-row :gutter="20">
                  <el-col :span="12">
                    <el-card class="contact-card">
                      <h4>📧 邮箱支持</h4>
                      <p>support@edumatrix.com</p>
                      <p>工作时间：周一至周五 9:00-18:00</p>
                    </el-card>
                  </el-col>
                  <el-col :span="12">
                    <el-card class="contact-card">
                      <h4>📱 电话支持</h4>
                      <p>400-123-4567</p>
                      <p>工作时间：周一至周五 9:00-18:00</p>
                    </el-card>
                  </el-col>
                </el-row>
              </div>
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
  name: 'Help',
  data() {
    return {
      searchQuery: '',
      activeSection: 'getting-started',
      activeNames: ['teacher'],
      activeFAQ: ['reset-password']
    }
  },
  methods: {
    handleSectionSelect(key) {
      this.activeSection = key
    }
  }
}
</script>

<style scoped>
.help {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
  color: #409EFF;
}

.nav-menu {
  border-bottom: none;
}

.main-content {
  margin-top: 60px;
  padding: 20px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.help-header {
  text-align: center;
  margin-bottom: 40px;
}

.help-header h1 {
  font-size: 2.5em;
  color: #333;
  margin-bottom: 10px;
}

.help-header p {
  font-size: 1.2em;
  color: #666;
}

.search-section {
  margin-bottom: 30px;
}

.help-nav {
  position: sticky;
  top: 80px;
}

.help-menu {
  border-right: none;
}

.section-content h2 {
  color: #333;
  margin-bottom: 30px;
  font-size: 1.8em;
}

.steps {
  margin-top: 20px;
}

.guide-content h4 {
  color: #333;
  margin-bottom: 15px;
}

.guide-content ul {
  padding-left: 20px;
}

.guide-content li {
  margin-bottom: 8px;
  color: #666;
}

.ai-tool-card {
  margin-bottom: 20px;
  text-align: center;
}

.ai-tool-card h4 {
  color: #409EFF;
  margin-bottom: 10px;
}

.file-guide .alert-item {
  margin-bottom: 15px;
}

.contact-card {
  text-align: center;
  margin-bottom: 20px;
}

.contact-card h4 {
  color: #409EFF;
  margin-bottom: 10px;
}

.contact-card p {
  color: #666;
  margin-bottom: 5px;
}

.footer {
  background: #333;
  color: white;
  text-align: center;
  padding: 20px;
  margin-top: auto;
}

@media (max-width: 768px) {
  .help-nav {
    position: static;
    margin-bottom: 20px;
  }
  
  .help-content .el-col {
    width: 100%;
  }
}
</style> 