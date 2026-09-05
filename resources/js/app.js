import Alpine from 'alpinejs';
import 'katex/dist/katex.min.css';
import renderMathInElement from 'katex/contrib/auto-render';

window.Alpine = Alpine;

const mathOptions = {
    delimiters: [
        { left: '\\[', right: '\\]', display: true },
        { left: '$$', right: '$$', display: true },
        { left: '\\(', right: '\\)', display: false },
        { left: '$', right: '$', display: false },
    ],
    throwOnError: false,
    strict: false,
};

function renderMathInMarkdownElement(element) {
    if (!element || element.dataset.mathRendering === 'true') {
        return;
    }

    /*
     * Do not pass KaTeX-generated HTML back into KaTeX.
     * When Alpine x-html replaces the note content, the new content has no
     * .katex descendants, so rendering proceeds normally.
     */
    if (element.querySelector('.katex')) {
        return;
    }

    element.dataset.mathRendering = 'true';

    try {
        renderMathInElement(element, mathOptions);
    } finally {
        delete element.dataset.mathRendering;
    }
}

function renderMarkdownMath(root = document) {
    const elements = [];

    if (root instanceof Element && root.matches('.markdown-content')) {
        elements.push(root);
    }

    if (root.querySelectorAll) {
        elements.push(...root.querySelectorAll('.markdown-content'));
    }

    elements.forEach(renderMathInMarkdownElement);
}

window.renderMarkdownMath = renderMarkdownMath;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        renderMarkdownMath();
    });
} else {
    renderMarkdownMath();
}

Alpine.start();