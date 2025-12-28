<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

class Database {
    private static $connection = null;
    
    public static function connect(): PDO {
        if (self::$connection === null) {
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=utf8mb4",
                $_ENV['db_host'],
                $_ENV['db_name']
            );

            try {
                self::$connection = new PDO(
                    $dsn,
                    $_ENV['db_user'],
                    $_ENV['db_pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $err) {
                die("Connection failed: " . $err->getMessage());
            }
        }
        return self::$connection;
    }
}

class Validation {
    public static function validateString($str, $regex = null): bool {
        $trimmed = trim($str);
        if (empty($trimmed)) {
            return false;
        }
        if ($regex !== null && !preg_match($regex, $trimmed)) {
            return false;
        }
        return true;
    }

    public static function validateEmail($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateDate($date, $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validateGender($gender): bool {
        $valid = ['male', 'female'];
        return in_array(strtolower($gender), $valid);
    }

    public static function validatePhone($phone): bool {
        $trimmed = trim($phone);
        if (empty($trimmed)) { 
            return false;
        }
        return preg_match('/^(06|07|05)\d{8}$/', $trimmed);
    }
}

class Helper {
    public static function prompt($label): string {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}

class BaseModel {
    protected $connection;
    protected $table;
    protected $primaryKey;

    public function __construct($table, $primaryKey = 'id') {
        $this->connection = Database::connect();
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }
    
    public function getAll(): array {
        $qry = "SELECT * FROM " . $this->table;
        $stmt = $this->connection->prepare($qry);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = [];
        foreach ($result as $row) {
            $arr[] = $this->fromArray($row);
        }
        return $arr;
    }

    public function getById($id): mixed {
        $qry = "SELECT * FROM $this->table WHERE $this->primaryKey = ?";
        $stmt = $this->connection->prepare($qry);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $this->fromArray($result);
        }
        return null;
    }

    public function getBy($column, $value): array {
        $qry = "SELECT * FROM $this->table WHERE $column = ?";
        $stmt = $this->connection->prepare($qry);
        $stmt->execute([$value]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = [];
        foreach ($results as $row) {
            $arr[] = $this->fromArray($row);
        }
        return $arr;
    }
    
    public function insert($data): bool {
        $keys = array_keys($data);
        $columns = implode(", ", $keys);
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $qry = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->connection->prepare($qry);
        return $stmt->execute(array_values($data));
    }

    public function update($id, $data): bool {
        $updates = [];
        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
        }
        $setClause = implode(", ", $updates);
        $qry = "UPDATE $this->table SET $setClause WHERE $this->primaryKey = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->connection->prepare($qry);
        return $stmt->execute($values);
    }

    public function delete($id): bool {
        $qry = "DELETE FROM $this->table WHERE $this->primaryKey = ?";
        $stmt = $this->connection->prepare($qry);
        return $stmt->execute([$id]);
    }

    public function search($criteria): array {
        $conditions = [];
        $values = [];
        
        foreach ($criteria as $column => $value) {
            if (!empty($value)) {
                $conditions[] = "$column LIKE ?";
                $values[] = "%$value%";
            }
        }
        
        if (empty($conditions)) {
            return $this->getAll();
        }
        
        $whereClause = implode(" AND ", $conditions);
        $qry = "SELECT * FROM $this->table WHERE $whereClause";
        
        $stmt = $this->connection->prepare($qry);
        $stmt->execute($values);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $arr = [];
        foreach ($results as $row) {
            $arr[] = $this->fromArray($row);
        }
        return $arr;
    }

    protected function fromArray($data): static {
        $instance = new static('', '');
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }
}

class Person extends BaseModel {
    protected $id;
    protected $first_name;
    protected $last_name;
    protected $phone_number;
    protected $email;

    public function __construct($table, $primaryKey = 'id') {
        parent::__construct($table, $primaryKey);
    }
    
    public function getId(): mixed {
        return $this->id;
    }
    
    public function getFirstName(): string {
        return $this->first_name;
    }
    
    public function setFirstName($first_name): void {
        $this->first_name = $first_name;
    }
    
    public function getLastName(): string {
        return $this->last_name;
    }
    
    public function setLastName($last_name): void {
        $this->last_name = $last_name;
    }
    
    public function getFullName(): string {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    public function getPhoneNumber(): string {
        return $this->phone_number;
    }
    
    public function setPhoneNumber($phone_number): void {
        $this->phone_number = $phone_number;
    }
    
    public function getEmail(): string {
        return $this->email;
    }
    
    public function setEmail($email): void {
        $this->email = $email;
    }

    public function validatePersonInfo(): array {
        $errors = [];

        if (!Validation::validateString($this->first_name, '/^[a-zA-Z\s]+$/')) {
            $errors[] = "First name cannot be empty and must only contain letters and spaces.";
        }

        if (!Validation::validateString($this->last_name, '/^[a-zA-Z\s]+$/')) {
            $errors[] = "Last name cannot be empty and must only contain letters and spaces.";
        }

        if (!Validation::validateEmail($this->email)) {
            $errors[] = "Invalid email format.";
        }

        if (!Validation::validatePhone($this->phone_number)) {
            $errors[] = "Invalid phone number format. Use digits, spaces, hyphens, or parentheses.";
        }

        return $errors;
    }

    public function save(): bool {
        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email
        ];

        if (isset($this->id)) {
            return $this->update($this->id, $data);
        } else {
            return $this->insert($data);
        }
    }
}

class Patient extends Person {
    protected $patient_id;
    protected $gender;
    protected $date_of_birth;
    protected $address;
    
    public function __construct() {
        parent::__construct('patients', 'patient_id');
    }

    public function getId(): mixed {
        return $this->patient_id;
    }
    
    public function getGender(): string {
        return $this->gender;
    }
    
    public function setGender($gender): void {
        $this->gender = strtolower($gender);
    }
    
    public function getDateOfBirth(): string {
        return $this->date_of_birth;
    }
    
    public function setDateOfBirth($date_of_birth): void {
        $this->date_of_birth = $date_of_birth;
    }
    
    public function getAddress(): string {
        return $this->address;
    }
    
    public function setAddress($address): void {
        $this->address = $address;
    }

    protected function fromArray($data): static {
        $instance = parent::fromArray($data);
        if (isset($data['patient_id'])) {
            $instance->patient_id = $data['patient_id'];
            $instance->id = $data['patient_id'];
        }
        return $instance;
    }

    public function save(): bool {
        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'address' => $this->address
        ];

        if (isset($this->patient_id)) {
            return $this->update($this->patient_id, $data);
        } else {
            return $this->insert($data);
        }
    }

    public function validate(): array {
        $errors = $this->validatePersonInfo();

        if (!Validation::validateGender($this->gender)) {
            $errors[] = "Must be 'male' or 'female'.";
        }

        if (!Validation::validateDate($this->date_of_birth)) {
            $errors[] = "Invalid date format. Use YYYY-MM-DD.";
        }

        return $errors;
    }
}

class Doctor extends Person {
    protected $doctor_id;
    protected $specialization;
    
