var _URL = '../resources/ajax_backend/education_backend.php?';

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
  par += '&arr=' + arr.join('||');
  xmlhttp.open('POST', _URL, true);
  xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xmlhttp.send(par);
}

/*---- Miscellaneous ----*/

window.onload = function() {
  initPage();

  function initPage() {
    readStudents();
    readCohorts();
  }
};

function readStudents() {
  getRequest('read_students', function(obj) {
    document.getElementById('div_sdt_switch').innerHTML = obj.html;
  });
}

function createStudent() {
  var nam = document.getElementById('ipt_sdt_crt_nam').value;
  var pye = document.getElementById('ipt_sdt_crt_pye').value;
  if (nam === '') {
    alert('Looks like you missed one');
    return;
  }

  function foo(obj) {
    _close('mdl_sdt_crt');
    readStudents();
    readStudent(obj.id);
  }

  postRequest('create_student', foo, [nam, pye]);
}

function readStudent(id) {
  function redStudent(obj) {
    document.getElementById('ipt_sdt_nam').value = obj.nam;
    document.getElementById('ipt_sdt_not').value = obj.not;
    var pye = document.getElementById('ipt_sdt_pye');
    pye.options[0].selected = 'selected';
    for (var i = 0; i < pye.options.length; i++)
      if (pye.options[i].value === String(obj.pid))
        pye.options[i].selected = 'selected';
    document.getElementById('div_sdt_ref_switch').innerHTML = obj.ref;
    document.getElementById('div_sdt_sav_switch').innerHTML = obj.sav;
    document.getElementById('div_sdt_chg_switch').innerHTML = obj.chg;
    document.getElementById('div_phn_crt_switch').innerHTML = obj.phn_crt;
    document.getElementById('div_eml_crt_switch').innerHTML = obj.eml_crt;
    document.getElementById('div_add_crt_switch').innerHTML = obj.add_crt;

    readContacts(obj.id);
    _close('div_cht');
    _open('div_sdt');
  }

  function readPayees(obj) {
    document.getElementById('ipt_sdt_pye').innerHTML = obj.html;
    document.getElementById('ipt_sdt_pye').options[0].selected = 'selected';

    getRequest('read_student', redStudent, [id]);
  }

  getRequest('read_payees', readPayees);
}

function updateStudentInfo(id) {
  var nam = document.getElementById('ipt_sdt_nam').value;
  var not = document.getElementById('ipt_sdt_not').value;
  if (nam.trim() === '') {
    alert('You should probably have a name for that one.');
    return;
  }
  if (not === null || not === undefined) not = '';

  function foo(obj) {
    alert('success!');
    readStudents();
    readStudent(id);
  }

  postRequest('update_student_info', foo, [id, nam, not]);
}

function updateStudentPayee(id) {
  var pid = document.getElementById('ipt_sdt_pye').value;

  function foo(obj) {
    alert('success!');
    readStudents();
    readStudent(id);
  }

  postRequest('update_student_payee', foo, [id, pid]);
}

function readCohorts() {
  getRequest('read_cohorts', function(obj) {
    document.getElementById('div_cht_switch').innerHTML = obj.html;
  });

}

function createCohort() {
  var nam = document.getElementById('ipt_cht_crt_nam').value;
  var ldr = document.getElementById('ipt_cht_crt_ldr').value;
  var umb = document.getElementById('ipt_cht_crt_umb').value;
  var atv = document.getElementById('ipt_cht_crt_atv').checked;
  var cbk = document.getElementById('ipt_cht_crt_cbk').value;
  var nbk = document.getElementById('ipt_cht_crt_nbk').value;
  var pgm = document.getElementById('ipt_cht_crt_pgm').value;
  if (umb === '' || umb === null || umb === undefined) umb = 0;
  if (nam === '') {
    alert('Looks like you missed the name');
    return;
  } else if (umb === '' || isNaN(umb) || umb < 0 || umb > 100) {
    alert('Check your value for Unlisted Members');
    return;
  }

  function foo(obj) {
    _close('mdl_cht_crt');
    readCohorts();
    readCohort(obj.id);
  }

  postRequest('create_cohort', foo, [nam, ldr, umb, atv, cbk, nbk, pgm]);
}

