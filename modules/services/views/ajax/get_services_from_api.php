<label><?php echo lang('list_of_api_services'); ?></label>
<select name="api_service_id" class="form-control square ajaxGetServiceDetail">
  <?php
    if (!empty($services)) {
      foreach ($services as $key => $row) {
        $service_type = (isset($row->type)) ? strtolower(strip_tags($row->type)) : 'default';;
        $service_type = str_replace(" ", "_", $service_type);
  ?>
    <option value="<?php echo strip_tags($row->service); ?>" isred='1' data-rate="<?php echo strip_tags($row->rate); ?>" data-min="<?php echo strip_tags($row->min); ?>" data-max="<?php echo strip_tags($row->max); ?>" data-name="<?php echo strip_tags($row->name); ?>" data-type="<?php echo $service_type; ?>" data-dripfeed="<?=(isset($row->dripfeed) && $row->dripfeed) ? 1 : 0; ?>"   <?php if(isset($api_service_id) && $api_service_id == $row->service) echo 'selected'; else echo ''; ?> > 

    	<?php
    		$service = $row->service. ' - ['.$row->rate.'] - '.truncate_string($row->name, 60);
    	 echo strip_tags($service); 
    	?>
    </option>
  <?php }} ?>
</select>

