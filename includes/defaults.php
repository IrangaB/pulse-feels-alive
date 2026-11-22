<?php
/* The website's original content. Used until the customer saves changes in the admin page,
   and to fill in anything missing. Editing is done in /admin, not here. */
return [
    'hero' => [
        'eyebrow' => 'CUSTOM APPAREL & ACCESSORIES',
        'title_line1' => 'YOUR DESIGN.',
        'title_line2' => 'YOUR IDENTITY.',
        'intro' => 'Custom printed apparel that brings your ideas, colours and identity to life.',
        'caption_left' => 'MADE AROUND YOUR VISION',
        'caption_right' => 'ENDLESS CUSTOM DESIGNS',
        'poster' => 'assets/images/hero-poster.jpg',
        'video' => 'assets/video/pulse-hero.mp4',
    ],
    'intro' => [
        'eyebrow' => 'PULSE · FEELS ALIVE',
        'title' => 'Apparel that represents',
        'title_accent' => 'you.',
        'text' => 'We create custom-printed apparel and accessories for sports teams, clubs, associations, businesses and brands. Choose your colours, decide how much of each garment you want to print, and create a design that feels uniquely yours.',
    ],
    'story' => [
        'eyebrow' => 'WHO WE ARE',
        'title' => 'Our',
        'title_accent' => 'Story',
        'heading_line1' => 'More than apparel.',
        'heading_line2' => "It's your identity.",
        'text' => "Every brand has a story, and every design has a message. PULSE · FEELS ALIVE was created around a simple idea: custom apparel should give people and organisations the freedom to express who they are.\n\nWe work with teams, clubs, associations and businesses to turn ideas into distinctive garments and accessories. From a simple logo placement to a bold all-over print, we help bring your vision into production.",
        'highlight' => 'Your colours. Your design. Your pulse.',
        'image' => 'assets/images/our-story.jpg',
    ],
    'features' => [
        'image' => 'assets/images/pulse-feature-bar.png',
        'alt' => 'Minimum order 10 pieces; full or partial print coverage; colour choices; door-to-door delivery within 8 weeks.',
    ],
    'fabrics_section' => [
        'eyebrow' => 'MATERIAL OPTIONS',
        'title' => 'Explore our',
        'title_accent' => 'Fabrics',
        'text' => 'Explore selected fabric options for your custom apparel. Final availability and suitability depend on the product and design.',
    ],
    'products_section' => [
        'eyebrow' => 'WHAT WE MAKE',
        'title' => 'Product',
        'title_accent' => 'Categories',
        'text' => 'Customise apparel and accessories with your own artwork, colours and identity.',
    ],
    'gallery_page' => [
        'eyebrow' => 'MADE BY PULSE',
        'title' => 'Product',
        'title_accent' => 'Gallery',
        'text' => 'A selection of custom apparel we have designed and produced for clubs, teams and businesses.',
    ],
    'customers_page' => [
        'eyebrow' => 'TRUSTED BY',
        'title' => 'Our',
        'title_accent' => 'Customers',
        'text' => 'We are proud to create custom apparel for these clubs, teams and businesses.',
        'button_label' => 'Request a Quote',
    ],
    'contact' => [
        'eyebrow' => 'START A PROJECT',
        'title_line1' => "Let's create",
        'title_line2' => 'something together.',
        'text' => "Tell us about your team, business or apparel idea. Share the product, quantity and design details, and we'll get back to you.",
        'bullets' => "Minimum order: 10 pieces\nPartial or full-garment printing\nEstimated delivery: up to two months, subject to confirmation",
    ],
    'footer' => [
        'tagline' => 'Fully customised apparel & accessories.',
        'company_details' => 'C Suomi Ryhmä Ay  |  Y-Tunnus 3446140-3',
    ],
    'products' => [
        ['id' => 'tshirts', 'name' => 'T-Shirts', 'image' => 'assets/images/product-tshirts.jpg', 'order' => 10, 'online' => true],
        ['id' => 'neck-warmers', 'name' => 'Neck Warmers', 'image' => 'assets/images/product-neck-warmers.jpg', 'order' => 20, 'online' => true],
        ['id' => 'pipos', 'name' => 'Pipos (coming soon)', 'image' => 'assets/images/product-pipos.jpg', 'order' => 30, 'online' => true],
    ],
    'gallery' => [],
    'customers' => [],
    'fabrics' => [
        ['id' => 'wetlook', 'name' => 'Wetlook', 'description' => 'Smooth, tightly woven surface with a distinctive glossy finish and light-reflecting folds.', 'image' => 'assets/images/fabric-wetlook.jpg', 'order' => 10, 'online' => true],
        ['id' => 'baby-pique', 'name' => 'Baby Piqué', 'description' => 'Fine, compact knitted texture with a small, closely spaced pattern and a subtle matte finish.', 'image' => 'assets/images/fabric-baby-pique.jpg', 'order' => 20, 'online' => true],
        ['id' => 'mesh', 'name' => 'Mesh', 'description' => 'Open-knit structure with clearly visible small perforations and a distinctive textured appearance.', 'image' => 'assets/images/fabric-mesh.jpg', 'order' => 30, 'online' => true],
    ],
];
