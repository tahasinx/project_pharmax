<template>
    <Head title="Medicine List" />
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex flex-column gap-2 min-w-0">
                <h4 class="mb-0 font-size-18">Medicine List</h4>
                <FilterSummary
                    :chips="filterChips"
                    :show-button="false"
                    @clear="resetFilters"
                    @remove="removeFilter"
                />
            </div>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" @click="showFilters = true">
                    <i class="bi bi-funnel me-1" />
                    Filters
                    <span v-if="filterChips.length" class="badge bg-primary ms-1">{{ filterChips.length }}</span>
                </button>
                <Link :href="route('medicines.create')" class="btn btn-primary btn-sm">Add Medicine</Link>
                <button type="button" class="btn btn-success btn-sm" @click="showImportModal = true">Import CSV</button>
                <button type="button" class="btn btn-primary btn-sm" @click="openApiModal">
                    <i class="bi bi-cloud-download me-1" />
                    Reference Catalog
                </button>
            </div>
        </template>

        <FilterDrawer
            :show="showFilters"
            title="Medicine filters"
            @close="showFilters = false"
            @apply="applyFilters"
            @clear="resetFilters"
        >
            <div class="filter-field">
                <label class="field-label">Search</label>
                <input v-model="listFilters.search" type="search" class="field" placeholder="Name, form, barcode…">
            </div>
            <div class="filter-field">
                <label class="field-label">Brand</label>
                <SearchableSelect
                    v-model="listFilters.brand_id"
                    :options="brandSelectOptions"
                    placeholder="Search brand…"
                />
            </div>
            <div class="filter-field">
                <label class="field-label">Generic</label>
                <SearchableSelect
                    v-model="listFilters.generic_id"
                    :options="genericSelectOptions"
                    placeholder="Search generic…"
                />
            </div>
            <div class="filter-field">
                <label class="field-label">Manufacturer</label>
                <SearchableSelect
                    v-model="listFilters.manufacturer_id"
                    :options="manufacturerSelectOptions"
                    placeholder="Search manufacturer…"
                />
            </div>
            <div class="filter-field">
                <label class="field-label">Status</label>
                <SearchableSelect
                    v-model="listFilters.status"
                    :options="statusSelectOptions"
                    placeholder="Search status…"
                />
            </div>
            <div class="filter-field">
                <label class="field-label">Segment</label>
                <SearchableSelect
                    v-model="listFilters.segment"
                    :options="segmentSelectOptions"
                    placeholder="Search segment…"
                />
            </div>
            <div class="filter-field">
                <label class="field-label">Price range (Box MRP)</label>
                <div class="d-flex gap-2">
                    <input v-model="listFilters.price_min" type="number" min="0" step="0.01" class="field" placeholder="Min">
                    <input v-model="listFilters.price_max" type="number" min="0" step="0.01" class="field" placeholder="Max">
                </div>
            </div>
        </FilterDrawer>

        <LunaTable title="Medicines" :pagination="medicines" empty-text="No medicines found">
