class View {

  constructor() {}

  notifyViewRefresh(array, cp, info_idx = 0) {
    this.updateList(array, cp);
    this.updateFilters(array, cp);
    for (let i = 0; i < array.length; i++)
      this.toggleListItemsVisibilities(array[i]["id"], false);
    setMetaListeners();
    this.displayInfo(array[info_idx], cp);
  }

  updateList(array, cp) {
    $("#entity_list").html("");
    let t = this;
    $.each(array, function (i, ent) {
      t.addListItem(ent, cp);
    });
  }

  addListItem(ent, cp) {
    let name, id, denomination;
    name = ent["name"];
    id = formatNumLength(ent["id"], 4) + "\u2002";
    denomination = (cp === "c") ? ent["denomination"] : "";

    let li = $("<li>", {
      "class": "w3-bar w3-display-container w3-border-bottom w3-panel w3-card w3-white b2-hidden",
      "data-id": ent["id"]
    });
    li.append($("<div class='w3-bar-item'>"));
    li.append($("<span class='w3-large name'>").text(name));
    li.append($("<br>"));
    li.append($("<span class='id'>").text(id));
    li.append($("<span class='denomination'>").text(denomination));

    let btn = $("<button>").attr({
      "class": "w3-bar-item w3-button w3-xlarge w3-display-bottomright",
      "title": "show info",
      "onclick": "_open('infobar')"
    }).append("<i class='fas fa-bars'></i>");
    li.append(btn);
    setLoadButtonListener(btn, ent["id"], cp);
    $("#entity_list").append(li);
  }

  updateFilters(entities, cp = "c") {
    if (cp === "p") {
      $("#filter_switch").html("");
    } else if (cp === "c") {
      let den = [];
      let vis = [];
      let reg = [];
      $.each(entities, function (i, ent) {
        if ($.inArray(ent["denomination"], den) === -1) den.push(ent["denomination"]);
        if ($.inArray(ent["visibility"], vis) === -1) vis.push(ent["visibility"]);
        if ($.inArray(ent["region"], reg) === -1) reg.push(ent["region"]);
      });
      let cont = $("#filter_switch").html("");
      createCheckboxes(cont, "Denomination", den, "denomination");
      createCheckboxes(cont, "Visibility", vis, "visibility");
      createCheckboxes(cont, "Region", reg, "region");
    }

    function createCheckboxes(container, title, arr, attr) {
      let outerDiv = $("<div>", {"class": "w3-bar-item w3-border-bottom w3-animate-left"})
        .append(`<h5>${title}</h5>`)
        .append("<div class='switch'>");
      container.append(outerDiv);

      let btnDiv = $(`<div class='w3-animate-left w3-row w3-margin-bottom'>`);
      let selAll = $(`<button>`, {
        "class": "w3-text-white w3-theme w3-button w3-col l6 m6 s6 w3-left",
        "title": "select all", "id": "sel_all_" + attr, "text": "all"
      });
      let selNon = $(`<button>`, {
        "class": "w3-text-white w3-theme w3-button  w3-col l6 m6 s6 w3-right",
        "title": "select none", "id": "sel_non_" + attr, "text": "none"
      });

      btnDiv.append(selAll).append(selNon);
      let div = outerDiv.find(".switch").append(btnDiv);
      $.each(arr.sort(), function (i, val) {
        let cbx = $("<input type='checkbox' checked>").attr({"value": val});
        let lbl = $("<label class='w3-animate-left'>")
          .append(cbx).append((val.trim() === "") ? " -" : " " + val)
          .append("<br>");
        setCheckboxListener(lbl, attr, val, cp);
        div.append(lbl);
      });
      setFilterMetaListeners(btnDiv, outerDiv, attr, cp);
    }

    setSearchListener(cp);
  }

