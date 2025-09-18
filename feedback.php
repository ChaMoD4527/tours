<?php
// Start the session and check if user is logged in
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db.php';

// Set active page for sidebar highlighting
$active_page = 'feedback';

// Fetch customers and tour packages for the dropdowns
try {
    $stmt = $conn->query("SELECT CustomersID, CustomerName FROM customers");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading customers: " . $e->getMessage();
    $customers = [];
}

try {
    $stmt = $conn->query("SELECT PackagesID, TourName FROM tourpackage");
    $tourpackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading tour packages: " . $e->getMessage();
    $tourpackages = [];
}

// Handle form submission for adding a new feedback
if (isset($_POST['add_feedback'])) {
    $customer_id = trim($_POST['customer_id'] ?? '');
    $package_id = trim($_POST['package_id'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $comments = trim($_POST['comments'] ?? '');

    // Basic validation
    if (empty($customer_id) || empty($package_id) || empty($rating) || empty($comments)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $error = "Rating must be a number between 1 and 5.";
    } else {
        try {
            // Insert the feedback into the database
            $stmt = $conn->prepare("INSERT INTO feedback (CustomerID, PackageID, Rating, Comments) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer_id, $package_id, $rating, $comments]);
            // Redirect with a success message
            header("Location: feedback.php?success=" . urlencode("Feedback added successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error adding feedback: " . $e->getMessage();
        }
    }
}

// Handle form submission for updating a feedback
if (isset($_POST['update_feedback'])) {
    $feedback_id = trim($_POST['feedback_id'] ?? '');
    $customer_id = trim($_POST['customer_id'] ?? '');
    $package_id = trim($_POST['package_id'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $comments = trim($_POST['comments'] ?? '');

    // Basic validation
    if (empty($feedback_id) || empty($customer_id) || empty($package_id) || empty($rating) || empty($comments)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $error = "Rating must be a number between 1 and 5.";
    } else {
        try {
            // Update the feedback in the database
            $stmt = $conn->prepare("UPDATE feedback SET CustomerID = ?, PackageID = ?, Rating = ?, Comments = ? WHERE FeedbackID = ?");
            $stmt->execute([$customer_id, $package_id, $rating, $comments, $feedback_id]);
            // Redirect with a success message
            header("Location: feedback.php?success=" . urlencode("Feedback updated successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating feedback: " . $e->getMessage();
        }
    }
}

// Handle feedback deletion
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM feedback WHERE FeedbackID = ?");
        $stmt->execute([$_GET['delete_id']]);
        header("Location: feedback.php?success=" . urlencode("Feedback deleted successfully!"));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting feedback: " . $e->getMessage();
    }
}

// Fetch all feedback with customer and tour package names
try {
    $stmt = $conn->query("
        SELECT f.FeedbackID, f.CustomerID, f.PackageID, f.Rating, f.Comments, 
               c.CustomerName, t.TourName 
        FROM feedback f
        LEFT JOIN customers c ON f.CustomerID = c.CustomersID
        LEFT JOIN tourpackage t ON f.PackageID = t.PackagesID
    ");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading feedback: " . $e->getMessage();
    $feedbacks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ExoticLanka Tours - Feedback</title>
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
        .rating-stars {
            color: #FFD700; /* Gold color for stars */
            font-size: 1.2em;
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
                        <a href="index.php" class="logo">
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
                            <h3 class="fw-bold mb-3">Feedback</h3>
                            <h6 class="op-7 mb-2">Manage Customer Feedback</h6>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addFeedbackModal">
                                <i class="fas fa-plus"></i> Add Feedback
                            </button>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Add Feedback Modal -->
                    <div class="modal fade" id="addFeedbackModal" tabindex="-1" aria-labelledby="addFeedbackModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addFeedbackModalLabel">Add New Feedback</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="addFeedbackForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="customer_id">Customer</label>
                                                    <select class="form-control" id="customer_id" name="customer_id" required>
                                                        <option value="">Select Customer</option>
                                                        <?php foreach ($customers as $customer): ?>
                                                            <option value="<?= htmlspecialchars($customer['CustomersID'], ENT_QUOTES, 'UTF-8') ?>">
                                                                <?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="package_id">Tour Package</label>
                                                    <select class="form-control" id="package_id" name="package_id" required>
                                                        <option value="">Select Tour Package</option>
                                                        <?php foreach ($tourpackages as $tourpackage): ?>
                                                            <option value="<?= htmlspecialchars($tourpackage['PackagesID'], ENT_QUOTES, 'UTF-8') ?>">
                                                                <?= htmlspecialchars($tourpackage['TourName'], ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="rating">Rating</label>
                                            <div class="rating-input">
                                                <select class="form-control" id="rating" name="rating" required>
                                                    <option value="">Select Rating (1-5)</option>
                                                    <option value="1">1 Star</option>
                                                    <option value="2">2 Stars</option>
                                                    <option value="3">3 Stars</option>
                                                    <option value="4">4 Stars</option>
                                                    <option value="5">5 Stars</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="comments">Comments</label>
                                            <textarea class="form-control" id="comments" name="comments" rows="4" required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="add_feedback" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Feedback
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Feedback Modal -->
                    <div class="modal fade" id="updateFeedbackModal" tabindex="-1" aria-labelledby="updateFeedbackModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateFeedbackModalLabel">Update Feedback</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="updateFeedbackForm">
                                        <input type="hidden" name="feedback_id" id="update_feedback_id">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="update_customer_id">Customer</label>
                                                    <select class="form-control" id="update_customer_id" name="customer_id" required>
                                                        <option value="">Select Customer</option>
                                                        <?php foreach ($customers as $customer): ?>
                                                            <option value="<?= htmlspecialchars($customer['CustomersID'], ENT_QUOTES, 'UTF-8') ?>">
                                                                <?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="update_package_id">Tour Package</label>
                                                    <select class="form-control" id="update_package_id" name="package_id" required>
                                                        <option value="">Select Tour Package</option>
                                                        <?php foreach ($tourpackages as $tourpackage): ?>
                                                            <option value="<?= htmlspecialchars($tourpackage['PackagesID'], ENT_QUOTES, 'UTF-8') ?>">
                                                                <?= htmlspecialchars($tourpackage['TourName'], ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_rating">Rating</label>
                                            <div class="rating-input">
                                                <select class="form-control" id="update_rating" name="rating" required>
                                                    <option value="">Select Rating (1-5)</option>
                                                    <option value="1">1 Star</option>
                                                    <option value="2">2 Stars</option>
                                                    <option value="3">3 Stars</option>
                                                    <option value="4">4 Stars</option>
                                                    <option value="5">5 Stars</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_comments">Comments</label>
                                            <textarea class="form-control" id="update_comments" name="comments" rows="4" required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update_feedback" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Update Feedback
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Customer Feedback</div>
                                        <div class="card-tools">
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="Search feedback..." id="feedbackSearch">
                                                <button class="btn btn-outline-secondary" type="button">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="feedbackTable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Customer</th>
                                                    <th>Tour Package</th>
                                                    <th>Rating</th>
                                                    <th>Comments</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($feedbacks as $feedback): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($feedback['FeedbackID'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($feedback['CustomerName'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($feedback['TourName'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <div class="rating-stars">
                                                            <?php 
                                                            $rating = isset($feedback['Rating']) ? (int)$feedback['Rating'] : 0;
                                                            echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($feedback['Comments'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button class="btn btn-update btn-sm" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#updateFeedbackModal"
                                                                    data-id="<?= htmlspecialchars($feedback['FeedbackID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-customerid="<?= htmlspecialchars($feedback['CustomerID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-packageid="<?= htmlspecialchars($feedback['PackageID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-rating="<?= htmlspecialchars($feedback['Rating'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-comments="<?= htmlspecialchars($feedback['Comments'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                                <i class="fas fa-edit"></i> Update
                                                            </button>
                                                            <a href="?delete_id=<?= htmlspecialchars($feedback['FeedbackID'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                                               class="btn btn-danger btn-sm"
                                                               onclick="return confirm('Are you sure you want to delete this feedback?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </div>
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
            $('#feedbackTable').DataTable({
                responsive: true,
                "pageLength": 10,
                "language": {
                    "search": "_INPUT_",
                    "searchPlaceholder": "Search feedback...",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": {
                        "previous": "<i class='fas fa-angle-left'></i>",
                        "next": "<i class='fas fa-angle-right'></i>"
                    }
                },
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                       "<'row'<'col-sm-12'tr>>" +
                       "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                "initComplete": function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

            // Reset form when add modal is closed
            $('#addFeedbackModal').on('hidden.bs.modal', function () {
                $('#addFeedbackForm')[0].reset();
            });

            // Reset form when update modal is closed
            $('#updateFeedbackModal').on('hidden.bs.modal', function () {
                $('#updateFeedbackForm')[0].reset();
            });

            // Populate the update feedback modal with feedback data
            $('#updateFeedbackModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');
                var customerid = button.data('customerid');
                var packageid = button.data('packageid');
                var rating = button.data('rating');
                var comments = button.data('comments');

                var modal = $(this);
                modal.find('#update_feedback_id').val(id);
                modal.find('#update_customer_id').val(customerid);
                modal.find('#update_package_id').val(packageid);
                modal.find('#update_rating').val(rating);
                modal.find('#update_comments').val(comments);
            });

            // Initialize rating input for add modal
            $('#rating').on('change', function() {
                const rating = $(this).val();
                $('.rating-preview').html('').append(
                    Array(parseInt(rating)).fill('<i class="fas fa-star"></i>').join('') +
                    Array(5 - parseInt(rating)).fill('<i class="far fa-star"></i>').join('')
                );
            });

            // Initialize rating input for update modal
            $('#update_rating').on('change', function() {
                const rating = $(this).val();
                $('.rating-preview').html('').append(
                    Array(parseInt(rating)).fill('<i class="fas fa-star"></i>').join('') +
                    Array(5 - parseInt(rating)).fill('<i class="far fa-star"></i>').join('')
                );
            });
        });
    </script>
</body>
</html>