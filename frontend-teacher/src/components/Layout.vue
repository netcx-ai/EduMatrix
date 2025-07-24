<template>
  <div class="layout-container">
    <!-- 侧边栏 -->
    <div class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo">
          <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <span v-if="!sidebarCollapsed" class="logo-text">EduMatrix</span>
        </div>
        <el-button 
          link
          @click="toggleSidebar" 
          class="collapse-btn"
          :icon="sidebarCollapsed ? 'Expand' : 'Fold'"
        />
      </div>
      
      <div class="sidebar-menu">
        <el-menu
          :default-active="activeMenu"
          :collapse="sidebarCollapsed"
          :unique-opened="true"
          router
          class="menu"
        >
          <el-sub-menu 
            v-for="menu in subMenus" 
            :key="menu.path"
            :index="menu.path"
          >
            <template #title>
              <el-icon><component :is="menu.icon" /></el-icon>
              <span>{{ menu.title }}</span>
            </template>
            <el-menu-item 
              v-for="child in menu.children" 
              :key="child.path" 
              :index="child.path"
            >
              <el-icon><component :is="child.icon" /></el-icon>
              <span>{{ child.title }}</span>
            </el-menu-item>
          </el-sub-menu>
          <el-menu-item 
            v-for="menu in singleMenus" 
            :key="menu.path"
            :index="menu.path"
          >
            <el-icon><component :is="menu.icon" /></el-icon>
            <span>{{ menu.title }}</span>
          </el-menu-item>
        </el-menu>
      </div>
    </div>
    
    <!-- 主内容区域 -->
    <div class="main-content" :class="{ expanded: sidebarCollapsed }">
      <!-- 顶部导航栏 -->
      <div class="top-navbar">
        <div class="navbar-left">
          <el-breadcrumb separator="/">
            <el-breadcrumb-item v-for="item in breadcrumbs" :key="item.path" :to="item.path">
              {{ item.title }}
            </el-breadcrumb-item>
          </el-breadcrumb>
        </div>
        
        <div class="navbar-right">
          <div class="user-info">
            <el-dropdown @command="handleUserCommand">
              <div class="user-avatar">
                <el-avatar :size="32" :src="userInfo.avatar">
                  {{ userInfo.name ? userInfo.name.charAt(0) : 'U' }}
                </el-avatar>
                <span class="user-name">{{ userInfo.name || userInfo.phone }}</span>
                <el-icon><ArrowDown /></el-icon>
              </div>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="profile">
                    <el-icon><User /></el-icon>
                    个人设置
                  </el-dropdown-item>
                  <el-dropdown-item command="password">
                    <el-icon><Lock /></el-icon>
                    修改密码
                  </el-dropdown-item>
                  <el-dropdown-item divided command="logout">
                    <el-icon><SwitchButton /></el-icon>
                    退出登录
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </div>
      
      <!-- 页面内容 -->
      <div class="page-content">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  Expand, 
  Fold, 
  ArrowDown, 
  User, 
  Lock, 
  SwitchButton,
  House,
  Document,
  Folder,
  MagicStick,
  Setting,
  School,
  UserFilled,
  DataAnalysis,
  Tools,
  Monitor,
  Bell,
  VideoPlay
} from '@element-plus/icons-vue'
import { userApi } from '@/api/user'

const router = useRouter()
const route = useRoute()

// 响应式数据
const sidebarCollapsed = ref(false)
const userInfo = ref({})

// 计算属性
const activeMenu = computed(() => route.path)

const breadcrumbs = computed(() => {
  const matched = route.matched.filter(item => item.meta && item.meta.title)
  return matched.map(item => ({
    title: item.meta.title,
    path: item.path
  }))
})

