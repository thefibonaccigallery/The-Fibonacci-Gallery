import { z } from 'zod'
import { computed } from 'vue'

// ── Payout Request Schema ──
const payoutRequestSchemaRaw = z.object({
  wallet_address: z
    .string()
    .min(1, 'Wallet address is required')
    .min(10, 'Invalid wallet address format')
    .max(100, 'Wallet address is too long'),
  network: z.string().min(1, 'Network is required'),
})

export const usePayoutRequestSchema = computed(() => payoutRequestSchemaRaw)
export type PayoutRequestFormState = z.infer<typeof payoutRequestSchemaRaw>
export type PayoutRequestFormField = keyof PayoutRequestFormState

// ── Admin Reject Schema ──
const rejectPayoutSchemaRaw = z.object({
  admin_note: z.string().min(1, 'Please provide a reason for rejection').max(500, 'Reason is too long'),
})

export const useRejectPayoutSchema = computed(() => rejectPayoutSchemaRaw)
export type RejectPayoutFormState = z.infer<typeof rejectPayoutSchemaRaw>
export type RejectPayoutFormField = keyof RejectPayoutFormState
