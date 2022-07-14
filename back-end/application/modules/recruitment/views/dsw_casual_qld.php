<?php 
if(in_array($type,['employment_content','staff_content','position_content','header'])){
    if($type!='header'){
        $firstName = isset($complete_data['firstname']) ? $complete_data['firstname']:'';
        $lastName = isset($complete_data['lastname']) ? $complete_data['lastname']:'';
        $fullName = $firstName .' '.$lastName;
        $dateData = isset($complete_data['issue_date'])? $complete_data['issue_date'] : date('d/m/Y'); 
    }
    $logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');
    $signatureLogoUrl = base_url('assets/img/representative_signature.png');
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/style.css">
<?php if($type=='header'){ ?>
    <table>
        <tr>
           <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
           <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
       </tr>
   </table>
<?php } ?>
<?php if($type=='footer'){ ?>
    <div class="text-right">{PAGENO}</div>
<?php } ?>
<?php if($type=='employment_content'){ ?>
    <table class="pb-4">
        <tr>
            <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;" /> </td>
            <td style="font-size:22px; padding-left:50px" class="font-weight-bold"></td>
        </tr>
        <tr><td colspan="2"><?php echo $dateData;?></td></tr>

    </table>
    <div>
        <div class="f-13"><?php echo $fullName;?></div>        
        <?php 
            if (isset($complete_data['is_manual_address']) && $complete_data['is_manual_address'] == 0) { ?>
            <div class="f-13"> <?php
                echo !empty($complete_data['unit_number']) ? $complete_data['unit_number'] . ', ' : ''; 
                echo !empty($complete_data['street_address']) ? $complete_data['street_address'] : ''; 
            ?> </div> <?php
            } else { ?>
            <div class="f-13" style="width: 250px;word-break: break-word;"> <?php
                echo !empty($complete_data['unit_number']) ? $complete_data['unit_number'] . ', ' : ''; 
                echo !empty($complete_data['manual_address']) ? $complete_data['manual_address'] : '';
            ?> </div> <?php
            }
        ?>        
        <div class="f-13 pb-3 justify-para"><?php echo !empty($complete_data['street_address_other']) ? $complete_data['street_address_other'] : '';?></div>
        <div class="f-15 pb-4 font-weight-bold">Strictly Private and Confidential</div>

        <div class="f-13 pb-3 justify-para">Dear <?php echo $firstName;?>,</div>
        <div class="f-15 pb-4 font-weight-bold">Offer of Casual Employment</div>

        <div class="f-13 pb-3 justify-para">
            We would like to take this opportunity to offer you casual employment with ONCALL Group Australia
            Pty Ltd (ACN 633 010 330) <b>(ONCALL)</b> in the position of Labour-Hire Worker <b>(Position)</b>.
        </div>
        <div class="f-13 pb-3 justify-para">
            ONCALL will endeavour to make our relationship a success and offer work to you as and when required by ONCALL. 
            Our ability to offer you work will depend on a range of factors including client requirements, your availability, 
            type of work, location and your shift preferences. It will also depend on customer feedback and choice. 
            The clients are not obliged to provide ongoing work and ONCALL is not obliged to allocate work to you on an ongoing basis.
        </div>
        <div class="f-13 pb-3 justify-para">
            An agreement to perform work is only made when you accept a request to attend a particular 
            work site on a given day. At that time, you agree to attend the site to perform such work as 
            may be allocated to you. You are also required to respect and comply with the working conditions, 
            policies and procedures of the work site and ONCALL throughout your shift, although these policies 
            and procedures are not incorporated into this letter and do not form part of your employment contract.
        </div>
        <div class="f-13 pb-3 justify-para">
            The terms and conditions in this document apply to any offer of work for ONCALL with a partner organisation 
            or client.
        </div>
        <div class="pb-3">
            <div class="f-13 font-weight-bold">ELIGIBILITY TO WORK WITH ONCALL</div>
            <div class="f-13 justify-para">
            This offer of casual employment is subject to satisfaction of the following conditions precedent.  Despite any other 
            provision in this document, if all the below conditions precedent have not been met by the day you first attend for work 
            with ONCALL, ONCALL's offer of employment will lapse or the employment will immediately end, whichever the case may be, 
            without any liability to ONCALL for any payment or compensation to you.  
            </div>           
        </div>
        <div class="f-13 pb-3 justify-para">
            Your employment is conditional on you continuing to satisfy the conditions set out below. If your circumstances change and 
            you are no longer able to satisfy any of these conditions, you must immediately notify ONCALL. Failure to comply with your 
            obligations under this clause may result in your immediate dismissal.
         </div>
        <div class="f-13 pb-3">
        <i>Police check</i>
    </div>
    <div class="f-13 pb-3 justify-para ">
    If you have not already provided ONCALL with evidence of a satisfactory police check, you must do so prior to your first engagement. You agree to renew your police check prior to its expiration or at the request of ONCALL. You understand and accept that if the police check obligation is not met, ONCALL will not be able to offer you work.
    </div>
    <div class="f-13 pb-3 justify-para">
    You warrant that you have disclosed any criminal charges or convictions to ONCALL and will advise ONCALL as soon as practicable if you are charged or convicted of a criminal offence. You consent to ONCALL disclosing such information to its clients with whom you have worked where ONCALL is required to do so in accordance with applicable law or any other relevant obligations.
    </div>
       
    <pagebreak />
    
    <div class="f-13 pb-3 justify-para">
        <i>International police check</i>
    </div>
    <div class="f-13 pb-3 justify-para" >
    If you were a citizen or a permanent resident of a country other than Australia at any time since turning 16 years of age, you must provide ONCALL with a statutory declaration which testifies that you have no existing criminal record in that country. This document must be submitted to ONCALL at the recruitment stage.
    </div>
    <div class="f-13 pb-3 justify-para">
    Additionally, if you resided in an overseas country for 12 months or more in the last ten years you must contact the relevant overseas police force to obtain a criminal or police record check. If you have copies of the relevant police clearance which may have formed part of your visa application, these documents 
    </div>
    <div class="f-13 pb-3 justify-para">
        <i>Blue Card</i>
    </div>
    <div class="f-13 pb-3 justify-para">
    If you have not already obtained a Blue Card, you may be required to do so before commencing a placement with any of ONCALL’s clients. This is in accordance with the 'No Card, No Start laws' in Queensland. You agree to renew your Blue Card prior to its expiration (usually every two years). 
    </div>
    <div class="f-13 pb-3 justify-para">
    You must immediately notify ONCALL if your Blue Card expires. If you do not apply to renew your Blue Card by the time it expires, you will be subject to the No Card, No Start laws and ONCALL will not be able to offer you work.
    </div>
    <div class="f-13 pb-3 justify-para">
    You agree to familiarise yourself with the Blue Card requirements by visiting the Queensland Government website at: 
    <a href="https://www.qld.gov.au/law/laws-regulated-industries-and-accountability/queensland-laws-and-regulations/regulated-industries-and-licensing/blue-card">https://www.qld.gov.au/law/laws-regulated-industries-and-accountability/queensland-laws-and-regulations/regulated-industries-and-licensing/blue-card</a>
    </div>
    
    <div class="f-13 pb-3 justify-para">
        <i>Disability Worker Screening (Queensland)</i>
    </div>
    <div class="f-13 pb-3 justify-para">
    You will need:
    <div>
            <ul class="pl-3 mt-0 ml-3">
                <li>an NDIS worker screening clearance; </li>
                <li>Queensland disability worker screening clearance; or existing Yellow Card. </li>                          
            </ul>
    </div>
    </div>
    <div class="f-13 pb-3 justify-para">
    You will need to apply for the relevant worker screening clearance by completing the online application. To apply for a disability worker screening clearance, you will need to register for the Worker Portal and complete the online identity check.
    </div>
    <div class="f-13 pb-3 justify-para">
    You agree to familiarise yourself with the NDIS Worker Screening Requirements by visiting the NDIS Commission Website at:
    <a href="https://www.ndiscommission.gov.au/providers/worker-screening">https://www.ndiscommission.gov.au/providers/worker-screening</a>
    </div>
    <div class="f-13 pb-3 justify-para">
    You agree to familiarise yourself with the NDIS Worker Screening Requirements by visiting the NDIS Commission Website at:
    <a href="https://workerscreening.communities.qld.gov.au">https://workerscreening.communities.qld.gov.au</a>
    </div>
    <div class="f-13 pb-3 justify-para">
    By signing this contract, it will be deemed that you understand and comply with the requirements of this sub-clause and that you offer your consent to participate in all required worker screening checks. You acknowledge that you are familiar with the requirements of these screening checks and you understand that if you are listed as an excluded person on the NDIS National Database for any reason (which may include a work related incident involving a client) your employment with ONCALL will immediately terminate.
    </div>
    <div class="f-13 pb-3 justify-para">
        <i>Driver’s Licence</i>
    </div>

    <div class="f-13 pb-3 justify-para">
        <div>If you are engaged in Child Youth & Family Support or Transport Shifts, you will require:</div>
        <div>
            <ul class="pl-3 mt-0">
                <li>a current Queensland or other eligible driver’s licence;</li>
                <li>a registered, roadworthy vehicle; and</li>
                <li>comprehensive motor vehicle insurance that takes into account your usage of the vehicle for work purposes.
                </li>
            </ul>
        </div>
    </div>
    
    <div class="f-13 pb-3 justify-para">
    If you are engaged in Disability Support, a Queensland or other eligible driver’s licence is required for residential and NDIS community access shifts, and while not mandatory is strongly encouraged for all Disability Support.
    </div>
    <pagebreak />
    <div class="f-13 pb-3 justify-para">
    If requested, you must provide ONCALL with a copy of your driver’s licence or present it for verification by ONCALL or ONCALL Customer Organisation you are rostered to work at.
    </div>
    
    <div class="f-13 pb-3 justify-para">
        <i>Right to work in Australia</i>
    </div>
    <div class="f-13 pb-3 justify-para">
    At all times during your employment, you must be lawfully entitled to perform work in Australia, and where applicable, must hold (and if required, provide copies of) the necessary visas and meet all other immigration requirements necessary to perform work in Australia.  If you are no longer entitled to work in Australia, your employment with ONCALL will immediately terminate.
    </div>
    <div class="f-13 pb-3 justify-para">
        <div class="font-weight-bold ">CASUAL RATE OF PAY</div>
    </div>

    <div class="f-13 pb-3 justify-para">
    The <i>Social, Community, Home Care and Disability Services Industry Award 2010</i><b> (Award)</b> applies to your employment. This industrial instrument may vary from time to time and is not incorporated into your contract of employment with ONCALL. You will be advised of the applicable Award classification and base rate of pay prior to commencing your shift or assignment.
    </div>

    <div class="f-13 pb-3 justify-para">
        Your rate of pay will be determined by reference to the nature of your placement with ONCALL. You will be paid a base rate of pay, inclusive of a 25% casual loading, at least equal to the base rate of pay applicable under the Award for the work performed. You will be advised of the applicable base rate of pay prior to commencing your shift or placement.
    </div>

    <div class="f-13 pb-3 justify-para">
        In addition to your base rate of pay, you will be paid an amount at least equal to the penalty rate, overtime rate, loading or allowance due to you under the Award for the work performed. Any entitlement under the Award will be calculated by reference to the applicable rate of pay in the Award.   
    </div>

    <div class="f-13 pb-3 justify-para">
         Your wages, less deduction of applicable taxes (other than payroll tax), will be paid into your nominated financial institution account weekly.
    </div>

    <div class="f-13 pb-3 justify-para">
        ONCALL will additionally make statutory superannuation contributions equal to the minimum amount that ONCALL must contribute to avoid being liable for a charge under the <i>Superannuation Guarantee Charge Act 1992</i> (Cth), currently 10% of your ordinary time earnings.
    </div>

    <div class="f-13 pb-3 justify-para">
        <div class="font-weight-bold ">GENERAL</div>
        <div>
        The payments and benefits set out in this agreement are paid in full satisfaction of all payment obligations ONCALL has to you in respect of your employment. This includes (but is not limited to) benefits that may be payable to you under any legislation, award, enterprise agreement or other industrial instrument and for any reasonable additional hours necessary in the performance of your duties. Such benefits may include minimum weekly wages, penalty rates (including public holiday rates), overtime, loadings (including annual leave loading), allowances or other monetary benefits.
        </div>
    </div>    
    <div class="f-13 pb-3 justify-para">
        Where you are entitled to a payment or benefit that is not set out in this agreement, you agree that ONCALL may set off any amount paid to you under this agreement in satisfaction of that payment or benefit.
    </div>
    <div class="f-13 pb-3 justify-para">
        As you are engaged on a casual basis, your base rate of pay is inclusive of a 25% casual loading, which is paid instead of (and which may otherwise be applied to offset any) entitlements to paid leave and other matters from which casuals are excluded (including redundancy pay and notice of termination).
    </div>
    
    <div class="f-13 pb-3 justify-para">
        <div class="font-weight-bold ">NATURE OF ENGAGEMENT</div>
        <div>
            ONCALL may offer, and you may agree to a placement with a client of ONCALL. You may elect to accept or reject each offer of work made by ONCALL. Placements offered may be at various locations at the as requested by the client. Your hours of work will be dependent on your placement. ONCALL does not guarantee you a minimum or maximum number of hours or shifts per week and makes no firm advance commitment to continuing and indefinite work according to an agreed pattern of work.
        </div>
    </div>
    <pagebreak />
    <div class="f-13 pb-3 justify-para">
    Your engagement to perform your duties will conclude at the end of each day on which you are given work.  Both you and ONCALL agree and acknowledge that because the nature of labour hire work can be irregular and uncertain, each offer of engagement can be accepted or rejected. Each instance of engagement is a new contract on these terms.
    </div>
    <div class="f-13 pb-3 justify-para">
        <div class="font-weight-bold ">LEAVE</div>
        <div>
            As a casual employee, you are not entitled to paid annual leave or to paid personal/carer's leave. 
        </div>
    </div>
    <div class="f-13 pb-3 justify-para">
        <div class="font-weight-bold ">DUTIES</div>
        <div>
            Your duties will be as per your Position Description.
        </div>
    </div>
    <div class="f-13 pb-3 justify-para">
        Following consultation with you, ONCALL may vary your position, duties, reporting lines and base location where such changes are consistent with your skills and knowledge. The terms and conditions of this agreement will continue to apply to your employment with ONCALL despite any changes from time to time to your position, duties and responsibilities, remuneration, working hours or employment location, unless agreed in writing by both parties.
    </div>
    
    <div class="f-13 pb-3 justify-para">
        <div class="font-weight-bold">CONFIDENTIAL INFORMATION</div>
        <div>
            <b>Confidential information</b> means all information (whether or not it is described as confidential and whenever acquired) in any form or medium concerning any past, present or future business, operations or affairs of ONCALL or any client of ONCALL including, without limitation:
        </div>
    </div>
    <div class="f-13 pb-3 justify-para">
        <table class="f-13 ml-3">
            <tr>
                <td width="25" class="align-top">a)</td>
                <td>all technical or non-technical data, formulae, patterns, programs, devices, methods, techniques, plans, drawings, models and processes, source and object code, software and computer records;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">b)</td>
                <td>all business and marketing plans and projections, details of agreements and arrangements with third parties, and customer and supplier information and lists;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">c)</td>
                <td>all financial information, pricing schedules and structures, product margins, remuneration details and investment outlays;
                </td>
            </tr>
            <tr>
                <td width="25" class="align-top">d)</td>
                <td>all information concerning any employee, client, customer, contractor, supplier or agent of ONCALL;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">e)</td>
                <td>ONCALL's policies and procedures; and</td>
            </tr>
            <tr>
                <td width="25" class="align-top">f)</td>
                <td>all information contained in this letter, </td>
            </tr>
        </table>
    </div>

    <div class="f-13 pb-3 justify-para">
        but excludes information that you can establish:
    </div>

    <div class="f-13 pb-3 justify-para">
        <table class="f-13 ml-3">
            <tr>
                <td width="25" class="align-top">a)</td>
                <td>is known by you or in your possession or control other than through a breach of this document and is not subject to any obligation of confidence; or</td>
            </tr>
            
            <tr>
                <td width="25" class="align-top">b)</td>
                <td>is in the public domain other than by a breach of this document or any obligations of confidence.</td>
            </tr>           
        </table>
    </div>

    <div class="f-13 pb-3 justify-para">
        <div><i>Access and use<i>
            <div>
                You acknowledge that during your employment you will have access to and knowledge of Confidential Information. You must not use, or make a copy or other record of, Confidential Information for a purpose other than for the benefit of ONCALL.
            </div>
        </div>
    </div>
    <div class="f-13 pb-3 justify-para">
        Additionally, you are required to enter into the Confidentiality Deed annexed to this agreement.
    </div>
    <div class="f-13 pb-3 justify-para">
        <div><i>Disclosure</i></div>
        <div>
            You must not disclose Confidential Information and must use your best endeavours, including complying with all security measures used to safeguard Confidential Information, to prevent the disclosure of the Confidential Information to any person or entity unless:
        </div>
    </div>
    <div class="f-13 pb-3 justify-para">
        <table class="f-13 ml-3">
            <tr>
                <td width="25" class="align-top">a)</td>
                <td> disclosure is required or authorised in the legitimate performance of your duties;</td>
            </tr>
            <pagebreak />
            <tr>
                <td width="25" class="align-top">b)</td>
                <td> you obtain the prior written consent of ONCALL; or</td>
            </tr>
            <tr>
                <td width="25" class="align-top">c)</td>
                <td> you are required by law to disclose Confidential Information.</td>
            </tr>
        </table>
    </div>
    <div class="f-13 pb-3 justify-para">
       If you are required by law to disclose Confidential Information, where possible, you must prior to making the disclosure inform ONCALL of the requirement and co-operate with ONCALL, to the extent permissible at law, to minimise the disclosure.
   </div>
   <div class="f-13 pb-3 justify-para">
    If you are uncertain as to whether certain information is Confidential Information, you will treat that information as Confidential Information unless you are advised otherwise in writing by ONCALL.
    </div>
