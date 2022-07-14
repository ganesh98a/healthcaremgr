<?php

/**
 * @var string $first_name
 */

/*
// In text form

Dear {Candidates.First Name},

Thanks for applying to join the ONCALL Group Australia team! Should your profile match the criteria for the role, one of our Recruitment Consultants will be in touch with you in a few days.

If you do not hear from us, it is likely that you have not been shortlisted and we thank you for the time you have invested in the application.

We are always posting new jobs on our website, seek and ethical jobs, so keep checking for the latest roles.

Thanks again and good luck!

Kind Regards,

Recruitment Team
ONCALL Group Australia
www.oncall.com.au
(03) 9896 2468
*/


?>
<?= emailHeader() ?>
<table width="100%">
    <tr>
        <td style="font-family:sans-serif; font-size: 14px;">
            <table width="80%" align="center">
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" width="100%" align="center">
                            <tr>
                                <td>
                                    <p><b>Dear <?= $firstname ?>,</b></p>
                                    <p>Thanks for applying to join the ONCALL Group Australia team! Should your profile match the criteria for the role, one of our Recruitment Consultants will be in touch with you in a few days.</p>
                                    <p>If you do not hear from us, it is likely that you have not been shortlisted and we thank you for the time you have invested in the application.</p>
                                    <p>We are always posting new jobs on our website, seek and ethical jobs, so keep checking for the latest roles.</p>
                                    <p>Thanks again and good luck!</p>
                                </td>
                            </tr>
                        </table>

                        <p>Kind Regards,</p>
                        <p style="margin:0px;">Recruitment Team</p>
                        <p style="margin:0px;">ONCALL Group Australia</p>
                        <p style="margin:0px;"><a href="https://www.oncall.com.au">www.oncall.com.au</a></p>
                        <p style="margin:0px;">(03) 9896 2468</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?= emailFooter() ?>