const UserPermissionController = function(select_el, container_el, button_el) {
  const _EDIT_PER_URL = PAGE_RECONCILER
    + 'resources/ajax_backend/user_permission_controller.php';
  let username = '';

  loadUsernames(_EDIT_PER_URL, select_el);

  function loadPermissions(uid = '') {
    username = uid;
    jsonPost(_EDIT_PER_URL, {q: 'read_permissions', username: username},
      (obj) => {
        container_el.innerHTML = `<p>${obj.username}</p>`;
        let reducer = (acc, per) => acc
          + `<label for="${per}"><input type="checkbox" id="${per}" ${obj.permissions[per]
            ? 'checked' : ''}>${per}</label><br>`;
        container_el.innerHTML +=
          Object.keys(obj.permissions).reduce(reducer, '');
      });
  }

  select_el.onchange = function() {
    loadPermissions(select_el.selectedOptions[0].value);
  };

  button_el.onclick = function() {
    let perms = {};
    container_el.querySelectorAll('input').forEach(
      (input) => perms[input.id] = input.checked);
    jsonPost(_EDIT_PER_URL,
      {q: 'update_permissions', username: username, permissions: perms},
      (obj) => {
        alert(obj.success ? 'success' : obj.msg);
      });
  };

};