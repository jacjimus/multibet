/* x-page.navbar */

$(function(){
	//navbar
	let navbar = '#navbar';
	if (!$(navbar).length) return console.error('Invalid navbar selector:', navbar);
	
	//scrollpsy
	$('body').scrollspy({
	    target: navbar,
	    offset: 74,
	});
	
	//collapse
	let navbarCollapse = function(){
	    if ($(navbar).offset().top > 50) $(navbar).addClass('navbar-shrink');
	    else $(navbar).removeClass('navbar-shrink');
	};
	navbarCollapse();
	$(window).scroll(navbarCollapse);
});
