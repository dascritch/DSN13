<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
# This file is part of Ductile, a theme for Dotclear
#
# Copyright (c) 2011 - Association Dotclear
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

# Behaviors
$core->addBehavior('publicInsideFooter',array('tplDuctileTheme','publicInsideFooter'));

# Templates
$core->tpl->addValue('ductileEntriesList',array('tplDuctileTheme','ductileEntriesList'));
$core->tpl->addBlock('EntryIfContentIsCut',array('tplDuctileTheme','EntryIfContentIsCut'));
$core->tpl->addValue('ductileNbEntryPerPage',array('tplDuctileTheme','ductileNbEntryPerPage'));
$core->tpl->addBlock('IfPreviewIsNotMandatory',array('tplDuctileTheme','IfPreviewIsNotMandatory'));

# DaScritchNet micro adressage

$core->url->register('shortlink', 'm', '^m(?:/(.+))?$', ['urlDaScritch', 'shortlink']);

# DaScritchNet templates

$core->tpl->addValue('TEMPEntryURL',			['DSN_tpl','TEMPEntryURL']);
$core->tpl->addValue('TEMPEntryTitle',          ['DSN_tpl','TEMPEntryTitle']);

$core->tpl->addValue('UrlDate',					['DSN_tpl','UrlDate']);
$core->tpl->addValue('EntryDateHumaine',		['DSN_tpl','EntryDateHumaine']);
$core->tpl->addValue('CommentsTBCount',			['DSN_tpl','CommentsTBCount']);
$core->tpl->addBlock('AuthorNotXavier',			['DSN_tpl','AuthorNotXavier']);
$core->tpl->addBlock('FrontPage',				['DSN_tpl','FrontPage']);
$core->tpl->addValue('OggFile',					['DSN_tpl','OggFile']);


function datehumaine($date,$format_norm='%l %j%S %F %Y',$format_ya7j='%l dernier',$format_hier='hier',$format_jour="aujourd'hui",$Initiale=false) {

	// cette fonction permet d'afficher une date plus humaine dans un monde si informatisé
	// a regler en fonction du creneau horaire de votre serveur

	// à noter que dans dotclear2, la fonction à patcher est /clearbricks/common/lib.date.php :: str()

	// NOTE IMPORTANTE : ce code a été totalement ré-écrit dans dAgence en 2007 puis ré-écrit encore en TDD en 2011
	// donc avec moins de bugs (dates futures), internationalisation et meilleure lisibilité
	// la version ci-dessous date de 2004, c'est NORMAL qu'il soit CRÂDE

	// * l
	$days = [ 0 => 'dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi' ];
	// * D
	$dayc = [ 0 => 'dim','lun','mar','mer','jeu','ven','sam' ];
	// * F
	$mois = [ 1 => 'janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre' ];
	// * M
	$moic = [ 1 => 'jan','fév','mar','avr','mai','jun','jul','aoû','sep','oct','nov','déc' ];

	/// Oui, je sais c'est CRADE
	$decal_tz = 60 * 60 * 7;
	$t_aujo = strtotime('now') - $decal_tz;
	$t_hier = strtotime('-1 day', $t_aujo);
	$t_ya7j = strtotime('-7 days', $t_aujo);

	$format = '!!!erreur';
	if ($date < $t_aujo) {
		if ($date < $t_hier) {
			if ($date < $t_ya7j) {
				$format = $format_norm;
			} else {
				$format = $format_ya7j;
			}
		} else {
			$format = $format_hier;
		}
	} else {
		$format = $format_jour;
	}

	foreach(['d','j','N','w','z','W','m','n','t','L','o','Y','y','a','A','B','g','G','h','H','i','s','e','I','O','P','T','Z','c','r','U'] as $parsePHPdate)
	{
		if (strpos($format, '%'.$parsePHPdate)) {
			$format = str_replace('%'.$parsePHPdate, date($parsePHPdate,$date), $format);
		}
	}

	$format=str_replace('%l', $days[date('w', $date)], $format);
	$format=str_replace('%D', $dayc[date('w', $date)], $format);
	$format=str_replace('%S', date('j',$date)=='1'?'er':'', $format);
	$format=str_replace('%F', $mois[date('n', $date)], $format);
	$format=str_replace('%M', $moic[date('n', $date)], $format);
	$sortie=$format;
	if ($Initiale) $sortie=ucfirst($sortie);
	return $sortie;
}


class tplDuctileTheme
{
	public static function ductileNbEntryPerPage($attr) {
		return '<?php tplDuctileTheme::ductileNbEntryPerPageHelper(); ?>';
	}

