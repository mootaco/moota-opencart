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
        $request = $this->request;
        $response = $this->response;

        MootaConfig::fromArray( unserialize( $this->config->get(
            self::SETTING_KEY
        ) ) );

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $serverAddress = $_POST['payment_mootapay_env'] === 'production'
                ? 'app.moota.co' : 'moota.matamerah.com';

            $config = array(
                'apiKey' => $_POST['payment_mootapay_apikey'],
                'apiTimeout' => $_POST['payment_mootapay_apitimeout'],
                'sdkMode' => $_POST['payment_mootapay_env'],
                'serverAddress' => $serverAddress,
                'useUniqueCode' => MootaConfig::$useUniqueCode,
                'uqCodePreffix' => MootaConfig::$uqCodePreffix,
                'uqCodeSuffix' => MootaConfig::$uqCodeSuffix,
            );

            $this->model_setting_setting->editSetting(
                self::SETTING_CODE, array(
                    self::SETTING_KEY => serialize($config)
                )
            );

            MootaConfig::fromArray($config);

            $session->data['success'] = $this->language
                ->get('text_success');

            $this->response->redirect($this->url->link(
                'marketplace/extension',
                "user_token={$userToken}&type=payment",
                true
            ));
        }

        $data = array(
            'payment_mootapay_apikey' => MootaConfig::$apiKey,
            'payment_mootapay_apitimeout' => MootaConfig::$apiTimeout,
            'payment_mootapay_env' => MootaConfig::$sdkMode,
            'payment_mootapay_push_url' => $baseUrl
                . '?route=extension/payment/moota',

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

        $this->response->setOutput(
            $this->load->view('extension/payment/mootapay', $data)
        );
    }

    public function install() {
        $this->load->model('setting/setting');$this->model_setting_setting->editSetting(
            self::SETTING_CODE, array(
                self::SETTING_KEY => serialize(MootaConfig::toArray())
            )
        );
    }

    public function uninstall() {
        $this->load->model('setting/setting');$this->model_setting_setting->deleteSetting(self::SETTING_CODE);
    }
}
