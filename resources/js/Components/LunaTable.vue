<script setup>
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

defineProps({
    title: { type: String, default: 'Records' },
    pagination: { type: Object, default: null },
});

const root = ref(null);
let tableApi = null;

const loadScript = (src) => new Promise((resolve, reject) => {
    if (document.querySelector(`script[data-minia="${src}"]`)) {
        resolve();
        return;
    }
    const script = document.createElement('script');
    script.src = src;
    script.dataset.minia = src;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(src));
    document.body.appendChild(script);
});

const init = async () => {
    const files = [
        '/minia/assets/libs/jquery/jquery.min.js',
        '/minia/assets/libs/datatables.net/js/jquery.dataTables.min.js',
        '/minia/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js',
    ];
    for (const file of files) {
        await loadScript(file);
    }
    await nextTick();
    const node = root.value?.querySelector('table');
    const $ = window.jQuery;
    if (!node || !$?.fn?.DataTable) {
        return;
    }
    if ($.fn.DataTable.isDataTable(node)) {
        $(node).DataTable().destroy();
    }
    node.classList.add('table', 'table-bordered', 'dt-responsive', 'nowrap', 'w-100');
    tableApi = $(node).DataTable();
};

onMounted(init);
onUnmounted(() => {
    tableApi?.destroy();
    tableApi = null;
});
</script>

<template>
    <div ref="root" class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