function readCohort(id) {
  function redCohort(obj) {
    document.getElementById('ipt_cht_nam').value = obj.nam;
    document.getElementById('ipt_cht_not').value = obj.not;
    document.getElementById('ipt_cht_umb').value = obj.umb;
    document.getElementById('ipt_cht_atv').checked = obj.atv;
    var ldr = document.getElementById('ipt_cht_ldr');
    ldr.options[0].selected = 'selected';
    for (var i = 0; i < ldr.options.length; i++)
      if (ldr.options[i].value === String(obj.ldr))
        ldr.options[i].selected = 'selected';
    var cbk = document.getElementById('ipt_cht_cbk');
    cbk.options[0].selected = 'selected';
    for (var i = 0; i < cbk.options.length; i++)
      if (cbk.options[i].value === String(obj.cbk))
        cbk.options[i].selected = 'selected';
    var nbk = document.getElementById('ipt_cht_nbk');
    nbk.options[0].selected = 'selected';
    for (var i = 0; i < nbk.options.length; i++)
      if (nbk.options[i].value === String(obj.nbk))
        nbk.options[i].selected = 'selected';
    var pgm = document.getElementById('ipt_cht_pgm');
    pgm.options[0].selected = 'selected';
    for (var i = 0; i < pgm.options.length; i++)
      if (pgm.options[i].value === String(obj.pgm))
        pgm.options[i].selected = 'selected';
    document.getElementById('div_cht_ref_switch').innerHTML = obj.ref;
    document.getElementById('div_cht_sav_switch').innerHTML = obj.sav;
    document.getElementById('div_cht_not_switch').innerHTML = obj.chg;
    document.getElementById('div_cht_sdt_mdl_switch').innerHTML = obj.sdt;

    readCohortStudents(id);
    _close('div_sdt');
    _open('div_cht');
  }

  function cohortPrep(obj) {
    document.getElementById('ipt_cht_ldr').innerHTML = obj.ldrs;
    document.getElementById('ipt_cht_cbk').innerHTML = obj.boks;
    document.getElementById('ipt_cht_nbk').innerHTML = obj.boks;
    document.getElementById('ipt_cht_pgm').innerHTML = obj.pgms;
    document.getElementById('ipt_cht_sdt_nam').innerHTML = obj.ldrs;
    document.getElementById('ipt_cht_sdt_nam').options[0].selected = 'selected';

    getRequest('read_cohort', redCohort, [id]);
  }

  getRequest('cohort_prep', cohortPrep);
}

function updateCohortInfo(id) {
  var nam = document.getElementById('ipt_cht_nam').value;
  var ldr = document.getElementById('ipt_cht_ldr').value;
  var umb = document.getElementById('ipt_cht_umb').value;
  var atv = document.getElementById('ipt_cht_atv').checked;
  var cbk = document.getElementById('ipt_cht_cbk').value;
  var nbk = document.getElementById('ipt_cht_nbk').value;
  var pgm = document.getElementById('ipt_cht_pgm').value;
  if (umb === '' || umb === null || umb === undefined) umb = 0;
  if (nam === '') {
    alert('Looks like you missed the name');
    return;
  } else if (umb === '' || isNaN(umb) || umb < 0 || umb > 100) {
    alert('Check your value for Unlisted Members');
    return;
  }

  function foo(obj) {
    alert('success!');
    readCohorts();
    readCohort(id);
  }

  postRequest('update_cohort_info', foo,
    [id, nam, ldr, umb, atv, cbk, nbk, pgm]);
}

