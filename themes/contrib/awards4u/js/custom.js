(function($) {
$('.mobile-filters').on('click', '.filters', function(){

    $('.path-catalog #block-category').toggle();
	$('.path-catalog #block-pricesearch').toggle();
	$('.path-rush-awards #block-category-2').toggle();
    $('.path-rush-awards #block-pricesearch-2').toggle();
    $('.path-catalog #block-awards4ufacetsearchreset').toggle();
    $('.mobile-filters .filters').toggleClass('open');
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

$(window).width();	
var width = $(window).width();

if ((width >= 768  )){
	setTimeout(function(){
		$(".path-frontpage .new-site").fadeOut("slow", function () {
			
		$(".path-frontpage .new-site").remove();
			});
		}, 11000);
  }
  else {
	setTimeout(function(){
		$(".path-frontpage .new-site").fadeOut("slow", function () {
			
		$(".path-frontpage .new-site").remove();
			});
		}, 18000);
  }


$(".new-site #hide").click(function(){
	$("#block-headernewsiteintro").hide();
});

})(jQuery);
