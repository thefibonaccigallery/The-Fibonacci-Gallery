import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import router from '@/router'
import { useRoute } from 'vue-router'
import Cookies from 'js-cookie'
import axiosInstance from '@/api/axiosInstance'
import { useMutation } from '@tanstack/vue-query'
import { useForm } from '@tanstack/vue-form'
import { toast } from 'vue-sonner'
import {
  useRegisterSchema,
  useLoginSchema,
  type RegisterFormField,
  type RegisterFormState,
  type LoginFormField,
  type LoginFormState,
} from '@/schema/userSchema'
import { validateFormWithZod, normalizeErrors, type NormalizedErrors } from '@/utils/errorFormatter'
import type { AuthResponse, LoginData, RegisterData, User } from '@/types/UserType'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null)
  const errors = ref<NormalizedErrors<RegisterFormField | LoginFormField>>({})

  /** Helper: get redirect path from query, fallback to default */
  function getRedirectPath(defaultPath: string): string {
    try {
      const route = useRoute()
      const redirectTo = route.query.redirect as string | undefined
      if (redirectTo && redirectTo.startsWith('/') && !redirectTo.startsWith('//')) {
        return redirectTo
      }
    } catch {
      // useRoute() can throw outside component context — safe to ignore
    }
    return defaultPath
  }
  const countries = ref<string[]>([
    'Afghanistan',
    'Albania',
    'Algeria',
    'Andorra',
    'Angola',
    'Antigua and Barbuda',
    'Argentina',
    'Armenia',
    'Australia',
    'Austria',
    'Azerbaijan',
    'Bahamas',
    'Bahrain',
    'Bangladesh',
    'Barbados',
    'Belarus',
    'Belgium',
    'Belize',
    'Benin',
    'Bhutan',
    'Bolivia',
    'Bosnia and Herzegovina',
    'Botswana',
    'Brazil',
    'Brunei',
    'Bulgaria',
    'Burkina Faso',
    'Burundi',
    'Cabo Verde',
    'Cambodia',
    'Cameroon',
    'Canada',
    'Central African Republic',
    'Chad',
    'Chile',
    'China',
    'Colombia',
    'Comoros',
    'Congo (Congo-Brazzaville)',
    'Costa Rica',
    'Croatia',
    'Cuba',
    'Cyprus',
    'Czechia (Czech Republic)',
    'Democratic Republic of the Congo',
    'Denmark',
    'Djibouti',
    'Dominica',
    'Dominican Republic',
    'Ecuador',
    'Egypt',
    'El Salvador',
    'Equatorial Guinea',
    'Eritrea',
    'Estonia',
    'Eswatini',
    'Ethiopia',
    'Fiji',
    'Finland',
    'France',
    'Gabon',
    'Gambia',
    'Georgia',
    'Germany',
    'Ghana',
    'Greece',
    'Grenada',
    'Guatemala',
    'Guinea',
    'Guinea-Bissau',
    'Guyana',
    'Haiti',
    'Holy See',
    'Honduras',
    'Hungary',
    'Iceland',
    'India',
    'Indonesia',
    'Iran',
    'Iraq',
    'Ireland',
    'Israel',
    'Italy',
    'Ivory Coast',
    'Jamaica',
    'Japan',
    'Jordan',
    'Kazakhstan',
    'Kenya',
    'Kiribati',
    'Kuwait',
    'Kyrgyzstan',
    'Laos',
    'Latvia',
    'Lebanon',
    'Lesotho',
    'Liberia',
    'Libya',
    'Liechtenstein',
    'Lithuania',
    'Luxembourg',
    'Madagascar',
    'Malawi',
    'Malaysia',
    'Maldives',
    'Mali',
    'Malta',
    'Marshall Islands',
    'Mauritania',
    'Mauritius',
    'Mexico',
    'Micronesia',
    'Moldova',
    'Monaco',
    'Mongolia',
    'Montenegro',
    'Morocco',
    'Mozambique',
    'Myanmar (Burma)',
    'Namibia',
    'Nauru',
    'Nepal',
    'Netherlands',
    'New Zealand',
    'Nicaragua',
    'Niger',
    'Nigeria',
    'North Korea',
    'North Macedonia',
    'Norway',
    'Oman',
    'Pakistan',
    'Palau',
    'Palestine State',
    'Panama',
    'Papua New Guinea',
    'Paraguay',
    'Peru',
    'Philippines',
    'Poland',
    'Portugal',
    'Qatar',
    'Romania',
    'Russia',
    'Rwanda',
    'Saint Kitts and Nevis',
    'Saint Lucia',
    'Saint Vincent and the Grenadines',
    'Samoa',
    'San Marino',
    'Sao Tome and Principe',
    'Saudi Arabia',
    'Senegal',
    'Serbia',
    'Seychelles',
    'Sierra Leone',
    'Singapore',
    'Slovakia',
    'Slovenia',
    'Solomon Islands',
    'Somalia',
    'South Africa',
    'South Korea',
    'South Sudan',
    'Spain',
    'Sri Lanka',
    'Sudan',
    'Suriname',
    'Sweden',
    'Switzerland',
    'Syria',
    'Tajikistan',
    'Tanzania',
    'Thailand',
    'Timor-Leste',
    'Togo',
    'Tonga',
    'Trinidad and Tobago',
    'Tunisia',
    'Turkey',
    'Turkmenistan',
    'Tuvalu',
    'Uganda',
    'Ukraine',
    'United Arab Emirates',
    'United Kingdom',
    'United States',
    'Uruguay',
    'Uzbekistan',
    'Vanuatu',
    'Venezuela',
    'Vietnam',
    'Yemen',
    'Zambia',
    'Zimbabwe',
  ])

  const countryCodes = ref<Array<{ code: string; country: string; dialCode: string }>>([
    { code: 'AF', country: 'Afghanistan', dialCode: '+93' },
    { code: 'AL', country: 'Albania', dialCode: '+355' },
    { code: 'DZ', country: 'Algeria', dialCode: '+213' },
    { code: 'AD', country: 'Andorra', dialCode: '+376' },
    { code: 'AO', country: 'Angola', dialCode: '+244' },
    { code: 'AG', country: 'Antigua and Barbuda', dialCode: '+1-268' },
    { code: 'AR', country: 'Argentina', dialCode: '+54' },
    { code: 'AM', country: 'Armenia', dialCode: '+374' },
    { code: 'AU', country: 'Australia', dialCode: '+61' },
    { code: 'AT', country: 'Austria', dialCode: '+43' },
    { code: 'AZ', country: 'Azerbaijan', dialCode: '+994' },
    { code: 'BS', country: 'Bahamas', dialCode: '+1-242' },
    { code: 'BH', country: 'Bahrain', dialCode: '+973' },
    { code: 'BD', country: 'Bangladesh', dialCode: '+880' },
    { code: 'BB', country: 'Barbados', dialCode: '+1-246' },
    { code: 'BY', country: 'Belarus', dialCode: '+375' },
    { code: 'BE', country: 'Belgium', dialCode: '+32' },
    { code: 'BZ', country: 'Belize', dialCode: '+501' },
    { code: 'BJ', country: 'Benin', dialCode: '+229' },
    { code: 'BT', country: 'Bhutan', dialCode: '+975' },
    { code: 'BO', country: 'Bolivia', dialCode: '+591' },
    { code: 'BA', country: 'Bosnia and Herzegovina', dialCode: '+387' },
    { code: 'BW', country: 'Botswana', dialCode: '+267' },
    { code: 'BR', country: 'Brazil', dialCode: '+55' },
    { code: 'BN', country: 'Brunei', dialCode: '+673' },
    { code: 'BG', country: 'Bulgaria', dialCode: '+359' },
    { code: 'BF', country: 'Burkina Faso', dialCode: '+226' },
    { code: 'BI', country: 'Burundi', dialCode: '+257' },
    { code: 'CV', country: 'Cabo Verde', dialCode: '+238' },
    { code: 'KH', country: 'Cambodia', dialCode: '+855' },
    { code: 'CM', country: 'Cameroon', dialCode: '+237' },
    { code: 'CA', country: 'Canada', dialCode: '+1' },
    { code: 'CF', country: 'Central African Republic', dialCode: '+236' },
    { code: 'TD', country: 'Chad', dialCode: '+235' },
    { code: 'CL', country: 'Chile', dialCode: '+56' },
    { code: 'CN', country: 'China', dialCode: '+86' },
    { code: 'CO', country: 'Colombia', dialCode: '+57' },
    { code: 'KM', country: 'Comoros', dialCode: '+269' },
    { code: 'CG', country: 'Congo (Congo-Brazzaville)', dialCode: '+242' },
    { code: 'CR', country: 'Costa Rica', dialCode: '+506' },
    { code: 'HR', country: 'Croatia', dialCode: '+385' },
    { code: 'CU', country: 'Cuba', dialCode: '+53' },
    { code: 'CY', country: 'Cyprus', dialCode: '+357' },
    { code: 'CZ', country: 'Czechia (Czech Republic)', dialCode: '+420' },
    { code: 'CD', country: 'Democratic Republic of the Congo', dialCode: '+243' },
    { code: 'DK', country: 'Denmark', dialCode: '+45' },
    { code: 'DJ', country: 'Djibouti', dialCode: '+253' },
    { code: 'DM', country: 'Dominica', dialCode: '+1-767' },
    { code: 'DO', country: 'Dominican Republic', dialCode: '+1-809' },
    { code: 'EC', country: 'Ecuador', dialCode: '+593' },
    { code: 'EG', country: 'Egypt', dialCode: '+20' },
    { code: 'SV', country: 'El Salvador', dialCode: '+503' },
    { code: 'GQ', country: 'Equatorial Guinea', dialCode: '+240' },
    { code: 'ER', country: 'Eritrea', dialCode: '+291' },
    { code: 'EE', country: 'Estonia', dialCode: '+372' },
    { code: 'SZ', country: 'Eswatini', dialCode: '+268' },
    { code: 'ET', country: 'Ethiopia', dialCode: '+251' },
    { code: 'FJ', country: 'Fiji', dialCode: '+679' },
    { code: 'FI', country: 'Finland', dialCode: '+358' },
    { code: 'FR', country: 'France', dialCode: '+33' },
    { code: 'GA', country: 'Gabon', dialCode: '+241' },
    { code: 'GM', country: 'Gambia', dialCode: '+220' },
    { code: 'GE', country: 'Georgia', dialCode: '+995' },
    { code: 'DE', country: 'Germany', dialCode: '+49' },
    { code: 'GH', country: 'Ghana', dialCode: '+233' },
    { code: 'GR', country: 'Greece', dialCode: '+30' },
    { code: 'GD', country: 'Grenada', dialCode: '+1-473' },
    { code: 'GT', country: 'Guatemala', dialCode: '+502' },
    { code: 'GN', country: 'Guinea', dialCode: '+224' },
    { code: 'GW', country: 'Guinea-Bissau', dialCode: '+245' },
    { code: 'GY', country: 'Guyana', dialCode: '+592' },
    { code: 'HT', country: 'Haiti', dialCode: '+509' },
    { code: 'VA', country: 'Holy See', dialCode: '+379' },
    { code: 'HN', country: 'Honduras', dialCode: '+504' },
    { code: 'HU', country: 'Hungary', dialCode: '+36' },
    { code: 'IS', country: 'Iceland', dialCode: '+354' },
    { code: 'IN', country: 'India', dialCode: '+91' },
    { code: 'ID', country: 'Indonesia', dialCode: '+62' },
    { code: 'IR', country: 'Iran', dialCode: '+98' },
    { code: 'IQ', country: 'Iraq', dialCode: '+964' },
    { code: 'IE', country: 'Ireland', dialCode: '+353' },
    { code: 'IL', country: 'Israel', dialCode: '+972' },
    { code: 'IT', country: 'Italy', dialCode: '+39' },
    { code: 'CI', country: 'Ivory Coast', dialCode: '+225' },
    { code: 'JM', country: 'Jamaica', dialCode: '+1-876' },
    { code: 'JP', country: 'Japan', dialCode: '+81' },
    { code: 'JO', country: 'Jordan', dialCode: '+962' },
    { code: 'KZ', country: 'Kazakhstan', dialCode: '+7' },
    { code: 'KE', country: 'Kenya', dialCode: '+254' },
    { code: 'KI', country: 'Kiribati', dialCode: '+686' },
    { code: 'KW', country: 'Kuwait', dialCode: '+965' },
    { code: 'KG', country: 'Kyrgyzstan', dialCode: '+996' },
    { code: 'LA', country: 'Laos', dialCode: '+856' },
    { code: 'LV', country: 'Latvia', dialCode: '+371' },
    { code: 'LB', country: 'Lebanon', dialCode: '+961' },
    { code: 'LS', country: 'Lesotho', dialCode: '+266' },
    { code: 'LR', country: 'Liberia', dialCode: '+231' },
    { code: 'LY', country: 'Libya', dialCode: '+218' },
    { code: 'LI', country: 'Liechtenstein', dialCode: '+423' },
    { code: 'LT', country: 'Lithuania', dialCode: '+370' },
    { code: 'LU', country: 'Luxembourg', dialCode: '+352' },
    { code: 'MG', country: 'Madagascar', dialCode: '+261' },
    { code: 'MW', country: 'Malawi', dialCode: '+265' },
    { code: 'MY', country: 'Malaysia', dialCode: '+60' },
    { code: 'MV', country: 'Maldives', dialCode: '+960' },
    { code: 'ML', country: 'Mali', dialCode: '+223' },
    { code: 'MT', country: 'Malta', dialCode: '+356' },
    { code: 'MH', country: 'Marshall Islands', dialCode: '+692' },
    { code: 'MR', country: 'Mauritania', dialCode: '+222' },
    { code: 'MU', country: 'Mauritius', dialCode: '+230' },
    { code: 'MX', country: 'Mexico', dialCode: '+52' },
    { code: 'FM', country: 'Micronesia', dialCode: '+691' },
    { code: 'MD', country: 'Moldova', dialCode: '+373' },
    { code: 'MC', country: 'Monaco', dialCode: '+377' },
    { code: 'MN', country: 'Mongolia', dialCode: '+976' },
    { code: 'ME', country: 'Montenegro', dialCode: '+382' },
    { code: 'MA', country: 'Morocco', dialCode: '+212' },
    { code: 'MZ', country: 'Mozambique', dialCode: '+258' },
    { code: 'MM', country: 'Myanmar (Burma)', dialCode: '+95' },
    { code: 'NA', country: 'Namibia', dialCode: '+264' },
    { code: 'NR', country: 'Nauru', dialCode: '+674' },
    { code: 'NP', country: 'Nepal', dialCode: '+977' },
    { code: 'NL', country: 'Netherlands', dialCode: '+31' },
    { code: 'NZ', country: 'New Zealand', dialCode: '+64' },
    { code: 'NI', country: 'Nicaragua', dialCode: '+505' },
    { code: 'NE', country: 'Niger', dialCode: '+227' },
    { code: 'NG', country: 'Nigeria', dialCode: '+234' },
    { code: 'KP', country: 'North Korea', dialCode: '+850' },
    { code: 'MK', country: 'North Macedonia', dialCode: '+389' },
    { code: 'NO', country: 'Norway', dialCode: '+47' },
    { code: 'OM', country: 'Oman', dialCode: '+968' },
    { code: 'PK', country: 'Pakistan', dialCode: '+92' },
    { code: 'PW', country: 'Palau', dialCode: '+680' },
    { code: 'PS', country: 'Palestine State', dialCode: '+970' },
    { code: 'PA', country: 'Panama', dialCode: '+507' },
    { code: 'PG', country: 'Papua New Guinea', dialCode: '+675' },
    { code: 'PY', country: 'Paraguay', dialCode: '+595' },
    { code: 'PE', country: 'Peru', dialCode: '+51' },
    { code: 'PH', country: 'Philippines', dialCode: '+63' },
    { code: 'PL', country: 'Poland', dialCode: '+48' },
    { code: 'PT', country: 'Portugal', dialCode: '+351' },
    { code: 'QA', country: 'Qatar', dialCode: '+974' },
    { code: 'RO', country: 'Romania', dialCode: '+40' },
    { code: 'RU', country: 'Russia', dialCode: '+7' },
    { code: 'RW', country: 'Rwanda', dialCode: '+250' },
    { code: 'KN', country: 'Saint Kitts and Nevis', dialCode: '+1-869' },
    { code: 'LC', country: 'Saint Lucia', dialCode: '+1-758' },
    { code: 'VC', country: 'Saint Vincent and the Grenadines', dialCode: '+1-784' },
    { code: 'WS', country: 'Samoa', dialCode: '+685' },
    { code: 'SM', country: 'San Marino', dialCode: '+378' },
    { code: 'ST', country: 'Sao Tome and Principe', dialCode: '+239' },
    { code: 'SA', country: 'Saudi Arabia', dialCode: '+966' },
    { code: 'SN', country: 'Senegal', dialCode: '+221' },
    { code: 'RS', country: 'Serbia', dialCode: '+381' },
    { code: 'SC', country: 'Seychelles', dialCode: '+248' },
    { code: 'SL', country: 'Sierra Leone', dialCode: '+232' },
    { code: 'SG', country: 'Singapore', dialCode: '+65' },
    { code: 'SK', country: 'Slovakia', dialCode: '+421' },
    { code: 'SI', country: 'Slovenia', dialCode: '+386' },
    { code: 'SB', country: 'Solomon Islands', dialCode: '+677' },
    { code: 'SO', country: 'Somalia', dialCode: '+252' },
    { code: 'ZA', country: 'South Africa', dialCode: '+27' },
    { code: 'KR', country: 'South Korea', dialCode: '+82' },
    { code: 'SS', country: 'South Sudan', dialCode: '+211' },
    { code: 'ES', country: 'Spain', dialCode: '+34' },
    { code: 'LK', country: 'Sri Lanka', dialCode: '+94' },
    { code: 'SD', country: 'Sudan', dialCode: '+249' },
    { code: 'SR', country: 'Suriname', dialCode: '+597' },
    { code: 'SE', country: 'Sweden', dialCode: '+46' },
    { code: 'CH', country: 'Switzerland', dialCode: '+41' },
    { code: 'SY', country: 'Syria', dialCode: '+963' },
    { code: 'TJ', country: 'Tajikistan', dialCode: '+992' },
    { code: 'TZ', country: 'Tanzania', dialCode: '+255' },
    { code: 'TH', country: 'Thailand', dialCode: '+66' },
    { code: 'TL', country: 'Timor-Leste', dialCode: '+670' },
    { code: 'TG', country: 'Togo', dialCode: '+228' },
    { code: 'TO', country: 'Tonga', dialCode: '+676' },
    { code: 'TT', country: 'Trinidad and Tobago', dialCode: '+1-868' },
    { code: 'TN', country: 'Tunisia', dialCode: '+216' },
    { code: 'TR', country: 'Turkey', dialCode: '+90' },
    { code: 'TM', country: 'Turkmenistan', dialCode: '+993' },
    { code: 'TV', country: 'Tuvalu', dialCode: '+688' },
    { code: 'UG', country: 'Uganda', dialCode: '+256' },
    { code: 'UA', country: 'Ukraine', dialCode: '+380' },
    { code: 'AE', country: 'United Arab Emirates', dialCode: '+971' },
    { code: 'GB', country: 'United Kingdom', dialCode: '+44' },
    { code: 'US', country: 'United States', dialCode: '+1' },
    { code: 'UY', country: 'Uruguay', dialCode: '+598' },
    { code: 'UZ', country: 'Uzbekistan', dialCode: '+998' },
    { code: 'VU', country: 'Vanuatu', dialCode: '+678' },
    { code: 'VE', country: 'Venezuela', dialCode: '+58' },
    { code: 'VN', country: 'Vietnam', dialCode: '+84' },
    { code: 'YE', country: 'Yemen', dialCode: '+967' },
    { code: 'ZM', country: 'Zambia', dialCode: '+260' },
    { code: 'ZW', country: 'Zimbabwe', dialCode: '+263' },
  ])

  // TanStack useForm for register
  const registerForm = useForm({
    defaultValues: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      country: '',
      country_code: '',
      phone_number: '',
      referral_code: '',
    } as RegisterFormState,
    validators: {
      onChange: useRegisterSchema.value,
    },
  })

  // TanStack useForm for login
  const loginForm = useForm({
    defaultValues: {
      email: '',
      password: '',
    } as LoginFormState,
    validators: {
      onChange: useLoginSchema.value,
    },
  })

  function clearError(field: RegisterFormField | LoginFormField) {
    if (errors.value && errors.value[field]) {
      delete errors.value[field]
    }
  }

  const resetForm = () => {
    registerForm.reset()
    errors.value = {}
  }

  const resetLoginForm = () => {
    loginForm.reset()
    errors.value = {}
  }

  // Getters
  const isAuthenticated = computed(() => !!user.value && !!Cookies.get('access_token'))
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isUser = computed(() => user.value?.role === 'user')

  // Actions
  const setUserData = (userData: User) => {
    user.value = userData
    localStorage.setItem('auth', JSON.stringify(userData))
  }

  const initializeAuth = () => {
    const storedUser = localStorage.getItem('auth')
    if (storedUser) {
      try {
        const userData = JSON.parse(storedUser)
        user.value = userData
      } catch (e) {
        console.error('Error parsing stored user:', e)
        localStorage.removeItem('auth')
      }
    }
  }

  const { mutate: login, isPending: isLoggingIn } = useMutation({
    mutationFn: async (data: LoginData) => {
      const response = await axiosInstance.post<AuthResponse>('/login', data)
      return response.data
    },
    onSuccess: (data) => {
      // Store token
      Cookies.set('access_token', data.token, { expires: 7 })

      // Store user data
      if (data.user) {
        setUserData(data.user)
      }

      // Redirect based on user role
      if (data.user?.role === 'admin') {
        router.push('/admin/dashboard')
      } else {
        router.push(getRedirectPath('/user-home'))
      }
      toast.success('Signed in successfully')
    },
    onError: (error: any) => {
      if (error?.response?.data?.errors) {
        errors.value = normalizeErrors<LoginFormField>(error.response.data.errors)
      } else {
        toast.error(error.response?.data?.message)
      }
    },
  })

  const { mutate: register, isPending: isRegistering } = useMutation({
    mutationFn: async (data: RegisterData) => {
      const response = await axiosInstance.post<AuthResponse>('/register', data)
      return response.data
    },
    onSuccess: (data) => {
      // Store token
      Cookies.set('access_token', data.token, { expires: 7 })

      // Store user data
      setUserData(data.user)

      // Redirect after registration — honour ?redirect= if present
      router.push(getRedirectPath('/user-home'))
      toast.success('Account created successfully')
    },
    onError: (error: any) => {
      if (error.response?.status === 422) {
        errors.value = normalizeErrors<RegisterFormField>(error.response.data.errors)
        toast.error('Please fix the validation errors')
      } else {
        toast.error(error.response?.data?.message)
      }
    },
  })

  const { mutate: logout, isPending: isLoggingOut } = useMutation({
    mutationFn: async () => {
      await axiosInstance.post('/logout')
    },
    onSuccess: () => {
      // Clear state
      user.value = null

      // Clear local storage and cookies
      localStorage.removeItem('auth')
      Cookies.remove('access_token')
      Cookies.remove('lang')

      // Redirect to login
      router.push('/login')
      toast.success('Logged out successfully')
    },
    onError: (error: any) => {
      console.error('Logout error:', error)
      // Still clear local data even if API call fails
      user.value = null
      localStorage.removeItem('auth')
      Cookies.remove('access_token')
      router.push('/login')
    },
  })

  const isLoading = computed(() => isLoggingIn.value || isRegistering.value || isLoggingOut.value)

  const handleSubmit = () => {
    errors.value = {}
    const error = validateFormWithZod<RegisterFormField>(
      useRegisterSchema.value,
      registerForm.state.values,
    )
    if (error) {
      errors.value = error
      return
    }

    register(registerForm.state.values as RegisterData)
  }

  const handleLogin = () => {
    errors.value = {}
    const error = validateFormWithZod<LoginFormField>(useLoginSchema.value, loginForm.state.values)
    if (error) {
      errors.value = error
      return
    }

    login(loginForm.state.values as LoginData)
  }

  // Initialize auth on store creation
  initializeAuth()

  return {
    // State
    user,
    isLoading,
    errors,
    registerForm,
    loginForm,

    // Getters
    isAuthenticated,
    isAdmin,
    isUser,
    countries,
    countryCodes,

    // Actions
    register,
    login,
    logout,
    handleSubmit,
    handleLogin,
    clearError,
    setUserData,
    resetForm,
    resetLoginForm,
  }
})
