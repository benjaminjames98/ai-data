function _open(id) {
  document.getElementById(id).style.display = 'block';
}

function _close(id) {
  document.getElementById(id).style.display = 'none';
}

function formatNumLength(input, length) {
  if (length < 0) length = 0;
  return (Array(length + 1).join('0') + input).slice(-length);
}

function jsonPost(url, obj, fun) {
  let xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {
      let obj = JSON.parse(this.responseText);
      if (obj.a === 0) {
        alert('An error has been detected. Please try again.');
        return;
      }
      fun(obj);
    }
  };

  let par = 't=' + Math.random();
  par += '&user=' + JSON.stringify(obj);
  xmlhttp.open('POST', url, true);
  xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xmlhttp.send(par);
}

function el(a) { return document.getElementById(a); }

function loadUsernames(url, select_el) {
  jsonPost(url, {q: 'read_usernames'}, (obj) => {
    let reducer = (acc, cur) => acc + `<option value="${cur}">${cur}</option>`;
    select_el.innerHTML = obj.usernames.reduce(reducer, '');
  });
}