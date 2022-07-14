
<?php if($type=='header'){?>
    <div style="text-align: left; font-weight: bold;visibility: hidden;">
   <img height="60px" src="<?php echo base_url('assets/img/ocs_logo.png'); ?>"/>
</div>
<?php } ?>
<?php if($type=='footer'){?>
    <table width="100%">
    <tr>
        <td width="33%">{DATE j/m/Y}</td>
        <td width="33%" align="center">{PAGENO}/{nbpg}</td>
        <td width="33%" style="text-align: right;">Cabday interview contract</td>
    </tr>
</table>
<?php } ?>
<?php if($type=='content'){?>
<style>
.p-0{padding:0}
.pt-0,.py-0{padding-top:0}
.pr-0,.px-0{padding-right:0}
.pb-0,.py-0{padding-bottom:0}
.pl-0,.px-0{padding-left:0}
.p-1{padding:.25rem}
.pt-1,.py-1{padding-top:.25rem}
.pr-1,.px-1{padding-right:.25rem}
.pb-1,.py-1{padding-bottom:.25rem}
.pl-1,.px-1{padding-left:.25rem}
.p-2{padding:.5rem}
.pt-2,.py-2{padding-top:.5rem}
.pr-2,.px-2{padding-right:.5rem}
.pb-2,.py-2{padding-bottom:.5rem}
.pl-2,.px-2{padding-left:.5rem}
.p-3{padding:1rem}
.pt-3,.py-3{padding-top:1rem}
.pr-3,.px-3{padding-right:1rem}
.pb-3,.py-3{padding-bottom:1rem}
.pl-3,.px-3{padding-left:1rem}
.p-4{padding:1.5rem}
.pt-4,.py-4{padding-top:1.5rem}
.pr-4,.px-4{padding-right:1.5rem}
.pb-4,.py-4{padding-bottom:1.5rem}
.pl-4,.px-4{padding-left:1.5rem}
.p-5{padding:3rem}
.pt-5,.py-5{padding-top:3rem}
.pr-5,.px-5{padding-right:3rem}
.pb-5,.py-5{padding-bottom:3rem}
.pl-5,.px-5{padding-left:3rem}


.m-0{margin:0}
.mt-0,.my-0{margin-top:0}
.mr-0,.mx-0{margin-right:0}
.mb-0,.my-0{margin-bottom:0}
.ml-0,.mx-0{margin-left:0}
.m-1{margin:.25rem}
.mt-1,.my-1{margin-top:.25rem}
.mr-1,.mx-1{margin-right:.25rem}
.mb-1,.my-1{margin-bottom:.25rem}
.ml-1,.mx-1{margin-left:.25rem}
.m-2{margin:.5rem}
.mt-2,.my-2{margin-top:.5rem}
.mr-2,.mx-2{margin-right:.5rem}
.mb-2,.my-2{margin-bottom:.5rem}
.ml-2,.mx-2{margin-left:.5rem}
.m-3{margin:1rem}
.mt-3,.my-3{margin-top:1rem}
.mr-3,.mx-3{margin-right:1rem}
.mb-3,.my-3{margin-bottom:1rem}
.ml-3,.mx-3{margin-left:1rem}
.m-4{margin:1.5rem}
.mt-4,.my-4{margin-top:1.5rem}
.mr-4,.mx-4{margin-right:1.5rem}
.mb-4,.my-4{margin-bottom:1.5rem}
.ml-4,.mx-4{margin-left:1.5rem}
.m-5{margin:3rem}
.mt-5,.my-5{margin-top:3rem}
.mr-5,.mx-5{margin-right:3rem}
.mb-5,.my-5{margin-bottom:3rem}
.ml-5,.mx-5{margin-left:3rem}


.PDF_header{margin-bottom:25px;}
.PDF_header .header_child{margin-bottom:20px;}
.PDF_header .header_child .Name{font-size:16px; margin-bottom:5px;}
.PDF_header .header_child .Details{font-size:15px; height:15px; display:inline-block;}
.PDF_header .header_child .Dotted{margin-left:-5px; margin-top:3px;}

.PDF_Create_Date{}
.PDF_Create_Date .set_date_salutation {margin-bottom:15px;}
.maring_left_dotted{margin-left:-5px;}

.Bold{font-weight:bold;}
.text-Bold{font-weight:bold;}
.pad_1_{padding-left:20px;}
.align_top{vertical-align:top;}

.heading_box{border-top:1px solid #1e1e1e; border-bottom:1px solid #1e1e1e; border-right:1px solid #1e1e1e; border-left:1px solid #1e1e1e; padding:5px 15px; font-weight:bold;}
</style>
    

<div class="PDF_header">
    <div class="header_child">
        <div class="Name">Name</div>
        <div width="50%" class="Details"><?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></div>
        <div width="50%" class="Dotted">
            <dottab />
        </div>
    </div>

    <div class="header_child">
        <div class="Name">Street Address</div>
        <div width="45%" class="Details"><?php echo isset($complete_data['street_address']) ? $complete_data['street_address']:''?></div>
        <div width="45%" class="Dotted">
            <dottab />
        </div>
    </div>

    <div class="header_child">
        <div class="Name">City State Postcode</div>
        <div width="40%" class="Details"><?php echo isset($complete_data['street_address_other']) ? $complete_data['street_address_other']:''?></div>
        <div width="40%" class="Dotted">
            <dottab />
        </div>
    </div>
</div>



<div class="PDF_Create_Date">

    <div class="set_date_salutation">
        <div style="float: left; width: 85px">Create Date </div>
        <div style="float: left; width: 100px;" class="set_date_1">
            <div class="text_1"><?php echo date('d/m/Y');?></div>
            <div class="maring_left_dotted">
                <dottab />
            </div>
        </div>
    </div>

    <div class="set_date_salutation">
        <div style="float: left; width: 110px">Dear Salutation </div>
        <div style="float: left; width: 100px;" class="set_date_1">
            <div class="text_1"><?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></div>
            <div class="maring_left_dotted">
                <dottab />
            </div>
        </div>
    </div>

</div>




<div>
    <p>
        ONCALL is a dynamic organisation. We work in partnership and are recognized as a sector leader
        and service provider in disability and welfare. We are pleased to advise you that your application for
        Labour Hire Work with ONCALL Personnel has been successful and we welcome you to the ONCALL team.
    </p>
    <p>
        ONCALL will make every endeavour to make our relationship a success and assign work to you.
        This will depend on your availability, type of work, location and your shift preferences. It will also
        depend on customer feedback and choice. If the response from partner organisations or clients is negative, the
        clients
        are not bound to provide further work and ONCALL is not bound to
        allocate further work to you.
    </p>
    <p>
        An agreement to perform work is only concluded when you accede to a request to attend a particular work site on
        a given day.
        At that time, you assume an obligation to attend the site to perform such work as may be allocated to you.
        There is a clear expectation that you will respect the working conditions, policies and procedures of the work
        site and
        adhere to them throughout your shift.
    </p>
    <p>
        When you are assigned to work for ONCALL with a partner organisation or client, it must be in accordance with
        the
        attached position description and on the terms and conditions set out below.
    </p>
</div>


<div class="Bold">1. Aim</div>
<div class="pad_1_">
    <div class="">By entering into this Agreement both parties aim to:</div>
    <div class="">
        <table>
            <tr>
                <td>a) </td>
                <td>adhere to their Occupational Health and Safety (OHS) responsibilities to maintain and promote safe
                    workplaces;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>b) </td>
                <td>ensure that all aspects of ONCALL’s operation reflect the quality demanded of the industry;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>c) </td>
                <td>encourage the achievement of the best practices in all areas of such services;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>d) </td>
                <td> respect and value the diversity of the work force by aiming to prevent and eliminate discrimination
                    at
                    ONCALL\'s enterprise on the basis of race, colour, sex, sexual preference, age, physical or mental
                    disability, marital status, family responsibilities, pregnancy, religion, political opinion,
                    national
                    extraction or social origin.</td>
            </tr>
        </table>
    </div>
</div>


<pagebreak />
<div class="Bold">2. Your Duties</div>
<div class="pad_1_">
    <div class="">You must:</div>
    <div class="">
        <table>
            <tr>
                <td>a) </td>
                <td>diligently perform the duties (“the Duties”) specified in Schedule B and such other duties as ONCALL
                    may from time to time reasonably require;/td>
            </tr>
        </table>
        <table>
            <tr>
                <td>b) </td>
                <td>observe and comply with all of ONCALL’s reasonable and lawful directions, rules, processes, policies
                    and procedures as they pertain to labour hire workers;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>c) </td>
                <td>use your best endeavours to promote ONCALL’s commercial aims and objectives and refrain from doing
                    any act which can adversely affect ONCALL’s present or future interests.</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>d) </td>
                <td>devote your time, attention and skills to your Duties whist at work</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>d) </td>
                <td>not engage in any other business activity or be involved either directly or indirectly in any
                    business or activity which is in conflict with the interests of ONCALL.</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>d) </td>
                <td>respect and adhere to the working conditions, policies and procedures of the work site and meet
                    their organizational requirements.</td>
            </tr>
        </table>
    </div>
