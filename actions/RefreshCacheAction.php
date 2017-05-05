<?php
class Pleesher_RefreshCacheAction extends Pleesher_AdminAction
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_name' => null,
			'refresh' => null
		]);
	}

	protected function doExecute()
	{
		// FIXME: handle non-global cache refresh
		// FIXME: check parameters
		$user_name = $this->getParameter('user_name');
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
				$keys = null;
				break;
			default:
				$this->getResult()->addValue(null, 'success', 0);
				return;
		}

		if (isset($user_name))
			PleesherExtension::$pleesher->refreshCache($user_name, $keys);
		else
			PleesherExtension::$pleesher->refreshCacheGlobally($keys);

		if (is_null($keys) || in_array('goal_relative_to_user', $keys))
		{
			foreach (PleesherExtension::getUsers() as $user)
				PleesherExtension::$pleesher->getAchievements($user->getId());
		}

		$this->getResult()->addValue(null, 'success', 1);
	}
}
