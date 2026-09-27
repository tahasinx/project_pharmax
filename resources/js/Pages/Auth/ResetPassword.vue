<script setup>
import OutlinedField from '@/Components/OutlinedField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="text-center">
            <h1 class="text-[28px] font-semibold tracking-tight text-slate-900">Choose a new password</h1>
            <p class="mt-2 text-sm text-slate-500">Use the email on this account</p>
        </div>

        <form class="mt-8 space-y-5" autocomplete="off" @submit.prevent="submit">
            <div>
                <OutlinedField id="email" v-model="form.email" label="E-mail address" type="email" leading="envelope" autocomplete="off" required />
                <p v-if="form.errors.email" class="mt-2 text-sm text-[#f15b40]">{{ form.errors.email }}</p>
            </div>

            <div>
                <OutlinedField id="password" v-model="form.password" label="Password" type="password" leading="lock" autocomplete="off" required />
                <p v-if="form.errors.password" class="mt-2 text-sm text-[#f15b40]">{{ form.errors.password }}</p>
            </div>

            <div>
                <OutlinedField id="password_confirmation" v-model="form.password_confirmation" label="Confirm password" type="password" leading="lock" autocomplete="off" required />
                <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-[#f15b40]">{{ form.errors.password_confirmation }}</p>
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-[#1f2933] py-2.5 text-sm font-medium text-white transition hover:bg-[#111827] disabled:opacity-60"
                :disabled="form.processing"
            >
                Save password
            </button>
        </form>
    </GuestLayout>
</template>
