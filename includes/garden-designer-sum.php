<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

$products = $_POST['products'];
$product_ids = explode(',', $products);
array_pop($product_ids);

$total = 0;

foreach ($product_ids as $product_id) {
    list($id, $quantity) = explode(':', $product_id);

    $product = wc_get_product($id);

    if ($product->is_on_sale()) {
        $price = $product->get_sale_price();
    } else {
        $price = $product->get_regular_price();
    }

    $total += $price * $quantity;
}

echo $total.' €';
?>
