<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuery } from '@tanstack/vue-query'
import { useAdminCoupons } from '@/composables/admin/useCoupons'
import { useCourses } from '@/composables/admin/useCourses'
import { couponSchema } from '@/schemas/couponSchemas'
import { validateFormWithZod, normalizeErrors } from '@/utils/errorFormatter'
import type { CouponFormState, CouponFormField } from '@/schemas/couponSchemas'
import type { NormalizedErrors } from '@/utils/errorFormatter'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { PhArrowLeft, PhFloppyDisk } from '@phosphor-icons/vue'

const route = useRoute()
const router = useRouter()

const {
  fetchCoupon,
  createCoupon,
  updateCoupon,
  isCreating,
  isUpdating,
  createError,
  updateError,
} = useAdminCoupons()

const { fetchCourses } = useCourses()

const couponUuid = computed(() => {
  const uuid = route.params.uuid
  return uuid ? String(uuid) : null
})

const isEditMode = computed(() => !!couponUuid.value)
const isSaving = computed(() => isCreating.value || isUpdating.value)

const form = ref<CouponFormState>({
  course_uuid: '',
  code: '',
  discount_percentage: 10,
  max_uses: null,
  expires_at: null,
  is_active: true,
})

const errors = ref<NormalizedErrors<CouponFormField>>({})

// Fetch courses for select
const { data: coursesData } = useQuery({
  queryKey: ['admin-courses-select'],
  queryFn: () => fetchCourses({ per_page: 100 }),
})

const courses = computed(() => coursesData.value?.data || [])

// Fetch coupon data if editing
const { data: couponData, isLoading } = useQuery({
  queryKey: ['admin-coupon', couponUuid],
  queryFn: () => fetchCoupon(couponUuid.value!),
  enabled: isEditMode,
})

watch(couponData, (data) => {
  if (data?.coupon) {
    const c = data.coupon
    form.value = {
      course_uuid: c.course?.uuid || '',
      code: c.code,
      discount_percentage: Number(c.discount_percentage),
      max_uses: c.max_uses ?? null,
      expires_at: c.expires_at ? c.expires_at.substring(0, 10) : null,
      is_active: c.is_active,
    }
  }
})

// Sync backend errors from mutation
watch([createError, updateError], ([ce, ue]) => {
  const err = ce || ue
  if (err && (err as any).response?.data?.errors) {
    errors.value = normalizeErrors<CouponFormField>((err as any).response.data.errors)
  }
})

function clearError(field: CouponFormField) {
  if (errors.value[field]) {
    delete errors.value[field]
  }
}

async function handleSubmit() {
  errors.value = {}

  const validationError = validateFormWithZod<CouponFormField>(couponSchema, form.value)
  if (validationError) {
    errors.value = validationError
    return
  }

  const payload = {
    ...form.value,
    code: form.value.code.toUpperCase().trim(),
    max_uses: form.value.max_uses || null,
    expires_at: form.value.expires_at || null,
  }

  if (isEditMode.value) {
    updateCoupon(
      { uuid: couponUuid.value!, data: payload },
      { onSuccess: () => router.push('/admin/coupons') },
    )
  } else {
    createCoupon(payload, { onSuccess: () => router.push('/admin/coupons') })
  }
}
</script>

<template>
  <div class="space-y-6 max-w-2xl">
    <!-- Header -->
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="sm" @click="router.push('/admin/coupons')">
        <PhArrowLeft :size="18" class="mr-2" />
        Back
      </Button>
      <h1 class="text-3xl font-bold text-foreground">
        {{ isEditMode ? 'Edit Coupon' : 'Create Coupon' }}
      </h1>
    </div>

    <!-- Loading (edit mode) -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
    </div>

    <!-- Form -->
    <div v-else class="liquid-glass rounded-xl p-6 space-y-5">
      <!-- Course -->
      <div class="space-y-2">
        <Label>Course</Label>
        <Select
          v-model="form.course_uuid"
          @update:model-value="clearError('course_uuid')"
        >
          <SelectTrigger :class="errors.course_uuid ? 'border-destructive' : ''">
            <SelectValue placeholder="Select a course" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="course in courses" :key="course.uuid" :value="course.uuid">
              {{ course.title }}
            </SelectItem>
          </SelectContent>
        </Select>
        <small v-if="errors.course_uuid" class="text-destructive text-xs">
          {{ errors.course_uuid }}
        </small>
      </div>

      <!-- Code -->
      <div class="space-y-2">
        <Label>Coupon Code</Label>
        <Input
          v-model="form.code"
          placeholder="e.g. SUMMER50"
          class="uppercase"
          :invalid="!!errors.code"
          @input="clearError('code')"
        />
        <small v-if="errors.code" class="text-destructive text-xs">{{ errors.code }}</small>
        <p class="text-xs text-muted-foreground">Code will be stored in uppercase.</p>
      </div>

      <!-- Discount % -->
      <div class="space-y-2">
        <Label>Discount Percentage</Label>
        <div class="relative">
          <Input
            :model-value="form.discount_percentage"
            @update:model-value="(v) => { form.discount_percentage = Number(v); clearError('discount_percentage') }"
            placeholder="e.g. 50"
            :invalid="!!errors.discount_percentage"
          />
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">%</span>
        </div>
        <small v-if="errors.discount_percentage" class="text-destructive text-xs">
          {{ errors.discount_percentage }}
        </small>
        <p class="text-xs text-muted-foreground">
          Enter 1–100. 100% means the course will be completely free for coupon holders.
        </p>
      </div>

      <!-- Max Uses -->
      <div class="space-y-2">
        <Label>Max Uses <span class="text-muted-foreground font-normal">(optional)</span></Label>
        <Input
          :model-value="form.max_uses ?? ''"
          @update:model-value="(v) => { form.max_uses = v ? Number(v) : null; clearError('max_uses') }"
          placeholder="Leave blank for unlimited"
          :invalid="!!errors.max_uses"
        />
        <small v-if="errors.max_uses" class="text-destructive text-xs">{{ errors.max_uses }}</small>
        <p class="text-xs text-muted-foreground">
          Maximum number of students who can use this coupon. Leave blank for unlimited.
        </p>
      </div>

      <!-- Expires At -->
      <div class="space-y-2">
        <Label>Expiry Date <span class="text-muted-foreground font-normal">(optional)</span></Label>
        <Input
          :model-value="form.expires_at ?? ''"
          @update:model-value="(v) => { form.expires_at = v || null; clearError('expires_at') }"
          type="date"
          :invalid="!!errors.expires_at"
        />
        <small v-if="errors.expires_at" class="text-destructive text-xs">{{ errors.expires_at }}</small>
        <p class="text-xs text-muted-foreground">Leave blank for no expiry.</p>
      </div>

      <!-- Is Active -->
      <div class="flex items-center justify-between py-2">
        <div class="space-y-1">
          <Label>Active</Label>
          <p class="text-xs text-muted-foreground">
            Disabled coupons cannot be used by students.
          </p>
        </div>
        <Switch v-model:checked="form.is_active" />
      </div>

      <!-- Submit -->
      <div class="flex justify-end pt-2">
        <Button @click="handleSubmit" :disabled="isSaving">
          <PhFloppyDisk :size="18" class="mr-2" />
          {{ isSaving ? 'Saving...' : isEditMode ? 'Save Changes' : 'Create Coupon' }}
        </Button>
      </div>
    </div>
  </div>
</template>
