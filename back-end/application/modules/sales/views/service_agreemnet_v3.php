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
    <img src="<?php echo base_url(); ?>assets/img/service_agreement_bg_cover_img.png" 
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
				<td class="f-13">A Service Agreement can be made between a Service Provider and a Participant or their representative. A representative can be the Plan Nominee, Guardian, Administrator, Attorney or someone who has authority over the Participants NDIS file.
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Service Agreement	</span>
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
					<td class="f-13" width="40%">Service Agreement start date:</td>
					<td class="f-13"><?php if(isset($service_agreement) == true && isset($service_agreement['contract_start_date']) == true && $service_agreement['contract_start_date'] != '' && $service_agreement['contract_start_date'] != '0000-00-00 00:00:00') { echo date('d/m/Y', strtotime($service_agreement['contract_start_date']));} ?> </td>
				</tr>
				<tr>
					<td class="f-13" width="40%">For the NDIS Plan:</td>
					<td class="f-13"><?php if(isset($service_agreement) == true && isset($service_agreement['plan_start_date']) == true && $service_agreement['plan_start_date'] != '' && $service_agreement['plan_start_date'] != '0000-00-00 00:00:00') { echo date('d/m/Y', strtotime($service_agreement['plan_start_date']));} ?> to <?php if(isset($service_agreement) == true && isset($service_agreement['plan_end_date']) == true && $service_agreement['plan_end_date'] != '' && $service_agreement['plan_end_date'] != '0000-00-00 00:00:00') { echo date('d/m/Y', strtotime($service_agreement['plan_end_date']));} ?> </td>
				</tr>
			</table>
		</div>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">The NDIS and this Service Agreement</span>
				</td>
			</tr>
		</table>
		<div class="pad-l-3">
			<p class="pt-1 f-13 mt-0">
				This Service Agreement is made for the purpose of providing supports under the National Disability Insurance Scheme (NDIS) plan and sets out ONCALL Group Australia’s Terms and Conditions.
			</p>
			<p class="pt-1 f-13">
				The Parties agree that this Service Agreement is made in the context of the NDIS, which is a scheme that aims to:
			</p>
			<ul class="pt-1">
				<li class="f-13">Support the independence and social and economic participation of people with disability and;</li>
				<li class="f-13 pt-2">Enable people with a disability to exercise choice and control in the pursuit of their goals and the planning and delivery of their supports.</li>
			</ul>
		</div>
	</div>

	<pagebreak />

	<div class="px-5 pt-1">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Participant NDIS Plan Goals</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<p class="f-13 mt-0 mb-0 pad-l-3">
			Participant/Representatives should provide ONCALL with the relevant ‘My Goals’ section in the Participants NDIS Plan to ensure support can be delivered to meet the needs of the Participant.
		</p>
	</div>
	
	<?php 
		if(isset($goals) == true && empty($goals) == false) {
			$inc_goals = 0;
			foreach($goals as $val) {
				if ($val['goal'] !='' || $val['outcome']!='') {
				?>
				<?php
				if ($inc_goals == 0) {
					?>
					<div class="px-5 pt-2">
						<span class="f-13 f-bold">NDIS Plan ‘My Goals’</span>
					</div>
					<?php
				}
				?>
				<div class="px-5 pt-<?php if ($inc_goals > 0) { echo '4'; } else { echo '2'; } ?>">
					<table cell-padding="5" class="sa-input-goal-table pt-1 sa-input-table-col" border="1">
						<tr>
							<td class="f-13 f-bold" width="20%">Goal:</td>
							<td class="f-13"><?php echo $val['goal'];?></td>
						</tr>
						<tr>
							<td class="f-13 f-bold" width="20%">Outcome:</td>
							<td class="f-13"><?php echo $val['objective'];?></td>
						</tr>
					</table>
				</div>
				<?php
				}
				$inc_goals++;
			}
		}
	?>	

	<div class="px-5 pt-2">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Schedule of Supports</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<p class="f-13 mt-0 pad-l-3">
			This Schedule of Supports specifies the agreed supports and costs that will be delivered to the Participant by ONCALL to meet the needs of the participant in line with their stated Goals.
		</p>
	</div>
	<?php 
		if (isset($line_items) == true && empty($line_items) == false && isset($line_items['list']) ==true && empty($line_items['list']) == false) {
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
								<th class="f-13" width="20%">Support Category: </th>
								<td class="f-13" colspan="2"><?php echo $category['cat_name'];?></td>
							</tr>
						</table>
						<table cell-padding="5" class="sa-input-support-table pt-1" border="1">
							<tr>
								<th class="f-13 text-center" width="40%">Support Item Name & #</th>
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
	
	<pagebreak />

	<!-- <pagebreak /> -->

	<div class="px-5 pt-1">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Pricing</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<p class="f-13 mt-0">
				ONCALL adheres to the arrangements set out in the NDIS Price Guide, including price limits for all supports and the prices in the NDIS Support Catalogue.
			</p>
			<ul class="pt-1">
				<li class="f-13">ONCALL display (and update) the NDIS Price Guide on their website for all supports they are registered to provide.</li>
				<li class="f-13 pt-2">ONCALL will include variations or increases made to the NDIS Price Guide during the signed term of a NDIS Service Agreement. This will include any periodic NDIS Price Guide increases where “prices are subject to change” as outlined in the NDIS Price Guide.</li>
			</ul>
		</div>
	</div>
	<div class="px-5 pt-2">
		<span class="f-13 f-brown-light">Additional Expenses</span>
	</div>
	<div class="px-5 pt-2">
		<ul>
			<li class="f-13">Additional expenses are those things that are not included as part of a Participant’s NDIS supports, and are the responsibility of the Participant/Representative and are not included in the cost of the supports, for example entrance fees, cash for meals, etc. Participant/Representatives are also responsible for any transport or entrance fees for the support worker, excluding food.</li>
		</ul>
	</div>
	<div class="px-5 pt-2">
		<span class="f-13 f-brown-light">Cancellations</span>
	</div>
	<div class="px-5 pt-2">
		<ul>
			<li class="f-13">ONCALL monitors Participant/Representative cancellations to understand why they are occurring, and support Participants/Representatives to reduce cancellations where possible.</li>
			<li class="f-13 pt-2">ONCALL adheres to the arrangements set out in the NDIS Price Guide, including the definition of a short-term cancellation, and charges cancellations accordingly. </li>
		</ul>
	</div>

	<!-- <pagebreak /> -->
	<div class="px-5 pt-2">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Capacity Building Supports</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<p class="f-13 mt-0">
				All Capacity Building Supports are delivered Monday – Friday, 9am – 5pm only.
			</p>
			<p class="f-13 mt-0">
				ONCALL adheres to the arrangements set out in the NDIS Price Guide, including price limits for all supports and the prices in the NDIS Support Catalogue. Accordingly, ONCALL charge for Travel, NDIA Reporting and Non-Face to Face for Capacity Building Supports.
			</p>
			<p class="f-13 mt-0">
				Specifically, time spent on activities that assist the Participant to meet their goals and are part of the delivery of the support item as follows:
			</p>
			<p class="f-13 mb-0">
				Time spent on;
			</p>
			<ul class="pt-1">
				<li class="f-13">Face to face meetings at your home, workplace, day service or ONCALL Offices, including provider travel.</li>
				<li class="f-13 pt-2">Non face to face communication with yourself, your family, NDIA, service providers and any other stakeholders as per your consent you provide.</li>
				<li class="f-13 pt-2">Conducting and completing assessments.</li>
				<li class="f-13 pt-2">To complete NDIS Plan Reporting Requirements.</li>
			</ul>
		</div>
	</div>
	<div class="px-5 pt-2">
		<span class="f-13 f-brown-light">Support Coordination Only</span>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<p class="f-13 mt-0">
				As above and any time spent on:
			</p>
			<ul class="pt-1">
				<li class="f-13">Researching suitable service providers to meet your needs.</li>
				<li class="f-13 pt-2">Completion of referrals to your service providers, including initial enquiries and completion of necessary referral forms.</li>
				<li class="f-13 pt-2">Support to submit unscheduled plan review requests/change of circumstances requests.</li>
				<li class="f-13 pt-2">Attendance (to support not advocate) at your plan review meetings with the NDIA at your home, workplace, day service, NDIS office etc.</li>
				<li class="f-13 pt-2">Services relevant to your plan goals/outcomes may include subcontracting to other Registered Providers as necessary and discussed with you.</li>
			</ul>
		</div>
	</div>
	
	<div class="px-5 pt-2">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Transport</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<div class="pad-l-3">
			<span class="f-13 f-brown-light">Capacity Supports</span>
		</div>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<ul class="pt-1">
				<li class="f-13"><span class="f-bold">Provider Travel Time</span></li>
			</ul>
			<p class="f-13 mb-0 mt-0">
				As per the NDIS Price Guide, if a support item is listed in the Support Catalogue for Provider Travel, ONCALL will claim for the spent travelling to appointments and meetings at the support item rate in accordance with the Modified Monash Model (MMM) to determine regional, remote and very remote rates. 
			</p>
		</div>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<ul class="pt-1">
				<li class="f-13"><span class="f-bold">Provider Travel non labour costs </span></li>
			</ul>
			<p class="f-13 mb-0 mt-0">
				As per the NDIS Price Guide, if ONCALL incurs costs, in addition to the cost of a worker’s time, when travelling to deliver Face to-Face supports to a participant (such as road tolls, parking fees and the running costs of the vehicle) a reasonable contribution can be charge for these costs. The NDIS considers that the following would be reasonable contributions:
			</p>
		</div>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<ul class="pt-1">
				<li class="f-13">up to $0.85 a kilometre for a vehicle that is not modified for accessibility; </li>
				<li class="f-13">and other forms of transport or associated costs up to the full amount, such as road tolls, parking, public transport fares. </li>
			</ul>
			<p class="f-13 mb-0 mt-0">
				The Support Coordination  Provider Travel – non labour is claimed using the support item <u>Provider Travel – non-labour costs</u> 07_799_0106_8_3, this support item is included in your Statement of Supports. 
			</p>
		</div>
	</div>
	<!-- 
	<pagebreak />
 -->
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Goods and Services Tax</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<div class="pad-l-3">
			<p class="f-13 mt-0 mb-0">
				GST means a Goods and Services Tax imposed under the A New Tax System (GST) Act 1999 (GST Act).
			</p>
			<p class="f-13 mb-0">
				If any supply made by the party under this Service Agreement is subject to GST, the Participant must pay an additional amount to ONCALL at the GST rate.
			</p>
			<p class="f-13 mb-0">
				Except where expressly stated otherwise, all amounts referred to in this Service Agreement are exclusive of GST.
			</p>
			<p class="f-13 mb-0">
				For the purposes of GST legislation, the Parties confirm that:
			</p>
			<p class="f-13 mb-0">
				A supply of supports under this Service Agreement is a supply of one or more of the reasonable and necessary supports specified in the statement included, under subsection 33(2) of the National Disability Insurance Scheme Act 2013 (NDIS Act), in the Participant’s NDIS plan currently in effect under section 37 of the NDIS Act;
			</p>
			<ul>
				<li class="f-13">the Participant’s NDIS plan is expected to remain in effect during the period the supports are provided; and</li>
				<li class="f-13 pt-2">the Participant/Representative will immediately notify the Provider if the Participant’s NDIS Plan is replaced by a new plan or the Participant stops being a Participant in the NDIS.</li>
			</ul>
		</div>
	</div>
<!-- 
	<pagebreak />
 -->
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Payments</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5">
		<div class="pad-l-3">
			<p class="f-13">The funding allocated under this Service Agreement is managed by, please select: </p>
		</div>
		<?php if(isset($payments) == true && isset($payments['managed_type']) == true && $payments['managed_type'] == 1) { ?>
			<table class="sa-input-table" border="1">
				<tr class="td-tr-color">
					<td class="f-13 f-bold td-tr-color" width="30%" valign="top">NDIA My Place <input type="checkbox" <?php if(isset($payments) == true && isset($payments['managed_type']) == true && $payments['managed_type'] == 1) echo 'checked="true"'; else echo '';?>/></td>
					<td class="td-tr-color">
						<p class="f-13 pt-1"><u>Service Booking Required</u></p><br />
						<p class="f-13 pt-2">Service bookings are used to set aside funding for a registered NDIS provider for the support they will deliver.</p><br />
						<p class="f-13 pt-2">Participant/Representative to select;</p>
						<p class="f-13 mt-0 mb-0"><input type="checkbox" <?php if(isset($payments) == true && isset($payments['portal_managed']) == true && isset($payments['portal_managed']['service_booking_creator']) == true && $payments['portal_managed']['service_booking_creator'] == 1) echo 'checked="true"'; else echo '';?>/> Participant/Representative will create the Service Booking for ONCALL to approve</p>
						<p class="f-13 pt-2"><input type="checkbox"  <?php if(isset($payments) == true && isset($payments['portal_managed']) == true && isset($payments['portal_managed']['service_booking_creator']) == true && $payments['portal_managed']['service_booking_creator'] == 2) echo 'checked="true"'; else echo '';?>/> ONCALL will create the Service Booking and approve on Participant/Representative behalf</p>
					</td>
				</tr>
			</table>
		<?php } ?>
		<?php if(isset($payments) == true && isset($payments['managed_type']) == true && $payments['managed_type'] == 2) { ?>
			<table class="sa-input-table" border="1">
				<tr class="td-tr-color">
					<td class="f-13 f-bold td-tr-color" width="30%" valign="top">Plan-managed <input type="checkbox" <?php if(isset($payments) == true && isset($payments['managed_type']) == true && $payments['managed_type'] == 2) echo 'checked="true"'; else echo '';?>/></td>
					<td class="td-tr-color">
						<p class="f-13 pt-1"><u>Plan Manager Details</u></p><br />
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="35%">Business Name:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['plan_manged']) == true && isset($payments['plan_manged']['account_name']) == true) echo $payments['plan_manged']['account_name'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="20%">Address:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['plan_manged']) == true && isset($payments['plan_manged']['account_address']) == true && isset($payments['plan_manged']['account_address']['address_line_1']) == true) echo $payments['plan_manged']['account_address']['address_line_1'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td colspan="2" class="f-13 padding-empty sa-sub-border-bottom">
									<p><?php if(isset($payments) == true && isset($payments['plan_manged']) == true && isset($payments['plan_manged']['account_address']) == true && isset($payments['plan_manged']['account_address']['address_line_2']) == true) echo $payments['plan_manged']['account_address']['address_line_2']; else echo "&nbsp;";?></p>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="30%">Contact Name:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['plan_manged']) == true && isset($payments['plan_manged']['contact_name']) == true) echo $payments['plan_manged']['contact_name'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="25%">Contact No:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['plan_manged']) == true && isset($payments['plan_manged']['contact_phone']) == true) echo $payments['plan_manged']['contact_phone'];?>
								</td>
							</tr>
						</table>
						<table class="width-tbl">
							<tr>
								<td class="f-13" width="20%">Email:</td>
								<td class="f-13 sa-sub-border-bottom">
									<?php if(isset($payments) == true && isset($payments['plan_manged']) == true && isset($payments['plan_manged']['contact_email']) == true) echo $payments['plan_manged']['contact_email'];?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		<?php } ?>
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
						<table class="width-tbl-1">
							<tr>
								<td>
									<p class="f-13 pt-2"><br/><i>If you have elected to Self-Manage, this includes being in control of selecting and arranging your service providers and supports, as well as requesting invoices, receipts and processing payments through the NDIS Participant portal My Place or directly with your provider of choice.</i></p>
								</td>
							</tr>
							<tr>
								<td>
									<p class="f-13 pt-2">
										If any services in this Service Agreement are self-managed this means you agree to meet ONCALL’s credit terms of 14 days.
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		<?php } ?>
		<div class="pad-l-3">
			<p class="f-13">
				ONCALL collect a signature for all delivered supports and will claim payment for delivered supports via the method selected above on a weekly basis. Invoice Credit Terms are 14 days.
			</p>
		</div>
	</div>
	<div class="px-5">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Responsibilities of Participant/Representative</span>
				</td>
			</tr>
		</table>
		<div class="pad-l-3">
			<p class="f-13 mt-0">The Participant/Representative agrees to:</p>
			<ul>
				<li class="f-13 pt-1">Engage with ONCALL to provide the relevant NDIS Plan information for the support detailed in this Service Agreement, including details of related goals.</li>
				<li class="f-13 pt-1">Provide ONCALL with all relevant information to ensure support can be delivered to meet the needs of the Participant.</li>
				<li class="f-13 pt-1">Contact ONCALL if you have any concerns about the supports being provided.</li>
				
				<li class="f-13 pt-1">Provide ONCALL a minimum of 24 hours’ notice if you cannot make a scheduled appointment; and agree if this notice is not provided, a cancellation fee may apply.</li>
				<li class="f-13 pt-1">Advise ONCALL immediately if your NDIS plan is suspended or replaced by a new NDIS plan or if you stop being a Participant in the NDIS.</li>
				<li class="f-13 pt-1">Provide ONCALL 14 days’ notice if you need to end the Service Agreement.</li>
				<li class="f-13 pt-1">Agree that if either Party seriously breaches this Service Agreement the requirement of notice will be waived.</li>
				<li class="f-13 pt-1">Treat all ONCALL workers with courtesy and respect.</li>
			</ul>
		</div>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Responsibilities of ONCALL</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<div class="pad-l-3">
			<p class="f-13 mt-0">ONCALL agrees to:</p>
			<ul>
				<li class="f-13 pt-1">Adhere to the arrangements set out in the NDIS Price Guide, including price limits for all supports and their prices in the NDIS Support Catalogue.</li>
				<li class="f-13 pt-1">Protect each Participant’s privacy and confidential information, in conjunction with the Consent Form - Collection of Sensitive Information for the purpose of supports being requested.</li>
				<li class="f-13 pt-1">Provide supports in a manner consistent with all relevant laws, including the National Disability Insurance Scheme Act 2013, and Australian Consumer Law.</li>
				<li class="f-13 pt-1">Engage with Participants and their representatives to understand how, when, and where the support detailed in this Service Agreement is to be delivered, including details of related goals, to ensure supports delivered meet the Participants needs.</li>
				<li class="f-13 pt-1">Provide Participants and their Representatives with information on how to provide feedback, or to make a complaint.</li>
				<li class="f-13 pt-1">Provide Participants and their Representatives with information on how incidents are managed.</li>
				<li class="f-13 pt-1">Listen to Participant/Representative feedback and resolve any issues quickly and appropriately.</li>
				<li class="f-13 pt-1">Keep full and accurate support and financial records on the supports provided to the Participant and claim payments/issue regular invoices for the supports delivered to the Participant no greater than 60 days of the support being delivered.</li>
				<li class="f-13 pt-1">Notify Participant/Representative immediately, if ONCALL must cancel or change a scheduled appointment to provide supports.</li>
				<li class="f-13 pt-1">Provide Participants 14 days’ notice if ONCALL need to end this Service Agreement.</li>
				<li class="f-13 pt-1">Agree that if either Party seriously breaches this Service Agreement the requirement of notice will be waived.</li>
				<li class="f-13 pt-1">Treat all Participants and their Representatives with courtesy and respect.</li>
				<li class="f-13 pt-1">Communicate openly and honestly and in a timely manner.</li>
			</ul>
		</div>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">How to resolve problems</span>
				</td>
			</tr>
		</table>
		<div class="pad-l-3">
			<p class="f-13 mt-0 mb-0">ONCALL welcomes feedback. Feedback includes complaints, suggestions for improvement and compliments. Participants/Representatives can request a copy of ONCALL’s Feedback Policy.</p>
			<p class="f-13">If a Participant/Representative is not happy with their supports or wants to provide feedback they can contact:</p>
		</div>
	</div>
