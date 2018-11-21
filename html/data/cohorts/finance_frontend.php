<?php
include_once(dirname(__FILE__).'/../../../includes/db_connect.php');
include_once(dirname(__FILE__).'/../../../includes/login_functions.php');

sec_session_start();

if (!permission_check($mysqli, 'per_education')) {
  header('Location: ../index.php');
  exit(0);
} ?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta content='text/html; charset=UTF-8'/>
  <title>Finance</title>
  <script src='finance.js'></script>
  <link rel='stylesheet' href='../resources/styles/w3.css'>
  <link rel='stylesheet' href='../resources/styles/w3-theme-red.css'>
</head>
<body>

  <!-- Sidebar -->
  <nav class='w3-sidebar w3-bar-block w3-border-right w3-card' style='display:none' id='sidebar'>
    <img style='width: 200px; height: 100px' class='w3-bar-item w3-image' alt='citynetworks logo'
         src='../resources/media/city_networks_logo.png'/>
    <button onclick="_close('sidebar');" class='w3-bar-item w3-button w3-large'>Close &times;</button>
    <a href='finance_frontend.php' class='w3-bar-item w3-button'>Finance</a>
    <a href='education_frontend.php' class='w3-bar-item w3-button'>Education</a>
    <a href='resources_frontend.php' class='w3-bar-item w3-button'>Resources</a>
  </nav>
  <!-- Sidebar -->

  <!-- Header -->
  <header class='w3-theme w3-card'>
    <button class='w3-button w3-xlarge' onclick="_open('sidebar');">&#9776;</button>
  </header>
  <!-- Header -->

  <div class='w3-row'>
    <!-- Payments -->
    <section class='w3-card w3-margin w3-third w3-mobile'>
      <header class='w3-container w3-theme'>
        <h4 class='w3-left'>Payments</h4>
        <button class='w3-button w3-xlarge w3-right' title='create new payment'
                onclick="prepModal('mdl_pmt_crt');">+
        </button>
        <button class='w3-button w3-xlarge w3-right' title='refresh payments' onclick='readPayments();'>
          &#8635;
        </button>
      </header>
      <div id='div_pmt_switch' class='w3-responsive' style='max-height: 20vh; overflow-y: scroll'></div>
    </section>
    <!-- Payment -->

    <!-- Payees -->
    <section class='w3-card w3-margin w3-rest w3-mobile'>
      <header class='w3-container w3-theme'>
        <h4 class='w3-left'>Payees</h4>
        <button class='w3-button w3-xlarge w3-right' title='create new payee'
                onclick="prepModal('mdl_pye_crt');">+
        </button>
        <button class='w3-button w3-xlarge w3-right' title='refresh payees' onclick='readPayees();'>&#8635;
        </button>
      </header>
      <div id='div_pye_switch' class='w3-responsive' style='max-height: 20vh; overflow-y: scroll'></div>
    </section>
    <!-- Payees -->
  </div>

  <!-- Info -->
  <section class='w3-card w3-margin'>
    <header class='w3-container w3-theme'>
      <h4 class='w3-left'>Info</h4>
      <div id='div_inf_ref_switch'></div>
    </header>
    <div class='w3-row'>
      <!-- Details -->
      <section class='w3-card w3-margin w3-padding w3-third w3-mobile'>
        <div class='w3-row'>
          <input id='ipt_det_nam' placeholder='name' class='w3-input w3-threequarter w3-border w3-mobile'
                 type='text'>
          <div id='div_det_sav_switch'></div>
        </div>
        <div class='w3-row w3-section'>
          <input id='ipt_det_abn' placeholder='ABN' class='w3-input w3-threequarter w3-border w3-mobile'
                 type='text'>
        </div>
        <p id='txt_det_bal'>Balance: </p>
        <p id='txt_det_upd'>Unpaid: </p>
      </section>
      <!-- Details -->

      <!-- Contacts -->
      <section class='w3-card w3-margin w3-rest w3-mobile'>
        <header class='w3-container w3-text-theme'>
          <h5 class='w3-left'>Contacts</h5>
          <button class='w3-button w3-large w3-right' title='create new email'
                  onclick="prepModal('mdl_crt_ctc', undefined, 'eml');">E
          </button>
          <button class='w3-button w3-large w3-right' title='create new phone'
                  onclick="prepModal('mdl_crt_ctc', undefined, 'phn');">P
          </button>
          <button class='w3-button w3-large w3-right' title='create new address'
                  onclick="prepModal('mdl_crt_ctc', undefined, 'add');">A
          </button>
        </header>
        <div id='div_ctc_switch' style='max-height: 20vh; overflow-y: scroll'></div>
      </section>
      <!-- Contacts -->
    </div>
    <div class='w3-row'>
      <!-- Invoices -->
      <section class='w3-card w3-margin w3-half w3-mobile'>
        <header class='w3-container w3-text-theme'>
          <h5 class='w3-left'>Invoices</h5>
          <button class='w3-button w3-large w3-right' title='create new invoice'
                  onclick="prepModal('mdl_ivc_crt');">+
          </button>
        </header>
        <div id='div_ivc_switch' style='max-height: 20vh; overflow-y: scroll'></div>
      </section>
      <!-- Invoices -->

      <!-- Receipts -->
      <section class='w3-card w3-margin w3-rest w3-mobile'>
        <header class='w3-container w3-text-theme'>
          <h5 class='w3-left'>Receipts</h5>
          <button class='w3-button w3-large w3-right' title='create new receipts'
                  onclick="prepModal('mdl_rct_crt');">+
          </button>
        </header>
        <div id='div_rct_switch' style='max-height: 20vh; overflow-y: scroll'></div>
      </section>
      <!-- Receipts -->
    </div>
  </section>
  <!-- Info -->

  <!-------------------------- MODALS -------------------------->

  <!-- mdl_pmt_crt -->
  <div id='mdl_pmt_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_pmt_crt');">&times;</button>
        <h4 class='w3-left'>Create Payment</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <input class='w3-input w3-border w3-section' id='ipt_pmt_des' placeholder='description' maxlength='128'>
        <input class='w3-input w3-border w3-section' id='ipt_pmt_amt' placeholder='amount' maxlength='20'>
        <input class='w3-input w3-border w3-section' id='ipt_pmt_gst' placeholder='gst' maxlength='20'>
        <div class='w3-row w3-section'>
          <button class='w3-btn w3-theme w3-right w3-margin-left' onclick='createPayment();'>Create</button>
          <button class='w3-btn w3-theme w3-right' onclick="_close('mdl_pmt_crt');">Cancel</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_pmt_crt -->

  <!-- mdl_pmt_red -->
  <div id='mdl_pmt_red' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_pmt_red');">&times;
        </button>
        <h4 class='w3-left'>Payment Details</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-row'>
          <p class='w3-third'>Payment Number:</p>
          <p class='w3-rest' id='txt_pmt_num'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Date:</p>
          <p class='w3-rest' id='txt_pmt_dat'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Description:</p>
          <p class='w3-rest' id='txt_pmt_des'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Amount:</p>
          <p class='w3-rest' id='txt_pmt_amt'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>GST:</p>
          <p class='w3-rest' id='txt_pmt_gst'></p>
        </div>
        <div class='w3-row w3-section'>
          <button class='w3-btn w3-theme w3-right' onclick="_close('mdl_pmt_red');">Close</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_pmt_red -->

  <!-- mdl_pye_crt -->
  <div id='mdl_pye_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_pye_crt');">&times;</button>
        <h4 class='w3-left'>Create Payee</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <input class='w3-input w3-border w3-section' id='ipt_pye_nam' placeholder='name' maxlength='64'>
        <input class='w3-input w3-border w3-section' id='ipt_pye_abn' placeholder='ABN' maxlength='11'>
        <div class='w3-row w3-section'>
          <button class='w3-btn w3-theme w3-right w3-margin-left' onclick='createPayee();'>Create</button>
          <button class='w3-btn w3-theme w3-right' onclick="_close('mdl_pye_crt');"> Close</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_pye_crt -->

  <!-- mdl_pye_sdt -->
  <div id='mdl_pye_sdt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_pye_sdt');">&times;</button>
        <h4 class='w3-left'>Students</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-row'>
          <div id='div_pye_sdt_switch' class='w3-responsive'
               style='max-height: 50vh; overflow-y: scroll'></div>
        </div>
        <div class='w3-row w3-section'>
          <button class='w3-btn w3-theme w3-right' onclick="_close('mdl_pye_sdt');"> Close</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_pye_sdt -->

  <!-- mdl_rct_crt -->
  <div id='mdl_rct_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_rct_crt');">&times;</button>
        <h4 class='w3-left'>Create Receipt</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <select class='w3-input w3-section' id='ipt_rct_des1' name='option'>
          <option value='general'>general</option>
          <option value='invoice'>invoice</option>
          <option value='correction'>correction</option>
        </select>
        <input class='w3-input w3-border w3-section' id='ipt_rct_des2' placeholder='description'>
        <input class='w3-input w3-border w3-section' id='ipt_rct_amt' placeholder='amount'>
        <label class='w3-text-theme'>correction:</label>
        <select class='w3-input w3-section' id='ipt_rct_cor' name='option'>
          <option value='no'>No</option>
          <option value='yes'>Yes</option>
        </select>
        <div class='w3-row w3-section'>
          <div id='div_rct_crt_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_rct_crt');">Cancel
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_rct_crt -->

  <!-- mdl_rct_red -->
  <div id='mdl_rct_red' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_rct_red');">&times;</button>
        <h4 class='w3-left'>Receipt Details</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-row'>
          <p class='w3-third'>Receipt Number:</p>
          <p class='w3-rest' id='txt_rct_num'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Date:</p>
          <p class='w3-rest' id='txt_rct_dat'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Description:</p>
          <p class='w3-rest' id='txt_rct_des'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Amount:</p>
          <p class='w3-rest' id='txt_rct_amt'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Correction:</p>
          <p class='w3-rest' id='txt_rct_cor'></p>
        </div>
        <div class='w3-row w3-section'>
          <div id='div_rct_dld_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_rct_red');"> Close
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_rct_red -->

  <!-- mdl_ivc_crt -->
  <div id='mdl_ivc_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_ivc_crt');">&times;</button>
        <h4 class='w3-left'>Create Invoice</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <p class='w3-section'>Charges:</p>
        <div class='w3-section'>
          <p class='w3-section'>description>quantity>price>gst</p>
          <textarea id='ipt_ivc_chg' class='w3-input w3-border' maxlength='1024' autocomplete='false' rows='8'
                    placeholder='notes' style='resize: vertical'> </textarea>
        </div>
        <input class='w3-input w3-border w3-section' id='ipt_ivc_due' placeholder='due days (default 15)'>
        <div class='w3-row w3-section'>
          <div id='div_ivc_crt_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_ivc_crt');">Cancel
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_ivc_crt -->

  <!-- mdl_ivc_red -->
  <div id='mdl_ivc_red' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_ivc_red');">&times;</button>
        <h4 class='w3-left'>Invoice Details</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-row'>
          <p class='w3-third'>Invoice Number:</p>
          <p class='w3-rest' id='txt_ivc_num'></p>
        </div>
        <div class='w3-row'>
          <p class='w3-third'>Amount:</p>
          <p class='w3-rest' id='txt_ivc_amt'></p>
        </div>
        <div class='w3-row w3-section'>
          <div id='div_ivc_dld_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_ivc_red');">Close
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_ivc_red -->

  <!-- mdl_phn_crt -->
  <div id='mdl_phn_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_phn_crt');">&times;</button>
        <h4 class='w3-left'>Create Phone</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <input class='w3-input w3-border w3-section' id='ipt_phn_phn' placeholder='phone number'>
        <div class='w3-row w3-section'>
          <div id='div_phn_crt_switch'></div>
          <div id='div_phn_udt_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_phn_crt');">Cancel
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_phn_crt -->

  <!-- mdl_eml_crt -->
  <div id='mdl_eml_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_eml_crt');">&times;</button>
        <h4 class='w3-left'>Create Email</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <input class='w3-input w3-border w3-section' id='ipt_eml_eml' placeholder='email'>
        <div class='w3-row w3-section'>
          <div id='div_eml_crt_switch'></div>
          <div id='div_eml_udt_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_eml_crt');">Cancel
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_eml_crt -->

  <!-- mdl_add_crt -->
  <div id='mdl_add_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_add_crt');">&times;</button>
        <h4 class='w3-left'>Create Address</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <input class='w3-input w3-border w3-section' id='ipt_add_ln1' placeholder='line 1'>
        <input class='w3-input w3-border w3-section' id='ipt_add_ln2' placeholder='line 2'>
        <input class='w3-input w3-border w3-section' id='ipt_add_sub' placeholder='suburb'>
        <select class='w3-input w3-section' id='ipt_add_stt'>
          <option value='TAS'>TAS</option>
          <option value='VIC'>VIC</option>
          <option value='NSW'>NSW</option>
          <option value='ACT'>ACT</option>
          <option value='QLD'>QLD</option>
          <option value='SA'>SA</option>
          <option value='NT'>NT</option>
          <option value='WA'>WA</option>
        </select>
        <input class='w3-input w3-border w3-section' id='ipt_add_pcd' placeholder='Post Code'>
        <div class='w3-row w3-section'>
          <div id='div_add_crt_switch'></div>
          <div id='div_add_udt_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_add_crt');">Cancel
          </button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_add_crt -->
</body>
</html>