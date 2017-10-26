<?php
  
require_once DIR_SYSTEM . '/library/mootapay/autoload.php';

define('MOOTAPAY_VERSION', '1.0.0');

class ControllerExtensionPaymentMootapay extends Controller {
    private $error = array();

    public function index() {
        $this->language->load('payment/mootapay');
        $this->document->setTitle('MootaPay Payment Method Configuration');
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_setting_setting->editSetting(
                'custom', $this->request->post
            );

            $this->session->data['success'] = 'Saved.';

            $this->redirect($this->url->link(
                'extension/payment',
                'token=' . $this->session->data['token'],
                'SSL'
            ));
        }

        $this->data['heading_title'] = 'asdf';

        $this->data['action'] = $this->url->link(
            'payment/mootapay', 'token=' . $this->session->data['token'], 'SSL'
        );

        $this->data['cancel'] = $this->url->link(
            'extension/payment', 'token=' . $this->session->data['token'], 'SSL'
        );

        if (isset($this->request->post['text_config_one'])) {
            $this->data['text_config_one'] = $this->request
                ->post['text_config_one'];
        } else {
            $this->data['text_config_one'] = $this->config
                ->get('text_config_one');
        }

        $this->load->model('localisation/order_status');

        $this->data['order_statuses'] = $this->model_localisation_order_status
            ->getOrderStatuses();

        $this->template = 'payment/mootapay.tpl';

        $this->children = array('common/header', 'common/footer');

        $this->response->setOutput($this->render());
    }
  
    private function prefix() {
        return (version_compare(VERSION, '3.0', '>=')) ? 'payment_' :  '';
    }

    private function validate()
    {
        $error = false;

        if (!$this->user->hasPermission(
            'modify', 'extension/payment/mootapay'
        )) {
            $this->variables['no_permission'] = true;
            $error = true;
        }

        // Debug mode
        if (!isset($this->request->post['mootapay_debug'])) {
            $this->request->post['mootapay_debug'] = 0;
        }

        return !$error; // If no error => validated
    }
  
    // Test if the config is OK
    private function testConfig()
    {
        // // Load settings
        // $this->load->model('setting/setting');

        // // TEST
        // if ($this->variables['mootapay_is_test_mode']) {
        // // DIRECTKIT TEST URL
        //     $dkUrl = $this->model_setting_setting->getSettingValue(
        //         $this->prefix() . 'mootapay_directkit_url_test'
        //     );
        // } //PROD
        // else {
        // // DIRECTKIT URL
        //     $dkUrl = $this->model_setting_setting->getSettingValue(
        //         $this->prefix() . 'mootapay_directkit_url'
        //     );
        // }

        // // require_once DIR_SYSTEM . '/library/mootapay/mootapayService.php';

        // // API connection
        // $mootapayService = new mootapayService(
        //     $dkUrl,
        //     $this->variables['mootapay_api_login'],
        //     $this->variables['mootapay_api_password'],
        //     substr($this->language->get('code'), 0, 2),
        //     $this->variables['mootapay_debug']
        // );

        // if (empty($this->variables['mootapay_environment_name'])) {
        //     // If lwecommerce, get wallet by email
        //     $params = array('email' => $this->variables['mootapay_api_login']);
        // } else {
        //     // If custom env, get custom wallet
        //     $params = array(
        //         'wallet' => $this->variables['mootapay_custom_wallet']
        //     );
        // }

        // $res = $mootapayService->getWalletDetails($params);
        // if (empty($this->variables['mootapay_environment_name'])) {
        //     // If lwecommerce, get wallet
        //     if (isset($res->WALLET->ID)) {
        //         $this->model_setting_setting->editSettingValue(
        //             $this->prefix() . 'mootapay',
        //             $this->prefix() . 'mootapay_wallet',
        //             $res->WALLET->ID
        //         );
        //     }
        // }

        // if (isset($res->E)) {
        //     $this->variables['error_api'] = $this->language->get('error_api')
        //         . " - " . $mootapayService->printError($res->E);
        //     $this->variables['api_error'] = true;

        //     return false;
        // } else {
        //     return true;
        // }

        // return true;
    }
  
    public function install()
    {
        $this->load->model('extension/payment/mootapay');
        $this->model_extension_payment_mootapay->install();

        // Default settings
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting(
            $this->prefix() . 'mootapay',
            array(
                $this->prefix() . 'mootapay_status' => 1,
            )
        );
    }
  
    public function uninstall()
    {
        // $this->load->model('extension/payment/mootapay');
        // $this->model_extension_payment_mootapay->uninstall();
    }
}
