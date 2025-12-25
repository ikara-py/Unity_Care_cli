<?php
include_once __DIR__ . '/config/connection.php';

class Patient_m {
    public function handlePatients() {
        global $connection;
        while (true) {
            echo "\n=== Patient Management ===\n";
            echo "1. List all patients\n";
            echo "2. Search for a patient\n";
            echo "3. Add a patient\n";
            echo "4. Edit a patient\n";
            echo "5. Delete a patient\n";
            echo "6. Back\n";

            $choice = $this->prompt("Action");

            switch ($choice) {
                case '1':
                    $stmt = $connection->query("select * from patients");
                    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n=== Patient List ===\n";
                    foreach ($patients as $patient) {
                        echo $patient['first_name'] . "   ---   " . $patient['last_name'] . "   ---   " . $patient['gender'] . "   ---   " . $patient['date_of_birth'] . "   ---   " . $patient['email'] . "   ---   " . $patient['address'] . "\n";
                    }
                    break;
                case '2':
                    echo "Search\n";
                    break;
                case '3':
                    echo "Add form\n";
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

    private function prompt($label) {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}

class Doctor_m {
    public function handleDoctors() {
        global $connection;
        while (true) {
            echo "\n=== Doctor Management ===\n";
            echo "1. List all doctors\n";
            echo "2. Search for a doctor\n";
            echo "3. Add a doctor\n";
            echo "4. Edit a doctor\n";
            echo "5. Delete a doctor\n";
            echo "6. Back\n";

            $choice = $this->prompt("Action");

            switch ($choice) {
                case '1':
                    $stmt = $connection->query("select * from doctors");
                    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n=== Doctors List ===\n";
                    foreach ($doctors as $doctor) {
                        echo $doctor['first_name'] . "   ---   " . $doctor['last_name'] . "   ---   " . $doctor['specialization'] . "   ---   " . $doctor['phone_number']  . "   ---   " . $doctor['email'] ."\n";
                    }
                    break;
                case '2':
                    echo "Search\n";
                    break;
                case '3':
                    echo "Add form\n";
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

    private function prompt($label) {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}

class Department_m {
    public function handleDepartments() {
        global $connection;
        while (true) {
            echo "\n=== Department Management ===\n";
            echo "1. List departments\n";
            echo "2. Add a department\n";
            echo "3. Back\n";

            $choice = $this->prompt("Action");

            switch ($choice) {
                case '1':
                    $stmt = $connection->query("select * from departments");
                    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "\n=== Departments List ===\n";
                    foreach ($departments as $department) {
                        echo $department['department_name'] . "   ---   " . $department['location'] ."\n";
                    }
                    break;
                case '2':
                    echo "Add a department\n";
                    break;
                case '3':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function prompt($label) {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}



class MainMenu {
    public function run() {
        while (true) {
            $this->showMainMenu();
            $choice = $this->prompt("Selection");

            switch ($choice) {
                case '1':
                    $patient_m = new Patient_m();
                    $patient_m->handlePatients();
                    break;
                case '2':
                    $doctor_m = new Doctor_m();
                    $doctor_m->handleDoctors();
                    break;
                case '3':
                    $department_m = new Department_m();
                    $department_m->handleDepartments();
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

    private function showMainMenu() {
        echo "\n=== Unity Care CLI ===\n";
        echo "1. Manage patients\n";
        echo "2. Manage doctors\n";
        echo "3. Manage departments\n";
        echo "4. Statistics\n";
        echo "5. Quit\n";
    }

    private function prompt($label) {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}

$cli = new MainMenu();
$cli->run();