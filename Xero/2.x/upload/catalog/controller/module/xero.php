<?php
require_once(substr_replace(DIR_SYSTEM, '', -7) . 'vendor/equotix/xero/equotix.php');
class ControllerModuleXero extends Equotix {
	protected $code = 'xero';
	protected $extension_id = '81';
	
  	public function index() {
		if (isset($this->request->get['token']) && ($this->request->get['token'] == $this->config->get('xero_token')) && $this->validated()) {
			// Trigger orders
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, html_entity_decode($this->url->link('module/xero/orders', 'token=' . $this->config->get('xero_token')), ENT_QUOTES));
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_NOBODY, true);
			curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 5);
			curl_exec($curl);
			curl_close($curl);
			
			// Trigger sync
			if ($this->config->get('xero_product')) {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, html_entity_decode($this->url->link('module/xero/sync', 'token=' . $this->config->get('xero_token')), ENT_QUOTES));
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_NOBODY, true);
				curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_TIMEOUT, 5);
				curl_exec($curl);
				curl_close($curl);
			}
		}
  	}
	
	public function orders() {
		if (isset($this->request->get['token']) && ($this->request->get['token'] == $this->config->get('xero_token')) && $this->validated()) {			
			$start = 0;
			$limit = 10;
			
			$this->load->model('extension/xero');
		
			$data = $this->model_extension_xero->exportOrders($start, $limit);
			
			if ($this->config->get('xero_debug')) {
				$this->log->write('XERO DEBUG ORDERS :: EXPORTED ' . $limit . ' orders with ' . $data['success'] . ' successful, ' . $data['ignored'] . ' ignored and ' . $data['failed'] . ' failed.');
			}
			
			$total = $this->model_extension_xero->getTotalOrders();
			
			if (($start + $limit) < $total) {
				if ($this->config->get('xero_http_loopback')) {
					$this->response->redirect($this->url->link('module/xero/orders', 'token=' . $this->config->get('xero_token')));
				} else {
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, html_entity_decode($this->url->link('module/xero/orders', 'token=' . $this->config->get('xero_token')), ENT_QUOTES));
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_NOBODY, true);
					curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_TIMEOUT, 5);
					curl_exec($curl);
					curl_close($curl);
				}
			}
		}
	}
	
	public function customers() {
		if (isset($this->request->get['token']) && ($this->request->get['token'] == $this->config->get('xero_token')) && $this->validated()) {
			if (!isset($this->request->get['start'])) {
				$start = 0;
			} else {
				$start = $this->request->get['start'];
			}
			
			$limit = 50;
			
			$this->load->model('extension/xero');
		
			$data = $this->model_extension_xero->exportCustomers($start, $limit);
			
			if ($this->config->get('xero_debug')) {
				$this->log->write('XERO DEBUG CUSTOMERS :: EXPORTED ' . ($start + 1) . ' to ' . ($start + $limit) . ' with ' . $data['success'] . ' successful and ' . $data['failed'] . ' failed.');
			}
			
			$total = $this->model_extension_xero->getTotalCustomers();
			
			if (($start + $limit) < $total) {
				if ($this->config->get('xero_http_loopback')) {
					$this->response->redirect($this->url->link('module/xero/customers', 'token=' . $this->config->get('xero_token') . '&start=' . ($start + $limit)));
				} else {
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, html_entity_decode($this->url->link('module/xero/customers', 'token=' . $this->config->get('xero_token') . '&start=' . ($start + $limit)), ENT_QUOTES));
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_NOBODY, true);
					curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_TIMEOUT, 5);
					curl_exec($curl);
					curl_close($curl);
				}
			}
		}
	}
	
	public function products() {
		if (isset($this->request->get['token']) && ($this->request->get['token'] == $this->config->get('xero_token')) && $this->validated()) {
			if (!isset($this->request->get['start'])) {
				$start = 0;
			} else {
				$start = $this->request->get['start'];
			}
			
			$limit = 50;
			
			$this->load->model('extension/xero');
		
			$data = $this->model_extension_xero->exportProducts($start, $limit);
			
			if ($this->config->get('xero_debug')) {
				$this->log->write('XERO DEBUG PRODUCTS :: EXPORTED ' . ($start + 1) . ' to ' . ($start + $limit) . ' with ' . $data['success'] . ' successful and ' . $data['failed'] . ' failed.');
			}
			
			$total = $this->model_extension_xero->getTotalProducts();
			
			if (($start + $limit) < $total) {
				if ($this->config->get('xero_http_loopback')) {
					$this->response->redirect($this->url->link('module/xero/products', 'token=' . $this->config->get('xero_token') . '&start=' . ($start + $limit)));
				} else {
					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, html_entity_decode($this->url->link('module/xero/products', 'token=' . $this->config->get('xero_token') . '&start=' . ($start + $limit)), ENT_QUOTES));
					curl_setopt($curl, CURLOPT_HEADER, false);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_NOBODY, true);
					curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($curl, CURLOPT_TIMEOUT, 5);
					curl_exec($curl);
					curl_close($curl);
				}
			}
		}
	}
	
	public function sync() {
		if (isset($this->request->get['token']) && ($this->request->get['token'] == $this->config->get('xero_token')) && $this->validated()) {
			$this->load->model('extension/xero');
			
			$data = $this->model_extension_xero->sync();

			if ($this->config->get('xero_debug')) {
				$this->log->write('XERO DEBUG SYNC :: ' . $data['success'] . ' products sync in total with Xero.');
			}
		}
	}
}