<template>
    <footer class="border-t border-slate-200 bg-slate-50 py-12 text-sm text-slate-500">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 md:flex-row md:justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-3 text-slate-800">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                        ME
                    </span>
                    <div>
                        <p class="text-sm font-semibold tracking-tight">{{appName}}</p>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Since 2000</p>
                    </div>
                </div>
                <p class="max-w-sm text-sm text-slate-500">
                    High-performance devices, smart living solutions, and concierge-grade support. Built with Laravel + Vue + Tailwind.
                </p>
                <p class="text-xs text-slate-400">&copy; {{ currentYear }} {{appName}}. All rights reserved. Developed by <a target="_blank" href="https://www.netsiteweaver.com">Netsiteweaver Ltd</a></p>
            </div>

            <div class="grid flex-1 gap-8 text-sm text-slate-500 sm:grid-cols-2 md:grid-cols-3">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Explore</p>
                    <RouterLink to="/category/laptops" class="block transition hover:text-sky-600">Computers</RouterLink>
                    <RouterLink to="/category/audio" class="block transition hover:text-sky-600">Audio</RouterLink>
                    <RouterLink to="/category/smart-home" class="block transition hover:text-sky-600">Smart Home</RouterLink>
                    <RouterLink to="/category/accessories" class="block transition hover:text-sky-600">Accessories</RouterLink>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Support</p>
                    <RouterLink to="/support" class="block transition hover:text-sky-600">Help center</RouterLink>
                    <RouterLink to="/support/warranty" class="block transition hover:text-sky-600">Warranty</RouterLink>
                    <RouterLink to="/support/returns" class="block transition hover:text-sky-600">Returns</RouterLink>
                    <RouterLink to="/support/contact" class="block transition hover:text-sky-600">Contact us</RouterLink>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Stay in sync</p>
                    <form class="space-y-3">
                        <label class="block text-xs text-slate-400" for="newsletter">Subscribe for drops &amp; deals</label>
                        <div class="flex rounded-full border border-slate-200 bg-white p-1">
                            <input
                                id="newsletter"
                                type="email"
                                placeholder="you@example.com"
                                class="flex-1 bg-transparent px-3 text-xs text-slate-600 placeholder:text-slate-400 focus:outline-none"
                            />
                            <button type="button" class="rounded-full bg-sky-600 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-white transition hover:bg-sky-500">
                                Join
                            </button>
                        </div>
                    </form>
                    <div class="flex items-center gap-3 text-slate-500">
                        <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white transition hover:border-sky-400 hover:text-sky-600" aria-label="Instagram">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <rect x="2.75" y="2.75" width="18.5" height="18.5" rx="5" />
                                <circle cx="12" cy="12" r="4.25" />
                                <circle cx="17" cy="7" r="1.25" fill="currentColor" stroke="none" />
                            </svg>
                        </a>
                        <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white transition hover:border-sky-400 hover:text-sky-600" aria-label="X (Twitter)">
                            <span class="sr-only">X (Twitter)</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 4.5 9.75 12 4 19.5h3.2L12 14l4.8 5.5h3.2L14.25 12 20 4.5h-3.2L12 10l-4.8-5.5z" />
                            </svg>
                        </a>
                        <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white transition hover:border-sky-400 hover:text-sky-600" aria-label="YouTube">
                            <span class="sr-only">YouTube</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3.75 7.5a2.25 2.25 0 0 1 2.137-2.22C7.83 5.025 9.907 4.875 12 4.875s4.17.15 6.113.405a2.25 2.25 0 0 1 2.137 2.22v8.999a2.25 2.25 0 0 1-2.137 2.22c-1.943.255-4.02.405-6.113.405s-4.17-.15-6.113-.405a2.25 2.25 0 0 1-2.137-2.22z" />
                                <path d="M10.5 9.75v4.5l4-2.25-4-2.25z" fill="currentColor" stroke="none" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
// Get app name from environment variable
const appName = import.meta.env.VITE_APP_NAME;
const currentYear = new Date().getFullYear();
</script>
