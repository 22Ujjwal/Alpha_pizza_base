<!-- Removed "Phone" attribute since it was not an attribute of the Emplpoyee relation -->
<?php
include 'db_connect.php';

//******************************       PHP       ***************************** */
//********************************************** */
// Delete employee
//********************************************** */
if (isset($_POST['deleteEmpID'])) {
    $id = (int) $_POST['deleteEmpID'];

    // Check if this employee supervises anyone
    $check = $conn->query("SELECT EmployeeID FROM EMPLOYEE WHERE SupervisorID = $id");

    if ($check && $check->num_rows > 0) {
        echo "<p style='color:red;'>Cannot delete this employee because they supervise other employees.</p>";
    } else {
        $sql = "DELETE FROM EMPLOYEE WHERE EmployeeID = $id";

        if ($conn->query($sql)) {
            header("Location: employee-management.php?deleted=1");
            exit();
        } else {
            echo "<p style='color:red;'>DELETE FAILED: " . $conn->error . "</p>";
        }
    }
}

//*************************************************** */
// Insert employee
//************************************************************* */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['firstName'])) {
        $first = $_POST['firstName'];
        $last = $_POST['lastName'];
        $email = $_POST['email'];
        $dob = $_POST['dob'];
        $ssn = $_POST['ssn'];
        $start = $_POST['startDate'];
        $position = $_POST['position'];
        $salary = $_POST['salary'];
        // If the user did not select a supervisor (the case where the employee is the supervisor themselves),
        // assign NULL to supervisor ID field.
        $supervisor = ($_POST['supervisor'] === "") ? "NULL" : (int) $_POST['supervisor'];
        $department = $_POST['department'];

        $sql = "INSERT INTO EMPLOYEE 
        (FirstName, LastName, Email, DOB, SSN, StartDate, Position, Salary, SupervisorID, Department)
        VALUES 
        ('$first', '$last', '$email', '$dob', '$ssn', '$start', '$position', '$salary', $supervisor, '$department')";

        if (!$conn->query($sql)) {
            die("<p style='color:red;'>SQL FAILED: " . $conn->error . "</p>");
        }

        header("Location: employee-management.php?added=1");
        exit();
    }
}

// ================================ ============================================
// Query being performed when we enter employee ID and click on "Find Employee"
// under Update Employee tab
// =============================================================================
// Check if serach key exists and is not an empty string

$emp = null; // Array that stores employees' data
if (isset($_GET['search_id']) && $_GET['search_id'] !== "") {
    $id = (int) $_GET['search_id'];
    // Query the database to obtain all columns from EMPLOYEE table where
    // EmployeeId mtaches input $id
    $result = $conn->query("SELECT * FROM EMPLOYEE WHERE EmployeeID = $id");

    if ($result && $result->num_rows > 0) { // employ with this ID exists (1 row returned)
        $emp = $result->fetch_assoc(); // append data for employee into the array
    }
}

$searchResults = null; // returns multiple rows

if (isset($_GET['search']) && $_GET['search'] !== "") {
    $search = $conn->real_escape_string($_GET['search']);
    // Query the database to obtain all columns from the Employee table 
    // where user's input matches EmployeeID, First/LastName, etc.
    $sql = "SELECT * FROM EMPLOYEE
            WHERE EmployeeID LIKE '%$search%'
            OR FirstName LIKE '%$search%'
            OR LastName LIKE '%$search%'
            OR Email LIKE '%$search%'";

    $searchResults = $conn->query($sql);
}
//***************************************************************** */
//  Update employee
//****************************************************************** */
if (isset($_POST['upEmpID'])) {
    $id = $_POST['upEmpID'];
    $first = $_POST['upFirstName'];
    $last = $_POST['upLastName'];
    $email = $_POST['upEmail'];
    $position = $_POST['upPosition'];
    $salary = $_POST['upSalary'];
    $supervisor = $_POST['upSupervisor'];
    $department = $_POST['upDepartment'];

    // New employee not assigned a supervisor will have the supervisor filed 
    // passed as NULL. Otherwise typecast into integer
    $supervisorSQL = ($supervisor === "") ? "NULL" : (int) $supervisor;

    // Query to update employee's information based on input
    $sql = "UPDATE EMPLOYEE 
            SET FirstName='$first',
                LastName='$last',
                Email='$email',
                Position='$position',
                Salary='$salary',
                SupervisorID=$supervisorSQL, 
                Department='$department'
            WHERE EmployeeID=$id";

    $conn->query($sql);
}