<div class="f-13 pb-3 justify-para">
    <div><i>Notification and cooperation to protect information</i></div>
    You must immediately notify ONCALL of any actual or suspected unauthorised use, copying or disclosure of Confidential Information.
</div>
<div class="f-13 pb-3 justify-para">
    You must comply with any reasonable steps required by ONCALL in order to protect its Confidential Information.
</div>
<div class="f-13 pb-3 justify-para">
    Your obligations in respect of Confidential Information continue after your employment ends.
</div>
<div class="f-13 pb-3 justify-para">
    <div><i>Return and destruction</i></div>
    <div>Upon termination of your employment with ONCALL, or at any time at the request of ONCALL, you must immediately deliver to ONCALL all documents or other things in your possession, custody or control on which any Confidential Information is stored or recorded, whether in writing or in electronic or other form.</div>
</div>
<div class="f-13 pb-3 justify-para">
    Alternatively, and only if requested by ONCALL, you must destroy the Confidential Information (in the case of data stored electronically or in any other form, by erasing it from the media on which it is stored such that it cannot be recovered or in any way reconstructed or reconstituted) and certify in writing to ONCALL that the Confidential Information, including all copies, has been destroyed.
</div>
<!-- <div class="f-13 pb-3 justify-para">
    You acknowledge that ONCALL owns all Intellectual Property Rights in any material created, generated or contributed to by you in connection with your employment.
</div>
<div class="f-13 pb-3 justify-para">
    You assign to ONCALL all existing and future Intellectual Property Rights in any material created, generated or contributed to by you in connection with your employment.
</div>
<div class="f-13 pb-3 justify-para">
    You must do all things reasonably requested by ONCALL to enable ONCALL to perfect the assignment of the Intellectual Property Rights. If you do not comply with such a request, you authorise ONCALL (or any persons authorised by ONCALL) to do all things and execute all documents necessary to give effect to that request on your behalf.
</div> -->


<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">INTELLECTUAL PROPERTY RIGHTS</div>
    <div>You acknowledge that ONCALL owns all Intellectual Property Rights in any material created, generated or contributed to by you in connection with your employment.
    </div>
</div>
<div class="f-13 pb-3 justify-para">
    You assign to ONCALL all existing and future Intellectual Property Rights in any material created, generated or contributed to by you in connection with your employment.  
