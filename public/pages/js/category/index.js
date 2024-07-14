$(document).ready(function () {
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
        drawCallback: () => {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                })
            });
            var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'))
            const dropdown = dropdownTriggerList.map(dropdownToggleEl => {
                var instance = new bootstrap.Dropdown(dropdownToggleEl, {
                    // popperConfig(defaultBsPopperConfig) {
                    //     console.log(defaultBsPopperConfig);
                    //     return { ...defaultBsPopperConfig, strategy: "fixed" };
                    // },
                    boundary: "clippingParents",
                    rootBoundary: "viewport",
                    // strategy: "fixed",
                    display: "static",
                    // offset: [-300, 0],
                });

                // / /Attach event listeners to the dropdown trigger
                dropdownToggleEl.addEventListener("show.bs.dropdown", function (event) {
                    $(event.target).closest(".table").find(".dtfc-fixed-right").removeClass("z-index-9");
                    $(event.target).closest("td").addClass("z-index-3");
                });

                dropdownToggleEl.addEventListener("hide.bs.dropdown", function (event) {
                    $(event.target).closest("td").removeClass("z-index-3");
                });
            });
            // console.log(tooltipTriggerList);
        },
        order: [[0, 'asc']],
        ajax: baseUrl + "/products/category",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', searchable: false, className: 'text-center text-secondary text-sm' },
            { data: 'category_name', name: 'category_name', title: 'Kategori Produk', className: 'text-center text-secondary text-sm' },
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
            url: baseUrl + "/products/category/" + type + "/ajax-modal",
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
            url: baseUrl + "/products/category/store",
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
            url: baseUrl + "/products/category/update",
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
    function ajaxDeleteCategory(id) {
        $.ajax({
            type: "POST",
            url: baseUrl + "/products/category/delete",
            data: {
                id: id
            },
            async: true,
            success: function (result) {
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    tbCategory.ajax.reload();
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
    });
    $('#tb_Category').on('click','.btnDelete',function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        $.confirm({
            theme: 'modern',
            icon: 'fa fa-question',
            title: 'Delete Category',
            content: 'Are you sure you want to delete (' + name + ')?',
            type: 'red',
            columnClass: 'col-md-6 col-md-offset-3',
            animationBounce: 2.5,
            buttons: {
                confirm: {
                    text: 'Sure!',
                    btnClass: 'btn-red',
                    action: function() {
                        ajaxDeleteCategory(id);
                    }
                },
                cancel: function() {
                    // $.alert('Canceled!');
                }
            }
        });
    });
});
