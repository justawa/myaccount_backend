(function ($) {
    $.fn.dateFormat = function (options) {
        var params = $.extend({
            format: 'xx-xx-xxxx',
        }, options);

        if (params.format === 'xx-xx-xxxx') {
            $(this).bind('paste', function (e) {
                e.preventDefault();
                var inputValue = e.originalEvent.clipboardData.getData('Text');
                if (!$.isNumeric(inputValue)) {
                    return false;
                } else {
                    inputValue = String(inputValue.replace(/(\d{2})(\d{2})(\d{4})/, "$1-$2-$3"));
                    // $(this).val(inputValue);
                    // $(this).val('');
                    inputValue = inputValue.substring(0, 10);
                    $(this).val(inputValue);
                }
            });
            $(this).on('keyup', function (e) {
                if ( e.which != 8 && e.which != 0 ) {
                    if (!((e.which >= 48 && e.which <= 57) || (e.which >= 96 && e.which <= 105))) {
                        return false;
                    }
                }

                var curchr = this.value.length;
                var curval = $(this).val();

                if (curchr == 2 && e.which != 8 && e.which != 0) {
                    $(this).val(curval + "-");
                } else if (curchr == 5 && e.which != 8 && e.which != 0) {
                    $(this).val(curval + "-");
                }
                $(this).attr('maxlength', '10');
            });

        } else if (params.format === 'xx/xx/xxxx') {
            $(this).on('keypress', function (e) {

                // console.log(e.which);

                if ( e.which != 8 && e.which != 0 ) {
                    if ( !(e.which >= 48 && e.which <= 57) ) {
                        e.preventDefault();
                    }
                }

                var curchr = this.value.length;
                var curval = $(this).val();

                if (curchr == 2 && e.which != 8 && e.which != 0) {
                    $(this).val(curval + "/");
                } else if (curchr == 5 && e.which != 8 && e.which != 0) {
                    $(this).val(curval + "/");
                }
                $(this).attr('maxlength', '10');
            });
            $(this).bind('paste', function (e) {
                e.preventDefault();
                var inputValue = e.originalEvent.clipboardData.getData('Text');
                if (!$.isNumeric(inputValue)) {
                    return false;
                } else {
                    inputValue = String(inputValue.replace(/(\d{2})(\d{2})(\d{4})/, "$1/$2/$3"));
                    // $(this).val(inputValue);
                    // $(this).val('');
                    inputValue = inputValue.substring(0, 10);
                    $(this).val(inputValue);
                }
            });

        }
    }
}(jQuery));