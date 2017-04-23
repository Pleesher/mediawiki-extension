<?php
abstract class Pleesher_SpecialPage extends SpecialPage
{
	public function __construct($name = '', $restriction = '', $listed = true, $function = false, $file = '', $includable = false)
	{
		parent::__construct($name, $restriction, $listed, $function, $file, $includable);

		$this->setListed(PleesherExtension::$implementation->isExtensionEnabled($this->getUser()));
	}

	public function userCanExecute(User $user)
	{
		return PleesherExtension::$implementation->isExtensionEnabled($user);
	}
}