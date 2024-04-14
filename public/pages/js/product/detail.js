$(document).ready(function () {
    function ajaxModalBarcode(el, id) {
        $.ajax({
            type: "GET",
            url: window.location.origin + "/products/detail/" + id,
            data: {
                req: "get-barcode",
            },
            success: function (data) {
                el.find('#titleModalBarcode').html(data.title);
                el.find('#mainContent').html(data.html);
            },
            error: function (data) {
                notifToast("error", data.responseJSON.message);
            }
        });
    }
    $('#mdBarcode').on({
        'show.bs.modal': function (e) {
            let id = window.location.pathname.split("/").pop();
            let el = $(this);
            $('#mainContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            ajaxModalBarcode(el, id);
        },
        'hidden.bs.modal': function () {
            $(this).find('#mainContent').html('');
            $(this).find('#titleModalBarcode').html('');
        }
    });
    async function ajaxCollapseTable(productId, supplierId, unitSatuan,indexCol) {
        await $.ajax({
            type: "GET",
            url: window.location.origin + "/products/detail/" + productId,
            data: {
                req: "get-supplier",
                supplierId: supplierId,
                unitSatuan: unitSatuan
            },
            success: function (data) {
                $('#contentTable'+indexCol).html(data);
            },
            error: function (data) {
                notifToast("error", data.responseJSON.message);
            }
        });
    }
    $('#tableSupplier').on({
        'show.bs.collapse': (e) => {
            var productId = e.target.dataset.productId;
            var supplierId = e.target.dataset.supplierId;
            var unitSatuan = e.target.dataset.unit;
            var indexCol = e.target.dataset.indexcol;
            $('#' + e.currentTarget.ownerDocument.activeElement.id).html('<i class="fas fa-minus-circle text-gradient text-primary"></i> ');
            $('#contentTable'+indexCol).html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            ajaxCollapseTable(productId, supplierId, unitSatuan,indexCol);

        },
        'hidden.bs.collapse': (e) => {
            $('#' + e.currentTarget.ownerDocument.activeElement.id).html('<i class="fas fa-plus-circle text-gradient text-dark"></i> ');
        }
    });
    // $('.btn-toggle-collapse').click(function (e) {
    //     // console.log(e.currentTarget.id);
    //     if ($('#' + e.currentTarget.id + ' i').hasClass('fas fa-plus-circle')) {
    //         $('#' + e.currentTarget.id).html('<i class="fas fa-minus-circle"></i> ');
    //     }
    //     else {
    //         $('#' + e.currentTarget.id).html('<i class="fas fa-plus-circle"></i> ');
    //     }
    // })
});
