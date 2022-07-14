<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call([
            ActivitySeeder::class,
            GoalRatingSeeder::class,
            CompanySeeder::class,
            StateSeeder::class,
            ShiftIncidentTypeSeeder::class,
            PlaceSeeder::class,
            PlanManagementBillerSeeder::class,
            FmsCaseAllCategorySeeder::class,
            OrganisationRequirementSeeder::class,
            ParticipantGenralSeeder::class,
            PermissionSeeder::class,
//            AdminEmailSeeder::class,
//            AdminPhoneSeeder::class,
            RoleSeeder::class,
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
//            MemberSeeder::class,
            ShiftRequirementSeeder::class,
            ShiftRequirementOrgSeeder::class,
            ClassificationLevelSeeder::class,
            ClassificationPointSeeder::class,
            SupportTypeSeeder::class,
            CrmDepartmentSeeder::class,
            CrmStageSeeder::class,
            RecruitmentChannelSeeder::class,
            CrmtaskprioritySeeder::class,
            EmploymentTypeSeeder::class,
            RecruitmentJobTypeSeeder::class,
            RecruitmentJobTemplate::class,
            RecruitmentJobSalaryRangeSeeder::class,
            EmploymentTypeSeeder::class,
            RecruitmentTaskStage::class,
            RecruitmentDepartment::class,
            RecruitmentJobRequirementDocs::class,
            RecruitmentStageLabel::class,
            RecruitmentStage::class,
            XeroDetailsSeeder::class,
            ModuleTitle::class,
            RecruitmentApplicant::class,
            RecruitmentApplicantEmail::class,
            RecruitmentApplicantPhone::class,
            RecruitmentApplicantSeekAnswer::class,
            RecruitmentApplicantStage::class,
            RecruitmentApplicantAppliedApplication::class,
            RecruitmentJobPosition::class,
            RecruitmentQuestionTopic::class,
            NDISSupportSeeder::class,
            RecruitmentApplicantDocCategory::class,
            RecruitmentLocation::class,
            RecruitmentInterviewType::class,
            RecruitmentJobCategory::class,
            RecruitmentSeekQuestion::class,
            RecruitmentApplicantWorkArea::class,
            FinanceFundingType::class,
            FinanceSupportOutcomeDomain::class,
            FinanceSupportCategory::class,
            FinanceSupportRegistrationGroup::class,
            FinanceTimeOfTheDay::class,
            WeekDay::class,
            FinanceEnquiryCustomerCatogory::class,
            FinancePayrateCategory::class,
            PayrateType::class,
            FinanceAdditionalPaypointRateType::class,
            KeyPayDetailsSeeder::class,
            FinanceMeasure::class,
            RecruitmentApplicantDuplicateStatus::class,
            PlanDisputeContactMethod::class,
            CommunicationType::class,
            CrmRelationsParticipant::class,
            LivingSituation::class,
            MaritalStatus::class,
            CognitiveLevel::class,
            Relations::class,
            Language::class,
            ReligiousBeliefs::class,
            Ethnicity::class,
            SuburbStateSeeder::class,
            ReferenceDataSeeder::class,
			RecruitmentApplicantPresentation::class  //keep in last
        ]);
    }

}