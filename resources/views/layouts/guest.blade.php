<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LMS') }}</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Global Brand CSS -->
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    <style>
        body{ background:#f3f4f7; }

        .auth-shell{
            min-height: 100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 24px 12px;
        }
        .auth-card{
            background:#fff;
            border: 1px solid rgba(0,0,0,.06);
            border-radius: 18px;
            overflow:hidden;
            box-shadow: 0 18px 55px rgba(16, 24, 40, .10);
        }
        .auth-left{
            padding: 28px 28px;
            background:#fff;
        }
        .auth-right{
            background: var(--brand-bg-soft);
            padding: 28px 28px;
            border-left: 1px solid var(--brand-border-soft);
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .brand-row{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom: 22px;
        }
        .brand-logo{
            height: 34px;
            width: auto;
            display:block;
        }

        .auth-title{
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 6px 0;
            font-size: 26px;
            color: var(--brand-text-dark);
        }
        .auth-subtitle{
            margin:0 0 18px 0;
            color: var(--brand-text-muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .form-label{
            font-size: 12px;
            color:#374151;
            margin-bottom: 6px;
        }
        .form-control{
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid rgba(0,0,0,.12);
        }

        .divider{
            display:flex;
            align-items:center;
            gap:10px;
            color:#9ca3af;
            font-size: 12px;
            margin: 14px 0;
        }
        .divider::before,
        .divider::after{
            content:"";
            flex:1;
            height:1px;
            background: rgba(0,0,0,.10);
        }

        .right-wrap{
            width: 100%;
            max-width: 520px;
            text-align:center;
        }
        .right-kicker{
            color:#6b7280;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .right-title{
            font-weight: 900;
            letter-spacing: -0.03em;
            color: var(--brand-primary);
            font-size: 38px;
            margin: 0 0 14px 0;
        }
        .illus{
            width: 100%;
            max-width: 440px;
            height: auto;
            display:block;
            margin: 0 auto;
        }

        @media (max-width: 991.98px){
            .auth-right{
                border-left: 0;
                border-top: 1px solid var(--brand-border-soft);
            }
            .right-title{ font-size: 32px; }
        }
    </style>
</head>

<body>
<div class="auth-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-9">
                <div class="auth-card">
                    <div class="row g-0">
                        <div class="col-12 col-lg-5 auth-left">
                            {{ $slot }}
                        </div>

                        <div class="col-12 col-lg-7 auth-right">
                            <div class="right-wrap">
                                <img class="illus"
                                     src="{{ asset('images/login-illustration.png') }}"
                                     alt="Welcome illustration"
                                     onerror="this.style.display='none';">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center small text-muted mt-3">
                    © {{ date('Y') }} {{ config('app.name', 'LMS') }} 
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
