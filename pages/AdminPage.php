<?php
class Pleesher_AdminPage extends Pleesher_SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementsAdmin', PleesherExtension::ADMIN_RIGHT);
	}

	function getGroupName() {
		return 'pleesher';
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$pleesher_disabled = PleesherExtension::getSettingValue('disabled', '0') == '1';
		$users = PleesherExtension::getUsers(['require_achievements_in_cache' => false]);
		uasort($users, function(User $user1, User $user2) {
			return strcmp($user1->getName(), $user2->getName());
		});

		$html = PleesherExtension::render('admin', [
			'pleesher_disabled' => $pleesher_disabled,
			'users' => $users
		]);
		$this->getOutput()->addHTML($html);
	}
}
