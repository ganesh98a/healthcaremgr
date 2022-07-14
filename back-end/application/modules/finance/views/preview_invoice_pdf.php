
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
  border-bottom: 1px solid #000;
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
.line_items_table td {
  padding: 5px 0px 20px 2px;
  font-family: Arial;
  font-size: 13px;
  line-height: 20px;
  vertical-align: top;
  border-bottom: 1px #ccc solid;
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
.total_row td {
  border-top: 1px solid #000;
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
            <td class="arial_caps_12 bold blue">ACCOUNT TO:</td>
            <td class="arial_caps_12">
            <span class="bold"><?= $contact_label ?></span><br>
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
            <td class="under_logo arial_caps_15" style="padding-bottom: 30px"><?php
            if($invoice_type_label == 'COS (65+)') {
              $invoice_type_label = "COS";
            }
            echo $invoice_type_label;
            ?> Invoice</td>
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
      <th width="11%">Job Date</th>
      <th width="16%">Worker Name</th>
      <th width="20%">Shift</th>
      <th width="6%">Hours Worked</th>
      <th width="6%">Rate</th>
      <th width="6%">S/O</th>
      <th width="6%">Allow Hours</th>
      <th width="6%">Allow Rate</th>
      <th width="6%">Kms</th>
      <th width="6%">Exp.</th>
      <th width="7%">Total Ex GST</th>
      <th width="7%">GST</th>
      <th width="7%">Total Incl GST</th>
    </tr>
    <?php if($invoice_shifts) { 
      $cnt = 0;
      foreach($invoice_shifts as $shift_row) {
        $class = ($cnt == 0 || $cnt % 2 == 0) ? "grey_row" : "";
        $cnt++;
    ?>
    <tr class="<?= $class ?>">
      <td><?= $shift_row['job_date']." ".$shift_row['job_day'] ?></td>
      <td><?= $shift_row['member_fullname'] ?></td>
      <td><?= $shift_row['actual_time']; ?></td>
      <td><?php
        $slices = explode(",",$shift_row['hours_worked']);
        foreach($slices as $data) {
          echo "<div>".$data."</div>";
        }
      ?></td>
      <td><?php
        $slices = explode(",",$shift_row['rate']);
        foreach($slices as $data) {
          echo "<div>".$data."</div>";
        }
      ?></td>
      <td><?= $shift_row['sleepover'] ?></td>
      <td><?php
        $slices = explode(",",$shift_row['hours_allowance']);
        foreach($slices as $data) {
          echo "<div>".$data."</div>";
        }
      ?></td>
      <td><?php
        $slices = explode(",",$shift_row['rate_allowance']);
        foreach($slices as $data) {
          echo "<div>".$data."</div>";
        }
      ?></td>
      <td>&nbsp;</td>
      <td><?= $shift_row['expenses'] ?></td>
      <td><?= $shift_row['total_cost_exc'] ?></td>
      <td><?= $shift_row['gst'] ?></td>
      <td><?= $shift_row['total_cost_inc'] ?></td>
    </tr>
    <?php }
    }
    else {
    ?>
    <tr><td colspan="13">&nbsp;</td></tr>
    <tr class="grey_row"><td colspan="13">&nbsp;</td></tr>
    <tr><td colspan="13">&nbsp;</td></tr>
    <tr class="grey_row"><td colspan="13">&nbsp;</td></tr>
    <?php
    }
    ?>
    <tr class="total_row">
      <td colspan="10" class="textright bold">Total: &nbsp; &nbsp;</td>
      <td class="bold"><?= $total_cost_exc ?></td>
      <td class="bold"><?= $total_gst ?></td>
      <td class="bold"><?= $total_cost_inc ?></td>
    </tr>
  </table>
</div>
