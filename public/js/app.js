(() => {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const STATUS_TEXT = {
        200: 'OK',
        201: 'Created',
        204: 'No Content',
        302: 'Found',
        400: 'Bad Request',
        401: 'Unauthorized',
        403: 'Forbidden',
        404: 'Not Found',
        405: 'Method Not Allowed',
        419: 'Page Expired',
        422: 'Unprocessable Content',
        429: 'Too Many Requests',
        500: 'Server Error',
    };

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));

    const statusLabel = (status) => `${status} ${STATUS_TEXT[status] || ''}`.trim();

    const statusClass = (status) => {
        if (status >= 200 && status < 300) return 'status-2xx';
        if (status >= 300 && status < 400) return 'status-3xx';
        if (status >= 400 && status < 500) return 'status-4xx';
        if (status >= 500) return 'status-5xx';
        return 'status-other';
    };

    const prettyBody = (body) => {
        if (body === null || body === undefined || body === '') return '';
        const text = String(body);

        try {
            return JSON.stringify(JSON.parse(text), null, 2);
        } catch {
            return text;
        }
    };

    const truncate = (text, limit = 24000) => {
        const value = String(text ?? '');
        return value.length > limit ? `${value.slice(0, limit)}\n\n… dipotong ${value.length - limit} karakter untuk tampilan.` : value;
    };

    const decodeTrace = (header) => {
        if (!header) return null;

        try {
            let value = header.replace(/-/g, '+').replace(/_/g, '/');
            value += '='.repeat((4 - (value.length % 4)) % 4);
            const binary = atob(value);
            const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
            return JSON.parse(new TextDecoder().decode(bytes));
        } catch {
            return null;
        }
    };

    const headerValue = (result, name) => {
        const wanted = name.toLowerCase();
        const pair = (result.headers || []).find(([key]) => key.toLowerCase() === wanted);
        return pair ? pair[1] : null;
    };

    const safePath = (path) => {
        const value = String(path || '').trim();

        if (!value.startsWith('/') || value.startsWith('//') || value.includes('\\') || /[\u0000-\u001f]/.test(value)) {
            throw new Error('Path harus berupa path internal yang dimulai dengan "/".');
        }

        const url = new URL(value, window.location.origin);

        if (url.origin !== window.location.origin) {
            throw new Error('Hanya path internal aplikasi ini yang boleh dipanggil dari panel demo.');
        }

        return url.pathname + url.search;
    };

    const renderTrace = (trace) => {
        if (!trace || !Array.isArray(trace.entries) || trace.entries.length === 0) {
            return '<p class="tiny" style="margin-top:.7rem;">Tidak ada header X-Demo-Trace pada respons ini.</p>';
        }

        const steps = trace.entries.map((entry) => {
            const status = entry.status || 'info';
            const meta = entry.meta && Object.keys(entry.meta).length
                ? Object.entries(entry.meta).map(([key, value]) => {
                    const display = typeof value === 'object' ? JSON.stringify(value) : String(value);
                    return `<span class="trace-chip">${escapeHtml(key)}: ${escapeHtml(display)}</span>`;
                }).join('')
                : '';

            return `<li class="trace-step" data-status="${escapeHtml(status)}">
                <span class="trace-stage">${escapeHtml(entry.stage || 'step')}</span>
                <span>
                    <span class="trace-message"><strong>${escapeHtml(entry.label || '')}</strong>${entry.message ? ' — ' + escapeHtml(entry.message) : ''}</span>
                    <span class="trace-meta">${meta}${entry.phase ? `<span class="trace-chip">fase: ${escapeHtml(entry.phase)}</span>` : ''}</span>
                </span>
            </li>`;
        }).join('');

        return `<div class="trace-panel">
            <div class="trace-title">
                <h4>Jejak request (X-Demo-Trace)</h4>
                <span class="tiny">${escapeHtml(trace.total_ms ?? '—')} ms · ${trace.entries.length} langkah</span>
            </div>
            <ol class="trace-list">${steps}</ol>
            <p class="tiny" style="margin:.45rem 0 0;"><a href="/code-review/mw-07">Baca cara trace dipasang →</a></p>
        </div>`;
    };

    const renderResponse = (result, options = {}) => {
        if (!result || result.ok === false) {
            return `<div class="warning" role="alert">
                <strong>Request gagal dijalankan.</strong>
                <p>${escapeHtml(result?.error || 'Terjadi kesalahan jaringan.')}</p>
            </div>`;
        }

        const requestPath = result.requestPath || result.requestUrl || '';
        const status = result.status;
        const body = prettyBody(result.body);
        const shownBody = body === ''
            ? '<span class="muted">(body kosong)</span>'
            : escapeHtml(truncate(body));
        const redirectInfo = result.finalUrl && result.finalUrl !== result.requestUrl
            ? `<p class="tiny">Browser mengikuti redirect ke <span class="mono">${escapeHtml(result.finalUrl)}</span>.</p>`
            : '';
        const headerRows = (result.headers || []).map(([name, value]) => {
            const shown = name.toLowerCase() === 'x-demo-trace'
                ? '<span class="muted">(base64 JSON — diterjemahkan di bawah)</span>'
                : escapeHtml(value);
            return `<tr><th>${escapeHtml(name)}</th><td>${shown}</td></tr>`;
        }).join('');
        const trace = Object.prototype.hasOwnProperty.call(options, 'trace')
            ? options.trace
            : decodeTrace(headerValue(result, 'x-demo-trace'));
        const elapsed = result.elapsed !== undefined ? `${result.elapsed.toFixed(0)} ms` : '—';

        return `<div class="request-summary">
            <div class="summary-box">
                <h4>Request</h4>
                <div class="summary-method">
                    <span class="method-badge method-${escapeHtml(result.method)}">${escapeHtml(result.method)}</span>
                    <span class="summary-url">${escapeHtml(requestPath)}</span>
                </div>
                ${result.role ? `<p class="tiny" style="margin:.35rem 0 0;">Peran request: <strong>${escapeHtml(result.role)}</strong></p>` : ''}
            </div>
            <div class="summary-box">
                <h4>Response</h4>
                <div class="response-head">
                    <span class="status-badge ${statusClass(status)}">${escapeHtml(statusLabel(status))}</span>
                    <span class="response-meta">${escapeHtml(elapsed)}</span>
                </div>
                ${redirectInfo}
            </div>
        </div>
        <details class="response-section" ${options.openHeaders ? 'open' : ''}>
            <summary class="tiny" style="cursor:pointer;font-weight:700;color:var(--ink-soft);">Header respons (${(result.headers || []).length})</summary>
            <div class="table-wrap" style="margin-top:.35rem;">
                <table class="headers-table"><tbody>${headerRows || '<tr><td colspan="2" class="tiny">Tidak ada header yang terbaca.</td></tr>'}</tbody></table>
            </div>
        </details>
        <div class="response-section">
            <h4>Body respons</h4>
            <pre class="response-body">${shownBody}</pre>
        </div>
        ${renderTrace(trace)}`;
    };

    const renderLoading = (label = 'Mengirim request…') => `<p class="loading">${escapeHtml(label)}</p>`;

    const runFetch = async ({ path, method = 'GET', body = null, headers = {}, role = null, noCsrf = false }) => {
        let normalized;

        try {
            normalized = safePath(path);
        } catch (error) {
            return { ok: false, error: error.message, method, requestPath: path };
        }

        const upperMethod = String(method || 'GET').toUpperCase();
        const requestHeaders = new Headers({
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        });

        if (role && role !== 'guest') {
            requestHeaders.set('X-Demo-Role', role);
        }

        Object.entries(headers || {}).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                requestHeaders.set(key, String(value));
            }
        });

        if (!noCsrf && !normalized.startsWith('/api/') && !['GET', 'HEAD', 'OPTIONS'].includes(upperMethod) && csrfToken) {
            requestHeaders.set('X-CSRF-TOKEN', csrfToken);
        }

        let payload = null;

        if (!['GET', 'HEAD'].includes(upperMethod) && body !== null && body !== undefined && String(body).trim() !== '') {
            let rawBody = body;

            if (typeof rawBody !== 'string') {
                rawBody = JSON.stringify(rawBody);
            } else {
                rawBody = rawBody.trim();
                try {
                    JSON.parse(rawBody);
                } catch {
                    return { ok: false, error: 'Body bukan JSON yang valid. Perbaiki dulu sebelum mengirim.', method: upperMethod, requestPath: normalized };
                }
            }

            payload = rawBody;
            requestHeaders.set('Content-Type', 'application/json');
        }

        const started = performance.now();

        try {
            const response = await fetch(normalized, {
                method: upperMethod,
                headers: requestHeaders,
                body: payload,
                credentials: 'same-origin',
                redirect: 'follow',
            });
            const text = await response.text();
            const responseHeaders = [];
            response.headers.forEach((value, name) => responseHeaders.push([name, value]));

            return {
                ok: true,
                method: upperMethod,
                requestPath: normalized,
                requestUrl: new URL(normalized, window.location.origin).href,
                finalUrl: response.url,
                status: response.status,
                statusText: response.statusText,
                headers: responseHeaders,
                body: text,
                elapsed: performance.now() - started,
                role: role || null,
            };
        } catch (error) {
            return {
                ok: false,
                error: error.message || 'Fetch gagal. Pastikan server masih berjalan.',
                method: upperMethod,
                requestPath: normalized,
                elapsed: performance.now() - started,
            };
        }
    };

    const showResult = (target, html) => {
        if (!target) return;
        target.hidden = false;
        target.innerHTML = html;
    };

    const runInto = async (target, options, label) => {
        showResult(target, renderLoading(label));
        const result = await runFetch(options);
        showResult(target, renderResponse(result));

        return result;
    };

    const parseJson = (text) => {
        try {
            return JSON.parse(text);
        } catch {
            return null;
        }
    };

    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value) || 0);

    const initRunner = () => {
        document.querySelectorAll('[data-demo-runner]').forEach((runner) => {
            const methodSelect = runner.querySelector('[data-runner-method]');
            const pathInput = runner.querySelector('[data-runner-path]');
            const bodyInput = runner.querySelector('[data-runner-body]');
            const roleSelect = runner.querySelector('[data-runner-role]');
            const resultBox = runner.querySelector('[data-runner-result]');
            const sendButton = runner.querySelector('[data-runner-send]');
            const resetButton = runner.querySelector('[data-runner-reset]');

            if (!methodSelect || !pathInput || !resultBox) return;

            const defaults = {
                method: runner.dataset.defaultMethod || methodSelect.value || 'GET',
                path: runner.dataset.defaultPath || pathInput.value,
                body: runner.dataset.defaultBody || bodyInput?.value || '',
                role: roleSelect?.value || runner.dataset.role || '',
                headers: parseJson(runner.dataset.headers || '{}') || {},
            };

            let activeBody = defaults.body;
            let activeRole = defaults.role;
            let activeHeaders = { ...defaults.headers };

            const applyRole = (role) => {
                const value = role || '';
                activeRole = value;
                if (roleSelect) roleSelect.value = value || 'guest';
            };

            const run = async () => {
                const body = bodyInput ? bodyInput.value : activeBody;
                const result = await runInto(resultBox, {
                    method: methodSelect.value,
                    path: pathInput.value,
                    body,
                    role: activeRole,
                    headers: activeHeaders,
                }, 'Mengirim request dari panel…');

                if (result && result.ok === false && !result.status) {
                    showResult(resultBox, renderResponse(result));
                }
            };

            const reset = () => {
                methodSelect.value = defaults.method;
                pathInput.value = defaults.path;
                if (bodyInput) bodyInput.value = defaults.body;
                activeBody = defaults.body;
                activeHeaders = { ...defaults.headers };
                if (roleSelect) roleSelect.value = defaults.role || 'guest';
                activeRole = roleSelect ? roleSelect.value : defaults.role;
                resultBox.hidden = true;
                resultBox.innerHTML = '';
            };

            runner.__demoRun = run;
            runner.__demoApplyPreset = (preset) => {
                if (preset.method) methodSelect.value = String(preset.method).toUpperCase();
                if (preset.path) pathInput.value = preset.path;

                if (Object.prototype.hasOwnProperty.call(preset, 'body')) {
                    if (preset.body === null || preset.body === '') {
                        activeBody = '';
                        if (bodyInput) bodyInput.value = '';
                    } else if (typeof preset.body === 'string') {
                        activeBody = preset.body;
                        if (bodyInput) bodyInput.value = preset.body;
                    } else {
                        activeBody = JSON.stringify(preset.body, null, 2);
                        if (bodyInput) bodyInput.value = activeBody;
                    }
                }

                if (preset.role) applyRole(preset.role);
                if (preset.headers) activeHeaders = { ...preset.headers };
            };

            sendButton?.addEventListener('click', run);
            resetButton?.addEventListener('click', reset);

            roleSelect?.addEventListener('change', () => {
                activeRole = roleSelect.value;
            });
        });

        document.querySelectorAll('[data-runner-preset]').forEach((button) => {
            button.addEventListener('click', () => {
                const runner = button.closest('.demo-card')?.querySelector('[data-demo-runner]')
                    || button.closest('[data-demo-runner]');

                if (!runner || !runner.__demoApplyPreset) return;

                let preset;
                try {
                    preset = JSON.parse(button.dataset.runnerPreset || '{}');
                } catch {
                    return;
                }

                runner.__demoApplyPreset(preset);
                runner.__demoRun?.();
            });
        });
    };

    const initRouteFilter = () => {
        const input = document.querySelector('[data-route-filter]');
        const rows = [...document.querySelectorAll('[data-route-row]')];
        const count = document.querySelector('[data-route-count]');

        if (!input || !rows.length) return;

        const update = () => {
            const query = input.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const matches = !query || (row.dataset.search || '').includes(query);
                row.hidden = !matches;
                if (matches) visible += 1;
            });

            if (count) count.textContent = `${visible} dari ${rows.length} route demo ditampilkan.`;
        };

        input.addEventListener('input', update);
    };

    const initTabs = () => {
        document.querySelectorAll('[data-tabs]').forEach((tabs) => {
            const container = tabs.closest('.card') || tabs.parentElement;
            const buttons = [...tabs.querySelectorAll('[data-tab-button]')];
            const panels = [...container.querySelectorAll('[data-tab-panel]')];

            const select = (key) => {
                buttons.forEach((button) => {
                    const active = button.dataset.tabButton === key;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.tabPanel !== key;
                });
            };

            buttons.forEach((button) => {
                button.addEventListener('click', () => select(button.dataset.tabButton));
            });
        });
    };

    const initTerminals = () => {
        document.querySelectorAll('[data-terminal]').forEach((terminal) => {
            const tabs = [...terminal.querySelectorAll('[data-terminal-tab]')];
            const panels = [...terminal.querySelectorAll('[data-terminal-panel]')];
            const status = terminal.querySelector('[data-terminal-status]');
            const copy = terminal.querySelector('[data-terminal-copy]');
            let active = 0;

            if (!tabs.length || !panels.length) return;

            const select = (index) => {
                active = index;
                tabs.forEach((tab, i) => {
                    tab.classList.toggle('is-active', i === index);
                    tab.setAttribute('aria-selected', i === index ? 'true' : 'false');
                });
                panels.forEach((panel, i) => {
                    panel.hidden = i !== index;
                });
                if (status) status.textContent = `Tab aktif: ${tabs[index].textContent.trim()}.`;
            };

            tabs.forEach((tab, index) => tab.addEventListener('click', () => select(index)));

            copy?.addEventListener('click', async () => {
                const text = panels[active]?.textContent || '';
                try {
                    await navigator.clipboard.writeText(text);
                    if (status) status.textContent = 'Perintah disalin ke clipboard.';
                } catch {
                    const helper = document.createElement('textarea');
                    helper.value = text;
                    helper.setAttribute('readonly', 'readonly');
                    helper.style.position = 'fixed';
                    helper.style.opacity = '0';
                    document.body.appendChild(helper);
                    helper.select();
                    document.execCommand('copy');
                    helper.remove();
                    if (status) status.textContent = 'Perintah disalin ke clipboard.';
                }

                window.setTimeout(() => {
                    if (status) status.textContent = `Tab aktif: ${tabs[active].textContent.trim()}.`;
                }, 2200);
            });

            select(0);
        });
    };

    const initMobileNav = () => {
        const toggle = document.querySelector('[data-menu-toggle]');
        const sidebar = document.getElementById('sidebar');

        if (!toggle || !sidebar) return;

        const setOpen = (open) => {
            document.body.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle.addEventListener('click', () => setOpen(!document.body.classList.contains('nav-open')));
        sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setOpen(false);
        });
    };

    const initConfirmForms = () => {
        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });
    };

    const initFaq = () => {
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-faq-run]');
            if (!button) return;

            const item = button.closest('.faq-item');
            const target = item?.querySelector('[data-faq-result]');
            if (!target) return;

            let scenario;
            try {
                scenario = JSON.parse(button.dataset.scenario || '{}');
            } catch {
                showResult(target, '<div class="warning">Skenario tidak valid.</div>');
                return;
            }

            button.disabled = true;
            showResult(target, renderLoading('Menjalankan skenario…'));

            try {
                if (scenario.kind === 'csrf') {
                    await runCsrfScenario(scenario, target);
                } else if (scenario.kind === 'throttle') {
                    await runThrottle(target);
                } else if (scenario.kind === 'signed') {
                    await runSigned(target);
                } else {
                    const result = await runFetch({
                        method: scenario.method || 'GET',
                        path: scenario.path,
                        body: scenario.body,
                        headers: scenario.headers || {},
                        role: scenario.role || null,
                    });
                    showResult(target, renderResponse(result));
                }
            } finally {
                button.disabled = false;
            }
        });
    };

    const runCsrfScenario = async (scenario, target) => {
        const payload = scenario.body || { name: 'tanpa token' };
        const without = await runFetch({
            method: 'POST',
            path: scenario.path,
            body: payload,
            noCsrf: true,
        });
        const withToken = await runFetch({
            method: 'POST',
            path: scenario.path,
            body: payload,
        });

        showResult(target, `
            <p class="tiny" style="margin-bottom:.5rem;"><strong>Perbandingan:</strong> request pertama sengaja tidak membawa token; request kedua memakai token dari meta tag.</p>
            <div class="summary-box" style="margin-bottom:.6rem;"><h4>1. Tanpa token</h4>${renderResponse(without)}</div>
            <div class="summary-box"><h4>2. Dengan token</h4>${renderResponse(withToken)}</div>
        `);
    };

    const initCsrfButtons = () => {
        document.querySelectorAll('[data-csrf-mode]').forEach((button) => {
            button.addEventListener('click', async () => {
                const card = button.closest('.demo-card') || document;
                const target = card.querySelector('[data-csrf-result]');
                if (!target) return;

                button.disabled = true;
                showResult(target, renderLoading('Mengirim request…'));
                const without = button.dataset.csrfMode === 'without';
                const result = await runFetch({
                    method: 'POST',
                    path: '/demo/csrf-test',
                    body: { name: without ? 'tanpa token' : 'dengan token' },
                    noCsrf: without,
                });
                showResult(target, renderResponse(result));
                button.disabled = false;
            });
        });
    };

    const runThrottle = async (target) => {
        const results = [];
        showResult(target, renderLoading('Mengirim enam request berurutan…'));

        for (let i = 0; i < 6; i += 1) {
            const result = await runFetch({ method: 'GET', path: '/api/demo/throttle' });
            results.push(result);
        }

        const list = results.map((result, index) => {
            if (!result.ok) {
                return `<li>Request ${index + 1}: <strong>gagal jaringan</strong></li>`;
            }
            const remaining = headerValue(result, 'x-ratelimit-remaining') ?? '—';
            const retry = headerValue(result, 'retry-after');
            return `<li>Request ${index + 1}: <span class="status-badge ${statusClass(result.status)}">${escapeHtml(statusLabel(result.status))}</span>
                <span class="tiny">sisa: ${escapeHtml(remaining)}${retry ? ` · Retry-After: ${escapeHtml(retry)} detik` : ''}</span></li>`;
        }).join('');

        const detailResult = results.find((result) => result.status === 429) || results[results.length - 1];
        const externalDetail = document.querySelector('[data-throttle-detail]');
        const detailHtml = renderResponse(detailResult);
        showResult(target, `<ol class="experiment-list">${list}</ol>${externalDetail ? '' : detailHtml}`);

        if (externalDetail) showResult(externalDetail, detailHtml);
    };

    const pathFromUrl = (url) => {
        try {
            const parsed = new URL(url, window.location.origin);
            return parsed.pathname + parsed.search;
        } catch {
            return null;
        }
    };

    const runSigned = async (target) => {
        showResult(target, renderLoading('Membuat signed URL…'));
        const generated = await runFetch({ method: 'GET', path: '/api/demo/signed/generate' });
        const payload = generated.ok ? parseJson(generated.body) : null;
        const data = payload?.data || {};

        if (!generated.ok || !data.valid_url || !data.tampered_url) {
            showResult(target, renderResponse(generated));
            return;
        }

        const detail = document.querySelector('[data-signed-detail]');
        const validPath = pathFromUrl(data.valid_url);
        const tamperedPath = pathFromUrl(data.tampered_url);
        const valid = await runFetch({ method: 'GET', path: validPath });
        const tampered = await runFetch({ method: 'GET', path: tamperedPath });

        showResult(target, `
            <p class="tiny">Signature dibuat untuk host browser saat ini. URL valid dan URL yang satu karakternya diubah diuji berurutan.</p>
            <ul class="experiment-list">
                <li>Tanggal kedaluwarsa: <span class="mono">${escapeHtml(data.expires_in || '15 menit')}</span></li>
                <li>URL valid: <span class="status-badge ${statusClass(valid.status)}">${escapeHtml(statusLabel(valid.status))}</span></li>
                <li>URL tampered: <span class="status-badge ${statusClass(tampered.status)}">${escapeHtml(statusLabel(tampered.status))}</span></li>
            </ul>
            <div class="quick-examples">
                <a class="btn btn-small btn-ghost" href="${escapeHtml(data.valid_url)}" target="_blank" rel="noreferrer">Buka URL valid</a>
                <a class="btn btn-small btn-ghost" href="${escapeHtml(data.tampered_url)}" target="_blank" rel="noreferrer">Buka URL tampered</a>
            </div>
            <div class="summary-box" style="margin-top:.6rem;"><h4>URL valid</h4>${renderResponse(valid)}</div>
            <div class="summary-box" style="margin-top:.6rem;"><h4>URL tampered</h4>${renderResponse(tampered)}</div>
        `);
        showResult(detail, renderResponse(generated));
    };

    const initThrottle = () => {
        const button = document.querySelector('[data-throttle-run]');
        const target = document.querySelector('[data-throttle-result]');
        if (!button || !target) return;

        button.addEventListener('click', async () => {
            button.disabled = true;
            await runThrottle(target);
            button.disabled = false;
        });
    };

    const initSigned = () => {
        const button = document.querySelector('[data-signed-run]');
        const target = document.querySelector('[data-signed-result]');
        if (!button || !target) return;

        button.addEventListener('click', async () => {
            button.disabled = true;
            await runSigned(target);
            button.disabled = false;
        });
    };

    const initMatrix = () => {
        const cells = [...document.querySelectorAll('[data-matrix-url]')];
        const runAll = document.querySelector('[data-matrix-run]');
        const panel = document.querySelector('[data-matrix-result]');

        if (!cells.length) return;

        const execute = async (cell) => {
            const url = cell.dataset.matrixUrl;
            const role = cell.dataset.matrixRole;
            cell.disabled = true;
            cell.classList.add('cell-loading');
            cell.textContent = '…';
            const result = await runFetch({ method: 'GET', path: url, role });
            cell.classList.remove('cell-loading');
            cell.classList.toggle('cell-200', result.status === 200);
            cell.classList.toggle('cell-401', result.status === 401);
            cell.classList.toggle('cell-403', result.status === 403);
            cell.textContent = result.ok ? String(result.status) : 'ERR';
            cell.title = result.ok ? statusLabel(result.status) : (result.error || 'Gagal');
            cell.disabled = false;
            return result;
        };

        cells.forEach((cell) => {
            cell.addEventListener('click', async () => {
                const result = await execute(cell);
                if (panel) showResult(panel, renderResponse(result));
            });
        });

        runAll?.addEventListener('click', async () => {
            runAll.disabled = true;
            let last = null;
            for (const cell of cells) {
                last = await execute(cell);
            }
            if (panel && last) showResult(panel, renderResponse(last));
            runAll.disabled = false;
        });
    };

    const initResponseDemo = () => {
        const root = document.querySelector('[data-response-demo]');
        if (!root) return;

        const target = root.querySelector('[data-response-result]');
        const buttons = [...root.querySelectorAll('[data-response-type]')];
        const labels = {
            view: 'View HTML',
            json: 'JSON',
            redirect: 'Redirect',
            download: 'Download',
            'no-content': 'No Content',
        };

        buttons.forEach((button) => {
            button.addEventListener('click', async () => {
                const type = button.dataset.responseType;
                buttons.forEach((item) => { item.disabled = true; });
                showResult(target, renderLoading(`Memeriksa response ${labels[type] || type}…`));

                const inspected = await runFetch({ method: 'GET', path: `/api/demo/response/inspect/${type}` });
                if (inspected.ok === false) {
                    showResult(target, renderResponse(inspected));
                } else {
                    const payload = parseJson(inspected.body);
                    const data = payload?.data;

                    if (!data) {
                        showResult(target, renderResponse(inspected));
                    } else {
                        const targetResult = {
                            ok: true,
                            method: 'GET',
                            requestPath: `/api/demo/response/${type}`,
                            requestUrl: new URL(`/api/demo/response/${type}`, window.location.origin).href,
                            finalUrl: new URL(`/api/demo/response/${type}`, window.location.origin).href,
                            status: data.status_code,
                            statusText: '',
                            headers: Object.entries(data.headers || {}),
                            body: data.body_preview || '',
                            elapsed: 0,
                        };
                        const trace = decodeTrace(headerValue(inspected, 'x-demo-trace'));
                        showResult(target, `
                            <div class="notice" style="margin-bottom:.55rem;">
                                Target response diperiksa tanpa diikuti browser. Ukuran body: <strong>${escapeHtml(data.body_size)} byte</strong>.
                                ${data.target_url ? `Header Location: <span class="mono">${escapeHtml(data.target_url)}</span>.` : ''}
                            </div>
                            ${renderResponse(targetResult, { trace })}
                            <p class="tiny">Inspector sendiri mengembalikan HTTP 200; status di atas adalah status target.</p>
                        `);
                    }
                }

                buttons.forEach((item) => { item.disabled = false; });
            });
        });
    };

    const initProductApp = () => {
        const root = document.querySelector('[data-product-app]');
        if (!root) return;

        const rows = root.querySelector('[data-product-rows]');
        const pagination = root.querySelector('[data-product-pagination]');
        const responsePanel = root.querySelector('[data-product-response]');
        const form = root.querySelector('[data-product-form]');
        const search = root.querySelector('[data-product-search]');
        const searchButton = root.querySelector('[data-product-search-run]');
        const refreshButton = root.querySelector('[data-product-refresh]');
        const cancelButton = root.querySelector('[data-product-cancel]');
        const formTitle = root.querySelector('[data-product-form-title]');
        const formNote = root.querySelector('[data-product-form-note]');
        const submit = root.querySelector('[data-product-submit]');
        const idInput = root.querySelector('[data-product-id]');
        const fields = Object.fromEntries(
            [...root.querySelectorAll('[data-product-field]')].map((field) => [field.dataset.productField, field])
        );

        let productsById = new Map();
        let editingId = null;
        let keyword = '';
        let currentPage = 1;
        let lastChangedId = null;
        let loading = false;

        const showPanel = (result) => {
            if (!responsePanel) return;
            responsePanel.hidden = false;
            responsePanel.innerHTML = renderResponse(result);
        };

        const resetForm = () => {
            editingId = null;
            idInput.value = '';
            form.reset();
            if (formTitle) formTitle.textContent = 'Tambah produk';
            if (formNote) formNote.innerHTML = 'Form mengirim POST ke <code>/api/products</code>. Kolom yang kosong akan ditolak validasi.';
            if (submit) submit.textContent = 'Tambah via API';
        };

        const fillForm = (id) => {
            const product = productsById.get(String(id));
            if (!product) return;

            editingId = product.id;
            idInput.value = product.id;
            fields.name.value = product.name || '';
            fields.category.value = product.category || '';
            fields.price.value = product.price ?? '';
            fields.stock.value = product.stock ?? '';
            fields.description.value = product.description || '';
            if (formTitle) formTitle.textContent = `Edit produk #${product.id}`;
            if (formNote) formNote.innerHTML = 'Form mengirim PATCH ke <code>/api/products/{id}</code>. Hanya field yang ada di body yang diubah.';
            if (submit) submit.textContent = 'Simpan perubahan';
            fields.name.focus();
        };

        const renderPagination = (meta) => {
            if (!pagination) return;
            if (!meta || keyword) {
                pagination.innerHTML = keyword ? '<span class="tiny">Mode pencarian: semua hasil ditampilkan.</span>' : '';
                return;
            }

            const page = Number(meta.current_page || 1);
            const last = Number(meta.last_page || 1);
            const buttons = [];

            if (page > 1) buttons.push(`<button type="button" class="btn btn-small btn-ghost" data-product-page="${page - 1}">← Sebelumnya</button>`);
             for (let i = 1; i <= last; i += 1) {
                if (last > 7 && Math.abs(i - page) > 2 && i !== 1 && i !== last) continue;
                buttons.push(i === page
                    ? `<span class="current" aria-current="page">${i}</span>`
                    : `<button type="button" class="btn btn-small btn-ghost" data-product-page="${i}">${i}</button>`);
            }
            if (page < last) buttons.push(`<button type="button" class="btn btn-small btn-ghost" data-product-page="${page + 1}">Berikutnya →</button>`);
            buttons.push(`<span class="tiny">${meta.total ?? 0} total · ${meta.per_page ?? 10} per halaman</span>`);
            pagination.innerHTML = buttons.join(' ');
        };

        const renderRows = (items) => {
            if (!rows) return;
            if (!items.length) {
                rows.innerHTML = '<tr><td colspan="6" class="empty-row">Tidak ada produk yang cocok.</td></tr>';
                return;
            }

            rows.innerHTML = items.map((product) => {
                const changed = lastChangedId !== null && String(lastChangedId) === String(product.id);
                return `<tr class="${changed ? 'highlight-row' : ''}" data-product-row="${escapeHtml(product.id)}">
                    <td class="mono">#${escapeHtml(product.id)}</td>
                    <td><strong>${escapeHtml(product.name)}</strong><div class="table-sub">${escapeHtml(product.description || '—')}</div></td>
                    <td><span class="chip">${escapeHtml(product.category)}</span></td>
                    <td>${escapeHtml(formatRupiah(product.price))}</td>
                    <td>${escapeHtml(product.stock)}</td>
                    <td><div class="product-actions">
                        <button type="button" class="btn btn-small btn-ghost" data-product-edit="${escapeHtml(product.id)}">Edit</button>
                        <button type="button" class="btn btn-small btn-danger" data-product-delete="${escapeHtml(product.id)}">Hapus</button>
                    </div></td>
                </tr>`;
            }).join('');
            lastChangedId = null;
        };

        const load = async (page = 1, searchKeyword = '') => {
            if (loading) return;
            loading = true;
            currentPage = page;
            keyword = searchKeyword;
            const path = keyword
                ? `/api/demo/search?q=${encodeURIComponent(keyword)}`
                : `/api/products?page=${page}`;
            if (rows) rows.innerHTML = '<tr><td colspan="6" class="empty-row">Memuat produk…</td></tr>';
            if (responsePanel) {
                responsePanel.hidden = false;
                responsePanel.innerHTML = renderLoading('Memuat data produk…');
            }

            const result = await runFetch({ method: 'GET', path });
            showPanel(result);

            if (result.ok) {
                const payload = parseJson(result.body);
                const items = Array.isArray(payload?.data) ? payload.data : [];
                productsById = new Map(items.map((item) => [String(item.id), item]));
                renderRows(items);
                renderPagination(payload?.meta);
            } else if (rows) {
                rows.innerHTML = `<tr><td colspan="6" class="empty-row">Gagal memuat data: ${escapeHtml(result.error)}</td></tr>`;
            }

            loading = false;
        };

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (submit?.disabled) return;

            const payload = {
                name: fields.name.value.trim(),
                category: fields.category.value.trim(),
                price: fields.price.value === '' ? null : Number(fields.price.value),
                stock: fields.stock.value === '' ? null : Number(fields.stock.value),
                description: fields.description.value.trim(),
            };
            const method = editingId ? 'PATCH' : 'POST';
            const path = editingId ? `/api/products/${editingId}` : '/api/products';

            submit.disabled = true;
            const result = await runFetch({ method, path, body: payload });
            showPanel(result);

            if (result.ok && [200, 201].includes(result.status)) {
                const saved = parseJson(result.body)?.data;
                const wasEditing = editingId !== null;
                lastChangedId = saved?.id ?? editingId;
                resetForm();
                await load(wasEditing ? currentPage : 1, keyword);
            }

            submit.disabled = false;
        });

        rows?.addEventListener('click', async (event) => {
            const editButton = event.target.closest('[data-product-edit]');
            const deleteButton = event.target.closest('[data-product-delete]');

            if (editButton) {
                fillForm(editButton.dataset.productEdit);
                return;
            }

            if (deleteButton) {
                const id = deleteButton.dataset.productDelete;
                const product = productsById.get(String(id));
                if (!window.confirm(`Hapus produk #${id}${product ? ` (${product.name})` : ''}?`)) return;

                deleteButton.disabled = true;
                const result = await runFetch({ method: 'DELETE', path: `/api/products/${id}` });
                showPanel(result);
                if (result.ok && result.status === 204) {
                    if (editingId && String(editingId) === String(id)) resetForm();
                    await load(currentPage, keyword);
                }
                deleteButton.disabled = false;
            }
        });

        pagination?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-product-page]');
            if (button) load(Number(button.dataset.productPage), '');
        });

        searchButton?.addEventListener('click', () => load(1, search?.value.trim() || ''));
        search?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                load(1, search.value.trim());
            }
        });
        refreshButton?.addEventListener('click', () => {
            if (search) search.value = '';
            load(1, '');
        });
        cancelButton?.addEventListener('click', resetForm);

        resetForm();
        load(1, '');
    };

    const init = () => {
        initRunner();
        initRouteFilter();
        initTabs();
        initTerminals();
        initMobileNav();
        initConfirmForms();
        initFaq();
        initCsrfButtons();
        initThrottle();
        initSigned();
        initMatrix();
        initResponseDemo();
        initProductApp();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
