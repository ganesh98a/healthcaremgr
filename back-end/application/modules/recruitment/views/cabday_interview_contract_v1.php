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
        <div class="f-13"><?php echo isset($complete_data['street_address']) ? $complete_data['street_address']:''?></div>
        <div class="f-13 pb-3"><?php echo isset($complete_data['street_address_other']) ? $complete_data['street_address_other']:''?></div>
        <div class="f-15 pb-4 font-weight-bold">Strictly Private and Confidential</div>

        <div class="f-13 pb-3">Dear <?php echo $firstName;?>,</div>
        <div class="f-15 pb-4 font-weight-bold">Offer of Casual Employment</div>

        <div class="f-13 pb-3">
            We would like to take this opportunity to offer you casual employment with ONCALL Group Australia
            Pty Ltd (ACN 633 010 330) <b>(ONCALL)</b> in the position of Labour-Hire Worker <b>(Position)</b>.
        </div>
        <div class="f-13 pb-3">
            ONCALL will endeavour to make our relationship a success and assign work to you. This will depend
            on your availability, type of work, location and your shift preferences. It will also depend on customer
            feedback and choice. If the response from partner organisations or clients is negative, the clients are
            not bound to provide further work and ONCALL is not bound to allocate further work to you.
        </div>
        <div class="f-13 pb-3">
            An agreement to perform work is only concluded when you accede to a request to attend a particular
            work site on a given day. At that time, you assume an obligation to attend the site to perform such work
            as may be allocated to you. You are also required to respect and comply with the working conditions,
            policies and procedures of the work site and ONCALL throughout your shift, although these policies and
            procedures are not incorporated into this letter or form part of your employment contract.
        </div>
        <div class="f-13 pb-3">
            When you are assigned to work for ONCALL with a partner organisation or client, it must be in
            accordance with any position description relevant to your engagement and on the terms and conditions
            set out below.
        </div>
        <div class="pb-3">
            <div class="f-13 font-weight-bold">ELIGIBILITY TO WORK WITH ONCALL</div>
            <div class="f-13">
                Your employment is conditional on you satisfying the conditions set out below. If your circumstances change and you are no longer able to satisfy any of these conditions, you must immediately notify ONCALL. Failure to comply with your obligations under this clause may result in your immediate dismissal.
            </div>
        </div>
        <div class="f-13 pb-3"><i>Police check</i></div>
        <div class="f-13 pb-3">
            If you have not already provided ONCALL with evidence of a successful police check clearance, you must do so prior to your first engagement. You agree to renew your police check prior to its expiration. You understand and accept that if the police check obligation is not met, ONCALL will not be able to place you with any partner organisation or client.
        </div>
        <div class="f-13 pb-3">
           You agree that you will disclose any criminal charges or convictions to ONCALL as soon as practicable (where relevant in accordance with applicable legislation). You consent to ONCALL disclosing such information to its clients with whom you have worked where ONCALL is required to do so in accordance with applicable law or any other relevant obligations.
       </div>
       <div class="f-13 pb-3">
        <i>International police check</i>
    </div>
    <div class="f-13 pb-3">
        If you were a citizen or a permanent resident of a country other than Australia at any time since turning 16 years of age - at the recruitment stage - you should have submitted a statutory declaration which testifies that you have no existing criminal record in that country. ONCALL requires you to provide it with a copy of that statutory declaration.
    </div>
    <pagebreak />
    <div class="f-13 pb-3">
        Additionally, if you resided in an overseas country for 12 months or more in the last ten years you must contact the relevant overseas police force to obtain a criminal or police record check. If you have copies of the relevant police clearance which may have formed part of your visa application, these documents should have been submitted to ONCALL at the recruitment stage. This is mandatory for ONCALL’s purposes in satisfying Government policy and guidelines and our contractual obligation to our clients and partner organisations.
    </div>
    <div class="f-13 pb-3">
        <i>Working with Children Check (WWCC)</i>
    </div>
    <div class="f-13 pb-3">
        If you have not already obtained a WWCC, you may be required to do so before commencing an assignment with any of ONCALL’s clients. You agree to renew your WWCC prior to its expiration (usually every five years). You must immediately notify ONCALL if your WWCC expires.
    </div>
    <div class="f-13 pb-3">
        <i>Support Worker Screening Checks</i>
    </div>
    <div class="f-13 pb-3">
        All Labour Hire Workers must consent to having their name checked against the Department of Health and Humans Services’ (DHHS) Disability Worker Exclusion List (DWEL) that is part of the Disability Worker Exclusion Scheme (DWES) and the NDIS Quality & Safeguards Commission’s Worker Screening Check national database, and that this information will be provided to ONCALL’s clients as necessary.
    </div>
    <div class="f-13 pb-3">
        You agree to familiarise yourself with DWES by visiting the Department of Health and Human Services (DHHS) website on:<a href="https://providers.dhhs.vic.gov.au/disability-worker-exclusion-scheme">https://providers.dhhs.vic.gov.au/disability-worker-exclusion-scheme</a>
    </div>
    <div class="f-13 pb-3">
        You agree to familiarise yourself with the NDIS Worker Screening Requirements by visiting the NDIS Commission Website on:<a href="https://www.ndiscommission.gov.au/providers/worker-screening">https://www.ndiscommission.gov.au/providers/worker-screening</a>
    </div>
    <div class="f-13 pb-3">
        By signing this contract, it shall be deemed that you understand and comply with the requirements of this sub-clause and that you offer your consent to participate in all required worker screening checks. You acknowledge that you are familiar with the requirements of these screening checks and you understand that if your name is added to the DWEL or if you are listed as an excluded person on the NDIS National Database for any reason (which may include a work related incident involving a client) your employment with ONCALL will immediately terminate.
    </div>
    <div class="f-13 pb-3">
        <i>Driver’s Licence</i>
    </div>

    <div class="f-13 pb-3">
        <div>If you are engaged in Child Youth & Family Support or Transport Shifts, you will require:</div>
        <div>
            <ul class="pl-3 mt-0">
                <li>a current Victorian or other eligible driver’s licence;</li>
                <li>a registered, roadworthy vehicle; and</li>
                <li>comprehensive motor vehicle insurance that takes into account your usage of the vehicle for work purposes.
                </li>
            </ul>
        </div>
    </div>


    <div class="f-13 pb-3">
        If you are engaged in Disability Support, a Victorian or other eligible driver’s licence is required for residential and NDIS community access shifts, and while not mandatory is strongly encouraged for all Disability Support.
    </div>
    <div class="f-13 pb-3">
        If requested, you must provide ONCALL with a copy of your driver’s licence or present it for verification by ONCALL or ONCALL Customer Organisation you are rostered to work at.
    </div>
    <div class="f-13 pb-3">
        <i>Right to work in Australia</i>
    </div>
    <div class="f-13 pb-3">
        Before commencement of your employment, you may have provided either your Birth Certificate (if an Australian citizen) or passport and visa/residency status within Australia (if not an Australian citizen). If you are not an Australian citizen and during your employment, your visa status changes and you are no longer entitled to work in Australia legally, ONCALL will terminate your employment with no notice.
    </div>
    
    <div class="f-13 pb-3">
        <div class="font-weight-bold ">CASUAL RATE OF PAY</div>
        <div class="f-13">
            Your rate of pay will be determined by reference to the nature of your placement with ONCALL. You will be advised of the applicable rate of pay prior to commencing your shift or assignment (where appropriate) <b>(Wages)</b>.
        </div>
    </div>

    <div class="f-13 pb-3">
        Your Wages, less deduction of applicable taxes, will be paid into your nominated financial institution account weekly.
    </div>
    
    <div class="f-13 pb-3">
        ONCALL will additionally make statutory superannuation contributions as required under the Superannuation Guarantee Charge Act 1992 (Cth) in order to avoid a charge, currently 9.5% of your ordinary time earnings.
    </div>
    <pagebreak />
    <div class="f-13 pb-3">
        <div class="font-weight-bold ">GENERAL</div>
        <div>Your employment may be covered by an industrial instrument(s) from time to time, including the Social, Community, Home Care and Disability Services Industry Award 2010 (Award) (available at https://www.fwc.gov.au/documents/documents/modern_awards/award/ma000100/default.htm). For the avoidance of doubt, any industrial instrument applicable to your employment is regulated by legislation and does not form part of this agreement.
        </div>
    </div>
    <div class="f-13 pb-3">
        The payments and benefits set out in this agreement are paid in full satisfaction of all payment obligations ONCALL has to you in respect of your employment. This includes (but is not limited to) benefits that may be payable to you under any legislation, award, enterprise agreement or other industrial instrument and for any reasonable additional hours necessary in the performance of your duties. Such benefits may include penalty rates (including public holiday rates), overtime, loadings (including annual leave loading), allowances or other monetary benefits.
    </div>
    <div class="f-13 pb-3">
        Where you are entitled to a payment or benefit that is not set out in this agreement, you agree that ONCALL may set off any amount paid to you under this agreement in satisfaction of that payment or benefit.
    </div>
    <div class="f-13 pb-3">
        As you are engaged on a casual basis, your Wages are inclusive of a 25% casual loading, which is paid instead of (and which may otherwise be applied to offset any) entitlements to paid leave and other matters from which casuals are excluded (including redundancy pay and notice of termination).
    </div>
    <div class="f-13 pb-3">
        <div class="font-weight-bold ">NATURE OF ENGAGEMENT</div>
        <div>
            ONCALL may require, and you may agree to a placement with a client of ONCALL. You will be required to work at various locations at the direction of the client for the duration of each placement. Your hours of work will be dependent on your assignment.
        </div>
    </div>
    <div class="f-13 pb-3">
        Your engagement to perform your duties will conclude at the end of each day on which you are given work. Both you and ONCALL agree and acknowledge that because the nature of labour hire work can be irregular and uncertain, each offer of engagement can be accepted or rejected. Each instance of engagement is a new contract on these terms.
    </div>
    <div class="f-13 pb-3">
        <div class="font-weight-bold ">LEAVE</div>
        <div>
            As a casual employee, you are not entitled to annual leave or to paid personal/carer's leave.
        </div>
    </div>
    <div class="f-13 pb-3">
        <div class="font-weight-bold ">DUTIES</div>
        <div>
            Your duties will be as per your Position Description.
        </div>
    </div>
    <div class="f-13 pb-3">
        Following consultation with you, ONCALL may vary your position, duties, reporting lines and base location where such changes are consistent with your skills and knowledge. The terms and conditions of this agreement will continue to apply to your employment with ONCALL despite any changes from time to time to your position, duties and responsibilities, remuneration, working hours or employment location, unless agreed in writing by both parties.
    </div>
    <pagebreak />
    <div class="f-13 pb-3">
        <div class="font-weight-bold ">CONFIDENTIAL INFORMATION</div>
        <div>
            <b>Confidential Information</b> means any information that is not in the public domain (otherwise than as result of an unauthorised disclosure of that information), however communicated or recorded, relating to ONCALL's and / or the Group’s business or affairs and includes, but is not limited to:
        </div>
    </div>
    <div class="f-13 pb-3">
        <table class="f-13">
            <tr>
                <td width="25" class="align-top">a)</td>
                <td>any information of a commercial, operational, technical or financial type;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">b)</td>
                <td>all information relating to any apparatus, process, training program, teaching method, formula or product, corporate opportunities, research, financial and sales data, pricing and trading terms, evaluations, opinions, interpretations, incentive payment bases, human resources and remuneration strategies and plans, acquisition prospects;</td>
            </tr>
            <tr>
                <td width="25" class="align-top">c)</td>
                <td>the identity of clients or suppliers or their requirements, the identity of key client contacts, client or supplier lists, sales and marketing and merchandising techniques, products (including source code), prospective names and marks and any trade secret; and
                </td>
            </tr>
            <tr>
                <td width="25" class="align-top">d)</td>
                <td>any information relating to ONCALL’s clients or the respective clients of ONCALL’s clients (End Clients), including their identities and any personal or sensitive information in relation to any End Clients.</td>
            </tr>
        </table>
    </div>

    <div class="f-13 pb-3">
        <div><i>Access and use<i>
            <div>
                You acknowledge that during your employment you will have access to and knowledge of Confidential Information. You must not use, or make a copy or other record of, Confidential Information for a purpose other than for the benefit of ONCALL and / or the Group.
            </div>
        </div>
    </div>
    <div class="f-13 pb-3">
        Additionally, you are required to enter into the Confidentiality Deed annexed to this agreement.
    </div>
    <div class="f-13 pb-3">
        <div><i>Disclosure</i></div>
        <div>
            You must not disclose Confidential Information and must use your best endeavours, including complying with all security measures used to safeguard Confidential Information, to prevent the disclosure of the Confidential Information to any person or entity unless:
        </div>
    </div>
    <div class="f-13 pb-3">
        <table class="f-13">
            <tr>
                <td width="25" class="align-top">a)</td>
                <td> disclosure is required or authorised in the legitimate performance of your duties;</td>
            </tr>
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
    <div class="f-13 pb-3">
       If you are required by law to disclose Confidential Information, where possible, you must prior to making the disclosure inform ONCALL of the requirement and co-operate with ONCALL, to the extent permissible at law, to minimise the disclosure.
   </div>
   <div class="f-13 pb-3">
    If you are uncertain as to whether certain information is Confidential Information, you will treat that information as Confidential Information unless you are advised otherwise in writing by ONCALL.
