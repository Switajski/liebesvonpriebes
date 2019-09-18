<script type="text/javascript">
    {literal}
    $(document).ready(function() {
        var $praefix       = $('#bestellabschluss_bestellnummer_praefix'),
            $anfangsnummer = $('#bestellabschluss_bestellnummer_anfangsnummer'),
            $suffix        = $('#bestellabschluss_bestellnummer_suffix'),
            $all           = $('#bestellabschluss_bestellnummer_praefix, #bestellabschluss_bestellnummer_anfangsnummer, #bestellabschluss_bestellnummer_suffix'),
            force          = false;

        $praefix.focus(function(e) {
            this.maxLength = 20 - $anfangsnummer.val().length - $suffix.val().length;
        });
        $anfangsnummer.focus(function(e) {
            this.maxLength = 20 - $praefix.val().length - $suffix.val().length;
        });
        $suffix.focus(function(e) {
            this.maxLength = 20 - $anfangsnummer.val().length - $praefix.val().length;
        });

        $all.tooltip({trigger:'manual'})
            .blur(function(e) {
                $(this).tooltip('hide');
                var value = $(this).val();
                if (value.length > this.maxLength) {
                    $(this).val(value.substr(0, this.maxLength));
                }
            })
            .focus(function(e) {
                updateBestellnummer(this);
            })
            .keyup(function(e) {
                updateBestellnummer(this);
            });

        $all.closest('form').submit(function(e) {
            var praefix       = $praefix.val(),
                anfangsnummer = isNaN(parseInt($anfangsnummer.val())) ? 0 : parseInt($anfangsnummer.val()),
                suffix        = $suffix.val(),
                maxValLength  = 20 - praefix.length - suffix.length,
                maxValStr     = '9'.repeat(maxValLength),
                maxVal        = parseInt(maxValStr);

            if (anfangsnummer > maxVal) {
                $all.closest('.input-group').addClass('has-error has-feedback');
                showNotify('warning', 'Bestellnumer nicht zulässig', 'Die max. m&ouml;gliche Bestellnummer ist kleiner als die Anfangsnummer!');

                return false;
            }
            if (!force && (maxVal - anfangsnummer) < 10000) {
                $anfangsnummer.closest('.input-group').addClass('has-warning has-feedback');
                var $notify = createNotify({
                    title: 'Bestellnummerbereich zu gering',
                    message: 'Es sind nur max. ' + (maxVal - anfangsnummer) + ' Bestellnummern im Format ' + praefix + maxValStr + suffix + ' m&ouml;glich! <button id="forceSave" class="pull-right btn btn-warning"><i class="fa fa-save"></i>Trotzdem speichern!</button>'
                }, {
                    type: 'info',
                    delay: 12000,
                    allow_dismiss: true
                });
                $('#forceSave').on('click', function(e) {
                    $notify.close();
                    force = true;
                    $all.closest('form')[0].submit();
                });

                return false;
            }

            return true;
        });

        function updateBestellnummer(elem) {
            var praefix       = $praefix.val(),
                anfangsnummer = isNaN(parseInt($anfangsnummer.val())) ? 0 : parseInt($anfangsnummer.val()),
                suffix        = $suffix.val(),
                maxValLength  = 20 - praefix.length - suffix.length,
                maxValStr     = '9'.repeat(maxValLength),
                maxVal        = parseInt(maxValStr);

            var result = 'Vorschau: ' + praefix + maxValStr + suffix;

            $(elem).attr('title', result)
                .tooltip('fixTitle')
                .tooltip('show');
            if ((maxVal - anfangsnummer) < 10000) {
                $(elem).closest('.input-group').addClass('has-warning has-feedback');
            } else {
                $all.closest('.input-group').removeClass('has-warning has-feedback');
            }
        }
    });
    {/literal}
</script>