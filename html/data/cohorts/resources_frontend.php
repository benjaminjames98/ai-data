<?php
include_once(dirname(__FILE__) . '/../../../includes/db_connect.php');
include_once(dirname(__FILE__) . '/../../../includes/login_functions.php');

sec_session_start();

if (!permission_check($mysqli, 'per_education')) {
  header('Location: ../index.php');
  exit(0);
} ?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta content='text/html; charset=UTF-8'/>
    <title>Resources</title>
    <script src='resources.js'></script>
    <link rel='stylesheet' href='../resources/styles/w3.css'>
    <link rel='stylesheet' href='../resources/styles/w3-theme-red.css'>
</head>
<body>

<!-- Sidebar -->
<nav class='w3-sidebar w3-bar-block w3-border-right w3-card'
     style='display:none' id='sidebar'>
    <img style='width: 200px; height: 100px' class='w3-bar-item w3-image'
         alt='citynetworks logo'
         src='../resources/media/city_networks_logo.png'/>
    <button onclick="_close('sidebar');" class='w3-bar-item w3-button w3-large'>
        Close &times;
    </button>
    <a href='finance_frontend.php' class='w3-bar-item w3-button'>Finance</a>
    <a href='education_frontend.php' class='w3-bar-item w3-button'>Education</a>
    <a href='resources_frontend.php' class='w3-bar-item w3-button'>Resources</a>
</nav>
<!-- Sidebar -->

<!-- Header -->
<header class='w3-theme w3-card'>
    <button class='w3-button w3-xlarge' onclick="_open('sidebar');">&#9776;
    </button>
</header>
<!-- Header -->
<!-- Payments -->
<section class='w3-card w3-margin w3-mobile'>
    <header class='w3-container w3-theme'>
        <h4 class='w3-left'>Book</h4>
        <button class='w3-button w3-xlarge w3-right' title='create new book'
                onclick="prepModal('mdl_bok_crt');">+
        </button>
        <button class='w3-button w3-xlarge w3-right'
                title='create dispatch document'
                onclick="prepModal('mdl_doc_dld');">
            d
        </button>
        <button class='w3-button w3-xlarge w3-right' title='refresh books'
                onclick='readBooks();'>&#8635;
        </button>
    </header>
    <div id='div_bok_switch' class='w3-responsive'
         style='max-height: 55vh; overflow-y: scroll'></div>
</section>
<!-- Payment -->

<!-- Payees -->
<section class='w3-card w3-margin w3-mobile'>
    <header class='w3-container w3-theme'>
        <h4 class='w3-left'>Programs</h4>
        <button class='w3-button w3-xlarge w3-right' title='create new program'
                onclick="prepModal('mdl_pgm_crt');">+
        </button>
        <button class='w3-button w3-xlarge w3-right' title='refresh programs'
                onclick='readPrograms();'>&#8635;
        </button>
    </header>
    <div id='div_pgm_switch' class='w3-responsive'
         style='max-height: 35vh; overflow-y: scroll'></div>
</section>
<!-- Payees -->
</section>
<!-- Info -->

<!-------------------------- MODALS -------------------------->

<!-- mdl_bok_crt -->
<div id='mdl_bok_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
        <header class='w3-container w3-theme'>
            <button class='w3-button w3-xlarge w3-right'
                    onclick="_close('mdl_bok_crt');">&times;
            </button>
            <h4 class='w3-left'>Create Book</h4>
        </header>
        <div class='w3-padding'>
            <!-- content -->
            <input class='w3-input w3-border w3-section' id='ipt_bok_nam'
                   placeholder='name' maxlength='64'>
            <input class='w3-input w3-border w3-section' id='ipt_bok_des'
                   placeholder='description' maxlength='128'>
            <input class='w3-input w3-border w3-section' id='ipt_bok_prc'
                   placeholder='price' maxlength='11'>
            <div class='w3-row w3-section'>
                <button class='w3-btn w3-theme w3-right' id='btn_bok_crt'
                        onclick='createBook();'>Create
                </button>
                <div id='div_bok_udt_switch'></div>
                <button class='w3-btn w3-theme w3-right w3-margin-right'
                        onclick="_close('mdl_bok_crt');">Cancel
                </button>
            </div>
            <!-- content -->
        </div>
    </div>
</div>
<!-- mdl_bok_crt -->

