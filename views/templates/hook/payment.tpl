<p class="payment_module">
	<a href="javascript:$('#servired_form').submit();" title="{l s='Connect to TPV' mod='servired'}" >
		<img src="{$module_dir}img/credit_cards.jpg" alt="{l s='Connect to TPV' mod='servired'}" style="height:48px"/>
		{l s='Credit card payment: Visa, Visa Electron, Mastercard or Maestro. Secure connection through Servired payment gateway.' mod='servired'}
	</a>
</p>

<form action="{$tpv_url}" method="post" id="servired_form" class="hidden" accept-charset="iso-8859-1">
	<input type="hidden" name="Ds_Merchant_Amount" value="{$total_amount}" />
	<input type="hidden" name="Ds_Merchant_Currency" value="{$currency_code}" />
	<input type="hidden" name="Ds_Merchant_Order" value="{$order}" />
	<input type="hidden" name="Ds_Merchant_MerchantCode" value="{$merchant_code}" />
	<input type="hidden" name="Ds_Merchant_Terminal" value="{$terminal}" />
	<input type="hidden" name="Ds_Merchant_TransactionType" value="{$transaction_type}" />
	<input type="hidden" name="Ds_Merchant_Cardholder" value="{$cardholder}" />
	<input type="hidden" name="Ds_Merchant_MerchantName" value="{$merchant_name}" />
  {if $notification>0}
	<input type="hidden" name="Ds_Merchant_MerchantURL" value="{$merchant_url}" />
  {/if}
	<input type="hidden" name="Ds_Merchant_ProductDescription" value="{$product_list}" />
	<input type="hidden" name="Ds_Merchant_UrlOK" value="{$url_ok}" />
	<input type="hidden" name="Ds_Merchant_UrlKO" value="{$url_ko}" />
    <input type="hidden" name="Ds_Merchant_MerchantSignature" value="{$signature}" />
	<input type="hidden" name="Ds_Merchant_ConsumerLanguage" value="{$tpv_lang_code}" />
    <input type="hidden" name="Ds_Merchant_PayMethods" value="T" />
</form>
