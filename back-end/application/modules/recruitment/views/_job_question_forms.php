<?php

/**
 * @var \CI_Controller $this
 * @var array $form
 * @var \stdClass[] $questions
 */

/**
 * Convert associative array to html attribute string. 
 * 
 * If value is `false` or type of `array`, it will be ignored.
 * 
 * ```php
 * 
 * $array = [
 *    'type' => 'input', 
 *    'maxlength' => 50, 
 *    'required' => false, 
 *    'items' => [],
 * ];
 * 
 * $res = array_to_html_attributes($array);
 * // Outputs 'type="input" maxlength="50"'
 * 
 * ```
 * @param array<string, mixed> $array 
 * @return string 
 */
function array_to_html_attributes(array $array)
{
    $attrs = "";
    foreach ($array as $attr_key => $attr_value) {
        if (!is_array($attr_value) && $attr_value !== false) {
            $attrs .= $attr_key . '="' . htmlspecialchars($attr_value) . '" ';
        }
    }
    return $attrs;
}


/**
 * Convert associative array to html data attributes string prefixed with `data-`. 
 * 
 * If value is `false` or type of `array`, it will be ignored.
 * 
 * @param array $array 
 * @return string 
 */
function array_to_html_data_attributes(array $array)
{
    $data_attrs = [];
    foreach ($array as $attr_key => $attr_value) {
        $data_attrs["data-" . $attr_key] = $attr_value;
    }
    return array_to_html_attributes($data_attrs);
}

/**
 * Displays group of checkboxes or radio buttons depending on `$question['question_type']`
 * @param array $question 
 * @return string 
 */
function render_choices(array $question)
{
    $namePrefix = "forms[{$question['form_id']}]";
    $idPrefix = "forms-{$question['form_id']}";

    $choices = $question['answers'];
    $html = [];
    foreach ($choices as $choice) {
        $htmlChoiceId = "{$idPrefix}-{$question['id']}-{$choice['answer_id']}";

        $label_attrs = array_to_html_attributes([
            'for' => $htmlChoiceId,
            'style' => "cursor: pointer; display: inline-block; margin-right: 30px",
        ]);

        $input_attrs = array_to_html_attributes([
            'type' => $question['question_type'] == '1' ? 'checkbox' : 'radio',
            'name' => $question['question_type'] == '1' ? "{$namePrefix}[{$question['id']}][answer_id][]" : "{$namePrefix}[{$question['id']}][answer_id]",
            'id' => $htmlChoiceId,
            "value" => $choice['answer_id'],
            'required' => !!$question['is_required'] ? 'required' : false,
            "data-container" => "div[data-id=" . $question['id'] . "] > div:first-child > label",
        ]);

        $html[] = "<label {$label_attrs}><input {$input_attrs}> {$choice['value']}</label>";
    }

    return implode('', $html);
}


/**
 * Display a textarea
 * 
 * @param array $question 
 * @return string 
 */
function render_short_answer_question(array $question)
{
    $namePrefix = "forms[{$question['form_id']}]";
    $idPrefix = "forms-{$question['form_id']}";

    $textarea_attrs = array_to_html_attributes([
        "class" => "form-control d-block",
        'type' => $question['question_type'],
        'name' => "{$namePrefix}[{$question['id']}][answer_text]",
        'id' => "{$idPrefix}-{$question['id']}",
        "rows" => 3,
        'required' => !!$question['is_required'] ? 'required' : false
    ]);
    
    $html = "<textarea {$textarea_attrs}></textarea>";

    return $html;
}


?>
<div class="row">
    <div class="col-md-12 bt-1 bt-color pt-4 mt-4"></div>
    <div class="col-md-12">
        <div class="row">
            <?php foreach ($questions as $q) : 
                $question = (array) $q;
                $question = array_merge($question, [
                    'form_id' => $form['id'],
                ]);

                $namePrefix = "forms[{$question['form_id']}]";
                $idPrefix = "forms-{$question['form_id']}";
            ?>
                <div class="col-lg-6 col-md-12" <?= !!$question['is_required'] ? 'required ' : ''?><?= array_to_html_data_attributes($question) ?>>
                    <div>
                        <label for="<?= "{$idPrefix}-{$question['id']}" ?>">
                            <?= $question['question'] ?> 
                            <?php if (!!$question['is_required']) : ?>
                                <b class="text-danger">*</b>
                            <?php endif ?>
                        </label>
                    </div>
                    <div>
                        <?php 
                        switch (intval($question['question_type'])) :
                            case 4: 
                            ?>
                                <?= render_short_answer_question($question) ?>
                            <?php 
                            break; 
                            case 3:
                            case 2:
                            case 1:
                            ?>
                                <?= render_choices($question) ?>
                            <?php 
                            break; 
                        endswitch ?>
                    </div>
                    <br>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>