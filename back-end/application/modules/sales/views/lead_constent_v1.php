<?php
$logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/stylesheet.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css">
<?php if (isset($type) && $type == 'header') { ?>
<div class="header_image">
	<table class="header-table">
	<tr>
		<td align="right" class="pt-5 pr-5">
			<p class="f-22 f-white">Consent To Access NDIS Plan</p>
		</td>
    </tr>
    <tr>
		<td align="right" class="pt-3 pr-5">
			<p class="f-12 f-white pt-2"> version: 1&#x7c; 1 Mar 2021</p>
		</td>
    </tr>
</table>
</div>
<?php } ?>
<?php if (isset($type) && $type == 'content_1') { ?>
<div class="px-8">	
	<div class="pt-8">
		<table class="to-table">
			<tr>
				<td class="border-bottom"><span class="sub-head f-14">To: </span><span class="sub-head-name f-14"><?php if (isset($data) && isset($data['to'])) { echo $data['to']; }?></span>
				</td>
			</tr>
		</table>
	</div>
	<div class="pt-1">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label f-15">Explanation of ‘Information’ and what your ‘Consent’ means:</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="pl-align f-col pt-2">
		<p class="f-13 mt-0-im">ONCALL Group Australia Pty Ltd (ACN 633 010 330) (ONCALL) would like your consent to access your NDIS Plan or the part of your plan that details the funding for the support you want us to deliver so that we can administer and manage the services we provide to you. We will use this information to prepare a service agreement for you to sign.  We will ask for your consent when you get a new plan, review your plan or you renew your plan when it expires.</p>
        <p class="f-13 pt-0">ONCALL may also need to discuss with other service providers, such as your Support Coordinator or Behaviour Support Provider the details of your NDIS Plan including funding items, levels and limitations.</p>
        <p class="f-13 pt-0">To access this information, ONCALL needs your knowledge and permission, or ’Consent’. Please read this form carefully before signing.</p>
	</div>

    <div class="pt-1">
        <table class="subhead-table">
            <tr>
                <td class="sub-border-bottom"><span class="sub-label f-15">Your Consent</span>
                </td>
            </tr>
        </table>
    </div>
    <div class="pl-align f-col pt-2">
        <p class="f-13 mt-0-im">If you sign this form, you will be agreeing to provide us a copy of all or some of your NDIS plan for the purposes explained on this form.</p>
        <p class="f-13">If you have difficulty understanding this form, you should ask someone you trust (an ‘Independent Person’) to explain it to you and to help you to understand it.</p>
        <p class="f-13">PLEASE NOTE:  You can choose not to give your consent, but if so, ONCALL may not be able to provide the best possible service to you. If you choose to give consent, you may withdraw it at any time.</p>
    </div>

    <div class="pt-2 f-col">
        <table class="confirm-table">
            <tr>
                <td class="border-right f-13 p-1" width="50%">“I consent to provide ONCALL a copy of the relevant funding information in my NDIS plan for the purposes explained on this form”</td>
                <td class="border-right" width="10%"></td>
                <td class="f-13 border-right p-1" width="30%">Please tick the box if you agree</td>
                <td width="10%"></td>
            </tr>
        </table>
    </div>

    <div class="pt-4 f-col">
        <table class="input-table">
            <tr>
                <td width="10%" class="f-13">Name: </td>
                <td class="input-fill f-13"><?php if (isset($data) && isset($data['name'])) { echo $data['name']; }?></td>
            </tr>
            <tr>
                <td colspan="2" class="f-13">(*or guardian’s Name)</td>
            </tr>
        </table>
    </div>
    <div class="pt-4 f-col">
        <table class="input-table">
            <tr>
                <td width="17%" class="f-13">Signature*:</td>
                <td class="input-fill f-13"></td>
            </tr>
            <tr>
                <td colspan="2" class="f-13">(*or guardian’s Signature)</td>
            </tr>
        </table>
    </div>
    <div class="pt-4 f-col">
        <table class="input-table">
            <tr>
                <td width="38%" class="f-13">Name of Independent Person:</td>
                <td class="input-fill f-13"></td>
            </tr>
            <tr>
                <td colspan="2" class="f-13">(if required)</td>
            </tr>
        </table>
    </div>
    <div class="pt-4 f-col">
        <table class="input-table">
            <tr>
                <td width="45%" class="f-13">Signature of Independent Person:</td>
                <td class="input-fill f-13"></td>
            </tr>
            <tr>
                <td colspan="2" class="f-13">(if required)</td>
            </tr>
        </table>
    </div>
    <div class="pt-4 f-col">
        <table class="input-table">
            <tr>
                <td width="10%" class="f-13">Date:</td>
                <td><?php if(isset($generated_date) == true && $generated_date != '') { echo $generated_date;} ?></td>
            </tr>
        </table>
    </div>
</div>
<?php } ?>