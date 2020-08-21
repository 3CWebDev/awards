(function($) {
$('.mobile-filters').on('click', '.filters', function(){

    $('.path-catalog #block-category').toggle();
	$('.path-catalog #block-pricesearch').toggle();
	$('.path-rush-awards #block-category-2').toggle();
    $('.path-rush-awards #block-pricesearch-2').toggle();
    $('.path-catalog #block-awards4ufacetsearchreset').toggle();
    $('.mobile-filters .filters').toggleClass('open');
});
$('.mobile-lp').on('click', '.filters-lp', function(){

	$('.landing-sidebar').toggle();
	$('.mobile-lp .filters-lp').toggleClass('open');
});

$('.mobile-filters a').click(function(){
		var $this = $(this);
		$this.toggleClass('toggleText');
		if($this.hasClass('toggleText')){
			$this.text('Hide Filters');
		} else {
			$this.text('Show Filters');
		}
	});
	$('.mobile-lp a').click(function(){
		var $this = $(this);
		$this.toggleClass('toggleText');
		if($this.hasClass('toggleText')){
			$this.text('Need Help?');
		} else {
			$this.text('Close');
		}
	});	

$(window).width();	
var width = $(window).width();

if ((width >= 768  )){
	setTimeout(function(){
		$(".new-site").fadeOut("slow", function () {
			
		$(".new-site").remove();
			});
		}, 11000);
  }
  else {
	setTimeout(function(){
		$(".new-site").fadeOut("slow", function () {
			
		$(".new-site").remove();
			});
		}, 18000);
  }


$(".new-site #hide").click(function(){
	$("#block-headernewsiteintro").hide();
});


// WCAG FIXES
$('#block-views-block-slideshow-block-2').attr('role', 'complementary');
$('#block-views-block-slideshow-block-2').attr('aria-label', 'Splash Banner');
$('#block-headerfreeengraving').attr('role', 'contentinfo');
$('#block-headerfreeengraving').attr('aria-label', 'Free Engraving Banner');
$('#block-topnavawards4uphone').attr('role', 'navigation');
$('#block-topnavawards4uphone').attr('aria-label', 'Telephone number and social media links');
$('#block-headernavawards4u-2').attr('role', 'navigation');
$('#block-headernavawards4u-2').attr('aria-hidden', 'true');
$('#block-headernavawards4u-2').attr('aria-label', 'Spacer');
$('#block-views-block-slideshow-block-2 .slick .slide__caption h3').attr('role', 'heading');
$('#block-views-block-slideshow-block-2 .slick .slide__caption h3').attr('aria-level', '3');
$('#block-headerawards4unavicons').attr('role', 'navigation');
$('#block-headerawards4unavicons').attr('aria-label', 'Search & Contact Icons');
$('#block-mainnavigation-2').attr('role', 'navigation');
$('#block-mainnavigation-2').attr('aria-label', 'Main Navigation');
$('#block-bannerawards4u').attr('role', 'banner');
$('#block-bannerawards4u').attr('aria-label', 'Banner Ad');
$('header.navbar').attr('aria-label', 'Navigation Header Container');
$('footer .footer').attr('role', 'navigation');
$('footer .footer').attr('aria-label', 'Footer Navigation');
$('.post-footer').attr('aria-label', 'Phone number, newsletter signup, social media links');
$('.path-product #product-display .column-right #edit-field-order-item-stock-image--wrapper label').attr('aria-label', 'Stock image selection form');
$('.path-product #product-display .column-right #edit-field-order-item-stock-image--wrapper label').attr('aria-hidden', 'true');
$( 'Stock Image' ).insertBefore( ".path-product #product-display .column-right #edit-field-order-item-stock-image--wrapper label img" );
$('.skip-link').attr('role', 'complementary');
$('#skip-link').attr('role', 'complementary');
$('.path-frontpage article').attr('role', 'complementary');
$('.path-frontpage article').attr('aria-label', 'Spacer');
$('.skip-link').attr('aria-label', 'Skip to page content');
$('#skip-link').attr('aria-label', 'Skip to main content');
$('.path-catalog .main-container .col-sm-3').attr('aria-label', 'Search Filters');
$('.path-catalog .main-container .col-sm-3').removeAttr('role');
$('nav.pager-nav').removeAttr('role');
$('.view-product-search-search-api .view-filters .form-type-textfield .form-text').attr('id', 'edit-site-search');
$('.view-product-search-search-api .view-filters .form-actions').attr('id', 'edit-actions-search');

$('#block-category #collapsiblock-wrapper-category > a').addClass('collapse-sidebar-category');
$('#block-pricesearch #collapsiblock-wrapper-pricesearch > a').addClass('collapse-sidebar-price');

$( '<a name="collapse-category"></a>' ).insertBefore( "#block-awards4usidebarmobilefilterstoggle" );
$( '<a name="collapse-pricesearch"></a>' ).insertBefore( "#block-pricesearch" );
$('.region-sidebar-first div.collapsiblock.collapsiblockCollapsed a').removeAttr('role');

$('.path-product #product-display .column-right [data-drupal-selector="edit-field-order-item-logo-wrapper"] fieldset').removeAttr('aria-required');
$('#block-footerawards4uleft').removeAttr('role');
$('#block-footerawards4umiddle').removeAttr('role');

$('#edit-text-type').attr('role', 'radiogroup');
$('#edit-text-type').attr('aria-label', 'Text Submission Options');

$('#block-webform-3 .form-actions').attr('id', 'edit-actions-search2');
$('#block-webform-3 .form-actions .form-submit').attr('id', 'edit-actions-submit2');
$('.webform-submission-website-search-form > .form-actions').attr('id', 'edit-actions-site-search');
$('.webform-submission-website-search-form > .form-actions > button').attr('id', 'edit-actions-site-search-submit');



})(jQuery);
