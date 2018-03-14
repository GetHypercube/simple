
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.moment = require('moment');

import draggable from "jquery-ui";
import jsPlumb from "jsplumb";

import datetimepicker from "bootstrap4-datetimepicker";
require("bootstrap4-datetimepicker/src/sass/bootstrap-datetimepicker-build.scss");

//Sends forms
$(".ajaxForm :submit").attr("disabled", false);
$(document).on("submit", ".ajaxForm", function () {

    var form = this;
    if (!form.submitting) {
        form.submitting = true;
        $(form).find(":submit").attr("disabled", true);
        //$(form).append("<div class='ajaxLoader'>Cargando</div>");
        var ajaxLoader = $(form).find(".ajaxLoader");
        $(ajaxLoader).css({
            left: ($(form).width() / 2 - $(ajaxLoader).width() / 2) + "px",
            top: ($(form).height() / 2 - $(ajaxLoader).height() / 2) + "px"
        });
        $.ajax({
            url: form.action,
            data: $(form).serialize(),
            type: form.method,
            dataType: "json",
            success: function (response) {
                if (response.validacion) {
                    if (response.redirect) {
                        window.location = response.redirect;
                    } else {
                        var f = window[$(form).data("onsuccess")];
                        f(form);
                    }
                }
            },
            error: function (error) {
                if ($('#login_captcha').length > 0) {
                    if ($('#login_captcha').is(':empty')) {
                        grecaptcha.render('login_captcha', {
                            'sitekey': site_key
                        });
                    } else {
                        grecaptcha.reset();
                    }
                }

                var html = '';
                $.each(error.responseJSON.errors, function (index, value) {
                    html += '' +
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">\n' +
                        value[0] +
                        '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
                        '    <span aria-hidden="true">&times;</span>\n' +
                        '  </button>\n' +
                        '</div>';
                });

                $(".validacion").html(html);

                $('html, body').animate({
                    scrollTop: $(".validacion").offset().top - 10
                });

                form.submitting = false;
                $(ajaxLoader).remove();
                $(form).find(":submit").attr("disabled", false);
            }
        });
    }
    return false;
});