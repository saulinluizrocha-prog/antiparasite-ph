

$(document).ready(function() {

    // Instantiate a counter
    clock = new FlipClock($('.clock'), 94, {
        clockFace: 'Counter',
        autoStart: true,
        interval: 7000,
        countdown: true
    });

});


/* end timer*/

/* toform*/
$(document).ready(function () {
    $('.toform').click(function (e) {
        e.preventDefault();
        var a = $('.js_submit'), b = a.closest('form');
        if ($('form#toform').length) a = $('#toform .js_submit'), b = a.closest('form#toform');
        if (b.length && a.is(':visible')) {
            $("html,body").animate({ scrollTop: b.offset().top }, 1000);
        }
        return false;
    });
});
/* end toform*/

/* nav scroll*/
$(document).ready(function () {
    $("#menu").on("click", "a", function (event) {
        event.preventDefault();
        var id = $(this).attr('href'),
            top = $(id).offset().top;
        $('body,html').animate({ scrollTop: top }, 1500);
    });
});
/* end nav scroll*/

$(document).ready(function () {


    $('.reviews-list').slick({
        infinite: true,
        adaptiveHeight: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        dots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        arrows: true,
    });
});
 