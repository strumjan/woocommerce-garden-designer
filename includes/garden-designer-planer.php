<?php
// Поврзување со Woocommerce API
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/post.php');
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/image.php');
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/media.php');

// Празнење кошничка
global $woocommerce;
$woocommerce->cart->empty_cart();
$checkout_page_url = function_exists( 'wc_get_cart_url' ) ?
wc_get_checkout_url() : $woocommerce->cart->get_checkout_url();

//преземање на таговите од сочуваните во компонентата
$options = get_option('woocommerce_garden_designer_options');
$height_param = $options['height_tag'];
$width_param = $options['width_tag'];
$shade_param = $options['shade_tag'];
$part_shade_param = $options['part_shade_tag'];
$sun_param = $options['sun_tag'];


// Пресметка на површината која ја повикува Povrshina.php
$shapeType = isset($_POST['shapeType']) ? $_POST['shapeType'] : '';
$shineType = isset($_POST['shineType']) ? $_POST['shineType'] : ''; // Треба да се направи проверка и зависно која е одбрана таа да оди во преводот, оти вака дава хрватски
$totalWidth = isset($_POST['width']) ? $_POST['width'] : 0;
$totalLength = isset($_POST['length']) ? $_POST['length'] : 0;

if ($shineType === $sun_param) {
	$shineTypeText = __('Sun', 'woocommerce-garden-designer');
}
if ($shineType === $part_shade_param) {
	$shineTypeText = __('Partial shade', 'woocommerce-garden-designer');
}
if ($shineType === $shade_param) {
	$shineTypeText = __('Shade', 'woocommerce-garden-designer');
}

if ($shapeType === 'rectangle') {
    $area = $totalWidth * $totalLength / 10000;
	echo '<div id="pregledOdabira"><strong>' . __('Illumination: ', 'woocommerce-garden-designer') . $shineTypeText . '<br>'.__('Type of flower bed: against the wall', 'woocommerce-garden-designer').'<br>';
    echo __('Field surface: ', 'woocommerce-garden-designer') . round($area,2) . ' m<sup>2</sup></strong>';
} elseif ($shapeType === 'ellipse') {
    $area = ((M_PI * $totalWidth * $totalLength) / 4) / 10000; // Површина на елипсата (приближно)
	echo '<div id="pregledOdabira"><strong>' . __('Illumination: ', 'woocommerce-garden-designer') . $shineTypeText . '<br>' . __('Type of flower bed: central', 'woocommerce-garden-designer') . '<br>';
    echo __('Field surface: ', 'woocommerce-garden-designer') . round($area,2) . ' m<sup>2</sup></strong>';
} else {
    echo __('You have not selected the type of flower bed.', 'woocommerce-garden-designer');
}
// Крај на пресметка на површината
?>
		<div class="legenda"><b><?php _e('Legend: ', 'woocommerce-garden-designer'); ?></b><br>
		<div class="circle visoki" style="background-color: rgb(255,87,87);"></div><div class="legendaopis"><?php _e('&nbsp;Tall plants (90-150 cm)', 'woocommerce-garden-designer'); ?></div>
		<div class="circle srednji" style="background-color: rgb(101 153 101);"></div><div class="legendaopis"><?php _e('&nbsp;Medium plants (50-80 cm)', 'woocommerce-garden-designer'); ?></div>
		<div class="circle niski" style="background-color: rgb(198,165,142);"></div><div class="legendaopis"><?php _e('&nbsp;Low plants (15-40 cm)', 'woocommerce-garden-designer'); ?></div>
		</div>
<?php
// Функција за добивање на производите според таговите и залихата
function get_products_by_tags_and_stock($tags) {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_tag',
                'field'    => 'name',
                'terms'    => $tags,
                'operator' => 'IN',
            ),
        ),
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock',
            ),
        ),
    );

    $query = new WP_Query($args);

    return $query->get_posts();
}

