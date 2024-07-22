
if (typeof wc_garden_designer_params !== 'undefined' && typeof wc_garden_designer_params.checkout_url !== 'undefined') {
	var checkoutPageUrl = wc_garden_designer_params.checkout_url;
	var heightTag = wc_garden_designer_params.height_tag;
	var widthTag = wc_garden_designer_params.width_tag;
	var shadeTag = wc_garden_designer_params.shade_tag;
	var partShadeTag = wc_garden_designer_params.part_shade_tag;
	var sunTag = wc_garden_designer_params.sun_tag;
} else {
	console.error('Checkout URL is not defined.');
}

const { __, _x, _n, sprintf } = wp.i18n;

            var currentShapeType = 'rectangle';
			var currentShineType = 'polusjena';
            var currentDimensions = { width: 0, length: 0 };

            function drawRectangle(ctx, width, length) {
				ctx.fillStyle = '#008f7e';
                ctx.fillRect(20, 15, width * 3, length * 3);
            }

            function drawEllipse(ctx, width, length) {
				ctx.fillStyle = '#008f7e';
                ctx.beginPath();
                ctx.ellipse(width * 1.5+15, length * 1.5+15, width * 1.5, length * 1.5, 0, 0, 2 * Math.PI);
                ctx.fill();
            }

            function updateInformativeShape() {
                var informativeCanvas = document.getElementById('informativeCanvas');
                var informativeCtx = informativeCanvas.getContext('2d');

                informativeCtx.clearRect(0, 0, informativeCanvas.width, informativeCanvas.height);

				currentShineType = document.querySelector('input[name="shineType"]:checked').value;
                currentShapeType = document.querySelector('input[name="shapeType"]:checked').value;
                var width = 50;
                var length = 30;
                if (currentShapeType === 'rectangle') {
                    drawRectangle(informativeCtx, width, length);
                } else if (currentShapeType === 'ellipse') {
                    drawEllipse(informativeCtx, width, length);
                }
				informativeCtx.font = '14px Arial';
				informativeCtx.fillText(__( 'lenght', 'woocommerce-garden-designer' ), 73, 10);
				informativeCtx.save();
				
				informativeCtx.rotate(-Math.PI / 2);
				informativeCtx.fillText(__( 'width', 'woocommerce-garden-designer' ), -informativeCanvas.height / 2-24, 10);
				informativeCtx.restore();
				
				window.globalShine = document.querySelector('input[name="shineType"]:checked').value;
				window.globalShape = document.querySelector('input[name="shapeType"]:checked').value;
            }
		
function showLoader() {
    var loader = document.createElement("div");
	var loaderimg = document.createElement("img");
	var ajaxurl = "/wp-content/plugins/woocommerce-garden-designer/includes/flower-loader.png";
    loaderimg.src = ajaxurl;
    loader.className = "loader";
	loaderimg.className = "loaderimg";
    loader.textContent = __( 'Preparing a plan, be patient...', 'woocommerce-garden-designer' );
    document.body.appendChild(loader);
	document.body.appendChild(loaderimg);
}

function hideLoader() {
    var loader = document.querySelector(".loader");
	var loaderimg = document.querySelector(".loaderimg");
    document.body.removeChild(loader);
	document.body.removeChild(loaderimg);
}

function hideFirstForm() {
	var prvaForma = document.getElementById('shapeForm');
	prvaForma.remove();
}

