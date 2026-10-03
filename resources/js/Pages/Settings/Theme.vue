<template>
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Theme</h4>
        </template>

        <div v-if="flash.success" class="alert alert-success">{{ flash.success }}</div>

        <div class="settings-shell">
            <aside class="settings-shell-nav">
                <SettingsNav active="theme" />
            </aside>

            <form class="settings-shell-main" @submit.prevent="submit">
                <section class="theme-panel">
                    <header class="theme-panel-head">
                        <h5>Accent color</h5>
                        <p>Primary buttons, links, focus rings, and active menu states.</p>
                    </header>
                    <div class="theme-color-row">
                        <input v-model="form.primary" type="color" class="theme-color-input" aria-label="Accent color">
                        <div>
                            <div class="theme-color-hex">{{ form.primary }}</div>
                            <div class="theme-preview-bar" :style="{ background: form.primary }" />
                        </div>
                    </div>
                </section>

                <section class="theme-panel">
                    <header class="theme-panel-head">
                        <h5>Corner shape</h5>
                        <p>Controls radius on cards, inputs, and buttons across the app.</p>
                    </header>
                    <div class="theme-shape-grid">
                        <label
                            v-for="[value, label, hint] in shapes"
                            :key="value"
                            class="theme-shape"
                            :class="{ 'is-on': form.shape === value }"
                        >
                            <input v-model="form.shape" class="sr-only" type="radio" :value="value">
                            <span class="theme-shape-swatch" :class="`is-${value}`" aria-hidden="true" />
                            <span>
                                <span class="theme-shape-label">{{ label }}</span>
                                <span class="theme-shape-hint">{{ hint }}</span>
                            </span>
                        </label>
                    </div>
                </section>

                <section v-if="canManageTypography" class="theme-panel">
                    <header class="theme-panel-head">
                        <h5>Typography</h5>
                        <p>Base type for labels, tables, and forms. Managed by app admin only.</p>
                    </header>
                    <div class="theme-type-grid">
                        <label class="theme-field">
                            <span>Font name</span>
                            <input v-model="form.font_family" type="text" maxlength="60" required>
                        </label>
                        <label class="theme-field">
                            <span>Size (px)</span>
                            <input v-model.number="form.font_size" type="number" min="12" max="22" required>
                        </label>
                        <label class="theme-field">
                            <span>Weight</span>
                            <select v-model.number="form.font_weight">
                                <option v-for="weight in weights" :key="weight" :value="weight">{{ weight }}</option>
                            </select>
                        </label>
                        <label class="theme-field theme-field-wide">
                            <span>Font stylesheet URL</span>
                            <input v-model="form.font_href" type="url" placeholder="https://fonts.googleapis.com/css2?family=…">
                            <small>Google Fonts, jsDelivr, or cdnjs HTTPS links only.</small>
                        </label>
                    </div>
                </section>

                <section v-else class="theme-panel theme-type-locked">
                    <header class="theme-panel-head">
                        <h5>Typography</h5>
                        <p>Controlled by app admin and applied to every tenant screen.</p>
                    </header>
                    <div class="theme-locked-row">
                        <div>
                            <span class="theme-locked-label">Font</span>
                            <strong>{{ theme.font_family }} · {{ theme.font_size }}px · {{ theme.font_weight }}</strong>
                        </div>
                        <span class="theme-locked-badge">App admin only</span>
                    </div>
                </section>

                <section class="theme-panel theme-preview">
                    <header class="theme-panel-head">
                        <h5>Live preview</h5>
                        <p>Shows how accent and radius will feel before you save.</p>
                    </header>
                    <div class="theme-preview-card" :style="previewStyle">
                        <div class="theme-preview-title">Sample card</div>
                        <p class="theme-preview-copy">Primary actions and focus states follow the accent.</p>
                        <div class="theme-preview-actions">
                            <button type="button" class="btn btn-primary btn-sm" :style="previewButtonStyle">Primary</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" :style="{ borderRadius: previewRadius }">Secondary</button>
                        </div>
                    </div>
                </section>

                <div class="theme-actions">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save theme' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SettingsNav from '@/Components/SettingsNav.vue'

const props = defineProps({
    theme: Object,
    canManageTypography: {
        type: Boolean,
        default: false,
    },
})

const page = usePage()
const flash = computed(() => page.props.flash || {})

const shapes = [
    ['rounded', 'Rounded', 'Soft corners'],
    ['default', 'Default', 'Subtle corners'],
    ['flat', 'Flat', 'Square edges'],
]
const weights = [300, 400, 500, 600, 700, 800, 900]