function updateCohortNote(id) {
  var nam = document.getElementById('ipt_cht_nam').value;
  var not = document.getElementById('ipt_cht_not').value;
  if (nam.trim() === '') {
    alert('You should probably have a name for that one.');
    return;
  }
  if (not === null || not === undefined) not = '';

  function foo(obj) {
    alert('success!');
    readCohorts();
    readCohort(id);
  }

  postRequest('update_cohort_note', foo, [id, nam, not]);
}

function readCohortStudents(id) {
  getRequest('read_cohort_students', function(obj) {
    document.getElementById('div_cht_sdt_switch').innerHTML = obj.html;
  }, [id]);

}

function createCohortStudent(cid) {
  var pid = document.getElementById('ipt_cht_sdt_nam').value;

  function foo(obj) {
    _close('mdl_eml_crt');
    alert('Job Done!');
    readCohortStudents(cid);

    _close('mdl_cht_sdt');
  }

  postRequest('create_cohort_student', foo, [cid, pid]);
}

function deleteCohortStudent(eid, nam, cid) {
  if (!confirm('We\'ll remove ' + nam + ' then?'))
    return;

  function foo(obj) {
    alert('Done!');
    readCohort(cid);
  }

  postRequest('delete_cohort_student', foo, [eid]);
}

function readContacts(id) {
  getRequest('read_contacts', function(obj) {
    document.getElementById('div_ctc_switch').innerHTML = obj.html;
  }, [id]);
}

function createContact(id, type, udt, cid) {
  if (type === 'eml') {
    var eml = document.getElementById('ipt_eml_eml').value;
    if (eml === '') {
      alert('Looks like you missed one');
      return;
    }

    function foo(obj) {
      _close('mdl_eml_crt');
      alert(obj.msg);
      readContacts(id);
    }

    if (udt !== undefined && udt) postRequest('update_contact', foo,
      ['eml', cid, eml]);
    else postRequest('create_contact', foo, ['eml', id, eml]);
  }
  if (type === 'phn') {
    var phn = document.getElementById('ipt_phn_phn').value;
    if (phn === '') {
      alert('Looks like you missed one');
      return;
    }

    function foo(obj) {
      _close('mdl_phn_crt');
      alert(obj.msg);
      readContacts(id);
    }

    if (udt !== undefined && udt) postRequest('update_contact', foo,
      ['phn', cid, phn]);
    else postRequest('create_contact', foo, ['phn', id, phn]);
  }
  if (type === 'add') {
    var ln1 = document.getElementById('ipt_add_ln1').value;
    var ln2 = document.getElementById('ipt_add_ln2').value;
    var sub = document.getElementById('ipt_add_sub').value;
    var stt = document.getElementById('ipt_add_stt').value;
    var pcd = document.getElementById('ipt_add_pcd').value;
    if (ln1 === '' || sub === '' || stt === '' || pcd === '') {
      alert('Looks like you missed one');
      return;
    }

    function foo(obj) {
      _close('mdl_add_crt');
      alert(obj.msg);
      readContacts(id);
    }

    if (udt !== undefined && udt) postRequest('update_contact', foo,
      ['add', cid, ln1, ln2, sub, stt, pcd]);
    else postRequest('create_contact', foo,
      ['add', id, ln1, ln2, sub, stt, pcd]);
  }
}

function updateContact(id, cid, type) {
  createContact(id, type, true, cid);
}

function deleteContact(pid, cid, type, ctc) {
  if (!confirm('We\'ll delete ' + ctc + ' then?'))
    return;

  function foo(obj) {
    alert(obj.msg);
    readContacts(pid);
  }

  postRequest('delete_contact', foo, [type, cid]);
}

