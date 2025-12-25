<?php
include_once __DIR__ . '/config/connection.php';

class Validation
{
    public static function validateString($str, $regex = null)
    {
        $trimmed = trim($str);
        if (empty($trimmed)) {
            return false;
        }
        if ($regex !== null && !preg_match($regex, $trimmed)) {
            return false;
        }
        return true;
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validateGender($gender)
    {
        $valid = ['male', 'female'];
        return in_array(strtolower($gender), $valid);
    }
}

class Helper
{
    public static function prompt($label)
    {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}

class Patients
{
    public function handlePatients()
    {
        global $connection;
        while (true) {
            echo "\n=== Patient Management ===\n";
            echo "1. List all patients\n";
            echo "2. Search for a patient\n";
            echo "3. Add a patient\n";
            echo "4. Edit a patient\n";
            echo "5. Delete a patient\n";
            echo "6. Back\n";

            $choice = Helper::prompt("Action");

            switch ($choice) {
                case '1':
                    $this->getAll();
                    break;
                case '2':
                    echo "Search\n";
                    break;
                case '3':
                    echo "\n--- Add New Patient ---\n";
                    $fname = Helper::prompt("First Name");
                    $lname = Helper::prompt("Last Name");

                    if (!Validation::validateString($fname, '/^[a-zA-Z\s]+$/') || !Validation::validateString($lname, '/^[a-zA-Z\s]+$/')) {
                        echo "Names cannot be empty and must only contain letters.\n";
                        break;
                    }

                    $gender = Helper::prompt("Gender (male/female)");
                    $dob = Helper::prompt("Date of Birth (YYYY-MM-DD)");
                    $phone = Helper::prompt("Phone Number");
                    $email = Helper::prompt("Email");
                    $address = Helper::prompt("Address");

                    if (!Validation::validateGender($gender)) {
                        echo "Invalid gender.\n";
                        break;
                    }
                    if (!Validation::validateDate($dob)) {
                        echo "Invalid date format.\n";
                        break;
                    }
                    if (!Validation::validateEmail($email)) {
                        echo "Invalid email format.\n";
                        break;
                    }

                    try {
                        $sql = "INSERT INTO patients (first_name, last_name, gender, date_of_birth, phone_number, email, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$fname, $lname, strtolower($gender), $dob, $phone, $email, $address]);
                        echo "Patient added successfully!\n";
                    } catch (PDOException $err) {
                        echo "SQL Error: " . $err->getMessage() . "\n";
                    }
                    break;
                case '4':
                    echo "Edit form\n";
                    break;
                case '5':
                    echo "Deletion\n";
                    break;
                case '6':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function getAll()
    {
        global $connection;
        $stmt = $connection->query("SELECT * FROM patients");
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\n=== Patient List ===\n";
        foreach ($patients as $patient) {
            echo $patient['first_name'] . " --- " . $patient['last_name'] . " --- " . $patient['gender'] . " --- " . $patient['date_of_birth'] . " --- " . $patient['email'] . " --- " . $patient['address'] . "\n";
        }
    }
}

class Doctors
{
    public function handleDoctors()
    {
        global $connection;
        while (true) {
            echo "\n=== Doctor Management ===\n";
            echo "1. List all doctors\n";
            echo "2. Search for a doctor\n";
            echo "3. Add a doctor\n";
            echo "4. Edit a doctor\n";
            echo "5. Delete a doctor\n";
            echo "6. Back\n";

            $choice = Helper::prompt("Action");

            switch ($choice) {
                case '1':
                    $stmt = $connection->query("SELECT * FROM doctors");
                    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n=== Doctors List ===\n";
                    foreach ($doctors as $doctor) {
                        echo $doctor['first_name'] . " --- " . $doctor['last_name'] . " --- " . $doctor['specialization'] . " --- " . $doctor['phone_number']  . " --- " . $doctor['email'] . "\n";
                    }
                    break;
                case '2':
                    echo "Search\n";
                    break;
                case '3':
                    echo "\n--- Add New Doctor ---\n";
                    $fname = Helper::prompt("First Name");
                    $lname = Helper::prompt("Last Name");
                    $spec = Helper::prompt("Specialization");
                    $phone = Helper::prompt("Phone Number");
                    $email = Helper::prompt("Email");

                    if (!Validation::validateString($fname, '/^[a-zA-Z\s]+$/') || !Validation::validateString($lname, '/^[a-zA-Z\s]+$/')) {
                        echo "Names cannot be empty and must only contain letters.\n";
                        break;
                    }
                    if (!Validation::validateEmail($email)) {
                        echo "Invalid email format.\n";
                        break;
                    }

                    try {
                        $sql = "INSERT INTO doctors (first_name, last_name, specialization, phone_number, email) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$fname, $lname, $spec, $phone, $email]);
                        echo "Doctor added\n";
                    } catch (PDOException $err) {
                        echo "SQL Error: " . $err->getMessage() . "\n";
                    }
                    break;
                case '4':
                    echo "Edit form\n";
                    break;
                case '5':
                    echo "Deletion\n";
                    break;
                case '6':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }
}

class Departments
{
    public function handleDepartments()
    {
        global $connection;
        while (true) {
            echo "\n=== Department Management ===\n";
            echo "1. List departments\n";
            echo "2. Add a department\n";
            echo "3. Back\n";

            $choice = Helper::prompt("Action");

            switch ($choice) {
                case '1':
                    $stmt = $connection->query("SELECT * FROM departments");
                    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n=== Departments List ===\n";
                    foreach ($departments as $department) {
                        echo $department['department_name'] . " --- " . $department['location'] . "\n";
                    }
                    break;
                case '2':
                    echo "\n--- Add New Department ---\n";
                    $name = Helper::prompt("Department Name");
                    $location = Helper::prompt("Location");

                    if (!Validation::validateString($name) || !Validation::validateString($location)) {
                        echo "Name and Location cannot be empty.\n";
                        break;
                    }

                    try {
                        $sql = "INSERT INTO departments (department_name, location) VALUES (?, ?)";
                        $stmt = $connection->prepare($sql);
                        $stmt->execute([$name, $location]);
                        echo "Department added\n";
                    } catch (PDOException $err) {
                        echo "SQL Error: " . $err->getMessage() . "\n";
                    }
                    break;
                case '3':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }
}

class MainMenu
{
    public function run()
    {
        while (true) {
            $this->showMainMenu();
            $choice = Helper::prompt("Selection");

            switch ($choice) {
                case '1':
                    $patients = new Patients();
                    $patients->handlePatients();
                    break;
                case '2':
                    $doctors = new Doctors();
                    $doctors->handleDoctors();
                    break;
                case '3':
                    $departments = new Departments();
                    $departments->handleDepartments();
                    break;
                case '4':
                    break;
                case '5':
                    echo "Closing the program.\n";
                    exit();
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function showMainMenu()
    {
        echo "\n=== Unity Care CLI ===\n";
        echo "1. Manage patients\n";
        echo "2. Manage doctors\n";
        echo "3. Manage departments\n";
        echo "4. Statistics\n";
        echo "5. Quit\n";
    }
}

$cli = new MainMenu();
$cli->run();
