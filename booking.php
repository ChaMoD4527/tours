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
$active_page = 'bookings';

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

// Handle form submission for adding a new booking
if (isset($_POST['add_booking'])) {
    $customer_id = trim($_POST['customer_id'] ?? '');
    $package_id = trim($_POST['package_id'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Basic validation
    if (empty($customer_id) || empty($package_id) || empty($booking_date) || empty($status)) {
        $error = "All fields are required.";
    } else {
        try {
            // Insert the booking into the database
            $stmt = $conn->prepare("INSERT INTO booking (CustomerID, PackagesID, BookingDate, STATUS) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer_id, $package_id, $booking_date, $status]);
            // Redirect with a success message
            header("Location: booking.php?success=" . urlencode("Booking added successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error adding booking: " . $e->getMessage();
        }
    }
}

// Handle form submission for updating a booking
if (isset($_POST['update_booking'])) {
    $booking_id = trim($_POST['booking_id'] ?? '');
    $customer_id = trim($_POST['customer_id'] ?? '');
    $package_id = trim($_POST['package_id'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Basic validation
    if (empty($booking_id) || empty($customer_id) || empty($package_id) || empty($booking_date) || empty($status)) {
        $error = "All fields are required.";
    } else {
        try {
            // Update the booking in the database
            $stmt = $conn->prepare("UPDATE booking SET CustomerID = ?, PackagesID = ?, BookingDate = ?, STATUS = ? WHERE BookingID = ?");
            $stmt->execute([$customer_id, $package_id, $booking_date, $status, $booking_id]);
            // Redirect with a success message
            header("Location: booking.php?success=" . urlencode("Booking updated successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating booking: " . $e->getMessage();
        }
    }
}

// Handle booking deletion
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM booking WHERE BookingID = ?");
        $stmt->execute([$_GET['delete_id']]);
        header("Location: booking.php?success=" . urlencode("Booking deleted successfully!"));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting booking: " . $e->getMessage();
    }
}

// Fetch all bookings with customer and tour package names
try {
    $stmt = $conn->query("
        SELECT b.BookingID, b.CustomerID, b.PackagesID, b.BookingDate, b.STATUS, 
               c.CustomerName, t.TourName 
        FROM booking b
        LEFT JOIN customers c ON b.CustomerID = c.CustomersID
        LEFT JOIN tourpackage t ON b.PackagesID = t.PackagesID
    ");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading bookings: " . $e->getMessage();
    $bookings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ExoticLanka Tours - Bookings</title>
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
                            <h3 class="fw-bold mb-3">Bookings</h3>
                            <h6 class="op-7 mb-2">Manage Booking Information</h6>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addBookingModal">Add Booking</button>
                            <a href="logout.php" class="btn btn-danger btn-round ms-2">Logout</a>
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

                    <!-- Add Booking Modal -->
                    <div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addBookingModalLabel">Add New Booking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="addBookingForm">
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
                                        <div class="form-group mb-3">
                                            <label for="booking_date">Booking Date</label>
                                            <input type="date" class="form-control" id="booking_date" name="booking_date" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="status">Status</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Confirmed">Confirmed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="add_booking" class="btn btn-primary btn-round">Save Booking</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Booking Modal -->
                    <div class="modal fade" id="updateBookingModal" tabindex="-1" aria-labelledby="updateBookingModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateBookingModalLabel">Update Booking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="updateBookingForm">
                                        <input type="hidden" name="booking_id" id="update_booking_id">
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
                                        <div class="form-group mb-3">
                                            <label for="update_booking_date">Booking Date</label>
                                            <input type="date" class="form-control" id="update_booking_date" name="booking_date" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_status">Status</label>
                                            <select class="form-control" id="update_status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Confirmed">Confirmed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_booking" class="btn btn-primary btn-round">Update Booking</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Booking ID</th>
                                                    <th>Customer</th>
                                                    <th>Tour Package</th>
                                                    <th>Booking Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($bookings as $booking): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($booking['BookingID'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($booking['CustomerName'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($booking['TourName'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($booking['BookingDate'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($booking['STATUS'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <button class="btn btn-update btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updateBookingModal"
                                                                data-id="<?= htmlspecialchars($booking['BookingID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-customerid="<?= htmlspecialchars($booking['CustomerID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-packageid="<?= htmlspecialchars($booking['PackagesID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-bookingdate="<?= htmlspecialchars($booking['BookingDate'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-status="<?= htmlspecialchars($booking['STATUS'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            Update
                                                        </button>
                                                        <a href="?delete_id=<?= htmlspecialchars($booking['BookingID'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this booking?')">
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

            // Reset the add booking form after submission
            $('#addBookingForm').on('submit', function() {
                setTimeout(function() {
                    $('#addBookingForm')[0].reset();
                    $('#addBookingModal').modal('hide');
                }, 1000);
            });

            // Populate the update booking modal with booking data
            $('#updateBookingModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');
                var customerid = button.data('customerid');
                var packageid = button.data('packageid');
                var bookingdate = button.data('bookingdate');
                var status = button.data('status');

                var modal = $(this);
                modal.find('#update_booking_id').val(id);
                modal.find('#update_customer_id').val(customerid);
                modal.find('#update_package_id').val(packageid);
                modal.find('#update_booking_date').val(bookingdate);
                modal.find('#update_status').val(status);
            });

            // Reset the update booking form after submission
            $('#updateBookingForm').on('submit', function() {
                setTimeout(function() {
                    $('#updateBookingForm')[0].reset();
                    $('#updateBookingModal').modal('hide');
                }, 1000);
            });
        });
    </script>
</body>
</html>