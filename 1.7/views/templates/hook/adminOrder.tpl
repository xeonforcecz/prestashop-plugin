{**
*  @author HN Consulting Brno s.r.o
*  @copyright  2019-*
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-edit"></i>
        HomeCredit
    </div>

    <b>{$text["id"]|escape:'htmlall':'UTF-8'}</b> {$loanDetail->getApplicationId()}<br>
    <b>{$text["state"]|escape:'htmlall':'UTF-8'}</b> {$loanDetail->getStateReason()}<br>
    <b>{$text["downpayment"]|escape:'htmlall':'UTF-8'}</b> {round($amount, 2)|escape:'htmlall':'UTF-8'} {$loanDetail->getCurrency()|escape:'htmlall':'UTF-8'}<br>
    <br>
    <a id="refresh" class="btn btn-default" href="{$refreshLink|escape:'htmlall':'UTF-8'}">
        <i class="icon-refresh"></i>
        {$text["button"]|escape:'htmlall':'UTF-8'}
    </a>
</div>
