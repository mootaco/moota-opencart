<?php namespace Moota\Opencart;

use Moota\SDK\Contracts\FetchesTransactions;

class OrderFetcher implements FetchesTransactions
{
    protected $db;

    public function __construct($db)
    {
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
                $whereInflowAmounts[ $key ] = (float) $value;
            }

            $whereInflowAmounts = implode(',', $whereInflowAmounts);
            $whereInflowAmounts = "AND `total` IN ($whereInflowAmounts)";
        }

        $dbPrefix = DB_PREFIX;

        $sql = "
            SELECT `order_id`, `invoice_no`, `invoice_prefix`
                , `store_id`, `total`
            FROM `{$dbPrefix}order`
            WHERE `order_status_id` NOT IN (
                SELECT `value` FROM `{$dbPrefix}setting` 
                WHERE `key` IN (
                    'config_complete_status',
                    'config_fraud_status_id',
                    'config_return_status_id'
                )
            ) $whereInflowAmounts"
        ;

        foreach ($this->db->query($sql)->rows as $row) {
            $orders[] = $row;
        }

        return $orders;
    }
}
