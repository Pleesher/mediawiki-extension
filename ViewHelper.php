<?php
class Pleesher_ViewHelper
{
	public function pageUrl($page_name, $absolute = false)
	{
		$title = Title::newFromText($page_name);

		return $absolute ? $title->getFullURL() : $title->getLocalURL();
	}

	public function actionUrl($name, array $params = [])
	{
		return $GLOBALS['wgScriptPath'] . '/api.php?' . http_build_query(array_merge(['action' => $name], $params));
	}

	public function text($key, array $params = [])
	{
		return wfMessage($key)->params($params)->inContentLanguage()->escaped();
	}
}
