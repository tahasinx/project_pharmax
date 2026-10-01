<template>
    <div class="sheet-select relative" :class="{ 'is-open': showDropdown }">
        <input
            :value="displayText"
            type="text"
            :placeholder="placeholder"
            class="sheet-field w-full pr-8"
            @focus="showDropdown = true"
            @blur="handleBlur"
            @keydown="handleKeydown"
            @input="handleInput"
        />
        <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-[#8a8175]">
            <i class="bi bi-chevron-down text-[11px]" aria-hidden="true" />
        </span>
        <div v-show="showDropdown" class="sheet-select__menu">
            <p v-if="filteredOptions.length === 0" class="px-2 py-1.5 text-[12px] text-[#8a8175]">No matches</p>
            <button
                v-for="(option, index) in filteredOptions"
                :key="option.value"
                type="button"
                class="sheet-select__option"
                :class="{ 'is-on': hoveredIndex === index || selectedValue === option.value }"
                @mousedown.prevent="selectOption(option)"
                @mouseenter="hoveredIndex = index"
            >
                {{ option.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true
    },
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    searchable: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:modelValue'])

const showDropdown = ref(false)
const searchQuery = ref('')
const hoveredIndex = ref(-1)

const selectedValue = computed(() => props.modelValue)

const selectedOption = computed(() => {
    return props.options.find(option => option.value === selectedValue.value)
})

const displayText = computed(() => {
    if (showDropdown.value) {
        return searchQuery.value
    }
    if (selectedOption.value) {
        return selectedOption.value.label
    }
    return props.placeholder
})

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value) {
        return props.options
    }

    return props.options.filter(option =>
        option.label.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

const selectOption = (option) => {
    emit('update:modelValue', option.value)
    searchQuery.value = ''
    showDropdown.value = false
    hoveredIndex.value = -1
}

const handleInput = (event) => {
    searchQuery.value = event.target.value
    showDropdown.value = true
    hoveredIndex.value = -1
}

const handleBlur = () => {
    // Delay hiding to allow click events to fire
    setTimeout(() => {
        showDropdown.value = false
        searchQuery.value = ''
        hoveredIndex.value = -1
    }, 150)
}

const handleKeydown = (event) => {
    if (!showDropdown.value) {
        showDropdown.value = true
        return
    }

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault()
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
            if (props.searchable) {
                showDropdown.value = true
                hoveredIndex.value = -1
            }
    }
}

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
    if (newValue && selectedOption.value) {
        searchQuery.value = selectedOption.value.label
    }
}, { immediate: true })
</script>
