import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/dashboard'
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/Login.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: 'dashboard',
          name: 'Dashboard',
          component: () => import('@/views/Dashboard.vue'),
          meta: { title: '控制台' }
        },
        {
          path: 'colleges',
          name: 'Colleges',
          component: () => import('@/views/colleges/CollegeList.vue'),
          meta: { title: '学院管理' }
        },
        {
          path: 'colleges/:id',
          name: 'CollegeDetail',
          component: () => import('@/views/colleges/CollegeDetail.vue'),
          meta: { title: '学院详情' }
        },
        {
          path: 'teachers',
          name: 'Teachers',
          component: () => import('@/views/teachers/TeacherList.vue'),
          meta: { title: '教师管理' }
        },
        {
          path: 'teachers/:id',
          name: 'TeacherDetail',
          component: () => import('@/views/teachers/TeacherDetail.vue'),
          meta: { title: '教师详情' }
        },
        {
          path: 'teachers/audit',
          name: 'TeacherAudit',
          component: () => import('@/views/teachers/TeacherAudit.vue'),
          meta: { title: '教师审核' }
        },
        {
          path: 'courses',
          name: 'Courses',
          component: () => import('@/views/courses/CourseList.vue'),
          meta: { title: '课程管理' }
        },
        {
          path: 'courses/detail/:id',
          name: 'CourseDetail',
          component: () => import('@/views/courses/CourseDetail.vue'),
          meta: { title: '课程详情' }
        },
        {
          path: 'statistics',
          name: 'Statistics',
          component: () => import('@/views/statistics/Statistics.vue'),
          meta: { title: '使用统计' }
        },
        {
          path: 'settings',
          name: 'Settings',
          component: () => import('@/views/settings/Settings.vue'),
          meta: { title: '学校设置' }
        },
        {
          path: 'profile',
          name: 'Profile',
          component: () => import('@/views/Profile.vue'),
          meta: { title: '个人设置' }
        }
      ]
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: () => import('@/views/NotFound.vue')
    }
  ]
})

// 路由守卫
router.beforeEach((to, from, next) => {
  const userStore = useUserStore()
  
  // 设置页面标题
  if (to.meta.title) {
    document.title = `${to.meta.title} - 学校管理系统`
  }
  
  // 检查是否需要登录
  if (to.meta.requiresAuth !== false) {
    if (!userStore.isLoggedIn) {
      next('/login')
      return
    }
  }
  
  // 如果已登录且访问登录页，跳转到首页
  if (to.path === '/login' && userStore.isLoggedIn) {
    next('/dashboard')
    return
  }
  
  next()
})

export default router 