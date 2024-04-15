$(document).ready(function () {
    let baseUrl = window.location.origin;
    let tbProduct = $('#tb_Product').DataTable({
        "responsive": true,
        "autoWidth": true,
        scrollX: true,
        scrollY: 300,
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
            { data: 'image', name: 'image', title: 'Gambar Produk', className: 'text-center text-secondary text-sm' },
            { data: 'product_code', name: 'product_code', title: 'Kode Produk', className: 'text-center text-secondary text-sm' },
            { data: 'product_name', name: 'product_name', title: 'Nama Produk', className: 'text-center text-secondary text-sm' },
            { data: 'category_name', name: 'category_name', title: 'Kategori Produk', className: 'text-center text-secondary text-sm' },
            { data: 'total_stock', name: 'total_stock', title: 'Stok', className: 'text-center text-secondary text-sm' },
            { data: 'unit_satuan', name: 'unit_satuan', title: 'Unit/Satuan', className: 'text-center text-secondary text-sm' },
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
    async function select2Category() {
        await $('.select-category').select2({
            placeholder: 'Choose category',
            allowClear: true,
            dropdownParent: $("#mdProduct"),
            width: 'resolve',
            ajax: {
                url: baseUrl + "/products/select2/category",
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
        }).on("change", function (e) {
            if (this.value) {
                $(this).valid();
            }
        });
    }
    async function select2Supplier() {
        await $('.select-supplier').each(function (e) {
            $(this).select2({
                placeholder: 'Choose supplier',
                allowClear: true,
                dropdownParent: $("#mdProduct"),
                ajax: {
                    url: baseUrl + "/products/select2/supplier",
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
                }
            }).on("change", function (e) {
                if (this.value) {
                    $(this).valid();
                }
            });
        });
    }
    function airDatepicker(el) {
        new AirDatepicker(el, {
            autoClose: true,
            maxDate: new Date(),
            locale: {
                days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                daysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                daysMin: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
                today: 'Hari ini',
                clear: 'Hapus',
                dateFormat: 'dd/MM/yyyy',
                timeFormat: 'hh:mm aa',
                firstDay: 1
            },
            container: "#mdProduct",
            position: 'top left',
            buttons: ['clear'],
            onSelect: function (selectedDates, dateStr, instance) {
                // console.log(selectedDates.formattedDate);
                $(el).val(selectedDates.formattedDate).valid();
            }

        });
    }
    function ajaxClickAddMore() {
        var max_fields = 20; //maximum input boxes allowed
        var wrapper = $('.input_fields_wrap'); //Fields wrapper
        var wrapperS = $('.input_fields_wrap_selling'); //Fields wrapper
        var sort = wrapper.find('[data-sort]').length;
        var sortSell = wrapper.find('[data-sortselling]').length;
        var x = sort; //initlal text box count
        var y = sortSell; //initlal text box count
        $('.btnAddSupplier').click(function (e) {
            // console.log(e);
            //on add input button click
            e.preventDefault();
            if (x < max_fields) {
                //max input box allowed
                x++; //text box increment
                $(wrapper).append(
                    `<div class="row field-more"><div class="form-group col-md-3">
                <select name="supplier_id[]" id="supplierFieldMore` + x + `" class="form-control form-control-sm select-supplier" required>
                    <option label="Choose One"></option>
                </select>
            </div>
            <div class="form-group col-md-3">
            <input type="number" name="product_quantity[]" id="product_amountField` + x + `" min="1" class="form-control form-control-sm" placeholder="Enter Amount" required>
                </div>
            <div class="form-group col-md-3">
                <input type="number" name="buying_price[]" id="buying_priceField` + x + `" min="1" class="form-control form-control-sm" placeholder="Enter Buying Price" required>
            </div>
            <div class="form-group col-md-3">
                <div class="input-group input-group-sm">
                <input type="text" name="buying_date[]" id="buying_dateField`+x+`" class="form-control form-control-sm buying_date_cls" placeholder="Pick buying date" readonly required>
                <div class="input-group-append">
                        <span class="input-group-text"><a href="javascript:void(0)" class="remove_field_supplier text-danger" title="Delete Field"><i class="fas fa-times"></i></a></span>
                    </div>
                </div>
            </div>
            </div>`
                );
                select2Supplier();
                airDatepicker("#buying_dateField"+x);
                // airDatepicker(".buying_date_cls");
            }
        });
        $(wrapper).on("click", ".remove_field_supplier", function (e) {
            //user click on remove text
            e.preventDefault();
            $(this).closest(".field-more").remove();
            x--;
        });
        $('.btnAddSellingPrice').click(function (e) {
            //on add input button click
            e.preventDefault();
            if (y < max_fields) {
                //max input box allowed
                y++; //text box increment
                $(wrapperS).append(
                    `<div class="row field-more-selling"><div class="form-group col-md-6">
                    <input type="text" name="selling_price_type[]" id="selling_price_typeField` + y + `" class="form-control form-control-sm" placeholder="Enter Selling Price Type" required>
                </div>
                <div class="form-group col-md-6">
                    <div class="input-group input-group-sm">
                    <input type="number" name="selling_price[]" id="selling_priceField` + y + `" min="1" class="form-control form-control-sm" placeholder="Enter Selling Price" required>
                    <div class="input-group-append">
                        <span class="input-group-text"><a href="javascript:void(0)" class="remove_field_selling_price text-danger" title="Delete Field"><i class="fas fa-times"></i></a></span>
                    </div>
                    </div>
                </div></div>`
                );
            }
        });
        $(wrapperS).on("click", ".remove_field_selling_price", function (e) {
            //user click on remove text
            e.preventDefault();
            $(this).closest(".field-more-selling").remove();
            y--;
        });

    }
    function selectedOptionEditProduct(result) {
        $("#categoryField").select2("trigger", "select", {
            data: {
                id: result.category.id,
                text: result.category.category_name
            }
        });
        Object.entries(result.supplier).forEach((entry) => {
            let [key, value] = entry;
            if (key != 0) {
                var i = parseInt(key) + 1;
            }
            let el = key == 0 ? "#supplierField" : "#supplierFieldMore" + i;
            $(el).select2("trigger", "select", {
                data: {
                    id: value.supplier_id,
                    text: value.supplier_name
                }
            })
        });
    }
    function collapseChangeImage(data) {
        $('#imgEdit').on('click','.btnChangeImg',function (e) {
            e.preventDefault();
            $('#imgEdit').html(`<div class="form-group col-md-12">
            <label for="imageField" class="col-form-label mb-2">Image: <span class="text-danger">*</span></label>
            <input type="file" name="image" id="imageField" onchange="onFileSelected(event)" class="form-control form-control-sm" required>
            <span id="err_image"></span>
        </div>
        <div id="image_preview">
        <img src="`+window.location.origin +'/storage/product/img/'+data.image+`" width="200" class="img-thumbnail rounded">
        <br>
        <a href="javascript:void(0)" class="text-gradient text-primary btnCancelChangeImg" title="Cancel Change"><i class="fas fa-times"></i> Cancel Change</a>
        </div>`).change();
        });
        $('#imgEdit').on('click','.btnCancelChangeImg',function (e) {
            e.preventDefault();
            $('#imgEdit').html(`<div class="form-group col-md-12">
            <label for="imageField" class="col-form-label mb-2">Image: <span class="text-danger">*</span></label>
            <br>
            <img src="`+window.location.origin + '/storage/product/img/' + data.image+`" width="200" class="img-thumbnail rounded">
        </div>
        <a href="javascript:void(0)" class="text-gradient text-primary btnChangeImg" title="Change Image"><i class="fas fa-pen"></i> Change Image</a>`).change();
        });
    }
    async function ajaxModalProduct(el, type, id, name) {
        await $.ajax({
            type: "GET",
            url: baseUrl + "/products/" + type + "/ajax-modal",
            data: {
                id: id,
                name: name
            },
            success: function (result) {
                el.find('#titleModalProduct').html(result.title);
                if (type != 'history') {
                    el.find(':submit').data('el', '#' + result.idForm);
                    el.find(':submit').attr('form', result.idForm);
                }
                el.find('#mainContent').html(result.html);
                let valid = jqueryValidation_("#fm_" + type + "Product", {
                    category_name: {
                        required: true,
                    },
                    'supplier_id[]': {
                        required: true,
                    },
                    'product_quantity[]': {
                        required: true,
                        number: true
                    },
                    'buying_price[]': {
                        required: true,
                        number: true
                    },
                    "buying_date[]": {
                        required: true,
                    },
                    product_name: {
                        required: true,
                    },
                    unit_satuan: {
                        required: true,
                    },
                    product_code: {
                        required: true,
                    },
                    'selling_price_type[]': {
                        required: true,
                    },
                    'selling_price[]': {
                        required: true,
                        number: true
                    },
                    image: {
                        required: true,
                        extension: "jpg|png|jpeg|webp"
                    }
                });
                select2Category();
                select2Supplier();
                if (type == 'edit') {
                    selectedOptionEditProduct(result);
                    Object.entries(result.dataSup).forEach(entry => {
                        'use strict'
                        let [key, value] = entry;
                        if (key != 0) {
                            var i = parseInt(key) + 1;
                        }
                        new AirDatepicker(key == 0 ? "#buying_dateField1" : '#buying_dateField'+i, {
                            autoClose: true,
                            maxDate: new Date(),
                            locale: {
                                days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                                daysShort: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                                daysMin: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                                months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                                monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
                                today: 'Hari ini',
                                clear: 'Hapus',
                                dateFormat: 'dd/MM/yyyy',
                                timeFormat: 'hh:mm aa',
                                firstDay: 1
                            },
                            container: "#mdProduct",
                            position: 'top left',
                            buttons: ['clear'],
                            selectedDates: value.buying_date,
                            onSelect: function (selectedDates, dateStr, instance) {
                                // console.log(selectedDates.formattedDate);
                                $(key == 0 ? "#buying_dateField1" : '#buying_dateField'+i).val(selectedDates.formattedDate).valid();
                            }

                        });
                    });
                    collapseChangeImage(result.product);
                } else if (type == 'add') {
                    airDatepicker('#buying_dateField');
                }
            },
            error: function (err) {
                // console.log(err);
                notifToast("error", err.statusText);
            },
            complete: function () {
                ajaxClickAddMore();
            }
        });
    }
    async function ajaxAddProduct(el) {
        await $.ajax({
            type: "POST",
            url: baseUrl + "/products/store",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                // console.log(result);
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    $("#fm_addProduct").trigger("reset");
                    $('#mdProduct').modal('hide');
                    tbProduct.ajax.reload();
                }
            },
            error: function (err) {
                notifToast("error", err.statusText);
            }
        });
    }
    async function ajaxAddStockProduct(el){
        await $.ajax({
            type: "POST",
            url: baseUrl + "/products/add-stock-product-action/ajax-modal",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                // console.log(result);
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    $("#fm_addStockProduct").trigger("reset");
                    $('#mdProduct').modal('hide');
                    tbProduct.ajax.reload();
                }
            },
            error: function (err) {
                notifToast("error", err.statusText);
            }
        });
    }
    async function ajaxEditProduct(el) {
        await $.ajax({
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
                notifToast("error", err.statusText);
            }
        });
    }
    $('#mdProduct').on({
        'show.bs.modal': function (e) {
            var type = $(e.relatedTarget).data('type');
            var id = $(e.relatedTarget).data('id');
            var name = $(e.relatedTarget).data('name');
            var el = $(this);
            $('#mainContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
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
            var val = $(this).find('[name="product_name"]').val();
            var ele = $(this).find(':submit').data('el');
            var el = $(ele);
            if (el.valid()) {
                switch (ele) {
                    case '#fm_addProduct':
                        var title = 'Add Product';
                        var text = 'add product (' + val + ')?';
                        break;
                    case '#fm_addStockProduct':
                        var title = "Add Stock Product";
                        var text = 'add stock product (' + val + ')?';
                        break;
                    default:
                        var title = 'Edit Product';
                        var text = 'edit product (' + val + ')?';
                        break;
                }
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
                                switch (title) {
                                    case 'Add Product':
                                        ajaxAddProduct(el)
                                        break;
                                    case 'Add Stock Product':
                                        ajaxAddStockProduct(el)
                                        break;
                                    default:
                                        ajaxEditProduct(el)
                                        break;
                                }
                            }
                        },
                        cancel: function () {
                            // $.alert('Canceled!');
                        }
                    }
                });
            }
        }
    });
});
const uploadManager = new Bytescale.UploadManager({
    apiKey: "free" // e.g. "public_xxxxx"
  });