const form = useForm({
    primary: props.theme.primary,
    shape: props.theme.shape,
    font_family: props.theme.font_family,
    font_href: props.theme.font_href || '',
    font_size: props.theme.font_size,
    font_weight: props.theme.font_weight,
})

const previewRadius = computed(() => {
    if (form.shape === 'flat') return '0px'
    if (form.shape === 'default') return '4px'
    return '10px'
})

const previewStyle = computed(() => ({
    borderRadius: previewRadius.value,
    borderColor: form.primary,
}))

const previewButtonStyle = computed(() => ({
    background: form.primary,
    borderColor: form.primary,
    borderRadius: previewRadius.value,
}))

const submit = () => {
    const payload = props.canManageTypography
        ? undefined
        : { primary: form.primary, shape: form.shape }

    if (payload) {
        form.transform(() => payload).put(route('settings.theme.update'), {
            preserveScroll: true,
            onFinish: () => form.transform((data) => data),
        })
        return
    }

    form.put(route('settings.theme.update'), { preserveScroll: true })
}
</script>

<style scoped>
.settings-shell {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 1rem;
    align-items: start;
}

.settings-shell-main {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.theme-panel {
    background: #fff;
    border: 1px solid #e6e8ee;
    border-radius: 0.65rem;
    padding: 1rem 1.15rem 1.2rem;
}

.theme-panel-head {
    margin-bottom: 0.9rem;
    padding-bottom: 0.65rem;
    border-bottom: 1px solid #eef0f5;
}

.theme-panel-head h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #343747;
}

.theme-panel-head p {
    margin: 0.25rem 0 0;
    font-size: 0.8rem;
    color: #74788d;
}

.theme-color-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.theme-color-input {
    width: 3.25rem;
    height: 2.5rem;
    padding: 0.15rem;
    border: 1px solid #ced4da;
    border-radius: 0.4rem;
    background: #fff;
    cursor: pointer;
}

.theme-color-hex {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
}

.theme-preview-bar {
    margin-top: 0.35rem;
    width: 8rem;
    height: 0.35rem;
    border-radius: 999px;
}

.theme-shape-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}

.theme-shape {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.75rem;
    border: 1px solid #e6e8ee;
    border-radius: 0.55rem;
    cursor: pointer;
    background: #f8f9fc;
}

.theme-shape.is-on {
    border-color: var(--pf-accent, #5156be);
    background: color-mix(in srgb, var(--pf-accent, #5156be) 8%, #fff);
    box-shadow: 0 0 0 1px color-mix(in srgb, var(--pf-accent, #5156be) 35%, transparent);
}

.theme-shape-swatch {
    width: 2.4rem;
    height: 1.6rem;
    background: #fff;
    border: 1px solid #ced4da;
    flex-shrink: 0;
}

.theme-shape-swatch.is-rounded {
    border-radius: 10px;
}

.theme-shape-swatch.is-default {
    border-radius: 4px;
}

.theme-shape-swatch.is-flat {
    border-radius: 0;
}

.theme-shape-label {
    display: block;
    font-size: 0.86rem;
    font-weight: 600;
    color: #343747;
}

.theme-shape-hint {
    display: block;
    font-size: 0.72rem;
    color: #74788d;
}

.theme-type-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}

.theme-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
}

.theme-field-wide {
    grid-column: 1 / -1;
}

.theme-field input,
.theme-field select {
    min-height: 2.15rem;
    border: 1px solid #ced4da;
    border-radius: 0.4rem;
    padding: 0.4rem 0.65rem;
    font-weight: 400;
    color: #495057;
    background: #fff;
}

.theme-field small {
    font-weight: 400;
    color: #74788d;
}

.theme-preview-card {
    border: 1px solid #e6e8ee;
    background: #f8f9fc;
    padding: 1rem;
}

.theme-preview-title {
    font-weight: 700;
    color: #343747;
}

.theme-preview-copy {
    margin: 0.35rem 0 0.85rem;
    font-size: 0.85rem;
    color: #74788d;
}

.theme-preview-actions {
    display: flex;
    gap: 0.5rem;
}

.theme-locked-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 0.85rem;
    background: #f8f9fc;
    border: 1px dashed #dfe3ea;
    border-radius: 0.5rem;
}

.theme-locked-label {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: #74788d;
}

.theme-locked-badge {
    flex-shrink: 0;
    border-radius: 999px;
    border: 1px solid #e6e8ee;
    background: #fff;
    padding: 0.25rem 0.65rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #74788d;
}

.theme-actions {
    display: flex;
    justify-content: flex-end;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

@media (max-width: 991.98px) {
    .settings-shell {
        grid-template-columns: 1fr;
    }

    .theme-shape-grid,
    .theme-type-grid {
        grid-template-columns: 1fr;
    }
}
</style>
