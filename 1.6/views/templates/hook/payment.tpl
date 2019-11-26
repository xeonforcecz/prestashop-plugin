{**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="row" id="hc-confirm-payment-method">
    <div class="col-xs-12">
        <p class="payment_module">
            <a id="hc-select-payment" class="cheque">
                <img src="{$hcLogo|escape:'quotes'}" height="30"/>
                <span>
                    {l s='(Buy in installments)' mod='myloanconnector' }
                </span>
                <span id="hc-loan-overview">{$loanOverview|escape:'htmlall':'UTF-8'}</span>
            </a>
        </p>
    </div>
</div>

{if $isCertified == true}
    {include "./hc-calculator-dialog.tpl"}
    <script>

        $(document).ready(function () {
            $('#hc-select-payment').click(function () {
                if (isLoanCookieValid()) {
                    window.location.href = "{$actionUrl|escape:'quotes'}";
                } else {
                    showCalc();
                }
            });
        });

        function showCalc() {
            let apiKey = '{$apiKey|escape:'quotes'}';
            showHcCalc('{$productSetCode|escape:'htmlall':'UTF-8'}', {$cartOrderTotal|escape:'htmlall':'UTF-8'}, 0, false, '{$calcUrl|escape:'quotes'}', apiKey, processCalcResult);
        }

        function processCalcResult(calcResult) {
            calcResult.productPrice = {$cartOrderTotal|escape:'htmlall':'UTF-8'};
            $.cookie("hc_calculator", JSON.stringify(calcResult));
            $.ajax({
                url: "{$calcPostUrl|escape:'quotes'}",
                data: {literal}{hc_calculator: JSON.stringify(calcResult)}{/literal},
                method: 'POST',
                crossDomain: true,
                dataType: 'json',
                complete: function() {
                    window.location.href = "{$actionUrl|escape:'quotes'}";
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
{else}
    <script>

        $(document).ready(function () {
            $('#hc-select-payment').click(function () {
                window.location.href = "{$actionUrl|escape:'quotes'}";
            });
        });

    </script>
{/if}
