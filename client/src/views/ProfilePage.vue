<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
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
import ThemeToggle from '@/components/blocks/ThemeToggle.vue'
import { PhIdentificationCard, PhLockKey, PhUserCircle, PhSunDim } from '@phosphor-icons/vue'

const {
  lang,
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
  countrySelectClass,
  currentPasswordClass,
  newPasswordClass,
  confirmPasswordClass,
} = useProfile()
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background pt-24">
      <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm sticky top-20 z-10">
        <div class="container mx-auto px-4 py-6">
          <h1 class="text-3xl font-bold text-foreground">{{ lang.profile.title }}</h1>
          <p class="text-sm text-muted-foreground mt-1">{{ lang.profile.subtitle }}</p>
        </div>
      </div>

      <div class="container mx-auto px-4 py-8 pb-24 md:pb-8">
        <div v-if="isProfileLoading" class="text-center py-16 text-muted-foreground">
          {{ lang.profile.states.loading }}
        </div>

        <div v-else-if="!profile" class="text-center py-16 text-muted-foreground">
          {{ lang.profile.states.empty }}
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <section class="liquid-glass rounded-2xl border border-border/20 p-6 space-y-6">
              <div class="flex items-center gap-2">
                <PhIdentificationCard class="h-5 w-5 text-primary" />
                <h2 class="text-lg font-semibold text-foreground">
                  {{ lang.profile.sections.updateProfile }}
                </h2>
              </div>

              <form @submit.prevent="handleProfileSubmit" class="space-y-4">
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-foreground">{{
                    lang.profile.fields.name
                  }}</Label>
                  <Input
                    v-model="profileForm.state.values.name"
                    :placeholder="lang.profile.placeholders.name"
                    :class="nameInputClass"
                    @input="clearProfileError('name')"
                  />
                  <p v-if="profileErrors.name" class="text-xs text-destructive">
                    {{ profileErrors.name }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label class="text-sm font-medium text-foreground">
                    {{ lang.profile.fields.country }}
                  </Label>
                  <Select
                    v-model="profileForm.state.values.country"
                    @update:modelValue="clearProfileError('country')"
                  >
                    <SelectTrigger :class="countrySelectClass">
                      <SelectValue :placeholder="lang.profile.placeholders.country" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="country in countries" :key="country" :value="country">
                        {{ country }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="profileErrors.country" class="text-xs text-destructive">
                    {{ profileErrors.country }}
                  </p>
                </div>

                <Button type="submit" :disabled="isUpdatingProfile" class="w-full">
                  {{ lang.profile.actions.saveProfile }}
                </Button>
              </form>
            </section>

            <section class="liquid-glass rounded-2xl border border-border/20 p-6 space-y-6">
              <div class="flex items-center gap-2">
                <PhLockKey class="h-5 w-5 text-primary" />
                <h2 class="text-lg font-semibold text-foreground">
                  {{ lang.profile.sections.updatePassword }}
                </h2>
              </div>

              <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-foreground">
                    {{ lang.profile.fields.currentPassword }}
                  </Label>
                  <Input
                    v-model="passwordForm.state.values.current_password"
                    type="password"
                    :placeholder="lang.profile.placeholders.currentPassword"
                    :class="currentPasswordClass"
                    @input="clearPasswordError('current_password')"
                  />
                  <p v-if="passwordErrors.current_password" class="text-xs text-destructive">
                    {{ passwordErrors.current_password }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label class="text-sm font-medium text-foreground">
                    {{ lang.profile.fields.newPassword }}
                  </Label>
                  <Input
                    v-model="passwordForm.state.values.new_password"
                    type="password"
                    :placeholder="lang.profile.placeholders.newPassword"
                    :class="newPasswordClass"
                    @input="clearPasswordError('new_password')"
                  />
                  <p v-if="passwordErrors.new_password" class="text-xs text-destructive">
                    {{ passwordErrors.new_password }}
                  </p>
                </div>

                <div class="space-y-2">
                  <Label class="text-sm font-medium text-foreground">
                    {{ lang.profile.fields.confirmNewPassword }}
                  </Label>
                  <Input
                    v-model="passwordForm.state.values.new_password_confirmation"
                    type="password"
                    :placeholder="lang.profile.placeholders.confirmNewPassword"
                    :class="confirmPasswordClass"
                    @input="clearPasswordError('new_password_confirmation')"
                  />
                  <p
                    v-if="passwordErrors.new_password_confirmation"
                    class="text-xs text-destructive"
                  >
                    {{ passwordErrors.new_password_confirmation }}
                  </p>
                </div>

                <Button type="submit" :disabled="isUpdatingPassword" class="w-full">
                  {{ lang.profile.actions.savePassword }}
                </Button>
              </form>
            </section>
          </div>

          <aside class="space-y-6">
            <section class="liquid-glass rounded-2xl border border-border/20 p-6 space-y-4">
              <div class="flex items-center gap-2">
                <PhUserCircle class="h-6 w-6 text-primary" />
                <h2 class="text-lg font-semibold text-foreground">
                  {{ lang.profile.sections.personalInfo }}
                </h2>
              </div>

              <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">{{ lang.profile.fields.name }}</span>
                  <span class="text-foreground font-medium">
                    {{ profile?.name || lang.common.notAvailable }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">{{ lang.profile.fields.email }}</span>
                  <span class="text-foreground font-medium">
                    {{ profile?.email || lang.common.notAvailable }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">{{ lang.profile.fields.country }}</span>
                  <span class="text-foreground font-medium">
                    {{ profile?.country || lang.common.notAvailable }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-muted-foreground">{{ lang.profile.fields.role }}</span>
                  <span class="text-foreground font-medium">
                    {{ profile?.role || lang.common.notAvailable }}
                  </span>
                </div>
              </div>

              <p class="text-xs text-muted-foreground">
                {{ lang.profile.helper.emailReadOnly }}
              </p>
            </section>

            <section class="liquid-glass rounded-2xl border border-border/20 p-6 space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <PhSunDim class="h-6 w-6 text-primary" />
                  <h2 class="text-lg font-semibold text-foreground">Appearance</h2>
                </div>
                <!-- <ThemeToggle /> -->
              </div>
            </section>
          </aside>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
