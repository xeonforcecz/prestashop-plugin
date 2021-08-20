{**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div id="hc-calc-button" style="display: none; text-align: center;">
    <a href="javascript:showCalc()">{l s='Installment calculator' mod='myloanconnector' }</a>
</div>

{if $isCertified == true}
    {include file="module:myloanconnector/views/templates/hook/hc-calculator-dialog.tpl"}
{/if}

<script type="text/javascript">
    let priceElement = document.getElementsByClassName("product-prices")[0];
    let calculatorButton = document.getElementById("hc-calc-button");
    let productPriceVariant = null;
    let minimalPrices = {
        'CZK': {$minimalPrice["CZK"]},
        'EUR': {$minimalPrice["EUR"]}
    };
    let isoCode; 

    if(typeof currency === 'undefined' && typeof currencySign !== 'undefined') {
        
        switch(currencySign){
            case '€': 
                isoCode = "EUR";
            break;
            default: 
                isoCode = "CZK";
            break;
        }

    } else {
        isoCode = "{$currency.iso_code}";
    }

    setInterval(function () {

        productPriceVariant = Math.round(parseFloat(priceElement.textContent.replace(/\s/g,'').replace(/,/g,'.'))*100);

        if(productPriceVariant >= (minimalPrices[isoCode] * 100)){
            calculatorButton.style.display = "";
        } else {
            calculatorButton.style.display = "none";
        }

    }, 1000);

    function showCalc() {
        let calcUrl = decodeURI(decodeURIComponent('{$calcUrl|escape:'url'}'));
        calcUrl = calcUrl.replace(/%price_placeholder%/g, productPriceVariant);

        {if $isCertified == true}
            let apiKey = '{$apiKey|escape:'quotes'}';
            showHcCalc('{$productSetCode|escape:'htmlall':'UTF-8'}', productPriceVariant, 0, false, calcUrl, apiKey, processCalcResult);
        {else}
            let dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
            let dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;
            let width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            let height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
            let w = 800;
            let h = 550;
            let systemZoom = width / window.screen.availWidth;
            let left = (width - w) / 2 / systemZoom + dualScreenLeft;
            let top = (height - h) / 2 / systemZoom + dualScreenTop;
            window.open(calcUrl, '_blank', 'toolbar=no, location=no, directories=no, status=no, menubar=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
    {/if}
    }

    {if $isCertified == true}

        function processCalcResult(calcResult) {
            //ajaxCart.add({$productId|escape:'htmlall':'UTF-8'}, null, false, this);
            calcResult.productPrice = {$productPrice|escape:'htmlall':'UTF-8'};
            $.post(  decodeURI(decodeURIComponent("{$calcPostUrl|escape:'url'}")), {literal}{hc_calculator: JSON.stringify(calcResult)});{/literal}
            $.cookie("hc_calculator", JSON.stringify(calcResult));
        }

    {/if}

</script>
