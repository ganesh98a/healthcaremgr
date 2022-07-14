<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Enums of possible keys for 'tbl_settings'.
 * 
 * Tip: It is recommended that the config keys you put in DB are also present here
 */
class Setting 
{
    const DS_STAGING_USERNAME = 'DS_STAGING_USERNAME';
    const DS_STAGING_PASSWORD = 'DS_STAGING_PASSWORD';
    const DS_STAGING_INTEGRATATION_KEY = 'DS_STAGING_INTEGRATATION_KEY';

    const PRIVACY_IDEA_OTP_ENABLED = 'PRIVACY_IDEA_OTP_ENABLED';
    
    // Schedule variables
    const OVERTIME_ALLOWED = 'OVERTIME_ALLOWED';
    const GAP_BETWEEN_SHIFTS = 'GAP_BETWEEN_SHIFTS';
    const GOOGLE_DURATION_CHECK_ALLOWED = 'GOOGLE_DURATION_CHECK_ALLOWED';

    // Seek Const variable
    const SEEK_USERNAME = 'SEEK_USERNAME';
    const SEEK_PASSWORD = 'SEEK_PASSWORD';
    const SEEK_ADVERTISER_NAME = 'SEEK_ADVERTISER_NAME';
    const SEEK_ADVERTISER_ID = 'SEEK_ADVERTISER_ID';

    // SMS Default type
    const SMS_ATTRIBUTE_TYPE = 'SMS_ATTRIBUTE_TYPE';

    # Access Token for migration
    const DATA_MIGRATION_ACCESS_TOKEN = 'DATA_MIGRATION_ACCESS_TOKEN';

    // // Template
    // const XXXXXXXXXXX = 'XXXXXXXXXXX';
    // const YYYYYYYYYYY = 'YYYYYYYYYYY';

    // @todo: Additional settings key here
}
