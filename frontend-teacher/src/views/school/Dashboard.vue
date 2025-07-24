<template>
  <div class="school-dashboard">
    <!-- 顶部导航栏 -->
    <el-header class="header">
      <div class="header-content">
        <div class="logo">
          <h2>🏫 学校管理控制台</h2>
        </div>
        <div class="user-info">
          <el-dropdown @command="handleCommand">
            <span class="user-dropdown">
              <el-avatar :size="32" :src="userInfo.avatar">{{ userInfo.name?.charAt(0) }}</el-avatar>
              <span class="username">{{ userInfo.name }}</span>
              <el-icon><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">个人设置</el-dropdown-item>
                <el-dropdown-item command="security">安全设置</el-dropdown-item>
                <el-dropdown-item divided command="logout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>
    </el-header>

    <!-- 主要内容区域 -->
    <div class="main-container">
      <!-- 左侧导航菜单 -->
      <el-aside class="sidebar" width="250px">
        <el-menu
          :default-active="activeMenu"
          class="sidebar-menu"
          @select="handleMenuSelect"
        >
          <el-menu-item index="dashboard">
            <el-icon><DataBoard /></el-icon>
            <span>控制台</span>
          </el-menu-item>
          <el-menu-item index="colleges">
            <el-icon><OfficeBuilding /></el-icon>
            <span>学院管理</span>
          </el-menu-item>
          <el-menu-item index="teachers">
            <el-icon><User /></el-icon>
            <span>教师管理</span>
          </el-menu-item>
          <el-menu-item index="courses">
            <el-icon><Reading /></el-icon>
            <span>课程管理</span>
          </el-menu-item>
          <el-menu-item index="approval">
            <el-icon><Check /></el-icon>
            <span>教师审核</span>
          </el-menu-item>
          <el-menu-item index="statistics">
            <el-icon><DataAnalysis /></el-icon>
            <span>使用统计</span>
          </el-menu-item>
          <el-menu-item index="settings">
            <el-icon><Setting /></el-icon>
            <span>学校设置</span>
          </el-menu-item>
          <el-menu-item index="admin">
            <el-icon><Monitor /></el-icon>
            <span>管理后台</span>
          </el-menu-item>
        </el-menu>
      </el-aside>

      <!-- 右侧内容区域 -->
      <el-main class="main-content">
        <!-- 控制台概览 -->
        <div v-if="activeMenu === 'dashboard'" class="dashboard-overview">
          <h2>欢迎回来，{{ userInfo.name }}！</h2>
          
          <!-- 统计卡片 -->
          <el-row :gutter="20" class="stats-cards">
            <el-col :span="6">
              <el-card class="stat-card">
                <div class="stat-content">
                  <div class="stat-icon">🏢</div>
                  <div class="stat-info">
                    <div class="stat-number">{{ stats.collegeCount }}</div>
                    <div class="stat-label">学院数量</div>
                  </div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="6">
              <el-card class="stat-card">
                <div class="stat-content">
                  <div class="stat-icon">👨‍🏫</div>
                  <div class="stat-info">
                    <div class="stat-number">{{ stats.teacherCount }}</div>
                    <div class="stat-label">教师数量</div>
                  </div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="6">
              <el-card class="stat-card">
                <div class="stat-content">
                  <div class="stat-icon">📚</div>
                  <div class="stat-info">
                    <div class="stat-number">{{ stats.courseCount }}</div>
                    <div class="stat-label">课程数量</div>
                  </div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="6">
              <el-card class="stat-card">
                <div class="stat-content">
                  <div class="stat-icon">🤖</div>
                  <div class="stat-info">
                    <div class="stat-number">{{ stats.aiUsage }}</div>
                    <div class="stat-label">AI使用次数</div>
                  </div>
                </div>
              </el-card>
            </el-col>
          </el-row>

          <!-- 最近活动 -->
          <el-row :gutter="20" class="recent-activities">
            <el-col :span="12">
              <el-card class="activity-card">
                <template #header>
                  <div class="card-header">
                    <span>👨‍🏫 待审核教师</span>
                  </div>
                </template>
                <div class="activity-list">
                  <div v-for="teacher in pendingTeachers" :key="teacher.id" class="activity-item">
                    <div class="activity-icon">👤</div>
                    <div class="activity-content">
                      <div class="activity-title">{{ teacher.name }}</div>
                      <div class="activity-time">{{ teacher.college }}</div>
                    </div>
                    <el-button size="small" type="primary" @click="reviewTeacher(teacher)">审核</el-button>
                  </div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="12">
              <el-card class="activity-card">
                <template #header>
                  <div class="card-header">
                    <span>📊 活跃度统计</span>
                  </div>
                </template>
                <div class="activity-list">
                  <div v-for="stat in activityStats" :key="stat.id" class="activity-item">
                    <div class="activity-icon">{{ stat.icon }}</div>
                    <div class="activity-content">
                      <div class="activity-title">{{ stat.title }}</div>
                      <div class="activity-time">{{ stat.value }}</div>
                    </div>
                  </div>
                </div>
              </el-card>
            </el-col>
          </el-row>
        </div>

        <!-- 学院管理 -->
        <div v-if="activeMenu === 'colleges'" class="colleges-section">
          <div class="section-header">
            <h2>🏢 学院管理</h2>
            <el-button type="primary" @click="createCollege">
              <el-icon><Plus /></el-icon>
              添加学院
            </el-button>
          </div>
          
          <el-table :data="colleges" style="width: 100%">
            <el-table-column prop="name" label="学院名称" />
            <el-table-column prop="code" label="学院代码" />
            <el-table-column prop="teacherCount" label="教师数量" />
            <el-table-column prop="courseCount" label="课程数量" />
            <el-table-column prop="status" label="状态">
              <template #default="scope">
                <el-tag :type="scope.row.status === 'active' ? 'success' : 'info'">
                  {{ scope.row.status === 'active' ? '正常' : '停用' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="250">
              <template #default="scope">
                <el-button size="small" @click="viewCollege(scope.row)">查看</el-button>
                <el-button size="small" type="primary" @click="editCollege(scope.row)">编辑</el-button>
                <el-button size="small" type="warning" @click="manageTeachers(scope.row)">教师管理</el-button>
                <el-button size="small" type="danger" @click="deleteCollege(scope.row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <!-- 教师管理 -->
        <div v-if="activeMenu === 'teachers'" class="teachers-section">
          <div class="section-header">
            <h2>👨‍🏫 教师管理</h2>
            <el-button type="primary" @click="addTeacher">
              <el-icon><Plus /></el-icon>
              添加教师
            </el-button>
          </div>
          
          <el-table :data="teachers" style="width: 100%">
            <el-table-column prop="name" label="姓名" />
            <el-table-column prop="email" label="邮箱" />
            <el-table-column prop="college" label="所属学院" />
            <el-table-column prop="role" label="角色" />
            <el-table-column prop="status" label="状态">
              <template #default="scope">
                <el-tag :type="scope.row.status === 'active' ? 'success' : 'danger'">
                  {{ scope.row.status === 'active' ? '正常' : '停用' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="lastLogin" label="最后登录" />
            <el-table-column label="操作" width="200">
              <template #default="scope">
                <el-button size="small" @click="viewTeacher(scope.row)">查看</el-button>
                <el-button size="small" type="primary" @click="editTeacher(scope.row)">编辑</el-button>
                <el-button size="small" type="danger" @click="deleteTeacher(scope.row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <!-- 课程管理 -->
        <div v-if="activeMenu === 'courses'" class="courses-section">
          <div class="section-header">
            <h2>📚 课程管理</h2>
            <el-button type="primary" @click="createCourse">
              <el-icon><Plus /></el-icon>
              创建课程
            </el-button>
          </div>
          
          <el-table :data="courses" style="width: 100%">
            <el-table-column prop="name" label="课程名称" />
            <el-table-column prop="code" label="课程代码" />
            <el-table-column prop="college" label="所属学院" />
            <el-table-column prop="teacher" label="负责人" />
            <el-table-column prop="status" label="状态">
              <template #default="scope">
                <el-tag :type="scope.row.status === 'active' ? 'success' : 'info'">
                  {{ scope.row.status === 'active' ? '进行中' : '已结束' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="200">
              <template #default="scope">
                <el-button size="small" @click="viewCourse(scope.row)">查看</el-button>
                <el-button size="small" type="primary" @click="editCourse(scope.row)">编辑</el-button>
                <el-button size="small" type="danger" @click="deleteCourse(scope.row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>

        <!-- 教师审核 -->
        <div v-if="activeMenu === 'approval'" class="approval-section">
          <div class="section-header">
            <h2>✅ 教师审核</h2>
          </div>
          
          <el-tabs v-model="activeApprovalTab">
            <el-tab-pane label="待审核" name="pending">
              <el-table :data="pendingApprovals" style="width: 100%">
                <el-table-column prop="name" label="姓名" />
                <el-table-column prop="email" label="邮箱" />
                <el-table-column prop="college" label="申请学院" />
                <el-table-column prop="applyTime" label="申请时间" />
                <el-table-column label="操作" width="200">
                  <template #default="scope">
                    <el-button size="small" type="success" @click="approveTeacher(scope.row)">通过</el-button>
                    <el-button size="small" type="danger" @click="rejectTeacher(scope.row)">驳回</el-button>
                  </template>
                </el-table-column>
              </el-table>
            </el-tab-pane>
            <el-tab-pane label="已审核" name="reviewed">
              <el-table :data="reviewedApprovals" style="width: 100%">
                <el-table-column prop="name" label="姓名" />
                <el-table-column prop="email" label="邮箱" />
                <el-table-column prop="college" label="申请学院" />
                <el-table-column prop="status" label="审核结果">
                  <template #default="scope">
                    <el-tag :type="scope.row.status === 'approved' ? 'success' : 'danger'">
                      {{ scope.row.status === 'approved' ? '通过' : '驳回' }}
                    </el-tag>
                  </template>
                </el-table-column>
                <el-table-column prop="reviewTime" label="审核时间" />
              </el-table>
            </el-tab-pane>
          </el-tabs>
        </div>

        <!-- 使用统计 -->
        <div v-if="activeMenu === 'statistics'" class="statistics-section">
          <h2>📊 使用统计</h2>
          
          <el-row :gutter="20">
            <el-col :span="12">
              <el-card class="chart-card">
                <template #header>
                  <div class="card-header">
                    <span>👨‍🏫 教师活跃度</span>
                  </div>
                </template>
                <div class="chart-placeholder">
                  <p>教师活跃度统计图表</p>
                </div>
              </el-card>
            </el-col>
            <el-col :span="12">
              <el-card class="chart-card">
                <template #header>
                  <div class="card-header">
                    <span>🏢 学院统计</span>
                  </div>
                </template>
                <div class="chart-placeholder">
                  <p>学院使用情况统计图表</p>
                </div>
              </el-card>
            </el-col>
            <el-col :span="12">
              <el-card class="chart-card">
                <template #header>
                  <div class="card-header">
                    <span>🤖 AI 使用统计</span>
                  </div>
                </template>
                <div class="chart-placeholder">
                  <p>AI工具使用情况统计图表</p>
                </div>
              </el-card>
            </el-col>
            <el-col :span="12">
              <el-card class="chart-card">
                <template #header>
                  <div class="card-header">
                    <span>📊 课程发布统计</span>
                  </div>
                </template>
                <div class="chart-placeholder">
                  <p>课程发布数量统计图表</p>
                </div>
              </el-card>
            </el-col>
          </el-row>
        </div>

        <!-- 学校设置 -->
        <div v-if="activeMenu === 'settings'" class="settings-section">
          <h2>⚙️ 学校设置</h2>
          <el-tabs v-model="activeSettingTab">
            <el-tab-pane label="基本信息" name="basic">
              <el-form :model="schoolInfo" label-width="120px">
                <el-form-item label="学校名称">
                  <el-input v-model="schoolInfo.name" />
                </el-form-item>
                <el-form-item label="学校代码">
                  <el-input v-model="schoolInfo.code" />
                </el-form-item>
                <el-form-item label="联系人">
                  <el-input v-model="schoolInfo.contact" />
                </el-form-item>
                <el-form-item label="联系电话">
                  <el-input v-model="schoolInfo.phone" />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="saveSchoolInfo">保存</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="公告管理" name="announcement">
              <div class="announcement-section">
                <el-button type="primary" @click="createAnnouncement">发布公告</el-button>
                <el-table :data="announcements" style="width: 100%; margin-top: 20px;">
                  <el-table-column prop="title" label="标题" />
                  <el-table-column prop="content" label="内容" />
                  <el-table-column prop="publishTime" label="发布时间" />
                  <el-table-column label="操作" width="150">
                    <template #default="scope">
                      <el-button size="small" @click="editAnnouncement(scope.row)">编辑</el-button>
                      <el-button size="small" type="danger" @click="deleteAnnouncement(scope.row)">删除</el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </el-tab-pane>
          </el-tabs>
        </div>

        <!-- 管理后台入口 -->
        <div v-if="activeMenu === 'admin'" class="admin-section">
          <div class="section-header">
            <h2>🔧 管理后台</h2>
            <p class="section-description">访问完整的系统管理后台，进行高级配置和管理</p>
          </div>
          
          <el-card class="admin-card">
            <div class="admin-content">
              <div class="admin-info">
                <h3>系统管理后台</h3>
                <p>提供完整的系统管理功能，包括：</p>
                <ul>
                  <li>用户权限管理</li>
                  <li>系统配置管理</li>
                  <li>AI工具配置</li>
                  <li>日志审计</li>
                  <li>数据统计</li>
                </ul>
              </div>
              <div class="admin-actions">
                <el-button type="primary" size="large" @click="openAdminPanel">
                  <el-icon><Monitor /></el-icon>
                  打开管理后台
                </el-button>
                <el-button size="large" @click="showAdminHelp">
                  <el-icon><QuestionFilled /></el-icon>
                  使用说明
                </el-button>
              </div>
            </div>
          </el-card>
        </div>
      </el-main>
    </div>
  </div>
</template>

<script>
import { ArrowDown, DataBoard, OfficeBuilding, User, Reading, Check, DataAnalysis, Setting, Plus, Monitor, QuestionFilled } from '@element-plus/icons-vue'

export default {
  name: 'SchoolDashboard',
  components: {
    ArrowDown,
    DataBoard,
    OfficeBuilding,
    User,
    Reading,
    Check,
    DataAnalysis,
    Setting,
    Plus,
    Monitor,
    QuestionFilled
  },
  data() {
    return {
      activeMenu: 'dashboard',
      activeApprovalTab: 'pending',
      activeSettingTab: 'basic',
      userInfo: {
        name: '李校长',
        avatar: ''
      },
      stats: {
        collegeCount: 8,
        teacherCount: 156,
        courseCount: 342,
        aiUsage: 1250
      },
      pendingTeachers: [
        { id: 1, name: '张老师', college: '数学学院' },
        { id: 2, name: '王老师', college: '物理学院' },
        { id: 3, name: '李老师', college: '化学学院' }
      ],
      activityStats: [
        { id: 1, title: '今日活跃教师', value: '45人', icon: '👨‍🏫' },
        { id: 2, title: '今日AI使用', value: '89次', icon: '🤖' },
        { id: 3, title: '今日文件上传', value: '156个', icon: '📁' }
      ],
      colleges: [
        { id: 1, name: '数学学院', code: 'MATH', teacherCount: 25, courseCount: 45, status: 'active' },
        { id: 2, name: '物理学院', code: 'PHYS', teacherCount: 20, courseCount: 38, status: 'active' },
        { id: 3, name: '化学学院', code: 'CHEM', teacherCount: 18, courseCount: 32, status: 'active' }
      ],
      teachers: [
        { id: 1, name: '张老师', email: 'zhang@example.com', college: '数学学院', role: '教师', status: 'active', lastLogin: '2024-01-15' },
        { id: 2, name: '王老师', email: 'wang@example.com', college: '物理学院', role: '教师', status: 'active', lastLogin: '2024-01-14' },
        { id: 3, name: '李老师', email: 'li@example.com', college: '化学学院', role: '教师', status: 'active', lastLogin: '2024-01-13' }
      ],
      courses: [
        { id: 1, name: '高等数学', code: 'MATH101', college: '数学学院', teacher: '张老师', status: 'active' },
        { id: 2, name: '线性代数', code: 'MATH102', college: '数学学院', teacher: '王老师', status: 'active' },
        { id: 3, name: '概率论', code: 'MATH103', college: '数学学院', teacher: '李老师', status: 'active' }
      ],
      pendingApprovals: [
        { id: 1, name: '赵老师', email: 'zhao@example.com', college: '数学学院', applyTime: '2024-01-15' },
        { id: 2, name: '钱老师', email: 'qian@example.com', college: '物理学院', applyTime: '2024-01-14' }
      ],
      reviewedApprovals: [
        { id: 1, name: '孙老师', email: 'sun@example.com', college: '化学学院', status: 'approved', reviewTime: '2024-01-13' },
        { id: 2, name: '周老师', email: 'zhou@example.com', college: '数学学院', status: 'rejected', reviewTime: '2024-01-12' }
      ],
      schoolInfo: {
        name: '示例大学',
        code: 'EXAMPLE',
        contact: '李校长',
        phone: '010-12345678'
      },
      announcements: [
        { id: 1, title: '系统维护通知', content: '系统将于今晚进行维护', publishTime: '2024-01-15' },
        { id: 2, title: '新功能上线', content: 'AI工具功能已上线', publishTime: '2024-01-14' }
      ]
    }
  },
  methods: {
    handleCommand(command) {
      switch (command) {
        case 'profile':
          this.activeMenu = 'settings'
          this.activeSettingTab = 'basic'
          break
        case 'security':
          this.activeMenu = 'settings'
          this.activeSettingTab = 'security'
          break
        case 'logout':
          this.logout()
          break
      }
    },
    handleMenuSelect(key) {
      this.activeMenu = key
      // 如果是其他菜单项，跳转到对应页面
      if (key !== 'dashboard') {
        this.$router.push(`/school/${key}`)
      }
    },
    reviewTeacher(teacher) {
      this.$message.info(`审核教师：${teacher.name}`)
    },
    createCollege() {
      this.$message.info('创建学院功能')
    },
    viewCollege(college) {
      this.$message.info(`查看学院：${college.name}`)
    },
    editCollege(college) {
      this.$message.info(`编辑学院：${college.name}`)
    },
    manageTeachers(college) {
      this.$message.info(`管理学院教师：${college.name}`)
    },
    deleteCollege(college) {
      this.$message.info(`删除学院：${college.name}`)
    },
    addTeacher() {
      this.$message.info('添加教师功能')
    },
    viewTeacher(teacher) {
      this.$message.info(`查看教师：${teacher.name}`)
    },
    editTeacher(teacher) {
      this.$message.info(`编辑教师：${teacher.name}`)
    },
    deleteTeacher(teacher) {
      this.$message.info(`删除教师：${teacher.name}`)
    },
    createCourse() {
      this.$message.info('创建课程功能')
    },
    viewCourse(course) {
      this.$message.info(`查看课程：${course.name}`)
    },
    editCourse(course) {
      this.$message.info(`编辑课程：${course.name}`)
    },
    deleteCourse(course) {
      this.$message.info(`删除课程：${course.name}`)
    },
    approveTeacher(teacher) {
      this.$message.success(`审核通过：${teacher.name}`)
    },
    rejectTeacher(teacher) {
      this.$message.warning(`审核驳回：${teacher.name}`)
    },
    saveSchoolInfo() {
      this.$message.success('保存成功')
    },
    createAnnouncement() {
      this.$message.info('发布公告功能')
    },
    editAnnouncement(announcement) {
      this.$message.info(`编辑公告：${announcement.title}`)
    },
    deleteAnnouncement(announcement) {
      this.$message.info(`删除公告：${announcement.title}`)
    },
    logout() {
      localStorage.removeItem('token')
      this.$router.push('/login')
    },
    openAdminPanel() {
      this.$message.info('打开管理后台功能')
    },
    showAdminHelp() {
      this.$message.info('显示管理后台使用说明')
    }
  }
}
</script>

<style scoped>
.school-dashboard {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  z-index: 1000;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
}

.logo h2 {
  margin: 0;
  color: #409EFF;
}

.user-dropdown {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.username {
  margin: 0 8px;
  color: #333;
}

.main-container {
  flex: 1;
  display: flex;
}

.sidebar {
  background: #fff;
  border-right: 1px solid #e6e6e6;
}

.sidebar-menu {
  border-right: none;
}

.main-content {
  padding: 20px;
  background: #f5f7fa;
}

.dashboard-overview h2 {
  margin-bottom: 30px;
  color: #333;
}

.stats-cards {
  margin-bottom: 30px;
}

.stat-card {
  text-align: center;
}

.stat-content {
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon {
  font-size: 2em;
  margin-right: 15px;
}

.stat-number {
  font-size: 2em;
  font-weight: bold;
  color: #409EFF;
}

.stat-label {
  color: #666;
  font-size: 0.9em;
}

.recent-activities {
  margin-bottom: 30px;
}

.activity-card {
  height: 300px;
}

.activity-list {
  max-height: 200px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}

.activity-icon {
  font-size: 1.5em;
  margin-right: 10px;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 500;
  color: #333;
}

.activity-time {
  font-size: 0.9em;
  color: #666;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  margin: 0;
  color: #333;
}

.chart-card {
  margin-bottom: 20px;
}

.chart-placeholder {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9f9f9;
  border-radius: 4px;
}

.chart-placeholder p {
  color: #999;
  font-size: 1.1em;
}

.announcement-section {
  margin-top: 20px;
}

.admin-section {
  margin-top: 20px;
}

.section-description {
  color: #666;
  font-size: 0.9em;
}

.admin-card {
  background: #fff;
  border-radius: 4px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.admin-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.admin-info {
  flex: 1;
}

.admin-info h3 {
  margin-bottom: 10px;
  color: #333;
}

.admin-info p {
  margin-bottom: 10px;
  color: #666;
}

.admin-info ul {
  margin-left: 20px;
  color: #666;
}

.admin-actions {
  text-align: right;
}

.admin-actions .el-button {
  margin-left: 10px;
}
</style> 