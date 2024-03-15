$(document).ready(function () {
    let baseUrl = window.location.origin;
    let tbCategory = $('#tb_Product').DataTable({
        "responsive": true,
        "autoWidth": false,
        select: true,
        processing: true,
        serverSide: false,
        fixedColumns: {
            left: 0,
            right: 1
        },
        scrollX: true,
        scrollY: 300,
        language: {
            searchPlaceholder: 'Cari...',
            sSearch: '',
            lengthMenu: '_MENU_ /halaman',
        },
        order: [[0, 'asc']],
        drawCallback: () => {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('.tooltip-wrapper'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl, {
                        trigger: 'hover'
                    })
                })
                // console.log(tooltipTriggerList);
        },
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
            { data: 'action', name: 'action', title: 'Action', orderable: false, className: 'text-center text-sm' },
        ]
    });
    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        // console.log(message);
        // notifToast("error",settings.jqXHR.statusText)
        if (settings && settings.jqXHR && settings.jqXHR.status == 401) {
            window.location.reload();
        }
    };
    function ajaxModalCategory(el, type, id, name) {
        $.ajax({
            type: "GET",
            url: baseUrl + "/category/" + type + "/ajax-modal",
            data: {
                id: id,
                name: name
            },
            success: function (result) {
                el.find('#titleModalCategory').html(result.title);
                el.find(':submit').data('el','#'+result.idForm);
                el.find(':submit').attr('form',result.idForm);
                el.find('#mainContent').html(result.html);
            },
            error: function (err) {
                console.log(err.responseJSON.message);
                notifToast("error", err.responseJSON.message);
            },
            complete: function () {
                let valid = jqueryValidation_("#fm_" + type + "Category", {
                    category_name: {
                        required: true,
                    }
                });
            }
        });
    }
    function ajaxAddCategory(el) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/category/store",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                // console.log(result);
                notifToast(result.status, result.message);
                $("#fm_addCategory").trigger("reset");
                if (result.status == "success") {
                    tbCategory.ajax.reload();
                    // $('#mdCategory').modal('hide');
                }
            },
            error: function (err) {
                notifToast("error", err.responseJSON.message);
            }
        });
    }
    function ajaxEditCategory(el) {
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
                    tbCategory.ajax.reload();
                    $('#mdCategory').modal('hide');
                }
            },
            error: function (err) {
                notifToast("error", err.responseJSON.message);
            }
        });
    }
    $('#mdCategory').on({
        'shown.bs.modal': function (e) {
            var type = $(e.relatedTarget).data('type');
            var id = $(e.relatedTarget).data('id');
            var name = $(e.relatedTarget).data('name');
            var el = $(this);
            ajaxModalCategory(el, type, id, name);
        },
        'hidden.bs.modal': function () {
            $(this).find('#mainContent').html('');
            $(this).find('#titleModalCategory').html('');
            $(this).find(':submit').data('el','');
            $(this).find(':submit').attr('form','');
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
                            action: function() {
                                title === 'Add Category' ? ajaxAddCategory(el) : ajaxEditCategory(el);
                            }
                        },
                        cancel: function() {
                            // $.alert('Canceled!');
                        }
                    }
                });
            }
        }
    })
});