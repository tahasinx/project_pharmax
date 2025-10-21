<template>
  <AuthenticatedLayout>
    <Head title="Backup Management" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Backup Management</h2>
              <button
                @click="createBackup"
                :disabled="isCreating"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
              >
                <span v-if="isCreating">Creating...</span>
                <span v-else>Create Backup</span>
              </button>
            </div>

            <!-- Restore Section -->
            <div class="mb-8 p-6 bg-gray-50 rounded-lg">
              <h3 class="text-lg font-semibold mb-4">Restore from Backup</h3>
              <form @submit.prevent="restoreBackup" class="flex items-center gap-4">
                <input
                  type="file"
                  ref="backupFile"
                  accept=".zip"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />
                <button
                  type="submit"
                  :disabled="isRestoring"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                >
                  <span v-if="isRestoring">Restoring...</span>
                  <span v-else>Restore</span>
                </button>
              </form>
            </div>

            <!-- Backups List -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Backup Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Size
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Created At
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="backup in backups" :key="backup.name">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ backup.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatFileSize(backup.size) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(backup.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <a
                          :href="backup.download_url"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Download
                        </a>
                        <button
                          @click="deleteBackup(backup.name)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="backups.length === 0">
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                      No backups found
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
  backups: Array
})

const isCreating = ref(false)
const isRestoring = ref(false)
const backupFile = ref(null)

const createBackup = async () => {
  isCreating.value = true

  try {
    const response = await fetch('/backup', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })

    const result = await response.json()

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: result.message,
      })

      // Reload the page to show the new backup
      router.reload()
    } else {
      throw new Error(result.message)
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message,
    })
  } finally {
    isCreating.value = false
  }
}

const restoreBackup = async () => {
  if (!backupFile.value.files[0]) {
    await Swal.fire({
      icon: 'warning',
      title: 'Warning!',
      text: 'Please select a backup file',
    })
    return
  }

  const confirmed = await Swal.fire({
    title: 'Are you sure?',
    text: "This will restore the backup and may overwrite current data. This action cannot be undone!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, restore it!'
  })

  if (!confirmed.isConfirmed) return

  isRestoring.value = true

  try {
    const formData = new FormData()
    formData.append('backup_file', backupFile.value.files[0])

    const response = await fetch('/backup/restore', {
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

      // Reload the page
      router.reload()
    } else {
      throw new Error(result.message)
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message,
    })
  } finally {
    isRestoring.value = false
    backupFile.value.value = ''
  }
}

const deleteBackup = async (backupName) => {
  const confirmed = await Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  })

  if (!confirmed.isConfirmed) return

  try {
    const response = await fetch(`/backup/${backupName}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })

    const result = await response.json()

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: result.message,
      })

      // Reload the page
      router.reload()
    } else {
      throw new Error(result.message)
    }
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message,
    })
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString()
}
</script>
