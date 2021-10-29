/* global $ */  // jquery.js

$(document).ready(function() {
    // Cache selectors
    /*var lastId,
    topMenu = $("#sidenav");
    if (topMenu.length) {
        var topMenuHeight = topMenu.outerHeight()+1,
            topMenuPos = topMenu.offset().top - 20;

        // All list items
        menuItems = topMenu.find("a"),

        // Anchors corresponding to menu items
        scrollItems = menuItems.map(function(){
            var item = $($(this).attr("href"));
            if (item.length) {
                return item;
            }
        });

        // Bind click handler to menu items
        // so we can get a fancy scroll animation
        menuItems.click(function(e){
            var href = $(this).attr("href"),
                offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
            $('html, body').stop().animate({
                scrollTop: offsetTop
            }, 850);
            e.preventDefault();
        });

        // Bind to scroll
        $(window)
            .on('scroll', function (e) {
                if ($(this).scrollTop() >= topMenuPos) {
                    if (topMenu.css('position') != 'fixed') {
                        topMenu.css('position', 'fixed').css('top', 20);
                    }
                } else {
                    topMenu.css('position', 'static').css('top', 'auto');
                }

                // Get container scroll position
                var fromTop = $(this).scrollTop()+topMenuHeight;
                
                // Get id of current scroll item
                var cur = scrollItems.map(function(){
                    if ($(this).offset().top < fromTop)
                    return this;
                });

                // Get the id of the current element
                cur = cur[cur.length-1];
                var id = cur && cur.length ? cur[0].id : "";

                if (lastId !== id) {
                    lastId = id;
                    // Set/remove active class
                    menuItems
                        .parent().removeClass("active")
                        .end().filter("[href='#"+id+"']").parent().addClass("active");
                }
            });
    }*/

    $('.form-check-input').on('change', function () {
        if ($(this).is(':checked') && $(this).data('show')) {
            $($(this).data('show')).removeClass('hide');
            if ($(this).data('hide')) {
                $($(this).data('hide')).addClass('hide');
            }
        } else {
            $(this).closest('fieldset').find('.form-dependent').addClass('hide');
        }
    });

    $('[data-word-limit]').on('keyup', function () {
        var words = $(this).val()
            .replace(/^[\s,.;]+/, "")
            .replace(/[\s,.;]+$/, "")
            .split(/[\s,.;]+/)
            .length;
        $(this).parent().find('.word-count').text(words);

        var max = parseInt($(this).data('word-limit'));
        if (words >= max) {
            var trimmed = $(this).val().split(/\s+/, max).join(' ');
            $(this).val(trimmed);
        }
    });

    var invalid = false,
        sbmt = $('#earlyuser'),
        frm = sbmt.closest('form')[0];
    sbmt.prop('disabled', true);

    var inputs = $('input[required],textarea[required]');
    var needed = inputs.length, validated = 0;

    inputs.on('blur', function () {
        if (this.value) {
            if (this.validity.valid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                validated++;
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
        if (needed == validated) {
            sbmt.prop('disabled', false);
        }
    });

    inputs.on('change', function () {
        if (this.value) {
            if (this.validity.valid) {
                this.classList.add('is-valid');
                validated++;
            } else {
                this.classList.add('is-invalid');
            }
        }
        if (needed == validated) {
            sbmt.prop('disabled', false);
        }
    });

    sbmt.on('click', function (e) {
        e.preventDefault();

        var elms = frm.querySelectorAll('input[required]');
        elms.forEach(function (el) {
            if (!el.value || !el.validity.valid) {
                el.classList.add('is-invalid');
                invalid = true;
            } else {
                el.classList.remove('is-invalid');
            }
        });
        elms = frm.querySelectorAll('select[required]');
        elms.forEach(function (el) {
            if (!el.value || el.value <= 0) {
                el.classList.add('is-invalid');
                invalid = true;
            } else {
                el.classList.remove('is-invalid');
            }
        });
        elms = frm.querySelectorAll('textarea[required]');
        elms.forEach(function (el) {
            if (!el.value || !el.validity.valid) {
                el.classList.add('is-invalid');
                invalid = true;
            } else {
                el.classList.remove('is-invalid');
            }
        });

        if (!invalid) {
            frm.submit();
        }
    });
});
