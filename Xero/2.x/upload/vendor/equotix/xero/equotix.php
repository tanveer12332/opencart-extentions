<?php
if (!class_exists('Equotix')) {
	class Equotix extends Controller {
		public function generateOutput($file, $data = array()) {
			$data['license'] =  array(
				'license_key'		=> $this->config->get($this->code . '_license_license_key'),
				'order_id'			=> $this->config->get($this->code . '_license_order_id'),
				'name' 				=> $this->config->get($this->code . '_license_name'),
				'date_purchased' 	=> $this->config->get($this->code . '_license_date_purchased'),
				'date_expired' 		=> $this->config->get($this->code . '_license_date_expired'),
				'domains' 			=> $this->config->get($this->code . '_license_domains') ? $this->config->get($this->code . '_license_domains') : array()
			);
			
			$data['services'] = $this->config->get($this->code . '_license_services');
			
			$data['token'] = $this->session->data['token'];
			$data['code'] = $this->code;
			
			if (version_compare(VERSION, '2.3.0.0', '>=')) {
				$folder = 'extension/module';
			} else {
				$folder = 'module';
			}
			
			$data['folder'] = isset($this->folder) ? $this->folder : $folder;
			$data['purchase_url'] = $this->purchase_url;
			$data['purchase_id'] = $this->purchase_id;
			
			$this->callbackServer($this->config->get($this->code . '_license_license_key'), false);
			
			$search = array(
				'{version}',
				'view/javascript/jquery/jquery-1.7.1.min.js',
				'view/javascript/jquery/jquery-1.6.1.min.js',
				'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
			);
			
			$base = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;
			
			$replace = array(
				'<div style="color:#222222;text-align:center;">' . $this->extension . ' v' . $this->version . ' by <a href="http://www.marketinsg.com" target="_blank">MarketInSG</a></div>',
				$base . 'vendor/equotix/' . $this->code . '/js/jquery-1.11.3.min.js',
				$base . 'vendor/equotix/' . $this->code . '/js/jquery-1.11.3.min.js',
				$base . 'vendor/equotix/' . $this->code . '/js/jquery-1.11.3.min.js',
				'<!DOCTYPE html>'
			);
			
			if (version_compare(VERSION, '2.0.0.0', '>=')) {
				$this->response->setOutput(str_replace($search, $replace, $this->load->view($file, $data)));
			} else {
				$this->document->addStyle($base . 'vendor/equotix/' . $this->code . '/bootstrap/css/bootstrap.min.css');
				$this->document->addStyle($base . 'vendor/equotix/' . $this->code . '/fontawesome/css/font-awesome.min.css');
				$this->document->addStyle($base . 'vendor/equotix/' . $this->code . '/css/equotix.css');
				$this->document->addScript($base . 'vendor/equotix/' . $this->code . '/bootstrap/js/bootstrap.min.js');
				$this->document->addScript($base . 'vendor/equotix/' . $this->code . '/js/jquery-migrate-1.2.1.min.js');
				$this->document->addScript($base . 'vendor/equotix/' . $this->code . '/js/equotix.js');
				
				$this->data = array_merge($this->data, $data);
				
				$this->template = $file;
				$this->children = array(
					'common/header',
					'common/footer'
				);

				$this->response->setOutput(str_replace($search, $replace, $this->render()));
			}
		}
		
		public function validateLicense() {
			$json = array();
			
			$license_key = isset($this->request->post['license_key']) ? $this->request->post['license_key'] : '';

			$license_info = $this->callbackServer($license_key, true);
			
			if ($license_info['success']) {
				$json['success'] = true;
			} else {
				$json['error'] = $license_info['error'];
			}
			
			$this->response->setOutput(json_encode($json));
		}
		
		private function saveSetting($group, $data) {
			if (version_compare(VERSION, '2.0.0.0', '>')) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `code` = '" . $this->db->escape($group) . "'");

				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
					} else {
						if (version_compare(VERSION, '2.1.0.0', '>=')) {
							$value = json_encode($value);
						} else {
							$value = serialize($value);
						}
					
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', serialized = '1'");
					}
				}
			} else {
				$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '0' AND `group` = '" . $this->db->escape($group) . "'");

				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
					} else {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
					}
				}
			}
		}
		
		protected function validated() {
			$this->callbackServer($this->config->get($this->code . '_license_license_key'), false);
		
			return $this->config->get($this->code . '_license_license_key') ? true : false;
		}
		
		protected function callbackServer($license_key, $reload) {
			$domain = parse_url(HTTP_SERVER);
			$domain = $domain['host'];
			$domain = str_replace('www.', '', $domain);
			$domain = str_replace('http://', '', $domain);
			$domain = str_replace('/', '', $domain);
			$domain = strtolower($domain);
			
			if (is_array($this->config->get($this->code . '_license_domains'))) {
				$search = array();
				
				foreach ($this->config->get($this->code . '_license_domains') as $licensed_domain) {
					$search[] = $licensed_domain['domain'];
				}
				
				if (!in_array($domain, $search)) {
					$data = array(
						$this->code . '_license_license_key'	=> '',
						$this->code . '_license_order_id'		=> '',
						$this->code . '_license_name'			=> '',
						$this->code . '_license_date_purchased'	=> '',
						$this->code . '_license_date_expired'	=> '',
						$this->code . '_license_domains'		=> array(),
						$this->code . '_license_check'			=> time(),
						$this->code . '_license_services'		=> ''
					);
					
					$this->saveSetting($this->code . '_license', $data);
				}
			}
			
			if (!$reload) {
				if ($this->config->get($this->code . '_license_check') && ($this->config->get($this->code . '_license_check') + 172800) >= time()) {
					return;
				}
			}
			
			$post_data = array(
				'license_key'	=> $license_key,
				'extension_id'	=> $this->extension_id,
				'domain'		=> $domain
			);

			$curl = curl_init();
			
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_USERAGENT, 'OpenCart Extension Licensing System');
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, 'http://license.marketinsg.com/index.php?load=common/home/validatelicense');
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

			$response = curl_exec($curl);
			
			if (curl_errno($curl)) {
				$curl_error = 'Error ' . curl_errno($curl) . ': ' . curl_error($curl);
			} else {
				$curl_error = '';
			}

			curl_close($curl);
			
			$response = json_decode($response, true);

			if (isset($response['error'])) {
				$data = array(
					$this->code . '_license_license_key'	=> '',
					$this->code . '_license_order_id'		=> '',
					$this->code . '_license_name'			=> '',
					$this->code . '_license_date_purchased'	=> '',
					$this->code . '_license_date_expired'	=> '',
					$this->code . '_license_domains'		=> array(),
					$this->code . '_license_check'			=> time(),
					$this->code . '_license_services'		=> ''
				);
				
				$this->saveSetting($this->code . '_license', $data);
				
				return array(
					'error' 	=> $response['error'],
					'success' 	=> false
				);
			} elseif(isset($response['success'])) {
				$data = array(
					$this->code . '_license_license_key'	=> $response['license_key'],
					$this->code . '_license_order_id'		=> $response['order_id'],
					$this->code . '_license_name'			=> $response['name'],
					$this->code . '_license_date_purchased'	=> $response['date_purchased'],
					$this->code . '_license_date_expired'	=> $response['date_expired'],
					$this->code . '_license_domains'		=> $response['domains'],
					$this->code . '_license_check'			=> time(),
					$this->code . '_license_services'		=> $response['services']
				);
				
				$this->saveSetting($this->code . '_license', $data);
				
				return array(
					'error'				=> false,
					'success'			=> true
				);
			} else {
				return array(
					'error'		=> base64_decode('VW5hYmxlIHRvIGNvbm5lY3QgdG8gdGhlIGxpY2Vuc2luZyBzZXJ2ZXIuICBQbGVhc2UgZW5zdXJlIHlvdXIgc2VydmVyIGlzIGNvbm5lY3RlZCB0byB0aGUgaW50ZXJuZXQu') . ' ' . $curl_error,
					'success'	=> false
				);
			}
		}
	}
}