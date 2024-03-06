<?php
/*
Plugin Name: CV Management System
Description: A simple CV management system for WordPress.
Version: 1.0
Author: atif
*/
ob_start();

// redirect to user login
function redirect_non_logged_in_users_to_login() {
    if ( ! is_user_logged_in() && ! is_page('login') ) {
        wp_redirect( wp_login_url() );
        exit();
    }
}
add_action( 'template_redirect', 'redirect_non_logged_in_users_to_login' );



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
        <!-- mail to PM start -->
        <form method="post">
    <button type="submit" name="emailpm">Email Forward to PM</button>
</form>

<?php
if(isset($_POST['emailpm'])){
    $to = 'nouman.wpbrigade@gmail.com'; // jis ko send krne ha
    $subject = 'Check the short listed candidate list';
    $message = 'Please check the short listed candidate list.';
    $headers = array('Content-Type: text/html; charset=UTF-8', 'From: atifwpbrigade@gmail.com'); // jis email sa message huna ha

    $sent = wp_mail($to, $subject, $message, $headers);

    if($sent){
        echo '<p>Email sent successfully!</p>';
    } else {
        echo '<p>Failed to send email.</p>';
    }
}
?>

        <!-- mail to PM end -->

        <table class="wp-list-table widefat fixed striped" id="shortlisted-candidates-table">
    <!-- Table Header -->
    <thead>
        <tr>
            <th style="width: 15px;">ID</th>
            <th style="width: 100px;">Full Name</th>
            <th style="width: 100px;">Email</th>
            <th style="width: 50px;" >Degree</th>
            <th style="width: 100px;">University</th>
            <th style="width: 100px;">Job Title</th>
            <th style="width: 100px;">Company</th>
            <th style="width: 100px;">Employment History</th>
            <th style="width: 100px;">Skills</th>
            <th style="width: 100px;">LinkedIn</th>
            <th style="width: 100px;">Phone Number</th>
            <th style="width: 100px;">Address</th>
            <th style="width: 100px;">PDF URL</th>
            <th style="width: 100px;">Comments</th>
            <th style="width: 100px;">Operations</th>
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
        echo '<td>
        <form action="" method="post">
            <input type="hidden" name="idsl" value="' . esc_html($candidate['id']) . '">
            <input title="' . esc_html($candidate['id']) . '" type="submit" name="deleteShortlisted" value="Delete" style="background-color: red; outline: none; border: none; padding:4px 7px; border-radius: 5px; color: #fff; cursor: pointer;">
        </form>
    </td>';

    
        echo '</tr>';
    }

    // delete shortlisted candidate 
    if (isset($_POST['deleteShortlisted'])) {
        global $wpdb;
        $id_to_deletee = isset($_POST['idsl']) ? intval($_POST['idsl']) : 0;
    
        
        if ($id_to_deletee > 0) {
            $delete_result = $wpdb->delete(
                'wp_shortlisted_candidates',
                array('id' => $id_to_deletee),
                array('%d') // ID is an integer
            );
            if ($delete_result !== false) {
                echo "Record deleted successfully.";
                header("location: #"); 
                exit; // Make sure to exit after redirecting
            }
            else {
                echo "Error deleting record.";
            }
        }
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

    $table_name = 'wp_resumes';

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
                    <th style="width: 15px;">ID</th>
                    <th style="width: 100px;">Full Name</th>
                    <th style="width: 140px;">Email</th>
                    <th style="width: 100px;">Degree</th>
                    <th style="width: 61px;">University</th>
                    <th style="width: 70px;">Job Title</th>
                    <th style="width: 60px;">Company</th>
                    <th style="width: 100px;">Employment History</th>
                    <th style="width: 100px;">Skills</th>
                    <th style="width: 100px;">LinkedIn</th>
                    <th style="width: 100px;">Phone Number</th>
                    <th style="width: 100px;">Address</th>
                    <th style="width: 100px;">CV Name</th>
                    <th style="width: 200px;">Action</th>
                    <th style="width: 100px;" >Operations</th>
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
        echo '<input type="text" name="commentss" placeholder="Add comment" required> <br>';
        echo '<button type="submit" name="insert" title="' . esc_html($resume['id']) . '">Shortlist</button>';
        echo '</form>';
        // echo '<button onclick="forwardToPM(' . $resume['id'] . ')">Forward to PM</button>';
        // echo '</td>';

        echo '<td>
                <form action="" method="post">
                    <input type="hidden" name="id" value="' . esc_html($resume['id']) . '">
                    <input type="submit" name="delete" value="Delete" style="background-color: red; outline: none; border: none; padding:4px 7px; border-radius: 5px; color: #fff; cursor: pointer;">
                </form>
            </td>';


        echo '</tr>';
    }

    // delete record from 
    if (isset($_POST['delete'])) {
        global $wpdb;
    
        // Get the ID from the hidden input field
        $id_to_delete = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
        // Check if the ID is valid
        if ($id_to_delete > 0) {
            // Delete the record from the wp_shortlisted_candidates table
            $delete_result = $wpdb->delete(
                'wp_resumes',
                array('id' => $id_to_delete),
                array('%d') // ID is an integer
            );
    
            // Check if the record is successfully deleted
            if ($delete_result !== false) {
                echo "Record deleted successfully.";
                header("location: #"); // Redirect to the current page
                exit; // Make sure to exit after redirecting
            }
            else {
                echo "Error deleting record.";
            }
        }
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
        $resume_data = $wpdb->get_row("SELECT * FROM wp_resumes WHERE id = $resume_id", ARRAY_A);
        $comment = $_POST['commentss'];
        $insert_id = $_POST['resume_id'];
       

        // Check if resume data is retrieved successfully
        if ($resume_data) {
            // Insert the data into wp_shortlisted_candidates table
            $insert_result = $wpdb->insert(
                'wp_shortlisted_candidates',
                array(
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
                    'comments' => $comment
                )
            );

            if ($insert_result !== false) {
                echo "Record inserted successfully.";
            } else {
                echo "Error inserting record." . $wpdb->last_error;
            }
        } else {
            echo "Error retrieving resume data.";
        }
    }
}