  /**
   * Toggles the visibility of <li> items based on data-id attribute
   *
   * @param id Identifier stored in the data-id attribute
   * @param hide Hide or show?
   */
  toggleListItemsVisibilities(id, hide) {
    $(`#entity_list`).find(`> li[data-id=${id}]`).each(function () {
      let needsShowing = !hide && $(this).hasClass("b2-hidden");
      let needsHiding = hide && !$(this).hasClass("b2-hidden");
      if (needsShowing) {
        $(this).removeClass("anim-out").removeClass("b2-hidden").addClass("anim-in");
      } else if (needsHiding) {
        $(this).one("animationend", function () {
          $(this).one("animationend", function () {
            $(this).addClass("b2-hidden");
          });
        });
        $(this).removeClass("anim-in").addClass("anim-out");
      }
    });
  }

  scrollTo(id) {
    let container = $("#entity_div");
    let scrollTo = container.find(`>ul>[data-id=${id}]`);
    let delta = scrollTo.offset().top - container.offset().top + container.scrollTop();
    container.animate({scrollTop: delta}, 400);
  }

  /**
   * Updates a single entity's card info in the main list,
   * based on 'data-id' HTML attribute.
   *
   * If no <li> with that 'data-id' exists, create a new list item.
   *
   * @param id ID of entity
   * @param ent Entity array/object
   * @param cp church ('c') or person ('p')
   */
  updateListItemInfo(id, ent, cp) {
    let li = $(`#entity_list`).find(`> li[data-id=${id}]`);
    if (li.length > 0) {
      li.find(".name").text(ent["name"]);
      li.find(".id").text(formatNumLength(ent["id"], 4) + "\u2002");
      if (cp === "c")
        li.find(".denomination").text(ent["denomination"]);
    } else {
      this.addListItem(ent, cp);
      this.toggleListItemsVisibilities(ent["id"], false);
      this.displayInfo(ent, cp);
    }
  }

  /**
   * Displays provided entity's information on the infobar.
   * Currently also recreates much of the infobar.
   *
   * @param ent The entity's data to be displayed
   * @param cp Church or Person
   */
  displayInfo(ent, cp) {
    let elm = document.getElementById("info");
    elm.parentNode.replaceChild(elm.cloneNode(true), elm);

    let header = cp === "c" ? "Church: " : "Person: ";
    header += formatNumLength(ent["id"], 4);
    $("#info_header").text(header);
    setInfoMetaListeners(ent, cp);

    let details = $("#details_switch").html("");
    if (cp === "c") {
      details.append(createInput("Name:", "name", ent["name"]));
      details.append(createInput("Denomination:", "denomination", ent["denomination"]));
      details.append(createRegionSelector("region", ent["region"]));
      details.append(createVisibilitySelector("visibility", ent["visibility"]));
      details.append(createTextarea("Notes:", "note", ent["note"]));
    } else if (cp === "p") {
      details.append(createInput("First Name:", "first_name", ent["first_name"]));
      details.append(createInput("Last Name:", "last_name", ent["last_name"]));
      details.append(createTextarea("Notes:", "note", ent["note"]));
    }
    setSaveButtonListener($("#details_save"), ent["id"], cp);

    let contacts_table = $("#contacts_switch").html("");
    ent["contacts"].forEach((ctc) => {
      this.addContactRow(ctc, ent["id"], contacts_table, cp);
    });
    setCreateContactListeners(ent["id"], cp);

    let roles_table = $("#roles_switch").html("");
    let roles = (cp === "p") ? ent["churches"] : ent["people"];
    roles.forEach((ctc) => {
      this.addRolesRow(ctc, ent, roles_table, cp);
    });
    setCreateRoleListener(ent["id"], cp);
  }

