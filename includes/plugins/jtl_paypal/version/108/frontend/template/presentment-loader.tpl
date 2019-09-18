<div class="ppf-loader">
    <script>
        $(function() {
            paypal({ selector: '.ppf-loader' })
                .getInstallments('{$amount}', '{$currency}');
        });
    </script>
</div>