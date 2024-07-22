# woocommerce-garden-designer

"Garden Designer" allows your customers to optimize their garden based on expert recommendations. After selecting the lighting and surface type, the "Garden Designer" calculates the necessary types and quantities of plants. It also provides a planting layout sketch. Customers can choose from the available plants in stock and add them to their cart with a single click, directing them to your payment page.

Rules:
Lighting is categorized into three types:
  Sun (6 or more hours of sunlight)
  Partial shade (4-6 hours of sunlight)
  Shade (3 or fewer hours of sunlight)
Flower bed type options:
  Along a wall (where at least one side touches a wall)
  Central (where no side touches a wall)
Plants are divided into three groups:
  Tall plants (90-150 cm)
  Medium plants (50-80 cm)
  Short plants (15-40 cm)
Customers can choose between two planting methods:
  Standard spacing (planting distance: tall 60 cm, medium 50 cm, short 50 cm)
  Dense spacing (planting distance: tall 40 cm, medium 30 cm, short 30 cm)

Instructions for site administrators:
1. Before installing "Garden Designer," ensure that WooCommerce is already installed.
2. Define tags for the following characteristics:
  Lighting:
    Sun (e.g. sun, light)
    Partial shade (e.g. partial-shade, semi-dark)
    Shade (e.g. shade, dark)
  Flower bed type:
    Along a wall
    Central
  Plant height:
    Tall plants (90-150 cm)
    Medium plants (50-80 cm)
    Short plants (15-40 cm)
  Planting method:
    Standard spacing (planting distance: tall 60 cm, medium 50 cm, short 50 cm)
    Dense spacing (planting distance: tall 40 cm, medium 30 cm, short 30 cm)
3. After defining the tags, assign them to each of your products or the plants you offer for sale.
4. "Garden Designer" will consider only in-stock products and present them to customers.
5. After selecting desired plants, customers will be directly redirected to your designated payment page.
6. To use "Garden Designer", insert the following shortcode on any page: [garden_designer].

Note: You can enter only one tag without quotation marks and other special characters in the corresponding field for each tag.
After clicking the "Save Changes" button, the tags will be saved in the component's settings and automatically added to the WooCommerce "Tags" set.
In order for the component to function properly, after determining the tags, you need to assign them to each of your products, that is, to each herb that you offer for sale.
You add the "brightness" tags as you defined them.
You provide the "width" and "height" labels in the following format: label number cm (where "label" is the label you defined in the settings, "number" is the corresponding width or height of the plant expressed in centimeters, "cm" is for informational display of the dimension. Note that there are spaces between the sections.)
For example:
- If the label is "W:" and the plant is "30 cm" wide, then enter "W: 30 cm"
- If the mark is "H:" and the plant is "120 cm high", then enter "H: 120 cm"
Unfortunately, tags can only be typed in one language, although the component itself can be used in multiple languages.
