<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	https://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are three reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router which controller/method to use if those
  | provided in the URL cannot be matched to a valid route.
  |
  |	$route['translate_uri_dashes'] = FALSE;
  |
  | This is not exactly a route, but allows you to automatically route
  | controller and method names that contain dashes. '-' isn't a valid
  | class or method name character, so it requires translation.
  | When you set this option to TRUE, it will replace ALL dashes with
  | underscores in the controller and method URI segments.
  |
  | Examples:	my-controller/index	-> my_controller/index
  |		my-controller/my-method	-> my_controller/my_method
 */

/*
 *  Admin panal routing
 */
$route['default_controller'] = 'admin/Login';
$route['dashboard'] = 'admin/Dashboard';
$route['logout'] = 'admin/Login/logout';
$route['forgot-password'] = 'admin/Login';
$route['reset-password/(:any)/(:any)'] = 'admin/Login/reset_password/$1/$2';
$route['user'] = 'admin/Dashboard';
$route['create-user'] = 'admin/Dashboard';
$route['roles-list'] = 'admin/Dashboard';
$route['jobs/(:any)'] = 'recruitment/RecruitmentAppliedForJob/job_preview/$i';
$route['job/getSeekDetails'] = 'recruitment/RecruitmentAppliedForJob/getSeekDetails';
$route['api'] = 'admin/Api/callapi';
$route['preview_consent_service_agreement'] = 'sales/ServiceAgreement/preview_consent_service_agreement';
$route['get_file/(:any)/(:any)'] = 'common/Common/get_file/$i/$2';

/*
* Member routing
*/
$route['get_members_list'] = 'member/MemberDashboard/get_member_search';
$route['admin/Login/verify_ocs_admin_token'] = 'admin/Login/verify_reset_password_token';
$route['mediaShowProfile/(:any)/(:any)/(:any)/(:any)'] = 'common/Common/mediaShowProfile/$1/$3/$2/$4';
$route['mediaShowProfile/(:any)/(:any)/(:any)'] = 'common/Common/mediaShowProfile/$1/$3/$2';
$route['mediaShowProfile/(:any)/(:any)'] = 'common/Common/mediaShowProfile/$1/$2';
$route['mediaShowDocument/(:any)/(:any)/(:any)'] = 'common/Common/mediaShowProfile/$1/$3/$2';
$route['mediaShowDocument/(:any)/(:any)/(:any)/(:any)'] = 'common/Common/mediaShowProfile/$1/$3/$2';
$route['mediaShowDocument/(:any)/(:any)'] = 'common/Common/mediaShowProfile/$1/$2';
$route['mediaShow/(:any)/(:any)/(:any)'] = 'common/Common/mediaShow/$1/$3/$2';
$route['mediaShowForm/(:any)/(:any)/(:any)'] = 'common/Common/mediaShowDownloadForm/$1/$3/$2';
$route['mediaDownload/(:any)/(:any)'] = 'common/Common/mediaDownload/$1/$2';
$route['mediaDownload/(:any)'] = 'common/Common/mediaDownload/all/$1';
$route['mediaShowView'] = 'common/Common/cookie_set';
$route['mediaShowTemp/(:any)'] = 'common/Common/mediaShowTemp/$1';
$route['mediaShowTempAndDelete/(:any)'] = 'common/Common/mediaShowTempAndDelete/$1';
$route['mediaShow/SA/(:any)'] = 'common/Common/mediaShowSA/$1';
$route['mediaShow/EA/(:any)'] = 'common/Common/mediaShowEA/$1';
$route['mediaShow/f/(:any)'] = 'common/Common/mediaShow/f/$1';
$route['mediaImailShowEA/EA/(:any)'] = 'common/Common/mediaImailShowEA/$1';
$route['mediaShowView/(:any)/(:any)'] = 'common/Common/mediaShowView/$1/$2';
$route['storeCMScontent'] = 'common/Common/storeCMScontent/';
$route['getAdminCMScontent'] = 'common/Common/getAdminCMScontent/';
$route['getMemberCMScontent'] = 'common/Common/getMemberCMScontent/';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
