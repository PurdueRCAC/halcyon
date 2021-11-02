/*
    -- -- -- -- -- -- --
    SimpleMDE
    -- -- -- -- -- -- --
*/

document.addEventListener('DOMContentLoaded', function () {
    var els = document.querySelectorAll('.simplemde');

    els.forEach(function(el){
        var simplemde = new SimpleMDE({
            element: el,
            blockStyles: {
                bold: "**",
                italic: "_"
            },
            spellChecker: false,
            status: false,
            showIcons: ["bold", "italic", "heading", "strikethrough", "code", "quote", "unordered-list", "ordered-list", "link", "image", "horizontal-rule", "table"],
            hideIcons: ["side-by-side", "fullscreen", "guide", "undo", "redo"],
            autoDownloadFontAwesome: false
        });
    });
});
