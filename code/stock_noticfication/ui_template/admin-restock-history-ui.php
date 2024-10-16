<?php

global $wpdb;

$table_name = $wpdb->prefix . "updated_stocks";


$stock_updated_history = $wpdb->get_results("
    SELECT 
        t.product_id,
        t.product_name,
        t.stock,
        t.timeStamp,
        counts.product_count
    FROM 
        $table_name AS t
    INNER JOIN (
        SELECT 
            product_id, 
            COUNT(*) AS product_count
        FROM 
            $table_name 
        GROUP BY 
            product_id
    ) AS counts ON t.product_id = counts.product_id
    WHERE 
        t.timeStamp = (
            SELECT MAX(timeStamp) 
            FROM $table_name 
            WHERE product_id = t.product_id
        )
    ORDER BY 
        t.timeStamp DESC
");




// $stock_updated_history = $wpdb->get_results("
//     SELECT 
//         t.product_id,
//         t.product_name,
//         t.stock,
//         t.timeStamp,
//         counts.product_count
//     FROM 
//         $table_name AS t
//     INNER JOIN (
//         SELECT 
//             product_id, 
//             COUNT(*) AS product_count
//         FROM 
//             $table_name 
//         GROUP BY 
//             product_id
//     ) AS counts ON t.product_id = counts.product_id
//     ORDER BY 
//         t.timeStamp DESC
// ");



$serialNumber = 1;

if (count($stock_updated_history) == 0) {
?>
  <div class="custom-container mt-5">
    <div class="custom-alert alert-warning" role="alert">
      No restock history available at the moment, but we will update you as soon as more information becomes available.
    </div>
  </div>
<?php
} else {
?>
  <div class="custom-container mt-5">
    <h2 class="custom-heading text-center mb-4">Restock History</h2>

    <table class="custom-table table myTable">
      <thead class="custom-table-header">
        <tr>
          <th scope="col">#</th>
          <th scope="col">Product Id</th>
          <th scope="col">Product Name</th>
          <th scope="col">Times Manages</th>
          <th scope="col">Stock</th>
          <th scope="col">Time</th>
          <th scope="col">preview</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($stock_updated_history as $stock) {
        ?>
          <tr class="custom-table-row">
            <th scope="row"><?php echo esc_html($serialNumber++) ?></th>
            <td><?php echo esc_html($stock->product_id) ?></td>
            <td><?php echo esc_html($stock->product_name) ?></td>
            <td><?php echo esc_html($stock->product_count) ?></td>
            <td><?php echo esc_html($stock->stock) ?></td>
            <td><?php echo esc_html((new DateTime($stock->timeStamp))->format('l, F j, Y \a\t g:i A')); ?></td>
            <td> <button type="button" class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="productHistory(this)" data-productId="<?php echo esc_html($stock->product_id) ?>" data-productName="<?php echo esc_html($stock->product_name) ?>" data-stock="<?php echo esc_html($stock->stock) ?>" data-timeChanges="<?php echo esc_html($stock->product_count) ?>" data-timeStamps="<?php echo esc_html((new DateTime($stock->timeStamp))->format('l, F j, Y \a\t g:i A')); ?>">
                <i class="fa-solid fa-eye"></i>
              </button></td>
          </tr>
        <?php
        }
        ?>
      </tbody>
    </table>
  </div>
<?php
}
?>


<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Product History</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card" style="margin: 0px;">
          <ul class="list-group list-group-flush">
            <li class="list-group-item" id="product_id"></li>
            <li class="list-group-item" id="product_name"></li>
            <li class="list-group-item" id="times_manage"></li>
            <li class="list-group-item" id="now_stock"></li>
            <li class="list-group-item" id="last_update_time"></li>
          </ul>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