// if(isset($_POST['delete'])){
//     $id = ;

// }
?>

        </table>
    </div>
<?php
 }



// AJAX handler
add_action('wp_ajax_get_filtered_data', 'get_filtered_data');
add_action('wp_ajax_nopriv_get_filtered_data', 'get_filtered_data');

function get_filtered_data() {
    global $wpdb;

    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    $sql = $wpdb->prepare("SELECT * FROM wp_resumes WHERE skills LIKE %s", '%' . $search_query . '%');
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
    $table_name = 'wp_resumes';

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


    ?>
    <div class="wrap">
        <h1>HR Dashboard</h1>

        <div class="hr-dashboard-container" style="display: flex; justify-content: space-around;">

            <!-- HR Dashboard Section 1 -->
            <div class="hr-dashboard-section">
            <a href="?page=hr_management_page&action=review_cvs">
    <img src="<?php echo plugins_url('/assets/img/profile.png', __FILE__); ?>" width="70" height="52" alt="Icon 1">
</a>
                <h2><?php echo esc_html($user_display_name); ?></h2>
                <p>Helping Hand for HRs</p>
            </div>
            <div class="hr-dashboard-section">
            <a href="?page=hr_management_page&action=review_cvs">
                <img src="<?php echo plugins_url('/assets/img/documents.png', __FILE__); ?>" width="70" height="52" alt="Icon 1">
            </a>
                <h2>Received CVs</h2>
                <h2><?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'resumes';
                
                $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name");
                $total_rows = $wpdb->get_var($query);
                
                echo $total_rows;
                ?></h2>
            </div>
            <!-- <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Forwarded Candidates</h2>
                <p>56</p>
            </div> -->
            <div class="hr-dashboard-section">
            <a href="?page=hr_management_page&action=review_cvs">
                <img src="<?php echo plugins_url('/assets/img/documents.png', __FILE__); ?>" width="70" height="52" alt="Icon 1">
            </a>
                <h2>Shortlisted Candidates</h2>
                <h2><?php
                global $wpdb;
                $table_sl = $wpdb->prefix . 'shortlisted_candidates';
                
                $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_sl");
                $total_rows = $wpdb->get_var($query);
                
                echo $total_rows;
                ?></h2>
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
?>
<?php
function pdfgenreration_management_page() {
    ?>
    <div class="wrap" style="max-width: 600px; margin: 0 auto;">
        <h1>PDF Generate</h1>
        
        <form method="post" action="">
            <div style="margin-bottom: 20px;">
                <label for="hr_name" style="display: block; margin-bottom: 5px;">Enter HR Name:</label>
                <input type="text" name="hr_name" id="hr_name" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="select_data" style="display: block; margin-bottom: 5px;">Select Data:</label>
                <input type="date" name="reviewed_cvs" id="reviewed_cvs" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="reviewed_cvs" style="display: block; margin-bottom: 5px;">Reviewed CVs:</label>
                <input type="number" name="reviewed_cvs" id="reviewed_cvs" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="shortlisted_cvs" style="display: block; margin-bottom: 5px;">Shortlisted CVs:</label>
                <input type="number" name="shortlisted_cvs" id="shortlisted_cvs" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="hired_interns" style="display: block; margin-bottom: 5px;">Hired Interns:</label>
                <input type="number" name="hired_interns" id="hired_interns" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="hired_employees" style="display: block; margin-bottom: 5px;">Hired Employees:</label>
                <input type="number" name="hired_employees" id="hired_employees" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label for="workforce_required" style="display: block; margin-bottom: 5px;">Workforce Required:</label>
                <input type="number" name="workforce_required" id="workforce_required" required style="padding: 8px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
            </div>

            <button type="submit" name="generate_pdf" style="padding: 10px 20px; border-radius: 5px; border: none; background-color: #007bff; color: #fff; cursor: pointer; width: 100%;">Generate PDF</button>
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
    <button onclick="printTable()" style="padding: 10px 20px; border-radius: 5px; border: none; background-color: #007bff; color: #fff; cursor: pointer;">Print Table</button>
    <?php
}
add_shortcode('pdf_generation', 'pdfgenreration_management_page');
?>

<?php
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
            <label><input type="checkbox" name="skills[]" value="Plugin Development"> Plugin Development</label>
            <label><input type="checkbox" name="skills[]" value="Theme Development"> PSD to Email</label><br>
            <label><input type="checkbox" name="skills[]" value="Plugin Development">PSD to Wordpress</label>
            <label><input type="checkbox" name="skills[]" value="Plugin Development">Python</label>
            <label><input type="checkbox" name="skills[]" value="Plugin Development">Human Resources Skills</label>
            <label><input type="checkbox" name="skills[]" value="Plugin Development">Java</label><br>
            <label><input type="checkbox" name="skills[]" value="PSD to HTML&CSS"> PSD to HTML&CSS</label> <br>
            <label>Other :</label><input type="text" name="skills[]" value="" placeholder="java,c++"><br>
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
        $sql = "INSERT INTO wp_resumes (full_name, email, degree, university, job_title, company, employment_history, skills, linkedin, phno, address, pdf_url) 
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


// database tables
// shortlist_candidate
// Shortlist_candidates table
function create_table_for_shortlisted_candidates() {
    global $wpdb;

    // Define the table name with the WordPress prefix
    $table_name = $wpdb->prefix . 'shortlisted_candidates'; // Corrected table name

    $sql = "CREATE TABLE $table_name (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `employment_history` varchar(255) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `phno` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `pdf_url` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
    )";

    $wpdb->query($sql);
}

