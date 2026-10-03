<template>
    <Head title="Backup Management" />
    <AuthenticatedLayout>
        <template #header>
            <h4 class="mb-sm-0 font-size-18">Backup management</h4>
            <div class="page-title-right d-flex flex-wrap align-items-center gap-2">
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    :disabled="isCreating"
                    @click="createBackup"
                >
                    <span v-if="isCreating">Creating...</span>
                    <span v-else>Create backup</span>
                </button>
            </div>
        </template>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Restore from backup</h5>
                <form class="row g-3 align-items-end" @submit.prevent="restoreBackup">
                    <div class="col-md-8">
                        <label class="form-label">Backup file (.zip)</label>
                        <input
                            ref="backupFile"
                            type="file"
                            accept=".zip"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-4">
                        <button
                            type="submit"
                            class="btn btn-success w-100"
                            :disabled="isRestoring"
                        >
                            <span v-if="isRestoring">Restoring...</span>
                            <span v-else>Restore</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <LunaTable title="Backups" empty-text="No backups found">
            <table class="table table-striped table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th>Backup name</th>
                        <th>Size</th>
                        <th>Created at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="backup in backups" :key="backup.name">
                        <td class="fw-semibold">{{ backup.name }}</td>
                        <td>{{ formatFileSize(backup.size) }}</td>
                        <td>{{ formatDate(backup.created_at) }}</td>
                        <td>
                            <div class="dt-actions">
                                <a
                                    :href="backup.download_url"
                                    class="btn btn-sm btn-icon btn-soft-primary"
                                    title="Download"
                                >
                                    <i class="bi bi-download"></i>
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon btn-soft-danger"
                                    title="Delete"
                                    @click="deleteBackup(backup.name)"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </LunaTable>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import LunaTable from '@/Components/LunaTable.vue'
import { confirmDelete, notify } from '@/Composables/confirmDelete'

defineProps({
    backups: Array,
})

const isCreating = ref(false)
const isRestoring = ref(false)
const backupFile = ref(null)

const csrfToken = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const createBackup = async () => {
    isCreating.value = true

    try {
        const response = await fetch('/backup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
            },
        })

        const result = await response.json()

        if (result.success) {
            await notify({ title: 'Success', text: result.message })
            router.reload()
        } else {
            throw new Error(result.message)
        }
    } catch (error) {
        await notify({ title: 'Error', text: error.message, tone: 'error' })
    } finally {
        isCreating.value = false
    }
}

const restoreBackup = async () => {
    if (!backupFile.value?.files?.[0]) {
        await notify({ title: 'Warning', text: 'Please select a backup file', tone: 'error' })
        return
    }

    const confirmed = await confirmDelete({
        title: 'Restore this backup?',
        text: 'This will restore the backup and may overwrite current data. This action cannot be undone.',
    })

    if (!confirmed.isConfirmed) return

    isRestoring.value = true

    try {
        const formData = new FormData()
        formData.append('backup_file', backupFile.value.files[0])

        const response = await fetch('/backup/restore', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: formData,
        })

        const result = await response.json()

        if (result.success) {
            await notify({ title: 'Success', text: result.message })
            router.reload()
        } else {
            throw new Error(result.message)
        }
    } catch (error) {
        await notify({ title: 'Error', text: error.message, tone: 'error' })
    } finally {
        isRestoring.value = false
        if (backupFile.value) {
            backupFile.value.value = ''
        }
    }
}

const deleteBackup = async (backupName) => {
    const confirmed = await confirmDelete({ title: 'Delete this backup?' })

    if (!confirmed.isConfirmed) return

    try {
        const response = await fetch(`/backup/${backupName}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
            },
        })

        const result = await response.json()

        if (result.success) {
            await notify({ title: 'Deleted', text: result.message })
            router.reload()
        } else {
            throw new Error(result.message)
        }
    } catch (error) {
        await notify({ title: 'Could not delete', text: error.message, tone: 'error' })
    }
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'

    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}
</script>
