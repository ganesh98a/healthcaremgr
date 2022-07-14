<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class DocuSignAuthConfig{ 
	public function __construct()
    {
        require_once APPPATH.'third_party/docusign/vendor/autoload.php';
    }
	
	public function getConfig(){		
		$config = new DocuSign\eSign\Configuration();
        $config->setHost(DS_STAGING_HOST);

        $DS_STAGING_USERNAME = get_setting(Setting::DS_STAGING_USERNAME, DS_STAGING_USERNAME);
        $DS_STAGING_PASSWORD = get_setting(Setting::DS_STAGING_PASSWORD, DS_STAGING_PASSWORD);
        $DS_STAGING_INTEGRATATION_KEY = get_setting(Setting::DS_STAGING_INTEGRATATION_KEY, DS_STAGING_INTEGRATATION_KEY);

        $config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"" . $DS_STAGING_USERNAME . "\",\"Password\":\"" . $DS_STAGING_PASSWORD . "\",\"IntegratorKey\":\"" . $DS_STAGING_INTEGRATATION_KEY . "\"}");
		return $config;		
	}
}
?>