<template>
    <Head title="Generate QR Code" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Generate QR Code - {{ medicine.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('medicines.show', medicine.id)"
                          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to Medicine
                    </Link>
                    <button @click="generateQrCode"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Generate New QR Code
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

                        <!-- QR Code Display -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Generated QR Code</h3>
                            <div class="flex justify-center">
                                <div class="bg-white p-8 border-2 border-gray-200 rounded-lg shadow-lg">
                                    <!-- QR Code will be generated here -->
                                    <div v-if="qrCodeData" class="text-center">
                                        <div class="mb-4">
                                            <canvas ref="qrCodeCanvas" width="200" height="200"></canvas>
                                        </div>
                                        <p class="text-sm text-gray-600 font-mono">{{ qrCodeData }}</p>
                                        <p class="text-xs text-gray-500 mt-2">{{ medicine.name }}</p>
                                    </div>
                                    <div v-else class="text-center text-gray-500">
                                        <div class="flex items-center justify-center">
                                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-2"></div>
                                            <p>Generating QR code...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Settings -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">QR Code Data</label>
                                    <select v-model="qrCodeSettings.dataType"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="product_id">Product ID</option>
                                        <option value="medicine_info">Medicine Info</option>
                                        <option value="custom">Custom Text</option>
                                    </select>
                                </div>
                                <div v-if="qrCodeSettings.dataType === 'custom'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Custom Text</label>
                                    <input v-model="qrCodeSettings.customText"
                                           type="text"
                                           placeholder="Enter custom text"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                    <input v-model.number="qrCodeSettings.size"
                                           type="number"
                                           min="100"
                                           max="400"
                                           step="50"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-center space-x-4">
                            <button @click="printQrCode"
                                    :disabled="!qrCodeData"
                                    class="bg-green-500 hover:bg-green-700 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded">
                                Print QR Code
                            </button>
                            <button @click="downloadQrCode"
                                    :disabled="!qrCodeData"
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
import { ref, computed, onMounted } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    medicine: Object
})

const qrCodeCanvas = ref(null)
const qrCodeData = ref('')

const qrCodeSettings = ref({
    dataType: 'product_id',
    customText: '',
    size: 200
})

const generateQrCode = () => {
    // Generate QR code data based on selected type
    switch (qrCodeSettings.value.dataType) {
        case 'product_id':
            qrCodeData.value = props.medicine.product_id
            break
        case 'medicine_info':
            qrCodeData.value = JSON.stringify({
                name: props.medicine.name,
                product_id: props.medicine.product_id,
                generic_name: props.medicine.generic_name,
                strength: props.medicine.strength,
                category: props.medicine.category?.name,
                manufacturer: props.medicine.manufacturer?.name
            })
            break
        case 'custom':
            qrCodeData.value = qrCodeSettings.value.customText
            break
    }

    // Draw QR code on canvas
    drawQrCode()
}

const drawQrCode = () => {
    if (!qrCodeCanvas.value || !qrCodeData.value) return

    const canvas = qrCodeCanvas.value
    const ctx = canvas.getContext('2d')
    const size = qrCodeSettings.value.size

    // Set canvas size
    canvas.width = size
    canvas.height = size

    // Clear canvas
    ctx.clearRect(0, 0, size, size)

    // Simple QR code drawing (this is a basic implementation)
    // In a real application, you'd use a proper QR code library like qrcode.js
    const moduleSize = Math.floor(size / 25) // 25x25 grid
    const offset = (size - (moduleSize * 25)) / 2

    // Draw a simple pattern (this is just a placeholder)
    // Real QR codes have specific patterns and error correction
    ctx.fillStyle = 'black'

    // Draw corner squares (finder patterns)
    drawFinderPattern(ctx, offset, offset, moduleSize)
    drawFinderPattern(ctx, offset + moduleSize * 18, offset, moduleSize)
    drawFinderPattern(ctx, offset, offset + moduleSize * 18, moduleSize)

    // Draw data modules (simplified)
    for (let row = 0; row < 25; row++) {
        for (let col = 0; col < 25; col++) {
            // Skip finder pattern areas
            if (isFinderPatternArea(row, col)) continue

            // Simple pattern based on data
            const dataIndex = (row * 25 + col) % qrCodeData.value.length
            const charCode = qrCodeData.value.charCodeAt(dataIndex)

            if ((charCode + row + col) % 2 === 0) {
                ctx.fillRect(
                    offset + col * moduleSize,
                    offset + row * moduleSize,
                    moduleSize,
                    moduleSize
                )
            }
        }
    }
}

