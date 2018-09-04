<?php
class Pleesher_ErrorPage extends Pleesher_SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementsError');
		$this->setListed(false);
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		if (isset($_SESSION['PleesherExtension']['exception']) && is_array($_SESSION['PleesherExtension']['exception']) && count($_SESSION['PleesherExtension']['exception']) === 2)
		{
			list($error_code, $error_parameters) = $_SESSION['PleesherExtension']['exception'];
			$error_message = \PleesherExtension::$view_helper->text('pleesher.error.text.' . ($error_code ?: 'generic'), $error_parameters ?: []);
			unset($_SESSION['PleesherExtension']['exception']);
		}
		else
			$error_message = \PleesherExtension::$view_helper->text('pleesher.error.text.generic');

		$html = PleesherExtension::render('error', ['error_message' => $error_message]);
		$this->getOutput()->addHTML($html);
	}
}