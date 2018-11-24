/**
 * @param user username of user
 * @param list_el ul element to hold list of churches
 * @param par_el select element used for filtering by participation
 * @param rou_el select element used for filtering by route
 */
const RouteListController = function(user, list_el, par_el, rou_el) {
  const _ROUTE_LIST_URL = PAGE_RECONCILER
    + 'resources/ajax_backend/route_list_controller.php';
  let region = [];
  // TODO load churches
  // TODO create list elements
  // TODO attach listeners to filters

  function loadRegion() {
    jsonPost(_ROUTE_LIST_URL, {q: 'read_region', username: user}, (obj) => {
      region = obj.region;

      refreshList();
    });
  }

  function refreshList() {
    clearList();
    for (const route of Object.values(region.routes))
      for (const church of Object.values(route.churches))
        createElement(route, church);
  }


  function clearList() {
    list_el.innerHTML = '';
  }

  function createElement(r, c) {
    let li = document.createElement('LI');
    li.className =
      'w3-row-padding w3-card w3-round-large w3-margin-top w3-white';
    li.id = `church-${c.id}`;
    li.innerHTML += `
<div class="w3-col l4 m12 s12">
  <p class="w3-xlarge">${c.name}</p>
  <p>${r.name}</p>
</div>
<div class="w3-col l3 m6 s12">
  <p>${c.time}</p>
  <p>${c.suburb}, ${c.post_code}</p>
</div>
<div class="w3-col l2 m6 s12">
  <p>loaves - ${c.loaves}<br>bun - ${c.buns}<br>gf - ${c.gluten_free}</p>
</div>
<div class="w3-col l3 m12 s12">
  <label class="w3-text-dark-gray">participating</label>
  <select id="par-${c.id}" class="w3-select">
    <option value='yes'>yes</option>
    <option value='no'>no</option>
    <option value='undecided'>undecided</option>
    <option value='not yet contacted'>not yet contacted</option>
  </select>
  <label class="w3-text-dark-gray">route</label>
  <select id="route-${c.id}" class="w3-select">
  </select>
</div>`;

    // participation
    li.querySelector(`#par-${c.id}`).value = c.participating;

    // route options
    let route_selector = li.querySelector(`#route-${c.id}`);
    for (const route of Object.values(region.routes)) {
      let option = document.createElement('OPTION');
      option.value = route.id;
      option.text = route.name;
      route_selector.appendChild(option);
    }
    route_selector.value = r.id;

    // add to list
    list_el.appendChild(li);
  }

  loadRegion();

};