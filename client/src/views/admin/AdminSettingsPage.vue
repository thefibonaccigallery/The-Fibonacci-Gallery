<script setup lang="ts">
import { ref } from 'vue'
import { useProfile } from '@/composables/useProfile'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  PhUser,
  PhLockKey,
  PhFloppyDisk,
  PhKey,
  PhEye,
  PhEyeSlash,
  PhCircleNotch,
  PhGear,
} from '@phosphor-icons/vue'

const {
  profile,
  countries,
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
} = useProfile()

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
</script>

<template>
  <div class="space-y-6 max-w-4xl mx-auto animate-fade-in-up">
    <!-- Header -->
    <div>
      <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-foreground flex items-center gap-2.5">
        <PhGear :size="30" weight="bold" class="text-primary" />
        Settings
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        Update your account profile and change your password.
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isProfileLoading" class="liquid-glass rounded-2xl p-12 text-center text-muted-foreground">
      <PhCircleNotch :size="32" class="animate-spin text-primary mx-auto mb-3" />
      <p class="text-sm">Loading settings...</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Update Profile (Name & Email & Country) Form -->
      <section class="liquid-glass rounded-2xl border border-border/40 p-6 sm:p-7 space-y-6 shadow-sm">
        <div class="flex items-center gap-3 pb-4 border-b border-border/40">
          <div class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
            <PhUser :size="20" weight="bold" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-foreground">Profile Information</h2>
            <p class="text-xs text-muted-foreground">Update your display name and email address.</p>
          </div>
        </div>

        <form @submit.prevent="handleProfileSubmit" class="space-y-4">
          <div class="space-y-2">
            <Label for="admin-name" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Full Name
            </Label>
            <Input
              id="admin-name"
              v-model="profileForm.state.values.name"
              placeholder="Enter your name"
              :class="`${nameInputClass} h-11`"
              @input="clearProfileError('name')"
            />
            <p v-if="profileErrors.name" class="text-xs text-destructive">
              {{ profileErrors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="admin-email" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Email Address
            </Label>
            <Input
              id="admin-email"
              type="email"
              v-model="profileForm.state.values.email"
              placeholder="admin@example.com"
              :class="`${emailInputClass} h-11`"
              @input="clearProfileError('email')"
            />
            <p v-if="profileErrors.email" class="text-xs text-destructive">
              {{ profileErrors.email }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="admin-country" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Country
            </Label>
            <Select
              v-model="profileForm.state.values.country"
              @update:modelValue="clearProfileError('country')"
            >
              <SelectTrigger id="admin-country" :class="`${countrySelectClass} h-11`">
                <SelectValue placeholder="Select country" />
              </SelectTrigger>
              <SelectContent class="max-h-60">
                <SelectItem v-for="country in countries" :key="country" :value="country">
                  {{ country }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="profileErrors.country" class="text-xs text-destructive">
              {{ profileErrors.country }}
            </p>
          </div>

          <div class="pt-2 flex justify-end">
            <Button type="submit" :disabled="isUpdatingProfile" class="gap-2 h-11 px-6 font-medium">
              <PhCircleNotch v-if="isUpdatingProfile" :size="18" class="animate-spin" />
              <PhFloppyDisk v-else :size="18" weight="bold" />
              <span>{{ isUpdatingProfile ? 'Saving...' : 'Save Profile' }}</span>
            </Button>
          </div>
        </form>
      </section>

      <!-- Update Password Form -->
      <section class="liquid-glass rounded-2xl border border-border/40 p-6 sm:p-7 space-y-6 shadow-sm">
        <div class="flex items-center gap-3 pb-4 border-b border-border/40">
          <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
            <PhLockKey :size="20" weight="bold" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-foreground">Change Password</h2>
            <p class="text-xs text-muted-foreground">Update your password to keep your account secure.</p>
          </div>
        </div>

        <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
          <div class="space-y-2">
            <Label for="current-password" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Current Password
            </Label>
            <div class="relative">
              <Input
                id="current-password"
                :type="showCurrentPassword ? 'text' : 'password'"
                v-model="passwordForm.state.values.current_password"
                placeholder="Enter current password"
                :class="`${currentPasswordClass} pr-10 h-11`"
                @input="clearPasswordError('current_password')"
              />
              <button
                type="button"
                @click="showCurrentPassword = !showCurrentPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                tabindex="-1"
              >
                <component :is="showCurrentPassword ? PhEyeSlash : PhEye" :size="18" />
              </button>
            </div>
            <p v-if="passwordErrors.current_password" class="text-xs text-destructive">
              {{ passwordErrors.current_password }}
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="new-password" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                New Password
              </Label>
              <div class="relative">
                <Input
                  id="new-password"
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="passwordForm.state.values.new_password"
                  placeholder="Minimum 8 characters"
                  :class="`${newPasswordClass} pr-10 h-11`"
                  @input="clearPasswordError('new_password')"
                />
                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  tabindex="-1"
                >
                  <component :is="showNewPassword ? PhEyeSlash : PhEye" :size="18" />
                </button>
              </div>
              <p v-if="passwordErrors.new_password" class="text-xs text-destructive">
                {{ passwordErrors.new_password }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="confirm-password" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Confirm New Password
              </Label>
              <div class="relative">
                <Input
                  id="confirm-password"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="passwordForm.state.values.new_password_confirmation"
                  placeholder="Re-type new password"
                  :class="`${confirmPasswordClass} pr-10 h-11`"
                  @input="clearPasswordError('new_password_confirmation')"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                  tabindex="-1"
                >
                  <component :is="showConfirmPassword ? PhEyeSlash : PhEye" :size="18" />
                </button>
              </div>
              <p v-if="passwordErrors.new_password_confirmation" class="text-xs text-destructive">
                {{ passwordErrors.new_password_confirmation }}
              </p>
            </div>
          </div>

          <div class="pt-2 flex justify-end">
            <Button type="submit" :disabled="isUpdatingPassword" variant="outline" class="gap-2 h-11 px-6 font-medium">
              <PhCircleNotch v-if="isUpdatingPassword" :size="18" class="animate-spin" />
              <PhKey v-else :size="18" weight="bold" />
              <span>{{ isUpdatingPassword ? 'Updating...' : 'Update Password' }}</span>
            </Button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>