  /**
   * Attaches a new role object to a table
   *
   * @param ctc Object representing connection
   * @param ent Object representing primary entity
   * @param people_table table for row to be attached to
   * @param cp church or person
   */
  addRolesRow(ctc, ent, people_table, cp) {
    let row = $("<tr>");
    let role = $("<td>").css({"width": "30%", "word-wrap": "break-word"}).html(ctc["role"]);
    let see = $("<td>").css("width", "45px")
      .attr({"class": "w3-button", "title": "edit contact"});
    see.append($("<i>", {"class": {c: "fa fa-user", p: "fa fa-church"}[cp]}));
    let name = $("<td>").css({"word-wrap": "break-word"}).html(ctc["name"]);
    let edit = $("<td>").css("width", "30px")
      .attr({"class": "w3-button", "title": "edit contact"})
      .append($("<i class='fas fa-pencil-alt'>"));
    let del = $("<td>").css("width", "30px")
      .attr({"class": "w3-button", "title": "delete contact"})
      .append($("<i class='fas fa-trash-alt'>"));
    row.append(see).append(role).append(name).append(edit).append(del);
    setRowMetaListeners(see, ctc, ent, cp);
    setDeleteRolesListeners(del, ctc, ent, cp);
    setUpdateRoleListeners(edit, ctc, ent, cp);
    people_table.append(row);
  }

  addContactRow(ctc, id, contacts_table, cp) {
    let icons = {
      "add": "fa fa-home", "phn": "fa fa-phone", "web": "fa fa-globe", "eml": "fa fa-envelope"
    };
    let row = $("<tr>");
    let icon = $("<td>").css("width", "40px").append($("<i>")
      .attr({"class": icons[ctc["type"]]}));
    let label = $("<td>")
      .css({"word-wrap": "break-word"})
      .html(ctc["contact"]);
    let edit = $("<td>").css("width", "30px")
      .attr({"class": "w3-button", "title": "edit contact"})
      .append($("<i class='fas fa-pencil-alt'>"));
    let del = $("<td>").css("width", "30px")
      .attr({"class": "w3-button", "title": "delete contact"})
      .append($("<i class='fas fa-trash-alt'>"));
    row.append(icon).append(label).append(edit).append(del);
    setDeleteContactListeners(del, ctc, id, cp);
    setUpdateContactListeners(edit, ctc, id, cp);
    contacts_table.append(row);
  }

  getVisibleIds() {
    let ids = [];
    $(`#entity_list`).find(`>li:visible>.id`).each(function () {
      ids.push(parseInt($(this).text()));
    });
    return ids;
  }
}

class Model {

  constructor(view) {
    this._url = "../../../ajax_backend/churches_backend.php"; // URL of the php webpage
    this._view = view; // view class object of mvc
    this._churches = null; // churches arr
    this._people = null; // people arr
    this._cp = "c"; // ENUM(church, person) current state
  }

  /**
   * Reads DB into _churches and _people.
   * Refreshes view afterwards.
   */
  readDB() {
    let t = this;
    $.post(t._url, {q: "read_db"}, function (json) {
      t._churches = json["churches"].sort();
      t._people = json["people"].sort();
      t._churches.forEach(c => {
        c["hide_denomination"] = false;
        c["hide_visibility"] = false;
        c["hide_region"] = false;
        c["hide_search"] = false;
      });
      t.toggleContent("c");
    }, "json");
  }

  /**
   * Use to switch between people and church pages.
   * The primary way of refreshing the view.
   *
   * @param _cp Type to switch to: c, p or toggle
   */
  toggleContent(_cp) {
    let cp = _cp;
    if (typeof cp === "undefined" || cp === "toggle")
      cp = (this._cp === "c") ? "p" : "c";
    let arr = (cp === "c") ? this._churches : this._people;
    this._cp = cp;
    $("#entity_search > input").val("");
    this._view.notifyViewRefresh(arr, cp);
  }

  /**
   * Updates an entity by
   * - getting its info from server
   * - updating its record in _churches or _people
   * - updating its list item
   * - displaying its info in the infobar
   *
   * @param id ID of entity to update
   * @param cp Church or Person
   * @param display Update infobar? True by default
   */
  readEnt(id, cp = this._cp, display = true) {
    let t = this;
    let _q = cp === "c" ? "read_church" : "read_person";

    $.post(t._url, {q: _q, "id": id}, (json) => {
      let ent = json["church"] || json["person"];
      if (cp === "c") {
        ent["hide_denomination"] = false;
        ent["hide_visibility"] = false;
        ent["hide_region"] = false;
      }
      ent["hide_search"] = false;
      let idx = t.idToIndex(ent["id"], cp);
      if (idx === -1 && cp === "c") this._churches.push(ent);
      else if (idx === -1 && cp === "p") this._people.push(ent);
      else if (cp === "c") t._churches[idx] = ent;
      else if (cp === "p") t._people[idx] = ent;
      t._view.updateListItemInfo(id, ent, cp);
      if (display === true) this.switchToElement(ent["id"], cp);
    }, "json");
  }

