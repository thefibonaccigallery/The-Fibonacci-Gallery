import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import router from '@/router';
import Cookies from 'js-cookie';
import axiosInstance from '@/api/axiosInstance';
import { useMutation } from '@tanstack/vue-query';
import { useForm } from '@tanstack/vue-form';
import { toast } from 'vue-sonner';
import {
  useRegisterSchema,
  useLoginSchema,
  type RegisterFormField,
  type RegisterFormState,
  type LoginFormField,
  type LoginFormState,
} from '@/schema/userSchema';
import {
  validateFormWithZod,
  normalizeErrors,
  type NormalizedErrors,
} from '@/utils/errorFormatter';
import type { AuthResponse, LoginData, RegisterData, User } from '@/types/UserType';


export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<User | null>(null);
  const errors = ref<NormalizedErrors<RegisterFormField | LoginFormField>>({});

  // TanStack useForm for register
  const registerForm = useForm({
    defaultValues: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      country: '',
    } as RegisterFormState,
    validators: {
      onChange: useRegisterSchema.value,
    },
  });

  // TanStack useForm for login
  const loginForm = useForm({
    defaultValues: {
      email: '',
      password: '',
    } as LoginFormState,
    validators: {
      onChange: useLoginSchema.value,
    },
  });

  function clearError(field: RegisterFormField | LoginFormField) {
    if (errors.value && errors.value[field]) {
      delete errors.value[field];
    }
  }

  const resetForm = () => {
    registerForm.reset();
    errors.value = {};
  };

  const resetLoginForm = () => {
    loginForm.reset();
    errors.value = {};
  };

  // Getters
  const isAuthenticated = computed(() => !!user.value && !!Cookies.get('access_token'));
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isUser = computed(() => user.value?.role === 'user');

  // Actions
  const setUserData = (userData: User) => {
    user.value = userData;
    localStorage.setItem('auth', JSON.stringify(userData));
  };

  const initializeAuth = () => {
    const storedUser = localStorage.getItem('auth');
    if (storedUser) {
      try {
        const userData = JSON.parse(storedUser);
        user.value = userData;
      } catch (e) {
        console.error('Error parsing stored user:', e);
        localStorage.removeItem('auth');
      }
    }
  };

  const { mutate: login, isPending: isLoggingIn } = useMutation({
    mutationFn: async (data: LoginData) => {
      const response = await axiosInstance.post<AuthResponse>('/login', data);
      return response.data;
    },
    onSuccess: (data) => {
      // Store token
      Cookies.set('access_token', data.token, { expires: 7 });

      // Store user data
      if (data.user) {
        setUserData(data.user);
      }

      // Redirect based on user role
      if (data.user?.role === 'admin') {
        router.push('/admin-home');
      } else {
        router.push('/user-home');
      }
      toast.success('Signed in successfully');
    },
    onError: (error: any) => {
      if (error?.response?.data?.errors) {
        errors.value = normalizeErrors<LoginFormField>(error.response.data.errors);
      } else {
        toast.error(error.response?.data?.message);
      }
    }
  });

  const { mutate: register, isPending: isRegistering } = useMutation({
    mutationFn: async (data: RegisterData) => {
      const response = await axiosInstance.post<AuthResponse>('/register', data);
      return response.data;
    },
    onSuccess: (data) => {
      // Store token
      Cookies.set('access_token', data.token, { expires: 7 });

      // Store user data
      setUserData(data.user);

      // Always redirect to user-home after registration
      router.push('/user-home');
      toast.success('Account created successfully');
    },
    onError: (error: any) => {
      if (error.response?.status === 422) {
        errors.value = normalizeErrors<RegisterFormField>(error.response.data.errors);
        toast.error('Please fix the validation errors');
      } else {
        toast.error(error.response?.data?.message);
      }
    }
  });

  const { mutate: logout, isPending: isLoggingOut } = useMutation({
    mutationFn: async () => {
      await axiosInstance.post('/logout');
    },
    onSuccess: () => {
      // Clear state
      user.value = null;

      // Clear local storage and cookies
      localStorage.removeItem('auth');
      Cookies.remove('access_token');
      Cookies.remove('lang');

      // Redirect to login
      router.push('/login');
      toast.success('Logged out successfully');
    },
    onError: (error: any) => {
      console.error('Logout error:', error);
      // Still clear local data even if API call fails
      user.value = null;
      localStorage.removeItem('auth');
      Cookies.remove('access_token');
      router.push('/login');
    }
  });

  const isLoading = computed(() => isLoggingIn.value || isRegistering.value || isLoggingOut.value);

  const handleSubmit = () => {
    errors.value = {};
    const error = validateFormWithZod<RegisterFormField>(useRegisterSchema.value, registerForm.state.values);
    if (error) {
      errors.value = error;
      return;
    }

    register(registerForm.state.values);
  };

  const handleLogin = () => {
    errors.value = {};
    const error = validateFormWithZod<LoginFormField>(useLoginSchema.value, loginForm.state.values);
    if (error) {
      errors.value = error;
      return;
    }

    login(loginForm.state.values);
  };

  // Initialize auth on store creation
  initializeAuth();

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
  };
});