<!-- 
	<pagebreak />
	 -->
	<div class="px-5 pt-2">
		<table class="sa-input-table sa-input-table-col" border="1">
			<tr>
				<td colspan="2" class="f-bold">ONCALL Client NDIS Services</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Phone Number:</td>
				<td>
					<p class="f-13">03 9896 2468</p> <br />
					<p class="f-13">9.00 am to 5.00 pm, Monday to Friday, excluding Public Holidays</p>
				</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Web:</td>
				<td>
					<p><a class="f-13 f-brown" href="https://www.oncall.com.au/feedback">www.oncall.com.au/feedback</a></p><br />
					<p class="f-13">The online Feedback form can be completed anonymously</p>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-2">
		<p class="f-13 mb-0">If the Participant/Representative is not satisfied or does not want to talk to an ONCALL representative they can contact;</p>
		<table class="sa-input-table sa-input-table-col" border="1">
			<tr>
				<td colspan="2" class="f-bold">NDIS Quality and Safeguards Commission</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Phone Number:</td>
				<td>
					<p class="f-13">1800 035 544</p> <br />
					<p class="f-13">9.00 am to 5.00 pm, Monday to Friday, excluding Public Holidays</p>
				</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Web:</td>
				<td>
					<p><a class="f-13 f-brown" href="https://www.ndiscommission.gov.au">www.ndiscommission.gov.au</a></p><br />
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-2">
		<p class="f-13 mb-0">If the Participant/Representative wishes to make a complaint about the NDIA or their NDIS Plan they can contact;</p>
		<table class="sa-input-table sa-input-table-col" border="1">
			<tr>
				<td colspan="2" class="f-bold">National Disability Insurance Agency</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Phone Number:</td>
				<td>
					<p class="f-13">1800 800 110</p> <br />
				</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Web:</td>
				<td>
					<p><a class="f-13 f-brown" href="https://www.ndis.gov.au">www.ndis.gov.au</a></p><br />
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Conflict of Interest</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<div class="pad-l-3">
			<p class="f-13 mt-0 mb-0">ONCALL is a registered provider for a suite of support services under the National Disability Insurance Scheme (NDIS). ONCALL is aware of the potential or real or perceived conflict of interest in performing multiple roles for an individual. </p>
			<p class="f-13 mb-0">ONCALL aims;</p>
			<ul>
				<li class="f-13">
					To act in accordance with its values.
				</li>
				<li class="f-13 pt-1">
					To comply with its general and specific obligations as a registered provider of upports under the National Disability Insurance Scheme.
				</li>
				<li class="f-13 pt-1">
					ONCALL staff will act in the best interest of Participants to ensure they are well informed, empowered and able to maximize choice and control and make their own decisions about services.
				</li>
			</ul>
			<p class="f-13 mb-0">ONCALL and its team members will ensure that when providing supports to customers under the NDIS, including when offering, core supports, capital and capacity building any conflict of interest is declared and any risks to Participants are mitigated.</p>
			<p class="f-13 mb-0">ONCALL staff members will not (by act or omission) constrain, influence or direct decision-making by a Participant and/or their family to limit that Participant access to information, opportunities, or choice and control.</p>
		</div>
	</div>
