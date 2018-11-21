if (!String.prototype.padStart) {
  String.prototype.padStart = function padStart(targetLength, padString) {
    targetLength = targetLength >> 0; //truncate if number or convert non-number to 0;
    padString = String((typeof padString !== "undefined" ? padString : " "));
    if (this.length > targetLength) {
      return String(this);
    }
    else {
      targetLength = targetLength - this.length;
      if (targetLength > padString.length) {
        padString += padString.repeat(targetLength / padString.length);
      }
      return padString.slice(0, targetLength) + String(this);
    }
  };
}

function _open(id) {
  document.getElementById(id).style.display = "block";
}

function _close(id) {
  document.getElementById(id).style.display = "none";
}

/*---- Miscellaneous ----*/

var _URL = "../../../ajax_backend/finance_backend.php?";

window.onload = function () {
  initPage();

  function initPage() {
    readPayments();
    readPayees();
  }
};

function readPayments() {
  getRequest("read_payments", function (obj) {
    document.getElementById("div_pmt_switch").innerHTML = obj.html;
  });
}

function createPayment() {
  var des = document.getElementById("ipt_pmt_des").value;
  var amt = document.getElementById("ipt_pmt_amt").value;
  var gst = document.getElementById("ipt_pmt_gst").value;
  if (des.trim() === "" || amt.trim() === "" || gst.trim() === "") {
    alert("Looks like you missed one");
    return;
  }

  function foo(obj) {
    _close("mdl_pmt_crt");
    readPayments();
    prepModal("mdl_pmt_red", obj.id);
  }

  postRequest("create_payment", foo, [des, amt, gst]);
}

function readPayees() {
  getRequest("read_payees", function (obj) {
    document.getElementById("div_pye_switch").innerHTML = obj.html;
  });
}

function createPayee() {
  var nam = document.getElementById("ipt_pye_nam").value;
  var abn = document.getElementById("ipt_pye_abn").value;
  if (nam === "") {
    alert("Looks like you missed one");
    return;
  }

  function foo(obj) {
    _close("mdl_pye_crt");
    readPayees();
    readPayee(obj.id);
  }

  postRequest("create_payee", foo, [nam, abn]);
}

/**
 * Sets up the lower half of the page to display the information about a particular payee.
 * A lot of the work is delegated to other functions.
 * Also sets up crt and save modal buttons.
 *
 * @param id    The id of the payee
 */
function readPayee(id) {
  function foo(obj) {
    document.getElementById("ipt_det_nam").value = obj.nam;
    document.getElementById("ipt_det_abn").value = obj.abn;
    document.getElementById("txt_det_bal").innerHTML = "Balance: " + obj.bal;
    document.getElementById("txt_det_upd").innerHTML = "Unpaid: " + obj.upd;
    document.getElementById("div_inf_ref_switch").innerHTML = obj.ref;
    document.getElementById("div_det_sav_switch").innerHTML = obj.sav;
    document.getElementById("div_rct_crt_switch").innerHTML = obj.rct;
    document.getElementById("div_ivc_crt_switch").innerHTML = obj.ivc;
    document.getElementById("div_phn_crt_switch").innerHTML = obj.phn_crt;
    document.getElementById("div_eml_crt_switch").innerHTML = obj.eml_crt;
    document.getElementById("div_add_crt_switch").innerHTML = obj.add_crt;

    readReceipts(obj.id);
    readInvoices(obj.id);
    readContacts(obj.id);
  }

  getRequest("read_payee", foo, id);
}

function updatePayee(id) {
  var nam = document.getElementById("ipt_det_nam").value;
  var abn = document.getElementById("ipt_det_abn").value;
  if (nam.trim() === "") {
    alert("You should probably have a name for that one.");
    return;
  }

  function foo(obj) {
    alert("success!");
    readPayees();
    readPayee(obj.id);
  }

  postRequest("update_payee", foo, [id, nam, abn]);

}

function readReceipts(id) {
  getRequest("read_receipts", function (obj) {
    document.getElementById("div_rct_switch").innerHTML = obj.html;
  }, id);
}

