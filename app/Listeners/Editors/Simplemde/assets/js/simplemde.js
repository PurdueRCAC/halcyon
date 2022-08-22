/*
    -- -- -- -- -- -- --
    SimpleMDE
    -- -- -- -- -- -- --
*/
var smdeConfig = {
    element: null,
    blockStyles: {
        bold: "**",
        italic: "_"
    },
    spellChecker: false,
    status: false,
    forceSync: true,
    previewRender: function (plainText, preview) { // Async method
        if (typeof customMarkdownParser === 'function') {
            plainText = customMarkdownParser(plainText, this.element);
        }
        return this.parent.markdown(plainText);
    },
    showIcons: ["bold", "italic", "heading", "strikethrough", "code", "quote", "unordered-list", "ordered-list", "link", "image", "horizontal-rule", "table"],
    hideIcons: ["side-by-side", "fullscreen", "guide", "undo", "redo"],
    autoDownloadFontAwesome: false
};

var simplemdes = new Array;

function initSimpleMDE(el) {
    smdeConfig.element = el;
    simplemde = new SimpleMDE(smdeConfig);
    simplemdes.push(simplemde);

    el.addEventListener('refreshEditor', function (e) {
        if (e.target && (e.target.classList.contains('simplemde') || e.target.classList.contains('md'))) {
            for (var i = 0; i < simplemdes.length; i++) {
                if (simplemdes[i].element == e.target) {
                    simplemdes[i].codemirror.setValue(e.target.value);
                    simplemdes[i].codemirror.focus();
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.md').forEach(function (el) {
        initSimpleMDE(el);
    });
});

document.addEventListener('initEditor', function (e) {
    if (e.target && (e.target.classList.contains('simplemde') || e.target.classList.contains('md'))) {
        for (var i = 0; i < simplemdes.length; i++) {
            if (simplemdes[i].element == e.target) {
                return;
            }
        }

        initSimpleMDE(e.target);
    }
});

document.addEventListener('refreshEditor', function (e) {
    //if (e.target && (e.target.classList.contains('simplemde') || e.target.classList.contains('md'))) {
    for (var i = 0; i < simplemdes.length; i++) {
        if (simplemdes[i].element == e.target) {
            simplemdes[i].codemirror.setValue(e.target.value);
            simplemdes[i].codemirror.focus();
        }
    }
    //}
});
