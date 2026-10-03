<template>
    <div class="ss" :class="{ 'is-open': showDropdown, 'is-invalid': invalid, 'is-disabled': disabled }">
        <input
            :value="displayText"
            type="text"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            class="ss-input"
            autocomplete="off"
            role="combobox"
            :aria-expanded="showDropdown"
            :aria-invalid="invalid ? 'true' : 'false'"
            @focus="open"
            @blur="handleBlur"
            @keydown="handleKeydown"
            @input="handleInput"
        >
        <span class="ss-caret" aria-hidden="true">
            <i class="bi bi-chevron-down" />
        </span>
        <div v-show="showDropdown" class="ss-menu" role="listbox">
            <p v-if="filteredOptions.length === 0" class="ss-empty">No matches</p>
            <button
                v-for="(option, index) in filteredOptions"
                :key="String(option.value)"
                type="button"
                class="ss-option"
                :class="{ 'is-on': hoveredIndex === index || isSelected(option.value) }"
                role="option"
                :aria-selected="isSelected(option.value)"
                @mousedown.prevent="selectOption(option)"
                @mouseenter="hoveredIndex = index"
            >
                {{ option.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, required: true },
    placeholder: { type: String, default: 'Select…' },
    searchable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

const showDropdown = ref(false)
const searchQuery = ref('')
const hoveredIndex = ref(-1)

const same = (a, b) => String(a ?? '') === String(b ?? '')

const isSelected = (value) => same(value, props.modelValue)

const selectedOption = computed(() => props.options.find((option) => isSelected(option.value)))

const displayText = computed(() => {
    if (showDropdown.value) {
        return searchQuery.value
    }
    return selectedOption.value?.label || ''
})

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value.trim()) {
        return props.options
    }
    const q = searchQuery.value.trim().toLowerCase()
    return props.options.filter((option) => String(option.label).toLowerCase().includes(q))
})

const open = () => {
    if (props.disabled) return
    showDropdown.value = true
    searchQuery.value = ''
    hoveredIndex.value = Math.max(0, filteredOptions.value.findIndex((option) => isSelected(option.value)))
}

const selectOption = (option) => {
    emit('update:modelValue', option.value)
    emit('change', option)
    searchQuery.value = ''
    showDropdown.value = false
    hoveredIndex.value = -1
}

const handleInput = (event) => {
    searchQuery.value = event.target.value
    showDropdown.value = true
    hoveredIndex.value = filteredOptions.value.length ? 0 : -1
}

const handleBlur = () => {
    window.setTimeout(() => {
        showDropdown.value = false
        searchQuery.value = ''
        hoveredIndex.value = -1
    }, 120)
}

const handleKeydown = async (event) => {
    if (props.disabled) return

    if (!showDropdown.value && ['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
        event.preventDefault()
        open()
        await nextTick()
        return
    }

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault()
            if (!filteredOptions.value.length) return
            hoveredIndex.value = Math.min(hoveredIndex.value + 1, filteredOptions.value.length - 1)
            break
        case 'ArrowUp':
            event.preventDefault()
            hoveredIndex.value = Math.max(hoveredIndex.value - 1, 0)
            break
        case 'Enter':
            event.preventDefault()
            if (hoveredIndex.value >= 0 && filteredOptions.value[hoveredIndex.value]) {
                selectOption(filteredOptions.value[hoveredIndex.value])
            }
            break
        case 'Escape':
            showDropdown.value = false
            searchQuery.value = ''
            hoveredIndex.value = -1
            break
        default:
            break
    }
}

watch(() => props.modelValue, () => {
    if (!showDropdown.value) {
        searchQuery.value = ''
    }
})
</script>

<style scoped>
.ss {
    position: relative;
    width: 100%;
}

/* Match Medicine form `.field` exactly */
.ss-input {
    width: 100%;
    box-sizing: border-box;
    height: 2rem;
    min-height: 2rem;
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px));
    border: 1px solid var(--shell-panel-border, #ced4da);
    background: var(--shell-panel-surface, #fff);
    padding: 0 1.8rem 0 0.55rem;
    font-family: inherit;
    font-size: 0.82rem;
    font-weight: 400;
    line-height: calc(2rem - 2px);
    color: var(--shell-panel-text, #343747);
    box-shadow: none;
    appearance: none;
    -webkit-appearance: none;
}

.ss-input::placeholder {
    color: var(--shell-panel-muted, #adb5bd);
    font-size: 0.82rem;
    font-weight: 400;
    opacity: 1;
}

.ss-input:focus {
    outline: none;
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.ss-input:disabled {
    background: color-mix(in srgb, var(--shell-panel-bg, #eef0f5) 85%, #fff);
    color: var(--shell-panel-muted, #74788d);
    cursor: not-allowed;
}

.ss-caret {
    position: absolute;
    top: 0;
    right: 0.55rem;
    bottom: 0;
    display: inline-flex;
    align-items: center;
    pointer-events: none;
    color: var(--shell-panel-muted, #74788d);
    font-size: 0.7rem;
    transition: transform 0.15s ease;
}

.ss.is-open .ss-caret {
    transform: rotate(180deg);
}

.ss.is-open .ss-input {
    border-color: var(--pf-accent, #5156be);
    box-shadow: 0 0 0 0.12rem rgba(var(--pf-accent-rgb, 81, 86, 190), 0.18);
}

.ss-menu {
    position: absolute;
    z-index: 1080;
    top: calc(100% + 0.2rem);
    left: 0;
    right: 0;
    max-height: 14rem;
    overflow: auto;
    border: 1px solid var(--shell-panel-border, #ced4da);
    border-radius: var(--pf-radius, var(--bs-border-radius, 10px));
    background: var(--shell-panel-surface, #fff);
    box-shadow: 0 0.45rem 1rem rgba(16, 24, 40, 0.1);
    padding: 0.2rem;
}

.ss-empty {
    margin: 0;
    padding: 0.35rem 0.55rem;
    font-size: 0.82rem;
    color: var(--shell-panel-muted, #74788d);
}

.ss-option {
    display: block;
    width: 100%;
    border: 0;
    border-radius: var(--pf-radius, 0.35rem);
    background: transparent;
    text-align: left;
    padding: 0.32rem 0.55rem;
    font-family: inherit;
    font-size: 0.82rem;
    font-weight: 400;
    line-height: 1.3;
    color: var(--shell-panel-text, #343747);
    cursor: pointer;
}

.ss-option:hover,
.ss-option.is-on {
    background: rgba(var(--pf-accent-rgb, 81, 86, 190), 0.1);
    color: var(--pf-accent, #5156be);
}

.ss.is-invalid .ss-input,
.ss.is-invalid .ss-input:focus {
    border-color: #f46a6a;
    box-shadow: 0 0 0 0.12rem rgba(244, 106, 106, 0.18);
}
</style>
