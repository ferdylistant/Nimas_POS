$(document).ready(function () {
    let baseUrl = window.location.origin;
    let tbCustomer = $('#tb_Customer').DataTable({
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
        ajax: baseUrl + "/people/customer",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', searchable: false, className: 'text-center text-secondary text-sm' },
            { data: 'photo', name: 'photo', title: 'Foto', className: 'text-center text-secondary text-sm' },
            { data: 'name', name: 'name', title: 'Nama', className: 'text-center text-secondary text-sm' },
            { data: 'email', name: 'email', title: 'Email', className: 'text-center text-secondary text-sm' },
            { data: 'phone', name: 'phone', title: 'No Telp', className: 'text-center text-secondary text-sm' },
            { data: 'address', name: 'address', title: 'Alamat', className: 'text-center text-secondary text-sm' },
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
    function collapseChangeImage(data) {
        $('#imgEdit').on('click','.btnChangeImg',function (e) {
            e.preventDefault();
            $('#imgEdit').html(`<div class="form-group col-md-12">
            <label for="photoField" class="col-form-label mb-2">Photo: <small class="text-secondary">(Opsional)</small></label>
            <input type="file" name="photo" id="photoField" onchange="onFileSelected(event)" class="form-control form-control-sm" required>
            <span id="err_image"></span>
        </div>
        <div id="image_preview">
        <img src="`+window.location.origin +'/storage/supplier/img/'+data.photo+`" width="200" class="img-thumbnail rounded">
        <br>
        <a href="javascript:void(0)" class="text-gradient text-primary btnCancelChangeImg" title="Cancel Change"><i class="fas fa-times"></i> Cancel Change</a>
        </div>`).change();
        });
        $('#imgEdit').on('click','.btnCancelChangeImg',function (e) {
            e.preventDefault();
            $('#imgEdit').html(`<div class="form-group col-md-12">
            <label for="photoField" class="col-form-label mb-2">Photo: <small class="text-secondary">(Opsional)</small></label>
            <br>
            <img src="`+window.location.origin + '/storage/supplier/img/' + data.photo+`" width="200" class="img-thumbnail rounded">
        </div>
        <a href="javascript:void(0)" class="text-gradient text-primary btnChangeImg" title="Change Image"><i class="fas fa-pen"></i> Change Image</a>`).change();
        });
    }
    async function ajaxClickLoadMoreHistory(id) {
        await $('#loadMore').click(function (e) {
            e.preventDefault();
            var page = $(this).data("page");
            $(this).data("page", page + 1);
            $.ajax({
                type: "GET",
                url: baseUrl + "/people/customer/history/ajax-modal",
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
                        $('.timeline').animate({scrollTop: $('.timeline').prop("scrollHeight")}, 800);
                    }
                },
                error: function (err) {
                    notifToast("error", err.responseJSON.message);
                }
            })
        })
    }
    async function ajaxModalSupplier(el, type, id='', name='') {
        await $.ajax({
            type: "GET",
            url: baseUrl + "/people/customer/" + type + "/ajax-modal",
            data: {
                id: id,
                name: name
            },
            success: function (result) {
                el.find('#titleModalCustomer').html(result.title);
                if (type != 'history') {
                    el.find(':submit').data('el', '#' + result.idForm);
                    el.find(':submit').attr('form', result.idForm);
                }
                el.find('#mainContent').html(result.html);
                let valid = jqueryValidation_("#fm_" + type + "Customer", {
                    name: {
                        required: true,
                    },
                    email: {
                        email: true,
                        required: true,
                    },
                    phone: {
                        required: true,
                        number: true
                    },
                    address: {
                        required: true,
                    },
                    photo: {
                        extension: "jpg|png|jpeg|webp"
                    }
                });
                if (type == 'edit') {
                    collapseChangeImage(result.data);
                } else if (type == 'history') {
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
    async function ajaxAddCustomer(el) {
        await $.ajax({
            type: "POST",
            url: baseUrl + "/people/customer/store",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                console.log(result);
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    $("#fm_addCustomer").trigger("reset");
                    $('#mdCustomer').modal('hide');
                    tbCustomer.ajax.reload();
                }
            },
            error: function (err) {
                console.log(err);
                notifToast("error", err.statusText);
            }
        });
    }
    async function ajaxEditCustomer(el) {
        await $.ajax({
            type: "POST",
            url: baseUrl + "/people/customer/update",
            data: new FormData($(el).get(0)),
            processData: false,
            contentType: false,
            cache: false,
            success: function (result) {
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    $('#mdCustomer').modal('hide');
                    tbCustomer.ajax.reload();
                }
            },
            error: function (err) {
                notifToast("error", err.statusText);
            }
        });
    }
    async function ajaxDeleteCustomer(id) {
        await $.ajax({
            type: "DELETE",
            url: baseUrl + "/people/customer/delete/"+id,
            success: function (result) {
                notifToast(result.status, result.message);
                if (result.status == "success") {
                    tbCustomer.ajax.reload();
                }
            },
            error: function (err) {
                notifToast("error", err.responseJSON.message);
            }
        })
    }
    $('#tb_Customer').on('click', '.btnDeleteCustomer', function (e) {
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
                        ajaxDeleteCustomer(id);
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
        $('#mdCustomer').modal('show');
        ajaxModalSupplier($('#mdCustomer'), urlParams.get('modal'), urlParams.get('id'), urlParams.get('name'));
    }
    $('#mdCustomer').on({
        'show.bs.modal': function (e) {
            window.history.pushState({}, '', $(e.relatedTarget).attr('href'));
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            var type = urlParams.get('modal');
            var id = urlParams.get('id');
            var name = urlParams.get('name');
            var el = $(this);
            $('#mainContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            ajaxModalSupplier(el, type, id, name);
        },
        'hidden.bs.modal': function () {
            $(this).find('#mainContent').html('');
            $(this).find('#titleModalSupplier').html('');
            $(this).find(':submit').data('el', '');
            $(this).find(':submit').attr('form', '');
        },
        'hide.bs.modal': function () {
            window.history.pushState({}, '', '/people/customer');
        },
        'submit': function (e) {
            e.preventDefault();
            var val = $(this).find('[name="name"]').val();
            var ele = $(this).find(':submit').data('el');
            var el = $(ele);
            if (el.valid()) {
                switch (ele) {
                    case '#fm_addCustomer':
                        var title = 'Add Customer';
                        var text = 'add customer (' + val + ')?';
                        break;
                    default:
                        var title = 'Edit Customer';
                        var text = 'edit customer (' + val + ')?';
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
                                    case 'Add Customer':
                                        ajaxAddCustomer(el)
                                        break;
                                    default:
                                        ajaxEditCustomer(el)
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
