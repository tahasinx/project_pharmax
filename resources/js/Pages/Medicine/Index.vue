<template>
    <Head title="Medicines" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Medicines
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('medicines.create')"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Medicine
                    </Link>
                    <button @click="showImportModal = true"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Import CSV
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filters -->
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 space-y-4 md:space-y-0">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search Medicines</label>
                                <input v-model="search"
                                       type="text"
                                       placeholder="Search medicines..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="mb-4 md:mb-0">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <SearchableSelect
                                    v-model="categoryFilter"
                                    :options="[{ value: '', label: 'All Categories' }, ...categoryOptions]"
                                    placeholder="Select Category"
                                />
                            </div>
                            <div class="mb-4 md:mb-0">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
                                <SearchableSelect
                                    v-model="manufacturerFilter"
                                    :options="[{ value: '', label: 'All Manufacturers' }, ...manufacturerOptions]"
                                    placeholder="Select Manufacturer"
                                />
                            </div>
                            <div class="flex items-end">
                                <button @click="clearFilters"
                                        class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medicines Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Medicine
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Manufacturer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Codes
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="medicine in filteredMedicines" :key="medicine.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div v-if="medicine.image" class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" :src="medicine.image" :alt="medicine.name">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ medicine.name }}</div>
                                                <div class="text-sm text-gray-500">{{ medicine.generic_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ medicine.category?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ medicine.manufacturer?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ medicine.price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            In Stock
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex space-x-1">
                                            <span v-if="medicine.qr_code_data"
                                                  class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                QR
                                            </span>
                                            <span v-if="medicine.barcode_data"
                                                  class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                BC
                                            </span>
                                            <span v-if="!medicine.qr_code_data && !medicine.barcode_data"
                                                  class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                                None
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="medicine.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ medicine.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <Link :href="route('medicines.show', medicine.id)"
                                                  class="text-blue-600 hover:text-blue-900">
                                                View
                                            </Link>
                                            <Link :href="route('medicines.edit', medicine.id)"
                                                  class="text-indigo-600 hover:text-indigo-900">
                                                Edit
                                            </Link>
                                            <Link :href="route('medicines.codes', medicine.id)"
                                                  class="text-green-600 hover:text-green-900">
                                                Codes
                                            </Link>
                                            <button @click="deleteMedicine(medicine.id)"
                                                    class="text-red-600 hover:text-red-900">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link v-if="medicines.prev_page_url"
                                  :href="medicines.prev_page_url"
                                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Previous
                            </Link>
                            <Link v-if="medicines.next_page_url"
                                  :href="medicines.next_page_url"
                                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ medicines.from }} to {{ medicines.to }} of {{ medicines.total }} results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link v-if="medicines.prev_page_url"
                                          :href="medicines.prev_page_url"
                                          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        Previous
                                    </Link>
                                    <Link v-if="medicines.next_page_url"
                                          :href="medicines.next_page_url"
                                          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                        Next
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 z-[9999]">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60" @click="closeImportModal"></div>
            <!-- Dialog -->
            <div class="absolute inset-0 flex items-center justify-center p-4 overflow-y-auto">
                <div class="w-full max-w-2xl bg-white rounded-lg shadow-2xl">
                    <div class="px-6 py-4 border-b flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Import Medicines from CSV</h3>
                        <button @click="closeImportModal" class="text-gray-500 hover:text-gray-700">✖</button>
                    </div>

                    <form @submit.prevent="importMedicines" enctype="multipart/form-data">
                        <div class="px-6 py-5 space-y-4">
                            <div class="text-sm text-gray-600">
                                Required columns (order not important):
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <span v-for="h in requiredHeaders" :key="h" class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-800">{{ h }}</span>
                                </div>
                            </div>

                            <!-- Dropzone -->
                            <label class="block">
                                <span class="block text-sm font-medium text-gray-700 mb-2">CSV File</span>
                                <div class="border-2 border-dashed rounded-md px-4 py-8 text-center cursor-pointer hover:border-blue-400"
                                     @dragover.prevent
                                     @drop.prevent="onDrop">
                                    <input ref="fileInput" name="file" type="file" accept=".csv,.xlsx,.xls" class="hidden" @change="onFileChange">
                                    <div v-if="!selectedFile" class="text-gray-500">
                                        <p>Drag and drop your .csv here, or
                                            <span class="text-blue-600 hover:underline" @click.prevent="triggerFile">browse</span>
                                        </p>
                                        <p class="text-xs mt-1">Max 5MB. Only .csv supported.</p>
                                    </div>
                                    <div v-else class="text-left">
                                        <p class="font-medium">Selected: {{ selectedFile.name }}</p>
                                        <p class="text-xs text-gray-500">{{ (selectedFile.size/1024).toFixed(1) }} KB</p>
                                    </div>
                                </div>
                            </label>

                            <!-- Validation -->
                            <div v-if="csvErrors.length" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li v-for="(err, idx) in csvErrors" :key="idx">{{ err }}</li>
                                </ul>
                            </div>

                            <div class="pt-2">
                                <div class="mb-3">
                                    <button type="button" @click="downloadSampleCsv" class="text-blue-600 hover:underline">
                                        Download sample.csv
                                    </button>
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            @click="closeImportModal"
                                            :disabled="isImporting"
                                            class="bg-gray-500 hover:bg-gray-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            :disabled="isImporting || !selectedFile || csvErrors.length > 0"
                                            class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                        <svg v-if="isImporting" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        <span>{{ isImporting ? 'Importing...' : 'Import' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    medicines: Object,
    categories: Array,
    manufacturers: Array
})

