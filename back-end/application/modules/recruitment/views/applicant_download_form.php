<style>
.questionLeftTd {
  border-bottom:1px solid;
  border-color: #BFBFBF;
  padding-top:8px;
  padding-bottom:8px;
}
.questionRightTd {
  border-left:1px solid;
  border-bottom:1px solid;
  padding-top:8px;
  padding-bottom:8px;  
  border-color: #BFBFBF;
}
.tdWithoutBorder {
	padding-top:8px;padding-bottom:8px;
}
.mainTable{
	border-collapse: collapse;
	border-bottom:1px solid;
	margin-top: 10px; 
	border-color: #BFBFBF;
}
</style>
<div>
  <table width="100%">
    <tr>
      <td width="60%">&nbsp;</td>
      <td width="40%" class="logo_section" style="text-align:right;">
        <div class="logo_line1">
		  <img class="log_img" width="200" src="<?php echo $_SERVER['DOCUMENT_ROOT']; ?>/assets/img/oncall_logo_multiple_color.jpg"/>
        </div>
      </td>
	</tr>
	<tr  style="background-color: #F1F4F9;">
	   <td colspan="2">
	   <h3><?php echo $data['data']['title'];?></h3>
	       <table width="100%" class="mainTable">
		      <tr>
			     <td width="15%" class="tdWithoutBorder"> Job<td>
				 <td width="40%" class="tdWithoutBorder"><?php echo $applicantInfo['title'];?></td>
				 <td width="45%"> </td>
			  </tr>
			  <tr>
			     <td width="15%" class="tdWithoutBorder"> Applicant Info<td>
				 <td width="40%" class="tdWithoutBorder"><?php echo $data['data']['applicant_id'].' | '.$applicantInfo['firstname'].' '.
				                  $applicantInfo['lastname'].' | '.$applicantInfo['email'];?></td>
				 <td width="45%"> </td>
			  </tr>
			  <tr>
			     <td width="15%" class="tdWithoutBorder"> Application Info<td>
				 <td width="40%" class="tdWithoutBorder"><?php echo $data['data']['application_id'];?></td>
				 <td width="45%"> </td>
			  </tr>
		   </table>
	   </td>
	</tr>
	<tr>
	   <td colspan="2" style="padding-top:100px;">

	   </td>
	</tr>
   </table>
</div> 

<table width="100%" style="border-collapse:collapse;" autosize="1">
    <tr style="background-color: #E7E6E6;">
	   <td width="50%" class="questionLeftTd"><strong>Question</strong></td>
	   <td width="50%" class="questionRightTd"><strong>Answer</strong></td>
	</tr>
	<?php foreach($questionList as $key => $value) {
        $answer_text = '';
	    $question = (isset($value->question)) ? $value->question : "";
		$question_option = (isset($value->question_option)) ? $value->question_option : "";
        if(isset($value->answer_text)) {
		    $answer_text = $value->answer_text;
        } else if(!isset($value->answer_text) && is_array($value->answer_id)) {
            $answer_id = $value->answer_id;
            $answer_text_array = [];
            foreach($value->answers as $key => $innerValue){
                if(in_array($innerValue['answer_id'], $answer_id)){
                    $answer_text_array[] = $innerValue['value'];
                }
            }
            if(count($answer_text_array)){
                $answer_text = implode(',', $answer_text_array);
            }
        }
	?>
	<tr>
	    <td width="50%" class="questionLeftTd"><?php echo $question; ?> </td>
	    <td width="50%" class="questionRightTd"><?php echo $question_option . $answer_text; ?></td>
	</tr>
	<?php } ?>
</table>  
	