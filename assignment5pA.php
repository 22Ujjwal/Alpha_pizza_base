<?php
// ---------------------------------- ASSIGNMENT 5 -------------------------------------------
require_once 'config.php';
$conn = get_db_connection();

$results = null;
$sql = "";

if (isset($_GET['firstName']) || isset($_GET['lastName'])) {
    $firstName = $_GET['firstName'] ?? '';
    $lastName = $_GET['lastName'] ?? '';

    // Vulnerable code -- PART A
    $sql = "SELECT EmployeeID, FirstName, LastName, Email, Position, Department
            FROM EMPLOYEE
            WHERE FirstName = '$firstName'
            AND LastName = '$lastName'";

    $results = $conn->query($sql);

    // PART B
   /* $sql = "SELECT EmployeeID, FirstName, LastName, Email, Position, Department
        FROM EMPLOYEE
        WHERE FirstName = ?
        AND LastName = ?";
    // send query to database before any data is attached
    $stmt = $conn->prepare($sql);
    // parse inputs as strings
    $stmt->bind_param("ss", $firstName, $lastName);

    $stmt->execute();
    // get result
    $results = $stmt->get_result();
}
?> */

<!DOCTYPE html>
<html>
<head>
    <title>Assignment 5 SQL Injection</title>
</head>
<body>
    <h1>Assignment 5 Part A</h1>
    <form method="GET">
        <label>First Name:</label>
        <input type="text" name="firstName">

        <br><br>

        <label>Last Name:</label>
        <input type="text" name="lastName">

        <br><br>

        <button type="submit">Search Employee</button>
    </form>

    <hr>

    <?php if ($results): ?>
        <h3>Results:</h3>

        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>First</th>
                <th>Last</th>
                <th>Email</th>
                <th>Position</th>
                <th>Department</th>
            </tr>

            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['EmployeeID']) ?></td>
                    <td><?= htmlspecialchars($row['FirstName']) ?></td>
                    <td><?= htmlspecialchars($row['LastName']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['Position']) ?></td>
                    <td><?= htmlspecialchars($row['Department']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>
