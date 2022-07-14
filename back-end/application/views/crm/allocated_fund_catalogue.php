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
$name = $participant_row['participant_name'];
$ndis_num = $participant_row['ndis_num'];
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
if($type=='footer'){ ?>
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
if($type=='content'){
	?>
	<div class="px-5">
		<h3>Allocated Funding Details</h3>
		<table class="support_table_" border="1" cellpadding="10" cellspacing="0">
			<thead>
				<tr>
					<th>Support Item No. and Name  <div><small>(as per NDIS price guide)</small></div></th>
					<th>Hourly Rate or Total Amount</th>
					<th>Days of Week</th>
					<th>No. of Hours or Total Quantity <div><small>(Minimum shift request 2 hours)</small></div></th>
					<th>Cost of Support</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($line_items))
				{
					$sum = 0;

					foreach ($line_items as $key => $value) {
						$qty = 1;
						$row_total = $qty*$value['amount'];
						?>
						<tr>
							<td><?php echo $value['line_item_name']?><br/><?php echo $value['line_item_number']?>
						</td>
						<td><?php echo $value['amount']?></td>
						<td>As and when requested </td>
						<td><?php echo $qty?></td>
						<td>$<?php echo $row_total?></td>
					</tr>
					<?php
					$sum = $sum + $row_total;
				}
			}else{
				?>
				<tr><td colspan="5">No record found.</td></tr>
				<?php
			}
			?>
		</tbody>

		<?php
		if(!empty($line_items)){
			?>
			<tfoot>
				<tr>
					<td colspan="5" class="text-right"><font class="bold">Total Service Booking :</font> $<?php echo $sum?></td>
				</tr>
			</tfoot>
		<?php } ?>

	</tbody>
</table>
</div>
<?php
}
?>