function createReceipt(id) {
  var des1 = document.getElementById("ipt_rct_des1").value;
  var des2 = document.getElementById("ipt_rct_des2").value;
  if (des2 != "") des1 += " - " + des2;
  var amt = document.getElementById("ipt_rct_amt").value;
  var cor = document.getElementById("ipt_rct_cor").value;
  if (des1 === "" || amt === "" || cor === "") {
    alert("Looks like you missed one");
    return;
  }
  if (isNaN(amt)) {
    alert("The amount should probably be numerical. Just saying...");
    return;
  }

  function foo(obj) {
    _close("mdl_rct_crt");
    readPayees();
    readPayee(obj.pid);
    prepModal("mdl_rct_red", obj.rid);
  }

  postRequest("create_receipt", foo, [id, des1, amt, cor]);
}

function downloadReceipt(id) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      var obj = this.responseText;

      download("rct" + String(id).padStart(11, "0") + ".html", obj);
    }
  };

  var url = _URL;
  url += "t=" + Math.random();
  url += "&q=" + "download_receipt";
  url += "&pw=" + document.getElementById("ipt_sec").value;
  url += "&id=" + id;
  xmlhttp.open("GET", url, true);
  xmlhttp.send();
}

function readInvoices(id) {
  getRequest("read_invoices", function (obj) {
    document.getElementById("div_ivc_switch").innerHTML = obj.html;
  }, id);
}

function createInvoice(id) {
  var chg = document.getElementById("ipt_ivc_chg").value;
  var due = document.getElementById("ipt_ivc_due").value;
  if (chg === "") {
    alert("Please enter your charges");
    return;
  }
  if (due === "") {
    due = 15;
  }

  var chgArr = chg.split(/\r?\n/g);
  for (var i = 0; i < chgArr.length; i++)
    if (chgArr[i].split(">").length === 3) {
      alert("Looks like you haven't formatted everything quite right. Have another look");
      return;
    }

  function foo(obj) {
    _close("mdl_ivc_crt");
    readPayees();
    readPayee(obj.pid);
    prepModal("mdl_ivc_red", obj.iid);
  }

  //chg = des>qty>price>gst
  postRequest("create_invoice", foo, [id, chg, due]);
}

function downloadInvoice(id) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      var obj = this.responseText;

      download("ivc" + String(id).padStart(11, "0") + ".html", obj);
    }
  };

  var url = _URL;
  url += "t=" + Math.random();
  url += "&q=" + "download_invoice";
  url += "&pw=" + document.getElementById("ipt_sec").value;
  url += "&id=" + id;
  xmlhttp.open("GET", url, true);
  xmlhttp.send();
}

function readContacts(id) {
  getRequest("read_contacts", function (obj) {
    document.getElementById("div_ctc_switch").innerHTML = obj.html;
  }, id);
}

function createContact(id, type, udt, cid) {
  if (type === "eml") {
    var eml = document.getElementById("ipt_eml_eml").value;
    if (eml === "") {
      alert("Looks like you missed one");
      return;
    }

    function foo(obj) {
      _close("mdl_eml_crt");
      alert(obj.msg);
      readContacts(id);
    }

    if (udt !== undefined && udt) postRequest("update_contact", foo, ["eml", cid, eml]);
    else postRequest("create_contact", foo, ["eml", id, eml]);
  }
  if (type === "phn") {
    var phn = document.getElementById("ipt_phn_phn").value;
    if (phn === "") {
      alert("Looks like you missed one");
      return;
    }

    function foo(obj) {
      _close("mdl_phn_crt");
      alert(obj.msg);
      readContacts(id);
    }

    if (udt !== undefined && udt) postRequest("update_contact", foo, ["phn", cid, phn]);
    else postRequest("create_contact", foo, ["phn", id, phn]);
  }
  if (type === "add") {
    var ln1 = document.getElementById("ipt_add_ln1").value;
    var ln2 = document.getElementById("ipt_add_ln2").value;
    var sub = document.getElementById("ipt_add_sub").value;
    var stt = document.getElementById("ipt_add_stt").value;
    var pcd = document.getElementById("ipt_add_pcd").value;
    if (ln1 === "" || sub === "" || stt === "" || pcd === "") {
      alert("Looks like you missed one");
      return;
    }

    function foo(obj) {
      _close("mdl_add_crt");
      alert(obj.msg);
      readContacts(id);
    }

    if (udt !== undefined && udt) postRequest("update_contact", foo, ["add", cid, ln1, ln2, sub, stt, pcd]);
    else postRequest("create_contact", foo, ["add", id, ln1, ln2, sub, stt, pcd]);
  }
}

