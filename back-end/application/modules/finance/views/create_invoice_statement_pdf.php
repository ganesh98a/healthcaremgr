
<?php if ($type == 'footer') { ?>
    <div class="foot"><div id="leyend_foot" class="center"><p>&copy; 2019 healthcare manager </p></div></div>
<?php } ?>
<?php
if ($type == 'content') { ?>

<style>
.p-0{padding:0}.pt-0,.py-0{padding-top:0}.pr-0,.px-0{padding-right:0}.pb-0,.py-0{padding-bottom:0}.pl-0,.px-0{padding-left:0}.p-1{padding:.25rem}.pt-1,.py-1{padding-top:.25rem}
.pr-1,.px-1{padding-right:.25rem}.pb-1,.py-1{padding-bottom:.25rem}.pl-1,.px-1{padding-left:.25rem}.p-2{padding:.5rem}.pt-2,.py-2{padding-top:.5rem}.pr-2,.px-2{padding-right:.5rem}
.pb-2,.py-2{padding-bottom:.5rem}.pl-2,.px-2{padding-left:.5rem}.p-3{padding:1rem}.pt-3,.py-3{padding-top:1rem}.pr-3,.px-3{padding-right:1rem}.pb-3,.py-3{padding-bottom:1rem}.pl-3,.px-3{padding-left:1rem}.p-4{padding:1.5rem}.pt-4,.py-4{padding-top:1.5rem}.pr-4,.px-4{padding-right:1.5rem}.pb-4,.py-4{padding-bottom:1.5rem}.pl-4,.px-4{padding-left:1.5rem}.p-5{padding:3rem}.pt-5,.py-5{padding-top:3rem}.pr-5,.px-5{padding-right:3rem}.pb-5,.py-5{padding-bottom:3rem}.pl-5,.px-5{padding-left:3rem}
.m-0{margin:0}.mt-0,.my-0{margin-top:0}.mr-0,.mx-0{margin-right:0}.mb-0,.my-0{margin-bottom:0}.ml-0,.mx-0{margin-left:0}.m-1{margin:.25rem}.mt-1,.my-1{margin-top:.25rem}.mr-1,.mx-1{margin-right:.25rem}.mb-1,.my-1{margin-bottom:.25rem}.ml-1,.mx-1{margin-left:.25rem}.m-2{margin:.5rem}.mt-2,.my-2{margin-top:.5rem}.mr-2,.mx-2{margin-right:.5rem}.mb-2,.my-2{margin-bottom:.5rem}.ml-2,.mx-2{margin-left:.5rem}.m-3{margin:1rem}.mt-3,.my-3{margin-top:1rem}.mr-3,.mx-3{margin-right:1rem}.mb-3,.my-3{margin-bottom:1rem}.ml-3,.mx-3{margin-left:1rem}.m-4{margin:1.5rem}.mt-4,.my-4{margin-top:1.5rem}.mr-4,.mx-4{margin-right:1.5rem}.mb-4,.my-4{margin-bottom:1.5rem}.ml-4,.mx-4{margin-left:1.5rem}.m-5{margin:3rem}.mt-5,.my-5{margin-top:3rem}.mr-5,.mx-5{margin-right:3rem}.mb-5,.my-5{margin-bottom:3rem}.ml-5,.mx-5{margin-left:3rem}
.f-bold{font-weight: bold;}.f-light{font-weight: normal;}.pull-left{float: left;}.pull-right{float: right;}.d-table{display:table;}.d-inline-display{display:inline-block}.text-left{text-align:left}.text-right{text-align:right}.text-center{text-align:center}
.bt{border-top:1px solid #777}.br{border-right:1px solid #777}.bb{border-bottom:1px solid #777}.bl{border-left:1px solid #777}.bz{border:1px solid #777}
.font-family{font-family:"sans-serif"}.listing_font_ table td{font-size:12px;}
</style>

<div class="font-family">
    
    <table class="font-family" width="100%" style="border-bottom:1px solid #777; padding-bottom:15px;">
        <tr>
            <td width="20%"><img height="40px" src="<?php echo $logo_path; ?>" /></td>
            <td width="27%" style="padding-left:15px">
                <div>
                    <div>Surrey Hills 3127</div>
                    <div>(03) 9896 2468</div>
                    <div>quotes@oncall.com.au</div>
                </div>
            </td>
            <td width="27%">
                <div>
                    <div class="mb-4"><b>Statement For: <?php echo $user_details['name']; ?></b></div>
                    <div><?php echo $user_details['address']; ?></div>
                    <div><?php echo $user_details['phone']; ?></div>
                    <div><?php echo $user_details['email']; ?></div>
                </div>
            </td>
            <td width="26%">
                <div>
                    <div class="mb-4"><b>Addressed To: <?php echo $user_details['name']; ?></b></div>
                    <div><?php echo $user_details['address']; ?></div>
                    <div><?php echo $user_details['phone']; ?></div>
                    <div><?php echo $user_details['email']; ?></div>
                </div>
            </td>
        </tr>
    </table>
    
    <div style="width:80%; margin:0px auto;">
        <h4  width="50%" class="d-inline-display pull-left f-light my-0 py-3">Statment Issued: <?php echo $issue_date;?></h4>
        <h4  width="50%" class="d-inline-display pull-left f-light my-0 text-right py-3">Statment Payment By: <?php echo $due_date; ?></h4>
        <h3 class="mt-4">Statement Number: <?php echo $statement_number; ?></h3>
        <div class="listing_font_">
            <table class="bl br bb font-family" width="100%" cellpadding="15px" cellspacing="0">
                <tr>
                    <th class="text-center bt br "><b>Invoice Number</b></th>
                    <th class="text-center bt  br "><b>Invoice Description</b></th>
                    <th class="text-center  bt  "><b>Cost (excluding GST)</b></th>
                </tr>
                <?php echo $invoice_rows; ?>
               
                <tr>
                    <td class="br"></td>
                    <td class="text-right br pt-5"><strong>Sub-Total(ex GST)</strong></td>
                    <td class="text-right pt-5">$<?php echo $pdf_sub_total; ?></td>
                </tr>
                
                <tr>
                    <td class="br"></td>
                    <td class="text-right br"><strong>GST</strong></td>
                    <td class="text-right">$<?php echo $pdf_gst; ?></td>
                </tr>
                
                <tr>
                    <td class="br"></td>
                    <td class="text-right br"><strong>Total(incl GST)</strong></td>
                    <td class="text-right">$<?php echo $pdf_total; ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    </div>
<?php } ?>