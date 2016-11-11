function nf_grecaptcha_explicit_render(){
    jQuery('.g-recaptcha').each(function(){
        var sitekey = jQuery(this).data('sitekey');
        grecaptcha.render(this, {
            'sitekey':sitekey
        })
    });
}