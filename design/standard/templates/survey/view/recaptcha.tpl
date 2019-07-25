<style>.g-recaptcha div{ldelim}margin: 0{rdelim}</style>
<fieldset>
    <div class="g-recaptcha" data-sitekey="{$question.text2}"></div>
    <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl={fetch( 'content', 'locale' ).country_code|downcase}"></script>
</fieldset>