function prepModal(mdl, id, type) {
  if (mdl === 'mdl_sdt_crt') {
    function readPayees(obj) {
      document.getElementById('ipt_sdt_crt_pye').innerHTML = obj.html;
      document.getElementById('ipt_sdt_crt_pye').options[0].selected =
        'selected';

      _open(mdl);
    }

    document.getElementById('ipt_sdt_crt_nam').value = '';
    getRequest('read_payees', readPayees);
  }
  if (mdl === 'mdl_cht_crt') {
    function cohortPrep(obj) {
      document.getElementById('ipt_cht_crt_ldr').innerHTML = obj.ldrs;
      document.getElementById('ipt_cht_crt_ldr').options[0].selected =
        'selected';
      document.getElementById('ipt_cht_crt_cbk').innerHTML = obj.boks;
      document.getElementById('ipt_cht_crt_cbk').options[0].selected =
        'selected';
      document.getElementById('ipt_cht_crt_nbk').innerHTML = obj.boks;
      document.getElementById('ipt_cht_crt_nbk').options[0].selected =
        'selected';
      document.getElementById('ipt_cht_crt_pgm').innerHTML = obj.pgms;
      document.getElementById('ipt_cht_crt_pgm').options[0].selected =
        'selected';
      document.getElementById('ipt_cht_crt_nam').value = '';
      document.getElementById('ipt_cht_crt_umb').value = '';
      document.getElementById('ipt_cht_crt_atv').checked = true;

      _open(mdl);
    }

    getRequest('cohort_prep', cohortPrep);
  }
  if (mdl === 'mdl_cht_sdt') {
    document.getElementById('ipt_cht_sdt_nam').options[0].selected = 'selected';
    _open(mdl);
  }
  /*----  Contact Stuff ----*/
  if (mdl === 'mdl_crt_ctc' && type === 'eml') {
    document.getElementById('ipt_eml_eml').value = '';
    _open('div_eml_crt_switch');
    _close('div_eml_udt_switch');
    _open('mdl_eml_crt');
  }
  if (mdl === 'mdl_crt_ctc' && type === 'phn') {
    document.getElementById('ipt_phn_phn').value = '';
    _open('div_phn_crt_switch');
    _close('div_phn_udt_switch');
    _open('mdl_phn_crt');
  }
  if (mdl === 'mdl_crt_ctc' && type === 'add') {
    document.getElementById('ipt_add_ln1').value = '';
    document.getElementById('ipt_add_ln2').value = '';
    document.getElementById('ipt_add_sub').value = '';
    document.getElementById('ipt_add_pcd').value = '';
    document.getElementById('ipt_add_stt').options[0].selected = 'selected';
    _open('div_add_crt_switch');
    _close('div_add_udt_switch');
    _open('mdl_add_crt');
  }
  if (mdl === 'mdl_upt_ctc' && type === 'eml') {
    function updateEmail(obj) {
      document.getElementById('ipt_eml_eml').value = obj.v0;
      document.getElementById('div_eml_udt_switch').innerHTML = obj.udt;
      _close('div_eml_crt_switch');
      _open('div_eml_udt_switch');
      _open('mdl_eml_crt');
    }

    getRequest('read_contact', updateEmail, [id, 'eml']);
  }
  if (mdl === 'mdl_upt_ctc' && type === 'phn') {
    function updatePhone(obj) {
      document.getElementById('ipt_phn_phn').value = obj.v0;
      document.getElementById('div_phn_udt_switch').innerHTML = obj.udt;
      _close('div_phn_crt_switch');
      _open('div_phn_udt_switch');
      _open('mdl_phn_crt');
    }

    getRequest('read_contact', updatePhone, [id, 'phn']);
  }
  if (mdl === 'mdl_upt_ctc' && type === 'add') {
    function updateAddress(obj) {
      document.getElementById('ipt_add_ln1').value = obj.v0;
      document.getElementById('ipt_add_ln2').value = obj.v1;
      document.getElementById('ipt_add_sub').value = obj.v2;
      document.getElementById('ipt_add_stt').value = obj.v3;
      document.getElementById('ipt_add_pcd').value = obj.v4;
      document.getElementById('div_add_udt_switch').innerHTML = obj.udt;
      _close('div_add_crt_switch');
      _open('div_add_udt_switch');
      _open('mdl_add_crt');
    }

    getRequest('read_contact', updateAddress, [id, 'add']);
  }
}