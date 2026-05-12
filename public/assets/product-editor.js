(() => {
    const editors = document.querySelectorAll('[data-rich-editor]');
    const activeButtonClasses = ['bg-blue-50', 'text-blue-700', 'border-blue-200'];
    const inactiveButtonClasses = ['bg-slate-50', 'text-slate-700', 'border-slate-200'];
    const fullscreenClasses = ['fixed', 'inset-3', 'z-[999]', 'rounded-[1.75rem]', 'shadow-2xl'];

    const normalize = (html) => html
        .replace(/<div><br><\/div>/gi, '')
        .replace(/&nbsp;/gi, ' ')
        .trim();

    const escapeHtml = (value) => value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const updateButtonStates = (container) => {
        container.querySelectorAll('[data-command]').forEach((button) => {
            const command = button.dataset.command;
            if (!command) {
                return;
            }

            try {
                const isActive = document.queryCommandState(command);
                button.classList.toggle('is-active', isActive);
                button.classList.toggle(activeButtonClasses[0], isActive);
                button.classList.toggle(activeButtonClasses[1], isActive);
                button.classList.toggle(activeButtonClasses[2], isActive);
                button.classList.toggle(inactiveButtonClasses[0], !isActive);
                button.classList.toggle(inactiveButtonClasses[1], !isActive);
                button.classList.toggle(inactiveButtonClasses[2], !isActive);
            } catch (error) {
                button.classList.remove('is-active');
                button.classList.remove(...activeButtonClasses);
                button.classList.add(...inactiveButtonClasses);
            }
        });
    };

    editors.forEach((container) => {
        const input = container.querySelector('.rich-editor-input');
        const surface = container.querySelector('[data-editor-surface]');
        const form = container.closest('form');

        if (!(input instanceof HTMLTextAreaElement) || !(surface instanceof HTMLElement)) {
            return;
        }

        const syncToInput = () => {
            input.value = normalize(surface.innerHTML);
            updateButtonStates(container);
        };

        const insertHtml = (html) => {
            surface.focus();
            document.execCommand('insertHTML', false, html);
            syncToInput();
        };

        surface.innerHTML = input.value || '';

        container.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const command = button.dataset.command;
                if (!command) {
                    return;
                }

                surface.focus();
                document.execCommand(command, false);
                syncToInput();
            });
        });

        container.querySelectorAll('[data-action="link"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const href = window.prompt('Enter a URL');
                if (!href) {
                    return;
                }

                surface.focus();
                document.execCommand('createLink', false, href);
                syncToInput();
            });
        });

        container.querySelectorAll('[data-action="clear"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                surface.innerHTML = '';
                syncToInput();
            });
        });

        container.querySelectorAll('[data-action="image"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const src = window.prompt('Enter an image URL');
                if (!src) {
                    return;
                }

                const alt = window.prompt('Enter image alt text') || '';
                insertHtml(`<p><img src="${escapeHtml(src)}" alt="${escapeHtml(alt)}"></p>`);
            });
        });

        container.querySelectorAll('[data-action="media"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const href = window.prompt('Enter a media URL');
                if (!href) {
                    return;
                }

                insertHtml(`<p><a href="${escapeHtml(href)}">Media link</a></p>`);
            });
        });

        container.querySelectorAll('[data-action="code"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const selection = window.getSelection();
                const selectedText = selection ? selection.toString() : '';
                const content = selectedText || 'Code snippet';

                insertHtml(`<pre><code>${escapeHtml(content)}</code></pre>`);
            });
        });

        container.querySelectorAll('[data-action="fullscreen"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                container.classList.toggle('is-fullscreen');
                fullscreenClasses.forEach((className) => container.classList.toggle(className));
                document.body.classList.toggle('overflow-hidden');
                surface.focus();
            });
        });

        surface.addEventListener('input', syncToInput);
        surface.addEventListener('keyup', () => updateButtonStates(container));
        surface.addEventListener('mouseup', () => updateButtonStates(container));
        surface.addEventListener('blur', syncToInput);

        if (form) {
            form.addEventListener('submit', syncToInput);
        }

        syncToInput();
    });
})();
