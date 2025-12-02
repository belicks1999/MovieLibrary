<?php
// Simple router: handle form submission in the same file

// Path to JSON storage
$storageFile = __DIR__ . '/submissions.json';

// Load email helper
require_once __DIR__ . '/includes/email_helper.php';

$formErrors = [];
$formSuccess = false;

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $comments  = trim($_POST['comments'] ?? '');

    // Backend validation
    if ($firstName === '') {
        $formErrors['first_name'] = 'First name is required';
    }
    if ($lastName === '') {
        $formErrors['last_name'] = 'Last name is required';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Valid email is required';
    }
    if ($comments === '') {
        $formErrors['comments'] = 'Comments are required';
    }

    if (empty($formErrors)) {
        // Prepare data
        $entry = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'comments'   => $comments,
            'submitted_at' => date('c'),
        ];

        // Load existing submissions
        $existing = [];
        if (file_exists($storageFile)) {
            $json = file_get_contents($storageFile);
            $existing = json_decode($json, true);
            if (!is_array($existing)) {
                $existing = [];
            }
        }
        $existing[] = $entry;
        file_put_contents($storageFile, json_encode($existing, JSON_PRETTY_PRINT));

        // Send emails using email helper
        $userSubject = 'Thank you for contacting Movie Library';
        $userMessage = "Hi {$firstName},\n\nThank you for reaching out to Movie Library. We have received your message and will get back to you soon.\n\nYour message:\n{$comments}\n\nRegards,\nMovie Library Team";

        // Send confirmation email to user
        sendEmail($email, $userSubject, $userMessage);

        // Send notification emails to admins
        $adminSubject = 'New contact form submission - Movie Library';
        $adminBody = "New contact submission:\n\n"
            . "First Name: {$firstName}\n"
            . "Last Name: {$lastName}\n"
            . "Email: {$email}\n"
            . "Phone: {$phone}\n"
            . "Comments:\n{$comments}\n\n"
            . "Submitted at: " . $entry['submitted_at'] . "\n";

        $adminRecipients = [
            'dumidu.kodithuwakku@ebeyonds.com',
            'prabhath.senadheera@ebeyonds.com',
            
        ];
        foreach ($adminRecipients as $recipient) {
            sendEmail($recipient, $adminSubject, $adminBody, $email);
        }

        $formSuccess = true;
        
        // Clear form values after successful submission
        $firstName = '';
        $lastName = '';
        $email = '';
        $phone = '';
        $comments = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Movie Library</title>
    <link rel="stylesheet" href="assets/styles.css" />
