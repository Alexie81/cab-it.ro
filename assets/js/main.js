/***************************************************
==================== JS INDEX ======================
****************************************************
01. PreLoader Js


****************************************************/

(function ($) {
	"use strict";

	var windowOn = $(window);
	////////////////////////////////////////////////////
	// 01. PreLoader Js
	windowOn.on('load', function () {
		$("#loading").fadeOut(500);
	});


	//mobile menu/////
	$('#tp-mobile-menu').meanmenu({
		meanMenuContainer: '.tp-mobile-menu-pos',
		meanScreenWidth: "1199",
	});

		////////////////////////////////////
	// add class
	$(".tp-toogle").on('click', function () {
		$(".tp-side-info").addClass("tp-side-info-open");
		$(".offcanvas-overlay").addClass("offcanvas-overlay-open");
	});

	$(".tp-side-close").on('click', function () {
		$(".tp-side-info").removeClass("tp-side-info-open");
		$(".offcanvas-overlay").removeClass("offcanvas-overlay-open");
	});

	$(".offcanvas-overlay").on('click', function () {
		$(".tp-side-info").removeClass("tp-side-info-open");
		$(".offcanvas-overlay").removeClass("offcanvas-overlay-open");
	});


		////////////////////////////////////////////////////
	// 03. Search Js
	$(".search-open-btn").on("click", function () {
		$(".search__popup").addClass("search-opened");
		$(".search-popup-overlay").addClass("search-popup-overlay-open");
	});

	
	$(".search-close-btn").on("click", function () {
		$(".search__popup").removeClass("search-opened");
		$(".search-popup-overlay").removeClass("search-popup-overlay-open");
	});
	
	$(".job-form-open-btn").on("click", function () {
		$(".job__form").slideToggle("job__form");
	});

	$(".search-popup-overlay").on('click', function () {
		$(".search__popup").removeClass("search-opened");
		$(".search-popup-overlay").removeClass("search-popup-overlay-open");
	});

	// last child menu
	$('.wp-menu nav > ul > li').slice(-4).addClass('menu-last');


	////////////////////////////////////////////////////
	// 03. Offcanvas Js
	$(".offcanvas-open-btn").on("click", function () {
		$(".offcanvas__area").addClass("offcanvas-opened");
		$(".body-overlay").addClass("opened");
	});
	$(".offcanvas-close-btn").on("click", function () {
		$(".offcanvas__area").removeClass("offcanvas-opened");
		$(".body-overlay").removeClass("opened");
	});


	////////////////////////////////////////////////////
	// 04. Body overlay Js
	$(".body-overlay").on("click", function () {
		$(".offcanvas__area").removeClass("offcanvas-opened");
		$(".cartmini__area").removeClass("cartmini-opened");
		$(".body-overlay").removeClass("opened");
	});


	$(".cartmini-open-btn").on("click", function () {
		$(".cartmini__area").addClass("cartmini-opened");
		$(".body-overlay").addClass("opened");
	});


	$(".cartmini-close-btn").on("click", function () {
		$(".cartmini__area").removeClass("cartmini-opened");
		$(".body-overlay").removeClass("opened");
	});



	////////////////////////////////////////////////////
	// 06. Sticky Header Js
	windowOn.on('scroll', function () {
		var scroll = $(window).scrollTop();
		if (scroll < 100) {
			$("#header-sticky").removeClass("header-sticky");
		} else {
			$("#header-sticky").addClass("header-sticky");
		}
	});


	////////////////////////////////////////////////////
	// 07. Data CSS Js
	$("[data-background").each(function () {
		$(this).css("background-image", "url( " + $(this).attr("data-background") + "  )");
	});

	$("[data-width]").each(function () {
		$(this).css("width", $(this).attr("data-width"));
	});

	$("[data-bg-color]").each(function () {
		$(this).css("background-color", $(this).attr("data-bg-color"));
	});

	////////////////////////////////////////////////////
	// 07. Nice Select Js
	$('.active-class-select').niceSelect();

	////////////////////////////////////////////////////
	// 07. Smooth Scroll Js
	function smoothSctoll() {
		$('.smooth a').on('click', function (event) {
			var target = $(this.getAttribute('href'));
			if (target.length) {
				event.preventDefault();
				$('html, body').stop().animate({
					scrollTop: target.offset().top - 120
				}, 1500);
			}
		});
	}
	smoothSctoll();

	function back_to_top() {
		var btn = $('#back_to_top');
		var btn_wrapper = $('.back-to-top-wrapper');

		windowOn.scroll(function () {
			if (windowOn.scrollTop() > 300) {
				btn_wrapper.addClass('back-to-top-btn-show');
			} else {
				btn_wrapper.removeClass('back-to-top-btn-show');
			}
		});

		btn.on('click', function (e) {
			e.preventDefault();
			$('html, body').animate({ scrollTop: 0 }, '300');
		});
	}
	back_to_top();


	// rtl_setting
	let rtl_setting = false;

	// mainSlider
	function mainSlider() {
		var BasicSlider = $('.slider-active');
		BasicSlider.on('init', function (e, slick) {
			var $firstAnimatingElements = $('.single-slider:first-child').find('[data-animation]');
			doAnimations($firstAnimatingElements);
		});
		BasicSlider.on('beforeChange', function (e, slick, currentSlide, nextSlide) {
			var $animatingElements = $('.single-slider[data-slick-index="' + nextSlide + '"]').find('[data-animation]');
			doAnimations($animatingElements);
		});
		BasicSlider.slick({
			autoplay: true,
			autoplaySpeed: 4000,
			dots: false,
			fade: true,
			rtl: rtl_setting,
			arrows: true,
			prevArrow: '<button type="button" class="slick-prev"><i class="far fa-arrow-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next"><i class="far fa-arrow-right"></i></button>',
			responsive: [{
				breakpoint: 767,
				settings: {
					dots: false,
					arrows: false
				}
			}]
		});

		function doAnimations(elements) {
			var animationEndEvents = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
			elements.each(function () {
				var $this = $(this);
				var $animationDelay = $this.data('delay');
				var $animationType = 'animated ' + $this.data('animation');
				$this.css({
					'animation-delay': $animationDelay,
					'-webkit-animation-delay': $animationDelay
				});
				$this.addClass($animationType).one(animationEndEvents, function () {
					$this.removeClass($animationType);
				});
			});
		}
	}
	mainSlider();


	$('.tp-active').slick({
		autoplay: true,
		autoplaySpeed: 4000,
		dots: true,
		fade: false,
		arrows: true,
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-chevron-left"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-chevron-right"></i></button>',
		appendArrows: $('.the-arrow'),
		infinite: true,
		speed: 300,
		rtl: rtl_setting,
		slidesToShow: 4,
		slidesToScroll: 4,
		responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3,
					infinite: true,
					dots: true
				}
			},
			{
				breakpoint: 600,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
			},
			{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}
		]
	});

	// home 3 feature
	////////////////
	function mediaSize() { 
		// / Set the matchMedia /
		if (window.matchMedia('(min-width: 575px)').matches) {
			const panels = document.querySelectorAll('.col-custom')
			panels.forEach(panel => {
				panel.addEventListener('click', () => {
					removeActiveClasses()
					panel.classList.add('active')
				})
			})
		
			function removeActiveClasses() {
				panels.forEach(panel => {
					panel.classList.remove('active')
				})
			}

		} else {
		// / Reset for CSS changes – Still need a better way to do this! /
			$(".col-custom ").addClass("active");
		}
	};
	// / Call the function /
	mediaSize();
	// / Attach the function to the resize event listener /
	  window.addEventListener('resize', mediaSize, false); 



	//  add class faq
	$('.tp-custom-accordion .accordion-items').on("click", function(){
		$(this).addClass('tp-faq-active').siblings().removeClass('tp-faq-active');
	});  

	// service slider active
	$('.tp-service-active').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		speed: 300,
		arrows:true,
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-arrow-left-long"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-arrow-right-long"></i></button>',
		rtl: rtl_setting,
		slidesToShow: 3,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 1200,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1,
			}
		},
			{
			breakpoint: 767,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
			}
		},
		{
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows:false
			}
		}
	]
	});


	// portfolio slider active
	$('.tp-portfolio-active').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		speed: 300,
		arrows:true,
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-arrow-left-long"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-arrow-right-long"></i></button>',
		rtl: rtl_setting,
		slidesToShow: 2,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 767,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
			}
		},
			{
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows:false
			}
		}
	]
	});

	// portfolio slider active
	$('.tp-portfolio-active-3').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		speed: 300,
		arrows:true,
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-arrow-left-long"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-arrow-right-long"></i></button>',
		slidesToShow: 2,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 991,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
			}
		},
		{
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows:false
			}
		}
	]
	});

	// casedetails slider active
	$('.tp-casedetails-active').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		speed: 300,
		arrows:true,
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-arrow-left-long"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-arrow-right-long"></i></button>',
		slidesToShow: 2,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 991,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1,
			}
		},
		{
			breakpoint: 768,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows:false
			}
		}
	]
	});

	// read blog slider active
	$('.tp-read-blog-active').slick({
		dots: false,
		infinite: true,
		autoplay: true,
		speed: 300,
		arrows:true,
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-arrow-left-long"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-arrow-right-long"></i></button>',
		slidesToShow: 1,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 991,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
			}
		},
		{
			breakpoint: 576,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows:false
			}
		}
	]
	});


	// testimonial slider active
	$('.tp-testimonial-active').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		speed: 300,
		appendArrows: $('.testimonial-slider-nav'),
		prevArrow: '<button type="button" class="slick-prev"><i class="fa-regular fa-arrow-left-long"></i></button>',
		nextArrow: '<button type="button" class="slick-next"><i class="fa-regular fa-arrow-right-long"></i></button>',
		rtl: rtl_setting,
		slidesToShow: 3,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 992,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1,
			}
		},
			{
			breakpoint: 767,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1,
			}
		},
	]
	});


	// testimonial-2 slider active
	$('.tp-testimonial-slider-2-active').slick({
		dots: false,
		arrows:true,
		infinite: true,
		autoplay: false,
		fade: true,
  		cssEase: 'linear',
		speed: 300,
		prevArrow: '<button type="button" class="slick-prev tp-btn-hover alt-color"><i class="fa-regular fa-arrow-left-long"></i><b></b></button>',
		nextArrow: '<button type="button" class="slick-next tp-btn-hover alt-color"><i class="fa-regular fa-arrow-right-long"></i><b></b></button>',
		rtl: rtl_setting,
		slidesToShow: 1,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 576,
			settings: {
				arrows:false
			}
		},
	]
	});


	// testimonial-3 slider active
	$('.tp-testimonial-slider-3-active').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        asNavFor: '.tp-testimonial-nav',
		appendArrows: '.tp-slider__arrows',
		prevArrow: '<button type="button" class="slick-prev"><i class="fal fa-angle-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next"><i class="fal fa-angle-right"></i></button>',
    });
    $('.tp-testimonial-nav').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.tp-testimonial-slider-3-active',
        dots: false,
        centerMode: false,
        centerPadding: 0,
        focusOnSelect: true,
		arrows: false,
    });


	$('.tp-blog-active').slick({
		dots: false,
		arrows:true,
		appendArrows: '.tp-blog__arrows',
		prevArrow: '<button type="button" class="slick-prev"><i class="fal fa-angle-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next"><i class="fal fa-angle-right"></i></button>',	
		infinite: true,
		autoplay: false,
		fade: true,
  		cssEase: 'linear',
		speed: 300,
		rtl: rtl_setting,
		slidesToShow: 1,
		slidesToScroll: 1,
		responsive: [
			{
			breakpoint: 576,
			settings: {
				arrows:false
			}
		},
	]
	});

	
	////////////////////////////////////////////////////
	// 08. slider__active Slider Js
	if ($(".slider__active").length > 0) {
		let sliderActive1 = ".slider__active";
		let sliderInit1 = new Swiper(sliderActive1, {
			slidesPerView: 1,
			slidesPerColumn: 1,
			paginationClickable: true,
			loop: true,
			rtl: rtl_setting,
			effect: 'fade',

			autoplay: {
				delay: 5000000,
			},

			// If we need pagination
			pagination: {
				el: ".main-slider-dot",
				dynamicBullets: false,
				clickable: true,
			},

			// Navigation arrows
			navigation: {
				nextEl: ".slider-button-next",
				prevEl: ".slider-button-prev",
			},

			a11y: false,
		});

		function animated_swiper(selector, init) {
			let animated = function animated() {
				$(selector + " [data-animation]").each(function () {
					let anim = $(this).data("animation");
					let delay = $(this).data("delay");
					let duration = $(this).data("duration");

					$(this)
						.removeClass("anim" + anim)
						.addClass(anim + " animated")
						.css({
							webkitAnimationDelay: delay,
							animationDelay: delay,
							webkitAnimationDuration: duration,
							animationDuration: duration,
						})
						.one(
							"webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",
							function () {
								$(this).removeClass(anim + " animated");
							}
						);
				});
			};
			animated();
			// Make animated when slide change
			init.on("slideChange", function () {
				$(sliderActive1 + " [data-animation]").removeClass("animated");
			});
			init.on("slideChange", animated);
		}

		animated_swiper(sliderActive1, sliderInit1);
	}


	var slider = new Swiper('.active-class', {
		slidesPerView: 1,
		spaceBetween: 30,
		loop: true,
		rtl: rtl_setting,
		pagination: {
			el: ".testimonial-pagination-6",
			clickable: true,
			renderBullet: function (index, className) {
				return '<span class="' + className + '">' + '<button>' + (index + 1) + '</button>' + "</span>";
			},
		},
		breakpoints: {
			'1200': {
				slidesPerView: 3,
			},
			'992': {
				slidesPerView: 2,
			},
			'768': {
				slidesPerView: 2,
			},
			'576': {
				slidesPerView: 1,
			},
			'0': {
				slidesPerView: 1,
			},
		},
	});

	var postboxSlider = new Swiper('.postbox__slider', {
		slidesPerView: 4,
		spaceBetween: 4,
		loop: true,
		rtl: rtl_setting,
		autoplay: {
			delay: 3000,
		},
		// Navigation arrows
		navigation: {
			nextEl: ".postbox-slider-button-next",
			prevEl: ".postbox-slider-button-prev",
		},
		breakpoints: {
			'1200': {
				slidesPerView: 4,
			},
			'992': {
				slidesPerView: 1,
			},
			'768': {
				slidesPerView: 1,
			},
			'576': {
				slidesPerView: 1,
			},
			'0': {
				slidesPerView: 1,
			},
		},
	});

	////////////////////////////////////////////////////
	// 13. Masonary Js
	$('.grid').imagesLoaded(function () {
		// init Isotope
		var $grid = $('.grid').isotope({
			itemSelector: '.grid-item',
			percentPosition: true,
			masonry: {
				// use outer width of grid-sizer for columnWidth
				columnWidth: '.grid-item',
			}
		});


		// filter items on button click
		$('.masonary-menu').on('click', 'button', function () {
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({ filter: filterValue });
		});

		//for menu active class
		$('.masonary-menu button').on('click', function (event) {
			$(this).siblings('.active').removeClass('active');
			$(this).addClass('active');
			event.preventDefault();
		});

	});

	/* magnificPopup img view */
	$('.popup-image').magnificPopup({
		type: 'image',
		gallery: {
			enabled: true
		}
	});

	/* magnificPopup video view */
	$(".popup-video").magnificPopup({
		type: "iframe",
	});

	////////////////////////////////////////////////////
	// 14. Wow Js
	new WOW().init();


	////////////////////////////////////////////////////
	// 21. Counter Js
	new PureCounter();
	new PureCounter({
		filesizing: true,
		selector: ".filesizecount",
		pulse: 2,
	});


	/* magnificPopup video view */
	$(".popup-video").magnificPopup({
		type: "iframe",
	});

	////////////////////////////////////////////////////
	// 22. Parallax Js
	if ($('.scene').length > 0) {
		$('.scene').parallax({
			scalarX: 10.0,
			scalarY: 15.0,
		});
	};

	////////////////////////////////////////////////////
	// 22. Parallax Js
	if ($('.scene').length > 0) {
		$('.scene').parallax({
			scalarX: 10.0,
			scalarY: 15.0,
		});
	};
	
	////////////////////////////////////////////////////
	// 22. Isotop-Case
	if ($(".case-stadies").length > 0) {
		var $grid = $('.case-stadies').isotope({
			itemSelector: '.grid-item',
			percentPosition: true,
			layoutMode: 'fitRows',
			masonry: {
				// use outer width of grid-sizer for columnWidth
				columnWidth: 1,
			}
		});

		// filter items on button click
		$('.masonary-menu').on('click', 'button', function () {
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({ filter: filterValue });
		});

		//for menu active class
		$('.masonary-menu button').on('click', function (event) {
			$(this).siblings('.active').removeClass('active');
			$(this).addClass('active');
			event.preventDefault();
		});
	}

	////////////////////////////////////////////////////
	// 14. tp btn hover
	if ($(".tp-btn-hover").length > 0) {
		$('.tp-btn-hover').on('mouseenter', function (e) {
				var parentOffset = $(this).offset(),
					relX = e.pageX - parentOffset.left,
					relY = e.pageY - parentOffset.top;
				$(this).find('b').css({ top: relY, left: relX })
		});
	
		$('.tp-btn-hover').on('mouseout', function (e) {
	
			var parentOffset = $(this).offset(),
				relX = e.pageX - parentOffset.left,
				relY = e.pageY - parentOffset.top;
			$(this).find('b').css({ top: relY, left: relX })
	
		});
	}


})(jQuery);