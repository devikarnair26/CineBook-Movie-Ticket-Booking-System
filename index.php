
<?php
session_start();
require_once "db.php";

$sql = "SELECT movie_id, title, genre, language, duration,
               release_date, description, poster_url
        FROM movies
        ORDER BY movie_id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load movies. Please check your database connection.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CineBook | Movie Tickets</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #7855d9;
            --primary-dark: #6240bd;
            --lavender: #eee9ff;
            --background: #f8f7ff;
            --text: #37305e;
            --muted: #77718e;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            min-height: 76px;
            padding: 15px 8%;
            background: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            box-shadow: 0 2px 12px #30206008;
        }

        .logo {
            font-size: 27px;
            font-weight: 700;
            color: var(--primary);
        }

        .logo span {
            color: var(--text);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links .login-link {
            background: var(--primary);
            color: white;
            padding: 9px 19px;
            border-radius: 8px;
        }

        .nav-links .login-link:hover {
            background: var(--primary-dark);
            color: white;
        }

        .hero {
            min-height: 350px;
            padding: 65px 8%;
            display: flex;
            align-items: center;
            background:
                linear-gradient(90deg,
                    rgba(248, 247, 255, 0.97) 0%,
                    rgba(248, 247, 255, 0.89) 43%,
                    rgba(248, 247, 255, 0.12) 100%),
                url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1800&q=85')
                center/cover no-repeat;
        }

        .hero-content {
            max-width: 520px;
        }

        .eyebrow {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .hero h1 {
            font-size: clamp(32px, 4vw, 46px);
            line-height: 1.2;
            margin-bottom: 16px;
            color: var(--text);
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero p {
            color: #625b7d;
            font-size: 15px;
            margin-bottom: 25px;
            max-width: 430px;
        }

        .primary-button {
            display: inline-block;
            background: var(--primary);
            color: var(--white);
            padding: 12px 25px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s, transform 0.2s;
        }

        .primary-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1240px;
            margin: auto;
            padding: 40px 25px 55px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .section-heading h2 {
            font-size: 25px;
            font-weight: 600;
        }

        .section-heading p {
            font-size: 13px;
            color: var(--muted);
            margin-top: 3px;
        }

        .view-all {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 20px;
        }

        .movie-card {
            min-width: 0;
            background: var(--white);
            border: 1px solid #eee9fb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 18px #39276a08;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .movie-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px #39276a14;
        }

        .poster {
            height: 235px;
            background: linear-gradient(135deg, #d9ccff, #9070df);
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 12px;
        }

        .poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .poster-placeholder {
            color: white;
            font-size: 19px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .movie-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .movie-info h3 {
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 9px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .genre {
            display: inline-block;
            align-self: flex-start;
            background: var(--lavender);
            color: var(--primary-dark);
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 11px;
            margin-bottom: 12px;
        }

        .movie-details {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .book-button {
            display: block;
            text-align: center;
            margin-top: auto;
            padding: 10px 8px;
            border-radius: 7px;
            background: #9478f0;
            color: white;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .book-button:hover {
            background: var(--primary-dark);
        }

        .empty {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 12px;
            color: var(--muted);
        }

        .empty h3 {
            color: var(--text);
            margin-bottom: 8px;
        }

        footer {
            background: #eee9ff;
            padding: 25px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        footer .logo {
            font-size: 22px;
        }

        footer p {
            color: #71678f;
            font-size: 12px;
        }

        .footer-links {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--text);
            font-size: 12px;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        @media (max-width: 1050px) {
            .movie-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .poster {
                height: 260px;
            }
        }

        @media (max-width: 650px) {
            .navbar {
                padding: 16px 5%;
            }

            .nav-links {
                gap: 14px;
            }

            .hero {
                min-height: 320px;
                padding: 45px 6%;
                background-position: center;
            }

            .hero h1 {
                font-size: 32px;
            }

            .container {
                padding: 30px 16px 40px;
            }

            .movie-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 13px;
            }

            .poster {
                height: 200px;
            }

            .movie-info {
                padding: 11px;
            }

            .movie-info h3 {
                font-size: 13px;
            }

            .section-heading h2 {
                font-size: 22px;
            }

            footer {
                padding: 24px 6%;
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 380px) {
            .movie-grid {
                grid-template-columns: 1fr;
            }

            .poster {
                height: 280px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="index.php">🎬 Cine<span>Book</span></a>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="#movies">Movies</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php" class="login-link">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <div class="hero-content">
        <div class="eyebrow">WELCOME TO CINEBOOK</div>

        <h1>Your Next Movie <span>Awaits!</span></h1>

        <p>
            Discover your favourite movies, choose your theatre,
            select your seats, and enjoy the big-screen experience.
        </p>

        <a href="#movies" class="primary-button">Browse Movies &nbsp; →</a>
    </div>
</section>

<main class="container" id="movies">

    <div class="section-heading">
        <div>
            <h2>Now Showing</h2>
            <p>Find your next favourite movie.</p>
        </div>

        <a href="#movies" class="view-all">View All →</a>
    </div>

    <?php if ($result->num_rows > 0): ?>

        <div class="movie-grid">

            <?php while ($movie = $result->fetch_assoc()): ?>

                <article class="movie-card">

                    <div class="poster">

                        <?php if (!empty($movie['poster_url'])): ?>

                            <img
                                src="<?php echo htmlspecialchars($movie['poster_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >

                            <div class="poster-placeholder"
                                 style="display:none;">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </div>

                        <?php else: ?>

                            <div class="poster-placeholder">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="movie-info">

                        <h3>
                            <?php echo htmlspecialchars($movie['title']); ?>
                        </h3>

                        <span class="genre">
                            <?php echo htmlspecialchars($movie['genre'] ?: 'Movie'); ?>
                        </span>

                        <div class="movie-details">
                            <div>
                                Language:
                                <?php echo htmlspecialchars($movie['language']); ?>
                            </div>

                            <div>
                                Duration:
                                <?php echo (int)$movie['duration']; ?> min
                            </div>

                            <div>
                                Release:
                                <?php echo htmlspecialchars($movie['release_date']); ?>
                            </div>
                        </div>

                        <a class="book-button"
                           href="theater_selection.php?movie_id=<?php echo (int)$movie['movie_id']; ?>">
                            Book Tickets &nbsp; →
                        </a>

                    </div>
                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">
            <h3>No movies available</h3>
            <p>Please add movies to your database and refresh this page.</p>
        </div>

    <?php endif; ?>

</main>

<footer>
    <a class="logo" href="index.php">🎬 Cine<span>Book</span></a>

    <div class="footer-links">
        <a href="index.php">Home</a>
        <a href="#movies">Movies</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my_bookings.php">My Bookings</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>

    <p>&copy; <?php echo date('Y'); ?> CineBook. All rights reserved.</p>
</footer>

</body>
</html>