</div>



<div class="Bold pt-4">3. Police Check</div>
<div class="pad_1_">
    <div class="">Police Check</div>
    <div class="">
        <p class="mt-2 mb-0">You have undergone a successful police check clearance before your engagement and you agree to renew it prior
            to the expiration of each (three) years.
            You understand and accept that if the police check obligation is not met, this contract will be null and
            void
            resulting in ONCALL’s inability to place you with any partner organisation or client</p>
        <p class="mt-2 mb-0">A criminal record and the types of offences which would disqualify the labour hire worker from engagement
            must be relevant to the inherent requirements of the job held by you.</p>
        <p class="mt-2 mb-0">It is agreed that there is an obligation on the labour hire worker to disclose any relevant criminal charges
            or convictions which would
            disqualify you from being engaged in the period between three yearly police checks.
            Failure to do so will result in the immediate cessation of the working relationship.</p>
        <p class="mt-2 mb-0">Please take note that ONCALL is bound by regulation and contractual undertakings to disclose all relevant
            criminal matters as they may pertain to the workers placed with their services and their homes.</p>
        <p class="text-Bold">International Police Check Workers who resided overseas as a permanent resident or citizen
        </p>
        <p class="mt-2 mb-0">If you were a citizen or a permanent resident of a country other than Australia at any time since turning
            16 years of age - at the recruitment stage - you should have submitted a statutory declaration
            which testifies that you have no existing criminal record in that country. ONCALL requires to have a copy of
            that statutory declaration.Additionally,
            if you resided in an overseas country for 12 months or more in the last ten years you should have contacted
            the relevant overseas police force to obtain a
            criminal or police record check.
            <pagebreak />
            If you have copies of the relevant police clearance which may have formed part of your visa application,
            these documents should
            have been submitted to ONCALL at the recruitment stage. This is mandatory for ONCALL’s purposes in
            satisfying
            Government policy and guidelines and our contractual obligation to our clients and partner organizations.
        </p>
        <p class="mt-2 mb-0 text-Bold">Working with Children Check (WWCC)</p>
        <p class="mt-2 mb-0">ou have undergone a WWCC before your engagement and you agree to renew it prior to the expiration of each
            (five) years.
            You understand and accept that if the WWCC obligation is not met,
            this contract will be null and void resulting in ONCALL’s inability to place you with any partner
            organisation or client.</p>
        <p class="mt-2 mb-0 text-Bold">Disability Worker Exclusion Scheme (DWES)</p>
        <p class="mt-2 mb-0">Before commencing in your role as Labour Hire Worker you shall familiarise yourself with the content
            pertaining to DWES by visiting DHS
            website on: <a
                href="https://providers.dhhs.vic.gov.au/">http://www.dhs.vic.gov.au/for-service-providers/disability/accommodation/supported-accommodation/disability-worker-exclusion-scheme</a>
            You
            can seek clarification from the DHS by contacting them directly on Tel: 1300 650 172 [local call free within
            Victoria, except mobile phones].
            In order to work for ONCALL, you are required to consent to have your name checked against the Disability
            Worker Exclusion List (DWEL) and agree to the terms of the DWES, which may involve a
            work related incident involving a client which may result in your name being placed on the DWEL.By signing
            this contract, it shall be deemed that you have complied with the requirements
            of this sub-clause and that you offer your consent to participate in this Scheme.</p>

    </div>
</div>



<div class="Bold pt-4">4. Rules of engagement to performwork</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">Your engagement to perform your duties shall conclude at the end of each day on which you are
            given work.
            Both you and ONCALL agree and acknowledge that because the nature of labour hire work can be irregular and
            uncertain,
            each offer of engagement can be accepted or rejected. Each instance of engagement is a new contract on these
            terms.</p>
    </div>
</div>

<div class="Bold pt-4">5. placement and Place ofWork</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">ONCALL may require, and you may agree to a placement with a Client of ONCALL.
            As such, you will be required to work at various locations at the direction of the Client for the duration
            of each placement.
            Accordingly, you will require a current Victorian or eligible Drivers Licence (Drivers licence must be
            presented for verification)
            (Mandatory for Welfare Support – preferred for Disability Support). You need to own or
            have access to a Roadworthy, Registered Vehicle and you must have comprehensive motor vehicle insurance,
            which takes into consideration your work activities.</p>
    </div>
</div>

<pagebreak />
<div class="Bold pt-0">6. Payment for work</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">You shall be paid by ONCALL on a weekly basis in accordance with the industrial legislation
            governing disability and welfare.
            The dominant Award in our sector is the Social, Community, Home Care and Disability Services Industry Award
            (SCHCDSI) 2010.
            Hourly rates vary depending on various factors, such as your classification and level and the shifts you
            agree to attend. You will find the Award,
            containing its classifications and pay structure on www.fwa.gov.au Award ID: MA000100. You may also seek
            clarification from our payroll team.</p>
        <p class="mt-2 mb-0">Where the work is covered by an industrial instrument other than the SCHCDS Award, you will be advised of the
            changes and the different pay rates.
            Superannuation payments will be made by ONCALL in accordance with the Superannuation Guarantee
            (Administration) Act 1992 (Cth).
            Your pay, less applicable tax deductions,
            will be directly deposited weekly into your nominated bank account or financial institution at the end of
            the pay period,
            upon provision of a correctly completed and appropriately authorised timesheet.</p>
        <p class="mt-2 mb-0">You agree to lawful deduction from your wages, of any amounts that may be owed by you to ONCALL.</p>
    </div>
</div>

<div class="Bold pt-4">7. Leave Entitlements</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">You acknowledge that as a labour hire worker you have no entitlement to paid leave including
            personal leave,
            sick leave, annual leave, public holidays or bereavement leave. Your hourly rate will attract a 25% loading
            instead of
            the paid leave entitlements accrued by full-time employees.</p>
    </div>
</div>

<div class="Bold pt-4">8. Expenses</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">ONCALL shall reimburse you for all reasonable and necessary client related expenses you incur
            which are approved in advance by ONCALL.
            You agree to provide to ONCALL appropriate receipts including tax invoices, itemised accounts or other
            documentation of those expenses in support
            of the need for reimbursement. These need to be attached to your time sheets.
            Incomplete documentation or inappropriate authorization of expenses may delay payment.</p>
    </div>
</div>


<div class="Bold pt-4">9. Confidential Information</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">You must not disclose any information of a confidential nature relating to ONCALL’s business or its clients\'
            business, or associated companies,
            except to officials whose duty it is to know such information.</p>
        <p class="mt-2 mb-0">"Confidential Information" means any information relating to the business of ONCALL or its clients,
            whether or not marked or designated as confidential, secret or otherwise. You will be required to agree to
            and sign ONCALL’s
            Deed of Confidentiality document and may be required to sign other similar documents provided by ONCALL’s
            clients and partner organisations.</p>
        <p class="mt-2 mb-0">Whilst on shift you are responsible for any sensitive or confidential document in your possession. All
            documents, including all hard copies shall be treated with the highest security.</p>
    </div>
</div>

<pagebreak />
<div class="Bold pt-0">10. Superannuation</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">ONCALL will make quarterly superannuation contributions for eligible workers under the federal Superannuation
            Guarantee (Administration) Act (the Act). Since 1 July 2002 the minimum level of superannuation contribution
            for workers has been nine per cent.</p>
        <p class="mt-2 mb-0">Additional superannuation payments can be made on your behalf.</p>
        <p class="mt-2 mb-0">You can choose your own super fund as long as it is compliant. Health Super is ONCALL’s default
            superannuation fund. If you don’t select your own superannuation fund, or it is not a compliant fund, we
            will automatically default your superannuation contributions to a Health Super fund.</p>
    </div>
</div>

<div class="Bold pt-4">11. Immediate cessation of services</div>
<div class="pad_1_">
    <div>Despite anything else contained in this Agreement, ONCALL shall stop utilizing your services, if you engage in
        any act or omission constituting serious misconduct</div>
    <div class="">
        <table>
            <tr>
                <td>a)</td>
                <td>serious misconduct includes, but is not limited to the following:</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>b)</td>
                <td>bullying, theft, fraud or assault;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>c)</td>
                <td>drinking alcohol at work or arriving to a shift intoxicated or under the influence of nonprescribed
                    drugs
                    during working hours;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>d)</td>
                <td>any conduct that causes imminent and serious risk to the reputation, viability or profitability of
                    ONCALL’s
                    business;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>e)</td>
                <td>any act of sexual harassment or molestation of a Client, another worker or any other person;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>f) </td>
                <td>use of abusive or offensive language;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>g) </td>
                <td>violation of ONCALL\'s Occupational Health and Safety policies and procedures;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>h) </td>
                <td>serious breach of duty of care, Code of Conduct or any governing legislation;</td>
            </tr>
        </table>
        <table>
            <tr>
                <td>i)</td>
                <td>inappropriate and unauthorised use of social media whilst at work</td>
            </tr>
        </table>
    </div>
</div>

