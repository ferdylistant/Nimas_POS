$(document).ready(function () {
    let baseUrl = window.location.origin;
    let tbProduct = $('#tb_Product').DataTable({
        "responsive": true,
        "autoWidth": true,
        scrollX: true,
        fixedColumns: {
            left: 0,
            right: 1
        },
        // columnDefs: [
        //   {
        //     targets: -1,
        //     className: 'dt-body-right',
        //     render: function (data, type, row) {
        //         return $('.sticky-dropdown').html();
        //       }
        //   }
        // ],
        // select: true,
        processing: true,
        serverSide: false,
        language: {
            searchPlaceholder: 'Cari...',
            sSearch: '',
            lengthMenu: '_MENU_ /halaman',
        },
        order: [[0, 'asc']],
        ajax: baseUrl + "/products",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', searchable: false, className: 'text-center text-secondary text-sm' },
            { data: 'product_code', name: 'product_code', title: 'Kode Produk', className: 'text-center text-secondary text-sm' },
            { data: 'product_name', name: 'product_name', title: 'Nama Produk', className: 'text-center text-secondary text-sm' },
            { data: 'category_name', name: 'category_name', title: 'Kategori Produk', className: 'text-center text-secondary text-sm' },
            { data: 'buying_price', name: 'buying_price', title: 'Harga Beli', className: 'text-center text-secondary text-sm' },
            { data: 'selling_price', name: 'selling_price', title: 'Harga Jual', className: 'text-center text-secondary text-sm' },
            { data: 'buying_date', name: 'buying_date', title: 'Tgl. Beli', className: 'text-center text-secondary text-sm' },
            { data: 'created_at', name: 'created_at', title: 'Tanggal Dibuat', className: 'text-center text-secondary text-sm' },
            { data: 'updated_at', name: 'updated_at', title: 'Tanggal Diubah', className: 'text-center text-secondary text-sm' },
            { data: 'action', name: 'action', title: 'Action', orderable: false, searchable: false, className: 'text-sm' },
        ]
    });

    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        // console.log(message);
        // notifToast("error",settings.jqXHR.statusText)
        if (settings && settings.jqXHR && settings.jqXHR.status == 401) {
            window.location.reload();
        }
    };
    function select2Category() {
        $('.select-category').select2({
            placeholder: 'Pilih...',
            allowClear: true,
            dropdownParent: $("#mdProduct"),
            width: 'resolve',
            ajax: {
                url: baseUrl + "/products/select2/category",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };

                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.category_name,
                                id: item.id,
                            };
                        }),
                    };
                },
                cache: true
            }
        });
    }
    function select2Supplier() {
        $('.select-supplier').select2({
            placeholder: 'Pilih...',
            allowClear: true,
            dropdownParent: $("#mdProduct"),
            width: 'resolve',
            ajax: {
                url: baseUrl + "/products/select2/supplier",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };

                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                            };
                        }),
                    };
                },
                cache: true
            }
        });
    }
    function ajaxModalProduct(el, type, id, name) {
        $.ajax({
            type: "GET",
            url: baseUrl + "/products/" + type + "/ajax-modal",
            data: {
                id: id,
                name: name
            },
            success: function (result) {
                el.find('#titleModalProduct').html(result.title);
                el.find(':submit').data('el', '#' + result.idForm);
                el.find(':submit').attr('form', result.idForm);
                el.find('#mainContent').html(result.html);
            },
            error: function (err) {
                console.log(err.responseJSON.message);
                notifToast("error", err.responseJSON.message);
            },
            complete: function () {
                let valid = jqueryValidation_("#fm_" + type + "Product", {
                    category_name: {
                        required: true,
                    }
                });
                select2Category();
                select2Supplier();
            }
        });
    }
    function ajaxAddProduct(el) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/products/store",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                // console.log(result);
                notifToast(result.status, result.message);
                $("#fm_addCategory").trigger("reset");
                if (result.status == "success") {
                    tbProduct.ajax.reload();
                    // $('#mdProduct').modal('hide');
                }
            },
            error: function (err) {
                notifToast("error", err.responseJSON.message);
            }
        });
    }
    function ajaxEditProduct(el) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/category/update",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    tbProduct.ajax.reload();
                    $('#mdProduct').modal('hide');
                }
            },
            error: function (err) {
                notifToast("error", err.responseJSON.message);
            }
        });
    }
    $('#mdProduct').on({
        'shown.bs.modal': function (e) {
            var type = $(e.relatedTarget).data('type');
            var id = $(e.relatedTarget).data('id');
            var name = $(e.relatedTarget).data('name');
            var el = $(this);
            ajaxModalProduct(el, type, id, name);
        },
        'hidden.bs.modal': function () {
            $(this).find('#mainContent').html('');
            $(this).find('#titleModalProduct').html('');
            $(this).find(':submit').data('el', '');
            $(this).find(':submit').attr('form', '');
        },
        'submit': function (e) {
            e.preventDefault();
            var val = $(this).find('[name="category_name"]').val();
            var ele = $(this).find(':submit').data('el');
            var el = $(ele);
            if (el.valid()) {
                var title = ele === '#fm_addCategory' ? 'Add Category' : 'Edit Category';
                var text = ele === '#fm_addCategory' ? 'add category (' + val + ')?' : 'edit category (' + val + ')?';
                $.confirm({
                    theme: 'modern',
                    icon: 'fa fa-question',
                    title: title,
                    content: text,
                    type: 'purple',
                    columnClass: 'col-md-6 col-md-offset-3',
                    animationBounce: 2.5,
                    buttons: {
                        confirm: {
                            text: 'Sure!',
                            btnClass: 'btn-purple',
                            action: function () {
                                title === 'Add Category' ? ajaxAddProduct(el) : ajaxEditProduct(el);
                            }
                        },
                        cancel: function () {
                            // $.alert('Canceled!');
                        }
                    }
                });
            }
        }
    })
});
