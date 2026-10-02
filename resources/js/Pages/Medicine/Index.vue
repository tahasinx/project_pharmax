<template>
    <Head title="Medicines" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Medicines</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <Link :href="route('medicines.create')" class="btn btn-primary btn-sm">Add Medicine</Link>
                <button type="button" class="btn btn-success btn-sm" @click="showImportModal = true">Import CSV</button>
                <button type="button" class="btn btn-primary btn-sm" @click="openApiModal">Search Medicine [API]</button>
            </div>
        </template>

        <LunaTable title="Medicines" :pagination="medicines">
<table class="table table-striped table-hover mb-0">
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
                                                <div>
                                                    <button v-if="medicine.medex_id && medicine.medex_name"
                                                            @click="openMedexDetailsForMedicine(medicine)"
                                                            class="text-sm font-semibold text-blue-700 hover:underline">
                                                        {{ medicine.name }}
                                                    </button>
                                                    <div v-else class="text-sm font-medium text-gray-900">{{ medicine.name }}</div>
                                                </div>
                                                <div class="text-sm text-gray-500">{{ medicine.generic_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-[180px]">
                                        <span class="block truncate" :title="medicine.category?.name">{{ medicine.category?.name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 max-w-[200px]">
                                        <span class="block truncate" :title="medicine.manufacturer?.name">{{ medicine.manufacturer?.name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ medicine.price }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            In Stock
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="medicine.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ medicine.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <div class="dt-actions">
                                            <Link :href="route('medicines.show', medicine.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-eye"></i></Link>
                                            <Link :href="route('medicines.edit', medicine.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                            <Link :href="route('medicines.codes', medicine.id)" class="btn btn-sm btn-icon btn-soft-success" title="Codes"><i class="bi bi-upc"></i></Link>
                                            <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteMedicine(medicine.id)"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
</LunaTable>

        <!-- API Modal -->
        <div v-if="showApiModal" class="fixed inset-0 z-[9999]">
            <div class="absolute inset-0 bg-black/50" @click="closeApiModal"></div>
            <div class="absolute inset-0 flex items-stretch p-3">
                <div class="card d-flex flex-column w-100" style="max-height: 100%;">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">Medicine reference</h5>
                            <div class="text-muted font-size-12">Search a product, or import brands and manufacturers from MedEx.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light" @click="closeApiModal">Close</button>
                    </div>
                    <div class="px-3 pt-3">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn" :class="apiTab === 'medicine' ? 'btn-primary' : 'btn-light'" @click="apiTab = 'medicine'">Medicines</button>
                            <button type="button" class="btn" :class="apiTab === 'brands' ? 'btn-primary' : 'btn-light'" @click="apiTab = 'brands'">Brands</button>
                            <button type="button" class="btn" :class="apiTab === 'companies' ? 'btn-primary' : 'btn-light'" @click="apiTab = 'companies'">Manufacturers</button>
                        </div>
                    </div>
                    <div class="card-body overflow-auto">
                        <div v-if="apiTab === 'medicine'" class="row g-3">
                            <div class="col-lg-4">
                                <input v-model="apiSearch" class="form-control" placeholder="Brand, generic, or strength" @input="debouncedApiSearch">
                                <div v-if="apiSearchLoading" class="text-muted font-size-12 mt-2">Searching MedEx...</div>
                                <div v-if="apiResults.length" class="list-group mt-2">
                                    <button v-for="res in apiResults" :key="res.link" type="button" class="list-group-item list-group-item-action d-flex gap-2 align-items-center" @click="selectApiResult(res)">
                                        <img v-if="res.img" :src="res.img" alt="" width="28" height="28">
                                        <span>
                                            <span class="d-block fw-medium">{{ res.name }}</span>
                                            <span class="d-block text-muted font-size-12">{{ res.form }} · {{ res.strength }}</span>
                                        </span>
                                    </button>
                                </div>
                                <div v-else-if="apiSearch && !apiSearchLoading" class="text-muted font-size-12 mt-2">No matching products.</div>
                            </div>
                            <div class="col-lg-8">
                                <div v-if="apiDetailsLoading" class="text-muted">Loading the product record...</div>
                                <div v-else-if="apiDetails">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h4 class="mb-1">{{ apiDetails.name }}</h4>
                                            <div class="text-muted">{{ apiDetails.generic }} · {{ apiDetails.form }} · {{ apiDetails.strength }}</div>
                                            <div class="text-muted font-size-12">{{ apiDetails.manufacturer }}</div>
                                        </div>
                                        <button v-if="!apiDetails.exists" type="button" class="btn btn-primary" :disabled="savingApi" @click="saveFromApi">{{ savingApi ? 'Saving...' : 'Save record' }}</button>
                                        <span v-else class="badge bg-success">Already in catalog</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div v-for="fact in apiFacts" :key="fact.label" class="col-md-4">
                                            <div class="border rounded p-2 h-100">
                                                <div class="text-muted font-size-12">{{ fact.label }}</div>
                                                <div>{{ fact.value }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-for="section in apiSections" :key="section.label" class="mb-3">
                                        <div class="fw-medium mb-1">{{ section.label }}</div>
                                        <div v-if="section.html" class="font-size-13" v-html="section.value"></div>
                                        <div v-else class="font-size-13" style="white-space: pre-line;">{{ section.value }}</div>
                                    </div>
                                </div>
                                <div v-else class="text-muted">Search, then choose a product to review the full record before saving.</div>
                            </div>
                        </div>
                        <div v-else-if="apiTab === 'brands'">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                <select v-model="brandLetter" class="form-select" style="width: 120px;" @change="loadBrands(1)">
                                    <option value="">All</option>
                                    <option v-for="letter in letters" :key="letter" :value="letter">{{ letter.toUpperCase() }}</option>
                                </select>
                                <button type="button" class="btn btn-light" :disabled="brandPage <= 1 || brandLoading" @click="loadBrands(brandPage - 1)">Previous</button>
                                <span class="text-muted">Page {{ brandPage }}</span>
                                <button type="button" class="btn btn-light" :disabled="brandLoading || !brandRows.length" @click="loadBrands(brandPage + 1)">Next</button>
                                <button type="button" class="btn btn-primary ms-auto" :disabled="brandLoading || importingDirectory || !brandRows.length" @click="importBrands">{{ importingDirectory ? 'Importing...' : 'Import this page' }}</button>
                            </div>
                            <div v-if="directoryNote" class="alert alert-success py-2">{{ directoryNote }}</div>
                            <div v-if="brandLoading" class="text-muted">Loading brands...</div>
                            <table v-else class="table table-sm align-middle mb-0">
                                <thead><tr><th>Brand</th><th>Strength</th><th>Generic</th><th>Manufacturer</th></tr></thead>
                                <tbody>
                                    <tr v-for="row in brandRows" :key="row.link"><td>{{ row.name }}</td><td>{{ row.strength }}</td><td>{{ row.generic }}</td><td>{{ row.manufacturer }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                <select v-model="companyLetter" class="form-select" style="width: 120px;" @change="loadCompanies(1)">
                                    <option value="">All</option>
                                    <option v-for="letter in letters" :key="letter" :value="letter">{{ letter.toUpperCase() }}</option>
                                </select>
                                <button type="button" class="btn btn-light" :disabled="companyPage <= 1 || companyLoading" @click="loadCompanies(companyPage - 1)">Previous</button>
                                <span class="text-muted">Page {{ companyPage }}</span>
                                <button type="button" class="btn btn-light" :disabled="companyLoading || !companyRows.length" @click="loadCompanies(companyPage + 1)">Next</button>
                                <button type="button" class="btn btn-primary ms-auto" :disabled="companyLoading || importingDirectory || !companyRows.length" @click="importCompanies">{{ importingDirectory ? 'Importing...' : 'Import this page' }}</button>
                            </div>
                            <div v-if="directoryNote" class="alert alert-success py-2">{{ directoryNote }}</div>
                            <div v-if="companyLoading" class="text-muted">Loading manufacturers...</div>
                            <table v-else class="table table-sm align-middle mb-0">
                                <thead><tr><th>Manufacturer</th><th>Catalog</th></tr></thead>
                                <tbody>
                                    <tr v-for="row in companyRows" :key="row.link"><td>{{ row.name }}</td><td>{{ row.details }}</td></tr>
                                </tbody>
                            </table>
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
import LunaTable from '@/Components/LunaTable.vue'

import { ref, computed } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import Pagination from '@/Components/Pagination.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

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
// API modal state
const showApiModal = ref(false)
const apiSearch = ref('')
const apiResults = ref([])
const apiSearchLoading = ref(false)
const apiDetails = ref(null)
const savingApi = ref(false)
const apiDetailsLoading = ref(false)
const apiTab = ref('medicine')
const letters = 'abcdefghijklmnopqrstuvwxyz'.split('')
const brandLetter = ref('')
const brandPage = ref(1)
const brandRows = ref([])
const brandLoading = ref(false)
const companyLetter = ref('')
const companyPage = ref(1)
const companyRows = ref([])
const companyLoading = ref(false)
const importingDirectory = ref(false)
const directoryNote = ref('')
const apiFacts = computed(() => {
    const details = apiDetails.value
    if (!details) return []
    return [
        ['Therapeutic class', details.therapeutic_class],
        ['Unit price', details.unit_price],
        ['Strip price', details.strip_price],
        ['Pack size', details.pack_size],
        ['Storage', details.storage],
    ].filter((item) => item[1]).map(([label, value]) => ({ label, value }))
})
const apiSections = computed(() => {
    const details = apiDetails.value
    if (!details) return []
    return [
        ['Indications', details.indications],
        ['Pharmacology', details.pharmacology],
        ['Dosage', details.dosage, true],
        ['Interaction', details.interaction],
        ['Contraindications', details.contraindications],
        ['Side effects', details.side_effects],
        ['Pregnancy and lactation', details.pregnancy_lactation],
        ['Precautions', details.precautions],
        ['Overdose', details.overdose],
    ].filter((item) => item[1]).map(([label, value, html]) => ({ label, value, html: Boolean(html) }))
})
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
    destroyRecord('medicines.destroy', id, 'Delete this medicine?', 'The medicine has been deleted.')
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

// API modal methods
const openApiModal = () => {
    showApiModal.value = true
    apiSearch.value = ''
    apiResults.value = []
    apiDetails.value = null
}
const closeApiModal = () => {
    showApiModal.value = false
}

let apiSearchTimeout = null
const debouncedApiSearch = () => {
    clearTimeout(apiSearchTimeout)
    apiDetails.value = null
    if (!apiSearch.value || apiSearch.value.length < 2) {
        apiResults.value = []
        return
    }
    apiSearchTimeout = setTimeout(async () => {
        apiSearchLoading.value = true
        try {
            const res = await fetch(`${route('api.medex.search')}?q=${encodeURIComponent(apiSearch.value)}`)
            apiResults.value = await res.json()
        } catch (e) {
            apiResults.value = []
        } finally {
            apiSearchLoading.value = false
        }
    }, 350)
}

const selectApiResult = async (res) => {
    apiDetails.value = null
    apiDetailsLoading.value = true
    try {
        const url = `${route('api.medex.product')}?url=${encodeURIComponent(res.link)}`
        const r = await fetch(url)
        apiDetails.value = await r.json()
        // keep reference to medex info
        apiDetails.value._medex = { id: res.link, name: res.name }
    } catch {}
    finally {
        apiDetailsLoading.value = false
    }
}

const postDirectory = async (name, rows) => {
    importingDirectory.value = true
    directoryNote.value = ''
    try {
        const response = await fetch(route(name), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ rows }),
        })
        const data = await response.json()
        directoryNote.value = `${data.created || 0} new records saved from ${data.received || rows.length} rows.`
    } finally {
        importingDirectory.value = false
    }
}
const loadBrands = async (page = 1) => {
    apiTab.value = 'brands'
    brandLoading.value = true
    directoryNote.value = ''
    brandPage.value = page
    try {
        const response = await fetch(`${route('api.medex.brands')}?page=${page}&letter=${brandLetter.value}`)
        const data = await response.json()
        brandRows.value = data.rows || []
    } finally {
        brandLoading.value = false
    }
}
const importBrands = () => postDirectory('api.medex.brands.import', brandRows.value.map((row) => ({ name: row.name })))
const loadCompanies = async (page = 1) => {
    apiTab.value = 'companies'
    companyLoading.value = true
    directoryNote.value = ''
    companyPage.value = page
    try {
        const response = await fetch(`${route('api.medex.companies')}?page=${page}&letter=${companyLetter.value}`)
        const data = await response.json()
        companyRows.value = data.rows || []
    } finally {
        companyLoading.value = false
    }
}
const importCompanies = () => postDirectory('api.medex.companies.import', companyRows.value.map((row) => ({ name: row.name, details: row.details })))
const saveFromApi = async () => {
    if (!apiDetails.value) return
    savingApi.value = true
    try {
        const link = apiDetails.value._medex?.id || ''
        // Extract medex_id and medex_name from URL like /brands/{id}/{slug}
        let medexId = null
        let medexName = null
        try {
            const u = new URL(link)
            const parts = u.pathname.split('/').filter(Boolean)
            const idx = parts.indexOf('brands')
            if (idx !== -1 && parts[idx+1]) {
                medexId = parts[idx+1]
                medexName = parts[idx+2] || null
            }
        } catch {}

        const parsePrice = (s) => {
            if (!s) return null
            const m = String(s).replace(/[^0-9.,]/g, '').replace(/,/g, '')
            const n = parseFloat(m)
            return isNaN(n) ? null : n
        }
        const numericPrice = apiDetails.value.numeric_unit_price ?? apiDetails.value.numeric_strip_price ?? parsePrice(apiDetails.value.unit_price) ?? parsePrice(apiDetails.value.strip_price)

        const payload = {
            name: apiDetails.value.name,
            manufacturer: apiDetails.value.manufacturer,
            category: apiDetails.value.therapeutic_class || null,
            generic_name: apiDetails.value.generic,
            strength: apiDetails.value.strength,
            dosage_form: apiDetails.value.form || null,
            price: numericPrice,
            medex_id: medexId,
            medex_name: medexName,
            details: {
                indications: apiDetails.value.indications,
                pharmacology: apiDetails.value.pharmacology,
                dosage: apiDetails.value.dosage,
                interaction: apiDetails.value.interaction,
                contraindications: apiDetails.value.contraindications,
                side_effects: apiDetails.value.side_effects,
                pregnancy: apiDetails.value.pregnancy_lactation,
                precautions: apiDetails.value.precautions,
                special_populations: apiDetails.value.special_populations,
                overdose: apiDetails.value.overdose,
                storage: apiDetails.value.storage,
                pack_size: apiDetails.value.pack_size,
                unit_price: apiDetails.value.unit_price,
                strip_price: apiDetails.value.strip_price,
            },
        }
        const res = await fetch(route('api.medicines.storeExternal'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        const data = await res.json()
        if (data.status === 'created' || data.status === 'duplicate') {
            closeApiModal()
            router.visit(route('medicines.index'))
        }
    } finally {
        savingApi.value = false
    }
}

// Open MedEx details from existing medicine row
const openMedexDetailsForMedicine = async (medicine) => {
    showApiModal.value = true
    apiSearch.value = ''
    apiResults.value = []
    apiDetails.value = null
    apiDetailsLoading.value = true
    try {
        const medexUrl = `https://medex.com.bd/brands/${medicine.medex_id}/${medicine.medex_name}`
        const url = `${route('api.medex.product')}?url=${encodeURIComponent(medexUrl)}`
        const r = await fetch(url)
        apiDetails.value = await r.json()
        apiDetails.value._medex = { id: medexUrl, name: medicine.name }
    } finally {
        apiDetailsLoading.value = false
    }
}
</script>
