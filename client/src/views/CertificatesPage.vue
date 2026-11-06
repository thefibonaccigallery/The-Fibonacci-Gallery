<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Button } from '@/components/ui/button'
import { useCertificates, type CertificateItem } from '@/composables/useCertificates'
import {
  PhCertificate,
  PhDownloadSimple,
  PhPaperPlaneTilt,
  PhArrowSquareOut,
  PhCopy,
  PhCheck,
  PhSpinner,
  PhBookOpen,
  PhSparkle,
  PhSealCheck,
} from '@phosphor-icons/vue'
import { toast } from 'vue-sonner'

const router = useRouter()
const {
  useUserCertificates,
  downloadPdf,
  resendCertificateEmailAsync,
  isResendingEmail,
  isDownloading,
} = useCertificates()

const { data, isLoading, error } = useUserCertificates()
const copiedId = ref<string | null>(null)
const downloadingUuid = ref<string | null>(null)
const resendingUuid = ref<string | null>(null)

const handleDownload = async (cert: CertificateItem) => {
  if (downloadingUuid.value || resendingUuid.value) return
  downloadingUuid.value = cert.uuid
  try {
    await downloadPdf(cert.uuid, cert.certificate_number)
  } catch (err) {
    // Already handled in downloadPdf
  } finally {
    downloadingUuid.value = null
  }
}

const handleResend = async (cert: CertificateItem) => {
  if (resendingUuid.value || downloadingUuid.value) return
  resendingUuid.value = cert.uuid
  try {
    await resendCertificateEmailAsync(cert.uuid)
  } catch (err) {
    // Handled in mutation onError
  } finally {
    resendingUuid.value = null
  }
}

