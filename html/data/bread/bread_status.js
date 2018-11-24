var _URL = '../resources/ajax_backend/bread_status_backend.php';
var _interval;
var _region;

$(document).ready(function() {
  $.getJSON(_URL, {q: 'read_regions'}, function(res) {
    $.each(res.regions, function(i, region) {
      $('#sel_rgn').append($('<option />').text(region.name).val(region.id));
    });
  });

  $('#btn_red_rgn').click(function() {
    $('#div_switch').html('');
    _region = $('#sel_rgn').val();
    readRoutes();
    clearInterval(_interval);
    _interval = setInterval(readRoutes, 60 * 1000);
  });
});

function readRoutes() {
  $.getJSON(_URL, {q: 'read_routes', region: _region}, function(region) {
    var div = $('#div_switch').html('');
    div.append(createRegionDiv(region));
  });
}

/**
 * re: [name: String, routes: array<churches>]
 * returns <div></div> containing region info
 */
function createRegionDiv(re) {
  var div = $('<div></div>');
  div.html($('<h1></h1>').text(re.name));
  $.each(re.routes, function(i, route) {
    div.append(createRouteDiv(route));
  });
  return div;
}

/**
 * ro: [name: String, churches: array<churches>]
 * returns <div></div> containing route info
 */
function createRouteDiv(ro) {
  var div = $('<div class=\'w3-row w3-text\'></div>');
  div.append($('<h3></h3>').text(ro.name));
  $.each(ro.churches, function(i, church) {
    div.append(createChurchCard(church));
  });
  return div;
}

/**
 * churches: [name: String, time: String, delivered: Int, address: String,
 * note: String] returns <div></div> containing church info
 */
function createChurchCard(ch) {
  var card = $('<div class=\'w3-col l2 m4 s12 w3-padding w3-cell-row\'></div>');
  //https://stackoverflow.com/questions/19346405/jquery-how-to-get-hhmmss-from-date-object
  var late05 = new Date(new Date().getTime() - (5 * 60 * 1000)).toTimeString().
    split(' ')[0];
  var late20 = new Date(new Date().getTime() - (20 * 60 * 1000)).toTimeString().
    split(' ')[0];
  var late60 = new Date(new Date().getTime() - (60 * 60 * 1000)).toTimeString().
    split(' ')[0];
  var early60 = new Date(
    new Date().getTime() + (60 * 60 * 1000)).toTimeString().split(' ')[0];
  var color = 'w3-pale-yellow';
  if (ch.delivered === 1) color = 'w3-green';
  else if (ch.time < late60) color = 'w3-purple';
  else if (ch.time < late20) color = 'w3-red';
  else if (ch.time < late05) color = 'w3-orange';
  else if (ch.time < early60) color = 'w3-yellow';
  card.append($('<div></div>').attr({
    'class': 'w3-btn w3-cell ' + color,
    'style': 'height: 150px; white-space: normal',
    'title': ['name: ' + ch.name, 'time: ' + ch.time,
              'delivered: ' + ch.delivered].join('\n'),
  }).text([ch.name, ch.time].join('\n')));
  return card;
}