<?php

namespace Plentymarket\Controllers\Api;

use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plentymarket\Controllers\BaseApiController;
use Plentymarket\Services\AccountService;
use Plentymarket\Services\ConfigService;
use Plentymarket\Services\CountryService;
use Plentymarket\Services\HomeService;
use Plentymarket\Services\ItemListService;
use Plentymarket\Services\SessionService;
use Throwable;

/**
 * Class IndexController
 * @package Plentymarket\Controllers\Api
 */
class IndexController extends BaseApiController
{
	/**
	 * @var AccountService
	 */
	private $accountService;

	/**
	 * IndexController constructor.
	 * @param Request $request
	 * @param Response $response
	 * @param AccountService $accountService
	 */
	function __construct (Request $request, Response $response, AccountService $accountService)
	{
		parent::__construct($request, $response);
		$this->accountService = $accountService;
	}

	/**
	 * 用户登录接口
	 * @return Response
	 */
	public function login (): Response
	{
		$email = $this->request->get('email');
		$password = $this->request->get('password');

		if (empty($email) || empty($password)) {
			return $this->error('');
		}

		try {
			$this->accountService->login($email, $password);
			return $this->success([]);
		} catch (\Throwable $e) {
			return $this->exception($e);
		}
	}

	/**
	 * 用户注册接口
	 * @return Response
	 */
	public function register (): Response
	{
		$email = $this->request->get('email');
		$password = $this->request->get('password');

		if (empty($email) || empty($password)) {
			return $this->error('');
		}

		if ($this->accountService->register($email, $password)) {
			return $this->success([]);
		} else {
			return $this->error('');
		}
	}

	/**
	 * 获取商品信息
	 * @param $product_id
	 * @return Response
	 */
	public function product ($product_id): Response
	{
		try {
			/** @var ItemListService $itemListService */
			$itemListService = pluginApp(ItemListService::class);
			$item = $itemListService->getItem($product_id);
			return $this->success($item);
		} catch (\Exception $e) {
			return $this->exception($e);
		}
	}

	/**
	 * 根据国家ID获取城市信息
	 * @return Response
	 */
	public function state (): Response
	{
		try {
			$country_id = $this->request->input('country_id');
			$country_list = pluginApp(CountryService::class)->getAll();
			$states = [];
			foreach ($country_list as $c) {
				if ($c['id'] == $country_id) {
					foreach ($c['states'] as $state) {
						$states[] = [
							'id' => $state['id'],
							'name' => $state['name'],
							'country_id' => $state['countryId'],
						];
					}
				}
			}

			return $this->success($states);
		} catch (Throwable $e) {
			return $this->exception($e);
		}
	}

	/**
	 * 设置语言
	 * @return Response
	 */
	public function language (): Response
	{
		/** @var SessionService $sessionService */
		$sessionService = pluginApp(SessionService::class);
		$id = $this->request->input('id');
		$sessionService->setLang($id);
		return $this->success([
			'id' => $id
		]);
	}

	/**
	 * test
	 * @return Response
	 */
	public function test (): Response
	{
		try {
			/** @var HomeService $homeService */
			$homeService = pluginApp(HomeService::class);
			$itemListService = pluginApp(ItemListService::class);
			$item = $itemListService->getItem(170);
			return $this->success([
				'language' => pluginApp(ConfigService::class)->getActiveLanguageList(),
				'crossSelling' => $itemListService->getItems($item['crossSelling']),
				'product174' => pluginApp(ItemListService::class)->getItem(174, true),
				'product139' => pluginApp(ItemListService::class)->getItem(139, true),
				'blog' => $homeService->article()
			]);
		} catch (\Throwable $e) {
			return $this->exception($e);
		}
	}
}