const search = ref('')
const categoryFilter = ref('')
const manufacturerFilter = ref('')
const showImportModal = ref(false)
const fileInput = ref(null)
const selectedFile = ref(null)
const csvErrors = ref([])
const isImporting = ref(false)
const requiredHeaders = [
    'name',
    'generic_name',
    'category',
    'manufacturer',
    'price'
]

// Formatted categories for SearchableSelect
const categoryOptions = computed(() => {
    let categories = [...props.categories]

    // Sort by name
    categories.sort((a, b) => a.name.localeCompare(b.name))

    return categories.map(category => ({
        value: category.id,
        label: category.name
    }))
})

// Formatted manufacturers for SearchableSelect
const manufacturerOptions = computed(() => {
    let manufacturers = [...props.manufacturers]

    // Sort by name
    manufacturers.sort((a, b) => a.name.localeCompare(b.name))

    return manufacturers.map(manufacturer => ({
        value: manufacturer.id,
        label: manufacturer.name
    }))
})

const filteredMedicines = computed(() => {
    let filtered = props.medicines.data

    if (search.value) {
        filtered = filtered.filter(medicine =>
            medicine.name.toLowerCase().includes(search.value.toLowerCase()) ||
            medicine.generic_name?.toLowerCase().includes(search.value.toLowerCase())
        )
    }

    if (categoryFilter.value) {
        filtered = filtered.filter(medicine => medicine.category_id == categoryFilter.value)
    }

    if (manufacturerFilter.value) {
        filtered = filtered.filter(medicine => medicine.manufacturer_id == manufacturerFilter.value)
    }

    return filtered
})

const clearFilters = () => {
    search.value = ''
    categoryFilter.value = ''
    manufacturerFilter.value = ''
}

const deleteMedicine = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('medicines.destroy', id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Medicine has been deleted.',
                        'success'
                    )
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Something went wrong while deleting.',
                        'error'
                    )
                }
            })
        }
    })
}

const triggerFile = () => fileInput.value?.click()

const closeImportModal = () => {
    showImportModal.value = false
    selectedFile.value = null
    csvErrors.value = []
    if (fileInput.value) fileInput.value.value = ''
}

const onDrop = async (e) => {
    const file = e.dataTransfer.files?.[0]
    if (file) await handleSelectedFile(file)
}

const onFileChange = async (e) => {
    const file = e.target.files?.[0]
    if (file) await handleSelectedFile(file)
}

const handleSelectedFile = async (file) => {
    selectedFile.value = file
    csvErrors.value = []
    const isCsv = /\.csv$/i.test(file.name)
    const isExcel = /\.(xlsx|xls)$/i.test(file.name)
    if (!isCsv && !isExcel) {
        csvErrors.value.push('Only .csv, .xlsx, .xls files are supported')
        return
    }
    // For Excel files, skip client-side header validation (server will parse)
    if (isExcel) return
    // Read first line to validate headers for CSV only
    try {
        const text = await file.text()
        const firstLine = text.split(/\r?\n/).find(l => l.trim().length)
        if (!firstLine) {
            csvErrors.value.push('CSV appears to be empty')
            return
        }
        const headers = firstLine.split(',').map(h => h.trim().replace(/^"|"$/g, '')).map(h => h.toLowerCase())
        for (const h of requiredHeaders) {
            if (!headers.includes(h)) {
                csvErrors.value.push(`Missing required column: ${h}`)
            }
        }
    } catch (e) {
        csvErrors.value.push('Unable to read the CSV file')
    }
}

const downloadSampleCsv = () => {
    const rows = [
        requiredHeaders.join(','),
        'Paracetamol,Acetaminophen,Pain Relief,ACME Pharma,3.50',
        'Amoxicillin,Amoxicillin,Antibiotics,HealthCorp,5.75'
    ]
    const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'sample_medicines.csv'
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
}

const importMedicines = () => {
    if (!selectedFile.value) return
    if (csvErrors.value.length > 0) return
    const formData = new FormData()
    // Include filename to preserve extension for Laravel's mimes validator
    formData.append('file', selectedFile.value, selectedFile.value.name)

    isImporting.value = true
    router.post(route('medicines.import'), formData, {
        onSuccess: () => {
            closeImportModal()
        },
        onError: (errors) => {
            // Surface backend validation errors inside the modal
            csvErrors.value = []
            if (errors && typeof errors === 'object') {
                if (errors.file) csvErrors.value.push(errors.file)
                for (const [key, val] of Object.entries(errors)) {
                    if (key !== 'file' && val) csvErrors.value.push(String(val))
                }
            }
        },
        onFinish: () => {
            isImporting.value = false
        },
        forceFormData: true
    })
}
</script>
