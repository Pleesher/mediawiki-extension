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

		if (isset($_SESSION[\PleesherExtension::class]['exception']))
		{
			$e = $_SESSION[\PleesherExtension::class]['exception'];
			$error_message = \PleesherExtension::$view_helper->text('pleesher.error.text.' . ($e->getErrorCode() ?: 'generic'), $e->getErrorParameters() ?: []);
			unset($_SESSION[\PleesherExtension::class]['exception']);
		}
		else
			$error_message = \PleesherExtension::$view_helper->text('pleesher.error.text.generic');

		$html = PleesherExtension::render('error', ['error_message' => $error_message]);
		$this->getOutput()->addHTML($html);
	}
}