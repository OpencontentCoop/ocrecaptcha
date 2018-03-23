(function ($) {

    var Alpaca = $.alpaca;

    Alpaca.Fields.Recaptcha = Alpaca.Fields.HiddenField.extend({
        getFieldType: function () {
            return "recaptcha";
        },

        afterRenderControl: function(model, callback) {
            var self = this;
            this.base(model, function() {
                var container = self.getFieldEl();
                var recaptcha = $('<div class="g-recaptcha" data-sitekey="'+self.options.sitekey+'"></div>').appendTo(container);
                grecaptcha.render(recaptcha[0], {
                    'sitekey' : self.options.sitekey,
                    'callback': function(response) {
                        self.setValue(response)
                    }
                });
                callback();

            });
        }
    });

    Alpaca.registerFieldClass("recaptcha", Alpaca.Fields.Recaptcha);

})(jQuery);
