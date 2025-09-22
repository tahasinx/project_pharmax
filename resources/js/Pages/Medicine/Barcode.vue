<template>
    <Head title="Generate Barcode" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Generate Barcode - {{ medicine.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('medicines.show', medicine.id)"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Medicine
                    </Link>
                    <button @click="generateBarcode"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Generate New Barcode
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Medicine Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Medicine Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Medicine Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ medicine.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Generic Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ medicine.generic_name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product ID</label>
                                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ medicine.product_id }}</p>
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

                        <!-- Barcode Display -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Generated Barcode</h3>
                            <div class="flex justify-center">
                                <div class="bg-white p-8 border-2 border-gray-200 rounded-lg shadow-lg">
                                    <!-- Barcode will be generated here -->
                                    <div v-if="barcodeData" class="text-center">
                                        <div class="mb-4">
                                            <canvas ref="barcodeCanvas" width="300" height="100"></canvas>
                                        </div>
                                        <p class="text-sm text-gray-600 font-mono">{{ barcodeData }}</p>
                                        <p class="text-xs text-gray-500 mt-2">{{ medicine.name }}</p>
                                    </div>
                                    <div v-else class="text-center text-gray-500">
                                        <div class="flex items-center justify-center">
                                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-2"></div>
                                            <p>Generating barcode...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barcode Settings -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Barcode Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode Type</label>
                                    <select v-model="barcodeSettings.type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="code128">Code 128</option>
                                        <option value="code39">Code 39</option>
                                        <option value="ean13">EAN-13</option>
                                        <option value="upc">UPC</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Width</label>
                                    <input v-model.number="barcodeSettings.width"
                                           type="number"
                                           min="1"
                                           max="5"
                                           step="0.1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Height</label>
                                    <input v-model.number="barcodeSettings.height"
                                           type="number"
                                           min="50"
                                           max="200"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-center space-x-4">
                            <button @click="printBarcode"
                                    :disabled="!barcodeData"
                                    class="bg-green-500 hover:bg-green-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                                Print Barcode
                            </button>
                            <button @click="downloadBarcode"
                                    :disabled="!barcodeData"
                                    class="bg-purple-500 hover:bg-purple-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                                Download PNG
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    medicine: Object
})

const barcodeCanvas = ref(null)
const barcodeData = ref('')

const barcodeSettings = ref({
    type: 'code128',
    width: 2,
    height: 100
})

const generateBarcode = () => {
    // Generate barcode data (using product ID as the data)
    barcodeData.value = props.medicine.product_id

    // Draw barcode on canvas
    drawBarcode()
}

const drawBarcode = () => {
    if (!barcodeCanvas.value || !barcodeData.value) return

    const canvas = barcodeCanvas.value
    const ctx = canvas.getContext('2d')

    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height)

    // Simple barcode drawing (this is a basic implementation)
    // In a real application, you'd use a proper barcode library
    const barWidth = barcodeSettings.value.width
    const barHeight = barcodeSettings.value.height
    const startX = 20
    let currentX = startX

    // Draw start pattern
    ctx.fillStyle = 'black'
    ctx.fillRect(currentX, 10, barWidth, barHeight)
    currentX += barWidth * 2
    ctx.fillRect(currentX, 10, barWidth, barHeight)
    currentX += barWidth * 2

    // Draw data bars (simplified)
    for (let i = 0; i < barcodeData.value.length; i++) {
        const char = barcodeData.value[i]
        const charCode = char.charCodeAt(0)

        // Simple bar pattern based on character code
        for (let j = 0; j < 8; j++) {
            if ((charCode >> j) & 1) {
                ctx.fillRect(currentX, 10, barWidth, barHeight)
            }
            currentX += barWidth
        }
        currentX += barWidth // Space between characters
    }

    // Draw end pattern
    ctx.fillRect(currentX, 10, barWidth, barHeight)
    currentX += barWidth * 2
    ctx.fillRect(currentX, 10, barWidth, barHeight)
}

const printBarcode = () => {
    if (!barcodeData.value) return

    const printWindow = window.open('', '_blank')
    printWindow.document.write(`
        <html>
            <head>
                <title>Print Barcode - ${props.medicine.name}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 20px;
                    }
                    .barcode-container {
                        border: 1px solid #ccc;
                        padding: 20px;
                        margin: 20px auto;
                        max-width: 400px;
                    }
                    canvas {
                        border: 1px solid #ddd;
                        margin: 10px 0;
                    }
                    .medicine-info {
                        margin-top: 20px;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                <h2>Medicine Barcode</h2>
                <div class="barcode-container">
                    <canvas width="300" height="100"></canvas>
                    <div class="medicine-info">
                        <p><strong>${props.medicine.name}</strong></p>
                        <p>Product ID: ${props.medicine.product_id}</p>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                    </div>
                </div>
                <script>
                    // Redraw barcode in print window
                    const canvas = document.querySelector('canvas');
                    const ctx = canvas.getContext('2d');
                    const barWidth = ${barcodeSettings.value.width};
                    const barHeight = ${barcodeSettings.value.height};
                    const startX = 20;
                    let currentX = startX;

                    // Draw barcode
                    ctx.fillStyle = 'black';
                    // Start pattern
                    ctx.fillRect(currentX, 10, barWidth, barHeight);
                    currentX += barWidth * 2;
                    ctx.fillRect(currentX, 10, barWidth, barHeight);
                    currentX += barWidth * 2;

                    // Data bars
                    const data = '${barcodeData.value}';
                    for (let i = 0; i < data.length; i++) {
                        const char = data[i];
                        const charCode = char.charCodeAt(0);
                        for (let j = 0; j < 8; j++) {
                            if ((charCode >> j) & 1) {
                                ctx.fillRect(currentX, 10, barWidth, barHeight);
                            }
                            currentX += barWidth;
                        }
                        currentX += barWidth;
                    }

                    // End pattern
                    ctx.fillRect(currentX, 10, barWidth, barHeight);
                    currentX += barWidth * 2;
                    ctx.fillRect(currentX, 10, barWidth, barHeight);

                    // Auto print
                    window.onload = function() {
                        window.print();
                    };
                <\/script>
            </body>
        </html>
    `)
    printWindow.document.close()
}

const downloadBarcode = () => {
    if (!barcodeCanvas.value || !barcodeData.value) return

    const canvas = barcodeCanvas.value
    const link = document.createElement('a')
    link.download = `barcode-${props.medicine.product_id}.png`
    link.href = canvas.toDataURL()
    link.click()
}

// Generate barcode on component mount
onMounted(() => {
    // Small delay to ensure canvas is fully rendered
    setTimeout(() => {
        generateBarcode()
    }, 100)
})
</script>
