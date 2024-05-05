$(document).ready(function () {
    let baseUrl = window.location.origin;
    let id = window.location.pathname.split("/").pop();
    let tbSupplier = $('#tableSupplier').DataTable({
        "responsive": true,
        "autoWidth": true,
        scrollX: true,
        scrollY: 300,
        fixedColumns: {
            left: 0,
            right: 1
        },
        processing: true,
        serverSide: false,
        language: {
            searchPlaceholder: 'Cari...',
            sSearch: '',
            lengthMenu: '_MENU_ /halaman',
        },
        order: [[0, 'asc']],
        ajax: baseUrl + "/people/supplier/detail/"+id,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', searchable: false, className: 'text-center text-secondary text-sm' },
            { data: 'product_code', name: 'product_code', title: 'Kode Produk', className: 'text-center text-secondary text-sm' },
            { data: 'product_name', name: 'product_name', title: 'Nama Produk', className: 'text-center text-secondary text-sm' },
            { data: 'product_qty', name: 'product_qty', title: 'Stok', className: 'text-center text-secondary text-sm' },
            { data: 'buying_price', name: 'buying_price', title: 'Harga Beli', className: 'text-center text-secondary text-sm' },
            { data: 'buying_date', name: 'buying_date', title: 'Stok', className: 'text-center text-secondary text-sm' },
            { data: 'action', name: 'action', title: 'Action', orderable: false, searchable: false, className: 'text-center text-sm' },
        ]
    });

});
