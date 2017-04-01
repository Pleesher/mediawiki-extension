<?php
class Pleesher_RefreshCacheAction extends ApiBase
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_id' => null,
			'refresh' => null
		]);
	}

	public function execute()
	{
		if (!$this->getUser()->isAllowed(PleesherExtension::ADMIN_RIGHT))
		{
			$this->getResult()->addValue(null, 'success', 0);
			return;
		}

		// FIXME: handle non-global cache refresh
		// FIXME: check parameters
		$user_id = $this->getParameter('user_id');
		$refresh_type = $this->getParameter('refresh');

		switch ($refresh_type)
		{
			case 'goals':
				$keys = ['goal'];
				break;
			case 'achievements':
				$keys = ['goal_relative_to_user'];
				break;
			case 'users':
				$keys = ['user'];
				break;
			case 'all':
			default:
				$keys = null;
		}

		if (isset($user_id))
			PleesherExtension::$pleesher->refreshCache($user_id, $keys);
		else
			PleesherExtension::$pleesher->refreshCacheGlobally($keys);

		$this->getResult()->addValue(null, 'success', 1);
	}
}
