import type { User } from '@/types/UserType'

export interface ProfileFormState {
  name: string
  country: string
}

export type ProfileFormField = keyof ProfileFormState

export interface PasswordFormState {
  current_password: string
  new_password: string
  new_password_confirmation: string
}

export type PasswordFormField = keyof PasswordFormState

export interface ProfileResponse {
  success: boolean
  message?: string
  user: User
}

export interface PasswordUpdateResponse {
  success: boolean
  message: string
}

export interface ProfileSchemaMessages {
  nameRequired: string
  countryRequired: string
}

export interface PasswordSchemaMessages {
  currentPasswordRequired: string
  newPasswordRequired: string
  newPasswordMin: string
  confirmPasswordRequired: string
  passwordsMismatch: string
}
