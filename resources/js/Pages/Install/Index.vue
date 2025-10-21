<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl w-full space-y-8 overflow-hidden">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center mb-4">
          <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900">PharmaCare Modern</h2>
        <p class="mt-2 text-lg text-gray-600">Pharmacy Management System Installation</p>
      </div>

      <!-- Progress Steps -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-8">
          <!-- Desktop stepper -->
          <div class="hidden sm:flex items-center justify-center space-x-4 mb-4">
            <div v-for="(step, index) in steps" :key="index" class="flex items-center">
              <div :class="[
                'flex items-center justify-center w-10 h-10 rounded-full text-sm font-medium',
                currentStep >= index ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
              ]">
                {{ index + 1 }}
              </div>
              <span :class="[
                'ml-3 text-sm font-medium',
                currentStep >= index ? 'text-blue-600' : 'text-gray-500'
              ]">
                {{ step }}
              </span>
              <div v-if="index < steps.length - 1" :class="[
                'ml-6 w-20 h-0.5',
                currentStep > index ? 'bg-blue-600' : 'bg-gray-200'
              ]"></div>
            </div>
          </div>

          <!-- Mobile stepper -->
          <div class="sm:hidden">
            <div class="flex items-center justify-center space-x-2 mb-3">
              <div v-for="(step, index) in steps" :key="index" class="flex items-center">
                <div :class="[
                  'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium',
                  currentStep >= index ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
                ]">
                  {{ index + 1 }}
                </div>
                <div v-if="index < steps.length - 1" :class="[
                  'ml-2 w-8 h-0.5',
                  currentStep > index ? 'bg-blue-600' : 'bg-gray-200'
                ]"></div>
              </div>
            </div>
            <div class="text-center">
              <span class="text-sm font-medium text-gray-600">
                Step {{ currentStep + 1 }} of {{ steps.length }}: {{ steps[currentStep] }}
              </span>
            </div>
          </div>
        </div>

        <!-- Step 1: Requirements Check -->
        <div v-if="currentStep === 0" class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-900">System Requirements</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(req, key) in requirements" :key="key" class="flex items-center justify-between p-4 border rounded-lg" :class="req.status ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
              <div>
                <div class="font-medium text-gray-900">{{ req.name }}</div>
                <div class="text-sm text-gray-600">Required: {{ req.required }}</div>
                <div class="text-sm text-gray-600">Current: {{ req.current }}</div>
              </div>
              <div :class="req.status ? 'text-green-600' : 'text-red-600'">
                <svg v-if="req.status" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              @click="nextStep"
              :disabled="!allRequirementsMet"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next Step
            </button>
          </div>
        </div>

        <!-- Step 2: Permissions Check -->
        <div v-if="currentStep === 1" class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-900">Directory Permissions</h3>

          <div class="space-y-4">
            <div v-for="(perm, key) in permissions" :key="key" class="flex items-center justify-between p-4 border rounded-lg" :class="perm.writable ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
              <div>
                <div class="font-medium text-gray-900">{{ key }}</div>
                <div class="text-sm text-gray-600">{{ perm.path }}</div>
              </div>
              <div :class="perm.writable ? 'text-green-600' : 'text-red-600'">
                <svg v-if="perm.writable" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="flex justify-between">
            <button
              @click="prevStep"
              class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
            >
              Previous
            </button>
            <button
              @click="nextStep"
              :disabled="!allPermissionsMet"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next Step
            </button>
          </div>
        </div>

        <!-- Step 3: Database Configuration -->
        <div v-if="currentStep === 2" class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-900">Database Configuration</h3>

          <form @submit.prevent="testDatabase" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Database Host</label>
                <input
                  v-model="databaseConfig.db_host"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="localhost"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Database Port</label>
                <input
                  v-model="databaseConfig.db_port"
                  type="number"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="3306"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Database Name</label>
                <input
                  v-model="databaseConfig.db_name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="pharmacare"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Database Username</label>
                <input
                  v-model="databaseConfig.db_username"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="root"
                  required
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Database Password</label>
                <input
                  v-model="databaseConfig.db_password"
                  type="password"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter database password"
                />
              </div>
            </div>

            <div class="flex justify-between">
              <button
                type="button"
                @click="prevStep"
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
              >
                Previous
              </button>
              <button
                type="submit"
                :disabled="isTestingDatabase"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                <span v-if="isTestingDatabase">Testing...</span>
                <span v-else>Test Connection</span>
              </button>
            </div>
          </form>

          <div v-if="databaseTestResult" class="p-4 rounded-lg" :class="databaseTestResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
            <div class="flex items-center">
              <svg v-if="databaseTestResult.success" class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              <span :class="databaseTestResult.success ? 'text-green-800' : 'text-red-800'">
                {{ databaseTestResult.message }}
              </span>
            </div>
          </div>

          <div v-if="databaseTestResult && databaseTestResult.success" class="flex justify-end">
            <button
              @click="nextStep"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Next Step
            </button>
          </div>
        </div>

        <!-- Step 4: Application Configuration -->
        <div v-if="currentStep === 3" class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-900">Application Configuration</h3>

          <form @submit.prevent="install" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Application Name</label>
                <input
                  v-model="appConfig.app_name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="PharmaCare Modern"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Application URL</label>
                <input
                  v-model="appConfig.app_url"
                  type="url"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="https://your-domain.com"
                  required
                />
              </div>
            </div>

            <div class="border-t pt-6">
              <h4 class="text-lg font-medium text-gray-900 mb-4">Admin Account</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Admin Name</label>
                  <input
                    v-model="appConfig.admin_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Administrator"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                  <input
                    v-model="appConfig.admin_email"
                    type="email"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="admin@pharmacare.com"
                    required
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Admin Password</label>
                  <input
                    v-model="appConfig.admin_password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter a strong password"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="border-t pt-6">
              <h4 class="text-lg font-medium text-gray-900 mb-4">Company Information</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Company Name</label>
                  <input
                    v-model="appConfig.company_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Your Pharmacy Name"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Company Email</label>
                  <input
                    v-model="appConfig.company_email"
                    type="email"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="info@yourpharmacy.com"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Company Phone</label>
                  <input
                    v-model="appConfig.company_phone"
                    type="tel"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="+1-555-0123"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Company Address</label>
                  <input
                    v-model="appConfig.company_address"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="123 Main Street, City, State"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="flex justify-between">
              <button
                type="button"
                @click="prevStep"
                class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
              >
                Previous
              </button>
              <button
                type="submit"
                :disabled="isInstalling"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
              >
                <span v-if="isInstalling">Installing...</span>
                <span v-else>Install Application</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Step 5: Installation Complete -->
        <div v-if="currentStep === 4" class="text-center space-y-6">
          <div class="mx-auto h-16 w-16 bg-green-600 rounded-full flex items-center justify-center">
            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900">Installation Complete!</h3>
          <p class="text-lg text-gray-600">PharmaCare Modern has been successfully installed.</p>
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-medium text-blue-900 mb-2">Login Credentials:</h4>
            <p class="text-blue-800">Email: {{ appConfig.admin_email }}</p>
            <p class="text-blue-800">Password: [The password you entered]</p>
          </div>
          <button
            @click="goToLogin"
            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-lg font-medium"
          >
            Go to Login
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
  requirements: Object,
  permissions: Object,
})