</div>
<div class="f-13 pb-3">
    <div><i>Notification and cooperation to protect information</i></div>
    You must immediately notify ONCALL of any actual or suspected unauthorised use, copying or disclosure of Confidential Information.
</div>
<div class="f-13 pb-3">
    You must comply with any reasonable steps required by ONCALL in order to protect its Confidential Information, including providing an appropriate statutory declaration on request.
</div>
<div class="f-13 pb-3">
    <div><i>Return and destruction</i></div>
    <div>Upon termination of your employment with ONCALL, or at any time at the request of ONCALL, you must immediately deliver to ONCALL all documents or other things in your possession, custody or control on which any Confidential Information is stored or recorded, whether in writing or in electronic or other form.</div>
</div>
<div class="f-13 pb-3">
    Alternatively, and only if requested by ONCALL, you must destroy the Confidential Information (in the case of data stored electronically or in any other form, by erasing it from the media on which it is stored such that it cannot be recovered or in any way reconstructed or reconstituted) and certify in writing to ONCALL that the Confidential Information, including all copies, has been destroyed.
</div>
<pagebreak />

<div class="f-13 pb-3">
    <div class="font-weight-bold">INTELLECTUAL PROPERTY RIGHTS</div>
    <div>You acknowledge that ONCALL owns all Intellectual Property Rights in any material created, generated or contributed to by you in connection with your employment.
    </div>
