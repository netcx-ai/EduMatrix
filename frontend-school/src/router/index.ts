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
      name: 'login',
      component: () => import('@/views/Login.vue'),
      meta: { requiresAuth: false }
    },
    {
      path: '/',
      component: () => import('@/layouts/AuthLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/dashboard'
        },
        {
          path: 'dashboard',
          name: 'dashboard',
          component: () => import('@/views/Dashboard.vue'),
          meta: { title: '控制台' }
        },
        {
          path: 'colleges',
          name: 'colleges',
          component: () => import('@/views/colleges/CollegeList.vue'),
          meta: { title: '学院管理' }
        },
        {
          path: 'colleges/:id',
          name: 'college-detail',
          component: () => import('@/views/colleges/CollegeDetail.vue'),
          meta: { title: '学院详情' }
        },
        {
          path: 'teachers',
          name: 'teachers',
          component: () => import('@/views/teachers/TeacherList.vue'),
          meta: { title: '教师管理' }
        },
        {
          path: 'teacher-audit',
          name: 'teacher-audit',
          component: () => import('@/views/teachers/TeacherAudit.vue'),
          meta: { title: '教师审核' }
        }
      ]
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFound.vue')
    }
  ]
})

// 路由守卫
router.beforeEach((to, from, next) => {
  const userStore = useUserStore()
  
  if (to.meta.requiresAuth && !userStore.isLoggedIn) {
    next('/login')
  } else if (to.path === '/login' && userStore.isLoggedIn) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
