$(document).ready(function() {
    let baseUrl = window.location.origin;
    let tbCategory = $('#tb_Category').DataTable({
        "responsive": true,
        "autoWidth": false,
        select: true,
        processing: true,
        serverSide: false,
        language: {
            searchPlaceholder: 'Cari...',
            sSearch: '',
            lengthMenu: '_MENU_ /halaman',
        },
        order: [[0, 'asc']],
        ajax: baseUrl + "/category",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', searchable: false, className: 'text-center' },
            { data: 'category_name', name: 'category_name', title: 'Kategori Produk' },
            { data: 'created_at', name: 'created_at', title: 'Tanggal Dibuat' },
            { data: 'updated_at', name: 'updated_at', title: 'Tanggal Diubah' },
            { data: 'action', name: 'action', title: 'Action', orderable: false },
        ]
    });
    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        // console.log(message);
        // notifToast("error",settings.jqXHR.statusText)
        if (settings && settings.jqXHR && settings.jqXHR.status == 401) {
            window.location.reload();
        }
    };
});
