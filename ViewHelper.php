<?php
class Pleesher_ViewHelper
{
	public function pageUrl($page_name, $absolute = false)
	{
		$title = Title::newFromText($page_name);

		return $absolute ? $title->getFullURL() : $title->getLocalURL();
	}

	public function text($key, array $params = [])
	{
		return wfMessage($key)->params($params)->inContentLanguage()->escaped();
	}
}
