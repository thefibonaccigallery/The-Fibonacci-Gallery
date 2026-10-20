import { computed } from 'vue'
import { z } from 'zod'
import type { ProfileFormState, ProfileSchemaMessages } from '@/types/ProfileTypes'

const buildProfileSchema = (messages: ProfileSchemaMessages) =>
  z.object({
    name: z.string().min(1, messages.nameRequired).max(255),
    email: z
      .string()
      .email(messages.emailInvalid || 'Please enter a valid email address')
      .optional()
      .or(z.literal('')),
    country: z.string().min(1, messages.countryRequired).max(255),
  })

export const useProfileSchema = (messages: ProfileSchemaMessages) =>
  computed(() => buildProfileSchema(messages) as z.ZodType<ProfileFormState>)

export type { ProfileFormState, ProfileFormField } from '@/types/ProfileTypes'
