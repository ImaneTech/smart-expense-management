<!-- dashboard.php - Example usage -->
<?php include 'includes\header.php'; ?>
<?php include 'includes\sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <h1 class="mb-2">Dashboard</h1>
        <p class="text-muted mb-4">Welcome back! Here's an overview of your expense requests.</p>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-muted mb-0">Total Requests</h6>
                            <i class="fas fa-file-alt text-primary"></i>
                        </div>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-muted mb-0">Pending</h6>
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-muted mb-0">Approved</h6>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="text-muted mb-0">Rejected</h6>
                            <i class="fas fa-times-circle text-danger"></i>
                        </div>
                        <h2 class="mb-0">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-dollar-sign text-primary fs-4 me-2"></i>
                    <h5 class="mb-0">Total Amount</h5>
                </div>
                <p class="text-muted small mb-3">Total amount of all your expense requests</p>
                <h2 class="text-primary mb-0">$0.00</h2>
            </div>
        </div>
    </div>
</main>

<?php include 'includes\footer.php'; ?>