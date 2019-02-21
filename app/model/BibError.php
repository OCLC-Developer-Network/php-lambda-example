<?php
/**
 * Copyright 2013 OCLC
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright (c) 2017 OCLC
 * @license http://www.opensource.org/licenses/Apache-2.0
 * @author Karen A. Coombs <coombsk@oclc.org>
 */

/**
 * A class that represents an Error
 *
 */
use GuzzleHttp\Psr7\Response;
class BibError
{
	/**
	 * requestError
	 * @var 
	 */
    protected $requestError;
    
	/**
	 * code
	 * @var string
	 */
	protected $code;
	
	/**
	 * message
	 * @var string
	 */
	protected $message;
	
	/**
	 * detail
	 * @var string
	 */
	protected $detail;
    
	/**
	 * Set Request Error
	 *
	 * @return HTTP Error
	 */
	function setRequestError($error)
	{
	    if (!is_a($error, 'GuzzleHttp\Psr7\Response')) {
	        Throw new \BadMethodCallException('You must pass a valid Guzzle Http PSR7 Response');
	    }
	    $this->requestError = $error;
	    if (implode($this->requestError->getHeader('Content-Type')) !== 'text/html;charset=utf-8'){
	        $error_response = simplexml_load_string($this->requestError->getBody());
	        $this->code = (integer) $this->requestError->getStatusCode();
	        $this->message = (string) $error_response->message;
	        $this->detail = (string) $error_response->detail;
	    } else {
	    	$this->code = (integer) $this->requestError->getStatusCode();
	    }
	    
	}
	
	/**
	 * Get Request Error
	 *
	 * @return HTTP Error
	 */
	function getRequestError()
	{
	    return $this->requestError;
	}
	
    /**
     * Get Error Code
     *
     * @return string
     */
    function getCode()
    {   
        return $this->code;
    }
    
    /**
     * Get Error Message
     *
     * @return string
     */
    function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Get Error Detail
     *
     * @return string
     */
    function getDetail()
    {
        return $this->detail;
    }
    
}