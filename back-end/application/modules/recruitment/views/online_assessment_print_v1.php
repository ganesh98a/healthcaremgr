<?php
/**
 * @var \CI_Controller $this
 * @var array[] $data
 */
$logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');
$footerUrl = base_url('assets/img/ocs_logo.png');
$tickImg = base_url('assets/img/green-tick.jpg');
$crossImg = base_url('assets/img/red-cross.jpg');
$application = '';
if (!empty($assessment) && !empty($assessment->applicant_name)) {
    $application = $assessment->applicant_name;
}
if (!empty($assessment) && !empty($assessment->application_id)) {
    $application .= ($application != '' ? ' - ' : ''). $assessment->application_id;
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/stylesheet.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css">
<?php
if (isset($type) == true && $type == 'header') {
?>
<div class="px-5 pt-4 pb-4 br-bot-1">
    <table class="oa_print_header">
        <tr>
            <td width="33.3%"><img src="<?php echo $logoUrl; ?>" style="height:30px;"/> </td>
            <td width="33.3%" class="td-align-center"><span class="f-15 txt-uppercase"><?=$application?></span></td>
            <td width="33.3%" class="td-align-right"><span class="f-15">ONLINE ASSESSMENT</span></td>
        </tr>
    </table>
</div>
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
if (isset($type) == true && $type == 'content') {

    $template_txt = '';
    if (!empty($template) && !empty($template->title)) {
        $template_txt = $template->title;
    }
    $job_type = '';
    if (!empty($assessment) && !empty($assessment->sub_category_name)) {
        $job_type = $assessment->sub_category_name;
    }
    $submitted_on = '';
    if (!empty($assessment) && !empty($assessment->completed_date_time)) {
        $submitted_on = date('d/m/Y | H:i A', strtotime($assessment->completed_date_time));
    }

    $grade = '';
    if (!empty($assessment) && !empty($assessment->marks_scored)) {
        $grade = $assessment->marks_scored;
    }
    if (!empty($assessment) && !empty($assessment->total_grade)) {
        $grade .= ($grade != '' ? ' / ' : '0 / '). $assessment->total_grade;
    }

    $percentage = '';
    if (!empty($assessment) && !empty($assessment->percentage)) {
        $percentage = $assessment->percentage;
    }

    $status = '';
    $status_txt = '';
    if (!empty($assessment) && !empty($assessment->status)) {
        $status = (integer) $assessment->status;
    }

    $statusData = [ 1 => 'Sent', 2 => 'In-progress', 3 => 'Submitted', 4 => 'Completed', 5 => 'Link Expired', 6 => 'Error', 8 => 'Session Expired'];
    $status_txt = isset($statusData[$status]) ? $statusData[$status] : '';
?>
    <div class="px-5 pt-4">
        <span class="f-14 f-bold">ASSESSMENT DETAILS</span>
    </div>
    <div class="px-5 pt-1">
        <div class="pt-1">
            <table cell-padding="10" class="on-assessment-details" border="1">
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Assessment</td>
                    <td class="f-13 p-2"><?=$template_txt?></td>
                </tr>
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Application</td>
                    <td class="f-13 p-2 txt-uppercase"><?=$application?></td>
                </tr>
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Job Type</td>
                    <td class="f-13 p-2"><?=$job_type?></td>
                </tr>
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Submitted On</td>
                    <td class="f-13 p-2"><?=$submitted_on?></td>
                </tr>
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Status</td>
                    <td class="f-13 p-2"><?=$status_txt?></td>
                </tr>
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Grade</td>
                    <td class="f-13 p-2"><?=$grade?></td>
                </tr>
                <tr>
                    <td class="f-13 f-bold p-2" width="25%">Percentage</td>
                    <td class="f-13 p-2"><?=$percentage?> %</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="px-5 pt-4">
        <div class="q_and_ans-header">
            <span class="f-16 f-bold ">QUESTION AND ANSWERS</span>
        </div>
    </div>
    <?php
        if (!empty($question_answer)) {
            $qu_index = 1;
            foreach($question_answer as $ind => $quest_ans) {
                $quest_ans = (object) $quest_ans;
                $qu_index = $quest_ans->serial_no ?? '';
                $is_passage = (integer) $quest_ans->is_passage;
                $index_incre = true;

                if ($is_passage !== 1) {
                    $suggest_answer = $quest_ans->suggest_answer ?? '';
                    $is_mandatory = (integer) $quest_ans->is_mandatory;
                    $is_mandatory_check = $is_mandatory === 1 ? true : false;
                    $score_ques = (integer) $quest_ans->score;
                    $grade_ques =  $quest_ans->grade ?? '0';
                    $question_txt =  (string) $quest_ans->question;
                    $answer_type = (integer) $quest_ans->answer_type;
                    $answer_short = (string) $quest_ans->answer;
                    $is_answered = false;
                    if ($answer_type == 6){
                        if(count($quest_ans->answer_array) >  0)
                        {
                            $is_answered = true;  
                        }
                    }else {
                        if(!empty($answer_short))
                        {
                            $is_answered = true;
                        }
                    }
                    $result = (integer) $quest_ans->result;
                    $result_ques = '';
                    $result_ques_cls = '';
                    if ($quest_ans->result === 1) {
                        $result_ques = 'Correct';
                        $result_ques_cls = 'txt-success';
                    }
                    
                    if ($quest_ans->result === 0) {
                        $result_ques = 'Incorrect';
                        $result_ques_cls = 'txt-error';    
                    }

                    if ($status !== 4 && $answer_type === 4) {
                        $result_ques = "&nbsp;&nbsp;&nbsp;&nbsp;";
                    }
                    $quest_ind = $ind + 1;

                    if ($answer_type == 6 && $quest_ans->blank_question_type == 2) {
                        $quesion_additional = $quest_ans->question;
                        if ($quest_ans->result === 2) {
                            $result_ques = 'Partially Correct';
                            $result_ques_cls = 'txt-warning';
                        }
                        $question_raw_txt =  (string) $quest_ans->quesion_raw;
                        $ans_array = $quest_ans->answer_array;
                        $options_arr = $quest_ans->options;
                        $search = '{{SELECT_OPTION}}';
                        if (empty($ans_array)) {
                            $option_count = substr_count($question_raw_txt, $search);
                            $replace_count = count($options_arr);
                        } else {
                            $replace_count = count($ans_array);
                        }                                                    
                        
                        $searchlen = strlen($search);
                        $newstring = '';
                        $offset = 0;
                        $key = -1;
                        for($i = 0; $i < $replace_count; $i++) {
                            $answer_txt = '';
                            $is_correct_ans = false;
                            if (empty($ans_array)) {
                                $option_is_correct = $crossImg;
                                $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><img src=".$option_is_correct." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$option_is_correct."/>";
                            } else {
                                $answer_id = $ans_array[$i];
                                $key = array_search($answer_id, array_column($options_arr, 'id'));
                                if ($key > -1 ) {
                                    $is_correct_ans =  $options_arr[$key]->{'is_correct'} == 1 ? true : false;
                                    $option_is_correct = $is_correct_ans === true ? $tickImg : $crossImg;
                                    $answer_txt ="<span class='background-grey f-bold pl-2'>". $options_arr[$key]->{'option'}."</span><img src=".$option_is_correct." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$option_is_correct."/>";
                                } else {
                                    $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><img src=".$crossImg." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$crossImg."/>";
                                }
                            }

                            if (($pos = strpos($question_raw_txt, $search, $offset)) !== false){
                                $newstring .= substr($question_raw_txt, $offset, $pos-$offset) . $answer_txt;
                                $offset = $pos + $searchlen;
                            }
                        }
                        $newstring .= substr($question_raw_txt, $offset);
                        $question_txt = $newstring;
                    } else if ($answer_type == 6 && $quest_ans->blank_question_type == 1) {
                        $quesion_additional = $quest_ans->question;
                        $question_raw_txt =  (string) $quest_ans->quesion_raw;
                        $ans_array = $quest_ans->answer_array;
                        $options_arr = $quest_ans->options;
                        $ans_count = count($ans_array);
                        $search = '{{INPUT_OPTION}}';
                        if (empty($ans_array)) {
                            $replace_count = substr_count($question_raw_txt, $search);
                        } else {
                            $replace_count = count($ans_array);
                        } 
                        $searchlen = strlen($search);
                        $newstring = '';
                        $offset = 0;
                        for($i = 0; $i < $replace_count; $i++) {
                            $answer_txt = '';
                            $is_correct_ans = false;
                            if (empty($ans_array)) {
                                $option_is_correct = $crossImg;
                                $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
                            }  else {
                                $answer_txt_opt = $ans_array[$i];
                                if ($answer_txt_opt !='' ) {
                                    $answer_txt ="<span class='background-grey f-bold pl-2'>". $answer_txt_opt."</span>";
                                } else {
                                    $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><img src=".$crossImg." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$crossImg."/>";
                                }
                            }                                                         

                            if (($pos = strpos($question_raw_txt, $search, $offset)) !== false){
                                $newstring .= substr($question_raw_txt, $offset, $pos-$offset) . $answer_txt;
                                $offset = $pos + $searchlen;
                            }
                        }
                        $newstring .= substr($question_raw_txt, $offset);
                        $question_txt = $newstring;
                    }
                    ?>
                    <div class="px-5 pt-4 avoid-page-break">
                        <div class="q_and_ans">
                            <table class="qus_count_tbl">
                                <tbody>
                                    <tr>
                                    <?php if (!$is_answered ) {
                                    ?>
                                    <td class="f-13 f-bold ">Question <?=$qu_index?>: <span style="color:red;font-weight:bold">Not Answered</span></td>
                                    <?php 
                                        }  else { ?>
                                      <td class="f-13 f-bold ">Question <?=$qu_index?>:</td>
                                    <?php } ?> 
                                        <td class="f-13 f-bold td-align-right">Score: <?=$score_ques?>/<?=$grade_ques?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="qus_ans_container p-2">
                                <?php if ($answer_type == 6 && $quesion_additional) {
                                    ?>
                                        <p class="f-13 mt-0 mb-0">
                                            <?php if ($is_mandatory_check) { /*echo "<span class='txt-error'>*</span>&nbsp;";*/} ?>
                                            <?=$quesion_additional?> <br/><br/>
                                            <span class="f-13 f-bold mb-0">Applicant's Answer:<?php 
                                              if ( $quest_ans->result !=='' && $quest_ans->result !==null) {  
                                                 $src_img = $quest_ans->result == 1 ? $tickImg:$crossImg;
                                              
                                               ?>
                                              <img src="<?=$src_img?>" style='height:15px;margin-top:5px;margin-left:5px;' alt="$src_img"/> 
                                               <?php
                                                 }
                                               ?></span><br/>
                                            <?=$question_txt?>
                                            

                                        </p>
                                    <?php } else if ($answer_type === 4 && $quest_ans->quesion_raw !== '') {
                                            $question_txt = $quest_ans->quesion_raw; 
                                        ?>
                                            <pre class="f-13 mt-0 ml-0 mb-0 p-ws"><?=$question_txt?></pre>
                                        <?php 
                                        }  else { ?>
                                        <p class="f-13 mt-0 mb-0">
                                            <?php if ($is_mandatory_check) { /*echo "<span class='txt-error'>*</span>&nbsp;";*/} ?>
                                            <?=$question_txt?>
                                        </p>
                                    <?php } ?>                                
                                <div class="pt-1 pb-1">
                                    <?php 
                                        if ($answer_type == 4 && $suggest_answer != '' && $suggest_answer != null) {
                                            ?>
                                            <p class="f-13 f-bold mb-0">Suggested Answer:</p>
                                            <pre class="f-13 mt-0 content-justify"><?=$suggest_answer?></pre>
                                            <?php
                                        }
                                        $answer_option = (object) $quest_ans->options;
                                        
                                        $correct_answer = [];
                                        foreach($answer_option as $ans_option) {
                                            $ans_option = (object) $ans_option;
                                            $option_txt = $ans_option->option;
                                            $is_selected = (integer) $ans_option->is_selected;
                                            $is_correct = (integer) $ans_option->is_correct;
                                            $option_selected = $is_selected === 1 ? true : false;
                                            $option_is_correct = $is_correct === 1 ? $tickImg : $crossImg;
                                            if ($is_correct) {
                                                $correct_answer[] = $option_txt;
                                            }
                                            switch($answer_type) {
                                                case 1:
                                                    ?>
                                                    <p class="f-13">
                                                        <input type="checkbox" <?php if($option_selected == true) { echo "checked='true'"; } ?>/>&nbsp; <?=$option_txt?> &nbsp;&nbsp;
                                                        <?php 
                                                        if ($option_selected) {
                                                        ?>
                                                            <img src="<?=$option_is_correct?>" style="height:15px;" alt="<?=$option_is_correct?>"/>    
                                                        <?php
                                                        }
                                                        ?>
                                                    </p> 
                                                    <?php
                                                    break;
                                                case 2:
                                                case 3:
                                                    ?>
                                                    <p class="f-13">
                                                        <input type="radio" <?php if($option_selected == true) { echo "checked='true'"; } ?>/>&nbsp; <?=$option_txt?> &nbsp;&nbsp;
                                                        <?php 
                                                        if ($option_selected) {
                                                        ?>
                                                            <img src="<?=$option_is_correct?>" style="height:15px;" alt="<?=$option_is_correct?>"/>    
                                                        <?php
                                                        }
                                                        ?>
                                                    </p> 
                                                    <?php
                                                    break;
                                                case 4:
                                                    ?>
                                                        <p class="f-13 f-bold mb-0">Applicant's Answer:<?php 
                                                       if ( $quest_ans->result !=='' && $quest_ans->result !== null) {  
                                                          $src_img = $quest_ans->result == 1 ? $tickImg:$crossImg;
                                              
                                                        ?>
                                                     <img src="<?=$src_img?>" style='height:15px;margin-top:5px;margin-left:5px;' alt="$src_img"/> 
                                                     <?php
                                                       }
                                                     ?></p>
                                                      </p>
                                                        <p class="f-13 mt-0"><?=$answer_short?></p>
                                                    <?php
                                                    break;
                                                case 7:
                                                    $option_is_correct = $quest_ans->result == 1 ? $tickImg : $crossImg;
                                                    ?>
                                                    <p class="f-13 f-bold mb-0">Applicant's Answer:
                                                     <?php 
                                                        ?>
                                                            <img src="<?=$option_is_correct?>" style="height:15px;margin-top:5px;margin-left:5px;" alt="<?=$option_is_correct?>"/>    
                                                        <?php
                                                        ?></p>
                                                      </p>
                                                        <p class="f-13 mt-0"><?=$answer_short?></p>
                                                    <?php
                                                    break;
                                                default:
                                                    break;
                                            }
                                        }

                                        $correct_answer_txt = implode(', ', $correct_answer);

                                        if ($answer_type === 4 && count((array)$answer_option) === 0) {
                                           
                                            ?>
                                              <p class="f-13 f-bold mb-0">Applicant's Answer:<?php 
                                              if ( $quest_ans->result !=='' && $quest_ans->result !== null) {  
                                                 $src_img = $quest_ans->result == 1 ? $tickImg:$crossImg;
                                              
                                               ?>
                                              <img src="<?=$src_img?>" style='height:15px;margin-top:5px;margin-left:5px;' alt="$src_img"/> 
                                               <?php
                                                 }
                                               ?></p>
                                               </p> 
                                            
                                                <pre class="f-13 mt-0 content-justify"><?=$answer_short?></pre>
                                            <?php
                                        }
                                    ?>
                                </div>
                                <?php 
                                    if ($answer_type !== 4 && $correct_answer_txt != '') {
                                        ?>
                                        <p class="f-13 mt-0 mb-0 content-justify"><span class="f-bold">Correct Answer: </span><?=$correct_answer_txt?></p>
                                        <?php
                                    } else if ($answer_type === 4 && $correct_answer_txt !='' && $correct_answer_txt != null) {
                                        ?>
                                        <p class="f-13 mt-0 mb-0 content-justify"><span class="f-bold">Correct Answer: </span><?=$correct_answer_txt?></p>
                                        <?php
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                } else {
                    $question_passage = (string) $quest_ans->question_passage;
                    $question_answer_list =(object) $quest_ans->questions;
                    $is_answered = false;
                    if (isset($question_answer_list->{0}->serial_no) && isset($question_answer_list->{0}->serial_no)) {
                        $quest_ind = $question_answer_list->{0}->serial_no;
                        if($question_answer_list->{0}->answer_type ==6 )
                        {
                            if(count($question_answer_list->{0}->{'answer_array'}) >  0)
                            {
                                $is_answered = true;  
                            }
                        }else{
                            if(!empty($question_answer_list->{0}->answer))
                            {
                                $is_answered = true;   
                            }
                        }
                    }
                    $index_incre = false;
                    ?>
                        <div class="px-5 pt-4">
                            <div class="q_and_ans">
                                <table class="qus_count_tbl">
                                    <tbody>
                                        <tr>
                                        <?php if (!$is_answered ) {
                                    ?>
                                    <td class="f-13 f-bold ">Question <?=$quest_ind?>: <span style="color:red;font-weight:bold">Not Answered</span></td>
                                    <?php 
                                        }  else { ?>
                                      <td class="f-13 f-bold ">Question <?=$quest_ind?>:</td>
                                    <?php } ?> 
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="qus_ans_container_wo_border pt-2 pb-2">
                                    <div class="f-13 mt-1 mb-0 content-justify"><?=$question_passage?></div>
                                        <?php
                                            foreach($question_answer_list as $ind_sub => $quest_ans) {
                                                $quest_ans = (object) $quest_ans;
                                                $qu_index = $quest_ans->serial_no ?? '';
                                                $suggest_answer = $quest_ans->suggest_answer ?? '';
                                                $is_mandatory = (integer) $quest_ans->is_mandatory;
                                                $is_mandatory_check = $is_mandatory === 1 ? true : false;
                                                $score_ques = (integer) $quest_ans->score;
                                                $grade_ques =  $quest_ans->grade ?? '0';
                                                $question_txt =  (string) $quest_ans->question;
                                                $answer_type = (integer) $quest_ans->answer_type;
                                                $answer_short = (string) $quest_ans->answer;
                                                $result_ques = '';
                                                $result_ques_cls = '';
                                                if ($quest_ans->result === 1) {
                                                    $result_ques = 'Correct';
                                                    $result_ques_cls = 'txt-success';
                                                }
                                                if ($quest_ans->result === 0) {
                                                    $result_ques = 'Incorrect';
                                                    $result_ques_cls = 'txt-error';    
                                                }
                                                if ($status !== 4 && $answer_type === 4) {
                                                    $result_ques = "&nbsp;&nbsp;&nbsp;&nbsp;";
                                                }
                                                $quest_ind_sub = $ind_sub + 1;
                                                
                                                if ($answer_type == 6 && $quest_ans->blank_question_type == 2) {
                                                    $quesion_additional = $quest_ans->question;
                                                    if ($quest_ans->result === 2) {
                                                        $result_ques = 'Partially Correct';
                                                        $result_ques_cls = 'txt-warning';
                                                    }
                                                    $question_raw_txt =  (string) $quest_ans->quesion_raw;
                                                    $ans_array = $quest_ans->answer_array;
                                                    $options_arr = $quest_ans->options;
                                                    $search = '{{SELECT_OPTION}}';
                                                    if (empty($ans_array)) {
                                                        $replace_count = substr_count($question_raw_txt, $search);
                                                    } else {
                                                        $replace_count = count($ans_array);
                                                    }                                                    
                                                    
                                                    $searchlen = strlen($search);
                                                    $newstring = '';
                                                    $offset = 0;
                                                    $key = -1;
                                                    for($i = 0; $i < $replace_count; $i++) {
                                                        $answer_txt = '';
                                                        $is_correct_ans = false;
                                                        if (empty($ans_array)) {
                                                            $option_is_correct = $crossImg;
                                                            $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><img src=".$option_is_correct." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$option_is_correct."/>";
                                                        } else {
                                                            $answer_id = $ans_array[$i];
                                                            $key = array_search($answer_id, array_column($options_arr, 'id'));
                                                            if ($key > -1 ) {
                                                                $is_correct_ans =  $options_arr[$key]->{'is_correct'} == 1 ? true : false;
                                                                $option_is_correct = $is_correct_ans === true ? $tickImg : $crossImg;
                                                                $answer_txt ="<span class='background-grey f-bold pl-2'>". $options_arr[$key]->{'option'}."</span><img src=".$option_is_correct." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$option_is_correct."/>";
                                                            } else {
                                                                $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><img src=".$crossImg." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$crossImg."/>";
                                                            }
                                                        }

                                                        if (($pos = strpos($question_raw_txt, $search, $offset)) !== false){
                                                            $newstring .= substr($question_raw_txt, $offset, $pos-$offset) . $answer_txt;
                                                            $offset = $pos + $searchlen;
                                                        }
                                                    }
                                                    $newstring .= substr($question_raw_txt, $offset);
                                                    $question_txt = $newstring;
                                                } else if ($answer_type == 6 && $quest_ans->blank_question_type == 1) {
                                                    $quesion_additional = $quest_ans->question;
                                                    $question_raw_txt =  (string) $quest_ans->quesion_raw;
                                                    $ans_array = $quest_ans->answer_array;
                                                    $options_arr = $quest_ans->options;
                                                    $ans_count = count($ans_array);
                                                    $search = '{{INPUT_OPTION}}';
                                                    if (empty($ans_array)) {
                                                        $option_count = substr_count($question_raw_txt, $search);
                                                        $replace_count = count($options_arr);
                                                    } else {
                                                        $replace_count = count($ans_array);
                                                    } 
                                                    $searchlen = strlen($search);
                                                    $newstring = '';
                                                    $offset = 0;
                                                    for($i = 0; $i < $replace_count; $i++) {
                                                        $answer_txt = '';
                                                        $is_correct_ans = false;
                                                        if (empty($ans_array)) {
                                                            $option_is_correct = $crossImg;
                                                            $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
                                                        }  else {
                                                            $answer_txt_opt = $ans_array[$i];
                                                            if ($answer_txt_opt !='' ) {
                                                                $answer_txt ="<span class='background-grey f-bold pl-2'>". $answer_txt_opt."</span>";
                                                            } else {
                                                                $answer_txt ="<span class='background-grey f-bold pl-2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><img src=".$crossImg." style='height:10px;margin-top:5px;margin-left:5px;' alt=".$crossImg."/>";
                                                            }
                                                        }                                                         

                                                        if (($pos = strpos($question_raw_txt, $search, $offset)) !== false){
                                                            $newstring .= substr($question_raw_txt, $offset, $pos-$offset) . $answer_txt;
                                                            $offset = $pos + $searchlen;
                                                        }
                                                    }
                                                    $newstring .= substr($question_raw_txt, $offset);
                                                    $question_txt = $newstring;
                                                }
                                                ?>
                                                <div class="pt-4 avoid-page-break">
                                                    <div class="q_and_ans">
                                                        <table class="qus_count_tbl">
                                                            <tbody>
                                                                <tr>
                                                                    <?php 
                                                                    if ($quest_ind_sub !== 1) {
                                                                        ?>
                                                                        <td class="f-13 f-bold ">Question <?=$qu_index?>:</td>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                    
                                                                    <td class="f-13 f-bold td-align-right">Score: <?=$score_ques?>/<?=$grade_ques?></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <div class="qus_ans_container pt-2 pb-2 pl-2 pr-0">
                                                            <?php if ($answer_type == 6 && $quesion_additional) { ?>
                                                                <p class="f-13 mt-0 mb-0">
                                                                    <?php if ($is_mandatory_check) { /* echo "<span class='txt-error'>*</span>&nbsp;"; */} ?>
                                                                    <?=$quesion_additional?> <br/><br/>
                                                                    <span class="f-13 f-bold mb-0">Applicant's Answer:<?php 
                                                                      if ( $quest_ans->result !=='' && $quest_ans->result !==null) {  
                                                                        $src_img = $quest_ans->result == 1 ? $tickImg:$crossImg;
                                                                        ?>
                                                                     <img src="<?=$src_img?>" style='height:15px;margin-top:5px;margin-left:5px;' alt="$src_img"/> 
                                                                     <?php
                                                                     }
                                                                     ?></span><br/>
                                                                    <?=$question_txt?>
                                                                </p>
                                                            <?php }  else if ($answer_type === 4 && $quest_ans->quesion_raw !== '') {
                                                                        $question_txt = $quest_ans->quesion_raw; 
                                                                    ?>
                                                                        <pre class="f-13 mt-0 mb-0 p-ws"><?=$question_txt?></pre>
                                                                    <?php 
                                                                    } else { ?>
                                                                <p class="f-13 mt-0 mb-0">
                                                                    <?php if ($is_mandatory_check) { /* echo "<span class='txt-error'>*</span>&nbsp;";*/} ?>
                                                                    <?=$question_txt?>
                                                                </p>
                                                            <?php } ?>
                                                            <div class="pt-1 pb-1">
                                                                <?php 
                                                                    if ($answer_type == 4 && $suggest_answer != '' && $suggest_answer != null) {
                                                                        ?>
                                                                        <p class="f-13 f-bold mb-0">Suggested Answer:</p>
                                                                        <pre class="f-13 mt-1 content-justify"><?=$suggest_answer?></pre>
                                                                        <?php
                                                                    }
                                                                    $answer_option = (object) $quest_ans->options;
                                                                    
                                                                    $correct_answer = [];
                                                                    foreach($answer_option as $ans_option) {
                                                                        $ans_option = (object) $ans_option;
                                                                        $option_txt = $ans_option->option;
                                                                        $is_selected = (integer) $ans_option->is_selected;
                                                                        $is_correct = (integer) $ans_option->is_correct;
                                                                        $option_selected = $is_selected === 1 ? true : false;
                                                                        $option_is_correct = $is_correct === 1 ? $tickImg : $crossImg;
                                                                        if ($is_correct) {
                                                                            $correct_answer[] = $option_txt;
                                                                        }
                                                                        switch($answer_type) {
                                                                            case 1:
                                                                                ?>
                                                                                <p class="f-13">
                                                                                    <input type="checkbox" <?php if($option_selected == true) { echo "checked='true'"; } ?>/>&nbsp; <?=$option_txt?> &nbsp;&nbsp;
                                                                                    <?php 
                                                                                    if ($option_selected) {
                                                                                    ?>
                                                                                        <img src="<?=$option_is_correct?>" style="height:15px;" alt="<?=$option_is_correct?>"/>    
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </p> 
                                                                                <?php
                                                                                break;
                                                                            case 2:
                                                                            case 3:
                                                                                ?>
                                                                                <p class="f-13">
                                                                                    <input type="radio" <?php if($option_selected == true) { echo "checked='true'"; } ?>/>&nbsp; <?=$option_txt?> &nbsp;&nbsp;
                                                                                    <?php 
                                                                                    if ($option_selected) {
                                                                                    ?>
                                                                                        <img src="<?=$option_is_correct?>" style="height:15px;" alt="<?=$option_is_correct?>"/>    
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </p> 
                                                                                <?php
                                                                                break;
                                                                            case 4:
                                                                                ?>
                                                                                    <span class="f-13 f-bold mb-0">Applicant's Answer:</span>
                                                                             <?php 
                                                                         if ( $quest_ans->result !=='' && $quest_ans->result !== null) {  
                                                                            $src_img = $quest_ans->result == 1 ? $tickImg:$crossImg;
                                                                         
                                                                          ?>
                                                                         <img src="<?=$src_img?>" style='height:15px;margin-top:5px;margin-left:5px;' alt="$src_img"/> 
                                                                          <?php
                                                                            }
                                                                          ?></p>
                                                                                    <span class="f-13 mt-0"><?=$answer_short?></span>
                                                                                <?php
                                                                                break;
                                                                            
                                                                            case 7:
                                                                                $option_is_correct = $quest_ans->result == 1 ? $tickImg : $crossImg;
                                                                                ?>
                                                                                <p class="f-13 f-bold mb-0">Applicant's Answer:
                                                                                    <?php 
                                                                                    ?>
                                                                                        <img src="<?=$option_is_correct?>" style="height:15px;margin-top:5px;margin-left:5px;" alt="<?=$option_is_correct?>"/>    
                                                                                    <?php
                                                                                    ?></p>
                                                                                    </p>
                                                                                    <p class="f-13 mt-0"><?=$answer_short?></p>
                                                                                <?php
                                                                                break;
                                                                            default:
                                                                                break;
                                                                        }
                                                                    }
                            
                                                                    $correct_answer_txt = implode(', ', $correct_answer);
                            
                                                                    if ($answer_type === 4 && count((array)$answer_option) === 0) {
                                                                        ?>
                                                                            <p class="f-13 f-bold mb-0">Applicant's Answer:<?php 
                                                                         if ( $quest_ans->result !=='' && $quest_ans->result !== null) {  
                                                                            $src_img = $quest_ans->result == 1 ? $tickImg:$crossImg;
                                                                         
                                                                          ?>
                                                                         <img src="<?=$src_img?>" style='height:15px;margin-top:5px;margin-left:5px;' alt="$src_img"/> 
                                                                          <?php
                                                                            }
                                                                          ?></p>
                                                                            <pre class="f-13 mt-0 content-justify"><?=$answer_short?></pre>
                                                                        <?php
                                                                    }
                                                                ?>
                                                            </div>
                                                            <?php 
                                                                if ($answer_type !== 4) {
                                                                    ?>
                                                                    <p class="f-13 mt-0 mb-0 content-justify"><span class="f-bold">Correct Answer: </span><?=$correct_answer_txt?></p>
                                                                    <?php
                                                                } else if ($answer_type === 4 && $correct_answer_txt !='' && $correct_answer_txt != null) {
                                                                    ?>
                                                                    <p class="f-13 mt-0 mb-0 content-justify"><span class="f-bold">Correct Answer: </span><?=$correct_answer_txt?></p>
                                                                    <?php
                                                                }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            // $qu_index++;
                                            }
                                        ?>
                                </div>
                            </div>
                        </div>
                    <?php                   
                }
                if ($index_incre) {
                    // $qu_index++;    
                } 
            }
        }
    ?>
<?php }