<div class="Bold pt-4">12. Direct Engagement by a Client</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">You recognise that ONCALL invests significant costs in your recruitment. You acknowledge that if
            you are
            offered or seek engagement with a Client of ONCALL and you accept such engagement, the Client must pay
            ONCALL a placement fee as determined by ONCALL. You agree that in the event that you are engaged through a
            third party to provide services to a Client of ONCALL for whom you were assigned to work in the preceding
            six months, a placement fee will be paid to ONCALL as determined. The minimum fee payable to ONCALL is
            $2,500.00.</p>
    </div>
</div>

<div class="Bold pt-4">13. Dispute Resolution Procedure</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">In the event of a dispute or grievance arising concerning the contents of this Agreement the
            parties agree to make every effort to resolve the dispute by consultation and negotiation. If the
            negotiation process is exhausted without the dispute being resolved, the parties may refer the matter to a
            mutually agreed mediator or conciliator for the purpose of resolving the dispute.</p>
    </div>
</div>

<div class="Bold pt-4">14. Entire agreement, governing law and severability</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">This Agreement represents the entire agreement, between ONCALL and you in relation to your
            engagement and it replaces and supersedes all previous agreements, terms and conditions of engagement,
            contracts, negotiations, understandings, or representations between ONCALL and you. This Agreement may only
            be varied, amended or replaced by agreement in writing between ONCALL and you.</p>
        <p class="mt-2 mb-0">This Agreement shall be governed by and construed in accordance with the laws of the State of Victoria.</p>
        <p class="mt-2 mb-0">In the event that any provision of this Agreement is held unenforceable, such provision shall be severed and
            shall not affect the validity or enforceability of the remaining portions.</p>
    </div>
</div>


<div class="Bold pt-4">15. Information is accurate</div>
<div class="pad_1_">
    <div class="">
        <p class="mt-2 mb-0">You warrant that all information you provided to ONCALL which led to your engagement including information
            relating to your qualifications and curriculum vitae is accurate in all respects and you have not misled or
            deceived ONCALL in any way in relation to the information provided.</p>
        <p class="mt-2 mb-0">You warrant that you have not omitted or failed to disclose any information to ONCALL, which you may
            reasonably consider to be relevant to your engagement under this Agreement.</p>
    </div>
</div>
<p class="mt-2 mb-0">If you agree to these terms and conditions of engagement, please indicate by signing and dating both copies of this
    letter where indicated below and retain the original copy for your records. Yours faithfully</p>

<pagebreak />

<table width="100%">
    <tr><td  width="100%">
<img src="<?php echo base_url('assets/img/signature.png'); ?>" width="120px">
<div class="mt-3">For</div>
<h4 class="Bold">ONCALL Personnel & Management Pty Ltd</h4>
</td></tr>
</table>

<pagebreak />
<div>
<h4 style="text-align:center"><b>SCHEDULE A Item </b></h4>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="10px">
        <tr>
            <td width="10%">Item 1</td>
            <td width="30%">Labour Hire Worker</td>
            <td width="60%">
            <table width="100%" style="padding-bottom:15px;">
                <tr><td>Name</td></tr>
                <tr><td style="height:15px"><?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></td></tr>
                <tr><td><dottab /></td></tr>
            </table>
            <table width="100%" style="padding-bottom:15px;">
                <tr><td>Street Address</td></tr>
                <tr><td style="height:15px"><?php echo isset($complete_data['street_address']) ? $complete_data['street_address']:''?></td></tr>
                <tr><td>  <dottab /></td></tr>
            </table>
            <table width="100%" style="padding-bottom:15px;">
                <tr><td>City State Postcode</td></tr>
                <tr><td style="height:25px"><?php echo isset($complete_data['street_address_other']) ? $complete_data['street_address_other']:''?></td></tr>
                <tr><td>  <dottab /></td></tr>
            </table>
            
            </td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse;" cellpadding="10px ">
    <tr>
        <td width="10%">Item 1</td>
        <td width="30%">Labour Hire Worker</td>
        <td width="60%">
        <table width="100%" style="padding-bottom:15px;">
            <tr><td>
            You shall be paid by ONCALL on a weekly basis in accordance with the industrial legislation governing disability and welfare. The dominant Award in our sector is the Social, Community, Home Care and Disability Services Industry Award (SCHCDSI) 2010. Hourly rates vary depending on various factors, such as your classification and level and the shifts you agree to attend. You will find the Award, containing its classifications and pay structure on www.fwa.gov.au Award ID: MA000100.You may also seek clarification from our payroll team.
            </td></tr>
        </table>
        
        </td>
    </tr>
</table>

</div>

<h4 class="text-Bold">CONFIDENTIALITY DEED REGARDING CLIENT INFORMATION</h4>
<div style="padding-bottom:15px;">
    <div class="text-Bold" width="20%" style="float:left">BETWEEN:</div>
    <div width="80%" style="float:left">
        <div>ONCALL Personnel & Management Services Pty Ltd of</div>
        <div>
            <div width="50%" style="float:left">Level 2, 660 Canterbury Rd</div>
            <div width="50%" style="float:left">Surrey Hills, 3127</div>
        </div>
    </div>
</div>

<p class="text-Bold" style="padding-left:50px;">("Company")</p>
<p class="text-Bold">AND:</p>
<p class="text-Bold" style="padding-left:50px;">(“Worker”)</p>
<p class="text-Bold">of</p>

<div class="Bold pt-3">Recitals</div>
<p class="mt-2 mb-0">The Company operates a business of providing specialised temporary labour hire ("Business") to clients in the Community Services and Support sectors ("Client Organisations"). The Company employs the Worker.</p>
<p class="mt-2 mb-0">During  the  Worker\'s  contact  with  the  Client  Organisations  and  the  Client  Organisation\'s  respective  clients ("Clients"),  the  Worker  may  have  access  to  or  gain  knowledge  of  all  or  part  of  the  following  confidential information:</p>    

<div>
    <table class="align_top">
    <tr>
        <td>a)</td>
        <td>personal or sensitive information relating to a Client including information which may identify a Client, a  Client\'s  place  of  residence  and  information  in  relation  to  a  Client\'s  physical  and  psychological condition;</td>
    </tr>
    </table>
    <table class="align_top">
    <tr>
        <td>b)</td>
        <td>all  administrative  procedures,  business  and  financial  information,  business  systems,  operating techniques,  procedures,  policies  and  practices,  methods,  systems,  trade  secrets,  intellectual property, Client lists, computer programs, manuals, notes, routines, concepts, ideas, know how of the Company, the Client Organisation or the Client; and</td>
    </tr>
    </table>
    <table class="align_top">
    <tr>
        <td>c)</td>
        <td>any other information that would otherwise at law be considered secret or confidential information of the Company, the Client Organisation or the Client ("Confidential Information")</td>
    </tr>
    </table>
</div>


<div class="Bold pt-5">1. In consideration of the Company agreeing to employ the Worker, the Worke</div>
<div class="pad_1_">
    <div class="">By entering into this Agreement both parties aim to:</div>
    <div class="">
        <table class="align_top">
            <tr>
                <td>a) </td>
                <td>agrees that the above information is true and accurate;</td>
            </tr>
        </table>

        <table class="align_top">
            <tr>
                <td>b) </td>
                <td>acknowledges the necessity of protecting the Confidential Information and agrees that any disclosure
                    in breach of this Deed may cause damage;</td>
            </tr>
        </table>
        <table class="align_top">
            <tr>
                <td>c) </td>
                <td>acknowledges the need to protect the confidentiality of the Client</td>
            </tr>
        </table>
        <table class="align_top">

            <tr>
                <td>d) </td>
                <td>agrees that all of the provisions of this Deed are reasonable in all the circumstances;</td>
            </tr>
        </table>
        <table class="align_top">
            <tr>
                <td>e) </td>
                <td>agrees that the confidentiality obligations created by this Deed shall not merge or be released upon
                    cessation of the Workers’ engagement but will continue afterwards</td>
            </tr>
        </table>
        <table class="align_top">
            <tr>
                <td>f) </td>
                <td>consents to ONCALL releasing Personal Information it holds about the Worker under the following
                    circumstances and to:</td>
            </tr>
        </table>
        <div class="pad_1_">
            <div>
                <table class="align_top">
                    <tr>
                        <td>(i) </td>
                        <td>any organisation which may be interested in employing the Worker’s services who requests
                            ONCALL furnish them with a copy of the Worker’s police check and / or working with children
                            check;</td>
                    </tr>
                </table>
                <table class="align_top">
                    <tr>
                        <td>(ii) </td>
                        <td>any person or organisation which may be interested in employing the Worker services. This
                            may include a summary of any investigations in which the Worker has been involved (verbal
                            verification will be obtained and recorded from employee in each instance);</td>
                    </tr>
                </table>
                <table class="align_top">
                    <tr>
                        <td>(iii) </td>
                        <td>the Department of Health and Human Services – under the Children, Youth and Families
                            Regulations 2007 (VIC) for the purpose of demonstrating and verifying ONCALL’s compliance
                            with the Department’s selection criteria for the recruitment of residential carers. This may
                            include a summary of any investigations in which the Worker has been involved;</td>
                    </tr>
                </table>
                <table class="align_top">
                    <tr>
                        <td>(iv) </td>
                        <td>the Department of Health and Human Services for the purpose of undertaking a ‘Disqualified
                            Carer Check’;</td>
                    </tr>
                </table>
                <table class="align_top">
                    <tr>
                        <td>(v) </td>
                        <td>ONCALL’s Auditors for the purpose of demonstrating and verifying ONCALL’s compliance with
                            applicable standards (Auditors are subject to stringent confidentiality protocols and will
                            not record or use employees’ details for any other purpose); and</td>
                    </tr>
                </table>
                <table class="align_top">
                    <tr>
                        <td>(vi) </td>
                        <td>any other party, if required to do so by law.</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="Bold pt-4">2. The Worker agrees that he/she must not</div>
