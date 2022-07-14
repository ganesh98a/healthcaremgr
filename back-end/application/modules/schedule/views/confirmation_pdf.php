<style>
    .color{color:#777}
    .text-center{text-align:center}
    .text-right{text-align:right}
    .details_table table{border-collapse: collapse; margin-top:30px; font-family:"sans-serif";}
    .details_table table tr td{padding:10px 15px; width:50%}
    .font-family table td{
        font-family:"sans-serif";
    }
    
</style>

<div class="font-family">

<table  width="100%" style="border-bottom:1px solid #777; padding-bottom:15px;">
    <tr>
        <td width="50%" style="padding-left:0px">
        <img height="40px" src="<?php echo base_url('assets/img/ocs_logo.png'); ?>" />
      
        </td>
        <td>
            <table width="100%">
                <tr>
                    <td >
                        <div>
                            
                             <div class="mb-4"><b>Shift id: </b><?php echo $shiftData['id'] ?? 'NA';?></div>
                             <div class="mb-4"><b><?php echo $shiftData['booked_for'] ?? 'NA';?>: </b><?php echo $shiftData['shift_for'] ?? 'NA';?></div>
                             <div class="mb-4"><b>Suburb: </b><?php echo  $shiftData['shift_location'][0]->suburb ?? '';?></div>
                          
                        </div>
                    </td>
                    <td width="150px">
                        <div>
                            <div class="mb-4"><b>Date: </b><?php echo DateFormate($shiftData['shift_date'],DATE_VIEW_FORMAT); ?></div>
                            <div class="mb-4"><b>Start </b><?php echo DateFormate($shiftData['start_time'],TIME_VIEW_FORMAT); ?></div>
                            <div class="mb-4"><b>End </b><?php echo DateFormate($shiftData['end_time'],TIME_VIEW_FORMAT); ?></div>
                            <div class="mb-4"><b>Duration: </b><?php echo $shiftData['duration'] ?? 'NA';?></div>
                        </div>
                    </td>
                  
                </tr>
            </table>
        </td>

    </tr>
</table>
</div>
<div class="details_table">
    <table width="100%">
    <thead>
        <tr>
            <th colspan="2" style="padding:15px"><h2 >Confirmation Details</h2></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-right"><strong>Allocated to member:</strong></td>
            <td><?php echo $shiftData['allocated_member']['0']->memberName ?? 'NA'; ?></td>
        </tr>
        <tr>
            <td class="text-right"><strong>Allocated to member on:</strong></td>
            <td><?php echo $shiftData['allocated_member']['0']->allocate_on ?? 'NA'; ?></td>
        </tr>
        <tr>
            <td class="text-right"><strong>Confirmed with allocated member on:</strong></td>
            <td><?php echo !empty($shiftData['allocated_member']['0']->confirmed_with_allocated) ? DateFormate($shiftData['allocated_member']['0']->confirmed_with_allocated,DATE_TIME_VIEW_FORMAT) : 'NA'; ?></td>
        </tr>
        <tr>
            <td class="text-right"><strong>Confirmed with Booker:</strong></td>
            <td><?php echo $shiftConfirmationData['confirmer_name'] ?? 'NA'; ?></td>
         </tr>
         <tr>
            <td class="text-right"><strong>Confirmed with Booker on:</strong></td>
            <td><?php echo !empty($shiftConfirmationData['confirmed_with_booker']) ? DateFormate($shiftConfirmationData['confirmed_with_booker'],DATE_TIME_VIEW_FORMAT) : 'NA'; ?></td>
        </tr>
        <tr>
            <td class="text-right"><strong>Confirmed Method:</strong></td>
            <td><?php echo get_shift_confirm_by($shiftConfirmationData['confirm_by']?? 0,'NA');?></td>
        </tr>
    </tbody>

    </table>
</div>
