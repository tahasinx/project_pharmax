<template>
    <Head title="Generate Codes" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Generate Codes - {{ medicine.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('medicines.index')"
                          class="bg-green-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        All Medicines
                    </Link>
                    <Link :href="route('medicines.show', medicine.id)"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Medicine
                    </Link>
                    <button @click="generateAllCodes"
                            :disabled="isLoading"
                            class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded">
                        {{ isLoading ? 'Generating...' : 'Generate All Codes' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Medicine Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Medicine Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Medicine Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ medicine.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Product ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ medicine.product_id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Generic Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ medicine.generic_name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Strength</label>
                                <p class="mt-1 text-sm text-gray-900">{{ medicine.strength || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Category</label>
                                <p class="mt-1 text-sm text-gray-900">{{ medicine.category?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Manufacturer</label>
                                <p class="mt-1 text-sm text-gray-900">{{ medicine.manufacturer?.name || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Code Generation Interface -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Generate QR Code & Barcode</h3>
                            <p class="text-sm text-gray-500 mb-6">
                                Click the buttons below to generate QR codes and barcodes for this medicine.
                                The generated codes will be displayed in a modal where you can download or print them.
                            </p>

                            <!-- Generation Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <button @click="generateQrCode"
                                        :disabled="isLoading"
                                        class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ isLoading ? 'Generating...' : 'Generate QR Code' }}
                                </button>

                                <button @click="generateBarcode"
                                        :disabled="isLoading"
                                        class="bg-green-500 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ isLoading ? 'Generating...' : 'Generate Barcode' }}
                                </button>
                            </div>

                            <!-- Current Codes Status -->
                            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-blue-800">QR Code Status</h4>
                                            <p class="text-sm text-blue-700">
                                                {{ medicine.qr_code_data ? 'Generated' : 'Not Generated' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-green-800">Barcode Status</h4>
                                            <p class="text-sm text-green-700">
                                                {{ medicine.barcode_data ? 'Generated' : 'Not Generated' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Code Modal -->
        <CodeModal
            :is-open="showModal"
            :medicine-name="medicine.name"
            :qr-code-data="generatedQrCodeData"
            :qr-code-type="generatedQrCodeType"
            :barcode-data="generatedBarcodeData"
            :barcode-type="generatedBarcodeType"
            :modal-type="modalType"
            @close="closeModal"
            @save="saveCodes"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CodeModal from '@/Components/CodeModal.vue'

const props = defineProps({
    medicine: Object
})

const isLoading = ref(false)
const showModal = ref(false)
const modalType = ref('both') // 'qr', 'barcode', or 'both'
const generatedQrCodeData = ref('')
const generatedQrCodeType = ref('product_id')
const generatedBarcodeData = ref('')
const generatedBarcodeType = ref('code128')

const generateQrCode = async () => {
    isLoading.value = true

    // Generate QR code data based on medicine info
    const qrData = {
        name: props.medicine.name,
        product_id: props.medicine.product_id,
        generic_name: props.medicine.generic_name,
        strength: props.medicine.strength,
        category: props.medicine.category?.name,
        manufacturer: props.medicine.manufacturer?.name
    }

    generatedQrCodeData.value = JSON.stringify(qrData)
    generatedQrCodeType.value = 'medicine_info'
    generatedBarcodeData.value = '' // Clear barcode data
    modalType.value = 'qr' // Set modal type to QR only

    // Show modal
    showModal.value = true
    isLoading.value = false
}

const generateBarcode = async () => {
    isLoading.value = true

    // Generate barcode data using product ID
    generatedBarcodeData.value = props.medicine.product_id
    generatedBarcodeType.value = 'code128'
    generatedQrCodeData.value = '' // Clear QR code data
    modalType.value = 'barcode' // Set modal type to barcode only

    // Show modal
    showModal.value = true
    isLoading.value = false
}

const generateAllCodes = async () => {
    isLoading.value = true

    // Generate both codes
    const qrData = {
        name: props.medicine.name,
        product_id: props.medicine.product_id,
        generic_name: props.medicine.generic_name,
        strength: props.medicine.strength,
        category: props.medicine.category?.name,
        manufacturer: props.medicine.manufacturer?.name
    }

    generatedQrCodeData.value = JSON.stringify(qrData)
    generatedQrCodeType.value = 'medicine_info'
    generatedBarcodeData.value = props.medicine.product_id
    generatedBarcodeType.value = 'code128'
    modalType.value = 'both' // Set modal type to both

    // Show modal
    showModal.value = true
    isLoading.value = false
}

const closeModal = () => {
    showModal.value = false
}

const saveCodes = async (codesData) => {
    try {
        const saveData = {}

        // Only save the codes that were generated based on modal type
        if (modalType.value === 'qr' || modalType.value === 'both') {
            saveData.qr_code_data = codesData.qrCodeData
            saveData.qr_code_type = codesData.qrCodeType
            saveData.qr_code_image_path = null
        }

        if (modalType.value === 'barcode' || modalType.value === 'both') {
            saveData.barcode_data = codesData.barcodeData
            saveData.barcode_type = codesData.barcodeType
            saveData.barcode_image_path = null
        }

        await router.post(route('medicines.codes.save', props.medicine.id), saveData, {
            onSuccess: () => {
                showModal.value = false
                // Refresh the page to show updated status
                router.reload()
            }
        })
    } catch (error) {
        console.error('Error saving codes:', error)
    }
}
</script>