<div class="pad_1_">
    <div class="">
        <table class="align_top">
            <tr>
                <td>(a) </td>
                <td>use any or all of the Confidential Information for any purpose other than to perform the employment
                    or contractual duties to the Company;</td>
            </tr>
        </table>
        <table class="align_top">
            <tr>
                <td>(b) </td>
                <td>divulge to any person all or any aspect of the Confidential Information otherwise than with the
                    prior approval of:</td>
            </tr>
        </table>
        <div class="pad_1_">
            <div>
                <table class="align_top">
                    <tr>
                        <td>(i) </td>
                        <td>the Company, the Client Organisation and the Client, or where the Client does not have
                            capacity to give consent, the Client\'s parent, guardian or attorney as the case may be; or
                        </td>
                    </tr>
                </table>
                <table class="align_top">
                    <tr>
                        <td>(ii) </td>
                        <td>an authorised officer at the Department of Health and Human Services;</td>
                    </tr>
                </table>
            </div>
        </div>
        <table class="align_top">
            <tr>
                <td>(c) </td>
                <td>grant or permit any person to have access to or possession of the Confidential Information</td>
            </tr>
        </table>
        <table class="align_top">
            <tr>
                <td>(d) </td>
                <td>make any written notes, copy, reproduce, store, record, computerise, document or duplicate any part
                    of the Confidential Information without the prior written consent of the Company and any such notes,
                    copies reproductions, records, documents or duplicates must be delivered up to the Company forthwith
                    upon its request to do so;</td>
            </tr>
        </table>
    </div>
</div>


<div class="Bold pt-4">3. The Worker agrees that he/she must not</div>
<div class="pad_1_">
    <div class=""></div>
    <div class="">
        <table class="align_top">
            <tr>
                <td>(a) </td>
                <td>maintain all Confidential Information in strictest confidence including ensuring secure and proper
                    storage, taking precautions to prevent accidental disclosure; and</td>
            </tr>
        </table>
        <table class="align_top">
            <tr>
                <td>(b) </td>
                <td>on completion of all business between the Company and the Worker, obliterate, destroy or otherwise
                    delete the Confidential Information and any other confidential information in its possession and
                    provide evidence satisfactory to Company that it has been done.</td>
            </tr>
        </table>
    </div>
</div>

<div class="Bold pt-4">4. In relation to any information in the public domain, the parties agree that</div>
    <div class="pad_1_">
        <div class=""></div>
        <div class="">
            <table class="align_top">
                <tr>
                    <td>(a) </td>
                    <td>this Deed does not impose obligations upon the Worker in respect of Confidential Information
                        that is in the public domain otherwise than as a result of disclosure by the Worker or in
                        relation to disclosure required by a court order; and</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(b) </td>
                    <td>any Confidential Information is not deemed to be within the exceptions set out in clause 4(a) if
                        merely individual elements of the Confidential Information or certain combinations of the
                        Confidential Information are in the public domain or known to the Worker, but only if the
                        combination of all individual elements as a whole and as such is in the public domain or known
                        to the Worker. A specific element of the Confidential Information is not deemed to be in the
                        public domain or known to the Worker merely because it is embraced by more general information
                        that is in the public domain or known to the Worker.</td>
                </tr>
            </table>
        </div>
    </div>


    <div class="Bold pt-4">5. The Worker:</div>
    <div class="pad_1_">
        <div class=""></div>
        <div class="">
            <table class="align_top">
                <tr>
                    <td>(a) </td>
                    <td>acknowledges and accepts that the Company would suffer financial and other loss and damage if
                        the Confidential Information was disclosed to any other person or used for any purpose other
                        than the purpose of this Deed and that monetary damages would be an insufficient;</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(b) </td>
                    <td>acknowledges and accepts that in addition to any other remedy which may be available in law or
                        equity, the Company is entitled to injunctive relief to prevent a breach of this Deed and to
                        compel specific performance of this Deed; and</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(c) </td>
                    <td>acknowledges and accepts that it will immediately reimburse the Company for all costs and
                        expenses (including legal costs and disbursements on a full indemnity basis) incurred in
                        enforcing the obligations of the Worker under this Deed.</td>
                </tr>
            </table>
            <pagebreak/>
            <table class="align_top">
                <tr>
                    <td>(d) </td>
                    <td>indemnifies the Company against all costs, expenses, actions or claims directly or indirectly
                        incurred or suffered by the Company as a result of any breach of this Deed by the Worker, which
                        costs include all costs, damages and expenses incurred by the Company in defending or settling
                        any such costs, expenses, actions, suits proceedings, claims or demands (including legal costs
                        and disbursements on a full indemnity basis).</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="Bold pt-4">6. On completion of the Worker’s employment or contractual engagement with the Company, the
        Worker must:</div>
    <div class="pad_1_">
        <div class=""></div>
        <div class="">
            <table class="align_top">
                <tr>
                    <td>(a) </td>
                    <td>deliver up to the Company without fee or charge all documentation, information or other media in
                        which the Confidential Information or any part of it may be embodied (including all copies) in
                        the possession, custody or control of the Worker or its servants or agents; and/or</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(b) </td>
                    <td>return all the Confidential Information to the Company;</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(c) </td>
                    <td>obliterate, destroy or otherwise delete any other copies of the Confidential Information and all
                        parts of it from any media on which it is embodied; and</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(d) </td>
                    <td>provide evidence satisfactory to the Company that the Confidential Information on documentation
                        is destroyed and the Confidential Information on other media has been obliterated, destroyed or
                        otherwise deleted.</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="Bold pt-4">7. The Worker undertakes, covenants and agrees that:</div>
    <div class="pad_1_">
        <div class=""></div>
        <div class="">
            <table class="align_top">
                <tr>
                    <td>(a) </td>
                    <td>they will not discredit or attempt to discredit, make disparaging or harmful statements about
                        the Company to any person (including, without limitation, any customer or supplier) which is
                        likely to injure the reputation or standing of the Company or jeopardise the Company’s
                        relationship with any such person or third parties;</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(b) </td>
                    <td>if the Worker is uncertain whether any information comprises part of the Confidential
                        Information, then the Worker must seek direction from the Company before divulging the
                        information to any other person;</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(c) </td>
                    <td>the Worker will not communicate, publish, release or retain copies of any information regarding
                        the Client, the Client Organisation or the community service organisation’s staff, residents,
                        services provided by the Worker, operations or policy that are received in connection with the
                        provision of services by the Worker to the Client, the Client Organisation or the community
                        service organisation, except the Client Organisation or the community service organisation;</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(d) </td>
                    <td>will comply with and be bound by the provisions of the Privacy Act 1988 (Cth), the Information
                        Privacy Act 2000 (Vic) and Health Records Act 2001 (Vic) (as each is amended or replaced from
                        time to time) and the privacy principles set out in those Acts, in the same way and to the same
                        extent as the Client Organisation or community service organisation is bound by them, in
                        relation to any information that the Worker receives in connection with this Deed, which
                        includes information about an individual or from which the identity of an individual is
                        reasonably ascertainable (“personal information”).</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(e) </td>
                    <td>the Worker’s obligations under the Acts mentioned in clause 7(d) include, among others, the
                        obligation not to use or disclose personal information of any person, without that persons
                        consent, for any purpose other than the primary purpose for which the personal information was
                        collected (subject to some exceptions as set out in those Acts).</td>
                </tr>
            </table>
        </div>
    </div>

    <pagebreak/>
    <div class="Bold pt-4">8. The miscellaneous provisions which apply to this Deed are as follows:</div>
    <div class="pad_1_">
        <div class=""></div>
        <div class="">
            <table class="align_top">
                <tr>
                    <td>(a) </td>
                    <td>This Deed may be executed in any number of counterparts and counterparts may be exchanged by
                        electronic transmission (including by email), each of which will be deemed an original, but all
                        of which together constitute one and the same instrument.</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(b) </td>
                    <td>This Deed will be construed in accordance with the laws of the State of Victoria.</td>
                </tr>
            </table>
            <table class="align_top">
                <tr>
                    <td>(c) </td>
                    <td>If a provision (or part of it) of this Deed is held to be unenforceable or invalid, it must be
                        interpreted as narrowly as necessary to allow it to be enforceable and valid. If it cannot be so
                        interpreted narrowly, then the provision (or part of it) must be severed from this Deed without
                        affecting the validity and enforceability of the remaining provisions.</td>
                </tr>
            </table>
        </div>
    </div>


    <div class="Bold pt-4">PRE-EXISTING INJURY DECLARATION:</div>
    <p class="mt-2 mb-0">In accordance with s82 (7) – (9) of the Accident Compensation Act 1985 (Vic), you are
        required to disclose any or all pre-existing injuries, illnesses or diseases (“pre-existing conditions”)
        suffered by you which could be accelerated, exacerbated, aggravated or caused to recur or deteriorate by you
        performing the responsibilities associated with the engagement for which you are applying with ONCALL
        Personnel & Management Services Pty Ltd.In making this disclosure, please refer to the attached /included
        position description, which describes the nature of the engagement. It includes a list of responsibilities
        and physical demands associated with the engagement.</p>
    <p class="mt-2 mb-0">Please note that, if you fail to disclose this information or if you provide false and
        misleading information in relation to this issue, under s82(8) and s82(9) of the Act you and your dependants
        may not be entitled to any form of workers’ compensation as a result of the recurrence, aggravation,
        acceleration, exacerbation or deterioration of a pre-existing condition arising out of, in the course of, or
        due to the nature of your engagement.</p>
    <p class="mt-2 mb-0">Please also note that the giving of the false information in relation to your application
        for engagement with ONCALL Personnel & Management Services Pty Ltd may constitute grounds for disciplinary
        action or dismissal.</p>

    
    <div class="heading_box mt-5">WORKER DECLARATION</div>
        <div class="set_date_salutation pt-3">
            <div style="float: left; width: 12px">I,</div>
            <div style="float: left; width: 300px;" class="set_date_1">
                <div class="text_1"><?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></div>
                <div class="maring_left_dotted">
                    <dottab />
                </div>
            </div>
            <div style="float: left;">declare that:</div>
        </div>
     </div>

     <div class="pad_1_">
     <div class=""></div>
     <div class="pt-3">
         <table class="align_top">
             <tr>
                 <td>1.</td>
                 <td>I have read and understood this form and the attached/included position description, and have
                     discussed the engagement with ONCALL Personnel & Management Services Pty Ltd. I understand the
                     responsibilities and physical demands of the engagement.</td>
             </tr>
         </table>
         <table class="align_top">
             <tr>
                 <td>2.</td>
                 <td>I acknowledge that I am required to disclose all pre-existing conditions which I believe may be
                     affected by me undertaking the engagement.</td>
             </tr>
         </table>
         <table class="align_top">
             <tr>
                 <td>3.</td>
                 <td>I acknowledge that failure to disclose this information or providing false and misleading
                     information may result in invoking section 82(7)-(9) of the Accident Compensation Act 1985 (Vic)
                     which may disentitle me or my dependants from receiving any workers’ compensation benefits relating
                     to any recurrence, aggravation, acceleration, exacerbation or deterioration of any pre-existing
                     condition which I may have arising out of or in course of, the engagement.</td>
             </tr>
         </table>
         <table class="align_top">
             <tr>
                 <td>4.</td>
                 <td>Please tick whichever of the following statements is applicable: I have suffered no prior injuries
                     that may recur or deteriorate, accelerate or be exacerbated or aggravated by the engagement. I have
                     suffered the following conditions that may recur or deteriorate, accelerate or be exacerbated or
                     aggravated by the engagement.</td>
             </tr>
         </table>
 
     </div>
 </div>



 <div style="padding:15px 40px">
 <div style="padding:15px; border:1px solid #1e1e1e">
 <div>Please list details of all pre-existing conditions:</div>
 <div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div style="height: 25px"></div>
     <div class="maring_left_dotted"><dottab/></div>
     <div>I acknowledge and declare that the information provided in this form is true and correct in every Particular.</div>
 </div>