<table class="table table-striped table-hover mb-0">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Brand Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Generic Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Manufacturer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        P. Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Box MRP
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Discount
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
                                            <div v-if="medicineImageUrl(medicine.image)" class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" :src="medicineImageUrl(medicine.image)" :alt="medicine.name">
                                            </div>
                                            <div class="ml-4 min-w-0">
                                                <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                                    <button v-if="medicine.medex_id"
                                                            type="button"
                                                            @click="openMedexDetailsForMedicine(medicine)"
                                                            class="text-sm font-semibold text-blue-700 hover:underline"
                                                            title="Open linked API reference details">
                                                        {{ medicine.name }}
                                                    </button>
                                                    <div v-else class="text-sm font-medium text-gray-900">{{ medicine.name }}</div>
                                                    <span v-if="medicine.strength" class="text-muted font-size-12">{{ medicine.strength }}</span>
                                                </div>
                                                <div class="text-sm text-gray-500">{{ medicine.medicine_type?.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ medicine.generic_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ medicine.manufacturer?.name || '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ medicine.manufacturer_price }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $page.props.ui.currency_position === 'before' ? $page.props.ui.currency_symbol : '' }}{{ medicine.price }}{{ $page.props.ui.currency_position === 'after' ? $page.props.ui.currency_symbol : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ medicine.discount_percent || 0 }}%
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
                                            <Link :href="route('medicines.show', medicine.id)" class="btn btn-sm btn-icon btn-soft-primary" title="View"><i class="bi bi-journal-richtext"></i></Link>
                                            <button
                                                v-if="medicine.medex_id"
                                                type="button"
                                                class="btn btn-sm btn-icon btn-soft-info"
                                                title="API reference details"
                                                @click="openMedexDetailsForMedicine(medicine)"
                                            ><i class="bi bi-cloud"></i></button>
                                            <Link :href="route('medicines.edit', medicine.id)" class="btn btn-sm btn-icon btn-soft-secondary" title="Edit"><i class="bi bi-pencil"></i></Link>
                                            <Link :href="route('medicines.codes', medicine.id)" class="btn btn-sm btn-icon btn-soft-success" title="Codes"><i class="bi bi-upc"></i></Link>
                                            <button type="button" class="btn btn-sm btn-icon btn-soft-danger" title="Delete" @click="deleteMedicine(medicine.id)"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
</LunaTable>

        <FormScreen v-if="showApiModal" title="Product Reference Catalog" @close="closeApiModal">
            <template #header-actions>
                <div class="api-segment">
                    <button type="button" class="api-segment-btn" :class="{ active: medexSegment === 'allopathic' }" @click="setSegment('allopathic')">Allopathic</button>
                    <button type="button" class="api-segment-btn" :class="{ active: medexSegment === 'herbal' }" @click="setSegment('herbal')">Herbal</button>
                </div>
            </template>

            <div class="api-shell">
                <p class="api-shell-lead">Search external product directories, review full monograph details, then save into your database.</p>

                <div class="api-platform-toolbar">
                    <nav class="api-tabs">
                        <button type="button" class="api-tab" :class="{ active: apiTab === 'medicine' }" @click="apiTab = 'medicine'">Products</button>
                        <button type="button" class="api-tab" :class="{ active: apiTab === 'brands' }" @click="loadBrands(1)">Brand index</button>
                        <button type="button" class="api-tab" :class="{ active: apiTab === 'companies' }" @click="loadCompanies(1)">Manufacturers</button>
                        <button type="button" class="api-tab" :class="{ active: apiTab === 'generics' }" @click="loadGenerics(1)">Generics</button>
                        <button type="button" class="api-tab" :class="{ active: apiTab === 'forms' }" @click="loadDosageForms">Dosage forms</button>
                    </nav>
                </div>

                <div v-if="apiTab === 'medicine'" class="api-products">
                    <aside class="api-search-pane">
                        <label class="api-label" for="api-medicine-search">Medicine name</label>
                        <div class="api-search-wrap">
                            <i class="bi bi-search" />
                            <input
                                id="api-medicine-search"
                                v-model="apiSearch"
                                type="search"
                                class="api-search-input"
                                placeholder="Brand, generic, or strength"
                                autofocus
                                @input="debouncedApiSearch"
                            >
                        </div>
                        <div v-if="apiSearchLoading" class="api-hint">Searching…</div>
                        <div v-else-if="apiResults.length" class="api-result-list">
                            <button
                                v-for="res in apiResults"
                                :key="res.link || res.medex_id || res.name"
                                type="button"
                                class="api-result-item"
                                :class="{ active: apiSelectedLink === res.link, 'is-added': res.exists }"
                                @click="selectApiResult(res)"
                            >
                                <span class="api-result-copy">
                                    <strong>
                                        {{ res.name }}
                                        <span v-if="res.strength" class="api-result-strength">{{ res.strength }}</span>
                                    </strong>
                                    <small>{{ res.form || 'Reference product' }}</small>
                                </span>
                                <span v-if="res.exists" class="api-added-chip" title="Already in your database">
                                    <i class="bi bi-check2-circle" />
                                    Added
                                </span>
                                <i class="bi bi-chevron-right api-result-chevron" />
                            </button>
                        </div>
                        <div v-else-if="apiSearch && !apiSearchLoading" class="api-hint">No matching products.</div>
                        <div v-else class="api-hint">Type at least 2 characters to search.</div>
                    </aside>

                    <section class="api-detail-pane">
                        <div v-if="apiDetailsLoading" class="api-empty">Loading reference details…</div>
                        <template v-else-if="apiDetails">
                            <div class="api-detail-head">
                                <div class="api-detail-identity">
                                    <img v-if="apiDetails.image || apiDetails.img" :src="apiDetails.image || apiDetails.img" alt="" class="api-detail-thumb">
                                    <div class="min-w-0">
                                        <h4 class="api-detail-name">{{ apiDetails.name }}</h4>
                                        <p class="api-detail-meta">{{ [apiDetails.generic, apiDetails.form, apiDetails.strength].filter(Boolean).join(' · ') }}</p>
                                        <p class="api-detail-meta">{{ apiDetails.manufacturer }}</p>
                                    </div>
                                </div>
                                <button
                                    v-if="!apiDetails.exists"
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    :disabled="savingApi"
                                    @click="saveFromApi"
                                >
                                    <span v-if="savingApi" class="spinner-border spinner-border-sm me-1" role="status" />
                                    {{ savingApi ? 'Saving…' : 'Save to database' }}
                                </button>
                                <span v-else class="api-added-chip api-added-chip--lg align-self-start">
                                    <i class="bi bi-check2-circle" />
                                    Already in database
                                </span>
                            </div>
                            <div v-if="apiFacts.length" class="api-facts">
                                <div v-for="fact in apiFacts" :key="fact.label" class="api-fact">
                                    <span>{{ fact.label }}</span>
                                    <strong>{{ fact.value }}</strong>
                                </div>
                            </div>
                            <div v-for="section in apiSections" :key="section.label" class="api-section">
                                <h6>{{ section.label }}</h6>
                                <div v-if="section.html" class="api-section-body" v-html="section.value" />
                                <div v-else class="api-section-body" style="white-space: pre-line;">{{ section.value }}</div>
                            </div>
                        </template>
                        <div v-else class="api-empty">
                            <i class="bi bi-journal-richtext" />
                            <p>Search a medicine name, then select a result to view direct reference details.</p>
                        </div>
                    </section>
                </div>

                <div v-else-if="apiTab === 'brands'" class="api-directory">
                    <div class="api-directory-bar">
                        <select v-model="brandLetter" class="form-select form-select-sm" style="width: 5.5rem;" @change="loadBrands(1)">
                            <option value="">All</option>
                            <option v-for="letter in letters" :key="letter" :value="letter">{{ letter.toUpperCase() }}</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-light" :disabled="brandPage <= 1 || brandLoading" @click="loadBrands(brandPage - 1)">Prev</button>
                        <span class="text-muted font-size-12">Page {{ brandPage }}</span>
                        <button type="button" class="btn btn-sm btn-light" :disabled="brandLoading || !brandRows.length" @click="loadBrands(brandPage + 1)">Next</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="syncingDirectory" @click="queueSync('brands')">{{ syncingDirectory ? 'Queuing…' : 'Sync A–Z' }}</button>
                        <button type="button" class="btn btn-sm btn-primary ms-auto" :disabled="brandLoading || importingDirectory || !brandRows.length" @click="importBrands">{{ importingDirectory ? 'Saving…' : 'Save brands to catalog' }}</button>
                    </div>
                    <div v-if="directoryNote" class="alert alert-success py-2 mb-2">{{ directoryNote }}</div>
                    <div v-if="brandLoading" class="api-hint">Loading…</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0 api-table">
                            <thead><tr><th>Product</th><th>Form</th><th>Generic</th><th>Manufacturer</th><th>Status</th><th /></tr></thead>
                            <tbody>
                                <tr v-for="row in brandRows" :key="row.link || row.medex_id || row.name" :class="{ 'api-row-added': row.exists }">
                                    <td class="fw-medium">
                                        <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                            <span>{{ row.name }}</span>
                                            <span v-if="row.strength" class="text-muted font-size-12 fw-normal">{{ row.strength }}</span>
                                            <span v-if="row.exists" class="api-added-chip" title="Already in your database">
                                                <i class="bi bi-check2-circle" />
                                                Added
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ row.form || '—' }}</td>
                                    <td>{{ row.generic || '—' }}</td>
                                    <td>{{ row.manufacturer || '—' }}</td>
                                    <td>
                                        <span v-if="row.exists" class="text-success font-size-12 fw-semibold">In database</span>
                                        <span v-else class="text-muted font-size-12">Not saved</span>
                                    </td>
                                    <td>
                                        <button
                                            v-if="row.link || row.medex_id"
                                            type="button"
                                            class="btn btn-sm btn-soft-primary"
                                            @click="openApiDetailsFromRow(row)"
                                        >View details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="apiTab === 'companies'" class="api-directory">
                    <div class="api-directory-bar">
                        <select v-model="companyLetter" class="form-select form-select-sm" style="width: 5.5rem;" @change="loadCompanies(1)">
                            <option value="">All</option>
                            <option v-for="letter in letters" :key="letter" :value="letter">{{ letter.toUpperCase() }}</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-light" :disabled="companyPage <= 1 || companyLoading" @click="loadCompanies(companyPage - 1)">Prev</button>
                        <span class="text-muted font-size-12">Page {{ companyPage }}</span>
                        <button type="button" class="btn btn-sm btn-light" :disabled="companyLoading || !companyRows.length" @click="loadCompanies(companyPage + 1)">Next</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="syncingDirectory" @click="queueSync('companies')">{{ syncingDirectory ? 'Queuing…' : 'Sync A–Z' }}</button>
                        <button type="button" class="btn btn-sm btn-primary ms-auto" :disabled="companyLoading || importingDirectory || !companyRows.length" @click="importCompanies">{{ importingDirectory ? 'Importing…' : 'Import page' }}</button>
                    </div>
                    <div v-if="directoryNote" class="alert alert-success py-2 mb-2">{{ directoryNote }}</div>
                    <div v-if="companyLoading" class="api-hint">Loading…</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0 api-table">
                            <thead><tr><th>Manufacturer</th><th>Catalog</th></tr></thead>
                            <tbody>
                                <tr v-for="row in companyRows" :key="row.link || row.name"><td class="fw-medium">{{ row.name }}</td><td>{{ row.details }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="apiTab === 'generics'" class="api-directory">
                    <div class="api-directory-bar">
                        <select v-model="genericLetter" class="form-select form-select-sm" style="width: 5.5rem;" @change="loadGenerics(1)">
                            <option value="">All</option>
                            <option v-for="letter in letters" :key="letter" :value="letter">{{ letter.toUpperCase() }}</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-light" :disabled="genericPage <= 1 || genericLoading" @click="loadGenerics(genericPage - 1)">Prev</button>
                        <span class="text-muted font-size-12">Page {{ genericPage }}</span>
                        <button type="button" class="btn btn-sm btn-light" :disabled="genericLoading || !genericRows.length" @click="loadGenerics(genericPage + 1)">Next</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="syncingDirectory" @click="queueSync('generics')">{{ syncingDirectory ? 'Queuing…' : 'Sync A–Z' }}</button>
                        <button type="button" class="btn btn-sm btn-primary ms-auto" :disabled="genericLoading || importingDirectory || !genericRows.length" @click="importGenerics">{{ importingDirectory ? 'Importing…' : 'Import page' }}</button>
                    </div>
                    <div v-if="directoryNote" class="alert alert-success py-2 mb-2">{{ directoryNote }}</div>
                    <div v-if="genericLoading" class="api-hint">Loading…</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0 api-table">
                            <thead><tr><th>Generic</th><th>Brands</th></tr></thead>
                            <tbody>
                                <tr v-for="row in genericRows" :key="row.link || row.name"><td class="fw-medium">{{ row.name }}</td><td>{{ row.brand_count || 0 }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="apiTab === 'forms'" class="api-directory">
                    <div class="api-directory-bar">
                        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="syncingDirectory" @click="queueSync('dosage-forms')">{{ syncingDirectory ? 'Queuing…' : 'Sync forms' }}</button>
                        <button type="button" class="btn btn-sm btn-primary ms-auto" :disabled="formLoading || importingDirectory || !formRows.length" @click="importDosageForms">{{ importingDirectory ? 'Importing…' : 'Import all' }}</button>
                    </div>
                    <div v-if="directoryNote" class="alert alert-success py-2 mb-2">{{ directoryNote }}</div>
                    <div v-if="formLoading" class="api-hint">Loading…</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm align-middle mb-0 api-table">
                            <thead><tr><th>Dosage form</th><th>Brand count</th></tr></thead>
                            <tbody>
                                <tr v-for="row in formRows" :key="row.name"><td class="fw-medium">{{ row.name }}</td><td>{{ row.brand_count || 0 }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <template #footer="{ close }">
                <button type="button" class="btn btn-outline-danger" @click="close">Close</button>
            </template>
        </FormScreen>
        <!-- Import CSV -->
        <div v-if="showImportModal" class="import-platform-root">
            <div class="import-platform-backdrop" @click="!isImporting && closeImportModal()" />
            <div class="import-platform" role="dialog" aria-modal="true" aria-labelledby="import-csv-title">
                <header class="import-platform-head">
                    <div>
                        <h5 id="import-csv-title" class="import-platform-title">Import CSV</h5>
                        <p class="import-platform-sub">Upload a product file to add medicines in bulk.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-light" :disabled="isImporting" aria-label="Close" @click="closeImportModal">
                        <i class="bi bi-x-lg" />
                    </button>
                </header>

                <form class="import-platform-body" enctype="multipart/form-data" @submit.prevent="importMedicines">
                    <section class="import-card">
                        <div class="import-card-label">Required columns</div>
                        <p class="import-card-help">Order does not matter. Header names must match exactly.</p>
                        <div class="import-chips">
                            <span v-for="h in requiredHeaders" :key="h" class="import-chip">{{ h }}</span>
                        </div>
                    </section>

                    <section class="import-card">
                        <div class="import-card-label">File</div>
                        <button
                            type="button"
                            class="import-dropzone"
                            :class="{ 'has-file': !!selectedFile }"
                            @dragover.prevent
                            @drop.prevent="onDrop"
                            @click="triggerFile"
                        >
                            <input ref="fileInput" name="file" type="file" accept=".csv,.xlsx,.xls" class="d-none" @change="onFileChange">
                            <template v-if="!selectedFile">
                                <i class="bi bi-cloud-arrow-up import-drop-icon" />
                                <p class="import-drop-title">Drop your CSV here</p>
                                <p class="import-drop-help">or click to browse · Max 5MB · .csv</p>
                            </template>
                            <template v-else>
                                <i class="bi bi-file-earmark-spreadsheet import-drop-icon is-file" />
                                <p class="import-drop-title">{{ selectedFile.name }}</p>
                                <p class="import-drop-help">{{ (selectedFile.size / 1024).toFixed(1) }} KB · click to change</p>
                            </template>
                        </button>
                        <button type="button" class="btn btn-link btn-sm px-0 import-sample" @click="downloadSampleCsv">
                            <i class="bi bi-download me-1" />
                            Download sample.csv
                        </button>
                    </section>

                    <div v-if="csvErrors.length" class="import-errors">
                        <div class="import-errors-title">Fix these before importing</div>
                        <ul>
                            <li v-for="(err, idx) in csvErrors" :key="idx">{{ err }}</li>
                        </ul>
                    </div>

                    <footer class="import-platform-foot">
                        <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="isImporting" @click="closeImportModal">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                            :disabled="isImporting || !selectedFile || csvErrors.length > 0"
                        >
                            <span v-if="isImporting" class="spinner-border spinner-border-sm me-1" role="status" />
                            {{ isImporting ? 'Importing…' : 'Import medicines' }}
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import LunaTable from '@/Components/LunaTable.vue'

import { ref, computed, reactive, onMounted } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FormScreen from '@/Components/FormScreen.vue'
import SearchableSelect from '@/Components/SearchableSelect.vue'
import Pagination from '@/Components/Pagination.vue'
import FilterDrawer from '@/Components/FilterDrawer.vue'
import FilterSummary from '@/Components/FilterSummary.vue'
import { destroyRecord } from '@/Composables/confirmDelete'

const props = defineProps({
    medicines: Object,
    categories: Array,
    manufacturers: Array,
    brands: { type: Array, default: () => [] },
    dosageForms: { type: Array, default: () => [] },
    generics: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const search = ref('')
const categoryFilter = ref('')
const manufacturerFilter = ref('')
const showFilters = ref(false)
const emptyFilters = () => ({
    search: '',
    brand_id: '',
    generic_id: '',
    manufacturer_id: '',
    status: '',
    segment: '',
    price_min: '',
    price_max: '',
})
const listFilters = reactive({
    ...emptyFilters(),
    search: props.filters?.search || '',
    brand_id: props.filters?.brand_id || '',
    generic_id: props.filters?.generic_id || '',
    manufacturer_id: props.filters?.manufacturer_id || '',
    status: props.filters?.status || '',
    segment: props.filters?.segment || '',
    price_min: props.filters?.price_min || '',
    price_max: props.filters?.price_max || '',
})
const withAll = (options, allLabel) => [{ value: '', label: allLabel }, ...options]

const brandSelectOptions = computed(() => withAll(
    [...(props.brands || [])]
        .sort((a, b) => a.name.localeCompare(b.name))
        .map((b) => ({ value: b.brand_id || b.id, label: b.name })),
    'All brands',
))
const genericSelectOptions = computed(() => withAll(
    [...(props.generics || [])]
        .sort((a, b) => a.name.localeCompare(b.name))
        .map((g) => ({ value: g.generic_id || g.id, label: g.name })),
    'All generics',
))
const manufacturerSelectOptions = computed(() => withAll(
    [...(props.manufacturers || [])]
        .sort((a, b) => a.name.localeCompare(b.name))
        .map((m) => ({ value: m.manufacturer_id || m.id, label: m.name })),
    'All manufacturers',
))
const statusSelectOptions = [
    { value: '', label: 'All statuses' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
]
const segmentSelectOptions = [
    { value: '', label: 'All segments' },
    { value: 'allopathic', label: 'Allopathic' },
    { value: 'herbal', label: 'Herbal' },
    { value: 'device', label: 'Device' },
]

const labelFor = (options, value) => options.find((row) => String(row.value) === String(value))?.label || value

const filterChips = computed(() => {
    const chips = []
    if (listFilters.search) chips.push({ key: 'search', label: 'Search', value: listFilters.search })
    if (listFilters.brand_id) chips.push({ key: 'brand_id', label: 'Brand', value: labelFor(brandSelectOptions.value, listFilters.brand_id) })
    if (listFilters.generic_id) chips.push({ key: 'generic_id', label: 'Generic', value: labelFor(genericSelectOptions.value, listFilters.generic_id) })
    if (listFilters.manufacturer_id) {
        chips.push({ key: 'manufacturer_id', label: 'Manufacturer', value: labelFor(manufacturerSelectOptions.value, listFilters.manufacturer_id) })
    }
    if (listFilters.status) chips.push({ key: 'status', label: 'Status', value: labelFor(statusSelectOptions, listFilters.status) })
    if (listFilters.segment) chips.push({ key: 'segment', label: 'Segment', value: labelFor(segmentSelectOptions, listFilters.segment) })
    if (listFilters.price_min || listFilters.price_max) {
        const min = listFilters.price_min || '0'
        const max = listFilters.price_max || '∞'
        chips.push({ key: 'price', label: 'Price', value: `${min} – ${max}` })
    }
    return chips
})

const applyFilters = () => {
    showFilters.value = false
    const query = Object.fromEntries(
        Object.entries({ ...listFilters }).filter(([, value]) => value !== '' && value !== null && value !== undefined)
    )
    router.get(route('medicines.index'), query, { preserveState: true, replace: true })
}
const resetFilters = () => {
    Object.assign(listFilters, emptyFilters())
    applyFilters()
}
const removeFilter = (key) => {
    if (key === 'price') {
        listFilters.price_min = ''
        listFilters.price_max = ''
    } else if (key in listFilters) {
        listFilters[key] = ''
    }
    applyFilters()
}
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
const apiSelectedLink = ref('')
const savingApi = ref(false)
const apiDetailsLoading = ref(false)
const apiTab = ref('medicine')
const medexSegment = ref('allopathic')
const letters = 'abcdefghijklmnopqrstuvwxyz'.split('')
const brandLetter = ref('')
const brandPage = ref(1)
const brandRows = ref([])
const brandLoading = ref(false)
const companyLetter = ref('')
const companyPage = ref(1)
const companyRows = ref([])
const companyLoading = ref(false)
const genericLetter = ref('')
const genericPage = ref(1)
const genericRows = ref([])
const genericLoading = ref(false)
const formRows = ref([])
const formLoading = ref(false)
const importingDirectory = ref(false)
const syncingDirectory = ref(false)
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
        ['Indications', details.indications, details.indications_html],
        ['Pharmacology', details.pharmacology, details.pharmacology_html],
        ['Dosage', details.dosage, details.dosage_html ?? true],
        ['Interaction', details.interaction, details.interaction_html],
        ['Contraindications', details.contraindications, details.contraindications_html],
        ['Side effects', details.side_effects, details.side_effects_html],
        ['Pregnancy and lactation', details.pregnancy_lactation || details.pregnancy, details.pregnancy_html],
        ['Precautions', details.precautions, details.precautions_html],
        ['Overdose', details.overdose, details.overdose_html],
    ].filter((item) => item[1]).map(([label, value, html]) => ({ label, value, html: Boolean(html) }))
})

const medicineImageUrl = (path) => {
    if (!path) return ''
    if (/^https?:\/\//i.test(path) || path.startsWith('/storage/') || path.startsWith('blob:')) return path
    return `/storage/${String(path).replace(/^\/+/, '')}`
}
const requiredHeaders = [
    'name',
    'generic_name',
    'category',
    'manufacturer',
    'price'
]

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
let apiSearchTimeout = null
let apiSearchSeq = 0
let apiProductSeq = 0

const resetApiModalState = () => {
    clearTimeout(apiSearchTimeout)
    apiSearchSeq += 1
    apiProductSeq += 1
    apiSearch.value = ''
    apiResults.value = []
    apiDetails.value = null
    apiSelectedLink.value = ''
    apiSearchLoading.value = false
    apiDetailsLoading.value = false
    savingApi.value = false
}

const openApiModal = () => {
    resetApiModalState()
    showApiModal.value = true
}
const closeApiModal = () => {
    showApiModal.value = false
    resetApiModalState()
}

const debouncedApiSearch = () => {
    clearTimeout(apiSearchTimeout)
    apiDetails.value = null
    apiSelectedLink.value = ''
    if (!apiSearch.value || apiSearch.value.length < 2) {
        apiResults.value = []
        apiSearchLoading.value = false
        return
    }
    const seq = ++apiSearchSeq
    apiSearchTimeout = setTimeout(async () => {
        apiSearchLoading.value = true
        try {
            const res = await fetch(`${route('api.medex.search')}?q=${encodeURIComponent(apiSearch.value)}`)
            const data = await res.json().catch(() => [])
            if (seq !== apiSearchSeq) return
            apiResults.value = Array.isArray(data) ? data : []
        } catch (e) {
            if (seq !== apiSearchSeq) return
            apiResults.value = []
        } finally {
            if (seq === apiSearchSeq) apiSearchLoading.value = false
        }
    }, 350)
}

const medexUrlFromParts = (id, slug = null) => {
    if (!id) return ''
    const cleanSlug = slug ? String(slug).replace(/^\/+|\/+$/g, '') : ''
    return cleanSlug
        ? `https://medex.com.bd/brands/${id}/${cleanSlug}`
        : `https://medex.com.bd/brands/${id}`
}

const resolveApiLink = (row = {}) => {
    if (row.link) return row.link
    if (row.medex_path) {
        return row.medex_path.startsWith('http')
            ? row.medex_path
            : `https://medex.com.bd${row.medex_path.startsWith('/') ? '' : '/'}${row.medex_path}`
    }
    return medexUrlFromParts(row.medex_id, row.medex_slug || row.medex_name || null)
}

const selectApiResult = async (res) => {
    const link = resolveApiLink(res)
    if (!link) return
    apiTab.value = 'medicine'
    apiSelectedLink.value = link
    apiDetails.value = null
    const seq = ++apiProductSeq
    apiDetailsLoading.value = true
    try {
        const url = `${route('api.medex.product')}?url=${encodeURIComponent(link)}`
        const r = await fetch(url)
        const data = await r.json().catch(() => ({}))
        if (seq !== apiProductSeq) return
        if (!r.ok || data?.error) {
            apiDetails.value = null
            return
        }
        apiDetails.value = {
            ...data,
            exists: data.exists ?? Boolean(res.exists),
            medicine_id: data.medicine_id ?? res.medicine_id ?? null,
            _medex: { id: link, name: res.name || data.name },
        }
    } catch {
        if (seq !== apiProductSeq) return
        apiDetails.value = null
    } finally {
        if (seq === apiProductSeq) apiDetailsLoading.value = false
    }
}

const openApiDetailsFromRow = (row) => {
    selectApiResult({
        link: resolveApiLink(row),
        name: row.name,
        exists: row.exists,
        medicine_id: row.medicine_id,
        medex_id: row.medex_id,
        medex_slug: row.medex_slug || row.medex_name,
    })
}

const csrfHeaders = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!token) throw new Error('Missing CSRF token')
    return {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json',
        Accept: 'application/json',
    }
}
const herbalQuery = () => (medexSegment.value === 'herbal' ? '&herbal=1' : '')
const setSegment = (segment) => {
    medexSegment.value = segment
    if (apiTab.value === 'brands') loadBrands(1)
    else if (apiTab.value === 'companies') loadCompanies(1)
    else if (apiTab.value === 'generics') loadGenerics(1)
}
const postDirectory = async (name, rows, extra = {}) => {
    importingDirectory.value = true
    directoryNote.value = ''
    try {
        const response = await fetch(route(name), {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ rows, segment: medexSegment.value, herbal: medexSegment.value === 'herbal', ...extra }),
        })
        const data = await response.json()
        const created = data.created ?? data.indexed ?? 0
        const updated = data.updated ?? data.brands ?? 0
        directoryNote.value = `${created} new / ${updated} updated from ${data.received || rows.length} rows (${medexSegment.value}).`
    } finally {
        importingDirectory.value = false
    }
}
const queueSync = async (directory) => {
    syncingDirectory.value = true
    directoryNote.value = ''
    try {
        const response = await fetch(route('api.medex.sync', directory), {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ segment: medexSegment.value, herbal: medexSegment.value === 'herbal' }),
        })
        const data = await response.json()
        directoryNote.value = data.queued
            ? `Queued ${directory} sync for ${medexSegment.value}.`
            : (data.message || 'Could not queue sync.')
    } finally {
        syncingDirectory.value = false
    }
}
const loadBrands = async (page = 1) => {
    apiTab.value = 'brands'
    brandLoading.value = true
    directoryNote.value = ''
    brandPage.value = page
    try {
        const response = await fetch(`${route('api.medex.brands')}?page=${page}&letter=${brandLetter.value}${herbalQuery()}`)
        const data = await response.json()
        brandRows.value = data.rows || []
    } finally {
        brandLoading.value = false
    }
}
const importBrands = () => postDirectory('api.medex.brands.import', brandRows.value)
const loadCompanies = async (page = 1) => {
    apiTab.value = 'companies'
    companyLoading.value = true
    directoryNote.value = ''
    companyPage.value = page
    try {
        const response = await fetch(`${route('api.medex.companies')}?page=${page}&letter=${companyLetter.value}${herbalQuery()}`)
        const data = await response.json()
        companyRows.value = data.rows || []
    } finally {
        companyLoading.value = false
    }
}
const importCompanies = () => postDirectory('api.medex.companies.import', companyRows.value)
const loadGenerics = async (page = 1) => {
    apiTab.value = 'generics'
    genericLoading.value = true
    directoryNote.value = ''
    genericPage.value = page
    try {
        const response = await fetch(`${route('api.medex.generics')}?page=${page}&letter=${genericLetter.value}${herbalQuery()}`)
        const data = await response.json()
        genericRows.value = data.rows || []
    } finally {
        genericLoading.value = false
    }
}
const importGenerics = () => postDirectory('api.medex.generics.import', genericRows.value)
const loadDosageForms = async () => {
    apiTab.value = 'forms'
    formLoading.value = true
    directoryNote.value = ''
    try {
        const response = await fetch(route('api.medex.dosage-forms'))
        const data = await response.json()
        formRows.value = data.rows || []
    } finally {
        formLoading.value = false
    }
}
const importDosageForms = () => postDirectory('api.medex.dosage-forms.import', formRows.value)
const saveFromApi = async () => {
    if (!apiDetails.value) return
    savingApi.value = true
    try {
        const link = apiDetails.value.url || apiDetails.value._medex?.id || apiSelectedLink.value || ''
        let medexId = apiDetails.value.medex_id || null
        let medexName = apiDetails.value.medex_name || null
        if (!medexId || !medexName) {
            try {
                const u = new URL(link)
                const parts = u.pathname.split('/').filter(Boolean)
                const idx = parts.indexOf('brands')
                if (idx !== -1 && parts[idx + 1]) {
                    medexId = medexId || parts[idx + 1]
                    medexName = medexName || parts[idx + 2] || null
                }
            } catch {}
        }

        const parsePrice = (s) => {
            if (!s) return null
            const m = String(s).replace(/[^0-9.,]/g, '').replace(/,/g, '')
            const n = parseFloat(m)
            return isNaN(n) ? null : n
        }
        const pricesBlob = Array.isArray(apiDetails.value.prices) ? apiDetails.value.prices.join(' ') : ''
        const numericPrice = apiDetails.value.numeric_unit_price
            ?? apiDetails.value.numeric_strip_price
            ?? parsePrice(apiDetails.value.unit_price)
            ?? parsePrice(apiDetails.value.strip_price)
            ?? parsePrice(pricesBlob)
            ?? parsePrice(apiDetails.value.pack_size)

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
            segment: medexSegment.value,
            details: {
                indications: apiDetails.value.indications,
                pharmacology: apiDetails.value.pharmacology,
                dosage: apiDetails.value.dosage,
                interaction: apiDetails.value.interaction,
                contraindications: apiDetails.value.contraindications,
                side_effects: apiDetails.value.side_effects,
                pregnancy: apiDetails.value.pregnancy_lactation || apiDetails.value.pregnancy,
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
            headers: csrfHeaders(),
            body: JSON.stringify(payload)
        })
        const data = await res.json().catch(() => ({}))
        if (!res.ok) {
            const firstError = data?.errors ? Object.values(data.errors).flat()[0] : null
            window.alert(firstError || data?.message || 'Could not save medicine to the database.')
            return
        }
        if (data.status === 'created' || data.status === 'duplicate') {
            if (apiDetails.value) {
                apiDetails.value = { ...apiDetails.value, exists: true, medicine_id: data.id || apiDetails.value.medicine_id }
            }
            apiResults.value = apiResults.value.map((row) => (
                (medexId && row.medex_id === medexId) || row.link === link
                    ? { ...row, exists: true, medicine_id: data.id || row.medicine_id }
                    : row
            ))
            brandRows.value = brandRows.value.map((row) => (
                (medexId && row.medex_id === medexId) || row.link === link
                    ? { ...row, exists: true, medicine_id: data.id || row.medicine_id }
                    : row
            ))
            closeApiModal()
            router.visit(route('medicines.index'))
        }
    } finally {
        savingApi.value = false
    }
}

