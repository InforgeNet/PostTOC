<?php

namespace Inforge\PostTOC\BbCode;

class TagRenderer
{
	private static int $chNum = 0;
	private static int $secNum = 0;
	private static int $subsecNum = 0;
	private static int $subsubsecNum = 0;
	private static int $parNum = 0;

	private static ?string $context = null;
	private static ?string $subContext = null;
	private static ?int $entityId = null;

	private static function getContent($tagChildren, $removeNewLines = true)
	{
		if (is_string($tagChildren))
			return $removeNewLines ?
				str_replace(["\r\n", "\n", "\r"], ' ',
					$tagChildren) : $tagChildren;
		$content = '';
		foreach ($tagChildren as $child)
			if (is_string($child))
				$content .= $removeNewLines ?
					str_replace(["\r\n", "\n", "\r"], ' ',
						$child) : $child;
			else if (is_array($child))
				$content .= self::getContent($child['children'],
					$removeNewLines);
		return $content;
	}

	private static function getAnchorId($uniqueId, $tagChildren)
	{
		$text = self::getContent($tagChildren);
		$text = preg_replace('/[\s+]/', '-', $text);
		$text = preg_replace('/[^A-Za-z0-9\-]/', '', $text);
		$text = substr($text, 0, 30);
		return $uniqueId . '-' . $text;
	}

	private static function getEntityId($entity)
	{
		if (!isset($entity)) {
			//\XF::logError('Can not get post/resource id: $entity is not defined.');
			return 0;
		}
		return $entity->getEntityId();
	}

	private static function getNumByDepth($depth, $override = -1)
	{
		// TODO: write better
		$nums = [];
		switch ($depth) {
		case 4:
			if ($override > -1)
				self::$parNum = $override;
			else
				self::$parNum++;
			$nums[] = self::$parNum;
		case 3:
			if ($depth == 3)
				if ($override > -1)
					self::$subsubsecNum = $override;
				else
					self::$subsubsecNum++;
			$nums[] = self::$subsubsecNum;
		case 2:
			if ($depth == 2)
				if ($override > -1)
					self::$subsecNum = $override;
				else
					self::$subsecNum++;
			$nums[] = self::$subsecNum;
		case 1:
			if ($depth == 1)
				if ($override > -1)
					self::$secNum = $override;
				else
					self::$secNum++;
			$nums[] = self::$secNum;
		case 0:
			if ($depth == 0)
				if ($override > -1)
					self::$chNum = $override;
				else
					self::$chNum++;
			$nums[] = self::$chNum;
			break;
		default:
			return '';
		}
		self::resetCounters($depth + 1);
		$nums = array_reverse($nums);
		$idxString = '';
		foreach ($nums as $num)
			$idxString .= ".$num";
		return ltrim($idxString, '.');

	}

	private static function renderTag($depth, $tagChildren, $tagOption,
		$entity, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		self::resetCountersIfNeeded($entity, $renderer);
		$content = self::getContent($tagChildren);
		$depth = max($depth, 0);
		$depth = min($depth, 4);
		$options = self::buildOptions($tagOption, [
				'enumerate' => $depth < 3,
				'index' => $depth < 4,
				'overrideNum' => null,
			]);
		$tag = 'h' . ($depth + 2);
		$htmlClass = 'posttoc';
		if (!empty($options['index']))
			$htmlClass .= '-index';
		$num = '';
		if (!empty($options['enumerate']))
			$num .= self::getNumByDepth($depth, $options['overrideNum'] ?? -1);
		$uniqueId = self::$entityId . '-' . str_replace('.', '-', $num);
		$anchorId = self::getAnchorId($uniqueId, $tagChildren);
		if (strlen($num) > 0)
			$num .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		return $renderer->wrapHtml(
			"<$tag class=\"$htmlClass\" id=\"$anchorId\">",
			"$num$content", "</$tag>");
	}

	private static function buildOptions($tagOption, $options = [])
	{
		$strOptions = explode(',', trim($tagOption));
		foreach ($strOptions as $opt) {
			$opt = strtolower(trim($opt));
			if (strlen($opt) == 0)
				continue;
			if ($opt == 'num')
				$options['enumerate'] = true;
			else if ($opt == 'toc')
				$options['index'] = true;
			else if ($opt == 'nonum')
				$options['enumerate'] = false;
			else if ($opt == 'notoc')
				$options['index'] = false;
			else
				$options['overrideNum'] = filter_var($opt,
					FILTER_VALIDATE_INT, [
						'options' => [
							'min_range' => 0,
						],
						'flags' => FILTER_NULL_ON_FAILURE,
					]);
		}
		if (isset($options['overrideNum']))
			$options['enumerate'] = true;
		return $options;
	}

	public static function renderChapterTag($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return self::renderTag(0, $tagChildren, $tagOption,
			$options['entity'], $renderer);
	}

	public static function renderSectionTag($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return self::renderTag(1, $tagChildren, $tagOption,
			$options['entity'], $renderer);
	}

	public static function renderSubsectionTag($tagChildren, $tagOption,
		$tag, array $options,
		\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return self::renderTag(2, $tagChildren, $tagOption,
			$options['entity'], $renderer);
	}

	public static function renderSubsubsectionTag($tagChildren, $tagOption,
		$tag, array $options,
		\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return self::renderTag(3, $tagChildren, $tagOption,
			$options['entity'], $renderer);
	}

	public static function renderParagraphTag($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return self::renderTag(4, $tagChildren, $tagOption,
			$options['entity'], $renderer);
	}

	public static function renderTocTag($tagChildren, $tagOption, $tag,
		array $options, \XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return $renderer->getTemplater()->renderTemplate(
			'public:if_toc_bb_code_tag_toc');
	}

	private static function resetCounters($depth = 0)
	{
		switch ($depth) {
		case 0:
			self::$chNum = 0;
		case 1:
			self::$secNum = 0;
		case 2:
			self::$subsecNum = 0;
		case 3:
			self::$subsubsecNum = 0;
		case 4:
			self::$parNum = 0;
		}
	}

	private static function resetCountersIfNeeded($entity,
		\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		$curId = self::getEntityId($entity);
		$curContext = self::getCurrentContext($renderer);
		$curSubContext = self::getCurrentSubContext($renderer);
		if (self::$entityId !== $curId
			|| self::$context !== $curContext
			|| self::$subContext !== $curSubContext) {
			self::$entityId = $curId;
			self::$context = $curContext;
			self::$subContext = $curSubContext;
			self::resetCounters();
		}
	}

	private static function getCurrentContext(\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return $renderer->getRules()->getContext();
	}

	private static function getCurrentSubContext(\XF\BbCode\Renderer\AbstractRenderer $renderer)
	{
		return $renderer->getRules()->getSubContext();
	}
}
