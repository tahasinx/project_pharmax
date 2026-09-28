<script setup>
import { computed, ref, useSlots } from 'vue';

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    autocomplete: { type: String, default: 'off' },
    required: { type: Boolean, default: false },
    autofocus: { type: Boolean, default: false },
    leading: { type: String, default: '' },
    invalid: { type: Boolean, default: false },
});

const model = defineModel({ type: String, default: '' });
const slots = useSlots();
const focused = ref(false);
const floated = computed(() => focused.value || String(model.value || '').length > 0);
</script>

<template>
    <div class="relative">
        <input
            :id="id"
            v-model="model"
            :type="type"
            :required="required"
            :autofocus="autofocus"
            :autocomplete="autocomplete"
            :aria-invalid="invalid ? 'true' : 'false'"
            class="block w-full rounded border bg-transparent px-3 py-2 text-sm leading-5 text-slate-900 outline-none transition"
            :class="[
                slots.default ? 'pr-10' : '',
                floated && leading ? 'pl-10' : '',
                invalid ? 'is-invalid' : '',
                invalid
                    ? 'border-[#dc3545]'
                    : (focused ? 'border-[color:var(--pf-accent)]' : 'border-slate-300'),
            ]"
            @focus="focused = true"
            @blur="focused = false"
        />
        <span
            v-if="floated && leading"
            class="leading-icon pointer-events-none absolute left-3 top-1/2 flex h-4 -translate-y-1/2 items-center"
            :class="invalid ? 'text-[#dc3545]' : (focused ? 'text-[color:var(--pf-accent)]' : 'text-slate-500')"
        >
            <svg v-if="leading === 'envelope'" class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z" />
            </svg>
            <svg v-else-if="leading === 'lock'" class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1" />
            </svg>
            <i v-else-if="leading === 'envelope-at'" class="bi bi-envelope-at text-[16px] leading-none" aria-hidden="true" />
            <i v-else-if="leading === 'shield-lock'" class="bi bi-shield-lock text-[16px] leading-none" aria-hidden="true" />
        </span>
        <label
            :for="id"
            class="pointer-events-none absolute left-2.5 origin-left bg-white px-1 transition-all"
            :class="floated
                ? ['top-0 -translate-y-1/2 text-xs', invalid ? 'text-[#dc3545]' : (focused ? 'text-[color:var(--pf-accent)]' : 'text-slate-500')]
                : ['top-1/2 -translate-y-1/2 text-sm', invalid ? 'text-[#dc3545]' : 'text-slate-500']"
        >
            {{ label }}
        </label>
        <div class="absolute right-0 top-1/2 flex -translate-y-1/2 items-center pr-3 text-slate-400">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.leading-icon :deep(.bi)::before {
    line-height: 1;
    vertical-align: 0;
}
</style>