<!-- 
	<pagebreak />
 -->
	<div class="px-5 pt-4">
		<p class="f-13 mb-0">Other support services being provided by ONCALL:</p>
		<p class="f-13 mb-0 pl-3">
			<input type="checkbox" <?php 
			if (isset($service_agreement) == true && isset($service_agreement['additional_services']) == true) {
				if (stripos($service_agreement['additional_services'], '1')) {
					echo 'checked="true"';
				}				
			}
			?>/> Support Coordination
		</p>
		<p class="f-13 mb-0 pl-3">
			<input type="checkbox" <?php 
			if (isset($service_agreement) == true && isset($service_agreement['additional_services']) == true) {
				if (stripos($service_agreement['additional_services'], '2')) {
					echo 'checked="true"';
				}				
			}
			?>/> NDIS Client Services 
		</p>
		<p class="f-13 mb-0 pl-3">
			<input type="checkbox" <?php 
			if (isset($service_agreement) == true && isset($service_agreement['additional_services']) == true) {
				if (stripos($service_agreement['additional_services'], '3')) {
					echo 'checked="true"';
				}				
			}
			?>/> Supported Independent Living 
		</p>
		<p class="f-13 mb-0 pl-3">
			<input type="checkbox" <?php 
			if (isset($service_agreement) == true && isset($service_agreement['additional_services']) == true) {
				if (stripos($service_agreement['additional_services'], '4')) {
					echo 'checked="true"';
				}				
			}
			?>/> Plan Management
		</p>
		<p class="f-13 mb-0">
			<table>
				<tr>
					<td class="f-13  pl-3" width="20%">
						<input type="checkbox" <?php 
			if (isset($service_agreement) == true && isset($service_agreement['additional_services']) == true) {
				if (stripos($service_agreement['additional_services'], '5')) {
					echo 'checked="true"';
				}				
			}
			?>/> Other: 
					</td>
					<td class="sa-sub-border-bottom">
						<?php 
							if (isset($service_agreement) == true && isset($service_agreement['additional_services_custom']) == true) {
								echo $value_check = $service_agreement['additional_services_custom'];
							}
						?>
					</td>
				</tr>
			</table>	
		</p>
		</ul>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Advocacy</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-2">
		<div class="pad-l-3">
			<ul class="ul-a">
				<li class="f-13">
					ONCALL does not offer any Advocacy services.
				</li>
				<li class="f-13 pt-2">
					The National Disability Advocacy Program (NDAP) provides people with disability with access to effective disability advocacy that promotes, protects and ensures their full and equal enjoyment of all human rights enabling community participation.
				</li>
				<li class="f-13 pt-2">
					The Department of Social Services funds organisations to provide advocacy support services to assist participants when engaging in NDIA processes, should they require an advocate. The Disability Advocacy Finder is an online tool to help find NDIS Appeals providers and disability advocacy agencies across Australia. For more information, go to this website: <a class="f-brown" href="https://disabilityadvocacyfinder.dss.gov.au/disability/ndap/">https://disabilityadvocacyfinder.dss.gov.au/disability/ndap/</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Changes to this Service Agreement</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<div class="pad-l-3">
			<p class="f-13 mt-0 mb-0">If changes to the supports or their delivery are required, the parties agree to discuss and review this Service Agreement. The parties agree that any changes to this Service Agreement will be in writing, signed, and dated by all parties.</p>
			<p class="f-13 mb-0">ONCALL will include variations or increases in line with changes made to the NDIS Price Guide during the signed term of this NDIS Service Agreement. This will include any periodic NDIS Price Guide increases where “prices are subject to change”.</p>
			<p class="f-13 mb-0">If the Participant’s NDIS Plan is suspended or replaced by a new plan ,or the plan ends, and a new plan is not in place, the Participant/Representative will immediately notify ONCALL, and any support provided during this period, that is not funded by NDIA will be payable by the Participant/Representative within 30 days.</p>
			<p class="f-13 mb-0">If the Participant stops being a Participant in the NDIS the Participant/Representative will immediately notify ONCALL, any support provided after the NDIS Plan end date will be payable by the Participant/representative within 30 days.  </p>
		</div>
	</div>
