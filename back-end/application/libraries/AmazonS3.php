<?php
/**
 * Class: AmazonS3
 * This library used to upload the document|files to aws s3 bucket
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/aws/aws-autoloader.php';

use Aws\Common\Aws;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Credentials\Credentials;
use Aws\MockHandler;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

use Aws\CommandInterface;
use Aws\Result;
// use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise;
use Aws\Middleware;
use Aws\ResultInterface;

class AmazonS3 {

    Protected $bucket;
    Protected $profile;
    Protected $location_region = 'ap-southeast-2';
    Protected $access_key_id;
    Protected $secert_access_key;

    Private $source_file;
    Private $folder_key;

    // ACL flags
	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';

	public function __construct()
    {
        // set values
        $s3_bucket = getenv('AWS_S3_BUCKET') ? getenv('AWS_S3_BUCKET') : '';
        $this->bucket = $s3_bucket;
        // set aws configure profile
        $s3_profile = getenv('AWS_S3_PROFILE') ? getenv('AWS_S3_PROFILE') : 'default';
        $this->profile = $s3_profile;
    }

    /**
     * Set source file of file
     */
    public function setSourceFile($source_file) {
        return $this->source_file = $source_file;
    }

    /**
     * Set key (fiename) with or not folder
     */
    public function setFolderKey($folder_key) {
        return $this->folder_key = $folder_key;
    }

    /**
     * Set AWS config using S3 Client
     * - Access Key Id
     * - Secret Access Key
     */
    public function getConfig() {

        // Instantiate the S3 client with your AWS credentials
        return S3Client::factory(array(
            'region'  => $this->location_region,
            'version' => 'latest',
        ));
    }

    /**
     * Create a valid bucket and used a LocationConstraint for customize region
     */
    public function createBucket() {
        try {
            // Get config
            $client = $this->getConfig();

            // Check if the bucket exist or not
            $myBucketExists = $client->doesBucketExist($this->bucket);

            // if not exist allow to create new one
            if (!$myBucketExists) {
                $result = $client->createBucket(array(
                    'Bucket'             => $this->bucket,
                    'LocationConstraint' => $this->location_region,
                ));
                $response = [ 'status' => 200, 'msg' => 'Bucket created successfully.', 'data' => $result ];
            } else {
                $response = [ 'status' => 400, 'error' => 'Bucket Already Exist.' ];
            }

            return $response;

        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray()  ];
        } catch (AwsException $e) {
            // This dumps any modeled response data, if supported by the service
            var_dump($e->toArray());
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }

    /**
     * List all created bucket
     */
    public function listBuckets() {
        try {

            $validate_res = $this->validateBucket();
            $status = $validate_res['status'];

            if ($status == 200) {
                // Get config
                $client = $this->getConfig();

                // get all bucket list
                $result = $client->listBuckets();

                return [ 'status' => 200, 'msg' => 'Fetch Bucket list successfully.', 'data' => $result ];
            } else {
                return $validate_res;
            }
        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray()  ];
        } catch (AwsException $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }

    /**
     * Copy Upload document to archive folder using copyObject
     */
    public function moveToArchive() {
        try {

            $validate_res = $this->validateBucket();

            $status = $validate_res['status'];

            if ($status == 200) {
                // Get config
                $client = $this->getConfig();

                // upload file data
                $result = $client->copyObject(array(
                    'Bucket'     => $this->bucket,
                    'ACL' => self::ACL_PUBLIC_READ_WRITE,
                    'Key'        => $this->folder_key,
                    'CopySource' => $this->source_file,
                ));

                // We can poll the object until it is accessible
                $client->waitUntil('ObjectExists', array(
                    'Bucket' => $this->bucket,
                    'Key'    => $this->folder_key
                ));

                return [ 'status' => 200, 'msg' => 'File moved successfully.', 'data' => $result->toArray() ];

            } else {
                return $validate_res;
            }

        } catch (S3Exception $e) {

            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];

        } catch (AwsException $e) {

            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];

        }

    }

    /**
     * delete folder using deleteObject
     */
    public function deleteFolder() {

        try {
            $validate_res = $this->validateBucket();
            $status = $validate_res['status'];

            if ($status == 200) {
                // Get config
                $client = $this->getConfig();
                // upload file data
                $result = $client->deleteObject(array(
                    'Bucket'     => $this->bucket,
                    'Key'        => $this->folder_key,
                ));

                if ($result['DeleteMarker'] || ( isset($result['@metadata']) && ($result['@metadata']['statusCode'] == 200 || $result['@metadata']['statusCode'] == 204) ))
                {
                    return [ 'status' => 200, 'msg' => 'Document deleted successfully.', 'data' => $result->toArray() ];
                } else {
                    return [ 'status' => 400, 'msg' => 'Document was not deleted.', 'data' => $result->toArray() ];
                }

            } else {
                return $validate_res;
            }

        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];

        } catch (AwsException $e) {
            // Catch an Aws specific exception.

            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }

    }

    /**
     * Upload document to s3 bucket using putObject
     */
    public function uploadDocumentBYPutObject() {
        try {

            $validate_res = $this->validateBucket();
            $status = $validate_res['status'];

            if ($status == 200) {
                // Get config
                $client = $this->getConfig();

                // upload file data
                $result = $client->putObject(array(
                    'Bucket'     => $this->bucket,
                    'Key'        => $this->folder_key,
                    'SourceFile' => $this->source_file,
                    'ACL'    => self::ACL_PUBLIC_READ,
                ));

                // We can poll the object until it is accessible
                $client->waitUntil('ObjectExists', array(
                    'Bucket' => $this->bucket,
                    'Key'    => $this->folder_key,
                ));

                return [ 'status' => 200, 'msg' => 'Document uploaded successfully.', 'data' => $result->toArray() ];
            } else {
                return $validate_res;
            }

        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
        } catch (AwsException $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }

    /**
     * Upload document into custom S3 bucket
     */
    public function uploadDocumentwithCustomBucketName($conf_ary = '') {
        try {

            $validate_res = $this->validateBucket();
            $status = $validate_res['status'];

            if ($status == 200) {
                // Get config
                $client = $this->getConfig();

                // upload file data
                $result = $client->putObject(array(
                    'Bucket'     => $conf_ary['bucket_name'] ?? $this->bucket,
                    'Key'        => $this->folder_key,
                    'SourceFile' => $this->source_file,
                    'ACL'    => self::ACL_PUBLIC_READ_WRITE,
                ));
               
                return [ 'status' => 200, 'msg' => 'Document uploaded successfully.', 'data' => $result->toArray() ];
            } else {
                return $validate_res;
            }

        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
        } catch (AwsException $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }

    /**
     * Upload document to s3 bucket using Stream
     */
    public function uploadDocument() {
        $validate_res = $this->validateBucket();
        $status = $validate_res['status'];

        if ($status == 200) {
            // Get config
            $s3Client = $this->getConfig();

            //Using stream instead of file path
            $source = fopen($this->source_file, 'rb');
            $uploader = new MultipartUploader($s3Client, $source, [
                'Bucket' => $this->bucket,
                'Key'    => $this->folder_key,
            ]);

            do {
                try {
                    $result = $uploader->upload();
                    //Clear the carbage collection once upload done
                    gc_collect_cycles();
                    return [ 'status' => 200, 'msg' => 'Document uploaded successfully.', 'data' => $result->toArray() ];

                } catch (MultipartUploadException $e) {
                    rewind($source);
                    $uploader = new MultipartUploader($s3Client, $source, [
                        'state' => $e->getState(),
                    ]);
                    return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e ];
                }
            } while (!isset($result));

        }
    }

    /**
     * get document from s3 bucket using getObject
     */
    public function getDocument() {
        try {
            $validate_res = $this->validateBucket();
            $status = $validate_res['status'];

            if ($status == 200) {
                // Get config
                $client = $this->getConfig();

                if (!$this->folder_key) {
                    return [ 'status' => 400, 'error' => 'Key is required', 'data' => [] ];
                }

                // get file data
                $result = $client->getObject(array(
                    'Bucket'     => $this->bucket,
                    'Key'        => $this->folder_key,
                ));

                return [ 'status' => 200, 'msg' => 'Fetch document successfully.', 'data' => $result ];
            } else {
                return $validate_res;
            }
        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
        } catch (AwsException $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }

    /**
     * Validate bucket is exist or not and required
     */
    public function validateBucket() {
        try {

            if (!$this->bucket) {
                return [ 'status' => 400, 'error' => 'Bucket name is Missing', 'data' => [] ];
            }

            // Get config
            $client = $this->getConfig();

            // Check if the bucket exist or not
            $myBucketExists = $client->doesBucketExist($this->bucket);

            if (!$myBucketExists) {
                // Create new bucket if doesn't exist
                $createBucket = $this->createBucket();
                if ($createBucket['status'] == 200) {
                    return [ 'status' => 200, 'msg' => 'Bucket is created', 'data' => [] ];
                } else {
                    return [ 'status' => 400, 'error' => 'Bucket is not exist', 'data' => [] ];
                }

            }

            return [ 'status' => 200, 'msg' => 'Bucket is exist', 'data' => [] ];

        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
        } catch (AwsException $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }

    /**
     * Mock object create for upload Result
     */
    public function testUploadDocumentMock()
    {
        try {
            $mock = new MockHandler();
            // Return a mocked result
            $mock->append(new Result(
                array
                (
                    "Expiration" => "",
                    "ETag" => "fae6fca8dfd2ea12625925fccdecfba7",
                    "ServerSideEncryption" => "",
                    "VersionId" => "",
                    "SSECustomerAlgorithm" => "",
                    "SSECustomerKeyMD5" => "",
                    "SSEKMSKeyId" => "",
                    "SSEKMSEncryptionContext" => "",
                    "RequestCharged" => "",
                    "@metadata" => array
                        (
                            "statusCode" => 200,
                            "effectiveUri" => "https://".$this->bucket.".s3.ap-southeast-2.amazonaws.com/".$this->folder_key,
                            "headers" => array
                                (
                                    "x-amz-id-2" => "sFTUYWjgObbaFi8bgdYrkgsb6n11iKVEQ7AFhdWvrPmjRA4XAholQZpi/xlaGtBgCUgJoNUmfvI=",
                                    "x-amz-request-id" => "B7DA66567B6438FA",
                                    "date" => DATE_TIME,
                                    "etag" => "fae6fca8dfd2ea12625925fccdecfba7",
                                    "content-length" => "0",
                                    "server" => "AmazonS3"
                                ),
                            "transferStats" => array
                                (
                                    "http" => array
                                        (
                                            "0" => array
                                                (
                                                )
                                        )
                                )
                        ),
                    "ObjectURL" => "https://".$this->bucket.".s3.ap-southeast-2.amazonaws.com/".$this->folder_key
                )
            ));

            // You can provide a function to invoke; here we throw a mock exception
            $mock->append(function (CommandInterface $cmd, RequestInterface $req) {
                return new AwsException('Mock exception', $cmd);
            });

            // Create a client with the mock handler
            $client = new DynamoDbClient([
                'credentials' => [
                    'key' => getenv('AWS_ACCESS_KEY_ID')?? "",
                    'secret' =>  getenv('AWS_SECRET_ACCESS_KEY')?? "",
                ],
                'region'  => 'ap-southeast-2',
                'version' => 'latest',
                'handler' => $mock
            ]);
            $command = $client->getCommand('listTables');
            $command->getHandlerList()->setHandler($mock);
            // Result object response will contain mock result
            //$result = $client->listTables();
            $result = $client->execute($command);
            return [ 'status' => 200, 'msg' => 'Document uploaded successfully.', 'data' => $result->toArray() ];

        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
        } catch (AwsException $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getAwsErrorMessage(), 'data' => $e->toArray() ];
        }
    }
    /** Download S3 Content from its objects */
    public function downloadDocument() {
        try {

            $s3Client = $this->getConfig();

            $result = $s3Client->getObject([
               'Bucket' => $this->bucket,
               'Key'    => $this->folder_key,
           ]);
            //If function has a specific name for download then it's utilize source_file
            //Otherwise filename of the S3 path will getting utilize
           $filename = ($this->source_file) ?? basename($this->folder_key);

           header("Content-Type: {$result['ContentType']}");
           header("Content-disposition: inline; filename=\"" . $filename . "\"");
           echo $result['Body'];

       } catch (S3Exception $e) {

            return ['status' => 400, 'message' => $e->getMessage()];

       }
    }

    /** Download S3 Content from its objects */
    public function downloadDocumentTemp($tempId = '', $subfolder = '') {
        try {

                $s3Client = $this->getConfig();

                $result = $s3Client->getObject([
                   'Bucket' => $this->bucket,
                   'Key'    => $this->folder_key,
                ]);
                //If function has a specific name for download then it's utilize source_file
                //Otherwise filename of the S3 path will getting utilize
                $filename = ($this->source_file) ?? basename($this->folder_key);

                // Save files
                $archive_dir = FCPATH . ARCHIEVE_DIR;
                if (!is_dir($archive_dir)) {
                    mkdir($archive_dir, 0755);
                }
                $destination = $archive_dir;

                if ($subfolder != '') {
                    $destination = $archive_dir . '/' . $subfolder;
                    if (!is_dir($destination)) {
                        mkdir($destination, 0755);
                    }
                }

                if ($tempId != '') {
                    if ($subfolder != '') {
                        $destination = $archive_dir . '/' . $subfolder . '/' . $tempId;
                    } else {
                        $destination = $archive_dir . '/' . $tempId;
                    }
                    if (!is_dir($destination)) {
                        mkdir($destination, 0755);
                    }
                }

                $destination .= '/' . $filename;
                // open file with write permision
                $file = fopen($destination, "w+");
                // put the content content into file
                fputs($file, $result['Body']);
                // close the file
                fclose($file);

       } catch (S3Exception $e) {

            return ['status' => 400, 'message' => $e->getMessage()];

       }
    }

    //Get URL from object
    public function getObjectUrl() {
        $s3Client = $this->getConfig();

        try {
            return ['status' => 200, 'url' => $s3Client->getObjectUrl($this->bucket, $this->folder_key)];
        }
        catch (S3Exception $e) {
            return ['status' => 400, 'message' => $e->getMessage()];
        }
    }

}