  switchToElement(id, cp) {
    if (cp !== this._cp) this.toggleContent(cp);
    this.displayInfo(id, cp);
  }

  createChurch(name, denomination, region, visibility) {
    let obj = {name: name, denomination: denomination, region: region, visibility: visibility};
    let t = this;
    $.post(t._url, {q: "create_church", data: JSON.stringify(obj)},
      (data, status) => {
        if (status !== "success" || data["a"] === "0")
          alert(data["msg"] || status);
        else {
          this.readEnt(data.church_id, "c", true);
          alert("success");
        }
      }, "json");
  }

  updateChurch(id, nam, den, reg, vis, not) {
    let obj = {id: id, name: nam, denomination: den, region: reg, visibility: vis, note: not};
    this.miniPost("update_church", obj, id, "c");
  }

  createPerson(first_name, last_name) {
    let obj = {"first_name": first_name, "last_name": last_name};
    let t = this;
    $.post(t._url, {q: "create_person", data: JSON.stringify(obj)},
      (data, status) => {
        if (status !== "success" || data["a"] === "0")
          alert(data["msg"] || status);
        else {
          this.readEnt(data.person_id, "p", true);
          alert("success");
        }
      }, "json");
  }

  updatePerson(id, fnam, lnam, not) {
    let obj = {"id": id, "first_name": fnam, "last_name": lnam, "note": not};
    this.miniPost("update_person", obj, id, "p");
  }

  createContact(type, church_id, cp, ctc1, ctc2, ctc3, ctc4, ctc5) {
    let obj = null;
    if (type === "web") obj = {"type": type, "id": church_id, "cp": cp, "url": ctc1};
    else if (type === "eml") obj = {"type": type, "id": church_id, "cp": cp, "eml": ctc1};
    else if (type === "phn") obj = {"type": type, "id": church_id, "cp": cp, "phn": ctc1};
    else if (type === "add") obj = {
      "type": type, "id": church_id, "cp": cp, "ln1": ctc1,
      "ln2": ctc2 || "", "sub": ctc3, "stt": ctc4, "pcd": ctc5
    };
    else return;
    this.miniPost("create_contact", obj, church_id, cp);
  }

  updateContact(type, contact_id, ent_id, cp, ctc1, ctc2, ctc3, ctc4, ctc5) {
    let obj = null;
    if (type === "web") obj = {"type": type, "id": contact_id, "url": ctc1};
    else if (type === "eml") obj = {"type": type, "id": contact_id, "eml": ctc1};
    else if (type === "phn") obj = {"type": type, "id": contact_id, "phn": ctc1};
    else if (type === "add") obj = {
      "type": type, "id": contact_id, "ln1": ctc1,
      "ln2": ctc2 || "", "sub": ctc3, "stt": ctc4, "pcd": ctc5
    };
    else return;
    this.miniPost("update_contact", obj, ent_id, cp);
  }

  deleteContact(ctc_type, ctc_id, ent_id, cp) {
    let obj = {"type": ctc_type, "contact_id": ctc_id};
    this.miniPost("delete_contact", obj, ent_id, cp);
  }

  /**
   * Sends a network request to create a 'role' connection between
   * a church and a person.
   *
   * @param type Role description
   * @param person_id ID of person
   * @param church_id ID of church
   * @param cp church or person
   */
  createRole(type, person_id, church_id, cp) {
    let obj = {"type": type, "person_id": person_id, "church_id": church_id};
    let id = cp === "c" ? church_id : person_id;
    this.miniPost("create_role", obj, id, cp);
  }