</div>

<div class="f-13 pb-3">
    You assign to ONCALL all existing and future Intellectual Property Rights in any material created, generated or contributed to by you in connection with your employment.
</div>

<div class="f-13 pb-3">
    You must do all things reasonably requested by ONCALL to enable ONCALL to perfect the assignment of the Intellectual Property Rights.
</div>

<div class="f-13 pb-3">
    You and ONCALL acknowledge that you may have moral rights within the meaning of the <i>Copyright Act
    1968</i> (Cth) in relation to the Intellectual Property. You genuinely consent to all acts or omissions, whether occurring before or after this consent is given, committed by ONCALL, its officers, licensees and successors in title, that may infringe any or all of your moral rights in relation to the Intellectual Property. This consent is irrevocable and does not terminate on termination of your employment or for any other reason.
</div>
<div class="f-13 pb-3">
    <b>Intellectual Property</b> includes but is not limited to: inventions, ideas know-how, discoveries and improvements whether patentable or unpatentable; trademarks, designs and copyrightable works.
</div>

<div class="f-13 pb-3">
    <b>Intellectual Property Rights</b> means all property rights in connection with Intellectual Property; any right to have Confidential Information kept confidential; and any application or right to apply for registration of any rights in connection with Intellectual Property.
</div>

<div class="f-13 pb-3">
    <div class="font-weight-bold">STANDARDS OF BEHAVIOUR</div>
    <div>You are required to make disclosures to ONCALL of any actual or potential conflict of interest between your personal affairs and your duties in your position. ONCALL may require you to resolve a conflict of interest by ensuring that your duties are paramount. Should you be unable or unwilling to resolve a conflict of interest, that will constitute a failure to carry out the duties of your position satisfactorily and ONCALL may terminate your contract of employment.
    </div>
</div>
<div class="f-13 pb-3">
    In the interest of health and safety, efficiency and harmony in the workplace and the public image of ONCALL, all employees must maintain a professional standard of behaviour. This includes but is not limited to:
</div>

<div class="f-13 pb-3">
    <ul class="pl-3 f-13">
        <li>Complying with ONCALL’s code of conduct as amended from time to time and any applicable industry codes of conduct;
        </li>
        <li>Dealing with both internal and external customers and your colleagues in a polite, helpful and considerate way;
        </li>
        <li>Being clean and tidy; and</li>
        <li>Performing all your duties promptly and efficiently.
        </li>
    </ul>
</div>
<div class="f-13 pb-3">
    A breach of these standards of behaviour by you can result in disciplinary action, which may include termination of your employment.
</div>
<div class="f-13 pb-3">
    If you engage in serious misconduct, ONCALL may terminate your employment immediately and cease obtaining services from you. Serious misconduct includes (but is not limited to):
