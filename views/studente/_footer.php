    </div><!-- /stu-content -->

    <?php $cur = $page ?? ''; ?>

    <!-- Mobile bottom nav (hidden on desktop) -->
    <nav class="stu-bottomnav d-lg-none">
        <a href="<?= BASE_URL ?>studente" class="<?= $cur === 'studente-home' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?= BASE_URL ?>studente/lezioni" class="<?= $cur === 'studente-lezioni' ? 'active' : '' ?>">
            <i class="fas fa-book-open"></i>
            <span>Lezioni</span>
        </a>
        <a href="<?= BASE_URL ?>studente/voti" class="<?= $cur === 'studente-voti' ? 'active' : '' ?>">
            <i class="fas fa-star"></i>
            <span>Voti</span>
        </a>
        <a href="<?= BASE_URL ?>bacheca" class="<?= $cur === 'bacheca' ? 'active' : '' ?>">
            <i class="fas fa-bullhorn"></i>
            <span>Bacheca</span>
        </a>
        <a href="<?= BASE_URL ?>studente/eventi" class="<?= $cur === 'studente-eventi' ? 'active' : '' ?>">
            <i class="fas fa-calendar-day"></i>
            <span>Eventi</span>
        </a>
    </nav>

</div><!-- /stu-main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
