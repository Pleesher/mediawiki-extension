<?php
class Pleesher_MarkNotificationsReadAction extends ApiBase
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), ['event_ids' => []]);
	}

	public function execute()
	{
		$event_ids = explode('|', $this->getParameter('event_ids'));
		// FIXME: check result/error
		PleesherExtension::$pleesher->markNotificationsRead($this->getUser()->getId(), $event_ids);

		$this->getResult()->addValue(null, 'success', 1);

		return true;
	}
}
