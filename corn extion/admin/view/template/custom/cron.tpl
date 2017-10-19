<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_title; ?></h1>
	  <div class="pull-right"><a href="<?php echo $upload_history_url ?>" data-toggle="tooltip" title="<?php echo $upload_history; ?>" class="btn btn-primary"><i class="fa fa-history"></i> <?php echo $upload_history ?></a></div>
		  <div class="clearfix"></div>
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
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-bar-chart"></i> <?php echo $text_add; ?></h3>
      </div>
      <div class="panel-body clearfix">
        <div class="well">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class="control-label" for="input-date-start"><?php echo $exception_des; ?></label>
                  <input type="text" name="exception_des" value="<?php echo $exception_des; ?>" placeholder="<?php echo $exception_des; ?>"  class="form-control input-block-lavel" />
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class="control-label" for="input-date-end"><?php echo $exception_days; ?></label>
                <div class="input-group date">
                  <input type="text" name="exception_days"  placeholder="<?php echo $exception_days; ?>" data-date-format="DD-MM-YYYY" id="input-date-end" class="form-control" />
                  <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                  </span></div>
              </div>
             
            </div>
			
          </div>
		  <div class="clearfix"></div>
		  <div class="pull-right"><a href="javascript:void(0)" id="button-add" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo $button_add ?></a></div>
		  <div class="clearfix"></div>
        </div>
	   </div>
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
                  <td class="text-left"><?php if ($sort == 'fg.description') { ?>
                    <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_exception_dec; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_name; ?>"><?php echo $column_exception_dec; ?></a>
                    <?php } ?></td>
                  <td class="text-right">
                    <?php echo $column_date; ?>
                  
                    </td>
                 <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($filters) { ?>
                <?php foreach ($filters as $filter) { ?>
                <tr>
                  <td class="text-left"><?php echo $filter['description']; ?></td>
                  <td class="text-right"><?php echo $filter['sheduledate']; ?></td>
                  <td class="text-right"> <button type="button" onclick="deleteBtn('<?php echo $filter['cron_id'] ?>')" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger" ><i class="fa fa-trash-o"></i></button></td>
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
$('#button-add').on('click', function() {
	var exception_des = $('input[name=\'exception_des\']').val();
	var exception_days = $('input[name=\'exception_days\']').val();
	if(exception_des == ""||exception_days == ""){
		$('.alert').removeClass('hide');
	}else{
		$('.alert').addClass('hide');
		 var url = 'index.php?route=custom/cron/add&token=<?php echo $token; ?>';

		 if (exception_des) {
		  url += '&exception_des=' + encodeURIComponent(exception_des);
		 }
		 if (exception_days) {
                   var d=new Date(exception_days.split("/").reverse().join("-"));
                   var dd=d.getDate();
                   var mm=d.getMonth()+1;
                   var yy=d.getFullYear();
                   var newdate =yy+"-"+mm+"-"+dd;
		  url += '&exception_days=' + newdate;
		 }

		 location = url;
	}
});

function deleteBtn(id) {
	var url = 'index.php?route=custom/cron/delete&token=<?php echo $token; ?>';
    confirm("Are you sure want to delete");
	 url += '&id=' + encodeURIComponent(id);
	location = url;
};

//--></script>
  <script type="text/javascript"><!--
$('.date').datetimepicker({
	pickTime: true,
  date:false
});
//--></script> 

</div>
<?php echo $footer; ?>