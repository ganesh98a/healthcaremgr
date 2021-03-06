<?php
/**
 * BulkEnvelopesApi
 * PHP version 5
 *
 * @category Class
 * @package  DocuSign\eSign
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * DocuSign REST API
 *
 * The DocuSign REST API provides you with a powerful, convenient, and simple Web services API for interacting with DocuSign.
 *
 * OpenAPI spec version: v2
 * Contact: devcenter@docusign.com
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 *
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace DocuSign\eSign\Api\BulkEnvelopesApi;

class ListOptions
{
    /**
      * $count The number of results to return. This can be 1 to 20.
      * @var string
      */
    protected $count;

    /**
     * Gets count
     * @return string
     */
    public function getCount()
    {
        return $this->count;
    }
  
    /**
     * Sets count
     * @param string $count The number of results to return. This can be 1 to 20.
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
    /**
      * $include 
      * @var string
      */
    protected $include;

    /**
     * Gets include
     * @return string
     */
    public function getInclude()
    {
        return $this->include;
    }
  
    /**
     * Sets include
     * @param string $include 
     * @return $this
     */
    public function setInclude($include)
    {
        $this->include = $include;
        return $this;
    }
    /**
      * $start_position The position of the bulk envelope items in the response. This is used for repeated calls, when the number of bulk envelopes returned is too large for one return. The default value is 0.
      * @var string
      */
    protected $start_position;

    /**
     * Gets start_position
     * @return string
     */
    public function getStartPosition()
    {
        return $this->start_position;
    }
  
    /**
     * Sets start_position
     * @param string $start_position The position of the bulk envelope items in the response. This is used for repeated calls, when the number of bulk envelopes returned is too large for one return. The default value is 0.
     * @return $this
     */
    public function setStartPosition($start_position)
    {
        $this->start_position = $start_position;
        return $this;
    }
}
class GetOptions
{
    /**
      * $count Specifies the number of entries to return.
      * @var string
      */
    protected $count;

    /**
     * Gets count
     * @return string
     */
    public function getCount()
    {
        return $this->count;
    }
  
    /**
     * Sets count
     * @param string $count Specifies the number of entries to return.
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
    /**
      * $include Specifies which entries are included in the response. Multiple entries can be included by using commas in the query string (example: ?include=???failed,queued???)   Valid values are:   * all - Returns all entries. If present, overrides all other query settings. This is the default if no query string is provided. * failed - This only returns entries with a failed status. * queued - This only returns entries with a queued status. * sent ??? This only returns entries with a sent status.
      * @var string
      */
    protected $include;

    /**
     * Gets include
     * @return string
     */
    public function getInclude()
    {
        return $this->include;
    }
  
    /**
     * Sets include
     * @param string $include Specifies which entries are included in the response. Multiple entries can be included by using commas in the query string (example: ?include=???failed,queued???)   Valid values are:   * all - Returns all entries. If present, overrides all other query settings. This is the default if no query string is provided. * failed - This only returns entries with a failed status. * queued - This only returns entries with a queued status. * sent ??? This only returns entries with a sent status.
     * @return $this
     */
    public function setInclude($include)
    {
        $this->include = $include;
        return $this;
    }
    /**
      * $start_position Specifies the location in the list of envelopes from which to start.
      * @var string
      */
    protected $start_position;

    /**
     * Gets start_position
     * @return string
     */
    public function getStartPosition()
    {
        return $this->start_position;
    }
  
    /**
     * Sets start_position
     * @param string $start_position Specifies the location in the list of envelopes from which to start.
     * @return $this
     */
    public function setStartPosition($start_position)
    {
        $this->start_position = $start_position;
        return $this;
    }
}
class GetRecipientsOptions
{
    /**
      * $include_tabs 
      * @var string
      */
    protected $include_tabs;

    /**
     * Gets include_tabs
     * @return string
     */
    public function getIncludeTabs()
    {
        return $this->include_tabs;
    }
  