const drawFinderPattern = (ctx, x, y, moduleSize) => {
    // Draw 7x7 finder pattern
    for (let row = 0; row < 7; row++) {
        for (let col = 0; col < 7; col++) {
            if (row === 0 || row === 6 || col === 0 || col === 6 ||
                (row >= 2 && row <= 4 && col >= 2 && col <= 4)) {
                ctx.fillRect(
                    x + col * moduleSize,
                    y + row * moduleSize,
                    moduleSize,
                    moduleSize
                )
            }
        }
    }
}

const isFinderPatternArea = (row, col) => {
    // Check if position is in finder pattern area
    return (row < 9 && col < 9) ||
           (row < 9 && col > 15) ||
           (row > 15 && col < 9)
}

const printQrCode = () => {
    if (!qrCodeData.value) return

    const printWindow = window.open('', '_blank')
    printWindow.document.write(`
        <html>
            <head>
                <title>Print QR Code - ${props.medicine.name}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 20px;
                    }
                    .qrcode-container {
                        border: 1px solid #ccc;
                        padding: 20px;
                        margin: 20px auto;
                        max-width: 300px;
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
                <h2>Medicine QR Code</h2>
                <div class="qrcode-container">
                    <canvas width="${qrCodeSettings.value.size}" height="${qrCodeSettings.value.size}"></canvas>
                    <div class="medicine-info">
                        <p><strong>${props.medicine.name}</strong></p>
                        <p>Product ID: ${props.medicine.product_id}</p>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                    </div>
                </div>
                <script>
                    // Redraw QR code in print window
                    const canvas = document.querySelector('canvas');
                    const ctx = canvas.getContext('2d');
                    const size = ${qrCodeSettings.value.size};
                    const moduleSize = Math.floor(size / 25);
                    const offset = (size - (moduleSize * 25)) / 2;

                    // Draw QR code
                    ctx.fillStyle = 'black';

                    // Draw corner squares
                    function drawFinderPattern(x, y, moduleSize) {
                        for (let row = 0; row < 7; row++) {
                            for (let col = 0; col < 7; col++) {
                                if (row === 0 || row === 6 || col === 0 || col === 6 ||
                                    (row >= 2 && row <= 4 && col >= 2 && col <= 4)) {
                                    ctx.fillRect(x + col * moduleSize, y + row * moduleSize, moduleSize, moduleSize);
                                }
                            }
                        }
                    }

                    drawFinderPattern(offset, offset, moduleSize);
                    drawFinderPattern(offset + moduleSize * 18, offset, moduleSize);
                    drawFinderPattern(offset, offset + moduleSize * 18, moduleSize);

                    // Draw data modules
                    const data = '${qrCodeData.value}';
                    for (let row = 0; row < 25; row++) {
                        for (let col = 0; col < 25; col++) {
                            if ((row < 9 && col < 9) || (row < 9 && col > 15) || (row > 15 && col < 9)) continue;

                            const dataIndex = (row * 25 + col) % data.length;
                            const charCode = data.charCodeAt(dataIndex);

                            if ((charCode + row + col) % 2 === 0) {
                                ctx.fillRect(offset + col * moduleSize, offset + row * moduleSize, moduleSize, moduleSize);
                            }
                        }
                    }

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

const downloadQrCode = () => {
    if (!qrCodeCanvas.value || !qrCodeData.value) return

    const canvas = qrCodeCanvas.value
    const link = document.createElement('a')
    link.download = `qrcode-${props.medicine.product_id}.png`
    link.href = canvas.toDataURL()
    link.click()
}

// Generate QR code on component mount
onMounted(() => {
    // Small delay to ensure canvas is fully rendered
    setTimeout(() => {
        generateQrCode()
    }, 100)
})
</script>
