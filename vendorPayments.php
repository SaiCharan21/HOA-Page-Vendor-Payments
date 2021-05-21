<?php
   date_default_timezone_set('America/Los_Angeles');
   ini_set("session.save_path", "/var/www/html/session/");
   session_start();
   ?>
<!DOCTYPE html>
<html lang='en'>
   <head>
      <title>VENDOR PAYMENTS</title>
      <?php
         $response  = "";
           if (!$_SESSION['hoa_username']) {
               $response = '<h4>Unauthorized access.</h4>';
               echo $response;
               header("Location: logout.php");
               exit(0);
           }
         
             include 'includes/dbconn.php';
             require_once('automation/connection/conn.php');
             require_once 'includes/commonheader.php';
             require_once('automation/crypto.php');
             require_once('automation/filehandler.php');
             require_once 'includes/invokeawsapi.php';
         
             $community_id = $_SESSION['hoa_community_id'];
             $mode = $_SESSION['hoa_mode'];
             $hoa_user_id = $_SESSION['hoa_user_id'];
             $vendor_id = 0;
             $today = date('Y-m-d');

             $year = date('Y');
         
             if (isset($_GET['vendor_id']) &&  $_GET['vendor_id'] != '' && $_GET['vendor_id'] != 0) {
                 $vendor_id = $_GET['vendor_id'] ;
             }
         
         
             function hideEmail($email)
             {
                 $em   = explode("@", $email);
                 $name = implode(array_slice($em, 0, count($em)-1), '@');
                 $len  = floor(strlen($name)/2);
         
                 return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
             }
         
            function hidePhoneNumber($number)
            {
                if ($number == "not set") {
                    return "Number not set";
                }
                return  substr($number, 0, 4) . '****' . substr($number, -4);
            }
            function secondsConversion($seconds)
            {
                $t = round($seconds);
                return sprintf('%02d:%02d:%02d', ($t/3600), ($t/60%60), $t%60);
            }

            if(!function_exists('getPageName')){
                function getPageName(){
                    return str_replace('/', '', explode('.', explode('?', $_SERVER['REQUEST_URI'], 2)[0]))[0];
                }
            }
         
         ?>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
      <link rel="stylesheet" type="text/css" href="assets/css/snackbar.css">
      
      <!-- Functions required for vendor CRUD -->
      <script src="/assets/js/vendors.js"></script>
      <style>
         .form-control {
         text-transform: none;
         }
         .red_custom{
         background: #fff;
         border: 1px solid #ff0d00;
         border-radius: 5px;
         color: #ff0d00;
         padding: .3rem;
         font-size: 11px;
         }
         .red_custom:hover{
         background: #ff0d00;
         border: 1px solid #ff0d00;
         color: #fff;
         }
         .blue_custom {
         background: #fff;
         border: 1px solid #417dde;
         border-radius: 5px;
         color: #417dde;
         padding: .3rem;
         font-size: 11px;
         }
         .blue_custom:hover{
         background: #417dde;
         border: 1px solid #417dde;
         color: #fff;
         }
         .no-border{
         border:none;
         }   
         div.sticky {
         position: -webkit-sticky;
         position: sticky;
         top: 0;
         font-size: 20px;
         }
         #currency-field-label,#v_otp_label {
         display: block;
         font-size: 24px;
         font-weight: bold;
         margin-bottom: 10px;
         }
         #currency-field,#v_otp {
         border: 2px solid #333;
         border-radius: 5px;
         color: #333;
         font-size: 15px;
         margin: 0 0 20px;
         padding: .5rem 1rem;
         width: 40%;
         }
         #v_otp {
         border: 2px solid #333;
         border-radius: 3px;
         color: #333;
         font-size: 15px;
         margin: 0 0 20px;
         padding: .5rem 1rem;
         width: 90%;
         }
         #add-charge-button,#v_charge_member,#v_charge_member_cancel,#link-quickbook-button {
         background: #fff;
         border: 2px solid #333;
         border-radius: 5px;
         color: #333;
         padding: .3rem;
         font-size: 13px;
         }
         #add-charge-button:hover,#v_charge_member:hover,#v_charge_member_cancel:hover,#link-quickbook-button:hover {
         background: #333;
         border: 2px solid #333;
         color: #fff;
         }
         #reload_schedule {
         background: #fff;
         border: 2px solid #65b82f;
         border-radius: 5px;
         color: #65b82f;
         padding: .3rem;
         font-size: 13px;
         }
         #reload_schedule:hover{
         background: #65b82f;
         border: 2px solid #65b82f;
         color: #fff;
         }
         .custom_d {
         background: #fff;
         border: 2px solid #65b82f;
         border-radius: 5px;
         color: #65b82f;
         padding: .3rem;
         font-size: 13px;
         }
         .custom_d:hover{
         background: #65b82f;
         border: 2px solid #65b82f;
         color: #fff;
         }
         #reload_schedule:disabled{
         opacity:0.5;
         }      
         .swal-table th{
         text-align: center;
         }
         .small-meta{
         font-size: 10px;
         font-weight: normal;
         color: #6e6d6d;
         }
         .heading {
         text-decoration: underline;
         text-decoration-color: #3D7DDE;
         text-decoration-thickness: 2px; 
         font: 14px;
         }
         .sub_heading {
         text-decoration: underline;
         text-decoration-color: #3D7DDE;
         text-decoration-thickness: 2px; 
         font-size: 16px;
         }
         .sub_heading2 {
         text-decoration: underline;
         text-decoration-color: #3D7DDE;
         text-decoration-thickness: 1.5px; 
         font-size: 15px;
         }
         .sub_heading2e {
         font-size: 15px;
         }
         .top_margin {
         margin-top:20px;
         }
         .spinner {
         margin: 10px auto 0;
         width: 70px;
         /*text-align: center;*/
         }
         .spinner > div {
         width: 8px;
         height: 8px;
         background-color: #333;
         border-radius: 100%;
         display: inline-block;
         -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;
         animation: sk-bouncedelay 1.4s infinite ease-in-out both;
         }
         .spinner .bounce1 {
         -webkit-animation-delay: -0.32s;
         animation-delay: -0.32s;
         }
         .spinner .bounce2 {
         -webkit-animation-delay: -0.16s;
         animation-delay: -0.16s;
         }
         .custom_d {
         background: #fff;
         border: 2px solid #65b82f;
         border-radius: 5px;
         color: #65b82f;
         padding: .3rem;
         font-size: 13px;
         }
         .custom_d:hover{
         background: #65b82f;
         border: 2px solid #65b82f;
         color: #fff;
         }
         /* #refresh_pending_invoice{
         background: #fff;
         border: 2px solid #65b82f;
         border-radius: 5px;
         color: #65b82f;
         padding: .3rem;
         font-size: 13px;        
         }
         #refresh_pending_invoice:hover{
         background: #65b82f;
         border: 2px solid #65b82f;
         color: #fff;
         }
         #refresh_pending_invoice:disabled{
         opacity:0.5;
         }
         #refresh_scheduled_invoice{
         background: #fff;
         border: 2px solid #65b82f;
         border-radius: 5px;
         color: #65b82f;
         padding: .3rem;
         font-size: 13px;        
         }
         #refresh_scheduled_invoice:hover{
         background: #65b82f;
         border: 2px solid #65b82f;
         color: #fff;
         }
         #refresh_scheduled_invoice:disabled{
         opacity:0.5;
         } */

         .error {
            display:inline;
        }
        .error:after {
            content:"\a";
            white-space: pre;
        }
         .info{
         background: #fff;
         border: 2px solid #2196F3;
         border-radius: 5px;
         color: dodgerblue;
         padding: .3rem;
         font-size: 13px;        
         }
         .info:hover{
         background: dodgerblue;
         border: 2px solid dodgerblue;
         color: #fff;
         }
         .info:disabled{
         opacity:0.5;
         }
         #ignore_invoice {
         background: #fff;
         border: 1px solid #ff0d00;
         border-radius: 5px;
         color: #ff0d00;
         padding: .3rem;
         font-size: 11px;
         }
         #ignore_invoice:hover{
         background: #ff0d00;
         border: 1px solid #ff0d00;
         color: #fff;
         }
         @-webkit-keyframes sk-bouncedelay {
         0%, 80%, 100% { -webkit-transform: scale(0) }
         40% { -webkit-transform: scale(1.0) }
         }
         @keyframes sk-bouncedelay {
         0%, 80%, 100% { 
         -webkit-transform: scale(0);
         transform: scale(0);
         }
         40% { 
         -webkit-transform: scale(1.0);
         transform: scale(1.0);
         }
         } 
      </style>
   </head>
   <body>
      <div class='layout'>
         <div class="wrapper">
            <?php if ($mode == 1) {
             include "boardHeader.php";
         } elseif ($mode == 2) {
             include "residentHeader.php";
         } ?>
            <section class="module-page-title">
               <div class="container">
                  <div class="row-page-title">
                     <div class="page-title-captions">
                        <h1 class="h5">Vendor Payments <small></h1>
                     </div>
                     <div class="page-title-secondary">
                        <ol class="breadcrumb">
                           <li class="breadcrumb-item"><i class='fa fa-wrench'></i> Vendors</li>
                           <li class="breadcrumb-item active"><a href='vendorDashboard.php'>Vendor Dashboard</a></li>
                        </ol>
                        <input type="hidden" value="<?php echo getPageName(); ?>" id="page_name">
                     </div>
                  </div>
               </div>
            </section>
            <section class="module">
               <div class="container">
                  <div class='table-responsive col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12'>
                     <div class='col-md-12'>
                        <div class='container'>
                           <div class='row'>
                              <div class='col-md-2'>
                                 <h4>VENDOR</h4>
                              </div>
                              <div class='col-md-1'>
                                 
                              </div>
                              <div class='col-md-7'>
                                 <select class='form-control' name='filter_vendor_by_id' id='filter_vendor_by_id'>
                                    <option value='0' selected >- Select Vendor -</option>
                                    <?php
                                       $selectQ = pg_query("SELECT vm.vendor_id, vm.vendor_name FROM vendor_master vm WHERE vm.vendor_id in (select distinct vendor_id from vendor_pay_method where community_id = $community_id) and vm.status IS TRUE ORDER BY vm.vendor_name");
                                       while ($selectR = pg_fetch_assoc($selectQ)) {
                                           echo "<option value='".$selectR['vendor_id']."' ";
                                           if (trim($vendor_id) == trim($selectR['vendor_id'])) {
                                               echo "selected";
                                           }
                                           echo " >{$selectR['vendor_name']}</option>";
                                       }
                                       ?>
                                 </select>
                              </div>
                              
                           </div>
                            <!--  <div class='special-heading m-b-40'>
                                 <h4>VENDOR</h4>
                                 <select class='form-control' name='filter_vendor_by_id' id='filter_vendor_by_id'>
                                    <option selected disabled>Select Vendor</option>
                                    <?php
                                       /*$selectQ = pg_query("SELECT vm.vendor_id, vm.vendor_name FROM vendor_master vm WHERE vm.vendor_id in (select distinct vendor_id from vendor_pay_method where community_id = $community_id) and vm.status IS TRUE ORDER BY vm.vendor_name");
                                       while ($selectR = pg_fetch_assoc($selectQ)) {
                                           echo "<option value='".$selectR['vendor_id']."' ";
                                           if(trim($vendor_id) == trim($selectR['vendor_id'])){
                                               echo "selected";
                                           }
                                          echo " >{$selectR['vendor_name']}</option>";
                                       }*/
                                       ?>
                                 </select>
                              </div> -->
                        </div>
                        <br><br>
                        <?php include('createVendorPayment.php'); ?>
                        <br><br>
                        <div class='container'>
                           <div class='row'>
                              <div class='special-heading m-b-40'>
                                 <h4>Pending Invoice</h4>
                                 &nbsp;&nbsp;&nbsp;
                                 <button class="btn btn-link text-primary toggle_pending_payments_btn">
                                    <li class="fa fa-toggle-on"></li>
                                    Hide
                                 </button>
                              </div>
                              <br>
                              <div class="toggle_pending_payments" style="">                              
                                    <?php include_once('pendingInvoiceTable.php');?>
                              </div>
                           </div>
                        </div>

                        <div class='container'>
                           <div class='row'>
                              <div class='special-heading m-b-40'>
                                 <h4>Unpaid Invoices</h4>
                                 &nbsp;&nbsp;&nbsp;
                                 <button class="btn btn-link text-primary toggle_scheduled_payments_btn">
                                    <li class="fa fa-toggle-on"></li>
                                    Hide
                                 </button>
                              </div>
                              <br>
                              <div class="toggle_scheduled_payments" style="">
                                <?php include_once('unpaidInvoicesTable.php');?>
                              </div>
                              
                           </div>
                        </div>
                        <div class='container'>
                           <div class='row'>
                              <div class='special-heading m-b-40'>
                                 <h4>Vendor Pay Methods</h4>
                                 &nbsp;&nbsp;&nbsp;
                                 <button class="btn btn-link text-primary toggle_pay_methods_btn">
                                    <li class="fa fa-toggle-on"></li>
                                    Hide
                                 </button>
                              </div>
                              <br>
                              <div class="mx-auto" style="">
                                 <button class="btn btn-primary toggle_pay_methods" onclick="submitVendor('<?php echo $vendor_id; ?>');" style="position: absolute; right: 0;">
                                    <li class="fa fa-plus"></li>
                                    Add Vendor Pay Method
                                 </button>
                              </div>
                              <table class='table table-striped dt-0 toggle_pay_methods' id='pay_methods_table' style='color: black;'>
                                 <thead>
                                    <th>Payment Type</th>
                                    <th>Account Number</th>
                                    <th>COA</th>
                                    <th>Payment Address</th>
                                    <th>Bank Details</th>
                                    <th>Additional Info</th>
                                    <th></th>
                                 </thead>
                                 <tbody>
                                    <?php

                                       if (isset($vendor_id) && $vendor_id > 0) {
                                           foreach ($vendorsQData as $vendorsQ) {
                                               $vendor_account_number = $vendorsQ['account_id'];
                                               $payment_type = $vendorsQ['payment_type_id'];
                                               $payment_type_name = $vendorsQ['payment_type_name'];
                                               
                                               $paymentAddress = $vendorsQ['payment_address1'];
                                               $paymentAddress .= ' '.$vendorsQ['payment_address2'].'<br/>';
                                               $paymentAddress .= ' '.$vendorsQ['city_name'].'<br/>';
                                               $paymentAddress .= ' '.$vendorsQ['state_name'].'<br/>';
                                               $paymentAddress .= ' '.$vendorsQ['payment_zip'].'<br/>';
                                               
                                               $bankDetails = ' Bank Name : '.$vendorsQ['ach_bank'].'<br/>';
                                               $bankDetails .= ' Account # '.$vendorsQ['ach_account_no'].'<br/>';
                                               $bankDetails .= ' Routing # '.$vendorsQ['ach_aba'].'<br/>';
                                               
                                               $more_details = "No of Approvers - <strong>".$vendorsQ['no_of_approvers']."</strong><br>";
                                               $more_details .= "No of Signatures - <strong>".$vendorsQ['no_of_signatures']."</strong><br>";
                                               if ($vendorsQ['pay_from_checking_acct'] == "t") {
                                                   $more_details .= "Pay from Checking Acc - <strong>Yes</strong>";
                                               } elseif ($vendorsQ['pay_from_checking_acct'] == 'f') {
                                                   $more_details .= "Pay from Checking Acc - <strong>No</strong>";
                                               }
                                           
                                               echo "<tr>";
                                               
                                               echo "<td>$payment_type_name</td>";
                                               
                                               echo "<td>$vendor_account_number</td>";
                                               echo "<td>".$vendorsQ['coa_name']."</td>";
                                                   
                                               echo "<td>$paymentAddress</td>"; //payment address
                                                   echo "<td>$bankDetails</td>";//bank details
                                                   echo "<td>$more_details</td>";
                                               
                                               // Edit and remove pending
                                               
                                               
                                               echo "<td>
                                                       <button class='btn btn-link text-primary' type='button'><li class='fa fa-edit'></li></button>
                                                       <button class='btn btn-link text-danger'><li class='fa fa-close'></li></button>
                                                       </td>";
                                               echo "</tr>";
                                           }
                                       }
                                       ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>                        
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <div class='container'>
                           <div class='row'>
                              <br><br>
                              <div class='special-heading m-b-40'>
                                 <h4>Payment History</h4>
                                 &nbsp;&nbsp;&nbsp;&nbsp;
                                 <button class="btn btn-link text-primary toggle_payment_history_btn">
                                    <li class="fa fa-toggle-on"></li>
                                    Hide
                                 </button>
                              </div>
                              <br>
                              <table class='table table-striped dt-0 toggle_payment_history' id='payment_history_table' style='color: black;'>
                                 <thead>
                                    <th>Invoice ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Document</th>
                                    <th></th>
                                    <th></th>
                                 </thead>
                                 <tbody>
                                    <?php

                                       if (isset($vendor_id) && $vendor_id > 0) {
                                           $result = pg_query("SELECT ci.id,ci.usps_last_sent,ci.invoice_date,ci.invoice_id,ci.invoice_amount,ci.url,ci.work_status, ci.payment_status,ci.reserve_expense,ci.check_num,ci.split_invoice, ci.due_date, ec.name as expense_category_name, esc.name as expense_sub_category_name FROM community_invoices ci LEFT JOIN expense_category_type ec ON ec.id = ci.expense_category_id LEFT JOIN expense_sub_category_type esc ON esc.id = ci.expense_sub_category_id WHERE ci.community_id=$community_id AND ci.vendor_id=$vendor_id AND ci.is_hidden='f' ORDER BY ci.invoice_date DESC  LIMIT 10");
                                       
                                           while ($row = pg_fetch_assoc($result)) {
                                               $i = $row['invoice_id'];
                                               $invoice_date = $row['invoice_date'];
                                               $invoice_date_ = new DateTime($row['invoice_date']);
                                               $invoice_id = $row['invoice_id'];
                                               $invoice_amount = $row['invoice_amount'];
                                               $document_id = $row['url'];
                                               $key = $row['url'];
                                               $description = '';
                                           
                                               $status = "";
                                               if ($row['work_status'] != "") {
                                                   $status .= "Work Status - <strong>".$row['work_status']."</strong><br>";
                                               }
                                           
                                               if ($row['payment_status'] != "") {
                                                   $status .= "Payment Status - <strong>".$row['payment_status']."</strong>";
                                               }
                                           
                                               $more_details = "";
                                           
                                               if ($row['reserve_expense'] == 't') {
                                                   $more_details .= "Reserve Expense - <strong>Yes</strong><br>";
                                               } elseif ($row['reserve_expense'] == 'f') {
                                                   $more_details .= "Reserve Expense - <strong>No</strong><br>";
                                               }
                                           
                                               if ($row['check_num'] != "") {
                                                   $more_details .= "Check number - <strong>".$row['check_num']."</strong><br>";
                                               }
                                           
                                               if ($row['split_invoice'] == 't') {
                                                   $more_details .= "Invoice was Split b/w residents";
                                               }
                                           
                                               $document = "";
                                           
                                               if ($document_id != "") {
                                                   $document = "<a target='_blank' href='automation/awsviewdoc.php?stmt=$key'><i class='fa fa-file-pdf-o'></i></a>";
                                               } else {
                                                   $document = "<button class='btn btn-link btn-xs' type='button' data-toggle='modal' data-target='#modal_upload_invoice_".$i."'><i class='fa fa-upload'></i> Upload</button>";
                                               }
                                           
                                               if ($invoice_date != "") {
                                                   $invoice_date = date('m/d/Y', strtotime($invoice_date));
                                               }
                                           
                                               if ($invoice_amount != "") {
                                                   $invoice_amount_d = "$ ".$invoice_amount;
                                               }
                                           
                                               echo "<tr>";
                                               echo "<td>$i</td>";
                                               echo "<td>$invoice_date</td>";
                                               echo "<td>$invoice_amount_d</td>";
                                               echo "<td>$status</td>";
                                               echo "<td>".$row['due_date']."</td>";
                                               echo "<td>$document</td>";
                                           
                                                   
                                           
                                               $lastSent = "";
                                           
                                               if (isset($row['usps_last_sent'])) {
                                                   $lastSent = "Last sent on: <b>".date('m/d/Y', strtotime($row['usps_last_sent']))."</b>";
                                               }
                                           
                                               echo "<td>
                                                               <!--<button class='btn btn-link' type='button' data-toggle='modal' data-target='#modal_edit_invoice' onclick='set_invoice_details_".$i."();'><i class='fa fa-edit'></i> Edit</button>
                                                               <br>
                                                               <button class='btn btn-link text-danger btn-xs' type='button' data-toggle='modal' data-target='#modal_remove_invoice_".$i."'><i class='fa fa-close'></i> Remove</button> -->
                                                               </td> <td><button class='btn btn-link' id=\"".$i."\" type='button' onclick=\"sendViaUSPS(this)\"><i class='fa fa-paper-plane'></i> Send Via USPS</button><br>".$lastSent."</td>";
                                                               
                                               echo "</tr>";
                                           }
                                       }
                                       ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
            <?php include($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."assets/modals/vendors.php"); ?>
            <!-- Invoices -->
            <?php include 'assets/modals/invoices.php'; ?>
            <!-- Footer-->
            <?php include 'footer.php'; ?>
            <div id="snackbar"></div>
            <a class='scroll-top' href='#top'><i class='fa fa-angle-up'></i></a>  
            <!--<form class="form-horizontal" role="form" name="boardMeetingSignupForm" class="contact-form" id="boardMeetingSignupForm" data-toggle="validator" class="shake" > -->
            <!-- </form> -->
         </div>
      </div>
      <div class="modal" id="showPleaseWaitModal" role="dialog" data-backdrop="static">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Please wait...</h4>
               </div>
               <div class="modal-body">
                  <div class="progress">
                     <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                        aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; height: 100%">
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      
   </body>
   <!-- Scripts-->
   <script src='https://cdnjs.cloudflare.com/ajax/libs/tether/1.1.1/js/tether.min.js'></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
      <script src="//cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.js"></script>
      <script src="//cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
      <script src="//cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/additional-methods.js"></script>
      <script src="//cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/additional-methods.min.js"></script>

   <script src='/assets/js/plugins.min.js'></script>
   <script src='/assets/js/custom.min.js'></script>
   <!-- Datatable -->
   <script src='//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'></script>
   <!-- bootstrap-select -->
   <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
   <!-- Sweet Alert 2 -->
   <script src='assets/js/sweetalert2.all.min.js'></script>
   <!-- Optional: include a polyfill for ES6 Promises for IE11 and Android browser -->
   <script src="https://unpkg.com/promise-polyfill"></script>
   <script src="/assets/js/showSwal.js"></script>
   <!-- Functions required for vendor CRUD -->
   <script src="/assets/js/vendors.js"></script>
   <!-- Handler for API forms -->
   <script src="/assets/js/formhandler.js"></script>
   <script src='assets/js/send_approvals.js'></script>
   <!-- Invoice Functions -->
   <script src='assets/js/invoices.js'></script>
   <script src='assets/js/createVendorPaymentScript.js'></script>
   <script src="assets/js/communityBankTransactions.js"></script>
    <script src="assets/js/pendingInvoiceTableScript.js"></script>
    <script src="assets/js/unpaidInvoicesTableScript.js"></script>

   <script type="text/javascript">
      $(document).ready(function() {


           
      
           $(".toggle_payment_history_btn").click(function(){
                    
                    $(".toggle_payment_history").toggle();
      
                    if($(this).html().includes("Show")){
                        $(this).html("<li class='fa fa-toggle-on' ></li>Hide");
                    }
                    else{
                        $(this).html("<li class='fa fa-toggle-off' ></li>Show");
                    }
            }); 
      
           $(".toggle_pending_payments_btn").click(function(){
                    
                    $(".toggle_pending_payments").toggle();
      
                    if($(this).html().includes("Show")){
                        $(this).html("<li class='fa fa-toggle-on' ></li>Hide");
                    }
                    else{
                        $(this).html("<li class='fa fa-toggle-off' ></li>Show");
                    }
            }); 
      
           $(".toggle_scheduled_payments_btn").click(function(){
                    
                    $(".toggle_scheduled_payments").toggle();
      
                    if($(this).html().includes("Show")){
                        $(this).html("<li class='fa fa-toggle-on' ></li>Hide");
                    }
                    else{
                        $(this).html("<li class='fa fa-toggle-off' ></li>Show");
                    }
            }); 
      
           $(".toggle_pay_methods_btn").click(function(){
                    
                    $(".toggle_pay_methods").toggle();
      
                    if($(this).html().includes("Show")){
                        $(this).html("<li class='fa fa-toggle-on' ></li>Hide");
                    }
                    else{
                        $(this).html("<li class='fa fa-toggle-off' ></li>Show");
                    }
            }); 


      
          
      });
      
      /*if(typeof table != 'undefined'){
          var table;
      }

      if(typeof table1 != 'undefined'){
        var table1;
      }*/
      
      
      
        $(function () {
      
            vendor_id = GetURLParameter('vendor_id');

            // trigger event vedor pay method
            if(vendor_id != 'undefined') {
                $('#vendor_pay_mthd_account').trigger('change')
            }

            /*url = "ajax/getpendinginvoices.php?vendor_id="+vendor_id+"&mode=pending";
      
            table = $("#pending_payments_table").DataTable({ "pageLength": 10 ,
            "ajax": {"url":url,
                     "type":"GET"},
            'processing': true,
            "language": {
                          "emptyTable": "No Pending Invoices"
                        },
            'paging': false,
            'bFilter': false,
            'bInfo':false,
            "order": [[ 1, "asc" ]],
            "columns": [
                    { "data": "id" },
                    { "data": "invoice_id" },
                    { "data": "invoice_date" },
                    {"data": "invoice_amount"},
                    {"data": "vendor_name"},
                    { "data": "invoice_url" },
                    { "data": "approvals_required" },
                    { "data": "approvals_secured" },
                    {"data": "invoice_options"},
                    {"data": "actions"}
            ],
            "columnDefs": [
                {"className":"test", "width": "10%", "targets": 0 },
                { "width": "10%", "targets": 1 },
                { "width": "10%", "targets": 2 },
                { "width": "10%", "targets": 3 },
                { "width": "10%", "targets": 4 },
                { "width": "10%", "targets": 5 },
                { "width": "8%", "targets": 6 },
                { "width": "8%", "targets": 7 },
                { "width": "14%", "targets": 8 }
      
            ]
            });
      
            $('#refresh_pending_invoice').click(function(){
                table.ajax.reload();
            });
      
            url1 = "ajax/getpendinginvoices.php?vendor_id="+vendor_id+"&mode=scheduled";
      
            table1 = $("#scheduled_payments_table").DataTable({ "pageLength": 10 ,
            "ajax": {"url": url1,
                     "type":"GET"},
            "language": {
                          "emptyTable": "No Payments Scheduled"
                        },
            'processing': true,
            'paging': false,
            'bFilter': false,
            'bInfo':false,
            "order": [[ 1, "asc" ]],
            "columns": [
                    { "data": "id" },
                    { "data": "invoice_id" },
                    { "data": "invoice_date" },
                    {"data": "invoice_amount"},
                    {"data": "vendor_name"},
                    { "data": "invoice_url" },
                    { "data": "approvals_required" },
                    { "data": "approvals_secured" },
                    {"data": "invoice_options"},
                    {"data": "actions"}
            ],
            "columnDefs": [
                { "width": "10%", "targets": 0 },
                { "width": "10%", "targets": 1 },
                { "width": "10%", "targets": 2 },
                { "width": "10%", "targets": 3 },
                { "width": "10%", "targets": 4 },
                { "width": "10%", "targets": 5 },
                { "width": "8%", "targets": 6 },
                { "width": "8%", "targets": 7 },
                { "width": "14%", "targets": 8 }
      
            ]
            });
      
            $('#refresh_scheduled_invoice').click(function(){
                table1.ajax.reload();
            });*/


       /* vendor_id = $('#filter_vendor_by_id option:selected').val();
        contract_id = $('#select_contract option:selected').val().split('|')[0];

         url2 = "ajax/getpendinginvoices.php?vendor_id="+vendor_id+"&mode=scheduled&contract_id="+contract_id;*/
      
        });
        
      $('#filter_vendor_by_id').on('change', function(){
      
         showPleaseWait('Loading Vendor Info... Please wait...');
         val = $('#filter_vendor_by_id option:selected').val();
         window.location = "/vendorPayments.php?vendor_id="+val;
                
      });

       function showPleaseWait(title = 'Please wait...'){
                    var modal = $('#showPleaseWaitModal');
                    modal.find('.modal-title').text(title)
                      $("#showPleaseWaitModal").modal("show");
        }
         
        function hidePleaseWait() {
         
                      $("#showPleaseWaitModal").modal("hide");
                       $('#auto').html('');
         
        }

      
      
   </script>
</html>
