/*
Scripts for the Anvil pages
*/

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.form-check-input').forEach(function(el) {
        el.addEventListener('change', function () {
            if (this.checked && this.getAttribute('data-show')) {
                document.querySelectorAll(this.getAttribute('data-show')).forEach(function(item) {
                    item.classList.remove('hide');
                });
                if (this.getAttribute('data-hide')) {
                    document.querySelectorAll(this.getAttribute('data-hide')).forEach(function (item) {
                        item.classList.add('hide');
                    });
                }
            } else {
                this.closest('fieldset').querySelectorAll('.form-dependent').forEach(function (item) {
                    item.classList.add('hide');
                });
            }
        });
    });

    document.querySelectorAll('[data-word-limit]').forEach(function (el) {
        el.addEventListener('keyup', function () {
            var words = this.value
                .replace(/^[\s,.;]+/, "")
                .replace(/[\s,.;]+$/, "")
                .split(/[\s,.;]+/)
                .length;

            this.parentNode.querySelector('.word-count').textContent = words;

            var max = parseInt(this.getAttribute('data-word-limit'));
            if (words >= max) {
                var trimmed = this.value.split(/\s+/, max).join(' ');
                this.value = trimmed;
            }
        });
    });

    var invalid = false,
        sbmt = document.getElementById('earlyuser'),
        frm = sbmt.closest('form');
    sbmt.disabled = true;

    var inputs = document.querySelectorAll('input[required],textarea[required]');
    var needed = inputs.length, validated = 0;

    inputs.forEach(function(input) {
        input.addEventListener('blur', function () {
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
                sbmt.disabled = false;
            }
        });

        input.addEventListener('change', function () {
            if (this.value) {
                if (this.validity.valid) {
                    this.classList.add('is-valid');
                    //validated++;
                } else {
                    this.classList.add('is-invalid');
                }
            }
            if (needed == validated) {
                sbmt.disabled = false;
            }
        });
    });

    sbmt.addEventListener('click', function (e) {
        if (!frm) {
            return true;
        }
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
        elms = frm.find('select[required]');
        elms.forEach(function (el) {
            if (!el.value || el.value <= 0) {
                el.classList.add('is-invalid');
                invalid = true;
            } else {
                el.classList.remove('is-invalid');
            }
        });
        elms = frm.find('textarea[required]');
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
