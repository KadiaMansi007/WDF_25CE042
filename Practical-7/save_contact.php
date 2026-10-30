<?php
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $text = trim($_POST["message"] ?? "");
 
    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $text === "") {
        $message = "Please enter valid details in every field.";
    } else {
        $file = fopen("contacts.csv", "a");
        if ($file) {
            fputcsv($file, [$name, $email, $text, date("Y-m-d H:i:s")]);
            fclose($file);
            $message = "Your message was saved successfully.";
        } else {
            $message = "Unable to save the record. Check folder permissions.";
        }
    }
} else {
    $message = "Please submit the form first.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Form Result</title></head>
<body>
    <h1>Form Result</h1>
    <p><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
    <a href="contact.html">Back to form</a>
</body>
</html>