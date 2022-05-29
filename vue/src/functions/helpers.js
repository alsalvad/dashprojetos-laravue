export const makeForm = (object) => {
  let form = new FormData();
  for (var key in object) {
    form.append(`${key}`, object[key])
  }
  return form;
}

export const inArray = (needle, array) => {
  let res = false;
  if(!needle) return false;
  if(Array.isArray(needle)){
    needle.forEach(item => {
      if(array.includes(item)) return res = true;
    });
    return res;
  }
  return array.includes(needle);
}

export const urlGet = (param) => {
  return new URL(location.href).searchParams.get(param);
}


export const mask = {
	dateBR(data, setHora=false){
		let date = new Date(data);
		let dia = ('0' + (date.getDate())).slice(-2);
		let mes = ('0' + (date.getMonth() + 1)).slice(-2);
		let ano = date.getFullYear();
		let hora = '';
		if(setHora){
			let h = ('0' + date.getHours()).slice(-2);
			let m = ('0' + date.getMinutes()).slice(-2);
			hora = ` - ${h}:${m}`;
		}

		return `${dia}/${mes}/${ano}${hora}`;
	}
}

/** Cookies */
export const getCookie = (cname) => {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

export const setCookie = (nome, valor, dias) => {
  var d = new Date();
  d.setTime(d.getTime() + (dias*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = nome + "=" + valor + ";" + expires + ";path=/";
}

export const deleteCookie = (name) => {
  document.cookie = name +'=false; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

export default {};