</head>
<body>
    <div class="page-wrapper">
        <header class="site-header">
            <div class="header-inner">
                <a href="index.php" class="logo" aria-label="Home - Movie Library">
                    <img src="assets/Logos.png" alt="Movie Library Logo" width="150" height="50">
                </a>
                <nav class="main-nav" aria-label="Main Navigation">
                    <button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu">
                        <span class="nav-toggle-bar"></span>
                        <span class="nav-toggle-bar"></span>
                        <span class="nav-toggle-bar"></span>
                        <span class="visually-hidden">Toggle navigation</span>
                    </button>
                    <ul id="primary-menu" class="nav-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#intro">About</a></li>
                        <li><a href="#favorites">Favorites</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main>
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero-overlay"></div>
                
            </section>

            <section id="intro" class="intro">
            <div class="hero-content">
                    <h1 id="hero-title">Movie Library</h1>
                    <p>Discover, search and collect your favorite movies and TV shows in one cinematic experience.</p>
                </div>
            </section>

            <section id="favorites" class="favorites" aria-labelledby="favorites-title">
                <div class="favorites-inner">
                    <div class="section-header">
                        <h2 id="favorites-title">Collect your favourites</h2>
                        <div class="search-row">
                            <label for="search-input" class="visually-hidden">Search for movies</label>
                            <input
                                type="search"
                                id="search-input"
                                class="search-input"
                                placeholder="Search movies or TV shows..."
                                autocomplete="off"
                            />
                        </div>
                    </div>

                    <div class="favorites-grid" aria-live="polite"></div>
                    <div class="load-more-wrapper" style="display: none;">
                        <button id="load-more-btn" class="btn primary-btn">Load More</button>
                    </div>

                    <div id="api-results" class="api-results" aria-label="Search results"></div>
                </div>
            </section>

            <section id="contact" class="contact" aria-labelledby="contact-title">
                <div class="contact-inner">
                    <div class="contact-form-wrapper">
                        <h2 id="contact-title">How to reach us</h2>
                        <p>Leave us a message and we will get back to you shortly.</p>

                        <?php if ($formSuccess): ?>
                            <div class="form-message success" role="status">
                                Thank you for your message. We have received your submission.
                            </div>
                        <?php elseif (!empty($formErrors)): ?>
                            <div class="form-message error" role="alert">
                                Please fix the errors highlighted below and try again.
                            </div>
                        <?php endif; ?>

                        <form id="contact-form" class="contact-form" method="post" novalidate>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="first_name">First Name<span aria-hidden="true">*</span></label>
                                    <input
                                        type="text"
                                        id="first_name"
                                        name="first_name"
                                        value="<?php echo htmlspecialchars($firstName ?? ''); ?>"
                                        required
                                    />
                                    <?php if (!empty($formErrors['first_name'])): ?>
                                        <p class="field-error"><?php echo htmlspecialchars($formErrors['first_name']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-field">
                                    <label for="last_name">Last Name<span aria-hidden="true">*</span></label>
                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        value="<?php echo htmlspecialchars($lastName ?? ''); ?>"
                                        required
                                    />
                                    <?php if (!empty($formErrors['last_name'])): ?>
                                        <p class="field-error"><?php echo htmlspecialchars($formErrors['last_name']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="email">Email<span aria-hidden="true">*</span></label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                        required
                                    />
                                    <?php if (!empty($formErrors['email'])): ?>
                                        <p class="field-error"><?php echo htmlspecialchars($formErrors['email']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="form-field full-width">
                                    <label for="phone">Phone Number</label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                    />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-field full-width">
                                    <label for="comments">Comments<span aria-hidden="true">*</span></label>
                                    <textarea
                                        id="comments"
                                        name="comments"
                                        rows="4"
                                        required
                                    ><?php echo htmlspecialchars($comments ?? ''); ?></textarea>
                                    <?php if (!empty($formErrors['comments'])): ?>
                                        <p class="field-error"><?php echo htmlspecialchars($formErrors['comments']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row form-meta-row">
                                <p class="form-required-note">*required fields</p>
                            </div>
                            <div class="form-row policy-row">
                                <label class="policy-field">
                                    <input
                                        type="checkbox"
                                        id="policy"
                                        name="policy"
                                    />
                                    <span>I agree to the <a href="#" id="terms-link">Terms &amp; Conditions</a></span>
                                </label>
                            </div>
                            <div class="form-row form-actions">
                                <button type="submit" class="btn primary-btn">Submit</button>
                            </div>
                        </form>
                    </div>

                    <div class="contact-map-wrapper" aria-label="Map to our location">
                    <iframe width="100%" height="100%" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d114714.99548975375!2d79.78799343556913!3d6.844815414372193!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae25069caa2f53b%3A0xe7eae3a8b1f1214d!2seBEYONDS%20eBusiness%20%26%20Digital%20Solutions!5e1!3m2!1sen!2slk!4v1764673071533!5m2!1sen!2slk" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-top">
                    <div class="footer-company">
                        <h3>IT Group</h3>
                        <address>
                            C. Salvador de Madariaga, 1<br>
                            28027 Madrid<br>
                            Spain
                        </address>
                    </div>
                    <div class="footer-social">
                        <span>Follow us on</span>
                        <div class="social-icons">
                            <a href="#" aria-label="Twitter">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            <a href="#" aria-label="YouTube">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="footer-divider"></div>
                <div class="footer-bottom">
                    <p class="footer-copyright">Copyright © <?php echo date('Y'); ?> IT Hote Is. All rights reserved.</p>
                    <p class="footer-credits">Photos by Felix Mooneeram & Serge Kutuzov, on <a href="https://unsplash.com" target="_blank" rel="noopener">Unsplash</a></p>
                </div>
            </div>
        </footer>

        <div class="modal-backdrop" id="terms-modal" aria-hidden="true">
            <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="terms-title">
                <button class="modal-close" type="button" aria-label="Close terms">&times;</button>
                <h2 id="terms-title">Terms &amp; Conditions</h2>
                <div class="modal-body">
                    <p>This is a dummy Terms &amp; Conditions document for assignment purposes.</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur sit amet magna vel massa malesuada aliquet. Integer ac urna vel augue dictum imperdiet.</p>
                    <p>By using this demo site you agree that no real services are being provided and no personal data will be processed beyond this exercise.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/app.js"></script>
</body>
</html>


