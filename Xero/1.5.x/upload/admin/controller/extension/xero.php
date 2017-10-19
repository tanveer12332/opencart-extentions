<?php
require_once(substr_replace(DIR_SYSTEM, '', -7) . 'vendor/equotix/xero/equotix.php');
class ControllerExtensionXero extends Equotix { 
	protected $version = '3.2.1';
	protected $code = 'xero';
	protected $folder = 'extension';
	protected $extension = 'Xero Integration';
	protected $extension_id = '81';
	protected $purchase_url = 'xero-integration';
	protected $purchase_id = '14643';
	protected $error = array();
  
  	public function index() {
		$this->language->load('extension/xero');
		 
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('xero', $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/xero', 'token=' . $this->session->data['token'], 'SSL'));
		}
			
		$this->data['text_replace'] = $this->language->get('text_replace');
		$this->data['text_prepend'] = $this->language->get('text_prepend');
		$this->data['text_append'] = $this->language->get('text_append');
		$this->data['text_tax_single'] = $this->language->get('text_tax_single');
		$this->data['text_tax_multiple'] = $this->language->get('text_tax_multiple');
		$this->data['text_tax_no'] = $this->language->get('text_tax_no');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_customer'] = $this->language->get('tab_customer');
		$this->data['tab_order'] = $this->language->get('tab_order');
		$this->data['tab_product'] = $this->language->get('tab_product');
		
		$this->data['entry_api_key'] = $this->language->get('entry_api_key');
		$this->data['entry_api_secret'] = $this->language->get('entry_api_secret');
		$this->data['entry_sales_code'] = $this->language->get('entry_sales_code');
		$this->data['entry_invoice_status'] = $this->language->get('entry_invoice_status');
		$this->data['entry_customer'] = $this->language->get('entry_customer');
		$this->data['entry_allowed_order'] = $this->language->get('entry_allowed_order');
		$this->data['entry_refund_order'] = $this->language->get('entry_refund_order');
		$this->data['entry_http_loopback'] = $this->language->get('entry_http_loopback');
		$this->data['entry_token'] = $this->language->get('entry_token');
		$this->data['entry_debug'] = $this->language->get('entry_debug');
		$this->data['entry_mark'] = $this->language->get('entry_mark');
		$this->data['entry_unmark'] = $this->language->get('entry_unmark');
		$this->data['entry_tax'] = $this->language->get('entry_tax');
		$this->data['entry_product'] = $this->language->get('entry_product');
		$this->data['entry_shipping_code'] = $this->language->get('entry_shipping_code');
		$this->data['entry_inventory_code'] = $this->language->get('entry_inventory_code');
		$this->data['entry_cogs_code'] = $this->language->get('entry_cogs_code');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_export_customer'] = $this->language->get('button_export_customer');
		$this->data['button_export_order'] = $this->language->get('button_export_order');
		$this->data['button_export_product'] = $this->language->get('button_export_product');
		$this->data['button_sync_product'] = $this->language->get('button_sync_product');
		$this->data['button_mark'] = $this->language->get('button_mark');
		$this->data['button_unmark'] = $this->language->get('button_unmark');
		
		$this->data['action'] = $this->url->link('extension/xero', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['mark'] = $this->url->link('extension/xero/mark', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['unmark'] = $this->url->link('extension/xero/unmark', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['export_customer'] = $this->url->link('extension/xero/export', 'token=' . $this->session->data['token'] . '&type=customers', 'SSL'); 
		$this->data['export_order'] = $this->url->link('extension/xero/export', 'token=' . $this->session->data['token'] . '&type=orders', 'SSL'); 
		$this->data['export_product'] = $this->url->link('extension/xero/export', 'token=' . $this->session->data['token'] . '&type=products', 'SSL'); 
		$this->data['sync_product'] = $this->url->link('extension/xero/export', 'token=' . $this->session->data['token'] . '&type=sync', 'SSL'); 
		
		if ($this->config->get('xero_http_loopback')) {
			$this->data['cron_command'] = 'Please create a cron job to trigger the following URLs:' . "\n" . HTTP_CATALOG . 'index.php?route=module/xero/orders&token=' . $this->config->get('xero_token');
			$this->data['cron_command'] .= "\n" . HTTP_CATALOG . 'index.php?route=module/xero/sync&token=' . $this->config->get('xero_token');
		} else {
			$this->data['cron_command'] = 'curl -s -o /dev/null "' . HTTP_CATALOG . 'index.php?route=module/xero&token=' . $this->config->get('xero_token') . '"';
		}
		
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/xero', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['api_secret'])) {
			$this->data['error_api_secret'] = $this->error['api_secret'];
		} else {
			$this->data['error_api_secret'] = '';
		}
		
		if (isset($this->error['api_key'])) {
			$this->data['error_api_key'] = $this->error['api_key'];
		} else {
			$this->data['error_api_key'] = '';
		}
		
		if (isset($this->error['sales_code'])) {
			$this->data['error_sales_code'] = $this->error['sales_code'];
		} else {
			$this->data['error_sales_code'] = '';
		}
		
		if (isset($this->error['shipping_code'])) {
			$this->data['error_shipping_code'] = $this->error['shipping_code'];
		} else {
			$this->data['error_shipping_code'] = '';
		}
		
		if (isset($this->error['token'])) {
			$this->data['error_token'] = $this->error['token'];
		} else {
			$this->data['error_token'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		if (isset($this->request->post['xero_api_key'])) {
			$this->data['xero_api_key'] = $this->request->post['xero_api_key'];
		} else {
			$this->data['xero_api_key'] = $this->config->get('xero_api_key');
		}
		
		if (isset($this->request->post['xero_api_secret'])) {
			$this->data['xero_api_secret'] = $this->request->post['xero_api_secret'];
		} else {
			$this->data['xero_api_secret'] = $this->config->get('xero_api_secret');
		}

		if (isset($this->request->post['xero_sales_code'])) {
			$this->data['xero_sales_code'] = $this->request->post['xero_sales_code'];
		} else {
			$this->data['xero_sales_code'] = $this->config->get('xero_sales_code');
		}
		
		if (isset($this->request->post['xero_shipping_code'])) {
			$this->data['xero_shipping_code'] = $this->request->post['xero_shipping_code'];
		} else {
			$this->data['xero_shipping_code'] = $this->config->get('xero_shipping_code');
		}
		
		if (isset($this->request->post['xero_inventory_code'])) {
			$this->data['xero_inventory_code'] = $this->request->post['xero_inventory_code'];
		} else {
			$this->data['xero_inventory_code'] = $this->config->get('xero_inventory_code');
		}
		
		if (isset($this->request->post['xero_cogs_code'])) {
			$this->data['xero_cogs_code'] = $this->request->post['xero_cogs_code'];
		} else {
			$this->data['xero_cogs_code'] = $this->config->get('xero_cogs_code');
		}
		
		if (isset($this->request->post['xero_invoice_status'])) {
			$this->data['xero_invoice_status'] = $this->request->post['xero_invoice_status'];
		} else {
			$this->data['xero_invoice_status'] = $this->config->get('xero_invoice_status');
		}
		
		if (isset($this->request->post['xero_customer'])) {
			$this->data['xero_customer'] = $this->request->post['xero_customer'];
		} else {
			$this->data['xero_customer'] = $this->config->get('xero_customer');
		}
		
		if (isset($this->request->post['xero_token'])) {
			$this->data['xero_token'] = $this->request->post['xero_token'];
		} else {
			$this->data['xero_token'] = $this->config->get('xero_token');
		}
		
		if (isset($this->request->post['xero_http_loopback'])) {
			$this->data['xero_http_loopback'] = $this->request->post['xero_http_loopback'];
		} else {
			$this->data['xero_http_loopback'] = $this->config->get('xero_http_loopback');
		}
		
		$this->data['xero_order_status'] = array();
		
		if (isset($this->request->post['xero_order_status'])) {
			$this->data['xero_order_status'] = $this->request->post['xero_order_status'];
		} elseif ($this->config->get('xero_order_status')) {
			$this->data['xero_order_status'] = $this->config->get('xero_order_status');
		}
		
		$this->data['xero_order_refund'] = array();
		
		if (isset($this->request->post['xero_order_refund'])) {
			$this->data['xero_order_refund'] = $this->request->post['xero_order_refund'];
		} elseif ($this->config->get('xero_order_refund')) {
			$this->data['xero_order_refund'] = $this->config->get('xero_order_refund');
		}
		
		if (isset($this->request->post['xero_tax'])) {
			$this->data['xero_tax'] = $this->request->post['xero_tax'];
		} elseif ($this->config->get('xero_tax')) {
			$this->data['xero_tax'] = $this->config->get('xero_tax');
		} else {
			$this->data['xero_tax'] = 0;
		}
		
		if (isset($this->request->post['xero_product'])) {
			$this->data['xero_product'] = $this->request->post['xero_product'];
		} elseif ($this->config->get('xero_product')) {
			$this->data['xero_product'] = $this->config->get('xero_product');
		} else {
			$this->data['xero_product'] = 0;
		}

		if (isset($this->request->post['xero_debug'])) {
			$this->data['xero_debug'] = $this->request->post['xero_debug'];
		} elseif ($this->config->get('xero_debug')) {
			$this->data['xero_debug'] = $this->config->get('xero_debug');
		} else {
			$this->data['xero_debug'] = 0;
		}
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->load->model('setting/store');
		
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		$this->data['stores'][] = array(
			'store_id'		=> 0,
			'name'			=> $this->language->get('text_default')
		);
		
		$this->generateOutput('extension/xero.tpl');
  	}
	
	public function export() {
		$this->language->load('extension/xero');
				
		if ($this->user->hasPermission('modify', 'extension/xero')) {
			if (isset($this->request->get['type'])) {
				$type = $this->request->get['type'];
			} else {
				$type = 'orders';
			}
		
			if ($this->config->get('xero_http_loopback')) {
				$this->response->redirect(HTTP_CATALOG . 'index.php?route=module/xero/' . $type . '&token=' . $this->config->get('xero_token'));
			} else {
				$curl = curl_init(); 
				curl_setopt($curl, CURLOPT_URL, HTTP_CATALOG . 'index.php?route=module/xero/' . $type . '&token=' . $this->config->get('xero_token'));
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_NOBODY, true);
				curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_TIMEOUT, 5);
				curl_exec($curl);
				curl_close($curl);
			}
			
			$this->session->data['success'] = $this->language->get('text_export_success');
		}

		$this->redirect($this->url->link('extension/xero', 'token=' . $this->session->data['token'], 'SSL'));
  	}
	
	public function mark() {
		$this->language->load('extension/xero');
		
		if ($this->user->hasPermission('modify', 'extension/xero')) {
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET xero_exported = '1'");
			
			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		$this->redirect($this->url->link('extension/xero', 'token=' . $this->session->data['token'], 'SSL'));
	}
	
	public function unmark() {
		$this->language->load('extension/xero');
	
		if ($this->user->hasPermission('modify', 'extension/xero')) {
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET xero_exported = '0'");
			
			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		$this->redirect($this->url->link('extension/xero', 'token=' . $this->session->data['token'], 'SSL'));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/xero') || !$this->validated()) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['xero_api_key']) {
			$this->error['api_key'] = $this->language->get('error_api_key');
		}	
		
		if (!$this->request->post['xero_api_secret']) {
			$this->error['api_secret'] = $this->language->get('error_api_secret');
		}
		
		foreach ($this->request->post['xero_sales_code'] as $key => $value) {
			if (empty($value)) {
				$this->error['sales_code'] = $this->language->get('error_sales_code');
			}
		}
		
		foreach ($this->request->post['xero_shipping_code'] as $key => $value) {
			if (empty($value)) {
				$this->error['shipping_code'] = $this->language->get('error_shipping_code');
			}
		}
		
		if (!$this->request->post['xero_order_status']) {
			$this->error['order_status'] = $this->language->get('error_order_status');
		}
		
		if (!$this->request->post['xero_token']) {
			$this->error['token'] = $this->language->get('error_token');
		}

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order`");
		$exists = false;
			
		foreach ($query->rows as $result) {
			if ($result['Field'] == 'xero_exported') {
				$exists = true;
				break;
			}
		}
		
		if (!$exists) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD xero_exported INT(1) NOT NULL DEFAULT '0' AFTER date_modified");
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>