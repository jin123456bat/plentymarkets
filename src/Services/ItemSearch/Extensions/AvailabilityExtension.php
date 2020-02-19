<?php

namespace Plentymarket\Services\ItemSearch\Extensions;

class AvailabilityExtension implements ItemSearchExtension
{
	/**
	 * @inheritdoc
	 */
	public function getSearch ($parentSearchBuilder)
	{
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function transformResult ($baseResult, $extensionResult)
	{
		if (count($baseResult['documents'])) {
			foreach ($baseResult['documents'] as $key => $extensionDocument) {
				$iconPath = '/tpl/availability/' . $baseResult['documents'][$key]['data']['variation']['availability']['icon'];
				$baseResult['documents'][$key]['data']['variation']['availability']['iconPath'] = $iconPath;
			}
		}

		return $baseResult;
	}
}
