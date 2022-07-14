
<style>
.arial_9, .line_items_table_tr th {
  font-family: Arial;
  font-size: 13px;
  vertical-align: top;
  line-height: 17px;
}
.arial_8 {
  font-family: Arial;
  font-size: 11px;
  vertical-align: top;
  line-height: 17px;
}
.line_items_table_tr th {
  border-bottom: 1px solid #000080;
  text-align:left;
  padding: 0px 0px 0px 2px;
}
.arial_caps_12 {
  font-family: Arial;
  vertical-align: top;
  font-size: 15px;
  text-decoration:capitalize;
}
.arial_caps_15 {
  font-family: Arial;
  vertical-align: top;
  font-size: 18px;
  text-decoration:capitalize;
}
.bold {
  font-weight:bold;
}
.blue, .line_items_table_tr th {
  color: #000080;
}
.blue_border {
  border: 1px solid #000080;
  padding: 5px 5px 5px 5px;
}
.under_logo {
  text-align:center;
  width:100%;
}
.textright {
  text-align: right;
}
.textcenter {
  text-align: center;
}
.grey_row td {
  background-color: #ebebeb;
}
table.line_items_table {
  margin-bottom: 2px;
}
.line_items_table td {
  padding: 5px 0px 10px 2px;
  font-family: Arial;
  font-size: 13px;
  line-height: 20px;
  vertical-align: top;
  border-bottom: 1px #ccc solid;
}
.line_items_table tr {
  border: 1px #000 solid;
}
    
.logo_line1  {
  font-family: Arial;
  font-size: 42px;
  line-height: 44px;
  color: #f26e21;
  font-weight: bold;
}
.logo_line2  {
  font-family: Arial;
  font-size: 17px;
  line-height: 23px;
  color: #f26e21;
  font-weight: bold;
}
.green {
  color: green;
}

tr.border_bottom td {
  border-bottom: 1px solid #ccc;
}

</style>