function updateContact(id, cid, type) {
  createContact(id, type, true, cid);
}

function deleteContact(pid, cid, type, ctc) {
  if (!confirm("We'll delete " + ctc + " then?"))
    return;

  function foo(obj) {
    alert(obj.msg);
    readContacts(pid);
  }

  postRequest("delete_contact", foo, [type, cid]);
}

/**
 * This is used to prepare any modals that are shown to the user.
 * It mostly cleans crt modals and defines return functions for read modals.
 *
 * @param mdl   The modal to be prepared. Each is prepared using custom code based on this value.
 * @param id    ID if required. Not always used.
 * @param type  used for contact (ctc) modals. eml, phn or add.
 */
function prepModal(mdl, id, type) {
  if (mdl === "mdl_pmt_crt") {
    document.getElementById("ipt_pmt_des").value = "";
    document.getElementById("ipt_pmt_amt").value = "";
    document.getElementById("ipt_pmt_gst").value = "";
    _open(mdl);
  }
  if (mdl === "mdl_pmt_red") {
    function readPayment(obj) {
      document.getElementById("txt_pmt_num").innerHTML = obj.num;
      document.getElementById("txt_pmt_dat").innerHTML = obj.dat;
      document.getElementById("txt_pmt_des").innerHTML = obj.des;
      document.getElementById("txt_pmt_amt").innerHTML = obj.amt;
      document.getElementById("txt_pmt_gst").innerHTML = obj.gst;
      _open("mdl_pmt_red");
    }

    getRequest("read_payment", readPayment, id);
  }
  if (mdl === "mdl_pye_crt") {
    document.getElementById("ipt_pye_nam").value = "";
    document.getElementById("ipt_pye_abn").value = "";
    _open("mdl_pye_crt");
  }
  if (mdl === "mdl_pye_sdt") {
    function readStudents(obj) {
      document.getElementById("div_pye_sdt_switch").innerHTML = obj.html;
      _open("mdl_pye_sdt");
    }

    getRequest("read_students", readStudents, id);
  }
  if (mdl === "mdl_rct_crt") {
    document.getElementById("ipt_rct_des1").options[0].selected = "selected";
    document.getElementById("ipt_rct_des2").value = "";
    document.getElementById("ipt_rct_amt").value = "";
    document.getElementById("ipt_rct_cor").options[0].selected = "selected";
    _open("mdl_rct_crt");
  }
  if (mdl === "mdl_rct_red") {
    function readReceipt(obj) {
      document.getElementById("txt_rct_num").innerHTML = obj.num;
      document.getElementById("txt_rct_dat").innerHTML = obj.dat;
      document.getElementById("txt_rct_des").innerHTML = obj.des;
      document.getElementById("txt_rct_amt").innerHTML = obj.amt;
      document.getElementById("txt_rct_cor").innerHTML = obj.cor;
      document.getElementById("div_rct_dld_switch").innerHTML = obj.dld;
      _open("mdl_rct_red");
    }

    getRequest("read_receipt", readReceipt, id);
  }
  if (mdl === "mdl_ivc_crt") {
    document.getElementById("ipt_ivc_chg").value = "";
    _open("mdl_ivc_crt");
  }
  if (mdl === "mdl_ivc_red") {
    function readInvoice(obj) {
      document.getElementById("txt_ivc_num").innerHTML = obj.num;
      document.getElementById("txt_ivc_amt").innerHTML = obj.amt;
      document.getElementById("div_ivc_dld_switch").innerHTML = obj.dld;
      _open("mdl_ivc_red");
    }

    getRequest("read_invoice", readInvoice, id);
  }
  /*----  Contact Stuff ----*/
  if (mdl === "mdl_crt_ctc" && type === "eml") {
    document.getElementById("ipt_phn_phn").value = "";
    _open("div_eml_crt_switch");
    _close("div_eml_udt_switch");
    _open("mdl_eml_crt");
  }
  if (mdl === "mdl_crt_ctc" && type === "phn") {
    document.getElementById("ipt_phn_phn").value = "";
    _open("div_phn_crt_switch");
    _close("div_phn_udt_switch");
    _open("mdl_phn_crt");
  }
  if (mdl === "mdl_crt_ctc" && type === "add") {
    document.getElementById("ipt_add_ln1").value = "";
    document.getElementById("ipt_add_ln2").value = "";
    document.getElementById("ipt_add_sub").value = "";
    document.getElementById("ipt_add_pcd").value = "";
    document.getElementById("ipt_add_stt").options[0].selected = "selected";
    _open("div_add_crt_switch");
    _close("div_add_udt_switch");
    _open("mdl_add_crt");
  }
  if (mdl === "mdl_upt_ctc" && type === "eml") {
    function updateEmail(obj) {
      document.getElementById("ipt_eml_eml").value = obj.v0;
      document.getElementById("div_eml_udt_switch").innerHTML = obj.udt;
      _close("div_eml_crt_switch");
      _open("div_eml_udt_switch");
      _open("mdl_eml_crt");
    }

    getRequest("read_contact", updateEmail, id, "eml");
  }
  if (mdl === "mdl_upt_ctc" && type === "phn") {
    function updatePhone(obj) {
      document.getElementById("ipt_phn_phn").value = obj.v0;
      document.getElementById("div_phn_udt_switch").innerHTML = obj.udt;
      _close("div_phn_crt_switch");
      _open("div_phn_udt_switch");
      _open("mdl_phn_crt");
    }

    getRequest("read_contact", updatePhone, id, "phn");
  }
  if (mdl === "mdl_upt_ctc" && type === "add") {
    function updateAddress(obj) {
      document.getElementById("ipt_add_ln1").value = obj.v0;
      document.getElementById("ipt_add_ln2").value = obj.v1;
      document.getElementById("ipt_add_sub").value = obj.v2;
      document.getElementById("ipt_add_stt").value = obj.v3;
      document.getElementById("ipt_add_pcd").value = obj.v4;
      document.getElementById("div_add_udt_switch").innerHTML = obj.udt;
      _close("div_add_crt_switch");
      _open("div_add_udt_switch");
      _open("mdl_add_crt");
    }

    getRequest("read_contact", updateAddress, id, "add");
  }
}

