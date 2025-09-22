<template>
    <div class="relative" :style="{ zIndex: showDropdown ? 10000 : 1000 }">
        <div class="relative">
            <input
                :value="displayText"
                @focus="showDropdown = true"
                @blur="handleBlur"
                @keydown="handleKeydown"
                @input="handleInput"
                type="text"
                :placeholder="placeholder"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>

        <div
            v-show="showDropdown"
            class="absolute w-full mt-1 bg-white border border-gray-300 rounded-md shadow-xl max-h-60 overflow-auto"
            style="position: absolute; z-index: 10001; top: 100%; left: 0; right: 0;"
        >
            <div v-if="filteredOptions.length === 0" class="px-3 py-2 text-gray-500 text-sm">
                No options found
            </div>
            <div
                v-for="(option, index) in filteredOptions"
                :key="option.value"
                @click="selectOption(option)"
                @mouseenter="hoveredIndex = index"
                :class="[
                    'px-3 py-2 text-sm cursor-pointer',
                    hoveredIndex === index ? 'bg-blue-100 text-blue-900' : 'hover:bg-gray-100',
                    selectedValue === option.value ? 'bg-blue-50 text-blue-900' : ''
                ]"
            >
                {{ option.label }}
            </div>
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
