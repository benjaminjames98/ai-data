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
  <meta charset='UTF-8'>
  <title>Education</title>
  <script src='education.js'></script>
  <link rel='stylesheet' href='../../../styles/w3.css'>
  <link rel='stylesheet' href='../../../styles/w3-theme-red.css'>
</head>
<body>
  <!-- Sidebar -->
  <nav class='w3-sidebar w3-bar-block w3-border-right w3-card' style='display:none' id='sidebar'>
    <img style='width: 200px; height: 100px' class='w3-bar-item w3-image' alt='citynetworks logo'
         src='../../../media/city_networks_logo.png'/>
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
    <!-- Students -->
    <section class='w3-card w3-margin w3-third w3-mobile'>
      <header class='w3-container w3-theme'>
        <h4 class='w3-left'>Students</h4>
        <button class='w3-button w3-xlarge w3-right' title='create new students' onclick="prepModal('mdl_sdt_crt');">+
        </button>
        <button class='w3-button w3-xlarge w3-right' title='refresh students' onclick='readStudents();'>&#8635;</button>
      </header>
      <div id='div_sdt_switch' class='w3-responsive' style='max-height: 20vh; overflow-y: scroll'></div>
    </section>
    <!-- Students -->

    <!-- Cohort -->
    <section class='w3-card w3-margin w3-rest w3-mobile'>
      <header class='w3-container w3-theme'>
        <h4 class='w3-left'>Cohort</h4>
        <button class='w3-button w3-xlarge w3-right' title='create new cohort' onclick="prepModal('mdl_cht_crt');">+
        </button>
        <button class='w3-button w3-xlarge w3-right' title='refresh cohort' onclick='readCohorts();'>&#8635;</button>
      </header>
      <div id='div_cht_switch' class='w3-responsive' style='max-height: 20vh; overflow-y: scroll'></div>
    </section>
    <!-- Cohort -->
  </div>

  <!-- Info -->
  <section class='w3-card w3-margin'>
    <!-- Student Info -->
    <div id='div_sdt' style='display: block'>
      <div class='w3-row'>
        <header class='w3-container w3-theme'>
          <h4 class='w3-left'>Student Details</h4>
          <div id='div_sdt_ref_switch'></div>
        </header>
        <div class='w3-row'>
          <div class='w3-half'>
            <!-- Details -->
            <section class='w3-card w3-margin'>
              <header class='w3-text-theme w3-margin'>
                <h5 class='w3-left'>Info</h5>
              </header>
              <div class='w3-margin'>
                <input id='ipt_sdt_nam' placeholder='name' class='w3-input w3-border w3-section' type='text'>
                <textarea id='ipt_sdt_not' class='w3-input w3-border-left' maxlength='1024' autocomplete='false'
                          rows='8'
                          placeholder='notes' style='resize: none'> </textarea>
                <div id='div_sdt_sav_switch'></div>
              </div>
            </section>
            <!-- Details -->
          </div>
          <div class='w3-rest'>
            <!-- Payee -->
            <section class='w3-card w3-margin'>
              <header class='w3-text-theme w3-margin'>
                <h5 class='w3-left'>Payee</h5>
              </header>
              <div class='w3-margin'>
                <select class='w3-input w3-section' id='ipt_sdt_pye' name='payee'>
                  <option value='-'>pye1</option>
                  <option value='-'>pye2</option>
                </select>
                <div id='div_sdt_chg_switch'></div>
              </div>
            </section>
            <!-- Payee -->
            <!-- Contacts -->
            <section class='w3-card w3-margin'>
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
        </div>
      </div>
    </div>
    <!-- Student Info -->
    <!-- Cohort Info -->
    <div id='div_cht' style='display: none'>
      <div class='w3-row'>
        <header class='w3-container w3-theme'>
          <h4 class='w3-left'>Cohort Details</h4>
          <div id='div_cht_ref_switch'></div>
        </header>
        <div class='w3-row'>
          <div class='w3-half'>
            <!-- Details -->
            <section class='w3-card w3-margin'>
              <header class='w3-text-theme w3-container'>
                <h5 class='w3-left'>Info</h5>
              </header>
              <div class='w3-container'>
                <div class='w3-section'>
                  <label for='ipt_cht_nam'>Name</label>
                  <input id='ipt_cht_nam' placeholder='name' class='w3-input w3-border' type='text'>
                </div>
                <div class='w3-section'>
                  <label for='ipt_cht_umb'>Unlisted Members</label>
                  <input id='ipt_cht_umb' placeholder='unlisted members' class='w3-input w3-border' type='text'>
                </div>
                <div class='w3-section'>
                  <input id='ipt_cht_atv' class='w3-check' type='checkbox'>
                  <label for='ipt_cht_atv'>Active</label>
                </div>
                <div class='w3-section w3-row-padding'>
                  <div class='w3-half w3-mobile'>
                    <label for='ipt_cht_cbk'>Current Book:</label>
                    <select class='w3-input' id='ipt_cht_cbk' name='option'>
                      <option value='no'>Acts</option>
                      <option value='yes'>Paulines</option>
                    </select>
                  </div>
                  <div class='w3-rest w3-mobile'>
                    <label for='ipt_cht_nbk'>Next Book:</label>
                    <select class='w3-input' id='ipt_cht_nbk' name='option'>
                      <option value='no'>Acts</option>
                      <option value='yes'>Paulines</option>
                    </select>
                  </div>
                </div>
                <div class='w3-section w3-row-padding'>
                  <div class='w3-half w3-mobile'>
                    <label for='ipt_cht_ldr'>Leader</label>
                    <select class='w3-input' id='ipt_cht_ldr' name='option'>
                      <option value='no'>1 Ben</option>
                      <option value='yes'>2 Kiran</option>
                    </select>
                  </div>
                  <div class='w3-rest w3-mobile'>
                    <label for='ipt_cht_pgm'>Program:</label>
                    <select class='w3-input' id='ipt_cht_pgm' name='option'>
                      <option value='no'>B.Min</option>
                      <option value='yes'>M.Min</option>
                    </select>
                  </div>
                </div>
                <div id='div_cht_sav_switch'></div>
              </div>
            </section>
            <!-- Details -->
          </div>
          <div class='w3-rest'>
            <!-- Note -->
            <section class='w3-card w3-margin'>
              <header class='w3-text-theme w3-container'>
                <h5 class='w3-left'>Note</h5>
              </header>
              <div class='w3-margin'>
             <textarea id='ipt_cht_not' class='w3-input w3-border-left' maxlength='1024' autocomplete='false' rows='6'
                       placeholder='notes' style='resize: none'> </textarea>
                <div id='div_cht_not_switch'></div>
              </div>
            </section>
            <!-- Note -->
            <!-- Students -->
            <section class='w3-card w3-margin'>
              <header class='w3-container w3-text-theme'>
                <h5 class='w3-left'>Students</h5>
                <button class='w3-button w3-large w3-right' title='create new email'
                        onclick="prepModal('mdl_cht_sdt');">S
                </button>
              </header>
              <div id='div_cht_sdt_switch' style='max-height: 20vh; overflow-y: scroll'></div>
            </section>
            <!-- Students -->
          </div>
        </div>
      </div>
    </div>
    <!-- Cohort Info -->
  </section>
  <!-- Info -->

  <!-------------------------- MODALS -------------------------->

  <!-- mdl_sdt_crt -->
  <div id='mdl_sdt_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_sdt_crt');">&times;</button>
        <h4 class='w3-left'>Create Student</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-section'>
          <label for='ipt_sdt_crt_nam'>Name</label>
          <input class='w3-input w3-border' id='ipt_sdt_crt_nam' placeholder='name'>
        </div>
        <div class='w3-section'>
          <label for='ipt_sdt_crt_pye'>Payee</label>
          <select class='w3-input' id='ipt_sdt_crt_pye' name='option'>
            <option value='-'>pye1</option>
            <option value='-'>pye2</option>
          </select>
        </div>
        <div class='w3-row w3-section'>
          <button class='w3-btn w3-theme w3-right w3-margin-left' onclick='createStudent();'>Create</button>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_sdt_crt');">Cancel</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_sdt_crt -->


  <!-- mdl_cht_crt -->
  <div id='mdl_cht_crt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_cht_crt');">&times;</button>
        <h4 class='w3-left'>Create Cohort</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-section'>
          <label for='ipt_cht_crt_nam'>Name</label>
          <input id='ipt_cht_crt_nam' placeholder='name' class='w3-input w3-border' type='text'>
        </div>
        <div class='w3-section'>
          <label for='ipt_cht_crt_ldr'>Leader</label>
          <select class='w3-input' id='ipt_cht_crt_ldr' name='option'>
            <option value='no'>1 Ben</option>
            <option value='yes'>2 Kiran</option>
          </select>
        </div>
        <div class='w3-section'>
          <label for='ipt_cht_crt_umb'>Unlisted Members</label>
          <input id='ipt_cht_crt_umb' placeholder='unlisted members' class='w3-input w3-border' type='text'>
        </div>
        <div class='w3-section'>
          <input id='ipt_cht_crt_atv' class='w3-check' type='checkbox'>
          <label for='ipt_cht_crt_atv'>Active</label>
        </div>
        <div class='w3-section'>
          <label for='ipt_cht_crt_cbk'>Current Book:</label>
          <select class='w3-input' id='ipt_cht_crt_cbk' name='option'>
            <option value='no'>Acts</option>
            <option value='yes'>Paulines</option>
          </select>
        </div>
        <div class='w3-section'>
          <label for='ipt_cht_crt_nbk'>Next Book:</label>
          <select class='w3-input' id='ipt_cht_crt_nbk' name='option'>
            <option value='no'>Acts</option>
            <option value='yes'>Paulines</option>
          </select>
        </div>
        <div class='w3-section'>
          <label for='ipt_cht_crt_pgm'>Program:</label>
          <select class='w3-input' id='ipt_cht_crt_pgm' name='option'>
            <option value='no'>B.Min</option>
            <option value='yes'>M.Min</option>
          </select>
        </div>
        <div class='w3-row w3-section'>
          <button class='w3-btn w3-theme w3-right w3-margin-left' onclick='createCohort();'>Create</button>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_cht_crt');">Cancel</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_cht_crt -->

  <!-- mdl_cht_sdt -->
  <div id='mdl_cht_sdt' class='w3-modal'>
    <div class='w3-modal-content w3-card'>
      <header class='w3-container w3-theme'>
        <button class='w3-button w3-xlarge w3-right' onclick="_close('mdl_cht_sdt');">&times;</button>
        <h4 class='w3-left'>Add Student</h4>
      </header>
      <div class='w3-padding'>
        <!-- content -->
        <div class='w3-section'>
          <label for='ipt_cht_sdt_nam'>Student</label>
          <select class='w3-input' id='ipt_cht_sdt_nam' name='option'>
            <option value='no'>1 Ben</option>
            <option value='yes'>2 Kiran</option>
          </select>
        </div>
        <div class='w3-row w3-section'>
          <div id='div_cht_sdt_mdl_switch'></div>
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_cht_sdt');">Cancel</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_cht_sdt -->

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
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_phn_crt');">Cancel</button>
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
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_eml_crt');">Cancel</button>
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
        <h4 class='w3-left'>Create Number</h4>
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
          <button class='w3-btn w3-theme w3-right w3-margin-right' onclick="_close('mdl_add_crt');">Cancel</button>
        </div>
        <!-- content -->
      </div>
    </div>
  </div>
  <!-- mdl_add_crt -->
</body>
</html>