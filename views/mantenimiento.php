<?php
if (!defined('BASE_URL')) require_once __DIR__ . '/../core/config.php';
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitio en Mantenimiento – BookArt</title>
    <link href="https://fonts.googleapis.com/css2?family=Chewy&family=Martian+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f0e8;
            font-family: 'Martian Mono', monospace;
            padding: 2rem;
        }

        .card {
            background: #fff;
            max-width: 560px;
            width: 100%;
            padding: 3rem 2.5rem;
            border: 5px solid #3d2b1f;
            box-shadow: 8px 8px 0 #3d2b1f;
            text-align: center;
            position: relative;
        }

        /* Washi tape superior */
        .card::before {
            content: '';
            position: absolute;
            top: -14px;
            left: 15%;
            right: 15%;
            height: 24px;
            background: repeating-linear-gradient(
                45deg,
                #f4c430 0px, #f4c430 15px,
                #4a7c59 15px, #4a7c59 30px,
                #c0392b 30px, #c0392b 45px
            );
            opacity: 0.8;
            box-shadow: 0 2px 6px rgba(0,0,0,.25);
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        h1 {
            font-family: 'Chewy', cursive;
            font-size: 2.8rem;
            color: #3d2b1f;
            margin-bottom: 0.75rem;
            line-height: 1.1;
        }

        .subtitle {
            color: #7a6652;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .divider {
            border: none;
            border-top: 3px dashed #d4c4a8;
            margin: 1.5rem 0;
        }

        .contact-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #7a6652;
            margin-bottom: 1rem;
        }

        .nsc-card {
            background: #3d2b1f;
            color: #f5f0e8;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: left;
            text-decoration: none;
            transition: background 0.2s;
        }

        .nsc-card:hover { background: #4a7c59; }

        .nsc-logo {
            font-family: 'Chewy', cursive;
            font-size: 1.6rem;
            line-height: 1;
            flex-shrink: 0;
            color: #f4c430;
        }

        .nsc-info { flex: 1; }
        .nsc-name { font-weight: 600; font-size: 0.95rem; }
        .nsc-url  { font-size: 0.78rem; color: #d4c4a8; margin-top: 0.2rem; }

        .nsc-arrow { font-size: 1.5rem; color: #f4c430; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🔧</div>
    <h1>Sitio en Mantenimiento</h1>
    <p class="subtitle">
        Estamos realizando mejoras para ofrecerte una mejor experiencia.<br>
        Por favor regresa más tarde.
    </p>

    <hr class="divider">

    <p class="contact-label">¿Eres el administrador? Contacta a tu proveedor</p>

    <a href="https://neuralstackcode.com.mx" target="_blank" rel="noopener" class="nsc-card">
        <div class="nsc-logo">NS</div>
        <div class="nsc-info">
            <div class="nsc-name">NeuralStack Code</div>
            <div class="nsc-url">neuralstackcode.com.mx</div>
        </div>
        <div class="nsc-arrow">→</div>
    </a>
</div>
</body>
</html>
