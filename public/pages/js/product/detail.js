$(document).ready(function() {
    function ajaxModalBarcode(el, id) {
        $.ajax({
            type: "GET",
            url: window.location.origin + "/products/detail/"+id,
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
    })
});
