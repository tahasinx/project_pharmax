<template>
    <div v-if="pagination.data && pagination.data.length > 0" class="mt-6 w-full flex items-center justify-end">
        <div class="text-sm text-gray-700 mr-4">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex items-center space-x-2">
            <!-- Previous Button -->
            <Link v-if="pagination.prev_page_url"
                  :href="pagination.prev_page_url"
                  class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                ← Previous
            </Link>
            <span v-else class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                ← Previous
            </span>

            <!-- Page Numbers -->
            <template v-if="pagination.links && pagination.links.length > 3">
                <Link v-for="link in pagination.links.slice(1, -1)"
                      :key="link.label"
                      :href="link.url"
                      v-html="link.label"
                      :class="[
                          link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                          'px-3 py-2 text-sm font-medium border rounded-lg transition-colors'
                      ]">
                </Link>
            </template>

            <!-- Next Button -->
            <Link v-if="pagination.next_page_url"
                  :href="pagination.next_page_url"
                  class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                Next →
            </Link>
            <span v-else class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                Next →
            </span>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    }
})
</script>
