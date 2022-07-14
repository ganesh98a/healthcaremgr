<?php 
if(getenv('IS_SITE_MAINTENANCE') == 'yes') :
?>

<!doctype html>
<html>
  <head>
    <title>Site Maintenance</title>
    <meta charset="utf-8"/>
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      body { text-align: center; padding: 20px; font: 20px Helvetica, sans-serif; color: #333; }
      @media (min-width: 768px){
        body{ padding-top: 150px; }
      }
      h1 { font-size: 50px; }
      article { display: block; text-align: left; max-width: 650px; margin: 0 auto; }
      a { color: #dc8100; text-decoration: none; }
      a:hover { color: #333; text-decoration: none; }
    </style>
  </head>
  <body>
    <article>
        <img src="https://www.oncall.com.au/wp-content/uploads/2021/08/oncall-logo-colour-rgb-e1628821005154.png" width="200px" align="center">
        <h1>We&rsquo;ll be back soon!</h1>
        <div>
            <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. We&rsquo;ll be back online shortly!</p>
            <p>&mdash; ONCALL GROUP AUSTRALIA PTY LTD</p>
        </div>
    </article>
  </body>
</html>

<?php
    exit();
    endif;
?>
<?php

/**
 * @var \CI_Controller $this
 * @var array[] $form_data
 */


$assets_hostname = base_url() ?: '';
$assets_hostname = rtrim($assets_hostname, '/'); // remove trailing slash

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Recruitment Job' ?></title>
    <link href="<?= $assets_hostname ?>/assets/preview_job/css/jquery.toast.css" rel="stylesheet" />
    <link href="<?= $assets_hostname ?>/assets/preview_job/css/style.css" rel="stylesheet" />
    <link href="<?= $assets_hostname ?>/assets/preview_job/css/bootstrap.css" rel="stylesheet" />
    <link href="<?= $assets_hostname ?>/assets/preview_job/css/responsive_style.css" rel="stylesheet" />
    <link href="<?= $assets_hostname ?>/assets/preview_job/css/icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">

    <style>
        label.required:after {
            content: ' *';
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
        }
        .required:after {
            content: ' *';
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
        }

        form div[data-question] > div:first-child > label {
            position: relative;
        }

        form div[data-question] > div:first-child > label > .tooltip {
            top: 8px !important;
            left: auto !important;
            text-align: center;
            transform: translateY(-100%);
            width: 100%;
        }

        form div[data-question] > div:first-child > label > .tooltip > .tooltip-inner {
            display: inline-block;
        }
        .address_check {
            margin-top: 38px;
        }
        .address_check input{
            width: 20px;
            height: 20px;
        }
        .address_check label{
            padding-left: 10px;
        }
        .padding_right_ {
            padding-right: 0px;
            text-align: center;
        }

        @media only screen and (max-device-width: 960px) {

            /* styles for browsers larger than 960px; */
            .padding_right_ {
                padding-right: 15px;
            }
        }

    </style>
</head>

<body>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NK3BJ8F');</script>
    <!-- End Google Tag Manager -->

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-44576017-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-44576017-1');        
    </script>
    <script>
        window.onload = function() {
            //Page will getting refersh after success, to avoid one more unwanted trigger based on the local storage
            if(localStorage.getItem("isSubmit")) {
                localStorage.removeItem("isSubmit");
            } else {  
                triggerSurvey('open');                
            }
        };
    </script>
    <div>
        <div class="main_page_template_hcm_app">
            <div class="left_side"><img class="w-100" src="<?= $assets_hostname ?>/assets/preview_job/img/oga-logo-white.svg" /></div>
            <div class="right_side">
                <div>
                    <div class="main_logo">
                        <!-- <img src="<?= $assets_hostname ?>/assets/preview_job/img/ocs_logo.svg" /> -->
                </div>
                    <div>
                        <div class="pt-5">
                            <h2><strong><?php echo $job_data->position ?></strong></h2>
                            <p class="mb-0"><?php echo $job_data->description ?? 'N/A' ?></p>
                        </div>
                        <div class="pt-5">
                            <?php if($job_data->is_cat_publish || $job_data->is_subcat_publish || $job_data->is_emptype_publish || $job_data->is_salary_publish || $job_data->address || $job_data->phone || $job_data->email || $job_data->website){ ?>
                            <h5><strong>Job Details:</strong></h5>
                            <?php } ?>

                            <?php if($job_data->is_cat_publish){ ?>
                            <p class="mb-0"><strong>Job Category:</strong> <?php echo $job_data->job_category ?? 'N/A' ?></p>
                            <?php } ?>

                            <?php if($job_data->is_subcat_publish){ ?>
                            <p class="mb-0"><strong>Job Sub-Category:</strong> <?php echo $job_data->job_sub_category ?? 'N/A' ?></p>
                            <?php } ?>

                            <?php if($job_data->is_emptype_publish){ ?>
                            <p class="mb-0"><strong>Employment Type:</strong> <?php echo $job_data->employment_type ?? 'N/A' ?></p>
                            <?php } ?>

                            <?php if($job_data->is_salary_publish){ ?>
                            <p class="mb-0"><strong>Salary Range:</strong> <?php echo $job_data->salary_range ?></p>
                            <?php } ?>

                            <?php if($job_data->address){ ?>
                            <p class="mb-0"><strong>Location:</strong> <?php echo $job_data->address ?></p>
                            <?php } ?>
                            <?php if($job_data->phone){ ?>
                            <p class="mb-0"><strong>Phone:</strong> <?php echo $job_data->phone ?></p>
                            <?php } ?>
                            <?php if($job_data->email){ ?>
                            <p class="mb-0"><strong>Email:</strong> <?php echo $job_data->email ?></p>
                            <?php } ?>
                            <?php if($job_data->website){ ?>
                            <p class="mb-0"><strong>Website:</strong> <?php echo $job_data->website ?></p>
                            <?php } ?>
                        </div>

                        <?php
                        if (!empty($job_data->docs)) {
                            ?>
                            <div class="pt-5">
                                <h5><strong>Required Documents:</strong></h5>
                                <div class="list_req_doc">
                                    <?php
                                    foreach ($job_data->docs as $value) {
                                        ?>
                                        <div><span><?php echo $value->title ?></span> <span class="btn_small_1"><?php echo isset($value->is_required) && $value->is_required == 1 ? 'Required' : 'Optional' ?></span></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if($job_data->job_status == 3){ ?>
                        <div class="pt-5 pb-5">
                            <button type="button" class="btn btn-bg-color" data-toggle="modal" data-target="#jpbAppliedModalCenter" onclick = 'triggerSurvey("apply")'>Apply</button>
                            <?php if (isset($seek_settings) && empty($seek_settings) == false) {
                                $client_id = $seek_settings['client_id'];
                                $redirect_uri = $seek_settings['redirect_uri'];
                                $state = $seek_settings['state'];
                                $advertiser_id = $seek_settings['advertiser_id'];
                            ?>
                            <iframe
                              frameborder="0"
                              width="180px"
                              height="37px"
                              style="position: relative; top: 14px; display:none"
                              class="apply_cus_seek"
                              src="https://apply-with-seek-button.seek.com.au?client_id=<?php echo $client_id; ?>&redirect_uri=<?php echo $redirect_uri; ?>&advertiser_id=<?php echo $advertiser_id; ?>&state=<?php echo $state; ?>"
                            ></iframe><input type="hidden" name="cus_param_seek" id="cus_param_seek" value="<?php echo $seek_settings['redirected_by']; ?>"><?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="left_side footer_left"></div>
            <div class="right_side footer_right">
                <div class="log_footer">
                    <!-- <div class="img_footer"><img src="<?= $assets_hostname ?>/assets/preview_job/img/ocs_logo.svg" /></div>
                    <div class="website_text">hcm.com.au</div> -->
                </div>
                <div class="social_icons">
                    <!-- <div><a href="#"><img src="<?= $assets_hostname ?>/assets/preview_job/img/fb.png" /></a></div>
                    <div><a href="#"><img src="<?= $assets_hostname ?>/assets/preview_job/img/twitter.png" /></a></div>
                    <div><a href="#"><img src="<?= $assets_hostname ?>/assets/preview_job/img/instagram.png" /></a></div> -->

                    <div><a href="<?php echo ON_CALL_FB_LINK;?>" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2 10h-2v2h2v6h3v-6h1.82l.18-2h-2v-.833c0-.478.096-.667.558-.667h1.442v-2.5h-2.404c-1.798 0-2.596.792-2.596 2.308v1.692z"/></svg></a></div>
                <div><a href="<?php echo ON_CALL_LINKEDIN_LINK;?>" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-2 8c0 .557-.447 1.008-1 1.008s-1-.45-1-1.008c0-.557.447-1.008 1-1.008s1 .452 1 1.008zm0 2h-2v6h2v-6zm3 0h-2v6h2v-2.861c0-1.722 2.002-1.881 2.002 0v2.861h1.998v-3.359c0-3.284-3.128-3.164-4-1.548v-1.093z"/></svg></a></div>


                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade  modal_lg_my" id="jpbAppliedModalCenter" tabindex="-1" role="dialog" aria-labelledby="jpbAppliedModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">


            <div class="modal-content">
                <div class="modal-header m_header_1">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add Application Info</h5>
                    <button type="button" class="close" aria-label="Close" onclick="clear_form_inputs()">
                        <span aria-hidden="true"><img src="<?= $assets_hostname ?>/assets/preview_job/img/close.svg" /></span>
                    </button>
                </div>
                <form autocomplete="off" id="job_applied" action="<?php echo $form_action?>">
                    <input type="hidden" name="seek_time_out_error" id="seek_time_out_error" value="<?php if (isset($applicant_info) && isset($applicant_info['timeout_error'])) echo $applicant_info['timeout_error']; ?>">
                    <input type="hidden" name="seek_complete_url" id="seek_complete_url" value="<?php if (isset($applicant_info) && isset($applicant_info['complete_url'])) echo $applicant_info['complete_url']; ?>">

                    <input type="hidden" name="campaign_source" id="campaign_source" value="<?php echo (!empty($_GET['campaign_source'])) ? $_GET['campaign_source'] : NULL; ?>">
                    <input type="hidden" name="ad_name" id="ad_name" value="<?php echo (!empty($_GET['adname'])) ? $_GET['adname'] : NULL; ?>">
                    <input type="hidden" name="campaign_name" id="campaign_name" value="<?php echo (!empty($_GET['campaign_name'])) ? $_GET['campaign_name'] : NULL; ?>">
                    <div class="modal-body m_body_1">
                        <h5 class="mt-3">Applicants Information:</h5>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1" title="required">First Name:<sup style="font-size: 16px;color:red;font-weight: bold;color: #dc3545;">*</sup></label>
                                    <input type="text" class="form-control input_box"
                                        name="firstname"
                                        placeholder="First Name"
                                        data-rule-valid_name="true"
                                        data-msg-valid_name="Please enter a valid first name"
                                        value="<?php if (isset($applicant_info) && isset($applicant_info['firstName'])) echo $applicant_info['firstName']; ?>"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1" >Middle Name:</label>
                                    <input type="text" class="form-control input_box"
                                        name="middlename"
                                        placeholder="Middle Name"
                                        data-rule-valid_name="true"
                                        data-msg-valid_name="Please enter a valid middle name"
                                        value="<?php if (isset($applicant_info) && isset($applicant_info['middleName'])) echo $applicant_info['middleName']; ?>"
                                        
                                    >
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1"  title="required">Last Name:<sup style="font-size: 16px;color:red;font-weight: bold;color: #dc3545;">*</sup></label>
                                    <input type="text" class="form-control input_box"
                                        name="lastname"
                                        placeholder="Last Name"
                                        data-rule-valid_name="true"
                                        data-msg-valid_name="Please enter a valid last name"
                                        required
                                        value="<?php if (isset($applicant_info) && isset($applicant_info['lastName'])) echo $applicant_info['lastName']; ?>"
                                    >
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1" >Previous Name:</label>
                                    <input type="text" class="form-control input_box"
                                        name="previousname"
                                        placeholder="Previous Name"
                                        data-rule-valid_name="true"
                                        data-msg-valid_name="Please enter a valid previous name"
                                        value="<?php if (isset($applicant_info) && isset($applicant_info['previousName'])) echo $applicant_info['previousName']; ?>"
                                        
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                           <div class="col-lg-3">
                                <div class="form-group">  
                                <label for="exampleFormControlInput1" title="required">Date of birth:<sup style="font-size: 16px;color:red;font-weight: bold;color: #dc3545;">*</sup></label>
                                <input type="text"  class="form-control input_box" name="dob"  placeholder="DD/MM/YYYY" required
                                 value="<?php if (isset($applicant_info) && isset($applicant_info['dob'])) echo $applicant_info['dob']; ?>" id="dob_datepicker">
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-3">Contact Information:</h5>
                        <div class="row">
                        <div class="col-lg-1 padding_right_">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Phone:<sup style="font-size: 16px;color:red;font-weight: bold;color: #dc3545;">*</sup></label>
                                    <input type="text" style="text-align: center;" class="form-control input_box" name="" id="" placeholder="+61" value="+61" readOnly="true">
                                </div>
                            </div>
                            <div class="col-lg-3 mt-8">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">
                                    </label>
                                    <input type="text" class="form-control input_box" style="margin-top: 8px;" name="phone" id="mobile_num_validation" placeholder="04XXXXXXXX" required data-rule-phonenumber maxLength='10' phoneMinLength='10' value="<?php if (isset($applicant_info) && isset($applicant_info['phoneNumber'])) echo $applicant_info['phoneNumber']; ?>">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Email:<sup style="font-size: 16px;color:red;font-weight: bold;color: #dc3545;">*</sup></label>
                                    <input type="email" class="form-control input_box" name="email" id="email_num_validation" placeholder="Email" value="<?php if (isset($applicant_info) && isset($applicant_info['emailAddress'])) echo $applicant_info['emailAddress']; ?>" required >

                                </div>
                            </div>                            
                        </div>
                        <div class="row">                            
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Apartment/Unit number:</label>
                                    <input type="text" class="form-control input_box" name="unit_number" id="unit_number" placeholder="Apartment/Unit number" value="<?php if (isset($applicant_info) && isset($applicant_info['unit_number'])) echo $applicant_info['unit_number']; ?>">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Primary Address:</label>                         
                                    <input type="text" class="form-control input_box" name="address" value="<?php if (isset($applicant_info) && isset($applicant_info['address'])) echo $applicant_info['address']; ?>" id="pac-input" onChange="get_address_from_google()"  placeholder="Primary Address">
                                </div>
                            </div>
                            <div class="col-lg-4 address_check ">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_manual_address" id="is_manual_address" onClick="changeManualAddress()"
                                    value="<?php if (isset($applicant_info) && isset($applicant_info['is_manual_address'])) echo $applicant_info['is_manual_address']; ?>">
                                    <label class="form-check-label" for="is_manual_address">Address did not show up</label>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Address (Manual Entry):</label>                         
                                    <input type="text" class="form-control input_box"                                     
                                    name="manual_address"
                                    id="manual_address"                                     
                                    placeholder="Manual Address"
                                    value="<?php if (isset($applicant_info) && isset($applicant_info['manual_address'])) echo $applicant_info['manual_address']; ?>"
                                    disabled
                                    >
                                </div>
                            </div>
                        </div>
                        <h5 class="mt-3">Referral Information:</h5>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Referred By:</label>
                                    <input type="text" class="form-control input_box" name="referred_by" id="referred_by" placeholder="Referred By" 
                                    value="<?php if (isset($applicant_info) && isset($applicant_info['referredBy'])) echo $applicant_info['referredBy']; ?>"
                                    data-rule-valid_name="true"
                                    data-msg-valid_name="Please enter a valid referred by"
                                    >
                                </div>
                            </div>
                            <div class="col-lg-1 padding_right_">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Phone:</label>
                                    <input type="text" style="text-align: center;" class="form-control input_box" name="" id="" placeholder="+61" value="+61" readOnly="true">
                                </div>
                            </div>
                            <div class="col-lg-3">                                
                                <div class="form-group">
                                    <label for="exampleFormControlInput1"></label>
                                    <input type="text" style="margin-top: 8px;" class="form-control input_box" name="referred_phone" id="referred_phone" placeholder="04XXXXXXXX"                                     
                                    data-rule-phonenumber maxLength='10' phoneMinlength='10' value="<?php if (isset($applicant_info) && isset($applicant_info['referredPhoneNumber'])) echo $applicant_info['referredPhoneNumber']; ?>">
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">Email:</label>
                                    <input type="email" class="form-control input_box" name="referred_email" id="referred_email" placeholder="Email" value="<?php if (isset($applicant_info) && isset($applicant_info['referredEmailAddress'])) echo $applicant_info['referredEmailAddress']; ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                       <!--  <div class="col-lg-3">
                            <div class="form-group">
                                <label for="exampleFormControlInput1">State:</label>
                                <div class="">
                                    <select class="select" id="state_select"  data-placeholder="Choose a country..." required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="exampleFormControlInput1">Suburb:</label>
                                <div class="">
                                    <select class="wide chzn-choices" id="suburb_chosen_search" required>
                                        <option data-display="Select">Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label for="exampleFormControlInput1">PostCode:</label>
                                <input type="text" class="form-control input_box" id="postal" name="postcode" placeholder="PostCode" required>
                            </div>
                        </div> -->
                    </div>

                    <?php

                    if (!empty($job_data->docs)) { ?>
                        <div>
                            <h5 class="mb-4 bt-1 bt-color pt-4 mt-4">Upload Documents:</h5>
                        </div>

                        <div class="row">

                          <?php
                          foreach ($job_data->docs as $value) {
                            $name = str_replace(' ','_',$value->title).'_'.$value->docs_p_id;
                            ?>
                            <div class="col-lg-4">
                                <label for="upload_<?= $name ?>" class="<?= ($value->is_required ?? false) ? 'required' : '' ?>" title="<?= $value->title . ($value->is_required ? ' (required)' : ''); ?>">
                                    <?= $value->title ?>
                                </label>
                                <br>
                                <?php
                                        // The "accept" attr prevent the file dialog from displaying incorrect file formats
                                        // The error validation will appear not because of "accept", but because of "data-rule-extension"
                                ?>
                                <span class="d-inline-block">
                                    <input type="file" name="<?php echo $name?>" id="upload_<?php echo $name?>" data-target="<?= 'input#hdn_' . $name ?>"
                                        class="<?= (isset($value->is_required) && $value->title == 'Resume') && isset($resume['link']) ? 'resume_input_required' : '' ?>"
                                        accept="<?= implode(', ', [ '.doc',  '.docx',  '.pdf', 'application/msword',  'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf',  'image/jpg', 'image/jpeg', 'image/png'])  ?>"
                                        data-rule-extension="<?= implode('|', ['pdf','doc','docx','png','jpeg','jpg'])  ?>"
                                        data-msg-extension="Can only accept pdf, docx, doc, png, jpeg, jpg"
                                        <?= (isset($value->is_required) && $value->is_required && $value->title != 'Resume') == 1 ? 'required' : '' ?>
                                        <?= ($value->title == 'Resume' && !isset($resume['link']) && !empty($value->is_required)) == 1 ? 'required' : '' ?>
                                    >
                                </span>
                                <br>
                                <?php if ($value->title == 'Resume' && isset($resume['link'])) { ?>
                                    <input type="hidden" name="seek_resume[active]" id="seek_resume_active" value="1">
                                    <input type="hidden" name="seek_resume[name]" value="<?php echo $name?>">
                                    <input type="hidden" name="seek_resume[file_name]" value="<?=$resume['file_name']?>">
                                    <input type="hidden" name="seek_resume[temp_path]" value="<?=$resume['temp_path']?>">
                                    <input type="hidden" name="seek_resume[view_path]" value="<?=$resume['view_path']?>">
                                    <input type="hidden" name="seek_resume[full_path]" value="<?=$resume['full_path']?>">
                                    <a href="<?php echo $resume['view_path']; ?>" target="_blank" class="resume_link"><?php echo $resume['file_name']; ?></a>
                                <?php } ?>
                                <br>
                            </div>

                            <!--
                            <div class="col-lg-4">
                                <div style="display: grid;">
                                 <input class="hdn_box" type="text" name="hdn_<?php echo $name?>" value="" id="hdn_<?php echo $name?>" <?php echo isset($value->is_required) && $value->is_required == 1 ? 'required' : '' ?> >
                                <div class="form-group upload_req_doc">
                                    <div class="left_req_doc"><?php echo $value->title ?></div>
                                    <div class="right_req_doc">

                                        <label class="upload_bro">
                                            <input class="invisible but_upload" type="file"  name="<?php echo $name?>" id="upload_<?php echo $name?>" data-target="<?= 'input#hdn_' . $name ?>"
                                                accept="<?= implode(', ', ['.pdf', '.doc', '.docx', '.png', '.jpeg', '.jpg'])  ?>"
                                            >
                                            <span>Upload File</span>
                                        </label>
                                    </div>

                                </div>
                                <div class="progressBar_1"></div>
                                <div class="filename"></div>
                            </div>
                            </div>
                            -->
                        <?php } ?>
                    </div>
                    <?php
                }
                ?>

                <?php foreach ($form_data as $d) : ?>
                    <?= $this->load->view('recruitment/_job_question_forms', $d, true) ?>
                <?php endforeach ?>

            </div>
            <div class="modal-footer m_footer_1">
                <input type="hidden" name="job_id" value="<?php echo $job_data->id ?>">
                <button type="button" class="btn btn-secondary" onclick="clear_form_inputs()">Close</button>
                <button type="button" class="btn btn-bg-color save_data">Submit</button>
            </div>
        </form>
    </div>
</div>
</div>

<script type="text/javascript">
    var imgUpload = '<?php echo $saveAttachment?>'
    var seek_timeout_err = '<?php if (isset($applicant_info) && isset($applicant_info['timeout_error'])) echo $applicant_info['timeout_error']; ?>';
</script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/jquery-3.4.1.min.js" ></script>
<script>
    function get_address_from_google() {        
        var input_auto = document.getElementById('pac-input');
            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                var addr = autocomplete.getPlace();
                var addr1 = autocomplete.getPlace().address_components;
                var addr2 = autocomplete.getPlace().formatted_address;

                var foundaddress = addr1;
            
                var newData = addr2.split(',');
                let str_num = addr1[0].long_name;
                let str_name = addr1[1].long_name;
                var final_addr = str_num + ' ' + str_name;
                newData.forEach((e,i) => {
                    if (i != 0) {
                        final_addr = final_addr +','+e;
                    }
                });
                document.getElementById('pac-input').value = final_addr;
            });
    }
</script>

<script>  
$(document).ready(function(){  
    $("#email_num_validation").blur(function(){  
        var email = $(this).val(); 
        
        jQuery.ajax({
                url: "/recruitment/RecruitmentAppliedForJob/get_applicant_existing_address",
                type: 'POST',
                dataType: 'JSON',
                data: { 'email': email },
                success: (data) => {
                    if (data && data.address) {
                        
                        
                        if(data.address.unit_number){
                            document.getElementById('unit_number').value = data.address.unit_number;
                        }
                        if(data.address.is_manual_address=='1'){
                            document.getElementById('manual_address').value = data.address.manual_address;
                            document.getElementById("is_manual_address").checked = true;
                            document.getElementById("manual_address").disabled = false;
                        }else{
                            document.getElementById('pac-input').value = data.address.address;
                            document.getElementById("is_manual_address").checked = false;
                            document.getElementById("manual_address").disabled = true;
                        }
                    } 
                },
                error: (xhr) => {
                    console.log("error"+ xhr);
                }
            });
    });  
});
</script>
<script>
function changeManualAddress(){
    var checkbox = document.getElementById("is_manual_address");
        if(checkbox.checked){
            document.getElementById("manual_address").disabled = false;
            document.getElementById("pac-input").disabled = true;
            document.getElementById("is_manual_address").value = 1;
            document.getElementById("pac-input").value = '';
        }else{
            document.getElementById("manual_address").disabled = true;
            document.getElementById("manual_address").value = '';
            document.getElementById("pac-input").disabled = false;
            document.getElementById("is_manual_address").value = 0;            
        }
}
</script>
<script>
$( function() {
    let d= new Date();
    let start_year = d.getFullYear()-110;
    let end_year = d.getFullYear();
    $( "#dob_datepicker" ).datepicker({
        beforeShow: function(input, inst) {
            $(document).off('focusin.bs.modal');
        },
        onClose:function(){
            $(document).on('focusin.bs.modal');
        },
      changeMonth: true,
      changeYear: true,
      yearRange: start_year+":"+end_year,
     dateFormat: "dd-mm-yy",
     maxDate: new Date()
    });
  } );
  </script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_MAP_KEY ?>&libraries=places&callback=initAutocomplete" async defer></script>

<script src="<?= $assets_hostname ?>/assets/preview_job/js/operate.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/jquery-ui.js " ></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/popper.min.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/bootstrap.min.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/jquery.validate.min.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/jquery-validate.bootstrap-tooltip.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/jquery.toast.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/simpleUpload.min.js"></script>
<script src="<?= $assets_hostname ?>/assets/preview_job/js/jquery.validate.additional-methods.js"></script>

<?php /* Don't use 'accept' rule provided by jquery validation because it is too strict*/ ?>
</body>
</html>
