import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('@/views/Home.vue')
  },
  {
    path: '/help',
    name: 'Help',
    component: () => import('@/views/Help.vue')
  },
  {
    path: '/pricing',
    name: 'Pricing',
    component: () => import('@/views/Pricing.vue')
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/Register.vue')
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue')
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/views/ResetPassword.vue')
  },
  // 教师端路由
  {
    path: '/teacher',
    name: 'TeacherDashboard',
    component: () => import('@/views/teacher/Dashboard.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '教师控制台' }
  },
  {
    path: '/teacher/courses',
    name: 'TeacherCourses',
    component: () => import('@/views/teacher/Courses.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '课程管理' }
  },
  {
    path: '/teacher/course/:id',
    name: 'TeacherCourseDetail',
    component: () => import('@/views/teacher/CourseDetail.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '课程详情' }
  },
  {
    path: '/teacher/files',
    name: 'TeacherFiles',
    component: () => import('@/views/teacher/Files.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '文件管理' }
  },
  {
    path: '/teacher/files/upload',
    name: 'TeacherUploadFile',
    component: () => import('@/views/teacher/UploadFile.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '上传文件' }
  },
  {
    path: '/teacher/ai-tools',
    name: 'TeacherAiTools',
    component: () => import('@/views/teacher/AiTools.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: 'AI工具' }
  },
  {
    path: '/teacher/ai-history',
    name: 'TeacherAiHistory',
    component: () => import('@/views/teacher/AiHistory.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: 'AI使用历史' }
  },
  {
    path: '/teacher/content-library',
    name: 'TeacherContentLibrary',
    component: () => import('@/views/teacher/ContentLibrary.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '内容库' }
  },
  {
    path: '/teacher/profile',
    name: 'TeacherProfile',
    component: () => import('@/views/teacher/Profile.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '个人设置' }
  },
  {
    path: '/teacher/password',
    name: 'TeacherPassword',
    component: () => import('@/views/teacher/Password.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '修改密码' }
  },
  {
    path: '/teacher/content',
    name: 'TeacherContentCenter',
    component: () => import('@/views/teacher/ContentCenter.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '内容中心' }
  },
  {
    path: '/teacher/content/edit/:id',
    name: 'TeacherContentEdit',
    component: () => import('@/views/teacher/ContentEdit.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '编辑内容' }
  },
  {
    path: '/teacher/content/create',
    name: 'TeacherContentCreate',
    component: () => import('@/views/teacher/ContentEdit.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '新建内容' }
  },
  {
    path: '/teacher/demo',
    name: 'TeacherDemo',
    component: () => import('@/views/teacher/Demo.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '业务流程演示' }
  },
  {
    path: '/test-components',
    name: 'TestComponents',
    component: () => import('@/views/test-components.vue'),
    meta: { title: '组件测试' }
  },
  {
    path: '/preview/content/:id',
    name: 'ContentPreview',
    component: () => import('@/views/teacher/ContentPreview.vue'),
    meta: { requiresAuth: true, role: 'teacher', title: '内容预览' }
  },
  // 学校管理员路由
  {
    path: '/school',
    name: 'SchoolDashboard',
    component: () => import('@/views/school/Dashboard.vue'),
    meta: { requiresAuth: true, role: 'school', title: '学校管理控制台' }
  },
  {
    path: '/school/colleges',
    name: 'SchoolColleges',
    component: () => import('@/views/school/Colleges.vue'),
    meta: { requiresAuth: true, role: 'school', title: '学院管理' }
  },
  {
    path: '/school/teachers',
    name: 'SchoolTeachers',
    component: () => import('@/views/school/Teachers.vue'),
    meta: { requiresAuth: true, role: 'school', title: '教师管理' }
  },
  {
    path: '/school/teachers/pending',
    name: 'SchoolPendingTeachers',
    component: () => import('@/views/school/PendingTeachers.vue'),
    meta: { requiresAuth: true, role: 'school', title: '待审核教师' }
  },
  {
    path: '/school/courses',
    name: 'SchoolCourses',
    component: () => import('@/views/school/Courses.vue'),
    meta: { requiresAuth: true, role: 'school', title: '课程管理' }
  },
  {
    path: '/school/statistics',
    name: 'SchoolStatistics',
    component: () => import('@/views/school/Statistics.vue'),
    meta: { requiresAuth: true, role: 'school', title: '使用统计' }
  },
  {
    path: '/school/settings',
    name: 'SchoolSettings',
    component: () => import('@/views/school/Settings.vue'),
    meta: { requiresAuth: true, role: 'school', title: '学校设置' }
  },
  {
    path: '/school/profile',
    name: 'SchoolProfile',
    component: () => import('@/views/school/Profile.vue'),
    meta: { requiresAuth: true, role: 'school', title: '个人设置' }
  },
  {
    path: '/school/password',
    name: 'SchoolPassword',
    component: () => import('@/views/school/Password.vue'),
    meta: { requiresAuth: true, role: 'school', title: '修改密码' }
  },
  // 平台管理员路由已移除
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守卫
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const userType = localStorage.getItem('userType')
  
  // 如果访问登录、注册或重置密码页面，直接放行
  if (to.path === '/login' || to.path === '/register' || to.path === '/reset-password') {
    next()
    return
  }
  
  // 如果访问首页、帮助中心、定价方案，直接放行
  if (to.path === '/' || to.path === '/help' || to.path === '/pricing') {
    next()
    return
  }
  
  // 如果需要认证但没有 token，重定向到登录页
  if (to.meta.requiresAuth && !token) {
    next('/login')
    return
  }
  
  // 检查用户角色权限
  if (to.meta.requiresAuth && to.meta.role && userType !== to.meta.role) {
    // 用户角色不匹配，重定向到对应的控制台
    const dashboardPath = `/${userType}`
    next(dashboardPath)
    return
  }
  
  next()
})

export default router 