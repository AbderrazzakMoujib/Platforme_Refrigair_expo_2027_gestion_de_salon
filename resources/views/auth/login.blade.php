
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>Connexion | Refrigair Expo 2027</title>

    <link rel="icon"
          type="image/png"
          href="{{ asset('assets/images/icons/favicon_io.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        :root {
            --primary: #2563EB;
            --primary-hover: #1D4ED8;
            --navy: #0F2347;
            --muted: #64748B;
            --border: #E2E8F0;
            --background: #F5F7FB;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--navy);
            background: var(--background);
            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        [hidden] {
            display: none !important;
        }

        /* ==================================
           MAIN LOGIN LAYOUT
        ================================== */

        .login-page {
            display: grid;
            grid-template-columns: minmax(0, 48%) minmax(0, 52%);
            width: 100%;
            min-height: 100vh;
            min-height: 100dvh;
        }

        /* ==================================
           LEFT IMAGE - FULL COVER
        ================================== */

        .visual-panel {
            position: sticky;
            top: 0;
            width: 100%;
            min-width: 0;
            height: 100vh;
            height: 100dvh;
            padding: 0;
            margin: 0;
            overflow: hidden;
            background: #D9EDF3;
        }

        .poster-image {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            max-width: none;
            max-height: none;
            margin: 0;
            padding: 0;
            border: 0;
            object-fit: cover;
            object-position: center;
        }

        /* ==================================
           RIGHT PANEL
        ================================== */

        .form-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 0;
            min-height: 100vh;
            min-height: 100dvh;
            padding: 24px 32px;

            background:
                radial-gradient(
                    ellipse at top right,
                    #E5EEFF 0%,
                    transparent 55%
                ),
                #F5F7FB;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            min-width: 0;
            margin: auto;
        }

        .login-card {
            width: 100%;
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #FFFFFF;
            box-shadow: 0 15px 45px rgba(15, 35, 71, .07);
        }

        /* ==================================
           LOGO AND TITLE
        ================================== */

        .login-logo {
            display: block;
            width: 210px;
            max-width: 100%;
            height: 60px;
            margin: 0 auto 18px;
            object-fit: contain;
        }

        .login-heading {
            margin: 0;
            text-align: center;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -.7px;
            line-height: 1.3;
            color: var(--navy);
        }

        .login-description {
            margin: 8px 0 0;
            text-align: center;
            font-size: 13px;
            line-height: 1.6;
            color: var(--muted);
        }

        /* ==================================
           LOGIN FORM
        ================================== */

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 24px;
        }

        .form-group {
            min-width: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 700;
            color: var(--navy);
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            min-width: 0;
            min-height: 48px;
            padding: 0 13px;
            border: 1px solid #CBD5E1;
            border-radius: 11px;
            background: #FFFFFF;
            transition:
                border-color .2s,
                box-shadow .2s;
        }

        .input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        }

        .input-wrapper.has-error {
            border-color: #EF4444;
        }

        .input-wrapper input {
            display: block;
            flex: 1;
            width: 100%;
            min-width: 0;
            height: 46px;
            padding: 0;
            margin: 0;
            border: 0;
            border-radius: 0;
            outline: none;
            box-shadow: none;
            background: transparent;
            color: #1E293B;
            font-size: 14px;
        }

        .input-wrapper input:focus {
            border: 0;
            outline: none;
            box-shadow: none;
        }

        .input-wrapper input::placeholder {
            color: #94A3B8;
            font-size: 13px;
        }

        .input-icon {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
            color: #64748B;
        }

        /* CHROME AUTOFILL FIX */

        .input-wrapper input:-webkit-autofill,
        .input-wrapper input:-webkit-autofill:hover,
        .input-wrapper input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset;
            -webkit-text-fill-color: #1E293B;
            caret-color: #1E293B;
        }

        /* ==================================
           PASSWORD TOGGLE
        ================================== */

        .password-toggle {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
        }

        .password-toggle:hover {
            background: #EFF6FF;
            color: var(--primary);
        }

        .password-toggle svg {
            width: 19px;
            height: 19px;
        }

        /* ==================================
           REMEMBER AND FORGOT PASSWORD
        ================================== */

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #475569;
            cursor: pointer;
        }

        .remember-label input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        /* ==================================
           LOGIN BUTTON
        ================================== */

        .login-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 48px;
            padding: 12px 18px;
            border: 0;
            border-radius: 11px;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition:
                background .2s,
                box-shadow .2s;
        }

        .login-button:hover:not(:disabled) {
            background: var(--primary-hover);
            box-shadow: 0 5px 15px rgba(37, 99, 235, .2);
        }

        .login-button:disabled {
            opacity: .7;
            cursor: wait;
        }

        .login-button svg {
            width: 21px;
            height: 21px;
            flex-shrink: 0;
        }

        /* ==================================
           ROLES
        ================================== */

        .separator {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0 13px;
            font-size: 11px;
            font-weight: 600;
            color: #94A3B8;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 0;
            gap: 7px;
            padding: 12px 4px;
            border: 1px solid transparent;
            border-radius: 12px;
            text-align: center;
        }

        .role-card svg {
            width: 22px;
            height: 22px;
        }

        .role-card span {
            font-size: 10px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .role-admin {
            background: #F5F3FF;
            border-color: #EDE9FE;
            color: #7C3AED;
        }

        .role-commercial {
            background: #EFF6FF;
            border-color: #DBEAFE;
            color: #2563EB;
        }

        .role-exhibitor {
            background: #ECFDF5;
            border-color: #D1FAE5;
            color: #059669;
        }

        .role-conferences {
            background: #FFF7ED;
            border-color: #FFEDD5;
            color: #C2410C;
        }

        .role-description {
            margin: 12px 0 0;
            font-size: 11px;
            line-height: 1.6;
            text-align: center;
            color: var(--muted);
        }

        /* ==================================
           ALERT MESSAGES
        ================================== */

        .alert {
            margin-top: 16px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .alert p {
            margin: 0;
        }

        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
        }

        .alert-success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #047857;
        }

        /* ==================================
           FOOTER
        ================================== */

        .login-footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            padding-top: 15px;
            font-size: 11px;
            line-height: 1.5;
            text-align: center;
            color: var(--muted);
        }

        .secure-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            font-size: 11px;
        }

        .secure-label svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: #059669;
        }

        .login-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        button:focus-visible,
        a:focus-visible,
        input[type="checkbox"]:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 3px;
        }

        /* ==================================
           LARGE DESKTOP
        ================================== */

        @media (min-width: 1600px) {

            .login-container {
                max-width: 480px;
            }

            .form-panel {
                padding: 30px 40px;
            }
        }

        /* ==================================
           LAPTOP / SMALL DESKTOP HEIGHT
        ================================== */

        @media (min-width: 1024px) and (max-height: 850px) {

            .form-panel {
                padding: 15px 24px;
            }

            .login-container {
                max-width: 430px;
            }

            .login-card {
                padding: 22px 24px;
                border-radius: 20px;
            }

            .login-logo {
                height: 48px;
                margin-bottom: 14px;
            }

            .login-heading {
                font-size: 23px;
            }

            .login-description {
                font-size: 12px;
            }

            .login-form {
                margin-top: 18px;
                gap: 12px;
            }

            .form-label {
                margin-bottom: 5px;
            }

            .input-wrapper {
                min-height: 42px;
            }

            .input-wrapper input {
                height: 40px;
            }

            .login-button {
                min-height: 44px;
                padding: 10px 16px;
            }

            .separator {
                margin: 15px 0 10px;
            }

            .role-card {
                padding: 8px 4px;
                gap: 5px;
            }

            .role-card svg {
                width: 20px;
                height: 20px;
            }

            .role-description {
                margin-top: 8px;
                font-size: 10px;
            }

            .login-footer {
                padding-top: 10px;
                gap: 3px;
                font-size: 10px;
            }
        }

        /* ==================================
           TABLET
        ================================== */

        @media (max-width: 1023px) {

            .login-page {
                grid-template-columns: minmax(0, 1fr);
            }

            .visual-panel {
                display: none;
            }

            .form-panel {
                padding: 30px 24px;
            }

            .login-container {
                max-width: 450px;
            }
        }

        /* ==================================
           MOBILE
        ================================== */

        @media (max-width: 600px) {

            body {
                background: #FFFFFF;
            }

            .form-panel {
                padding:
                    max(20px, env(safe-area-inset-top))
                    18px
                    max(20px, env(safe-area-inset-bottom));

                background: #FFFFFF;
            }

            .login-container {
                max-width: 400px;
            }

            .login-card {
                padding: 15px 4px;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .login-logo {
                width: 200px;
                height: 65px;
                margin-bottom: 22px;
            }

            .login-heading {
                font-size: 24px;
            }

            .login-description {
                font-size: 13px;
            }

            .login-form {
                margin-top: 26px;
                gap: 18px;
            }

            .input-wrapper {
                min-height: 52px;
            }

            .input-wrapper input {
                height: 50px;
                font-size: 16px;
            }

            .password-toggle {
                width: 40px;
                height: 40px;
            }

            .form-options {
                gap: 12px;
            }

            .login-button {
                min-height: 50px;
            }

            .roles-grid {
                gap: 6px;
            }

            .role-card {
                padding: 12px 3px;
            }

            .login-footer {
                padding-top: 18px;
            }
        }

        /* SMALL MOBILE */

        @media (max-width: 350px) {

            .form-panel {
                padding-left: 10px;
                padding-right: 10px;
            }

            .login-heading {
                font-size: 21px;
            }

            .form-options {
                align-items: flex-start;
                flex-direction: column;
            }

            .roles-grid {
                gap: 4px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            * {
                transition: none !important;
            }
        }

    </style>
</head>

<body>

    <main class="login-page">

        <!-- ==================================
             IMAGE LEFT
        ================================== -->

        <section class="visual-panel"
                 aria-label="Refrigair Expo 2027">

            <img
                src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-afich_refrigair-expo_2027.jpeg') }}"
                class="poster-image"
                alt="Refrigair Expo 2027 — 2 au 4 février 2027, OFEC Casablanca"
                fetchpriority="high"
            >

        </section>

        <!-- ==================================
             LOGIN RIGHT
        ================================== -->

        <section class="form-panel">

            <div class="login-container">

                <div class="login-card">

                    <!-- LOGO -->

                    <img
                        src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}"
                        class="login-logo"
                        alt="Refrigair Expo 2027"
                    >

                    <h1 class="login-heading">
                        Connexion à votre espace
                    </h1>

                    <p class="login-description">
                        Accédez à la plateforme de gestion du salon.
                    </p>

                    <!-- SUCCESS MESSAGE -->

                    @if (session('status'))

                        <div class="alert alert-success"
                             role="status">

                            {{ session('status') }}

                        </div>

                    @endif

                    <!-- VALIDATION ERRORS -->

                    @if ($errors->any())

                        <div class="alert alert-error"
                             role="alert">

                            @foreach ($errors->all() as $error)

                                <p>{{ $error }}</p>

                            @endforeach

                        </div>

                    @endif

                    <!-- LOGIN FORM -->

                    <form
                        method="POST"
                        action="{{ route('login.store') }}"
                        class="login-form"
                        id="login-form"
                    >

                        @csrf

                        <!-- EMAIL -->

                        <div class="form-group">

                            <label for="email"
                                   class="form-label">

                                Adresse email

                            </label>

                            <div class="input-wrapper {{ $errors->has('email') ? 'has-error' : '' }}">

                                <svg
                                    class="input-icon"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path d="m3 7 9 6 9-6"/>
                                </svg>

                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    placeholder="Votre adresse email"
                                    autocomplete="username"
                                    required

                                    @if ($errors->has('email'))
                                        aria-invalid="true"
                                    @endif
                                >

                            </div>

                        </div>

                        <!-- PASSWORD -->

                        <div class="form-group">

                            <label for="password"
                                   class="form-label">

                                Mot de passe

                            </label>

                            <div class="input-wrapper {{ $errors->has('password') ? 'has-error' : '' }}">

                                <svg
                                    class="input-icon"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                    <path d="M12 14v3"/>
                                </svg>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="Votre mot de passe"
                                    autocomplete="current-password"
                                    required

                                    @if ($errors->has('password'))
                                        aria-invalid="true"
                                    @endif
                                >

                                <!-- PASSWORD VISIBILITY -->

                                <button
                                    type="button"
                                    id="password-toggle"
                                    class="password-toggle"
                                    aria-label="Afficher le mot de passe"
                                    aria-pressed="false"
                                >

                                    <svg
                                        id="eye-open"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>

                                    <svg
                                        id="eye-closed"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                        hidden
                                    >
                                        <path d="m3 3 18 18"/>
                                        <path d="M9.9 5.2A11.5 11.5 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-2 2.8"/>
                                        <path d="M6.3 6.3C3.5 8.6 2 12 2 12s3.5 7 10 7a10 10 0 0 0 5.7-1.7"/>
                                    </svg>

                                </button>

                            </div>

                        </div>

                        <!-- REMEMBER AND FORGOT -->

                        <div class="form-options">

                            <label class="remember-label">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                >

                                Se souvenir de moi

                            </label>

                            <a
                                href="{{ route('password.request') }}"
                                class="forgot-link"
                            >

                                Mot de passe oublié ?

                            </a>

                        </div>

                        <!-- LOGIN BUTTON -->

                        <button
                            type="submit"
                            class="login-button"
                            id="login-button"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M4 12h15"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>

                            <span id="button-text">

                                Se connecter

                            </span>

                        </button>

                    </form>

                    <!-- ==================================
                         USER ROLES
                    ================================== -->

                    <div class="separator">

                        Vos espaces

                    </div>

                    <div class="roles-grid">

                        <!-- ADMIN -->

                        <div class="role-card role-admin">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>

                            <span>Administrateur</span>

                        </div>

                        <!-- COMMERCIAL -->

                        <div class="role-card role-commercial">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect x="3" y="7" width="18" height="14" rx="2"/>
                                <path d="M8 7V4h8v3"/>
                                <path d="M3 12c5 3 13 3 18 0M12 12v3"/>
                            </svg>

                            <span>Commercial</span>

                        </div>

                        <!-- EXPOSANT -->

                        <div class="role-card role-exhibitor">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M3 9l2-6h14l2 6M3 9v3a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0V9H3Z"/>
                                <path d="M5 15v6h14v-6M10 21v-5h4v5"/>
                            </svg>

                            <span>Exposant</span>

                        </div>

                        <div class="role-card role-conferences">

                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="17" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18M8 14h3M8 17h7"/>
                            </svg>

                            <span>Responsable Conférences</span>

                        </div>

                    </div>

                    <p class="role-description">

                        Vous serez automatiquement redirigé vers votre espace.

                    </p>

                </div>

                <!-- ==================================
                     FOOTER
                ================================== -->

                <footer class="login-footer">

                    <div class="secure-label">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>

                        Accès réservé aux utilisateurs autorisés

                    </div>

                    <span>
                        © 2027 Refrigair Expo. Tous droits réservés.
                    </span>

                    <a
                        href="https://refrigairexpo.com/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        www.refrigairexpo.com

                    </a>

                </footer>

            </div>

        </section>

    </main>

    <!-- ==================================
         JAVASCRIPT
    ================================== -->

    <script>

        (() => {

            const password = document.getElementById('password');

            const toggle = document.getElementById('password-toggle');

            const eyeOpen = document.getElementById('eye-open');

            const eyeClosed = document.getElementById('eye-closed');

            const form = document.getElementById('login-form');

            const button = document.getElementById('login-button');

            const buttonText = document.getElementById('button-text');


            // SHOW / HIDE PASSWORD

            toggle.addEventListener('click', () => {

                const visible = password.type === 'password';

                password.type = visible ? 'text' : 'password';

                eyeOpen.hidden = visible;

                eyeClosed.hidden = !visible;

                toggle.setAttribute(
                    'aria-pressed',
                    String(visible)
                );

                toggle.setAttribute(
                    'aria-label',
                    visible
                        ? 'Masquer le mot de passe'
                        : 'Afficher le mot de passe'
                );

            });


            // FORM SUBMISSION

            form.addEventListener('submit', event => {

                if (button.disabled) {

                    event.preventDefault();

                    return;

                }

                button.disabled = true;

                buttonText.textContent = 'Connexion en cours...';

                form.setAttribute('aria-busy', 'true');

            });


            // RESET BUTTON

            window.addEventListener('pageshow', () => {

                button.disabled = false;

                buttonText.textContent = 'Se connecter';

                form.removeAttribute('aria-busy');

            });

        })();

    </script>

    <x-ui.toast />
</body>

</html>