</div>
<h4 style="text-align:center">Consent Form- Release of Personal Information</h4>
</div>

<div class="PDF_Create_Date">
    <div class="set_date_salutation">
        <div style="float: left; width: 27px">TO:</div>
        <div style="float: left; width: 300px;" class="set_date_1">
            <div class="text_1"><?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></div>
            <div class="maring_left_dotted">
                <dottab />
            </div>
        </div>
    </div>
    <div width="70%" height="1px" style="background:#777"></div>
</div>

<div>
    <div class="Bold pt-4">Explanation of ‘Personal Information’ and what your ‘Consent’ means</div>
    <p class="mt-2 mb-0">ONCALL keeps information about you on what we call a client file.</p>
    <p class="mt-2 mb-0">Client files include a variety of information including: names; contact details; dates of
        birth; referral
        information; assessments of your needs; your goals; information about support which can help you achieve these
        goals; and information about the other agencies and services who may be working with us to assist you.</p>

    <div class="Bold pt-4">Why do we collect this information?</div>
    <p class="mt-2 mb-0">The information we collect helps us to:</p>
    <ol style="padding-left:20px;" type="disc">
        <li>Provide the most effective response to your individual needs</li>
        <li>Plan services with you</li>
        <li>Administer and manage the services we provide</li>
        <li>Keep records of the people we work with for reporting to DHS as required by law Keep records of our actions
            so that we are accountable for them.</li>
    </ol>
</div>

<div>
    <div class="Bold pt-4">Who else may see this information?</div>
    <p class="mt-2 mb-0">We aim to provide you with the best possible support. This may mean involving other support
        services to help you.</p>
    <p class="mt-2 mb-0">Sometimes ONCALL needs to give some of your information to other people to make sure you get
        the best support possible. Generally, we do this with your knowledge and permission, or ’Consent’. The
        information that we keep on your client file can only be read by the professionals from these services who will
        be involved in providing assistance to you and your family.</p>
    <p class="mt-2 mb-0">From time to time ONCALL will be audited to make sure we are meeting the standards that we
        operate under. Our auditors may want to look at your client file to help them understand how we support you, and
        will only do so with your consent. Our auditors sign a code of conduct and follow a code of ethics and they will
        only use your personal information for the purpose of their work and will not pass it on to any other person.
    </p>

    <p class="mt-2 mb-0">Aside from this, we will only release information about you and your family if you give your
        consent, or if we are required to do so by law</p>
    <p class="mt-2 mb-0">Please read this form carefully before signing. <b>Your:</b></p>
    <p class="mt-0 mb-0"><b>Consent:</b></p>

    <p class="mt-2 mb-0">If you sign this form, you will be allowing ONCALL to release your personal information for the
        purposes explained on page 1 of this form.</p>
    <p class="mt-2 mb-0">If you have difficulty understanding this form, you should ask someone you trust (an
        ‘Independent Person’) to explain it to you and to help you to understand it..</p>
    <p class="mt-2 mb-0">PLEASE NOTE: You can choose not to give your consent, but if so, ONCALL may not be able to
        provide the best possible service to you. If you choose to give consent, you may withdraw it at any time.</p>
</div>


<div class="pt-4">
    <table width="100%"  border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td>“I consent to ONCALL releasing my personal information for the purposes explained on Page 1”.</td>
            <td>Please tick the box if you agree</td>
            <td><input type="checkbox" checked /></td>
        </tr>
    </table>
</div>


<pagebreak />
<div>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Integrity:</td>
            <td width="70%">All ONCALL staff will act ethically, with integrity, honesty and transparency, and
                steadfastly adhere to high moral principles and professional standards at all times</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Respect:</td>
            <td width="70%">All ONCALL Staff will show consideration and treat all people and property with respect.
                Positively accept and welcome diversity in all people and cultures regardless of any differences,
                including disability, background, race, religion, gender, sexual identity or age</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Accountability:</td>
            <td width="70%">We all accept and take personal responsibility for our own actions and behaviours, ensuring
                we are trustworthy, transparent and meet or exceed assigned tasks, obligations and to admit mistakes
            </td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Teamwork:</td>
            <td width="70%">We all will strive to work cooperatively and effectively as part of a group, large or small,
                acting and working together in the interests of a common goal and in line with ONCALL person centred
                approached</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Leadership:</td>
            <td width="70%">We will all take a role in leading by example, as individuals, teams and as an organisation
                within our sector, working toward the achievement of ONCALL’s vision and goals</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Leadership:</td>
            <td width="70%">We uphold to always treat people with dignity and respect, upholding fundamental rights to
                which a person is inherently entitled simply because she or he is a human being.</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Commitment to Human Rights:</td>
            <td width="70%">We uphold to always treat people with dignity and respect, upholding fundamental rights to
                which a person is inherently entitled simply because she or he is a human being.</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Advocacy:</td>
            <td width="70%">To act or process of support or defence of a person or cause, including commitment to report
                any form of abuse or suspected abuse</td>
        </tr>
    </table>
    <table width="100%" border="1" style="border-collapse:collapse" cellpadding="15px">
        <tr>
            <td width="30%" class="text-Bold">Professional Boundaries:</td>
            <td width="70%">Boundaries are mutually understood, unspoken physical and emotional limits of the
                relationship between the person being supported and the worker</td>
        </tr>
    </table>
</div>


