<template>
  <div class="layout-container">
    <!-- 移动端遮罩 -->
    <div 
      v-if="isMobile && sidebarVisible" 
      class="mobile-overlay"
      @click="toggleSidebar"
    ></div>
    
    <!-- 侧边栏 -->
    <div 
      class="sidebar"
      :class="{ 'sidebar-mobile': isMobile, 'sidebar-visible': sidebarVisible }"
    >
      <div class="logo">
        <h2>{{ userStore.userInfo.school_name || '学校' }}管理系统</h2>
      </div>
      
      <el-menu
        :default-active="activeMenu"
        class="sidebar-menu"
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409EFF"
        router
      >
        <el-menu-item index="/dashboard">
          <el-icon><Monitor /></el-icon>
          <span>控制台</span>
        </el-menu-item>
        
        <el-sub-menu index="colleges">
          <template #title>
            <el-icon><School /></el-icon>
            <span>学院管理</span>
          </template>
          <el-menu-item index="/colleges">学院列表</el-menu-item>
        </el-sub-menu>
        
        <el-sub-menu index="teachers">
          <template #title>
            <el-icon><User /></el-icon>
            <span>教师管理</span>
          </template>
          <el-menu-item index="/teachers">教师列表</el-menu-item>
          <el-menu-item index="/teachers/audit">教师审核</el-menu-item>
        </el-sub-menu>
        
        <el-menu-item index="/courses">
          <el-icon><Reading /></el-icon>
          <span>课程管理</span>
        </el-menu-item>
        
        <el-menu-item index="/statistics">
          <el-icon><DataAnalysis /></el-icon>
          <span>使用统计</span>
        </el-menu-item>
        
        <el-menu-item index="/settings">
          <el-icon><Setting /></el-icon>
          <span>学校设置</span>
        </el-menu-item>
      </el-menu>
    </div>

    <!-- 主内容区 -->
    <div class="main-container">
      <!-- 顶部导航 -->
      <div class="header">
        <div class="header-left">
          <!-- 学校名称显示在顶部导航左侧 -->
          <span class="school-name" style="font-weight:bold;font-size:18px;margin-right:24px;">
            欢迎登录{{ userStore.userInfo.school_name || '学校' }}管理系统
          </span>
          <!-- 移动端菜单按钮 -->
          <el-button
            v-if="isMobile"
            class="menu-toggle"
            @click="toggleSidebar"
            :icon="Menu"
            circle
            size="small"
          />
          
          <el-breadcrumb separator="/" class="breadcrumb">
            <el-breadcrumb-item :to="{ path: '/dashboard' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item v-if="currentRoute.meta.title">
              {{ currentRoute.meta.title }}
            </el-breadcrumb-item>
          </el-breadcrumb>
        </div>
        
        <div class="header-right">
          <el-dropdown @command="handleCommand">
            <span class="user-info">
              <el-avatar :size="32" :src="userStore.userInfo.avatar">
                {{ userStore.userInfo.name?.charAt(0) }}
              </el-avatar>
              <span class="username" v-if="!isMobile">{{ userStore.userInfo.name }}</span>
              <el-icon v-if="!isMobile"><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">个人设置</el-dropdown-item>
                <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>

      <!-- 内容区域 -->
      <div class="main-content">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessageBox } from 'element-plus'
import { Menu } from '@element-plus/icons-vue'

export default {
  name: 'MainLayout',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const userStore = useUserStore()

    // 移动端状态
    const isMobile = ref(false)
    const sidebarVisible = ref(false)

    // 检测屏幕尺寸
    const checkScreenSize = () => {
      isMobile.value = window.innerWidth <= 768
      if (!isMobile.value) {
        sidebarVisible.value = false
      }
    }

    // 切换侧边栏
    const toggleSidebar = () => {
      sidebarVisible.value = !sidebarVisible.value
    }

    // 当前激活的菜单
    const activeMenu = computed(() => {
      const path = route.path
      if (path === '/teachers') return '/teachers'
      if (path === '/teachers/audit') return '/teachers/audit'
      if (path.startsWith('/colleges')) return '/colleges'
      return path
    })

    // 当前路由
    const currentRoute = computed(() => route)

    // 处理下拉菜单命令
    const handleCommand = async (command) => {
      if (command === 'profile') {
        router.push('/profile')
      } else if (command === 'logout') {
        try {
          await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          })
          userStore.logout()
          router.push('/login')
        } catch {
          // 用户取消
        }
      }
    }

    // 监听窗口大小变化
    onMounted(() => {
      checkScreenSize()
      window.addEventListener('resize', checkScreenSize)
    })

    onUnmounted(() => {
      window.removeEventListener('resize', checkScreenSize)
    })

    return {
      userStore,
      activeMenu,
      currentRoute,
      handleCommand,
      isMobile,
      sidebarVisible,
      toggleSidebar,
      Menu
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.layout-container {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
}

.sidebar {
  width: 250px;
  background-color: #304156;
  color: #bfcbd9;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
}

.logo {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid #1f2d3d;
}

.logo h2 {
  color: #fff;
  margin: 0;
  font-size: 18px;
}

.sidebar-menu {
  border: none;
  flex: 1;
}

.main-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  width: calc(100vw - 250px);
  min-width: 0;
}

.header {
  height: 60px;
  background-color: #fff;
  border-bottom: 1px solid #e6e6e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  flex-shrink: 0;
}

.header-left {
  flex: 1;
}

.header-right {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.user-info:hover {
  background-color: #f5f5f5;
}

.username {
  margin: 0 8px;
  color: #333;
}

.main-content {
  flex: 1;
  background-color: #f0f2f5;
  overflow-y: auto;
  width: 100%;
}

/* 移动端适配 */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

.sidebar-mobile {
  position: fixed;
  top: 0;
  left: -280px;
  height: 100vh;
  z-index: 1000;
  transition: left 0.3s ease;
  width: 280px;
}

.sidebar-mobile.sidebar-visible {
  left: 0;
}

.menu-toggle {
  margin-right: 12px;
}

.breadcrumb {
  display: none;
}

/* 移动端样式 */
@media (max-width: 768px) {
  .layout-container {
    flex-direction: column;
  }
  
  .sidebar {
    display: none;
  }
  
  .sidebar-mobile {
    display: flex;
  }
  
  .main-container {
    width: 100vw;
  }
  
  .header {
    padding: 0 16px;
    height: 60px;
  }
  
  .header-left {
    display: flex;
    align-items: center;
  }
  
  .breadcrumb {
    display: block;
  }
  
  .logo h2 {
    font-size: 16px;
  }
  
  .user-info {
    padding: 6px 8px;
  }
  
  .username {
    display: none;
  }
}

/* 平板适配 */
@media (min-width: 769px) and (max-width: 1024px) {
  .sidebar {
    width: 220px;
  }
  
  .main-container {
    width: calc(100vw - 220px);
  }
}
</style> 