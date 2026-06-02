import { computed } from 'vue'
import { z } from 'zod'
import type { PasswordFormState, PasswordSchemaMessages } from '@/types/ProfileTypes'

const buildPasswordSchema = (messages: PasswordSchemaMessages) =>
  z
    .object({
      current_password: z.string().min(1, messages.currentPasswordRequired),
      new_password: z.string().min(1, messages.newPasswordRequired).min(8, messages.newPasswordMin),
      new_password_confirmation: z.string().min(1, messages.confirmPasswordRequired),
    })
    .refine((data) => data.new_password === data.new_password_confirmation, {
      message: messages.passwordsMismatch,
      path: ['new_password_confirmation'],
    })

export const usePasswordSchema = (messages: PasswordSchemaMessages) =>
  computed(() => buildPasswordSchema(messages) as z.ZodType<PasswordFormState>)

export type { PasswordFormState, PasswordFormField } from '@/types/ProfileTypes'
