import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminDashboardPage from '@/views/admin/AdminDashboardPage.vue'
import Cookies from 'js-cookie'

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
      redirect: '/dashboard',
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardPage.vue'),
    },
    {
      path: '/my-courses',
      name: 'my-courses',
      component: () => import('@/views/MyCoursesPage.vue'),
    },
    {
      path: '/explore',
      name: 'explore-courses',
      component: () => import('@/views/ExploreCoursesPage.vue'),
    },
    {
      path: '/features',
      name: 'features',
      component: () => import('@/views/FeaturesView.vue'),
    },
    {
      path: '/markets',
      name: 'markets',
      component: () => import('@/views/MarketsView.vue'),
    },
    {
      path: '/testimonials',
      name: 'testimonials',
      component: () => import('@/views/TestimonialsView.vue'),
    },
    {
      path: '/faq',
      name: 'faq',
      component: () => import('@/views/FaqView.vue'),
    },
    {
      path: '/privacy',
      name: 'privacy',
      component: () => import('@/views/PrivacyView.vue'),
    },
    {
      path: '/terms',
      name: 'terms',
      component: () => import('@/views/TermsView.vue'),
    },
    {
      path: '/disclaimer',
      name: 'disclaimer',
      component: () => import('@/views/DisclaimerView.vue'),
    },
    {
      path: '/courses/:uuid/details',
      name: 'course-details',
      component: () => import('@/views/CourseDetailsPage.vue'),
    },
    {
      path: '/checkout/:courseUuid',
      name: 'checkout',
      component: () => import('@/views/CheckoutPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/payment/success',
      name: 'payment-success',
      component: () => import('@/views/PaymentSuccessPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/payment/cancel',
      name: 'payment-cancel',
      component: () => import('@/views/PaymentCancelPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/courses/:courseUuid/player',
      name: 'course-player',
      component: () => import('@/views/CoursePlayerPage.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/ProfilePage.vue'),
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
          path: 'courses/create',
          name: 'admin-courses-create',
          component: () => import('@/views/admin/AdminCourseFormPage.vue'),
        },
        {
          path: 'courses/:uuid/edit',
          name: 'admin-courses-edit',
          component: () => import('@/views/admin/AdminCourseFormPage.vue'),
        },
        {
          path: 'chapters',
          name: 'admin-chapters',
          component: () => import('@/views/admin/AdminChaptersPage.vue'),
        },
        {
          path: 'chapters/create',
          name: 'admin-chapters-create',
          component: () => import('@/views/admin/AdminChapterFormPage.vue'),
        },
        {
          path: 'chapters/:uuid/edit',
          name: 'admin-chapters-edit',
          component: () => import('@/views/admin/AdminChapterFormPage.vue'),
        },
        {
          path: 'videos',
          name: 'admin-videos',
          component: () => import('@/views/admin/AdminVideosPage.vue'),
        },
        {
          path: 'videos/create',
          name: 'admin-videos-create',
          component: () => import('@/views/admin/AdminVideoFormPage.vue'),
        },
        {
          path: 'videos/:uuid/edit',
          name: 'admin-videos-edit',
          component: () => import('@/views/admin/AdminVideoFormPage.vue'),
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
          path: 'coupons',
          name: 'admin-coupons',
          component: () => import('@/views/admin/AdminCouponsPage.vue'),
        },
        {
          path: 'coupons/create',
          name: 'admin-coupons-create',
          component: () => import('@/views/admin/AdminCouponFormPage.vue'),
        },
        {
          path: 'coupons/:uuid/edit',
          name: 'admin-coupons-edit',
          component: () => import('@/views/admin/AdminCouponFormPage.vue'),
        },
        {
          path: 'coupons/:uuid/usage',
          name: 'admin-coupons-usage',
          component: () => import('@/views/admin/AdminCouponUsagePage.vue'),
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

router.beforeEach((to, _from) => {
  const token = Cookies.get('access_token')
  const userStr = localStorage.getItem('auth')

  if (to.path === '/' && token && userStr) {
    try {
      const user = JSON.parse(userStr)
      return user.role === 'admin' ? '/admin/dashboard' : '/dashboard'
    } catch (e) {
      // Ignore parse error and proceed
    }
  }
})

export default router