onMounted(() => {
    try {
        const params = new URLSearchParams(window.location.search)
        if (params.get('open_api') === '1' || params.get('open_medex') === '1') {
            showApiModal.value = true
            apiTab.value = 'medicine'
        }
    } catch {
        // ignore
    }
})

const openMedexDetailsForMedicine = async (medicine) => {
    if (!medicine?.medex_id) return
    resetApiModalState()
    showApiModal.value = true
    await selectApiResult({
        link: medexUrlFromParts(medicine.medex_id, medicine.medex_name),
        name: medicine.name,
        exists: true,
        medicine_id: medicine.id,
        medex_id: medicine.medex_id,
        medex_slug: medicine.medex_name,
    })
}
</script>

<style scoped>
.api-shell {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    min-height: calc(100vh - 12rem);
}

.api-shell-lead {
    margin: 0;
    font-size: 0.84rem;
    color: var(--shell-panel-muted, #74788d);
}

.api-platform-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    padding: 0.45rem;
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    background: var(--shell-panel-bg, #f8f9fc);
}

.api-tabs,
.api-segment {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.15rem;
    border-radius: var(--pf-radius, 0.55rem);
    background: rgba(15, 23, 42, 0.04);
}

.api-tab,
.api-segment-btn {
    border: 0;
    background: transparent;
    color: var(--shell-panel-muted, #74788d);
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.35rem 0.7rem;
    border-radius: calc(var(--pf-radius, 0.55rem) - 0.1rem);
}

.api-tab.active,
.api-segment-btn.active {
    background: var(--bs-primary, #5156be);
    color: #fff;
}

.api-products {
    display: grid;
    grid-template-columns: minmax(220px, 280px) 1fr;
    gap: 0.85rem;
    flex: 1;
    min-height: 0;
}

.api-search-pane,
.api-detail-pane {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.65rem);
    background: var(--shell-panel-bg, #f8f9fc);
    padding: 0.85rem;
    min-height: 0;
}

.api-detail-pane {
    background: var(--shell-panel-surface, #fff);
}

.api-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.api-search-wrap {
    position: relative;
}

.api-search-wrap > i {
    position: absolute;
    left: 0.7rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--shell-panel-muted, #74788d);
    font-size: 0.85rem;
}

.api-search-input {
    width: 100%;
    border: 1px solid var(--shell-panel-border, #ced4da);
    border-radius: var(--pf-radius, 0.45rem);
    padding: 0.5rem 0.7rem 0.5rem 2rem;
    background: var(--shell-panel-surface, #fff);
    color: var(--shell-panel-text, #343747);
    font-size: 0.86rem;
}

.api-search-input:focus {
    outline: none;
    border-color: var(--bs-primary, #5156be);
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb, 81, 86, 190), 0.15);
}

.api-hint {
    margin-top: 0.7rem;
    font-size: 0.76rem;
    color: var(--shell-panel-muted, #74788d);
}

.api-result-list {
    margin-top: 0.55rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    max-height: calc(100vh - 20rem);
    overflow: auto;
}

.api-result-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    width: 100%;
    text-align: left;
    border: 1px solid transparent;
    border-radius: var(--pf-radius, 0.4rem);
    background: transparent;
    color: var(--shell-panel-text, #343747);
    padding: 0.38rem 0.5rem;
    transition: background 0.12s ease, border-color 0.12s ease;
}

.api-result-item:hover,
.api-result-item.active {
    border-color: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.28);
    background: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.06);
}

.api-result-copy {
    min-width: 0;
    flex: 1;
}

.api-result-item strong {
    display: block;
    font-size: 0.8rem;
    font-weight: 650;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.api-result-strength {
    margin-left: 0.35rem;
    font-size: 0.72rem;
    font-weight: 500;
    color: var(--shell-panel-muted, #74788d);
}

.api-result-item small {
    display: block;
    color: var(--shell-panel-muted, #74788d);
    font-size: 0.68rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.api-result-chevron {
    flex-shrink: 0;
    font-size: 0.72rem;
    color: var(--shell-panel-muted, #adb5bd);
}

.api-result-item.active .api-result-chevron {
    color: var(--bs-primary, #5156be);
}

.api-result-item.is-added {
    border-color: rgba(25, 135, 84, 0.18);
}

.api-added-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    flex-shrink: 0;
    padding: 0.12rem 0.42rem;
    border-radius: 999px;
    background: rgba(25, 135, 84, 0.12);
    color: #146c43;
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    line-height: 1.2;
    white-space: nowrap;
}

.api-added-chip i {
    font-size: 0.72rem;
}

.api-added-chip--lg {
    padding: 0.35rem 0.65rem;
    font-size: 0.74rem;
}

.api-added-chip--lg i {
    font-size: 0.85rem;
}

.api-row-added td {
    background: rgba(25, 135, 84, 0.035);
}

.api-detail-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.api-detail-identity {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    min-width: 0;
}

.api-detail-thumb {
    width: 3.25rem;
    height: 3.25rem;
    object-fit: contain;
    border-radius: var(--pf-radius, 0.45rem);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    background: var(--shell-panel-bg, #f8f9fc);
    flex-shrink: 0;
}

.api-detail-name {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 650;
    color: var(--shell-panel-text, #343747);
}

.api-detail-meta {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: var(--shell-panel-muted, #74788d);
}

.api-facts {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.5rem;
    margin-bottom: 0.85rem;
}

.api-fact {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.45rem);
    padding: 0.55rem 0.65rem;
    background: var(--shell-panel-bg, #f8f9fc);
}

.api-fact span {
    display: block;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--shell-panel-muted, #74788d);
}

.api-fact strong {
    font-size: 0.86rem;
    color: var(--shell-panel-text, #343747);
}

.api-section {
    margin-bottom: 0.85rem;
}

.api-section h6 {
    margin: 0 0 0.25rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--shell-panel-muted, #74788d);
}

.api-section-body {
    font-size: 0.84rem;
    line-height: 1.55;
    color: var(--shell-panel-text, #343747);
}

.api-empty {
    min-height: 280px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: var(--shell-panel-muted, #74788d);
    text-align: center;
    padding: 1.5rem;
}

.api-empty i {
    font-size: 1.6rem;
    opacity: 0.55;
}

.api-directory-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.75rem;
}

.api-table th {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--shell-panel-muted, #74788d);
}

@media (max-width: 900px) {
    .api-products { grid-template-columns: 1fr; }
    .api-result-list { max-height: 14rem; }
}

.import-platform-root {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.import-platform-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.48);
}

.import-platform {
    position: relative;
    z-index: 1;
    width: min(32rem, 100%);
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: calc(var(--pf-radius, 0.65rem) + 0.1rem);
    box-shadow: 0 24px 64px rgba(15, 23, 42, 0.18);
    overflow: hidden;
}

.import-platform-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid var(--shell-panel-border, #e6e8ee);
}

.import-platform-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 650;
    color: var(--shell-panel-text, #343747);
}

.import-platform-sub {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.import-platform-body {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1rem 1.15rem 1.15rem;
}

.import-card {
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    border-radius: var(--pf-radius, 0.55rem);
    background: var(--shell-panel-bg, #f8f9fc);
    padding: 0.85rem 0.95rem;
}

.import-card-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--shell-panel-muted, #74788d);
}

.import-card-help {
    margin: 0.25rem 0 0.55rem;
    font-size: 0.78rem;
    color: var(--shell-panel-muted, #74788d);
}

.import-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.import-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    background: var(--shell-panel-surface, #fff);
    border: 1px solid var(--shell-panel-border, #e6e8ee);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--shell-panel-text, #343747);
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}

.import-dropzone {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
    min-height: 8.5rem;
    margin-top: 0.55rem;
    border: 1px dashed color-mix(in srgb, var(--bs-primary, #5156be) 35%, var(--shell-panel-border, #ced4da));
    border-radius: var(--pf-radius, 0.55rem);
    background: var(--shell-panel-surface, #fff);
    color: var(--shell-panel-text, #343747);
    padding: 1.1rem 0.85rem;
    text-align: center;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.import-dropzone:hover,
.import-dropzone.has-file {
    border-color: var(--bs-primary, #5156be);
    background: rgba(var(--bs-primary-rgb, 81, 86, 190), 0.04);
}

.import-drop-icon {
    font-size: 1.55rem;
    color: var(--bs-primary, #5156be);
    opacity: 0.85;
    margin-bottom: 0.15rem;
}

.import-drop-icon.is-file {
    opacity: 1;
}

.import-drop-title {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 650;
    color: var(--shell-panel-text, #343747);
}

.import-drop-help {
    margin: 0;
    font-size: 0.76rem;
    color: var(--shell-panel-muted, #74788d);
}

.import-sample {
    margin-top: 0.45rem;
    text-decoration: none;
    font-weight: 600;
}

.import-errors {
    border: 1px solid rgba(220, 53, 69, 0.25);
    background: rgba(220, 53, 69, 0.06);
    border-radius: var(--pf-radius, 0.45rem);
    padding: 0.7rem 0.85rem;
    color: #b02a37;
    font-size: 0.82rem;
}

.import-errors-title {
    font-weight: 700;
    margin-bottom: 0.3rem;
}

.import-errors ul {
    margin: 0;
    padding-left: 1.1rem;
}

.import-platform-foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding-top: 0.25rem;
}
</style>

