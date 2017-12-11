<?php namespace Moota\Opencart;

use Moota\SDK\Config as MootaConfig;
use Moota\SDK\Contracts\Push\FindsDuplicate;

class DuplicateFinder implements FindsDuplicate
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function findDupes(array &$mootaInflows, array &$orders)
    {
        $dupes = array();
        $dupedOrderIds = array();
        $idsToRemove = array();
        $dupedCount = 0;

        // for each inflow, find __all__ orders that has the same total
        foreach ($mootaInflows as $inflow) {
            if (
                !empty($inflow['tags'])
                && !empty($inflow['tags']['order_id'])
            ) {
                continue;
            }

            $dupeKey = $inflow['amount'] . '';

            $dupes[ $dupeKey ] = array_filter($orders, function ($order) use (
                $inflow, &$dupedOrderIds, $dupeKey
            ) {
                /** @var array $order */
                $isDuped =
                    (float) $order['total'] === (float) $inflow['amount'];

                // group ids from orders with the same amount
                if ($isDuped) {
                    if ( ! isset($dupedOrderIds[ $dupeKey ]) ) {
                        $dupedOrderIds[ $dupeKey ] = array();
                    }

                    $dupedOrderIds[ $dupeKey ][] = $order['order_id'];
                }

                return $isDuped;
            });
        }

        $message = '';

        foreach ($dupedOrderIds as $amount => $orderIds) {
            if (count($orderIds) <= 1) {
                continue;
            }

            $idsToRemove = array_merge($idsToRemove, $orderIds);

            $dupedCount += count($orderIds) - 1;

            $message .= PHP_EOL . sprintf(
                'Ada order yang sama untuk nominal %s',
                $this->rpFormat( (float) $amount, true )
            ) . PHP_EOL;

            $message .= sprintf(
                'Berikut Order ID yang bersangkutan: %s',
                PHP_EOL . '- ' . implode(PHP_EOL . '- ', $orderIds)
            ) . PHP_EOL . PHP_EOL;
        }

        if ($dupedCount > 0) {
            $message = 'Hai Admin.' . PHP_EOL . PHP_EOL . $message;
            $message .= 'Mohon dicek manual.' . PHP_EOL . PHP_EOL;

            if ( MootaConfig::isLive() ) {
                $this->sendMail(
                    sprintf(
                        '[%s] Ada nominal order yang sama - Moota',
                        // store name
                        $this->config->get('config_name')
                    ),
                    $message
                );
            }
        }

        // change the duplicates in $orders into nulls
        foreach ($orders as $idx => $order) {
            if ( !empty($order) && in_array($order['order_id'], $idsToRemove)
            ) {
                $orders[ $idx ] = null;
            }
        }

        // filter out all nulls;
        $orders = array_filter($orders);

        return $orders;
    }

    protected function rpFormat($money, $withCurr = false) {
        $formatted = number_format(
            $money, 2, ',', '.'
        );

        if ($withCurr) {
            return 'Rp. ' . $formatted;
        }

        return $formatted;
    }

    protected function sendMail($subject, $body)
    {
        try {
            $mail = new \Mail($this->config->get('config_mail_engine'));

            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config
                ->get('config_mail_smtp_hostname');

            $mail->smtp_username = $this->config
                ->get('config_mail_smtp_username');

            $mail->smtp_password = html_entity_decode(
                $this->config->get('config_mail_smtp_password'),
                ENT_QUOTES,
                'UTF-8'
            );

            $mail->smtp_port = $this->config->get('config_mail_smtp_port');

            $mail->smtp_timeout = $this->config
                ->get('config_mail_smtp_timeout');

            $mail->setTo( $this->config->get('config_email') );
            $mail->setFrom( $this->config->get('config_email') );

            $mail->setSender( html_entity_decode(
                // store name
                $this->config->get('config_name'),
                ENT_QUOTES,
                'UTF-8'
            ) );

            $mail->setSubject( html_entity_decode(
                $subject, ENT_QUOTES, 'UTF-8'
            ));

            $mail->setText($body);
            $mail->setHtml($body);
            $mail->send();
        } catch (\Exception $ex) {}
    }
}
