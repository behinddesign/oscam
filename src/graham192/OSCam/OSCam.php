<?php

namespace graham192\OSCam;

use Curl\Curl;

class OSCam
{
    private $server = "";
    private $port = "";
    private $username = "";
    private $password = "";
    private $https = false;
    private $auth_str = false;
    private $return_type = "array";
    private $url_parts = array(
        "status" => "/oscamapi.html?part=status",
        "status_with_log" => "/oscamapi.html?part=status&appendlog=1",
        "reader_stats_with_label" => "/oscamapi.html?part=readerstats&label=%s",
        "reader_entitlement_with_label" => "/oscamapi.html?part=entitlement&label=%s",
        "user_stats" => "/oscamapi.html?part=userstats",
        "user_stats_with_label" => "/oscamapi.html?part=userstats&label=%s"
    );

    public function __construct()
    {
        return $this;
    }

    public function setConnection($server, $port, $https = false)
    {
        if (!$server || !(int)$port) {
            throw new \InvalidArgumentException("Server or port invalid");
        }

        $this->server = $server;
        $this->port = $port;
        $this->https = $https;

        return $this;
    }

    public function setAuth($username, $password)
    {
        if (!$username || !$password) {
            throw new \InvalidArgumentException("Username or Password invalid");
        }

        $this->username = $username;
        $this->password = $password;

        $this->auth_str = "";

        return $this;
    }

    private function _connect($url_part, $args = false)
    {
        if (!isset($this->url_parts[$url_part])) {
            throw new \Exception("Unable to find the URL part : " . $url_part . ". Please select from\n" .
                var_export($this->url_parts, 1));
        }

        $protocol = ($this->https) ? "https://" : "http://";

        $curl = new Curl();

        if ($this->username || $this->password) {
            $curl->setOpt(CURLOPT_HTTPAUTH, Curl::AUTH_DIGEST);
            $curl->setopt(CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }

        //Remove header
        $curl->setopt(CURLOPT_HEADER, false);

        if ($args) {
            $parsed_parts = vsprintf($this->url_parts[$url_part], $args);
        } else {
            $parsed_parts = $this->url_parts[$url_part];
        }

        $curl->get($protocol . $this->server . ":" . $this->port . "/" . $parsed_parts);

        if ($curl->error) {
            throw new \Exception($curl->error_message);
        }

        return $this->_parse($curl->response);
    }

    private function _parse($raw_xml)
    {
        $raw_xml = trim($raw_xml);

        if (!$raw_xml) {
            throw new \Exception("Cannot read from URL, XML Response is empty");
        }

        $simplexml_result = simplexml_load_string($raw_xml);

        if (isset($simplexml_result->error)) {
            throw new \Exception("Error! " . $simplexml_result->error);
        }

        if ($this->return_type == "simplexml") {
            return $simplexml_result;
        } else {
            return @json_decode(@json_encode($simplexml_result), 1);
        }
    }

    public function setParseResultAsSimpleXML()
    {
        $this->return_type = "simple_xml";
    }

    public function setParseResultAsArray()
    {
        $this->return_type = "array";
    }

    public function getStatus()
    {
        return $this->_connect("status");
    }

    public function getStatusWithLog()
    {
        return $this->_connect("status_with_log");
    }

    public function getReaderStats($label)
    {
        if (!$label) {
            throw new \InvalidArgumentException("Please supply name of the reader");
        }

        return $this->_connect("reader_stats_with_label", array($label));
    }

    public function getReaderEntitlements($label)
    {
        if (!$label) {
            throw new \InvalidArgumentException("Please supply label of the reader");
        }

        return $this->_connect("reader_entitlement_with_label", array($label));
    }

    public function getUserStats($label = null)
    {
        if ($label) {
            return $this->_connect("user_stats_with_label", array($label));
        } else {
            return $this->_connect("user_stats");
        }
    }
}