async function drawShape() {
	var radios = document.getElementsByName('shineType');
    var isAnyRadioChecked = false;
    for (var i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            isAnyRadioChecked = true;
            break;
        }
    }
    if (!isAnyRadioChecked) {
		var errorOne = __( 'You have not selected the type of illumination!', 'woocommerce-garden-designer' );
		throw new Error(errorOne);
    }

	var radiosGradina = document.getElementsByName('shapeType');
    var isAnyRadioCheckedGradina = false;
    for (var i = 0; i < radiosGradina.length; i++) {
        if (radiosGradina[i].checked) {
            isAnyRadioCheckedGradina = true;
            break;
        }
    }
    if (!isAnyRadioCheckedGradina) {
		var errorTwo = __( 'You have not selected a flower bed type!', 'woocommerce-garden-designer' );
		throw new Error(errorTwo);
    }

	var lengthInput = document.getElementById('lengthInput');
	var widthInput = document.getElementById('widthInput');

    if (lengthInput.value === '' || widthInput.value === '') {
		var errorThree = __( 'You have not set a dimension!', 'woocommerce-garden-designer' );
		throw new Error(errorThree);
    }
	
		return new Promise(resolve => {
        setTimeout(() => {

	window.globalWidth = document.getElementById('widthInput').value;
	window.globalLength = document.getElementById('lengthInput').value;
	
    updateInformativeShape();

    currentDimensions.width = parseFloat(document.getElementById('widthInput').value);
    currentDimensions.length = parseFloat(document.getElementById('lengthInput').value);

    // AJAX for passing values ​​in PHP
    var xhr = new XMLHttpRequest();
    var ajaxurl = "/wp-content/plugins/woocommerce-garden-designer/includes/garden-designer-planer.php";
    xhr.open("POST", ajaxurl, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('result').innerHTML = xhr.responseText;
        }
    };

    xhr.send("shapeType=" + currentShapeType + "&shineType=" + currentShineType + "&width=" + currentDimensions.length + "&length=" + currentDimensions.width);
resolve();
        }, 3000);
    });
}

async function startDrawing() {
    showLoader();
    try {
        await drawShape();
    } catch (error) {
        alert(error);
        location.reload();
        return;
    }
	hideFirstForm();
    hideLoader();
}

var isRotated = false;
function drawShapeRotate() {

var currentDimensions = { width: 0, length: 0 };
var shine = window.globalShine;
var shape = window.globalShape;
var width = window.globalWidth;
var length = window.globalLength;
    if (isRotated) {
        currentDimensions.width = parseFloat(width);
        currentDimensions.length = parseFloat(length);
    } else {
        currentDimensions.width = parseFloat(length);
        currentDimensions.length = parseFloat(width);
    }

    // AJAX for passing values ​​in PHP
    var xhr = new XMLHttpRequest();
	var ajaxurl = "/wp-content/plugins/woocommerce-garden-designer/includes/garden-designer-planer.php";
    xhr.open("POST", ajaxurl, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('result').innerHTML = xhr.responseText;
        }
    };

    xhr.send("shapeType=" + shape + "&shineType=" + shine + "&width=" + currentDimensions.width + "&length=" + currentDimensions.length);
    isRotated = !isRotated;
}

var visokiproductsIDandQuantity = '';
var sredniproductsIDandQuantity = '';
var niskiproductsIDandQuantity = '';
let visokiproductsIDandQuantitySet = false;
let sredniproductsIDandQuantitySet = false;
let niskiproductsIDandQuantitySet = false;
var productsIDandQuantity = '';

