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
        'shown.bs.modal': function (e) {
            let id = window.location.pathname.split("/").pop();
            let el = $(this);
            ajaxModalBarcode(el, id);
        },
        'hidden.bs.modal': function () {
            $(this).find('#mainContent').html('');
            $(this).find('#titleModalBarcode').html('');
        }
    });
    $('#tableSupplier').on({
        'show.bs.collapse': (e) => {

            $('#' + e.currentTarget.ownerDocument.activeElement.id).html('<i class="fas fa-minus-circle"></i> ');

        },
        'hidden.bs.collapse': (e) => {
            $('#' + e.currentTarget.ownerDocument.activeElement.id).html('<i class="fas fa-plus-circle"></i> ');
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
