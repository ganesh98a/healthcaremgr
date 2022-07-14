<?php
$logoUrl = base_url('assets/img/logo_service_agreement.jpg');
$footerUrl = base_url('assets/img/service_agreement_footer_img.png');
$toula_moustakas_sign = base_url('assets/img/toula_moustakas_sign.png');
if (isset($type) && $type == 'header') { ?>
<div class="header_null"></div>
<?php } ?>
<?php
if (isset($type) == true && $type == 'footer') {
?>
<table>
    <tr>
       <td></td>
   </tr>
</table>
<?php } ?>
<?php
if (isset($type) == true && $type == 'footer_2_content') {
?>
<table>
    <tr>
       <td><img src="<?php echo $footerUrl; ?>"/> </td>
   </tr>
</table>
<?php } ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/stylesheet.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css">
<?php
if (isset($type) == true && $type == 'content_1') {
?>
<div style="position: absolute; left:0; right: 0; top: 0; bottom: 0;">
    <img src="<?php echo base_url(); ?>assets/img/private_contract_cover_img.png" 
         style="width: 210mm; height: 297mm; margin: 0;" />
</div>
<div class="conver_page-name-no">
	<div class="f-16 f-white pt-1"><?php if(isset($account) == true && isset($account['name']) == true) { echo $account['name'];} ?></div>
	<div class="f-16 f-white pt-8"><?php if(isset($participant) == true && isset($participant['my_ndis_number']) == true) { echo $participant['my_ndis_number'];} ?> </div>
</div>
<?php } ?>
<?php
if (isset($type) == true && $type == 'content_2_header') {
?>
<table style="height:70px;">
    <tr>
       <td><img src="<?php echo $logoUrl; ?>" style="height:65px;"/> </td>
   </tr>
</table>
<?php } ?>
<?php if (isset($type) && $type == 'content_2') { ?>
	<div class="px-5">
		<table class="sa-info-table">
			<tr>
				<td class="f-13">A Private Agreement can be made between a Service Provider and a Participant or their representative. A representative can be the Plan Nominee, Guardian, Administrator, Attorney or someone who has authority over the Participants NDIS file.
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Private Agreement</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-3">
		<div class="pt-1">
			<table cell-padding="5" class="sa-input-table pt-1 sa-input-table-col" border="1">
				<tr>
					<td class="f-13" width="40%">Participant Name</td>
					<td class="f-13"><?php if(isset($participant) == true && isset($participant['name']) == true) { echo $participant['name'];} ?></td>
				</tr>
				<tr>
					<td class="f-13" width="40%">Participants DOB</td>
					<td class="f-13"><?php if(isset($participant) == true && isset($participant['date_of_birth']) == true && $participant['date_of_birth'] != '' && $participant['date_of_birth'] != '0000-00-00') { echo date('d/m/Y', strtotime($participant['date_of_birth']));} ?></td>
				</tr>
			</table>
		</div>
	</div>
	<div class="px-5 pt-1">
		<div class="text-center">
			<span class="f-13 pt-2 f-bold">and is made between;</span>
		</div>
		<div class="pt-1">
			<table cell-padding="5" class="sa-input-table pt-1 sa-input-table-col" border="1">
				<tr>
					<td class="f-13" width="40%">Participant or Representative Name </td>
					<td class="f-13"><?php if(isset($recipient) == true && isset($recipient['name']) == true) { echo $recipient['name'];} ?></td>
				</tr>
			</table>
		</div>
	</div>
	<div class="px-5">
		<div class="text-center">
			<span class="f-13 pt-2 f-bold">and;</span>
		</div>
		<div class="pt-1">
			<table class="sa-info-table sa-input-table-col-ol">
				<tr>
					<td class="f-13 px-5">ONCALL Group Australia Pty. Ltd
					</td>
				</tr>
				<tr>
					<td class="f-13 px-5 pt-2 pb-2">2/660 Canterbury Rd, Surrey Hills, Vic 3127
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="px-5 pt-1">
		<div class="text-center">
			<span class="f-13 pt-2 f-bold">for;</span>
		</div>
		<div class="pt-1">
			<table cell-padding="5" class="sa-input-table pt-1 sa-input-table-col" border="1">
				<tr>
					<td class="f-13" width="40%">Private Agreement start date:</td>
					<td class="f-13"><?php if(isset($service_agreement) == true && isset($service_agreement['contract_start_date']) == true && $service_agreement['contract_start_date'] != '' && $service_agreement['contract_start_date'] != '0000-00-00 00:00:00') { echo date('d/m/Y', strtotime($service_agreement['contract_start_date']));} ?> </td>
				</tr>
				<tr>
					<td class="f-13" width="40%">Private Agreement end date:</td>
					<td class="f-13"><?php if(isset($service_agreement) == true && isset($service_agreement['contract_end_date']) == true && $service_agreement['contract_end_date'] != '' && $service_agreement['contract_end_date'] != '0000-00-00 00:00:00') { echo date('d/m/Y', strtotime($service_agreement['contract_end_date']));} ?> </td>
				</tr>
			</table>
		</div>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">This Private Agreement</span>
				</td>
			</tr>
		</table>
		<div class="pad-l-3">
			<p class="pt-1 f-13 mt-0">
				This Private Agreement is presented for the purpose of providing and charging for transport that is not covered by an NDIS Participant’s Funding. The Participant/Representative agrees to pay privately as per ONCALL’s Transport Fee Schedule* and within the standard ONCALL credit terms of 14 days from the date of invoice. 
			</p>
			<p class="pt-1 f-13">
				The Parties agree that this Private Agreement is made in the context of the NDIS, specifically:
			</p>
			<ul class="pt-1">
				<li class="f-13">NDIS Participants may be funded in their NDIS Plan for Transport to enable them to access disability supports outside their home, and to pay for transport that helps them to achieve their goals in their plan.</li>
				<li class="f-13 pt-2">The NDIS generally only fund transport assistance to those who cannot use public transport.</li>
				<li class="f-13 pt-2">The NDIS is not responsible for any transport expenses incurred by a Participant that are not funded in their NDIS Plan.</li>
			</ul>
		</div>
	</div>

	<pagebreak />

	<?php 
		$inc_cate = 0;
		if (isset($line_items) == true && empty($line_items) == false && isset($line_items['list']) ==true && empty($line_items['list']) == false) {
			?>
			<div class="px-5 pt-1">
				<table class="subhead-table">
					<tr>
						<td class="sub-border-bottom"><span class="sub-label-col f-14">Transport Fee Schedule</span>
						</td>
					</tr>
				</table>
			</div>
			<?php
			$list_items = $line_items['list'];
			$total_amount = $line_items['total_amount'];
			$line_item_sa_total = $line_items['line_item_sa_total'];
			$line_item_total = $line_items['line_item_total'];
			$inc_cate = 0;
			foreach ($list_items as $cat_key => $category) {
				?>
					<div class="px-5 pt-<?php if ($inc_cate > 0) { echo '4'; } else { echo '2'; } ?>">
						<table cell-padding="5" class="sa-input-support-table pt-1 sa-input-support-table-col" border="1">
							<tr>
								<th class="f-13" width="20%">Category: </th>
								<td class="f-13" colspan="2"><?php echo $category['cat_name'];?></td>
							</tr>
						</table>
						<table cell-padding="5" class="sa-input-support-table pt-1" border="1">
							<tr>
								<th class="f-13 text-center" width="40%">Item Name</th>
								<th class="f-13 text-center">Units</th>
								<th class="f-13 text-center">Rate</th>
								<th class="f-13 text-center">Funding</th>
							</tr>
							<?php
								$items = $category['items'];
								$sub_total = $category['sub_total'];
								foreach ($items as $key => $item) {
									if(!$item->category_ref){
										continue;
									}
								?>							
									<tr>
										<td class="f-13 " width="40%"><?php echo $item->line_item_name.' / '.$item->line_item_number; ?></td>
										<td class="f-13 " width="20%" align="center"><?php echo ($item->qty!="0" ? $item->qty : ''); ?></td>
										<td class="f-13 " align="right"><?php echo (number_format($item->upper_price_limit,2) != "0.00" ? "$".number_format($item->upper_price_limit,2) : '' ); ?></td>
										<td class="f-13 " align="right"><?php echo (number_format($item->amount,2) != "0.00" ? "$".number_format($item->amount,2) != "0.00" : '');  ?></td>
									</tr>
							<?php } ?>
							<tr>
								<td class="f-13 text-center" colspan="3"><u class="f-bold">Total Amount:</u></td>
								<td class="f-13 " align="right">$<?php echo number_format($sub_total,2); ?></td>
							</tr>
						</table>
					</div>
				<?php
				$inc_cate++;
			}
			?>
			<div class="px-5 pt-4">
			<?php 
				if(!empty($line_item_sa_total) && (number_format($line_item_sa_total,2)) && (number_format($line_item_total,2)) != "0.00") {
				?>
				<table cell-padding="5" class="sa-input-support-table pt-1 sa-input-table-col" border="1">
					<tr>
						<td class="f-13" width="50%">Value of Service Agreement:</td>
						<td class="f-13 text-center"><?php echo (number_format($line_item_sa_total,2) != "0.00" ? "$".number_format($line_item_sa_total,2) : ''); ?></td>
					</tr>
					<?php 
						foreach ($additional_funds as $additional => $sa_additional) {
							if(!empty($sa_additional->additional_title) && (number_format($sa_additional->additional_price,2)) != "0.00") {
						?>
						<tr>						
							<td class="f-13" width="50%"><?php echo $sa_additional->additional_title; ?></td>
							<td class="f-13 text-center"><?php echo (number_format($sa_additional->additional_price,2) != "0.00" ? '$'.number_format($sa_additional->additional_price,2) : ''); ?></td>
						</tr>
					<?php
							}
						}
						?>
						<tr>
							<td class="f-13" width="50%">Total Value of Service Agreement:</td>
							<td class="f-13 text-center"><?php echo (number_format($line_item_total,2) != "0.00" ? "$".number_format($line_item_total,2) : ''); ?></td>
						</tr>
				</table>
				<?php
							
				} else{?>
				<table cell-padding="5" class="sa-input-support-table pt-1 sa-input-table-col" border="1">
						<tr>
							<td class="f-13" width="50%">Total Value of Service Agreement:</td>
							<td class="f-13 text-center"><?php echo (number_format($total_amount,2) != "0.00" ? "$".number_format($total_amount,2) : ''); ?></td>
						</tr>
				</table><?php
							
				} ?>
			</div>
			<?php
		}
	?>

	<div class="px-5 <?php if ($inc_cate > 0) echo 'pt-4'; else echo 'pt-1'; ?>">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Payments</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5">
		<div class="pad-l-3">
			<p class="f-13">The funding allocated under this Private Agreement is managed by: </p>
		</div>
		<?php if(isset($payments) == true && isset($payments['managed_type']) == true && $payments['managed_type'] == 3) { ?>
			<table class="sa-input-table td-tr-color" border="1">
				<tr class="td-tr-color">
					<td class="f-13 f-bold td-tr-color" width="30%" valign="top">
						Self-managed <input type="checkbox" <?php if(isset($payments) == true && isset($payments['managed_type']) == true && $payments['managed_type'] == 3) echo 'checked="true"'; else echo '';?>/>
					</td>
					<td class="td-tr-color">
						<p class="f-13 pt-1"><u>Debtor Details</u></p><br />
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="35%">Debtor Name:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['self_managed']) == true && isset($payments['self_managed']['account_name']) == true) echo $payments['self_managed']['account_name'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="20%">Address:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['self_managed']) == true && isset($payments['self_managed']['account_address']) == true && isset($payments['self_managed']['account_address']['address_line_1']) == true) echo $payments['self_managed']['account_address']['address_line_1'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td colspan="2" class="f-13 padding-empty sa-sub-border-bottom"><p>
									<?php if(isset($payments) == true && isset($payments['self_managed']) == true && isset($payments['self_managed']['account_address']) == true && isset($payments['self_managed']['account_address']['address_line_2']) == true) echo $payments['self_managed']['account_address']['address_line_2']; else echo "&nbsp;";?>
								</p></td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="30%">Contact Name:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['self_managed']) == true && isset($payments['self_managed']['contact_name']) == true) echo $payments['self_managed']['contact_name'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="25%">Contact No:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['self_managed']) == true && isset($payments['self_managed']['contact_phone']) == true) echo $payments['self_managed']['contact_phone'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="20%">Email:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['self_managed']) == true && isset($payments['self_managed']['contact_email']) == true) echo $payments['self_managed']['contact_email'];?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		<?php } ?>
	</div>

	<pagebreak />

	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Terms and Conditions</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5">
		<div class="pad-l-3">
			<ul class="pt-1">
				<li class="f-13">Transport will be only be provided if a signed service agreement or signed private agreement is in place.</li>
				<li class="f-13 pt-2">Credit Terms are strictly 14 days from date of invoice. ONCALL will issue invoices every fortnight.</li>
				<li class="f-13 pt-2">Transport will be provided at the request of the Participant/Representative up to the nominated value or until such time as invoices are outstanding by greater than 14 days from invoice date. At this time, transport will be suspended until the account is settled. Support hours will not be affected.</li>
				<li class="f-13 pt-2">If Transport expenses exceed the agreed amount, the Participant/ Representative acknowledges that they will be personally responsible for paying the excess.</li>
			</ul>
		</div>
	</div>
	<pagebreak />

	<div class="px-5">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Private Agreement Signatures</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 mt-2">
		<table class="sa-info-table">
			<tr>
				<td class="f-13">The Private Agreement should be signed by the Participant or their representative. A representative can be the Plan Nominee, Guardian, Administrator, Attorney or someone who has authority over the Participants NDIS file.
				</td>
			</tr>
		</table>
		<p class="f-13 pt-2 f-brown-light">The Parties agree to the terms and conditions of this Private Agreement;</p>
	</div>
	<div class="px-5 mt-7">
		<table class="width-tbl-1">
			<tr>
				<td class="sa-sub-border-bottom f-13" width="40%"></td><td></td>
			</tr>
		</table>
		<p class="f-13 mt-1 ml-1">Participant/Representative Signature</p>
	</div>
	<div class="px-5">
		<div class="tbl-flex-b">
			<table class="width-tbl-1 tbl-bottom-abs">
				<tr>
					<td class="sa-sub-border-bottom f-13" width="40%"><?php if(isset($recipient) == true && isset($recipient['name']) == true) { echo $recipient['name'];} ?></td><td></td>
				</tr>
			</table>
		</div>
		<p class="f-13 mt-1 ml-1">Participant/Representative Name</p>
	</div>
	<div class="px-5 mt-4">
		<table class="width-tbl-1">
			<tr>
				<td class="sa-sub-border-bottom f-13" width="40%"></td><td></td>
			</tr>
		</table>
		<p class="f-13 mt-1 ml-1">Representative Relationship</p>
	</div>
	<div class="px-5 mt-4">
		<table class="width-tbl-1">
			<tr>
				<td class="sa-sub-border-bottom f-13" width="40%"></td><td></td>
			</tr>
		</table>
		<p class="f-13 mt-1 ml-1">Date</p>
	</div>
	<div class="px-5 mt-5">
		<p class="f-13 mt-1 ml-1">and;</p>
	</div>
	<div class="px-5 mt-4">
		<table class="width-tbl-1">
			<tr>
				<td class="sa-sub-border-bottom f-13" width="40%">
					<img src="<?php echo $toula_moustakas_sign; ?>" style="height:75px;width:115px;"/>
				</td>
				</td>
				<td></td>
			</tr>
		</table>
		<p class="f-13 mt-1 ml-1">Signature of Toula Moustakas</p>
		<p class="f-13 mt-1 ml-1">Executive Manager Accommodation & Client Services</p>
		<p class="f-13 mt-1 ml-1">ONCALL Group Australia Pty Ltd</p>
	</div>
	<div class="px-5 mt-4">
		<table class="width-tbl-1">
			<tr>
				<td class="sa-sub-border-bottom f-13" width="40%"><?php if(isset($generated_date) == true && $generated_date != '') { echo $generated_date;} ?></td><td></td>
			</tr>
		</table>
		<p class="f-13 mt-1 ml-1">Date</p>
	</div>
<?php } ?>