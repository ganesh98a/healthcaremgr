<?php

function emailHeader()
{
    return $header = '<!DOCTYPE html>
 <html>
 <head>
 <title>Email HCM</title>
 </head>
 <body style="padding:0px; margin:0px; background:#F6F6F6">
 
 <table cellpadding="0" cellspacing="0" width="700px" style="margin: 0px auto;">
    <tr>
        <td style="border-radius:15px; background: #fff; overflow: hidden;" width="100%">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="background: linear-gradient(-135deg, #d77f0b, #09a275); padding: 30px;" align="center">
                        <img width="200px" src="' . base_url("assets/img/logo.png") . '">
                    </td>
                 </tr>
                 <tr>
                 <td>';
}

function emailFooter()
{
    return $footer = '
    </td>
    </tr>            
    <tr>
                    <td>
                        <table width="80%" align="center" style="border-top:1px solid #09A275; padding:15px 0px; margin-top: 15px; font-family: sans-serif">
                        <tr>
                            <td align="right" style="border-right:1px solid #09A275; padding-right:30px; ">healthcaremgr.com</b></td>
                            <td align="left" style="padding-left:30px;">  <b>(03) 9896 2468</b></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center" style="padding-top:10px; font-size:14px;">660 Canterbury Rd, Surrey Hills VIC 3127, Australia</td>
                        </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>';
}

function send_mail_smtp($to_email, $subject, $body, $cc_email_address = null, $file_attach = null)
{
    $obj = &get_instance();
    $msg = $body;
    $from_email = 'developer@yourdevelopmentteam.com.au';
    $obj->load->library('email');
    $config['protocol'] = "smtp";
    $config['smtp_host'] = 'ssl://smtp.gmail.com';
    //$config['smtp_host'] = 'tls://smtp.gmail.com';
    $config['smtp_port'] = 465;  //25
    $config['smtp_user'] = 'developer@yourdevelopmentteam.com.au';
    $config['smtp_pass'] = 'NPRBpXEtUf';
    $config['charset'] = "utf-8";
    $config['mailtype'] = "html";
    $config['newline'] = "\r\n";
    $config['priority'] = "1";
    $obj->email->initialize($config);
    $obj->email->from($from_email, APPLICATION_NAME);
    $obj->email->to($to_email);
    $obj->email->subject($subject);
    $obj->email->message($msg);
    $obj->email->bcc(ADMIN_EMAIL);
    if ($file_attach != null) {
        $obj->email->attach($file_attach);
    }
    $obj->email->send();
    $output = $obj->email->print_debugger();

    return true;
}

function send_mail_php_mailer($to_email, $subject, $body, $cc_email_address = null, $file_attach = null)
{
    //mail send using ci library
    $obj = &get_instance();
    $obj->load->library('email');
    $obj->email->set_mailtype('html');
    $obj->email->from(FROM_EMAIL, APPLICATION_NAME);

    $obj->email->set_header('MIME-Version', '1.0');
    $obj->email->set_header('charset', 'UTF-8');


    $obj->email->to($to_email);
    if (!empty($cc_email_address)) {
        $obj->email->cc($cc_email_address);
    }
    $obj->email->subject($subject);
    $obj->email->message($body);
    $obj->email->bcc(ADMIN_EMAIL);
    if ($file_attach != null) {
        $obj->email->attach($file_attach);
    }
    $return = @$obj->email->send();
    return true;
}

function send_mail($to_email, $subject, $body, $cc_email_address = null, $file_attach = null)
{
    if (MAIL_METHOD === "PHP_MAILER") {
        send_mail_php_mailer($to_email, $subject, $body, $cc_email_address, $file_attach);
    } elseif (MAIL_METHOD === "SMTP") {
        send_mail_smtp($to_email, $subject, $body, $cc_email_address, $file_attach);
    } else {
        send_mail_php_mailer($to_email, $subject, $body, $cc_email_address, $file_attach);
    }
}