<div>
  <table width="100%">
    <tr>
      <td width="28%">&nbsp;</td>
      <td width="44%" class="logo_section" style="text-align:center">
        <?php if(substr_count($base_url,"localhost") > 0) { ?>
        <div class="logo_line1">
          <span class="green">ON</span>CALL
        </div>
        <div class="logo_line2">
          GROUP AUSTRALIA
        </div>
        <?php } else { ?>
        <img class="log_img" width="200" src="../../../../assets/img/oncall_logo_multiple_color.jpg"/>
        <?php } ?>
      </td>
      <td width="28%" class="arial_9 blue">
        Please make cheque payable to: <br>
        ONCALL Group Australia PTY LTD <br>
        Level 2, 660 Canterbury Road, Surrey Hills 3127
      </td>
    </tr>
    <tr>
      <td style="vertical-align: top">
        <table width="100%">
          <tr>
            <td class="arial_caps_12 bold"><span class="blue">ACCOUNT TO: </span><br> <?= $contact_label ?></td>
            <td class="arial_caps_12">
            <span class="bold"><?= ($managed_type == "1") ? "NDIS PORTAL" : "Accounts payable" ?></span><br>
            <?= $billing_address ?>
            </td>
          </tr>
          <tr><td colspan="2">&nbsp;</td></tr>
          <tr><td colspan="2" class="arial_9">
            <div class="bold blue">Worksite Address & Cost Centre:</div>
            <div><?= $site_address ?></div>
          </td></tr>
        </table>
      </td>
      <td>
        <table width="100%">
          <tr>
            <td class="under_logo arial_caps_15" style="padding-bottom: 30px">&nbsp;
           </td>
          </tr>
          <tr>
            <td>
              <table width="100%">
                <tr>
                  <td width="50%" class="arial_caps_15 bold blue textright">TAX INVOICE:</td>
                  <td width="50%" class="arial_caps_12"><?= $invoice_no ?></td>
                </tr>
                <tr>
                  <td class="arial_caps_12 bold blue textright">INVOICE DATE:</td>
                  <td class="arial_caps_12"><?= $invoice_date ?></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
      <td class="arial_9 blue">
        <div class="bold">
        Direct Credit: Bank of Melbourne <br>
        BSB: 193-879 Account 476 019 701 <br>
        Enter '<?= $invoice_no ?>' as Payment Reference<br><br>
        </div>
        <div>
        ABN 27 633 010 330<br>
        Send Payment Advice to:<br>
        Email: accounts@oncall.com.au<br>
        Ph: 03 9896 2468<br>
        </div>
      </td>
    </tr>
  </table>
  <div>&nbsp;</div>
  <table width="100%" class="line_items_table" cellspacing="0" cellpadding="2">
    <tr class="line_items_table_tr">
      <th width="11%">Shift Date</th>
      <th width="10%">Shift Details</th>
      <th width="20%">Support Item</th>
      <th width="15%">Support Item Number</th>
      <th width="5%">Hours</th>
      <th width="4%">Units</th>
      <th width="3%">Kms</th>
      <th width="6%">Rate</th>
      <th width="7%">Total <br> Ex GST</th>
      <th width="7%">GST</th>
      <th width="7%">Total <br> Incl GST</th>
    </tr>
    <tr>
        <td colspan="11"><span class="blue arial_caps_12">Participant </span>
        <span class="arial_caps_12"><?= $participant_name ?></span>
        <br>
        <span class="blue arial_caps_12">NDIS#</span>
        <span class="arial_caps_12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $ndis_number ?></span>
        </td>
     </tr>
    <?php
     if($invoice_shifts) {
        $invoice_shifts = array_reverse($invoice_shifts);
        $cnt = 0;
        foreach($invoice_shifts as $shift_row) {
          $class = ($cnt == 0 || $cnt % 2 == 0) ? "grey_row" : "";
          $cnt++;
          $hrs_worked = explode(",",$shift_row['hours_worked']);
          $item_name = explode(",",$shift_row['line_item_name']);
          $item_number = explode(",",$shift_row['line_item_number']);
          $rate = explode(",",$shift_row['rate']);
          $row_total_cost = explode(",",$shift_row['row_total_cost']);

        # More than one line items for single shift
         if(count($hrs_worked) > 1) {
              foreach($hrs_worked as $key=> $hrs) {
                $newclass = ($cnt == 0 || $cnt % 2 == 0) ? "grey_row" : "";
                $cnt++;
            ?>     
            <tr class="border_bottom">
                <td><?= $shift_row['job_date'] ?></td>
                <td><?= $shift_row['actual_time'] ?></td>
                <td><?= $item_name[$key] ?></td>
                <td><?= $item_number[$key] ?></td>
                <td><?= $hrs ?></td>      
                <td> - </td>
                <td> - </td>      
                <td><?= $rate[$key] ?></td>      
                <td><?= $row_total_cost[$key] ?></td>
                <td><?= $shift_row['gst'] ?></td>
                <td><?= $row_total_cost[$key] ?></td>
            </tr>
      <?php }
          }
          else if($invoice_shifts) {
           
          ?>     
           <tr class="border_bottom">
            <td><?= $shift_row['job_date'] ?></td>
            <td><?= $shift_row['actual_time'] ?></td>
            <td><?= $shift_row['line_item_name'] ?></td>
            <td><?= $shift_row['line_item_number'] ?></td>
            <td><?= $shift_row['hours_worked'] ?></td>      
            <td> - </td>
            <td> - </td>      
            <td><?= $shift_row['rate'] ?></td>      
            <td><?= $shift_row['total_cost_exc'] ?></td>
            <td><?= $shift_row['gst'] ?></td>
            <td><?= $shift_row['total_cost_inc'] ?></td>
          </tr>
          <?php 
        }
        }
    } 
    else {
    ?>
    <tr><td colspan="11">&nbsp;</td></tr>
    <tr class="grey_row"><td colspan="11">&nbsp;</td></tr>
    <tr><td colspan="11">&nbsp;</td></tr>
    <tr class="grey_row"><td colspan="11">&nbsp;</td></tr>
    <?php
    }
    ?>
    <tr class="total_row">      
        <td colspan="8" class="textright bold">Total: &nbsp; &nbsp;</td>
        <td class="bold"><?= $total_cost_exc ?></td>
        <td class="bold"><?= $total_gst ?></td>
        <td class="bold"><?= $total_cost_inc ?></td>   
    </tr>
  </table>  
</div>
