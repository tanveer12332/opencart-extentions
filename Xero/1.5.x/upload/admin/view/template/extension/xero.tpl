<?php echo $header; ?>
<div id="content">
  <div class="container-fluid">
    <div class="pull-right">
      <button type="submit" form="form-module" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
    </div>
	<ul class="breadcrumb">
      <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
      <?php } ?>
    </ul>
</div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
	<?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
		  <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
            <li><a href="#tab-customer" data-toggle="tab"><?php echo $tab_customer; ?></a></li>
            <li><a href="#tab-order" data-toggle="tab"><?php echo $tab_order; ?></a></li>
            <li><a href="#tab-product" data-toggle="tab"><?php echo $tab_product; ?></a></li>
            <li><a href="#tab-about" data-toggle="tab"><i class="fa fa-question-circle"></i> About</a></li>
          </ul>
		  <div class="tab-content">
            <div class="tab-pane active" id="tab-general">
			  <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-api-key"><?php echo $entry_api_key; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="xero_api_key" value="<?php echo $xero_api_key; ?>" placeholder="<?php echo $entry_api_key; ?>" id="input-api-key" class="form-control" />
                  <?php if ($error_api_key) { ?>
				  <div class="text-danger"><?php echo $error_api_key; ?></div>
				  <?php } ?>
				</div>
              </div>
			  <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-api-secret"><?php echo $entry_api_secret; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="xero_api_secret" value="<?php echo $xero_api_secret; ?>" placeholder="<?php echo $entry_api_secret; ?>" id="input-api-secret" class="form-control" />
				  <?php if ($error_api_secret) { ?>
				  <div class="text-danger"><?php echo $error_api_secret; ?></div>
				  <?php } ?>
				</div>
              </div>
			  <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-sales-code"><?php echo $entry_sales_code; ?></label>
                <div class="col-sm-10">
				  <?php foreach ($stores as $store) { ?>
                  <input type="text" name="xero_sales_code[<?php echo $store['store_id']; ?>]" value="<?php echo !empty($xero_sales_code[$store['store_id']]) ? $xero_sales_code[$store['store_id']] : ''; ?>" placeholder="<?php echo $entry_sales_code; ?>" class="form-control" /> (<?php echo $store['name']; ?>)<br />
				  <?php } ?>
				  <?php if ($error_sales_code) { ?>
				  <div class="text-danger"><?php echo $error_sales_code; ?></div>
				  <?php } ?>
				</div>
              </div>
			  <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-shipping-code"><?php echo $entry_shipping_code; ?></label>
                <div class="col-sm-10">
                  <?php foreach ($stores as $store) { ?>
                  <input type="text" name="xero_shipping_code[<?php echo $store['store_id']; ?>]" value="<?php echo !empty($xero_shipping_code[$store['store_id']]) ? $xero_shipping_code[$store['store_id']] : ''; ?>" placeholder="<?php echo $entry_shipping_code; ?>" class="form-control" /> (<?php echo $store['name']; ?>)<br />
				  <?php } ?>
				  <?php if ($error_shipping_code) { ?>
				  <div class="text-danger"><?php echo $error_shipping_code; ?></div>
				  <?php } ?>
				</div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-inventory-code"><?php echo $entry_inventory_code; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="xero_inventory_code" value="<?php echo $xero_inventory_code; ?>" placeholder="<?php echo $entry_inventory_code; ?>" class="form-control" />
				</div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-cogs-code"><?php echo $entry_cogs_code; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="xero_cogs_code" value="<?php echo $xero_cogs_code; ?>" placeholder="<?php echo $entry_cogs_code; ?>" class="form-control" />
				</div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-http-loopback"><?php echo $entry_http_loopback; ?></label>
                <div class="col-sm-10">
                  <select name="xero_http_loopback" id="input-http-loopback" class="form-control">
					<?php if ($xero_http_loopback) { ?>
					<option value="1" selected="selected">Yes</option>
					<option value="0">No</option>
					<?php } else { ?>
					<option value="1">Yes</option>
					<option value="0" selected="selected">No</option>
					<?php } ?>
				  </select>
				</div>
              </div>
			  <div class="form-group required">
                <label class="col-sm-2 control-label" for="input-token"><?php echo $entry_token; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="xero_token" value="<?php echo $xero_token; ?>" placeholder="<?php echo $entry_token; ?>" id="input-token" class="form-control" />
				  <?php if ($error_token) { ?>
				  <div class="text-danger"><?php echo $error_token; ?></div>
				  <?php } ?>
				</div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-10">
                  <textarea cols="100" rows="3" class="form-control" readonly="true"><?php echo $cron_command; ?></textarea>
				</div>
              </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-debug"><?php echo $entry_debug; ?></label>
				<div class="col-sm-10">
				  <div class="checkbox">
				    <label>
					  <?php if ($xero_debug) { ?>
					  <input type="checkbox" name="xero_debug" value="1" checked="checked" />
					  <?php } else { ?>
					  <input type="checkbox" name="xero_debug" value="1" />
					  <?php } ?>
					</label>
				  </div>
				</div>
			  </div>
			</div>
			<div class="tab-pane" id="tab-customer">
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-customer"><?php echo $entry_customer; ?></label>
				<div class="col-sm-10">
				  <select name="xero_customer" id="input-customer" class="form-control">
					<?php if ($xero_customer == "REPLACE") { ?>
					<option value="REPLACE" selected="selected"><?php echo $text_replace; ?></option>
					<option value="PREPEND"><?php echo $text_prepend; ?></option>
					<option value="APPEND"><?php echo $text_append; ?></option>
					<?php } elseif ($xero_customer == "PREPEND") { ?>
					<option value="REPLACE"><?php echo $text_replace; ?></option>
					<option value="PREPEND" selected="selected"><?php echo $text_prepend; ?></option>
					<option value="APPEND"><?php echo $text_append; ?></option>
					<?php } else { ?>
					<option value="REPLACE"><?php echo $text_replace; ?></option>
					<option value="PREPEND"><?php echo $text_prepend; ?></option>
					<option value="APPEND" selected="selected"><?php echo $text_append; ?></option>
					<?php } ?>
				  </select>
				</div>
			  </div>
			  <div class="form-group">
			    <label class="col-sm-2 control-label"><?php echo $button_export_customer; ?></label>
				<div class="col-sm-10">
				  <a href="<?php echo $export_customer; ?>" data-toggle="tooltip" title="<?php echo $button_export_customer; ?>" class="btn btn-success"<?php echo $xero_http_loopback ? ' target="_blank"' : ''; ?>><i class="fa fa-users"></i></a>
				</div>
			  </div>
			</div>
			<div class="tab-pane" id="tab-order">
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-invoice-status"><?php echo $entry_invoice_status; ?></label>
				<div class="col-sm-10">
				  <select name="xero_invoice_status" id="input-invoice-status" class="form-control">
					<?php if ($xero_invoice_status == "AUTHORISED") { ?>
					<option value="AUTHORISED" selected="selected">AUTHORISED</option>
					<option value="DRAFT">DRAFT</option>
					<?php } else { ?>
					<option value="AUTHORISED">AUTHORISED</option>
					<option value="DRAFT" selected="selected">DRAFT</option>
					<?php } ?>
				  </select>
				</div>
			  </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-allowed-order"><?php echo $entry_allowed_order; ?></label>
                <div class="col-sm-10">
                  <div class="well well-sm" style="height: 250px; overflow: auto;">
					<?php foreach ($order_statuses as $order_status) { ?>
					  <div class="checkbox">
                        <label>
                          <?php if (in_array($order_status['order_status_id'], $xero_order_status)) { ?>
                          <input type="checkbox" name="xero_order_status[]" value="<?php echo $order_status['order_status_id']; ?>" checked="checked" />
                          <?php echo $order_status['name']; ?>
                          <?php } else { ?>
                          <input type="checkbox" name="xero_order_status[]" value="<?php echo $order_status['order_status_id']; ?>" />
                          <?php echo $order_status['name']; ?>
                          <?php } ?>
                        </label>
                      </div>
					<?php } ?>
				  </div>
				</div>
              </div>
			  <div class="form-group">
                <label class="col-sm-2 control-label" for="input-refund-order"><?php echo $entry_refund_order; ?></label>
                <div class="col-sm-10">
                  <div class="well well-sm" style="height: 250px; overflow: auto;">
					<?php foreach ($order_statuses as $order_status) { ?>
					  <div class="checkbox">
                        <label>
                          <?php if (in_array($order_status['order_status_id'], $xero_order_refund)) { ?>
                          <input type="checkbox" name="xero_order_refund[]" value="<?php echo $order_status['order_status_id']; ?>" checked="checked" />
                          <?php echo $order_status['name']; ?>
                          <?php } else { ?>
                          <input type="checkbox" name="xero_order_refund[]" value="<?php echo $order_status['order_status_id']; ?>" />
                          <?php echo $order_status['name']; ?>
                          <?php } ?>
                        </label>
                      </div>
					<?php } ?>
				  </div>
				</div>
              </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-tax"><?php echo $entry_tax; ?></label>
				<div class="col-sm-10">
				  <select name="xero_tax" id="input-tax" class="form-control">
					<?php if ($xero_tax == "SINGLE") { ?>
					<option value="SINGLE" selected="selected"><?php echo $text_tax_single; ?></option>
					<option value="MULTIPLE"><?php echo $text_tax_multiple; ?></option>
					<option value="NO"><?php echo $text_tax_no; ?></option>
					<?php } elseif ($xero_tax == "MULTIPLE") { ?>
					<option value="SINGLE"><?php echo $text_tax_single; ?></option>
					<option value="MULTIPLE" selected="selected"><?php echo $text_tax_multiple; ?></option>
					<option value="NO"><?php echo $text_tax_no; ?></option>
					<?php } else { ?>
					<option value="SINGLE"><?php echo $text_tax_single; ?></option>
					<option value="MULTIPLE"><?php echo $text_tax_multiple; ?></option>
					<option value="NO" selected="selected"><?php echo $text_tax_no; ?></option>
					<?php } ?>
				  </select>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-mark"><?php echo $button_export_order; ?></label>
				<div class="col-sm-10">
				  <a href="<?php echo $export_order; ?>" data-toggle="tooltip" title="<?php echo $button_export_order; ?>" class="btn btn-success"<?php echo $xero_http_loopback ? ' target="_blank"' : ''; ?>><i class="fa fa-book"></i></a>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-mark"><?php echo $entry_mark; ?></label>
				<div class="col-sm-10">
				  <a href="<?php echo $mark; ?>" class="btn btn-danger" data-toggle="tooltip" title="<?php echo $button_mark; ?>"><i class="fa fa-check-circle"></i></a>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-unmark"><?php echo $entry_unmark; ?></label>
				<div class="col-sm-10">
				  <a href="<?php echo $unmark; ?>" class="btn btn-danger" data-toggle="tooltip" title="<?php echo $button_unmark; ?>"><i class="fa fa-times-circle"></i></a>
				</div>
			  </div>
			</div>
			<div class="tab-pane" id="tab-product">
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-product"><?php echo $entry_product; ?></label>
				<div class="col-sm-10">
				  <select name="xero_product" id="input-product" class="form-control">
					<?php if ($xero_product) { ?>
					<option value="1" selected="selected">Yes</option>
					<option value="0">No</option>
					<?php } else { ?>
					<option value="1">Yes</option>
					<option value="0" selected="selected">No</option>
					<?php } ?>
				  </select>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><?php echo $button_export_product; ?></label>
				<div class="col-sm-10">
				  <a href="<?php echo $export_product; ?>" class="btn btn-success" data-toggle="tooltip" title="<?php echo $button_export_product; ?>"<?php echo $xero_http_loopback ? ' target="_blank"' : ''; ?>><i class="fa fa-arrow-circle-right"></i></a>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-sm-2 control-label"><?php echo $button_sync_product; ?></label>
				<div class="col-sm-10">
				  <a href="<?php echo $sync_product; ?>" class="btn btn-success" data-toggle="tooltip" title="<?php echo $button_sync_product; ?>"<?php echo $xero_http_loopback ? ' target="_blank"' : ''; ?>><i class="fa fa-refresh"></i></a>
				</div>
			  </div>
			</div>
			<?php require_once(substr_replace(DIR_SYSTEM, '', -7) . 'vendor/equotix/' . $code . '/equotix.tpl'); ?>
		  </div>
		</form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>