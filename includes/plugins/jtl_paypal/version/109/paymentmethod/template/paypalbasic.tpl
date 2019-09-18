{if $redirect}
    <div id="paypal-basic-redirect">
        <p class="text-muted">{lang key='redirect'}</p>
        <script type="text/javascript">
            $(function() {
                window.location.href = {$redirect|json_encode};
            });
        </script>
    </div>
{/if}