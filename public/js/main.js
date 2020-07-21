$(document).ready(function () {

    $('.sf-display-none').remove()
    setConteinerHeight();

    $( window ).resize(function() {
        setConteinerHeight();
    });

    console.log('holi');
    
    placeButton();    
})

function setConteinerHeight() {

    var containerw = $('.content.container').width();
    // $('.header > div > div').width(containerw);
    $('.header > div > div').css('width', containerw/2);
    var headerh = $(".header").height();
    var bodyh = $('.content.container').height();
    var footerh = $('.footer').height();
    var windowh = $(window).height();

    var minHeighContainer = windowh - headerh - footerh;

    $('.content.container').css('min-height', minHeighContainer);
}
