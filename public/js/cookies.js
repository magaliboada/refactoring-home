initalizeRetailBanner();


$( ".button-cerrar" ).click(function() {
  Cookies.set('popup', 'seen');
  $('.banner-retail').css('display', 'none');
  $('body .home, .main-content').css('opacity', '1');
});

$( ".button-readmore" ).click(function() {
  
  var url = "/info/privacy";    
  $(location).attr('href',url);  
});
  
  function initalizeRetailBanner() {
      let cookie = Cookies.get('popup');

      console.log(cookie);

        if((window.location.pathname != '/info/privacy' && window.location.pathname != '/info/terms') && cookie == null)
            $('.cookies').css('display', 'block');
  }