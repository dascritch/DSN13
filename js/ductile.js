
(function($,document){
//	"use strict";
	function create_name(text) {
		// Convert text to lower case.
		var name = text.toLowerCase();

		// Remove leading and trailing spaces, and any non-alphanumeric
		// characters except for ampersands, spaces and dashes.
		name = name.replace(/^\s+|\s+$|[^a-z0-9&\s-]/g, '');

		// Replace '&' with 'and'.
		name = name.replace(/&/g, 'and');

		// Replaces spaces with dashes.
		name = name.replace(/\s/g, '-');

		// Squash any duplicate dashes.
		name = name.replace(/(-)+\1/g, "$1");

		return name;
	};

	function add_link() {
		// Convert the h2 element text into a value that
		// is safe to use in a name attribute.
		var name = create_name($(this).text());

		// Create a name attribute in the following sibling
		// to act as a fragment anchor.
		$(this).next().attr('name', name);

		// Replace the h2.toggle element with a link to the
		// fragment anchor.  Use the h2 text to create the
		// link title attribute.
		$(this).html(
			'<a href="#' + name + '" title="Reveal ' +
			$(this).text() + ' content">' +
			$(this).html() + '</a>');
	};

	function toggle(event) {
		event.preventDefault();

		// Toggle the 'expanded' class of the h2.toggle
		// element, then apply the slideToggle effect
		// to all siblings.
		$(this).toggleClass('expanded').
			nextAll().slideToggle('fast');
		return false;
	};

	function remove_focus() {
		// Use the blur() method to remove focus.
		$(this).blur();
	};

	function resize() {
		if ($(window).width() < 1024) {

			// Set toggle class to each #sidebar h2
			$("#sidebar div div h2").addClass('toggle');

			// Hide all h2.toggle siblings
			$('#sidebar div div h2').nextAll().hide();

			// Add a link to each h2.toggle element.
			$('h2.toggle').each(add_link);

			// Add a click event handler to all h2.toggle elements.
			// N'AURAIT JAMAIS DU ETRE ICI

			/*
			// Remove the focus from the link tag when accessed with a mouse.
			$('h2.toggle a').mouseup(remove_focus);
			*/
		}
		lastsize = $(window).width();
	}

	function chainevide(chaine) {
		// IE le plus con de tous les navigateurs
		return (chaine==='') || (chaine=='null');
	};

	function insererlibjs(url) {
		script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = url;
		document.getElementsByTagName('head')[0].appendChild(script);
	};

	function setCookie () {
		var name = c_name.value;
		var mail = c_mail.value;
		var site = c_site.value;
		var cpath = $('link[rel="top"]').attr('href');
		cpath = (!cpath) ? '/' : cpath.replace(/.*:\/\/[^\/]*([^?]*).*/g,"$1");
		var rec = name+'\n'+mail+'\n'+site;
		has_storage
			? localStorage.setItem('comment_info',rec)
			: $.cookie('comment_info',rec , {expires:60,path:cpath});
	}

	function dropCookie(){
		has_storage
			? localStorage.removeItem('comment_info')
			: $.cookie('comment_info','',{expires:-30,path:'/'});
	}

	function readCookie(c){
		if (!c) {
			return false;
		}
		var s=c.split('\n');
		if (s.length!==3) {
			dropCookie();
			return false;
		}
		return s;
	}

	// utile pour rebasculer rapidement en version draft
	var baseurl = 'http://dascritch.net' ;
	// tu t'es vu quand tu me frame ?
	if (top.location != self.location) {
		top.location = self.location.href;
	} //naaaaaaaaaaaaan mais !

	var lastsize ;

	var kkeys = [], konami = "38,38,40,40,37,39,37,39,66,65";

	$(function() {
		function onFloatLabelChangeInput() {
			var $p = $(this).closest(tagIfEmpty);
			if (this.value!=='') {
				// petite obligation car impossible de faire un sélecteur qui
				// change dynamiquement si le champ n'est pas vide
				// DOC http://stackoverflow.com/questions/8639282/notempty-css-selector-is-not-working

				$p.addClass(markedNotEmpty);
			} else {
				$p.removeClass(markedNotEmpty);
			}

			$p.closest(tagIfModified).addClass(markedModified);
		}




		if (window.addEventListener) {
			window.addEventListener('resize', resize);
		}
		resize();

		if ($('.post-content')) {
			var titres = document.getElementsByTagName('h3');
			var untags = /<[^<>]+>/g;
			if (titres.length>3) {
				var liste = document.createElement('ul');
				for (titre = 0 ; titre < titres.length ; titre++) {
					var cet_h3 = titres[titre];
					// construction de l'id
					var id = titres[titre].id;
					if (chainevide(id)) {
						id = titres[titre].id = 'chap-'+titre;
					}
					// ajout dans la liste
					var li = document.createElement('li');
					liste.appendChild(li);
					var li_a = document.createElement('a');
					var node= document.all ? cet_h3.innerText : cet_h3.textContent;
					if (!node) {
						node = cet_h3.innerHTML.replace(untags,'');
					}
					li_a.appendChild(document.createTextNode(node));
					li.appendChild(li_a);
					li_a.href = '#'+id;
					// mise en place d'une ancre
					var ancre = document.createElement('a');
					ancre.href = li_a.href;
					ancre.className = 'h3-ancre';
					ancre.appendChild(document.createTextNode('#◂'));
					cet_h3.insertBefore(ancre,cet_h3.firstChild);
				}

				// maintenant, je construit le mini-sommaire
				var $dd= $('<dd>',{
							'id' : 'minisom_m',
							'class' : 'masquee'
						}).append(liste);
				$dt = $('<dt>',{
							'id':'minisom',
							'class':'masqueur'
						}).html('Sommaire de ce billet');
				$('<dl>').append($dt).append($dd).appendTo('.post-excerpt')
			}
		}

		$(document).on('click','.masqueur',function() {
			$('#'+(this.id)+'_m').toggle();
		});


		$('.masqueur').each(function() {
			this.title='cliquez pour afficher ou cacher::';
			this.style.cursor='pointer'; // ou row-resize
			if ($(document.getElementById(this.id+'_m')).hasClass('masquee')) {
				$('#'+(this.id)+'_m').hide();
			};
		});

		// insecticide
		$('[href^="mailto:xav"]').each(function() {
			this.href = this.href.replace(/^(\w+\:\w+)(\W+)(\w+)(\W+)(\w+)(\W+)(\w+)(\+.*)(\?.*)$/,'$1$7$8@$5.$3$9');
		});

		/* spécifique float label */

	    var tagIfEmpty = '.field';
		var markedNotEmpty = 'notEmpty';
		var tagIfModified = 'form';
		var markedModified = 'modified';

		$(tagIfModified).on('change input',tagIfEmpty+' :input',onFloatLabelChangeInput);

		/*  ré-écriture du post.js système remember de dotclear */

		var $commentform = $('#comment-form');
		if ($commentform.length !== 0) {
			var c_name = document.getElementById('c_name');
			var c_mail = document.getElementById('c_mail');
			var c_site = document.getElementById('c_site');
			var c_s = '#c_name, #c_mail, #c_site';
			var has_storage = ("localStorage" in window);

			var $latestp = $('button[name="preview"]',$commentform).closest('p');
	    	// c'est crade mais c'est contre le confiturage de commentaires
	    	$('#preview').removeAttr('name').removeProp('name').
	      		removeAttr('value').removeProp('value').
	    	  	html($('#preview').data('reel'));

			var post_remember_str = $latestp.data('remember');
			$latestp.append('<input type="checkbox" id="c_remember" name="c_remember" /> '
							+'<label for="c_remember">'
								+post_remember_str
							+'</label>');
			var remember = document.getElementById('c_remember');

			var cookie = readCookie(
							has_storage
							? localStorage.getItem('comment_info')
							: $.cookie('comment_info')
							);
			if (cookie !== false) {
		    	c_name.value = cookie[0];
		    	c_mail.value = cookie[1];
		    	c_site.value = cookie[2];
		    	remember.checked = true;
				$(c_s).closest(tagIfEmpty).addClass(markedNotEmpty);
		    }
			$(remember).on('click',function(){
				if (this.checked) {
					setCookie();
				} else {
					dropCookie();
				}
			}
			);
			$commentform.on('change',c_s,function() {
				if (remember && remember.checked) {
					setCookie();
				}
			});
		}


		$(document).on('keydown',function(e) {
			// pour faire vite, http://css-tricks.com/snippets/jquery/konomi-code/
			kkeys.push( e.keyCode );
			if ( kkeys.toString().indexOf( konami ) >= 0 ) {
			// do something awesome
				alert('Désolé, pas de Konami code');
				kkeys = [];
			}
		});

		if ($('#q')) {
			$(window).on('hashchange',function(){
				if ( (location.hash === '#search') || (location.hash === '#q')) {
					$('#q').focus();
				}
			});
		}

		// déplacement du revezaler buggé
		$(document).on('click','h2.toggle',toggle);

	});

})(jQuery,document);
