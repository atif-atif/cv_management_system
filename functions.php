<?php
/*
Plugin Name: CV Management System
Description: A simple CV management system for WordPress.
Version: 1.0
Author: Hammad Nazir
*/

// Enqueue necessary scripts and styles
function csv_management_enqueue_scripts() {
    wp_enqueue_style('csv-management-style', plugins_url('/css/style.css', __FILE__));
    // wp_enqueue_script('csv-management-script', plugins_url('/js/script.js', _FILE_), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'csv_management_enqueue_scripts');

// Create the admin menu page

function csv_management_menu() {
    add_menu_page('CSV Management', 'CV Management', 'manage_options', 'csv-management', 'csv_management_page');
    add_submenu_page('csv-management', 'HR Management', 'HR Dashboard', 'manage_options', 'hr-management', 'hr_management_page');
    add_submenu_page('csv-management', 'Received CVs', 'Received CVs', 'manage_options', 'received-cvs', 'received_cvs_page');
    add_submenu_page('csv-management', 'PDF Generation', 'PDF Generation', 'manage_options', 'pdf-generation', 'pdfgenreration_management_page');
}
add_action('admin_menu', 'csv_management_menu');

// Function to display the Received CVs page
function received_cvs_page() {
    global $wpdb;

    $table_name = 'resumes';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo "<p>Table '$table_name' not found!</p>";
        return;
    }

    // Retrieve data from the table
    $resumes_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    ?>
    <div class="wrap">
        <h1>Received CVs</h1>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Degree</th>
                    <th>University</th>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Employment History</th>
                    <th>Skills</th>
                    <th>LinkedIn</th>
                    <th>Phone Number</th>
                    <th>Address</th>
                    <th>CV Name</th>
                    <th>Action</th> <!-- Added Action column -->
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($resumes_data as $resume) {
                    echo '<tr>';
                    echo '<td>' . esc_html($resume['id']) . '</td>';
                    echo '<td>' . esc_html($resume['full_name']) . '</td>';
                    echo '<td>' . esc_html($resume['email']) . '</td>';
                    echo '<td>' . esc_html($resume['degree']) . '</td>';
                    echo '<td>' . esc_html($resume['university']) . '</td>';
                    echo '<td>' . esc_html($resume['job_title']) . '</td>';
                    echo '<td>' . esc_html($resume['company']) . '</td>';
                    echo '<td>' . esc_html($resume['employment_history']) . '</td>';
                    echo '<td>' . esc_html($resume['skills']) . '</td>';
                    echo '<td>' . esc_html($resume['linkedin']) . '</td>';
                    echo '<td>' . esc_html($resume['phno']) . '</td>';
                    echo '<td>' . esc_html($resume['address']) . '</td>';
                    echo '<td>';
                    $cv_name = esc_html($resume['pdf_url']);
                    $pdf_url = get_cv_pdf_url($cv_name); // Function to get PDF URL

                    if ($pdf_url) {
                        echo '<a href="'. esc_url($pdf_url) . '" target="_blank">Open PDF</a>';
                    } else {
                        echo 'No PDF available';
                    }
                    echo '</td>';
                    
                    // Action buttons
                    echo '<td>';
                    echo '<button onclick="shortlistCandidate(' . $resume['id'] . ')">Shortlist</button>';
                    echo '<button onclick="forwardToPM(' . $resume['id'] . ')">Forward to PM</button>';
                    echo '</td>';

                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function shortlistCandidate(id)
 {
            // Add your logic for shortlisting the candidate with the given ID
            alert('Shortlisting candidate with ID ' + id);
        }

        function forwardToPM(id)
 {
            // Add your logic for forwarding the candidate to PM with the given ID
            alert('Forwarding candidate with ID ' + id + ' to PM');
        }
    </script>
    <?php
}

function get_cv_pdf_url($cv_name) {
    global $wpdb;
    $table_name = 'resumes';

    $pdf_url = $wpdb->get_var(
        $wpdb->prepare("SELECT pdf_url FROM $table_name WHERE cv_name = %s", $cv_name)
    );

    // Debugging statement
    error_log('CV Name: ' . $cv_name . ', PDF URL: ' . $pdf_url);

    return $pdf_url ? $pdf_url : false;
}

// Function to display the CSV management page
function csv_management_page() {
    ?>
    <div class="wrap">
        <h1>CV Management System</h1>
        
        <!-- Add your HTML and PHP code for CSV management here -->

    </div>
    <?php
}
// Function to display the HR Management page
function hr_management_page() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        // Redirect to the login page if the user is not logged in
        wp_redirect(wp_login_url());
        exit;
    }

    // Get the current user's information
    $current_user = wp_get_current_user();
    $user_display_name = $current_user->display_name;

    // Display the HR Dashboard
    ?>
    <div class="wrap">
        <h1>HR Dashboard</h1>

        <div class="hr-dashboard-container" style="display: flex; justify-content: space-around;">

            <!-- HR Dashboard Section 1 -->
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/profile.png" width="70" height="52" alt="Icon 1"></a>
                <h2><?php echo esc_html($user_display_name); ?></h2>
                <p>Helping Hand for HRs</p>
            </div>
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents-2.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Received CVs</h2>
                <p>134</p>
            </div>
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents-2.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Forwarded Candidates</h2>
                <p>56</p>
            </div>
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents-2.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Shortlisted Candidates</h2>
                <p>20</p>
            </div>

            <!-- Add more dashboard sections as needed -->

        </div>
    </div>
    <?php
}






