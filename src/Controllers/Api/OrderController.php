<?php

namespace Plentymarket\Controllers\Api;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Plugin\Http\Response;
use Plentymarket\Builder\Order\AddressType;
use Plentymarket\Builder\Order\OrderBuilder;
use Plentymarket\Builder\Order\OrderBuilderQuery;
use Plentymarket\Builder\Order\OrderOptionSubType;
use Plentymarket\Builder\Order\OrderType;
use Plentymarket\Controllers\BaseApiController;
use Plentymarket\Helper\Utils;
use Plentymarket\Services\AccountService;
use Plentymarket\Services\BasketService;
use Plentymarket\Services\CheckoutService;
use Plentymarket\Services\OrderService;

/**
 * Class OrderController
 * @package Plentymarket\Controllers\Api
 */
class OrderController extends BaseApiController
{
	/**
	 * 创建订单
	 */
	function create (): Response
	{
		try {
			/** @var OrderBuilder $orderBuilder */
			$orderBuilder = pluginApp(OrderBuilder::class);

			/** @var CheckoutService $checkoutService */
			$checkoutService = pluginApp(CheckoutService::class);

			/** @var BasketService $basketService */
			$basketService = pluginApp(BasketService::class);

			/** @var AccountService $accountService */
			$accountService = pluginApp(AccountService::class);
			$contactId = $accountService->getContactId();

			$addressId = $this->request->input('address_id');

			/** @var OrderBuilderQuery $order */
			$order = $orderBuilder->prepare(OrderType::ORDER)
				->fromBasket()
				->withContactId($contactId)
				->withAddressId($addressId, AddressType::BILLING)
				->withAddressId($addressId, AddressType::DELIVERY)
				->withOrderProperty(OrderPropertyType::PAYMENT_METHOD, OrderOptionSubType::MAIN_VALUE, $checkoutService->getMethodOfPaymentId())
				->withOrderProperty(OrderPropertyType::SHIPPING_PROFILE, OrderOptionSubType::MAIN_VALUE, $basketService->getBasket()->shippingProfileId)
				->withOrderProperty(OrderPropertyType::DOCUMENT_LANGUAGE, OrderOptionSubType::MAIN_VALUE, Utils::getLang())
				->withOrderProperty(OrderPropertyType::SHIPPING_PRIVACY_HINT_ACCEPTED, OrderOptionSubType::MAIN_VALUE, 'false')
				->withComment(true, $this->request->input('comment', ''))
				->done();

			$order = pluginApp(OrderService::class)->create($order);

			if ($order instanceof Order) {
				return $this->success([]);
			} else {
				return $this->error('创建订单失败');
			}
		} catch (\Throwable $e) {
			return $this->exception($e);
		}
	}
}