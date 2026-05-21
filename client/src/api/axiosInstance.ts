import axios from 'axios';
import Cookies from 'js-cookie';
import { API_URL } from '@/constants';

const axiosInstance = axios.create({
  baseURL: API_URL,
});

axiosInstance.interceptors.request.use(
  (config) => {
    const token = Cookies.get('access_token');
    const currentLanguage = Cookies.get('lang');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    if (currentLanguage) {
      config.headers.lang = currentLanguage;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && (error.response.status === 401 || error.response.status === 403)) {
      localStorage.removeItem('auth');
      Cookies.remove('access_token');
      Cookies.remove('lang');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
