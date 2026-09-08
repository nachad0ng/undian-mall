<style>
    /* Sesuaikan DataTables dengan tema Tabler */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 0;
        font-size: 0.875rem;
        color: var(--tblr-secondary);
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--tblr-border-color);
        border-radius: var(--tblr-border-radius);
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        color: var(--tblr-body-color);
        background-color: var(--tblr-bg-forms);
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: var(--tblr-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--tblr-primary-rgb), 0.25);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--tblr-border-color);
        border-radius: var(--tblr-border-radius);
        padding: 0.25rem 2rem 0.25rem 0.5rem;
        font-size: 0.875rem;
        color: var(--tblr-body-color);
        background-color: var(--tblr-bg-forms);
        margin: 0 0.25rem;
    }

    /* Hilangkan border bawaan DataTables */
    table.dataTable {
        border-collapse: collapse !important;
        margin: 0 !important;
    }

    table.dataTable>thead>tr>th {
        border-bottom: 1px solid var(--tblr-border-color) !important;
        padding: 0.75rem 0.75rem !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--tblr-secondary);
        font-weight: 600;
    }

    table.dataTable>thead>tr>th.sorting,
    table.dataTable>thead>tr>th.sorting_asc,
    table.dataTable>thead>tr>th.sorting_desc {
        cursor: pointer;
    }

    table.dataTable>tbody>tr>td {
        padding: 0.75rem 0.75rem !important;
        vertical-align: middle;
        border-top: 1px solid var(--tblr-border-color) !important;
    }

    table.dataTable>tbody>tr:hover {
        background-color: var(--tblr-active-bg);
    }

    /* Padding tepi kiri/kanan mengikuti pola card-table Tabler */
    table.dataTable.card-table>thead>tr>th:first-child,
    table.dataTable.card-table>tbody>tr>td:first-child {
        padding-left: 1.5rem !important;
    }

    table.dataTable.card-table>thead>tr>th:last-child,
    table.dataTable.card-table>tbody>tr>td:last-child {
        padding-right: 1.5rem !important;
    }

    /* Loading indicator */
    .dataTables_processing {
        background: var(--tblr-bg-surface, #fff) !important;
        border: 1px solid var(--tblr-border-color) !important;
        border-radius: var(--tblr-border-radius) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        color: var(--tblr-body-color) !important;
        font-size: 0.875rem !important;
        padding: 0.6rem 1.25rem !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .alert {
        overflow: hidden;
        transition: opacity 0.3s ease, max-height 0.4s ease, margin 0.4s ease, padding 0.4s ease;
        max-height: 200px;
    }

    .alert.collapsing-out {
        opacity: 0;
        max-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ === 'undefined' || !$.fn.dataTable) {
            return;
        }

        $.extend(true, $.fn.dataTable.defaults, {
            rowId: 'id',
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            processing: true,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div><span>Memuat data...</span>',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6"></path></svg>',
                    previous: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6"></path></svg>',
                },
            },
            dom: "<'card-header d-flex align-items-center justify-content-between'l<'ms-auto'f>>" +
                "<'table-responsive't>" +
                "<'card-footer d-flex align-items-center justify-content-between'i<'ms-auto'p>>",
        });

        if (@json($ajax_same_page ?? false)) {
            $.extend(true, $.fn.dataTable.defaults, {
                serverSide: true,
                ajax: window.location.href,
            });
        }
    });

    function DataTableDetectClick(event, url) {
        if ($(event.target).closest('a, button').length) {
            return;
        }

        switch (event.which) {
            // Left Click.
            case 1:
                if (event.ctrlKey || event.metaKey) {
                    window.open(url);
                } else {
                    window.location = url;
                }

                break;

                // Middle click.
            case 2:

                window.open(url);
                break;

                // Default behaviour for right click.
            case 3:
                return;
        }

        // Pass control back to default handler.
        return true;
    }

    // Clickable row
    function DataTableClickableRow(row, data, url) {
        $(row).css('cursor', 'pointer');

        var clickTimer;
        var isDragging = false;
        var isDoubleClick = false;

        $(document).on('mousedown', function() {
            isDragging = false;
        }).on('mousemove', function() {
            isDragging = true;
        }).on('mouseup', function() {
            isDragging = false;
        });

        $(row).on('dblclick', function(event) {
            isDoubleClick = true;
            clearTimeout(clickTimer);
        });

        $(row).on('mousedown', function(event) {
            clickTimer = setTimeout(function() {
                if (!isDragging || isDoubleClick) {
                    DataTableDetectClick(event, url);
                }
            }, 250);
        });
    }

    // Apply clickable row to all rows with data-href
    $(function() {
        $('tr[data-href]').each(function() {
            var url = $(this).attr('data-href');
            DataTableClickableRow(this, null, url);
        });
    });
</script>