  updateRole(type, role_id, ent_id, cp) {
    let obj = {"type": type, "id": role_id};
    this.miniPost("update_role", obj, ent_id, cp);
  }

  deleteRole(role_id, ent_id, cp) {
    let obj = {"role_id": role_id};
    this.miniPost("delete_role", obj, ent_id, cp);
  }

  miniPost(q, obj, ent_id, cp) {
    if (cp === undefined) console.log("minipost: cp undefined");
    let t = this;
    $.post(t._url, {q: q, data: JSON.stringify(obj)},
      (data, status) => {
        if (status !== "success" || data["a"] === "0")
          alert(data["msg"] || status);
        else {
          t.readEnt(ent_id, cp);
          alert("success");
        }
      }, "json");
  }

  search(search, cp) {
    search = search.toLowerCase();
    if (cp === "c")
      this._churches.forEach(
        (c) => c["hide_search"] = c["name"].toLowerCase().indexOf(search.toLowerCase()) === -1);
    else
      this._people.forEach(
        (p) => p["hide_search"] = p["name"].toLowerCase().indexOf(search.toLowerCase()) === -1);
    this.toggleListItemsVisibilities(cp);
  }

  filter(attr, val, hide, cp) {
    this._churches.forEach((c) => {
      if (c[attr] === val) c["hide_" + attr] = hide;
    });
    this.toggleListItemsVisibilities(cp);
  }

  filterAll(attr, hide, cp) {
    this._churches.forEach((c) => {
      c["hide_" + attr] = hide;
    });
    this.toggleListItemsVisibilities(cp);
  }

  toggleListItemsVisibilities(cp) {
    let arr = cp === "c" ? this._churches : this._people;
    arr.forEach((e) => {
      let hide = cp === "c" ?
        e["hide_denomination"] || e["hide_visibility"] || e["hide_region"]
        || e["hide_search"] : e["hide_search"];
      this._view.toggleListItemsVisibilities(e["id"], hide);
    });
  };

  displayInfo(id, cp) {
    let idx = this.idToIndex(id, cp);
    let arr = (cp === "c") ? this._churches : this._people;
    this._view.displayInfo(arr[idx], cp);
  }

  displayEmails() {
    let ids = this._view.getVisibleIds();
    let emails = [];
    let arr = this._cp === "c" ? this._churches : this._people;
    arr.forEach(el => {
      if (ids.indexOf(el["id"]) > 0)
        el["contacts"].forEach(ctc => {
          if (ctc["type"] === "eml") emails.push(ctc["contact"]);
        });
    });

    clearModal();
    createModal("Emails", createInput("Emails:", undefined, emails.join(";")));
  }

  scrollTo(id) {
    this._view.scrollTo(id);
  }

  idToIndex(id, cp) {
    if (cp === "c") return this._churches.findIndex(ent => ent["id"] === id);
    else return this._people.findIndex(ent => ent["id"] === id);
  }

  getArray(cp = "p") {
    if (cp === "c")
      return this._churches.slice();
    else
      return this._people.slice();
  }
}

/* ---- CONTROLLER ---- */

_MODEL = new Model(new View());

$(document).ready(function () {
  _MODEL.readDB();
});

function setFilterMetaListeners(div, outerDiv, atr, cp) {
  div.find("#sel_non_" + atr).off().click(function () {
    _MODEL.filterAll(atr, true, cp);
    outerDiv.find(`[type='checkbox']`).prop("checked", false);
  });

  div.find("#sel_all_" + atr).off().click(function () {
    _MODEL.filterAll(atr, false, cp);
    outerDiv.find(`[type='checkbox']`).prop("checked", true);
  });
}

function setCheckboxListener(lbl, atr, val, cp) {
  lbl.off().click(function () {
    let show = $(this).find(`[type='checkbox']`).is(":checked");
    _MODEL.filter(atr, val, !show, cp);
  });
}

