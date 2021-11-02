/*
    -- -- -- -- -- -- --
    image CAPTCHA
    -- -- -- -- -- -- --
*/

document.addEventListener('DOMContentLoaded', function () {
    var els = document.querySelectorAll('.captcha-refresh'),
        captchaSrc;

    els.forEach(function(el){
        captchaSrc = document.getElementById(el.getAttribute('href').replace('#', ''));
        captchaSrc.src = captchaSrc.src + '&time=' + new Date().getTime();
    });
});
