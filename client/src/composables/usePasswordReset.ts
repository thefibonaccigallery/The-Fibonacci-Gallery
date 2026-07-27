import { ref } from 'vue'
import { useMutation } from '@tanstack/vue-query'
import { useForm } from '@tanstack/vue-form'
import { toast } from 'vue-sonner'
import axiosInstance from '@/api/axiosInstance'
import {
  forgotPasswordSchema,
  resetPasswordSchema,
  type ForgotPasswordFormField,
  type ForgotPasswordFormState,
  type ResetPasswordFormField,
  type ResetPasswordFormState,
} from '@/schemas/passwordResetSchemas'
import {
  validateFormWithZod,
  normalizeErrors,
  type NormalizedErrors,
} from '@/utils/errorFormatter'
import type {
  ForgotPasswordPayload,
  ForgotPasswordResponse,
  ResetPasswordPayload,
  ResetPasswordResponse,
  VerifyTokenResponse,
} from '@/types/passwordResetTypes'

export function usePasswordReset() {
  const forgotPasswordErrors = ref<NormalizedErrors<ForgotPasswordFormField>>({})
  const resetPasswordErrors = ref<NormalizedErrors<ResetPasswordFormField>>({})
  const forgotMailSent = ref(false)
  const resetCompleted = ref(false)

  // TanStack Form for Forgot Password
  const forgotForm = useForm({
    defaultValues: {
      email: '',
    } as ForgotPasswordFormState,
  })

  // TanStack Form for Reset Password
  const resetForm = useForm({
    defaultValues: {
      password: '',
      password_confirmation: '',
    } as ResetPasswordFormState,
  })

  function clearForgotPasswordError(field: ForgotPasswordFormField) {
    if (forgotPasswordErrors.value && forgotPasswordErrors.value[field]) {
      delete forgotPasswordErrors.value[field]
    }
  }

  function clearResetPasswordError(field: ResetPasswordFormField) {
    if (resetPasswordErrors.value && resetPasswordErrors.value[field]) {
      delete resetPasswordErrors.value[field]
    }
  }

  // Mutation: Request Password Reset (Forgot Password)
  const forgotMutation = useMutation({
    mutationFn: async (payload: ForgotPasswordPayload) => {
      const response = await axiosInstance.post<ForgotPasswordResponse>('/forgot-password', payload)
      return response.data
    },
    onSuccess: (data) => {
      forgotMailSent.value = true
      toast.success(data.message || 'Reset link sent successfully')
    },
    onError: (error: any) => {
      if (error?.response?.data?.errors) {
        forgotPasswordErrors.value = normalizeErrors<ForgotPasswordFormField>(
          error.response.data.errors,
        )
      } else {
        toast.error(error?.response?.data?.message || 'Something went wrong. Please try again.')
      }
    },
  })

  // Mutation: Verify Token
  const verifyTokenMutation = useMutation({
    mutationFn: async (token: string) => {
      const response = await axiosInstance.get<VerifyTokenResponse>(
        `/verify-reset-token?token=${encodeURIComponent(token)}`,
      )
      return response.data
    },
  })

  // Mutation: Submit New Password (Reset Password)
  const resetMutation = useMutation({
    mutationFn: async (payload: ResetPasswordPayload) => {
      const response = await axiosInstance.post<ResetPasswordResponse>('/reset-password', payload)
      return response.data
    },
    onSuccess: (data) => {
      resetCompleted.value = true
      toast.success(data.message || 'Password reset successfully')
    },
    onError: (error: any) => {
      if (error?.response?.data?.errors) {
        resetPasswordErrors.value = normalizeErrors<ResetPasswordFormField>(
          error.response.data.errors,
        )
      } else {
        toast.error(
          error?.response?.data?.message || 'Failed to reset password. Link may be invalid or expired.',
        )
      }
    },
  })

  // Submit handler for Forgot Password
  function handleForgotPasswordSubmit() {
    forgotPasswordErrors.value = {}
    const error = validateFormWithZod<ForgotPasswordFormField>(
      forgotPasswordSchema,
      forgotForm.state.values,
    )
    if (error) {
      forgotPasswordErrors.value = error
      return
    }
    forgotMutation.mutate(forgotForm.state.values as ForgotPasswordPayload)
  }

  // Submit handler for Reset Password
  function handleResetPasswordSubmit(token: string) {
    resetPasswordErrors.value = {}
    const error = validateFormWithZod<ResetPasswordFormField>(
      resetPasswordSchema,
      resetForm.state.values,
    )
    if (error) {
      resetPasswordErrors.value = error
      return
    }

    const payload: ResetPasswordPayload = {
      token,
      password: resetForm.state.values.password,
      password_confirmation: resetForm.state.values.password_confirmation,
    }

    resetMutation.mutate(payload)
  }

  return {
    forgotForm,
    resetForm,
    forgotPasswordErrors,
    resetPasswordErrors,
    forgotMailSent,
    resetCompleted,
    isSubmittingForgot: forgotMutation.isPending,
    isSubmittingReset: resetMutation.isPending,
    isVerifyingToken: verifyTokenMutation.isPending,
    verifyTokenError: verifyTokenMutation.error,
    verifyToken: verifyTokenMutation.mutateAsync,
    clearForgotPasswordError,
    clearResetPasswordError,
    handleForgotPasswordSubmit,
    handleResetPasswordSubmit,
  }
}
