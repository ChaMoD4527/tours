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
$active_page = 'activity';

// Handle form submission for adding a new activity
if (isset($_POST['add_activity'])) {
    $activity_name = trim($_POST['activity_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Basic validation
    if (empty($activity_name) || empty($description)) {
        $error = "All fields are required.";
    } else {
        try {
            // Insert the activity into the database
            $stmt = $conn->prepare("INSERT INTO activity (ActivityName, Description) VALUES (?, ?)");
            $stmt->execute([$activity_name, $description]);
            // Redirect with a success message
            header("Location: activity.php?success=" . urlencode("Activity added successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error adding activity: " . $e->getMessage();
        }
    }
}

// Handle form submission for updating an activity
if (isset($_POST['update_activity'])) {
    $activity_id = trim($_POST['activity_id'] ?? '');
    $activity_name = trim($_POST['activity_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Basic validation
    if (empty($activity_id) || empty($activity_name) || empty($description)) {
        $error = "All fields are required.";
    } else {
        try {
            // Update the activity in the database
            $stmt = $conn->prepare("UPDATE activity SET ActivityName = ?, Description = ? WHERE ActivityID = ?");
            $stmt->execute([$activity_name, $description, $activity_id]);
            // Redirect with a success message
            header("Location: activity.php?success=" . urlencode("Activity updated successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating activity: " . $e->getMessage();
        }
    }
}

// Handle activity deletion
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM activity WHERE ActivityID = ?");
        $stmt->execute([$_GET['delete_id']]);
        header("Location: activity.php?success=" . urlencode("Activity deleted successfully!"));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting activity: " . $e->getMessage();
    }
}

// Fetch all activities
try {
    $stmt = $conn->query("SELECT * FROM activity");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading activities: " . $e->getMessage();
    $activities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ExoticLanka Tours - Activities</title>
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
                            <h3 class="fw-bold mb-3">Activities</h3>
                            <h6 class="op-7 mb-2">Manage Activity Information</h6>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addActivityModal">Add Activity</button>
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

                    <!-- Add Activity Modal -->
                    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addActivityModalLabel">Add New Activity</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="addActivityForm">
                                        <div class="form-group mb-3">
                                            <label for="activity_name">Activity Name</label>
                                            <input type="text" class="form-control" id="activity_name" name="activity_name" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description" required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="add_activity" class="btn btn-primary btn-round">Save Activity</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Activity Modal -->
                    <div class="modal fade" id="updateActivityModal" tabindex="-1" aria-labelledby="updateActivityModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateActivityModalLabel">Update Activity</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="updateActivityForm">
                                        <input type="hidden" name="activity_id" id="update_activity_id">
                                        <div class="form-group mb-3">
                                            <label for="update_activity_name">Activity Name</label>
                                            <input type="text" class="form-control" id="update_activity_name" name="activity_name" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_description">Description</label>
                                            <textarea class="form-control" id="update_description" name="description" required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update_activity" class="btn btn-primary btn-round">Update Activity</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activities Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Activity ID</th>
                                                    <th>Activity Name</th>
                                                    <th>Description</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($activities as $activity): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($activity['ActivityID'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($activity['ActivityName'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($activity['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button class="btn btn-update btn-sm" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#updateActivityModal"
                                                                    data-id="<?= htmlspecialchars($activity['ActivityID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-name="<?= htmlspecialchars($activity['ActivityName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-description="<?= htmlspecialchars($activity['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                                Update
                                                            </button>
                                                            <a href="?delete_id=<?= htmlspecialchars($activity['ActivityID'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                                               class="btn btn-danger btn-sm"
                                                               onclick="return confirm('Are you sure you want to delete this activity?')">
                                                                Delete
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

            // Reset the add activity form after submission
            $('#addActivityForm').on('submit', function() {
                setTimeout(function() {
                    $('#addActivityForm')[0].reset();
                    $('#addActivityModal').modal('hide');
                }, 1000);
            });

            // Reset the update activity form after submission
            $('#updateActivityForm').on('submit', function() {
                setTimeout(function() {
                    $('#updateActivityForm')[0].reset();
                    $('#updateActivityModal').modal('hide');
                }, 1000);
            });

            // Populate the update activity modal with activity data
            $('#updateActivityModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');
                var name = button.data('name');
                var description = button.data('description');

                var modal = $(this);
                modal.find('#update_activity_id').val(id);
                modal.find('#update_activity_name').val(name);
                modal.find('#update_description').val(description);
            });

            // Reset the update modal form when closed
            $('#updateActivityModal').on('hidden.bs.modal', function () {
                $('#updateActivityForm')[0].reset();
            });
        });
    </script>
</body>
</html>