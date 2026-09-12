<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred Coffee Studio - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1.0.1">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>
<body>
    <div class="login-container">
        <!-- Animated Floating Visual Card (Left) -->
        <div class="login-visual-wrapper">
            <div class="login-visual-card">
                <div class="card-bg"></div>
            </div>
        </div>
        
        <!-- Floating Form Card (Right) -->
        <div class="login-form-section">
            <div class="form-wrapper">
                
                <div class="logo-container" style="display: flex; align-items: center; gap: 12px;">
                    <img src="<?= asset('images/earthbred-logo-dark.png') ?>" alt="Earthbred" style="width: 42px; height: 42px; object-fit: contain;">
                    <div>
                        <h1 class="logo-main">earthbred</h1>
                        <p class="logo-sub">Coffee Studio</p>
                    </div>
                </div>
                
                <div class="heading-container">
                    <h2 class="main-heading">Brewing<br>excellence<br>behind every cup.</h2>
                    <img src="<?= asset('images/cup.png') ?>" alt="Coffee Cup" class="coffee-cup-image">
                </div>

                <!-- Role / Method Switcher -->
                <div class="login-tab-switcher">
                    <button type="button" class="login-tab active" id="tabCashier">
                        <i class="fa-solid fa-cash-register"></i> Cashier PIN
                    </button>
                    <button type="button" class="login-tab" id="tabManager">
                        <i class="fa-solid fa-user-tie"></i> Manager / Owner
                    </button>
                </div>

                <!-- 1. Cashier 6-Digit PIN Login Form (Default) -->
                <form class="login-form" id="cashierLoginForm">
                    <p class="cashier-pin-sub">
                        <strong>Cashier Quick Access:</strong> Enter your 6-digit PIN to open the POS terminal.
                    </p>

                    <div class="input-group">
                        <label for="cashierPin">6-Digit PIN</label>
                        <div class="password-input-wrapper pin-display-wrapper">
                            <input type="password" id="cashierPin" name="pin" maxlength="6" pattern="\d{6}" inputmode="numeric" placeholder="••••••" autocomplete="off" autofocus required>
                            <i class="fa-regular fa-eye password-toggle" data-target="cashierPin"></i>
                        </div>
                    </div>

                    <button type="submit" class="login-btn" id="cashierLoginBtn">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Log In to POS
                    </button>
                </form>

                <!-- 2. Manager / Owner Email & Password Form -->
                <form class="login-form" id="managerLoginForm" style="display: none;" autocomplete="off">
                    <!-- Decoy inputs to absorb browser autofill heuristics -->
                    <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; width: 0; height: 0; pointer-events: none;" aria-hidden="true">
                        <input type="text" name="fake_usernameremembered" tabindex="-1" autocomplete="off">
                        <input type="password" name="fake_passwordremembered" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="input-group">
                        <label for="email">Manager / Owner Email</label>
                        <input type="text" id="email" name="auth_identity_<?= time() ?>" placeholder="enter your email..." autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other" aria-autocomplete="none">
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="auth_key_<?= time() ?>" placeholder="enter your password..." autocomplete="new-password" data-lpignore="true" data-form-type="other">
                            <i class="fa-regular fa-eye password-toggle" data-target="password"></i>
                        </div>
                        <a href="#" class="forgot-password" style="display:none;">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn" id="managerLoginBtn">
                        <i class="fa-solid fa-lock"></i> Log In as Manager
                    </button>
                </form>

            </div>
        </div>
    </div>
    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/script.js') ?>?v=1.0.0"></script>
</body>
</html>
