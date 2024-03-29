<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPrepend'));

# Suppression des articles selectionnes
if(isset($_POST['selection']) AND ($_POST['selection'][0] == 'delete' OR $_POST['selection'][1] == 'delete') AND isset($_POST['idArt'])) {
	foreach ($_POST['idArt'] as $k => $v) $plxAdmin->delArticle($v);
	header('Location: index.php');
	exit;
}

# Récuperation de l'id de l'utilisateur
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);

# Récuperation des paramètres
if(!empty($_GET['sel']) AND in_array($_GET['sel'], array('all','published', 'draft','mod'))) {
	$_SESSION['sel_get']=plxUtils::nullbyteRemove($_GET['sel']);
	$_SESSION['sel_cat']='';
}
else
	$_SESSION['sel_get']=(isset($_SESSION['sel_get']) AND !empty($_SESSION['sel_get']))?$_SESSION['sel_get']:'all';

if(!empty($_POST['sel_cat']))
	if(isset($_SESSION['sel_cat']) AND $_SESSION['sel_cat']==$_POST['sel_cat']) # annulation du filtre
		$_SESSION['sel_cat']='all';
	else # prise en compte du filtre
		$_SESSION['sel_cat']=$_POST['sel_cat'];
else
	$_SESSION['sel_cat']=(isset($_SESSION['sel_cat']) AND !empty($_SESSION['sel_cat']))?$_SESSION['sel_cat']:'all';

# Recherche du motif de sélection des articles en fonction des paramètres
$catIdSel = '';
$mod='';
switch ($_SESSION['sel_get']) {
case 'published':
	$catIdSel = '[home|0-9,]*FILTER[home|0-9,]*';
	$mod='';
	break;
case 'draft':
	$catIdSel = '[home|0-9,]*draft,FILTER[home|0-9,]*';
	$mod='_?';
	break;
case 'all':
	$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
	$mod='_?';
	break;
case 'mod':
	$catIdSel = '[home|draft|0-9,]*FILTER[draft|home|0-9,]*';
	$mod='_';
	break;
}

switch ($_SESSION['sel_cat']) {
case 'all' :
	$catIdSel = str_replace('FILTER', '', $catIdSel); break;
case '000' :
	$catIdSel = str_replace('FILTER', '000', $catIdSel); break;
case 'home':
	$catIdSel = str_replace('FILTER', 'home', $catIdSel); break;
case preg_match('/^[0-9]{3}$/', $_SESSION['sel_cat'])==1:
	$catIdSel = str_replace('FILTER', $_SESSION['sel_cat'], $catIdSel);
}

# Nombre d'article sélectionnés
$nbArtPagination = $plxAdmin->nbArticles($catIdSel, $userId);

# Récuperation du texte à rechercher
$_GET['artTitle'] = (!empty($_GET['artTitle']))?plxUtils::unSlash(trim(urldecode($_GET['artTitle']))):'';

# On génère notre motif de recherche
$motif = '/^'.$mod.'[0-9]{4}.'.$catIdSel.'.'.$userId.'.[0-9]{12}.(.*)'.plxUtils::title2filename($_GET['artTitle']).'(.*).xml$/';

# Calcul du nombre de page si on fait une recherche
if($_GET['artTitle']!='') {
	if($arts = $plxAdmin->plxGlob_arts->query($motif))
		$nbArtPagination = sizeof($arts);
}

# Traitement
$plxAdmin->prechauffage($motif);
$plxAdmin->getPage();
$arts = $plxAdmin->getArticles('all'); # Recuperation des articles

# Génération de notre tableau des catégories
$aFilterCat['all'] = L_ARTICLES_ALL_CATEGORIES;
$aFilterCat['home'] = L_CATEGORY_HOME;
$aFilterCat['000'] = L_UNCLASSIFIED;
if($plxAdmin->aCats) {
	foreach($plxAdmin->aCats as $k=>$v) {
		$aCat[$k] = plxUtils::strCheck($v['name']);
		$aFilterCat[$k] = plxUtils::strCheck($v['name']);
	}
	$aAllCat[L_CATEGORIES_TABLE] = $aCat;
}
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['home'] = L_CATEGORY_HOME_PAGE;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE]['draft'] = L_DRAFT;
$aAllCat[L_SPECIFIC_CATEGORIES_TABLE][''] = L_ALL_ARTICLES_CATEGORIES_TABLE;

# On inclut le header
include(dirname(__FILE__).'/top.php');

?>

<?php eval($plxAdmin->plxPlugins->callHook('AdminIndexTop')) # Hook Plugins ?>

