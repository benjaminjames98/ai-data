var _URL = '../resources/ajax_backend/resources_backend.php?';

function _open(id) {
  document.getElementById(id).style.display = 'block';
}

function _close(id) {
  document.getElementById(id).style.display = 'none';
}

function getRequest(q, fun, arr) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {
      var obj = JSON.parse(this.responseText);
      if (obj.a === 0) {
        alert('An error has been detected. Please try again.');
        return;
      }
      fun(obj);
    }
  };

  var url = _URL;
  url += 't=' + Math.random();
  url += '&q=' + q;
  if (arr != null)
    url += '&arr=' + arr.join('||');
  xmlhttp.open('GET', url, true);
  xmlhttp.send();
}

function postRequest(q, fun, arr) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {
      var obj = JSON.parse(this.responseText);
      if (obj.a === 0) {
        alert('An error has been detected. Please try again.');
        return;
      }
      fun(obj);
    }
  };

  if (arr === undefined) return;

  var par = 't=' + Math.random();
  par += '&q=' + q;
  par += '&arr=' + arr.join('||', arr);
  xmlhttp.open('POST', _URL, true);
  xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xmlhttp.send(par);
}

function download(filename, text) {
  var element = document.createElement('a');
  element.setAttribute('href',
    'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
  element.setAttribute('download', filename);

  element.style.display = 'none';
  document.body.appendChild(element);

  element.click();

  document.body.removeChild(element);
}

/*---- Miscellaneous ----*/

window.onload = function() {
  initPage();

  function initPage() {
    readBooks();
    readPrograms();
  }
};

function readBooks() {
  getRequest('read_books', function(obj) {
    document.getElementById('div_bok_switch').innerHTML = obj.html;
  });
}

function createBook() {
  var nam = document.getElementById('ipt_bok_nam').value;
  var des = document.getElementById('ipt_bok_des').value;
  var prc = document.getElementById('ipt_bok_prc').value;
  if (nam.trim() === '' || des.trim() === '' || prc.trim() === '') {
    alert('Looks like you missed one');
    return;
  }
  if (isNaN(prc)) {
    alert('That price should probably be a number');
    return;
  }

  function foo(obj) {
    _close('mdl_bok_crt');
    readBooks();
    prepModal('mdl_bok_red', obj.id);
  }

  postRequest('create_book', foo, [nam, des, prc]);
}

function updateBook(id) {
  var nam = document.getElementById('ipt_bok_nam').value;
  var des = document.getElementById('ipt_bok_des').value;
  var prc = document.getElementById('ipt_bok_prc').value;
  if (nam.trim() === '' || des.trim() === '' || prc.trim() === '') {
    alert('Looks like you missed one');
    return;
  }
  if (isNaN(prc)) {
    alert('That price should probably be a number');
    return;
  }

  function foo(obj) {
    _close('mdl_bok_crt');
    readBooks();
    prepModal('mdl_bok_red', obj.id);
  }

  postRequest('update_book', foo, [id, nam, des, prc]);
}

function updateStock(id) {
  var num = document.getElementById('ipt_stk_num').value;
  if (num.trim() === '') {
    alert('Looks like you missed one');
    return;
  }
  if (isNaN(num)) {
    alert('That stock should probably be a number');
    return;
  }

  function foo(obj) {
    _close('mdl_stk_udt');
    readBooks();
  }

  postRequest('update_stock', foo, [id, num]);
}

function createProgram() {
  var nam = document.getElementById('ipt_pgm_nam').value;
  var des = document.getElementById('ipt_pgm_des').value;
  if (nam.trim() === '' || des.trim() === '') {
    alert('Looks like you missed one');
    return;
  }

  function foo(obj) {
    _close('mdl_pgm_crt');
    readPrograms();
    alert('Success!');
  }

  postRequest('create_program', foo, [nam, des]);
}

function readPrograms() {
  getRequest('read_programs', function(obj) {
    document.getElementById('div_pgm_switch').innerHTML = obj.html;
  });
}

function downloadDocument() {
  var con = document.getElementById('ipt_doc_con').value;
  var dat = document.getElementById('ipt_doc_dat').value;
  var nam = document.getElementById('ipt_doc_nam').value;
  var top = document.getElementById('ipt_doc_ln1').value;
  var ln2 = document.getElementById('ipt_doc_ln2').value;
  if (ln2 != '') top += ', ' + ln2;
  var sub = document.getElementById('ipt_doc_sub').value;
  var pcd = document.getElementById('ipt_doc_pcd').value;
  var stt = document.getElementById('ipt_doc_stt').value;
  var bot = sub + ', ' + stt + ', ' + pcd;
  if (con === '' || dat === '' || nam === '' || top === '' || bot === '') {
    alert('Missed one');
    return;
  }

  con = con.replace(/\n/g, '||');

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {
      var obj = this.responseText;
      download('dispatchDocument.html', obj);
    }
  };

  var url = _URL;
  url += 't=' + Math.random();
  url += '&q=' + 'download_document';
  url += '&pw=' + document.getElementById('ipt_sec').value;
  url += '&con=' + con;
  url += '&dat=' + dat;
  url += '&nam=' + nam;
  url += '&top=' + top;
  url += '&bot=' + bot;
  xmlhttp.open('GET', url, true);
  xmlhttp.send();
}

function prepModal(mdl, id) {
  if (mdl === 'mdl_bok_crt') {
    document.getElementById('ipt_bok_nam').value = '';
    document.getElementById('ipt_bok_des').value = '';
    document.getElementById('ipt_bok_prc').value = '';
    _close('div_bok_udt_switch');
    _open('btn_bok_crt');
    _open(mdl);
  }
  if (mdl === 'mdl_bok_red') {
    function readBook(obj) {
      document.getElementById('txt_bok_nam').innerHTML = obj.nam;
      document.getElementById('txt_bok_des').innerHTML = obj.des;
      document.getElementById('txt_bok_prc').innerHTML = obj.prc;
      _open('mdl_bok_red');
    }

    getRequest('read_book', readBook, [id]);
  }
  if (mdl === 'mdl_bok_udt') {
    function updateBook(obj) {
      document.getElementById('ipt_bok_nam').value = obj.nam;
      document.getElementById('ipt_bok_des').value = obj.des;
      document.getElementById('ipt_bok_prc').value = obj.prc;
      document.getElementById('div_bok_udt_switch').innerHTML = obj.udt;
      _close('btn_bok_crt');
      _open('div_bok_udt_switch');
      _open('mdl_bok_crt');
    }

    getRequest('read_book', updateBook, [id]);
  }
  if (mdl === 'mdl_stk_udt') {
    function updateStock(obj) {
      document.getElementById('ipt_stk_num').value = '';
      document.getElementById('div_stk_udt_switch').innerHTML = obj.udt;
      _open('mdl_stk_udt');
    }

    getRequest('read_stock', updateStock, [id]);
  }
  if (mdl === 'mdl_doc_dld') {
    document.getElementById('ipt_doc_con').value = '';
    document.getElementById('ipt_doc_dat').value = '';
    document.getElementById('ipt_doc_nam').value = '';
    document.getElementById('ipt_doc_ln1').value = '';
    document.getElementById('ipt_doc_ln2').value = '';
    document.getElementById('ipt_doc_sub').value = '';
    document.getElementById('ipt_doc_pcd').value = '';
    document.getElementById('ipt_doc_stt').options[0].selected = 'selected';
    _open(mdl);
  }
  if (mdl === 'mdl_pgm_crt') {
    document.getElementById('ipt_pgm_nam').value = '';
    document.getElementById('ipt_pgm_des').value = '';
    _open(mdl);
  }
}