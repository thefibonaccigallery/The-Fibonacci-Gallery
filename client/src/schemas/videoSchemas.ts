import { z } from 'zod'

export const videoSchema = z.object({
  chapter_uuid: z.string().min(1, 'Please select a chapter'),
  title: z.string().min(1, 'Title is required').max(255, 'Title must be less than 255 characters'),
  youtube_video_id: z
    .string()
    .max(20, 'YouTube Video ID must be less than 20 characters')
    .nullable()
    .optional(),
  level: z.enum(['beginner', 'intermediate', 'advanced']).nullable().optional(),
  order: z.number().min(1, 'Order must be at least 1'),
  duration_seconds: z.number().min(0, 'Duration must be at least 0').default(0),
  is_published: z.boolean().default(false),
  is_preview: z.boolean().default(false),
  cooldown_hours: z
    .number()
    .min(0, 'Cooldown cannot be negative')
    .max(168, 'Cooldown cannot exceed 7 days (168 hours)')
    .default(0),
})

export type VideoFormState = z.infer<typeof videoSchema>
export type VideoFormField = keyof VideoFormState
