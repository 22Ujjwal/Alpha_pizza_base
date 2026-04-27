<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Pizza Company Database</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Header -->
    <header>
        <h1>Alpha Pizza Base</h1>
        <p class="header-subtitle">Employee Management Interface</p>
    </header>

    <!-- Navigation -->
    <nav>
        <a href="index.html">Dashboard</a>
        <a href="menu.html">Menu</a>
        <a href="orders.html">Orders</a>
        <a href="inventory-management.html">Inventory Management</a>
        <a href="employee-management.html" class="active">Employee Management</a>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- TAB NAVIGATION -->
        <div class="tabs">
            <button class="tab-button active" onclick="switchTab(event, 'search-employees')">
                Search Employees
            </button>
            <button class="tab-button" onclick="switchTab(event, 'add-employee')">
                Add Employee
            </button>
            <button class="tab-button" onclick="switchTab(event, 'update-employee')">
                Update Employee
            </button>
            <button class="tab-button" onclick="switchTab(event, 'employee-list')">
                Employee List
            </button>
            <button class="tab-button" onclick="switchTab(event, 'supervisors')">
                Supervisors
            </button>
        </div>

        <!-- Search Employees Tab -->
        <div id="search-employees" class="tab-content active">
            <div class="card">
                <h2 class="card-title">Search Employees</h2>
                <p class="text-muted mb-2">Find employees by ID, name, email, or department</p>

                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search by Name, ID, or Email...">
                    <button class="btn btn-primary" onclick="searchEmployees()">Search</button>
                    <button class="btn btn-outline" onclick="resetSearch()">Reset</button>
                </div>

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
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Enter search criteria and click Search to view employees
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add New Employee Tab -->
        <div id="add-employee" class="tab-content">
            <div class="card">
                <h2 class="card-title">Add New Employee</h2>

                <form id="addEmployeeForm" onsubmit="handleAddEmployee(event)">
                    <!-- Personal Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Personal Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name <span class="required">*</span></label>
                                <input type="text" id="firstName" required placeholder="John">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="lastName" required placeholder="Doe">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" required placeholder="john.doe@example.com">
                                <small class="text-muted">Must be a valid email address</small>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" placeholder="(555) 123-4567">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="dob">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="dob" required>
                            </div>
                            <div class="form-group">
                                <label for="ssn">Social Security Number <span class="required">*</span></label>
                                <input type="text" id="ssn" required placeholder="XXXXXXXXX" pattern="\d{9}">
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
                                <input type="date" id="startDate" required>
                            </div>
                            <div class="form-group">
                                <label for="position">Position <span class="required">*</span></label>
                                <select id="position" required>
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
                                <input type="number" id="salary" required step="0.01" min="0" placeholder="50000.00">
                            </div>
                            <div class="form-group">
                                <label for="supervisor">Supervisor <span class="required">*</span></label>
                                <select id="supervisor" required>
                                    <option value="">-- Select Supervisor --</option>
                                    <option value="original-supervisor" selected>Original Supervisor</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department">
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

        <!-- Update Employee Tab -->
        <div id="update-employee" class="tab-content">
            <div class="card">
                <h2 class="card-title">Update Employee Information</h2>

                <div class="search-bar">
                    <input type="text" id="updateEmployeeSearchInput" placeholder="Search employee by Name or ID...">
                    <button class="btn btn-primary" onclick="findEmployeeToUpdate()">Find Employee</button>
                </div>

                <form id="updateEmployeeForm" onsubmit="handleUpdateEmployee(event)" class="hidden">
                    <div class="alert alert-info">
                        <strong>Edit Employee Information</strong>
                    </div>

                    <!-- Personal Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Personal Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upEmpID">Employee ID</label>
                                <input type="text" id="upEmpID" readonly>
                            </div>
                            <div class="form-group">
                                <label for="upEmail">Email <span class="required">*</span></label>
                                <input type="email" id="upEmail" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upFirstName">First Name <span class="required">*</span></label>
                                <input type="text" id="upFirstName" required>
                            </div>
                            <div class="form-group">
                                <label for="upLastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="upLastName" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upPhone">Phone Number</label>
                                <input type="tel" id="upPhone">
                            </div>
                            <div class="form-group">
                                <label for="upDOB">Date of Birth <span class="required">*</span></label>
                                <input type="date" id="upDOB" required>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information -->
                    <div style="background: var(--light-bg); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Employment Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upPosition">Position <span class="required">*</span></label>
                                <select id="upPosition" required>
                                    <option value="">-- Select Position --</option>
                                    <option value="Chef">Chef</option>
                                    <option value="Cook">Cook</option>
                                    <option value="Cashier">Cashier</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Supervisor">Supervisor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="upSalary">Annual Salary (USD) <span class="required">*</span></label>
                                <input type="number" id="upSalary" required step="0.01" min="0">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="upSupervisor">Supervisor <span class="required">*</span></label>
                                <select id="upSupervisor" required>
                                    <option value="">-- Select Supervisor --</option>
                                    <option value="original-supervisor" selected>Original Supervisor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="upDepartment">Department</label>
                                <select id="upDepartment">
                                    <option value="">-- Select Department --</option>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Front of House">Front of House</option>
                                    <option value="Management">Management</option>
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
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <button type="button" class="btn btn-outline" onclick="cancelUpdateEmployee()">Cancel</button>
                    </div>
                </form>

                <div id="updateEmployeeMessage" class="hidden mt-2"></div>
            </div>
        </div>

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
                        <!-- ThuHuong's -->
                        <!-- Fetch data of employee from database-->
                        <tbody>
                            <?php
                            # query to display all columns of the EMPLOYEE table
                            $result = $conn->query("SELECT * FROM EMPLOYEE");

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["EmployeeID"] . "</td>";
                                echo "<td>" . $row["FirstName"] . "</td>";
                                echo "<td>" . $row["LastName"] . "</td>";
                                echo "<td>" . $row["Email"] . "</td>";
                                echo "<td>" . $row["Salary"] . "</td>";
                                echo "<td>" . $row["StartDate"] . "</td>";
                                echo "<td>" . $row["SupervisorID"] . "</td>";
                                echo "<td>Edit</td>";
                                echo "<td>Delete</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No supervisors loaded.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 2rem; padding: 1.5rem; background: var(--light-bg); border-radius: 8px;">
                    <h3 style="margin-bottom: 1rem; color: var(--primary-color);">Reassign Supervisor</h3>
                    <p class="text-muted mb-2">Change an employee's supervisor</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="empToReassign">Select Employee <span class="required">*</span></label>
                            <select id="empToReassign">
                                <option value="">-- Select Employee --</option>
                                <option value="original-employee" selected>Original Employee</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="newSupervisor">New Supervisor <span class="required">*</span></label>
                            <select id="newSupervisor">
                                <option value="">-- Select Supervisor --</option>
                                <option value="original-supervisor" selected>Original Supervisor</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-secondary" onclick="reassignSupervisor()">Update Supervisor
                        Assignment</button>
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