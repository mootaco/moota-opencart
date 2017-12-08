<?php namespace Moota\Opencart;

use Moota\SDK\Contracts\Push\FulfillsOrder;

class OrderFulfiller implements FulfillsOrder
{
    public $config;
    public $customerModel;
    public $orderModel;
    public $settingModel;

    public function __construct (
        &$config,
        &$customerModel,
        &$orderModel,
        &$settingModel
    )
    {
        $this->config = $config;
        $this->customerModel = $customerModel;
        $this->orderModel = $orderModel;
        $this->settingModel = $settingModel;
    }

    public function fulfill($order)
    {
        $orderFullfiled = null;

        $config = unserialize($this->config->get('payment_mootapay_serialized'));
        $notifyCustomer = $config['onCompleteSendMail'];
        $notifyCustomer = !is_bool($notifyCustomer) ?: false;
        $completedStatus = $config[ MOOTA_COMPLETED_STATUS ];

        try {
            $this->customerModel->addTransaction(
                $order['customerId'],
                "MootaPay: Payment Applied for OrderID #{$order['orderId']}",
                $order['mootaAmount'],
                $order['orderId']
            );

            if ( $order['mootaAmount'] >= $order['orderAmount'] ) {
                $this->orderModel->addOrderHistory(
                    $order['orderId'],
                    $completedStatus,
                    'MootaPay: Order fully paid',
                    $notifyCustomer, // notify
                    false // override
                );
            }

            $orderFullfiled = true;
        } catch (\Exception $ex) {
            $orderFullfiled = false;
        }

        return $orderFullfiled;
    }
}
