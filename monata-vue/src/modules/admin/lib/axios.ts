import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api/admin',
  withCredentials: true,
  withXSRFToken: true,
})

const csrf = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
})

export { api, csrf }
