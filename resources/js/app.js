import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import 'select2';
import Swal from 'sweetalert2';
import 'datatables.net-bs5';

window.$ = $;
window.jQuery = $;
window.bootstrap = bootstrap;
window.Swal = Swal;

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

    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });
    }

    if ($.fn.dataTable) {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                emptyTable: 'Tidak ada data yang tersedia',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                infoFiltered: '(disaring dari _MAX_ total data)',
                lengthMenu: 'Tampilkan _MENU_ data',
                loadingRecords: 'Memuat...',
                processing: 'Memproses...',
                search: 'Cari:',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya',
                },
            },
        });
    }
});

// Utility function for SweetAlert2 confirmation
window.confirmDelete = function (event) {
    event.preventDefault();
    const form = event.target.closest('form');

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
            form.submit();
        }
    });

    return false;
};

// Utility function for showing alerts
window.showAlert = function (title, message, type = 'info') {
    Swal.fire({
        title: title,
        text: message,
        icon: type,
    });
};
