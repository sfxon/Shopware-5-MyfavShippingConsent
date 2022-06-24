<?php

namespace MyfavShippingConsent;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Menu\Menu;
use Shopware\Components\Theme\LessDefinition;
use Shopware\Components\Api\Manager;

class MyfavShippingConsent extends Plugin
{
    /*
     * @inheritdoc
     */
    public function install(InstallContext $context)
    {
        // Freitextfeld hinzufügen, in dem hinterlegt werden kann, ob der Kunde mit der Weitergabe
        // bestimmter persönlicher Daten an den Veranddienstleister einverstanden ist.
        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update(
            's_order_attributes',
            'myfav_shipping_consent',
            'boolean',
            [
                'label' => 'Informiere Versanddienstleister',
                'supportText' => 'Zeigt, ob der Benutzer die Weitergabe persönlicher Daten an den Versanddienstleister akzeptiert hat.',
                'helpText' => 'Wenn die Bestellung vor der Installation dieses Plugins durchgeführt wurde, ist dieses Feld leer.',
                'displayInBackend' => true,
                'position' => 100,
                'custom' => false,
            ],
            null,
            false,
            0
        );

        parent::install($context);
    }

    /*
     * @inheritdoc
     */
    public function activate(ActivateContext $activateContext)
    {
        // Clear the cache on plugin activation.
        $activateContext->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
    
    /*
     * @inheritdoc
     */
    public function deactivate(DeactivateContext $deactivateContext)
    {
        // Clear the cach on plugin deactivation.
        $deactivateContext->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }

    /*
     * @inheritdoc
     */
    public function uninstall(UninstallContext $context)
    {
        if (!$context->keepUserData()) {
            $service = $this->container->get('shopware_attribute.crud_service');
            $service->delete('s_order_attributes', 'myfav_shipping_consent');
        }

        // Clear only cache when switching from active state to uninstall
        if ($context->getPlugin()->getActive()) {
            $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
        }

        parent::uninstall($context);
    }
}