<pagebreak/>
<div>
<div class="Bold pt-4">Integrity</div>
<ol style="padding-left:20px;" type="disc">
    <li>Always act honestly, transparently and with integrity in the performance of your duties, when making decisions
        or
        revealing information</li>
    <li>Ensure any advice given is current, based on available facts and data and within the boundaries of
        the role you are employed for</li>
    <li>Maintain a strict separation between work related and personal financial matters</li>
    <li>Exercise your power in a way that is fair and reasonable ensuring that family or other personal relationships do
        not improperly
        influence your decisions</li>
    <li>Respect the rights and dignity of those affected by your decisions and actions, including individuals’ rights to
        freedom of expression, self-determination and decision making</li>
    <li>Official and personal information is handled according
        to relevant legislation, policies and procedures</li>
    <li>Public comment should always be discussed with management prior to
        making any such comment/s. Public comments must always be restricted to factual information and avoid the
        expression
        of a personal opinion.</li>
    <li>Report to an appropriate authority workplace behaviour that violates any law, rule or
        regulation or represents corrupt conduct, mismanagement of funds or is a danger to public health or safety or to
        the
        environment</li>
    <li>Report to an appropriate authority immediately any form of abuse or suspected abuse</li>
    <li>Declare and avoid
        conflicts of interest to help maintain workplace and community trust and confidence.</li>
    <li>Do not use your power to
        provide a private benefit to yourself, your family, your friends or associates</li>
    <li>Only engage in other employment where
        the activity does not conflict with your role as an employee of ONCALL (Employment includes a second job,
        conducting
        a business, trade or profession, or active involvement with other organisations in a paid or voluntary role)
    </li>
    <li>Behave
        in a manner that does not bring yourself or ONCALL into disrepute</li>
    <li>Advise your manager immediately and in writing if
        you are charged with a criminal offence, which is punishable by imprisonment or, if found guilty, could
        reasonably
        be seen to affect your ability to meet the inherent requirements of the work you are engaged to perform</li>
    <li>Carry out
        your work safely and avoid conduct that puts yourself or others at risk. This includes the misuse of alcohol,
        drugs
        and other substances when at work or engaged in work related activities</li>
    <li>If you are on medication that could affect
        your work performance or the safety of yourself or others, inform your manager immediately to ensure any
        necessary
        precautions or adjustments to your work can be put in place</li>
    <li>Listen and respond to the views and concerns of clients
        (including children), particularly if they are telling you that they or another person has been abused and/or
        are
        worried about their safety or the safety of another</li>
</ol>

<pagebreak/>
<div class="Bold pt-4">Respect</div>
<ol style="padding-left:20px;" type="disc">
    <li>Lead by example and promote an environment that encourages respect and is free from discrimination, bullying,
    harassment and abuse</li>
    <li>Positively embrace diversity and ensure all people are treated equally and respectfully
    regardless of culture, religion, gender, age, sexual orientation, race or disability</li>
    <li>Be fair, objective and
    courteous in your dealings with individuals, organisations, community and other employees</li>
    <li>Ensure privacy and
    confidentiality are adhered to all times in accordance with legislation, policies and procedures relating to and
    dealing with private information</li>
    <li>Be aware of and actively listen to the expressed needs, values and beliefs of
    people from cultural, religious and ethnic groups that are different from yours, regarding culturally relevant needs
    that affect the delivery of service.</li>
    <li>Promote the cultural safety, participation and empowerment of Aboriginal
    people’s, including children, (for example, by never questioning an Aboriginal person’s self-identification) </li>
    <li>Promote the cultural safety, participation and empowerment of people, including children, with culturally and/or
        linguistically diverse backgrounds (for example, by having a zero tolerance of discrimination)</li>
    <li>Promote the safety,
        participation and empowerment of people with a disability, including children, (for example, during personal
        care
        activities)</li>
    <li>Be conscientious and efficient in your work striving for excellence at all times</li>
    <li>Contribute both
        individually and as part of a team and engage constructively with your colleagues on work related matters</li>
    <li>Share
        information with team members to support delivery of the best and most appropriate service outcomes</li>
</ol>

<div class="Bold pt-4">Accountability</div>
<ol style="padding-left:20px;" type="disc">
    <li>Work to the clear objectives of your role and if goals and objectives are unclear, discuss it with your
    manager.</li>
    <li>Take personal responsibility for your own actions and behaviours, ensuring you are trustworthy, transparent
    and meet or exceed assigned obligations or tasks and to admit to any mistakes</li>
    <li>Consider the impact of your decisions
    and actions on ONCALL, the individuals you support, other organisations, the community and other employees</li>
    <li>Use work
    resources and equipment efficiently and only for appropriate purposes as authorised by your employer. Work resources
    include: physical, financial, technological and intellectual property</li>
    <li>Always seek to achieve value for money and use
    resources in the most effective way possible</li>
    <li>Identify opportunities for continuous improvement to achieve best
    possible efficiency and responsiveness to processes and service delivery</li>
    <li>Maintain accurate and reliable records as
    required by relevant legislation, policies and procedures</li>
    <li>Records are to be kept in such a manner as to ensure their
    security and reliability and are made available to appropriate scrutiny when required</li>
    <pagebreak/>
    <li>Notify your manager of any
    loss, suspension of or change to a registration, accreditation, license or other qualification that affects your
    ability to meet relevant essential requirements or to perform your duties</li>
    <li>Ensure you are aware of and comply with
    all policies, procedures and legislation relevant to the performance of your duties</li>
    <li>Do not refuse to follow a lawful
    or reasonable management direction or instruction </li>
</ol>


<div class="Bold pt-4">Teamwork</div>
<p class="mt-0 mb-0">All employees should work cooperatively and effectively with colleagues or customer organisations
    to ensure the best
    possible support is provided – showing Reliability, Integrity, Responsibility, Attitude and Initiative.</p>
<ol style="padding-left:20px;" type="disc">
    <li>Reliability: Work cooperatively and demonstrate that you are reliable - arrive at work on time</li>
    <li>Integrity: Complete
        all tasks assigned or expected of you, ensuring you perform all tasks and providing support to clients to a high
        standard.</li>
    <li>Responsibility: Ensure all documentation is completed and the work area is left clean and tidy. Report all
        abuse or suspected abuse. Take responsibility for your own actions and behaviour.</li>
    <li>Positive Attitude: Be positive, keep all negative comments to yourself and smile. Avoid discussing personal
        issues in the workplace.</li>
    <li>Initiative: If you have completed your set tasks, look around to see if there are any additional tasks you may
        be able to do, and if in doubt ask.</li>
</ol>


<div class="Bold pt-4">Leadership</div>
<p class="mt-0 mb-0">All employees of ONCALL should demonstrate leadership by actively implementing, promoting and
    supporting these values</p>
<ol style="padding-left:20px;" type="disc">
    <li>Lead by example</li>
    <li>Be honest</li>
    <li>Make decisions free of bias and in line with ONCALL’s person centered approach</li>
    <li>Be
        transparent, responsible, use resources efficiently, invite scrutiny</li>
    <li>Treat all others fairly and without
        discrimination</li>
    <li>Work co-operatively with your colleagues</li>
    <li>Support and learn from your colleagues and accept
        differences in personal style </li>
</ol>

<div class="Bold pt-4">Commitment to Human Rights:</div>
<ol style="padding-left:20px;" type="disc">
    <li>Respect and promote the human rights as set out in the Charter of Human Rights and Responsibilities </li>
    <li>Embrace and
    advocate that everyone has the right to be respected, to feel safe and to be free from abuse.</li>
    <li>Uphold ONCALL’s zero
    tolerance policy towards abuse of children and people with a disability.</li>
    <li>Make decisions consistent with human
    rights</li>
    <li>Protect and implement human rights</li>
    <li>Report all abuse or suspected abuse </li>
</ol>


<div class="Bold pt-4">Advocacy</div>
<ol style="padding-left:20px;" type="disc">
    <li>As an organisation and as individuals, we have a responsibility to protect and advocate for our clients who are
        vulnerable.</li>
    <li>Encourage people with a disability and children to ‘have a say’ and participate in all relevant
        organisational activities where possible, especially on issues that are important to them.</li>
    <li>Seek advice from a
        manager if you are unclear on the correct procedures when advocating on behalf of a person you support</li>
    <li>Understand
        the boundaries within the scope of your position</li>
</ol>