async function onFileSelected(event) {
    try {
      // 1) Hide upload button when upload starts.
    //   uploadButton.remove()

      // 2) Upload file & show progress.
      const [ file ]    = event.target.files
      const { fileUrl } = await uploadManager.upload({
        data: file,
        onProgress: ({ progress }) =>

          document.querySelector('#image_preview').innerHTML = `<div class="progress-wrapper">
          <div class="progress-info">
            <span class="text-sm font-weight-bold">Uploading...</span>
            <div class="progress-percentage">
              <span class="text-sm font-weight-bold">${progress.toFixed(2)}%</span>
            </div>
          </div>
          <div class="progress">
            <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100" style="width: ${progress}%;"></div>
          </div>
        </div>`
      })

      // 3) Display uploaded file URL.
      document.querySelector('#image_preview').innerHTML = `
        <img src="${fileUrl}" width="200" class="img-thumbnail rounded">`
    //   document.querySelector('.modal-body').scrollTo = (0, document.querySelector('.modal-body').scrollHeight)
    //   setTimeout(() => {
    //       document.querySelector('.modal-body').scrollTo = (0, document.querySelector('.modal-body').scrollHeight)
    //   }, 2000)

    } catch(e) {
      // 4) Display errors.
    //   console.log(`Error: ${e.message}`)
      document.querySelector('#image_preview').innerHTML = ``
    //   document.querySelector('#image_preview').innerHTML = `Please try another file:<br/><br/>${e.message}`
    }
  }