// Функција за групирање на производите според висина
function group_products_by_height($products) {
    $high_array = $medium_array = $low_array = array();
    global $shineType;

    foreach ($products as $product) {
        $tags = wp_get_post_terms($product->ID, 'product_tag', array('fields' => 'names'));

        // Проверка за висина и други тагови
        $height_tag = '';
        $shineTypeExists = false;
        foreach ($tags as $tag) {
            if ($tag == $shineType) {
                $shineTypeExists = true;
            }
            // Користење preg_quote за да се осигураме дека специјалните карактери се правилно избегнати
            if (preg_match('/' . preg_quote($height_param, '') . '\s*(\d+) cm/', $tag, $matches) && $shineTypeExists) {
                $height_tag = $height_param . ' ' . intval($matches[1]) . ' cm'; // Нормализација на форматот со додавање на празно место
                break;
            }
        }

        // Групирање според висина
        if ($height_tag) {
            if (preg_match('/' . preg_quote($height_param, '/') . '\s*(\d+) cm/', $height_tag, $matches)) {
                $height = intval($matches[1]);
                if ($height >= 90 && $height <= 150) {
                    $high_array[] = $product;
                } elseif ($height >= 50 && $height <= 80) {
                    $medium_array[] = $product;
                } elseif ($height >= 15 && $height <= 40) {
                    $low_array[] = $product;
                }
            }
        }
    }

    return array(
        'visoki' => $high_array,
        'srednji' => $medium_array,
        'niski' => $low_array,
    );
}


// Главна логика
$tags = array($height_param, $width_param, $shade_param, $part_shade_param, $sun_param); // Одредете ги потребните тагови
$products = get_products_by_tags_and_stock($tags);
$grouped_products = group_products_by_height($products);

// Пример за пристап до групирани производи
$high_products = $grouped_products['visoki'];
$medium_products = $grouped_products['srednji'];
$low_products = $grouped_products['niski'];


// Функција за генерирање чекбокси
function generateCheckboxes($name, $products) {
    $html = '';

foreach ($products as $product) {
	global $totalWidth, $totalLength;
    $tags = wp_get_post_terms($product->ID, 'product_tag', array('fields' => 'names'));
	
	$productImages = wp_get_post_terms($product->ID, 'product_image');//var_dump($productImages);
	$product_id = $product->ID;
	$image_id = get_post_thumbnail_id($product_id);
	$image_info = wp_get_attachment_image_src($image_id, 'full');
	$image_url = $image_info[0];
	$imageKomplet = '<img loading="lazy" decoding="async" width="100" height="100" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" src="'.$image_url.'">';

    // Проверуваме дали има тагови и дали има таг со почеток 'Š:'
    $width_tag = '';
    foreach ($tags as $tag) {
			if (preg_match('/Š:\s*(\d+) cm/', $tag, $matches)) {
				$width_tag = 'Š: ' . intval($matches[1]) . ' cm'; // Нормализација на форматот со додавање на празно место
				// Дефинирање на вредноста на тагот
				$tagValue = intval($matches[1]);

				// Пресметување колку пати се содржи во $width
				$widthCount = intval($totalWidth / $tagValue);

				// Пресметување колку пати се содржи во $length
				$lengthCount = intval($totalLength / $tagValue);

				break;
			}
		}

    // Додадете го насловот и делот од тагот со 'Š:' како вредност на чекбокс
    $checkboxValue = htmlspecialchars($product->post_title);
    $proizvodot = wc_get_product($product_id);
		if( $proizvodot->get_manage_stock() ) {
		$kolichina = '<span class="stanje">' . __('In stock: ', 'woocommerce-garden-designer') . $proizvodot->get_stock_quantity() . '</span>';
		$kolichinaMax = $proizvodot->get_stock_quantity();
		} else {
			$stock_status = $proizvodot->get_stock_status();
			if( 'instock' === $stock_status ) {
				$kolichina = '<span class="stanje">' . __('We have a lot', 'woocommerce-garden-designer') . '</span>';
				$kolichinaMax = 100;
			}
			if( 'outofstock' === $stock_status ) {
				$kolichina = '<span class="stanje">' . __('We don\'t currently have any', 'woocommerce-garden-designer') . '</span>';	
				$kolichinaMax = 0;
			}
			// there is also "onbackorder" value can be returned
		}
    $opisot = $proizvodot->get_short_description();
    $html .= '<div class="proizvodi" data-title="' .  strip_tags($opisot) . '"><input type="checkbox" name="' . $name . '[]" onchange="updateCountsCheckbox(event)" value="' . $product->ID . '" title="' . $checkboxValue . '">' . $imageKomplet . '<p>' .$checkboxValue . '</p>' . $kolichina . ' <span> ' . __('quantity:', 'woocommerce-garden-designer') . '</span>';
	$html .= '<input type="number" id="' . $product->ID .'" name="quantity' . $name . '[]" min="1" max="' . $kolichinaMax .'" data-old-quantity="0" oninput="updateCountsNumber(event)" value=""></div>';
}

// Сега $html содржи чекбокс елементи со наслов и дел од тагот со 'Š:'
return $html;

}

