$(document).ready(function () {

    setConteinerHeight();

    $( window ).resize(function() {
        setConteinerHeight();
    });

    



    
})

function setConteinerHeight() {
    var headerh = $(".header").height();
    var bodyh = $('.content.container').height();
    var footerh = $('.footer').height();
    var windowh = $(window).height();

    var minHeighContainer = windowh - headerh - footerh;

    $('.content.container').css('min-height', minHeighContainer);
}