<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e40af">
    <title>Artemisys — <?= htmlspecialchars($pageTitle ?? 'Area Studente') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>public/css/style.css" rel="stylesheet">
    <style>
        /* ===== BASE ===== */
        body { background:#f1f5f9; padding-bottom:80px; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; }

        /* ===== MOBILE TOPBAR ===== */
        .stu-topbar {
            position:sticky; top:0; z-index:1000;
            background:linear-gradient(135deg,#1e40af 0%,#3b82f6 100%);
            color:#fff; padding:14px 16px; display:flex; align-items:center; gap:12px;
            box-shadow:0 2px 8px rgba(0,0,0,.08);
        }
        .stu-avatar {
            width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,.25);
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
            border:2px solid rgba(255,255,255,.4);
        }
        .stu-greeting { flex-grow:1; min-width:0; }
        .stu-greeting .name { font-weight:700; font-size:.95rem; line-height:1.1; }
        .stu-greeting .role { font-size:.72rem; opacity:.85; }
        .stu-logout {
            color:#fff; opacity:.85; padding:6px 10px; border-radius:8px;
            background:rgba(255,255,255,.12); font-size:.8rem; text-decoration:none;
        }
        .stu-logout:hover { color:#fff; background:rgba(255,255,255,.22); }

        /* ===== PAGE TITLE ===== */
        .stu-page-title { padding:16px 16px 8px; font-weight:800; font-size:1.3rem; color:#0c1a3a; }

        /* ===== MOBILE CONTENT WRAPPER ===== */
        .stu-content { padding:0 14px 20px; max-width:600px; margin:0 auto; }

        /* ===== CARDS ===== */
        .stu-card {
            background:#fff; border-radius:14px; padding:16px;
            box-shadow:0 1px 6px rgba(0,0,0,.05); margin-bottom:12px;
        }
        .stu-card-link { display:block; text-decoration:none; color:inherit; }
        .stu-card-link:active { transform:scale(.98); transition:.1s; }

        /* ===== STAT TILES ===== */
        .stu-stat {
            background:#fff; border-radius:14px; padding:14px 12px;
            box-shadow:0 1px 6px rgba(0,0,0,.05); text-align:center;
        }
        .stu-stat .num { font-size:1.6rem; font-weight:800; color:#0c1a3a; line-height:1; }
        .stu-stat .lbl { font-size:.7rem; color:#64748b; text-transform:uppercase; letter-spacing:.04em; font-weight:600; margin-top:4px; }
        .stu-stat .ico { font-size:1.1rem; margin-bottom:6px; }

        /* ===== BOTTOM NAV (mobile) ===== */
        .stu-bottomnav {
            position:fixed; bottom:0; left:0; right:0; z-index:1000;
            background:#fff; border-top:1px solid #e2e8f0;
            display:flex; justify-content:space-around;
            padding:6px 0 max(6px,env(safe-area-inset-bottom));
            box-shadow:0 -2px 10px rgba(0,0,0,.05);
        }
        .stu-bottomnav a {
            flex:1; text-align:center; text-decoration:none; color:#94a3b8;
            padding:6px 4px; font-size:.65rem; font-weight:600;
            display:flex; flex-direction:column; align-items:center; gap:2px;
            transition:.15s;
        }
        .stu-bottomnav a i { font-size:1.15rem; }
        .stu-bottomnav a.active { color:#1e40af; }
        .stu-bottomnav a.active i { transform:scale(1.1); }

        /* ===== MISC ===== */
        .badge-soft { font-size:.66rem; padding:.25rem .55rem; border-radius:6px; font-weight:600; }
        .pill-row { display:flex; gap:6px; flex-wrap:wrap; }
        .empty-stu { text-align:center; padding:30px 12px; color:#94a3b8; }
        .empty-stu i { font-size:2.2rem; color:#cbd5e1; display:block; margin-bottom:8px; }
        a { color:#1e40af; }

        /* ===== DESKTOP LAYOUT (992px+) ===== */
        @media (min-width:992px) {
            body { padding-bottom: 0; }

            /* Content area shifts right to make room for fixed sidebar */
            .stu-main-wrapper {
                margin-left: var(--sidebar-width, 265px);
                padding: 30px;
                min-height: 100vh;
            }

            /* Remove mobile card constraints */
            .stu-content {
                max-width: none;
                padding: 0;
                margin: 0;
            }

            /* Page title adjusts on desktop */
            .stu-page-title {
                padding: 0 0 20px 0;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<?php
$studenteName = trim(($studente['nome'] ?? '') . ' ' . ($studente['cognome'] ?? ''));
$cur = $page ?? '';
$userName = $_SESSION['user_nome'] ?? $studenteName;
?>

<!-- ===== DESKTOP SIDEBAR (hidden on mobile) ===== -->
<div class="sidebar d-none d-lg-flex" style="flex-direction:column;">

    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-name">Artemisys</span>
                <span class="sidebar-brand-module">Area Studente</span>
            </div>
        </div>
    </div>

    <div class="sidebar-menu-label">Navigazione</div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= BASE_URL ?>studente" class="<?= $cur === 'studente-home' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/lezioni" class="<?= $cur === 'studente-lezioni' ? 'active' : '' ?>">
                <i class="fas fa-book-open"></i>
                <span>Le mie lezioni</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/voti" class="<?= $cur === 'studente-voti' ? 'active' : '' ?>">
                <i class="fas fa-star"></i>
                <span>I miei voti</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/presenze" class="<?= $cur === 'studente-presenze' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Le mie presenze</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/rette" class="<?= $cur === 'studente-rette' ? 'active' : '' ?>">
                <i class="fas fa-euro-sign"></i>
                <span>Le mie rette</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/documenti" class="<?= $cur === 'studente-documenti' ? 'active' : '' ?>">
                <i class="fas fa-folder-open"></i>
                <span>Documenti</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/stage" class="<?= $cur === 'studente-stage' ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i>
                <span>Il mio stage</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>bacheca" class="<?= $cur === 'bacheca' ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i>
                <span>Bacheca</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>studente/eventi" class="<?= $cur === 'studente-eventi' ? 'active' : '' ?>">
                <i class="fas fa-calendar-day"></i>
                <span>Eventi</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar footer -->
    <div class="sidebar-footer">
        <div class="user-info-box">
            <div class="user-avatar-small">
                <i class="fas fa-user-graduate" style="color:white;font-size:0.75rem;"></i>
            </div>
            <div class="user-info-text">
                <span class="user-info-name"><?= htmlspecialchars($studenteName) ?></span>
                <span class="user-info-role">Studente</span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>auth/logout" class="logout-link">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>

</div><!-- /sidebar -->

<!-- ===== MAIN WRAPPER ===== -->
<div class="stu-main-wrapper">

    <!-- Mobile topbar (hidden on desktop) -->
    <div class="stu-topbar d-lg-none">
        <div class="stu-avatar">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stu-greeting">
            <div class="name"><?= htmlspecialchars($studenteName) ?></div>
            <div class="role">Studente · Artemisys</div>
        </div>
        <a href="<?= BASE_URL ?>auth/logout" class="stu-logout" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <!-- Desktop breadcrumb (hidden on mobile) -->
    <div class="d-none d-lg-block mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>studente"><i class="fas fa-home me-1"></i>Area Studente</a>
                </li>
                <?php if (!empty($pageTitle) && ($cur !== 'studente-home')): ?>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['flash_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); endif; ?>

    <!-- Page content -->
    <div class="stu-content">
