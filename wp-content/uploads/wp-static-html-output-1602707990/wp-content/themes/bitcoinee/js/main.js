  jQuery(document).ready(function($){
  	$(".owl-carousel").owlCarousel({
  		'items': 		1,
  		'lazyLoad': 	true,
  		'loop': 		true,
  		'center': 		true,
  		'touchDrag': 	true,
  		'nav': 			true,
  		'autoplay':		true,
  		'transitionStyle': 'fade',
  		'navText':      '',
  		'animateOut':	'fadeOut',
  		'animateIn': 	'fadeIn',
      'autoHeight': false,
      'dots': false,
  	}  	);
  	$(".owl-carousel").on( "changed.owl.carousel", function(event){
  		var item = event.item.index-2;
  		$(".slide-caption").removeClass("animated slideInRight");
  		$('.owl-item').not('.cloned').eq(item).find('.slide-caption').addClass('animated slideInDown');
  	} );

    
  });

  jQuery(document).ready(function($){
    $(".breakingnews-list").slick({
      'vertical': true,
      'autoplay': true,
      'autoplaySpeed': 2000,
      'slidesToShow': 1,
      'speed': 300,
      'prevArrow': false,
      'nextArrow': false,
      'adaptiveHeight': true,
      'infinite': true,
    });
 
  });

	jQuery(document).ready(function($){
		if ($('#gotop').length) {
    var scrollTrigger = 100, // px
        backToTop = function () {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > scrollTrigger) {
                $('#gotop').addClass('show');
            } else {
                $('#gotop').removeClass('show');
            }
        };
    backToTop();
    $(window).on('scroll', function () {
        backToTop();
    });
    $('#gotop').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });
}	
	});	
