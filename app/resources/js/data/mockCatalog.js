export const featuredCategories = [
    {
        name: 'Creator Studio Deals',
        description: '4K-ready desktops and color-true monitors for pro creatives.',
        to: '/category/creator-studio',
        image: 'https://images.unsplash.com/photo-1484704849700-f032a568e944?auto=format&fit=crop&w=800&q=80',
        accent: 'from $1,199',
    },
    {
        name: 'Smart Living',
        description: 'Intelligent climate, lighting, and security that just works.',
        to: '/category/smart-living',
        image: 'https://images.unsplash.com/photo-1580894908361-967195033215?auto=format&fit=crop&w=800&q=80',
        accent: 'bundle & save 20%',
    },
    {
        name: 'Work From Anywhere',
        description: 'Lightweight performance laptops and accessories for hybrid teams.',
        to: '/category/work-anywhere',
        image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
        accent: 'staff picks',
    },
];

export const featuredProducts = [
    {
        id: 1,
        name: 'VisionBook X16 OLED',
        slug: 'visionbook-x16-oled',
        price: 2399,
        compareAtPrice: 2699,
        rating: 4.7,
        ratingCount: 128,
        badge: 'new arrival',
        image: 'https://images.unsplash.com/photo-1587614295999-6c0c1a2cb2a5?auto=format&fit=crop&w=900&q=80',
        meta: ['Intel? Core? Ultra 9', 'GeForce RTX? 4090', '32GB RAM ? 2TB NVMe'],
    },
    {
        id: 2,
        name: 'Nimbus Soundstage Pro',
        slug: 'nimbus-soundstage-pro',
        price: 349,
        compareAtPrice: 399,
        rating: 4.9,
        ratingCount: 412,
        badge: 'best seller',
        image: 'https://images.unsplash.com/photo-1580894908361-967195033215?auto=format&fit=crop&w=900&q=80',
        meta: ['Spatial audio', '48h battery', 'Adaptive ANC'],
    },
    {
        id: 3,
        name: 'HomeSphere Automation Hub X2',
        slug: 'homesphere-automation-hub-x2',
        price: 229,
        compareAtPrice: null,
        rating: 4.6,
        ratingCount: 95,
        badge: 'bundle & save',
        image: 'https://images.unsplash.com/photo-1558089687-f282ffcbc126?auto=format&fit=crop&w=900&q=80',
        meta: ['Matter + Thread', 'Secure edge compute', 'Voice + app controls'],
    },
    {
        id: 4,
        name: 'Aeris XR Glass',
        slug: 'aeris-xr-glass',
        price: 1199,
        compareAtPrice: 1349,
        rating: 4.4,
        ratingCount: 61,
        badge: 'limited',
        image: 'https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=900&q=80',
        meta: ['Micro-OLED', '6DoF tracking', '2hr quick charge'],
    },
];

export const dailyDeals = [
    {
        id: 10,
        name: 'QuantaFlex Portable Monitor 15.6"',
        slug: 'quantaflex-portable-monitor',
        price: 199,
        compareAtPrice: 289,
        endsIn: '6h 21m',
        image: 'https://images.unsplash.com/photo-1580894732444-8ecded7900cd?auto=format&fit=crop&w=900&q=80',
        meta: ['1080p IPS', 'USB-C + mini HDMI', 'Built-in kickstand'],
    },
    {
        id: 11,
        name: 'PulseShift Wireless Mechanical Keyboard',
        slug: 'pulseshift-wireless-mechanical-keyboard',
        price: 139,
        compareAtPrice: 189,
        endsIn: 'Today',
        image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&q=80',
        meta: ['Tri-mode connectivity', 'Hot-swappable Gasket', 'Per-key RGB'],
    },
];

export const categoryFilters = [
    {
        label: 'Availability',
        key: 'availability',
        options: [
            { label: 'In stock', value: 'in-stock', count: 124 },
            { label: 'Pre-order', value: 'pre-order', count: 18 },
            { label: 'Refurbished', value: 'refurbished', count: 22 },
        ],
    },
    {
        label: 'Price',
        key: 'price',
        options: [
            { label: 'Under $1,000', value: 'under-1000', count: 34 },
            { label: '$1,000 - $2,000', value: '1000-2000', count: 52 },
            { label: 'Above $2,000', value: 'above-2000', count: 29 },
        ],
    },
    {
        label: 'Processor',
        key: 'processor',
        options: [
            { label: 'Intel? Core? Ultra', value: 'intel-core-ultra', count: 48 },
            { label: 'AMD Ryzen? 8000', value: 'amd-ryzen-8000', count: 41 },
            { label: 'Apple M3 Pro', value: 'apple-m3', count: 16 },
        ],
    },
    {
        label: 'Graphics',
        key: 'graphics',
        options: [
            { label: 'NVIDIA RTX? 40', value: 'nvidia-rtx-40', count: 61 },
            { label: 'AMD Radeon? RX 7000', value: 'amd-rx-7000', count: 25 },
            { label: 'Integrated', value: 'integrated', count: 19 },
        ],
    },
];

export const categoryProducts = featuredProducts.concat(
    Array.from({ length: 8 }).map((_, index) => ({
        id: 200 + index,
        name: `Aurora Creator Studio ${index + 1}`,
        slug: `aurora-creator-${index + 1}`,
        price: 1899 + index * 50,
        compareAtPrice: index % 2 === 0 ? 1999 + index * 45 : null,
        rating: 4.3 + (index % 3) * 0.1,
        ratingCount: 60 + index * 5,
        badge: index % 3 === 0 ? 'bundle' : null,
        image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80',
        meta: ['RTX? 4070', '32GB DDR5', '1TB NVMe SSD'],
    }))
);

export const productSpecifications = [
    {
        label: 'Performance',
        items: [
            ['Processor', 'Intel? Core? Ultra 9 185H'],
            ['Graphics', 'NVIDIA GeForce RTX? 4090 16GB GDDR6X'],
            ['Memory', '64GB LPDDR5X 7467MHz'],
            ['Storage', '2TB NVMe PCIe Gen4 SSD'],
        ],
    },
    {
        label: 'Display',
        items: [
            ['Panel', '16" OLED 240Hz, 100% DCI-P3'],
            ['Resolution', '3840 x 2400 (4K+)'],
            ['Brightness', '600 nits HDR'],
        ],
    },
    {
        label: 'Connectivity',
        items: [
            ['Wireless', 'Wi?Fi 7, Bluetooth 5.4'],
            ['Ports', '2x Thunderbolt 4, HDMI 2.1, SD Express 8.0'],
            ['Battery', '99.9 Wh, 180W GaN charger'],
        ],
    },
];

export const productMedia = [
    'https://images.unsplash.com/photo-1593642634315-48f5414c3ad9?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1519183071298-a2962eadcdb2?auto=format&fit=crop&w=1200&q=80',
];
