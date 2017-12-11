<?php namespace Moota\Opencart;

use Moota\SDK\Contracts\Push\MatchesOrders;

class OrderMatcher implements MatchesOrders
{
    /**
     * Matches payments sent by Moota to available transactions in storage.
     * Plugin specific implementation.
     *
     * @param array $payments
     * @param array $orders
     *
     * @return array
     */
    public function match(array $payments, array $orders)
    {
        $matchedPayments = [];

        $guardedPayments = $payments;

        foreach ($orders as $order) {
            $orderAmount = (float) $order['total'];
            $tmpPayment = null;

            foreach ($guardedPayments as $i => $mootaInflow) {
                if (empty($guardedPayments[ $i ])) continue;

                if ( ( (float) $mootaInflow['amount'] ) === $orderAmount ) {
                    $tmpPayment = $mootaInflow;
                    $guardedPayments[ $i ] = null;

                    break;
                }
            }

            if (!empty($tmpPayment)) {
                $matchedPayments[]  = array(
                    'transactionId' => implode('-', array(
                        $order['order_id'],
                        $order['store_id'],
                        $tmpPayment['id'],
                        $tmpPayment['account_number']
                    )),
                    'orderId' => $order['order_id'],
                    'storeId' => $order['store_id'],
                    'customerId' => $order['customer_id'],
                    'mootaId' => $tmpPayment['id'],
                    'mootaAccNo' => $tmpPayment['account_number'],
                    'amount' => (float) $tmpPayment['amount'],
                    'mootaAmount' => (float) $tmpPayment['amount'],
                    'orderAmount' => $orderAmount,
                );
            }
        }

        return $matchedPayments;
    }
}
