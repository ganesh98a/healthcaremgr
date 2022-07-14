<?php
/**
 * Class: AmazonS3
 * This library used to upload the document|files to aws s3 bucket
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/aws/aws-autoloader.php';

use Aws\Common\Aws;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Credentials\Credentials;
use Aws\MockHandler;
use Aws\Exception\MultipartUploadException;
use Aws\Lambda\LambdaClient;

use Aws\CommandInterface;
use Aws\Result;
// use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise;
use Aws\Middleware;
use Aws\ResultInterface;

class AmazonLambda {

    Protected $bucket;
    Protected $profile;
    Protected $location_region = 'ap-southeast-2';
    Protected $access_key_id;
    Protected $secert_access_key;
    // ACL flags
	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';

	public function __construct()
    {
       // set aws configure profile
        $s3_profile = getenv('AWS_S3_PROFILE') ? getenv('AWS_S3_PROFILE') : 'default';
        $this->profile = $s3_profile;
    }

    
    /**
     * Set AWS config using Lambda Client
     * - Access Key Id
     * - Secret Access Key
     */
    public function getConfigLambda() {

        // Instantiate the S3 client with your AWS credentials
        return LambdaClient::factory(array(
            'region'  => $this->location_region,
            'version' => 'latest',
        ));
    }
    
     /** Invoke Lambda for Shift */
     public function lambdaShift($member_vector) {
        try {
            $event_array =  array('mv'=> $member_vector);
            $event = json_encode($event_array);
            $lambdaClient = $this->getConfigLambda();
		
            $result = $lambdaClient->invoke([
                // The name your created Lamda function
                'FunctionName' => 'manhattan_shift',
                'Payload' => $event,
			 ]);
			  
           return json_decode((string) $result->get('Payload'));
			
        // return $result;
           

       } catch (Exception $e) {

            return ['status' => 400, 'message' => $e->getMessage()];

       }
    }//end
}
