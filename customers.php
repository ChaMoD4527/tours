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
$active_page = 'customers';

// Handle logout
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = [];
    // Destroy the session
    session_destroy();
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Handle form submission for adding a new customer
if (isset($_POST['add_customer'])) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    // Map "Male" and "Female" to 'M' and 'F' to match the database CHAR(1) type
    $gender = ($gender === 'Male') ? 'M' : ($gender === 'Female' ? 'F' : '');

    // Basic validation
    if (empty($customer_name) || empty($nationality) || empty($contact_no) || empty($email) || empty($gender)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($gender, ['M', 'F'])) {
        $error = "Invalid gender selection.";
    } else {
        try {
            // Use the correct column names from the database
            $stmt = $conn->prepare("INSERT INTO customers (CustomerName, Nationality, ContactNo, Email, gender) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$customer_name, $nationality, $contact_no, $email, $gender]);
            // Redirect with a success message
            header("Location: customers.php?success=" . urlencode("Customer added successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error adding customer: " . $e->getMessage();
        }
    }
}

// Handle form submission for updating a customer
if (isset($_POST['update_customer'])) {
    $customer_id = trim($_POST['customer_id'] ?? '');
    $customer_name = trim($_POST['customer_name'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    // Map "Male" and "Female" to 'M' and 'F' to match the database CHAR(1) type
    $gender = ($gender === 'Male') ? 'M' : ($gender === 'Female' ? 'F' : '');

    // Basic validation
    if (empty($customer_id) || empty($customer_name) || empty($nationality) || empty($contact_no) || empty($email) || empty($gender)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($gender, ['M', 'F'])) {
        $error = "Invalid gender selection.";
    } else {
        try {
            // Update the customer in the database
            $stmt = $conn->prepare("UPDATE customers SET CustomerName = ?, Nationality = ?, ContactNo = ?, Email = ?, gender = ? WHERE CustomersID = ?");
            $stmt->execute([$customer_name, $nationality, $contact_no, $email, $gender, $customer_id]);
            // Redirect with a success message
            header("Location: customers.php?success=" . urlencode("Customer updated successfully!"));
            exit();
        } catch (PDOException $e) {
            $error = "Error updating customer: " . $e->getMessage();
        }
    }
}

// Handle customer deletion
if (isset($_GET['delete_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM customers WHERE CustomersID = ?");
        $stmt->execute([$_GET['delete_id']]);
        header("Location: customers.php?success=" . urlencode("Customer deleted successfully!"));
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting customer: " . $e->getMessage();
    }
}

// Fetch all customers
try {
    $stmt = $conn->query("SELECT * FROM customers");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Check if ContactNo and gender are in the fetched data
    if (empty($customers)) {
        $error = "No customers found.";
    } else {
        // Print the first row to debug column names
        $first_customer = $customers[0];
        if (!isset($first_customer['ContactNo']) || !isset($first_customer['gender'])) {
            $error = "Debug: Missing columns in fetched data. Available columns: " . implode(", ", array_keys($first_customer));
        }
    }
} catch (PDOException $e) {
    $error = "Error loading customers: " . $e->getMessage();
    $customers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>ExoticLanka Tours - Customers</title>
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
                            <h3 class="fw-bold mb-3">Customers</h3>
                            <h6 class="op-7 mb-2">Manage Customer Information</h6>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Add Customer</button>
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

                    <!-- Add Customer Modal -->
                    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="addCustomerForm">
                                        <div class="form-group mb-3">
                                            <label for="customer_name">Customer Name</label>
                                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="nationality">Nationality</label>
                                            <input type="text" class="form-control" id="nationality" name="nationality" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="contact_no">Contact No</label>
                                            <input type="text" class="form-control" id="contact_no" name="contact_no" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="gender">Gender</label>
                                            <select class="form-control" id="gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="add_customer" class="btn btn-primary btn-round">Save Customer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Customer Modal -->
                    <div class="modal fade" id="updateCustomerModal" tabindex="-1" aria-labelledby="updateCustomerModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateCustomerModalLabel">Update Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="" id="updateCustomerForm">
                                        <input type="hidden" name="customer_id" id="update_customer_id">
                                        <div class="form-group mb-3">
                                            <label for="update_customer_name">Customer Name</label>
                                            <input type="text" class="form-control" id="update_customer_name" name="customer_name" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_nationality">Nationality</label>
                                            <input type="text" class="form-control" id="update_nationality" name="nationality" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_contact_no">Contact No</label>
                                            <input type="text" class="form-control" id="update_contact_no" name="contact_no" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_email">Email</label>
                                            <input type="email" class="form-control" id="update_email" name="email" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="update_gender">Gender</label>
                                            <select class="form-control" id="update_gender" name="gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_customer" class="btn btn-primary btn-round">Update Customer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customers Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Customer ID</th>
                                                    <th>Name</th>
                                                    <th>Nationality</th>
                                                    <th>Contact No</th>
                                                    <th>Email</th>
                                                    <th>Gender</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($customers as $customer): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($customer['CustomersID'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($customer['CustomerName'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($customer['Nationality'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($customer['ContactNo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($customer['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td><?= htmlspecialchars($customer['gender'] === 'M' ? 'Male' : ($customer['gender'] === 'F' ? 'Female' : ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <button class="btn btn-update btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updateCustomerModal"
                                                                data-id="<?= htmlspecialchars($customer['CustomersID'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-name="<?= htmlspecialchars($customer['CustomerName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-nationality="<?= htmlspecialchars($customer['Nationality'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-contactno="<?= htmlspecialchars($customer['ContactNo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-email="<?= htmlspecialchars($customer['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                                data-gender="<?= htmlspecialchars($customer['gender'] === 'M' ? 'Male' : ($customer['gender'] === 'F' ? 'Female' : ''), ENT_QUOTES, 'UTF-8') ?>">
                                                            Update
                                                        </button>
                                                        <a href="?delete_id=<?= htmlspecialchars($customer['CustomersID'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this customer?')">
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

            // Reset the add customer form after submission
            $('#addCustomerForm').on('submit', function() {
                setTimeout(function() {
                    $('#addCustomerForm')[0].reset();
                    $('#addCustomerModal').modal('hide');
                }, 1000);
            });

            // Populate the update customer modal with customer data
            $('#updateCustomerModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var id = button.data('id');
                var name = button.data('name');
                var nationality = button.data('nationality');
                var contactno = button.data('contactno');
                var email = button.data('email');
                var gender = button.data('gender');

                var modal = $(this);
                modal.find('#update_customer_id').val(id);
                modal.find('#update_customer_name').val(name);
                modal.find('#update_nationality').val(nationality);
                modal.find('#update_contact_no').val(contactno);
                modal.find('#update_email').val(email);
                modal.find('#update_gender').val(gender);
            });

            // Reset the update customer form after submission
            $('#updateCustomerForm').on('submit', function() {
                setTimeout(function() {
                    $('#updateCustomerForm')[0].reset();
                    $('#updateCustomerModal').modal('hide');
                }, 1000);
            });
        });
    </script>
</body>
</html>