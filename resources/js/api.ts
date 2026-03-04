import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
  },
});

const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  ?.getAttribute('content');

if (csrfToken) {
  api.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

export default api;
