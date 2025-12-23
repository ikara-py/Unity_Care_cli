<?php


class MainMenu{
    public function run(){
        while (true) {
            $this->showMainMenu();
            $choice = $this->prompt("Selection");

            switch ($choice) {
                case '1':
                    $this->handlePatients();
                    break;
                case '2':
                    $this->handleDoctors();
                    break;
                case '3':
                    $this->handleDepartments();
                    break;
                case '4':
                    $this->handleStats();
                    break;
                case '5':
                    echo "Closing the program.\n";
                    exit();
                default:
                    echo "Invalid choice.\n";
            }
        }
    }

    private function showMainMenu(){
        echo "\n=== Unity Care CLI ===\n";
        echo "1. Manage patients\n";
        echo "2. Manage doctors\n";
        echo "3. Manage departments\n";
        echo "4. Statistics\n";
        echo "5. Quit\n";
    }

    
    private function handlePatients(){
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
                    echo "Displaying list\n";
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

    
    private function handleDoctors(){
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
                case '6':
                    return;
                default:
                    echo "Option currently under development.\n";
            }
        }
    }

    
    private function handleDepartments(){
        while (true) {
            echo "\n=== Department Management ===\n";
            echo "1. List departments\n";
            echo "2. Add a department\n";
            echo "3. Back\n";

            $choice = $this->prompt("Action");

            if ($choice === '3') return;
            echo "Chosen option: $choice\n";
        }
    }

    
    private function handleStats(){
        echo "\n=== Global Statistics ===\n";
        echo "Average patient age\n";
        echo "Average doctor seniority\n";
        echo "Most populated department\n";
        }

    
    private function prompt($label)  {
        echo "$label : ";
        return trim(fgets(STDIN));
    }
}

$cli = new MainMenu();
$cli->run();