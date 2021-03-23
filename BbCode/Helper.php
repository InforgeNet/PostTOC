<?php

namespace Inforge\PostTOC\BbCode;

class Helper
{
	private static function getContent($tagChildren, $removeBbCode = false)
	{
		$content = '';
		foreach ($tagChildren as $child)
			if (is_string($child))
				$content .= $child;
			else if (is_array($child))
				$content .= ($removeBbCode ? '' : $child['original'][0])
					. self::getContent($child['children'], $removeBbCode)
					. ($removeBbCode ? '' : $child['original'][1]);
		return $content;
	}

	private static function getTextContent($tagChildren)
	{
		return getContent($tagChildren, true);
	}

	private static function getAnchorId($uniqueId, $tagChildren)
	{
		$text = getTextContent($tagChildren);
		$text = preg_replace('/[\s+]/', '-', $text);
		$text = preg_replace('/[^A-Za-z0-9\-]/', '', $text);
		$text = substr($text, 0, 30);
		return $uniqueId . '-' . $text;
	}

	public static function renderChapterTag($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		\XF::dump($tagChildren);
		\XF::dump($tagOption);
		\XF::dump($tag);
		\XF::dump($options);
		return "";
	}

	public static function renderSectionTag($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return null;
	}

	public static function renderSubsectionTag($tagChildren, $tagOption,
		$tag, array $options,
		\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return null;
	}

	public static function renderSubsubsectionTag($tagChildren, $tagOption,
		$tag, array $options,
		\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return null;
	}

	public static function renderParagraph($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return null;
	}
}
