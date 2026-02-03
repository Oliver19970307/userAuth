<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Auth Test SPA</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .tips {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        .tips.show { opacity: 1; }
        .tips.success { color: #155724; background-color: #d4edda; }
        .tips.error { color: #721c24; background-color: #f8d7da; }
        form { margin-bottom: 20px; }
        input { padding: 5px; margin: 5px 0; width: 200px; }
        button { padding: 5px 10px; }
    </style>
</head>
<body>

<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<h2>Register</h2>
<div id="registerTips" class="tips"></div>
<form id="registerForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>

<h2>Login</h2>
<div id="loginTips" class="tips"></div>
<form id="loginForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<script>
    /**
     * Show tips with fade in/out
     * @param {HTMLElement} el - The element to show the tip
     * @param {string} message - Message text
     * @param {'success'|'error'} type - Tip type
     */
    function showTips(el, message, type) {
        el.className = 'tips ' + type + ' show';
        el.textContent = message;

        // Fade out after 3 seconds
        setTimeout(() => {
            el.classList.remove('show');
        }, 5000);
    }

    /**
     * Send form via fetch (AJAX)
     * @param {string} url - Endpoint URL
     * @param {FormData} formData - Form data
     * @param {HTMLElement} tipsEl - Tips element
     */
    async function sendForm(url, formData, tipsEl) {
        try {
            const res = await fetch(url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!res.ok) throw new Error(`Network or server error (${res.status})`);

            const data = await res.json();

            if (data.status === 'success') {
                showTips(tipsEl, data.message || 'Operation succeeded', 'success');
            } else {
                showTips(tipsEl, data.message || 'Operation failed', 'error');
            }

            // Optional: refresh CSRF token if server returns a new one
            if (data.csrf_token) {
                formData.set('csrf_token', data.csrf_token);
                const hiddenInputs = document.querySelectorAll('input[name="csrf_token"]');
                hiddenInputs.forEach(input => input.value = data.csrf_token);
            }

        } catch (err) {
            showTips(tipsEl, err.message, 'error');
            console.error(err);
        }
    }

    /**
     * Initialize form handlers
     */
    function initForm(formId, tipsId, url) {
        const form = document.getElementById(formId);
        const tipsEl = document.getElementById(tipsId);

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            sendForm(url, formData, tipsEl);
        });
    }

    // Initialize both forms
    initForm('registerForm', 'registerTips', 'register.php');
    initForm('loginForm', 'loginTips', 'login.php');
</script>

</body>
</html>