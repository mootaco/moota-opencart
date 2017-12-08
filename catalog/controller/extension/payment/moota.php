<?php

require_once __DIR__ . '/../../../..'
    . '/system/library/moota-pay/vendor/autoload.php';

use Moota\Opencart\OrderFetcher;
use Moota\Opencart\OrderFulfiller;
use Moota\Opencart\OrderMatcher;
use Moota\Opencart\DuplicateFinder;
use Moota\SDK\Config as MootaConfig;
use Moota\SDK\PushCallbackHandler;

class ControllerExtensionPaymentMoota extends Controller
{
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);

            echo 'Only POST is allowed';
            return;
        }

        $this->load->model('account/customer');
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');

        $pluginConfig = unserialize( $this->config->get(
            'payment_mootapay_serialized'
        ) );

        MootaConfig::fromArray($pluginConfig);

        $handler = PushCallbackHandler::createDefault()
            ->setOrderFetcher(new OrderFetcher( $this->db, $this->config ))
            ->setOrderMatcher(new OrderMatcher)
            ->setOrderFulfiller(new OrderFulfiller(
                $this->config,
                $this->model_account_customer,
                $this->model_checkout_order,
                $this->model_setting_setting
            ))
            ->setDupeFinder(new DuplicateFinder($this->config))
        ;

        $statusData = $handler->handle();

        header('Content-Type: application/json');

        echo json_encode($statusData);
    }
}