<h2><?php echo L_ARTICLES_LIST ?></h2>

<div class="quicksearch">
	<form action="index.php?page=1" method="get" id="form_filter" class="pure-form">
	<p>
		<?php plxUtils::printInput('page',1,'hidden'); ?>
		<input type="text" name="artTitle" value="<?php echo plxUtils::strCheck($_GET['artTitle']) ?>" />
		<input class="button submit<?php echo (!empty($_GET['artTitle'])?' select':'') ?>" type="submit" value="<?php echo L_ARTICLES_SEARCH_BUTTON ?>" />
	</p>
	</form>
</div>

<form action="index.php" method="post" id="form_articles" class="pure-form">

<p class="breadcrumbs">
	<a class="pure-button pure-button-primary" <?php echo ($_SESSION['sel_get']=='all')?'class="selected" ':'' ?>href="index.php?sel=all&amp;page=1"><?php echo L_ALL ?>&nbsp;(<?php echo $plxAdmin->nbArticles('all', $userId) ?>)</a>
	<a class="pure-button pure-button-primary" <?php echo ($_SESSION['sel_get']=='published')?'class="selected" ':'' ?>href="index.php?sel=published&amp;page=1"><?php echo L_ALL_PUBLISHED ?>&nbsp;(<?php echo $plxAdmin->nbArticles('published', $userId, '') ?>)</a>
	<a class="pure-button pure-button-primary" <?php echo ($_SESSION['sel_get']=='draft')?'class="selected" ':'' ?>href="index.php?sel=draft&amp;page=1"><?php echo L_ALL_DRAFTS ?>&nbsp;(<?php echo $plxAdmin->nbArticles('draft', $userId) ?>)</a>
	<a class="pure-button pure-button-primary" <?php echo ($_SESSION['sel_get']=='mod')?'class="selected" ':'' ?>href="index.php?sel=mod&amp;page=1"><?php echo L_ALL_AWAITING_MODERATION ?>&nbsp;(<?php echo $plxAdmin->nbArticles('all', $userId, '_') ?>)</a>
</p>
<?php
if($_SESSION['profil']<=PROFIL_MODERATOR) {
	echo '<p>';
	plxUtils::printSelect('selection[]', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, '', false);
	echo '<input class="button submit" type="submit" name="submit" value="'.L_OK.'" />';
	echo '</p>';
}
?>

<table class="pure-table">
<thead>
	<tr>
		<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>
		<th class="date"><?php echo L_ARTICLE_LIST_DATE ?></th>
		<th class="title"><?php echo L_ARTICLE_LIST_TITLE ?></th>
		<th class="category">
			<?php plxUtils::printSelect('sel_cat', $aFilterCat, $_SESSION['sel_cat']) ?>
			<input class="button submit<?php echo $_SESSION['sel_cat']!='all'?' select':'' ?>" type="submit" name="submit" value="<?php echo L_ARTICLES_FILTER_BUTTON ?>" />
		</th>
		<th class="nbcoms"><?php echo L_ARTICLE_LIST_NBCOMS ?></th>
		<th class="author"><?php echo L_ARTICLE_LIST_AUTHOR ?></th>
		<th class="action"><?php echo L_ARTICLE_LIST_ACTION ?></th>
	</tr>
