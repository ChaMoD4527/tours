<?php
// Start the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

// Set active page for sidebar highlighting
$active_page = 'tourpackages';

// Handle form submission for adding a new tour package
if (isset($_POST['add_tourpackage'])) {
    $tour_name = trim($_POST['tour_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $duration = trim($_POST['duration'] ?? '');

    // Basic validation
    if (empty($tour_name) || empty($description) || empty($price) || empty($duration)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number.";
    } elseif (!is_numeric($duration) || $duration <= 0) {
        $error = "Duration must be a positive number.";
    } else {
        try {
            // Insert the tour package into the database
            $stmt = $conn->prepare("INSERT INTO tourpackage (TourName, Description, Price, Duration) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tour_name, $description, $price, $duration]);
            // Redirect with a success message
            header("Location: tourpackage.php?success=" . urlencode("Tour package added successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error adding tour package: " . $e->getMessage();
        }
    }
}

// Handle form submission for updating a tour package
if (isset($_POST['update_tourpackage'])) {
    $package_id = trim($_POST['package_id'] ?? '');
    $tour_name = trim($_POST['tour_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $duration = trim($_POST['duration'] ?? '');

    // Basic validation
    if (empty($package_id) || empty($tour_name) || empty($description) || empty($price) || empty($duration)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number.";
    } elseif (!is_numeric($duration) || $duration <= 0) {
        $error = "Duration must be a positive number.";
    } else {
        try {
            // Update the tour package in the database
            $stmt = $conn->prepare("UPDATE tourpackage SET TourName = ?, Description = ?, Price = ?, Duration = ? WHERE PackagesID = ?");
            $stmt->execute([$tour_name, $description, $price, $duration, $package_id]);
            // Redirect with a success message
            header("Location: tourpackage.php?success=" . urlencode("Tour package updated successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating tour package: " . $e->getMessage();
        }
    }
}

// Handle tour package deletion
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM tourpackage WHERE PackagesID = ?");
        $stmt->execute([$_GET['delete_id']]);
        header("Location: tourpackage.php?success=" . urlencode("Tour package deleted successfully!"));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting tour package: " . $e->getMessage();
    }
}

// Fetch all tour packages
try {
    $stmt = $conn->query("SELECT * FROM tourpackage");
    $tourpackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading tour packages: " . $e->getMessage();
    $tourpackages = [];
}

// Handle logout (optional, if you want logout functionality here)
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = [];
    // Destroy the session
    session_destroy();
    // Redirect to login page
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ExoticLanka Tours - Tour Packages</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["assets/css/fonts.min.css"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <style>
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff !important;
            font-weight: bold;
        }
        .btn-update {
            background-color: #007bff;
            color: white;
        }
        .btn-update:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="dark">
                        <a href="dashboard.php" class="logo">
                            <img src="assets/img/kaiadmin/exoticlanka.png" alt="ExoticLanka Tours" class="navbar-brand" height="200" />
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar">
                                <i class="gg-menu-right"></i>
                            </button>
                            <button class="btn btn-toggle sidenav-toggler">
                                <i class="gg-menu-left"></i>
                            </button>
                        </div>
                        <button class="topbar-toggler more">
                            <i class="gg-more-vertical-alt"></i>
                        </button>
                    </div>
                </div>
                <!-- Include Navbar -->
                <?php include 'navbar.php'; ?>
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Tour Packages</h3>
                            <h6 class="op-7 mb-2">Manage Tour Package Information</h6>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addTourPackageModal">Add Tour Package</button>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Tour Package Modal -->
                    <div class="modal fade" id="addTourPackageModal" tabindex="-1" aria-labelledby="addTourPackageModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addTourPackageModalLabel">Add New Tour Package</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="addTourPackageForm">
                                        <div class="form-group mb-3">
                                            <label for="tour_name">Tour Name</label>
                                            <input type="text" class="form-control" id="tour_name" name="tour_name" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description" required></textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="price">Price (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="duration">Duration (Days)</label>
                                            <input type="number" class="form-control" id="duration" name="duration" required>
                                        </div>
                                        <button type="submit" name="add_tourpackage" class="btn btn-primary btn-round">Save Tour Package</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Tour Package Modal -->
                    <div class="modal fade" id="updateTourPackageModal" tabindex="-1" aria-labelledby="updateTourPackageModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateTourPackageModalLabel">Update Tour Package</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="updateTourPackageForm">
                                        <input type="hidden" name="package_id" id="update_package_id">
                                        <div class="form-group mb-3">
                                            <label for="update_tour_name">Tour Name</label>
                                            <input type="text" class="form-control" id="update_tour_name" name="tour_name" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_description">Description</label>
                                            <textarea class="form-control" id="update_description" name="description" required></textarea>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_price">Price (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" id="update_price" name="price" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_duration">Duration (Days)</label>
                                            <input type="number" class="form-control" id="update_duration" name="duration" required>
                                        </div>
                                        <button type="submit" name="update_tourpackage" class="btn btn-primary btn-round">Update Tour Package</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tour Packages Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Package ID</th>
                                                    <th>Tour Name</th>
                                                    <th>Description</th>
                                                    <th>Price (LKR)</th>
                                                    <th>Duration (Days)</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tourpackages as $tourpackage): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($tourpackage['PackagesID'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($tourpackage['TourName'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($tourpackage['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($tourpackage['Price'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($tourpackage['Duration'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <button class="btn btn-update btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updateTourPackageModal"
                                                                data-id="<?= htmlspecialchars($tourpackage['PackagesID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-tourname="<?= htmlspecialchars($tourpackage['TourName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-description="<?= htmlspecialchars($tourpackage['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-price="<?= htmlspecialchars($tourpackage['Price'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-duration="<?= htmlspecialchars($tourpackage['Duration'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            Update
                                                        </button>
                                                        <a href="?delete_id=<?= htmlspecialchars($tourpackage['PackagesID'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this tour package?')">
                                                            Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('table').DataTable({
                "pageLength": 10,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "previous": "Previous",
                        "next": "Next"
                    }
                }
            });

            // Reset the add tour package form after submission
            $('#addTourPackageForm').on('submit', function() {
                setTimeout(function() {
                    $('#addTourPackageForm')[0].reset();
                    $('#addTourPackageModal').modal('hide');
                }, 1000);
            });

            // Populate the update tour package modal with tour package data
            $('#updateTourPackageModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');
                var tourname = button.data('tourname');
                var description = button.data('description');
                var price = button.data('price');
                var duration = button.data('duration');

                var modal = $(this);
                modal.find('#update_package_id').val(id);
                modal.find('#update_tour_name').val(tourname);
                modal.find('#update_description').val(description);
                modal.find('#update_price').val(price);
                modal.find('#update_duration').val(duration);
            });

            // Reset the update tour package form after submission
            $('#updateTourPackageForm').on('submit', function() {
                setTimeout(function() {
                    $('#updateTourPackageForm')[0].reset();
                    $('#updateTourPackageModal').modal('hide');
                }, 1000);
            });
        });
    </script>
</body>
</html>