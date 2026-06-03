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
  }

  return {
    translations,
  }
})