const currentStep = ref(0)
const steps = ['Requirements', 'Permissions', 'Database', 'Configuration', 'Complete']

const databaseConfig = reactive({
  db_host: 'localhost',
  db_port: 3306,
  db_name: 'pharmacare',
  db_username: 'root',
  db_password: '',
})

const appConfig = reactive({
  app_name: 'PharmaCare Modern',
  app_url: window.location.origin,
  admin_name: 'Administrator',
  admin_email: 'admin@pharmacare.com',
  admin_password: '',
  company_name: '',
  company_email: '',
  company_phone: '',
  company_address: '',
})

const isTestingDatabase = ref(false)
const isInstalling = ref(false)
const databaseTestResult = ref(null)

const allRequirementsMet = computed(() => {
  return Object.values(props.requirements).every(req => req.status)
})

const allPermissionsMet = computed(() => {
  return Object.values(props.permissions).every(perm => perm.writable)
})

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const testDatabase = async () => {
  isTestingDatabase.value = true
  databaseTestResult.value = null

  try {
    const response = await fetch('/install/database', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(databaseConfig),
    })

    const result = await response.json()
    databaseTestResult.value = result

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Database connection successful',
      })
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: result.message,
      })
    }
  } catch (error) {
    databaseTestResult.value = {
      success: false,
      message: 'Failed to test database connection',
    }
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to test database connection',
    })
  } finally {
    isTestingDatabase.value = false
  }
}

const install = async () => {
  isInstalling.value = true

  try {
    const response = await fetch('/install', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(appConfig),
    })

    const result = await response.json()

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: result.message,
      })
      currentStep.value = 4 // Go to completion step
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: result.message,
      })
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Installation failed. Please try again.',
    })
  } finally {
    isInstalling.value = false
  }
}

const goToLogin = () => {
  router.visit('/login')
}
</script>
