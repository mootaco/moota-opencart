<?php

// Heading
$_['heading_title'] = 'Moota Pay';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: You have modified your MootaPay details!';
$_['text_edit'] = 'Edit MootaPay';
$_['text_mootapay'] = <<<EOS
<a target="_blank" href="https://moota.co/">
    <img src="view/image/payment/mootapay.png" alt="MootaPay" title="MootaPay"
        style="border:1px solid #eee;"/>
</a>
EOS;
$_['text_authorisation'] = 'Authorisation';
$_['text_sale'] = 'Sale';
$_['text_transparent'] = 'Transparent Redirect (payment form on site)';
$_['text_iframe'] = 'IFrame (payment form in window)';

// Entry
$_['entry_apikey'] = 'API Key';
$_['entry_apitimeout'] = 'API Timeout';
$_['entry_env'] = 'SDK Environment';
$_['entry_env_prod'] = 'Live';
$_['entry_env_dev'] = 'Sandbox';
$_['entry_push_url'] = 'Moota Push Notification URL';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify the MootaPay payment module';
$_['error_username'] = 'MootaPay API Key is required!';
$_['error_password'] = 'MootaPay password is required!';
$_['error_payment_type'] = 'At least one payment type is required!';

// Help hints
$_['help_apikey'] = <<<EOS
Dapatkan API Key melalui: 
<a href="https://app.moota.co/settings?tab=api"
    target="_new"
>https://app.moota.co/settings?tab=api</a>
EOS;

$_['help_apitimeout'] = 'Dalam detik';
$_['help_push_url'] = 'Masuk halaman edit bank di moota > tab notifikasi > edit "API Push Notif" > lalu masukkan url ini';
