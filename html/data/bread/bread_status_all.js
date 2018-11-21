var _URL = "../../../ajax_backend/bread_status_all_backend.php";

$(document).ready(function () {
  readRegions();
  setInterval(readRegions, 60 * 1000);
});

function readRegions() {
  var div = $("#div_switch").html("");
  $.post(_URL, {q: "read_regions"}, function (res) {
    $.each(res.regions, function (i, region) {
      div.append(createRegionDiv(region));
    });
  }, "json");
}

function createRegionDiv(re) {
  var div = $("<div class=\"w3-row w3-text\"></div>");
  div.html($("<h1></h1>").text(re.name));
  $.each(re.churches, function (i, church) {
    div.append(createChurchCard(church));
  });
  return div;
}

function createChurchCard(ch) {
  var card = $("<div class='w3-col l1 m4 s12 w3-padding w3-cell-row'></div>");
  //https://stackoverflow.com/questions/19346405/jquery-how-to-get-hhmmss-from-date-object
  var late05 = new Date(new Date().getTime() - (5 * 60 * 1000)).toTimeString().split(" ")[0];
  var late20 = new Date(new Date().getTime() - (20 * 60 * 1000)).toTimeString().split(" ")[0];
  var late60 = new Date(new Date().getTime() - (60 * 60 * 1000)).toTimeString().split(" ")[0];
  var early60 = new Date(new Date().getTime() + (60 * 60 * 1000)).toTimeString().split(" ")[0];
  var color = "w3-pale-yellow";
  if (ch.delivered === 1) color = "w3-green";
  else if (ch.time < late60) color = "w3-purple";
  else if (ch.time < late20) color = "w3-red";
  else if (ch.time < late05) color = "w3-orange";
  else if (ch.time < early60) color = "w3-yellow";
  card.append($("<div></div>").attr({
    "class": "w3-btn w3-cell " + color,
    "style": "height: 155px; white-space: normal",
    "title": ["name: " + ch.name, "time: " + ch.time, "delivered: " + ch.delivered].join("\n")
  }).text([ch.name, ch.time].join("\n")));
  return card;
}