function getRequest(q, fun, id, type) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      var obj = JSON.parse(this.responseText);
      if (obj.a === 0) {
        alert("An error has been detected. Please try again.");
        return;
      }
      fun(obj);
    }
  };

  var url = _URL;
  url += "t=" + Math.random();
  url += "&q=" + q;
  url += (id === undefined) ? "" : "&id=" + id;
  url += (type === undefined) ? "" : "&type=" + type;
  xmlhttp.open("GET", url, true);
  xmlhttp.send();
}

function postRequest(q, fun, arr) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (this.readyState === 4 && this.status === 200) {
      var obj = JSON.parse(this.responseText);
      if (obj.a === 0) {
        alert("An error has been detected. Please try again.");
        return;
      }
      fun(obj);
    }
  };

  if (arr === undefined) return;

  var par = "t=" + Math.random();
  par += "&q=" + q;
  par += "&arr=" + arr.join("||", arr);
  xmlhttp.open("POST", _URL, true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send(par);
}

function download(filename, text) {
  var element = document.createElement("a");
  element.setAttribute("href", "data:text/plain;charset=utf-8," + encodeURIComponent(text));
  element.setAttribute("download", filename);

  element.style.display = "none";
  document.body.appendChild(element);

  element.click();

  document.body.removeChild(element);
}