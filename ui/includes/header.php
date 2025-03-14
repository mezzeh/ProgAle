<nav>
    <ul class="main-menu">
        <li><a href="<?php echo $base_path; ?>pages/index.php">Home</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo $base_path; ?>pages/my_piani.php">I Miei Piani</a></li>
            <li><a href="<?php echo $base_path; ?>pages/esami.php">Esami</a></li>
            <li><a href="<?php echo $base_path; ?>pages/argomenti.php">Argomenti</a></li>
            <li><a href="<?php echo $base_path; ?>pages/sottoargomenti.php">Sottoargomenti</a></li>
            <li><a href="<?php echo $base_path; ?>pages/esercizi.php">Esercizi</a></li>
            <li><a href="<?php echo $base_path; ?>pages/requisiti.php">Requisiti</a></li>
            <li><a href="<?php echo $base_path; ?>pages/formule.php">Formule</a></li>
            <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <li><a href="<?php echo $base_path; ?>pages/admin/users.php">Gestione Utenti</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $base_path; ?>pages/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="<?php echo $base_path; ?>pages/login.php">Accedi</a></li>
            <li><a href="<?php echo $base_path; ?>pages/register.php">Registrati</a></li>
        <?php endif; ?>
        <li class="search-item">
            <form action="<?php echo $base_path; ?>pages/search.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Cerca in tutto il sistema..." required>
                <button type="submit">Cerca</button>
            </form>
        </li>
    </ul>
</nav>