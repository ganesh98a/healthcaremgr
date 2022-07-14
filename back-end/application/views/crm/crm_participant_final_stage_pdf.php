<style type="text/css">
	.p-0{padding:0}
	.pt-0,.py-0{padding-top:0}
	.pr-0,.px-0{padding-right:0}
	.pb-0,.py-0{padding-bottom:0}
	.pl-0,.px-0{padding-left:0}
	.p-1{padding:.25rem}
	.pt-1,.py-1{padding-top:.25rem}
	.pr-1,.px-1{padding-right:.25rem}
	.pb-1,.py-1{padding-bottom:.25rem}
	.pl-1,.px-1{padding-left:.25rem}
	.p-2{padding:.5rem}
	.pt-2,.py-2{padding-top:.5rem}
	.pr-2,.px-2{padding-right:.5rem}
	.pb-2,.py-2{padding-bottom:.5rem}
	.pl-2,.px-2{padding-left:.5rem}
	.p-3{padding:1rem}
	.pt-3,.py-3{padding-top:1rem}
	.pr-3,.px-3{padding-right:1rem}
	.pb-3,.py-3{padding-bottom:1rem}
	.pl-3,.px-3{padding-left:1rem}
	.p-4{padding:1.5rem}
	.pt-4,.py-4{padding-top:1.5rem}
	.pr-4,.px-4{padding-right:1.5rem}
	.pb-4,.py-4{padding-bottom:1.5rem}
	.pl-4,.px-4{padding-left:1.5rem}
	.p-5{padding:3rem}
	.pt-5,.py-5{padding-top:3rem}
	.pr-5,.px-5{padding-right:3rem}
	.pb-5,.py-5{padding-bottom:3rem}
	.pl-5,.px-5{padding-left:3rem}
	.m-0{margin:0}
	.mt-0,.my-0{margin-top:0}
	.mr-0,.mx-0{margin-right:0}
	.mb-0,.my-0{margin-bottom:0}
	.ml-0,.mx-0{margin-left:0}
	.m-1{margin:.25rem}
	.mt-1,.my-1{margin-top:.25rem}
	.mr-1,.mx-1{margin-right:.25rem}
	.mb-1,.my-1{margin-bottom:.25rem}
	.ml-1,.mx-1{margin-left:.25rem}
	.m-2{margin:.5rem}
	.mt-2,.my-2{margin-top:.5rem}
	.mr-2,.mx-2{margin-right:.5rem}
	.mb-2,.my-2{margin-bottom:.5rem}
	.ml-2,.mx-2{margin-left:.5rem}
	.m-3{margin:1rem}
	.mt-3,.my-3{margin-top:1rem}
	.mr-3,.mx-3{margin-right:1rem}
	.mb-3,.my-3{margin-bottom:1rem}
	.ml-3,.mx-3{margin-left:1rem}
	.m-4{margin:1.5rem}
	.mt-4,.my-4{margin-top:1.5rem}
	.mr-4,.mx-4{margin-right:1.5rem}
	.mb-4,.my-4{margin-bottom:1.5rem}
	.ml-4,.mx-4{margin-left:1.5rem}
	.m-5{margin:3rem}
	.mt-5,.my-5{margin-top:3rem}
	.mr-5,.mx-5{margin-right:3rem}
	.mb-5,.my-5{margin-bottom:3rem}
	.ml-5,.mx-5{margin-left:3rem}
	.w-2{width:2%}
	.w-3{width:3%}
	.w-4{width:4%}
	.w-5{width:5%}
	.w-10{width:10%}
	.w-15{width:15%}
	.w-20{width:20%}
	.w-25{width:25%}
	.w-30{width:30%}
	.w-35{width:35%}
	.w-40{width:40%}
	.w-45{width:45%}
	.w-50{width:50%}
	.w-55{width:55%}
	.w-60{width:60%}
	.w-65{width:65%}
	.w-70{width:70%}
	.w-75{width:75%}
	.w-80{width:80%}
	.w-85{width:85%}
	.w-90{width:90%}
	.w-95{width:95%}
	.w-100{width:100%}
	.pull-right{float: right;}
	.pull-left{float: left;}
	.bz-1{border: 1px solid #000;}
	.bl-1{border-left: 1px solid #000;}
	.bt-1{border-top: 1px solid #000;}
	.br-1{border-right: 1px solid #000;}
	.bb-1{border-bottom: 1px solid #000;}

	body{font-family: sans-serif;}
	.d-inline-block{display:inline-block;}
	.text-right{text-align: right;}
	.text-left{text-align: left;}
	.text-center{text-align: center;}

	.Hero_section{position:relative;height:100%;width:100%;font-family:sans-serif}
	.Hero_img{background-image:url(<?php echo base_url('assets/img/hero-banner.png'); ?>);background-repeat: no-repeat;position:absolute;height:100%;width:100%;bottom:0;right:0;background-size:cover;background-position:center right}
	.font-normal{font-weight:normal !important;}
	.bold{font-weight:bold}
	.hero_sub_title{font-size:20px;color:#2596a7}
	.hero_sub_title_2{font-size:20px;font-family:sans-serif;color:#fff}
	.font-1{
		font-size:18px;font-family:sans-serif;color:#fff
	}
	.font-2{
		font-size:14px;font-family:sans-serif;color:#fff
	}
	.heading_{color: #6a2a78;}
	.heading_sub{color: #6a2a78;}

	.ndis_table_set{
		font-family:sans-serif;
		border-collapse:collapse;
	}
	.ndis_table_set tr td{
		word-break: break-all;
		font-size: 15px;
	}
	.ndis_table_set thead th{
		background: #6a2a78;
		color: #fff;
	}
	.support_table_ thead tr th{
		background: #1e98a5;
		color: #fff;
	}
	.contact_details_table{
		border-collapse:collapse;
	}
	.contact_details_table tr th{
		background: #1e98a5;
		color: #fff;
	}
	.font_1_set{
		font-size: 12px;
	}
</style>
<?php
$name = $crmPDetails['participant_name']??'';
$ndis_num = $crmPDetails['ndis_num']??'';
$email = $crmPDetails['email']??'';
$phone = $crmPDetails['phone']??'';
$address = $crmPDetails['address']??'';
$docs_create_date = DATE_CURRENT;

if($type=='header')  { ?>
	<div class="Hero_section">
		<div class="Hero_img">
			<div class="px-4 py-4 pb-5">
				<div><img src=<?php echo base_url('uploads/infographics/oncall-logo.png'); ?> width="250px"/></div>
			</div>
			<div class="px-4 py-4 pb-5">
				<div class="hero_sub_title">A Division of Oncall Trust</div>
				<div class="hero_sub_title pb-3">ABN: 69 346 459 755</div>
				<div class="hero_sub_title">Level 2/660 Canterbury Rd</div>
				<div class="hero_sub_title">Surrey Hills VIC 3127</div>
				<div class="hero_sub_title">P: 03 9896 2468</div>
				<div class="hero_sub_title">F: 03 9899 7012</div>
				<div class="hero_sub_title">W: oncall.com.au</div>
			</div>

			<div class="w-100" style="padding-top:70px">
				<div class="w-60 pull-left"> &nbsp; </div>
				<div class="w-40 pull-right">
					<div class="hero_sub_title_2"><b>NDIS</b></div>
					<div class="hero_sub_title_2"><b>SERVICE AGREEMENT</b></div>

					<div class="hero_sub_title_2 pt-4 pb-3">PREPARED FOR:</div>
					<div class="font-1"> NAME:</div>
					<div class="font-2"><?php echo $name?></div>

					<div class="font-1 pt-5">NDIS PARTICIPANT NUMBER:</div>
					<div class="font-2"><?php echo $ndis_num?></div>
				</div>
			</div>
		</div>
	</div>
	<?php 
}

if($type=='content'){ 
	?>
	<div class="px-2 py-2 bz-1">
		NOTE: A Service Agreement can be made between a Participant and a Provider or a
		Participant’s representative and a Provider. A Participant’s representative is someone close
		to the Participant, such as a family member or friend or someone who manages funding or
		is a decision maker for supports under a Participant’s NDIS plan.
	</div>

	<h3 class="pt-5">Parties</h3>
	<div class="bz-1">
		<div class="px-2 py-2 ">
			<div class="bold">This Service Agreement is for:</div>
			<div><?php echo $name?> </div>
		</div>
		<div class="px-2 py-2 bt-1 bb-1">
			<div class="bold">a participant in the National Disability Insurance Scheme, and is made between:</div>
			<div><?php echo $name?></div>
		</div>
		<div class="px-2 py-2 ">
			<div class="bold">and</div>
			<div>ONCALL Personnel & Management Services Pty Ltd </div>
		</div>
	</div>

	<div class="py-4">This Service Agreement will commence on <span class="bb-1 px-2"><?php echo (!empty($start_date))?date('d/m/Y',strtotime($start_date)):'00/00/0000'; ?></span> for the NDIS plan <span class="bb-1 px-2"><?php echo (!empty($start_date))?date('d/m/Y',strtotime($start_date)):'00/00/0000'; ?> - <?php echo (!empty($end_date))?date('d/m/Y',strtotime($end_date)):'00/00/0000'; ?></span></div>



	<h3 class="pt-5">The NDIS and this Service Agreement</h3>
	<p class="mt-0 mb-2">This Service Agreement is made for the purpose of providing supports under the
	Participant’s National Disability Insurance Scheme (NDIS) plan.</p>
	<p class="mt-0 mb-2">A copy of the Participant’s NDIS plan has been sighted, at a minimum or held on file
	(relevant parts or entire plan).</p>
	<p class="mt-0 mb-2">The Parties agree that this Service Agreement is made in the context of the NDIS, which is a
	scheme that aims to:</p>

	<div class="pl-3">
		<ul>
			<li class="mb-3"> Support the independence and social and economic participation of people with
			disability,</li>
			<li>Enable people with a disability to exercise choice and control in the pursuit of their
			goals and the planning and delivery of their supports.</li>
		</ul>
	</div>


	<h3 class="pt-5">Schedule of supports</h3>
	<p class="mt-0 mb-2">The supports and their prices are set out in the Schedule of Supports. All prices are GST
		inclusive (if applicable) and include the cost of providing the supports as per the NDIS Price
	Guide (Victoria).</p>

	<pagebreak>
		<p class="mt-0 mb-2">ONCALL will include variations or increases made to the NDIS Price Guide during the signed
			term of NDIS Service Agreement. This will include any periodic NDIS Price Guide increases
		where “prices are subject to change” as outlined in the NDIS Price Guides.</p>

		<p class="mt-0 mb-2">ONCALL display (and update) on their website, the relevant prices in use for services (as per
		the NDIS Price Guide) that they are registered to deliver.</p>

		<p class="mt-0 mb-2">Additional expenses (i.e. things that are not included as part of a Participant’s NDIS supports)
			are the responsibility of _____________________________and are not included in the
			hourly rate for the support worker’s time. Examples include entrance fees, event tickets,
			meals, etc. Cash for such expenses should be provided to the support worker or Participant
			on the day it is required only. A cash-log-book should be used on the day (by both the
			support worker and Guardian/Nominee/Representative) to demonstrate the exchange of
			cash funds and the support worker will provide a receipt for expenses, on completion of
		shift.</p>



		<h3 class="pt-5">Temporary Transformation Payment (TTP)</h3>
		<p class="mt-0 mb-2">Temporary Transformation Payment for Attendant Care and Community Participation
		supports.</p>
		<p class="mt-0 mb-2">From 1 July 2019, providers of attendant care and community/centre based activities have
		access to a higher support price limit through a Temporary Transformation Payment (TTP).</p>
		<p class="mt-0 mb-2">The TTP is a conditional loading to assist providers with any costs associated in transitioning
		to the NDIS.</p>
		<p class="mt-0 mb-2">In 2019–20, the TTP is set at 7.5 % on the relevant level 1 support item and will reduce by
		1.5 per cent each year thereafter.</p>
		<p class="mt-0 mb-2">The TTP support item number is the support base number with the addition of the letter T.</p>
		<p class="mt-0 mb-2">These support item numbers are outlined in the NDIS Support Catalogue 2019–20, effective
		1 July 2019.</p>
		<p class="mt-0 mb-2">As of 1 July 2019, Participants are automatically indexed with the TTP increase (7.5%) in their
		support budget in their NDIS plan and ONCALL will apply the TTP loading on this date. </p>


		<h3 class="pt-5">Participant Transport</h3>
		<p class="mt-0 mb-2">ONCALL advises that the rate for mileage is charged at $1.15 per kilometre travelled in a
		support worker’s vehicle. This is an addition cost to the support worker’s time.</p>
		<p class="mt-0 mb-2">A participant can fund this service by either:</p>

		<div class="pl-3">
			<ul>
				<li class="mb-3">Utilizing their Transport support category budget within their NDIS Plan or; </li>
				<li class="mb-3">Pay for this service through another source of income (not in the NDIS Plan)</li>
			</ul>
		</div>


		<h3 class="pt-5">Establishment fee for personal care / community access</h3>
		<p class="mt-0 mb-2">
			This fee applies to all plans for new NDIS participants in their first plan who receive at least
			20 hours of personal care / community access per month. This payment is to cover nonongoing costs for providers establishing arrangements and assisting participants in
			implementing their plan. The establishment fee is claimable by the provider who assists the
			participant with the implementation of their NDIS plan, delivers a minimum of 20 hours per
			month of personal care / community access support and has made an agreement with the
			participant to supply these services.
		</p>


		<h3 class="pt-5">Responsibilities of Provider</h3>
		<p class="mt-0 mb-2">The Provider agrees to:</p>
		<div class="pl-3">
			<ul>
				<li> review the provision of supports as required with the participant
				</li>
				<li> once agreed, provide supports that meet the Participant’s needs at the
					Participant’s preferred times/days
				</li>
				<li> listen to the Participant’s feedback and resolve problems quickly
				</li>
				<li> communicate openly and honestly in a timely manner and treat the Participant
					with courtesy and respect
				</li>
				<li> consult the Participant on decisions about how supports are provided
				</li>
				<li> give the Participant information about managing any complaints
				</li>
				<li> notify the Participant immediately, if the Provider has to change a scheduled
					appointment to provide supports
				</li>
				<li> give the Participant the required notice if the Provider needs to end the Service
					Agreement (see ‘Ending this Service Agreement’ below for more information)
				</li>
				<li> protect the Participant’s privacy and confidential information
				</li>
				<li> provide supports in a manner consistent with all relevant laws, including the
					National Disability Insurance Scheme Act 2013 (link is external) and rules (link is
					external), and the Australian Consumer Law; keep accurate records on the
					supports provided to the Participant, and issue regular invo
				</li>
			</ul>
		</div>


		
			<h3 class="pt-5">Responsibilities of ______________________________</h3>
			<p class="mt-0 mb-2">The Participant or the Participant’s Representative agrees to:</p>
			<div class="pl-3">
				<ul>

					<li class="mt-0 mb-2">Inform the Provider about how they wish the supports to be delivered to meet the
						Participant’s needs
					</li>
					<li class="mt-0 mb-2">Treat the Provider with courtesy and respect
					</li>
					<li class="mt-0 mb-2">Talk to the Provider if the Participant has any concerns about the supports being
						provided
					</li>
					<li class="mt-0 mb-2">Give the Provider a minimum of 24 hours’ notice if the Participant cannot make a
						scheduled appointment; and if the notice is not provided, a cancellation fee may
						apply.
						DocuSign Envelope ID: 544A4CD4-2A6E-4D39-8938-5095696C8E90
						<?php echo $name?>
					</li >
					<li class="mt-0 mb-2">Give the Provider the required notice if the Participant needs to end the Service
						Agreement (see ‘Ending this Service Agreement’ below for more information), and
						Let the Provider know immediately if the Participant’s NDIS plan is suspended or
					replaced by a new NDIS plan or the Participant stops being a participant in the NDIS.</li>
				</ul>
			</div>

			<pagebreak>
			<h3 class="pt-5">Payments</h3>
			<p class="mt-0 mb-2">The Provider will seek payment for their provision of supports after the participant /
				participant’s representative confirms service delivery took place (i.e by signing the support
			worker’s timesheet for the service delivered).</p>
			<p class="mt-0 mb-2">The provider will seek payment for their provision of supports:</p>
			<p class="mt-0 mb-2">The participant has nominated the NDIA to manage the funding for
				supports provided under this Service Agreement. After providing those
				supports, the provider will claim payment for those supports from the
			NDIA.</p>


			<h3 class="pt-5">Changes to this Service Agreement</h3>
			<p class="mt-0 mb-2">If changes to the supports or their delivery are required, the Parties agree to discuss and
			review this Service Agreement.</p>

			<h3 class="pt-5">
			Ending this Service Agreement</h3>
			<p class="mt-0 mb-2">Should either Party wish to end this Service Agreement they must give 14 days notice.
				If either Party seriously breaches this Service Agreement the requirement of notice will be
			waived.</p>

			<h3 class="pt-5">Feedback, complaints and disputes</h3>
			<p class="mt-0 mb-2">If the Participant wishes to give the Provider feedback, the Participant can talk to the
			Program Manager for Client & NDIS Services on 9896 2468 during business hours.</p>
			<p class="mt-0 mb-2">If the Participant is not happy with the provision of supports and wishes to make a
				complaint, the Participant can talk to the Program Manager for Client & NDIS Services on
			9896 2468.</p>
			<p class="mt-0 mb-2">If the Participant is not satisfied or does not want to talk to another representation at
				ONCALL, the Participant can contact the National Disability Insurance Agency by calling 1800
			800 110, visiting one of their offices in person, or visiting ndis.gov.au for further information.</p>

			<h3 class="pt-5">Goods and services tax (GST)</h3>
			<p class="mt-0 mb-2">For the purposes of GST legislation, the Parties confirm that:</p>
			<div class="pl-3">
				<ul>
					<li class="mt-0 mb-2">
						a supply of supports under this Service Agreement is a supply of one or more of the
						reasonable and necessary supports specified in the statement included, under
						subsection 33(2) of the National Disability Insurance Scheme Act 2013 (link is
						external) (NDIS Act), in the Participant’s NDIS plan currently in effect under section
						37 of the NDIS Act;
					</li>
				</ul>
			</div>

			<pagebreak>
				<h3>Contact details:</h3>
				<div class="pt-4"><?php echo $name?></div>
				<div class="pt-4 pb-3"><small>can be contacted on:</small></div>
				<table class="contact_details_table" border="1" width="100%" cellpadding="10px">
					<tr>
						<th width="30">Phone</th>
						<td width="70"><?php echo $phone?></td>
					</tr>
					<tr>
						<th width="30">Mobile</th>
						<td width="70"><?php echo $phone?></td>
					</tr>
					<tr>
						<th width="30">Email</th>
						<td width="70"><?php echo $email?> <<?php echo $email?>>;</td>
					</tr>
					<tr>
						<th width="30">Address</th>
						<td width="70"><?php echo $address?></td>
					</tr>
				</table>


				<div class="pt-4 pb-3"><small>Provider can be contacted on:</small></div>
				<table class="contact_details_table" border="1" width="100%" cellpadding="10px">
					<tr>
						<th width="30">Contact name</th>
						<td width="70">Program Manager
							<div>Client & NDIS Services</div>
						ONCALL Personnel & Management Services Pty Ltd</td>
					</tr>
					<tr>
						<th width="30">Phone [Business Hours]
							<div> Phone [After Hours]</div></th>
							<td width="70">9896 2468
								<div>9896 2468 (24/7 for emergencies)</div></td>
							</tr>
							<tr>
								<th width="30">Email</th>
								<td width="70">intak@oncall.com.au </td>
							</tr>
							<tr>
								<th width="30">Address</th>
								<td width="70">Leave 2, 660 Canterbury Rd, Surrey Hills, VIC 3127    </td>
							</tr>
						</table>

						<pagebreak>
							<h3>Agreement signatures</h3>
							<p style="margin-bottom:100px">The Parties agree to the terms and conditions of this Service Agreement.</p>

							<div><div class="bt-1 w-35"></div></div>
							<div style="width:100%; height:100px;">
								<div class="pt-3 pb-2 font_1_set">Signature of </div>
								<div class="font_1_set"><?php echo $name?></div>
							</div>


							<div><div class="bt-1 w-35"></div></div>
							<div class="font_1_set pt-4">Date</div>
							<div style="width:100%; height:120px;"><?php echo $docs_create_date?></div>

							<div><div class="bt-1 w-35"></div></div>
							<div style="width:100%; height:120px;">
								<div class="pt-3 pb-2 font_1_set">Signature of Toula Moustakas </div>
								<div class="font_1_set pb-2">Executive Manager Accommodation & Client Services</div>
								<div class="font_1_set">ONCALL Personnel & Management Services Pty Ltd</div>
							</div>
							<div><div class="bt-1 w-35"></div></div>
							<!-- <div class="font_1_set pt-4">Date:00/00/0000</div> -->
							<?php 
						}
						?>