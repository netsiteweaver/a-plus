export const primaryNavigation = [
    {
        label: 'Home',
        to: '/',
    },
    {
        label: 'Laptops & Computers',
        to: '/category/laptops',
        description: 'Premium ultrabooks, gaming rigs, and workstations.',
        columns: [
            {
                heading: 'Shop by device',
                items: [
                    { label: 'Ultrabooks', to: '/category/ultrabooks' },
                    { label: 'Gaming laptops', to: '/category/gaming-laptops' },
                    { label: '2-in-1 convertibles', to: '/category/2-in-1' },
                    { label: 'Chromebooks', to: '/category/chromebooks' },
                ],
            },
            {
                heading: 'Shop by brand',
                items: [
                    { label: 'Apple', to: '/category/laptops?brand=apple' },
                    { label: 'Lenovo', to: '/category/laptops?brand=lenovo' },
                    { label: 'ASUS', to: '/category/laptops?brand=asus' },
                    { label: 'MSI', to: '/category/laptops?brand=msi' },
                ],
            },
            {
                heading: 'Featured',
                items: [
                    { label: 'Creator studio picks', to: '/category/creator-laptops' },
                    { label: 'AI-ready PCs', to: '/category/ai-pc' },
                    { label: 'Certified refurbished', to: '/category/refurbished' },
                ],
            },
        ],
        hero: {
            eyebrow: 'Save up to 25%',
            title: 'Vision Pro Ultrabook 16',
            description: '12th gen Intel H-series, OLED 240Hz, LiquidCool X2 thermal design.',
            to: '/product/vision-pro-ultrabook-16',
            image: 'https://images.unsplash.com/photo-1527430253228-e93688616381?auto=format&fit=crop&w=900&q=80',
        },
    },
    {
        label: 'Audio & Wearables',
        to: '/category/audio',
        description: 'Immersive listening, crystal-clear calls, fitness tracking.',
        columns: [
            {
                heading: 'Headphones',
                items: [
                    { label: 'Noise-cancelling', to: '/category/noise-cancelling' },
                    { label: 'True wireless', to: '/category/true-wireless' },
                    { label: 'Studio monitors', to: '/category/studio-headphones' },
                ],
            },
            {
                heading: 'Wearables',
                items: [
                    { label: 'Smartwatches', to: '/category/smartwatches' },
                    { label: 'Fitness trackers', to: '/category/fitness-trackers' },
                    { label: 'AR glasses', to: '/category/ar-glasses' },
                ],
            },
            {
                heading: 'Accessories',
                items: [
                    { label: 'DAC & amps', to: '/category/audio-dac' },
                    { label: 'Microphones', to: '/category/microphones' },
                    { label: 'Charging stations', to: '/category/charging' },
                ],
            },
        ],
        hero: {
            eyebrow: 'Bundle & save',
            title: 'Nimbus Soundstage Pro',
            description: 'Spatial audio earbuds with adaptive noise control and 48-hour battery.',
            to: '/product/nimbus-soundstage-pro',
            image: 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=900&q=80',
        },
    },
    {
        label: 'Smart Home',
        to: '/category/smart-home',
        description: 'Automate lighting, climate, security, and more.',
        columns: [
            {
                heading: 'Solutions',
                items: [
                    { label: 'Smart lighting', to: '/category/smart-lighting' },
                    { label: 'Climate control', to: '/category/climate' },
                    { label: 'Security & cams', to: '/category/security' },
                ],
            },
            {
                heading: 'Ecosystems',
                items: [
                    { label: 'Matter-ready', to: '/category/matter' },
                    { label: 'HomeKit', to: '/category/homekit' },
                    { label: 'Google Home', to: '/category/google-home' },
                    { label: 'Alexa', to: '/category/alexa' },
                ],
            },
            {
                heading: 'Services',
                items: [
                    { label: 'Installation', to: '/services/installation' },
                    { label: 'Pro monitoring', to: '/services/monitoring' },
                ],
            },
        ],
        hero: {
            eyebrow: 'Smart essentials',
            title: 'HomeSphere Automation Hub X2',
            description: 'Control 300+ devices with Matter, Thread, and Zigbee support.',
            to: '/product/homesphere-automation-hub-x2',
            image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
        },
    },
    {
        label: 'Services',
        to: '/services',
    },
];

export const utilityNavigation = [
    { label: 'Support', to: '/support' },
    { label: 'Track order', to: '/support/track-order' },
    { label: 'Business', to: '/business' },
];
