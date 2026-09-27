<script setup>
import OutlinedField from '@/Components/OutlinedField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="text-center">
            <h1 class="text-[28px] font-semibold tracking-tight text-slate-900">Reset your password</h1>
            <p class="mt-2 text-sm text-slate-500">We'll email you a link to choose a new one</p>
        </div>

        <p v-if="status" class="mt-6 text-center text-sm text-emerald-600">{{ status }}</p>

        <form class="mt-8 space-y-5" autocomplete="off" @submit.prevent="submit">
            <div>
                <OutlinedField id="email" v-model="form.email" label="E-mail address" type="email" leading="envelope" autocomplete="off" required />
                <p v-if="form.errors.email" class="mt-2 text-sm text-[#f15b40]">{{ form.errors.email }}</p>
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-[#1f2933] py-2.5 text-sm font-medium text-white transition hover:bg-[#111827] disabled:opacity-60"
                :disabled="form.processing"
            >
                Send reset link
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            <Link :href="route('login')" class="font-medium text-[#3c8f6a]">Back to log in</Link>
        </p>
    </GuestLayout>
</template>
