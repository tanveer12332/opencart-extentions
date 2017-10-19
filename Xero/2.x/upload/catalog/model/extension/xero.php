<?php
// Prevents timeout
ini_set('max_execution_time', 3600);
set_time_limit(3600);

require_once(DIR_SYSTEM . 'library/xero.php');

class ModelExtensionXero extends Model {
	private $xero = false;
	private $key;
	private $secret;
	private $sales_code;
	private $shipping_code;
	private $inventory_code;
	private $cogs_code;
	private $invoice_status;
	
	private function startup(){
		if (!$this->xero){
			$this->key = $this->config->get('xero_api_key');
			$this->secret = $this->config->get('xero_api_secret');
			$this->invoice_status = $this->config->get('xero_invoice_status');			
			$this->sales_code = $this->config->get('xero_sales_code');
			$this->shipping_code = $this->config->get('xero_shipping_code');
			$this->inventory_code = $this->config->get('xero_inventory_code');
			$this->cogs_code = $this->config->get('xero_cogs_code');

			$this->xero = new Xero($this->key, $this->secret, DIR_SYSTEM . 'cert/xero_public.cer', DIR_SYSTEM . 'cert/xero_private.pem');
		}
	}
	
	public function getTotalCustomers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer");
		
		return $query->row['total'];
	}
	
	public function getTotalOrders() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE xero_exported = '0' AND order_status_id > 0");
		
		return $query->row['total'];
	}
	
	public function getTotalProducts() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product");
		
		return $query->row['total'];
	}
	
	public function exportOrders($start, $limit) {
		$this->startup();
		
		// Get all orders, throw them below to validate and export
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE xero_exported = '0' AND order_status_id > 0 LIMIT " . (int)$start . ',' . (int)$limit);
		
		// Count
		$total = 0;
		$failed = 0;
		$ignored = 0;
		
		// Store data to mass export
		$invoices = array();
		
		foreach($query->rows as $order){
			$total++;
			
			$data = $this->exportOrder($order, false, true);
		
			if ($data) {
				$invoices = array_merge($invoices, $data);
			} else {
				$ignored++;
			}
		}
		
		if ($invoices) {
			$response = $this->xero->invoices($invoices);
			
			// Debug log
			if ($this->config->get('xero_debug')) {
				$this->log->write(print_r($response, true));
			}
			
			// Failed
			$failed = substr_count(print_r($response, true), '<ValidationErrors>');
		}
		
		return array(
			'success'	=> $total - ($failed + $ignored),
			'failed'	=> $failed,
			'ignored'	=> $ignored
		);
	}
	
	public function exportCustomers($start, $limit) {
		$this->startup();
		
		// Get all customers
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer LIMIT " . (int)$start . ',' . (int)$limit);
		
		// Count
		$total = 0;
		$failed = 0;
		
		// Store data to mass export
		$contacts = array();

		foreach($query->rows as $customer){
			$total++;
			
			$contacts = array_merge($contacts, $this->exportCustomer($customer, true));
		}
		
		if ($contacts) {
			$response = $this->xero->contacts($contacts);
			
			// Debug log
			if ($this->config->get('xero_debug')) {
				$this->log->write(print_r($response, true));
			}
			
			// Return counts
			$failed = substr_count(print_r($response, true), '<ValidationErrors>');
		}
		
		return array(
			'success'	=> $total - $failed,
			'failed'	=> $failed
		);
	}
	
	public function exportProducts($start, $limit) {
		$this->startup();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT " . (int)$start . ',' . (int)$limit);
		
		// Count
		$total = 0;
		$failed = 0;
		
		foreach ($query->rows as $result) {
			$result['model'] = html_entity_decode($result['model'], ENT_QUOTES);
			$result['name'] = html_entity_decode($result['name'], ENT_QUOTES);
			
			$total++;
			
			$item = array(
				'Item' => array(
					'Code' 						=> (utf8_strlen($result['model']) > 30) ? utf8_substr($result['model'], 0, 29) : $result['model'],
					'InventoryAssetAccountCode' => $this->inventory_code,
					'Name'						=> (utf8_strlen($result['name']) > 50) ? utf8_substr($result['name'], 0, 49) : $result['name'],
					'Description'				=> $result['name'] . ' - ' . $result['model'],
					'SalesDetails'				=> array(
						'UnitPrice' => $result['price']
					),
					'PurchaseDetails'			=> array(
						'COGSAccountCode' => $this->cogs_code
					)
				)
			);
			
			$response = $this->xero->items($item);
			
			// Debug log
			if ($this->config->get('xero_debug')) {
				$this->log->write(print_r($response, true));
			}
			
			// Return counts
			$failed = substr_count(print_r($response, true), '<ValidationErrors>');
		}
		
		return array(
			'success'	=> $total - $failed,
			'failed'	=> $failed
		);
	}
	
	public function exportOrder($order, $deleted = false, $return = false) {
		$this->startup();
		
		// Decide on the sales code
		if (!empty($this->sales_code[$order['store_id']])) {
			$sales_code = $this->sales_code[$order['store_id']];
		} else {
			$sales_code = $this->sales_code[0];
		}
		
		if (!empty($this->shipping_code[$order['store_id']])) {
			$shipping_code = $this->shipping_code[$order['store_id']];
		} else {
			$shipping_code = $this->shipping_code[0];
		}
		
		// Mark exported
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET xero_exported = '1' WHERE order_id = '" . (int)$order['order_id'] . "'");
		
		$export = false;
		$refund = false;
		
		// Allowed to be exported
		if ($this->config->get('xero_order_status')) {
			if (in_array($order['order_status_id'], $this->config->get('xero_order_status'))) {
				$export = true;
			}
		}
		
		// Remove / void from xero
		if ($this->config->get('xero_order_refund')) {
			if (in_array($order['order_status_id'], $this->config->get('xero_order_refund'))) {
				$export = true;
				$refund = true;
			}
		}
		
		// Remove / void from xero
		if ($deleted) {
			$export = true;
			$refund = true;
		}
		
		// Proceed
		if ($export) {
			// Compile phone data
			$phone_data = array();
			
			if (!empty($order['fax'])) {
				$phone_data[] = array (
					'PhoneType'		=> 'FAX',
					'PhoneNumber'	=> $order['fax']
				);
			}
			
			$phone_data[] = array (
				'PhoneType'		=> 'DEFAULT',
				'PhoneNumber'	=> $order['telephone']
			);
			
			// Decide on the name
			$customer_id = $order['customer_id'] ? $order['customer_id'] : 'G' . $order['order_id'];
			
			if ($order['payment_company']) {
				$name = $order['payment_company'];
			} elseif ($this->config->get('xero_customer') == 'REPLACE') {
				$name = $order['firstname'] . ' ' . $order['lastname'];
			} elseif ($this->config->get('xero_customer') == 'PREPEND') {
				$name = $customer_id . ' ' . $order['firstname'] . ' ' . $order['lastname'];
			} else {
				$name = $order['firstname'] . ' ' . $order['lastname'] . ' ' . $customer_id;
			}
			
			// Prepare the addresses
			$addresses = array();
			
			$addresses[] = array(
				'AddressType'	=> 'STREET',
				'AttentionTo'	=> html_entity_decode($order['payment_firstname'] . ' ' . $order['payment_lastname'], ENT_QUOTES),
				'AddressLine1'	=> html_entity_decode($order['payment_address_1'], ENT_QUOTES),
				'AddressLine2'	=> html_entity_decode($order['payment_address_2'], ENT_QUOTES),
				'PostalCode'	=> html_entity_decode($order['payment_postcode'], ENT_QUOTES),
				'City'			=> html_entity_decode($order['payment_city'], ENT_QUOTES),
				'Region'		=> html_entity_decode($order['payment_zone'], ENT_QUOTES),
				'Country'		=> html_entity_decode($order['payment_country'], ENT_QUOTES)
			);
			
			if ($order['shipping_firstname']) {
				$addresses[] = array(
					'AddressType'	=> 'POBOX',
					'AttentionTo'	=> html_entity_decode($order['shipping_firstname'] . ' ' . $order['shipping_lastname'], ENT_QUOTES),
					'AddressLine1'	=> html_entity_decode($order['shipping_address_1'], ENT_QUOTES),
					'AddressLine2'	=> html_entity_decode($order['shipping_address_2'], ENT_QUOTES),
					'PostalCode'    => html_entity_decode($order['shipping_postcode'], ENT_QUOTES),
					'City'			=> html_entity_decode($order['shipping_city'], ENT_QUOTES),
					'Region'		=> html_entity_decode($order['shipping_zone'], ENT_QUOTES),
					'Country'		=> html_entity_decode($order['shipping_country'], ENT_QUOTES)
				);
			}
			
			// Prepare the contact
			$contact = array(
				array(
					'ContactStatus'	=> 'ACTIVE',
					'ContactNumber'	=> $customer_id,
					'Name' 			=> $name,
					'FirstName' 	=> html_entity_decode($order['firstname'], ENT_QUOTES),
					'LastName' 		=> html_entity_decode($order['lastname'], ENT_QUOTES),
					'EmailAddress' 	=> $order['email'],
					'Phones' 		=> array(
						'Phone'   => $phone_data
					),
					'Addresses' 	=> array(
						'Address'  => $addresses
					)
				)
			);
			
			$data = array();
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order['order_id'] . "'");
			
			$total_tax = 0;
			
			// Prepare products as line item
			foreach ($query->rows as $product) {
				$product['model'] = html_entity_decode($product['model'], ENT_QUOTES);
				$product['name'] = html_entity_decode($product['name'], ENT_QUOTES);
				
				if ($this->config->get('xero_tax') == "NO" || $this->config->get('xero_tax') == "SINGLE") {
					$tax = 0;
				} else {
					$tax = $product['tax'] * $product['quantity'];

					$total_tax += $product['tax'] * $product['quantity'];
				}
				
				if (!$this->config->get('xero_product')) {
					$product['model'] = '';
				}
				
				if ($tax) {
					$data[] = array(
						'LineItem' => array(
							'Description'	=> $product['name'],
							'ItemCode' 		=> (utf8_strlen($product['model']) > 30) ? utf8_substr($product['model'], 0, 29) : $product['model'],
							'Quantity'		=> $product['quantity'],
							'UnitAmount'	=> $product['price'],
							'AccountCode'	=> $sales_code,
							'TaxAmount'		=> round($tax, 4),
							'LineAmount'	=> $product['price'] * $product['quantity']
						)
					);
				} else {
					$data[] = array(
						'LineItem' => array(
							'Description'	=> $product['name'],
							'ItemCode' 		=> $product['model'],
							'Quantity'		=> $product['quantity'],
							'UnitAmount'	=> $product['price'],
							'TaxType'       => 'NONE',
							'AccountCode'	=> $sales_code,
							'LineAmount'	=> $product['price'] * $product['quantity']
						)
					);
				}
			}
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order['order_id'] . "' ORDER BY sort_order ASC");

			// Prepare order total as line item
			if ($this->config->get('xero_tax') == 'SINGLE') {
				foreach ($query->rows as $order_total) {
					if ($order_total['code'] != 'total' && $order_total['code'] != 'sub_total') {
						if ($order_total['code'] == 'shipping') {
							$code = $shipping_code;
						} else {
							$code = $sales_code;
						}
					
						$data[] = array(
							'LineItem' => array(
								'Description'	=> $order_total['title'],
								'Quantity'		=> 1,
								'AccountCode'	=> $code,
								'UnitAmount'	=> $order_total['value'],
								'LineAmount'	=> $order_total['value']
							)
						);
					}
				}
			} else {
				// Get balance taxes and add to shipping
				$balance_tax = 0;

				foreach ($query->rows as $order_total) {
					if ($order_total['code'] == 'tax') {
						$balance_tax = $order_total['value'] - $total_tax;

						break;
					}
				}
				
				// Do not output tax, Xero will handle it
				if ($this->config->get('xero_tax') == "NO") {
					$balance_tax = 0;
				}

				foreach ($query->rows as $order_total) {
					if ($order_total['code'] != 'total' && $order_total['code'] != 'sub_total' && $order_total['code'] != 'tax') {
						if ($order_total['code'] == 'shipping') {
							$code = $shipping_code;
						} else {
							$code = $sales_code;
						}
						
						if (round($balance_tax, 4) >= 0 && $order_total['code'] == 'shipping') {
							$data[] = array(
								'LineItem' => array(
									'Description'	=> $order_total['title'],
									'Quantity'		=> 1,
									'AccountCode'	=> $code,
									'UnitAmount'	=> $order_total['value'],
									'TaxAmount'		=> round($balance_tax, 4),
									'LineAmount'	=> $order_total['value']
								)
							);
						} elseif ($order_total['code'] == 'coupon') {
							$data[] = array(
								'LineItem' => array(
									'Description'	=> $order_total['title'],
									'Quantity'		=> 1,
									'AccountCode'	=> $code,
									'UnitAmount'	=> $order_total['value'],
									'TaxType'		=> 'NONE',
									'LineAmount'	=> $order_total['value']
								)
							);
						} else {
							$data[] = array(
								'LineItem' => array(
									'Description'	=> $order_total['title'],
									'Quantity'		=> 1,
									'AccountCode'	=> $code,
									'UnitAmount'	=> $order_total['value'],
									'LineAmount'	=> $order_total['value']
								)
							);
						}
					}
				}
			}
			
			// Status of invoices
			if ($refund) {
				if ($this->invoice_status == 'AUTHORISED') {
					$status = 'VOIDED';
				} else {
					$status = 'DELETED';
				}
			} else {
				$status = $this->invoice_status;
			}
			
			// Prepare the invoice
			$new_invoice = array(
				array(
					'Type' 			=> 'ACCREC',
					'Contact' 		=> $contact,
					'Status' 		=> $status,
					'LineItems'		=> $data,
					'CurrencyCode' 	=> $order['currency_code'],
					'CurrencyRate' 	=> $order['currency_value'],
					'Date' 			=> date('Y-m-d', strtotime($order['date_added'])),
					'DueDate' 		=> date('Y-m-d', strtotime($order['date_modified'])),
					'InvoiceNumber'	=> $order['invoice_prefix'] . $order['invoice_no'] .  '-' . $order['order_id'],
					'Total' 		=> $order['total'],
					'Reference'		=> 'OpenCart Order ID: ' . $order['order_id']
				)
			);
			
			// If we want to return data, we don't send to xero
			if ($return) {
				return $new_invoice;
			}

			// Get response from Xero
			$response = $this->xero->invoices($new_invoice);
			
			// Debug log & failed logging
			if ($this->config->get('xero_debug')) {
				$this->log->write(print_r($response, true));
			} elseif (strpos(print_r($response, true), 'ValidationException') !== false) {
				$this->log->write(print_r($response, true));
			}
		} else {
			return false;
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
				'Name'				=> $name,
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
	
	public function sync() {
		$this->startup();

		$response = $this->xero->getItems(array());

		if ($this->config->get('xero_debug')) {
			$this->log->write(print_r($response, true));
		} elseif (strpos(print_r($response, true), 'ValidationException') !== false) {
			$this->log->write(print_r($response, true));
		}
		
		// Count
		$total = 0;
		
		if ($response) {
			foreach ($response->Item as $item) {
				$total++;
				
				$quantity = $item->QuantityOnHand;
				$model = htmlspecialchars($item->Code);
				
				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$quantity . "' WHERE model = '" . $this->db->escape($model) . "'");
			}
		}
		
		return array(
			'success' => $total
		);
	}
}