const copyToClipboard = async (text: string, id: string, label: string) => {
  try {
    await navigator.clipboard.writeText(text)
    copiedId.value = id
    toast.success(`${label} copied to clipboard!`)
    setTimeout(() => {
      copiedId.value = null
    }, 2000)
  } catch (e) {
    toast.error('Failed to copy to clipboard')
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>

<template>
  <DashboardLayout>
    <div class="min-h-screen bg-background pb-20">
      <!-- Header -->
      <div class="border-b border-border/40 bg-card/50 backdrop-blur-sm">
        <div class="container mx-auto px-4 py-5 sm:py-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <PhSealCheck :size="14" weight="fill" />
                <span>Verified Credentials</span>
              </div>
              <h1 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">My Certificates</h1>
              <p class="text-xs sm:text-sm text-muted-foreground mt-1">
                Official accreditation credentials earned across your Fibonacci masterclasses
              </p>
            </div>
            <div class="flex items-center gap-2">
              <Button
                @click="router.push('/my-courses')"
                variant="outline"
                size="sm"
                class="border-border/60 hover:bg-card text-xs sm:text-sm"
              >
                <PhBookOpen class="h-4 w-4 mr-2" />
                My Courses
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="container mx-auto px-4 py-8">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-24 space-y-3">
          <PhSpinner class="h-9 w-9 animate-spin text-amber-400" />
          <p class="text-xs text-muted-foreground">Retrieving your credentials...</p>
        </div>

        <!-- Error State -->
        <div
          v-else-if="error"
          class="text-center py-16 liquid-glass rounded-2xl border border-destructive/30 p-8 max-w-lg mx-auto"
        >
          <p class="text-destructive font-medium">Failed to load certificates.</p>
          <Button @click="() => router.go(0)" variant="outline" size="sm" class="mt-4">
            Try Again
          </Button>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!data?.certificates || data.certificates.length === 0"
          class="text-center py-20 liquid-glass rounded-2xl border border-border/20 p-8 max-w-xl mx-auto space-y-4"
        >
          <div class="w-16 h-16 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center mx-auto text-amber-400">
            <PhCertificate :size="32" weight="fill" />
          </div>
          <h3 class="text-xl font-bold text-foreground">No Certificates Earned Yet</h3>
          <p class="text-sm text-muted-foreground max-w-md mx-auto leading-relaxed">
            Certificates of Completion are automatically issued once you finish 100% of the lessons in any enrolled masterclass.
          </p>
          <div class="pt-2">
            <Button
              @click="router.push('/my-courses')"
              class="bg-primary hover:bg-primary/90 text-primary-foreground font-semibold"
            >
              Continue Learning
            </Button>
          </div>
        </div>

        <!-- Certificates Grid -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div
            v-for="cert in data.certificates"
            :key="cert.uuid"
            class="liquid-glass rounded-2xl border border-border/30 overflow-hidden flex flex-col justify-between transition-all duration-300 hover:border-amber-500/40 hover:shadow-xl hover:shadow-amber-500/5"
          >
            <!-- Certificate Card Header & Visual Preview -->
            <div class="p-6 pb-4">
              <!-- Mini Certificate Parchment Preview -->
              <div
                class="relative rounded-xl p-6 bg-[#fbf9f4] text-[#1a2421] border-2 border-[#b38728] shadow-inner overflow-hidden select-none"
              >
                <!-- Subtle Inset Gold Dashed Border -->
                <div class="absolute inset-1.5 rounded-lg border border-dashed border-[#b38728]/40 pointer-events-none"></div>

                <!-- Subtle Fibonacci Spiral Watermark -->
                <svg
                  class="absolute -right-6 -bottom-6 w-36 h-36 text-[#b38728]/10 pointer-events-none"
                  viewBox="0 0 100 100"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.8"
                >
                  <path d="M 10 90 A 80 80 0 0 1 90 10 A 50 50 0 0 1 40 60 A 30 30 0 0 1 70 90 A 18 18 0 0 1 52 72" />
                </svg>

                <div class="relative z-10 flex flex-col items-center text-center space-y-1.5">
                  <div class="text-[9px] font-mono tracking-[0.25em] text-[#0f281e] uppercase font-bold">
                    THE FIBONACCI GALLERY
                  </div>
                  <div class="text-[14px] sm:text-[17px] font-serif font-black tracking-wider text-[#0f281e] uppercase">
                    Certificate of Completion
                  </div>
                  <div class="text-[10px] italic text-[#555555]">This certifies that</div>
                  <div class="text-[16px] sm:text-[20px] font-serif font-bold text-[#0b1e16] border-b border-[#b38728] px-4 pb-0.5 max-w-full truncate">
                    {{ cert.recipient_name }}
                  </div>
                  <div class="text-[9.5px] text-[#4a5550] max-w-sm">
                    has successfully mastered the academic curriculum for
                  </div>
                  <div class="text-[12px] sm:text-[14px] font-bold text-[#0f281e] tracking-wide uppercase px-2 line-clamp-1">
                    {{ cert.course_title }}
                  </div>

                  <!-- Mini Footer Row -->
                  <div class="w-full pt-3 mt-1 border-t border-[#0f281e]/15 flex items-center justify-between text-[8px] text-[#4a5550]">
                    <span>{{ formatDate(cert.issued_at) }}</span>
                    <span class="font-mono font-bold text-[#8c6d23]">{{ cert.certificate_number }}</span>
                    <span class="font-semibold text-[#0f281e]">★ VERIFIED</span>
                  </div>
                </div>
              </div>

              <!-- Certificate Meta Data & Details -->
              <div class="mt-5 space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <h3 class="text-lg font-bold text-foreground line-clamp-1">
                      {{ cert.course_title }}
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">
                      Issued to <strong class="text-foreground">{{ cert.recipient_name }}</strong> on {{ formatDate(cert.issued_at) }}
                    </p>
                  </div>
                  <div
                    class="shrink-0 px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1"
                  >
                    <PhCheck class="w-3 h-3" weight="bold" />
                    <span>Verified</span>
                  </div>
                </div>

                <!-- Credential ID Box with Copy Actions -->
                <div class="p-2.5 rounded-lg bg-card/80 border border-border/40 flex items-center justify-between gap-2">
                  <div class="min-w-0">
                    <div class="text-[10px] text-muted-foreground uppercase tracking-wider font-semibold">Credential ID</div>
                    <div class="text-xs font-mono font-bold text-amber-400 truncate">
                      {{ cert.certificate_number }}
                    </div>
                  </div>
                  <div class="flex items-center gap-1 shrink-0">
                    <Button
                      @click="copyToClipboard(cert.certificate_number, `id-${cert.uuid}`, 'Credential ID')"
                      variant="ghost"
                      size="sm"
                      class="h-7 px-2 text-xs text-muted-foreground hover:text-foreground"
                      title="Copy ID"
                    >
                      <PhCheck v-if="copiedId === `id-${cert.uuid}`" class="w-3.5 h-3.5 text-primary" weight="bold" />
                      <PhCopy v-else class="w-3.5 h-3.5" />
                    </Button>
                    <Button
                      @click="copyToClipboard(cert.verify_url, `url-${cert.uuid}`, 'Verification URL')"
                      variant="ghost"
                      size="sm"
                      class="h-7 px-2 text-xs text-muted-foreground hover:text-foreground"
                      title="Copy Public Verification Link"
                    >
                      <PhCheck v-if="copiedId === `url-${cert.uuid}`" class="w-3.5 h-3.5 text-primary" weight="bold" />
                      <PhSparkle v-else class="w-3.5 h-3.5" />
                      <span class="text-[10px] ml-1">Copy Link</span>
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="p-6 pt-3 border-t border-border/30 bg-card/30 flex flex-wrap items-center justify-between gap-2.5">
              <div class="flex items-center gap-2">
                <!-- Download PDF Button -->
                <Button
                  @click="handleDownload(cert)"
                  :disabled="downloadingUuid === cert.uuid || resendingUuid === cert.uuid"
                  size="sm"
                  class="gap-1.5 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold text-xs h-8 px-3 cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <PhSpinner v-if="downloadingUuid === cert.uuid" class="w-3.5 h-3.5 animate-spin" />
                  <PhDownloadSimple v-else class="w-3.5 h-3.5" weight="bold" />
                  <span>{{ downloadingUuid === cert.uuid ? 'Downloading...' : 'Download PDF' }}</span>
                </Button>

                <!-- Resend Email Button -->
                <Button
                  @click="handleResend(cert)"
                  :disabled="resendingUuid === cert.uuid || downloadingUuid === cert.uuid"
                  variant="outline"
                  size="sm"
                  class="gap-1.5 text-xs h-8 px-3 border-border/60 hover:bg-card cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
                  title="Send certificate again to your registered email"
                >
                  <PhSpinner v-if="resendingUuid === cert.uuid" class="w-3.5 h-3.5 animate-spin" />
                  <PhPaperPlaneTilt v-else class="w-3.5 h-3.5" weight="bold" />
                  <span>{{ resendingUuid === cert.uuid ? 'Sending...' : 'Email Me PDF' }}</span>
                </Button>
              </div>

              <!-- Public Verify Link -->
              <a
                :href="`/certificates/verify/${cert.certificate_number}`"
                target="_blank"
                class="inline-flex items-center gap-1 text-xs text-amber-400 hover:text-amber-300 font-medium hover:underline"
              >
                <span>Verify Online</span>
                <PhArrowSquareOut class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.liquid-glass {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%);
  backdrop-filter: blur(12px);
}
</style>
