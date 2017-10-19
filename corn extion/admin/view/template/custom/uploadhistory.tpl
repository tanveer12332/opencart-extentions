<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
	<div class="alert alert-danger hide"><i class="fa fa-exclamation-circle"></i> <?php echo $error_name?> <button type="button" class="close" data-dismiss="alert">×</button>
            </div>

	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<h3 class="panel-title"><i class="fa fa-bar-chart"></i> <?php echo $text_list; ?></h3>
	  </div>
		<div class="panel-body">
		 <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form-filter">
          <div class="table-responsive">
            <table class="table table-bordered table-hover ">
              <thead>
                <tr>
                  <td class="text-left"><?php if ($sort == 'fg.date_added') { ?>
                    <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_schedule_date; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_name; ?>"><?php echo $column_schedule_date; ?></a>
                    <?php } ?></td>
                  <td class="text-right">
                    <?php echo $column_total_inserted; ?>
                  
                    </td>
                     <td class="text-right">
                    <?php echo $column_total_updated; ?>
                  
                    </td>
                 <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($filters) { ?>
                <?php foreach ($filters as $filter) { ?>
                <tr>
                  <td class="text-left"><?php echo $filter['date_added']; ?></td>
                  <td class="text-right"><?php echo $filter['inserted']; ?></td>
                  <td class="text-right"><?php echo $filter['updated']; ?></td>
                  <td class="text-right"> <button type="button" onclick="deleteBtn('<?php echo $filter['uploadid'] ?>')" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" ><i class="fa fa-trash-o"></i></button></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="4"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </form>
        	
	   <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
 
<script type="text/javascript"><!--


function deleteBtn(id) {
	var url = 'index.php?route=custom/uploadhistory/delete&token=<?php echo $token; ?>';
    confirm("Are you sure want to delete this record");
	 url += '&id=' + encodeURIComponent(id);
	location = url;
};

//--></script>
</div>
<?php echo $footer; ?>