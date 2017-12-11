<?php

require_once __DIR__ . '/../../../..'
    . '/system/library/mootapay/vendor/autoload.php';

class ModelExtensionTotalMootapay extends Model {
    public function getTotal($totalData) {
        $config = unserialize(
            $this->config->get('payment_mootapay_serialized')
        );

        $sortOrder = (int) $this->config->get('total_mootapay_sort_order');

        $enabled = (bool) $this->config->get('total_mootapay_status');
        $enabled = $enabled && $totalData['total'] > 0;
        $enabled = $enabled && $config[ MOOTA_USE_UQ_CODE ];
        $uqCode = null;

        if ($enabled) {
            $uqCodeLabel = $config['uqCodeLabel'];
            $uqCodeMin = $config[ MOOTA_UQ_MIN ];
            $uqCodeMax = $config[ MOOTA_UQ_MAX ];

            $uqCode = mt_rand($uqCodeMin, $uqCodeMax);

            $totalData['totals'][] = array(
                'code' => 'mootapay',
                'title' => $uqCodeLabel,
                'value' => $uqCode,
                'sort_order' => $sortOrder,
            );

            $totalData['total'] += $uqCode;
        } else {
            $mootaIdx = null;
            $uqCode = null;

            foreach ($totalData['totals'] as $idx => $total) {
                if ($total['code'] === 'mootapay') {
                    $mootaIdx = $idx;
                    $uqCode = $total['value'];
                    break;
                }
            }

            if (!empty($mootaIdx)) {
                unset($totalData['totals'][ $mootaIdx ]);

                $totalData['totals'] = array_filter($totalData['totals']);

                $totalData['total'] -= $uqCode;
            }
        }
    }
}
