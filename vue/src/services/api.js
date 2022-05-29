import axios from "axios";
import { getCookie } from '@/functions/helpers';
import { useToast } from 'vue-toastification';
const toast = useToast();

let token = getCookie('dashboardtoken');

const _api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/',
  headers: {
    'dashboardtoken': token,
  },
});

async function _process(metodo, url, data){
  let r = _api[metodo](url, data);
  r.then(res => {
    let _toast = res.data.toast;
    if(_toast)
      toast[_toast.type](_toast.message);
  })
  .catch(res => {
    console.log('erro:', res);
  });
  return r;
}

function _get(url, data=null){
  return _process('get', url, data);
}
function _post(url, data=null){
  return _process('post', url, data);
}
function _put(url, data=null){
  return _process('put', url, data);
}
function _delete(url, data=null){
  return _process('delete', url, data);
}

const api = {
  get: _get,
  post: _post,
  put: _put,
  delete: _delete
}

export default api;