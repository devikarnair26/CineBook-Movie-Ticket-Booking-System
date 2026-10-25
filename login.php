
<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $sql = "SELECT user_id, name, email, password, role
            FROM users
            WHERE email = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database query error.");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["customer_id"] = $user["user_id"];
            $_SESSION["customer_name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            $stmt->close();

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | CineBook</title>

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

        .login-wrapper {
            width: 100%;
            max-width: 960px;
            min-height: 570px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
            border: 1px solid #e8e0ff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(75, 48, 130, 0.12);
        }

        .login-banner {
            padding: 48px 38px;
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
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .brand span {
            color: #dfd2ff;
        }

        .banner-content {
            margin: 45px 0;
        }

        .banner-label {
            display: inline-block;
            padding: 7px 13px;
            margin-bottom: 18px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .banner-content h1 {
            margin: 0 0 16px;
            font-size: clamp(30px, 4vw, 43px);
            line-height: 1.25;
            color: #ffffff;
        }

        .banner-content p {
            max-width: 340px;
            margin: 0;
            color: #eee8ff;
            font-size: 14px;
            line-height: 1.9;
        }

        .banner-footer {
            color: #e7ddff;
            font-size: 12px;
        }

        .login-panel {
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .mobile-brand {
            display: none;
        }

        .login-panel h2 {
            margin: 0 0 8px;
            color: #37305e;
            font-size: 29px;
            font-weight: 700;
        }

        .login-description {
            margin: 0 0 28px;
            color: #77718e;
            font-size: 13px;
            line-height: 1.8;
        }

        .login-error {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fff1f2;
            color: #b42318;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .login-form label {
            margin-top: 8px;
            color: #37305e;
            font-size: 13px;
            font-weight: 600;
        }

        .login-form input {
            width: 100%;
            min-width: 0;
            padding: 13px 14px;
            border: 1px solid #e1d8f7;
            border-radius: 10px;
            outline: none;
            background: #fcfbff;
            color: #37305e;
            font: inherit;
            font-size: 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .login-form input:focus {
            border-color: #7855d9;
            box-shadow: 0 0 0 3px rgba(120, 85, 217, 0.12);
        }

        .login-form button {
            width: 100%;
            margin-top: 18px;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #7855d9;
            color: #ffffff;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }

        .login-form button:hover {
            background: #6240bd;
            transform: translateY(-1px);
        }

        .register-text {
            margin: 25px 0 0;
            text-align: center;
            color: #77718e;
            font-size: 13px;
        }

        .register-text a,
        .home-link {
            color: #7855d9;
            font-weight: 600;
            text-decoration: none;
        }

        .register-text a:hover,
        .home-link:hover {
            color: #4c1d95;
            text-decoration: underline;
        }

        .home-link {
            align-self: center;
            margin-top: 24px;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            body {
                padding: 16px;
            }

            .login-wrapper {
                max-width: 460px;
                min-height: auto;
                grid-template-columns: 1fr;
                border-radius: 20px;
            }

            .login-banner {
                display: none;
            }

            .login-panel {
                padding: 34px 26px;
            }

            .mobile-brand {
                display: block;
                margin-bottom: 28px;
                color: #7855d9;
                font-size: 27px;
                font-weight: 800;
            }

            .mobile-brand span {
                color: #a78bfa;
            }

            .login-panel h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

    <main class="login-wrapper">

        <section class="login-banner">
            <div class="brand">Cine<span>Book</span></div>

            <div class="banner-content">
                <span class="banner-label">Your cinema experience</span>
                <h1>Every movie.<br>Every moment.</h1>
                <p>
                    Discover your favourite movies, choose your seats,
                    and make your next cinema visit memorable.
                </p>
            </div>

            <div class="banner-footer">
                Your next movie experience starts here.
            </div>
        </section>

        <section class="login-panel">

            <div class="mobile-brand">Cine<span>Book</span></div>

            <h2>Welcome back!</h2>

            <p class="login-description">
                Sign in to explore movies and manage your bookings.
            </p>

            <?php if ($error !== ""): ?>
                <div class="login-error">
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="login.php">

                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    autocomplete="email"
                    required
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

                <button type="submit">Sign In to CineBook</button>

            </form>

            <p class="register-text">
                Don't have an account?
                <a href="register.php">Create an account</a>
            </p>

            <a href="index.php" class="home-link">
                &larr; Back to Home
            </a>

        </section>

    </main>

</body>
</html>