	public static function ductileNbEntryPerPageHelper() {
		global $_ctx;

		$nb = 0;
		$s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme.'_entries_counts');
		if ($s !== null) {
			$s = @unserialize($s);
			if (is_array($s)) {
				if (isset($s[$GLOBALS['core']->url->type])) {
					// Nb de billets par page défini par la config du thème
					$nb = (integer) $s[$GLOBALS['core']->url->type];
				} else {
					if (($GLOBALS['core']->url->type == 'default-page') && (isset($s['default']))) {
						// Les pages 2 et suivantes de la home ont le même nombre de billet que la première page
						$nb = (integer) $s['default'];
					}
				}
			}
		}

		if ($nb == 0) {
			if (!empty($attr['nb'])) {
				// Nb de billets par page défini par défaut dans le template
				$nb = (integer) $attr['nb'];
			}
		}

		if ($nb > 0)
			$_ctx->nb_entry_per_page = $nb;
	}

	public static function EntryIfContentIsCut($attr,$content) {
		global $core;

		if (empty($attr['cut_string']) || !empty($attr['full'])) {
			return '';
		}

		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}

		$short = $core->tpl->getFilters($attr);
		$cut = $attr['cut_string'];
		$attr['cut_string'] = 0;
		$full = $core->tpl->getFilters($attr);
		$attr['cut_string'] = $cut;

		return '<?php if (strlen('.sprintf($full,'$_ctx->posts->getContent('.$urls.')').') > '.
			'strlen('.sprintf($short,'$_ctx->posts->getContent('.$urls.')').')) : ?>'.
			$content.
			'<?php endif; ?>';
	}

	public static function ductileEntriesList($attr) {
		global $core;

		$tpl_path = dirname(__FILE__).'/tpl/';
		$list_types = array('title','short','full');

		// Get all _entry-*.html in tpl folder of theme
		$list_types_templates = files::scandir($tpl_path);
		if (is_array($list_types_templates)) {
			foreach ($list_types_templates as $v) {
				if (preg_match('/^_entry\-(.*)\.html$/',$v,$m)) {
					if (isset($m[1])) {
						if (!in_array($m[1],$list_types)) {
							// template not already in full list
							$list_types[] = $m[1];
						}
					}
				}
			}
		}

		$default = isset($attr['default']) ? trim($attr['default']) : 'short';
		$ret = '<?php '."\n".
			'switch (tplDuctileTheme::ductileEntriesListHelper(\''.$default.'\')) {'."\n";

		foreach ($list_types as $v) {
			$ret .= '	case \''.$v.'\':'."\n".
				'?>'."\n".
						$core->tpl->includeFile(array('src' => '_entry-'.$v.'.html'))."\n".
				'<?php '."\n".
				'		break;'."\n";
		}

		$ret .= '}'."\n".
			'?>';

		return $ret;
	}

	public static function ductileEntriesListHelper($default) {
		$s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme.'_entries_lists');
		if ($s !== null) {
			$s = @unserialize($s);
			if (is_array($s)) {
				if (isset($s[$GLOBALS['core']->url->type])) {
					$model = $s[$GLOBALS['core']->url->type];
					return $model;
				}
			}
		}
		return $default;
	}

	public static function IfPreviewIsNotMandatory($attr,$content) {
		$s = $GLOBALS['core']->blog->settings->themes->get($GLOBALS['core']->blog->settings->system->theme.'_style');
		if ($s !== null) {
			$s = @unserialize($s);
			if (is_array($s)) {
				if (isset($s['preview_not_mandatory'])) {
					if ($s['preview_not_mandatory']) {
						return $content;
					}
				}
			}
		}
		return '';
	}

	public static function publicInsideFooter($core) {
		$res = '';
		$default = false;
		$img_url = $core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme.'/img/';

		$s = $core->blog->settings->themes->get($core->blog->settings->system->theme.'_stickers');

		if ($s === null) {
			$default = true;
		} else {
			$s = @unserialize($s);
			if (!is_array($s)) {
				$default = true;
			} else {
				$s = array_filter($s,array('tplDuctileTheme', 'cleanStickers'));
				if (count($s) == 0) {
					$default = true;
				} else {
					$count = 1;
					foreach ($s as $sticker) {
						$res .= self::setSticker($count,($count == count($s)),$sticker['label'],$sticker['url'],$img_url.$sticker['image']);
						$count++;
					}
				}
			}
		}

		if ($default || $res == '') {
			$res = self::setSticker(1,true,__('Subscribe'),$core->blog->url.
				$core->url->getURLFor('feed','atom'),$img_url.'sticker-feed.png');
		}

		if ($res != '') {
			$res = '<ul id="stickers">'."\n".$res.'</ul>'."\n";
			echo $res;
		}
	}

	protected static function cleanStickers($s) {
		if (is_array($s)) {
			if (isset($s['label']) && isset($s['url']) && isset($s['image'])) {
				if ($s['label'] != null && $s['url'] != null && $s['image'] != null) {
					return true;
				}
			}
		}
		return false;
	}

	protected static function setSticker($position,$last,$label,$url,$image) {
		return '<li id="sticker'.$position.'"'.($last ? ' class="last"' : '').'>'."\n".
			'<a href="'.$url.'">'."\n".
			'<img alt="" src="'.$image.'" />'."\n".
			'<span>'.$label.'</span>'."\n".
			'</a>'."\n".
			'</li>'."\n";
	}

	protected static $fonts = array(
		// Theme standard
		'Ductile body' => '"Century Schoolbook", "Century Schoolbook L", Georgia, serif',
		'Ductile alternate' => '"Franklin gothic medium", "arial narrow", "DejaVu Sans Condensed", "helvetica neue", helvetica, sans-serif',

		// Serif families
		'Times New Roman' => 'Cambria, "Hoefler Text", Utopia, "Liberation Serif", "Nimbus Roman No9 L Regular", Times, "Times New Roman", serif',
		'Georgia' => 'Constantia, "Lucida Bright", Lucidabright, "Lucida Serif", Lucida, "DejaVu Serif", "Bitstream Vera Serif", "Liberation Serif", Georgia, serif',
		'Garamond' => '"Palatino Linotype", Palatino, Palladio, "URW Palladio L", "Book Antiqua", Baskerville, "Bookman Old Style", "Bitstream Charter", "Nimbus Roman No9 L", Garamond, "Apple Garamond", "ITC Garamond Narrow", "New Century Schoolbook", "Century Schoolbook", "Century Schoolbook L", Georgia, serif',

		// Sans-serif families
		'Helvetica/Arial' => 'Frutiger, "Frutiger Linotype", Univers, Calibri, "Gill Sans", "Gill Sans MT", "Myriad Pro", Myriad, "DejaVu Sans Condensed", "Liberation Sans", "Nimbus Sans L", Tahoma, Geneva, "Helvetica Neue", Helvetica, Arial, sans-serif',
		'Verdana' => 'Corbel, "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", "DejaVu Sans", "Bitstream Vera Sans", "Liberation Sans", Verdana, "Verdana Ref", sans-serif',
		'Trebuchet MS' => '"Segoe UI", Candara, "Bitstream Vera Sans", "DejaVu Sans", "Bitstream Vera Sans", "Trebuchet MS", Verdana, "Verdana Ref", sans-serif',

		// Cursive families
		'Impact' => 'Impact, Haettenschweiler, "Franklin Gothic Bold", Charcoal, "Helvetica Inserat", "Bitstream Vera Sans Bold", "Arial Black", sans-serif',

		// Monospace families
		'Monospace' => 'Consolas, "Andale Mono WT", "Andale Mono", "Lucida Console", "Lucida Sans Typewriter", "DejaVu Sans Mono", "Bitstream Vera Sans Mono", "Liberation Mono", "Nimbus Mono L", Monaco, "Courier New", Courier, monospace'
	);

	protected static function fontDef($c)
	{
		return isset(self::$fonts[$c]) ? self::$fonts[$c] : null;
	}

	protected static function prop(&$css,$selector,$prop,$value)
	{
		if ($value) {
			$css[$selector][$prop] = $value;
		}
	}
}