$lengthGusto = $totalLength; //Дефинирање на почетна должина за густо да не ја избрише нормално
$widthGusto = $totalWidth; //Дефинирање на почетна ширина за густо да не ја избрише нормално

// Матрица за нормален распоред покрај ѕид
function createMatrix($totalWidth, $totalLength) {
    $visoki = ['dim' => 60, 'val' => 'visoki'];
    $sredni = ['dim' => 50, 'val' => 'srednji'];
    $niski = ['dim' => 50, 'val' => 'niski'];

    $matrix = [];

    $length = 0;
    while ($length + $visoki['dim'] <= $totalLength) {
        $width = 0;
        $row = [];
        if ($totalLength < 320) {
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $visoki['val'];
                $width += $visoki['dim'];
            }
            $matrix[] = $row;
            $length += $visoki['dim'];

            $width = 30;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $sredni['val'];
                $width += $visoki['dim'];
            }
            $matrix[] = $row;
            $length += $sredni['dim'];
        } else {
            for ($i = 0; $i < 2 && $length < $totalLength; $i++) {
				if ($i === 0) {
                $width = 0;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $visoki['val'];
                    $width += $visoki['dim'];
                }
                $matrix[] = $row;
                $length += $visoki['dim'];
				} else {
				$width = 60;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $visoki['val'];
                    $width += $visoki['dim'];
                }
                $matrix[] = $row;
                $length += $visoki['dim'];
				}
            }

            for ($i = 0; $i < 2 && $length < $totalLength; $i++) {
                if ($i === 0) {
				$width = 0;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $sredni['val'];
                    $width += $visoki['dim'];
                }
                $matrix[] = $row;
                $length += $sredni['dim'];
				} else {
				$width = 60;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $sredni['val'];
                    $width += $visoki['dim'];
                }
                $matrix[] = $row;
                $length += $sredni['dim'];
				}
            }
        }
			$nepar = 1;
        while ($length + $niski['dim'] <= $totalLength) {
			if ($nepar % 2 !== 0) {
			$width = 0;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $niski['val'];
                $width += $niski['dim'];
            }
            $matrix[] = $row;
            $length += $niski['dim'];
			$nepar++;
			} else {
			$width = 60;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $niski['val'];
                $width += $niski['dim'];
            }
            $matrix[] = $row;
            $length += $niski['dim'];
			$nepar++;
			}
        }
    }

    return $matrix;
}
$matrixNormalnoZid = createMatrix($totalWidth, $totalLength);
// Крај на Матрица за нормален распоред покрај ѕид

// Матрица за густ распоред покрај ѕид
function createMatrixGusto($totalWidth, $totalLength) {
    $visoki = ['dim' => 40, 'val' => 'visoki'];
    $sredni = ['dim' => 30, 'val' => 'srednji'];
    $niski = ['dim' => 30, 'val' => 'niski'];

    $matrix = [];

    $length = 0;
    while ($length + $visoki['dim'] <= $totalLength) {
        $width = 0;
        $row = [];
        if ($totalLength < 320) {
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $visoki['val'];
                $width += $visoki['dim'];
            }
            $matrix[] = $row;
            $length += $visoki['dim'];

            $width = 20;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $sredni['val'];
                $width += $sredni['dim'];
            }
            $matrix[] = $row;
            $length += $sredni['dim'];
        } else {
            for ($i = 0; $i < 2 && $length < $totalLength; $i++) {
				if ($i === 0) {
                $width = 0;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $visoki['val'];
                    $width += $visoki['dim'];
                }
                $matrix[] = $row;
                $length += $visoki['dim'];
				} else {
				$width = 40;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $visoki['val'];
                    $width += $visoki['dim'];
                }
                $matrix[] = $row;
                $length += $visoki['dim'];
				}
            }

            for ($i = 0; $i < 2 && $length < $totalLength; $i++) {
                if ($i === 0) {
				$width = 0;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $sredni['val'];
                    $width += $sredni['dim'];
                }
                $matrix[] = $row;
                $length += $sredni['dim'];
				} else {
				$width = 40;
                $row = [];
                while ($width + $visoki['dim'] <= $totalWidth) {
                    $row[] = $sredni['val'];
                    $width += $sredni['dim'];
                }
                $matrix[] = $row;
                $length += $sredni['dim'];
				}
            }
        }
			$nepar = 1;
        while ($length + $niski['dim'] <= $totalLength) {
			if ($nepar % 2 !== 0) {
			$width = 0;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $niski['val'];
                $width += $niski['dim'];
            }
            $matrix[] = $row;
            $length += $niski['dim'];
			$nepar++;
			} else {
			$width = 40;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                $row[] = $niski['val'];
                $width += $niski['dim'];
            }
            $matrix[] = $row;
            $length += $niski['dim'];
			$nepar++;
			}
        }
    }

    return $matrix;
}
$matrixNormalnoZidGusto = createMatrixGusto($totalWidth, $totalLength);
// Крај на Матрица за густ распоред покрај ѕид

