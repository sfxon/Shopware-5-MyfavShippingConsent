{extends file="parent:frontend/checkout/confirm.tpl"}

{block name='frontend_checkout_confirm_service_esd'}
    <li class="block-group row--shipping-consent">
        <span class="block column--checkbox">
            <input type="checkbox" id="MyfavShippingConsent" name="MyfavShippingConsent" />
        </span>
        <span class="block column--label">
            <label for="MyfavShippingConsent">{s name="MyfavShippingConsent"}{/s}</label>
        </span>
    </li>
    {$smarty.block.parent}
{/block}