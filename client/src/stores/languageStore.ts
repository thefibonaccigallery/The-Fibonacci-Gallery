import { defineStore } from 'pinia'

export const useLanguageStore = defineStore('language', () => {
  const translations = {
    profile: {
      title: 'My Profile',
      subtitle: 'Manage your account details',
      sections: {
        personalInfo: 'Personal Info',
        updateProfile: 'Update Profile',
        updatePassword: 'Update Password',
      },
      fields: {
        name: 'Full Name',
        email: 'Email',
        country: 'Country',
        role: 'Role',
        currentPassword: 'Current Password',
        newPassword: 'New Password',
        confirmNewPassword: 'Confirm New Password',
      },
      placeholders: {
        name: 'Enter your name',
        country: 'Select your country',
        currentPassword: 'Enter current password',
        newPassword: 'Enter new password',
        confirmNewPassword: 'Confirm new password',
      },
      actions: {
        saveProfile: 'Save Changes',
        savePassword: 'Update Password',
      },
      helper: {
        emailReadOnly: 'Email cannot be changed',
      },
      states: {
        loading: 'Loading profile...',
        empty: 'Profile not available',
      },
      toasts: {
        profileUpdated: 'Profile updated successfully',
        profileUpdateFailed: 'Failed to update profile',
        passwordUpdated: 'Password updated successfully',
        passwordUpdateFailed: 'Failed to update password',
        profileLoadFailed: 'Failed to load profile',
      },
      validation: {
        profile: {
          nameRequired: 'Name is required',
          countryRequired: 'Country is required',
        },
        password: {
          currentPasswordRequired: 'Current password is required',
          newPasswordRequired: 'New password is required',
          newPasswordMin: 'Password must be at least 8 characters',
          confirmPasswordRequired: 'Confirm your new password',
          passwordsMismatch: 'Passwords do not match',
        },
      },
    },
    notFound: {
      heading: 'Looks like this trade went wrong!',
      subtext: 'The asset or pattern you are looking for has been liquidated, moved, or never existed in this timeline.',
      backHome: 'Back to Home',
      nav: {
        home: 'Home',
        courses: 'Courses',
        about: 'About',
        contact: 'Contact',
        menu: 'Menu',
      },
    },
    common: {
      notAvailable: 'Not available',
    },
    enrollment: {
      success: 'Successfully enrolled.',
      go_to_course: 'Go to Course',
      enroll_free: 'Enroll Now',
      buy_now: 'Buy Now',
      already_enrolled: 'You are already enrolled in this course.',
    },
    checkout: {
      title: 'Checkout Summary',
      courseTitle: 'Course Title',
      courseLevel: 'Course Level',
      price: 'Price',
      completePayment: 'Complete Payment',
      comingSoon: 'Coming Soon',
      comingSoonNote: 'Payment integration is coming soon.',
      backToCourse: 'Back to Course details',
    },
    adminOrders: {
      title: 'Orders Management',
      search: 'Search by user or course...',
      filters: {
        all: 'All',
        waiting: 'Waiting',
        confirming: 'Confirming',
        finished: 'Finished',
        failed: 'Failed',
        expired: 'Expired',
      },
      columns: {
        user: 'User',
        course: 'Course',
        amount: 'Amount',
        currency: 'Currency',
        status: 'Status',
        date: 'Date',
        actions: 'Actions',
      },
      dialog: {
        title: 'Update Order Status',
        description: 'Manually override the payment status for this order.',
        statusLabel: 'New Status',
        confirm: 'Update Status',
        cancel: 'Cancel',
      },
      toasts: {
        updated: 'Order status updated successfully',
        updateFailed: 'Failed to update order status',
      },
      states: {
        loading: 'Loading orders...',
        empty: 'No orders found',
      },
    },
    adminStudents: {
      title: 'Students',
      search: 'Search by name or email...',
      columns: {
        name: 'Name',
        email: 'Email',
        country: 'Country',
        courses: 'Courses',
        joined: 'Joined',
      },
      states: {
        loading: 'Loading students...',
        empty: 'No students found',
        noCountry: 'N/A',
      },
    },
    adminEnrollments: {
      title: 'Enrollments',
      search: 'Search by student or course...',
      filters: {
        all: 'All',
        active: 'In Progress',
        completed: 'Completed',
      },
      columns: {
        student: 'Student',
        course: 'Course',
        progress: 'Progress',
        enrolledAt: 'Enrolled',
        status: 'Status',
        actions: 'Actions',
      },
      status: {
        completed: 'Completed',
        inProgress: 'In Progress',
      },
      dialog: {
        removeTitle: 'Remove Enrollment',
        removeDescription: 'This will revoke the student\'s access to the course. This action cannot be undone.',
        confirm: 'Remove',
        cancel: 'Cancel',
      },
      toasts: {
        enrolled: 'Student enrolled successfully',
        enrollFailed: 'Failed to enroll student',
        removed: 'Enrollment removed successfully',
        removeFailed: 'Failed to remove enrollment',
      },
      states: {
        loading: 'Loading enrollments...',
        empty: 'No enrollments found',
      },
    },
  }

  return {
    translations,
  }
})
