<?php

require_once __DIR__ . '/../../..'
    . '/system/library/moota-pay/vendor/autoload.php';

use Moota\Opencart\OrderFetcher;
use Moota\Opencart\OrderMatcher;
use Moota\SDK\Config as MootaConfig;
use Moota\SDK\PushCallbackHandler;

class ControllerPushMoota extends Controller
{
    public function index()
    {
        $this->load->model('account/customer');
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);

            echo 'Only POST is allowed';
            return;
        }

        $orderPaidStatusId = $this->model_setting_setting->getSettingValue(
            'config_complete_status'
        );
        $orderPaidStatusId = json_decode($orderPaidStatusId);
        $orderPaidStatusId = max($orderPaidStatusId);

        MootaConfig::fromArray( unserialize( $this->config->get(
            'payment_mootapay_serialized'
        ) ) );

        $handler = PushCallbackHandler::createDefault()
            ->setTransactionFetcher(new OrderFetcher( $this->db ))
            ->setPaymentMatcher(new OrderMatcher)
        ;

        $payments = $handler->handle();
        $statusData = array(
            'status' => 'not-ok', 'error' => 'No matching order found'
        );

        if ( count( $payments ) > 0 ) {
            foreach ($payments as $payment) {
                $this->model_account_customer->addTransaction(
                    $payment['customerId'],
                    'MootaPay: Payment Applied',
                    $payment['mootaAmount'],
                    $payment['orderId']
                );

                if ( $payment['mootaAmount'] >= $payment['orderAmount'] ) {
                    $this->model_checkout_order->addOrderHistory(
                        $payment['orderId'],
                        $orderPaidStatusId,
                        'MootaPay: Order fully paid',
                        false, // notify
                        false // override
                    );
                }
            }

            $statusData = array('status' => 'ok', 'count' => count($payments));
        }

        header('Content-Type: application/json');

        echo json_encode($payments);
    }

    /**
     * Add a transaction for a customer's order.
     *
     * Copied from admin's Customer model,
     * because OOP according to opencart core dev
     * means duplicating so much crap in so many places.
     *
     * We can't actually access admin's Customer model
     * via any catalog Controller because frak you extension developer.
     *
     * @param integer|string $customer_id
     * @param string $description
     * @param float|string $amount
     * @param integer|string $order_id
     * @return void
     */
    protected function addCustomerOrderTransaction(
        $customer_id, $description = '', $amount = '', $order_id = 0
    ) {
        $description = $this->db->escape($description);
        $amount = (float) $amount;

        return $this->db->query("
            INSERT INTO " . DB_PREFIX . "customer_transaction 
            SET customer_id = '{$customer_id}'
                , order_id = '{$order_id}'
                , description = '{$description}'
                , amount = '{$amount}'
                , date_added = NOW()"
        );
    }
}
