<?php
// Get the correct base path for assets
$current_dir = dirname($_SERVER['PHP_SELF']);
if ($current_dir == '/' || strpos($current_dir, '/S') === false) {
    $asset_path = '';
} else {
    $depth = substr_count($current_dir, '/') - 1;
    $asset_path = str_repeat('../', $depth);
}
?>
<header class="bg-white shadow-sm border-bottom">
    <div class="container-fluid">
        <div class="row align-items-center py-3">
            <div class="col">
                <div class="d-flex align-items-center">
                    <img src="<?php echo $asset_path; ?>assets/images/DonBosco-Color_200px.png" alt="Student Polling System Logo" style="height: 50px;" class="me-3">
                    <div>
                        <h4 class="mb-0 fw-bold text-primary">ADBU Student Polling and Survey System</h4>
                        <small class="text-muted">Empowering Student Voices</small>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="theme-toggle">
                    <button class="btn btn-outline-secondary" id="themeToggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>