function setMetaListeners() {
  $("#refresh_db").off().click(() => {
    _MODEL.readDB();
  });

  $("#toggle_content").off().click(() => {
    _MODEL.toggleContent("toggle");
  });

  $("#church_create").off().click(() => {
    clearModal();
    let content = $("<div>")
      .append(createInput("Name:", "mdl_ipt_1"))
      .append(createInput("Denomination", "mdl_ipt_2"))
      .append(createRegionSelector("mdl_ipt_3"))
      .append(createVisibilitySelector("mdl_ipt_4"));
    createModal("Create New Church", content.html(), () => {
      let ipt1 = $("#mdl_ipt_1").val();
      let ipt2 = $("#mdl_ipt_2").val();
      let ipt3 = $("#mdl_ipt_3").val();
      let ipt4 = $("#mdl_ipt_4").val();
      if (ipt1 === "") alert("Missed one!");
      else {
        _MODEL.createChurch(ipt1, ipt2, ipt3, ipt4);
        return true;
      }
    });
  });

  $("#person_create").off().click(() => {
    clearModal();
    let content = $("<div>")
      .append(createInput("First Name:", "mdl_ipt_1"))
      .append(createInput("Last Name:", "mdl_ipt_2"));
    createModal("Create New Person", content.html(), () => {
      let ipt1 = $("#mdl_ipt_1").val();
      let ipt2 = $("#mdl_ipt_2").val();
      if (ipt1 === "") alert("Missed one!");
      else {
        _MODEL.createPerson(ipt1, ipt2);
        return true;
      }
    });
  });

  $("#show_emails").off().click(() => {
    _MODEL.displayEmails();
  });
}

function setInfoMetaListeners(ent, cp) {
  $("#refresh_entity").off().click(() => {
    _MODEL.readEnt(ent["id"], cp);
  });

  $("#scroll_btn").off().click(() => {
    _MODEL.scrollTo(ent["id"]);
  });
}

function setSearchListener(cp) {
  $("#entity_search").find("> input").off().keypress(function (e) {
    if (e.which === 13) {
      let val = $(this).val();
      _MODEL.search(val, cp);
    }
  });
}

function setLoadButtonListener(btn, id, cp) {
  btn.off().click(() => _MODEL.displayInfo(id, cp));
}

function setSaveButtonListener(btn, id, cp) {
  btn.off().click(() => {
    if (cp === "c")
      _MODEL.updateChurch(id,
        $("#name").val() || "",
        $("#denomination").val() || "",
        $("#region").val() || "",
        $("#visibility").val() || "",
        $("#note").val() || "");
    else if (cp === "p")
      _MODEL.updatePerson(id,
        $("#first_name").val() || "",
        $("#last_name").val() || "",
        $("#note").val() || "");
  });
}

function setCreateContactListeners(id, cp) {
  $("#church_website").off().click(() => {
    foo("New Website", "URL:", "web");
  });
  $("#church_email").off().click(() => {
    foo("New Email", "Email Address:", "eml");
  });
  $("#church_phone").off().click(() => {
    foo("New Phone", "Phone Number:", "phn");
  });

  function foo(heading, label, type) {
    createModal(heading, createInput(label), () => {
      let ipt1 = $("#mdl_ipt_1").val();
      if (ipt1 === "") alert("Missed one!");
      else {
        _MODEL.createContact(type, id, cp, ipt1);
        return true;
      }
    });
  }

  $("#church_address").off().click(() => {
    let heading = "New Address";
    let div = $("<div>");
    div.append(createInput("Line 1", "mdl_ipt_1"))
      .append(createInput("Line 2", "mdl_ipt_2"))
      .append(createInput("Suburb", "mdl_ipt_3"))
      .append(createStateSelector("mdl_ipt_4"))
      .append(createInput("Suburb", "mdl_ipt_5"));

    createModal(heading, div.html(), () => {
      let ln1 = $("#mdl_ipt_1").val();
      let ln2 = $("#mdl_ipt_2").val() || "";
      let sub = $("#mdl_ipt_3").val();
      let stt = $("#mdl_ipt_4").val();
      let pcd = $("#mdl_ipt_5").val();
      if (ln1 === "" || sub === "" || pcd === "")
        alert("Missed one!");
      else {
        _MODEL.createContact("add", id, cp, ln1, ln2, sub, stt, pcd);
        return true;
      }
    });
  });
}