</thead>
<tbody>
<?php
# On va lister les articles
if($arts) { # On a des articles
	# Initialisation de l'ordre
	$num=0;
	$datetime = date('YmdHi');
	while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
		$author = plxUtils::getValue($plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name']);
		$publi = (boolean)($plxAdmin->plxRecord_arts->f('date') > $datetime);
		# Catégories : liste des libellés de toutes les categories
		$draft='';
		$libCats='';
		$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
		if(sizeof($catIds)>0) {
			$catsName = array();
			foreach($catIds as $catId) {
				if($catId=='home') $catsName[] = L_CATEGORY_HOME;
				elseif($catId=='draft') $draft= ' <strong>['.L_CATEGORY_DRAFT.']</strong>';
				elseif(!isset($plxAdmin->aCats[$catId])) $catsName[] = L_UNCLASSIFIED;
				else $catsName[] = plxUtils::strCheck($plxAdmin->aCats[$catId]['name']);
			}
			if(sizeof($catsName)>0) {
				$libCats = $catsName[0];
				unset($catsName[0]);
				if(sizeof($catsName)>0) $libCats .= ', ... <a class="pure-button" class="help" title="'.implode(',', $catsName).'">&nbsp;</a>';
			}
			else $libCats = L_UNCLASSIFIED;
		}
		# en attente de validation ?
		$idArt = $plxAdmin->plxRecord_arts->f('numero');
		$awaiting = $idArt[0]=='_' ? ' - <strong>'.L_AWAITING.'</strong>' : '';
		# Commentaires
		$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$idArt.'.(.*).xml$/','all');
		$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$idArt.'.(.*).xml$/','all');
		# On affiche la ligne
		echo '<tr class="line-'.(++$num%2).'">';
		echo '<td><input type="checkbox" name="idArt[]" value="'.$idArt.'" /></td>';
		echo '<td>'.plxDate::formatDate($plxAdmin->plxRecord_arts->f('date')).'&nbsp;</td>';
		echo '<td><a class="pure-button" href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.plxUtils::strCheck(plxUtils::strCut($plxAdmin->plxRecord_arts->f('title'),60)).'&nbsp;'.$draft.'</a>'.$awaiting.'</td>';
		echo '<td>'.$libCats.'&nbsp;</td>';
		echo '<td><a class="pure-button" title="'.L_NEW_COMMENTS_TITLE.'" href="comments.php?sel=offline&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsToValidate.'</a> / <a class="pure-button" title="'.L_VALIDATED_COMMENTS_TITLE.'" href="comments.php?sel=online&amp;a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsValidated.'</a>&nbsp;</td>';
		echo '<td class="author">'.plxUtils::strCheck($author).'&nbsp;</td>';
		echo '<td class="action">';
		echo '<a class="pure-button" href="article.php?a='.$idArt.'" title="'.L_ARTICLE_EDIT_TITLE.'">'.L_ARTICLE_EDIT.'</a>';
		if($publi AND $draft=='') # Si l'article est publié
			echo ' <a class="pure-button" href="'.PLX_ROOT.'?article'.intval($idArt).'/'.$plxAdmin->plxRecord_arts->f('url').'" title="'.L_ARTICLE_VIEW_TITLE.'">'.L_ARTICLE_VIEW.'</a>';
		echo "&nbsp;</td>";
		echo "</tr>";
	}
} else { # Pas d'article
	echo '<tr><td colspan="7" class="center">'.L_NO_ARTICLE.'</td></tr>';
}
?>
</tbody>
</table>

<?php
	echo '<p>';
	echo plxToken::getTokenPostMethod();
	if($_SESSION['profil']<=PROFIL_MODERATOR) {
		plxUtils::printSelect('selection[]', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, '', false);
		echo '<input class="button submit" type="submit" name="submit" value="'.L_OK.'" />';
	}
	echo '</p>';
?>

</form>

<div id="pagination">
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexPagination'));
# Affichage de la pagination
if($arts) { # Si on a des articles (hors page)
	# Calcul des pages
	$last_page = ceil($nbArtPagination/$plxAdmin->bypage);
	if($plxAdmin->page > $last_page) $plxAdmin->page = $last_page;
	$prev_page = $plxAdmin->page - 1;
	$next_page = $plxAdmin->page + 1;
	# Generation des URLs
	$artTitle = (!empty($_GET['artTitle'])?'&amp;artTitle='.urlencode($_GET['artTitle']):'');
	$p_url = 'index.php?page='.$prev_page.$artTitle; # Page precedente
	$n_url = 'index.php?page='.$next_page.$artTitle; # Page suivante
	$l_url = 'index.php?page='.$last_page.$artTitle; # Derniere page
	$f_url = 'index.php?page=1'.$artTitle; # Premiere page
	# On effectue l'affichage
	if($plxAdmin->page > 2) # Si la page active > 2 on affiche un lien 1ere page
		echo '<span class="p_first"><a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'">'.L_PAGINATION_FIRST.'</a></span>';
	if($plxAdmin->page > 1) # Si la page active > 1 on affiche un lien page precedente
		echo '<span class="p_prev"><a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'">'.L_PAGINATION_PREVIOUS.'</a></span>';
	# Affichage de la page courante
	printf('<span class="p_page">'.L_PAGINATION.'</span>',$plxAdmin->page,$last_page);
	if($plxAdmin->page < $last_page) # Si la page active < derniere page on affiche un lien page suivante
		echo '<span class="p_next"><a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'">'.L_PAGINATION_NEXT.'</a></span>';
	if(($plxAdmin->page + 1) < $last_page) # Si la page active++ < derniere page on affiche un lien derniere page
		echo '<span class="p_last"><a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'">'.L_PAGINATION_LAST.'</a></span>';
}
?>
</div>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminIndexFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