register_activation_hook(__FILE__, 'create_table_for_shortlisted_candidates');

// Resume table
function create_table_for_resume() {
    global $wpdb;

    // Define the table name with the WordPress prefix
    $table_name = $wpdb->prefix . 'resumes';

    $sql = "CREATE TABLE $table_name (
         `id` int(55) NOT NULL AUTO_INCREMENT,
        `full_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `email` varchar(255) NOT NULL,
        `degree` varchar(255) NOT NULL,
        `university` varchar(255) NOT NULL,
        `job_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `company` varchar(255) NOT NULL,
        `employment_history` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `skills` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `linkedin` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `phno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        `pdf_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        PRIMARY KEY (`id`)
    )";
    
    

    $wpdb->query($sql);
}

register_activation_hook(__FILE__, 'create_table_for_resume');

function custom_login_redirect( $redirect_to, $request, $user ) {
    // Check if $user is a WP_User object
    if ( is_a( $user, 'WP_User' ) ) {
        // Get the current user's role
        $user_role = $user->roles[0];
 
        // Set the URL to redirect users to based on their role
        if ( $user_role == 'subscriber' ) {
            $redirect_to = '/testing/';
        } 
    } else {
        // Handle WP_Error
        error_log( 'Custom login redirect error: User is not a valid WP_User object' );
        // You can redirect the user to a default location or display an error message
        // $redirect_to = '/default-redirect-location/';
    }

    return $redirect_to;
}
add_filter( 'login_redirect', 'custom_login_redirect', 10, 3 );

