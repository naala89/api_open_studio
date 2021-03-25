<?php

/**
 * Class Curl.
 *
 * @package    ApiOpenStudio
 * @subpackage Core
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *             If a copy of the MPL was not distributed with this file,
 *             You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Core;

/**
 * Class Curl
 *
 * Wrapper for the PHP Curl class.
 *
 * Curl constants
 *
 * 13     CURLOPT_TIMEOUT
 * 47     CURLOPT_POST
 * 52     CURLOPT_FOLLOWLOCATION
 * 64     CURLOPT_SSL_VERIFYPEER
 * 78     CURLOPT_CONNECTTIMEOUT
 * 80     CURLOPT_HTTPGET
 * 10001  CURLOPT_FILE
 * 10002  CURLOPT_URL
 * 10005  CURLOPT_USERPWD
 * 10015  CURLOPT_POSTFIELDS
 * 10022  CURLOPT_COOKIE
 * 19913  CURLOPT_RETURNTRANSFER
 */
class Curl
{
    /**
     * HTTP request status code.
     *
     * @var integer Curl result HTTP status code.
     */
    public $httpStatus;

    /**
     * Curl status code.
     *
     * @var integer Curl result status code.
     */
    public $curlStatus;

    /**
     * Curl error message.
     *
     * @var string Curl result error message.
     */
    public $errorMsg;

    /**
     * Result content-type.
     *
     * @var string Curl result content-type.
     */
    public $type;

    /**
     * Request options.
     *
     * @var array Request options.
     */
    public $options = [CURLOPT_RETURNTRANSFER => true];

    /**
     * Request URL.
     *
     * @var string Request URL.
     */
    public $url;

    /**
     * Send a GET request using cURL.
     *
     * @param string $url Url for the curl call.
     * @param array $options Additional options.
     *
     * @return string
     */
    public function get(string $url, array $options = [])
    {
        $options[CURLOPT_HTTPGET] = true;
        return $this->exec($url, $options);
    }

    /**
     * Send a POST request using cURL.
     *
     * @param string $url Url for the curl call.
     * @param array $options Additional options. This includes the post vars.
     *
     * @return string
     */
    public function post(string $url, array $options = array())
    {
        $options[CURLOPT_POST] = true;
        return $this->exec($url, $options);
    }

    /**
     * Utility function to get options after adding them to the default curl options.
     *
     * @param string $url Url for the curl call.
     * @param array $options Additional options.
     *
     * @return array Array of options
     */
    private function getCurlOptions(string $url, array $options = [])
    {
        return $this->options + array(CURLOPT_URL => $url) + $options;
    }

    /**
     * Perform a cURL request.
     *
     * @param string $url Url for the curl call.
     * @param array $options Additional options.
     *
     * @return string
     */
    private function exec(string $url, array $options = [])
    {
        $options = $this->getCurlOptions($url, $options);

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $this->httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $this->curlStatus = curl_errno($ch);
        $this->errorMsg = curl_error($ch);
        curl_close($ch);

        return $response;
    }
}