// Transferring to the selected products in the fields for display
async function showSelectedProducts() {
    var visokiList = document.getElementById('visoki_izbrani_proizvodi');
    var sredniList = document.getElementById('sredni_izbrani_proizvodi');
    var niskiList = document.getElementById('niski_izbrani_proizvodi');

    visokiList.innerHTML = '';
    sredniList.innerHTML = '';
    niskiList.innerHTML = '';

    var visokiCheckboxes = document.getElementsByName('visoki[]');
    var sredniCheckboxes = document.getElementsByName('sredni[]');
    var niskiCheckboxes = document.getElementsByName('niski[]');
	
	var visokiQuantityCheckboxes = document.getElementsByName('quantityvisoki[]');
    var sredniQuantityCheckboxes = document.getElementsByName('quantitysredni[]');
    var niskiQuantityCheckboxes = document.getElementsByName('quantityniski[]');
	
	var quantityText = __('quantity:', 'woocommerce-garden-designer');

    for (var i = 0; i < visokiCheckboxes.length; i++) {
		var checkbox = visokiCheckboxes[i];
		if (checkbox.checked) {
			var listItem = document.createElement('li');

			// Adding to the quantities next to the value on the check box
			var quantity = visokiQuantityCheckboxes[i].value;
			listItem.textContent = checkbox.title + ' (' +  quantityText + ' ' + quantity + ')';

			visokiList.appendChild(listItem);
			// Adding to ID and quantity in productsIDandQuantity
            if (!visokiproductsIDandQuantitySet) {
                var cleanedValue = checkbox.value.replace(odabrano, '');
                visokiproductsIDandQuantity += cleanedValue + ':' + quantity + ',';
                listForAddToCart(visokiproductsIDandQuantity);
            }
			
            visokiproductsIDandQuantitySet = true;
            
		}
	}

	for (var j = 0; j < sredniCheckboxes.length; j++) {
		var checkbox = sredniCheckboxes[j];
		if (checkbox.checked) {
			var listItem = document.createElement('li');

			// Adding to the quantities next to the value on the check box
			var quantity = sredniQuantityCheckboxes[j].value;
			listItem.textContent = checkbox.title + ' (' +  quantityText + ' ' + quantity + ')';

			sredniList.appendChild(listItem);
			// Adding to ID and quantity in productsIDandQuantity
            if (!sredniproductsIDandQuantitySet) {
			var cleanedValue = checkbox.value.replace(odabrano, '');
			sredniproductsIDandQuantity += cleanedValue + ':' + quantity + ',';
            listForAddToCart(sredniproductsIDandQuantity);
            }
            sredniproductsIDandQuantitySet = true;
		}
	}

	for (var k = 0; k < niskiCheckboxes.length; k++) {
		var checkbox = niskiCheckboxes[k];
		if (checkbox.checked) {
			var listItem = document.createElement('li');

			// Adding to the quantities next to the value on the check box
			var quantity = niskiQuantityCheckboxes[k].value;
			listItem.textContent = checkbox.title + ' (' +  quantityText + ' ' + quantity + ')';

			niskiList.appendChild(listItem);
			// Adding to ID and quantity in productsIDandQuantity
            if (!niskiproductsIDandQuantitySet) {
			var cleanedValue = checkbox.value.replace(odabrano, '');
			niskiproductsIDandQuantity += cleanedValue + ':' + quantity + ',';
            listForAddToCart(niskiproductsIDandQuantity);
            }
            niskiproductsIDandQuantitySet = true;
		}
	}

await presmetka(productsIDandQuantity);

	return false;
}

function listForAddToCart(listtype) {
    switch (listtype) {
        case visokiproductsIDandQuantity:
            productsIDandQuantity = listtype + sredniproductsIDandQuantity + niskiproductsIDandQuantity;
            break;
        case sredniproductsIDandQuantity:
            productsIDandQuantity = visokiproductsIDandQuantity + listtype + niskiproductsIDandQuantity;
            break;
        case niskiproductsIDandQuantity:
            productsIDandQuantity = visokiproductsIDandQuantity + sredniproductsIDandQuantity + listtype; 
            break;
    }
    return;
}

function kaNaplati() {
    var addToCartUrl = new URL(checkoutPageUrl);
    addToCartUrl.searchParams.append('add-to-cart', productsIDandQuantity);
	window.location.href = addToCartUrl.href;
}

// Calculation of prices
async function presmetka(productsIDandQuantity){
	showTotal = true;
	return new Promise(resolve => {
        setTimeout(() => {
			var xhrTotal = new XMLHttpRequest();
			var ajaxurl = "/wp-content/plugins/woocommerce-garden-designer/includes/garden-designer-sum.php";
			xhrTotal.open("POST", ajaxurl, true);
			xhrTotal.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

			xhrTotal.onreadystatechange = function() {
				if (xhrTotal.readyState == 4 && xhrTotal.status == 200) {
					document.getElementById('total').innerHTML += __( 'The total sum is:', 'woocommerce-garden-designer' ) + xhrTotal.responseText;
				}
			};

			xhrTotal.send("products=" + productsIDandQuantity);
			resolve();
		}, 100);
	});
}

