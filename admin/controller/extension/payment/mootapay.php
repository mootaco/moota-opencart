<?php

require_once __DIR__ . '/system/library/moota-pay/vendor/autoload.php';

use Moota\SDK\Config as MootaConfig;

class ControllerExtensionPaymentMootapay extends Controller
{
    const SETTING_KEY = 'payment_mootapay';

    private $error = array();

    public function index() {
        $this->load->language('extension/payment/mootapay');

        $this->document->setTitle('MOOTA');

        $this->load->model('setting/setting');

        MootaConfig::fromArray( unserialize( $this->config->get(
            self::SETTING_KEY
        ) ) );

        if (
            $this->request->server['REQUEST_METHOD'] == 'POST'
        ) {
            $this->model_setting_setting->editSetting(
                'payment_mootapay', $this->request->post
            );

            $this->session->data['success'] = $this->language
                ->get('text_success');

            $this->response->redirect($this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'],
                true
            ));
        }

        if ( isset(
            $this->request->post['payment_mootapay_payment_gateway']
        ) ) {
            $data['payment_mootapay_payment_gateway'] = $this->request
                ->post['payment_mootapay_payment_gateway'];
        } else {
            $data['payment_mootapay_payment_gateway'] = $this->config->get('payment_mootapay_payment_gateway');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'],
                true
            )
        );

        $data['breadcrumbs'][] = array(
            'text' => 'MOOTA',
            'href' => $this->url->link('extension/payment/mootapay', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link(
            'extension/payment/mootapay',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        $data['cancel'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token']
                . '&type=payment',
            true
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/payment/mootapay', $data)
        );
    }

    public function install() {
        $this->load->model('setting/setting');$this->model_setting_setting->editSetting(
            self::SETTING_KEY, array(
                self::SETTING_KEY => serialize(array(
                    'apiKey' => MootaConfig::$apiKey,
                    'apiTimeout' => MootaConfig::$apiTimeout,
                    'sdkMode' => MootaConfig::$sdkMode,
                    'serverAddress' => MootaConfig::$serverAddress,
                    'useUniqueCode' => MootaConfig::$useUniqueCode,
                    'uqCodePreffix' => MootaConfig::$uqCodePreffix,
                    'uqCodeSuffix' => MootaConfig::$uqCodeSuffix,
                ))
            )
        );
    }

    public function uninstall() {
        $this->load->model('setting/setting');$this->model_setting_setting->deleteSetting(self::SETTING_KEY);
    }
}
