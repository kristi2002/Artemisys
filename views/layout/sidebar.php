<?php
$page          = $page ?? '';
$currentModule = $_SESSION['current_module'] ?? 'didattica';
$userRuolo     = $_SESSION['user_ruolo'] ?? 'admin';
$userName      = $_SESSION['user_nome'] ?? 'Utente';

$ruoloLabel = match($userRuolo) {
    'admin'    => 'Amministratore',
    'docente'  => 'Docente',
    'studente' => 'Studente',
    default    => $userRuolo,
};
?>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">

        <!-- Brand -->
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="sidebar-brand-text">
                    <span class="sidebar-brand-name">Artemisys</span>
                    <span class="sidebar-brand-module">
                        <?= $currentModule === 'didattica' ? 'Didattica' : 'Orientamento' ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($userRuolo !== 'docente'): ?>
        <!-- Torna alla selezione -->
        <a href="<?= BASE_URL ?>switch" class="switch-module-link">
            <i class="fas fa-th-large"></i>
            <span>Cambia Modulo</span>
        </a>
        <hr class="sidebar-divider">
        <?php endif; ?>

        <?php if ($currentModule === 'didattica' && $userRuolo === 'docente'): ?>
            <!-- ===== MENU DOCENTE ===== -->
            <div class="sidebar-menu-label">Area Docente</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>mie-lezioni" class="<?= $page === 'mie-lezioni' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Le mie lezioni</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>bacheca" class="<?= $page === 'bacheca' ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn"></i>
                        <span>Bacheca</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>mie-materie" class="<?= $page === 'mie-materie' ? 'active' : '' ?>">
                        <i class="fas fa-book-open"></i>
                        <span>Le mie materie</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>mio-calendario" class="<?= $page === 'mio-calendario' ? 'active' : '' ?>">
                        <i class="fas fa-calendar"></i>
                        <span>Mio calendario</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>miei-esami" class="<?= $page === 'miei-esami' ? 'active' : '' ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>I miei esami</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>eventi" class="<?= $page === 'eventi' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-day"></i>
                        <span>Eventi</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-label" style="padding-top:5px;">Account</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>profilo" class="<?= $page === 'profilo-docente' ? 'active' : '' ?>">
                        <i class="fas fa-user-circle"></i>
                        <span>Profilo</span>
                    </a>
                </li>
            </ul>

        <?php elseif ($currentModule === 'didattica'): ?>
            <!-- ===== MENU DIDATTICA (admin) ===== -->
            <div class="sidebar-menu-label">Didattica</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>home" class="<?= $page === 'home' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>bacheca" class="<?= $page === 'bacheca' ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn"></i>
                        <span>Bacheca</span>
                    </a>
                </li>
                <?php $percStato = $_GET['stato'] ?? ''; $isPerc = ($page === 'percorsi'); ?>
                <li class="has-submenu <?= $isPerc ? 'open' : '' ?>">
                    <a href="<?= BASE_URL ?>percorsi" class="submenu-toggle <?= $isPerc ? 'active' : '' ?>">
                        <i class="fas fa-route"></i>
                        <span>Percorsi Accademici</span>
                        <i class="fas fa-chevron-down submenu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li>
                            <a href="<?= BASE_URL ?>percorsi?stato=attivi" class="<?= ($isPerc && $percStato === 'attivi') ? 'active' : '' ?>">
                                <i class="fas fa-circle-play"></i><span>Percorsi attivi</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>percorsi?stato=passati" class="<?= ($isPerc && $percStato === 'passati') ? 'active' : '' ?>">
                                <i class="fas fa-clock-rotate-left"></i><span>Percorsi passati</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= BASE_URL ?>percorsi/create" class="<?= ($pageTitle ?? '') === 'Crea percorso' ? 'active' : '' ?>">
                                <i class="fas fa-plus"></i><span>Crea percorso</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>lezioni" class="<?= $page === 'lezioni' ? 'active' : '' ?>">
                        <i class="fas fa-book-open"></i>
                        <span>Lezioni</span>
                    </a>
                </li>
                <?php if ($userRuolo === 'admin'): ?>
                <li>
                    <a href="<?= BASE_URL ?>insegnanti" class="<?= $page === 'insegnanti' ? 'active' : '' ?>">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Insegnanti</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>studenti" class="<?= $page === 'studenti' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i>
                        <span>Studenti</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>assegna-studenti" class="<?= $page === 'assegna-studenti' ? 'active' : '' ?>">
                        <i class="fas fa-user-check"></i>
                        <span>Assegna a Percorso</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>calendario-presenze" class="<?= $page === 'calendario-presenze' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check"></i>
                        <span>Calendario Presenze</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>esami" class="<?= ($page ?? '') === 'esami' ? 'active' : '' ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>Esami</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>stage" class="<?= ($page ?? '') === 'stage' ? 'active' : '' ?>">
                        <i class="fas fa-briefcase"></i>
                        <span>Stage</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>eventi" class="<?= ($page ?? '') === 'eventi' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-day"></i>
                        <span>Eventi</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>importazioni" class="<?= $page === 'importazioni' ? 'active' : '' ?>">
                        <i class="fas fa-file-import"></i>
                        <span>Importazioni</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <?php if ($userRuolo === 'admin'): ?>
            <div class="sidebar-menu-label" style="padding-top:5px;">Esami di Stato</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>esami-di-stato-prova" class="<?= ($page ?? '') === 'esami-di-stato-prova' ? 'active' : '' ?>">
                        <i class="fas fa-landmark"></i>
                        <span>Esami di Stato</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>tutti-gli-esami-di-stato" class="<?= ($page ?? '') === 'tutti-gli-esami-di-stato' ? 'active' : '' ?>">
                        <i class="fas fa-list"></i>
                        <span>Tutti gli Esami di Stato</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-label" style="padding-top:5px;">Amministrazione</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>rette" class="<?= ($page ?? '') === 'rette' ? 'active' : '' ?>">
                        <i class="fas fa-euro-sign"></i>
                        <span>Rette e Pagamenti</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>diplomi" class="<?= ($page ?? '') === 'diplomi' ? 'active' : '' ?>">
                        <i class="fas fa-award"></i>
                        <span>Diplomi/Certificati</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-menu-label" style="padding-top:5px;">Configurazione</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="<?= BASE_URL ?>anno-scolastico" class="<?= $page === 'anno-scolastico' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Anno Scolastico</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>materie" class="<?= $page === 'materie' ? 'active' : '' ?>">
                        <i class="fas fa-atom"></i>
                        <span>Materie</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>sedi" class="<?= $page === 'sedi' ? 'active' : '' ?>">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Sedi</span>
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Footer utente -->
        <div class="sidebar-footer">
            <div class="user-info-box">
                <div class="user-avatar-small">
                    <i class="fas fa-user" style="color:white;font-size:0.75rem;"></i>
                </div>
                <div class="user-info-text">
                    <span class="user-info-name"><?= htmlspecialchars($userName) ?></span>
                    <span class="user-info-role"><?= htmlspecialchars($ruoloLabel) ?></span>
                </div>
            </div>
            <a href="<?= BASE_URL ?>auth/logout" class="logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Contenuto principale -->
    <div class="content-wrapper" id="content-wrapper">

        <!-- Mobile navbar -->
        <nav class="mobile-navbar d-md-none">
            <button class="btn btn-link text-dark p-0" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <span class="fw-bold" style="color:#0c1a3a;">Artemisys</span>
            <div></div>
        </nav>

        <!-- Area sostituita via PJAX (navigazione senza refresh) -->
        <div id="pjax-main">

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <?php if ($userRuolo === 'docente'): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>mie-lezioni"><i class="fas fa-home me-1"></i>Area Docente</a>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>switch"><i class="fas fa-th-large me-1"></i>Moduli</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>insegnanti">Didattica</a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($pageTitle)): ?>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
                <?php endif; ?>
            </ol>
        </nav>