function handle_resume_submission() {
    if (isset($_POST['resume_submission_submit'])) {
        // Collect submitted data
        $full_name = sanitize_text_field($_POST['full_name']);
        $email = sanitize_email($_POST['email']);
        // Collect other personal, educational, and professional details as needed

        // Process the data (you can store it in a database, etc.)
        // For demonstration purposes, let's just print the data
        echo "<h2>Submitted Data:</h2>";
        echo "<p>Full Name: $full_name</p>";
        echo "<p>Email: $email</p>";
        // Display other submitted details

        // You can also store the data in a database or perform other actions as needed
    }
}

// Hook to handle resume submission when the form is submitted
add_action('admin_init', 'handle_resume_submission');

function pdfgenreration_management_page() {
    ?>
    <div class="wrap">
        <h1>PDF Generation</h1>
        <!-- Add your Personnels-related HTML and PHP code here -->
    </div>
    <?php
}
function custom_shortcode_function() {
    ob_start(); // Start output buffering
    ?>
    <div class="wrap">
        <h4>CV Submission Form</h4>

        <form method="post" enctype="multipart/form-data">
         <h5><strong>Personal Details</strong></h5>
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <!-- Add more personal details fields as needed -->

            <h5><strong>Educational Details</strong></h5>
            <label for="degree">Degree:</label>
            <input type="text" name="degree" required>

            <label for="university">University:</label>
            <input type="text" name="university" required>

            <!-- Add more educational details fields as needed -->

            <h5><strong>Professional Details</strong></h5>
            <label for="job_title">Job Title:</label>
            <input type="text" name="job_title" required>

            <label for="company">Company:</label>
            <input type="text" name="company" required>

            <!-- Add more professional details fields as needed -->

            <h5><strong>Employment History</strong></h5>
            <label for="employment_history">Employment History:</label>
            <textarea name="employment_history" rows="4" cols="50"></textarea>

            <h5><strong>Skills</strong></h5>
            <label><input type="checkbox" name="skills[]" value="Theme Development"> Theme Development</label>
            <label><input type="checkbox" name="skills[]" value="Plugin Development"> Plugin Development</label>
            <label><input type="checkbox" name="skills[]" value="PSD to HTML&CSS"> PSD to HTML&CSS</label> <br>
            <label>Other :</label><input type="text" name="skills[]" value="" placeholder="java,c++"><br>

            <h5><strong>Contact Details</strong></h5>
            <label for="linkedin">Linkedin Profile:</label>
            <input type="text" name="linkedin" required>

            <label for="phno">Phone no:</label>
            <input type="text" name="phno" required>
            
            <label for="address">Address:</label>
            <textarea name="address" rows="4" cols="50"></textarea><br>
            <h5>Upload CV (PDF)</h5>
            <label for="cv_upload">Upload CV:</label>
            <input type="file" name="cv_upload" accept=".pdf">
            <input type="submit" name="resume_submission_submit" value="Submit">
        </form>

        <form method="post" action="?page=pdf-generation">
            <!-- Assuming 'pdfgenreration_management_page' is the correct page slug for PDF Generation -->
            <input type="hidden" name="pdf_generation_data" value="1">
           
        </form>
    </div>
    <?php

    // Establish Database Connection
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = 'root';
    $db_name = 'local';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Process Form Submission
    if (isset($_POST['resume_submission_submit'])) {
        // Process File Upload
        if (isset($_FILES['cv_upload']) && $_FILES['cv_upload']['error'] == 0) {
            $target_directory = "uploads/";
            $target_file = $target_directory . basename($_FILES['cv_upload']['name']);

            if (move_uploaded_file($_FILES['cv_upload']['tmp_name'], $target_file)) {
                echo "File uploaded successfully.";
                $cv_url = $target_file;
            } else {
                echo "Error uploading file.";
                $cv_url = "";
            }
        } else {
            echo "No file uploaded.";
            $cv_url = "";
        }
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $degree = mysqli_real_escape_string($conn, $_POST['degree']);
        $university = mysqli_real_escape_string($conn, $_POST['university']);
        $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
        $company = mysqli_real_escape_string($conn, $_POST['company']);
        $employment_history = mysqli_real_escape_string($conn, $_POST['employment_history']);
        $linkedin = mysqli_real_escape_string($conn, $_POST['linkedin']);
        $phno = mysqli_real_escape_string($conn, $_POST['phno']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
   // Skills handling
   $skills = isset($_POST['skills']) ? implode(', ', $_POST['skills']) : '';
        // Insert data into the database
        $sql = "INSERT INTO resumes (full_name, email, degree, university, job_title, company, employment_history, skills, linkedin, phno, address,pdf_url) 
                VALUES ('$full_name', '$email', '$degree', '$university', '$job_title', '$company', '$employment_history', '$skills', '$linkedin', '$phno', '$address', '$cv_url')";

        if ($conn->query($sql) === TRUE) {
            echo "CV Submitted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Close Database Connection
    $conn->close();

    $output = ob_get_clean(); // Get the output and clean the buffer
    return $output; // Return the buffered output
}

add_shortcode('custom_shortcode', 'custom_shortcode_function');
// Handle CV file upload

function pdfcv_shortcode_function() {
    ob_start(); // Start output buffering

    // Check if the form is submitted
    if (isset($_POST['resume_submission_submit'])) {
        // Establish Database Connection
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = 'root';
        $db_name = 'local';

        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Handle File Upload
        if (isset($_FILES['cv_upload']) && $_FILES['cv_upload']['error'] === UPLOAD_ERR_OK) {
            $cv_tmp_name = $_FILES['cv_upload']['tmp_name'];
            $cv_name = basename($_FILES['cv_upload']['name']);
            
            // Create the "uploads" folder if it doesn't exist
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $cv_destination = "uploads/$cv_name"; // Choose your desired destination folder

            if (move_uploaded_file($cv_tmp_name, $cv_destination)) {
                // Insert data into the database
                $sql = "INSERT INTO cvs (cv_name) VALUES ('$cv_name')";

                if ($conn->query($sql) === TRUE) {
                    echo "CV uploaded and record inserted successfully.";
                } else {
                    echo "Error inserting record: " . $conn->error;
                }
            } else {
                echo "Error uploading CV.";
            }
        }

        // Close Database Connection
        $conn->close();
    }

    // Display the HTML form
    ?>
    <div class="wrap">
        <form method="post" enctype="multipart/form-data">
            <h2>Upload CV (PDF)</h2>
            <label for="cv_upload">Upload CV:</label>
            <input type="file" name="cv_upload" accept=".pdf">
            <input type="submit" name="resume_submission_submit" value="Submit">
        </form>
    </div>
    <?php

    $output = ob_get_clean(); // Get the output and clean the buffer
    return $output; // Return the buffered output
}

add_shortcode('pdfcv_shortcode', 'pdfcv_shortcode_function');


function email_shortcode_function($content) {
    // Check if the shortcode exists in the content
    if (strpos($content, '[email_inquiry_shortcode]') !== false) {
        ob_start();
        ?>
        <div class="wrap">
            <form method="post">
                <button><a href="mailto:hr@wpbrigade.com" class="button">Email to WP</a></button>
            </form>
        </div>
        <?php
        $form_content = ob_get_clean();

        // Replace the shortcode with the form content
        $content = str_replace('[email_inquiry_shortcode]', $form_content, $content);
    }

    return $content;
}
add_filter('the_content', 'email_shortcode_function');






function handle_csv_upload() {
    if (isset($_FILES['csv_file']) && !empty($_FILES['csv_file']['name'])) {
        $uploaded_file = wp_handle_upload($_FILES['csv_file'], array('test_form' => false));
        
        if (!isset($uploaded_file['error'])) {
            $file_path = $uploaded_file['file'];
            // Process the CSV file as needed
            // You can use functions like fgetcsv to read the CSV content
            // Example: $csv_data = array_map('str_getcsv', file($file_path));
        } else {
            // Handle the error
            echo 'Error uploading file: ' . $uploaded_file['error'];
        }
    }
}

// Hook to handle CSV file upload when the form is submitted
if (isset($_POST['csv_upload_submit'])) {
    handle_csv_upload();
}

// Add your additional functionality or hooks as needed