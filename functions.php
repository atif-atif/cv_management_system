<?php
/*
Plugin Name: CV Management System
Description: A simple CV management system for WordPress.
Version: 1.0
Author: Hammad Nazir
*/
ob_start();

// Enqueue necessary scripts and styles
function csv_management_enqueue_scripts() {
    wp_enqueue_style('csv-management-style', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_script('csv-management-script', plugins_url('/js/script.js', __FILE__), array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'csv_management_enqueue_scripts');

// Create the admin menu page

function csv_management_menu() {
    add_menu_page('CSV Management', 'CV Management', 'manage_options', 'csv-management', 'csv_management_page');
    add_submenu_page('csv-management', 'HR Management', 'HR Dashboard', 'manage_options', 'hr-management', 'hr_management_page');
    add_submenu_page('csv-management', 'Received CVs', 'Received CVs', 'manage_options', 'received-cvs', 'received_cvs_page');
    add_submenu_page('csv-management', 'PDF Generation', 'PDF Generation', 'manage_options', 'pdf-generation', 'pdfgenreration_management_page');
    add_submenu_page('csv-management', 'Shortlisted Candidates', 'Shortlisted Candidates', 'manage_options', 'shortlisted-candidates', 'shortlisted_candidates_page');
}
add_action('admin_menu', 'csv_management_menu');

// Function to display the Received CVs page
function shortlisted_candidates_page() {
    global $wpdb;

    $table_name = 'wp_shortlisted_candidates';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo "<p>Table '$table_name' not found!</p>";
        return;
    }

    // Retrieve data from the table
    $shortlisted_candidates_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    ?>

<!-- shortlist_candidate -->
<!-- shortlist_candidate -->

    <div class="wrap">
        <h1>Shortlisted Candidates</h1>

        <table class="wp-list-table widefat fixed striped" id="shortlisted-candidates-table">
    <!-- Table Header -->
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
            <th>PDF URL</th>
            <th>Comments</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($shortlisted_candidates_data as $candidate) {
        echo '<tr>';
        echo '<td>' . esc_html($candidate['id']) . '</td>';
        echo '<td>' . esc_html($candidate['full_name']) . '</td>';
        echo '<td>' . esc_html($candidate['email']) . '</td>';
        echo '<td>' . esc_html($candidate['degree']) . '</td>';
        echo '<td>' . esc_html($candidate['university']) . '</td>';
        echo '<td>' . esc_html($candidate['job_title']) . '</td>';
        echo '<td>' . esc_html($candidate['company']) . '</td>';
        echo '<td>' . esc_html($candidate['employment_history']) . '</td>';
        echo '<td>' . esc_html($candidate['skills']) . '</td>';
        echo '<td>' . esc_html($candidate['linkedin']) . '</td>';
        echo '<td>' . esc_html($candidate['phno']) . '</td>';
        echo '<td>' . esc_html($candidate['address']) . '</td>';
        
        // Button to download PDF
        echo '<td><form method="post"><button type="submit" name="download_pdf" value="' . esc_attr($candidate['pdf_url']) . '">Download PDF</button></form></td>';
        
        echo '<td>' . esc_html($candidate['comments']) . '</td>';
        echo '</tr>';
    }
    ?>
</tbody>
<?php
// Handle form submission
if (isset($_POST['download_pdf'])) {
    // Get the PDF URL from the form submission
    $pdf_url = $_POST['download_pdf'];
    
    // Validate the PDF URL and extract the file name
    $file_name = basename($pdf_url);
    
    // Set headers for file download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    
    // Read the file and output its contents
    readfile($pdf_url);
    
    // Exit to prevent further output
    exit;
}
?>



</table>

    </div>
    <?php
}

//shortlist candidate end

function received_cvs_page() {
    global $wpdb;

    $table_name = 'resumes';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo "<p>Table '$table_name' not found!</p>";
        return;
    }

    // Check if search is initiated
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Retrieve data from the table with optional search filter
    $sql = "SELECT * FROM $table_name";
    if (!empty($search_query)) {
        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE skills LIKE %s", '%' . $search_query . '%');
    }

    // Retrieve data from the table
    $resumes_data = $wpdb->get_results($sql, ARRAY_A);

    ?>
    <div class="wrap">
        <h1>Received CVs</h1>

        <!-- Search Form -->
        <form id="search-form">
            <label for="search">Search by Skills:</label>
            <input type="text" name="search" id="search" value="<?php echo esc_attr($search_query); ?>" />
            <input type="submit" value="Search" class="button" />
        </form>

        <table class="wp-list-table widefat fixed striped" id="cv-table">
            <!-- Table Header -->
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
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
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="resume_id" value="' . esc_html($resume['id']) . '">';
        echo '<button type="submit" name="insert" title="' . esc_html($resume['id']) . '">Shortlist</button>';
        echo '</form>';
        echo '<button onclick="forwardToPM(' . $resume['id'] . ')">Forward to PM</button>';
        echo '</td>';

        echo '</tr>';
    }
    ?>

