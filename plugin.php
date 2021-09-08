<?php
/* @global $modx */
/* @var $status $modx */
/* @event  OnSHKChangeStatus */

//Настройка данных для бота
$t_chat_id = "-1111111111"; //Указать свои данные
$t_token = "1111111111:AAAAAAAAAAAA-ZZZZZZZzzzzzzzZ"; //Указать свои данные

switch ($modx->event->name) {

    //При смене статуса заказа
    case 'OnSHKChangeStatus': //$order_id


        if ($status == 1) {
            $modx->addPackage('shopkeeper3', MODX_CORE_PATH . "components/shopkeeper3/model/");
            /*@var $order_ids*/
            $order_id = $order_ids[0];
            $order = $modx->getObject('shk_order', array('id' => $order_id));

            $msg = "";
            $msg .= "<b>Заказ № $order_id на сайте - " . $modx->getOption('site_name') . "</b>\n";
            //Разбиваем контакты
            $contacts = json_decode($order->contacts);
            foreach ($contacts as $k => $v) {
                $msg .= $v->label . ": " . $v->value . "\n";
            }
            $msg .= "Доставка: {$order->delivery}\n";
            $msg .= "Оплата:  {$order->payment}\n";

            //Продукция
            $prods = $modx->getCollection('shk_purchases', array('order_id' => $order_id));
            $msg .= "\n<b>Продукция заказа</b>\n";
            $prod_total_price = 0;
            foreach ($prods as $product) {

                //ДОпы
                $opts = '';
                if ($product->options) {

                    $opt = json_decode($product->options, true);
                    foreach ($opt as $p) {

                        $p['0'] = str_replace('Дополнительные параметры ', '', $p['0']);
                        $opts .= "\n{$p['0']} / {$p['1']} руб.";
                    }

                }

                $prod_total_price += $product->price;


                $data = json_decode($product->data);

                $msg .= "<i><a href='{$data->url}'>{$product->name}</a> / {$product->count} шт. / {$product->price} руб.</i><code>{$opts}</code>\n";
            }
            $msg .= "<b>Итого: $order->price руб.</b>\n";

            tSendmessage($msg, $t_chat_id, $t_token);
        } else {
            $order_ids_i = implode(",", $order_ids);
            tSendmessage("Сменился статус заказа № $order_ids_i \nСтатус: $status", $t_chat_id, $t_token);
        }


        break;

}

function tSendmessage($t_message, $t_chat_id, $t_token)
{
    return file_get_contents("https://api.telegram.org/bot{$t_token}/sendMessage?chat_id={$t_chat_id}&text=" . urlencode($t_message) . "&parse_mode=HTML&disable_web_page_preview=true");
}
