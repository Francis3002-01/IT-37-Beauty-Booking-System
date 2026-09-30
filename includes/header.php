<?php
/**
 * GLOBAL HEADER TEMPLATE
 * ------------------------------------------------------------------
 * Purpose: Contains the opening HTML tags, document metadata, and CSS links.
 * Why include this? 
 * 1. Ensures consistent site-wide styling and branding.
 * 2. If we change Bootstrap versions or stylesheet paths, we only edit this 1 file 
 *    instead of updating every single PHP page.
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'beautyReserve.'; ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Stylesheet (Updated to match your file name) -->
    <link href="css/appointments.css" rel="stylesheet">
</head>
<body>

<!--THE BODY TAG IS OPENED HERE AND CLOSED IN Includes/footer.php-->