import { z } from 'zod'
import { computed } from 'vue'

// ── Register Schema ──
const registerSchemaRaw = z
  .object({
    name: z.string().min(1, 'Name is required').max(255),
    email: z.email('Invalid email address'),
    password: z.string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: z.string().min(1, 'Password confirmation is required'),
    country: z.string().min(1, 'Country is required').max(255),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Passwords do not match',
    path: ['password_confirmation'],
  })

export const useRegisterSchema = computed(() => registerSchemaRaw)
export type RegisterFormState = z.infer<typeof registerSchemaRaw>
export type RegisterFormField = keyof Omit<RegisterFormState, never>

// ── Login Schema ──
const loginSchemaRaw = z.object({
  email: z.string().min(1, 'Email is required').email('Invalid email address'),
  password: z.string().min(1, 'Password is required'),
})

export const useLoginSchema = computed(() => loginSchemaRaw)
export type LoginFormState = z.infer<typeof loginSchemaRaw>
export type LoginFormField = keyof LoginFormState
