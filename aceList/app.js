/*!
aceList
17 FEB 17 07:00p
@thinkdj
*/
'use strict';

/*
    Form Label Animation [http://www.jqueryscript.net/form/jQuery-Plugin-For-Animated-User-friendly-Input-Placeholders-phAnimate.html]
*/
(function($) {
    $.fn.phAnim = function( options ) {
        var settings = $.extend({}, options),
            label,
            ph;
        function getLabel(input) {
            return $(input).parent().find('label');
        }
        function makeid() {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            for( var i=0; i < 5; i++ )
                text += possible.charAt(Math.floor(Math.random() * possible.length));
            return text;
        }
        return this.each( function() {
            if( $(this).attr('id') == undefined ) {
                $(this).attr('id', makeid());
            }
            if( getLabel($(this)).length == 0 ) {
                if( $(this).attr('placeholder') != undefined ) {
                    ph = $(this).attr('placeholder');
                    $(this).attr('placeholder', '');
                    $(this).parent().prepend('<label for='+ $(this).attr('id') +'>'+ ph +'</label>');
                }
            } else {
                $(this).attr('placeholder', '');
                if(getLabel($(this)).attr('for') == undefined ) {
                    getLabel($(this)).attr('for', $(this).attr('id'));
                }
            }
            $(this).on('focus', function() {
                label = getLabel($(this));
                label.addClass('active focusIn');
            }).on('focusout', function() {
                if( $(this).val() == '' ) {
                    label.removeClass('active');
                }
                label.removeClass('focusIn');
            });
        });
    };
}(jQuery));

$(document).ready(function() {
    $('form#aceListForm input').phAnim();
    aceListInit(1);
});

function aceListInit($focus,$clear) {

    $focus = $focus || 1;
    $clear = $clear || 0;

    var $formEl = $('form#aceListForm');
    $formEl.find("input").focus(); /* If input has data, focus to fix phAnim() label animations */
    /* form.reset() seldom work */
    $formEl.find(':input:not(input[type=button],input[type=submit],button)').each(function(){
        if($clear) $(this).val('');
        $(this).focus();
    });
    $focus?$formEl.find("input:first").focus():null;
}

$(document).ready(function() {

    var $responseShow = $("#aceList-response");

    $( 'form#aceListForm' ).submit(function( e ) {
        e.preventDefault();

        var form = $(this);
        var submitBtn = form.find("input[type=submit]");

        var x = new Date();
        form.find("input[name=dateTime]").val(x);

        $.ajax({
            type: 'POST',
            url: 'aceList.php',
            data: form.serialize(),
            dataType: 'json',
            beforeSend: function() {
                submitBtn.attr("disabled","1");
            },
            complete: function() {
                aceListInit(0);
                submitBtn.removeAttr("disabled");
            },
            success: function( resp ) {

                if("OK"==resp.status) {
                    $responseShow.addClass("green");
                    aceListInit(0,1);
                } else {
                    $responseShow.removeClass("green");
                }

                $("#aceList-response").empty().stop().fadeOut(250);

                $.each(resp.messages, function(index, value){
                    $responseShow.append("<p>"+value+"</p>");
                });
                $("#aceList-response").stop().fadeIn(250);

            },
            error: function( req, status, err ) {
                $responseShow.removeClass("green");
                $responseShow.html("<p> ! Network Error ["+status+"]</p>");
            }
        });
    });

});