<!-- mdl_bok_red -->
<div id='mdl_bok_red' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
        <header class='w3-container w3-theme'>
            <button class='w3-button w3-xlarge w3-right'
                    onclick="_close('mdl_bok_red');">&times;
            </button>
            <h4 class='w3-left'>Book Details</h4>
        </header>
        <div class='w3-padding'>
            <!-- content -->
            <div class='w3-row'>
                <p class='w3-third'>Name:</p>
                <p class='w3-rest' id='txt_bok_nam'></p>
            </div>
            <div class='w3-row'>
                <p class='w3-third'>Description:</p>
                <p class='w3-rest' id='txt_bok_des'></p>
            </div>
            <div class='w3-row'>
                <p class='w3-third'>Price:</p>
                <p class='w3-rest' id='txt_bok_prc'></p>
            </div>
            <div class='w3-row w3-section'>
                <button class='w3-btn w3-theme w3-right w3-margin-right'
                        onclick="_close('mdl_bok_red');">Close
                </button>
            </div>
            <!-- content -->
        </div>
    </div>
</div>
<!-- mdl_bok_red -->

<!-- mdl_stk_udt -->
<div id='mdl_stk_udt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
        <header class='w3-container w3-theme'>
            <button class='w3-button w3-xlarge w3-right'
                    onclick="_close('mdl_stk_udt');">&times;
            </button>
            <h4 class='w3-left'>Add to Stock</h4>
        </header>
        <div class='w3-padding'>
            <!-- content -->
            <input class='w3-input w3-border w3-section' id='ipt_stk_num'
                   placeholder='num to add' maxlength='11'>

            <div class='w3-row w3-section'>
                <div id='div_stk_udt_switch'></div>
                <button class='w3-btn w3-theme w3-right w3-margin-right'
                        onclick="_close('mdl_stk_udt');">Close
                </button>
            </div>
            <!-- content -->
        </div>
    </div>
</div>
<!-- mdl_stk_udt -->

<!-- mdl_pgm_crt -->
<div id='mdl_pgm_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
        <header class='w3-container w3-theme'>
            <button class='w3-button w3-xlarge w3-right'
                    onclick="_close('mdl_pgm_crt');">&times;
            </button>
            <h4 class='w3-left'>Create Program</h4>
        </header>
        <div class='w3-padding'>
            <!-- content -->
            <input class='w3-input w3-border w3-section' id='ipt_pgm_nam'
                   placeholder='name' maxlength='64'>
            <input class='w3-input w3-border w3-section' id='ipt_pgm_des'
                   placeholder='description' maxlength='128'>
            <div class='w3-row w3-section'>
                <button class='w3-btn w3-theme w3-right' id='btn_pgm_crt'
                        onclick='createProgram();'>Create
                </button>
                <button class='w3-btn w3-theme w3-right w3-margin-right'
                        onclick="_close('mdl_pgm_crt');">Cancel
                </button>
            </div>
            <!-- content -->
        </div>
    </div>
</div>
<!-- mdl_pgm_crt -->

<!-- mdl_doc_dld -->
<div id='mdl_doc_dld' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
        <header class='w3-container w3-theme'>
            <button class='w3-button w3-xlarge w3-right'
                    onclick="_close('mdl_doc_dld');">&times;
            </button>
            <h4 class='w3-left'>Download Dispatch Document</h4>
        </header>
        <div class='w3-padding'>
            <!-- content -->
            <input class='w3-input w3-border w3-section' id='ipt_doc_nam'
                   placeholder='name'>
            <input class='w3-input w3-border w3-section' id='ipt_doc_dat'
                   placeholder='date'>
            <input class='w3-input w3-border w3-section' id='ipt_doc_ln1'
                   placeholder='line 1'>
            <input class='w3-input w3-border w3-section' id='ipt_doc_ln2'
                   placeholder='line 2'>
            <input class='w3-input w3-border w3-section' id='ipt_doc_sub'
                   placeholder='suburb'>
            <select class='w3-input w3-section' id='ipt_doc_stt'>
                <option value='TAS'>TAS</option>
                <option value='VIC'>VIC</option>
                <option value='NSW'>NSW</option>
                <option value='ACT'>ACT</option>
                <option value='QLD'>QLD</option>
                <option value='SA'>SA</option>
                <option value='NT'>NT</option>
                <option value='WA'>WA</option>
            </select>
            <input class='w3-input w3-border w3-section' id='ipt_doc_pcd'
                   placeholder='Post Code'>
            <div class='w3-section'>
                <p class='w3-section'>id>quantity</p>
                <textarea id='ipt_doc_con' class='w3-input w3-border'
                          maxlength='1024' autocomplete='false' rows='8'
                          placeholder='books'
                          style='resize: vertical'> </textarea>
            </div>
            <div class='w3-row w3-section'>
                <button class='w3-btn w3-theme w3-right' id='btn_doc_dld'
                        onclick='downloadDocument();'>Download
                </button>
                <button class='w3-btn w3-theme w3-right w3-margin-right'
                        onclick="_close('mdl_doc_dld');">Cancel
                </button>
            </div>
            <!-- content -->
        </div>
    </div>
</div>
<!-- mdl_doc_dld -->
</body>
</html>