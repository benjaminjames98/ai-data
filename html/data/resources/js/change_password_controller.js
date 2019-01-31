const ChangePasswordController = function(usr_el, pwd_el, con_el, btn_el) {
  const _CHANGE_PASSWORD_URL = PAGE_RECONCILER
    + 'resources/ajax_backend/change_password_controller.php';

  loadUsernames(_CHANGE_PASSWORD_URL, usr_el);

  function clearInputs() {
    pwd_el.value = '';
    con_el.value = '';
  }

  usr_el.addEventListener('change', clearInputs);

  btn_el.addEventListener('click', () => {
    let usr = usr_el.value;
    let pwd = pwd_el.value;
    let con = con_el.value;

    if (usr === '')
      alert('please select a username');
    else if (pwd !== con)
      alert('passwords do not match');
    else if (pwd.length < 4)
      alert('minimum password length is 4 characters');
    else {
      let hashed_pwd = hex_sha512(pwd);
      jsonPost(_CHANGE_PASSWORD_URL,
        {q: 'change_password', pwd: hashed_pwd, usr: usr},
        function(obj) {
          if (!obj.success) {
            alert(obj.msg);
          } else {
            alert('success, password changed');
            clearInputs();
          }
        });
    }
  });

};