function clearForm(type) {
    clearCounts(type);
    clearSelectedProducts(type);
    clearCheckboxes(type);
    clearQuantityInputs(type);
    clearCounters();
    disableNext();
	setTimeout(() => {
	document.getElementById('total').innerHTML = "";
	}, 1000);
    clearProductsIDandQuantity(type);
}

function clearProductsIDandQuantity(type) {
    var variableName = type + 'productsIDandQuantity';
    window[variableName] = '';
    listForAddToCart(variableName);
}

function clearCounts(type) {
    if (counts.hasOwnProperty(type)) {
        counts[type] = 0;
    }
}

function clearCheckboxes(type) {
    var checkboxes = document.getElementsByName(type + '[]');
    clearCheckboxesState(checkboxes);
	showSelectedProducts();
}

function clearCheckboxesState(checkboxes) {
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = false;
    }
}

function clearSelectedProducts(type) {
    clearList(type + '_izbrani_proizvodi');
}

function clearList(id) {
    var list = document.getElementById(id);
    if (list) {
        while (list.firstChild) {
            list.removeChild(list.firstChild);
        }
    }
}

function clearCounters() {
    var counters = document.querySelectorAll('.brojach');
    counters.forEach(function(counter) {
        counter.textContent = '0';
		counter.classList.remove('uspeh');
    });
}
function clearQuantityInputs(type) {
    var quantityInputs = document.querySelectorAll('input[name=' + CSS.escape('quantity' + type + '[]') + ']');
    quantityInputs.forEach(function(input) {
        input.value = '';
		input.setAttribute('data-old-quantity', 0);
    });
}

let odabrano = "";

function odabir(tip) {
    odabrano = tip;

    var form = document.getElementById('forma' + odabrano);

    var numberInputs = form.querySelectorAll('input[type=number]');

    for (var i = 0; i < numberInputs.length; i++) {
        if (numberInputs[i].hasAttribute('data-old-quantity')) {
            numberInputs[i].id = numberInputs[i].id + odabrano;
        }
    }

    var checkboxInputs = form.querySelectorAll('input[type=checkbox]');
    for (var i = 0; i < checkboxInputs.length; i++) {
        checkboxInputs[i].value = checkboxInputs[i].value + odabrano;
    }
}

var counts = {
    'visoki': 0,
    'sredni': 0,
    'niski': 0,
};

function updateCountsCheckbox(event) {
    var checkbox = event.target;
    var quantityInput = document.getElementById(checkbox.value);
	var quantity = quantityInput && quantityInput.value ? parseInt(quantityInput.value) : 0;


    var type = checkbox.name.split('[]')[0];
    if (counts.hasOwnProperty(type)) {
        if (checkbox.checked) {
            counts[type] += quantity;
        } else { 
            counts[type] = Math.max(0, counts[type] - quantity);
        }
    }

    updateCounters();
}

function updateCountsNumber(event) {
    var input = event.target;
    var max = parseInt(input.max);
    var min = parseInt(input.min);
    if (input.value > max) {
        input.value = max;
    }
    if (input.value < min) {
        input.value = min;
    }

    var checkbox = document.querySelector('input[type=checkbox][value="' + input.id + '"]');
    if (checkbox && checkbox.checked) {
        var type = checkbox.name.split('[]')[0];
        if (counts.hasOwnProperty(type)) {
            var oldQuantity = parseInt(input.getAttribute('data-old-quantity')) || 0;
            counts[type] = counts[type] - oldQuantity + parseInt(input.value);
            input.setAttribute('data-old-quantity', input.value);
        }
    }

    updateCounters();
}