/* Section héritée déplacée de l'antédiluvien extension "Infomania/DaScritchNet"  **/

class urlDaScritch extends dcUrlHandlers
{

	public static function shortlink($args) {

		global $core, $_ctx;
		$post = $core->blog->getPosts([
										'post_id'		=> abs(intval(substr($_SERVER['QUERY_STRING'],2))),
										'post_type'		=> ['post' , 'page' ],
										]);

		$type = $post->post_type === 'page' ? 'pages' : 'post';
		$redirect = '/'. $type .'/'.$post->post_url;
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$redirect);
		header('Content-Type: text/html; charset=UTF-8');
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html>
				<head>
					<title>Ce document est à une autre adresse - This document is at another location</title>
					<meta http-equiv="refresh" content="0;url='.$redirect.'" />
					<link rel="top" href="/" />
					<link rel="canonical" href="'.$redirect.'" />
				</head><body>
					<p lang="fr"><a href="'.$redirect.'">Ce document est en fait à une autre adresse, canonique&nbsp;: '.$redirect.'</a></p>
					<p lang="en"><a href="'.$redirect.'">This document is actually at this canonical address&nbsp;: '.$redirect.'</a></p>
				</body>
				</html>';
		exit;
	}
}


class DSN_tpl
{
	public static function TEMPEntryURL($attr) {
		return '<?php echo urlencode($_ctx->posts->getURL()); ?>';
	}