function setUpdateContactListeners(btn, ctc, ent_id, cp) {
  let labels = {
    "phn": "Phone", "eml": "Email", "web": "URL:", "add": "Address"
  };
  btn.off().click(() => {
    let type = ctc["type"];
    clearModal();
    if (["phn", "eml", "web"].includes(type)) {
      let content = createInput(labels[type], "mdl_ipt_1", ctc["contact"]);
      createModal("Edit " + labels[type], content, () => {
        let ipt1 = $("#mdl_ipt_1").val();
        if (ipt1 === "") alert("Missed one!");
        else {
          _MODEL.updateContact(type, ctc["id"], ent_id, cp, ipt1);
          return true;
        }
      });
    } else if (["add"].includes(type)) {
      let div = $("<div>")
        .append(createInput("Line 1:", "mdl_ipt_1", ctc["ln1"]))
        .append(createInput("Line 2:", "mdl_ipt_2", ctc["ln2"]))
        .append(createInput("Suburb:", "mdl_ipt_3", ctc["sub"]))
        .append(createStateSelector("mdl_ipt_4", ctc["stt"]))
        .append(createInput("Post Code:", "mdl_ipt_5", ctc["pcd"]));

      createModal("Edit " + labels[type], div, () => {
        let ln1 = $("#mdl_ipt_1").val();
        let ln2 = $("#mdl_ipt_2").val() || "";
        let sub = $("#mdl_ipt_3").val();
        let stt = $("#mdl_ipt_4").val();
        let pcd = $("#mdl_ipt_5").val();
        if (ln1 === "" || sub === "" || pcd === "")
          alert("Missed one!");
        else {
          _MODEL.updateContact("add", ctc["id"], ent_id, cp, ln1, ln2, sub, stt, pcd);
          return true;
        }
      });
    }
  });
}

function setDeleteContactListeners(btn, ctc, ent_id, cp) {
  btn.off().click(() => {
    let confirmation = confirm("Delete " + ctc["contact"] + "?");
    if (confirmation) _MODEL.deleteContact(ctc["type"], ctc["id"],
      ent_id, cp);
  });
}

function setRowMetaListeners(see, ctc) {
  see.off().click(() => {
    _MODEL.toggleContent("toggle");
    _MODEL.readEnt(ctc["id"]);
  });
}

function setCreateRoleListener(id, cp) {
  $("#info_role").off().click(() => {
    clearModal();
    let input = createInput("Role in Church:", "mdl_ipt_1");
    let selector = (cp === "p") ? createChurchSelector("mdl_ipt_2") :
      createPeopleSelector("mdl_ipt_2");

    let content = $("<div>").append(input).append(selector);
    createModal("Connect Person to Church", content.html(), () => {
      let ipt1 = $("#mdl_ipt_1").val();
      let ipt2 = $("#mdl_ipt_2").val();
      if (ipt1 === "") alert("Missed one!");
      else {
        if (cp === "c") _MODEL.createRole(ipt1, ipt2, id, cp);
        if (cp === "p") _MODEL.createRole(ipt1, id, ipt2, cp);
        return true;
      }
    });
  });
}

function setUpdateRoleListeners(btn, ctc, ent, cp) {
  btn.off().click(() => {
    clearModal();
    let content = createInput("Role in Church:", "mdl_ipt_1", ctc["role"]);
    createModal("Edit Role in Church", content, () => {
      let ipt1 = $("#mdl_ipt_1").val();
      if (ipt1 === "") alert("Missed one!");
      else {
        _MODEL.updateRole(ipt1, ctc["role_id"], ent["id"], cp);
        return true;
      }
    });
  });
}