</div>
<div class="f-13 pb-3 justify-para">
    You must do all things reasonably requested by ONCALL to enable ONCALL to perfect the assignment of the Intellectual Property Rights. If you do not comply with such a request, you authorise ONCALL (or any persons authorised by ONCALL) to do all things and execute all documents necessary to give effect to that request on your behalf.
</div>
<div class="f-13 pb-3 justify-para">
    You and ONCALL acknowledge that you may have moral rights within the meaning of the <i>Copyright Act 1968 (Cth)</i> in relation to the Intellectual Property. You genuinely consent to all acts or omissions, whether occurring before or after this consent is given, committed by ONCALL, its officers, licensees and successors in title, that may infringe any or all of your moral rights in relation to the Intellectual Property. This consent is irrevocable and does not terminate on termination of your employment or for any other reason.  </div>
<div class="f-13 pb-3 justify-para">
    <b>Intellectual Property</b> means all present and future rights conferred in law in or in relation to any copyright, trademarks, designs, patents, circuit layouts, plant varieties, business and domain names, inventions and confidential information, and other results of intellectual activity in the industrial, commercial, scientific, literary or artistic fields.
</div>

<div class="f-13 pb-3 justify-para">
    <b>Intellectual Property Rights </b>means all property rights in connection with Intellectual Property; any right to have Confidential Information kept confidential; and any application or right to apply for registration of any rights in connection with Intellectual Property.
</div> 
<pagebreak />
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">STANDARDS OF BEHAVIOUR</div>
    <div>You are required to make disclosures to ONCALL of any actual or potential conflict of interest between your personal affairs and your duties in your position. Should you be unable or unwilling to resolve a conflict of interest to the satisfaction of ONCALL, ONCALL will not be able to offer you work. 
    </div>
</div>
<div class="f-13 pb-3 justify-para">
    In the interests of health and safety, efficiency and harmony in the workplace and the reputation of ONCALL, all employees must maintain a professional standard of behaviour. At all times during your employment you must:
</div>

<div class="f-13 pb-3 justify-para">
    <ul class="pl-3 f-13">
        <li>comply with ONCALL’s code of conduct as amended from time to time and any applicable industry codes of conduct;
        </li>
        <li>comply with all lawful orders and instructions given by ONCALL;
        </li>
        <li>dealing with both internal and external customers and your colleagues in a polite, helpful and considerate way;</li>
        <li>be clean and tidy; and
        </li>
        <li>performing all of your duties promptly and efficiently.
        </li>
    </ul>
</div>
<div class="f-13 pb-3 justify-para">
    A breach of these standards of behaviour by you can result in disciplinary action, which may include termination of your employment.
</div>

<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">DIRECT ENGAGEMENT BY A CLIENT</div>
    <div>You recognise that ONCALL invests significant costs in your recruitment. You acknowledge that if you are offered or seek engagement with a client of ONCALL and you accept such engagement, the client must pay ONCALL a placement fee as determined by ONCALL. 
    </div>
</div>

<div class="f-13 pb-3 justify-para">
    You agree that if you are offered, or apply for, employment with a client of ONCALL, you will notify ONCALL as soon as practicable. 
</div>
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">TERMINATION OF EMPLOYMENT</div>
    <div>As a casual employee, your employment with ONCALL comes to an end at the conclusion of each shift. ONCALL may also terminate your employment immediately, and prior to the conclusion of your shift, for serious misconduct.
    </div>
</div>
<div class="f-13 pb-3 justify-para">
    Serious misconduct includes (but is not limited to):
</div>

<div class="f-13 pb-3 justify-para">
        <table class="f-13 ml-3">
            <tr>
                <td width="25" class="align-top">a)</td>
                <td>bullying, theft, fraud or assault;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">b)</td>
                <td>drinking alcohol at work or arriving to a shift intoxicated or under the influence of non-prescribed drugs during working hours;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">c)</td>
                <td>any conduct that causes imminent and serious risk to the reputation, viability or profitability of ONCALL’s business; </td>
            </tr>
            <tr>
                <td width="25" class="align-top">d)</td>
                <td>any act of sexual harassment or molestation of a client, another worker or any other person;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">e)</td>
                <td>use of abusive or offensive language;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">f)</td>
                <td>violation of ONCALL's work health and safety policies and procedures;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">g)</td>
                <td>a serious breach of duty of care, code of conduct or any governing legislation; or</td>
            </tr>
            <tr>
                <td width="25" class="align-top">h)</td>
                <td>inappropriate and unauthorised use of social media.</td>
            </tr>
        </table>
</div>

<div class="f-13 pb-3 justify-para">
    Depending on the nature of the conduct, it may also be necessary for ONCALL to report any such conduct to an appropriate authority, including the Queensland Police, appropriate Queensland Government departments and the National Disability Insurance Agency.  
</div>
<pagebreak />
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">DISPUTE RESOLUTION PROCEDURE</div>
    <div>In the event of a dispute or grievance arising concerning the contents of this agreement the parties agree to make every effort to resolve the dispute by consultation and negotiation. If the negotiation process is exhausted without the dispute being resolved, the parties may refer the matter to a mutually agreed mediator or conciliator for the purpose of resolving the dispute.
    </div>
</div>
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">ACCURACY OF INFORMATION</div>
    <div>You warrant that all information you provided to ONCALL which led to your engagement including information relating to your qualifications and curriculum vitae is accurate in all respects and you have not misled or deceived ONCALL in any way in relation to the information provided.</div>
</div>
<div class="f-13 pb-3 justify-para">
    You warrant that you have not omitted or failed to disclose any information to ONCALL, which you may reasonably consider to be relevant to your engagement under this Agreement.
</div>
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">POLICIES AND PROCEDURES</div>
    <div>You are required to comply with the policies and procedures of ONCALL in place from time to time. ONCALL may create, amend, withdraw or replace its policies and procedures at its sole discretion.
    </div>
</div>
<div class="f-13 pb-3 justify-para">
    However, ONCALL’s policies and procedures do not form part of this Agreement. Where ONCALL does not follow a policy or procedure, to the extent permitted by law, this will not constitute a breach of this Agreement.
</div>
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">PRIVACY</div>
    <div>You consent to ONCALL collecting, using and disclosing your personal and health information for any lawful purpose relating to your employment. You also consent to ONCALL transferring your personal and health information outside Queensland and Australia in the course of ONCALL's business activities.
    </div>
</div>
<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">GENERAL PROVISIONS</div>
    <div>This Agreement represents the entire agreement, between ONCALL and you in relation to your engagement and it replaces and supersedes all previous agreements, terms and conditions of engagement, contracts, negotiations, understandings, or representations between ONCALL and you. This agreement may only be varied, amended or replaced by agreement in writing between ONCALL and you.</div>
</div>
<div class="f-13 pb-3 justify-para">
    This Agreement shall be governed by and construed in accordance with the laws of the State of Queensland.
</div>
<div class="f-13 pb-3 justify-para">
    In the event that any provision of this agreement is held unenforceable, such provision shall be severed and shall not affect the validity or enforceability of the remaining portions.
</div>

<div class="f-13 pb-3 justify-para">
    <div class="font-weight-bold">ACCEPTANCE OF TERMS AND CONDITIONS</div>
    <div>I trust the terms and conditions in this contract are acceptable to you. Please sign where indicated below to confirm your acceptance.</div>
</div>
<div class="f-13 pb-3">
    Yours faithfully
</div>
<div class="f-13 pb-5">
    <div class="pb-2" style="border-bottom:1px solid #000">ONCALL Group Australia Pty Ltd</div>   
    <div class="font-weight-bold pb-3 pt-1">ACCEPTANCE</div>
    <div class="pb-3">I, <?php echo $fullName;?> accept the terms and conditions contained in this casual employment contract.</div>
</div>
<div>
    <div class="w-50 float-left">
        <div>
            <b>Signature:</b>
        </div>
        <div style="padding-left:70px; width:60%;">
            <dottab outdent="2em" />
        </div>
    </div>
    <div class="w-50">
        <div>
            <b>Date:</b>
            <span><?php echo $dateData;?></span>
            <div>
                <div class="w-75" style="padding-left:30px;">
                    <dottab />
                </div>
            </div>
        </div>
    </div>
