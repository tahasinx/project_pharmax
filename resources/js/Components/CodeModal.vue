<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Background overlay -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Generated {{ modalType === 'qr' ? 'QR Code' : modalType === 'barcode' ? 'Barcode' : 'Codes' }} for {{ medicineName }}
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-4">
                                    Your {{ modalType === 'qr' ? 'QR code' : modalType === 'barcode' ? 'barcode' : 'QR code and barcode' }} {{ modalType === 'both' ? 'have' : 'has' }} been generated successfully. You can download or print {{ modalType === 'both' ? 'them' : 'it' }}.
                                </p>

                                <!-- Codes Display -->
                                <div :class="modalType === 'both' ? 'grid grid-cols-1 md:grid-cols-2 gap-6' : 'flex justify-center'">
                                    <!-- QR Code -->
                                    <div v-if="modalType === 'qr' || modalType === 'both'" class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">QR Code</h4>
                                        <div class="flex justify-center mb-3">
                                            <canvas ref="qrCodeCanvas" class="border border-gray-300 rounded"></canvas>
                                        </div>
                                        <div class="text-xs text-gray-600 mb-2">
                                            <strong>Data:</strong> {{ qrCodeData }}<br>
                                            <strong>Type:</strong> {{ qrCodeType }}
                                        </div>
                                        <button @click="downloadQrCode"
                                                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                            Download QR Code
                                        </button>
                                    </div>

                                    <!-- Barcode -->
                                    <div v-if="modalType === 'barcode' || modalType === 'both'" class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">Barcode</h4>
                                        <div class="flex justify-center mb-3">
                                            <canvas ref="barcodeCanvas" class="border border-gray-300 rounded"></canvas>
                                        </div>
                                        <div class="text-xs text-gray-600 mb-2">
                                            <strong>Data:</strong> {{ barcodeData }}<br>
                                            <strong>Type:</strong> {{ barcodeType }}
                                        </div>
                                        <button @click="downloadBarcode"
                                                class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                            Download Barcode
                                        </button>
                                    </div>
                                </div>

                                <!-- Combined Actions -->
                                <div v-if="modalType === 'both'" class="mt-6 bg-blue-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-blue-900 mb-3">Combined Actions</h4>
                                    <div class="flex space-x-3">
                                        <button @click="downloadBothCodes"
                                                class="flex-1 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                            Download Both Codes
                                        </button>
                                        <button @click="printBothCodes"
                                                class="flex-1 bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm">
                                            Print Both Codes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="saveCodes"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Codes
                    </button>
                    <button @click="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import QRCode from 'qrcode'
import JsBarcode from 'jsbarcode'

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    medicineName: {
        type: String,
        default: ''
    },
    qrCodeData: {
        type: String,
        default: ''
    },
    qrCodeType: {
        type: String,
        default: 'product_id'
    },
    barcodeData: {
        type: String,
        default: ''
    },
    barcodeType: {
        type: String,
        default: 'code128'
    },
    modalType: {
        type: String,
        default: 'both' // 'qr', 'barcode', or 'both'
    }
})

const emit = defineEmits(['close', 'save'])

const qrCodeCanvas = ref(null)
const barcodeCanvas = ref(null)

// Watch for modal opening to generate codes
watch(() => props.isOpen, async (isOpen) => {
    if (isOpen) {
        await nextTick()
        generateCodes()
    }
})

const generateCodes = async () => {
    if (props.qrCodeData && qrCodeCanvas.value) {
        try {
            await QRCode.toCanvas(qrCodeCanvas.value, props.qrCodeData, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            })
        } catch (error) {
            console.error('Error generating QR code:', error)
        }
    }

    if (props.barcodeData && barcodeCanvas.value) {
        try {
            JsBarcode(barcodeCanvas.value, props.barcodeData, {
                format: props.barcodeType,
                width: 2,
                height: 100,
                displayValue: true,
                fontSize: 12,
                margin: 10
            })
        } catch (error) {
            console.error('Error generating barcode:', error)
        }
    }
}

const downloadQrCode = () => {
    if (qrCodeCanvas.value) {
        const link = document.createElement('a')
        link.download = `qr-code-${props.medicineName.replace(/\s+/g, '-').toLowerCase()}.png`
        link.href = qrCodeCanvas.value.toDataURL()
        link.click()
    }
}

const downloadBarcode = () => {
    if (barcodeCanvas.value) {
        const link = document.createElement('a')
        link.download = `barcode-${props.medicineName.replace(/\s+/g, '-').toLowerCase()}.png`
        link.href = barcodeCanvas.value.toDataURL()
        link.click()
    }
}

const downloadBothCodes = () => {
    if (qrCodeCanvas.value && barcodeCanvas.value) {
        const combinedCanvas = document.createElement('canvas')
        const ctx = combinedCanvas.getContext('2d')

        // Set canvas size to accommodate both codes
        combinedCanvas.width = 400
        combinedCanvas.height = 350

        // Draw white background
        ctx.fillStyle = 'white'
        ctx.fillRect(0, 0, combinedCanvas.width, combinedCanvas.height)

        // Draw QR code
        ctx.drawImage(qrCodeCanvas.value, 50, 50)

        // Draw barcode
        ctx.drawImage(barcodeCanvas.value, 50, 280)

        // Add labels
        ctx.fillStyle = 'black'
        ctx.font = '16px Arial'
        ctx.fillText('QR Code', 50, 30)
        ctx.fillText('Barcode', 50, 260)

        // Download
        const link = document.createElement('a')
        link.download = `codes-${props.medicineName.replace(/\s+/g, '-').toLowerCase()}.png`
        link.href = combinedCanvas.toDataURL()
        link.click()
    }
}

const printBothCodes = () => {
    if (qrCodeCanvas.value && barcodeCanvas.value) {
        const combinedCanvas = document.createElement('canvas')
        const ctx = combinedCanvas.getContext('2d')

        // Set canvas size to accommodate both codes
        combinedCanvas.width = 400
        combinedCanvas.height = 350

        // Draw white background
        ctx.fillStyle = 'white'
        ctx.fillRect(0, 0, combinedCanvas.width, combinedCanvas.height)

        // Draw QR code
        ctx.drawImage(qrCodeCanvas.value, 50, 50)

        // Draw barcode
        ctx.drawImage(barcodeCanvas.value, 50, 280)

        // Add labels
        ctx.fillStyle = 'black'
        ctx.font = '16px Arial'
        ctx.fillText('QR Code', 50, 30)
        ctx.fillText('Barcode', 50, 260)

        // Print
        const printWindow = window.open('', '_blank')
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Codes - ${props.medicineName}</title>
                    <style>
                        body { margin: 0; padding: 20px; text-align: center; }
                        img { max-width: 100%; height: auto; }
                    </style>
                </head>
                <body>
                    <h2>${props.medicineName} - QR Code & Barcode</h2>
                    <img src="${combinedCanvas.toDataURL()}" alt="Codes">
                </body>
            </html>
        `)
        printWindow.document.close()
        printWindow.print()
    }
}

const saveCodes = () => {
    emit('save', {
        qrCodeData: props.qrCodeData,
        qrCodeType: props.qrCodeType,
        barcodeData: props.barcodeData,
        barcodeType: props.barcodeType
    })
}

const closeModal = () => {
    emit('close')
}
</script>