//**************************************************************** */
//  Reassign supervisor
//****************************************************************** */
if (isset($_POST['reassignSupervisor'])) {
    $empID = (int) $_POST['empToReassign'];
    $newSupID = (int) $_POST['newSupervisor'];

    // This query updates the  employee's supervisor
    $sql = "UPDATE EMPLOYEE
            SET SupervisorID = $newSupID
            WHERE EmployeeID = $empID";

    if ($conn->query($sql)) {
        echo "<p style='color:green;'>Supervisor updated successfully.</p>";
    } else {
        echo "<p style='color:red;'>SQL FAILED: " . $conn->error . "</p>";
    }
}
?>

<!--**********************************        INTERFACE        **********************************-->
<!------------------------------------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Pizza Company Database</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <!-- Header -->
    <header>
        <h1>Alpha Pizza Base</h1>
        <p class="header-subtitle">Employee Management Interface</p>
    </header>

    <!-- Navigation -->
    <nav>
        <a href="../index.html">Dashboard</a>
        <a href="../menu.html">Menu</a>
        <a href="../orders.html">Orders</a>
        <a href="../inventory-management.html">Inventory Management</a>
        <a href="employee-management.php" class="active">Employee Management</a>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- TAB NAVIGATION -->
        <div class="tabs">
            <button class="tab-button <?= !isset($_GET['search_id']) ? 'active' : '' ?>"
                onclick="switchTab(event, 'search-employees')">
                Search Employees
            </button>
            <button class="tab-button" onclick="switchTab(event, 'add-employee')">
                Add Employee
            </button>
            <button class="tab-button <?= isset($_GET['search_id']) ? 'active' : '' ?>"
                onclick="switchTab(event, 'update-employee')">
                Update Employee
            </button>
            <button class="tab-button" onclick="switchTab(event, 'employee-list')">
                Employee List
            </button>
            <button class="tab-button" onclick="switchTab(event, 'supervisors')">
                Supervisors
            </button>
        </div>
        <!-- ====================++++++++++++++++=========================================== 
    ||                               SEARCH FOR EMPLOYEE                          ||
    ================================================================================
 -->
        <!-- Search Employees Tab -->
        <div id="search-employees" class="tab-content <?= !isset($_GET['search_id']) ? 'active' : '' ?>">
            <div class="card">
                <h2 class="card-title">Search Employees</h2>
                <p class="text-muted mb-2">Find employees by ID, name, email, or department</p>

                <form method="GET">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search by Name, ID, or Email..."
                            value="<?= $_GET['search'] ?? '' ?>">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <!-- Added Reset button to clear result after search-->
                        <a href="employee-management.php" class="btn btn-outline">Reset</a>
                    </div>
                </form>

                <div class="form-group" style="margin-top: 1rem;">
                    <select id="searchFilter" onchange="searchEmployees()">
                        <option value="">-- Filter By --</option>
                        <option value="active">Active Employees</option>
                        <option value="supervisor">Supervisors Only</option>
                        <option value="recent">Recently Added</option>
                    </select>
                </div>

                <div id="searchResults" class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Start Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employeesTableBody">
                            <?php if ($searchResults && $searchResults->num_rows > 0): ?>
                                <?php while ($row = $searchResults->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['EmployeeID'] ?></td>
                                        <td><?= $row['FirstName'] ?></td>
                                        <td><?= $row['LastName'] ?></td>
                                        <td><?= $row['Email'] ?></td>
                                        <td><?= $row['Position'] ?? 'N/A' ?></td>
                                        <td><?= $row['StartDate'] ?></td>
                                        <td>
                                            <a class="btn btn-small btn-outline"
                                                href="employee-management.php?search_id=<?= $row['EmployeeID'] ?>">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php elseif (isset($_GET['search'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No employees found.</td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Enter search criteria and click Search to view employees
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ====================++++++++++++++++=========================================== 
    ||                               INSERT EMPLOYEE                              ||
    ================================================================================
 -->
        <!-- Add New Employee Tab -->
        <div id="add-employee" class="tab-content">
            <div class="card">
                <h2 class="card-title">Add New Employee</h2>
                <form method="POST">
                    <!-- Personal Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Personal Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name <span class="required">*</span></label>
                                <input type="text" id="firstName" name="firstName" required placeholder="John">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="lastName" name="lastName" required placeholder="Doe">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required placeholder="john.doe@example.com">
                                <small class="text-muted">Must be a valid email address</small>
                            </div>
                            <div class="form-group">

                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dob">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="dob" name="dob" required>
                            </div>
                            <div class="form-group">
                                <label for="ssn">Social Security Number <span class="required">*</span></label>
                                <input type="text" id="ssn" name="ssn" required placeholder="XXXXXXXXX" pattern="\d{9}">
                                <small class="text-muted">Format: XXXXXXXXX</small>
                            </div>
                        </div>
                    </div>
                    <!-- Employment Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Employment Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="startDate">Start Date <span class="required">*</span></label>
                                <input type="date" id="startDate" name="startDate" required>
                            </div>
                            <div class="form-group">
                                <label for="position">Position <span class="required">*</span></label>
                                <select id="position" name="position" required>
                                    <option value="">-- Select Position --</option>
                                    <option value="Chef">Chef</option>
                                    <option value="Cook">Cook</option>
                                    <option value="Cashier">Cashier</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Delivery Driver">Delivery Driver</option>
                                    <option value="HR Staff">HR Staff</option>
                                    <option value="IT Staff">IT Staff</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="salary">Annual Salary (USD) <span class="required">*</span></label>
                                <input type="number" id="salary" name="salary" required step="0.01" min="0"
                                    placeholder="50000.00">
                            </div>
                            <div class="form-group">
                                <label for="supervisor">Supervisor <span class="required">*</span></label>
                                <select id="supervisor" name="supervisor">
                                    <option value="">-- Select Supervisor --</option>
                                    <?php
                                    // Query to obtain row(s) of supervisor(s)
                                    $supervisors = $conn->query("
                                            SELECT EmployeeID, FirstName, LastName
                                            FROM EMPLOYEE
                                            WHERE Position = 'Supervisor'
                                            ORDER BY LastName
                                        ");

                                    while ($row = $supervisors->fetch_assoc()) { // grabs next row
                                    //checks if the current employee's supervisor matches this row's ID
                                        $selected = (($emp['SupervisorID'] ?? '') == $row['EmployeeID']) ? 'selected' : '';
                                        // Display drop down options
                                        echo "<option value='{$row['EmployeeID']}' $selected>
                                            {$row['EmployeeID']} - {$row['FirstName']} {$row['LastName']}
                                        </option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department">
                                <option value="">-- Select Department --</option>
                                <option value="Kitchen">Kitchen</option>
                                <option value="Front of House">Front of House</option>
                                <option value="Management">Management</option>
                                <option value="Delivery">Delivery</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                        <button type="reset" class="btn btn-outline">Clear Form</button>
                    </div>
                </form>

                <div id="addEmployeeMessage" class="hidden mt-2"></div>
            </div>
        </div>

        <!-- ====================++++++++++++++++=========================================== 
    ||                               UPDATE EMPLOYEE                              ||
    ================================================================================
 -->
        <!-- Update Employee Tab -->
        <div id="update-employee" class="tab-content <?= isset($_GET['search_id']) ? 'active' : '' ?>">
            <div class="card">
                <h2 class="card-title">Update Employee Information</h2>

                <!--input type="text" id="updateEmployeeSearchInput" placeholder="Search employee by Name or ID...">
                    <! button class="btn btn-primary" onclick="findEmployeeToUpdate()">Find Employee</button>-->

                <form method="GET">
                    <div class="search-bar">
                        <input type="text" name="search_id" placeholder="Search employee by ID..."
                            value="<?= $_GET['search_id'] ?? '' ?>">
                        <button class="btn btn-primary" type="submit">Find Employee</button>
                    </div>
                </form>

                <!--Make form appear when employee is found -->
                <form method="POST" class="<?= $emp ? '' : 'hidden' ?>">
                    <div class="alert alert-info">
                        <strong>Edit Employee Information</strong>
                    </div>

                    <!-- Personal Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Personal Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upEmpID">Employee ID</label>
                                <input type="text" id="upEmpID" name="upEmpID" readonly
                                    value="<?= $emp['EmployeeID'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label for="upEmail">Email <span class="required">*</span></label>
                                <input type="email" id="upEmail" name="upEmail" value="<?= $emp['Email'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upFirstName">First Name <span class="required">*</span></label>
                                <input type="text" id="upFirstName" name="upFirstName"
                                    value="<?= $emp['FirstName'] ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label for="upLastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="upLastName" name="upLastName"
                                    value="<?= $emp['LastName'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">

                            </div>
                            <div class="form-group">
                                <label for="upDOB">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="upDOB" name="upDOB" value="<?= $emp['DOB'] ?? '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Employment Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upPosition">Position <span class="required">*</span></label>
                                <select id="upPosition" name="upPosition" required>
                                    <option value="">-- Select Position --</option>
                                    <option value="Chef">Chef</option>
                                    <option value="Cook">Cook</option>
                                    <option value="Cashier">Cashier</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Delivery Driver">Delivery Driver</option>
                                    <option value="HR Staff">HR Staff</option>
                                    <option value="IT Staff">IT Staff</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="upSalary">Annual Salary (USD) <span class="required">*</span></label>
                                <input type="number" id="upSalary" name="upSalary" required step="0.01" min="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upSupervisor">Supervisor <span class="required">*</span></label>
                                    <select id="upSupervisor" name="upSupervisor">
                                        <option value="">-- Select Supervisor --</option>

                                        <?php
                                        // Query to select supervisor from the table
                                        $supList = $conn->query("
                                            SELECT EmployeeID, FirstName, LastName
                                            FROM EMPLOYEE
                                            WHERE Position IN ('Supervisor')
                                            ORDER BY LastName
                                        ");

                                        while ($row = $supList->fetch_assoc()) {
                                            echo "<option value='{$row['EmployeeID']}'>
                                                {$row['EmployeeID']} - {$row['FirstName']} {$row['LastName']}
                                                </option>";
                                        }
                                        ?>
                                    </select>
                            </div>
                            <div class="form-group">
                                <label for="upDepartment">Department</label>
                                <select id="upDepartment" name="upDepartment" required>
                                    <option value="">-- Select Department --</option>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Front of House">Front of House</option>
                                    <option value="Management">Management</option>
                                    <option value="Delivery">Delivery</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="upStartDate">Start Date <span class="required">*</span></label>
                            <input type="date" id="upStartDate" required readonly>
                            <small class="text-muted">Start date cannot be modified</small>
                        </div>
                    </div>
                    <div class="btn-group">
                        <!-- Save Changes button-->
                    <button type="submit" class="btn btn-success">Save Changes</button>
                            <!-- Delete Employee button-->
                    <button type="submit"
                            name="deleteEmpID"
                            value="<?= $emp['EmployeeID'] ?? '' ?>"
                            class="btn btn-danger"
                            formnovalidate
                            onclick="return confirm('Delete this employee?');">Delete Employee</button>
                            <!-- Cancel button-->
                    <a href="employee-management.php" class="btn btn-outline">Cancel</a>
                </div>


                        <!--  Redirect to main employee management page after clicking on cancel -->
                        <script>
                        function cancelUpdateEmployee() {
                            window.location.href = "employee-management.php";
                        }
                        </script>
                    </div>
                </form>

                <div id="updateEmployeeMessage" class="hidden mt-2"></div>
            </div>
        </div>

        <!-- ====================++++++++++++++++=========================================== 
    ||                             EMPLOYEE LIST                                  ||
    ================================================================================
 -->

        <!-- Employee List Tab -->
        <div id="employee-list" class="tab-content">
            <div class="card">
                <h2 class="card-title">Complete Employee List</h2>
                <p class="text-muted mb-2">All current employees in the system</p>

                <div class="form-group">
                    <select id="listFilter" onchange="filterEmployeeList()">
                        <option value="">All Employees</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive</option>
                        <option value="kitchen">Kitchen Staff</option>
                        <option value="management">Management</option>
                    </select>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Salary</th>
                                <th>Start Date</th>
                                <th>Supervisor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <!-- Fetch data of employee from database-->
                        <tbody>
                            <?php
                            # query to display all columns of the EMPLOYEE table
                            $result = $conn->query("SELECT * FROM EMPLOYEE");
                            # obtain employee data for each row
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["EmployeeID"] . "</td>";
                                echo "<td>" . $row["FirstName"] . " " . $row["LastName"] . "</td>";
                                echo "<td>" . $row["Email"] . "</td>";
                                echo "<td>" . $row["Position"] . "</td>";
                                echo "<td>" . $row["Department"] . "</td>";
                                echo "<td>" . $row["Salary"] . "</td>";
                                echo "<td>" . $row["StartDate"] . "</td>";
                                echo "<td>" . $row["SupervisorID"] . "</td>";
                                echo "<td>
                                <a class='btn btn-small btn-outline'
                                href='employee-management.php?search_id=" . $row['EmployeeID'] . "'>
                                Edit</a>
                                </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ====================++++++++++++++++=========================================== 
    ||                             SUPERVISOR HIERARCHY                            ||
    ================================================================================
 -->
        <!-- Supervisors & Hierarchy Tab -->
        <div id="supervisors" class="tab-content">
            <div class="card">
                <h2 class="card-title">Supervisor Hierarchy</h2>
                <p class="text-muted mb-2">View and manage supervisor assignments</p>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Supervisor ID</th>
                                <th>Supervisor Name</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Direct Reports</th>
                                <th>Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="supervisorTableBody">
                            
                                <?php
                                // This query return the supervisor's id, name, position, department, and salary
                                $sql = "SELECT 
                                    s.EmployeeID AS SupervisorID,
                                    CONCAT(s.FirstName, ' ', s.LastName) AS SupervisorName,
                                    s.Position,
                                    s.Department,
                                    s.Salary,
                                    COUNT(e.EmployeeID) AS DirectReports
                                FROM EMPLOYEE s
                                -- Use Left join to display all supervisors including those with 0 supervisee (direct reports)
                                LEFT JOIN EMPLOYEE e 
                                    ON e.SupervisorID = s.EmployeeID
                                WHERE s.Position = 'Supervisor'
                                GROUP BY s.EmployeeID, s.FirstName, s.LastName, s.Position, s.Department, s.Salary
                                ORDER BY s.LastName";

                                $result = $conn->query($sql);

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["SupervisorID"] . "</td>";
                                        echo "<td>" . $row["SupervisorName"] . "</td>";
                                        echo "<td>" . $row["Position"] . "</td>";
                                        echo "<td>" . $row["Department"] . "</td>";
                                        echo "<td>" . $row["DirectReports"] . "</td>";
                                        echo "<td>" . $row["Salary"] . "</td>";
                                        echo
                                            "<td>
                                        <a class='btn btn-small btn-outline'
                                        href='employee-management.php?search_id=" . $row['SupervisorID'] . "'>
                                        Edit
                                        </a>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center text-muted'>No supervisors loaded.</td></tr>";
                                }
                                ?>
                        
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 2rem; padding: 1.5rem; background: var(--light-bg); border-radius: 8px;">
                    <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Reassign Supervisor</h3>
                    <p class="text-muted mb-2">Change an employee's supervisor</p>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="empToReassign">Select Employee <span class="required">*</span></label>
                                <select id="empToReassign" name="empToReassign">
                                    <option value="">-- Select Employee --</option>
                                    <?php
                                    $employees = $conn->query("SELECT EmployeeID, FirstName, LastName FROM EMPLOYEE ORDER BY EmployeeID");

                                    while ($row = $employees->fetch_assoc()) {
                                        echo "<option value='" . $row['EmployeeID'] . "'>" .
                                            $row['EmployeeID'] . " - " .
                                            $row['FirstName'] . " " .
                                            $row['LastName'] .
                                            "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="newSupervisor">New Supervisor <span class="required">*</span></label>
                                <select id="newSupervisor" name="newSupervisor">
                                    <option value="">-- Select Supervisor --</option>
                                    <?php
                                    // This returns supervisor's ID and name without duplicate rows
                                    $supervisors = $conn->query("SELECT EmployeeID, FirstName, LastName
                                                            FROM EMPLOYEE
                                                            WHERE Position = 'Supervisor'
                                                            ORDER BY LastName");
                                    while ($row = $supervisors->fetch_assoc()) {
                                        echo "<option value='" . $row['EmployeeID'] . "'>" .
                                            $row['EmployeeID'] . " - " .
                                            $row['FirstName'] . " " .
                                            $row['LastName'] .
                                            "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!--======================== UPDATE SUPERVISOR REASSIGNMENT =============================-->
                        <button class="btn btn-secondary" type="submit" name="reassignSupervisor">Update Supervisor
                            Assignment
                        </button>
                    </form>
                    <div id="reassignMessage" class="hidden mt-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Alpha Pizza Base Database System.</p>
    </footer>

    <script>
        function switchTab(evt, tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>
