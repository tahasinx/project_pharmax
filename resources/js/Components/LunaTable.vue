<script setup>
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    title: { type: String, default: 'Records' },
    pagination: { type: Object, default: null },
    emptyText: { type: String, default: 'No records found' },
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

const loadStyle = (href) => {
    if (document.querySelector(`link[data-minia="${href}"]`)) {
        return;
    }
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    link.dataset.minia = href;
    document.head.appendChild(link);
};

const init = async () => {
    loadStyle('/minia/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css');
    const files = [
        '/minia/assets/libs/jquery/jquery.min.js',
        '/minia/assets/libs/datatables.net/js/jquery.dataTables.min.js',
        '/minia/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js',
        '/minia/assets/libs/jszip/jszip.min.js',
        '/minia/assets/libs/pdfmake/build/pdfmake.min.js',
        '/minia/assets/libs/pdfmake/build/vfs_fonts.js',
        '/minia/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js',
        '/minia/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js',
        '/minia/assets/libs/datatables.net-buttons/js/buttons.html5.min.js',
        '/minia/assets/libs/datatables.net-buttons/js/buttons.print.min.js',
        '/minia/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js',
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

    // Remove placeholder colspan rows — they break DataTables column counts.
    node.querySelectorAll('tbody tr').forEach((row) => {
        const cells = [...row.children];
        if (cells.some((cell) => cell.hasAttribute('colspan')) || cells.length === 0) {
            row.remove();
        }
    });

    node.classList.add('table', 'table-bordered', 'w-100');
    const headRow = node.querySelector('thead tr');
    if (headRow && !headRow.querySelector('.dt-index')) {
        headRow.insertAdjacentHTML('afterbegin', '<th class="dt-index">#</th>');
        node.querySelectorAll('tbody tr').forEach((row) => {
            if (row.querySelector('td')) {
                row.insertAdjacentHTML('afterbegin', '<td class="dt-index"></td>');
            }
        });
    }
    const exportColumns = ':visible:not(.dt-fit):not(.dt-index)';
    const button = (extend, icon, label) => ({
        extend,
        text: `<i class="bi ${icon}"></i><span>${label}</span>`,
        className: 'btn btn-sm btn-light',
        exportOptions: { columns: exportColumns },
    });
    const emptyHtml = `
        <div class="dt-empty">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <strong class="text-danger">${props.emptyText}</strong>
            <span>Nothing to show here yet</span>
        </div>
    `;
    tableApi = $(node).DataTable({
        autoWidth: false,
        responsive: false,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        dom: "<'dt-toolbar'<'dt-length'l><'dt-tools'<'dt-buttons'B>><'dt-filter'f>>rt<'dt-foot'<'dt-info'i><'dt-pages'p>>",
        language: {
            emptyTable: emptyHtml,
            zeroRecords: emptyHtml,
        },
        buttons: [
            button('copy', 'bi-clipboard', 'Copy'),
            button('csv', 'bi-filetype-csv', 'CSV'),
            button('excel', 'bi-file-earmark-excel', 'Excel'),
            button('pdf', 'bi-file-earmark-pdf', 'PDF'),
            button('print', 'bi-printer', 'Print'),
            {
                extend: 'colvis',
                text: '<i class="bi bi-layout-three-columns"></i><span>Columns</span>',
                className: 'btn btn-sm btn-light',
                columns: ':not(.dt-fit):not(.dt-index)',
            },
        ],
        columnDefs: [
            { targets: 0, orderable: false, searchable: false, width: '36px', className: 'dt-index' },
            { targets: -1, width: '1px', orderable: false, className: 'dt-fit' },
        ],
    });
    const syncRows = () => {
        const info = tableApi.page.info();
        const headers = [...node.querySelectorAll('thead th')].map((cell) => cell.textContent.replace(/[↑↓↕]/g, '').trim());
        const colCount = headers.length || node.querySelectorAll('thead th').length || 1;

        // Keep empty-state cell centered across the full table width.
        node.querySelectorAll('td.dataTables_empty').forEach((cell) => {
            cell.colSpan = colCount;
            cell.classList.add('dt-empty-cell');
            cell.classList.remove('dt-fit', 'dt-index');
            cell.style.textAlign = 'center';
            const row = cell.closest('tr');
            if (row) {
                row.querySelectorAll('td').forEach((sibling) => {
                    if (sibling !== cell) sibling.remove();
                });
            }
        });

        let number = info.start + 1;
        tableApi.rows({ page: 'current' }).every(function () {
            const row = this.node();
            if (row.querySelector('td.dataTables_empty')) {
                return;
            }
            row.querySelectorAll(':scope > td').forEach((cell, index) => {
                if (cell.classList.contains('dt-index')) {
                    cell.textContent = number;
                }
                cell.dataset.label = headers[index] || '';
            });
            number += 1;
        });
    };
    const toggleRow = (event) => {
        if (window.innerWidth > 767) {
            return;
        }
        if (event.target.closest('.dt-actions, a, button')) {
            return;
        }
        const row = event.target.closest('tbody tr');
        if (!row) {
            return;
        }
        row.classList.toggle('is-open');
    };
    syncRows();
    tableApi.on('draw', syncRows);
    node.addEventListener('click', toggleRow);
};

onMounted(init);
onUnmounted(() => {
    tableApi?.destroy();
    tableApi = null;
});
</script>

<template>
    <div ref="root" class="card dt-card">
        <div class="card-body">
            <slot />
        </div>
    </div>
</template>
