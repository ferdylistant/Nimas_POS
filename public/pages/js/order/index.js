
$(document).ready(function () {
    let baseUrl = window.location.origin;
    let tbOrder = $('#tb_Order').DataTable({
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
        ajax: baseUrl + "/transaction/orders",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', searchable: false, className: 'text-center text-secondary text-sm' },
            { data: 'order_code', name: 'order_code', title: 'Order Code', className: 'text-center text-secondary text-sm' },
            { data: 'status_payment', name: 'status_payment', title: 'Status', className: 'text-center text-secondary text-sm' },
            { data: 'name', name: 'name', title: 'Customer', className: 'text-center text-secondary text-sm' },
            { data: 'qty', name: 'qty', title: 'Quantity', className: 'text-center text-secondary text-sm' },
            { data: 'total', name: 'total', title: 'Total', className: 'text-center text-secondary text-sm' },
            { data: 'pay', name: 'pay', title: 'Pay', className: 'text-center text-secondary text-sm' },
            { data: 'due', name: 'due', title: 'Due', className: 'text-center text-secondary text-sm' },
            { data: 'order_date', name: 'order_date', title: 'Order Date', className: 'text-center text-secondary text-sm' },
            { data: 'created_at', name: 'created_at', title: 'Created On', className: 'text-center text-secondary text-sm' },
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
    var alreadyAdded = [];
    var triggerData = [];
    const rupiah = (number) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR"
        }).format(number);
    }
    const destroySelect2 = () => {
        $('[name="product_id"]').val('').change();
        $('[name="selling_price_id"]').val('').change();
        $('.select-price').select2('destroy');
        $('#sellingPriceField').select2({
            placeholder: 'Choose price',
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
            container: "#mdOrder",
            position: 'bottom left',
            buttons: ['clear'],
            selectedDates: new Date(),
            onSelect: function (selectedDates, dateStr, instance) {
                // console.log(selectedDates.formattedDate);
                $(el).val(selectedDates.formattedDate).valid();
            }

        });
    }
    async function select2Customer() {
        await $('.select-customer').select2({
            placeholder: 'Choose customer',
            allowClear: true,
            dropdownParent: $("#mdOrder"),
            width: 'resolve',

            ajax: {
                url: baseUrl + "/transaction/orders/select2/customer",
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
        }).on("change", function (e) {
            if (this.value) {
                $(this).valid();
            }
        });
    }
    function sumSubTotal() {
        var sum = 0;
        $(".sub_total").each(function () {
            var str = this.value.trim();  // .trim() may need a shim
            if (str) {   // don't send blank values to `parseInt`
                sum += parseInt(str, 10);
            }
        });
        return sum;
    }
    function calc(id, price) {
        if ($('#quantity_' + id).val() == '') {
            $('#quantity_' + id).val(1).change();
        }
        let count = parseInt(price, 10) * parseInt($('#quantity_' + id).val(), 10)
        $('#price_' + id).text(rupiah(count) == 'RpNaN' ? rupiah(0) : rupiah(count)).change();
        $('#sub_total' + id).val(count).change();
        $('#totalPrice').text(rupiah(sumSubTotal())).change();
        $('#total_price').val(sumSubTotal()).change();
        $('#dueField').val(sumSubTotal()).change();
        $('#parameters').val(sumSubTotal()).change();
        if ($('[name="discount"]').val() != '') {
            let hitung = parseInt($('#parameters').val(), 10) - parseInt($('[name="discount"]').val(), 10);
            $('#totalPrice').text(rupiah(hitung)).change();
            $('#total_price').val(hitung).change();
            $('#dueField').val(hitung).change();
        }
    }
    function triggerElement(triggerData) {
        $('.btnNumber').each(function () {
            $(this).click(function (e) {
                triggerData.forEach(entry => {
                    calc(entry.id, entry.price);
                });
            })
        });
        $('input[type="number"]').each(function () {
            $(this).keyup(function (e) {
                triggerData.forEach(entry => {
                    if (this.value > entry.stock) {
                        $(this).val(entry.stock).change();
                        calc(entry.id, entry.price);
                    } else {
                        calc(entry.id, entry.price);
                    }
                });
            })
        });
        $('input[name="pay"]').keyup(function (e) {
            if (parseInt(this.value, 10) > parseInt($('#dueField').val(), 10)) {
                var percent = parseInt($('#dueField').val(), 10) - (parseInt($('#dueField').val(), 10) * 5 / 100);
                $(this).val(percent).change();
            } else if (this.value == 0) {
                $(this).val('').change();
            }
        });
    }
    function getSelectedProduct(id) {
        $.ajax({
            url: baseUrl + `/transaction/orders/select2/get-product-to-table`,
            data: {
                id: id
            },
            success: function (data) {

                triggerData.push({
                    id: data.id,
                    price: data.selling_price,
                    stock: data.total_stock
                });
                html_ = `<tr id="prow_${data.id}">
                        <td><div class="d-flex px-2 py-1">
                        <div>
                        <img src="${baseUrl}/storage/product/img/${data.image}" class="avatar avatar-sm shadow me-3" alt="product image">
                        <input type="hidden" name="product_id[]" value="${data.id}">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                          <h6 class="mb-0 text-sm">${data.product_name}</h6>
                          <p class="text-xs text-secondary mb-0">${data.category_name}</p>
                        </div>
                        </div>
                        </td>
                        <td><p class="text-xs font-weight-bold mb-0">${rupiah(data.selling_price)}</p>
                        <p class="text-xs text-secondary mb-0">${data.type}</p>
                        <input type="hidden" name="selling_price_id[]" value="${data.selling_price_id}">
                        </td>
                        <td><p class="text-xs font-weight-bold mb-0">${data.total_stock}</p>
                        <p class="text-xs text-secondary mb-0">${data.unit_satuan}</p></td>
                        <td><div class="number-input">
                        <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()" class="btnNumber"></button>
                        <input class="quantity_${data.id}" min="1" max="${data.total_stock}" name="quantity[]" value="1" type="number" id="quantity_${data.id}">
                        <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()" class="plus btnNumber"></button>
                      </div></td>
                        <td><h6 class="mb-0 text-sm" id="price_${data.id}">${rupiah(data.selling_price)}</h6>
                        <input type="hidden" class="sub_total" id="sub_total${data.id}" name="sub_total[]" value="${data.selling_price}">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="btn btn-sm btn-primary pd-3" target="_blank">Lihat</a>
                        </td>
                    </tr>`
                $('#tb_selectedProductOrder').find('tbody').append(html_);
                destroySelect2();
                triggerElement(triggerData);
                $('#totalPrice').text(rupiah(sumSubTotal())).change();
                $('#total_price').val(sumSubTotal()).change();
                $('#dueField').val(sumSubTotal()).change();
                $('#parameters').val(sumSubTotal()).change();
                if ($('[name="discount"]').val() != '') {
                    let count = parseInt($('#parameters').val(), 10) - parseInt($('[name="discount"]').val(), 10);
                    $('#totalPrice').text(rupiah(count)).change();
                    $('#total_price').val(count).change();
                    $('#dueField').val(count).change();
                }
            },
            error: function (err) {

            }
        });
    }
    async function select2Price(id = '') {
        await $('.select-price').select2({
            placeholder: 'Choose price',
            allowClear: true,
            dropdownParent: $("#mdOrder"),
            width: 'resolve',
            ajax: {
                url: baseUrl + "/transaction/orders/select2/price",
                data: function (params) {
                    return {
                        q: params.term,
                        id: id
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.type + ' (' + rupiah(item.selling_price) + ')',
                                id: item.id,
                                product_id: item.product_id,
                            };
                        }),
                    };
                }
            }
        }).on('select2:select', function (e) {
            if ($.inArray(e.params.data.product_id, alreadyAdded) !== -1) {
                destroySelect2();
                return;
            } else {
                $('#notAvail').empty();
                getSelectedProduct(e.params.data.id);
                alreadyAdded.push(e.params.data.product_id);
            }

        });
    }
    function formatOption(option) {
        if (!option.id) {
            return option.text;
        }
        var imageUrl = baseUrl + '/storage/product/img/' + option.image;
        var optionWithImage = $(
            '<span><img src="' + imageUrl + '" class="avatar avatar-sm me-3" alt="product image">' + option.text + ' (' + option.category_name + ')</span>'
        );
        return optionWithImage;
    }

    function formatSelection(option) {
        if (!option.id) {
            return option.text;
        }
        return $('<span>' + option.text + ' (' + option.category_name + ')</span>');
    }
    async function select2Product() {
        await $('.select-product').select2({
            placeholder: 'Choose product',
            allowClear: true,
            dropdownParent: $("#mdOrder"),
            width: 'resolve',
            minimumInputLength: 1,
            minimumResultsForSearch: 10,
            templateResult: formatOption,
            templateSelection: formatSelection,
            ajax: {
                url: baseUrl + "/transaction/orders/select2/product",
                data: function (params) {
                    return {
                        q: params.term
                    };

                },
                delay: 650,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.product_name,
                                id: item.id,
                                category_name: item.category_name,
                                image: item.image
                            };
                        }),
                    };
                },
            }
        }).on('select2:select', function (e) {
            select2Price(e.params.data.id);
        }).on("select2:unselect", function (e) {
            destroySelect2();
        });
    }
    function formDivHidden(data) {
        if (data == 'Down Payment' || data == 'Paid') {
            select2PaymentType();
            $('#paymentFieldDiv').show('slow');
            $('#paymentTypeField').attr('required', true).change();
            $('#payField').attr('required', true).change();
            data == 'Paid' ? $('#payField').val($('#dueField').val()).attr("readonly", true).css("cursor", "not-allowed").change() : $('#payField').val('').attr("readonly", false).css("cursor", "pointer").change();
            //scroll down
            $('#scrollableModal').animate({ scrollTop: $('.modal-body').prop("scrollHeight") }, 800);
        } else {
            $('#paymentFieldDiv').hide('slow');
            $('#paymentTypeField').attr('required', false).change();
            $('#payField').attr('required', false).change();
        }
    }
    function unselectPaymentStatus() {
        $('#paymentFieldDiv').hide('slow');
        $('#payField').attr('required', false);
        $('#dueFieldDiv').hide('slow');
    }
    function select2PaymentType() {
        $('.select-payment-type').select2({
            placeholder: 'Choose payment type',
            allowClear: true,
            dropdownParent: $("#mdOrder"),
            width: 'resolve',
            ajax: {
                url: baseUrl + "/transaction/orders/select2/payment-type",
                data: function (params) {
                    return {
                        q: params.term
                    };

                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item,
                                id: item,
                            };
                        }),
                    };
                },
            }
        }).on('change', function (e) {
            if (this.value) {
                $(this).valid();
            }
        });
    }
    async function select2PaymentStatus() {
        await $('.select-payment-status').select2({
            placeholder: 'Choose payment status',
            allowClear: true,
            dropdownParent: $("#mdOrder"),
            ajax: {
                url: baseUrl + "/transaction/orders/select2/payment-status",
                data: function (params) {
                    return {
                        q: params.term
                    };

                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item,
                                id: item,
                            };
                        }),
                    };
                },
            }
        }).on('change', function (e) {
            if (this.value) {
                $(this).valid();
            }
        }).on('select2:select', function (e) {
            formDivHidden(e.params.data.id);
        }).on("select2:unselect", function (e) {
            unselectPaymentStatus();
        });
    }
    async function ajaxClickLoadMoreHistory(id) {
        await $('#loadMore').click(function (e) {
            e.preventDefault();
            var page = $(this).data("page");
            $(this).data("page", page + 1);
            $.ajax({
                type: "GET",
                url: baseUrl + "/transaction/orders/history/ajax-modal",
                data: {
                    id: id,
                    page: page
                },
                success: function (result) {
                    if (result.htmlSub.length == 0) {
                        $("#loadMore").attr("disabled", true).css("cursor", "not-allowed").change();
                        notifToast("error", "Tidak ada data lagi");
                    } else {
                        $(".timeline").append(result.htmlSub);
                        $('.timeline').animate({ scrollTop: $('.timeline').prop("scrollHeight") }, 800);
                    }
                },
                error: function (err) {
                    notifToast("error", err.responseJSON.message);
                }
            })
        })
    }
    async function ajaxModalOrder(el, type, id = '', name = '') {
        await $.ajax({
            type: "GET",
            url: baseUrl + "/transaction/orders/" + type + "/ajax-modal",
            data: {
                id: id,
                name: name
            },
            success: function (result) {
                el.find('#titleModalOrder').html(result.title);
                if (type != 'history') {
                    el.find(':submit').data('el', '#' + result.idForm);
                    el.find(':submit').attr('form', result.idForm);
                }
                el.find('#mainContent').html(result.html);
                $('[data-bs-toggle="tooltip"]').tooltip({
                    boundary: 'window',
                    template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                    html: true,
                    trigger: 'hover',
                    placement: 'top',
                    container: 'body',
                    customClass: 'tooltip-custom',
                    delay: { "show": 100, "hide": 100 },
                    offset: '10px',
                    sanitize: false,

                });
                $('.imask').each(function () {
                    IMask(
                        this,
                        {
                            mask: Number,
                            min: 1,
                        }
                    );
                })
                let valid = jqueryValidation_("#fm_" + type + "Order", {
                    date: {
                        required: true,
                    },
                    customer_id: {
                        required: true,
                    },
                    discount: {
                        number: true
                    },
                    payment_status: {
                        required: true,
                    },
                    pay: {
                        number: true,
                    },
                });
                select2Customer();
                select2Product();
                select2PaymentStatus();
                if (type == 'edit') {
                    $('#sellingPriceField').select2({
                        placeholder: 'Choose price',
                    });
                } else if (type == 'add') {
                    airDatepicker('#dateField');
                    $('[name="order_code"').val(result.code_order);
                    $('#discountField').keyup(function (e) {
                        var parameters = $('#parameters').val();
                        let count = parseInt(parameters, 10) - parseInt($(this).val(), 10);
                        if ($(this).val() == '') {
                            $('#totalPrice').text(rupiah(sumSubTotal())).change();
                            $('#totalDiscount').text(rupiah(0)).change();
                            $('#total_price').val(sumSubTotal()).change();
                            $('#dueField').val(sumSubTotal()).change();
                        } else {
                            $('#totalPrice').text(rupiah(count)).change();
                            $('#totalDiscount').text(rupiah(this.value)).change();
                            $('#total_price').val(count).change();
                            $('#dueField').val(count).change();
                        }
                    })
                } else {
                    $('[data-bs-toggle="tooltip"]').tooltip({
                        trigger: 'hover'
                    });
                    ajaxClickLoadMoreHistory(id);
                }
            },
            error: function (err) {
                // console.log(err);
                notifToast("error", err.statusText);
            },
            complete: function () {
            }
        });
    }
    async function ajaxAddOrder(el) {
        await $.ajax({
            type: "POST",
            url: baseUrl + "/transaction/orders/store",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                // console.log(result);
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    triggerData.length = 0;
                    $("#fm_addOrder").trigger("reset");
                    $('#mdOrder').modal('hide');
                    tbOrder.ajax.reload();
                }
            },
            error: function (err) {
                console.log(err);
                notifToast("error", err.statusText);
            }
        });
    }
    async function ajaxEditOrder(el) {
        await $.ajax({
            type: "POST",
            url: baseUrl + "/transaction/orders/update",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    $('#mdOrder').modal('hide');
                    tbOrder.ajax.reload();
                }
            },
            error: function (err) {
                notifToast("error", err.statusText);
            }
        });
    }
    async function ajaxDeleteOrder(id) {
        await $.ajax({
            type: "DELETE",
            url: baseUrl + "/transaction/orders/delete/" + id,
            success: function (result) {
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    tbOrder.ajax.reload();
                }
            },
            error: function (err) {
                notifToast("error", err.responseJSON.message);
            }
        })
    }

    $('#tb_Order').on('click', '.btnDeleteOrder', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        $.confirm({
            theme: 'modern',
            icon: 'fa fa-question',
            title: 'Delete Supplier',
            content: 'Are you sure you want to delete (' + name + ')?',
            type: 'red',
            columnClass: 'col-md-6 col-md-offset-3',
            animationBounce: 2.5,
            buttons: {
                confirm: {
                    text: 'Sure!',
                    btnClass: 'btn-red',
                    action: function () {
                        ajaxDeleteOrder(id);
                    }
                },
                cancel: function () {
                    // $.alert('Canceled!');
                }
            }
        });
    });
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    if (urlParams.has('modal')) {
        $('#mdOrder').modal('show');
        ajaxModalOrder($('#mdOrder'), urlParams.get('modal'), urlParams.get('id'), urlParams.get('name'));
    }
    $('#mdOrder').on({
        'show.bs.modal': function (e) {
            window.history.pushState({}, '', $(e.relatedTarget).attr('href'));
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            var type = urlParams.get('modal');
            var id = urlParams.get('id');
            var name = urlParams.get('name');
            var el = $(this);
            $('#mainContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            ajaxModalOrder(el, type, id, name);
        },
        'hidden.bs.modal': function () {
            $(this).find('#mainContent').html('');
            $(this).find('#titleModalOrder').html('');
            $(this).find(':submit').data('el', '');
            $(this).find(':submit').attr('form', '');
        },
        'hide.bs.modal': function () {
            window.history.pushState({}, '', '/transaction/orders');
        },
        'submit': function (e) {
            e.preventDefault();
            var val = $(this).find('[name="order_code"]').val();
            var ele = $(this).find(':submit').data('el');
            var el = $(ele);
            var checktable = $('#total_price').val();
            if (checktable == 0 || checktable == null || checktable == '' || checktable == undefined) {
                notifToast('error', 'Please add product in order');
                return;
            }

            if (el.valid()) {
                switch (ele) {
                    case '#fm_addOrder':
                        var title = 'Add Order';
                        var text = 'add order (' + val + ')?';
                        break;
                    default:
                        var title = 'Edit Order';
                        var text = 'edit order (' + val + ')?';
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
                                    case 'Add Order':
                                        ajaxAddOrder(el)
                                        break;
                                    default:
                                        ajaxEditOrder(el)
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
