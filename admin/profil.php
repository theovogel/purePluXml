<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilPrepend'));

# On édite la configuration
if(!empty($_POST)) {

	if(!empty($_POST['profil']))
		$plxAdmin->editProfil($_POST);
	elseif(!empty($_POST['password']))
		$plxAdmin->editPassword($_POST);

	header('Location: profil.php');
	exit;

}

# On inclut le header
include(dirname(__FILE__).'/top.php');

$_profil = $plxAdmin->aUsers[$_SESSION['user']];
?>

<h2><?php echo L_PROFIL_EDIT_TITLE ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminProfilTop')) # Hook Plugins ?>

<form class="pure-form pure-form-aligned" action="profil.php" method="post" id="form_profil">
	<fieldset class="withlabel">
    <div class="pure-control-group">
		<p class="field"><label><?php echo L_PROFIL_LOGIN ?>&nbsp;:</label>&nbsp;<strong><?php echo plxUtils::strCheck($_profil['login']) ?></strong></p>
		<p class="field"><label for="id_name"><?php echo L_PROFIL_USER ?>&nbsp;:</label>
		<?php plxUtils::printInput('name', plxUtils::strCheck($_profil['name']), 'text', '20-255') ?></p>
		<p class="field"><label for="id_email"><?php echo L_PROFIL_MAIL ?>&nbsp;:</label>
		<?php plxUtils::printInput('email', plxUtils::strCheck($_profil['email']), 'text', '30-255') ?></p>
		<p class="field"><label for="id_lang"><?php echo L_PROFIL_ADMIN_LANG ?>&nbsp;:</label>
		<?php plxUtils::printSelect('lang', plxUtils::getLangs(), $_profil['lang']) ?></p>
		<p id="p_content"><label for="id_content"><?php echo L_PROFIL_INFOS ?>&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($_profil['infos']),140,5); ?>
	</div>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminProfil')) # Hook Plugins ?>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" name="profil" value="<?php echo L_PROFIL_UPDATE ?>" />
	</p>
	<p>&nbsp;</p>
</form>

<h2><?php echo L_PROFIL_CHANGE_PASSWORD ?></h2>
<form class="pure-form pure-form-aligned" action="profil.php" method="post" id="form_password">
	<fieldset class="withlabel">
    <div class="pure-control-group">
		<p><label for="id_password1"><?php echo L_PROFIL_PASSWORD ?>&nbsp;:</label>
		<?php plxUtils::printInput('password1', '', 'password', '20-255') ?></p>
		<p><label for="id_password2"><?php echo L_PROFIL_CONFIRM_PASSWORD ?>&nbsp;:</label>
		<?php plxUtils::printInput('password2', '', 'password', '20-255') ?></p>
	</div>
	</fieldset>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" name="password" value="<?php echo L_PROFIL_UPDATE_PASSWORD ?>" />
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminProfilFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>