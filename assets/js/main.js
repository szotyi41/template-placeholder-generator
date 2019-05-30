$(document).ready(function(){

	$('.et-slogan').textfill({ minFontPixels: 12, maxFontPixels: 36 });
	$('.et-product').textfill({ minFontPixels: 8, maxFontPixels: 18 });
	$('.et-paragraph').textfill({ minFontPixels: 8, maxFontPixels: 32 });

	// The banner scene rotating function
	// ------------------------------------------
	function loop() {

		$('.et-scene-1, .et-scene-2, .et-scene-3').css('opacity','0').removeClass('animated slideInDown slideInRight');

	  setTimeout( function(){ 
	    $('.et-scene-1').css('opacity','1').addClass('animated slideInDown');
	  }, 500);  

	  setTimeout( function(){ 
	  	$('.et-scene-1').css('opacity','0');
	    $('.et-scene-2').css('opacity','1').addClass('animated slideInRight');
	  }, 5000);

	  setTimeout( function(){ 
	  	$('.et-scene-2').css('opacity','0');
	    $('.et-scene-3').css('opacity','1').addClass('animated slideInRight');
	  }, 10000);

	}

	loop(); 

	// Infinite loop the slides
	setInterval(function(){ loop() }, 15000);

	//$('.et-scene-1, .et-scene-2').css('opacity','0');
	//$('.et-scene-3').css('opacity','1');



});