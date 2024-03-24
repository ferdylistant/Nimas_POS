$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
function jqueryValidation_(element, rules, messages = {}) {
    let _rules = rules === undefined ? {} : rules;
    return $(element).validate({
        errorElement: 'span',
        errorPlacement: function (error, element) {
            // console.log(element)
            let name = element.attr('name');
            name = name.replace('[]', '');
            $('#err_' + name).addClass('invalid-feedback').append(error)
        },
        highlight: function (element) {
            if ($(element).parent().hasClass('image-preview')) {
                $(element).parent().css('border-color', '#dc3545')
            } else {
                $(element).addClass('is-invalid').removeClass('is-valid');
            }

        },
        unhighlight: function (element) {
            if ($(element).parent().hasClass('image-preview')) {
                $(element).parent().css('border-color', '#ddd')
            } else {
                $(element).removeClass('is-invalid');
            }

        },
        rules: _rules,
        ignore: [],
        messages: messages
    })
}
function notifToast(stts, msg, reload = false) {
    if (stts == 'success') {
        iziToast.success({
            title: 'Okay!',
            icon: 'fas fa-check-circle',
            message: msg,
            position: 'topRight',
            timeout: 2000,
            overlayColor: 'rgba(0, 0, 0, 0.6)',
            transitionIn: 'flipInX',
            transitionOut: 'flipOutX',
            transitionInMobile: 'flipInX',
            transitionOutMobile: 'flipOutX',
            onClosing: function () {
                if (reload) {
                    location.reload();
                }
            }
        });
    } else if (stts == 'error') {
        iziToast.error({
            title: 'Oops!',
            icon: 'fas fa-times-circle',
            message: msg,
            position: 'topRight',
            timeout: 2000,
            overlayColor: 'rgba(0, 0, 0, 0.6)',
            transitionIn: 'flipInX',
            transitionOut: 'flipOutX',
            transitionInMobile: 'flipInX',
            transitionOutMobile: 'flipOutX',
        });
    } else if (stts == 'warning') {
        iziToast.warning({
            title: 'Warning!',
            icon: 'fas fa-exclamation-triangle',
            message: msg,
            position: 'topRight',
            timeout: 2000,
            overlayColor: 'rgba(0, 0, 0, 0.6)',
            transitionIn: 'flipInX',
            transitionOut: 'flipOutX',
            transitionInMobile: 'flipInX',
            transitionOutMobile: 'flipOutX',
        });
    }
}
$(window).on("load", function() {
    $('body').loadingModal({
        text: 'Loading',
        animation: 'wanderingCubes',

    });
    setTimeout(function() {
        $('body').loadingModal('hide');
    }, 1000);
    if (window.location.pathname == "/login") {
        function disableBack() {
            window.history.forward()
        }
        window.onload = disableBack();
        window.onpageshow = function(e) {
            if (e.persisted)
                disableBack();
        }
    }
});
$(document).ready(function() {
    $('.logout-confirm').click(function(e) {
        e.preventDefault();

        var url = window.location.origin + "/logout";
        $.confirm({
            theme: 'modern',
            icon: 'fa fa-question',
            title: 'Are you sure to sign out?',
            content: false,
            type: 'purple',
            columnClass: 'col-md-6 col-md-offset-3',
            animationBounce: 2.5,
            buttons: {
                confirm: {
                    text: 'Sure!',
                    btnClass: 'btn-purple',
                    action: function() {
                        window.location.href = url;
                    }
                },
                cancel: function() {
                    // $.alert('Canceled!');
                }
            }
        });

    })
});
