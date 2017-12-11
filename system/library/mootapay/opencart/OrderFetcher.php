<?php namespace Moota\Opencart;

use Moota\SDK\Contracts\Push\FetchesOrders;

class OrderFetcher implements FetchesOrders
{
    protected $config;
    protected $db;

    public function __construct($db, $config)
    {
        $this->config = $config;
        $this->db = $db;
    }

    /**
     * Fetches currently available transaction in storage.
     * Plugin specific implementation.
     *
     * @param array $inflowAmounts
     *
     * @return array
     */
    public function fetch(array $inflowAmounts)
    {
        $orders = array();
        $whereInflowAmounts = '';

        if (!empty($inflowAmounts) && count($inflowAmounts) > 0) {
            $whereInflowAmounts = array();

            foreach ($inflowAmounts as $key => $value) {
                $whereInflowAmounts[] = (float) $value;
            }

            $whereInflowAmounts = implode(',', $whereInflowAmounts);
            $whereInflowAmounts = "AND `total` IN ($whereInflowAmounts)";
        }

        $dbPrefix = DB_PREFIX;

        $sql = "
            SELECT `value`, `serialized` FROM `{$dbPrefix}setting`
            WHERE `key` IN (
                'config_complete_status',
                'config_fraud_status_id',
                'config_return_status_id'
            )"
        ;

        $statusIds = array();

        foreach ($this->db->query($sql)->rows as $row) {
            $statusId = $row['serialized'] == 1
                ? json_decode($row['value']) : $row['value'];

            if (is_array($statusId)) {
                $statusIds = array_merge($statusIds, $statusId);
            } else {
                $statusIds[] = $statusId;
            }
        }

        asort($statusIds);
        $statusIds = implode(', ', $statusIds);

        $config = unserialize($this->config->get('payment_mootapay_serialized'));

        $oldestOrder = $config['oldestOrder'];
        $oldestOrder = $oldestOrder > 7 ?: 7;
        $oldestOrder = $oldestOrder < 1 ?: 7;
        $oldestOrder = date(
            'Y-m-d H:i:s', strtotime("-{$oldestOrder} days")
        );

        $sql = "
            SELECT `order_id`, `invoice_no`, `invoice_prefix`
                , `customer_id`, `store_id`, `total`
            FROM `{$dbPrefix}order`
            WHERE `date_added` >= '{$oldestOrder}'
              AND `order_status_id` NOT IN ($statusIds)
              $whereInflowAmounts"
        ;

        foreach ($this->db->query($sql)->rows as $row) {
            $orders[] = $row;
        }

        return $orders;
    }
}