<div class="Bold pt-4">Professional Boundaries</div>
<ol style="padding-left:20px;" type="disc">
    <li>Carry out your duties professionally, skilfully, competently and to the best of your ability within the scope of
        your role</li>
    <li>Behave in a manner that maintains the trust and integrity expected from you by ONCALL</li>
    <li>Sexual relationships
        between staff and clients/customer whom they work with are strictly prohibited. Always report sexual misconduct
        and
        abuse.</li>
    <li>Be prompt and courteous when dealing with the people we support, other stakeholders, employees of other agencies
        and members of the public</li>
    <li>Use courteous and business-like language in all correspondence and other communications to
        or with the public, other employees and stakeholders</li>
    <li>Always conduct yourself or act in such a way as to ensure that
        the good name of ONCALL and of other stakeholders is maintained at all times</li>
    <li>Do not disclose information about a
        person ONCALL supports except when the appropriate Manager/Executive Manager has approved such release of
        information and the stakeholder is authorised or required by an Act or other law to do so</li>
    <li>Do not use any property of
        ONCALL’s except in the pursuit of official duties of ONCALL or as otherwise duly authorised</li>
    <li>The use of a personal
        mobile phone and text messaging while on duty is not permitted, unless otherwise agreed by the Manager</li>
    <li>Never store
        or retain private contact details (including photos, phone numbers, email or Facebook) of clients or clients’
        families nor provide your own personal contact details directly to clients or families</li>
    <li>The use of ONCALL internet
        and email software will be in accordance with Use of Electronic Systems and Communications Policy.</li>
    <li>Always present
        yourself in a neat and professional manner wearing clothing appropriate to the role you fulfil in the workplace.
        Closed shoes must be worn at all times.</li>
    <li>Always behave and act in a way to ensure that you do not become liable to
        conviction of a criminal offence within the law</li>
    <li>Be responsible for the care of clients and ensure they are treated
        with due regard for justice and with decency. Be courteous and avoid any actions that may bring your conduct
        into
        question</li>
    <li>Treat clients fairly and do not abuse or exploit their position for personal gain</li>
    <li>Develop any ‘special’
        relationships with clients that could be seen as favouritism</li>
    <li>Advise your manager of involvement in a relationship
        with a client’s family or other associates, direct or indirect, to avoid any potential conflict of interest</li>
    <li>Do not
        demand or receive a fee, reward, commission or benefit of any kind, from any person or organisation, for the
        initiation, conduct, omission or conclusion of any business, by any person or organisation with ONCALL.</li>
    <li>Do not
        accept any gifts from a client, or relatives or friends of any client (including gifts under a Will), unless
        prior
        authority has been given by your manager</li>
    <li>Staff are strictly prohibited from being the executor of a client’s
        will.</li>
    <li>Do not provide any comment, opinion or information to the media relating to the business of ONCALL or
        concerning employment with ONCALL without being authorised to do so</li>
    <li>Alcohol, illicit drugs and other substances can
        compromise your judgement and therefore your ability to uphold your duty of care to vulnerable clients and
        others.
        Therefore, ONCALL adopts a strong, unequivocal stance as depicted below:
        <ol style="padding-left:20px;" type="disc">
            <li>Do not arrive to work under the influence of alcohol or illicit drugs or undertake any duties in an
                inebriated or drug affected state</li>
            <li>Do not bring into the workplace any alcohol, drugs or any other illicit substance</li>
        </ol>
    </li>
    <li>Responsible drinking of alcohol at ONCALL social functions as authorised by Management is permitted in accordance
    with the Social Functions Policy.</li>
    <li>Do not possess on the premises or in any workplace (including community-based
    support) any unauthorised weapon(s) or article(s) intended for use as such, whether for offensive or defensive
    purposes</li>
    <li>Have any online, phone, direct or any other contact with a client or their family outside professional
    duties. </li>
</ol>
</div>

<div class="Bold pt-4">No employee of ONCALL shall wilfully:</div>
<ol style="padding-left:20px;" type="disc">
    <li>Make any false entry in any book, record or document</li>
    <li>Make any false or misleading statement or any statement they
    know to be inaccurate or significantly incomplete</li>
    <li>Omit to make any required entry in any book, record or
    document</li>
    <li>Destroy or damage any book, record or document required by law or direction to be kept by ONCALL</li>
    <li>Furnish
    any false return or statement of any money or property</li>
    <li>Steal or fraudulently misappropriate or obtain money/goods
    from ONCALL, other stakeholders, clients, volunteers or contractors</li>
    <li>Breach Occupational Health & Safety policies and
    procedures of ONCALL, or any relevant legislation</li>
    <li>Damage or sabotage any property of ONCALL</li>
    <li>Assault, abuse or harass
    sexually or otherwise or discriminate against any client, volunteer, contractor or other stakeholder.</li>
    <li>Absent
    themselves from work for other than an authorised absence</li>
    <li>Disclose any information, or supply any document
    concerning ONCALL’s business, current or former stakeholders or clients or the content of ONCALL’s contracts or
    procedures, without the express written permission of your manager, unless required to do so by law</li>
    <li>When leaving the
    employment of ONCALL you should not use confidential information obtained during your employment to advantage a
    prospective employer or disadvantage ONCALL in commercial or other relationships with your prospective employer</li>
</ol>

<div class="Bold pt-4">Misconduct</div>
<ol style="padding-left:20px;" type="disc">
    <li>Misconduct allegations are the most serious of complaints made and may result in the Disciplinary policy and
    procedure being implemented by ONCALL management</li>
    <li>ONCALL may decide to stand down an employee while an investigation
    takes place. This means that the employee would be instructed not to come to work while the investigation is carried
    out, but you would be available for the purposes of the investigation</li>
    <li>If you are stood down, you are not permitted
    to have contact with other employees. This does not imply that ONCALL believes you to be guilty, it is a precaution
    to protect the integrity of the investigation</li>
</ol>
</div>


<div>
    <h3 class="Bold pt-4 text-align:center">Position Description: Labour Hire Worker</h3>
    <p class="mt-2 mb-0">ONCALL Personnel & Training is an Award winning, Quality Certified and DHS registered service
        provider. We specialize
        in providing creative and flexible workforce solutions through the recruitment, outplacement and ongoing
        management
        of disability, aged care and welfare support staff. We work in partnership with a great number of organizations
        including Government (federal, state & local), Community Service organizations and private businesses and are
        recognized as a Sector Leader</p>
    <p class="mt-2 mb-0"><b>ONCALL</b> consists of a number of business units headed by managers with experience in the
        Disability, Welfare and Aged Care sectors.</p>
    <div class="pt-2">Our business units comprise of the following:</div>
    <ol style="padding-left:20px;" type="disc">

        <li><b>Casual Staff Services in Disability and Welfare.</b> Our field workers are security checked and selected,
            recruited and
            managed by a team of skilled and experienced Allocations Consultants. The field workers undertake ongoing
            training
            to provide the best in care to the Clients of our Customer agencies.</li>
        <li><b>Client and NDIS Services</b> provides individual
            clients and their families’ customised selfdirected support in their own homes or in the community.</li>
        <li><b>Accommodation
                Services - Disability</b> provides expert support and accommodates clients with significant and complex
            needs in
            residential service settings and provides a complementing suite of services throughout metropolitan
            Melbourne and
            the Mornington Peninsula.</li>
        <li><b>OoHC</b> (Out of home care) provides support to young people who have been significantly
            impacted through trauma and neglect and have been referred through Child Protection. Our services aim to
            support
            young people in contingency accommodation, emergency placements and in-home support. </li>
    </ol>
    <div class="pt-4">
    <div style="width: 30%; float:left">
            Position Title<br/>
            Position<br/>
            Type Reporting to:
    </div>
    <div style="width: 30%; float:left">
            Support Worker<br/>
            Labour hire worker<br/>
            Business Unit Manager
    </div>
</div>
</div>

<pagebreak />
<div class="Bold pt-4">Personal Competencies Required</div>
<div style="padding-left:10px">Reliable; trustworthy; responsible.</div>
<ol style="padding-left:20px;" type="disc">
    <li>Possessing solid verbal and written communication skills & demonstrated interpersonal skills.</li>
    <li>Able to work
    effectively as part of a team but also in a self-directed manner Demonstrating proven time management &
    organisational skills.</li>
    <li>Capable of working calmly under pressure.</li>
    <li>Flexible and adaptable, respond & adjust easily to
    change in work demands & circumstances.</li>
    <li>Representing cross cultural awareness; capable to communicate well with,
    relate to & see issues from the perspective of people from a diverse range of abilities, cultures & backgrounds. </li>
</ol>
</div>

<div class="Bold pt-4">Mandatory Competencies Required</div>
<ol style="padding-left:20px;" type="disc">
    <li>Current First Aid Level II</li>
    <li>Appropriate qualification and demonstrated experience in areas of engagement
    (certificate verification required followed by presentation of card when received).</li>
    <li>Current Working with Children
    Check (with Worker Status) (Certificate verification required followed by presentation of card when
    received) </li>
    <li><b>Knowledge & Experience of Manual Handling & Lifting, and Personal Care.</b> (Certificate verification
    required)</li>
    <li><b>Current Victorian or eligible Drivers Licence</b> (Drivers licence must be presented for verification)
    (Mandatory for Welfare Support – preferred for Disability Support)</li>
    <li><b>Roadworthy, Registered Vehicle with comprehensive
    motor vehicle insurance</b> which takes into consideration work activities.</li>
    <li><b>Administration of Medication trainingFire
    Safety Training for support workers</b>(Certificate verification required)</li>
    <li>Familiarity (or an undertaking to familiarise
    oneself) with Disability Worker Exclusion Scheme (DWES) and a commitment to fully comply and cooperate with its
    requirements and obligations.</li>
    <li>Welfare and OoHC workers become familiar with the Carer’s Register and a commitment to
    fully comply and cooperate with its requirements and obligations.</li>
    <li>Minimum qualification for OoHC: Certificate IV in
    Child Youth Family Intervention (Residential and OoHC), including a mandatory trauma unit of competency; or a
    recognised relevant qualification, plus completion of a short top up skills course.</li>
