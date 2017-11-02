<?php

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

        // $this->load->model('checkout/order');

        $sql = 'SELECT `value` FROM `'. DB_PREFIX . 'setting` '
            . "WHERE `key` = 'config_complete_status'";

        $query = $this->db->query($sql);

        $statuses = array();

        foreach ($query->rows as $row) {
            $tmp = $row['value'];
            $tmp = str_replace(['[', '"', ']'], '', $tmp);

            $statuses = array_merge($statuses, explode(',', $tmp));
        }

        $statuses = array_unique($statuses, SORT_NUMERIC);
        asort($statuses);

        $statuses = implode(', ', $statuses);

        $sql = "
            SELECT `order_id`, `invoice_no`, `invoice_prefix`
                , `store_id`, `total`
            FROM `". DB_PREFIX . "order`
            WHERE `order_status_id` NOT IN ($statuses)";

        $orders = $this->db->query($sql)->rows;

        echo '<pre>';
        var_dump(compact(
            'statuses', 'sql', 'orders'
        ));
        echo '</pre>';
        exit;
    }
}