	public static function TEMPEntryTitle($attr) {
		if (isset($attr['spaces'])) {
			return '<?php echo strtr( urlencode($_ctx->posts->post_title), ["+" => "'.$attr['spaces'].'"] ); ?>';
		} else {
			return '<?php echo urlencode($_ctx->posts->post_title); ?>';
		}
	}

	public static function UrlDate($attr) {
		return
		'<?php
			$void=$_SERVER["QUERY_STRING"];
			$complementsok = preg_match(\'#post\/([[:digit:]]{4})\/([[:digit:]]{2})\/([[:digit:]]{2})\/#\',$void ,$recupdate );
			echo "/blog/archive/$recupdate[1]/$recupdate[2]'.($attr['quand']==='jour'?'/$recupdate[3]':'').'";
		?>';
	}

	public static function EntryDateHumaine($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return
		'<?php
			echo '.sprintf($f,'datehumaine(($_ctx->posts->getTS()),\'le %l %j%S %F %Y\',\'<abbr title="%j%S %F">%l dernier</abbr>\',\'<abbr title="%l %j%S %F">hier</abbr>\',\'<abbr title="%l %j %S">aujourd\\\'hui</abbr>\')').';
		?>';
	}

	public static function CommentsTBCount($attr) {
		return
			'<?php if(($_ctx->posts->hasComments() || $_ctx->posts->commentsActive())) : ?>
				<a href="<?php echo context::global_filter($_ctx->posts->getURL(),0,0,0,0,0,\'EntryURL\'); ?>#comments" class="comment_count">
				<?php
					$nb_t=(int) ($_ctx->posts->nb_trackback);
					$nb_c=(int) ($_ctx->posts->nb_comment);
					//if ((($nb_t!=0) || ($nb_c!=0))) { echo \'<img class="favicon" src="/nav/icons/16/action-comment.png"  alt="" />\'; }
				?>
				<?php if ($_ctx->posts->nb_comment == 0) {
					// printf(__(\'no comment\'),$_ctx->posts->nb_comment);
					} elseif ($_ctx->posts->nb_comment == 1) {
					printf(__(\'one comment\'),$_ctx->posts->nb_comment);
					} else {
					printf(__(\'%d comments\'),$_ctx->posts->nb_comment);
				} ?>
				</a>
				<?php if ($nb_t) { ?>
					<?php if ($_ctx->posts->nb_comment > 0) { ?>
						et
					<?php } ?>
					<a href="<?php echo context::global_filter($_ctx->posts->getURL(),0,0,0,0,0,\'EntryURL\'); ?>#pings" class="ping_count">
						<?php if ($_ctx->posts->nb_trackback == 0) {
						printf(__(\'no trackback\'),(integer) $_ctx->posts->nb_trackback);
						} elseif ($_ctx->posts->nb_trackback == 1) {
						printf(__(\'one trackback\'),(integer) $_ctx->posts->nb_trackback);
						} else {
						printf(__(\'%d trackbacks\'),(integer) $_ctx->posts->nb_trackback);
						} ?></a><?php } ?>
			<?php endif; ?>
		';

	}

	public static function AuthorNotXavier($attr,$content) {
		return '<?php if ((($_ctx->posts->getAuthorCN())!="admin") &&(($_ctx->posts->getAuthorCN())!="Da Scritch") && (($_ctx->posts->getAuthorCN())!="Xavier Mouton-Dubosc")) { ?>'.$content.'<?php } ?>';
	}


	public static function FrontPage($attr,$content)  {
		return '<?php if ($_SERVER["QUERY_STRING"]'.(isset($attr['is'])?'!':'=').'="") { ?>'.$content.'<?php } ?>';
	}


	public static function OggFile($attr) {
		return '<?php
					$oggfile=$attach_f->file_url;
					$oggpossible=preg_replace(array("/\.mp3/","/\/podcast\//"),array(".ogg","/"),$attach_f->file_url);
					echo $oggpossible;
				?>';
	}
}


?>