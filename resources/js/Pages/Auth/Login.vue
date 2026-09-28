<script setup>
import OutlinedField from '@/Components/OutlinedField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useToastNotifications } from '@/Composables/useToast';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const phase = ref('idle');
const { showSuccess, showError, clear: clearToasts } = useToastNotifications();

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

onMounted(() => {
    if (props.status) {
        showSuccess(props.status);
    }
});

const submit = async () => {
    phase.value = 'loading';
    form.clearErrors();
    clearToasts();

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await window.axios.post(route('login'), {
            email: form.email,
            password: form.password,
            remember: form.remember,
        }, {
            headers: {
                'X-CSRF-TOKEN': token,
                Accept: 'application/json',
            },
        });

        phase.value = 'success';
        await new Promise((resolve) => setTimeout(resolve, prefersReducedMotion() ? 120 : 1000));
        window.location.href = response.request?.responseURL || route('dashboard');
    } catch (error) {
        phase.value = 'idle';
        form.reset('password');
        const message = error.response?.status === 429
            ? 'Too many failed login attempts. Please try again later.'
            : 'Incorrect email or password. Please check and try again';
        form.setError({ email: message, password: message });
        showError(message);
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="text-center">
            <h1 class="text-[28px] font-semibold tracking-tight text-slate-900">Welcome back!</h1>
            <p class="mt-2 text-sm text-slate-500">Log in to your Epharma account</p>
        </div>

        <form class="mt-8 space-y-5" autocomplete="off" @submit.prevent="submit">
            <OutlinedField id="email" v-model="form.email" label="E-mail address" type="email" leading="envelope-at" autocomplete="off" required :invalid="Boolean(form.errors.email)" />

            <div>
                <OutlinedField id="password" v-model="form.password" label="Password" leading="shield-lock" :type="showPassword ? 'text' : 'password'" autocomplete="off" required :invalid="Boolean(form.errors.password)">
                    <button type="button" class="text-slate-400" @click="showPassword = !showPassword" aria-label="Show password">
                        <svg v-if="!showPassword" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M3 3l18 18M10.5 10.7A2.5 2.5 0 0013.3 13.5M9.9 5.2A10.8 10.8 0 0112 5c6.5 0 10 7 10 7a18.4 18.4 0 01-3.2 4.2M6.1 6.1C3.7 7.8 2 12 2 12s3.5 6 10 6c1.5 0 2.9-.3 4.1-.8" />
                        </svg>
                        <svg v-else class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" />
                            <circle cx="12" cy="12" r="2.5" />
                        </svg>
                    </button>
                </OutlinedField>
                <p class="mt-2 text-sm text-slate-600">
                    Forgot your password?
                    <Link v-if="canResetPassword" :href="route('password.request')" class="font-medium text-[#3c8f6a]">Reset</Link>
                </p>
            </div>

            <button
                type="submit"
                class="auth-submit flex w-full items-center justify-center rounded-md bg-[#1f2933] py-2.5 text-sm font-medium text-white hover:bg-[#111827]"
                :class="{
                    'auth-submit--loading': phase === 'loading',
                    'auth-submit--success': phase === 'success',
                }"
                :disabled="phase !== 'idle'"
                :aria-busy="phase !== 'idle'"
            >
                <span class="auth-submit__layer auth-submit__layer--idle">
                    <span>Log in</span>
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                    </svg>
                </span>
                <span class="auth-submit__layer auth-submit__layer--load" aria-hidden="true">
                    <span class="auth-submit__spinner" />
                </span>
                <span class="auth-submit__layer auth-submit__layer--success" aria-hidden="true">
                    <svg class="auth-submit__svg" viewBox="0 0 24 24" width="22" height="22">
                        <circle class="auth-submit__ring" cx="12" cy="12" r="9" />
                        <polyline class="auth-submit__check" points="8,12.5 11,15.5 16,9" />
                        <path class="auth-submit__arrow" d="M9 12h7M14 9l3 3-3 3" />
                    </svg>
                </span>
            </button>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-submit {
    justify-content: center;
    overflow: hidden;
    position: relative;
}

