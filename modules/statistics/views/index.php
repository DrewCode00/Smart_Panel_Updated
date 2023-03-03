
<?php
  $data_tickets_log = $data_log->data_tickets;
  $data_orders_log  = $data_log->data_orders;

  switch (get_option('currency_decimal_separator', 'dot')) {
    case 'dot':
      $decimalpoint = '.';
      break;
    case 'comma':
      $decimalpoint = ',';
      break;
    default:
      $decimalpoint = '';
      break;
  } 

  switch (get_option('currency_thousand_separator', 'comma')) {
    case 'dot':
      $separator = '.';
      break;
    case 'comma':
      $separator = ',';
      break;
    case 'space':
      $separator = ' ';
      break;
    default:
      $separator = '';
      break;
  }
  $currency_symbol = get_option('currency_symbol',"$");
?>
<div class="row justify-content-center row-card statistics">
  <div class="col-sm-12">
    <div class="row">
      <?php
        if (get_role("admin")) {
      ?>
      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
              <i class="fe fe-users"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_log->total_users?></h4>
                <small class="text-muted "><?=lang("total_users")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php }else{ ?>
      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=get_option('currency_symbol',"$")?><?=(!empty($data_log->user_balance)) ? currency_format($data_log->user_balance, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0?></h4>
                <small class="text-muted "><?=lang("your_balance")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-info-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=get_option('currency_symbol',"$")?><?=(!empty($data_log->total_spent_receive)) ? currency_format($data_log->total_spent_receive, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0?></h4>
                <small class="text-muted ">
                  <?=(get_role("admin") ? lang("total_amount_recieved") : lang("total_amount_spent"))?>
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-warning-gradient text-white mr-3">
              <i class="fe fe-shopping-cart"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->total?></h4>
                <small class="text-muted "><?=lang("total_orders")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-danger-gradient text-white mr-3">
              <i class="fa fa-ticket"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->total?></h4>
                <small class="text-muted "><?=lang("total_tickets")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
      if (get_role("admin")) {
    ?>
    <div class="row">
      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
              <i class="icon-fa fa fa-money"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->users_balance, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_users_balance"); ?> </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-info-gradient text-white mr-3">
              <i class="icon-fa fa fa-balance-scale"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->providers_balance, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_providers_balance"); ?> </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-warning-gradient text-white mr-3">
              <i class="icon-fa fa fa-calculator"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->last_profit_30_days, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_profit_30_days"); ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-danger-gradient text-white mr-3">
              <i class="icon-fa fa fa-calculator"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->profit_today, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_profit_today"); ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="row">
      <!-- Order -->
      <div class="col-sm-12 charts">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><?=lang("recent_orders")?></h3>
          </div>
          <div class="row">
            <div class="col-sm-8">
              <div class="p-4 card">
                <div id="orders_chart_spline" style="height: 20rem;"></div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="p-4 card">
                <div id="orders_chart_pie" style="height: 20rem;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-list"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->total?></h4>
                <small class="text-muted "><?=lang("total_orders")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-check"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 number"><?=$data_orders_log->completed?></h4>
                <small class="text-muted"><?=lang("Completed")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-trending-up"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->processing?></h4>
                <small class="text-muted "><?=lang("Processing")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-loader"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->inprogress?></h4>
                <small class="text-muted "><?=lang("In_progress")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-pie-chart"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->pending?></h4>
                <small class="text-muted "><?=lang("Pending")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fa fa-hourglass-half"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->partial?></h4>
                <small class="text-muted "><?=lang("Partial")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>    

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-x-square"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->canceled?></h4>
                <small class="text-muted "><?=lang("Canceled")?></small>
              </div>
            </div>
          </div>
        </div>
      </div> 

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-rotate-ccw"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->refunded?></h4>
                <small class="text-muted "><?=lang("Refunded")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>    
      <?php
        if (get_role('admin')) {
      ?>
      <!-- tickets -->
      <div class="col-sm-12 charts">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><?=lang("recent_tickets")?></h3>
          </div>
          <div class="row">
            <div class="col-sm-8">
              <div class="p-4 card">
                <div id="tickets_chart_spline" style="height: 20rem;"></div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="p-4 card">
                <div id="tickets_chart_pie" style="height: 20rem;"></div>
              </div>
            </div>
          </div>
          
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fa fa-ticket"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->total?></h4>
                <small class="text-muted "><?=lang("total_tickets")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-mail"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 number"><?=$data_tickets_log->new?></h4>
                <small class="text-muted"><?=lang("New")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-pie-chart"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->pending?></h4>
                <small class="text-muted "><?=lang("Pending")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-check"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->closed?></h4>
                <small class="text-muted "><?=lang("Closed")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php }?>
    </div>
  </div>
</div>


<div class="row justify-content-center">
  
  <!-- Top Best Sellers -->
  <div class="col-md-12">
    <?php

      if (get_role('admin')) {
        $columns_best_seller = array(
          "name"             => lang("Name"),
          "total_orders"     => lang("total_orders"),
          "add_type"         => lang("Type"),
          "provider"         => lang("api_provider"),
          "api_service_id"   => lang("api_service_id"),
          "price"            => lang("rate_per_1000")."(".get_option("currency_symbol","").")",
          "min_max"          => lang("min__max_order"),
          "desc"             => lang("Description"),
          "status"           => lang("Status"),
        );
      }else{
        $columns_best_seller = array(
          "name"             => lang("Name"),
          "price"            => lang("rate_per_1000")."(".get_option("currency_symbol","").")",
          "min_max"          => lang("min__max_order"),
          "desc"             => lang("Description"),
        );
      } 
      $data = array(
        'services' => $top_bestsellers,
        'columns'  => $columns_best_seller
      );
      $this->load->view('top_bestsellers', $data);
    ?>
  </div>  
  
  <?php 
    if (get_role('admin')) {
  ?>
  <!-- Last 5 Newest Users -->
  <div class="col-md-12">
    <?php 
      $data = array(
        'users' => $last_5_users,
        'columns'  => array(
            "name"                   => lang("name"),
            "Email"                  => lang("Email"),
            "type"                   => lang("Type"),
            "balance"                => lang('Funds'),
            "last_ip_address"        => 'Last IP Address',
            "created"                => lang("Created"),
            "status"                 => lang('Status'),
          )
      );
      $this->load->view('last_5_users', $data);
    ?>
  </div>
  
  <!-- Last 5 order -->
  <div class="col-md-12">
    <?php 
      $data = array(
        'order_logs' => $last_5_orders,
        'columns'  => array(
            "order_id"            => lang("order_id"),
            "uid"                 => lang("User"),
            "name"                => lang("name"),
            "type"                => lang("Type"),
            "link"                => lang("Link"),
            "quantity"            => lang("Quantity"),
            "amount"              => lang("Amount"),
            "created"             => lang("Created"),
            "status"              => lang("Status"),
          )
      );
      $this->load->view('last_5_orders', $data);
    ?>
  </div>
  <?php } ?>

</div>



<script>
  $(document).ready(function(){

    Chart_template.chart_spline('#orders_chart_spline', <?=$data_orders_log->data_orders_chart_spline?>);
    Chart_template.chart_pie('#orders_chart_pie', <?=$data_orders_log->data_orders_chart_pie?>);

    Chart_template.chart_spline('#tickets_chart_spline', <?=$data_tickets_log->data_tickets_chart_spline?>);
    Chart_template.chart_pie('#tickets_chart_pie', <?=$data_tickets_log->data_tickets_chart_pie?>);
  });
</script>