</tbody>


<script>
function printTable() {
    window.print();
}
</script>

<!-- Button to trigger printing -->
<button onclick="printTable()">Print Table</button>
<?php
if (isset($_POST['insert'])) {
    // Get the resume ID from the form submission
    $resume_id = isset($_POST['resume_id']) ? intval($_POST['resume_id']) : 0;

    // Check if the ID is valid
    if ($resume_id > 0) {
        global $wpdb;

        // Prepare the data to be inserted
        $resume_data = $wpdb->get_row("SELECT * FROM resumes WHERE id = $resume_id", ARRAY_A);

        // Check if resume data is retrieved successfully
        if ($resume_data) {
            // Insert the data into wp_shortlisted_candidates table
            $insert_result = $wpdb->insert(
                'wp_shortlisted_candidates',
                array(
                    'id' => $resume_data['id'],
                    'full_name' => $resume_data['full_name'],
                    'email' => $resume_data['email'],
                    'degree' => $resume_data['degree'],
                    'university' => $resume_data['university'],
                    'job_title' => $resume_data['job_title'],
                    'company' => $resume_data['company'],
                    'employment_history' => $resume_data['employment_history'],
                    'skills' => $resume_data['skills'],
                    'linkedin' => $resume_data['linkedin'],
                    'phno' => $resume_data['phno'],
                    'address' => $resume_data['address'],
                    'pdf_url' => $resume_data['pdf_url'],
                    'comments' => null // Assuming comments field is nullable
                )
            );

            if ($insert_result !== false) {
                echo "Record inserted successfully.";
            } else {
                echo "Error inserting record.";
            }
        } else {
            echo "Error retrieving resume data.";
        }
    }
}
?>

        </table>
    </div>

    Shortlist Overlay
    <div id="shortlist-overlay" class="overlay">
        <div class="modal">
            <span class="close" onclick="closeShortlistForm()">&times;</span>
            <h2>Shortlist Candidate</h2>
            <form id="shortlist-form">
    <!-- Display all the fields with their values -->
    <?php foreach ($resume as $field => $value) : ?>
        <label for="<?php echo esc_attr($field); ?>"><?php echo esc_html($field); ?>:</label>
        <input type="text" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>" readonly />
    <?php endforeach; ?>

    <label for="comments">Comments:</label>
    <textarea name="comments" id="comments" rows="4" cols="50"></textarea>

    <input type="submit" value="Shortlist" class="button" />
