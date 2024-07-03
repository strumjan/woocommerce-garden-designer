<?php
$gardenDesignerStyle = plugins_url( 'garden-designer-style.css', __FILE__ );
wp_enqueue_style( 'garden-designer-style', $gardenDesignerStyle );

//преземање на таговите од сочуваните во компонентата
$options = get_option('woocommerce_garden_designer_options');
$height_param = $options['height_tag'];
$width_param = $options['width_tag'];
$shade_param = $options['shade_tag'];
$part_shade_param = $options['part_shade_tag'];
$sun_param = $options['sun_tag'];
?>
<div class="container">
	<form id="shapeForm">
	<div class="osvetljenost">
		<h3><?php _e('1. Select the brightness', 'woocommerce-garden-designer'); ?></h3>
		<label>
			<input type="radio" name="shineType" value="<?php echo $sun_param; ?>"><?php _e('Sun (6 or more hours of sunlight)', 'woocommerce-garden-designer'); ?>
		</label>
		<br />
		<label>
			<input type="radio" name="shineType" value="<?php echo $part_shade_param; ?>"><?php _e('Partial shade (4-6 hours of sunlight)', 'woocommerce-garden-designer'); ?>
		</label>
		<br />
		<label>
			<input type="radio" name="shineType" value="<?php echo $shade_param; ?>"><?php _e('Shade (3 or less hours of sunlight)', 'woocommerce-garden-designer'); ?>
		</label>
	</div>
	<div class="gredica">
		<h3><?php _e('2. Choose the type of flower bed', 'woocommerce-garden-designer'); ?></h3>
		<div class="gredicaLabel">
		<label>
			<input type="radio" name="shapeType" value="rectangle" onchange="updateInformativeShape()"><?php _e('Against the wall', 'woocommerce-garden-designer'); ?>
		</label>
		<br />
		<label>
			<input type="radio" name="shapeType" value="ellipse" onchange="updateInformativeShape()"><?php _e('Central', 'woocommerce-garden-designer'); ?>
		</label><br />
		</div>
		<canvas id="informativeCanvas" width="200" height="110"></canvas>
	</div>
	<div class="dimensionsInput">
		<h3><?php _e('3. Enter the dimensions of the bed', 'woocommerce-garden-designer'); ?></h3>
		<label><?php _e('Length (in cm.):', 'woocommerce-garden-designer'); ?><input type="number" id="lengthInput" required>
		</label>
		<br />
		<label><?php _e('Width (in cm.):', 'woocommerce-garden-designer'); ?><input type="number" id="widthInput" required>
		</label>
	</div>
	<button id="gumbUnesi" type="button" onclick="startDrawing()"><?php _e('Confirm selection', 'woocommerce-garden-designer'); ?></button>
	</form>
	<canvas id="canvas" width="30" height="20"></canvas>
	<div id="result"></div>
	<div id="total"></div>
</div>