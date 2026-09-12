<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification - Earthbred Coffee Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #222222;
            color: #1a1a1a;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .card {
            background-color: #f7f3eb;
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .already { color: #2980b9; }
        h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: #2c2520;
        }
        p {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            background-color: #3d271d;
            color: #ffffff;
            padding: 0.75rem 2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(61, 39, 29, 0.3);
        }
        .btn:hover {
            background-color: #26160e;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(61, 39, 29, 0.4);
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($status === 'success'): ?>
            <div class="icon success"><i class="fa-solid fa-circle-check"></i></div>
            <h2>Account Verified!</h2>
            <p><?= htmlspecialchars($message) ?></p>
            <a href="<?= url('/login') ?>" class="btn">Proceed to Login</a>
        <?php elseif ($status === 'already_verified'): ?>
            <div class="icon already"><i class="fa-solid fa-circle-info"></i></div>
            <h2>Already Verified</h2>
            <p><?= htmlspecialchars($message) ?></p>
            <a href="<?= url('/login') ?>" class="btn">Go to Login</a>
        <?php else: ?>
            <div class="icon error"><i class="fa-solid fa-circle-xmark"></i></div>
            <h2>Verification Failed</h2>
            <p><?= htmlspecialchars($message) ?></p>
            <a href="<?= url('/login') ?>" class="btn">Back to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
