import axios from 'axios';

const token = document.head.querySelector('meta[name="csrf-token"]');

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.withCredentials = true;

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
