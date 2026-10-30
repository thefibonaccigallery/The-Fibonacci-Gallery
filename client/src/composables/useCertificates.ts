import { ref } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'

export interface CertificateItem {
  uuid: string
  certificate_number: string
  recipient_name: string
  course_title: string
  issued_at: string
  email_sent_at: string | null
  verify_url: string
  course: {
    uuid: string
    title: string
    level: string | null
    thumbnail_url: string | null
  } | null
}

export interface VerificationResult {
  valid: boolean
  message?: string
  certificate?: {
    uuid: string
    certificate_number: string
    recipient_name: string
    course_title: string
    issued_at: string
    academy: string
    course: {
      uuid: string
      title: string
      level: string | null
    } | null
  }
}

export function useCertificates() {
  const queryClient = useQueryClient()
  const isDownloading = ref(false)

  // Fetch all user certificates
  const useUserCertificates = () => {
    return useQuery({
      queryKey: ['user-certificates'],
      queryFn: async (): Promise<{ certificates: CertificateItem[] }> => {
        const { data } = await axiosInstance.get('/certificates')
        return data
      },
    })
  }

  // Fetch single certificate
  const useSingleCertificate = (uuid: string) => {
    return useQuery({
      queryKey: ['certificate', uuid],
      queryFn: async (): Promise<{ certificate: CertificateItem }> => {
        const { data } = await axiosInstance.get(`/certificates/${uuid}`)
        return data
      },
      enabled: Boolean(uuid),
    })
  }

  // Download PDF certificate
  const downloadPdf = async (uuid: string, certificateNumber?: string) => {
    isDownloading.value = true
    try {
      const response = await axiosInstance.get(`/certificates/${uuid}/download`, {
        responseType: 'blob',
      })

      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `Fibonacci-Certificate-${certificateNumber || uuid}.pdf`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)

      toast.success('Certificate downloaded successfully!')
    } catch (err) {
      console.error('Download error:', err)
      toast.error('Failed to download certificate PDF. Please try again.')
    } finally {
      isDownloading.value = false
    }
  }

  // Re-send certificate email
  const {
    mutate: resendCertificateEmail,
    mutateAsync: resendCertificateEmailAsync,
    isPending: isResendingEmail,
  } = useMutation({
    mutationFn: async (uuid: string) => {
      const { data } = await axiosInstance.post(`/certificates/${uuid}/resend-email`)
      return data
    },
    onSuccess: (data) => {
      toast.success(data.message || 'Certificate sent to your email!')
      queryClient.invalidateQueries({ queryKey: ['user-certificates'] })
    },
    onError: (err: any) => {
      const msg = err.response?.data?.message || 'Failed to send certificate email'
      toast.error(msg)
    },
  })

  // Verify public certificate
  const verifyCertificate = async (code: string): Promise<VerificationResult> => {
    try {
      const { data } = await axiosInstance.get(`/certificates/verify/${encodeURIComponent(code)}`)
      return data
    } catch (err: any) {
      return {
        valid: false,
        message: err.response?.data?.message || 'Certificate verification failed.',
      }
    }
  }

  return {
    useUserCertificates,
    useSingleCertificate,
    downloadPdf,
    resendCertificateEmail,
    resendCertificateEmailAsync,
    isResendingEmail,
    isDownloading,
    verifyCertificate,
  }
}
