<style>
@media (min-width: 992px) {
    #ppf-modal .modal-dialog {
        width: 1100px;
    }
}

/*** primary color ***
.ppf-container a {
    border-color: #bc3726 !important;
}
.ppf-container .show-details {
    color: #bc3726 !important;
}
.table-financing-option .badge {
    background: #bc3726 !important;
}
*** primary color ***/

.ppf-container a {
    color: #333;
    border: 2px solid rgb(0,156,222);

    display: block;
    padding: 10px 0;
    text-align: center;
    margin: 0 0 20px 0;

    /*
    display: inline-block;
    padding: 5px 10px;*/
}

.ppf-container a:active,
.ppf-container a:focus,
.ppf-container a:hover {
    text-decoration: none;
}
.ppf-container a:hover {
    /*border-color: rgb(0,135,193);
    background-color: #f0f0f0; rgba(0,156,222,0.05);*/
    border-color: rgb(119, 119, 119);
    background-color: #f6f6f6;
}

.ppf-details .price,
.ppf-container .price {
    font-size: 100%;
    font-weight: bold;
}

.table-financing-option .value {
    text-align: right;
    font-family: "Merriweather", Georgia, "Times New Roman", Times, serif;
}

.ppf-container a p {
    margin-bottom: 0px;
}

.ppf-container .show-details {
    font-size: 95%;
    color: rgb(0,156,222);
    margin: 2px 0 0 0;
}

.ppf-container a:hover .show-details {
    color: #666;
}

.ppf-image {
    max-width: 200px;
    margin-top: 10px;
}

.table-financing-option:hover,
.table-financing-option:hover tfoot {
    background: #f3f3f3;
}

.table-financing-option:hover tfoot {
    color: #fff;
    background: rgb(0,156,222);
}

.table-financing-option:hover > tbody > tr > td {
    /*border-top-color: red;*/
}

.table-financing-option .total {
    background: #f3f3f3;
}

.table-financing-option .badge {
    min-width: 2em;
    font-size: 110%;
    
    /*vertical-align: middle;*/
    padding: 4px 6px;
    border-radius: 0px;
    background: rgb(0,156,222);
}

.table-financing-option .rate-label {
    text-transform: uppercase;
}

.ppf-details .info {
    background: #f3f3f3;
    text-align: center;
    padding: 10px;
    margin: 0 0 20px 0;
}

.ppf-details .info p.desc {
    margin: 5px 0;
    font-size: 0.9em;
}

.ppf-details .info p.title,
.ppf-details .info p.loan {
    margin: 0;
    font-size: 1em;
    font-weight: bold;
}

.ppf-details .legal {
    font-size: 0.9em;
    text-align: center;
}

.ppf-star {
    color: #a94442;
    font-style: normal;
}

.ppf-star:before {
    content: '\2605';
}
</style>

{$bmp = $bestFinancingOption->getMonthlyPayment()}

<div class="ppf-container">
    <a href="#ppf-modal" data-toggle="modal" data-target="#ppf-modal">
        <p class="rate-info">Finanzierung ab <span class="price">{gibPreisStringLocalized($bmp->getValue())}</span> im Monat.</p>
        <p class="show-details"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Informationen zu m&ouml;glichen Raten</p>
    </a>
</div>

<div class="modal fade" id="ppf-modal" tabindex="-1" role="dialog" aria-labelledby="ppf-modal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
                <h4 class="modal-title text-center" id="ppf-modal-label">
                    <img src="{$plugin->cFrontendPfadURLSSL}/images/brands/ppf-credit-xs-de.jpg" class="ppf-image">
                </h4>
            </div>
            <div class="modal-body">
                {include file="{$plugin->cFrontendPfad}template/presentment.tpl"}
            </div>
            <div class="modal-footer">
                {include file="{$plugin->cFrontendPfad}template/presentment-legal.tpl"}
            </div>
        </div>
    </div>
</div>