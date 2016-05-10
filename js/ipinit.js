if (document.URL.match(/(gif|png|jpg|jpeg)$/i) && (navigator.appVersion.indexOf('Chrome/') != -1 || navigator.appVersion.indexOf('Safari/') != -1)) {
	alert('For direct jpg/gif/png url, please fetch image at Add > Pin > From Web');
}

(function(){
	var v = '1.7';

	if (window.jQuery === undefined || window.jQuery.fn.jquery < v) {
		var done = false;
		var script = document.createElement('script');
		script.src = '//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
		script.onload = script.onreadystatechange = function(){
			if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
				done = true;
				ipinit();
			}
		};
		document.getElementsByTagName('head')[0].appendChild(script);
	} else {
		ipinit();
	}

	function ipinit() {
		(window.ipinit = function() {
			if (jQuery('#ipinframe').length == 0) {
				jQuery('body').css('overflow', 'hidden')
				.append("\
				<div id='ipinframe'>\
					<div id='ipinframebg'><p>Loading...</p></div>\
					<div id='ipinheader'><p id='ipinclose'>X</p><p id='ipinlogo'>" + ipinsite + "</p></div>\
					<div id='ipinimages'></div>\
					<style type='text/css'>\
						#ipinframe {color: #333;}\
						#ipinframebg {background: #f2f2f2; display: none; position: fixed; top: 0; right: 0; bottom: 0; left: 0; z-index: 2147483646;}\
						#ipinframebg p {background: #999; border-radius: 8px; color: white; font: normal normal bold 16px\/22px Helvetica, Arial, sans-serif; margin: -2em auto 0 -9.5em; padding: 12px; position: absolute; top: 50%; left: 50%; text-align: center; width: 15em;}\
						#ipinframe #ipinheader {background: white; border-bottom: 1px solid #e7e7e7; color: white; height: 50px; margin: 0; overflow: hidden; padding: 0; position: fixed; top: 0; left: 0; text-align: center; width: 100%; z-index: 2147483647;}\
						#ipinframe #ipinheader #ipinlogo {color: black; font: normal normal bold 20px\/20px Helvetica, Arial, sans-serif; margin: 0; padding: 12px 15px 13px 20px;}\
						#ipinframe #ipinheader #ipinclose {background: #f33; color: white; cursor: pointer; float: right; font: normal normal bold 16px\/16px Helvetica, Arial, sans-serif; line-height: 50px; margin: 0; padding: 0 20px;}\
						#ipinimages {position: fixed; top: 60px; left: 0; width: 100%; height: 94%; overflow-x: auto; overflow-y: scroll; text-align: center; z-index: 2147483647;}\
						#ipinimages .ipinimgwrapper {background: #fcfcfc; border: 1px solid #ddd; cursor: pointer; display: inline-block; height: 200px; margin: 15px; overflow: hidden; position: relative; width: 200px;}\
						#ipinimages .ipinbutton {background: rgba(0, 0, 0, 0.5); border-radius: 8px; color: white; font: normal normal bold 36px/36px Helvetica, Arial, sans-serif; padding: 8px 16px; display: none; margin-left: -24px; margin-top: -36px; position: absolute; top: 50%; left:50%;}\
						#ipinimages .ipindimension {background: white; font: normal normal normal 12px/12px Helvetica, Arial, sans-serif; padding: 3px 0; position: absolute; right: 0; bottom: 0; left: 0;}\
						#ipinimages img {width: 100%; height: auto;}\
					</style>\
				</div>");
				
				jQuery('#ipinframebg').fadeIn(200);
				
				var imgarr = [];
				var videoflag = '0';
				var documentURL = document.URL;

				if (documentURL.indexOf('youtube.com/watch') != -1) {
					video_id = document.URL.match('[\\?&]v=([^&#]*)');
					imgsrc = 'http://img.youtube.com/vi/' + video_id[1] + '/0.jpg';
					imgarr.unshift([imgsrc,480,360]);
					videoflag = '1';
					display_thumbnails(imgarr, videoflag);
					jQuery('#movie_player').css('visibility','hidden');
				} else if (documentURL.match(/vimeo.com\/(\d+)($|\/)/)) {
					video_id = documentURL.split('/')[3];
					
					jQuery.getJSON('http://www.vimeo.com/api/v2/video/' + video_id + '.json?callback=?', {format: 'json'}, function(data) {
						imgsrc = data[0].thumbnail_large;
						imgarr.unshift([imgsrc,640,360]);
						videoflag = '1';
						display_thumbnails(imgarr, videoflag);
					});
				} else if (documentURL.match(/redtube.com\/(\d+)($|\/)/)) {
					imgsrc = jQuery('meta[property="og:image"]').attr('content').replace('m.jpg', 'i.jpg');
					imgarr.unshift([imgsrc,582,388]);
					videoflag = '1';
					display_thumbnails(imgarr, videoflag);
				} else if (documentURL.indexOf('youporn.com/watch/') != -1) {
					imgsrc = jQuery('#galleria img:eq(7)').attr('src');
					imgarr.unshift([imgsrc,720,576]);
					videoflag = '1';
					display_thumbnails(imgarr, videoflag);
				} else {
					jQuery('img').each(function() {
						var imgsrc = jQuery(this).prop('src');
						
						var imgwidth = this.naturalWidth;
						if (!imgwidth) {
							imgwidth = jQuery(this).width();
						}
						
						var imgheight = this.naturalHeight;
						if (!imgheight) {
							imgheight = jQuery(this).height();
						}
						
						if (imgsrc && imgwidth >= 125 && imgheight >= 125) {
							imgarr.unshift([imgsrc,imgwidth,imgheight]);
						}
					});
					
					jQuery('body, div').each(function() {
						var bgimage = jQuery(this).css('background-image');
						
						if (bgimage != 'none') {
						    regex = /(?:\(['|"]?)(.*?)(?:['|"]?\))/,
						    imgsrc = regex.exec(bgimage)[1];
							
							var imgwidth = this.naturalWidth;
							if (!imgwidth) {
								imgwidth = jQuery(this).width();
							}
							
							var imgheight = this.naturalHeight;
							if (!imgheight) {
								imgheight = jQuery(this).height();
							}
							
							if (imgsrc && imgwidth >= 250 && imgheight >= 250) {
								imgarr.unshift([imgsrc,imgwidth,imgheight]);
							}
						}
					});
					
					display_thumbnails(imgarr, videoflag);
				}
			}

			jQuery('#ipinheader').on('click', '#ipinclose', function() {
				if (documentURL.indexOf('youtube.com/watch') != -1) {
					jQuery('#movie_player').css('visibility','visible');
				}
				jQuery('body').css('overflow', 'visible');
				jQuery('#ipinframe').fadeOut(200, function() {
					jQuery(this).remove();
				});
			});
			
			jQuery('#ipinimages').on('click', '.ipinimgwrapper', function() {
				window.open(jQuery(this).data('href'), 'ipinwindow', 'width=400,height=760,left=0,top=0,resizable=1,scrollbars=1');
				if (documentURL.indexOf('youtube.com/watch') != -1) {
					jQuery('#movie_player').css('visibility','visible');
				}
				jQuery('body').css('overflow', 'visible');
				jQuery('#ipinframe').remove();
			});
			
			jQuery('#ipinimages').on('mouseover', '.ipinimgwrapper', function() {
				jQuery(this).find('.ipinbutton').show();
			}).on('mouseout', '.ipinimgwrapper', function() {
				jQuery(this).find('.ipinbutton').hide();
			});
			
			jQuery(document).keyup(function(e) {
				if (e.keyCode == 27) { 
				if (documentURL.indexOf('youtube.com/watch') != -1) {
					jQuery('#movie_player').css('visibility','visible');
				}
				jQuery('body').css('overflow', 'visible');
				jQuery('#ipinframe').fadeOut(200, function() {
					jQuery(this).remove();
				});
				}
			});
		})();
	}
	
	function display_thumbnails(imgarr, videoflag) {
		if (!imgarr.length) {
			jQuery('#ipinframebg').html('<p>Sorry, unable to find anything to save on this page.</p>');
		} else if (document.URL.match(/(gif|png|jpg|jpeg)$/i)) {
			jQuery('#ipinimages').hide();
			jQuery('#ipinframebg').html('<p>For direct jpg/gif/png url,<br />please fetch image at<br /><a href="' + ipinsiteurl + '/itm-settings/">Add > Pin > From Web</a></p>');
		} else {
			imgarr.sort(function(a,b)
			{
				if (a[1] == b[1]) return 0;
				return a[1] > b[1] ? -1 : 1;
			});
			
			page_title = jQuery.trim(document.getElementsByTagName('title')[0].innerHTML);
			page_description = jQuery('meta[name="description"]').attr('content');
			if (page_description && page_description.length > 255) {
				page_description = page_description.substr(0, 255) + '...';
			}

			var imgstr = '';
			for (var i = 0; i < imgarr.length; i++) {				
				if (videoflag == '0') {
					imgstr += '<div class="ipinimgwrapper" data-href="' + ipinsiteurl + 'itm-settings/?m=bm&imgsrc=' + encodeURIComponent(imgarr[i][0].replace('http','')) + '&source=' + encodeURIComponent(document.URL.replace('http','')) + '&t=' + encodeURIComponent(page_title) + '&desc=' + encodeURIComponent(page_description) + '&video=' + videoflag + '"><div class="ipinbutton">+</div><div class="ipindimension">' + parseInt(imgarr[i][1],10) + ' x ' + parseInt(imgarr[i][2],10) + '</div><img src="' + imgarr[i][0] + '" /></div>';
				} else {
					imgstr += '<div class="ipinimgwrapper" data-href="' + ipinsiteurl + 'itm-settings/?m=bm&imgsrc=' + encodeURIComponent(imgarr[i][0].replace('http','')) + '&source=' + encodeURIComponent(document.URL.replace('http','')) + '&t=' + encodeURIComponent(page_title) + '&desc=' + encodeURIComponent(page_description) + '&video=' + videoflag + '"><div class="ipinbutton">+</div><div class="ipindimension"> Video </div><img src="' + imgarr[i][0] + '" /></div>';
				}
			}
			jQuery('#ipinframebg p').fadeOut(200);
			jQuery('#ipinimages').css('height',jQuery(window).height()-jQuery('#ipinheader').height()-20)
								.html(imgstr + '<div style="height:40px;clear:both;"><br /></div>');
			if ((navigator.appVersion.indexOf('Chrome/') != -1 || navigator.appVersion.indexOf('Safari/')) && videoflag != '1') {
				jQuery('#ipinimages .ipinimgwrapper').css('float','left');
			}
		}	
	}
})();