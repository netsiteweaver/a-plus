<template>
    <footer class="border-t border-white/10 bg-slate-900/80 py-12 text-sm text-white/60">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 md:flex-row md:justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-3 text-white">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                        ME
                    </span>
                    <div>
                        <p class="text-sm font-semibold tracking-tight">Mega Electronics</p>
                        <p class="text-xs uppercase tracking-[0.2em] text-white/40">Since 2011</p>
                    </div>
                </div>
                <p class="max-w-sm text-sm text-white/60">
                    High-performance devices, smart living solutions, and concierge-grade support. Built with Laravel ? Vue ? Tailwind.
                </p>
                <p class="text-xs text-white/40">&copy; {{ currentYear }} Mega Electronics. All rights reserved.</p>
            </div>

            <div class="grid flex-1 gap-8 text-sm text-white/60 sm:grid-cols-2 md:grid-cols-3">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/40">Explore</p>
                    <RouterLink to="/category/laptops" class="block transition hover:text-white">Computers</RouterLink>
                    <RouterLink to="/category/audio" class="block transition hover:text-white">Audio</RouterLink>
                    <RouterLink to="/category/smart-home" class="block transition hover:text-white">Smart Home</RouterLink>
                    <RouterLink to="/category/accessories" class="block transition hover:text-white">Accessories</RouterLink>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/40">Support</p>
                    <RouterLink to="/support" class="block transition hover:text-white">Help center</RouterLink>
                    <RouterLink to="/support/warranty" class="block transition hover:text-white">Warranty</RouterLink>
                    <RouterLink to="/support/returns" class="block transition hover:text-white">Returns</RouterLink>
                    <RouterLink to="/support/contact" class="block transition hover:text-white">Contact us</RouterLink>
                </div>
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/40">Stay in sync</p>
                    <form class="space-y-3">
                        <label class="block text-xs text-white/40" for="newsletter">Subscribe for drops &amp; deals</label>
                        <div class="flex rounded-full border border-white/10 bg-slate-900/80 p-1">
                            <input
                                id="newsletter"
                                type="email"
                                placeholder="you@example.com"
                                class="flex-1 bg-transparent px-3 text-xs text-white placeholder:text-white/30 focus:outline-none"
                            />
                            <button type="button" class="rounded-full bg-emerald-500/80 px-4 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-slate-900 transition hover:bg-emerald-400">
                                Join
                            </button>
                        </div>
                    </form>
                    <div class="flex items-center gap-4 text-lg text-white/50">
                        <a href="#" class="transition hover:text-white" aria-label="Instagram">?</a>
                        <a href="#" class="transition hover:text-white" aria-label="Twitter">?</a>
                        <a href="#" class="transition hover:text-white" aria-label="YouTube">?</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>
