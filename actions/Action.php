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

		try {
			$this->doExecute();
		} catch (\Exception $e) {
			$this->getResult()->addValue(null, 'success', 0);
			$this->getResult()->addValue('error', 'code', $e->getCode());
			$this->getResult()->addValue('error', 'message', $e->getMessage());
		}
	}

	protected abstract function doExecute();
}
