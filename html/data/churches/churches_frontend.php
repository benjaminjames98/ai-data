<?php
include_once(dirname(__FILE__) . '/../../../includes/db_connect.php');
include_once(dirname(__FILE__) . '/../../../includes/login_functions.php');

sec_session_start();

if (!permission_check($mysqli, 'per_churches')) {
  header('Location: ../index.php');
  exit(0);
} ?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Churches</title>
    <link rel='stylesheet'
          href='https://use.fontawesome.com/releases/v5.0.13/css/all.css'
          integrity='sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp'
          crossorigin='anonymous'>
    <link rel='stylesheet' href='../resources/styles/w3-theme-green.css'>
    <link rel='stylesheet' href='../resources/styles/w3.css'>
    <link rel='stylesheet' href='churches.css'>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    <script src='../resources/js/utils.js'></script>
    <script src='churches.js'></script>
</head>
<body class='w3-theme'>
<div id='sidebar' class='w3-white w3-bar-block w3-card w3-animate-left'>
    <div class='w3-bar-item w3-border-bottom w3-animate-left'>
        <button id='close_sidebar'
                class='w3-text-red w3-button w3-xlarge w3-animate-left'
                onclick="_close('sidebar')" title='close_sidebar'
                style='width: 60px'>
            <i class='fas fa-times-circle'></i>
        </button>
        <button id='church_create'
                class='w3-text-theme w3-button w3-xlarge w3-animate-left'
                title='new church' style='width: 60px'>
            <i class='fas fa-church'></i>
        </button>
        <button id='person_create'
                class='w3-text-theme w3-button w3-xlarge w3-animate-left'
                title='new person' style='width: 60px'><i
                    class='fas fa-user'></i>
        </button>
        <button id='toggle_content'
                class='w3-text-theme w3-button w3-xlarge w3-animate-left'
                title='switch between churches and people' style='width: 60px'>
            <i class='fas fa-exchange-alt'></i>
        </button>
        <button id='refresh_db'
                class='w3-text-theme w3-button w3-xlarge w3-animate-left'
                title='refresh data' style='width: 60px'><i
                    class='fas fa-sync'></i>
        </button>
        <button id='show_emails'
                class='w3-text-theme w3-button w3-xlarge w3-animate-left'
                title='show emails of visible churches' style='width: 60px'>
            <i class='fas fa-envelope'></i>
        </button>
    </div>
    <!---- Toggle ---->

    <!---- Toggle ---->
    <!---- Filters ---->

    <div id='entity_search'
         class='w3-bar-item w3-border-bottom w3-animate-left'>
        <input class='w3-input' type='text' placeholder='Search..'
               name='search'>
    </div>
    <div id='filter_switch'></div>

    <!---- Filters ---->
</div>

<div id='infobar' class='w3-white w3-bar-block w3-card w3-animate-right'>
    <!---- Info ---->
    <section id='info' class='shake w3-bar-item w3-bar-block w3-card'
             style='padding: 0'>
        <section class='w3-bar-item w3-bar-block w3-card' style='padding: 0'>
            <section class='w3-bar-item w3-bar-block w3-card'
                     style='padding: 0'>
                <!---- Details ---->
                <div class='w3-bar-item w3-row w3-card' style='margin-top: 0'>
                    <h5 id='info_header'
                        class='w3-xlarge w3-col l6 m6 s12'></h5>
                    <button class='w3-button w3-xlarge w3-right-align w3-right w3-col w3-text-red'
                            onclick="_close('infobar')" title='close_infobar'
                            style='width: 60px'
                            id='close_infobar'>
                        <i class='fas fa-times-circle'></i>
                    </button>
                    <button class='w3-button w3-xlarge w3-right-align w3-right w3-col w3-text-theme'
                            title='refresh' style='width: 60px'
                            id='refresh_entity'>
                        <i class='fas fa-sync'></i>
                    </button>
                    <button class='w3-button w3-xlarge w3-right-align w3-right w3-col w3-text-theme'
                            title='scroll to' style='width: 60px'
                            id='scroll_btn'>
                        <i class='fas fa-eye'></i>
                    </button>
                </div>
                <div id='details_switch'></div>
                <button id='details_save' class='w3-bar-item w3-btn w3-theme'
                        style='text-align: center'>
                    Save
                </button>

                <!---- Details ---->
            </section>
            <!---- Contacts ---->

            <div class='w3-bar-item' style='padding: 0'>
                <button id='church_website'
                        class='w3-button w3-xlarge w3-right w3-text-theme'
                        title='new website'><i class='fas fa-globe'></i>
                </button>
                <button id='church_phone'
                        class='w3-button w3-xlarge w3-right w3-text-theme'
                        title='new phone'><i class='fas fa-phone'></i>
                </button>
                <button id='church_email'
                        class='w3-button w3-xlarge w3-right w3-text-theme'
                        title='new email'><i class='fas fa-envelope'></i>
                </button>
                <button id='church_address'
                        class='w3-button w3-xlarge w3-right w3-text-theme'
                        title='new address'><i class='fas fa-home'></i>
                </button>
            </div>
            <table id='contacts_switch' class='w3-table w3-bordered'
                   style='width: 100%; table-layout: fixed'></table>

            <!---- Contacts ---->
        </section>
        <!---- Roles ---->

        <div class='w3-bar-item' style='padding: 0'>
            <button id='info_role'
                    class='w3-button w3-xlarge w3-right w3-text-theme'
                    title='connect person'><i class='fas fa-user'></i>
            </button>
        </div>
        <table id='roles_switch' class='w3-table w3-bordered'
               style='width: 100%; table-layout: fixed'></table>

        <!---- Roles---->
    </section>

    <!---- Info ---->
</div>

<div class='b2-main'>
    <header class='b2-sticky-top w3-theme-d1 w3-card w3-container'>
        <button class='w3-button w3-xlarge' onclick="_open('sidebar')">
            <i class='fas fa-bars'></i>
        </button>
    </header>
    <!---- Content ---->
    <div id='entity_div' style='overflow-y: auto'>
        <ul id='entity_list' class='w3-container'
            style='list-style-type: none;'>
        </ul>
    </div>

    <!---- Content ---->
</div>
<!---- Modal ---->

<div id='modal' class='w3-modal'>
    <div class='w3-modal-content w3-card w3-white'>
        <header class='w3-theme w3-panel'>
            <h3 id='modal_heading' class='w3-xlarge'>Modal</h3>
            <span id='modal_close' onclick='clearModal()'
                  class='w3-button w3-xlarge w3-display-topright'>
        <i class='fas fa-times'></i>
      </span>
        </header>
        <section id='modal_content' class='w3-panel w3-bar-block'>
            <label class='w3-bar-item'>Name:
                <input id='input_1' class='w3-input' placeholder='name'
                       autocomplete='no'>
            </label>
        </section>
        <section class='w3-panel w3-padding'>
            <button id='modal_save'
                    class='w3-theme w3-btn w3-right w3-margin-left'>Save
            </button>
            <button id='modal_cancel' class='w3-grey w3-btn w3-right'
                    onclick='clearModal()'>Close
            </button>
        </section>
    </div>
</div>

<!---- Modal ---->
</body>
</html>