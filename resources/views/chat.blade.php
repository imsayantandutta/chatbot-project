<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel Chatbot') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #edf1f7;
            --panel: rgb(255 255 255 / 88%);
            --panel-solid: #ffffff;
            --text: #172033;
            --muted: #657084;
            --line: #d9e0eb;
            --primary: #2167d8;
            --primary-dark: #174ca7;
            --accent: #00a19a;
            --assistant: #ffffff;
            --user: #dff8ed;
            --danger: #b42318;
            --shadow: 0 24px 70px rgb(27 39 66 / 14%);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgb(0 161 154 / 18%), transparent 34rem),
                radial-gradient(circle at bottom right, rgb(33 103 216 / 16%), transparent 32rem),
                var(--bg);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        button,
        textarea {
            font: inherit;
        }

        .app {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            gap: 24px;
            min-height: 100vh;
            padding: 24px;
        }

        .sidebar {
            background: #102033;
            border: 1px solid rgb(255 255 255 / 12%);
            border-radius: 8px;
            box-shadow: var(--shadow);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: calc(100vh - 48px);
            overflow: hidden;
            padding: 26px;
            position: relative;
        }

        .sidebar::before {
            background: linear-gradient(135deg, rgb(0 161 154 / 36%), rgb(33 103 216 / 26%));
            content: "";
            inset: 0;
            position: absolute;
        }

        .sidebar > * {
            position: relative;
            z-index: 1;
        }

        .brand {
            align-items: center;
            display: flex;
            gap: 12px;
        }

        .brand-mark {
            align-items: center;
            background: #ffffff;
            border-radius: 8px;
            color: var(--primary);
            display: grid;
            flex: 0 0 44px;
            font-weight: 800;
            height: 44px;
            place-items: center;
            width: 44px;
        }

        h1 {
            font-size: 1.35rem;
            line-height: 1.2;
            margin: 0;
        }

        .subtitle {
            color: rgb(255 255 255 / 72%);
            font-size: 0.92rem;
            line-height: 1.55;
            margin: 28px 0 0;
        }

        .tips {
            display: grid;
            gap: 10px;
            margin-top: 28px;
        }

        .tip {
            background: rgb(255 255 255 / 10%);
            border: 1px solid rgb(255 255 255 / 12%);
            border-radius: 8px;
            color: rgb(255 255 255 / 84%);
            font-size: 0.9rem;
            line-height: 1.45;
            padding: 12px;
        }

        .status {
            align-items: center;
            color: rgb(255 255 255 / 78%);
            display: flex;
            font-size: 0.9rem;
            gap: 8px;
            margin-top: 28px;
        }

        .status-dot {
            background: #3ee089;
            border-radius: 50%;
            box-shadow: 0 0 0 6px rgb(62 224 137 / 14%);
            height: 8px;
            width: 8px;
        }

        .chat-shell {
            backdrop-filter: blur(18px);
            background: var(--panel);
            border: 1px solid rgb(255 255 255 / 78%);
            border-radius: 8px;
            box-shadow: var(--shadow);
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            min-height: calc(100vh - 48px);
            overflow: hidden;
        }

        .chat-header {
            align-items: center;
            background: rgb(255 255 255 / 72%);
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            padding: 18px 22px;
        }

        .chat-title {
            display: grid;
            gap: 4px;
        }

        .chat-title strong {
            font-size: 1rem;
        }

        .chat-title span,
        .model-pill {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .model-pill {
            background: #eef6ff;
            border: 1px solid #cfe0f8;
            border-radius: 999px;
            color: #1f5da9;
            padding: 8px 12px;
            white-space: nowrap;
        }

        main {
            min-height: 0;
            overflow-y: auto;
            padding: 24px;
        }

        .messages {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin: 0 auto;
            max-width: 860px;
            min-height: 100%;
        }

        .message-row {
            align-items: flex-end;
            display: flex;
            gap: 10px;
            max-width: min(780px, 96%);
        }

        .message-row.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-row.assistant,
        .message-row.error {
            align-self: flex-start;
        }

        .avatar {
            align-items: center;
            border-radius: 8px;
            display: grid;
            flex: 0 0 34px;
            font-size: 0.78rem;
            font-weight: 800;
            height: 34px;
            place-items: center;
            width: 34px;
        }

        .assistant .avatar {
            background: #e8f0ff;
            color: var(--primary);
        }

        .user .avatar {
            background: #dff8ed;
            color: #087c6f;
        }

        .error .avatar {
            background: #fee4e2;
            color: var(--danger);
        }

        .message {
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 12px 30px rgb(27 39 66 / 8%);
            line-height: 1.6;
            padding: 13px 15px;
            white-space: pre-wrap;
        }

        .message.assistant {
            background: var(--assistant);
        }

        .message.user {
            background: var(--user);
            border-color: #bcebd7;
        }

        .message.error {
            background: #fff5f4;
            border-color: #fecaca;
            color: var(--danger);
        }

        .empty {
            align-content: center;
            display: grid;
            gap: 18px;
            justify-items: center;
            margin: auto;
            max-width: 620px;
            min-height: 100%;
            text-align: center;
        }

        .empty-icon {
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 16px 44px rgb(27 39 66 / 10%);
            color: var(--primary);
            display: grid;
            height: 58px;
            place-items: center;
            width: 58px;
        }

        .empty h2 {
            font-size: clamp(1.45rem, 3vw, 2rem);
            line-height: 1.15;
            margin: 0;
        }

        .empty p {
            color: var(--muted);
            line-height: 1.6;
            margin: 0;
        }

        .prompt-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        .prompt {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: var(--text);
            cursor: pointer;
            line-height: 1.45;
            min-height: 70px;
            padding: 13px;
            text-align: left;
            transition: border-color 160ms ease, transform 160ms ease, box-shadow 160ms ease;
        }

        .prompt:hover {
            border-color: #a9c5f5;
            box-shadow: 0 12px 28px rgb(27 39 66 / 9%);
            transform: translateY(-1px);
        }

        footer {
            background: rgb(255 255 255 / 78%);
            border-top: 1px solid var(--line);
            padding: 16px 22px 18px;
        }

        form {
            align-items: end;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 10px 26px rgb(27 39 66 / 8%);
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(0, 1fr) 48px;
            margin: 0 auto;
            max-width: 860px;
            padding: 8px;
        }

        textarea {
            border: 0;
            color: var(--text);
            min-height: 48px;
            max-height: 170px;
            outline: 0;
            padding: 13px 12px;
            resize: none;
            width: 100%;
        }

        textarea::placeholder {
            color: #8a95a8;
        }

        button[type="submit"] {
            align-items: center;
            background: var(--primary);
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            cursor: pointer;
            display: grid;
            height: 48px;
            place-items: center;
            transition: background 160ms ease, transform 160ms ease;
            width: 48px;
        }

        button[type="submit"]:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        button[type="submit"]:disabled {
            cursor: wait;
            opacity: 0.7;
            transform: none;
        }

        .send-icon {
            height: 20px;
            width: 20px;
        }

        @media (max-width: 900px) {
            .app {
                grid-template-columns: 1fr;
                padding: 14px;
            }

            .sidebar {
                min-height: auto;
            }

            .tips {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .chat-shell {
                min-height: calc(100vh - 220px);
            }
        }

        @media (max-width: 640px) {
            body {
                background: var(--bg);
            }

            .app {
                gap: 12px;
                padding: 0;
            }

            .sidebar,
            .chat-shell {
                border-radius: 0;
                box-shadow: none;
            }

            .sidebar {
                padding: 18px;
            }

            .subtitle,
            .tips {
                display: none;
            }

            .chat-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            main {
                padding: 18px 14px;
            }

            .prompt-grid {
                grid-template-columns: 1fr;
            }

            .message-row {
                max-width: 100%;
            }

            .avatar {
                display: none;
            }

            footer {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div>
                <div class="brand">
                    <div class="brand-mark">AI</div>
                    <div>
                        <h1>{{ config('app.name', 'Laravel Chatbot') }}</h1>
                    </div>
                </div>

                <p class="subtitle">
                    A concise assistant for coding, APIs, Laravel, PHP, JavaScript, MySQL, and practical technical guidance.
                </p>

                <div class="tips" aria-label="Assistant strengths">
                    <div class="tip">Clear answers with simple explanations.</div>
                    <div class="tip">Developer-focused Laravel and API help.</div>
                    <div class="tip">Context-aware replies across the chat.</div>
                </div>
            </div>

            <div class="status" id="status">
                <span class="status-dot"></span>
                <span>Gemini AI Assistant</span>
            </div>
        </aside>

        <section class="chat-shell">
            <header class="chat-header">
                <div class="chat-title">
                    <strong>Conversation</strong>
                    <span>Ask anything. Short, accurate, and practical.</span>
                </div>
                <div class="model-pill">{{ config('services.gemini.model', 'gemini-2.5-flash') }}</div>
            </header>

            <main>
                <section class="messages" id="messages" aria-live="polite">
                    <div class="empty" id="empty">
                        <div class="empty-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                                <path d="M8 10h8"></path>
                                <path d="M8 14h5"></path>
                            </svg>
                        </div>
                        <div>
                            <h2>How can I help today?</h2>
                            <p>Start with a question, a coding task, or a Laravel issue you want to solve.</p>
                        </div>
                        <div class="prompt-grid">
                            <button class="prompt" type="button">Explain Laravel middleware in simple terms</button>
                            <button class="prompt" type="button">Create a clean API response format</button>
                            <button class="prompt" type="button">Help debug a MySQL query</button>
                            <button class="prompt" type="button">Write optimized JavaScript fetch code</button>
                        </div>
                    </div>
                </section>
            </main>

            <footer>
                <form id="chat-form">
                    <textarea id="message" name="message" placeholder="Type your message..." rows="1" required></textarea>
                    <button id="send-button" type="submit" aria-label="Send message">
                        <svg class="send-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m22 2-7 20-4-9-9-4Z"></path>
                            <path d="M22 2 11 13"></path>
                        </svg>
                    </button>
                </form>
            </footer>
        </section>
    </div>

    <script>
        const form = document.querySelector('#chat-form');
        const input = document.querySelector('#message');
        const messages = document.querySelector('#messages');
        const statusText = document.querySelector('#status span:last-child');
        const sendButton = document.querySelector('#send-button');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const history = [];

        function removeEmptyState() {
            document.querySelector('#empty')?.remove();
        }

        function appendMessage(role, text) {
            removeEmptyState();

            const row = document.createElement('div');
            row.className = `message-row ${role}`;

            const avatar = document.createElement('div');
            avatar.className = 'avatar';
            avatar.textContent = role === 'user' ? 'You' : role === 'error' ? '!' : 'AI';

            const bubble = document.createElement('div');
            bubble.className = `message ${role}`;
            bubble.textContent = text;

            row.append(avatar, bubble);
            messages.appendChild(row);
            row.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }

        document.querySelectorAll('.prompt').forEach((button) => {
            button.addEventListener('click', () => {
                input.value = button.textContent.trim();
                input.focus();
            });
        });

        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = `${Math.min(input.scrollHeight, 170)}px`;
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const text = input.value.trim();

            if (!text) {
                return;
            }

            appendMessage('user', text);
            input.value = '';
            input.style.height = 'auto';
            input.focus();
            statusText.textContent = 'Thinking...';
            sendButton.disabled = true;

            try {
                const response = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        message: text,
                        history: history.slice(-20),
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong.');
                }

                appendMessage('assistant', data.reply);
                history.push({ role: 'user', text });
                history.push({ role: 'assistant', text: data.reply });
            } catch (error) {
                appendMessage('error', error.message || 'Unable to get a response.');
            } finally {
                statusText.textContent = 'Gemini AI Assistant';
                sendButton.disabled = false;
            }
        });
    </script>
</body>
</html>
