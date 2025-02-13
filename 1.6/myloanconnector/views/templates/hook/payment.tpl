{**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<section>
    <div id="myloanconnector-logo" style="text-align: center;">
        <img src="{$hcLogo|escape:'quotes'}" height="30"/>
    </div>
<br>
    <div style="text-align: center;">
        <div>
        {if !empty($loanOverview)}
            {l s='(Buy in installments)' mod='myloanconnector' }
            {if !empty($loanOverview)}
                <div>({l s='Payment'  mod='myloanconnector'} <span id="loan-overview">{$loanOverview|escape:'htmlall':'UTF-8'}</span>)</div>
            {/if}
            {if $isCertified == true}
                <div id="hc-change-payment" onclick="showCalc()" style="text-decoration: underline; cursor: pointer;">
                    {l s='Change calculaton' mod='myloanconnector' }
                </div>
            {/if}
        {else}
            <div onclick="showCalc()" style="text-decoration: underline; cursor: pointer;">
                {l s='Buy in installments redirect' mod='myloanconnector' }
            </div>
        {/if}
    </div>
            <br>
            <div id="hc-info" style="font-size: 14px; text-decoration: underline;" role="button" data-toggle="modal" data-target="#infoModal">
                {l s='Information on the transmission of data to Home Credit a.s.' mod='myloanconnector' }
            </div>
    </div>

</section>

<!-- Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="">
                <h5 style="float: left;" class="modal-title" id="exampleModalLabel">{l s='Information about data transmission' mod='myloanconnector' }</h5>
                <button style="float: right; padding:0 8px 0 8px;" type="button" class="close" data-dismiss="infoModal" onclick="showInfo()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {l s='Personal Information' mod='myloanconnector' }
            </div>
        </div>
    </div>
</div>

{if $isCertified == true}
    {include "module:myloanconnector/views/templates/hook/hc-calculator-dialog.tpl"}
    <script>

        $(document).ready(function () {

            $('#hc-select-payment').click(function (e) {
                showCalc();
            });
        });

        function showCalc() {
            let apiKey = '{$apiKey|escape:'quotes'}';
            showHcCalc('{$productSetCode|escape:'htmlall':'UTF-8'}', {$cartOrderTotal|escape:'htmlall':'UTF-8'}, 0, false, decodeURI(decodeURIComponent('{$calcUrl|escape:'url'}')), apiKey, processCalcResult);
        }

        function processCalcResult(calcResult) {
            calcResult.productPrice = {$cartOrderTotal|escape:'htmlall':'UTF-8'};
            $.cookie("hc_calculator", JSON.stringify(calcResult));
            $.ajax({
                url: decodeURI(decodeURIComponent("{$calcPostUrl|escape:'url'}")),
                data: {literal}{hc_calculator: JSON.stringify(calcResult)}{/literal},
                method: 'POST',
                crossDomain: false,
                dataType: 'json',
            });

            $("#loan-overview").html(
                calcResult.preferredDownPayment/100 + " "
                + "{$currency.sign|escape:'quotes'}" + " + "
                + calcResult.preferredMonths + " x "
                + calcResult.preferredInstallment/100 + " "
                + "{$currency.sign|escape:'quotes'}"
            );
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

{/if}
<script>
    function showInfo() {
        $('#infoModal') .modal('toggle');
    }
</script>
