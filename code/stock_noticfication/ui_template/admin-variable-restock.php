<?php

global $wpdb;
$table_name = $wpdb->prefix . "notification_configuration";
$query = "SELECT * FROM $table_name";
$updated_data = $wpdb->get_results($query);

$configs_stock_quantity = $updated_data[0]->stock;

if (class_exists('WooCommerce')) { // Ensure WooCommerce is active
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => -1, 
        'post_status'    => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => 'variable', 
            ]
        ]
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) {
?>
        <div class="woocommerce-container mt-5">
            <h2 class="woocommerce-heading text-center mb-4">WooCommerce Variation Products</h2>
            <table class="woocommerce-table table myTable">
                <thead class="woocommerce-table-header">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Product ID</th>
                        <th scope="col">Product Name</th>
                        <th scope="col">Variation ID</th>
                        <th scope="col">Variation Name</th>
                        <th scope="col">Stock Quantity</th>
                        <th scope="col">Stock Managed?</th>
                        <th scope="col">Stock Status</th>
                        <th scope="col">Manage Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    while ($query->have_posts()) {
                        $query->the_post();
                        $product_id = get_the_ID();
                        $product = wc_get_product($product_id);

                        if ($product && $product->is_type('variable')) {
                            // Get variations
                            $variations = $product->get_children(); // Get all variation IDs

                            foreach ($variations as $variation_id) {
                                $variation = wc_get_product($variation_id); // Get variation product object

                                if ($variation) {
                                    $variation_name = $variation->get_name();
                                    $stock_quantity = $variation->get_stock_quantity();
                                    $manage_stock = $variation->get_manage_stock();
                                    $stock_status = $variation->get_stock_status();

                                    if ($manage_stock && $stock_quantity !== null && $stock_quantity <= $configs_stock_quantity) {
                    ?>
                                        <tr class="woocommerce-table-row" >
                                            <th scope="row"><?php echo esc_html($count++); ?></th>
                                            <td><?php echo esc_html($product_id); ?></td>
                                            <td><?php echo esc_html($product->get_name()); ?></td>
                                            <td><?php echo esc_html($variation_id); ?></td>
                                            <td><?php echo esc_html($variation_name); ?></td>
                                            <td><?php echo esc_html($stock_quantity); ?></td>
                                            <td><?php echo esc_html($manage_stock ? 'Yes' : 'No'); ?></td>
                                            <td><?php echo esc_html(ucfirst($stock_status)); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exampleModal" data-product-id="<?php echo esc_html($variation_id); ?>" data-product-name="<?php echo esc_html($variation_name); ?>" data-product-quantity="<?php echo esc_html($stock_quantity); ?>" onclick="hello(this)">
                                                    <i class="fa-solid fa-pen-to-square me-2"></i>
                                                    <span>Edit Variation</span>
                                                </button>
                                            </td>
                                        </tr>
                    <?php
                                    }
                                }
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Restock Variation</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="restockProductVariable" onsubmit="return validateForm(event)">
                            <?php wp_nonce_field('verifyStockVariable', 'token') ?>
                            <div class="mb-3">
                                <input type="hidden" id="productId" value="" name="product_id">
                                <label for="productName" class="form-label">Variation Name</label>
                                <input type="text" class="form-control" id="productName" value="" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" min="1" required name="stock_quantity">
                            </div>
                            <div id="error-message" style="display: none;" class="alert alert-danger mt-2">
                                Please enter a valid stock quantity (greater than 0).
                            </div>
                            <button type="submit" class="btn btn-primary">Restock Variation</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
<?php
        wp_reset_postdata();
    }
}
?>
