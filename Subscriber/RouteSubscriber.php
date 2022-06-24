<?php
namespace MyfavShippingConsent\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Plugin\ConfigReader;

class RouteSubscriber implements SubscriberInterface
{
    private $pluginDirectory;

    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' =>
                'onPostDispatch',
        ];
    }

    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        // Wenn das die Confirm-Action ist, wird geprüft, ob die Checkbox für den Versanddienstleister gesetzt war.
        if ($controller->Request()->getActionName() === 'finish') {
            $myfavShippingConsentChecked = $controller
                ->Request()
                ->getParam('MyfavShippingConsent');

            $sOrderNumber = false;
            $session = Shopware()->Session();
            $sOrderVariables = $session['sOrderVariables']->getArrayCopy();

            if (isset($sOrderVariables['sOrderNumber'])) {
                $sOrderNumber = $sOrderVariables['sOrderNumber'];

                if (
                    !empty($myfavShippingConsentChecked) &&
                    $myfavShippingConsentChecked === 'on'
                ) {
                    $this->updateOrderShippingConsentAttribute(
                        $sOrderNumber,
                        1
                    );
                } else {
                    $this->updateOrderShippingConsentAttribute(
                        $sOrderNumber,
                        0
                    );
                }
            }
        }
    }

    private function updateOrderShippingConsentAttribute(
        $sOrderNumber,
        $value
    ) {
        $orderId = $this->getOrderIdByOrderNumber($sOrderNumber);

        if (false === $orderId) {
            return;
        }

        $this->updateAttribute($orderId, $value);
    }

    private function getOrderIdByOrderNumber($sOrderNumber)
    {
        if (strlen($sOrderNumber) == 0) {
            return false;
        }

        $q = Shopware()
            ->Container()
            ->get('dbal_connection')
            ->createQueryBuilder();

        $q
            ->select('id')
            ->from('s_order')
            ->where('ordernumber = :ordernumber')
            ->setParameter('ordernumber', $sOrderNumber);
        $alldata = $q->execute()->fetchAll();

        if (isset($alldata[0]['id'])) {
            return $alldata[0]['id'];
        }

        return false;
    }

    private function updateAttribute($orderId, $value)
    {
        $connection = Shopware()
            ->Container()
            ->get('dbal_connection');
        $sql =
            'UPDATE s_order_attributes SET myfav_shipping_consent = :myfav_shipping_consent WHERE orderID= :orderID';

        $statement = $connection->prepare($sql);
        $statement->execute([
            'myfav_shipping_consent' => (int)$value,
            'orderID' => $orderId,
        ]);
    }
}
