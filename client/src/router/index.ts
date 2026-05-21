import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import UserHomeView from '@/views/UserHomeView.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminDashboardPage from '@/views/admin/AdminDashboardPage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/user-home',
      name: 'user-home',
      component: UserHomeView,
    },
    {
      path: '/admin-home',
      redirect: '/admin/dashboard',
    },
    {
      path: '/admin',
      component: AdminLayout,
      children: [
        {
          path: '',
          redirect: '/admin/dashboard',
        },
        {
          path: 'dashboard',
          name: 'admin-dashboard',
          component: AdminDashboardPage,
        },
        {
          path: 'courses',
          name: 'admin-courses',
          component: () => import('@/views/admin/AdminCoursesPage.vue'),
        },
        {
          path: 'chapters',
          name: 'admin-chapters',
          component: () => import('@/views/admin/AdminChaptersPage.vue'),
        },
        {
          path: 'videos',
          name: 'admin-videos',
          component: () => import('@/views/admin/AdminVideosPage.vue'),
        },
        {
          path: 'students',
          name: 'admin-students',
          component: () => import('@/views/admin/AdminStudentsPage.vue'),
        },
        {
          path: 'enrollments',
          name: 'admin-enrollments',
          component: () => import('@/views/admin/AdminEnrollmentsPage.vue'),
        },
        {
          path: 'orders',
          name: 'admin-orders',
          component: () => import('@/views/admin/AdminOrdersPage.vue'),
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: () => import('@/views/admin/AdminSettingsPage.vue'),
        },
      ],
    },
  ],
})

export default router
