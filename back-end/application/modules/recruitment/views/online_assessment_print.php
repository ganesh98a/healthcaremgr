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

    $statusData = [ 1 => 'Sent', 2 => 'In-progress', 3 => 'Submitted', 4 => 'Completed', 5 => 'Link Expired', 6 => 'Error'];
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
            foreach($question_answer as $ind => $quest_ans) {
                $is_mandatory = (integer) $quest_ans->is_mandatory;
                $is_mandatory_check = $is_mandatory === 1 ? true : false;
                $score_ques = (integer) $quest_ans->score;
                $grade_ques =  (integer) $quest_ans->grade;
                $question_txt =  (string) $quest_ans->question;
                $answer_type = (integer) $quest_ans->answer_type;
                $answer_short = (string) $quest_ans->answer;
                $result_ques = 'Incorrect';
                $result_ques_cls = 'txt-error';
                $result = (integer) $quest_ans->result;
                if ($quest_ans->result === 1) {
                    $result_ques = 'Correct';
                    $result_ques_cls = 'txt-success';
                }
                if ($status !== 4 && $answer_type === 4) {
                    $result_ques = "&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                $quest_ind = $ind + 1;
                ?>
                <div class="px-5 pt-4 avoid-page-break">
                    <div class="q_and_ans">
                        <table class="qus_count_tbl">
                            <tbody>
                                <tr>
                                    <td class="f-13 f-bold ">Question <?=$quest_ind?>:</td>
                                    <td class="f-13 f-bold td-align-right">Result: <span class="<?=$result_ques_cls?>"><?=$result_ques?>  </span>&nbsp;&nbsp; Score: <?=$score_ques?>/<?=$grade_ques?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="qus_ans_container p-2">
                            <p class="f-13 mt-0 mb-0"><?php if ($is_mandatory_check) { echo "<span class='txt-error'>*</span>&nbsp;";} ?><?=$question_txt?></p>
                            <div class="pt-1 pb-1">
                                <?php 
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
                                                    <p class="f-13 f-bold mb-0">Applicant's Answer:</p>
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
                                            <p class="f-13 f-bold mb-0">Applicant's Answer:</p>
                                            <p class="f-13 mt-0"></p>
                                        <?php
                                    }
                                ?>
                            </div>
                            <?php 
                                if ($answer_type !== 4) {
                                    ?>
                                    <p class="f-13 mt-0 mb-0"><span class="f-bold">Correct Answer: </span><?=$correct_answer_txt?></p>
                                    <?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
            <?php
            }
        }
    ?>
<?php }