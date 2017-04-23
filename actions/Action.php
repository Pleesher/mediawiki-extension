<?php
abstract class Pleesher_Action extends ApiBase
{
	public function userCanExecute(User $user)
	{
		return PleesherExtension::$implementation->isExtensionEnabled($user);
	}

	public final function execute()
	{
		if (!$this->userCanExecute($this->getUser()))
		{
			$this->getResult()->addValue(null, 'success', 0);
			return;
		}

		return $this->doExecute();
	}

	protected abstract function doExecute();
}