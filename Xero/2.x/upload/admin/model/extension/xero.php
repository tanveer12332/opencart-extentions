<?php
// Prevents timeout
ini_set('max_execution_time', 3600);
set_time_limit(3600);

require_once(DIR_SYSTEM . 'library/xero.php');

class ModelExtensionXero extends Model {
	private $xero = false;
	private $key;
	private $secret;
	
	private function startup(){
		if (!$this->xero){
			$this->key = $this->config->get('xero_api_key');
			$this->secret = $this->config->get('xero_api_secret');

			$this->xero = new Xero($this->key, $this->secret, DIR_SYSTEM . 'cert/xero_public.cer', DIR_SYSTEM . 'cert/xero_private.pem');
		}
	}
	
	public function exportCustomer($customer, $return = false) {
		$this->startup();
		
		$address_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer['customer_id'] . "'");
		
		// Prepare the addresses
		foreach ($query->rows as $address) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address['country_id'] . "'");
			
			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
			} else {
				$country = '';
			}
			
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address['zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
			} else {
				$zone = '';
			}		
		
			$address_data[] = array(
				'AddressType'	=> 'POBOX',
				'AttentionTo'	=> html_entity_decode($address['firstname'] . ' ' . $address['lastname'], ENT_QUOTES),
				'AddressLine1'	=> html_entity_decode($address['address_1'], ENT_QUOTES),
				'AddressLine2'	=> html_entity_decode($address['address_2'], ENT_QUOTES),
				'PostalCode'	=> html_entity_decode($address['postcode'], ENT_QUOTES),
				'City'			=> html_entity_decode($address['city'], ENT_QUOTES),
				'Region'		=> $zone,
				'Country'		=> $country
			);
		}
		
		// Store the telephone data
		$phone_data = array();
		
		if (!empty($customer['fax'])) {
			$phone_data[] = array (
				'PhoneType'		=> 'FAX',
				'PhoneNumber'	=> $customer['fax']
			);
		}
		
		$phone_data[] = array (
			'PhoneType'		=> 'DEFAULT',
			'PhoneNumber'	=> $customer['telephone']
		);
		
		// Decide on the name
		if (!empty($customer['company'])) {
			$name = $customer['company'];
		} elseif ($this->config->get('xero_customer') == 'REPLACE') {
			$name = $customer['firstname'] . ' ' . $customer['lastname'];
		} elseif ($this->config->get('xero_customer') == 'PREPEND') {
			$name = $customer['customer_id'] . ' ' . $customer['firstname'] . ' ' . $customer['lastname'];
		} else {
			$name = $customer['firstname'] . ' ' . $customer['lastname'] . ' ' . $customer['customer_id'];
		}
		
		// Compile data
		$contact = array(
			array(
				'Name'				=> html_entity_decode($name, ENT_QUOTES),
				'ContactNumber'		=> $customer['customer_id'],
				'FirstName'			=> html_entity_decode($customer['firstname'], ENT_QUOTES),
				'LastName'			=> html_entity_decode($customer['lastname'], ENT_QUOTES),
				'EmailAddress'		=> $customer['email'],
				'Phones'			=> array(
					'Phone'	=> $phone_data
				),
				'Addresses'			=> array(
					'Address' => array(
						$address_data
					)
				)
			)
		);
		
		// If we want to return data, we don't send to xero
		if ($return) {
			return $contact;
		}

		// Get response from Xero
		$response = $this->xero->contacts($contact);
		
		// Debug log & failed logging
		if ($this->config->get('xero_debug')) {
			$this->log->write(print_r($response, true));
		} elseif (strpos(print_r($response, true), 'ValidationException') !== false) {
			$this->log->write(print_r($response, true));
		}
	}
}