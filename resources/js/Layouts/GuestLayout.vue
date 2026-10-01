<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const logo = computed(() => usePage().props.app?.logo || '');
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-[#f4faf6] px-4 py-8">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-[0_20px_60px_rgba(30,70,50,0.08)] md:grid-cols-2">
            <section class="hidden flex-col items-center justify-center bg-[#e6fdec] px-8 py-10 text-center md:flex">
                <img src="/images/auth-study.png" alt="" class="w-full max-w-md bg-[#e6fdec]" />
                <h2 class="mt-2 text-[26px] font-semibold leading-none tracking-tight">
                    <span class="text-[#1e3a2f]">Eph</span><span class="text-[#3d8c6e]">arma</span>
                </h2>
                <p class="mt-2 max-w-xs text-sm leading-6 text-[#6b7280]">
                    The pharmacy counter for stock, sales, and prescriptions.
                </p>
            </section>

            <section class="flex items-center bg-white px-8 py-10 sm:px-12">
                <div class="w-full">
                    <Link href="/" class="mb-8 flex justify-center" aria-label="Epharma">
                        <img v-if="logo" :src="logo" alt="" class="h-12 w-12 object-contain">
                        <ApplicationLogo v-else class="h-12 w-12 fill-current text-[#141a17]" />
                    </Link>
                    <slot />
                </div>
            </section>
        </div>
    </div>
</template>
