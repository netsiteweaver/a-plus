<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <header class="border-b border-white/10 bg-slate-900/60 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-6">
                <div class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">ME</span>
                    <span>Mega Electronics</span>
                </div>
                <nav class="hidden items-center gap-4 text-sm font-medium md:flex">
                    <a href="#" class="text-white/70 transition-colors hover:text-white">Home</a>
                    <a href="#" class="text-white/70 transition-colors hover:text-white">Catalog</a>
                    <a href="#" class="text-white/70 transition-colors hover:text-white">Deals</a>
                    <a href="#" class="text-white/70 transition-colors hover:text-white">Support</a>
                </nav>
                <button class="rounded-full border border-white/20 px-5 py-2 text-sm font-medium text-white/80 transition hover:border-emerald-400/40 hover:text-white">
                    View Roadmap
                </button>
            </div>
        </header>

        <main class="mx-auto flex max-w-6xl flex-col gap-12 px-6 py-16">
            <section class="grid gap-8 md:grid-cols-5">
                <div class="md:col-span-3">
                    <h1 class="text-4xl font-bold tracking-tight text-white md:text-5xl">
                        Laravel + Vue commerce stack foundation
                    </h1>
                    <p class="mt-4 text-base text-white/70 md:text-lg">
                        This placeholder view confirms the SPA build pipeline (Laravel, Vite, Tailwind, Vue) is wired up. Replace with the
                        real mega-electronics experience as we implement catalog, PDP, and checkout flows.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4 text-sm text-white/70">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Vite hot reload ready
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1">
                            <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                            Tailwind utility tokens
                        </span>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-emerald-500/10 via-slate-900 to-slate-950 p-6 md:col-span-2">
                    <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="relative flex flex-col gap-4 text-sm text-white/70">
                        <h2 class="text-lg font-semibold text-white">Stack checklist</h2>
                        <ul class="flex flex-col gap-3">
                            <li class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Laravel 12 backend
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Vue 3 SPA via Vite
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Tailwind CSS 4 design tokens
                            </li>
                            <li class="flex items-center gap-2 text-white/60">
                                <span class="h-2 w-2 rounded-full bg-white/30"></span>
                                Pinia, routing, and API bindings coming next
                            </li>
                        </ul>
                        <p class="rounded-lg border border-white/10 bg-slate-900/50 p-4 text-xs leading-relaxed text-white/60">
                            Keep this placeholder until real feature work replaces it. It doubles as a smoke test to ensure the dev server builds
                            and the Blade entry point mounts the Vue app successfully.
                        </p>
                    </div>
                </div>
            </section>
            <section class="grid gap-6 md:grid-cols-3">
                <article class="rounded-2xl border border-white/10 bg-slate-900/50 p-6">
                    <h3 class="text-base font-semibold text-white">1. Backend foundation</h3>
                    <p class="mt-3 text-sm text-white/60">
                        Configure environment, database, and base modules for catalog, inventory, and RBAC.
                    </p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/50 p-6">
                    <h3 class="text-base font-semibold text-white">2. Frontend shell</h3>
                    <p class="mt-3 text-sm text-white/60">
                        Translate Woodmart structures into Tailwind + Vue components with responsive breakpoints.
                    </p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/50 p-6">
                    <h3 class="text-base font-semibold text-white">3. Commerce flows</h3>
                    <p class="mt-3 text-sm text-white/60">
                        Implement catalog browse, PDP, cart, checkout, and post-purchase experiences.
                    </p>
                </article>
            </section>
        </main>

        <footer class="border-t border-white/10 bg-slate-900/60">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-6 text-sm text-white/50 md:flex-row md:items-center md:justify-between">
                <p>&copy; {{ currentYear }} Mega Electronics Blueprint.</p>
                <div class="flex flex-wrap items-center gap-3">
                    <span>Laravel 12</span>
                    <span>Vue 3</span>
                    <span>Tailwind CSS</span>
                    <span>Vite</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
const currentYear = new Date().getFullYear();
</script>
