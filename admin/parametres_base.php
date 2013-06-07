<?php

/**
 * Edition des paramètres de base
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');
include(PLX_CORE.'lib/class.plx.timezones.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_base.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_CONFIG_BASE_CONFIG_TITLE ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) # Hook Plugins ?>

<form class="pure-form pure-form-aligned" action="parametres_base.php" method="post" id="form_settings">
	<fieldset class="config">
	<div class="pure-control-group">
		<p class="field"><label for="id_title"><?php echo L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label>
		<?php plxUtils::printInput('title', plxUtils::strCheck($plxAdmin->aConf['title'])); ?></p>
		<p class="field"><label for="id_description"><?php echo L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label>
		<?php plxUtils::printInput('description', plxUtils::strCheck($plxAdmin->aConf['description'])); ?></p>
		<p class="field"><label for="id_racine"><?php echo L_CONFIG_BASE_SITE_URL ?>&nbsp;:</label>
		<?php plxUtils::printInput('racine', $plxAdmin->racine);?>
		<a class="help" title="<?php echo L_CONFIG_BASE_URL_HELP ?>">&nbsp;</a></p>
		<p class="field"><label for="id_meta_description"><?php echo L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label>
		<?php plxUtils::printInput('meta_description', plxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?></p>
		<p class="field"><label for="id_meta_keywords"><?php echo L_CONFIG_META_KEYWORDS ?>&nbsp;:</label>
		<?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?></p>
		<p class="field"><label for="id_default_lang"><?php echo L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label>
		<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?></p>
		<p class="field"><label for="id_timezone"><?php echo L_CONFIG_BASE_TIMEZONE ?>&nbsp;:</label>
		<?php plxUtils::printSelect('timezone', plxTimezones::timezones(), $plxAdmin->aConf['timezone']); ?></p>
		<p class="field"><label for="id_allow_com"><?php echo L_CONFIG_BASE_ALLOW_COMMENTS ?>&nbsp;:</label>
		<?php plxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['allow_com']); ?></p>
		<p class="field"><label for="id_mod_com"><?php echo L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label>
		<?php plxUtils::printSelect('mod_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_com']); ?></p>
		<p class="field"><label for="id_mod_art"><?php echo L_CONFIG_BASE_MODERATE_ARTICLES ?>&nbsp;:</label>
		<?php plxUtils::printSelect('mod_art',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_art']); ?></p>
	</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" value="<?php echo L_CONFIG_BASE_UPDATE ?>" />
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>