</div>

<div class="f-13 pb-3">
    <table class="f-13">
        <tr>
            <td width="25" class="align-top">a)</td>
            <td>bullying, theft, fraud or assault;</td>
        </tr>
        <tr>
            <td width="25" class="align-top">b)</td>
            <td> drinking alcohol at work or arriving to a shift intoxicated or under the influence of non-prescribed drugs during working hours;</td>
        </tr>
        <tr>
            <td width="25" class="align-top">c)</td>
            <td>any conduct that causes imminent and serious risk to the reputation, viability or profitability of ONCALL’s business;</td>
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
            <td>violation of ONCALL's Occupational Health and Safety policies and procedures;
            </td>
        </tr>
        <tr>
            <td width="25" class="align-top">g)</td>
            <td>serious breach of duty of care, code of conduct or any governing legislation; or</td>
        </tr>
        <tr>
            <td width="25" class="align-top">h)</td>
            <td>inappropriate and unauthorised use of social media.</td>
        </tr>
    </table>
</div>
<pagebreak />

<div class="f-13 pb-3">
    It may be necessary for ONCALL to report any such conduct to an appropriate authority, including the Victorian Police, Victorian DHHS and the National Disability Insurance Agency.
</div>

<div class="f-13 pb-3">
    <div class="font-weight-bold">DIRECT ENGAGEMENT BY A CLIENT</div>
    <div>You recognise that ONCALL invests significant costs in your recruitment. You acknowledge that if you are offered or seek engagement with a client of ONCALL and you accept such engagement, the client must pay ONCALL a placement fee as determined by ONCALL.
    </div>
</div>

<div class="f-13 pb-3">
    You agree that if you are offered, or apply for, employment with a client of ONCALL, you will provide details to ONCALL in relation to such employment as soon as practicable.
</div>
<div class="f-13 pb-3">
    <div class="font-weight-bold">TERMINATION OF EMPLOYMENT</div>
    <div>As a casual employee, your employment with ONCALL comes to an end at the conclusion of each shift. ONCALL may also terminate your employment immediately, and prior to the conclusion of your shift, for serious misconduct.
    </div>
</div>

<div class="f-13 pb-3">Upon termination of your employment you authorise ONCALL to deduct from your termination monies, (excluding amounts ONCALL is not entitled by law to off–set) any sums due from you to ONCALL including without limitation, any overpayments, advances or loans made by ONCALL to you.
</div>
<div class="f-13 pb-3">
    <div class="font-weight-bold">DISPUTE RESOLUTION PROCEDURE</div>
    <div>In the event of a dispute or grievance arising concerning the contents of this agreement the parties agree to make every effort to resolve the dispute by consultation and negotiation. If the negotiation process is exhausted without the dispute being resolved, the parties may refer the matter to a mutually agreed mediator or conciliator for the purpose of resolving the dispute.
    </div>
</div>
<div class="f-13 pb-3">
    <div class="font-weight-bold">ACCURACY OF INFORMATION</div>
    <div>You warrant that all information you provided to ONCALL which led to your engagement including information relating to your qualifications and curriculum vitae is accurate in all respects and you have not misled or deceived ONCALL in any way in relation to the information provided.</div>
</div>
<div class="f-13 pb-3">
    You warrant that you have not omitted or failed to disclose any information to ONCALL, which you may reasonably consider to be relevant to your engagement under this Agreement.
</div>
<div class="f-13 pb-3">
    <div class="font-weight-bold">POLICIES AND PROCEDURES</div>
    <div>You are required to comply with the policies and procedures of ONCALL in place from time to time. ONCALL may create, amend, withdraw or replace its policies and procedures at its sole discretion.
    </div>
</div>
<div class="f-13 pb-3">
   It is a condition of your employment that you comply at all times with the obligations placed on you by all policies and procedures implemented by ONCALL. However, ONCALL’s policies and procedures do not form part of this Agreement nor do they have any contractual effect. Where ONCALL does not follow a policy or procedure, to the extent permitted by law, this will not constitute a breach of this Agreement.
</div>
<div class="f-13 pb-3">
    <div class="font-weight-bold">GENERAL PROVISIONS</div>
    <div>This Agreement represents the entire agreement, between ONCALL and you in relation to your engagement and it replaces and supersedes all previous agreements, terms and conditions of engagement, contracts, negotiations, understandings, or representations between ONCALL and you. This agreement may only be varied, amended or replaced by agreement in writing between ONCALL and you.</div>
</div>
<div class="f-13 pb-3">
    This Agreement shall be governed by and construed in accordance with the laws of the State of Victoria.
</div>
<div class="f-13 pb-3">
    In the event that any provision of this agreement is held unenforceable, such provision shall be severed and shall not affect the validity or enforceability of the remaining portions.
</div>
<pagebreak />
<div class="f-13 pb-3">
    <div class="font-weight-bold">ACCEPTANCE OF TERMS AND CONDITIONS</div>
    <div>I trust the terms and conditions in this contract are acceptable to you. Please sign where indicated below to confirm your acceptance.</div>
</div>
<div class="f-13 pb-5">
    Yours faithfully
</div>

