{**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}
<form method="POST" action="{$actionUrl}" id="hc-confirm-payment-method">
    <p class="payment_module" id="hc-select-payment">
        <img src="{$hcLogo}" height="30"/>
        <span>
            {l s='(Buy in installments)' mod='myloanconnector' }
        </span>
        <span id="hc-loan-overview">{$loanOverview|escape:'htmlall':'UTF-8'}</span>
    </p>
</form>

{if $isCertified == true}

    {include file="{$module_templates}hook/hc-calculator-dialog.tpl"}
    <script>
        var hcBaseUrl = '{$urls.base_url}';

        function showCalc() {
            let apiKey = '{$apiKey|escape:'quotes'}';
            showHcCalc('{$productSetCode|escape:'htmlall':'UTF-8'}', {$cartOrderTotal|escape:'htmlall':'UTF-8'}, 0, false, '{$calcUrl|escape:'UTF-8'}', apiKey, processCalcResult);
        }

        function processCalcResult(calcResult) {
            calcResult.productPrice = {$cartOrderTotal|escape:'htmlall':'UTF-8'};
            $.cookie("hc_calculator", JSON.stringify(calcResult));
            $.ajax({
                url: "{url entity='module' name='myloanconnector' controller='payment'}",
                data: {literal}{hc_calculator: JSON.stringify(calcResult)}{/literal},
                method: 'POST',
                crossDomain: true,
                dataType: 'json',
                complete: function() {
                    $("#payment-confirmation > .ps-shown-by-js > button").trigger("click");
                },
            });
        }

        function isLoanCookieValid() {
            if (!$.cookie("hc_calculator")) {
                return false
            }

            let loanCookie = $.parseJSON($.cookie("hc_calculator"));
            return (Math.round(loanCookie.creditAmount) == Math.round({$cartOrderTotal|escape:'quotes'}));
        }

    </script>
{/if}
