function $(a) { return document.getElementById(a); }

function createNewUser() {
  const _NEW_USER_URL = PAGE_RECONCILER + "../ajax_backend/create_new_user.php";
  
  let user = userFactory(el("new_username"), el("new_email"),
    el("new_password"), el("new_confirmpwd"));

  if (user.isValid()) jsonPost(_NEW_USER_URL, user.getRegoObj(),
    function (obj) {
      if (!obj.success) {
        alert(obj.msg);
      } else {
        alert('success');
        user.clearInputs()
      }
    });
}

const userFactory = function (uid_el, eml_el, pw_el, conf_el) {
  let uid = uid_el.value;
  let eml = eml_el.value;
  let pw = pw_el.value;
  let conf = conf_el.value;
  let isValid = function () {
    let re1 = /^\w+$/;
    let re2 = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}/;
    let msg = "";
    if (uid === "" || eml === "" || pw === "" || conf === "") {
      msg = "You must provide all the requested details. Please try again";
    } else if (!re1.test(uid)) {
      msg = "Username must contain only letters, numbers and underscores. Please try again";
    } else if (pw < 6) {
      msg = "Passwords must be at least 6 characters long.  Please try again";
    } else if (!re2.test(pw)) {
      msg = "Passwords must contain at least one number, one lowercase and one uppercase letter.  Please try again";
    } else if (pw !== conf) {
      msg = "Your password and confirmation do not match. Please try again";
    }
    if (msg === "")
      return true;
    alert(msg);
    return false;
  };

  let getRegoObj = function () {
    let obj = {};
    obj.username = uid;
    obj.email = eml;
    obj.p = hex_sha512(pw);
    return obj;
  };

  let clearInputs = function () {
    uid_el.value = '';
    eml_el.value = '';
    pw_el.value = '';
    conf_el.value = '';
  };

  return {isValid, getRegoObj, clearInputs};

};