function updateCounters() {
	
	var daljeTekst = {
    'visoki': __( 'Continue to the Middle Plants', 'woocommerce-garden-designer' ),
    'sredni': __( 'Continue to Low Plants', 'woocommerce-garden-designer' ),
    'niski': __( 'Add all the selected plants to the shopping list', 'woocommerce-garden-designer' ),
	};
	var daljeTekstNe = {
    'visoki': __( 'Please select tall plants first', 'woocommerce-garden-designer' ),
    'sredni': __( 'Please select medium plants first', 'woocommerce-garden-designer' ),
    'niski': __( 'Please select short plants first', 'woocommerce-garden-designer' ),
	};

    for (var type in counts) {
        var counter = document.getElementById('counter-' + type);
        var counterGusto = document.getElementById('counter-' + type + '-gusto');
        if (counter && counterGusto) {
            counter.textContent = counts[type];
            counterGusto.textContent = counts[type];

			 var recommended = parseInt(counter.getAttribute('data-recommended'));
            var recommendedGusto = parseInt(counterGusto.getAttribute('data-recommended'));

			if (odabrano === "Standardno") {
				var dalje = {
					'visoki': document.getElementById('daljevisokiStandardno'),
					'sredni': document.getElementById('daljesredniStandardno'),
					'niski': document.getElementById('daljeniskiStandardno'),
				};
				if (counts[type] >= recommended) {
					counter.classList.add('uspeh');
					dalje[type].disabled = false;
					dalje[type].innerText = daljeTekst[type];
					document.getElementById('dalje' + type + 'Standardno').focus();
				} else {
					counter.classList.remove('uspeh');
					dalje[type].disabled = true;
					dalje[type].innerText = daljeTekstNe[type];
				}
			} else if (odabrano === "Gusto") {
				var dalje = {
					'visoki': document.getElementById('daljevisokiGusto'),
					'sredni': document.getElementById('daljesredniGusto'),
					'niski': document.getElementById('daljeniskiGusto'),
				};
				if (counts[type] >= recommendedGusto) {
					counterGusto.classList.add('uspeh');
					counter.classList.add('bledo');
					dalje[type].disabled = false;
					dalje[type].innerText = daljeTekst[type];
					document.getElementById('dalje' + type + 'Gusto').focus();
				} else {
					counterGusto.classList.remove('uspeh');
					counter.classList.remove('bledo');
					dalje[type].disabled = true;
					dalje[type].innerText = daljeTekstNe[type];
				}
			}
        }
    }
}

function showStep(step) {
    document.getElementById('visokeBiljkeStandardno').style.display = 'none';
    document.getElementById('srednjeBiljkeStandardno').style.display = 'none';
    document.getElementById('niskeBiljkeStandardno').style.display = 'none';
	document.getElementById('visokeBiljkeGusto').style.display = 'none';
    document.getElementById('srednjeBiljkeGusto').style.display = 'none';
    document.getElementById('niskeBiljkeGusto').style.display = 'none';
    document.getElementById(step).style.display = 'block';
	document.documentElement.scrollTop = 0; // за <html>
}

function hideStep(step) {
    document.getElementById(step).style.display = 'none';
}

function disableNext() {
	document.getElementById('daljevisokiStandardno').disabled=true;
	document.getElementById('daljevisokiStandardno').innerText=__( 'Please select tall plants first', 'woocommerce-garden-designer' );
	document.getElementById('daljesredniStandardno').disabled=true;
	document.getElementById('daljesredniStandardno').innerText=__( 'Please select medium plants first', 'woocommerce-garden-designer' );
	document.getElementById('daljeniskiStandardno').disabled=true;
	document.getElementById('daljeniskiStandardno').innerText=__( 'Please select short plants first', 'woocommerce-garden-designer' );
	document.getElementById('daljevisokiGusto').disabled=true;
	document.getElementById('daljevisokiGusto').innerText=__( 'Please select tall plants first', 'woocommerce-garden-designer' );
	document.getElementById('daljesredniGusto').disabled=true;
	document.getElementById('daljesredniGusto').innerText=__( 'Please select medium plants first', 'woocommerce-garden-designer' );
	document.getElementById('daljeniskiGusto').disabled=true;
	document.getElementById('daljeniskiGusto').innerText=__( 'Please select short plants first', 'woocommerce-garden-designer' );
	document.documentElement.scrollTop = 0;
}