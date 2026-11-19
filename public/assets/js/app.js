(() => {
    const showMessage = (form, message, success) => {
        const host = form.dataset.agendaForm !== undefined ? document.querySelector('main.container') : form;
        let notice = host.querySelector('[data-form-message]');
        if (!notice) {
            notice = document.createElement('p');
            notice.dataset.formMessage = '';
            host.prepend(notice);
        }
        notice.className = `notice ${success ? 'success' : 'error'}`;
        notice.setAttribute('role', success ? 'status' : 'alert');
        notice.textContent = message;
    };

    document.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.matches('[data-confirm]') && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }
        if (!form.matches('[data-ajax-form]') || typeof window.fetch !== 'function') return;
        event.preventDefault();
        if (form.dataset.submitting === 'true') return;

        const button = event.submitter || form.querySelector('button[type="submit"], button:not([type])');
        const originalText = button?.textContent;
        form.dataset.submitting = 'true';
        if (button) {
            button.disabled = true;
            button.textContent = 'Enviando…';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST', body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            let result;
            try { result = await response.json(); } catch { throw new Error('Resposta inesperada do servidor.'); }
            if (!response.ok || !result || typeof result.success !== 'boolean') {
                throw new Error(result?.message || 'Não foi possível concluir a operação.');
            }
            if (!result.success) {
                showMessage(form, result.message || 'Não foi possível concluir a operação.', false);
                return;
            }

            showMessage(form, result.message || 'Operação concluída.', true);
            if (form.dataset.loginForm !== undefined) {
                window.location.assign(result.data?.redirect || form.action);
                return;
            }

            if (form.dataset.agendaForm !== undefined) {
                form.reset();
                try {
                    const page = await fetch(window.location.href, { headers: { 'Accept': 'text/html' }, credentials: 'same-origin' });
                    if (!page.ok) throw new Error();
                    const doc = new DOMParser().parseFromString(await page.text(), 'text/html');
                    const replacements = ['.week', '[data-plan-list]'].map(selector => ({
                        current: document.querySelector(selector), updated: doc.querySelector(selector)
                    }));
                    if (replacements.some(item => !item.current || !item.updated)) throw new Error();
                    replacements.forEach(item => item.current.replaceWith(item.updated));
                } catch {
                    showMessage(form, `${result.message} A operação foi salva, mas a agenda não pôde ser atualizada. Atualize a página.`, true);
                }
            } else {
                form.reset();
            }
        } catch (error) {
            showMessage(form, error.message || 'Falha de conexão. Tente novamente.', false);
        } finally {
            delete form.dataset.submitting;
            if (button) {
                button.disabled = false;
                button.textContent = originalText;
            }
        }
    });
})();
