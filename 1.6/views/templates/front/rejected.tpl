{**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="bootstrap">
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        {l s='Your loan was declined if you would like to change your payment method to continue'  mod='myloanconnector' }
        <a href="javascript:redirectToChangePayment()" id="hc-change-payment" >
            {l s='here'  mod='myloanconnector' }
        </a>
    </div>
</div>

<script>

    function redirectToChangePayment() {
        window.location.href = "{$linkChangePayment|escape:'quotes'}";
    }

</script>