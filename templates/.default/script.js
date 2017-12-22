$(document).ready(function () {
    // validation start

    var phoneInput = $('#clientTel');
    var reName = XRegExp('^[\\pL ]+$');

    phoneInput.mask('+7(000)-000-0000');
    phoneInput.on('focus', function () {
        $(this).val('+7(');
    });

    jQuery.validator.addMethod('mobileRu', function () {
        var phRe = /^\+7\(([0-9]{3})\)\-([0-9]{3})\-([0-9]{4})$/;
        return phoneInput.val().match(phRe);
    }, 'Введите корректный номер телефона');

    jQuery.validator.addMethod("clientName", function(value, element) {
        return this.optional(element) || reName.test(value);
    }, "Поле должно содержать только буквы и пробелы");

    jQuery.extend(jQuery.validator.messages, {
        required: "Это поле обязательно для заполнения",
        lettersonly: "Поле должно содержать только буквы"
    });

    $(document).on('blur', '#contactForm input[type=text], #contactForm input[type=tel], #contactForm input[type=email]', function () {
        if ($(this).hasClass('valid')) return;
        $(this).val('');
    });



    var form = $('.feedback-form').find('form');

    form.validate();

    // validation end

    // services start
    var sCateg = $('select[name=category]').val();
    var selServices = $('select.service');
    var termsSpan = $('#termsService');
    var priceSpan = $('#priceService');
    var curServiceName = '';
    var curService = '';
    var curPrice = 0;
    var curTerms = '';
    var curCost = 0;
    var canNotify = true;
    var delay = 500;
    var servicesList = "";
    var delimiter = "|";

    $('#calcSendForm button[type=submit]').on('click', function (e) {
        e.preventDefault();

        $('.selected-services').find('li').each(function (ind, item) {
            if (servicesList != "")
                servicesList += delimiter;
            servicesList += $(item).text().trim();
        });

        $('#servicesData').val(b64EncodeUnicode(servicesList));
        $('#servicesCost').val($('#cost').text());
        $('#calcSendForm').submit();
    });

    selServices.each(function (ind, item) {
        if (item.name == sCateg) {
            $(item).show();
            curService = item.value;
            setPriceTerms(curService);
        }
    });

    $('#confirmService').on('click', function (e) {
        e.preventDefault();
        var goodsExists = curCost > 0;

        if (!goodsExists) {
            showMessage($('#calcSendForm').data('errmsg'), 'error');
            return;
        }

        $('.calc').addClass('animated bounceOutDown');
        $('.feedback-form').show();

        setTimeout(function () {
            $('.feedback-form').addClass('animated bounceInUp').css('opacity', 1);
        }, delay);
    });

    $('#reset').on('click', function () {
        curCost = 0;
        $('#cost').text(curCost);
        $('.selected-services').empty();
    });

    selServices.on('change', function () {
        setPriceTerms($(this).val());
    });

    $('select[name=category]').on('change', function () {
        sCateg = $(this).val();
        selServices.each(function (ind, item) {
            if (item.name == sCateg) {
                $(item).show();
                setPriceTerms(item.value);
            } else {
                if ($(item).is(':visible'))
                    $(item).hide();
            }
        });
    });

    $('#addService').on('click', function (e) {
        e.preventDefault();
        curCost = parseInt($('#cost').text());
        curCost = parseInt(curPrice) + curCost;
        $('#cost').text(curCost);
        $('.selected-services').append($('<li/>').text(curServiceName));
    });

    function showMessage(msg, cls) {
        if (!canNotify)
            return;
        var notifyContainer = $('.notifications');
        canNotify = false;
        notifyContainer.append($('<div/>').addClass(cls).text(msg));
        setTimeout(function () {
            notifyContainer.find('.' + cls).animate({
                'opacity' : 0
            }, delay*3, function () {
                notifyContainer.find('.' + cls).remove();
                canNotify = true;
            });
        }, delay);
    }

    function b64EncodeUnicode(str) {
        // first we use encodeURIComponent to get percent-encoded UTF-8,
        // then we convert the percent encodings into raw bytes which
        // can be fed into btoa.
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
            function toSolidBytes(match, p1) {
                return String.fromCharCode('0x' + p1);
            }));
    }

    function setPriceTerms(service) {
        var option = $('option[data-name=' + service + ']');
        curPrice = option.data('price');
        curTerms = option.data('terms');
        curServiceName = option.text();
        termsSpan.text(curTerms);
        priceSpan.text(curPrice);
    }
    // services end
});