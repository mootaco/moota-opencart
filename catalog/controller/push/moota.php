<?php

require_once __DIR__ . '/../../..'
    . '/system/library/moota-pay/vendor/autoload.php';

use Moota\Opencart\OrderFetcher;
use Moota\Opencart\OrderFullfiler;
use Moota\Opencart\OrderMatcher;
use Moota\SDK\Config as MootaConfig;
use Moota\SDK\PushCallbackHandler;

class ControllerPushMoota extends Controller
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
            ->setOrderFetcher(new OrderFetcher( $this->db ))
            ->setOrderMatcher(new OrderMatcher)
            ->setOrderFullfiler(new OrderFullfiler(
                $this->model_account_customer,
                $this->model_checkout_order,
                $this->model_setting_setting
            ))
        ;

        $statusData = $handler->handle();

        header('Content-Type: application/json');

        echo json_encode($statusData);
    }
}