    /**
     * Sets include_tabs
     * @param string $include_tabs 
     * @return $this
     */
    public function setIncludeTabs($include_tabs)
    {
        $this->include_tabs = $include_tabs;
        return $this;
    }
    /**
      * $start_position 
      * @var string
      */
    protected $start_position;

    /**
     * Gets start_position
     * @return string
     */
    public function getStartPosition()
    {
        return $this->start_position;
    }
  
    /**
     * Sets start_position
     * @param string $start_position 
     * @return $this
     */
    public function setStartPosition($start_position)
    {
        $this->start_position = $start_position;
        return $this;
    }
}


namespace DocuSign\eSign\Api;

use \DocuSign\eSign\ApiClient;
use \DocuSign\eSign\ApiException;
use \DocuSign\eSign\Configuration;
use \DocuSign\eSign\ObjectSerializer;

/**
 * BulkEnvelopesApi Class Doc Comment
 *
 * @category Class
 * @package  DocuSign\eSign
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class BulkEnvelopesApi
{
    /**
     * API Client
     *
     * @var \DocuSign\eSign\ApiClient instance of the ApiClient
     */
    protected $apiClient;

    /**
     * Constructor
     *
     * @param \DocuSign\eSign\ApiClient|null $apiClient The api client to use
     */
    public function __construct(\DocuSign\eSign\ApiClient $apiClient = null)
    {
        if ($apiClient === null) {
            $apiClient = new ApiClient();
        }

        $this->apiClient = $apiClient;
    }

    /**
     * Get API client
     *
     * @return \DocuSign\eSign\ApiClient get the API client
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * Set the API client
     *
     * @param \DocuSign\eSign\ApiClient $apiClient set the API client
     *
     * @return BulkEnvelopesApi
     */
    public function setApiClient(\DocuSign\eSign\ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation callList
     *
     * Gets status information about bulk recipient batches.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
     * @param  $options Options for modifying the behavior of the function. (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return \DocuSign\eSign\Model\BulkEnvelopesResponse
     */
    public function callList($account_id, BulkEnvelopesApi\ListOptions $options = null)
    {
        list($response) = $this->callListWithHttpInfo($account_id, $options);
        return $response;
    }

    /**
     * Operation callListWithHttpInfo
     *
     * Gets status information about bulk recipient batches.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
     * @param  $options Options for modifying the behavior of the function. (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return array of \DocuSign\eSign\Model\BulkEnvelopesResponse, HTTP status code, HTTP response headers (array of strings)
     */
    public function callListWithHttpInfo($account_id, BulkEnvelopesApi\ListOptions $options = null)
    {
        // verify the required parameter 'account_id' is set
        if ($account_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $account_id when calling callList');
        }
        // parse inputs
        $resourcePath = "/v2/accounts/{accountId}/bulk_envelopes";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        if ($options != null)
        {
        // query params
        // query params
        if ($options->getCount() !== null) {
            $queryParams['count'] = $this->apiClient->getSerializer()->toQueryValue($options->getCount());
        }
        // query params
        if ($options->getInclude() !== null) {
            $queryParams['include'] = $this->apiClient->getSerializer()->toQueryValue($options->getInclude());
        }
        // query params
        if ($options->getStartPosition() !== null) {
            $queryParams['start_position'] = $this->apiClient->getSerializer()->toQueryValue($options->getStartPosition());
        }
        }

        // path params
        if ($account_id !== null) {
            $resourcePath = str_replace(
                "{" . "accountId" . "}",
                $this->apiClient->getSerializer()->toPathValue($account_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\eSign\Model\BulkEnvelopesResponse',
                '/v2/accounts/{accountId}/bulk_envelopes'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\DocuSign\eSign\Model\BulkEnvelopesResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\BulkEnvelopesResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\ErrorDetails', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation deleteRecipients
     *
     * Deletes the bulk recipient file from an envelope.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $envelope_id The envelopeId Guid of the envelope being accessed.
    * @param string $recipient_id The ID of the recipient being accessed.
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return \DocuSign\eSign\Model\BulkRecipientsUpdateResponse
     */
    public function deleteRecipients($account_id, $envelope_id, $recipient_id)
    {
        list($response) = $this->deleteRecipientsWithHttpInfo($account_id, $envelope_id, $recipient_id);
        return $response;
    }

    /**
     * Operation deleteRecipientsWithHttpInfo
     *
     * Deletes the bulk recipient file from an envelope.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $envelope_id The envelopeId Guid of the envelope being accessed.
    * @param string $recipient_id The ID of the recipient being accessed.
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return array of \DocuSign\eSign\Model\BulkRecipientsUpdateResponse, HTTP status code, HTTP response headers (array of strings)
     */
    public function deleteRecipientsWithHttpInfo($account_id, $envelope_id, $recipient_id)
    {
        // verify the required parameter 'account_id' is set
        if ($account_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $account_id when calling deleteRecipients');
        }
        // verify the required parameter 'envelope_id' is set
        if ($envelope_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $envelope_id when calling deleteRecipients');
        }
        // verify the required parameter 'recipient_id' is set
        if ($recipient_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $recipient_id when calling deleteRecipients');
        }
        // parse inputs
        $resourcePath = "/v2/accounts/{accountId}/envelopes/{envelopeId}/recipients/{recipientId}/bulk_recipients";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);


        // path params
        if ($account_id !== null) {
            $resourcePath = str_replace(
                "{" . "accountId" . "}",
                $this->apiClient->getSerializer()->toPathValue($account_id),
                $resourcePath
            );
        }
        // path params
        if ($envelope_id !== null) {
            $resourcePath = str_replace(
                "{" . "envelopeId" . "}",
                $this->apiClient->getSerializer()->toPathValue($envelope_id),
                $resourcePath
            );
        }
        // path params
        if ($recipient_id !== null) {
            $resourcePath = str_replace(
                "{" . "recipientId" . "}",
                $this->apiClient->getSerializer()->toPathValue($recipient_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'DELETE',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\eSign\Model\BulkRecipientsUpdateResponse',
                '/v2/accounts/{accountId}/envelopes/{envelopeId}/recipients/{recipientId}/bulk_recipients'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\DocuSign\eSign\Model\BulkRecipientsUpdateResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\BulkRecipientsUpdateResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\ErrorDetails', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation get
     *
     * Gets the status of a specified bulk send operation.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $batch_id 
     * @param  $options Options for modifying the behavior of the function. (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return \DocuSign\eSign\Model\BulkEnvelopeStatus
     */
    public function get($account_id, $batch_id, BulkEnvelopesApi\GetOptions $options = null)
    {
        list($response) = $this->getWithHttpInfo($account_id, $batch_id, $options);
        return $response;
    }

    /**
     * Operation getWithHttpInfo
     *
     * Gets the status of a specified bulk send operation.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $batch_id 
     * @param  $options Options for modifying the behavior of the function. (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return array of \DocuSign\eSign\Model\BulkEnvelopeStatus, HTTP status code, HTTP response headers (array of strings)
     */
    public function getWithHttpInfo($account_id, $batch_id, BulkEnvelopesApi\GetOptions $options = null)
    {
        // verify the required parameter 'account_id' is set
        if ($account_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $account_id when calling get');
        }
        // verify the required parameter 'batch_id' is set
        if ($batch_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $batch_id when calling get');
        }
        // parse inputs
        $resourcePath = "/v2/accounts/{accountId}/bulk_envelopes/{batchId}";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        if ($options != null)
        {
        // query params
        // query params
        if ($options->getCount() !== null) {
            $queryParams['count'] = $this->apiClient->getSerializer()->toQueryValue($options->getCount());
        }
        // query params
        if ($options->getInclude() !== null) {
            $queryParams['include'] = $this->apiClient->getSerializer()->toQueryValue($options->getInclude());
        }
        // query params
        if ($options->getStartPosition() !== null) {
            $queryParams['start_position'] = $this->apiClient->getSerializer()->toQueryValue($options->getStartPosition());
        }
        }

        // path params
        if ($account_id !== null) {
            $resourcePath = str_replace(
                "{" . "accountId" . "}",
                $this->apiClient->getSerializer()->toPathValue($account_id),
                $resourcePath
            );
        }
        // path params
        if ($batch_id !== null) {
            $resourcePath = str_replace(
                "{" . "batchId" . "}",
                $this->apiClient->getSerializer()->toPathValue($batch_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\eSign\Model\BulkEnvelopeStatus',
                '/v2/accounts/{accountId}/bulk_envelopes/{batchId}'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\DocuSign\eSign\Model\BulkEnvelopeStatus', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\BulkEnvelopeStatus', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\ErrorDetails', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getRecipients
     *
     * Gets the bulk recipient file from an envelope.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $envelope_id The envelopeId Guid of the envelope being accessed.
    * @param string $recipient_id The ID of the recipient being accessed.
     * @param  $options Options for modifying the behavior of the function. (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return \DocuSign\eSign\Model\BulkRecipientsResponse
     */
    public function getRecipients($account_id, $envelope_id, $recipient_id, BulkEnvelopesApi\GetRecipientsOptions $options = null)
    {
        list($response) = $this->getRecipientsWithHttpInfo($account_id, $envelope_id, $recipient_id, $options);
        return $response;
    }

    /**
     * Operation getRecipientsWithHttpInfo
     *
     * Gets the bulk recipient file from an envelope.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $envelope_id The envelopeId Guid of the envelope being accessed.
    * @param string $recipient_id The ID of the recipient being accessed.
     * @param  $options Options for modifying the behavior of the function. (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return array of \DocuSign\eSign\Model\BulkRecipientsResponse, HTTP status code, HTTP response headers (array of strings)
     */
    public function getRecipientsWithHttpInfo($account_id, $envelope_id, $recipient_id, BulkEnvelopesApi\GetRecipientsOptions $options = null)
    {
        // verify the required parameter 'account_id' is set
        if ($account_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $account_id when calling getRecipients');
        }
        // verify the required parameter 'envelope_id' is set
        if ($envelope_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $envelope_id when calling getRecipients');
        }
        // verify the required parameter 'recipient_id' is set
        if ($recipient_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $recipient_id when calling getRecipients');
        }
        // parse inputs
        $resourcePath = "/v2/accounts/{accountId}/envelopes/{envelopeId}/recipients/{recipientId}/bulk_recipients";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);

        if ($options != null)
        {
        // query params
        // query params
        if ($options->getIncludeTabs() !== null) {
            $queryParams['include_tabs'] = $this->apiClient->getSerializer()->toQueryValue($options->getIncludeTabs());
        }
        // query params
        if ($options->getStartPosition() !== null) {
            $queryParams['start_position'] = $this->apiClient->getSerializer()->toQueryValue($options->getStartPosition());
        }
        }

        // path params
        if ($account_id !== null) {
            $resourcePath = str_replace(
                "{" . "accountId" . "}",
                $this->apiClient->getSerializer()->toPathValue($account_id),
                $resourcePath
            );
        }
        // path params
        if ($envelope_id !== null) {
            $resourcePath = str_replace(
                "{" . "envelopeId" . "}",
                $this->apiClient->getSerializer()->toPathValue($envelope_id),
                $resourcePath
            );
        }
        // path params
        if ($recipient_id !== null) {
            $resourcePath = str_replace(
                "{" . "recipientId" . "}",
                $this->apiClient->getSerializer()->toPathValue($recipient_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\eSign\Model\BulkRecipientsResponse',
                '/v2/accounts/{accountId}/envelopes/{envelopeId}/recipients/{recipientId}/bulk_recipients'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\DocuSign\eSign\Model\BulkRecipientsResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\BulkRecipientsResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\ErrorDetails', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation updateRecipients
     *
     * Adds or replaces envelope bulk recipients.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $envelope_id The envelopeId Guid of the envelope being accessed.
    * @param string $recipient_id The ID of the recipient being accessed.
     * @param \DocuSign\eSign\Model\BulkRecipientsRequest $bulk_recipients_request  (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return \DocuSign\eSign\Model\BulkRecipientsSummaryResponse
     */
    public function updateRecipients($account_id, $envelope_id, $recipient_id, $bulk_recipients_request = null)
    {
        list($response) = $this->updateRecipientsWithHttpInfo($account_id, $envelope_id, $recipient_id, $bulk_recipients_request);
        return $response;
    }

    /**
     * Operation updateRecipientsWithHttpInfo
     *
     * Adds or replaces envelope bulk recipients.
     *
    * @param string $account_id The external account number (int) or account ID Guid.
    * @param string $envelope_id The envelopeId Guid of the envelope being accessed.
    * @param string $recipient_id The ID of the recipient being accessed.
     * @param \DocuSign\eSign\Model\BulkRecipientsRequest $bulk_recipients_request  (optional)
     * @throws \DocuSign\eSign\ApiException on non-2xx response
     * @return array of \DocuSign\eSign\Model\BulkRecipientsSummaryResponse, HTTP status code, HTTP response headers (array of strings)
     */
    public function updateRecipientsWithHttpInfo($account_id, $envelope_id, $recipient_id, $bulk_recipients_request = null)
    {
        // verify the required parameter 'account_id' is set
        if ($account_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $account_id when calling updateRecipients');
        }
        // verify the required parameter 'envelope_id' is set
        if ($envelope_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $envelope_id when calling updateRecipients');
        }
        // verify the required parameter 'recipient_id' is set
        if ($recipient_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $recipient_id when calling updateRecipients');
        }
        // parse inputs
        $resourcePath = "/v2/accounts/{accountId}/envelopes/{envelopeId}/recipients/{recipientId}/bulk_recipients";
        $httpBody = '';
        $queryParams = [];
        $headerParams = [];
        $formParams = [];
        $_header_accept = $this->apiClient->selectHeaderAccept(['application/json']);
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType([]);


        // path params
        if ($account_id !== null) {
            $resourcePath = str_replace(
                "{" . "accountId" . "}",
                $this->apiClient->getSerializer()->toPathValue($account_id),
                $resourcePath
            );
        }
        // path params
        if ($envelope_id !== null) {
            $resourcePath = str_replace(
                "{" . "envelopeId" . "}",
                $this->apiClient->getSerializer()->toPathValue($envelope_id),
                $resourcePath
            );
        }
        // path params
        if ($recipient_id !== null) {
            $resourcePath = str_replace(
                "{" . "recipientId" . "}",
                $this->apiClient->getSerializer()->toPathValue($recipient_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        // body params
        $_tempBody = null;
        if (isset($bulk_recipients_request)) {
            $_tempBody = $bulk_recipients_request;
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'PUT',
                $queryParams,
                $httpBody,
                $headerParams,
                '\DocuSign\eSign\Model\BulkRecipientsSummaryResponse',
                '/v2/accounts/{accountId}/envelopes/{envelopeId}/recipients/{recipientId}/bulk_recipients'
            );

            return [$this->apiClient->getSerializer()->deserialize($response, '\DocuSign\eSign\Model\BulkRecipientsSummaryResponse', $httpHeader), $statusCode, $httpHeader];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\BulkRecipientsSummaryResponse', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\DocuSign\eSign\Model\ErrorDetails', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }
}
