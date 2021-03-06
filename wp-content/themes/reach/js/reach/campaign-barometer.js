/*--------------------------------------------------------
 * Campaign Barometer
---------------------------------------------------------*/
REACH.Barometer = ( function($) {

	// Barometers collection
	var $barometers = $('.barometer'), 

	// Check whether the element is in view
	isInView = function($el) {
	    var docViewTop = $(window).scrollTop(), 
	    	docViewBottom = docViewTop + $(window).height(), 
			elemTop = $el.offset().top,
			elemBottom = elemTop + $el.height();

	    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom)
  			&& (elemBottom <= docViewBottom) &&  (elemTop >= docViewTop) );
	}, 

	// A custom arc to apply to the barometer's paths.
	// @see http://stackoverflow.com/questions/5061318/drawing-centered-arcs-in-raphael-js
	customArc = function (xloc, yloc, value, total, R) {
		var alpha = 360 / total * value,
			a = (90 - alpha) * Math.PI / 180,
			x = xloc + R * Math.cos(a),
			y = yloc - R * Math.sin(a),
			path;

		if (total == value) {
			path = [
				["M", xloc, yloc - R],
				["A", R, R, 0, 1, 1, xloc - 0.01, yloc - R]
			];
		} else {
			path = [
				["M", xloc, yloc - R],
				["A", R, R, 0, +(alpha > 180), 1, x, y]
			];
		}
		return {
			path: path
		};
	}

	// Draws a barometer
	drawBarometer = function($barometer, r, width, height, progress_val) {			
		var progress;

		// Draw the percentage filled arc
		if ( progress_val > 0 ) {
			progress = r.path().attr({ 
				stroke: $barometer.data('progress-stroke'), 
				'stroke-width' : $barometer.data('strokewidth')+1, 
				arc: [width/2, height/2, 0, 100, (width/2)-8]
			});

			// Animate it
			progress.animate({
				arc: [width/2, height/2, progress_val, 100, (width/2)-8]
			}, 1500, "easeInOut", function() {
				$barometer.find('span').animate( { opacity: 1}, 300, 'linear');
			});
		}			
	}, 

	// Init barometer
	initBarometer = function($barometer) {
		var width = $barometer.data('width'), 
			height = $barometer.data('height'),					
			r = Raphael( $barometer[0], width, height),
			drawn = false,							
			progress_val = $barometer.data('progress') > 100 ? 100 : $barometer.data('progress'),
			circle;

		// @see http://stackoverflow.com/questions/5061318/drawing-centered-arcs-in-raphael-js
		r.customAttributes.arc = customArc;

		// Draw the main circle
		circle = r.path().attr({
			stroke: $barometer.data('stroke'), 
			'stroke-width' : $barometer.data('strokewidth'), 
			arc: [width/2, height/2, 0, 100, (width/2)-8]
		});

		// Fill the main circle
		$barometer.parent().addClass('barometer-added');
		circle.animate({ arc: [width/2, height/2, 100, 100, (width/2)-8] }, 1000, function() {
			if ( progress_val === 0 ) {
				$barometer.find('span').animate( { opacity: 1}, 500, 'linear' );
			}					
		});

		if ( isInView($barometer) ) {
			drawBarometer($barometer, r, width, height, progress_val);

			drawn = true;
		}
		else {
			$(window).scroll( function() {
				if ( drawn === false && isInView($barometer) ) {
					drawBarometer($barometer, r, width, height, progress_val);

					drawn = true;
				}
			});
		}
	};

	return {
		init : function() {
			$barometers.each( function() {
				initBarometer( $(this) );
			});					
		},
		getBarometers : function() {
			return $barometers;
		}
	}
})( jQuery );