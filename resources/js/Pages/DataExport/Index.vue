<template>
  <AuthenticatedLayout>
    <Head title="Data Export/Import" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Data Export/Import</h2>

            <!-- Export Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold mb-4">Export Data</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Medicines</h4>
                  <div class="space-y-2">
                    <button
                      @click="exportData('medicines', 'excel')"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export Excel
                    </button>
                    <button
                      @click="exportData('medicines', 'csv')"
                      class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export CSV
                    </button>
                  </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Customers</h4>
                  <div class="space-y-2">
                    <button
                      @click="exportData('customers', 'excel')"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export Excel
                    </button>
                    <button
                      @click="exportData('customers', 'csv')"
                      class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export CSV
                    </button>
                  </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Invoices</h4>
                  <div class="space-y-2">
                    <button
                      @click="exportData('invoices', 'excel')"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export Excel
                    </button>
                    <button
                      @click="exportData('invoices', 'csv')"
                      class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export CSV
                    </button>
                  </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Stocks</h4>
                  <div class="space-y-2">
                    <button
                      @click="exportData('stocks', 'excel')"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export Excel
                    </button>
                    <button
                      @click="exportData('stocks', 'csv')"
                      class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                      Export CSV
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Import Section -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold mb-4">Import Data</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Medicines</h4>
                  <form @submit.prevent="importData('medicines')" class="space-y-3">
                    <input
                      type="file"
                      ref="medicinesFile"
                      accept=".xlsx,.xls,.csv"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="updateExisting.medicines"
                        class="mr-2"
                      />
                      Update existing records
                    </label>
                    <button
                      type="submit"
                      :disabled="isImporting.medicines"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                    >
                      <span v-if="isImporting.medicines">Importing...</span>
                      <span v-else>Import Medicines</span>
                    </button>
                  </form>
                  <button
                    @click="downloadSample('medicines', 'excel')"
                    class="w-full mt-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                  >
                    Download Sample
                  </button>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Customers</h4>
                  <form @submit.prevent="importData('customers')" class="space-y-3">
                    <input
                      type="file"
                      ref="customersFile"
                      accept=".xlsx,.xls,.csv"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="updateExisting.customers"
                        class="mr-2"
                      />
                      Update existing records
                    </label>
                    <button
                      type="submit"
                      :disabled="isImporting.customers"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                    >
                      <span v-if="isImporting.customers">Importing...</span>
                      <span v-else>Import Customers</span>
                    </button>
                  </form>
                  <button
                    @click="downloadSample('customers', 'excel')"
                    class="w-full mt-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                  >
                    Download Sample
                  </button>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2">Stocks</h4>
                  <form @submit.prevent="importData('stocks')" class="space-y-3">
                    <input
                      type="file"
                      ref="stocksFile"
                      accept=".xlsx,.xls,.csv"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="updateExisting.stocks"
                        class="mr-2"
                      />
                      Update existing records
                    </label>
                    <button
                      type="submit"
                      :disabled="isImporting.stocks"
                      class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                    >
                      <span v-if="isImporting.stocks">Importing...</span>
                      <span v-else>Import Stocks</span>
                    </button>
                  </form>
                  <button
                    @click="downloadSample('stocks', 'excel')"
                    class="w-full mt-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                  >
                    Download Sample
                  </button>
                </div>
              </div>
            </div>

            <!-- Validation Rules -->
            <div class="mb-8">
              <h3 class="text-lg font-semibold mb-4">Import Validation Rules</h3>
              <div class="space-y-4">
                <div v-for="(rules, type) in validationRules" :key="type" class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-medium mb-2 capitalize">{{ type }}</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                    <div v-for="(rule, field) in rules" :key="field" class="flex justify-between">
                      <span class="font-medium">{{ field }}:</span>
                      <span class="text-gray-600">{{ rule }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const medicinesFile = ref(null)
const customersFile = ref(null)
const stocksFile = ref(null)

const isImporting = reactive({
  medicines: false,
  customers: false,
  stocks: false
})

const updateExisting = reactive({
  medicines: false,
  customers: false,
  stocks: false
})

const validationRules = ref({})

const exportData = async (type, format) => {
  try {
    const response = await fetch(`/data-export/export-${type}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        format: format,
        filters: {}
      }),
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `${type}_export_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.${format === 'excel' ? 'xlsx' : 'csv'}`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)

      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: `${type} exported successfully`,
      })
    } else {
      throw new Error('Export failed')
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Export failed: ' + error.message,
    })
  }
}

const importData = async (type) => {
  const fileRef = type === 'medicines' ? medicinesFile : type === 'customers' ? customersFile : stocksFile

  if (!fileRef.value.files[0]) {
    await Swal.fire({
      icon: 'warning',
      title: 'Warning!',
      text: 'Please select a file to import',
    })
    return
  }

  isImporting[type] = true

  try {
    const formData = new FormData()
    formData.append('file', fileRef.value.files[0])
    formData.append('update_existing', updateExisting[type])

    const response = await fetch(`/data-export/import-${type}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: result.message,
      })

      // Clear the file input
      fileRef.value.value = ''
    } else {
      throw new Error(result.message)
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Import failed: ' + error.message,
    })
  } finally {
    isImporting[type] = false
  }
}

const downloadSample = async (type, format) => {
  try {
    const response = await fetch(`/data-export/sample`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        type: type,
        format: format
      }),
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `${type}_sample.${format === 'excel' ? 'xlsx' : 'csv'}`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
    } else {
      throw new Error('Download failed')
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Download failed: ' + error.message,
    })
  }
}

const loadValidationRules = async () => {
  try {
    const types = ['medicines', 'customers', 'stocks']

    for (const type of types) {
      const response = await fetch(`/data-export/validation-rules`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ type: type }),
      })

      const result = await response.json()
      if (result.success) {
        validationRules.value[type] = result.data
      }
    }
  } catch (error) {
    console.error('Failed to load validation rules:', error)
  }
}

onMounted(() => {
  loadValidationRules()
})
</script>
