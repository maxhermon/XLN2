<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/InputEmailForPasswordReset.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>
<body>
    <header>
        <a href="Homepage.php"><img class="logo" src="../xlnLogo.png" alt="XLN Logo"></a>
        <a href="../html/Contact.html"><i class="fa-solid fa-envelope"></i> Contact</a>
    </header>
    <main>
        <form id="resetPasswordForm">
            <label for="email"><b>Email address</b></label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send email</button>
            <div id="message" style="display:none;"></div>
        </form>
    </main>
    <footer>
        <p>&copy; <span id="year"></span> XLN</p>
    </footer>
    <script>
        document.getElementById("year").innerHTML = new Date().getFullYear();

        $(document).ready(function() {
            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                var email = $('#email').val();
                $.ajax({
                    type: 'POST',
                    url: 'SendEmail.php',
                    data: { email: email },
                    success: function(response) {
                        $('#message').html(response).show();
                    },
                    error: function() {
                        $('#message').html('An error occurred. Please try again.').show();
                    }
                });
            });
        });
    </script>
</body>
</html>