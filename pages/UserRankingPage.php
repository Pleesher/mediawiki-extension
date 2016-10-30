<?php
class Pleesher_UserRankingPage extends SpecialPage
{
	public function __construct()
	{
		parent::__construct('UserRanking');
	}

	public function execute($subPage)
	{
		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$user = $this->getUser();
		$user_id = $user->getId();

		$users = PleesherExtension::getUsers();

		$users = array_filter($users, function(User $user) {
			return !$user->isAnon();
		});
		uasort($users, function($user1, $user2) {
			return $user2->kudos - $user1->kudos;
		});

		$rank = 1;
		$users = array_map(function($user) use(&$rank) {
			$user->achievement_count = count(PleesherExtension::getAchievements($user->getId()));
			$user->rank = $rank++;
			return $user;
		}, $users);

		$html = PleesherExtension::render('user_ranking', [
			'users' => $users
		]);
		$this->getOutput()->addHTML($html);
	}
}