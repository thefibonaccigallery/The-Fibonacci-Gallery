<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCertificates, type VerificationResult } from '@/composables/useCertificates'
import { Button } from '@/components/ui/button'
import {
  PhCheckCircle,
  PhXCircle,
  PhSpinner,
  PhCertificate,
  PhSealCheck,
  PhArrowLeft,
  PhCalendar,
  PhBookOpen,
  PhGraduationCap,
  PhShareNetwork,
} from '@phosphor-icons/vue'
import { toast } from 'vue-sonner'

const route = useRoute()
const router = useRouter()
const { verifyCertificate } = useCertificates()

const certificateNumber = computed(() => (route.params.certificateNumber as string || '').toUpperCase())
const isLoading = ref(true)
const result = ref<VerificationResult | null>(null)

const verify = async () => {
  if (!certificateNumber.value) {
    isLoading.value = false
    result.value = { valid: false, message: 'No certificate number provided' }
    return
  }

  isLoading.value = true
  try {
    const res = await verifyCertificate(certificateNumber.value)
    result.value = res
  } finally {
    isLoading.value = false
  }
}

const copyShareLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href)
    toast.success('Verification link copied to clipboard!')
  } catch {
    toast.error('Failed to copy link')
  }
}

const formatDate = (dateString?: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

onMounted(() => {
  verify()
})
</script>

<template>
  <div class="min-h-screen bg-[#070908] text-foreground flex flex-col justify-between selection:bg-amber-500/20 selection:text-amber-300">
    <!-- Top Navigation Bar -->
    <header class="border-b border-border/30 bg-black/40 backdrop-blur-md sticky top-0 z-20">
      <div class="container mx-auto px-4 py-3.5 flex items-center justify-between">
        <router-link to="/" class="flex items-center gap-2 group">
          <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 group-hover:scale-105 transition-transform">
            <PhCertificate :size="18" weight="fill" />
          </div>
          <span class="font-bold text-sm sm:text-base tracking-wider uppercase text-zinc-200 group-hover:text-amber-400 transition-colors">
            THE FIBONACCI GALLERY
          </span>
        </router-link>

        <Button
          @click="router.push('/explore')"
          size="sm"
          variant="outline"
          class="border-border/60 hover:bg-zinc-900 text-xs gap-1.5"
        >
          <PhBookOpen :size="14" />
          <span class="hidden sm:inline">Explore Masterclasses</span>
          <span class="sm:hidden">Courses</span>
        </Button>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 md:p-10 my-auto">
      <div class="w-full max-w-2xl">
        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-20 space-y-4">
          <PhSpinner class="h-10 w-10 animate-spin text-amber-400 mx-auto" />
          <p class="text-sm text-muted-foreground font-mono">
            Verifying credential {{ certificateNumber }} in official registry...
          </p>
        </div>

        <!-- Valid Certificate Display -->
        <div
          v-else-if="result?.valid && result.certificate"
          class="liquid-glass rounded-2xl border border-amber-500/40 shadow-[0_20px_60px_rgba(0,0,0,0.8),0_0_40px_rgba(245,158,11,0.12)] overflow-hidden animate-fade-in"
        >
          <!-- Top Status Banner -->
          <div class="bg-linear-to-r from-emerald-950/60 via-emerald-900/40 to-emerald-950/60 border-b border-emerald-500/30 px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2 text-emerald-400 text-xs sm:text-sm font-bold tracking-wide">
              <PhCheckCircle :size="18" weight="fill" />
              <span>OFFICIALLY VERIFIED CREDENTIAL</span>
            </div>
            <div class="text-[11px] font-mono text-emerald-400/80 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-500/20">
              Active Record
            </div>
          </div>

          <!-- Certificate Parchment Card -->
          <div class="p-6 sm:p-8 space-y-6">
            <!-- Academy Branding -->
            <div class="text-center space-y-1">
              <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-[10.5px] font-mono tracking-widest uppercase">
                <PhSealCheck :size="13" weight="fill" />
                <span>ACADEMIC VERIFICATION REGISTRY</span>
              </div>
              <h1 class="text-2xl sm:text-3xl font-serif font-black text-white tracking-wider uppercase pt-2">
                Certificate of Completion
              </h1>
              <p class="text-xs text-zinc-400 italic">
                This authentic record confirms that
              </p>
            </div>

            <!-- Recipient Name Highlight -->
            <div class="text-center py-2">
              <div class="inline-block border-b-2 border-amber-500/80 px-6 pb-2">
                <span class="text-2xl sm:text-3xl font-serif font-bold text-amber-300 tracking-wide">
                  {{ result.certificate.recipient_name }}
                </span>
              </div>
            </div>

            <!-- Achievement Narrative -->
            <p class="text-center text-xs sm:text-sm text-zinc-300 max-w-lg mx-auto leading-relaxed">
              has completed all lectures, practical case studies, and curriculum requirements for
            </p>

            <!-- Course Title Badge -->
            <div class="p-4 rounded-xl bg-card/80 border border-border/60 text-center space-y-1">
              <div class="text-xs uppercase tracking-widest text-primary font-semibold">Masterclass Course</div>
              <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">
                {{ result.certificate.course_title }}
              </h2>
            </div>

            <!-- Credential Metadata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs">
              <div class="p-3 rounded-lg bg-zinc-950/80 border border-border/40 space-y-1">
                <div class="text-muted-foreground flex items-center gap-1.5 text-[11px] uppercase tracking-wider">
                  <PhGraduationCap :size="14" class="text-amber-400" />
                  <span>Issuing Institution</span>
                </div>
                <div class="font-semibold text-zinc-200">
                  {{ result.certificate.academy || 'The Fibonacci Gallery' }}
                </div>
              </div>

              <div class="p-3 rounded-lg bg-zinc-950/80 border border-border/40 space-y-1">
                <div class="text-muted-foreground flex items-center gap-1.5 text-[11px] uppercase tracking-wider">
                  <PhCalendar :size="14" class="text-amber-400" />
                  <span>Date of Issuance</span>
                </div>
                <div class="font-semibold text-zinc-200">
                  {{ formatDate(result.certificate.issued_at) }}
                </div>
              </div>

              <div class="sm:col-span-2 p-3 rounded-lg bg-zinc-950/80 border border-border/40 flex items-center justify-between">
                <div>
                  <div class="text-muted-foreground text-[11px] uppercase tracking-wider">Unique Credential ID</div>
                  <div class="font-mono font-bold text-amber-400 text-sm">
                    {{ result.certificate.certificate_number }}
                  </div>
                </div>
                <Button
                  @click="copyShareLink"
                  variant="outline"
                  size="sm"
                  class="gap-1.5 text-xs border-amber-500/40 text-amber-400 hover:bg-amber-500/10 h-7"
                >
                  <PhShareNetwork :size="14" />
                  <span>Share Verification</span>
                </Button>
              </div>
            </div>
          </div>

          <!-- Bottom Call to Action -->
          <div class="p-4 bg-zinc-950/90 border-t border-border/40 text-center flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span class="text-zinc-400">
              Want to master institutional market dynamics?
            </span>
            <Button
              @click="router.push('/explore')"
              size="sm"
              class="bg-primary hover:bg-primary/90 text-primary-foreground font-semibold"
            >
              Explore Masterclasses
            </Button>
          </div>
        </div>

        <!-- Invalid Certificate Display -->
        <div
          v-else
          class="liquid-glass rounded-2xl border border-destructive/40 p-8 text-center space-y-4 max-w-md mx-auto"
        >
          <div class="w-14 h-14 rounded-full bg-destructive/10 border border-destructive/30 flex items-center justify-center text-destructive mx-auto">
            <PhXCircle :size="32" weight="fill" />
          </div>
          <h2 class="text-xl font-bold text-foreground">Verification Failed</h2>
          <p class="text-sm text-muted-foreground leading-relaxed">
            The credential ID <strong class="text-foreground font-mono">{{ certificateNumber }}</strong> could not be verified in our official registry.
          </p>
          <div class="pt-2 flex justify-center gap-2">
            <Button @click="router.push('/')" variant="outline" size="sm">
              <PhArrowLeft class="mr-1 w-3.5 h-3.5" />
              Back Home
            </Button>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-border/20 py-4 text-center text-xs text-zinc-500">
      <div class="container mx-auto px-4">
        © {{ new Date().getFullYear() }} The Fibonacci Gallery. All verified certificates are cryptographically tracked and non-transferable.
      </div>
    </footer>
  </div>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(20, 24, 22, 0.95) 0%, rgba(10, 13, 11, 0.98) 100%);
  backdrop-filter: blur(14px);
}
</style>
