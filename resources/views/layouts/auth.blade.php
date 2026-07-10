<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') · {{ config('app.name') }}</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #172033;
            background: #f4f6fa;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
        }

        button,
        input {
            font: inherit;
        }

        .page {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 32px 20px;
            background:
                radial-gradient(circle at 15% 15%, rgba(79, 70, 229, 0.12), transparent 28%),
                radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.1), transparent 30%),
                #f4f6fa;
        }

        .auth-wrap {
            width: min(100%, 430px);
        }

        .brand {
            display: block;
            width: 220px;
            margin-bottom: 22px;
            margin-inline: auto;
            text-decoration: none;
        }

        .brand img {
            display: block;
            width: 100%;
            height: auto;
        }

        .card {
            padding: 34px;
            border: 1px solid #e3e7ef;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 20px 55px rgba(30, 41, 59, 0.09);
        }

        h1 {
            margin: 0;
            font-size: 26px;
            letter-spacing: -0.03em;
        }

        .subtitle {
            margin: 8px 0 26px;
            color: #667085;
            font-size: 14px;
            line-height: 1.6;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #344054;
            font-size: 14px;
            font-weight: 650;
        }

        input {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1px solid #d0d5dd;
            border-radius: 10px;
            outline: none;
            color: #172033;
            background: #fff;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        input::placeholder {
            color: #98a2b3;
        }

        input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .button {
            width: 100%;
            height: 46px;
            margin-top: 4px;
            border: 0;
            border-radius: 10px;
            color: #fff;
            background: #4f46e5;
            font-weight: 700;
            cursor: pointer;
            transition: background 150ms ease, transform 150ms ease;
        }

        .button:hover {
            background: #4338ca;
        }

        .button:active {
            transform: translateY(1px);
        }

        .button:disabled {
            cursor: wait;
            opacity: 0.65;
        }

        .message {
            display: none;
            margin-bottom: 18px;
            padding: 11px 13px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.5;
        }

        .message.error {
            display: block;
            color: #b42318;
            background: #fef3f2;
        }

        .message.success {
            display: block;
            color: #067647;
            background: #ecfdf3;
        }

        .switch {
            margin: 22px 0 0;
            color: #667085;
            font-size: 14px;
            text-align: center;
        }

        .switch a {
            color: #4f46e5;
            font-weight: 700;
            text-decoration: none;
        }

        .switch a:hover {
            text-decoration: underline;
        }

        .footnote {
            margin: 18px 0 0;
            color: #98a2b3;
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .page {
                padding: 22px 14px;
            }

            .card {
                padding: 26px 22px;
            }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="auth-wrap">
        <a class="brand" href="{{ url('/') }}">
            <img src="{{ asset('images/sembark-logo.png') }}" alt="Sembark Travel Software">
        </a>

        <section class="card">
            @yield('content')
        </section>

    </div>
</main>

<script>
    const form = document.querySelector('[data-auth-form]');

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const message = form.querySelector('[data-message]');
        const originalLabel = button.textContent;

        button.disabled = true;
        button.textContent = 'Please wait...';
        message.className = 'message';
        message.textContent = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });

            const data = await response.json();

            if (!response.ok) {
                const errors = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : data.message;

                throw new Error(errors || 'Something went wrong. Please try again.');
            }

            localStorage.setItem('access_token', data.access_token);
            localStorage.setItem('auth_user', JSON.stringify(data.user));

            message.className = 'message success';
            message.textContent = form.dataset.success;
            form.reset();
        } catch (error) {
            message.className = 'message error';
            message.textContent = error.message;
        } finally {
            button.disabled = false;
            button.textContent = originalLabel;
        }
    });
</script>
</body>
</html>