<!-- 
	<pagebreak />
 -->
	<div class="px-5 pt-4">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Ending this Service Agreement</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-1">
		<div class="pad-l-3">
			<p class="f-13 mt-0 mb-0">Should either party wish to end this Service Agreement 14 days’ notice is required.</p>
			<p class="f-13 mb-0">If either party breaches this Service Agreement the requirement of notice will be waived.</p>
			<p class="f-13 mb-0">ONCALL reserves the right to cease delivering services in circumstances where the service can no longer be delivered because of:</p>
			<ul class="ul-a">
				<li class="f-13">
					Safety to staff or other people.
				</li>
				<li class="f-13 pt-2">
					Capacity to provide a quality service is exceeded.
				</li>
			</ul>
			<p class="f-13 mb-0">If the Service Agreement ends, all outstanding claims/invoices must be paid within 14 days from invoice date.</p>
		</div>
	</div>

	<pagebreak />

	<div class="px-5">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Contact Details</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 pt-2">
		<table cell-padding="5" class="sa-input-goal-table sa-input-table-col mt-2" border="1">
			<tr>
				<td class="f-13" width="30%">Participant / Representative </td>
				<td class="f-13"><?php if(isset($recipient) == true && isset($recipient['name']) == true) { echo $recipient['name'];} ?></td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Phone </td>
				<td class="f-13"><?php if(isset($recipient) == true && isset($recipient['phone']) == true) { echo $recipient['phone'];} ?></td>
			</tr>
			<tr>
				<td class="f-13" width="30%">Address</td>
				<td class="f-13"><?php if(isset($recipient) == true && isset($recipient['address']) == true) { echo $recipient['address'];} ?></td>
			</tr>
		</table>
	</div>

	<div class="px-5 pt-2">
		<p class="f-13 mb-0"><span class="f-bold">ONCALL</span> can be contacted on:</p>
		<table class="sa-input-table sa-input-table-col" border="1">
			<tr>
				<td class="f-13" width="30%">
					Contact name
				</td>
				<td class="">
					<p class="f-13 pt-1">Program Manager</p><br />
					<p class="f-13 pt-1">NDIS Client Services</p><br />
					<p class="f-13 pt-1">ONCALL Group Australia Pty Ltd</p>
				</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">
					<p class="f-13">Phone [Business Hours]</p><br/>
					<p class="f-13">Phone [After Hours]</p>
				</td>
				<td class="">
					<p class="f-13 pt-1">03 9896 2468</p><br />
					<p class="f-13 pt-1">03 9896 2468 (24/7 for emergencies)</p>
				</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">
					<p class="f-13">Email</p>
				</td>
				<td class="">
					<p class="f-13 pt-1"><u>clientengagement@oncall.com.au</u></p>
				</td>
			</tr>
			<tr>
				<td class="f-13" width="30%">
					<p class="f-13">Address</p>
				</td>
				<td class="">
					<p class="f-13 pt-1">Level 2, 660 Canterbury Rd, Surrey Hills, VIC 3127</u></p>
				</td>
			</tr>
		</table>
	</div>

	<pagebreak />

	<div class="px-5">
		<table class="subhead-table">
			<tr>
				<td class="sub-border-bottom"><span class="sub-label-col f-14">Service Agreement Signatures</span>
				</td>
			</tr>
		</table>
	</div>
	<div class="px-5 mt-2">
		<table class="sa-info-table">
			<tr>
				<td class="f-13">The Service Agreement should be signed by the Participant or their representative. A representative can be the Plan Nominee, Guardian, Administrator, Attorney or someone who has authority over the Participants NDIS file.
				</td>
			</tr>
		</table>
		<p class="f-13 pt-2 f-brown-light">The Parties agree to the terms and conditions of this Service Agreement;</p>
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