</form>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('search-form');
            const cvTable = document.getElementById('cv-table');

            searchForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const searchQuery = document.getElementById('search').value;

                // Use AJAX to fetch filtered data based on search query
                fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_filtered_data&search=' + searchQuery)
                    .then(response => response.text())
                    .then(data => {
                        // Update the table with the fetched data
                        cvTable.innerHTML = data;
                    });
            });
        });
        function shortlistCandidate(id) {
    // Fetch the resume data based on the ID
    const resumeData = <?php echo json_encode($resumes_data); ?>;

    // Find the resume with the matching ID
    const selectedResume = resumeData.find(resume => resume.id === id);

    if (selectedResume) {
        // Display the overlay with the shortlist form
        const shortlistOverlay = document.getElementById('shortlist-overlay');
        shortlistOverlay.style.display = 'block';

        // Create a form element
        const shortlistForm = document.createElement('form');
        shortlistForm.id = 'shortlist-form';

        // Populate the form fields with the resume data
        for (const field in selectedResume) {
            if (selectedResume.hasOwnProperty(field)) {
                const label = document.createElement('label');
                label.htmlFor = field;
                label.textContent = field + ':';

                const inputField = document.createElement('input');
                inputField.type = 'text';
                inputField.name = field;
                inputField.value = selectedResume[field];
                inputField.readOnly = true;

                shortlistForm.appendChild(label);
                shortlistForm.appendChild(inputField);
            }
        }

        // Add a comments field
        const commentsField = document.createElement('textarea');
        commentsField.name = 'comments';
        commentsField.id = 'comments';
        commentsField.rows = '4';
        commentsField.cols = '50';
        shortlistForm.appendChild(commentsField);

        // Add a submit button
        const submitButton = document.createElement('input');
        submitButton.type = 'submit';
        submitButton.value = 'Shortlist';
        submitButton.className = 'button';
        shortlistForm.appendChild(submitButton);

        // Add a submit event listener to the form
        shortlistForm.addEventListener('submit', function(event) {
            event.preventDefault();

            // Use AJAX to send the form data to a server-side script for storage
            const formData = new FormData(shortlistForm);
            formData.append('resume_id', id);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=shortlist_candidate', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                // Hide the overlay after submission
                shortlistOverlay.style.display = 'none';
            });
        });

        // Append the form to the overlay
        shortlistOverlay.innerHTML = ''; // Clear previous content
        shortlistOverlay.appendChild(shortlistForm);
    } else {
         const id = resumeData.id;
    const data = resumeData.resume;

    // Rest of the code remains unchanged

    for (const field in data) {
        if (data.hasOwnProperty(field)) {
            const label = document.createElement('label');
            label.htmlFor = field;
            label.textContent = field + ':';

            const inputField = document.createElement('input');
            inputField.type = 'text';
            inputField.name = field;
            inputField.value = data[field];
            inputField.readOnly = true;

            shortlistForm.appendChild(label);
            shortlistForm.appendChild(inputField);
        }
    }
        alert('Resume not found for ID ' + id);
    }
}


        function forwardToPM(id) {
            // Add your logic for forwarding the candidate to PM with the given ID
            alert('Forwarding candidate with ID ' + id + ' to PM');
        }

        function closeShortlistForm() {
            // Close the overlay
            document.getElementById('shortlist-overlay').style.display = 'none';
        }
    </script>
<?php }

// AJAX handler
add_action('wp_ajax_get_filtered_data', 'get_filtered_data');
add_action('wp_ajax_nopriv_get_filtered_data', 'get_filtered_data');

function get_filtered_data() {
    global $wpdb;

    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    $sql = $wpdb->prepare("SELECT * FROM resumes WHERE skills LIKE %s", '%' . $search_query . '%');
    $resumes_data = $wpdb->get_results($sql, ARRAY_A);

    ob_start();

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
        echo '<button onclick="shortlistCandidate(' . json_encode($resume) . ')">Shortlist</button>';
        echo '<button onclick="forwardToPM(' . $resume['id'] . ')">Forward to PM</button>';
        echo '</td>';

        echo '</tr>';
    }

    $output = ob_get_clean();
    echo $output;

    wp_die();
}

// AJAX handler for shortlisting candidate
add_action('wp_ajax_shortlist_candidate', 'shortlist_candidate');
add_action('wp_ajax_nopriv_shortlist_candidate', 'shortlist_candidate');

function shortlist_candidate() {
    // Process the shortlist form submission and store the data in the database
    $resume_id = isset($_POST['resume_id']) ? intval($_POST['resume_id']) : 0;
    $comments = isset($_POST['comments']) ? sanitize_text_field($_POST['comments']) : '';

    // Perform necessary database operations here
    // For example, insert data into the shortlisted candidates table

    $response = array('message' => 'Candidate shortlisted successfully!');
    wp_send_json($response);
}


function get_cv_pdf_url($cv_name) {
    global $wpdb;
    $table_name = 'resumes';

    $pdf_filename = $wpdb->get_var(
        $wpdb->prepare("SELECT pdf_filename FROM $table_name WHERE cv_name = %s", $cv_name)
    );

    if ($pdf_filename) {
        // Assuming the uploads folder is located in the WordPress content directory
        $pdf_path = WP_CONTENT_DIR . '/uploads/' . $pdf_filename;

        if (file_exists($pdf_path)) {
            $pdf_url = content_url("/uploads/$pdf_filename");
            return esc_url($pdf_url);
        } else {
            return false; // PDF file not found in the expected location
        }
    }

    return false;
}


