<?php
// Поврзување со Woocommerce API
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/post.php');
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/image.php');
//require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/media.php');

//var_dump($_POST);
$products = $_POST['products']; // Добивање на податоците од AJAX
//$products = "1575:3,1058:3,1079:7,";
$product_ids = explode(',', $products);
array_pop($product_ids); // Отстранување на последниот елемент од низата

$total = 0;

foreach ($product_ids as $product_id) {
    list($id, $quantity) = explode(':', $product_id); // Разделување на ИД и количина

//var_dump($product_ids);
//echo '<br>I kolichina<br>';
//var_dump($id, $quantity);

    $product = wc_get_product($id); // Добивање на објектот на производот

    if ($product->is_on_sale()) {
        $price = $product->get_sale_price(); // Ако производот е на попуст, земи ја цената на попустот
    } else {
        $price = $product->get_regular_price(); // Ако производот не е на попуст, земи ја редовната цена
    }

    $total += $price * $quantity; // Додади ја цената на вкупната сума, помножена со количината
}

echo $total.' €'; // Врати ја вкупната сума
?>
