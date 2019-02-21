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
 * A class that represents a Bibliographic record.
 * The Bib has id, and record.
 *
 */
use GuzzleHttp\Client, GuzzleHttp\Exception\RequestException, GuzzleHttp\Psr7\Response;

Class Bib {	
	
	/**
	 * serviceUrl
	 * @var string
	 */
	public static $serviceUrl = 'https://worldcat.org/bib/data/';
	
	/**
	 * testServer
	 * @var string
	 */
	public static $testServer = FALSE;
	
	/**
	 * userAgent
	 * @var string
	 */
	public static $userAgent = 'OCLC DevConnect demo';
	
	/**
	 * id
	 * @var string
	 */
	protected $id;
	
	/**
	 * record
	 * @var File_MARC_Record
	 */
	protected $record;
	
	/**
	 * Getter for the ID
	 * @return string
	 */
	public function getId()
	{
		if (empty($this->id)){
		    if (is_numeric($this->record->getField('001')->getData())) {
		        $this->id = $this->record->getField('001')->getData();
		    } else {
			 $this->id = substr($this->record->getField('001')->getData(), 3);
		    }
		    
		}
		return $this->id;
	}
	
	/**
	 * Getter for the OCLCNumber
	 * @return string
	 */
	public function getOCLCNumber()
	{
		return $this->record->getField('001')->getData();
	}
	
	/**
	 * Getter for the Title
	 * @return string
	 */
	public function getTitle()
	{
		$title = $this->record->getField('24.', true)->getSubfield('a')->getData();
		if ($this->record->getField('24.', true)->getSubfield('b')){
			$title .= $this->record->getField('24.', true)->getSubfield('b')->getData();
		}
		$title = trim($title, " /");
		return $title;
	}
	
	/**
	 * Getter for the Author
	 * @return string
	 */
	public function getAuthor()
	{
		if ($this->record->getField('100', true)) {
			$author = $this->record->getField('100', true)->getSubfield('a')->getData();
		} elseif ($this->record->getField('110', true)) {
			$author = $this->record->getField('110', true)->getSubfield('a')->getData();
		} elseif ($this->record->getField('111', true)){
			$author = $this->record->getField('111', true)->getSubfield('a')->getData();
		} elseif ($this->record->getField('700', true)) {
			$author = $this->record->getField('700', true)->getSubfield('a')->getData();
		} elseif ($this->record->getField('710', true)) {
			$author = $this->record->getField('710', true)->getSubfield('a')->getData();
		} elseif ($this->record->getField('711', true)) {
			$author = $this->record->getField('711', true)->getSubfield('a')->getData();
		} else {
			$author = "";
		}
		$author = rtrim($author, ',');
		if (strpos($author, ".") > 0) {
			$author= rtrim($author, '.');
		}
		return $author;
	}
	
	/**
	 * Setter for the record
	 * 
	 */
	public function setRecord($record){
	    if (!is_a($record, 'File_MARC_Record')) {
	        Throw new \BadMethodCallException('You must pass a valid File_MARC_Record');
	    }
	    $this->record = $record;
	}
	
	/**
	 * Getter for the record
	 * @return string
	 */
	public function getRecord()
	{
		return $this->record;
	}
	
	/**
	 * Construct the Bib object
	 *
	 */
	public function __construct(){
		
	}
	/**
	 * Find and retrieve a Bib by ID
	 *
	 * @param $id string
	 * @param $accessToken OCLC/Auth/AccessToken
	 * @return Bib or \Guzzle\Http\Exception\BadResponseException
	 */
	
	public static function find($oclcnumber, $accessToken){
		if (!is_numeric($oclcnumber)){
			Throw new \BadMethodCallException('You must pass a valid OCLC Number');
		} elseif (!is_a($accessToken, '\OCLC\Auth\AccessToken')) {
			Throw new \BadMethodCallException('You must pass a valid Access Token');
		}
		$url = static::$serviceUrl . $oclcnumber . '?classificationScheme=LibraryOfCongress&holdingLibraryCode=MAIN';
		$client = new Client();
		$headers = array();
		$headers['Authorization'] = 'Bearer ' . $accessToken->getValue();
		$headers['Accept'] = 'application/atom+xml;content="application/vnd.oclc.marc21+xml"';
		
		try {
			$response = $client->request('GET', $url, ['headers' => $headers]);
			$response_body = simplexml_load_string($response->getBody());
			
			//We parse the MARCXML out of the Atom Response
			$response_body->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
			$response_body->registerXPathNamespace('rb', 'http://worldcat.org/rb');
			$marc_xml = $response_body->xpath('//atom:content/rb:response/child::*');
			//We want a File_MARC Record created from the MARC
			$records = new File_MARCXML($marc_xml[0]->asXML(), File_MARC::SOURCE_STRING);
			$bib = new Bib();
			$bib->id = $oclcnumber;
			$bib->record = $records->next();
			return $bib;
		} catch (RequestException $error) {
		    $bibError = new BibError();
		    $bibError->setRequestError($error->getResponse());
			return $bibError;
		}
	}
}