</div>
<div>(To confirm your acceptance of this position please sign and return one copy (keeping one for your records).</div>

<pagebreak />
<div class="f-13 pb-5 font-weight-bold">
    CONFIDENTIALITY DEED REGARDING CLIENT INFORMATION
</div>
<div class="f-13 pb-4 justify-para">
    <table class="f-13">
        <tr>
            <td width="100px" class="align-top">
                <div class="font-weight-bold">BETWEEN:</div>
                <div>of</div>
                <td>
                    <td class="pb-3">
                        <div>ONCALL Group Australia Pty Ltd (ACN 633 010 330)</div>
                        <div>KPMG, ‘Tower Two Collins Square’ Level 36, 727 Collins Street, Docklands, VIC,
                        3008</div>
                        <div class="font-weight-bold">(ONCALL)</div>
                    </td>
                </tr>
                <tr>
                    <td width="100px" class="align-top">
                        <div class="font-weight-bold">AND</div>
                        <div>of</div>
                        <td>
                            <td>
                                <div><?php echo $fullName;?></div>
                                <?php 
                                    if (isset($complete_data['is_manual_address']) && $complete_data['is_manual_address'] == 0) { ?>
                                    <div class="f-13"> <?php
                                        echo !empty($complete_data['unit_number']) ? $complete_data['unit_number'] . ', ' : ''; 
                                        echo !empty($complete_data['street_address']) ? $complete_data['street_address'] : ''; 
                                    ?> </div> <?php
                                    } else { ?>
                                    <div class="f-13" style="width: 250px;word-break: break-word;"> <?php
                                        echo !empty($complete_data['unit_number']) ? $complete_data['unit_number'] . ', ' : ''; 
                                        echo !empty($complete_data['manual_address']) ? $complete_data['manual_address'] : '';
                                    ?> </div> <?php
                                    }
                                ?> 
                                <div class="f-13"><?php echo isset($complete_data['street_address_other']) ? $complete_data['street_address_other']:''?></div>
                                <div class="font-weight-bold">(Employee)</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="f-13 pb-5">
                    <div class="font-weight-bold">RECITALS</div>
                    <table class="f-13 justify-para">
                        <tr>
                            <td class="align-top" width="25px">A.</td>
                            <td>ONCALL operates a business of providing specialised temporary labour hire <b>(Business)</b> to clients
                                in the disability and welfare support sectors <b>(Client Organisations)</b>.</td>
                            </tr>
                        </table>
                        <table class="f-13 justify-para">
                            <tr>
                                <td class="align-top" width="25px">B.</td>
                                <td>ONCALL engages the Employee.</td>
                            </tr>
                        </table>
                        <table class="f-13 justify-para">
                            <tr>
                                <td class="align-top" width="25px">C.</td>
                                <td>
                                    During the course of performing the Employee’s duties, the Employee will have contact with the Client Organisations and the Client Organisation's respective clients <b> (End Clients)</b>, the Employee may have access to or gain knowledge of all or part of the following confidential information:
                                </td>
                            </tr>
                        </table>
                        <table class="f-13 pl-4 justify-para">
                            <tr>
                                <td class="align-top" width="20px">a)</td>
                                <td>personal or sensitive information relating to an End Client, including information which may identify an End Client, an End Client's place of residence and information in relation to an End Client's physical and psychological condition;</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">b)</td>
                                <td>all administrative procedures, business and financial information, computer programs, manuals, notes, routines, concepts, ideas, know-how of ONCALL, the Client Organisation or the End Client; and
                                </td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">c)</td>
                                <td>any other information that would otherwise at law be considered secret or confidential information of ONCALL, the Client Organisation or the End Client,</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="pt-2">(together, <b>Confidential Information</b>).</td>
                            </tr>
                        </table>
                    </div>
                    <div class="f-13 pb-1 justify-para">
                        <div class="font-weight-bold">OPERATIVE TERMS</div>
                        <table class="f-13 justify-para">
                            <tr>
                                <td class="align-top" width="20px">1.</td>
                                <td>The Employee:</td>
                            </tr>
                        </table>
                        <table class="pl-4 f-13 justify-para">
                            <tr>
                                <td class="align-top" width="20px">a)</td>
                                <td>agrees that the above information is true and accurate;</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">b)</td>
                                <td>acknowledges the necessity of protecting the Confidential Information and agrees that any disclosure in breach of this Deed may cause damage;
                                </td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">c)</td>
                                <td>acknowledges the need to protect the confidentiality of the Confidential Information;</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">d)</td>
                                <td>agrees that all of the provisions of this Deed are reasonable in all the circumstances; and</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">e)</td>
                                <td>agrees that the confidentiality obligations created by this Deed shall not merge or be released upon cessation of the Employee’s engagement but will continue afterwards.</td>
                            </tr>
                        </table>
                    </div>

                    <div class="f-13 pb-3 justify-para">
                        <table class="f-13">
                            <tr>
                                <td class="align-top" width="20px">2.</td>
                                <td>The Employee agrees that he/she must not:</td>
                            </tr>
                        </table>
                        <table class="f-13 pl-4 justify-para">
                            <tr>
                                <td class="align-top" width="20px">a)</td>
                                <td>
                                    use any or all of the Confidential Information for any purpose other than in the proper performance of his/her duties as an employee of ONCALL;
                                </td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">b)</td>
                                <td>divulge to any person all or any aspect of the Confidential Information otherwise than with the prior approval of:
                                    <table class="f-13 pl-4 justify-para">
                                        <tr>
                                            <td class="align-top" width="20px">(i)</td>
                                            <td>ONCALL, the Client Organisation and the End Client, or where the End Client does not have capacity to give consent, the End Client's parent, guardian or attorney as the case may be; or</td>
                                        </tr>
                                        <tr>
                                            <td class="align-top" width="20px">(ii)</td>
                                            <td>an authorised officer at the Department of Human Services;</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-top justify-para" width="20px">c)</td>
                                <td>grant or permit any unauthorised person to have access to or possession of the Confidential Information; or</td>
                            </tr>
                            <tr>
                                <td class="align-top justify-para" width="20px">d)</td>
                                <td>Make any written notes, copy, reproduce, store, record, computerise, document or duplicate any part of the Confidential Information.</td>
                            </tr>
                        </table>
                    </div>
                    <pagebreak />

                    <div class="f-13 pb-3 justify-para"> If the Employee is uncertain whether any information comprises part of the Confidential Information then the Employee must seek direction from ONCALL before divulging the information to any other person.</div>

                    <div class="f-13 pb-3 justify-para">
                    This Deed will be construed in accordance with the laws of the State of Queensland. If a provision (or part of it) of this Deed is held to be unenforceable or invalid, it must be interpreted as narrowly as necessary to allow it to be enforceable and valid. If it cannot be so interpreted narrowly, then the provision (or part of it) must be severed from this Deed without affecting the validity and enforceability of the remaining provisions. </div>

                    <div class="f-13 pb-3 justify-para pt-3">
                        <div class="w-50 float-left">
                            <b>EXECUTED</b> as a deed for and on behalf of<br>
                            <b>ONCALL Group Australia Pty Ltd (ACN 633 010
                            330)</b> by its authorised representative in the
                            presence of:
                        </div>
                        <div class="w-50">
                            <div>
                            </div>
                        </div>
                    </div>

                    <div class="f-13 pb-3 justify-para pt-5 w-100">
                        <div class="w-50 float-right ">
                            <div class="text-center w-75"><img src="<?php echo $signatureLogoUrl; ?>" style="" /></div>
                            <div class="w-75" style="margin-left:-5px"> <dottab /></div>
                            <div class="w-100 ">Representative's signature</div>
                            <div class="w-100 pt-4  pb-2">Marcela Mandarino</div>
                            <div class="w-100  pb-2">Human Resources Manager, VIC Operations</div>
                            <div class="w-75" style="margin-left:-5px"><dottab /></div>
                            <div class="w-100 ">Representative (please print)</div>
                        </div>
                    </div>

                    <div class="font-weight-bold pt-5 f-13">SIGNED SEALED AND DELIVERED by </div>
                    <div class="f-13 pb-3 justify-para w-100 pt-3">
                        <div class="w-50 float-left">
                            <?php echo $fullName;?>
                            <div class="w-75 pt-1" style="margin-left:-5px"><dottab /></div>
                            Employee name (please print)
                        </div>
                        <div class="w-50 pt-4">
                            <div class="w-75" style="margin-left:-5px"><dottab /></div>     
                            Employee signature     
                        </div>
                    </div>
                    <div class="f-13 w-100 pt-5">
                        <b> Date of deed:</b><?php echo $dateData;?>
                    </div>
                    <pagebreak />

                    <div class="font-weight-bold f-13 pb-3 justify-para text-center">PRE-EXISTING INJURY DECLARATION FORM</div>
                    <div class="f-13 pb-3 justify-para ">
                    In accordance with <i>Workers’ Compensation and Rehabilitation Act 2003</i>(Qld) <b>(WCR Act)</b>, you are required to disclose any or all pre-existing injuries, illnesses or diseases <b>(Pre-Existing Conditions)</b> suffered by you which could be accelerated, exacerbated, aggravated or caused to recur or deteriorate by you performing the responsibilities associated with the engagement for which you are applying with ONCALL Group Australia Pty Ltd (ACN 633 010 330) <b>(ONCALL)</b>.
                    </div>
                    <div class="f-13 pb-3 justify-para ">
                        In making this disclosure, please refer to the attached/included position description, which describes the nature of the engagement. It includes a list of responsibilities and physical demands associated with the engagement.
                    </div>
                    <div class="f-13 pb-3 justify-para ">
                        Please note that, if you fail to disclose this information or if you provide false and misleading information in relation to this issue, under section 571C of the Act, you and your dependants may not be entitled to any form of workers’ compensation as a result of the recurrence, aggravation, acceleration, exacerbation or deterioration of a pre-existing condition arising out of, in the course of, or due to the nature of your engagement.
                    </div>
                    <div class="f-13 pb-3 justify-para ">
                        Please also note that the giving of the false information in relation to your application for engagement with ONCALL may constitute grounds for disciplinary action or dismissal.
                    </div>
                    <div style="border:1px solid #000" class="px-2 py-1 text-left font-weight-bold">WORKER DECLARATION</div>
                    <div class="f-13 pb-3 justify-para pt-3">
                        I, <?php echo $fullName;?>  declare that:
                        <table class="f-13">
                            <tr>
                                <td class="align-top" width="20px">1.</td>
                                <td>I have read and understood this form and the attached/included position description and have discussed the engagement with ONCALL. I understand the responsibilities and physical demands of the engagement.</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">2.</td>
                                <td>I acknowledge that I am required to disclose all Pre-Existing Conditions suffered by me of which I am aware and could expect to be affected by the nature of my proposed employment with ONCALL.</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">3.</td>
                                <td>I acknowledge that failure to disclose this information or providing false and misleading information may disentitle me from receiving any compensation under the WIRC Act with respect to any recurrence, aggravation, acceleration, exacerbation or deterioration of the Pre- Existing Condition arising out of or in the course of or due to the nature of employment with ONCALL.</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">4.</td>
                                <td>Please tick whichever of the following statements is applicable: </td>
                            </tr>                           
                            <tr><td class="align-top pt-3 pl-2 pr-2 pb-3" width="25"> <input type="checkbox" checked /></td> <td> I am aware of the below Pre-Existing Conditions which could be affected by the nature of my proposed employment with ONCALL.</td>
                            </tr>
                            <tr> <td class="align-top p-2" width="25"> <input type="checkbox" checked /></td><td> I am not aware of any Pre-Existing Conditions which could be affected by the nature of my proposed employment with ONCALL.</td></tr>
                        </table>
                    </div>

                    <pagebreak />
                    
                    <div class="f-13 pb-3 justify-para font-weight-bold">
                        If you selected Yes, please list all details of any pre-existing conditions:
                    </div>
                    <div class="f-13 pb-3 justify-para font-weight-bold">
                        <table class="blank_table" border="1" width="100%">
                           <tr><td height="300px"><div ></div></td></tr>
                       </table>
                   </div>

                   <div class="f-13 pb-3 justify-para font-weight-bold">
                    I acknowledge and declare that the information provided in this form is true and correct in every particular, and that ONCALL is relying on the information that I have declared above.
                </div>

                <div class="f-13 pb-3 justify-para w-100 pt-3">
                    <div class="w-50 float-left pt-4">
                        <div class="w-75 pt-1" style="margin-left:-5px"><dottab /></div>
                        Signature of Employee
                    </div>
                    <div class="w-50">
                        <?php echo $dateData;?>
                        <div class="w-75" style="margin-left:-5px"><dottab /></div>     
                        Date  
                    </div>
                </div>

                <div class="f-13 pb-3 justify-para w-100 pt-5">
                    <div class="w-50 float-left">
                        <?php echo $fullName;?>
                        <div class="w-75 pt-1" style="margin-left:-5px"><dottab /></div>
                        Employee name (please print)
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if($type=='staff_content'){ ?>
            <table>
                <tr>
                   <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
                   <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
               </tr>
           </table>
           <div>
            <div class="f-13 pb-3 justify-para pt-2">
                This Code of Conduct is based on ONCALL’s values of:
            </div>
            <table width="90%" align="center" border="1" class="table_1 f-13 justify-para">
                <tr>
                    <td class="font-weight-bold align-top">Integrity</td>
                    <td>All ONCALL staff will act ethically, with integrity, honesty and transparency, and steadfastly adhere to high moral principles and professional standards at all times.
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Respect</td>
                    <td>All ONCALL Staff will show consideration and treat all people and property with respect.  Positively accept and welcome diversity in all people and cultures regardless of any differences, including disability, background, race, religion, gender, sexual identity or age.</td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Accountability</td>
                    <td>We all accept and take personal responsibility for our own actions and behaviours, ensuring we are trustworthy, transparent and meet or exceed assigned tasks, obligations and to admit mistakes.</td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Teamwork </td>
                    <td>We all will strive to work cooperatively and effectively as part of a group, large or small, acting and working together in the interests of a common goal and in line with ONCALL person centred approached.</td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Leadership </td>
                    <td>We will all take a role in leading by example, as individuals, teams and as an organisation within our sector, working toward the achievement of ONCALL’s vision and goals.</td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Commitment to Human Rights</td>
                    <td>We uphold to always treat people with dignity and respect, upholding fundamental rights to which a person is inherently entitled simply because she or he is a human being.</td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Advocacy </td>
                    <td>To act or process of support or defence of a person or cause, including commitment to report any form of abuse or suspected abuse.
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Professional
                    Boundaries</td>
                    <td>Boundaries are mutually understood, unspoken physical and emotional limits of the relationship between the person being supported and the worker.</td>
                </tr>
            </table>
        </div>

        <pagebreak />
        <table>
            <tr>
               <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
               <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
           </tr>
       </table>
       <div width="90%" style="margin:0px auto" class="">
        <div class="f-13 font-weight-bold">Integrity</div>
        <ul class="pl-3 f-13 set_pading_bottom justify-para">
            <li>Always act honestly, transparently and with integrity in the performance of your duties, when making decisions or revealing information.
            </li>
            <li>Ensure any advice given is current, based on available facts and data and within the boundaries of the role you are employed for.</li>
            <li>Maintain a strict separation between work related and personal financial matters.</li>
            <li>Exercise your power in a way that is fair and reasonable ensuring that family or other personal relationships do not improperly influence your decisions.</li>
            <li>Respect the rights and dignity of those affected by your decisions and actions, including individuals’ rights to freedom of expression, self-determination and decision making.</li>
            <li>Official and personal information is handled according to relevant legislation, policies and procedures.</li>
            <li>Public comment should always be discussed with management prior to making any such comment/s. Public comments must always be restricted to factual information and avoid the expression of a personal opinion.</li>
            <li>Report to an appropriate authority workplace behaviour that violates any law, rule or regulation or represents corrupt conduct, mismanagement of funds or is a danger to public health or safety or to the environment.</li>
            <li>Report to an appropriate authority immediately any form of abuse or suspected abuse.</li>
            <li>Declare and avoid conflicts of interest to help maintain workplace and community trust and confidence.</li>
            <li>Do not use your power to provide a private benefit to yourself, your family, your friends or associates.</li>
            <li>Only engage in other employment where the activity does not conflict with your role as an employee of ONCALL (Employment includes a second job, conducting a business, trade or profession, or active involvement with other organisations in a paid or voluntary role).</li>
            <li>Behave in a manner that does not bring yourself or ONCALL into disrepute.</li>
            <li>Advise your manager immediately and in writing if you are charged with a criminal offence, which is punishable by imprisonment or, if found guilty, could reasonably be seen to affect your ability to meet the inherent requirements of the work you are engaged to perform.</li>
            <li>Carry out your work safely and avoid conduct that puts yourself or others at risk. This includes the misuse of alcohol, drugs and other substances when at work or engaged in work related activities.</li>
            <li>If you are on medication that could affect your work performance or the safety of yourself or others, inform your manager immediately to ensure any necessary precautions or adjustments to your work can be put in place.</li>
            <li>Listen and respond to the views and concerns of clients (including children), particularly if they are telling you that they or another person has been abused and/or are worried about their safety or the safety of another</li>
        </ul>
    </div>

    <pagebreak />
    <table>
        <tr>
           <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
           <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
       </tr>
   </table>
   <div width="90%" style="margin:0px auto" class="">
    <div class="f-13 font-weight-bold">Respect</div>
    <ul class="pl-3 f-13 set_pading_bottom justify-para">
        <li>Lead by example and promote an environment that encourages respect and is free from discrimination, bullying, harassment and abuse.</li>
        <li>Positively embrace diversity and ensure all people are treated equally and respectfully regardless of culture, religion, gender, age, sexual orientation, race or disability.</li>
        <li>Be fair, objective and courteous in your dealings with individuals, organisations, community and other employees.</li>
        <li>Ensure privacy and confidentiality are adhered to all times in accordance with legislation, policies and procedures relating to and dealing with private information.</li>
        <li>Be aware of and actively listen to the expressed needs, values and beliefs of people from cultural, religious and ethnic groups that are different from yours, regarding culturally relevant needs that affect the delivery of service.</li>
        <li>Promote the cultural safety, participation and empowerment of Aboriginal people’s, including children, (for example, by never questioning an Aboriginal person’s self-identification).</li>
        <li>Promote the cultural safety, participation and empowerment of people, including children, with culturally and/or linguistically diverse backgrounds (for example, by having a zero tolerance of discrimination).</li>
        <li>Promote the safety, participation and empowerment of people with a disability, including children, (for example, during personal care activities).</li>
        <li>Be conscientious and efficient in your work striving for excellence at all times.</li>
        <li>Contribute both individually and as part of a team and engage constructively with your colleagues on work related matters.</li>
        <li>Share information with team members to support delivery of the best and most appropriate service outcomes.</li>
    </ul>

    <div class="f-13 font-weight-bold">Accountability</div>
    <ul class="pl-3 f-13 set_pading_bottom justify-para">
        <li>Work to the clear objectives of your role and if goals and objectives are unclear, discuss it with your manager.</li>
        <li>Take personal responsibility for your own actions and behaviours, ensuring you are trustworthy, transparent and meet or exceed assigned obligations or tasks and to admit to any mistakes.</li>
        <li>Consider the impact of your decisions and actions on ONCALL, the individuals you support, other organisations, the community and other employees.</li>
        <li>Use work resources and equipment efficiently and only for appropriate purposes as authorised by your employer. Work resources include: physical, financial, technological and intellectual property.</li>
        <li>Always seek to achieve value for money and use resources in the most effective way possible.</li>
        <li>Identify opportunities for continuous improvement to achieve best possible efficiency and responsiveness to processes and service delivery.</li>
        <li>Maintain accurate and reliable records as required by relevant legislation, policies and procedures.</li>
        <li>Records are to be kept in such a manner as to ensure their security and reliability and are made available to appropriate scrutiny when required.</li>
    </ul>
    <pagebreak />
    <table>
        <tr>
           <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
           <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
       </tr>
   </table>
   <ul class="pl-3 f-13 set_pading_bottom justify-para">
    <li>Notify your manager of any loss, suspension of or change to a registration, accreditation, license or other qualification that affects your ability to meet relevant essential requirements or to perform your duties.</li>
    <li>Ensure you are aware of and comply with all policies, procedures and legislation relevant to the performance of your duties.</li>
    <li>Do not refuse to follow a lawful or reasonable management direction or instruction.
    </li>
</ul>
<div class="f-13 pb-3 justify-para font-weight-bold">Teamwork</div>
<div class="f-13 pb-3 justify-para ">
    All employees should work cooperatively and effectively with colleagues or customer organisations to ensure the best possible support is provided – showing Reliability, Integrity, Responsibility, Attitude and Initiative.

    <ul class="pl-3 set_pading_bottom justify-para">
        <li><span style="text-decoration: underline;">Reliability:</span> Work cooperatively and demonstrate that you are reliable - arrive at work on time
        </li>
        <li><span style="text-decoration: underline;">Integrity:</span> Complete all tasks assigned or expected of you, ensuring you perform all tasks and providing support to clients to a high standard.
        </li>
        <li><span style="text-decoration: underline;">Responsibility:</span> Ensure all documentation is completed and the work area is left clean and tidy. Report all abuse or suspected abuse. Take responsibility for your own actions and behaviour.
        </li>
        <li><span style="text-decoration: underline;">Positive Attitude:</span> Be positive, keep all negative comments to yourself and smile. Avoid discussing personal issues in the workplace.
        </li>
        <li><span style="text-decoration: underline;">Initiative:</span> If you have completed your set tasks, look around to see if there are any additional tasks you may be able to do, and if in doubt ask.
        </li>
    </ul>
</div>

<div class="f-13 pb-3 justify-para  font-weight-bold">Leadership</div>
<div class="f-13 justify-para">
    All employees of ONCALL should demonstrate leadership by actively implementing, promoting and supporting these values
    <ul class="pl-3 set_pading_bottom">
        <li>Lead by example.</li>
        <li>Be honest.</li>
        <li>Make decisions free of bias and in line with ONCALL’s person centered approach.</li>
        <li>Be transparent, responsible, use resources efficiently, invite scrutiny.</li>
        <li>Treat all others fairly and without discrimination.</li>
        <li>Work co-operatively with your colleagues.</li>
        <li>Support and learn from your colleagues and accept differences in personal style.</li>
    </ul>
</div>

<div class="f-13 font-weight-bold">Commitment to Human Rights</div>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">
       <li>Respect and promote the human rights as set out in the Charter of Human Rights and Responsibilities.</li>
       <li>Embrace and advocate that everyone has the right to be respected, to feel safe and to be free from abuse.</li>
       <li>Uphold ONCALL’s zero tolerance policy towards abuse of children and people with a disability.</li>
       <li>Make decisions consistent with human rights.</li>
       <li>Protect and implement human rights</li>
       <li>Report all abuse or suspected abuse</li>
   </ul>
</div>
<pagebreak />
<table>
    <tr>
       <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
       <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
   </tr>
</table>

<div class="f-13 font-weight-bold">Advocacy</div>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">
        <li>As an organisation and as individuals, we have a responsibility to protect and advocate for our clients who are vulnerable.</li>
        <li>Encourage people with a disability and children to ‘have a say’ and participate in all relevant organisational activities where possible, especially on issues that are important to them.</li>
        <li>Seek advice from a manager if you are unclear on the correct procedures when advocating on behalf of a person you support.</li>
        <li>Understand the boundaries within the scope of your position
        </li>
    </ul>
</div>

<div class="f-13 font-weight-bold">Professional Boundaries</div>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">
        <li>Carry out your duties professionally, skilfully, competently and to the best of your ability within the scope of your role.</li>
        <li>Behave in a manner that maintains the trust and integrity expected from you by ONCALL.</li>
        <li>Sexual relationships between staff and clients/customer whom they work with are strictly prohibited. Always report sexual misconduct and abuse.</li>
        <li>Be prompt and courteous when dealing with the people we support, other stakeholders, employees of other agencies and members of the public.</li>
        <li>Use courteous and business-like language in all correspondence and other communications to or with the public, other employees and stakeholders.</li>
        <li>Always conduct yourself or act in such a way as to ensure that the good name of ONCALL and of other stakeholders is maintained at all times.</li>
        <li>Do not disclose information about a person ONCALL supports except when the appropriate Manager/Executive Manager has approved such release of information and the stakeholder is authorised or required by an Act or other law to do so.</li>
        <li>Do not use any property of ONCALL’s except in the pursuit of official duties of ONCALL or as otherwise duly authorized.</li>
        <li>The use of a personal mobile phone and text messaging while on duty is not permitted, unless otherwise agreed by the Manager.</li>
        <li>Never store or retain private contact details (including photos, phone numbers, email or Facebook) of clients or clients’ families nor provide your own personal contact details directly to clients or families.</li>
        <li>The use of ONCALL internet and email software will be in accordance with Use of Electronic Systems and Communications Policy.</li>
        <li>Always present yourself in a neat and professional manner wearing clothing appropriate to the role you fulfil in the workplace. Closed shoes must be worn at all times.</li>
        <li>Always behave and act in a way to ensure that you do not become liable to conviction of a criminal offence within the law.</li>
        <li>Be responsible for the care of clients and ensure they are treated with due regard for justice and with decency. Be courteous and avoid any actions that may bring your conduct into question.</li>
        <li>Treat clients fairly and do not abuse or exploit their position for personal gain.
        </li>
        <li>Develop any ‘special’ relationships with clients that could be seen as favouritism
        </li>
    </ul>
</div>
<pagebreak />
<table>
    <tr>
       <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
       <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
   </tr>
</table>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">
<!-- <li>Do not develop any ‘special’ relationships with clients that could be seen as favouritism
</li> -->
<li>Advise your manager of involvement in a relationship with a client’s family or other associates, direct or indirect, to avoid any potential conflict of interest.</li>
<li>Do not demand or receive a fee, reward, commission or benefit of any kind, from any person or organisation, for the initiation, conduct, omission or conclusion of any business, by any person or organisation with ONCALL.</li>
<li>Do not accept any gifts from a client, or relatives or friends of any client (including gifts under a Will), unless prior authority has been given by your manager.</li>
<li>Staff are strictly prohibited from being the executor of a client’s will.</li>
<li>Do not provide any comment, opinion or information to the media relating to the business of ONCALL or concerning employment with ONCALL without being authorised to do so.
</li>
<li>Alcohol, illicit drugs and other substances can compromise your judgement and therefore your ability to uphold your duty of care to vulnerable clients and others. Therefore, ONCALL adopts a strong, unequivocal stance as depicted below:
</li>
<li> Do not arrive to work under the influence of alcohol or illicit drugs or undertake any duties in an inebriated or drug affected state.</li>
<li> Do not bring into the workplace any alcohol, drugs or any other illicit substances.</li>
<li> Responsible drinking of alcohol at ONCALL social functions as authorised by Management is permitted in accordance with the Social Functions Policy.</li>

<li>Do not possess on the premises or in any workplace (including community-based support) any unauthorised weapon(s) or article(s) intended for use as such, whether for offensive or defensive purposes.
</li>
<li>Have any online, phone, direct or any other contact with a client or their family outside professional duties.</li>
</ul>
</div>

<div class="f-13 font-weight-bold">No employee of ONCALL shall wilfully</div>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">
        <li>Make any false entry in any book, record or document.</li>
        <li>Make any false or misleading statement or any statement they know to be inaccurate or significantly incomplete.</li>
        <li>Omit to make any required entry in any book, record or document.</li>
        <li>Destroy or damage any book, record or document required by law or direction to be kept by ONCALL.</li>
        <li>Furnish any false return or statement of any money or property.</li>
        <li>Steal or fraudulently misappropriate or obtain money/goods from ONCALL, other stakeholders, clients, volunteers or contractors.</li>
        <li>Breach Occupational Health & Safety policies and procedures of ONCALL, or any relevant legislation.</li>
        <li>Damage or sabotage any property of ONCALL.</li>
        <li>Assault, abuse or harass sexually or otherwise or discriminate against any client, volunteer, contractor or other stakeholder.</li>
        <li>Absent themselves from work for other than an authorised absence.</li>
    </ul>
</div>
<pagebreak />
<table>
    <tr>
       <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
       <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
   </tr>
</table>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">

        <li> Disclose any information, or supply any document concerning ONCALL’s business, current or former stakeholders or clients or the content of ONCALL’s contracts or procedures, without the express written permission of your manager, unless required to do so by law.</li>
        <li> When leaving the employment of ONCALL you should not use confidential information obtained during your employment to advantage a prospective employer or disadvantage ONCALL in commercial or other relationships with your prospective employer.</li>
    </ul>
</div>

<div class="f-13 font-weight-bold">Misconduct</div>
<div class="f-13 justify-para">
    <ul class="pl-3 set_pading_bottom">
        <li>Misconduct allegations are the most serious of complaints made and may result in the Disciplinary policy and procedure being implemented by ONCALL management.</li>
        <li>ONCALL may decide to stand down an employee while an investigation takes place. This means that the employee would be instructed not to come to work while the investigation is carried out, but you would be available for the purposes of the investigation.</li>
        <li>If you are stood down, you are not permitted to have contact with other employees. This does not imply that ONCALL believes you to be guilty, it is a precaution to protect the integrity of the investigation.</li>
    </ul>
</div>
<div class="f-13 font-weight-bold">Related Documents </div>
    <ul class="pl-3 set_pading_bottom justify-para">
        <li>NDIS Code of Conduct</li>
        <li>Child Safety Standards and National Principles for Child Safe Organisations</li>
        <li>Freedom from Abuse and Neglect </li>
        <li>Conflict of Interest Policy </li>
        <li>Incident Reporting Procedure</li>
        <li>ONCALL Policies and Procedures  </li>
    </ul>
</div>
<!-- <pagebreak /> -->
<?php } ?>
<?php if($type=='position_content'){ ?>
    <table class="pb-5">
        <tr>
            <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;" /> </td>
            <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Position Description</td>
        </tr>
    </table>

    <table class="table_2 f-13">
        <tr>
            <td class="font-weight-bold align-top" width="200px">Position Title:</td>
            <td class="font-weight-bold">DISABILITY and/or CYF SUPPORT WORKER</td>
        </tr>
        <tr>
            <td class="font-weight-bold align-top" width="200px">Employment Type:</td>
            <td>Casual Labour Hire</td>
        </tr>
        <tr>
            <td class="font-weight-bold align-top" width="200px">Key Relationships:</td>
            <td>
                <div><b>Internal: </b>Casual Staff Services, People & Culture, Pay Office.</div>
                <div><b>External:</b> Customer Organisation’ Staff and Clients/Participants.</div>
            </td>
        </tr>
    </table>

    <div class="font-weight-bold color_1">Your Purpose</div>
    <div class="f-13 pb-3 justify-para">
        As a Disability and/or Child, Youth and Family (CYF) Support Worker, your purpose is to share
ONCALLs’ passion for delivering quality, safe, client/participant-centered support to significantly
add value, improve and create independence in the lives of the most vulnerable people in our
community. ONCALL prides itself on being a person-centred organisation, committed to human
rights, equal citizenship and maximising personal choice and control for all Clients/participants, in
an environment free from abuse.
    </div>
    <div class="f-13 pb-3 justify-para">
        All Support Workers must undertake all duties with a positive attitude and be committed to
providing best possible care. Support is always provided in line with a Client/Participant’s individual
plan, and duties are undertaken in line with ONCALL’s Code of Conduct, policies and procedures,
DHHS Standards, Legislation and NDIS National Quality and Safeguarding Framework.
    </div>
    <div class="f-13 pb-3 justify-para">
        All Support Workers must take responsibility to ensure their mandatory screening and training
documents are always up to date and renewed before expiry.
    </div>
    <div class="f-13 pb-3 justify-para">
        <b>Disability Support Worker</b> will be providing support to people living with disabilities to assist them
enhance and increase independence in their lives and achieve their individual goals. You will be
supporting clients/participants within a permanent, transitional or short-term/emergency
accommodation setting and/or within client/participant’s private residence and supporting
clients/participants with community access/activities.
    </div>
    <div class="f-13 pb-3 justify-para">
        <b>Child, Youth and Family Support Worker </b>will be providing support to significantly improve the
lives of children and young people with a history of varying levels of trauma and neglect. You will
guide and support children and young people in their personal, social and educational
development to help them reach their full potential in society. You will be working in residential
homes, parental homes, and short-term/emergency placement models and/or undertake transport
shifts with children and young people.
    </div>

    <div class="font-weight-bold color_1">Key to ONCALL’s success is our ability to employ people like you!</div>
    <div class="f-13 pb-3 justify-para">
        <ul class="pl-3">
            <li>People who share ONCALL’s passion for working in this sector;</li>
            <li>People who want to make a real difference;</li>
            <li>People who are positive, take responsibility, capable of working calmly under pressure, team
players, show initiative and have strong organisational skills;</li>
            <li>People who are dedicated to creating an inclusive community for all Australians regardless of
their backgrounds or challenges;</li>
            <li>People who actively promote clients/participants’ rights and ensure the opportunity for
clients/participants to make their own choices and achieve their own goals;</li>
            <li>People who promote a <i><b>Zero Tolerance to Abuse,</b></i> ensuring each client/participant is free from
physical, sexual, verbal and emotional abuse and neglect;</li>
            <li>People who understand the importance of documentation and reporting;</li>
            
            <li>People who support a client/participant’s right to complain and understand it is our duty of care
to support clients/participants through the complaint process to help them to feel empowered
and safe to give feedback.</li>
        </ul>
    </div>
    <pagebreak />
    <div class="font-weight-bold color_1">ONCALL Support Worker ALWAYS takes accountability!</div>
    <div class="f-13 pb-3 justify-para">
        <ul class="pl-3">
            <li>To carry out all duties/tasks in line with the house and/or clients/participants plan, to the
highest standard and in keeping with the Code of Conduct, policies and procedures,
legislation and with an excellent customer service focus.</li>
            <li>To ensure you arrive at your shift on time, dressed in a neat and professional manner with a
smile.</li>
            <li>Remembering to bring with you, your ONCALL ID card, NDIS disability worker screening clearance / Yellow Card / Blue Card (as applicable), Queensland Driver’s License, timesheet book and mobile phone.</li>
            <li>Personal mobile phones are to remain in the office and only to be used in an emergency or
contacting ONCALL only.</li>
            <li>To perform all tasks and activities safely and in line with OH&S standards – we need to keep
YOU safe, as well as the clients/participants and your colleagues.</li>
            <li>EVERY shift read the house diary, case notes and communication book and/or casual folder.
This is important because you need all the information possible to ensure you know exactly
what to do and what to expect to fulfil the needs/wants of the client/participant.</li>
            <li>Communicate in a professional, friendly manner at all times when dealing with
clients/participants, their families, community agencies, case managers, professionals, other
supports, staff and your ONCALL colleagues.</li>
            <li>Always attending inductions/trainings/meetings as requested, to ensure you are up to date,
fully ready and across all changes and support requirements.</li>
            <li>Remember to submit timesheets on time each week (so you get paid).</li>
            <li>Ensuring your availability is regularly updated.
            </li>
        </ul>
    </div>

    <div class="font-weight-bold color_1">Who to call if you need support?</div>
    <div class="f-13 justify-para">
        <ul class="pl-3 set_pading_bottom">
            <li>If working within an ONCALL run service (e.g. Accommodation Services Disability or OOHC
or NDIS) – if your house supervisor is not available/on shift, then always remember to call
ONCALL directly (03) 9896 2468 and speak to your Consultant or Coordinator or call the
ONCALL After Hours.</li>
            <li>If you are working for one of ONCALL’s Customer Organisation through Casual Staff Services
– call that Customer Organisations’ support number or their After Hours in the first instance
(the number will be up in the staff office area). Remember if in doubt, you can always call
ONCALL for support.</li>
        </ul>
    </div>

    <div class="font-weight-bold color_1">Where can I find ONCALL’s Policies/Procedures?</div>
    <div class="f-13 pb-3 justify-para">
        <ul class="pl-3">
            <li>You can access our policies on our website by clicking the ‘Staff Policies’ link at the bottom
corner of every page. The page is protected, so simply type in the password to get access.
Alternatively, copy the following web address into your browser
                <!-- <div> -->
                <<a href="https://www.oncall.com.au/staff-policies/">https://www.oncall.com.au/staff-policies/</a>> .
                <!-- </div> -->
                </li>
            </ul>
        </div>

        <pagebreak />
        <div class="font-weight-bold color_1">What an ONCALL Support Worker’s Job Looks Like!</div>



        <TABLE border="1" cellSpacing="0" cellPadding="5px" class="table_3 justify-para">
            <TR>
                <TH colspan="2">Key Duties/Responsibilities – you will do most or all of these!</TH>
                <TH class="w-30">Measures/KPIs to be
                    achieved
                </TH>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top w-20">Clients/
                    participants Goal
                    Achievement
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD> Support clients/participants to achieve their goals. Goals can include but are not limited to developing social confidence, joining community groups, gaining more independence e.g. learning or participating in their own cooking/shopping, maintaining/improving health, attending school or day programs etc. Remember to record/report progress towards goals as
required.
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Goals outlined in client/participant support plans successfully achieved and documented.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Shift
                    Commencement
                & Completion</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure you receive and/or provide adequate handover when starting and finishing a shift. In order to do this, make sure all relevant documentation read/complete.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Remember to get your timesheet signed by your Team Leader/client/participant.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Take personal responsibility to provide or receive all relevant information during handover.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Personal Care
                (Disability Only)</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Showering, dressing, toileting, personal
                                hygiene, etc
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Personal Care delivered in a way which protects privacy and promotes
dignity.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">In Home Support
                (CYF/Disability)</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Support children/young people to remain in the family home.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Support families to implement routine/boundaries within their own home.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Support provided in a way which promotes stability to both the client/participant
and family.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Administration of Medication</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure you read and understand each client/participant’s medication profiles before administrating medication. When medication is administered, make sure that the medication treatment sheet is documented correctly. It is also important to document if medication is refused by the Client/Participant– this will also involve an incident report.
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure all PRN (pro rẽ nata - Latin for as required/needed) is approved by the line manager before administration and documentation is completed. Also remember to update case notes if PRN is administered.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Administration, and documentation of Medication is in line with ONCALL’s Medication Policy.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Administration and documentation is in line with Customer Organisations procedure.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">House Cleanliness/ Food Safety & Infection control</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Undertake all tasks as required, these may include shopping, cooking, maintenance and cleaning.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>When working in a residential setting, ensure internal/external residence including common areas remain presentable and clean at all times.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure food handling standards and general hygiene is high and in line with food safety and infection control procedures.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure tasks and menu plans are followed.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>House cleaning, food and infection control duties carried out with diligence and due care for personal safety and the safety of others.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Community Access & Transport Shifts</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Support clients/participants to access and experience the community as required. This may include taking clients/participants shopping, to the movies, to a sporting game, or going out for lunch etc.
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>When transporting clients/participants in your personal/company vehicle, you must always carry your valid Queensland  Driver’s licence, treat the vehicle with respect, drive safely, adhering to the Queensland Road Rules.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>If using your own vehicle to transport clients, you must ensure your vehicle is registered, roadworthy, insured and serviced in line with ONCALL’s Use of Private Vehicles Policy.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Client/participant confidence and learning increased within the community.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Clients/participants safely transported to their destination.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
        </TABLE>




        <TABLE border="1" cellSpacing="0" cellPadding="5px" class="table_3 mt-3">
            <TR>
                <TH colspan="2">Documentation/Reporting</TH>
                <TH class="w-30">Measures/KPIs to be
                    achieved
                </TH>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top w-20">Documentation
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Take personal responsibility to ensure you always undertake all administrative and documentation activities to a high standard.                   
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure all documentation, which includes, but is not limited to: communication books, diaries, medication folders, financial, car log, active night sheets, etc, are completed and up to date as required at the end of each and every shift.                 
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Ensure you review relevant house folders (as applicable) eg Casual Folder, Individual Lifestyle Plans, Comprehensive Health Assessment Plans (CHAPS) and Behaviour Support Plans (BSP), Looking after Children (LAC), House Diary, Communication Book, to ensure you understand fully the Client/Participant/house requirements and needs and a consistent approach is taken by all and in line with care team/behaviour
specialists etc requirements.                 
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Records/Documentation (hard /electronic copies) completed professionally and to a high standard (as required) before leaving each shift in accordance with ONCALL’s policies and procedures or the customer organisations requirements.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Incident
                    Reporting
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD> Follow ONCALL’s Incident Reporting Policy and Procedures and ensure that reporting guidelines are followed, and mandatory reporting timeframes met.                  
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>When working through Casual Staff Services you need to report the incident as directed by the Customer Organisation. You should also advise ONCALL and report through OIMs.                  
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>All staff injuries should be reported through OIMs.                                     
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>See the link below to access ONCALL’s OIMS/CIMS incident report forms</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>< <a href="https://www.oncall.com.au/incident-reporting/">https://www.oncall.com.au/incident-reporting/</a>>                 
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD> All incidents are managed effectively at the time, ensuring procedures are followed with safety of client/ participant and self/staff is first priority.                 
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>All incidents are reported within relevant time frames via the correct channel (CIMS/OIMS).                  
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>All incidents are documented to a high standard.
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Occupational
                    Health, Safety
                    and Wellbeing
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Take personal responsibility to undertake all activities in line with safe working practices in accordance with OH&S legislation.
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Be on the lookout for potential hazards. Report all hazards, incidents, injuries and
near misses to your manager/ONCALL.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Comply with all ONCALL’s OH&S policies, protocols and safe work procedures and legislation.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Equipment
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Always use designated equipment if required,
e.g. personal protection equipment or manual handling/lifting equipment. Never take risks or shortcuts that will put your own safety at risk.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Work, Health and Safety instructions/procedures are followed, and all injuries/hazards
reported.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
        </TABLE>
        <pagebreak />
        <div class="font-weight-bold color_1 mt-3">Mandatory Requirements for all Support Workers</div>
        <TABLE border="1" cellSpacing="0" cellPadding="5px" class="table_3 justify-para">
            <TR>
                <TH class="w-20">COMPONENT</TH>
                <TH>Disability Support Worker</TH>
                <TH class="w-30">Child, Youth and Family
                    Support Worker
                </TH>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Mandatory
                Requirements</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>NDIS worker screening clearance (5 yearly renewal) / Queensland Disability worker screening clearance (3 yearly renewal)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Blue Card (2 yearly renewal)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>International police check (if applicable)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Right to Work Verification</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>First Aid level 2 (3 yearly)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>CPR (annual)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Administration of Medication</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Manual Handling (DSW)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Fire Safety Training</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Valid Driver’s Licence (if required) (yearly renewal)</TD>
                        </TR>
                        <!-- <TR>
                            <TD class="dot_custom">•</TD>
                            <TD></TD>
                        </TR> -->
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>NDIS Worker Orientation Module</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Blue Card (2 Yearly Renewal)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>NDIS worker screening clearance ( 5 yearly renewal) / Queensland Disability worker screening clearance / Yellow Card</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>International police check (if applicable)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Right to Work
                                Verification
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>First Aid level 2 (3
                                yearly)
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>CPR (annual</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Administration of
                            Medication</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Manual Handling</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Fire Safety Training</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Current Queensland Driver's
                                Licence
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>NDIS Worker
                            Orientation Module</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
            <TR>
                <TD class="font-weight-bold align-top">Qualifications</TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Certificate IV Disability or relevant qualification and equivalent experience and/or successful completion of NDIS Job Ready Program.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4 justify-para" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Certificate IV in Child Youth Family Intervention, or a recognised relevant qualification and the four mandatory top up
units.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
        </TABLE>
        <div class="f-13 pb-3 justify-para pt-3">
            ALL ONCALL employees are expected to take responsibility for keeping mandatory training/documents current and up to date and are required to send new certificates to <a href="status@oncall.com.au">status@oncall.com.au</a> prior to expiry date (we send you reminders). Where mandatory requirement expires, you will be considered non-compliant and unable to work.
        </div>
        <pagebreak />       
        <div class="f-13 pb-3 justify-para">
            <div class="font-weight-bold">
                Declaration
            </div>
        </div>
        <div class="f-13 pb-3 justify-para">
            <div class="font-weight-bold">
                I hereby declare:
            </div>
            <ul class="font-weight-bold">
                <li>I have read, understood and agree to abide by the above Contract, ONCALL Code of Conduct and all Policies and Procedures.</li>
                <li>I have read and understand the requirements and expectations of the above Position Description and agree that I have the ability to fulfil the inherent physical requirements of the position and accept my role in fulfilling the Key Duties/Accountabilities and meeting the KPI’s set out herewith. I understand that the information and statements in this position description are intended to reflect a general overview of the responsibilities and are not to be interpreted as being all-inclusive.</li>
                <li>I agree to abide by the DHHS Code of Conduct for Disability Workers</li>
                <li>I agree to abide by the NDIS Code of Conduct.</li>
            </ul>
        </div>

        <div class="f-13 pb-3 justify-para w-100 pt-3">
            <div class="w-50 float-left pt-4">
                <div class="w-75 pt-1" style="margin-left:-5px">
                    <dottab />
                </div>
                Signature of Employee
            </div>
            <div class="w-50">
                <?php echo $dateData;?>
                <div class="w-75" style="margin-left:-5px">
                    <dottab />
                </div>
                Date
            </div>
        </div>

        <div class="f-13 pb-3 justify-para w-100 pt-5">
            <div class="w-50 float-left">
                <?php echo $fullName;?>
                <div class="w-75 pt-1" style="margin-left:-5px">
                    <dottab />
                </div>
                Employee name (please print)
            </div>
        </div>

<!-- last -->
<pagebreak />       

    <div class="header_image">
        <table class="header-table">
        <tr>
            <td align="right" class="pt-5 pr-5">
                <p class="f-22 f-white">COVID-19 Vaccine Statement</p>
            </td>
        </tr>
        <tr>
            <td align="right" class="pt-3 pr-5">
                <p class="f-12 f-white pt-2"> version: 5&#x7c; Draft</p>
            </td>
        </tr>
    </table>
    </div>
    
<div>
    <div colspan="2"><?php echo $dateData;?></div>
    <div class="f-13 pb-3 justify-para">Dear: <?php echo $firstName;?></div>
    <div class="f-13 pb-3 justify-para">
        ONCALL is very serious about the health and well being of our staff, and all the clients and customers we support every day.
    </div>
    <div class="f-13 pb-3 justify-para"><span class="font-weight-bold">You must respond to this request</span> to provide ONCALL with up-to-date information for your records. </div>
    <div class="f-13 pb-3 justify-para">
        As a key provider of support services to the disability sector, ONCALL expects all employees will be vaccinated against COVID-19 (except in the exceptional circumstances, where a person cannot be vaccinated for  genuine  medical  reasons).Vaccination  is  a  necessary  preventative  measure  to managethe health and safety risks to our vulnerable clients and customers, and our employees.ONCALL has already received requests from our partner providers and clients for vaccinated staff only.  This is a very important issue to keep you safe, and make sure you can keep working.
    </div>
    <div class="f-13 pb-3 justify-para font-weight-bold">Why are we seeking information about your vaccination status?</div>
    <div class="f-13 pb-3 justify-para">To  help  ONCALL  manage  customer  requests for  vaccinated  support  workers,we request  your consent to record your COVID-19 vaccination status in our system.</div>
    <div class="f-13 pb-3 justify-para">This record will  be collected, used  and  disclosed by ONCALL for  the  purposes of  managing health and   safety   risks,   responding   to requests   from   partner   providers   and   clients(or their representatives), or where required or authorised by law (such as a public health order or direction).</div>
    <div class="f-13 pb-3 justify-para">If  you  respond  that  you  are vaccinated,we may be  in  contact  to  seek  further  information  to complete the record.</div>
    <div class="f-13 pb-3 justify-para font-weight-bold">Who will my vaccination status be disclosed to?</div>
    <div class="f-13 pb-3 justify-para">You consent to us providing your  vaccination  status  to  partner  providers  and  clients(and  their representatives), for the purpose set out above.</div>
    <div class="f-13 pb-3 justify-para">If you do not consent, please let us know using this form.  This might prevent us from being able to offer work where there is a requirement to send records to partner providers or clients.</div>
    <div class="f-13 pb-3 justify-para">Except where required or authorised by law, information will not be disclosed by ONCALL to anyone else without your consent.</div>
    <div class="f-13 pb-3 justify-para font-weight-bold">Further information</div>
    <div class="f-13 pb-3 justify-para">Your response will be recorded,used and kept secure in accordance with the Privacy Act 1988 and other  applicable  privacy  laws.  You  can  ask  for  further  information,  view  your  vaccination status record or request your record be updated at any time.</div>
    <div class="f-13 pb-4">See our privacy policy at <a href="https://www.oncall.com.au/privacy-policy/">https://www.oncall.com.au/privacy-policy/</a> for details about how we use, disclose and protect your records and other information.  The privacy policy also tells you how you can  make a  complaint and has  contact details  you  can use to ask for accessor  correction  of your information.  </div>
    <div class="f-15 pb-3">Thank you</div>
    <div class="f-15 pb-3">ONCALL Group Australia</div>

</div>

<pagebreak /> 

<table class="pb-4">
    <tr>
        <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;" /> </td>
        <td style="font-size:22px; padding-left:50px" class="font-weight-bold"></td>
    </tr>
</table>

<div>
    <div class="f-16 pb-3 font-weight-bold">Vaccinated</div>
    <div class="f-14 pb-3 font-weight-bold">I have received <span class="font-weight-bold"> 1 dose </span>of a COVID-19 vaccine</div>
    <div class="f-14 pb-3">
        <div class="f-14 pb-3">
            <input type="checkbox" checked /><span class="pl-2">    Sent evidence to ONCALL previously  </span> &nbsp;&nbsp;&nbsp;
        </div>
        <div class="f-14 pb-3">
            <input type="checkbox" checked /><span class="pl-2">    Not sent to ONCALL</span>
        </div>
    </div>
    <div class="f-14 pb-3 font-weight-bold">I have received <span class="font-weight-bold"> 2 doses </span>of a COVID-19 vaccine</div>
    <div class="f-14 pb-3">
        <div class="f-14 pb-3">
            <input type="checkbox" checked /><span class="pl-2">    Sent evidence to ONCALL previously  </span> &nbsp;&nbsp;&nbsp;
        </div>
        <div class="f-14 pb-3">
            <input type="checkbox" checked /><span class="pl-2">    Not sent to ONCALL</span>
        </div>
    </div>
    <div class="f-14 pb-3 font-weight-bold">I have received <span class="font-weight-bold"> Booster shot </span>of a COVID-19 vaccine</div>
    <div class="f-14 pb-3">
        <div class="f-14 pb-3">
            <input type="checkbox" checked /><span class="pl-2">    Sent evidence to ONCALL previously  </span> &nbsp;&nbsp;&nbsp;
        </div>
        <div class="f-14 pb-3">
            <input type="checkbox" checked /><span class="pl-2">    Not sent to ONCALL</span>
        </div>
    </div>

    <div class="f-16 pt-3 pb-3 font-weight-bold">Not Vaccinated</div>
    <div class="f-14 pb-3"><input type="checkbox" checked />  I have made an appointment to receive the COVID-19 vaccine</div>
    <div class="f-14 pb-3"><input type="checkbox" checked />  I have a medical condition and cannot be vaccinated</div>
    <div class="f-14 pb-3"><input type="checkbox" checked />  I would prefer not to say</div>

    <div class="f-16 pt-3 pb-3 font-weight-bold">Consent</div>
    <div class="f-14 pb-3"><input type="checkbox" checked /><span class="pl-3">    I consent to ONCALL providing my vaccination status to partner providers and clients</span></div>
    <div class="f-14 pb-4"><input type="checkbox" checked /><span class="pl-3">    I do not consent to ONCALL providing my vaccination status to partner providers and clients</span></div>
    

    <div>
        <div class="float-left pt-4 pb-4">
            <div>
                <b>Signed:</b>
            </div>
            <div style="padding-left:70px; width:60%;">
                <dottab outdent="2em" />
            </div>
        </div>
        <div class="pt-1 pb-4">
            <b>Name:</b>
            <span><?php echo $fullName;?></span>
            <div>
                <div class="w-75" style="padding-left:30px;">
                </div>
            </div>
        </div>
        
        <div class="pt-1 pb-4">
            <b>Date:</b>
            <span><?php echo $dateData;?></span>
            <div>
                <div class="w-75" style="padding-left:30px;">
                </div>
            </div>
        </div>
        
    </div>

</div>
<!-- last -->
<!-- <div class="f-13 pb-3 justify-para font-weight-bold">
<i>Please return a signed copy of this document to ONCALL’s Human Resources Manager or
email to</i> hroga@oncall.com.au
</div> -->
<?php } ?>