?>







<!-- dubara use kiya hai hrdashboard code for shortcode use -->
<!-- dubara use kiya hai hrdashboard code for shortcode use -->

<?php
function follow_us_link()
{
    $current_user = wp_get_current_user();
    $user_display_name = $current_user->display_name;
    ob_start();
    ?>
    <div class="wrap">
        <h1 style="margin: 0 0 50px; font-size: 20px;">HR Dashboard</h1>

        <div class="hr-dashboard-container" style="gap: 137px;display: flex; justify-content: space-around;">

            <!-- HR Dashboard Section 1 -->
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs">
                    <img src="<?php echo plugins_url('/assets/img/profile.png', __FILE__); ?>" width="70" height="52" alt="Icon 1">
                </a>
                <h2  style="margin: 0 0 50px; font-size: 20px;"><?php echo esc_html($user_display_name); ?></h2>
                <p>Helping Hand for HRs</p>
            </div>
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs">
                    <img src="<?php echo plugins_url('/assets/img/documents.png', __FILE__); ?>" width="70" height="52" alt="Icon 1">
                </a>
                <h2  style="margin: 0 0 50px; font-size: 20px;">Received CVs</h2>
                <h2><?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'resumes';
                    
                    $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name");
                    $total_rows = $wpdb->get_var($query);
                    
                    echo $total_rows;
                    ?></h2>
            </div>
            <!-- <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs"><img src="http://localhost:10016/wp-content/uploads/2024/02/documents.png" width="70" height="52" alt="Icon 1"></a>
                <h2>Forwarded Candidates</h2>
                <p>56</p>
            </div> -->
            <div class="hr-dashboard-section">
                <a href="?page=hr_management_page&action=review_cvs">
                    <img src="<?php echo plugins_url('/assets/img/documents.png', __FILE__); ?>" width="70" height="52" alt="Icon 1">
                </a>
                <h2 style="margin: 0 0 50px; font-size: 20px;">Shortlisted Candidates</h2>
                <h2><?php
                    global $wpdb;
                    $table_sl = $wpdb->prefix . 'shortlisted_candidates';
                    
                    $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_sl");
                    $total_rows = $wpdb->get_var($query);
                    
                    echo $total_rows;
                    ?></h2>
            </div>

            <!-- Add more dashboard sections as needed -->

        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('follow_us', 'follow_us_link');
?>



<?php
function received_cvv_page()
{
    global $wpdb;

    $table_name = 'wp_resumes';

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

    <style>
        /* Style for the form */
        #search-form {
            margin-bottom: 20px;
        }

        #search-form input[type="text"] {
            width: 200px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        #search-form input[type="submit"] {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        /* Style for the table */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Style for buttons */
        button {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .table-wrap{
            max-width: 1170px;
            width: 100%;
            overflow-x: auto;
            margin: 0 auto;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>

    <div class='table-wrap'>
        <h1>Received CVs</h1>

        <!-- Search Form -->
        <form id="search-form" method="GET">
            <label for="search">Search by Skills:</label>
            <input type="text" name="search" id="search" value="<?php echo esc_attr($search_query); ?>" />
            <input type="submit" value="Search" />
        </form>

        <table>
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
                    <th>Operations</th>
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
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="resume_id" value="' . esc_html($resume['id']) . '">';
                echo '<input type="text" name="commentss" placeholder="Add comment" required>';
                echo '<button type="submit" name="insert" title="' . esc_html($resume['id']) . '">Shortlist</button>';
                echo '</form>';
                // echo '<button onclick="forwardToPM(' . $resume['id'] . ')">Forward to PM</button>';
                // echo '</td>';

                echo '<td>
                        <form action="" method="post">
                            <input type="hidden" name="id" value="' . esc_html($resume['id']) . '">
                            <input type="submit" name="delete" value="Delete">
                        </form>
                    </td>';

                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}
add_shortcode('received_cvv', 'received_cvv_page');
?>

<!-- shortlist_candidate code will use again -->
<!-- shortlist_candidate code will use again -->
<!-- shortlist_candidate code will use again -->

<?php
function display_shortlisted_candidates()
{     
    global $wpdb;

    $table_name = 'wp_shortlisted_candidates';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo "<p>Table '$table_name' not found!</p>";
        return;
    }

    // Retrieve data from the table
    $shortlisted_candidates_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A); 
    
   
    ob_start();
    ?>
    <div class="table-wrap" style="max-width: 1170px; width: 100%; overflow-x: auto; margin: 0 auto; padding: 10px; margin-bottom: 20px;">
        <h1 style="text-align: center;">Shortlisted Candidates</h1>

        <table style="width: 100%; border-collapse: collapse;">
            <!-- Table Header -->
            <thead>
                <tr >
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Full Name</th>
                    <th style="padding: 10px;">Email</th>
                    <th style="padding: 10px;">Degree</th>
                    <th style="padding: 10px;">University</th>
                    <th style="padding: 10px;">Job Title</th>
                    <th style="padding: 10px;">Company</th>
                    <th style="padding: 10px;">Employment History</th>
                    <th style="padding: 10px;">Skills</th>
                    <th style="padding: 10px;">LinkedIn</th>
                    <th style="padding: 10px;">Phone Number</th>
                    <th style="padding: 10px;">Address</th>
                    <th style="padding: 10px;">PDF URL</th>
                    <th style="padding: 10px;">Comments</th>
                    <th style="padding: 10px;">Operations</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($shortlisted_candidates_data as $candidate) {
                echo '<tr>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['id']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['full_name']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['email']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['degree']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['university']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['job_title']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['company']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['employment_history']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['skills']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['linkedin']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['phno']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['address']) . '</td>';
                
                // Button to download PDF
                echo '<td style="padding: 10px; border: 1px solid #ddd;"><form method="post"><button type="submit" name="download_pdf" value="' . esc_attr($candidate['pdf_url']) . '" style="padding: 5px 10px; border-radius: 5px; border: none; background-color: #007bff; color: #fff; cursor: pointer;">Download PDF</button></form></td>';
                
                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html($candidate['comments']) . '</td>';
                echo '<td style="padding: 10px; border: 1px solid #ddd;">
                    <form action="" method="post">
                        <input type="hidden" name="idsl" value="' . esc_html($candidate['id']) . '">
                        <input title="' . esc_html($candidate['id']) . '" type="submit" name="deleteShortlisted" value="Delete" style="padding: 5px 10px; border-radius: 5px; border: none; background-color: red; color: #fff; cursor: pointer;">
                    </form>
                </td>';

                echo '</tr>';
            }

            // Delete shortlisted candidate 
            if (isset($_POST['deleteShortlisted'])) {
                global $wpdb;
                $id_to_deletee = isset($_POST['idsl']) ? intval($_POST['idsl']) : 0;
            
                
                if ($id_to_deletee > 0) {
                    $delete_result = $wpdb->delete(
                        'wp_shortlisted_candidates',
                        array('id' => $id_to_deletee),
                        array('%d') // ID is an integer
                    );
                    if ($delete_result !== false) {
                        echo "Record deleted successfully.";
                        header("location: #"); 
                        exit; // Make sure to exit after redirecting
                    }
                    else {
                        echo "Error deleting record.";
                    }
                }
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
return ob_get_clean();
}

add_shortcode('shortlisted_candidate', 'display_shortlisted_candidates');
?>


<!-- styling -->
<style>

/* .wrap {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 5px;
} */

h4 {
    text-align: center;
    color: #333;
}

form {
    margin-top: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="url"],
input[type="tel"],
textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

textarea {
    resize: vertical;
}

input[type="checkbox"] {
    margin-right: 5px;
}

input[type="file"] {
    margin-top: 5px;
}

input[type="submit"] {
    background-color: #4caf50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #45a049;
}

</style>

