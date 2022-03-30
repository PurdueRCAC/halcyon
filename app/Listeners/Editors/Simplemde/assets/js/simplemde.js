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
    showIcons: ["bold", "italic", "heading", "strikethrough", "code", "quote", "unordered-list", "ordered-list", "link", "image", "horizontal-rule", "table"],
    hideIcons: ["side-by-side", "fullscreen", "guide", "undo", "redo"],
    autoDownloadFontAwesome: false
};

var simplemdes = new Array;

document.addEventListener('DOMContentLoaded', function () {
    var els = document.querySelectorAll('.simplemde'),
        simplemde = null;

    els.forEach(function (el) {
        smdeConfig.element = el;
        simplemde = new SimpleMDE(smdeConfig);
        simplemdes.push(simplemde);
    });
});

document.addEventListener('initEditor', function (e) {
    if (e.target && (e.target.classList.contains('simplemde') || e.target.classList.contains('md'))) {
        smdeConfig.element = e.target;
        var simplemde = new SimpleMDE(smdeConfig);
        simplemdes.push(simplemde);
    }
});

document.addEventListener('refreshEditor', function (e) {
    if (e.target && (e.target.classList.contains('simplemde') || e.target.classList.contains('md'))) {
        for (var i = 0; i < simplemdes.length; i++) {
            if (simplemdes[i].element == e.target) {
                simplemdes[i].codemirror.setValue(e.target.value);
            }
        }
    }
});
