import { z } from 'zod'

export const forgotPasswordSchema = z.object({
  email: z.string().min(1, 'Email is required').email('Invalid email address'),
})

export type ForgotPasswordFormState = z.infer<typeof forgotPasswordSchema>
export type ForgotPasswordFormField = keyof ForgotPasswordFormState

export const resetPasswordSchema = z
  .object({
    password: z
      .string()
      .min(1, 'Password is required')
      .min(8, 'Password must be at least 8 characters'),
    password_confirmation: z.string().min(1, 'Please confirm your password'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

export type ResetPasswordFormState = z.infer<typeof resetPasswordSchema>
export type ResetPasswordFormField = keyof ResetPasswordFormState
