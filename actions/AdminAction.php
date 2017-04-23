<?php
abstract class Pleesher_AdminAction extends Pleesher_Action
{
	public function userCanExecute(User $user)
	{
		return parent::userCanExecute($user) && $user->isAllowed(PleesherExtension::ADMIN_RIGHT);
	}
}