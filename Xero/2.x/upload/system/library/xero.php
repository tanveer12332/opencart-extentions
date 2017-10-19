<?php
/*******************************************/
/*        OPENCART XERO INTEGRATION        */
/*             EQUOTIX PTE LTD             */
/*             www.equotix.com             */
/*******************************************/

define ('BASE_PATH', DIR_SYSTEM . 'library/xero');
define ('XRO_APP_TYPE', 'Private');
define ('OAUTH_CALLBACK', 'oob');
require_once('xero/XeroOAuth.php');

class Xero {
	private $xeroOAuth = '';

	public function __construct($key, $secret, $public, $private) {
		$useragent = "Xero Integration OpenCart Equotix";

		$signatures = array (
			'consumer_key' 		=> $key,
			'shared_secret' 	=> $secret,
			'core_version' 		=> '2.0',
			'payroll_version' 	=> '1.0',
			'file_version' 		=> '1.0' 
		);

		if (XRO_APP_TYPE == "Private" || XRO_APP_TYPE == "Partner") {
			$signatures['rsa_private_key'] = $private;
			$signatures['rsa_public_key'] = $public;
		}

		$this->xeroOAuth = new XeroOAuth (
			array_merge(
				array (
					'application_type' 	=> XRO_APP_TYPE,
					'oauth_callback' 	=> OAUTH_CALLBACK,
					'user_agent' 		=> $useragent 
				),
				$signatures
			)
		);
		
		$this->xeroOAuth->config['access_token'] = $this->xeroOAuth->config ['consumer_key'];
		$this->xeroOAuth->config['access_token_secret'] = $this->xeroOAuth->config ['shared_secret'];
	}
	
	public function contacts($contacts) {
		$xml = $this->parseXML($contacts, 'Contacts');
		
        $response = $this->xeroOAuth->request('POST', $this->xeroOAuth->url('Contacts', 'core'), array(), $xml);
		
		if ($this->xeroOAuth->response['code'] == 200) {
			$data = $this->xeroOAuth->parseResponse($this->xeroOAuth->response['response'], $this->xeroOAuth->response['format']);
			
			return $data->Contacts;
		} else {
			return $this->xeroOAuth->response['response'];
		}
	}
	
	public function invoices($invoices) {
		$xml = $this->parseXML($invoices, 'Invoices');
		
        $response = $this->xeroOAuth->request('POST', $this->xeroOAuth->url('Invoices', 'core'), array(), $xml);
		
		if ($this->xeroOAuth->response['code'] == 200) {
			$data = $this->xeroOAuth->parseResponse($this->xeroOAuth->response['response'], $this->xeroOAuth->response['format']);
			
			return $data->Invoices;
		} else {
			return $this->xeroOAuth->response['response'];
		}
	}
	
	public function items($items) {
		$xml = $this->parseXML($items, 'Items');
		
        $response = $this->xeroOAuth->request('POST', $this->xeroOAuth->url('Items', 'core'), array(), $xml);
		
		if ($this->xeroOAuth->response['code'] == 200) {
			$data = $this->xeroOAuth->parseResponse($this->xeroOAuth->response['response'], $this->xeroOAuth->response['format']);
			
			return $data->Items;
		} else {
			return $this->xeroOAuth->response['response'];
		}
	}
	
	public function getItems($items) {
		$xml = $this->parseXML($items, 'Items');
		
        $response = $this->xeroOAuth->request('GET', $this->xeroOAuth->url('Items', 'core'), array(), $xml);
		
		if ($this->xeroOAuth->response['code'] == 200) {
			$data = $this->xeroOAuth->parseResponse($this->xeroOAuth->response['response'], $this->xeroOAuth->response['format']);
			
			return $data->Items;
		} else {
			return $this->xeroOAuth->response['response'];
		}
	}
	
	private function parseXML($data, $root = 'ResultSet', &$xml=null) {
        if (is_null($xml)) {
			$xml = simplexml_load_string( "<$root />" );
			$root = rtrim($root, 's');
        }
		
        foreach($data as $key => $value) {
            $numeric = 0;
           
		   if (is_numeric($key)) {
                $numeric = 1;
				
                $key = $root;
            }

            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

            if (is_array($value)) {
                $node = ($this->isAssoc($value) || $numeric) ? $xml->addChild($key) : $xml;

                if ($numeric) {
					$key = 'anon';
				}
				
                $this->parseXML($value, $key, $node );
            } else {
                $xml->$key = $value;
            }
        }
		
		return $xml->asXML();
	}
	
	private function isAssoc($array) {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
}