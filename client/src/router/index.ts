import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '@/views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminDashboardPage from '@/views/admin/AdminDashboardPage.vue'
import Cookies from 'js-cookie'
import { useSeoMeta } from '@/composables/useSeoMeta'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: {
        title: 'The Fibonacci Gallery | Elite Trading Academy',
        description:
          'Master institutional trading, The Big Five strategy, and risk management across Forex, Gold, and Crypto. Built for global traders.',
        keywords: [
          'forex trading academy',
          'fibonacci trading',
          'the big five strategy',
          'gold analysis xauusd',
          'risk management trading',
          'institutional order flow',
          'dubai trading academy',
        ],
      },
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: {
        title: 'Sign In',
        noIndex: true,
      },
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: {
        title: 'Create Account',
        noIndex: true,
      },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('@/views/ForgotPasswordView.vue'),
      meta: {
        title: 'Reset Password',
        noIndex: true,
      },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('@/views/ResetPasswordView.vue'),
      meta: {
        title: 'Set New Password',
        noIndex: true,
      },
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
      meta: {
        title: 'Student Dashboard',
        noIndex: true,
      },
    },
    {
      path: '/my-courses',
      name: 'my-courses',
      component: () => import('@/views/MyCoursesPage.vue'),
      meta: {
        title: 'My Courses',
        noIndex: true,
      },
    },
    {
      path: '/explore',
      name: 'explore-courses',
      component: () => import('@/views/ExploreCoursesPage.vue'),
      meta: {
        title: 'Explore Masterclasses',
        description:
          'Browse institutional trading masterclasses. Master The Big Five strategy, Fibonacci confluence, and capital preservation.',
        keywords: [
          'trading courses',
          'forex masterclass',
          'technical analysis course',
          'institutional trading syllabus',
          'crypto trading course',
        ],
      },
    },
    {
      path: '/markets',
      name: 'markets',
      component: () => import('@/views/MarketsView.vue'),
      meta: {
        title: 'Live Market Analytics',
        description:
          'Track real-time quotes, charts, and volatility metrics across Forex, Indices, Crypto, and Commodities via TradingView.',
        keywords: [
          'live market analytics',
          'forex currency rates',
          'gold price live',
          'tradingview charts',
          'crypto market scanner',
        ],
      },
    },
    {
      path: '/testimonials',
      name: 'testimonials',
      component: () => import('@/views/TestimonialsView.vue'),
      meta: {
        title: 'Student Testimonials',
        description:
          'Verified reviews and transformation stories from traders worldwide who mastered The Big Five strategy with our academy.',
        keywords: [
          'trading academy reviews',
          'student testimonials',
          'fibonacci gallery results',
          'verified trader reviews',
        ],
      },
    },
    {
      path: '/faq',
      name: 'faq',
      component: () => import('@/views/FaqView.vue'),
      meta: {
        title: 'Frequently Asked Questions',
        description:
          'Get answers on course access, trading prerequisites, mobile learning, community access, and billing policies.',
        keywords: [
          'trading academy faq',
          'course prerequisites',
          'lifetime access trading course',
          'mobile trading lessons',
        ],
      },
    },
    {
      path: '/privacy',
      name: 'privacy',
      component: () => import('@/views/PrivacyView.vue'),
      meta: {
        title: 'Privacy Policy',
        description:
          'Learn how The Fibonacci Gallery collects, protects, and manages your personal data and account privacy.',
      },
    },
    {
      path: '/terms',
      name: 'terms',
      component: () => import('@/views/TermsView.vue'),
      meta: {
        title: 'Terms of Service',
        description:
          'Terms and conditions governing access, enrollment, and community participation at The Fibonacci Gallery.',
      },
    },
    {
      path: '/disclaimer',
      name: 'disclaimer',
      component: () => import('@/views/DisclaimerView.vue'),
      meta: {
        title: 'Financial Disclaimer',
        description:
          'Educational disclosures and financial risk notices for trading Forex, Commodities, Crypto, and Equities.',
      },
    },
    {
      path: '/courses/:uuid/details',
      name: 'course-details',
      component: () => import('@/views/CourseDetailsPage.vue'),
      meta: {
        title: 'Masterclass Curriculum',
        description:
          'Explore the complete syllabus, chapter video breakdowns, and lifetime enrollment options for this masterclass.',
      },
    },
    {
      path: '/checkout/:courseUuid',
      name: 'checkout',
      component: () => import('@/views/CheckoutPage.vue'),
      meta: { requiresAuth: true, noIndex: true },
    },
    {
      path: '/payment/success',
      name: 'payment-success',
      component: () => import('@/views/PaymentSuccessPage.vue'),
      meta: { requiresAuth: true, noIndex: true },
    },
    {
      path: '/payment/cancel',
      name: 'payment-cancel',
      component: () => import('@/views/PaymentCancelPage.vue'),
      meta: { requiresAuth: true, noIndex: true },
    },
    {
      path: '/courses/:courseUuid/player',
      name: 'course-player',
      component: () => import('@/views/CoursePlayerPage.vue'),
      meta: { requiresAuth: true, noIndex: true },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/ProfilePage.vue'),
      meta: { noIndex: true },
    },
    {
      path: '/affiliate',
      name: 'affiliate',
      component: () => import('@/views/AffiliatePage.vue'),
      meta: { requiresAuth: true, noIndex: true },
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
          meta: { noIndex: true },
        },
        {
          path: 'courses',
          name: 'admin-courses',
          component: () => import('@/views/admin/AdminCoursesPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'courses/create',
          name: 'admin-courses-create',
          component: () => import('@/views/admin/AdminCourseFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'courses/:uuid/edit',
          name: 'admin-courses-edit',
          component: () => import('@/views/admin/AdminCourseFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'chapters',
          name: 'admin-chapters',
          component: () => import('@/views/admin/AdminChaptersPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'chapters/create',
          name: 'admin-chapters-create',
          component: () => import('@/views/admin/AdminChapterFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'chapters/:uuid/edit',
          name: 'admin-chapters-edit',
          component: () => import('@/views/admin/AdminChapterFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'videos',
          name: 'admin-videos',
          component: () => import('@/views/admin/AdminVideosPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'videos/create',
          name: 'admin-videos-create',
          component: () => import('@/views/admin/AdminVideoFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'videos/:uuid/edit',
          name: 'admin-videos-edit',
          component: () => import('@/views/admin/AdminVideoFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'students',
          name: 'admin-students',
          component: () => import('@/views/admin/AdminStudentsPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'enrollments',
          name: 'admin-enrollments',
          component: () => import('@/views/admin/AdminEnrollmentsPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'orders',
          name: 'admin-orders',
          component: () => import('@/views/admin/AdminOrdersPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'payouts',
          name: 'admin-payouts',
          component: () => import('@/views/admin/AdminPayoutsPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'coupons',
          name: 'admin-coupons',
          component: () => import('@/views/admin/AdminCouponsPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'coupons/create',
          name: 'admin-coupons-create',
          component: () => import('@/views/admin/AdminCouponFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'coupons/:uuid/edit',
          name: 'admin-coupons-edit',
          component: () => import('@/views/admin/AdminCouponFormPage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'coupons/:uuid/usage',
          name: 'admin-coupons-usage',
          component: () => import('@/views/admin/AdminCouponUsagePage.vue'),
          meta: { noIndex: true },
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: () => import('@/views/admin/AdminSettingsPage.vue'),
          meta: { noIndex: true, title: 'Settings' },
        },
        {
          path: 'profile',
          redirect: '/admin/settings',
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/pages/NotFoundPage.vue'),
      meta: {
        title: 'Page Not Found',
        noIndex: true,
      },
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

// Dynamic SEO navigation hook
const { setSeoMeta, generateOrganizationSchema } = useSeoMeta()

router.afterEach((to) => {
  setSeoMeta({
    title: to.meta.title,
    description: to.meta.description,
    keywords: to.meta.keywords,
    noIndex: to.meta.noIndex,
    canonicalUrl: to.path,
    jsonLd: to.path === '/' ? generateOrganizationSchema() : undefined,
  })
})

export default router