function forgot_password_mail($userdata, $cc_email_address = null)
{
    $obj = &get_instance();
    $subject = 'HCM: Reset your password';
    $username = $userdata['firstname'] . ' ' . $userdata['lastname'];
    $url = $userdata['url'];
    $logo_url = base_url() . '';

    $body = '
    <table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p><b>Dear ' . $username . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Thanks for contacting us. Follow the directions below to reset
                                                                your password.</p>

                                                            <p style="padding-left:15px; display:inline-block; width:100%; margin: 10px auto; text-align: center;">
                                                                <a href="' . $url . '" style="cursor:pointer; text-decoration:none; margin: 0px auto;  display:table; background:#09A275; color: #fff; width:130px; padding:7px 15px; border-radius: 25px; text-align: center">
                                                                    Reset Password</a>
                                                            </p>


                                                            <p style="padding-left:15px; margin:0px;">After you click the button above, you\'ll be prompted to
                                                                    complete the following steps:</p>
                                                            <p style="padding-left:15px; margin:0px;">1. Enter and confirm your new password.</p>
                                                            <p style="padding-left:15px; margin:0px;">2.  Click "Submit"</p>
                                                            <p style="padding-left:15px;">If you didn\'t request a password reset or you feel you\'ve
                                                                    received this message in error, please call our 24/7 support
                                                                    team right away at 000 000 0000. If you take no action, don\'t
                                                                    worry — nobody will be able to change your password
                                                                    without access to this email.
                                                                    </p>
                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr>
                </table>
                    ';


    $msg = emailHeader() . $body . emailFooter();

    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function send_reset_pin_mail_to_admin($userdata, $cc_email_address = null)
{
    $obj = &get_instance();
    $subject = 'HCM: Reset Pin';
    $logo_url = base_url() . '';

    $redirect_url = $obj->config->item('server_url') . "forgot_reset_pin/" . encrypt_decrypt('encrypt', $userdata['id']) . '/' . $userdata['token'] . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME));

    $msg = '<table style="max-width:40%; min-width:280px; margin: 0px auto; border: 1px solid #cdcdcd; border-collapse:collapse; font-family:sans-serif;" cellpadding="0" cellspaceing="0">
    <tr>
        <td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Hi ' . $userdata['fullname'] . ',</td>
    </tr>
     <tr>
    <tr>
        <td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">for reset pin <a href="' . $redirect_url . '">Click here</a></td>
    </tr>
    <tr>
        <td width="100%" style="margin-bottom: 30px; float: left; width: 100%;"></td>
     </tr>
  </table>

  <table style="width: 41%; margin: 0px auto; padding: 20px;">
     <tr style="margin-bottom: 20px; font-size: 12px; float: left;  width: 100%;">
      <td style="line-height: 20px; width: 100%; text-align: center; display: block; color: #909090;">&#169;2018 All Rights  Reserved <b>HCM</b></td>
     </tr>
  </table>

