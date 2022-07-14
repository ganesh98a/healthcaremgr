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
			<span class="f-22 f-white">Consent Form <br /> Release of Personal Information</span>
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
				<td class="sub-border-bottom"><span class="sub-label f-12">Explanation of ‘Personal Information’ and what your ‘Consent’ means:</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="pl-align f-col">
		<p class="f-13 mt-0-im">ONCALL Group Australia Pty Limited (ACN 633 010 330) (ONCALL) keeps information about you on what we call a client file.</p>
		<p class="f-13 pt-0">Client files include a variety of information including: names; contact details; dates of birth; referral information; assessments of your needs; your goals; information about support which can help you achieve these goals; and information about the other agencies and services who may be working with us to assist you.</p>
	</div>
	<div class="pt-1">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label f-12">Why do we collect this information?	</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="pl-align f-col">
		<p class="f-13 mt-0-im">The information we collect helps us to: </p>
		<ul class="f-13">
			<li>&nbsp;&nbsp;&nbsp;&nbsp;Provide the most effective response to your individual needs.</li>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;Plan services with you</li>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;Administer and manage the services we provide</li>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;Keep records of the people we work with for reporting to DHS as required by law</li>
			<li>&nbsp;&nbsp;&nbsp;&nbsp;Keep records of our actions so that we are accountable for them.</li>
		</ul>
	</div>
	<div class="pt-1">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label f-12">Who else may see this information?</span>
				</td>
			</tr>
		</table>
	</div>

	<div class="pl-align f-col">
		<p class="f-13 mt-0-im">We aim to provide you with the best possible support. This may mean involving other support services to help you. </p>
		<p class="f-13">Sometimes ONCALL needs to give some of your information to other people to make sure you get the best support possible. Generally, we do this with your knowledge and permission, or ’Consent’. The information that we keep on your client file can only be read by the professionals from these services who will be involved in providing assistance to you and your family.</p>
		<p class="f-13">From time to time ONCALL will be audited to make sure we are meeting the standards that we operate under.  Our auditors may want to look at your client file to help them understand how we support you, and will only do so with your consent.  Our auditors sign a code of conduct and follow a code of ethics and they will only use your personal information for the purpose of their work and will not pass it on to any other person.</p>
		<p class="f-13">Aside from this, we will only release information about you and your family if you give your consent, or if we are required to do so by law.</p>
		<p class="f-13">Please read this form carefully before signing.</p>
	</div>
</div>
<?php } ?>
<?php if (isset($type) && $type == 'content_2_header') { ?>
<table style="height:70px;">
    <tr>
       <td><img src="<?php echo $logoUrl; ?>" style="height:70px;"/> </td>
   </tr>
</table>
<?php } ?>
<?php if (isset($type) && $type == 'content_2') { ?>
<div class="px-5">
	<div class="pt-0">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label f-12">Your Consent</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="pl-align f-col">
		<p class="f-13 mt-0-im">If you sign this form, you will be allowing ONCALL to release your personal information for the purposes explained on page 1 of this form.</p>
		<p class="f-13">If you have difficulty understanding this form, you should ask someone you trust (an ‘Independent Person’) to explain it to you and to help you to understand it.</p>
		<p class="f-13">PLEASE NOTE:  You can choose not to give your consent, but if so, ONCALL may not be able to provide the best possible service to you. If you choose to give consent, you may withdraw it at any time.</p>
	</div>

	<div class="pt-2 f-col">
		<table class="confirm-table">
			<tr>
				<td class="border-right f-13 p-1" width="50%">“I consent to ONCALL releasing my personal information for the purposes explained on Page 1”.</td>
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
				<td width="10%" class="f-13">Date:</td>
				<td><?php if(isset($generated_date) == true && $generated_date != '') { echo $generated_date;} ?></td>
			</tr>
		</table>
	</div>
</div>
<?php } ?>