    public function __construct() {
        parent::__construct('doctors', 'doctor_id');
    }

    public function getId(): mixed {
        return $this->doctor_id;
    }
    
    public function getSpecialization(): string {
        return $this->specialization;
    }
    
    public function setSpecialization($specialization): void {
        $this->specialization = $specialization;
    }

    protected function fromArray($data): static {
        $instance = parent::fromArray($data);
        if (isset($data['doctor_id'])) {
            $instance->doctor_id = $data['doctor_id'];
            $instance->id = $data['doctor_id'];
        }
        return $instance;
    }

    public function save(): bool {
        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'specialization' => $this->specialization,
            'phone_number' => $this->phone_number,
            'email' => $this->email
        ];

        if (isset($this->doctor_id)) {
            return $this->update($this->doctor_id, $data);
        } else {
            return $this->insert($data);
        }
    }

    public function validate(): array {
        $errors = $this->validatePersonInfo();

        if (!Validation::validateString($this->specialization)) {
            $errors[] = "Specialization cannot be empty.";
        }

        return $errors;
    }
}

class PatientsHandler {
    public function handlePatients(): void {
        while (true) {
            echo "\n=== Patient Management ===\n";
            echo "1. List all patients\n";
            echo "2. Search for a patient\n";
            echo "3. Add a patient\n";
            echo "4. Edit a patient\n";
            echo "5. Delete a patient\n";
            echo "6. Back to main menu\n";

            $choice = Helper::prompt("Action");

            switch ($choice) {
                case '1':
                    $this->getAll();
                    break;
                case '2':
                    $this->searchPatient();
                    break;
                case '3':
                    $this->addPatient();
                    break;
                case '4':
                    $this->editPatient();
                    break;
                case '5':
                    $this->deletePatient();
                    break;
                case '6':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function getAll(): void {
        $patient = new Patient();
        $patients = $patient->getAll();
        
        echo "\n=== Patient List ===\n";
        if (empty($patients)) {
            echo "No patients found.\n";
            return;
        }
        
        foreach ($patients as $p) {
            echo "ID: " . $p->getId() . " | " .
                 "Name: " . $p->getFullName() . " | " .
                 "Gender: " . $p->getGender() . " | " .
                 "DOB: " . $p->getDateOfBirth() . " | " .
                 "Email: " . $p->getEmail() . " | " .
                 "Address: " . $p->getAddress() . "\n";
        }
    }

    private function searchPatient(): void {
        echo "\n=== Search Patients ===\n";
        echo "Enter search criteria (leave blank to skip):\n";
        
        $first_name = Helper::prompt("First Name");
        $last_name = Helper::prompt("Last Name");
        $email = Helper::prompt("Email");
        $phone = Helper::prompt("Phone Number");
        
        $patient = new Patient();
        $criteria = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone_number' => $phone
        ];
        
        $patients = $patient->search($criteria);
        
        echo "\n=== Search Results ===\n";
        if (empty($patients)) {
            echo "No patients found matching your criteria.\n";
            return;
        }
        
        foreach ($patients as $p) {
            echo "ID: " . $p->getId() . " | " .
                 "Name: " . $p->getFullName() . " | " .
                 "Gender: " . $p->getGender() . " | " .
                 "DOB: " . $p->getDateOfBirth() . " | " .
                 "Email: " . $p->getEmail() . " | " .
                 "Address: " . $p->getAddress() . "\n";
        }
    }

    private function addPatient(): void {
        echo "\n--- Add New Patient ---\n";
        
        $patient = new Patient();
        
        $patient->setFirstName(Helper::prompt("First Name"));
        $patient->setLastName(Helper::prompt("Last Name"));
        $patient->setEmail(Helper::prompt("Email"));
        $patient->setPhoneNumber(Helper::prompt("Phone Number"));
        $patient->setGender(Helper::prompt("Gender (male/female)"));
        $patient->setDateOfBirth(Helper::prompt("Date of Birth (YYYY-MM-DD EX:2000-05-03)"));
        $patient->setAddress(Helper::prompt("Address"));

        $errors = $patient->validate();
        
        if (!empty($errors)) {
            echo "\nValidation errors:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
            return;
        }

        try {
            if ($patient->save()) {
                echo "Patient added successfully!\n";
            } else {
                echo "Failed to add patient.\n";
            }
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function editPatient(): void {
        echo "\n=== Edit Patient ===\n";
        
        $patient_id = Helper::prompt("Enter Patient ID to edit");
        if (empty($patient_id)) {
            echo "Patient ID is required.\n";
            return;
        }
        
        $patient = new Patient();
        $existing = $patient->getById($patient_id);
        
        if (!$existing) {
            echo "Patient with ID $patient_id not found.\n";
            return;
        }
        
        echo "\nCurrent Information:\n";
        echo "1. First Name: " . $existing->getFirstName() . "\n";
        echo "2. Last Name: " . $existing->getLastName() . "\n";
        echo "3. Email: " . $existing->getEmail() . "\n";
        echo "4. Phone: " . $existing->getPhoneNumber() . "\n";
        echo "5. Gender: " . $existing->getGender() . "\n";
        echo "6. Date of Birth: " . $existing->getDateOfBirth() . "\n";
        echo "7. Address: " . $existing->getAddress() . "\n";
        
        echo "\nEnter new values (press Enter to keep current value):\n";
        
        $first_name = Helper::prompt("First Name (" . $existing->getFirstName() . ")");
        $last_name = Helper::prompt("Last Name (" . $existing->getLastName() . ")");
        $email = Helper::prompt("Email (" . $existing->getEmail() . ")");
        $phone = Helper::prompt("Phone (" . $existing->getPhoneNumber() . ")");
        $gender = Helper::prompt("Gender (male/female) (" . $existing->getGender() . ")");
        $dob = Helper::prompt("Date of Birth (YYYY-MM-DD) (" . $existing->getDateOfBirth() . ")");
        $address = Helper::prompt("Address (" . $existing->getAddress() . ")");
        
        if (!empty($first_name)) $existing->setFirstName($first_name);
        if (!empty($last_name)) $existing->setLastName($last_name);
        if (!empty($email)) $existing->setEmail($email);
        if (!empty($phone)) $existing->setPhoneNumber($phone);
        if (!empty($gender)) $existing->setGender($gender);
        if (!empty($dob)) $existing->setDateOfBirth($dob);
        if (!empty($address)) $existing->setAddress($address);
        
        $errors = $existing->validate();
        
        if (!empty($errors)) {
            echo "\nValidation errors:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
            return;
        }

        try {
            if ($existing->save()) {
                echo "Patient updated successfully!\n";
            } else {
                echo "Failed to update patient.\n";
            }
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function deletePatient(): void {
        echo "\n=== Delete Patient ===\n";
        
        $patient_id = Helper::prompt("Enter Patient ID to delete");
        if (empty($patient_id)) {
            echo "Patient ID is required.\n";
            return;
        }
        
        $patient = new Patient();
        $existing = $patient->getById($patient_id);
        
        if (!$existing) {
            echo "Patient with ID $patient_id not found.\n";
            return;
        }
        
        echo "\nPatient to delete:\n";
        echo "ID: " . $existing->getId() . "\n";
        echo "Name: " . $existing->getFullName() . "\n";
        echo "Gender: " . $existing->getGender() . "\n";
        echo "Date of Birth: " . $existing->getDateOfBirth() . "\n";
        echo "Email: " . $existing->getEmail() . "\n";
        echo "Phone: " . $existing->getPhoneNumber() . "\n";
        echo "Address: " . $existing->getAddress() . "\n";
        
        $confirm = Helper::prompt("\nAre you sure you want to delete this patient? (y/n)");
        if (strtolower($confirm) !== 'y') {
            echo "Deletion cancelled.\n";
            return;
        }
        
        try {
            if ($existing->delete($patient_id)) {
                echo "Patient deleted successfully!\n";
            } else {
                echo "Failed to delete patient.\n";
            }
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }
}

class DoctorsHandler {
    public function handleDoctors(): void {
        while (true) {
            echo "\n=== Doctor Management ===\n";
            echo "1. List all doctors\n";
            echo "2. Search for a doctor\n";
            echo "3. Add a doctor\n";
            echo "4. Edit a doctor\n";
            echo "5. Delete a doctor\n";
            echo "6. Back to main menu\n";

            $choice = Helper::prompt("Action");

            switch ($choice) {
                case '1':
                    $this->getAll();
                    break;
                case '2':
                    $this->searchDoctor();
                    break;
                case '3':
                    $this->addDoctor();
                    break;
                case '4':
                    $this->editDoctor();
                    break;
                case '5':
                    $this->deleteDoctor();
                    break;
                case '6':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function getAll(): void {
        $doctor = new Doctor();
        $doctors = $doctor->getAll();
        
        echo "\n=== Doctors List ===\n";
        if (empty($doctors)) {
            echo "No doctors found.\n";
            return;
        }
        
        foreach ($doctors as $d) {
            echo "ID: " . $d->getId() . " | " .
                 "Name: " . $d->getFullName() . " | " .
                 "Specialization: " . $d->getSpecialization() . " | " .
                 "Phone: " . $d->getPhoneNumber() . " | " .
                 "Email: " . $d->getEmail() . "\n";
        }
    }

    private function searchDoctor(): void {
        echo "\n=== Search Doctors ===\n";
        echo "Enter search criteria (leave blank to skip):\n";
        
        $first_name = Helper::prompt("First Name");
        $last_name = Helper::prompt("Last Name");
        $email = Helper::prompt("Email");
        $phone = Helper::prompt("Phone Number");
        $specialization = Helper::prompt("Specialization");
        
        $doctor = new Doctor();
        $criteria = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone_number' => $phone,
            'specialization' => $specialization
        ];
        
        $doctors = $doctor->search($criteria);
        
        echo "\n=== Search Results ===\n";
        if (empty($doctors)) {
            echo "No doctors found matching your criteria.\n";
            return;
        }
        
        foreach ($doctors as $d) {
            echo "ID: " . $d->getId() . " | " .
                 "Name: " . $d->getFullName() . " | " .
                 "Specialization: " . $d->getSpecialization() . " | " .
                 "Phone: " . $d->getPhoneNumber() . " | " .
                 "Email: " . $d->getEmail() . "\n";
        }
    }

    private function addDoctor(): void {
        echo "\n--- Add New Doctor ---\n";
        
        $doctor = new Doctor();
        
        $doctor->setFirstName(Helper::prompt("First Name"));
        $doctor->setLastName(Helper::prompt("Last Name"));
        $doctor->setEmail(Helper::prompt("Email"));
        $doctor->setPhoneNumber(Helper::prompt("Phone Number"));
        $doctor->setSpecialization(Helper::prompt("Specialization"));

        $errors = $doctor->validate();
        
        if (!empty($errors)) {
            echo "\nValidation errors:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
            return;
        }

        try {
            if ($doctor->save()) {
                echo "Doctor added successfully!\n";
            } else {
                echo "Failed to add doctor.\n";
            }
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function editDoctor(): void {
        echo "\n=== Edit Doctor ===\n";
        
        $doctor_id = Helper::prompt("Enter Doctor ID to edit");
        if (empty($doctor_id)) {
            echo "Doctor ID is required.\n";
            return;
        }
        
        $doctor = new Doctor();
        $existing = $doctor->getById($doctor_id);
        
        if (!$existing) {
            echo "Doctor with ID $doctor_id not found.\n";
            return;
        }
        
        echo "\nCurrent Information:\n";
        echo "1. First Name: " . $existing->getFirstName() . "\n";
        echo "2. Last Name: " . $existing->getLastName() . "\n";
        echo "3. Email: " . $existing->getEmail() . "\n";
        echo "4. Phone: " . $existing->getPhoneNumber() . "\n";
        echo "5. Specialization: " . $existing->getSpecialization() . "\n";
        
        echo "\nEnter new values (press Enter to keep current value):\n";
        
        $first_name = Helper::prompt("First Name (" . $existing->getFirstName() . ")");
        $last_name = Helper::prompt("Last Name (" . $existing->getLastName() . ")");
        $email = Helper::prompt("Email (" . $existing->getEmail() . ")");
        $phone = Helper::prompt("Phone (" . $existing->getPhoneNumber() . ")");
        $specialization = Helper::prompt("Specialization (" . $existing->getSpecialization() . ")");
        
        if (!empty($first_name)) $existing->setFirstName($first_name);
        if (!empty($last_name)) $existing->setLastName($last_name);
        if (!empty($email)) $existing->setEmail($email);
        if (!empty($phone)) $existing->setPhoneNumber($phone);
        if (!empty($specialization)) $existing->setSpecialization($specialization);
        
        $errors = $existing->validate();
        
        if (!empty($errors)) {
            echo "\nValidation errors:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
            return;
        }

        try {
            if ($existing->save()) {
                echo "Doctor updated successfully!\n";
            } else {
                echo "Failed to update doctor.\n";
            }
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function deleteDoctor(): void {
        echo "\n=== Delete Doctor ===\n";
        
        $doctor_id = Helper::prompt("Enter Doctor ID to delete");
        if (empty($doctor_id)) {
            echo "Doctor ID is required.\n";
            return;
        }
        
        $doctor = new Doctor();
        $existing = $doctor->getById($doctor_id);
        
        if (!$existing) {
            echo "Doctor with ID $doctor_id not found.\n";
            return;
        }
        
        echo "\nDoctor to delete:\n";
        echo "ID: " . $existing->getId() . "\n";
        echo "Name: " . $existing->getFullName() . "\n";
        echo "Specialization: " . $existing->getSpecialization() . "\n";
        echo "Email: " . $existing->getEmail() . "\n";
        echo "Phone: " . $existing->getPhoneNumber() . "\n";
        
        $confirm = Helper::prompt("\nAre you sure you want to delete this doctor? (yes/no)");
        if (strtolower($confirm) !== 'yes') {
            echo "Deletion cancelled.\n";
            return;
        }
        
        try {
            if ($existing->delete($doctor_id)) {
                echo "Doctor deleted successfully!\n";
            } else {
                echo "Failed to delete doctor.\n";
            }
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }
}

class Departments {
    public function handleDepartments(): void {
        $connection = Database::connect();

        while (true) {
            echo "\n=== Department Management ===\n";
            echo "1. List departments\n";
            echo "2. Add a department\n";
            echo "3. Edit a department\n";
            echo "4. Delete a department\n";
            echo "5. Search departments\n";
            echo "6. Back to main menu\n";

            $choice = Helper::prompt("Action");

            switch ($choice) {
                case '1':
                    $this->listDepartments($connection);
                    break;
                case '2':
                    $this->addDepartment($connection);
                    break;
                case '3':
                    $this->editDepartment($connection);
                    break;
                case '4':
                    $this->deleteDepartment($connection);
                    break;
                case '5':
                    $this->searchDepartments($connection);
                    break;
                case '6':
                    return;
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function listDepartments($connection): void {
        $stmt = $connection->query("SELECT * FROM departments");
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n=== Departments List ===\n";
        if (empty($departments)) {
            echo "No departments found.\n";
            return;
        }
        
        foreach ($departments as $department) {
            echo "ID: " . $department['department_id'] . " | " .
                 "Name: " . $department['department_name'] . " | " .
                 "Location: " . $department['location'] . "\n";
        }
    }

    private function addDepartment($connection): void {
        echo "\n--- Add New Department ---\n";
        
        $name = Helper::prompt("Department Name");
        $location = Helper::prompt("Location");

        if (!Validation::validateString($name) || !Validation::validateString($location)) {
            echo "Name and Location cannot be empty.\n";
            return;
        }

        try {
            $sql = "INSERT INTO departments (department_name, location) VALUES (?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$name, $location]);
            echo "Department added successfully!\n";
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function editDepartment($connection): void {
        echo "\n=== Edit Department ===\n";
        
        $dept_id = Helper::prompt("Enter Department ID to edit");
        if (empty($dept_id)) {
            echo "Department ID is required.\n";
            return;
        }
        
        $stmt = $connection->prepare("SELECT * FROM departments WHERE department_id = ?");
        $stmt->execute([$dept_id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$department) {
            echo "Department with ID $dept_id not found.\n";
            return;
        }
        
        echo "\nCurrent Information:\n";
        echo "1. Name: " . $department['department_name'] . "\n";
        echo "2. Location: " . $department['location'] . "\n";
        
        echo "\nEnter new values (press Enter to keep current value):\n";
        
        $name = Helper::prompt("Department Name [" . $department['department_name'] . "]");
        $location = Helper::prompt("Location [" . $department['location'] . "]");
        
        if (empty($name)) $name = $department['department_name'];
        if (empty($location)) $location = $department['location'];
        
        if (!Validation::validateString($name) || !Validation::validateString($location)) {
            echo "Name and Location cannot be empty.\n";
            return;
        }

        try {
            $sql = "UPDATE departments SET department_name = ?, location = ? WHERE department_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$name, $location, $dept_id]);
            echo "Department updated successfully!\n";
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function deleteDepartment($connection): void {
        echo "\n=== Delete Department ===\n";
        
        $dept_id = Helper::prompt("Enter Department ID to delete");
        if (empty($dept_id)) {
            echo "Department ID is required.\n";
            return;
        }
        
        $stmt = $connection->prepare("SELECT * FROM departments WHERE department_id = ?");
        $stmt->execute([$dept_id]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$department) {
            echo "Department with ID $dept_id not found.\n";
            return;
        }
        
        echo "\nDepartment to delete:\n";
        echo "ID: " . $department['department_id'] . "\n";
        echo "Name: " . $department['department_name'] . "\n";
        echo "Location: " . $department['location'] . "\n";
        
        $confirm = Helper::prompt("\nAre you sure you want to delete this department? (yes/no)");
        if (strtolower($confirm) !== 'yes') {
            echo "Deletion cancelled.\n";
            return;
        }
        
        try {
            $sql = "DELETE FROM departments WHERE department_id = ?";
            $stmt = $connection->prepare($sql);
            $stmt->execute([$dept_id]);
            echo "Department deleted successfully!\n";
        } catch (PDOException $err) {
            echo "SQL Error: " . $err->getMessage() . "\n";
        }
    }

    private function searchDepartments($connection): void {
        echo "\n=== Search Departments ===\n";
        echo "Enter search :\n";
        
        $name = Helper::prompt("Department Name");
        $location = Helper::prompt("Location");
        
        $sql = "SELECT * FROM departments WHERE 1=1";
        $params = [];
        
        if (!empty($name)) {
            $sql .= " AND department_name LIKE ?";
            $params[] = "%$name%";
        }
        
        if (!empty($location)) {
            $sql .= " AND location LIKE ?";
            $params[] = "%$location%";
        }
        
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n=== Search Results ===\n";
        if (empty($departments)) {
            echo "No departments found matching your criteria.\n";
            return;
        }
        
        foreach ($departments as $department) {
            echo "ID: " . $department['department_id'] . " | " .
                 "Name: " . $department['department_name'] . " | " .
                 "Location: " . $department['location'] . "\n";
        }
    }
}

class MainMenu {
    public function run(): void {
        while (true) {
            $this->showMainMenu();
            $choice = Helper::prompt("Selection");

            switch ($choice) {
                case '1':
                    $patientsHandler = new PatientsHandler();
                    $patientsHandler->handlePatients();
                    break;
                case '2':
                    $doctorsHandler = new DoctorsHandler();
                    $doctorsHandler->handleDoctors();
                    break;
                case '3':
                    $departments = new Departments();
                    $departments->handleDepartments();
                    break;
                case '4':
                    echo "Statistics feature not yet implemented.\n";
                    break;
                case '5':
                    echo "Closing the program.\n";
                    exit();
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function showMainMenu(): void {
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