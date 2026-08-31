export interface User {
  id: number
  uuid: string
  name: string
  email: string
  country: string
  role: string
  referral_code?: string
}

export interface AuthResponse {
  success: boolean
  message: string
  token: string
  user: User
}

export interface LoginData {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  country: string
  referral_code?: string
}