<style>
a:hover, a:active{
  color:#fff !important;
  text-decoration:none !important;
}
</style>
  ';
    $msg = emailHeader() . $msg . emailFooter();
    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function welcome_mail_participant($userdata, $cc_email_address = null)
{
    $obj = &get_instance();
    $subject = 'HCM: Welcome Mail';
    $logo_url = base_url() . '';

    $msg = '<table style="max-width:40%; min-width:280px; margin: 0px auto; border: 1px solid #cdcdcd; border-collapse:collapse; font-family:sans-serif;" cellpadding="0" cellspaceing="0">

   <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Hi ' . $userdata['fullname'] . ',</td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Welcome to HCM </td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Username ' . $userdata["username"] . '</td></tr>
  <tr>

  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Password ' . $userdata["password"] . '</td></tr>
  <tr>

  <tr>
  <td width="100%" style="margin-bottom: 30px; float: left; width: 100%;">
  </td>
  </tr>
  </table>

  <table style="width: 41%; margin: 0px auto; padding: 20px;">
  <tr style="margin-bottom: 20px; font-size: 12px; float: left;  width: 100%;">
  <td style="line-height: 20px; width: 100%; text-align: center; display: block; color: #909090;">&#169;2018 All Rights  Reserved <b>HCM</b></td>
  </tr>
  </table>
<style>
a:hover, a:active{
  color:#fff !important;
  text-decoration:none !important;
}
</style>
  ';

    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function welcome_mail_admin($userdata, $cc_email_address = null)
{
    $obj = &get_instance();
    $subject = 'HCM: Welcome Mail';
    $logo_url = base_url() . '';

    $redirect_url = $obj->config->item('server_url') . "GeneratePassword/" . encrypt_decrypt('encrypt', $userdata['id']) . '/' . $userdata['token'] . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME));

    $msg = '<table style="max-width:40%; min-width:280px; margin: 0px auto; border: 1px solid #cdcdcd; border-collapse:collapse; font-family:sans-serif;" cellpadding="0" cellspaceing="0">


    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Hi ' . $userdata['fullname'] . ',</td></tr>
    <tr>
    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Welcome to HCM System </td></tr>
    <tr>
    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Username: ' . $userdata['username'] . '</td></tr>
    <tr>
    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#007dbc !important"><a href="' . $redirect_url . '" style="color:#007dbc !important">Click here to reset Password</a></td></tr>
    <tr>

    <tr>
    <td width="100%" style="margin-bottom: 30px; float: left; width: 100%;">
    </td>
    </tr>
    </table>

    <table style="width: 41%; margin: 0px auto; padding: 20px;">
    <tr style="margin-bottom: 20px; font-size: 12px; float: left;  width: 100%;">
    <td style="line-height: 20px; width: 100%; text-align: center; display: block; color: #909090;">&#169;2018 All Rights  Reserved <b>HCM</b></td>
    </tr>
    </table>
    <style>
    a:hover, a:active{
      color:#fff !important;
      text-decoration:none !important;
    }
    </style>
    ';

    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function shift_create_mail($userdata, $cc_email_address = null)
{
    $obj = &get_instance();
    $subject = 'HCM: Shift Schedule';
    $logo_url = base_url() . '';

    $msg = '<table style="max-width:40%; min-width:280px; margin: 0px auto; border: 1px solid #cdcdcd; border-collapse:collapse; font-family:sans-serif;" cellpadding="0" cellspaceing="0">


  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Hi ' . $userdata['fullname'] . ',</td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Your shift create at ' . $userdata['shift_date'] . '</td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Your shift will start at ' . $userdata['start_time'] . '</td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Your shift will end at ' . $userdata['end_time'] . '</td></tr>
  <tr>

  <tr>
  <td width="100%" style="margin-bottom: 30px; float: left; width: 100%;">
  </td>
  </tr>
  </table>

  <table style="width: 41%; margin: 0px auto; padding: 20px;">
  <tr style="margin-bottom: 20px; font-size: 12px; float: left;  width: 100%;">
  <td style="line-height: 20px; width: 100%; text-align: center; display: block; color: #909090;">&#169;2018 All Rights  Reserved <b>HCM</b></td>
  </tr>
  </table>
<style>
a:hover, a:active{
  color:#fff !important;
  text-decoration:none !important;
}
</style>
  ';

    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function roster_create_mail($userdata, $cc_email_address = null)
{
    $obj = &get_instance();
    $subject = 'HCM: Create Roster';
    $logo_url = base_url() . '';

    if ($userdata['is_default'] == 1) {

        $title = $userdata['title'];
    } else {
        $title = "default";
    }

    $msg = '<table style="max-width:40%; min-width:280px; margin: 0px auto; border: 1px solid #cdcdcd; border-collapse:collapse; font-family:sans-serif;" cellpadding="0" cellspaceing="0">


  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Hi ' . $userdata['fullname'] . ',</td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Your ' . $title . ' roster created successfully</td></tr>
  <tr>
  <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Your ' . $title . ' roster start at ' . $userdata['start_date'] . '</td></tr>
  <tr>';

    $msg .= ($userdata['is_default'] == 1) ? '<tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Your ' . $title . ' roster will end at ' . $userdata['end_date'] . '</td></tr>
  <tr>' : '';


    $msg .= '<tr>
  <td width="100%" style="margin-bottom: 30px; float: left; width: 100%;">
  </td>
  </tr>
  </table>

  <table style="width: 41%; margin: 0px auto; padding: 20px;">
  <tr style="margin-bottom: 20px; font-size: 12px; float: left;  width: 100%;">
  <td style="line-height: 20px; width: 100%; text-align: center; display: block; color: #909090;">&#169;2018 All Rights  Reserved <b>HCM</b></td>
  </tr>
  </table>
<style>
a:hover, a:active{
  color:#fff !important;
  text-decoration:none !important;
}
</style>
  ';

    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function send_Update_password_recovery_email($userdata)
{
    $obj = &get_instance();
    $subject = 'HCM: Verify email address';
    $username = $userdata['fullname'];
    $url = $userdata['url'];
    $logo_url = base_url() . '';

    $body = '
    <table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p><b>Dear ' . $username . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Thanks for contacting us. Please click below to change
                                                                your email.</p>

                                                            <p style="padding-left:15px; display:inline-block; width:100%; margin: 10px auto; text-align: center;">
                                                                <a href="' . $url . '" style="cursor:pointer; text-decoration:none; margin: 0px auto;  display:table; background:#09A275; color: #fff; width:130px; padding:7px 15px; border-radius: 25px; text-align: center">
                                                                    Click here</a>
                                                            </p>


                                                            <p style="padding-left:15px;">If you didn\'t request a change email address or you feel you\'ve
                                                                    received this message in error, please call our 24/7 support
                                                                    team right away at 000 000 0000.
                                                                    </p>
                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> </table>';


    $msg = emailHeader() . $body . emailFooter();

    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function send_invitation_mail_to_applicant_for_task($userdata)
{
    $obj = &get_instance();
    $subject = 'HCM: Invitation of task';
    $username = $userdata['fullname'];
    $url = $userdata['url'];
    $logo_url = base_url() . '';
    $task_date = DateFormate($userdata['task_date_time'], "d/m/Y");
    $task_time = DateFormate($userdata['task_date_time'], "h:i a");

    $body = ' <table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p><b>Hello, ' . $username . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Invitation of Task.</p>
                                                            <p style="padding-left:15px; margin:0px;">Your task schedule at ' . $task_date . ' ' . $task_time . '.  Please click below to confirm</p>
                                                            <p style="padding-left:15px; margin:0px;"></p>
                                                            <p style="padding-left:15px; margin:0px;">Your quiz test credential.</p>
                                                            <p style="padding-left:15px; margin:0px;">Pin : ' . $userdata['device_pin'] . '</p>
                                                            <p style="padding-left:15px; margin:0px;"></p>
                                                            <p style="padding-left:15px; display:inline-block; width:100%; margin: 10px auto; text-align: center;">
                                                                <a href="' . $url . '/a/" style="cursor:pointer; text-decoration:none; margin: 10px auto;  display:table; background:#09A275; color: #fff; width:130px; padding:7px 15px; border-radius: 25px; text-align: center">
                                                                    Accept</a>
                                                                <a href="' . $url . '/c/" style="cursor:pointer; text-decoration:none; margin: 0px auto;  display:table; background:#09A275; color: #fff; width:130px; padding:7px 15px; border-radius: 25px; text-align: center">
                                                                    Reject</a>
                                                            </p>

                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> </table>';


    $msg = emailHeader() . $body . emailFooter();

    $output = send_mail('developer@ydt.com.au', $subject, $msg);

    return $output;
}

function send_quote_email($quoteData)
{

    $msg = quote_email_html_content($quoteData);
    $subject = 'Quotes ' . $quoteData->quote_number . ' for ' . $quoteData->quote_for;
    $email = $quoteData->quote_email;
    $output = send_mail($email, $subject, $msg, null, $quoteData->quotePdfPath);
    return true;
}

function quote_email_html_content($quoteData)
{

    $body = ' <table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p style="text-align=center"><b>Hello, ' . $quoteData->quote_for . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Quotation created successfully. </p>

                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> </table>';


    $msg = emailHeader() . $body . emailFooter();
    return $msg;
}

function send_invoice_email($invoiceData)
{

    $msg = invoice_email_html_content($invoiceData);
    $subject = 'Invoice ' . $invoiceData->invoice_id . ' for ' . $invoiceData->invoice_for;
    $email = $invoiceData->invoice_email;
    $output = send_mail($email, $subject, $msg, null, $invoiceData->invoicePdfPath);
    return true;
}

function invoice_email_html_content($invoiceData)
{

    $body = ' <table width="100%"><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="100%" align="center">
                                                <tr>
                                                    <td>
                                                            <p style="text-align=center"><b>Hello, ' . $invoiceData->invoice_for . '</b></p>
                                                            <p style="margin:0px; margin-bottom:30px">Please find your attached invoice.</p>

                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> <table>';


    $msg = emailHeader() . $body . emailFooter();
    return $msg;
}

function send_reminder_renewal_payroll_exemption_email($payrollExemptionEmailData)
{

    $msg = renewal_payroll_exemption_email_html_content($payrollExemptionEmailData);
    $subject = 'Payroll Exemption Expiring Soon';
    $email = $payrollExemptionEmailData->to_email;
    $output = send_mail($email, $subject, $msg, null, null);
    return true;
}

function renewal_payroll_exemption_email_html_content($payrollExemptionEmailData)
{

    $body = ' <table ><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p style="text-align=center"><b>Hello, ' . $payrollExemptionEmailData->org_contact_name . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Your Payroll Exemption on file is set to end on the ' . $payrollExemptionEmailData->expire_untill . '. </p>
                                                            <p style="padding-left:15px; margin:0px;">Please submit your documentation to continue to receive payroll exemption, if you have already provided this, please ignore this email. </p>

                                                    </td>
                                                </tr>
                                            </table>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                    <p style="margin:0px;">&nbsp;</p>
                                                    <p style="margin:0px;">Thanks,</p>
                                                    <p style="margin:0px;">HCM</p>
                                                    </td>
                                                </tr>
                                            </table>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> </table>';


    $msg = emailHeader() . $body . emailFooter();
    return $msg;
}

function send_IPAD_draft_contract_email($contract_applicant)
{
    $msg = draft_contract_email_html_content($contract_applicant);
    $subject = 'Draft contract ' . (!empty($contract_applicant['intreview'])) ? $contract_applicant['intreview'] : '';
    $email = $contract_applicant['email'];
    $output = send_mail($email, $subject, $msg, null, $contract_applicant['filepath']);
    return true;
}

function draft_contract_email_html_content($contract_applicant)
{
    $firstName = (!empty($contract_applicant['firstname'])) ? $contract_applicant['firstname'] : '';
    $lastName = (!empty($contract_applicant['lastname'])) ? $contract_applicant['lastname'] : '';
    $body = ' <table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p style="text-align=center"><b>Hello, ' . $firstName . ' ' . $lastName . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Draft contract send successfully. </p>

                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> </table>';


    $msg = emailHeader() . $body . emailFooter();
    return $msg;
}

function send_disable_portal_access_mail($userdata, $type = 0)
{
    $obj = &get_instance();
    $subject = $type == 0 ? 'HCM: Disable Portal Access' : 'HCM: Enable Portal Access';
    $msg = $type == 0 ? 'Your portal access has been disabled. Here are the notes from us:' : 'Your portal access has been enabled.';
    $username = $userdata['fullName'];

    $logo_url = base_url() . '';

    $note = !empty($userdata['note']) ? $userdata['note'] : '';

    $body = '<table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p style="text-align=center"><b>Hi ' . $username . ',</b></p>
                                                            <p style="text-align=center"><b>' . $msg . '</b></p>';
    if ($type == 0) {
        $body .= '<p style="padding-left:15px; margin:0px;">' . $note . '</p>';
    }
    $body .= '</td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr> </table>';


    $msg = emailHeader() . $body . emailFooter();
    $output = send_mail($userdata['email'], $subject, $msg);

    return $msg;
}

function send_welcome_mail_to_member_user($userdata)
{
    $obj = &get_instance();
    $subject = 'HCM: Welcome Mail';
    $logo_url = base_url() . '';


    $msg = '<table style="max-width:40%; min-width:280px; margin: 0px auto; border: 1px solid #cdcdcd; border-collapse:collapse; font-family:sans-serif;" cellpadding="0" cellspaceing="0">


    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Hi ' . $userdata['fullname'] . ',</td></tr>
    <tr>
    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Welcome to HCM System </td></tr>
    <tr>
    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#443d3d;">Username: ' . $userdata['username'] . '</td></tr>
    <tr>
    <tr><td style="padding:15px 30px 0px; font-size:20px; font-weight:600; color:#007dbc ">Pin: ' . $userdata['pin'] . '</a></td></tr>
    <tr>

    <tr>
    <td width="100%" style="margin-bottom: 30px; float: left; width: 100%;">
    </td>
    </tr>
    </table>

    <table style="width: 41%; margin: 0px auto; padding: 20px;">
    <tr style="margin-bottom: 20px; font-size: 12px; float: left;  width: 100%;">
    <td style="line-height: 20px; width: 100%; text-align: center; display: block; color: #909090;">&#169;2018 All Rights  Reserved <b>HCM</b></td>
    </tr>
    </table>
    <style>
    a:hover, a:active{
      color:#fff !important;
      text-decoration:none !important;
    }
    </style>
    ';

    $msg = emailHeader() . $msg . emailFooter();
    $output = send_mail($userdata['email'], $subject, $msg);

    return $output;
}

function send_finanance_statement_email($statement_details)
{
    $user_details = $statement_details['user_details'];
    $msg = finanance_statement_email_html_content($user_details);
    $subject = 'Finance statement ' . ((!empty($statement_details['statement_number'])) ? $statement_details['statement_number'] : '');
    $email = $user_details['email'];
    $arr_cc_emails = obj_to_arr($statement_details['booker']);
    $arr_cc_emails = array_column($arr_cc_emails, 'email');
    $str_cc_mail = implode(',', $arr_cc_emails);
    $output = send_mail($email, $subject, $msg, $str_cc_mail, $statement_details['filepath']);
    return true;
}

function finanance_statement_email_html_content($contract_applicant)
{
    $firstName = (!empty($contract_applicant['firstname'])) ? $contract_applicant['firstname'] : '';
    $lastName = (!empty($contract_applicant['lastname'])) ? $contract_applicant['lastname'] : '';
    $body = '<table><tr>
                        <td style="font-family:sans-serif; font-size: 14px;">
                               <table width="80%" align="center">
                                   <tr>
                                       <td>
                                            <table cellpadding="0" cellspacing="0"  width="70%" align="center">
                                                <tr>
                                                    <td>
                                                            <p style="text-align=center"><b>Hello, ' . $firstName . ' ' . $lastName . '</b></p>
                                                            <p style="padding-left:15px; margin:0px;">Finance statement send successfully. </p>

                                                    </td>
                                                </tr>
                                            </table>

                                        <p style="margin:0px;">Sincerely,</p>
                                        <p style="margin:0px;">IT Department,</p>
                                        <p style="margin:0px;">HCM.</p>
                                        <p><div style="width:120px; border-top:1px solid #000"></div></p>
                                        <p style="margin:0px;"> E: tom12345@hcm.com</p>
                                        <p style="margin:0px;"> P: (03) 9896 2468</p>
                                    </td>
                                   </tr>
                               </table>
                        </td>
                    </tr></table>';


    $msg = emailHeader() . $body . emailFooter();
    return $msg;
}
