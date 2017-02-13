<?php
// FIXME: rename to something that doesn't include "view"--it's used outside too
class Pleesher_ViewHelper
{
	protected $implementation_i18n_prefix;

	public function __construct($implementation_i18n_prefix)
	{
		$this->implementation_i18n_prefix = $implementation_i18n_prefix;
	}

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

	public function dynPrefix()
	{
		return $this->implementation_i18n_prefix;
	}
}
