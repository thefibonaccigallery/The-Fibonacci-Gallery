import { computed, ref, watch } from 'vue'
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query'
import { useForm } from '@tanstack/vue-form'
import { toast } from 'vue-sonner'
import axiosInstance from '@/api/axiosInstance'
import { useAuthStore } from '@/stores/auth'
import { useLanguageStore } from '@/stores/languageStore'
import { useProfileSchema } from '@/schemas/profileSchemas'
import { usePasswordSchema } from '@/schemas/passwordSchemas'
import { validateFormWithZod, normalizeErrors, type NormalizedErrors } from '@/utils/errorFormatter'
import type {
  ProfileFormField,
  ProfileFormState,
  PasswordFormField,
  PasswordFormState,
  ProfileResponse,
  PasswordUpdateResponse,
} from '@/types/ProfileTypes'
import type { User } from '@/types/UserType'

export function useProfile() {
  const authStore = useAuthStore()
  const languageStore = useLanguageStore()
  const queryClient = useQueryClient()

  const lang = computed(() => languageStore.translations)
  const profileSchema = useProfileSchema(lang.value.profile.validation.profile)
  const passwordSchema = usePasswordSchema(lang.value.profile.validation.password)

  const profileErrors = ref<NormalizedErrors<ProfileFormField>>({})
  const passwordErrors = ref<NormalizedErrors<PasswordFormField>>({})

  const profileForm = useForm({
    defaultValues: {
      name: '',
      email: '',
      country: '',
      country_code: '',
      phone_number: '',
    } as ProfileFormState,
    validators: {
      onChange: profileSchema.value,
    },
  })

  const passwordForm = useForm({
    defaultValues: {
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    } as PasswordFormState,
    validators: {
      onChange: passwordSchema.value,
    },
  })

  const fetchProfile = async (): Promise<User> => {
    try {
      const response = await axiosInstance.get<ProfileResponse>('/user')
      return response.data.user
    } catch (err: any) {
      toast.error(lang.value.profile.toasts.profileLoadFailed)
      throw err
    }
  }

  const { data: profileData, isLoading: isProfileLoading } = useQuery({
    queryKey: ['profile'],
    queryFn: fetchProfile,
  })

  const profile = computed(() => profileData.value || authStore.user)

  watch(
    profile,
    (user) => {
      if (!user) return
      profileForm.state.values.name = user.name || ''
      profileForm.state.values.email = user.email || ''
      profileForm.state.values.country = user.country || ''
      profileForm.state.values.country_code = user.country_code || ''
      profileForm.state.values.phone_number = user.phone_number || ''
    },
    { immediate: true },
  )

  const { mutate: updateProfile, isPending: isUpdatingProfile } = useMutation({
    mutationFn: async (payload: ProfileFormState) => {
      const response = await axiosInstance.put<ProfileResponse>('/user', payload)
      return response.data
    },
    onSuccess: (data) => {
      if (data.user) {
        authStore.setUserData(data.user)
      }
      queryClient.invalidateQueries({ queryKey: ['profile'] })
      toast.success(lang.value.profile.toasts.profileUpdated)
    },
    onError: (err: any) => {
      if (err.response?.data?.errors) {
        profileErrors.value = normalizeErrors<ProfileFormField>(err.response.data.errors)
      } else {
        toast.error(err.response?.data?.message || lang.value.profile.toasts.profileUpdateFailed)
      }
    },
  })

  const { mutate: updatePassword, isPending: isUpdatingPassword } = useMutation({
    mutationFn: async (payload: PasswordFormState) => {
      const response = await axiosInstance.put<PasswordUpdateResponse>('/user/password', payload)
      return response.data
    },
    onSuccess: () => {
      passwordForm.reset()
      toast.success(lang.value.profile.toasts.passwordUpdated)
    },
    onError: (err: any) => {
      if (err.response?.data?.errors) {
        passwordErrors.value = normalizeErrors<PasswordFormField>(err.response.data.errors)
      } else {
        toast.error(err.response?.data?.message || lang.value.profile.toasts.passwordUpdateFailed)
      }
    },
  })

  const handleProfileSubmit = () => {
    profileErrors.value = {}
    const error = validateFormWithZod<ProfileFormField>(
      profileSchema.value,
      profileForm.state.values,
    )
    if (error) {
      profileErrors.value = error
      return
    }

    updateProfile(profileForm.state.values as ProfileFormState)
  }

  const handlePasswordSubmit = () => {
    passwordErrors.value = {}
    const error = validateFormWithZod<PasswordFormField>(
      passwordSchema.value,
      passwordForm.state.values,
    )
    if (error) {
      passwordErrors.value = error
      return
    }

    updatePassword(passwordForm.state.values as PasswordFormState)
  }

  const clearProfileError = (field: ProfileFormField) => {
    if (profileErrors.value && profileErrors.value[field]) {
      delete profileErrors.value[field]
    }
  }

  const clearPasswordError = (field: PasswordFormField) => {
    if (passwordErrors.value && passwordErrors.value[field]) {
      delete passwordErrors.value[field]
    }
  }

  const baseInputClass = 'bg-background/50 border-border/40 text-foreground'
  const focusRingClass = 'placeholder:text-muted-foreground focus-visible:ring-primary'
  const errorClass = 'border-destructive focus-visible:ring-destructive'

  const nameInputClass = computed(() =>
    [baseInputClass, focusRingClass, profileErrors.value.name ? errorClass : '']
      .filter(Boolean)
      .join(' '),
  )

  const emailInputClass = computed(() =>
    [baseInputClass, focusRingClass, profileErrors.value.email ? errorClass : '']
      .filter(Boolean)
      .join(' '),
  )

  const countrySelectClass = computed(() =>
    [baseInputClass, focusRingClass, profileErrors.value.country ? errorClass : '']
      .filter(Boolean)
      .join(' '),
  )

  const currentPasswordClass = computed(() =>
    [baseInputClass, focusRingClass, passwordErrors.value.current_password ? errorClass : '']
      .filter(Boolean)
      .join(' '),
  )

  const newPasswordClass = computed(() =>
    [baseInputClass, focusRingClass, passwordErrors.value.new_password ? errorClass : '']
      .filter(Boolean)
      .join(' '),
  )

  const confirmPasswordClass = computed(() =>
    [
      baseInputClass,
      focusRingClass,
      passwordErrors.value.new_password_confirmation ? errorClass : '',
    ]
      .filter(Boolean)
      .join(' '),
  )

  return {
    lang,
    profile,
    countries: authStore.countries,
    countryCodes: authStore.countryCodes,
    profileForm,
    passwordForm,
    profileErrors,
    passwordErrors,
    isProfileLoading,
    isUpdatingProfile,
    isUpdatingPassword,
    handleProfileSubmit,
    handlePasswordSubmit,
    clearProfileError,
    clearPasswordError,
    nameInputClass,
    emailInputClass,
    countrySelectClass,
    currentPasswordClass,
    newPasswordClass,
    confirmPasswordClass,
  }
}
