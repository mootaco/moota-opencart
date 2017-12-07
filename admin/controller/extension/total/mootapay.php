<?php
class ControllerExtensionTotalMootapay extends Controller {
    const SETTING_CODE = 'total_mootapay';

    public function index() {
        $userToken = $this->session->data['user_token'];
        $configUrl = $this->url->link(
            'extension/total/mootapay',
            "user_token={$userToken}",
            true
        );

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $postData = $this->request->post;

            if (
                !isset($postData['total_mootapay_status'])
                || (
                    $postData['total_mootapay_status'] != 1
                    && $postData['total_mootapay_status'] != 0
                )
            ) {
                $postData['total_mootapay_status'] = 1;
            }

            if (
                !isset($postData['total_mootapay_sort_order'])
                || $postData['total_mootapay_sort_order'] < 90
                || $postData['total_mootapay_sort_order'] > 999
            ) {
                $postData['total_mootapay_sort_order'] = 999;
            }

            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting(
                self::SETTING_CODE, $postData
            );

            $this->session->data['success'] = 'Success';

            $this->response->redirect($configUrl);
        }

        $this->load->language('extension/total/mootapay');

        $headingTitle = $this->language->get('heading_title');
        $this->document->setTitle($headingTitle);

        $status = $this->config->get(self::SETTING_CODE . '_status');
        $sortOrder = $this->config->get(self::SETTING_CODE . '_sort_order');

        $viewData = array(
            'total_mootapay_status' => $status,
            'total_mootapay_sort_order' => $sortOrder,

            'user_token' => $userToken,
            'heading_title' => $headingTitle,

            'text_edit' => 'Edit',
            'text_enabled' => 'Enable',
            'text_disabled' => 'Disable',
            'text_none' => 'None',

            'button_save' => 'Save',
            'button_cancel' => 'Cancel',

            'breadcrumbs' => array(
                array(
                    'text' => 'Home',
                    'href' => $this->url->link(
                        'common/dashboard',
                        "user_token={$userToken}",
                        true
                    )
                ),
                array(
                    'text' => 'Total',
                    'href' => $this->url->link(
                        'marketplace/extension',
                        "user_token={$userToken}&type=total",
                        true
                    )
                ),
                array(
                    'text' => 'Mootapay: Kode Unik',
                    'href' => $this->url->link(
                        'extension/total/mootapay',
                        "user_token={$userToken}",
                        true
                    )
                ),
            ),

            'config_url' => $configUrl,

            'action' => $this->url->link(
                'extension/total/mootapay',
                "user_token={$userToken}",
                true
            ),

            'cancel' => $this->url->link(
                'extension/total',
                "user_token={$userToken}",
                true
            ),

            'header' => $this->load->controller('common/header'),
            'column_left' => $this->load->controller('common/column_left'),
            'footer' => $this->load->controller('common/footer'),
        );

        $this->response->setOutput( $this->load->view(
            'extension/total/mootapay', $viewData
        ) );
    }

    public function install() {
        $this->load->model('setting/setting');

        $this->model_setting_setting
            ->editSetting( self::SETTING_CODE, array(
                self::SETTING_CODE . '_status' => '1',
                self::SETTING_CODE . '_sort_order' => '999',
            ) );
    }

    public function uninstall() {
        $this->load->model('setting/setting');

        $this->model_setting_setting
            ->editSetting( self::SETTING_CODE, array(
                self::SETTING_CODE . '_status' => '0',
                self::SETTING_CODE . '_sort_order' => '999',
            ) );
    }
}