.auth-submit__layer {
    align-items: center;
    display: inline-flex;
    gap: .35rem;
    justify-content: center;
    transition: opacity .18s ease;
}

.auth-submit__layer--idle {
    opacity: 1;
    visibility: visible;
}

.auth-submit__layer--load,
.auth-submit__layer--success {
    inset: 0;
    opacity: 0;
    pointer-events: none;
    position: absolute;
    visibility: hidden;
}

.auth-submit--loading .auth-submit__layer--idle,
.auth-submit--success .auth-submit__layer--idle {
    opacity: 0;
    pointer-events: none;
    visibility: hidden;
}

.auth-submit--loading .auth-submit__layer--load {
    opacity: 1;
    visibility: visible;
}

.auth-submit--success .auth-submit__layer--load {
    opacity: 0;
    visibility: hidden;
}

.auth-submit--success .auth-submit__layer--success {
    opacity: 1;
    visibility: visible;
}

.auth-submit__spinner {
    animation: auth-submit-spin .65s linear infinite;
    border: 2px solid rgba(255, 255, 255, .28);
    border-radius: 50%;
    border-top-color: #fff;
    height: 1rem;
    width: 1rem;
}

.auth-submit__svg {
    display: block;
    overflow: visible;
}

.auth-submit__ring,
.auth-submit__check,
.auth-submit__arrow {
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.75;
}

.auth-submit__ring {
    stroke-dasharray: 57;
    stroke-dashoffset: 57;
}

.auth-submit__check {
    opacity: 0;
    stroke-dasharray: 14;
    stroke-dashoffset: 14;
    transform-box: fill-box;
    transform-origin: center;
}

.auth-submit__arrow {
    opacity: 0;
    stroke-dasharray: 12;
    stroke-dashoffset: 12;
    transform-box: fill-box;
    transform-origin: center;
}

.auth-submit--success .auth-submit__ring {
    animation: auth-submit-ring .28s ease forwards;
}

.auth-submit--success .auth-submit__check {
    animation:
        auth-submit-check-draw .22s ease .24s forwards,
        auth-submit-check-out .16s ease .48s forwards;
}

.auth-submit--success .auth-submit__arrow {
    animation:
        auth-submit-arrow-in .16s ease .48s forwards,
        auth-submit-arrow-shoot .36s cubic-bezier(.4, 0, .2, 1) .62s forwards;
}

.auth-submit--success .auth-submit__svg {
    animation: auth-submit-svg-shoot .36s cubic-bezier(.4, 0, .2, 1) .62s forwards;
}

@keyframes auth-submit-spin {
    to { transform: rotate(360deg); }
}

@keyframes auth-submit-ring {
    to { stroke-dashoffset: 0; }
}

@keyframes auth-submit-check-draw {
    from { opacity: 1; }
    to { opacity: 1; stroke-dashoffset: 0; }
}

@keyframes auth-submit-check-out {
    to { opacity: 0; transform: scale(.82); }
}

@keyframes auth-submit-arrow-in {
    from { opacity: 0; stroke-dashoffset: 12; transform: scale(.82); }
    to { opacity: 1; stroke-dashoffset: 0; transform: scale(1); }
}

@keyframes auth-submit-arrow-shoot {
    to { opacity: 0; transform: translateX(12px); }
}

@keyframes auth-submit-svg-shoot {
    to { opacity: 0; transform: translateX(10px); }
}

@media (prefers-reduced-motion: reduce) {
    .auth-submit__spinner {
        animation: none;
        border-top-color: rgba(255, 255, 255, .55);
    }

    .auth-submit--success .auth-submit__ring,
    .auth-submit--success .auth-submit__check,
    .auth-submit--success .auth-submit__arrow,
    .auth-submit--success .auth-submit__svg {
        animation: none;
    }

    .auth-submit--success .auth-submit__ring,
    .auth-submit--success .auth-submit__check,
    .auth-submit--success .auth-submit__arrow {
        stroke-dashoffset: 0;
    }

    .auth-submit--success .auth-submit__check {
        opacity: 0;
    }

    .auth-submit--success .auth-submit__arrow {
        opacity: 1;
        transform: none;
    }
}
</style>
