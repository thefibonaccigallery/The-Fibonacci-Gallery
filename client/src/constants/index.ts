export const API_URL =
  import.meta.env.VITE_API_URL ||
  (import.meta.env.DEV
    ? 'http://127.0.0.1:8001/api'
    : 'https://the-fibonacci-gallery-back.clearsightinitiative.org/api')

