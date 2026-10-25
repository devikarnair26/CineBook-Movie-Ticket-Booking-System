
<?php
session_start();
require_once "db.php";

$message = "";
$messageType = "error";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
    } else {
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        if (!$check) {
            die("Database query error.");
        }

        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This email is already registered. Please log in.";
            $check->close();
        } else {
            $check->close();

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $role = "customer";

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, ?)"
            );

            if (!$stmt) {
                die("Database query error.");
            }

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashedPassword,
                $role
            );

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: login.php?registered=1");
                exit;
            } else {
                $message = "Registration failed. Please try again.";
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | CineBook</title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            background: #f8f7ff;
            color: #37305e;
        }

        .register-wrapper {
            width: 100%;
            max-width: 1000px;
            min-height: 650px;
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            background: #ffffff;
            border: 1px solid #e8e0ff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(75, 48, 130, 0.12);
        }

        .register-banner {
            padding: 42px 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #ffffff;
            background:
                linear-gradient(160deg, rgba(66, 42, 125, 0.85), rgba(120, 85, 217, 0.91)),
                url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1000&q=85');
            background-size: cover;
            background-position: center;
        }

        .brand {
            font-size: 29px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .brand span {
            color: #dfd2ff;
        }

        .banner-content {
            margin: 35px 0;
        }

        .banner-label {
            display: inline-block;
            padding: 7px 12px;
            margin-bottom: 18px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .banner-content h1 {
            margin: 0 0 16px;
            font-size: clamp(29px, 4vw, 40px);
            line-height: 1.3;
            color: #ffffff;
        }

        .banner-content p {
            margin: 0;
            color: #eee8ff;
            font-size: 13px;
            line-height: 1.9;
        }

        .banner-footer {
            color: #e7ddff;
            font-size: 12px;
        }

        .register-panel {
            padding: 38px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .mobile-brand {
            display: none;
        }

        .register-panel h2 {
            margin: 0 0 8px;
            color: #37305e;
            font-size: 28px;
            font-weight: 700;
        }

        .register-description {
            margin: 0 0 22px;
            color: #77718e;
            font-size: 13px;
            line-height: 1.8;
        }

        .register-message {
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fff1f2;
            color: #b42318;
            font-size: 12px;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .register-form {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .register-form label {
            margin-top: 7px;
            color: #37305e;
            font-size: 12px;
            font-weight: 600;
        }

        .register-form input {
            width: 100%;
            min-width: 0;
            padding: 11px 13px;
            border: 1px solid #e1d8f7;
            border-radius: 9px;
            outline: none;
            background: #fcfbff;
            color: #37305e;
            font-family: inherit;
            font-size: 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .register-form input:focus {
            border-color: #7855d9;
            box-shadow: 0 0 0 3px rgba(120, 85, 217, 0.12);
        }

        .register-form button {
            width: 100%;
            margin-top: 16px;
            padding: 13px;
            border: none;
            border-radius: 9px;
            background: #7855d9;
            color: #ffffff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .register-form button:hover {
            background: #6240bd;
            transform: translateY(-1px);
        }

        .login-text {
            margin: 20px 0 0;
            text-align: center;
            color: #77718e;
            font-size: 12px;
        }

        .login-text a,
        .home-link {
            color: #7855d9;
            font-weight: 600;
            text-decoration: none;
        }

        .login-text a:hover,
        .home-link:hover {
            color: #4c1d95;
            text-decoration: underline;
        }

        .home-link {
            align-self: center;
            margin-top: 18px;
            font-size: 12px;
        }

        @media (max-width: 720px) {
            body {
                padding: 16px;
            }

            .register-wrapper {
                max-width: 470px;
                min-height: auto;
                grid-template-columns: 1fr;
                border-radius: 20px;
            }

            .register-banner {
                display: none;
            }

            .register-panel {
                padding: 32px 25px;
            }

            .mobile-brand {
                display: block;
                margin-bottom: 23px;
                color: #7855d9;
                font-size: 27px;
                font-weight: 800;
            }

            .mobile-brand span {
                color: #a78bfa;
            }

            .register-panel h2 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

    <main class="register-wrapper">

        <section class="register-banner">
            <div class="brand">Cine<span>Book</span></div>

            <div class="banner-content">
                <span class="banner-label">Join CineBook</span>
                <h1>Your next favourite movie awaits.</h1>
                <p>
                    Create your account, discover exciting movies,
                    select your seats, and keep track of your bookings
                    in one place.
                </p>
            </div>

            <div class="banner-footer">
                Discover. Book. Enjoy.
            </div>
        </section>

        <section class="register-panel">

            <div class="mobile-brand">Cine<span>Book</span></div>

            <h2>Create Account</h2>

            <p class="register-description">
                Join CineBook and make your next movie booking easy.
            </p>

            <?php if ($message !== ""): ?>
                <div class="register-message" role="alert">
                    <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form class="register-form" method="POST" action="register.php">

                <label for="name">Full name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    autocomplete="name"
                    value="<?= htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    required
                >

                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    required
                >

                <label for="phone">Phone number</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    autocomplete="tel"
                    value="<?= htmlspecialchars($_POST["phone"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    required
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="At least 8 characters"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <button type="submit">Create My Account</button>

            </form>

            <p class="login-text">
                Already have an account?
                <a href="login.php">Sign in</a>
            </p>

            <a href="index.php" class="home-link">
                &larr; Back to Home
            </a>

        </section>

    </main>

</body>
</html>
