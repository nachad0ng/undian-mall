// Initialize Bootstrap
import * as bootstrap from 'bootstrap';

// Global JavaScript functions
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize Select2
    if (jQuery) {
        jQuery('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });
    }

    // Initialize DataTables
    if (jQuery.fn.dataTable) {
        jQuery('.datatable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json',
            },
        });
    }
});

// Utility function for SweetAlert2 confirmation
window.confirmDelete = function (url) {
    const Swal = window.Swal || require('sweetalert2').default;
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
};

// Utility function for showing alerts
window.showAlert = function (title, message, type = 'info') {
    const Swal = window.Swal || require('sweetalert2').default;
    Swal.fire({
        title: title,
        text: message,
        icon: type,
    });
};
