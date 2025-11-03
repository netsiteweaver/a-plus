<template>
    <footer class="border-t border-slate-200 bg-slate-100/80 py-12 text-sm text-slate-500">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 md:flex-row md:justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-3 text-slate-700">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white">
                        ME
                    </span>
                    <div>
                        <p class="text-sm font-semibold tracking-tight text-slate-900">Mega Electronics</p>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Since 2011</p>
                    </div>
                </div>
                <p class="max-w-sm text-sm text-slate-600">
                    High-performance devices, smart living solutions, and concierge-grade support. Built with Laravel + Vue + Tailwind.
                </p>
                <p class="text-xs text-slate-400">&copy; {{ currentYear }} Mega Electronics. All rights reserved.</p>
            </div>

            <div class="grid flex-1 gap-8 text-sm text-slate-600 sm:grid-cols-2 md:grid-cols-3">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Explore</p>
                    <RouterLink to="/category/laptops" class="block transition hover:text-blue-600">Computers</RouterLink>
                    <RouterLink to="/category/audio" class="block transition hover:text-blue-600">Audio</RouterLink>
                    <RouterLink to="/category/smart-home" class="block transition hover:text-blue-600">Smart Home</RouterLink>
                    <RouterLink to="/category/accessories" class="block transition hover:text-blue-600">Accessories</RouterLink>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Support</p>
                    <RouterLink to="/support" class="block transition hover:text-blue-600">Help center</RouterLink>
                    <RouterLink to="/support/warranty" class="block transition hover:text-blue-600">Warranty</RouterLink>
                    <RouterLink to="/support/returns" class="block transition hover:text-blue-600">Returns</RouterLink>
                    <RouterLink to="/support/contact" class="block transition hover:text-blue-600">Contact us</RouterLink>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Stay in sync</p>
                    <form class="space-y-3">
                        <label class="block text-xs text-slate-400" for="newsletter">Subscribe for drops &amp; deals</label>
                        <div class="flex rounded-full border border-slate-200 bg-white p-1 shadow-sm">
                            <input
                                id="newsletter"
                                type="email"
                                placeholder="you@example.com"
                                class="flex-1 bg-transparent px-3 text-xs text-slate-600 placeholder:text-slate-400 focus:outline-none"
                            />
                            <button type="button" class="rounded-full bg-blue-600 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-white transition hover:bg-blue-500">
                                Join
                            </button>
                        </div>
                    </form>
                    <div class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                        <a href="#" class="transition hover:text-blue-600" aria-label="Instagram">IG</a>
                        <a href="#" class="transition hover:text-blue-600" aria-label="Twitter">TW</a>
                        <a href="#" class="transition hover:text-blue-600" aria-label="YouTube">YT</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>
