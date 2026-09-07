<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" crossorigin>
<style>
    /* Sesuaikan DataTables dengan tema Tabler */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 0.75rem 1rem;
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

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: var(--tblr-border-radius) !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem;
        color: var(--tblr-body-color) !important;
        border: 0 !important;
        background: transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: var(--tblr-active-bg) !important;
        color: var(--tblr-body-color) !important;
        border: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: var(--tblr-primary) !important;
        color: #fff !important;
        border: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: var(--tblr-disabled-color) !important;
        cursor: not-allowed;
    }

    /* Hilangkan border bawaan DataTables */
    table.dataTable {
        border-collapse: collapse !important;
        margin: 0 !important;
    }

    table.dataTable > thead > tr > th {
        border-bottom: 1px solid var(--tblr-border-color) !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--tblr-secondary);
        font-weight: 600;
    }

    table.dataTable > thead > tr > th.sorting,
    table.dataTable > thead > tr > th.sorting_asc,
    table.dataTable > thead > tr > th.sorting_desc {
        cursor: pointer;
    }

    table.dataTable > tbody > tr > td {
        padding: 0.75rem 1rem !important;
        vertical-align: middle;
        border-top: 1px solid var(--tblr-border-color) !important;
    }

    table.dataTable > tbody > tr:hover {
        background-color: var(--tblr-active-bg);
    }

    /* Loading overlay */
    .dataTables_processing {
        background: rgba(255, 255, 255, 0.8) !important;
        border: none !important;
        box-shadow: none !important;
        color: var(--tblr-secondary) !important;
        font-size: 0.875rem !important;
        padding: 2rem !important;
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

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" crossorigin></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js" crossorigin></script>

<script>    
  $.extend(true, $.fn.dataTable.defaults, {
    rowId: 'id',
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    columnDefs: [
      { targets: 'no-sort', orderable: false }
    ],
    language: {
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
      processing: true,
      serverSide: true,
      ajax: window.location.href,
    });
  }
</script>