</ol>
</div>


<div class="Bold pt-4">Work Based Competencies</div>
<ol style="padding-left:20px;" type="disc">
    <li>Be aware of safe working conditions & implement safe working practices in accordance with Occupational Health &
        Safety legislation, in all work areas.</li>
    <li>Be aware of and observe all company’s reasonable & lawful directions, rules,
        messages, policies & procedures.</li>
    <li>Attend induction & training as required.</li>
    <li>Ensure First Aid Level II training /
        certificate is kept up to date</li>
    <li>Ensure all relevant qualifications are current</li>
    <li>Ensure Manual handling training and
        knowledge is current </li>
</ol>
</div>

<div class="Bold pt-4">Client & NDIS Services specific workplace competencies</div>
<ol style="padding-left:20px;" type="disc">
    <li>Have experience in a range of disability program ie: Adult and child in home respite, future for young adults,
        complex behaviour, mental health issues and community access.</li>
    <li>Experience with working 1:1 with individuals with a
        disability and their families. Knowledge of client and staff boundaries and proficient in incident reporting
        Accountability of client’s financial expenditure and funding guide-lines.</li>
    <li>You will be required to deliver personal
        assistance with day to day care, skill development and creating opportunities for the client.</li>
    <li>Familiarity in working
        in children services and this may include – respite, in-home recreation, school holiday programs</li>
    <li>Have knowledge of
        adolescent and adult support, such as day-to-day activities, community access, recreation, transportation,
        education
        and vocational support.</li>
    <li>Require a commitment to LifeChoices who work in partnership with individuals, their
        families, 1:1 staff, community organisation and governments to achieve valued life styles for people with
        disabilities.</li>
</ol>
</div>

<div class="Bold pt-4">Accommodation Services – not applicable to labour hire staff. A different recruitment process and
    contractual undertakings are required. You are welcome to make further enquiries with the LifeStyles team at ONCALL.
</div>
<ol style="padding-left:20px;" type="disc">
    <li>A division of ONCALL providing support to individuals in the least restrictive way, living in a sole or shared
        residential accommodation facility, in all areas of their lives.</li>
    <li>Assist individuals to access and engage in their
        community life.</li>
    <li>Commit to ongoing rostered shifts that include, team meetings and sleepover shifts as part of a team
        of staff.</li>
    <li>Contribute to the development and implementation of Individual Lifestyle Plans, Comprehensive Health
        Assessment Plans (CHAPS) and Behaviour Management Plans, (BSP) reviewing in a manner that is responsive to the
        decisions and choices of the individual.</li>
    <li>Provide a safe and healthy environment that supports individuals to
        exercise their human rights and responsibilities.</li>
    <li>Work collaboratively with all other people, including the
        individual’s personal networks.</li>
    <li>Complete accurate reporting and accountability in writing on a daily basis, ensuring
        all individual notes, including mandatory reporting of incidents under DHS guidelines.</li>
    <li>Ensure each individual is
        free to raise and have any complaints resolved.</li>
    <li>Ensure each individual is free from physical, sexual, verbal and
        emotional abuse and neglect.</li>
    <li>Recognise and respect each individual’s right to privacy, dignity and confidentiality
        in all aspects of their life.</li>
    <li>At all times demonstrate consistent verbal and written communication with LifeStyles
        leadership team.
    </li>
</ol>

<div class="Bold pt-4">Welfare</div>
<div>Welfare support work is mostly based around residential work with children, adolescents and young adults.</div>
<div>These include contingency units and residential units that are both government and nongovernment organisations.
</div>
<div>Welfare work involves dealing with risk taking and behaviours of concern. </div>
<div>The issues the client may have include:</div>
<ol style="padding-left:50px;" type="disc">
    <li>Mental health</li>
    <li>Mild intellectual disability</li>
    <li>Substance abuse</li>
    <li>Difficult to manage
        behaviours</li>
    <li>Aggression</li>
    <li>Violence</li>
    <li>Absconding</li>
    <li>Criminal activity</li>
    <li>Health conditions</li>
    <li>On regular medication etc</li>
</ol>

<div class="pt-2">The following skills are required of the support worker:</div>
<ol style="padding-left:50px;" type="disc">
    <li>Crisis management</li>
    <li>Ability to communicate effectively</li>
    <li>Strong interpersonal skills</li>
    <li>Ability to work with young people
        who exhibit high risk behaviors</li>
    <li>Documentation (incident reports, case notes etc)</li>
    <li>Ability to work within a structured
        supportive and professional environmen</li>
    <li>Understanding of conflict management and in particular defusing skills Qualification in Welfare work</li>
    <li>Previous
        engagement experience in the Welfare Industry</li>
</ol>


<div class="Bold pt-4">OoHC</div>
<div>Out of Home Care (OoHC) is a model of support to provide accommodation for young people who have been referred
    through Child Protection. OoHC supports young people in Residential homes, In-home support and Contingency
    (emergency) placements. The young people we work with are the highest risk young people in the State, with a history
    of varying levels of trauma and neglect.</div>
<div>The following skills are what we require our OoHC staff to demonstrate in this role: </div>
<div style="padding-left: 15px">Excellent written & verbal communications skills</div>
<ol style="padding-left:50px;" type="disc">
    <li>Understanding of Child Youth & Family Act 2005</li>
    <li>Ability to work in a Team to ensure best outcomes for young
        people.</li>
    <li>Working within a Strengths based model, person centred approach.</li>
    <li>Demonstrated ability and understanding of
        the impacts of Trauma and neglect on a young person.</li>
    <li>Demonstrated understanding of emotional and behavioural
        dysregulation of young people and the underlying causes behind this (trauma & attachment). </li>
</ol>


<div class="Bold pt-4 pb-2">Accountability</div>
<p class="mt-2 mb-0">Although engaged by ONCALL Personnel you are accountable to both ONCALL and the contracting
    organisation. Whilst
    engaged with the contracting organisation you must adhere to their policies & procedures. It is your responsibility
    to seek information in relation to the induction & orientation information as outlined on each timesheet. It is your
    responsibility to ensure you have the information required to fulfil the contracting organisations requirements.
    Please seek advice if you are unclear about any aspect of the work you have been assigned</p>


<div class="Bold pt-4 pb-2">Specific Accountabilit</div>
<ol style="padding-left:50px;" type="disc">
    <li>Develop relationship with Client families; Case manager & other support.</li>
    <li>Ensure all policy & procedures are
    enforced.</li>
    <li>Report any issues / complaints – feedback onto line Manager</li>
    <li>Complete all relevant documentation</li>
    <li>Adhere to
    confidentiality policy & procedures Attend all meetings as require  </li>
</ol>


<div class="Bold pt-4 pb-2">Administration Tasks:</div>
<ol style="padding-left:50px;" type="disc">
    <li>Report all incidents & potential hazards promptly and in accordance with policies and procedures</li>
    <li>Ensure timesheets
    are completed accurately and submitted in a timely manner.</li>
    <li>Once timesheet has been authorised by the worker no
    further claim can be recognised by ONCALL Personnel.</li>
    <li>Be aware of processes for grievances, complaints, concerns and
    compliment </li>
</ol>



<pagebreak />
<div>
    <p>This position description is subject to review and may change in accordance with the operational requirements of
        ONCALL Personnel & Management, its operations, and its clients and customersneeds.</p>
    <p>
        <div style="width: 130px; float:left">By signing here, I</div>
        <div style="width: 240px; height:15px; border-bottom: 1px solid #1e1e1e; float:left; "> <?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></div>
        <div style="float:left; margin-left:7px;"> hereby accept this offer of engagement</div>
    </p>
    <div> and all the inclusions within this contract on these terms and conditions.</div>

    <div class="PDF_Create_Date">
        <div class="pt-5 pb-3">
            <div><?php echo isset($complete_data['applicant_name']) ? $complete_data['applicant_name']:''?></div>
            <div width="35%" class="maring_left_dotted">
                <dottab />
            </div>
            <div>Labour hire worke</div>
        </div>
        <div class="set_date_salutation">
            <div class="py-3">Signature</div>
            <div style="float: left; width: 37px">Date:</div>
            <div style="float: left; width: 100px;" class="set_date_1">
                <div class="text_1"><?php echo date('d/m/Y');?></div>
                <div class="maring_left_dotted">
                    <dottab />
                </div>
            </div>
        </div>
        <div class="text-Bold py-3">SIGNED for ONCALL PERSONNEL & MANAGEMENT SERVICES PTY LTD ACN 114 585 116 inthe
            presence of:</div>
        <div>
            <img src="<?php echo base_url('assets/img/signature.png'); ?>" width="120px">
            <div width="35%" class="maring_left_dotted mt-2">
                <dottab />
            </div>
            Representative\'s signature
        </div>
        <div class="pt-4">
            <div class="text-Bold">Caroline Lane</div>
            Human Resources Manager<br />
            ONCALL Personnel and Management Pty Ltd<br />
        </div>
    </div>

</div>

<?php } ?>