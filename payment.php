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
$active_page = 'payments';

// Fetch bookings for the dropdown
try {
    $stmt = $conn->query("
        SELECT b.BookingID, c.CustomerName, t.TourName 
        FROM booking b
        LEFT JOIN customers c ON b.CustomerID = c.CustomersID
        LEFT JOIN tourpackage t ON b.PackagesID = t.PackagesID
    ");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading bookings: " . $e->getMessage();
    $bookings = [];
}

// Handle form submission for adding a new payment
if (isset($_POST['add_payment'])) {
    $booking_id = trim($_POST['booking_id'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    // Basic validation
    if (empty($booking_id) || empty($amount) || empty($payment_date) || empty($payment_method)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number.";
    } else {
        try {
            // Insert the payment into the database
            $stmt = $conn->prepare("INSERT INTO payment (BookingID, Amount, PaymentDate, PaymentMethod) VALUES (?, ?, ?, ?)");
            $stmt->execute([$booking_id, $amount, $payment_date, $payment_method]);
            // Redirect with a success message
            header("Location: payment.php?success=" . urlencode("Payment added successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error adding payment: " . $e->getMessage();
        }
    }
}

// Handle form submission for updating a payment
if (isset($_POST['update_payment'])) {
    $payment_id = trim($_POST['payment_id'] ?? '');
    $booking_id = trim($_POST['booking_id'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    // Debug: Log the values being sent to the UPDATE query
    error_log("Updating Payment - PaymentID: $payment_id, BookingID: $booking_id, Amount: $amount, PaymentDate: $payment_date, PaymentMethod: $payment_method");

    // Basic validation
    if (empty($payment_id) || empty($booking_id) || empty($amount) || empty($payment_date) || empty($payment_method)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number.";
    } else {
        try {
            // Update the payment in the database
            $stmt = $conn->prepare("UPDATE payment SET BookingID = ?, Amount = ?, PaymentDate = ?, PaymentMethod = ? WHERE PaymentID = ?");
            $stmt->execute([$booking_id, $amount, $payment_date, $payment_method, $payment_id]);
            // Debug: Log the number of affected rows
            error_log("Update successful - Affected rows: " . $stmt->rowCount());
            // Redirect with a success message
            header("Location: payment.php?success=" . urlencode("Payment updated successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating payment: " . $e->getMessage();
        }
    }
}

// Handle payment deletion
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM payment WHERE PaymentID = ?");
        $stmt->execute([$_GET['delete_id']]);
        header("Location: payment.php?success=" . urlencode("Payment deleted successfully!"));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting payment: " . $e->getMessage();
    }
}

// Fetch all payments with booking, customer, and tour package details
try {
    $stmt = $conn->query("
        SELECT p.PaymentID, p.BookingID, p.Amount, p.PaymentDate, p.PaymentMethod,
               b.CustomerID, b.PackagesID, c.CustomerName, t.TourName
        FROM payment p
        LEFT JOIN booking b ON p.BookingID = b.BookingID
        LEFT JOIN customers c ON b.CustomerID = c.CustomersID
        LEFT JOIN tourpackage t ON b.PackagesID = t.PackagesID
    ");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log the fetched payments to check the PaymentMethod value
    error_log("Fetched Payments: " . print_r($payments, true));
} catch (PDOException $e) {
    $error = "Error loading payments: " . $e->getMessage();
    $payments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ExoticLanka Tours - Payments</title>
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
                            <h3 class="fw-bold mb-3">Payments</h3>
                            <h6 class="op-7 mb-2">Manage Payment Information</h6>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>
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

                    <!-- Add Payment Modal -->
                    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="addPaymentForm">
                                        <div class="form-group mb-3">
                                            <label for="booking_id">Booking</label>
                                            <select class="form-control" id="booking_id" name="booking_id" required>
                                                <option value="">Select Booking</option>
                                                <?php foreach ($bookings as $booking): ?>
                                                    <option value="<?= htmlspecialchars($booking['BookingID'], ENT_QUOTES, 'UTF-8') ?>">
                                                        Booking #<?= htmlspecialchars($booking['BookingID'], ENT_QUOTES, 'UTF-8') ?> - 
                                                        <?= htmlspecialchars($booking['CustomerName'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($booking['TourName'], ENT_QUOTES, 'UTF-8') ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="amount">Amount (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="payment_date">Payment Date</label>
                                            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="payment_method">Payment Method</label>
                                            <select class="form-control" id="payment_method" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="Debit Card">Debit Card</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="Cash">Cash</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="add_payment" class="btn btn-primary btn-round">Save Payment</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Payment Modal -->
                    <div class="modal fade" id="updatePaymentModal" tabindex="-1" aria-labelledby="updatePaymentModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updatePaymentModalLabel">Update Payment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="updatePaymentForm">
                                        <input type="hidden" name="payment_id" id="update_payment_id">
                                        <div class="form-group mb-3">
                                            <label for="update_booking_id">Booking</label>
                                            <select class="form-control" id="update_booking_id" name="booking_id" required>
                                                <option value="">Select Booking</option>
                                                <?php foreach ($bookings as $booking): ?>
                                                    <option value="<?= htmlspecialchars($booking['BookingID'], ENT_QUOTES, 'UTF-8') ?>">
                                                        Booking #<?= htmlspecialchars($booking['BookingID'], ENT_QUOTES, 'UTF-8') ?> - 
                                                        <?= htmlspecialchars($booking['CustomerName'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($booking['TourName'], ENT_QUOTES, 'UTF-8') ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_amount">Amount (LKR)</label>
                                            <input type="number" step="0.01" class="form-control" id="update_amount" name="amount" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_payment_date">Payment Date</label>
                                            <input type="date" class="form-control" id="update_payment_date" name="payment_date" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_payment_method">Payment Method</label>
                                            <select class="form-control" id="update_payment_method" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="Debit Card">Debit Card</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="Cash">Cash</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_payment" class="btn btn-primary btn-round">Update Payment</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Payment ID</th>
                                                    <th>Booking</th>
                                                    <th>Customer</th>
                                                    <th>Tour Package</th>
                                                    <th>Amount (LKR)</th>
                                                    <th>Payment Date</th>
                                                    <th>Payment Method</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($payment['PaymentID'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($payment['BookingID'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($payment['CustomerName'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($payment['TourName'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($payment['Amount'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($payment['PaymentDate'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($payment['PaymentMethod'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <button class="btn btn-update btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updatePaymentModal"
                                                                data-id="<?= htmlspecialchars($payment['PaymentID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-bookingid="<?= htmlspecialchars($payment['BookingID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-amount="<?= htmlspecialchars($payment['Amount'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-paymentdate="<?= htmlspecialchars($payment['PaymentDate'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-paymentmethod="<?= htmlspecialchars($payment['PaymentMethod'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            Update
                                                        </button>
                                                        <a href="?delete_id=<?= htmlspecialchars($payment['PaymentID'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this payment?')">
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

            // Reset the add payment form after submission
            $('#addPaymentForm').on('submit', function() {
                setTimeout(function() {
                    $('#addPaymentForm')[0].reset();
                    $('#addPaymentModal').modal('hide');
                }, 1000);
            });

            // Populate the update payment modal with payment data
            $('#updatePaymentModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');
                var bookingid = button.data('bookingid');
                var amount = button.data('amount');
                var paymentdate = button.data('paymentdate');
                var paymentmethod = button.data('paymentmethod');

                var modal = $(this);
                modal.find('#update_payment_id').val(id);
                modal.find('#update_booking_id').val(bookingid);
                modal.find('#update_amount').val(amount);
                modal.find('#update_payment_date').val(paymentdate);
                modal.find('#update_payment_method').val(paymentmethod);
            });

            // Reset the update payment form after submission
            $('#updatePaymentForm').on('submit', function() {
                setTimeout(function() {
                    $('#updatePaymentForm')[0].reset();
                    $('#updatePaymentModal').modal('hide');
                }, 1000);
            });
        });
    </script>
</body>
</html>