// Матрица за нормален распоред централно
function createMatrixCentralno($totalWidth, $totalLength) {
    $visoki = ['dim' => 60, 'val' => 'visoki'];
    $sredni = ['dim' => 50, 'val' => 'srednji'];
    $niski = ['dim' => 50, 'val' => 'niski'];

    $matrix = [];

    $length = 0;
    while ($length < $totalLength) {
        $width = 0;
        $row = [];
        if ($length < 270) {
			if ($totalWidth < 320) {
                $numVisoki = 1;
                $numSredni = 1;
            } else {
                $numVisoki = 3;
                $numSredni = 2;
            }
            $numNiski = ($totalWidth - $numVisoki * $visoki['dim'] - $numSredni * $sredni['dim']) / $niski['dim'];
			$numNiski2 = ($totalWidth - $numSredni * $sredni['dim']) / $niski['dim'];
            while ($width + $sredni['dim'] <= $totalWidth) {
                $row = array_merge(
                    array_fill(0, $numNiski2 / 2, $niski['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numNiski2 / 2, $niski['val'])
                );
                $width += $sredni['dim'];
            }
            array_unshift($matrix, $row);
            $matrix[] = $row;
            $length += 2 * $sredni['dim'];

            $width = 0;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                 $row = array_merge(
                    array_fill(0, $numNiski / 2, $niski['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numVisoki, $visoki['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numNiski / 2, $niski['val'])
                );
                $width += $visoki['dim'];
            }
            array_splice($matrix, count($matrix) / 2, 0, [$row]);
            $length += $visoki['dim'];
        }

        while ($length < $totalLength) {
            $width = 0;
            $row = [];
            while ($width + $niski['dim'] <= $totalWidth) {
                $row[] = $niski['val'];
                $width += $niski['dim'];
            }
            array_unshift($matrix, $row);
            $matrix[] = $row;
            $length += 2 * $niski['dim'];
        }
    }

    return $matrix;
}

$matrixCentralno = createMatrixCentralno($totalWidth, $totalLength);
// Крај на Матрица за нормален распоред централно

// Матрица за густ распоред централно
function createMatrixCentralnoGusto($totalWidth, $totalLength) {
    $visoki = ['dim' => 40, 'val' => 'visoki'];
    $sredni = ['dim' => 30, 'val' => 'srednji'];
    $niski = ['dim' => 30, 'val' => 'niski'];

    $matrix = [];

    $length = 0;
    while ($length < $totalLength) {
        $width = 0;
        $row = [];
        if ($length < 270) {
			if ($totalWidth < 200) {
                $numVisoki = 1;
                $numSredni = 2;
            } else {
                $numVisoki = 3;
                $numSredni = 3;
            }
            $numNiski = ($totalWidth - $numVisoki * $visoki['dim'] - $numSredni * $sredni['dim']) / $niski['dim'];
			$numNiski2 = ($totalWidth - $numSredni * $sredni['dim']) / $niski['dim'];
            while ($width + $sredni['dim'] <= $totalWidth) {
                $row = array_merge(
                    array_fill(0, $numNiski2 / 2, $niski['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numNiski2 / 2, $niski['val'])
                );
                $width += $sredni['dim'];
            }
            array_unshift($matrix, $row);
            $matrix[] = $row;
            $length += 2 * $sredni['dim'];

            $width = 0;
            $row = [];
            while ($width + $visoki['dim'] <= $totalWidth) {
                 $row = array_merge(
                    array_fill(0, $numNiski / 2, $niski['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numVisoki, $visoki['val']),
                    array_fill(0, $numSredni, $sredni['val']),
                    array_fill(0, $numNiski / 2, $niski['val'])
                );
                $width += $visoki['dim'];
            }
            array_splice($matrix, count($matrix) / 2, 0, [$row]);
            $length += $visoki['dim'];
        }

        while ($length < $totalLength) {
            $width = 0;
            $row = [];
            while ($width + $niski['dim'] <= $totalWidth) {
                $row[] = $niski['val'];
                $width += $niski['dim'];
            }
            array_unshift($matrix, $row);
            $matrix[] = $row;
            $length += 2 * $niski['dim'];
        }
    }

    return $matrix;
}

$matrixCentralnoGusto = createMatrixCentralnoGusto($totalWidth, $totalLength);
// Крај на Матрица за густ распоред централно

// Пребројување на елементи во Матрица
function countElementsAndRows($matrixNZ) {
    $elementCounts = array_count_values(array_merge(...$matrixNZ));
    $rowCounts = array_map('count', $matrixNZ);

    $totalRowCounts = [
        'visoki' => 0,
        'srednji' => 0,
        'niski' => 0,
    ];
    foreach ($matrixNZ as $row) {
        if (in_array('visoki', $row)) {
            $totalRowCounts['visoki']++;
        }
        if (in_array('srednji', $row)) {
            $totalRowCounts['srednji']++;
        }
        if (in_array('niski', $row)) {
            $totalRowCounts['niski']++;
        }
    }
    return [
        'visoki' => $elementCounts['visoki'] ?? 0,
        'srednji' => $elementCounts['srednji'] ?? 0,
        'niski' => $elementCounts['niski'] ?? 0,
        'totalRowCounts' => $totalRowCounts,
    ];
}
$counts = countElementsAndRows($matrixNormalnoZid);
$countsGusto = countElementsAndRows($matrixNormalnoZidGusto);
$countsCentralno = countElementsAndRows($matrixCentralno);
$countsCentralnoGusto = countElementsAndRows($matrixCentralnoGusto);
// Крај на пребројување на елементи во Матрица

// Печатење на матрицата
	//Нормално до ѕид
if ($shapeType === 'rectangle') {
echo '<br /><h4>' .__('First, choose whether you want a standard or dense arrangement of plants. And in the following steps you will choose tall, medium and short plants for your flower bed.', 'woocommerce-garden-designer') . '</h4><br />';
echo '<p><strong>' . __('Against the wall, standard layout', 'woocommerce-garden-designer') . '</strong><br>' . __('(Planting distance: high 60 cm, medium 50 cm, low 50 cm)', 'woocommerce-garden-designer') . '</p>';
echo "<div class='containerkrug'><p class='zid'>" . __('wall', 'woocommerce-garden-designer') . "</p>";
foreach ($matrixNormalnoZid as $elementGroup) {
	echo "<div class='rowkrug'>";
    foreach ($elementGroup as $element) {
        $color = '';
		$class = '';
        switch ($element) {
            case 'visoki':
                $color = 'rgb(255,87,87)';
				$class = 'visoki';
                break;
            case 'srednji':
                $color = 'rgb(101 153 101)';
				$class = 'srednji';
                break;
            case 'niski':
                $color = 'rgb(198,165,142)';
				$class = 'niski';
                break;
        }

        echo "<div class='circle ".$class."' style='background-color: $color;'></div>";
    }
    echo "</div>"; // Затворање на редот
}
echo "</div>"; // Затворање на контенерот

echo '<button class="rotiranje" type="button" onclick="drawShapeRotate()">' . __('Rotate', 'woocommerce-garden-designer') . '</button>';
echo '<button id="daljebiljkeStandardnoZid" type="button" onclick="showStep(\'visokeBiljkeStandardno\');hideStep(\'pregledOdabira\');odabir(\'Standardno\');">' . __('Select <strong>standard layout</strong> and proceed to select tall plants', 'woocommerce-garden-designer') . '</button>';

	// Густо до ѕид
echo '<p><strong>' . __('Along the wall, a dense layout', 'woocommerce-garden-designer') . '</strong><br>' . __('(Planting distance: high 40 cm, medium 30 cm, low 30 cm)', 'woocommerce-garden-designer') . '</p>';
echo "<div class='containerkrug'><p class='zid'>" . __('wall', 'woocommerce-garden-designer') . "</p>";
foreach ($matrixNormalnoZidGusto as $elementGroup) {
	echo "<div class='rowkrug'>";
    foreach ($elementGroup as $element) {
        $color = '';
		$class = '';
        switch ($element) {
            case 'visoki':
                $color = 'rgb(255,87,87)';
				$class = 'visoki';
                break;
            case 'srednji':
                $color = 'rgb(101 153 101)';
				$class = 'srednji';
                break;
            case 'niski':
                $color = 'rgb(198,165,142)';
				$class = 'niski';
                break;
        }

        echo "<div class='circle ".$class."' style='background-color: $color;'></div>";
    }
   echo "</div>"; // Затворање на редот
}
echo "</div>"; // Затворање на контенерот

echo '<button class="rotiranje" type="button" onclick="drawShapeRotate()">' . __('Rotate', 'woocommerce-garden-designer') . '</button>';
echo '<button id="daljebiljkeGustoZid" type="button" onclick="showStep(\'visokeBiljkeGusto\');hideStep(\'pregledOdabira\');odabir(\'Gusto\');">' . __('Choose a <strong>dense arrangement</strong> and proceed to select tall plants', 'woocommerce-garden-designer') . '</button></div>';
}
	// Нормално централно
if ($shapeType === 'ellipse') {
echo '<br /><h4>' . __('First, choose whether you want a standard or dense arrangement of plants. And in the following steps you will choose tall, medium and short plants for your flower bed.', 'woocommerce-garden-designer') . '</h4><br />';
echo '<p><strong>' . __('Central, standard layout', 'woocommerce-garden-designer') . '</strong><br>' . __('(Planting distance: high 60 cm, medium 50 cm, low 50 cm)', 'woocommerce-garden-designer') . '</p>';
echo "<div class='containerkrug'>";
foreach ($matrixCentralno as $elementGroup) {
	echo "<div class='rowkrug'>";
    foreach ($elementGroup as $element) {
        $color = '';
		$class = '';
        switch ($element) {
            case 'visoki':
                $color = 'rgb(255,87,87)';
				$class = 'visoki';
                break;
            case 'srednji':
                $color = 'rgb(101 153 101)';
				$class = 'srednji';
                break;
            case 'niski':
                $color = 'rgb(198,165,142)';
				$class = 'niski';
                break;
        }

        echo "<div class='circle ".$class."' style='background-color: $color;'></div>";
    }
    echo "</div>"; // Затворање на редот
}
echo "</div>"; // Затворање на контенерот

echo '<button class="rotiranje" type="button" onclick="drawShapeRotate()">' . __('Rotate', 'woocommerce-garden-designer') . '</button>';
echo '<button id="daljebiljkeStandardnoCentralno" type="button" onclick="showStep(\'visokeBiljkeStandardno\');hideStep(\'pregledOdabira\');odabir(\'Standardno\');">' . __('Select <strong>standard layout</strong> and proceed to select tall plants', 'woocommerce-garden-designer') . '</button>';

	// Густо централно
echo '<p><strong>' . __('Central, dense layout', 'woocommerce-garden-designer') . '</strong><br>' . __('(Planting distance: high 40 cm, medium 30 cm, low 30 cm)', 'woocommerce-garden-designer') . '</p>';
echo "<div class='containerkrug'>";
foreach ($matrixCentralnoGusto as $elementGroup) {
	echo "<div class='rowkrug'>";
    foreach ($elementGroup as $element) {
        $color = '';
		$class = '';
        switch ($element) {
            case 'visoki':
                $color = 'rgb(255,87,87)';
				$class = 'visoki';
                break;
            case 'srednji':
                $color = 'rgb(101 153 101)';
				$class = 'srednji';
                break;
            case 'niski':
                $color = 'rgb(198,165,142)';
				$class = 'niski';
                break;
        }

        echo "<div class='circle ".$class."' style='background-color: $color;'></div>";
    }
    echo "</div>"; // Затворање на редот
}
echo "</div>"; // Затворање на контенерот

echo '<button class="rotiranje" type="button" onclick="drawShapeRotate()">' . __('Rotate', 'woocommerce-garden-designer') . '</button>';
echo '<button id="daljebiljkeGustoCentralno" type="button" onclick="showStep(\'visokeBiljkeGusto\');hideStep(\'pregledOdabira\');odabir(\'Gusto\');">' . __('Choose a <strong>dense arrangement</strong> and proceed to select tall plants', 'woocommerce-garden-designer') . '</button></div>';
// Крај на Печатење на матрицата
}

?>

    <form id="formaStandardno" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

	<div id="visokeBiljkeStandardno" style="display: none;">
		<h3><?php _e('Tall plants:', 'woocommerce-garden-designer'); ?></h3>
		<?php
		if ($shapeType === 'rectangle') {
		echo '<div class="visokiNormalnoBrojach"><div class="brojachOpis"><p>' . __('<strong>Standard arrangement along the wall</strong>, required number of <strong>tall</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $counts['visoki'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-visoki" class="visoki brojach" data-recommended="' . $counts['visoki'] . '">0</span></div>';
		} else {
		echo '<div class="visokiCentralnoBrojach"><div class="brojachOpis"><p>' . __('<strong>Standard layout central</strong>, required number of <strong>tall</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' .  $countsCentralno['visoki'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-visoki" class="visoki brojach" data-recommended="' . $countsCentralno['visoki'] . '">0</span></div>';
		}
		echo generateCheckboxes('visoki', $high_products); ?>
		<br />
	    <button id="daljevisokiStandardno" type="button" onclick="showStep('srednjeBiljkeStandardno')" disabled><?php _e('Please select tall plants first', 'woocommerce-garden-designer'); ?></button>
		<br /><button id="gumbObrisiV" type="button" onclick="clearForm('visoki');"><?php _e('Clear selection', 'woocommerce-garden-designer'); ?></button>
	</div>

	<div id="srednjeBiljkeStandardno" style="display: none;">
		<h3><?php _e('Medium plants:', 'woocommerce-garden-designer'); ?></h3>
		<?php
		if ($shapeType === 'rectangle') {
		echo '<div class="sredniNormalnoBrojach"><div class="brojachOpis"><p>' . __('<strong>Standard arrangement along the wall</strong>, required number of <strong>medium</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $counts['srednji'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-sredni" class="sredni brojach" data-recommended="' . $counts['srednji'] . '">0</span></div>';
		} else {
		echo '<div class="sredniCentralnoBrojach"><div class="brojachOpis"><p>' . __('<strong>Standard layout central</strong>, required number of <strong>middle</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsCentralno['srednji'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-sredni" class="sredni brojach" data-recommended="' . $countsCentralno['srednji'] . '">0</span></div>';
		}
		echo generateCheckboxes('sredni', $medium_products); ?>
		<br />
		<button id="daljesredniStandardno" type="button" onclick="showStep('niskeBiljkeStandardno')" disabled><?php _e('Please select medium plants first', 'woocommerce-garden-designer'); ?></button>
		<br /><button id="gumbObrisiS" type="button" onclick="clearForm('sredni');"><?php _e('Clear selection', 'woocommerce-garden-designer'); ?></button>
	</div>

	<div id="niskeBiljkeStandardno" style="display: none;">
		<h3><?php _e('Low plants:', 'woocommerce-garden-designer'); ?></h3>
		<?php
		if ($shapeType === 'rectangle') {
		echo '<div class="niskiNormalnoBrojach"><div class="brojachOpis"><p>' . __('<strong>Standard arrangement along the wall</strong>, required number of <strong>low</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $counts['niski'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-niski" class="niski brojach" data-recommended="' . $counts['niski'] . '">0</span></div>';
		} else {
		echo '<div class="niskiCentralnoBrojach"><div class="brojachOpis"><p>' . __('<strong>Standard layout central</strong>, the required number of <strong>low</strong> plants is: ', 'woocommerce-garden-designer') . '<strong>' . $countsCentralno['niski'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-niski" class="niski brojach" data-recommended="' . $countsCentralno['niski'] . '">0</span></div>';
		}
		echo generateCheckboxes('niski', $low_products); ?>
		<br />
		<button id="daljeniskiStandardno" type="button" onclick="showSelectedProducts();showStep('listaKupovine')" disabled><?php _e('Please select short plants first', 'woocommerce-garden-designer'); ?></button>
		<br /><button id="gumbObrisiN" type="button" onclick="clearForm('niski');"><?php _e('Clear selection', 'woocommerce-garden-designer'); ?></button>
	</div>
    </form>
	
	<form id="formaGusto" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

	<div id="visokeBiljkeGusto" style="display: none;">
		<h3><?php _e('Tall plants:', 'woocommerce-garden-designer'); ?></h3>
		<?php
		if ($shapeType === 'rectangle') {
		echo '<div class="visokiNormalnoGustoBrojach"><div class="brojachOpis"><p>' .__('<strong>Dense arrangement along the wall</strong>, required number of <strong>tall</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsGusto['visoki'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-visoki-gusto" class="visoki brojach" data-recommended="' . $countsGusto['visoki'] . '">0</span></div>';
		} else {
		echo '<div class="visokiCentralnoGustoBrojach"><div class="brojachOpis"><p>' . __('<strong>Dense arrangement centrally</strong>, required number of <strong>tall</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsCentralnoGusto['visoki'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-visoki-gusto" class="visoki brojach" data-recommended="' . $countsCentralnoGusto['visoki'] . '">0</span></div>';
		}
		echo generateCheckboxes('visoki', $high_products); ?>
		<br />
	    <button id="daljevisokiGusto" type="button" onclick="showStep('srednjeBiljkeGusto')" disabled><?php _e('Please select tall plants first', 'woocommerce-garden-designer'); ?></button>
		<br /><button id="gumbObrisiV" type="button" onclick="clearForm('visoki');"><?php _e('Clear selection', 'woocommerce-garden-designer'); ?></button>
	</div>

	<div id="srednjeBiljkeGusto" style="display: none;">
		<h3><?php _e('Medium plants:', 'woocommerce-garden-designer'); ?></h3>
		<?php
		if ($shapeType === 'rectangle') {
		echo '<div class="sredniNormalnoGustoBrojach"><div class="brojachOpis"><p>' . __('<strong>Dense arrangement along the wall</strong>, required number of <strong>medium</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsGusto['srednji'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-sredni-gusto" class="sredni brojach" data-recommended="' . $countsGusto['srednji'] . '">0</span></div>';
		} else {
		echo '<div class="sredniCentralnoGustoBrojach"><div class="brojachOpis"><p>' . __('<strong>Dense central arrangement</strong>, required number of <strong>middle</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsCentralnoGusto['srednji'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-sredni-gusto" class="sredni brojach" data-recommended="' . $countsCentralnoGusto['srednji'] . '">0</span></div>';
		}
		echo generateCheckboxes('sredni', $medium_products); ?>
		<br />
		<button id="daljesredniGusto" type="button" onclick="showStep('niskeBiljkeGusto')" disabled><?php _e('Please select medium plants first', 'woocommerce-garden-designer'); ?></button>
		<br /><button id="gumbObrisiS" type="button" onclick="clearForm('sredni');"><?php _e('Clear selection', 'woocommerce-garden-designer'); ?></button>
	</div>

	<div id="niskeBiljkeGusto" style="display: none;">
		<h3><?php _e('Low plants:', 'woocommerce-garden-designer'); ?></h3>
		<?php
		if ($shapeType === 'rectangle') {
		echo '<div class="niskiNormalnoGustoBrojach"><div class="brojachOpis"><p>' . __('<strong>Dense arrangement along the wall</strong>, required number of <strong>low</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsGusto['niski'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-niski-gusto" class="niski brojach" data-recommended="' . $countsGusto['niski'] . '">0</span></div>';
		} else {
		echo '<div class="niskiCentralnoGustoBrojach"><div class="brojachOpis"><p>' . __('<strong>Dense arrangement centrally</strong>, required number of <strong>low</strong> plants: ', 'woocommerce-garden-designer') . '<strong>' . $countsCentralnoGusto['niski'] . '</strong>. ' . __('Selected:', 'woocommerce-garden-designer') . '</p></div><span id="counter-niski-gusto" class="niski brojach" data-recommended="' . $countsCentralnoGusto['niski'] . '">0</span></div>';
		}
		echo generateCheckboxes('niski', $low_products); ?>
		<br />
		<button id="daljeniskiGusto" type="button" onclick="showSelectedProducts();showStep('listaKupovine')" disabled><?php _e('Please select short plants first', 'woocommerce-garden-designer'); ?></button>
		<br /><button id="gumbObrisiN" type="button" onclick="clearForm('niski');"><?php _e('Clear selection', 'woocommerce-garden-designer'); ?></button>
	</div>
    </form>

	<div id="listaKupovine" style="display: none;">
    <h3><?php _e('Shopping List:', 'woocommerce-garden-designer'); ?></h3>

	<p><?php _e('Tall plants:', 'woocommerce-garden-designer'); ?></p>
	<ul id="visoki_izbrani_proizvodi"></ul>

	<p><?php _e('Medium plants:', 'woocommerce-garden-designer'); ?></p>
	<ul id="sredni_izbrani_proizvodi"></ul>

	<p><?php _e('Low plants:', 'woocommerce-garden-designer'); ?></p>
	<ul id="niski_izbrani_proizvodi"></ul>
	
	<button id="naplati" type="button" onclick="kaNaplati();"><?php _e('Add all to cart', 'woocommerce-garden-designer'); ?></button>
	</div>