<div class="f-13 pb-5">
    <div class="pb-2" style="border-bottom:1px solid #000">ONCALL Group Australia Pty Ltd</div>
    <div class="font-weight-bold pb-3 pt-1">ACCEPTANCE</div>
    <div>I, <?php echo $fullName;?> accept the terms and conditions contained in this casual employment contract.</div>
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
<div class="f-13 pb-5 pt-3">
    <i>(To confirm your acceptance of this position please sign and return one copy (keeping one for your
    records).</i>
</div>
<pagebreak />
<div class="f-13 pb-5 font-weight-bold">
    CONFIDENTIALITY DEED REGARDING CLIENT INFORMATION
</div>
<div class="f-13 pb-5">
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
                                <div class="f-13"><?php echo isset($complete_data['street_address']) ? $complete_data['street_address']:''?></div>
        <div class="f-13"><?php echo isset($complete_data['street_address_other']) ? $complete_data['street_address_other']:''?></div>
                                <div class="font-weight-bold">(Employee)</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="f-13 pb-5">
                    <div class="font-weight-bold">RECITALS</div>
                    <table class="f-13">
                        <tr>
                            <td class="align-top" width="25px">A.</td>
                            <td>ONCALL operates a business of providing specialised temporary labour hire <b>(Business)</b> to clients
                                in the disability and welfare support sectors <b>(Client Organisations)</b>.</td>
                            </tr>
                        </table>
                        <table class="f-13">
                            <tr>
                                <td class="align-top" width="25px">B.</td>
                                <td>ONCALL engages the Employee.</td>
                            </tr>
                        </table>
                        <table class="f-13">
                            <tr>
                                <td class="align-top" width="25px">C.</td>
                                <td>
                                    During the course of performing the Employee’s duties, the Employee will have contact with the Client Organisations and the Client Organisation's respective clients <b> (End Clients)</b>, the Employee may have access to or gain knowledge of all or part of the following confidential information:
                                </td>
                            </tr>
                        </table>
                        <table class="f-13 pl-4">
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
                    <div class="f-13 pb-2">
                        <div class="font-weight-bold">OPERATIVE TERMS</div>
                        <table class="f-13">
                            <tr>
                                <td class="align-top" width="20px">1.</td>
                                <td>1. The Employee:</td>
                            </tr>
                        </table>
                        <table class="pl-4 f-13">
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

                    <div class="f-13 pb-3">
                        <table class="f-13">
                            <tr>
                                <td class="align-top" width="20px">2.</td>
                                <td>The Employee agrees that he/she must not:</td>
                            </tr>
                        </table>
                        <table class="f-13 pl-4">
                            <tr>
                                <td class="align-top" width="20px">a)</td>
                                <td>
                                    use any or all of the Confidential Information for any purpose other than in the proper performance of his/her duties as an employee of ONCALL;
                                </td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">b)</td>
                                <td>divulge to any person all or any aspect of the Confidential Information otherwise than with the prior approval of:
                                    <table class="f-13 pl-4">
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
                                <td class="align-top" width="20px">c)</td>
                                <td>grant or permit any unauthorised person to have access to or possession of the Confidential Information; or</td>
                            </tr>
                            <tr>
                                <td class="align-top" width="20px">d)</td>
                                <td>Make any written notes, copy, reproduce, store, record, computerise, document or duplicate any part of the Confidential Information.</td>
                            </tr>
                        </table>
                    </div>
                    <pagebreak />

                    <div class="f-13 pb-3"> If the Employee is uncertain whether any information comprises part of the Confidential Information then the Employee must seek direction from ONCALL before divulging the information to any other person.</div>

                    <div class="f-13 pb-3">
                    This Deed will be construed in accordance with the laws of the State of Victoria. If a provision (or part of it) of this Deed is held to be unenforceable or invalid, it must be interpreted as narrowly as necessary to allow it to be enforceable and valid. If it cannot be so interpreted narrowly, then the provision (or part of it) must be severed from this Deed without affecting the validity and enforceability of the remaining provisions. </div>

                    <div class="f-13 pb-3 pt-3">
                        <div class="w-50 float-left">
                            <b>EXECUTED</b> as a deed for and on behalf of<br>
                            <b>ONCALL Group Australia Pty Ltd (ACN 633 010
                            330)</b> by its authorised representative in the
                            presence of:
                        </div>
                        <div class="w-50">
                            <div>)</div>
                            <div>)</div>
                            <div>
                            </div>
                        </div>
                    </div>

                    <div class="f-13 pb-3 pt-5 w-100">
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
                    <div class="f-13 pb-3 w-100 pt-3">
                        <div class="w-50 float-left">
                            <?php echo $fullName;?>
                            <div class="w-75 pt-1" style="margin-left:-5px"><dottab /></div>
                            Employee name (please print
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

                    <div class="font-weight-bold f-13 pb-3 text-center">PRE-EXISTING INJURY DECLARATION FORM</div>
                    <div class="f-13 pb-3 ">
                        In accordance with <i>The Workplace Injury Rehabilitation and Compensation Act 2013</i> <b>(WIRC Act)</b>, you are required to disclose any or all pre-existing injuries, illnesses or diseases <b>(Pre-Existing Conditions)</b>
                        ssuffered by you which could be accelerated, exacerbated, aggravated or caused to recur or deteriorate by you performing the responsibilities associated with the engagement for which you are applying with ONCALL Group Australia Pty Ltd (ACN 633 010 330) <b>(ONCALL)</b>.
                    </div>
                    <div class="f-13 pb-3 ">
                        In making this disclosure, please refer to the attached/included position description, which describes the nature of the engagement. It includes a list of responsibilities and physical demands associated with the engagement.
                    </div>
                    <div class="f-13 pb-3 ">
                        Please note that, if you fail to disclose this information or if you provide false and misleading information in relation to this issue, under the WIRC Act, you and your dependents may not be entitled to any form of workers’ compensation as a result of the recurrence, aggravation, acceleration, exacerbation or deterioration of a pre-existing condition arising out of, in the course of, or due to the nature of your engagement.
                    </div>
                    <div class="f-13 pb-3 ">
                        Please also note that the giving of the false information in relation to your application for engagement with ONCALL may constitute grounds for disciplinary action or dismissal.
                    </div>
                    <div style="border:1px solid #000" class="px-2 py-1 text-left font-weight-bold">WORKER DECLARATION</div>
                    <div class="f-13 pb-3 pt-3">
                        I, <?php echo $fullName;?> &nbsp;&nbsp;&nbsp;&nbsp; declare that:
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
                        </table>
                        <table class="pl-4">
                            <tr><td class="align-top"> <input type="checkbox" checked /></td> <td>I am aware of the below Pre-Existing Conditions which could be affected by the nature of my proposed employment with ONCALL.</td>
                            </tr>
                            <tr> <td class="align-top"> <input type="checkbox" checked /></td><td>I am not aware of any Pre-Existing Conditions which could be affected by the nature of my proposed employment with ONCALL.</td></tr>
                        </table>
                    </div>

                    <pagebreak />
                    <div class="f-13 pb-3 font-weight-bold">
                        Please provide details of all Pre-Existing Conditions:
                    </div>
                    <div class="f-13 pb-3 font-weight-bold">
                        <table class="blank_table" border="1" width="100%">
                           <tr><td height="300px"><div ></div></td></tr>
                       </table>
                   </div>

                   <div class="f-13 pb-3 font-weight-bold">
                    I acknowledge and declare that the information provided in this form is true and correct in every particular, and that ONCALL is relying on the information that I have declared above.
                </div>

                <div class="f-13 pb-3 w-100 pt-3">
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

                <div class="f-13 pb-3 w-100 pt-5">
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
            <div class="f-13 pb-3 pt-2">
                This Code of Conduct is based on ONCALL’s values of:
            </div>
            <table width="90%" align="center" border="1" class="table_1 f-13">
                <tr>
                    <td class="font-weight-bold align-top">Integrity</td>
                    <td>All ONCALL staff will act ethically, with integrity, honesty and transparency, and steadfastly adhere to high moral principles and professional standards at all times.
                    </td>
                </tr>
                <tr>
                    <td class="font-weight-bold align-top">Respect</td>
                    <td>All ONCALL Staff will show consideration and treat all people and property with respect. Positively accept and welcome diversity in all people and cultures regardless of any differences, including disability, background, race, religion, gender, sexual identity or age.</td>
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
        <ul class="pl-3 f-13 set_pading_bottom">
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
    <ul class="pl-3 f-13 set_pading_bottom">
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
    <ul class="pl-3 f-13 set_pading_bottom">
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
   <ul class="pl-3 f-13 set_pading_bottom">
    <li>Notify your manager of any loss, suspension of or change to a registration, accreditation, license or other qualification that affects your ability to meet relevant essential requirements or to perform your duties.</li>
    <li>Ensure you are aware of and comply with all policies, procedures and legislation relevant to the performance of your duties.</li>
    <li>Do not refuse to follow a lawful or reasonable management direction or instruction.
    </li>
</ul>
<div class="f-13 pb-3 font-weight-bold">Teamwork</div>
<div class="f-13 pb-3 ">
    All employees should work cooperatively and effectively with colleagues or customer organisations to ensure the best possible support is provided – showing Reliability, Integrity, Responsibility, Attitude and Initiative.

    <ul class="pl-3 set_pading_bottom">
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

<div class="f-13 pb-3  font-weight-bold">Leadership</div>
<div class="f-13">
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
<div class="f-13">
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
<div class="f-13">
    <ul class="pl-3 set_pading_bottom">
        <li>As an organisation and as individuals, we have a responsibility to protect and advocate for our clients who are vulnerable.</li>
        <li>Encourage people with a disability and children to ‘have a say’ and participate in all relevant organisational activities where possible, especially on issues that are important to them.</li>
        <li>Seek advice from a manager if you are unclear on the correct procedures when advocating on behalf of a person you support.</li>
        <li>Understand the boundaries within the scope of your position
        </li>
    </ul>
</div>

<div class="f-13 font-weight-bold">Professional Boundaries</div>
<div class="f-13">
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
    </ul>
</div>
<pagebreak />
<table>
    <tr>
       <td><img src="<?php echo $logoUrl; ?>" style="height:70px; margin-left:-15px;"/> </td>
       <td style="font-size:22px; padding-left:50px" class="font-weight-bold">Staff Code of Conduct</td>
   </tr>
</table>
<div class="f-13">
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
    <ol style="list-style-type: circle;">
    <li> Do not arrive to work under the influence of alcohol or illicit drugs or undertake any duties in an inebriated or drug affected state.</li>
    <li> Do not bring into the workplace any alcohol, drugs or any other illicit substances.</li>
    <li> Responsible drinking of alcohol at ONCALL social functions as authorised by Management is permitted in accordance with the Social Functions Policy.</li>
</ol>
</li>

<li>Do not possess on the premises or in any workplace (including community-based support) any unauthorised weapon(s) or article(s) intended for use as such, whether for offensive or defensive purposes.
</li>
<li>Have any online, phone, direct or any other contact with a client or their family outside professional duties.</li>
</ul>
</div>

<div class="f-13 font-weight-bold">No employee of ONCALL shall wilfully</div>
<div class="f-13">
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
<div class="f-13">
    <ul class="pl-3 set_pading_bottom">

        <li> Disclose any information, or supply any document concerning ONCALL’s business, current or former stakeholders or clients or the content of ONCALL’s contracts or procedures, without the express written permission of your manager, unless required to do so by law.</li>
        <li> When leaving the employment of ONCALL you should not use confidential information obtained during your employment to advantage a prospective employer or disadvantage ONCALL in commercial or other relationships with your prospective employer.</li>
    </ul>
</div>

<div class="f-13 font-weight-bold">Misconduct</div>
<div class="f-13">
    <ul class="pl-3 set_pading_bottom">
        <li>Misconduct allegations are the most serious of complaints made and may result in the Disciplinary policy and procedure being implemented by ONCALL management.</li>
        <li>ONCALL may decide to stand down an employee while an investigation takes place. This means that the employee would be instructed not to come to work while the investigation is carried out, but you would be available for the purposes of the investigation.</li>
        <li>If you are stood down, you are not permitted to have contact with other employees. This does not imply that ONCALL believes you to be guilty, it is a precaution to protect the integrity of the investigation.</li>
    </ul>
</div>
</div>
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
                <div><b>Internal: </b>Accommodation Services, Casual Staff Services,
                NDIS & Client Services, People & Culture, PayOffice.</div>
                <div><b>External:</b> Customer Organisations’ Staff and
                Clients/Participants, Case Managers, DHHS, NDIS.</div>
            </td>
        </tr>
    </table>

    <div class="font-weight-bold color_1">Your Purpose</div>
    <div class="f-13 pb-3">
        As a Disability and/or Child, Youth and Family (CYF) Support Worker, your purpose is to share
ONCALLs’ passion for delivering quality, safe, client/participant-centered support to significantly
add value, improve and create independence in the lives of the most vulnerable people in our
community. ONCALL prides itself on being a person-centred organisation, committed to human
rights, equal citizenship and maximising personal choice and control for all Clients/participants, in
an environment free from abuse.
    </div>
    <div class="f-13 pb-3">
        All Support Workers must undertake all duties with a positive attitude and be committed to
providing best possible care. Support is always provided in line with a Client/Participant’s individual
plan, and duties are undertaken in line with ONCALL’s Code of Conduct, policies and procedures,
DHHS Standards, Legislation and NDIS National Quality and Safeguarding Framework.
    </div>
    <div class="f-13 pb-3">
        All Support Workers must take responsibility to ensure their mandatory screening and training
documents are always up to date and renewed before expiry.
    </div>
    <div class="f-13 pb-3">
        <b>Disability Support Worker</b> will be providing support to people living with disabilities to assist them
enhance and increase independence in their lives and achieve their individual goals. You will be
supporting clients/participants within a permanent, transitional or short-term/emergency
accommodation setting and/or within client/participant’s private residence and supporting
clients/participants with community access/activities.
    </div>
    <div class="f-13 pb-3">
        <b>Child, Youth and Family Support Worker </b>will be providing support to significantly improve the
lives of children and young people with a history of varying levels of trauma and neglect. You will
guide and support children and young people in their personal, social and educational
development to help them reach their full potential in society. You will be working in residential
homes, parental homes, and short-term/emergency placement models and/or undertake transport
shifts with children and young people.
    </div>

    <div class="font-weight-bold color_1">Key to ONCALL’s success is our ability to employ people like you!</div>
    <div class="f-13 pb-3">
        <ul class="pl-3">
            <li>People who share ONCALL’s passion for working in this sector;</li>
            <li>People who want to make a real difference;</li>
            <li>People who are positive, take responsibility, capable of working calmly under pressure, team
players, show initiative and have strong organisational skills;</li>
            <li>People who are dedicated to creating an inclusive community for all Australians regardless of
their backgrounds or challenges;</li>
            <li>People who actively promote clients/participants’ rights and ensure the opportunity for
clients/participants to make their own choices and achieve their own goals;</li>
            <li>People who promote a Zero Tolerance to Abuse, ensuring each client/participant is free from
physical, sexual, verbal and emotional abuse and neglect;</li>
            <li>People who understand the importance of documentation and reporting;</li>
            <pagebreak />
            <li>People who support a client/participant’s right to complain and understand it is our duty of care
to support clients/participants through the complaint process to help them to feel empowered
and safe to give feedback.</li>
        </ul>
    </div>

    <div class="font-weight-bold color_1">ONCALL Support Worker ALWAYS takes accountability!</div>
    <div class="f-13 pb-3">
        <ul class="pl-3">
            <li>To carry out all duties/tasks in line with the house and/or clients/participants plan, to the
highest standard and in keeping with the Code of Conduct, policies and procedures,
legislation and with an excellent customer service focus.</li>
            <li>To ensure you arrive at your shift on time, dressed in a neat and professional manner with a
smile.</li>
            <li>Remembering to bring with you, your ONCALL ID card, WWCC, Victorian Driver’s License,
timesheet book and mobile phone.</li>
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
    <div class="f-13">
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
    <div class="f-13 pb-3">
        <ul class="pl-3">
            <li>You can access our policies on our website by clicking the ‘Staff Policies’ link at the bottom
corner of every page. The page is protected, so simply type in the password to get access.
Alternatively, copy the following web address into your browser
                <div><<a href="https://www.oncall.com.au/staff-policies/">https://www.oncall.com.au/staff-policies/</a>> .</div></li>
            </ul>
        </div>

        <pagebreak />
        <div class="font-weight-bold color_1">What an ONCALL Support Worker’s Job Looks Like!</div>



        <TABLE border="1" cellSpacing="0" cellPadding="5px" class="table_3">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD> Support clients/participants to achieve their goals. Goals can include but are not limited to developing social confidence, joining community groups, gaining more independence e.g. learning or participating in their own cooking/shopping, maintaining/improving health, attending school or day programs etc. Remember to record/report progress towards goals as
required.
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Showering, dressing, toileting, personal
                                hygiene, etc
                            </TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Support clients/participants to access and experience the community as required. This may include taking clients/participants shopping, to the movies, to a sporting game, or going out for lunch etc.
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>When transporting clients/participants in your personal/company vehicle, you must always carry your valid Victorian Driver’s licence, treat the vehicle with respect, drive safely, adhering to the Victorian Road Rules.</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>If using your own vehicle to transport clients, you must ensure your vehicle is registered, roadworthy, insured and serviced in line with ONCALL’s Use of Private Vehicles Policy.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Always use designated equipment if required,
e.g. personal protection equipment or manual handling/lifting equipment. Never take risks or shortcuts that will put your own safety at risk.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
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
        <TABLE border="1" cellSpacing="0" cellPadding="5px" class="table_3">
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>National police check (within 6 months, then 3
                            yearly renewal)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>International police check (if applicable)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>WWCC (E) (5 yearly renewal)</TD>
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
                            <TD>Manual Handling</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Fire Safety Training</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Valid Driver’s Licence (if required)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>DWES Check (Worker Screening Check from
                            July 2020) </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>NDIS Worker Orientation Module</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>National Police check
                                (within 6 months, then
                            annual renewal)</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>International police
                            check (if applicable);</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>WWCC (E)</TD>
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
                            <TD>Current Victorian Drivers
                                Licence
                            </TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>DHHS Carer Register
                            Check</TD>
                        </TR>
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>DWES Check (Worker
                                Screening Check from
                            July 2020)</TD>
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
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Certificate IV Disability or relevant qualification and equivalent experience and/or successful completion of NDIS Job Ready Program.</TD>
                        </TR>
                    </TABLE>
                </TD>
                <TD>
                    <TABLE border="1" class="table_4" cellSpacing="0" cellPadding="0">
                        <TR>
                            <TD class="dot_custom">•</TD>
                            <TD>Certificate IV in Child Youth Family Intervention, or a recognised relevant qualification and the four mandatory top up
units.</TD>
                        </TR>
                    </TABLE>
                </TD>
            </TR>
        </TABLE>
        <div class="f-13 pb-3 pt-3">
            NB: ALL ONCALL employees are expected to take responsibility for keeping mandatory training/documents current and up to date and are required to send new certificates to <a href="status@oncall.com.au">status@oncall.com.au</a> prior to expiry date (we send you reminders). Where mandatory requirement expires, you will be considered non-compliant and unable to work.
        </div>
        <pagebreak />
        <div class="f-13 pb-3">
            <div class="font-weight-bold color_1">OVERVIEW OF ONCALL GROUP AUSTRALIA PTY LTD</div>
            <div><b>ONCALL</b> is an award winning, quality certified, DHHS, NDIS and TAC registered service provider in the Disability and Child Youth and Family sectors. ONCALL is a recognised industry leader and preferred service provider to over 300 clients. ONCALL who takes pride in employing staff who share our passion and commitment to delivering quality services, where the client is at the center of every decision made.</div>
        </div>

        <div class="f-13 pb-3">
            <div class="font-weight-bold">Casual Staff Services (Labour Hire) 24/7</div>
            <div>ONCALL is the largest specialised Disability, and Child Youth and Family staffing agency with over 1000 qualified and skill staff who are highly regarded and in high demand. ONCALL is recognised as the market leader, providing qualified and skilled casual staffing support to 300+ customers, 24 hours per day, 365 days per year, managed by a team of skilled consultants supported by a strong business infrastructure and state of the art IT system.
            </div>
        </div>
        <div class="f-13 pb-3">
            <div class="font-weight-bold">NDIS Client Services</div>
            <div>ONCALL provides a full suite of NDIS services to participants and their families. This includes direct support services through the provision of skilled, trained and dedicated disability support staff. ONCALL staff are committed to delivering personalised support to participants and their families, that is customised and tailored to individual participant’s choices, needs and goals. This support is provided in participant’s private homes and within the community.
            </div>
        </div>
        <div class="f-13 pb-3">
            <div class="font-weight-bold">Accommodation Services Disability</div>
            <div>ONCALL provides a comprehensive suite of services and support to people with significant, complex and dual disabilities. Emergency, contingency, transitional or ongoing support through Supported Independent Living (SIL) / Shared Accommodation services can be provided. The team is supported by professional specialist Planning Practitioners and registered APOs.
            </div>
        </div>
        <div class="f-13 pb-3">
            <div class="font-weight-bold">Accommodation Services Out of Home Care (Child Youth and Family)</div>
            <div>ONCALL provides tailored support to ensure the safety and wellbeing of vulnerable children and young people who are under statutory Child Protection Services system. A range of support models can be provided that include crisis care, short term emergency or residential care through to family re- unification with the child/young person’s best interests at the heart of every step.
            </div>
        </div>
        <div class="f-13 pb-3">
            <div class="font-weight-bold">
                Declaration
            </div>
        </div>
        <div class="f-13 pb-3">
            <div class="font-weight-bold">
                I hereby declare:
            </div>
            <ul class="font-weight-bold">
                <li>I have read, understood and agree to abide by the above Contract, ONCALL Code of Conduct, Policies and Procedures.</li>
                <li>I have read and understand the requirements and expectations of the above Position Description and agree that I have the ability to fulfil the inherent physical requirements of the position and accept my role in fulfilling the Key Duties/Accountabilities and meeting the KPI’s set out herewith. I understand that the information and statements in this position description are intended to reflect a general overview of the responsibilities and are not to be interpreted as being all-inclusive.</li>
                <li>I agree to abide by the DHHS Code of Conduct for Disability Workers</li>
                <li>I agree to abide by the NDIS Code of Conduct.</li>
            </ul>
        </div>

        <div class="f-13 pb-3 w-100 pt-3">
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

        <div class="f-13 pb-3 w-100 pt-5">
            <div class="w-50 float-left">
                <?php echo $fullName;?>
                <div class="w-75 pt-1" style="margin-left:-5px">
                    <dottab />
                </div>
                Employee name (please print)
            </div>
        </div>

<!-- <div class="f-13 pb-3 font-weight-bold">
<i>Please return a signed copy of this document to ONCALL’s Human Resources Manager or
email to</i> hroga@oncall.com.au
</div> -->
<pagebreak />
<div class="f-13 pb-3 font-weight-bold">
    Reference:
</div>

<div class="f-13 pb-3">
    <div class="pb-2"><i>DHHS Code of Conduct for Disability Workers (2018)</i></div>
    <div class="pb-2"><i>NDIS Code of Conduct</i></div>
    <div class="pb-2"><i>Victorian Child Safe Standards</i></div>
    <div class="pb-2"><i>Freedom from Abuse and Neglect Policy</i></div>
    <div class="pb-2"><i>Incident Reporting Procedure</i></div>
    <div class="pb-2"><i>Conflict of Interest Policy</i></div>
</div>

<?php } ?>
