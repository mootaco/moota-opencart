<?php

require_once __DIR__ . '/../../../..'
    . '/system/library/moota-pay/vendor/autoload.php';

use Moota\SDK\Config as MootaConfig;

class ControllerExtensionPaymentMootapay extends Controller
{
    const SETTING_CODE = 'payment_mootapay';
    const SETTING_KEY = 'payment_mootapay_serialized';

    private $error = array();

    public function index() {
        $this->load->model('setting/setting');

        $languageId = (int) $this->config->get('config_language_id');
        $orderStatuses = array();

        $sql = "
            SELECT `order_status_id`, `name`
            FROM `". DB_PREFIX ."order_status`
            WHERE `language_id` = '{$languageId}'"
        ;

        foreach ($this->db->query($sql)->rows as $row) {
            $orderStatuses[] = array(
                'id' => $row['order_status_id'],
                'name' => $row['name'],
            );
        }

        $baseUrl = explode('/admin/index.php', $_SERVER['REQUEST_URI']);
        $baseUrl = $_SERVER['HTTP_HOST'] . current($baseUrl) . '/index.php';
        $baseUrl = empty($_SERVER['HTTPS'])
            ? "http://{$baseUrl}" : "https://{$baseUrl}";

        $this->load->language('extension/payment/mootapay');

        $this->document->setTitle('MOOTA');

        // load setting data from settings table
        // that has a `code` of: `payment_mootapay` (SETTING_CODE)
        $this->load->model('setting/setting');

        $userToken = $this->session->data['user_token'];
        $session = $this->session;

        $moduleConfig = unserialize( $this->config->get( self::SETTING_KEY ) );

        MootaConfig::fromArray($moduleConfig);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $config = array(
                MOOTA_ENV => $_POST['payment_mootapay_env'],

                MOOTA_API_KEY => $_POST['payment_mootapay_apikey'],

                MOOTA_API_TIMEOUT => (int) $_POST['payment_mootapay_apitimeout'],

                MOOTA_COMPLETED_STATUS =>
                    (int) $_POST['payment_mootapay_completedstatus'],

                'onCompleteSendMail' =>
                    (int) $_POST['payment_mootapay_oncompletesendmail'],

                MOOTA_OLDEST_ORDER =>
                    (int) $_POST['payment_mootapay_oldestorder'],

                MOOTA_USE_UQ_CODE =>
                    (bool) $_POST['payment_mootapay_useuq'],

                'uqCodeLabel' => $_POST['payment_mootapay_uqcodelabel'],

                MOOTA_UQ_MIN =>
                    (int) $_POST['payment_mootapay_uqcodemin'],

                MOOTA_UQ_MAX =>
                    (int) $_POST['payment_mootapay_uqcodemax'],
            );

            $config[ MOOTA_UQ_MIN ] = $config[ MOOTA_UQ_MIN ] < 1
                ? 1 : $config[ MOOTA_UQ_MIN ];

            $config[ MOOTA_UQ_MAX ] =
                $config[ MOOTA_UQ_MAX ] < $config[ MOOTA_UQ_MIN ]
                    ? $config[ MOOTA_UQ_MIN ] : $config[ MOOTA_UQ_MAX ]
            ;

            $config[ MOOTA_OLDEST_ORDER ] = $config[ MOOTA_OLDEST_ORDER ] < 1
                ? 1 : $config[ MOOTA_OLDEST_ORDER ];

            $config[ MOOTA_OLDEST_ORDER ] = $config[ MOOTA_OLDEST_ORDER ] > 30
                ? 30 : $config[ MOOTA_OLDEST_ORDER ];

            $this->model_setting_setting->editSetting(self::SETTING_CODE, array(
                self::SETTING_KEY => serialize($config)
            ));

            MootaConfig::fromArray($config);

            $session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link(
                'extension/payment/mootapay',
                "user_token={$userToken}&type=payment",
                true
            ));
        }

        $viewData = array(
            'payment_mootapay_env' => $moduleConfig[ MOOTA_ENV ],
            'payment_mootapay_push_url' => $baseUrl
                . '?route=extension/payment/moota',
            'payment_mootapay_apikey' => $moduleConfig[ MOOTA_API_KEY ],
            'payment_mootapay_apitimeout' => $moduleConfig[ MOOTA_API_TIMEOUT ],

            'payment_mootapay_completedstatus' =>
                $moduleConfig[ MOOTA_COMPLETED_STATUS ],

            'payment_mootapay_oncompletesendmail' =>
                $moduleConfig['onCompleteSendMail'],

            'payment_mootapay_oldestorder' =>
                $moduleConfig[ MOOTA_OLDEST_ORDER ],

            'payment_mootapay_useuq' => $moduleConfig[ MOOTA_USE_UQ_CODE ],

            'payment_mootapay_uqcodelabel' => $moduleConfig['uqCodeLabel'],

            'payment_mootapay_uqcodemin' => $moduleConfig[ MOOTA_UQ_MIN ],

            'payment_mootapay_uqcodemax' => $moduleConfig[ MOOTA_UQ_MAX ],

            'orderStatuses' => $orderStatuses,

            'header' => $this->load->controller('common/header'),
            'column_left' => $this->load->controller('common/column_left'),
            'footer' => $this->load->controller('common/footer'),

            'action' => $this->url->link(
                'extension/payment/mootapay',
                "user_token={$userToken}",
                true
            ),

            'cancel' => $this->url->link(
                'marketplace/extension',
                "user_token={$userToken}&type=payment",
                true
            ),

            'breadcrumbs' => array(
                array(
                    'text' => $this->language->get('text_home'),
                    'href' => $this->url->link(
                        'common/dashboard', "user_token={$userToken}", true
                    ),
                ),
                array(
                    'text' => $this->language->get('text_extension'),
                    'href' => $this->url->link(
                        'marketplace/extension',
                        "user_token={$userToken}&type=payment",
                        true
                    ),
                ),
                array(
                    'text' => 'Moota',
                    'href' => $this->url->link(
                        'extension/payment/mootapay',
                        "user_token={$userToken}",
                        true
                    ),
                ),
            ),
        );

        $this->response->setOutput( $this->load->view(
            'extension/payment/mootapay', $viewData
        ) );
    }

    public function install() {
        $config = MootaConfig::toArray();
        $config['uqCodeLabel'] = 'Moota - Kode Unik';
        $config[ MOOTA_OLDEST_ORDER ] = 7;
        $config[ MOOTA_UQ_MIN ] = 1;
        $config[ MOOTA_UQ_MAX ] = 999;

        $this->load->model('setting/setting');$this->model_setting_setting
            ->editSetting( self::SETTING_CODE, array(
                self::SETTING_KEY => serialize($config)
            ) );
    }

    public function uninstall() {
        $this->load->model('setting/setting');$this->model_setting_setting
            ->deleteSetting(self::SETTING_CODE);
    }
}
