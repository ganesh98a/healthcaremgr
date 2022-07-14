<?php
class HttpClientCustom
{
    private $ch;
    private $headers;
    private $curlopts;

    function __construct($ch = null)
    {
        if ($ch === null) {
            $this->ch = curl_init();
            if ($this->ch === false) {
                $this->ch = null;
                throw Exception('failed to initialise curl');
            }
        } else {
            $this->ch = $ch;
        }

        $this->headers = [];
        $this->curlopts = [];
    }

    function __destruct()
    {
        if ($this->ch !== null) {
            curl_close($this->ch);
        }
    }

    public function error()
    {
        return [curl_errno($this->ch), curl_error($this->ch)];
    }

    public function set_header(string $key, string $value)
    {
        $this->headers[strtolower($key)] = $key . ': ' . $value;
        return $this;
    }

    public function remove_header(string $key)
    {
        unset($this->headers[strtolower($key)]);
        return $this;
    }

    public function set_curlopt($key, $value)
    {
        $this->curlopts[$key] = $value;
        return $this;
    }

    public function remove_curlopt($key)
    {
        unset($this->curlopts[$key]);
        return $this;
    }

    public function request(string $type, $url, array $data = null)
    {
        $type = !empty($type) ? strtoupper($type) : $type;
        // set options
        $this->set_curlopt(CURLOPT_URL, $url);
        $this->set_curlopt(CURLOPT_CUSTOMREQUEST, $type);
        if ($data !== null) {
            $this->set_curlopt(CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        
        // make request
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt_array($this->ch, $this->curlopts);
        $response = curl_exec($this->ch);

        // clean up
        $this->remove_curlopt(CURLOPT_URL);
        $this->remove_curlopt(CURLOPT_CUSTOMREQUEST);
        if ($data !== null) {
            $this->remove_curlopt(CURLOPT_POSTFIELDS);
        }

        return $response;
    }

    public function get($url, array $params = null)
    {

        if ($params !== null) {
            $query = http_build_query($params);
            if (strpos($url, '?') === false) {
                $url .= '?';
            }

            $url .= $query;
        }

        //echo $url;
        // set options
        $this->set_curlopt(CURLOPT_URL, $url);

        // make request
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt_array($this->ch, $this->curlopts);
        $response = curl_exec($this->ch);
        // clean up
        $this->remove_curlopt(CURLOPT_URL);

        return $response;
    }

    public function post($url, array $data = null)
    {
        return $this->request('POST', $url, $data);
    }

    public function put($url, array $data = null)
    {
        return $this->request('PUT', $url, $data);
    }

    public function patch($url, array $data = null)
    {
        return $this->request('PATCH', $url, $data);
    }

    public function delete($url, array $data = null)
    {
        return $this->request('DELETE', $url, $data);
    }

}

class CustomCurlCall extends HttpClientCustom {

    function __construct() {
        // Call the KeyPayCommonAtuh constructor
        parent::__construct();
    }

    public function requestWithoutWait(string $type, $url, array $data = null)
    {
        $type = !empty($type) ? strtoupper($type) : $type;
        $this->set_curlopt(CURLOPT_TIMEOUT_MS,3000);
        $this->request($type,$url,$data);
    }

}
