export interface ForgotPasswordPayload {
  email: string
}

export interface ForgotPasswordResponse {
  message: string
}

export interface VerifyTokenResponse {
  valid: boolean
  message?: string
}

export interface ResetPasswordPayload {
  token: string
  password: string
  password_confirmation: string
}

export interface ResetPasswordResponse {
  message: string
}