function setDeleteRolesListeners(btn, ctc, ent, cp) {
  btn.off().click(() => {
    let confirmation = confirm("Remove connection between '"
      + ctc["name"] + "' and '" + ent["name"] + "'?");
    if (confirmation) _MODEL.deleteRole(ctc["role_id"], ent["id"], cp);
  });
}

/*---- MODALS UTILS ----*/

function clearModal() {
  $("#modal_content").html("");
  $("#modal_close").show();
  $("#modal_cancel").show();
  $("#modal_save").show();
  $("#modal").hide();
}

function createModal(heading, content, callback) {
  $("#modal_heading").html(heading);
  $("#modal_content").html("").append(content);
  let save = $("#modal_save");
  if (callback === undefined) save.hide();
  save.off().on("click", () => {
    if (callback())
      clearModal();
  });
  $("#modal").show();
}

function createInput(label, id = "mdl_ipt_1", val = "") {
  return $("<label class='w3-bar-item'>").html(label)
    .append($("<input >").val(val).attr({
      "id": id, "name": id, "class": "w3-input", "placeholder": label,
      "autocomplete": "no"
    }));
}

function createStateSelector(id = "mdl_ipt_1", val = "") {
  let select = $("<select>").attr({"id": id, "name": id, "class": "w3-input"})
    .append($(`<option value='TAS'>Tasmania</option>`))
    .append($(`<option value='VIC'>Victoria</option>`))
    .append($(`<option value='ACT'>Australia Capital Territory</option>`))
    .append($(`<option value='NSW'>New South Wales</option>`))
    .append($(`<option value='QLD'>Queensland</option>`))
    .append($(`<option value='SA'>South Australia</option>`))
    .append($(`<option value='NT'>Northern Territory</option>`))
    .append($(`<option value='WA'>Western Australia</option>`));
  return $("<label class='w3-bar-item'>").html("State:").append(select.val(val));
}

function createVisibilitySelector(id = "mdl_ipt_1", val = "private") {
  let select = $("<select>").attr({"id": id, "class": "w3-input"});
  if (val === "public") select.append($(`<option>public</option>`, {"value": "public"}))
    .append($(`<option>private</option>`, {"value": "private"}));
  else select.append($(`<option>private</option>`, {"value": "private"}))
    .append($(`<option>public</option>`, {"value": "public"}));
  return $("<label class='w3-bar-item'>").html("Visibility:").append(select);
}

function createRegionSelector(id = "mdl_ipt_1", val = "none") {
  let select = $("<select>").attr({"id": id, "name": id, "class": "w3-input"})
    .append($(`<option value='none'>None</option>`))
    .append($(`<option value='tas_nw'>TAS North West</option>`))
    .append($(`<option value='tas_e'>TAS Eastern</option>`))
    .append($(`<option value='tas_s'>TAS Southern</option>`));
  return $("<label class='w3-bar-item'>").html("Region:").append(select.val(val));
}

function createPeopleSelector(id = "mdl_ipt_1") {
  let select = $("<select>").attr({"id": id, "name": id, "class": "w3-input"});
  let people = _MODEL.getArray("p").sort((p1, p2) => (p1["name"] < p2["name"]) ? -1 : 1);
  for (let per of people)
    select.append($(`<option>`).val(per["id"]).text(per["name"]));
  return $("<label class='w3-bar-item'>").html("Person:").append(select);
}

function createChurchSelector(id = "mdl_ipt_1") {
  let select = $("<select>").attr({"id": id, "name": id, "class": "w3-input"});
  let church = _MODEL.getArray("c").sort((p1, p2) => (p1["name"] < p2["name"]) ? -1 : 1);
  for (let ch of church)
    select.append($(`<option>`).val(ch["id"]).text(ch["name"]));
  return $("<label class='w3-bar-item'>").html("Church:").append(select);
}

function createTextarea(label, id = "mdl_ipt_1", val = "") {
  return $("<label class='w3-bar-item'>").html(label)
    .append($("<textarea>", {
      "id": id, "class": "w3-input w3-border", "placeholder": label, "autocomplete": "no",
      "style": "height: 20vh; resize: none", "maxlength": 1024
    }).val(val));
}