// Function to display the CSV management page
function csv_management_page() {
    ?>
    <div class="wrap">
        <h1>CV Management System</h1>
        
      

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
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Received CVs</h2>
                <p>134</p>
            </div>
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Forwarded Candidates</h2>
                <p>56</p>
            </div>
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents.png" width="70" height="52" alt="Icon 1"></a>
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
        
        <form method="post" action="">
            <label for="hr_name">Enter HR Name:</label>
            <input type="text" name="hr_name" id="hr_name" required><br>

            <label for="select_data">Select Data:</label>
            <input type="date" name="reviewed_cvs" id="reviewed_cvs" required><br>
            <br>

            <label for="reviewed_cvs">Reviewed CVs:</label>
            <input type="number" name="reviewed_cvs" id="reviewed_cvs" required><br>

            <label for="shortlisted_cvs">Shortlisted CVs:</label>
            <input type="number" name="shortlisted_cvs" id="shortlisted_cvs" required><br>

            <label for="hired_interns">Hired Interns:</label>
            <input type="number" name="hired_interns" id="hired_interns" required><br>

            <label for="hired_employees">Hired Employees:</label>
            <input type="number" name="hired_employees" id="hired_employees" required><br>

            <label for="workforce_required">Workforce Required:</label>
            <input type="number" name="workforce_required" id="workforce_required" required><br>

            <button type="submit" name="generate_pdf">Generate PDF</button>
        </form>
        <?php
        if (isset($_POST['generate_pdf'])) {
            // Process form data and generate PDF here
            // Use the entered data to generate the HR report in PDF format
            // You may want to use a PDF generation library like TCPDF or FPDF
            // Example: include the library and write code to create a PDF
            // ...

            // For demonstration purposes, let's assume a function generate_pdf() is used
            generate_pdf($_POST);
        }
        ?>
    </div>
    <?php
}
function generate_pdf($data) {
    // Placeholder function
    // Implement the actual PDF generation code here using a library like TCPDF or FPDF
    // Example: TCPDF code
    // ...
  
    // For demonstration purposes, let's just print the data
    echo '<pre>';
    print_r($data);
    echo '</pre>';
?>
    <script>
    function printTable() {
        window.print();
    }
    </script>

    <!-- Button to trigger printing -->
    <button onclick="printTable()">Print Table</button>
    <?php
}
// Add the custom shortcode function
function custom_shortcode_function() {
    ob_start(); // Start output buffering
    ?>

    <div class="wrap">
        <h4>CV Submission Form</h4>

        <form method="post" enctype="multipart/form-data">
            <!-- Form fields -->
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
            <!-- Add more skill checkboxes as needed -->

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
    </div>

    <?php

    // Process Form Submission
    if (isset($_POST['resume_submission_submit'])) {
        // Establish Database Connection
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = 'root';
        $db_name = 'local';

        global $wpdb;

        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

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

        // Collect form data
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $degree = $_POST['degree'];
        $university = $_POST['university'];
        $job_title = $_POST['job_title'];
        $company = $_POST['company'];
        $employment_history = $_POST['employment_history'];
        $linkedin = $_POST['linkedin'];
        $phno = $_POST['phno'];
        $address = $_POST['address'];

        // Skills handling
        $skills = isset($_POST['skills']) ? implode(', ', $_POST['skills']) : '';

        // Insert data into the database
        $sql = "INSERT INTO resumes (full_name, email, degree, university, job_title, company, employment_history, skills, linkedin, phno, address, pdf_url) 
                VALUES ('$full_name', '$email', '$degree', '$university', '$job_title', '$company', '$employment_history', '$skills', '$linkedin', '$phno', '$address', '$cv_url')";

        if ($conn->query($sql) === TRUE) {
            echo "CV Submitted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        // Send email to user
        $to = $email;
        $subject = 'CV Submission Confirmation';
        $message = 'Dear ' . $full_name . ',\n\n';
        $message .= 'Thank you for submitting your CV. We have received your submission successfully.';
        $headers = 'From: atifwpbrigade@gmail.com' . "\r\n";
        $headers .= 'Cc: atif.44e@gmail.com' . "\r\n"; // CC to admin email

        // Send email
        $sent_to_user = mail($to, $subject, $message, $headers);

        // Check if email sent successfully to user
        if ($sent_to_user) {
            // header("location: http://localhost:10016/resume-submission-portal/");
            echo '<p>Email sent successfully!</p>';
        } else {
            echo '<p>Failed to send email.</p>';
        }

        // Close Database Connection
        $conn->close();
    }

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