// 菜单配置
const menuConfig = {
  teacher: [
    {
      title: '控制台',
      path: '/teacher',
      icon: 'House'
    },
    {
      title: '课程管理',
      path: '/teacher/courses',
          icon: 'Document'
    },
    {
      title: '文件管理',
      path: '/teacher/files',
      icon: 'Folder',
      children: [
        {
          title: '文件列表',
          path: '/teacher/files',
          icon: 'Folder'
        },
        {
          title: '上传文件',
          path: '/teacher/files/upload',
          icon: 'Folder'
        }
      ]
    },
    {
      title: '内容中心',
      path: '/teacher/content',
      icon: 'Document',
      children: [
        {
          title: '内容库',
          path: '/teacher/content',
          icon: 'Document'
        },
        {
          title: 'AI工具',
          path: '/teacher/ai-tools',
          icon: 'MagicStick'
        },
        {
          title: 'AI使用历史',
          path: '/teacher/ai-history',
          icon: 'DataAnalysis'
        }
      ]
    },
    {
      title: '业务流程演示',
      path: '/teacher/demo',
      icon: 'VideoPlay'
    },

    {
      title: '个人设置',
      path: '/teacher/profile',
      icon: 'Setting'
    }
  ],
  school: [
    {
      title: '控制台',
      path: '/school',
      icon: 'House'
    },
    {
      title: '学院管理',
      path: '/school/colleges',
      icon: 'School'
    },
    {
      title: '教师管理',
      path: '/school/teachers',
      icon: 'UserFilled',
      children: [
        {
          title: '教师列表',
          path: '/school/teachers',
          icon: 'UserFilled'
        },
        {
          title: '待审核教师',
          path: '/school/teachers/pending',
          icon: 'UserFilled'
        }
      ]
    },
    {
      title: '课程管理',
      path: '/school/courses',
      icon: 'Document'
    },
    {
      title: '使用统计',
      path: '/school/statistics',
      icon: 'DataAnalysis'
    },
    {
      title: '学校设置',
      path: '/school/settings',
      icon: 'Setting'
    }
  ]
}

const menuList = computed(() => {
  const userType = localStorage.getItem('userType') || 'teacher'
  return menuConfig[userType] || []
})

const subMenus = computed(() => {
  return menuList.value.filter(menu => menu.children && menu.children.length > 0)
})

const singleMenus = computed(() => {
  return menuList.value.filter(menu => !menu.children || menu.children.length === 0)
})

// 方法
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const handleUserCommand = async (command) => {
  switch (command) {
    case 'profile':
      const userType = localStorage.getItem('userType')
      router.push(`/${userType}/profile`)
      break
    case 'password':
      const userType2 = localStorage.getItem('userType')
      router.push(`/${userType2}/password`)
      break
    case 'logout':
      await handleLogout()
      break
  }
}

const handleLogout = async () => {
  try {
    await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    // 调用退出登录API
    await userApi.logout()
    
    // 清除本地存储
    localStorage.removeItem('token')
    localStorage.removeItem('userInfo')
    localStorage.removeItem('userType')
    
    ElMessage.success('退出登录成功')
    
    // 跳转到登录页
    router.push('/login')
  } catch (error) {
    if (error !== 'cancel') {
      console.error('退出登录失败：', error)
      ElMessage.error('退出登录失败')
    }
  }
}

// 生命周期
onMounted(() => {
  // 获取用户信息
  const userInfoStr = localStorage.getItem('userInfo')
  if (userInfoStr) {
    try {
      userInfo.value = JSON.parse(userInfoStr)
    } catch (error) {
      console.error('解析用户信息失败：', error)
    }
  }
})
</script>

<style scoped>
.layout-container {
  display: flex;
  height: 100vh;
  background: #f5f7fa;
}

/* 侧边栏样式 */
.sidebar {
  width: 240px;
  background: #fff;
  border-right: 1px solid #e4e7ed;
  transition: width 0.3s ease;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

.sidebar.collapsed {
  width: 64px;
}

.sidebar-header {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  border-bottom: 1px solid #e4e7ed;
  background: #fff;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon svg {
  width: 20px;
  height: 20px;
  color: white;
}

.logo-text {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.collapse-btn {
  color: #909399;
}

.sidebar-menu {
  flex: 1;
  overflow-y: auto;
}

.menu {
  border: none;
}

.menu :deep(.el-menu-item) {
  height: 50px;
  line-height: 50px;
}

.menu :deep(.el-sub-menu__title) {
  height: 50px;
  line-height: 50px;
}

/* 主内容区域 */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
}

.main-content.expanded {
  margin-left: 0;
}

/* 顶部导航栏 */
.top-navbar {
  height: 60px;
  background: #fff;
  border-bottom: 1px solid #e4e7ed;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-left {
  display: flex;
  align-items: center;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-info {
  display: flex;
  align-items: center;
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.3s ease;
}

.user-avatar:hover {
  background: #f5f7fa;
}

.user-name {
  font-size: 14px;
  color: #303133;
  font-weight: 500;
}

/* 页面内容 */
.page-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar.show {
    transform: translateX(0);
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .page-content {
    padding: 16px;
  }
}
</style> 