<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Earthbred Coffee Studio</title>

        <!-- Fonts -->
        <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>?v=2.0">
        <link rel="apple-touch-icon" href="<?= asset('images/favicon.png') ?>?v=2.0">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            body {
                font-family: 'Nunito', sans-serif;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                background-color: #1a202c;
                color: #fff;
            }
            .container {
                text-align: center;
            }
            .container h1 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
            }
            .container p {
                color: #a0aec0;
                font-size: 1rem;
            }
            .links a {
                color: #cbd5e0;
                text-decoration: underline;
                margin: 0 1rem;
                font-size: 0.875rem;
            }
            .links a:hover {
                color: #fff;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Earthbred Coffee Studio</h1>
            <p>Welcome to the backend.</p>
            <div class="links" style="margin-top: 1.5rem;">
                <a href="/login">Log In</a>
                <a href="/pos">POS</a>
            </div>
        </div>
    </body>
</html>
